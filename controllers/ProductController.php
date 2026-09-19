<?php
declare(strict_types=1);

class ProductController {
    public function show(int $id): void {
        $db = Database::connection();
        $productModel = new Product($db);
        $p = $productModel->find($id);
        if (!$p) {
            header('Location: index.php');
            exit;
        }

        $tracks = $productModel->tracks($id);
        $reviewModel = new Review($db);
        $reviews = $reviewModel->forProduct($id);
        $reviewSummary = $reviewModel->summary($id);
        $currentUser = User::current();
        $product = $p;

        // Wishlist state for the heart toggle
        $inWishlist = false;
        if ($currentUser) {
            $inWishlist = (new Wishlist($db))->contains((int)$currentUser['id'], $id);
        }

        require __DIR__ . '/../views/product.php';
    }
}
