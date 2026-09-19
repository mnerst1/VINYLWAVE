<?php
declare(strict_types=1);

trait CartTrait {
    protected function getCartItems(): array {
        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) return [];
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("SELECT p.*, a.name artist_name FROM products p JOIN artists a ON a.id = p.artist_id WHERE p.id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = [];
        foreach ($stmt->fetchAll() as $p) {
            $p['qty'] = $cart[$p['id']]['qty'];
            $p['size'] = $cart[$p['id']]['size'] ?? null;
            $rows[] = $p;
        }
        return $rows;
    }
}