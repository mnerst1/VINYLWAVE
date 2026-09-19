<?php
declare(strict_types=1);

class WishlistController {
    /** Wishlist page (registered users) or a hint to log in. */
    public function index(): void {
        $user = User::current();
        $items = [];
        $wishlistIds = [];

        if ($user) {
            $wishlistModel = new Wishlist(Database::connection());
            $items = $wishlistModel->forUser((int)$user['id']);
            $wishlistIds = array_map('intval', array_column($items, 'id'));
        }

        require __DIR__ . '/../views/wishlist.php';
    }

    /** Toggle wishlist state; works for guests (localStorage) and users (DB). */
    public function toggle(): void {
        header('Content-Type: application/json');
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'bad product']);
            return;
        }

        $user = User::current();
        if (!$user) {
            // Guests manage the wishlist client-side
            echo json_encode(['ok' => true, 'guest' => true, 'active' => null]);
            return;
        }

        $model = new Wishlist(Database::connection());
        $active = $model->contains((int)$user['id'], $productId)
            ? !$model->remove((int)$user['id'], $productId)
            : $model->add((int)$user['id'], $productId);

        echo json_encode([
            'ok' => true,
            'guest' => false,
            'active' => $active,
            'count' => $model->countForUser((int)$user['id']),
        ]);
    }
}
