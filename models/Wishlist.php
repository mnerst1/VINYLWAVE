<?php
declare(strict_types=1);

class Wishlist {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function add(int $userId, int $productId): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)
            ");
            return $stmt->execute([$userId, $productId]);
        } catch (Throwable) {
            return false;
        }
    }

    public function remove(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }

    public function has(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }

    public function getForUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, a.name AS artist_name
            FROM wishlist w
            JOIN products p ON p.id = w.product_id
            JOIN artists a ON a.id = p.artist_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getIdsForUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function countForUser(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function clear(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function mergeGuestWishlist(int $userId, array $guestProductIds): int {
        $added = 0;
        foreach ($guestProductIds as $pid) {
            $pid = (int)$pid;
            if ($this->add($userId, $pid)) {
                $added++;
            }
        }
        return $added;
    }
}