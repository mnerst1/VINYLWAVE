<?php
declare(strict_types=1);

class InventoryReservation {
    public function __construct(private PDO $db) {}

    public function create(string $sessionId, int $productId, int $quantity, int $ttlMinutes = 15): bool {
        $expiresAt = (new DateTime())->modify("+{$ttlMinutes} minutes")->format('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare("
            INSERT INTO inventory_reservations (session_id, product_id, quantity, expires_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), expires_at = VALUES(expires_at)
        ");
        return $stmt->execute([$sessionId, $productId, $quantity, $expiresAt]);
    }

    public function getBySession(string $sessionId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM inventory_reservations 
            WHERE session_id = ? AND expires_at > NOW()
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    public function release(string $sessionId): int {
        $stmt = $this->db->prepare("DELETE FROM inventory_reservations WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->rowCount();
    }

    public function releaseByProduct(int $productId): int {
        $stmt = $this->db->prepare("DELETE FROM inventory_reservations WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->rowCount();
    }

    public function cleanupExpired(): int {
        $stmt = $this->db->prepare("DELETE FROM inventory_reservations WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getReservedQuantity(int $productId, ?string $excludeSessionId = null): int {
        $excludeClause = $excludeSessionId === null ? '' : ' AND session_id != ?';
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(quantity), 0) as total 
            FROM inventory_reservations 
            WHERE product_id = ? AND expires_at > NOW(){$excludeClause}
        ");
        $params = [$productId];
        if ($excludeSessionId !== null) {
            $params[] = $excludeSessionId;
        }
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function extendExpiry(string $sessionId, int $ttlMinutes = 15): bool {
        $expiresAt = (new DateTime())->modify("+{$ttlMinutes} minutes")->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            UPDATE inventory_reservations 
            SET expires_at = ? 
            WHERE session_id = ? AND expires_at > NOW()
        ");
        return $stmt->execute([$expiresAt, $sessionId]);
    }
}