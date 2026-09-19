<?php
declare(strict_types=1);

class HomeController {
    public const PAGE_SIZE = 9;

    public function index(): void {
        $productModel = new Product(Database::connection());
        $filters = [
            'category' => $_GET['category'] ?? '',
            'artist' => $_GET['artist'] ?? '',
            'genre' => $_GET['genre'] ?? '',
            'variant' => $_GET['variant'] ?? '',
            'q' => $_GET['q'] ?? '',
            'in_stock' => !empty($_GET['in_stock'])
        ];

        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage = self::PAGE_SIZE;

        $filters['limit'] = $perPage;
        $filters['offset'] = ($page - 1) * $perPage;

        $products = $productModel->all($filters);
        $totalCount = $productModel->countAll($filters);
        $hasMore = $filters['offset'] + count($products) < $totalCount;

        $artists = $productModel->artists();
        $genres = $productModel->genres();
        $variants = $productModel->variants();

        // Wishlist ids for the current user (guests use localStorage)
        $wishlistIds = [];
        $currentUser = User::current();
        if ($currentUser) {
            try {
                $wishlistIds = (new Wishlist(Database::connection()))->productIdsForUser((int)$currentUser['id']);
            } catch (Throwable) { /* wishlist table not migrated yet */ }
        }

        require __DIR__ . '/../views/home.php';
    }

    /** JSON endpoint for "Load more" / infinite scroll. */
    public function loadMore(): void {
        header('Content-Type: application/json');

        $productModel = new Product(Database::connection());
        $filters = [
            'category' => $_GET['category'] ?? '',
            'artist' => $_GET['artist'] ?? '',
            'genre' => $_GET['genre'] ?? '',
            'variant' => $_GET['variant'] ?? '',
            'q' => $_GET['q'] ?? '',
            'in_stock' => !empty($_GET['in_stock']),
            'limit' => self::PAGE_SIZE,
            'offset' => max(0, (int)($_GET['offset'] ?? 0)),
        ];

        $products = $productModel->all($filters);
        $totalCount = $productModel->countAll($filters);

        ob_start();
        foreach ($products as $p) {
            include __DIR__ . '/../views/partials/product-card.php';
        }
        $html = ob_get_clean();

        echo json_encode([
            'html' => $html,
            'count' => count($products),
            'total' => $totalCount,
            'hasMore' => $filters['offset'] + count($products) < $totalCount,
            'nextOffset' => $filters['offset'] + count($products),
        ]);
    }

    /** JSON feed for "recently viewed", guest wishlist and compare (ids from localStorage). */
    public function recentProducts(): void {
        header('Content-Type: application/json');
        $ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')), fn($i) => $i > 0);
        $ids = array_slice(array_unique($ids), 0, 24);
        if (!$ids) {
            echo '[]';
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("
            SELECT p.id, p.name, p.slug, p.category, p.price, p.stock, p.genre, p.color_variant,
                   p.cover_url, p.cover_thumb_url, p.is_limited, a.name AS artist_name,
                   (SELECT COALESCE(AVG(rating), 5.0) FROM reviews WHERE product_id = p.id AND status = 'approved') AS avg_rating,
                   (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') AS reviews_count
            FROM products p JOIN artists a ON a.id = p.artist_id
            WHERE p.id IN ($placeholders) AND p.is_active = 1
        ");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        // Preserve the recency order from the client
        $byId = array_column($rows, null, 'id');
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) $ordered[] = $byId[$id];
        }
        echo json_encode($ordered);
    }
}
