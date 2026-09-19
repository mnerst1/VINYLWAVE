<?php
declare(strict_types=1);

class Review {
    public function __construct(private PDO $db) {}

    public function forProduct(int $productId, bool $onlyApproved = true): array {
        $sql = "
            SELECT r.*, u.name AS user_name, u.avatar_url, u.role AS user_role
            FROM reviews r
            JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ?";
        if ($onlyApproved) {
            $sql .= " AND r.status = 'approved'";
        }
        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function summary(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total_count,
                AVG(rating) AS avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS star_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS star_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS star_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS star_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS star_1
            FROM reviews
            WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$productId]);
        $row = $stmt->fetch() ?: [];

        $total = (int)($row['total_count'] ?? 0);
        $avg = $total > 0 ? round((float)$row['avg_rating'], 1) : 5.0;

        return [
            'count' => $total,
            'average' => $avg,
            'breakdown' => [
                5 => (int)($row['star_5'] ?? 0),
                4 => (int)($row['star_4'] ?? 0),
                3 => (int)($row['star_3'] ?? 0),
                2 => (int)($row['star_2'] ?? 0),
                1 => (int)($row['star_1'] ?? 0),
            ]
        ];
    }

    public function create(int $productId, int $userId, int $rating, string $title, string $comment, ?string $photoUrl = null, string $status = 'pending'): int {
        $rating = max(1, min(5, $rating));
        $stmt = $this->db->prepare("
            INSERT INTO reviews (product_id, user_id, rating, title, comment, photo_url, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$productId, $userId, $rating, trim($title), trim($comment), $photoUrl, $status]);
        return (int)$this->db->lastInsertId();
    }

    public function pending(int $limit = 100): array {
        $stmt = $this->db->prepare("
            SELECT r.*, p.name AS product_name, p.cover_url, u.name AS user_name, u.email AS user_email
            FROM reviews r
            JOIN products p ON p.id = r.product_id
            JOIN users u ON u.id = r.user_id
            WHERE r.status = 'pending'
            ORDER BY r.created_at ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function pendingCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
    }

    public function setStatus(int $id, string $status): bool {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function bulkSetStatus(array $ids, string $status): int {
        if (!$ids || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE reviews SET status = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$status], $ids));
        return $stmt->rowCount();
    }

    public function all(int $limit = 50, string $status = ''): array {
        $sql = "
            SELECT r.*, p.name AS product_name, p.cover_url, u.name AS user_name, u.email AS user_email
            FROM reviews r
            JOIN products p ON p.id = r.product_id
            JOIN users u ON u.id = r.user_id";
        $params = [];
        if ($status !== '') {
            $sql .= " WHERE r.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        if ($status !== '') {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
