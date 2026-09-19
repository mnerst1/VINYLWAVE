<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Env.php';

Env::load();

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Migration must be run from the command line.');
}

try {
    $db = Database::connection();
    echo "[1/6] Connecting to database...\n";

    // 1. Users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
        avatar_url VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Users table checked/created.\n";

    // Create the first administrator only when explicitly configured for this CLI run.
    $initialAdminEmail = trim((string)Env::get('INITIAL_ADMIN_EMAIL', ''));
    $initialAdminPassword = (string)Env::get('INITIAL_ADMIN_PASSWORD', '');
    if (filter_var($initialAdminEmail, FILTER_VALIDATE_EMAIL) && strlen($initialAdminPassword) >= 12) {
        $checkUser = $db->prepare("SELECT id FROM users WHERE email = ?");
        $checkUser->execute([$initialAdminEmail]);
        if (!$checkUser->fetch()) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Administrator', $initialAdminEmail, password_hash($initialAdminPassword, PASSWORD_DEFAULT), 'admin']);
            echo "  Created configured initial administrator.\n";
        }
    } else {
        echo "  Initial admin skipped; set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD in .env.\n";
    }

    // 2. Modify products category enum to include single and dvd
    echo "[2/6] Updating products table schema...\n";
    $db->exec("ALTER TABLE products MODIFY COLUMN category ENUM('vinyl','single','cd','dvd','merch') NOT NULL");

    // Add video_url column if not exists
    $columns = $db->query("SHOW COLUMNS FROM products LIKE 'video_url'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE products ADD COLUMN video_url VARCHAR(500) DEFAULT NULL AFTER cover_url");
        echo "  Added video_url column to products.\n";
    }

    // 3. Add user_id to orders table if not exists
    echo "[3/6] Updating orders table schema...\n";
    $columns = $db->query("SHOW COLUMNS FROM orders LIKE 'user_id'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE orders ADD COLUMN user_id INT UNSIGNED DEFAULT NULL AFTER id");
        $db->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "  Added user_id column to orders.\n";
    }

    // 4. Reviews table
    echo "[4/6] Checking reviews table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        title VARCHAR(150) NOT NULL,
        comment TEXT NOT NULL,
        photo_url VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_product(product_id),
        INDEX idx_user(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Reviews table checked/created.\n";

    // 4b. Wishlist table
    echo "[4b/6] Checking wishlist table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS wishlist (
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Wishlist table checked/created.\n";

    // 4c. User carts table (persistent cart for logged-in users)
    echo "[4b/6] Checking user_carts table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS user_carts (
        user_id INT UNSIGNED PRIMARY KEY,
        cart_data JSON NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  User carts table checked/created.\n";

    // 4c. Inventory reservations table (hold stock during checkout)
    echo "[4c/6] Checking inventory_reservations table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS inventory_reservations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(128) NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_session(session_id),
        INDEX idx_expires(expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Inventory reservations table checked/created.\n";

    // 4d. Shipping zones table
    echo "[4d/6] Checking shipping_zones table...\n";
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
        INDEX idx_country(country)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Shipping zones table checked/created.\n";

    // 4e. Pickup points table
    echo "[4e/6] Checking pickup_points table...\n";
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
        INDEX idx_city(city)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "  Pickup points table checked/created.\n";

    // Seed default shipping zones for Kazakhstan
    $zoneCount = $db->query("SELECT COUNT(*) FROM shipping_zones")->fetchColumn();
    if ($zoneCount == 0) {
        $zones = [
            ['Алматы и Астана', 'KZ', 'Алматы,Астана', 0, 0, 15000, 2000, '1-2', '1', 10],
            ['Крупные города', 'KZ', 'Шымкент,Актобе,Караганда,Тараз,Павлодар,Усть-Каменогорск,Семей,Атырау,Костанай,Кызылорда,Уральск,Петропавловск,Тараз,Туркестан,Кокшетау,Жезказган,Актау,Актобе', 1500, 500, 25000, 3000, '2-3', '1-2', 20],
            ['Остальной Казахстан', 'KZ', '*', 2500, 800, 35000, 4000, '3-5', '2-3', 30],
            ['Международная', '*', '*', 5000, 1500, 100000, 10000, '7-14', '3-5', 100],
        ];
        
        $stmt = $db->prepare("INSERT INTO shipping_zones (name, country, cities, base_rate, per_kg, free_shipping_threshold, express_surcharge, standard_days, express_days, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($zones as $zone) {
            $stmt->execute($zone);
        }
        echo "  Seeded default shipping zones.\n";
    }

    // Seed default pickup points
    $pickupCount = $db->query("SELECT COUNT(*) FROM pickup_points")->fetchColumn();
    if ($pickupCount == 0) {
        $pickups = [
            ['VINYLWAVE Алматы', 'Алматы', 'пр. Абая 150, ТЦ Mega Center', '+7 (727) 2xx-xx-xx', '10:00-22:00', '43.2220,76.8512'],
            ['VINYLWAVE Астана', 'Астана', 'пр. Туран 37, ТЦ Khan Shatyr', '+7 (717) 2xx-xx-xx', '10:00-22:00', '51.1283,71.4307'],
            ['VINYLWAVE Шымкент', 'Шымкент', 'ул. Кабанбай батыра 45, ТЦ Aport Mall', '+7 (725) 2xx-xx-xx', '10:00-21:00', '42.3192,69.5976'],
        ];
        
        $stmt = $db->prepare("INSERT INTO pickup_points (name, city, address, phone, schedule, coordinates) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($pickups as $pickup) {
            $stmt->execute($pickup);
        }
        echo "  Seeded default pickup points.\n";
    }

    // 5. Seed sample singles, DVD and video URLs if missing
    echo "[5/6] Ensuring product catalog has singles, DVDs and videos...\n";
    // Update existing products with sample video links
    $db->exec("UPDATE products SET video_url = 'https://www.youtube.com/watch?v=2n3X9vS_q_0' WHERE id = 1 AND (video_url IS NULL OR video_url = '')");
    $db->exec("UPDATE products SET video_url = 'https://www.youtube.com/watch?v=diIFhc_698k' WHERE id = 3 AND (video_url IS NULL OR video_url = '')");

    // Add a Single if not exists
    $checkSingle = $db->query("SELECT id FROM products WHERE category = 'single' LIMIT 1")->fetch();
    if (!$checkSingle) {
        $stmt = $db->prepare("INSERT INTO products (artist_id, name, slug, category, genre, price, stock, color_variant, description, cover_url, video_url, is_limited, drop_ends_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            1,
            'MELTDOWN — 7" Vinyl Single',
            'meltdown-7-inch-single',
            'single',
            'Hip-Hop',
            18.99,
            45,
            'Clear Amber',
            'Official 7-inch collector single featuring MELTDOWN. Exclusive heavyweight jacket with foiled typography.',
            'https://images.unsplash.com/photo-1542208998-f6dbbb27a72f?auto=format&fit=crop&w=1000&q=85',
            'https://www.youtube.com/watch?v=2n3X9vS_q_0',
            1,
            date('Y-m-d H:i:s', strtotime('+4 days'))
        ]);
        $newSingleId = (int)$db->lastInsertId();
        $db->prepare("INSERT INTO tracks (product_id, track_number, title, preview_url, duration_seconds) VALUES (?, ?, ?, ?, ?)")
           ->execute([$newSingleId, 1, 'MELTDOWN (feat. Drake)', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 15]);
        echo "  Added sample 7\" single product.\n";
    } else {
        echo "  Single product already exists, skipping.\n";
    }

    // Add a DVD if not exists
    $checkDvd = $db->query("SELECT id FROM products WHERE category = 'dvd' LIMIT 1")->fetch();
    if (!$checkDvd) {
        $stmt = $db->prepare("INSERT INTO products (artist_id, name, slug, category, genre, price, stock, color_variant, description, cover_url, video_url, is_limited, drop_ends_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            1,
            'CIRCUS MAXIMUS — Tour Film DVD & Booklet',
            'circus-maximus-film-dvd',
            'dvd',
            'Hip-Hop',
            34.99,
            25,
            'Special Edition Box',
            'Official concert and visual film DVD release with 48-page glossy archival photo booklet and unreleased studio vignettes.',
            'https://images.unsplash.com/photo-1518609878373-06d740f60d8b?auto=format&fit=crop&w=1000&q=85',
            'https://www.youtube.com/watch?v=2n3X9vS_q_0',
            0,
            null
        ]);
        echo "  Added sample DVD product.\n";
    } else {
        echo "  DVD product already exists, skipping.\n";
    }

    // 6. Seed sample reviews with photos
    echo "[6/6] Checking sample reviews...\n";
    $checkReview = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    if ($checkReview == 0) {
        $custUser = $db->query("SELECT id FROM users WHERE email = 'alex@vinylwave.com'")->fetch();
        $adminUser = $db->query("SELECT id FROM users WHERE email = 'admin@vinylwave.com'")->fetch();
        $userId = $custUser ? (int)$custUser['id'] : 1;
        $adminId = $adminUser ? (int)$adminUser['id'] : 1;

        $revStmt = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, photo_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Review for UTOPIA (product 1)
        $revStmt->execute([
            1,
            $userId,
            5,
            'Звук на высоте, винил невероятно красивый!',
            'Получил пластинку за 3 дня. Красный сплаттер выглядит вживую даже круче чем на фото, дорожки чистые, бас плотный. Gatefold конверт супер плотный.',
            'https://images.unsplash.com/photo-1539375665275-f9de415ef9ac?auto=format&fit=crop&w=800&q=80',
            date('Y-m-d H:i:s', strtotime('-2 days'))
        ]);

        $revStmt->execute([
            1,
            $adminId,
            5,
            'Коллекционное издание высшего уровня',
            'Превосходное качество прессинга. Рекомендую всем ценителям современного хип-хопа. Прикрепляю фото распаковки.',
            'https://images.unsplash.com/photo-1603048588665-791ca8aea617?auto=format&fit=crop&w=800&q=80',
            date('Y-m-d H:i:s', strtotime('-5 days'))
        ]);

        // Review for Blonde (product 3)
        $revStmt->execute([
            3,
            $userId,
            5,
            'Чистый прозрачный винил — мечта!',
            'Упаковано было в тройной картон с пупыркой. Ни одного замятия на углах. Звучание треков Nikes и Ivy просто до мурашек.',
            'https://images.unsplash.com/photo-1461360228754-6e81c478b882?auto=format&fit=crop&w=800&q=80',
            date('Y-m-d H:i:s', strtotime('-1 day'))
        ]);
        echo "  Seeded 3 sample reviews with photos.\n";
    } else {
        echo "  Reviews already exist, skipping seed.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Throwable $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
