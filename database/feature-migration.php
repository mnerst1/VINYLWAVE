<?php
declare(strict_types=1);

/*
 * VINYLWAVE feature migration (idempotent).
 * Adds: user roles + bans, review moderation status, product is_active,
 * video thumbnails, wishlist. Safe to run multiple times.
 *
 * Run from the command line only:
 *   php database/feature-migration.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Env.php';

Env::load();

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Migration must be run from the command line.');
}

function columnExists(PDO $db, string $table, string $column): bool {
    $stmt = $db->prepare('
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
        LIMIT 1
    ');
    $stmt->execute([$table, $column]);
    return (bool)$stmt->fetchColumn();
}

function tableExists(PDO $db, string $table): bool {
    $stmt = $db->prepare('
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
        LIMIT 1
    ');
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

function indexExists(PDO $db, string $table, string $index): bool {
    $stmt = $db->prepare('
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
        LIMIT 1
    ');
    $stmt->execute([$table, $index]);
    return (bool)$stmt->fetchColumn();
}

try {
    $db = Database::connection();
    echo "[1/9] Users: roles, bans...\n";

    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('customer','moderator','admin') NOT NULL DEFAULT 'customer'");
    if (!columnExists($db, 'users', 'is_banned')) {
        $db->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
        echo "  Added users.is_banned\n";
    }
    if (!columnExists($db, 'users', 'ban_reason')) {
        $db->exec("ALTER TABLE users ADD COLUMN ban_reason VARCHAR(255) DEFAULT NULL AFTER is_banned");
        echo "  Added users.ban_reason\n";
    }

    echo "[2/9] Reviews: moderation status...\n";
    if (!columnExists($db, 'reviews', 'status')) {
        // Existing reviews stay published (approved), new ones go to the moderation queue.
        $db->exec("ALTER TABLE reviews ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER photo_url");
        $db->exec("ALTER TABLE reviews ADD INDEX idx_reviews_status (status)");
        echo "  Added reviews.status (existing reviews marked approved)\n";
    }

    echo "[3/9] Products: activity flag + video thumbnail...\n";
    if (!columnExists($db, 'products', 'is_active')) {
        $db->exec("ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_limited");
        $db->exec("ALTER TABLE products ADD INDEX idx_products_active (is_active)");
        echo "  Added products.is_active\n";
    }
    if (!columnExists($db, 'products', 'video_thumb_url')) {
        $db->exec("ALTER TABLE products ADD COLUMN video_thumb_url VARCHAR(500) DEFAULT NULL AFTER video_url");
        echo "  Added products.video_thumb_url\n";
    }
    if (!columnExists($db, 'products', 'cover_thumb_url')) {
        $db->exec("ALTER TABLE products ADD COLUMN cover_thumb_url VARCHAR(500) DEFAULT NULL AFTER cover_url");
        echo "  Added products.cover_thumb_url\n";
    }

    echo "[4/9] Catalog and reporting indexes...\n";
    if (!indexExists($db, 'products', 'idx_catalog_active_created')) {
        $db->exec("ALTER TABLE products ADD INDEX idx_catalog_active_created (is_active, created_at)");
    }
    if (!indexExists($db, 'products', 'idx_catalog_genre')) {
        $db->exec("ALTER TABLE products ADD INDEX idx_catalog_genre (genre)");
    }
    if (!indexExists($db, 'orders', 'idx_orders_user_created')) {
        $db->exec("ALTER TABLE orders ADD INDEX idx_orders_user_created (user_id, created_at)");
    }

    echo "[5/9] Wishlist table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  Wishlist table checked/created.\n";

    echo "[6/9] Account security...\n";
    if (!columnExists($db, 'users', 'email_verified_at')) {
        $db->exec("ALTER TABLE users ADD COLUMN email_verified_at DATETIME DEFAULT NULL AFTER avatar_url");
        echo "  Added users.email_verified_at\n";
    }
    $db->exec("CREATE TABLE IF NOT EXISTS account_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        token_hash CHAR(64) NOT NULL,
        purpose VARCHAR(32) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_account_tokens_lookup (purpose, expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_login_attempts_lookup (email, ip_address, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "[7/9] Persistent carts and inventory reservations...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS user_carts (
        user_id INT UNSIGNED PRIMARY KEY,
        cart_data JSON NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS inventory_reservations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(128) NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY uq_reservation_session_product (session_id, product_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    if (!indexExists($db, 'inventory_reservations', 'uq_reservation_session_product')) {
        $db->exec("ALTER TABLE inventory_reservations ADD UNIQUE KEY uq_reservation_session_product (session_id, product_id)");
        echo "  Added inventory reservation uniqueness constraint\n";
    }

    echo "[8/9] Checkout shipping fields...\n";
    if (!columnExists($db, 'orders', 'shipping_cost')) {
        $db->exec("ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER status");
        echo "  Added orders.shipping_cost\n";
    }
    if (!columnExists($db, 'orders', 'shipping_method')) {
        $db->exec("ALTER TABLE orders ADD COLUMN shipping_method VARCHAR(30) NOT NULL DEFAULT 'standard' AFTER shipping_cost");
        echo "  Added orders.shipping_method\n";
    }
    $db->exec("CREATE TABLE IF NOT EXISTS shipping_zones (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        country CHAR(2) NOT NULL DEFAULT 'KZ',
        cities TEXT NOT NULL,
        base_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
        per_kg DECIMAL(10,2) NOT NULL DEFAULT 0,
        free_shipping_threshold DECIMAL(10,2) NOT NULL DEFAULT 0,
        express_surcharge DECIMAL(10,2) NOT NULL DEFAULT 0,
        standard_days VARCHAR(20) NOT NULL DEFAULT '5-7',
        express_days VARCHAR(20) NOT NULL DEFAULT '1-2',
        priority INT UNSIGNED NOT NULL DEFAULT 100,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_country (country)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS pickup_points (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        city VARCHAR(120) NOT NULL,
        address VARCHAR(255) NOT NULL,
        phone VARCHAR(30) DEFAULT NULL,
        schedule VARCHAR(200) DEFAULT NULL,
        coordinates VARCHAR(100) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_city (city)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "[9/9] Cleanup expired inventory reservations...\n";
    $db->exec("DELETE FROM inventory_reservations WHERE expires_at <= NOW()");

    echo "Feature migration completed successfully!\n";
} catch (Throwable $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
    exit(1);
}


