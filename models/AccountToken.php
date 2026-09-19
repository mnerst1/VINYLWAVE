<?php
declare(strict_types=1);

class AccountToken {
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $purpose, int $ttlMinutes = 60): string {
        $this->db->prepare('DELETE FROM account_tokens WHERE user_id = ? AND purpose = ? AND used_at IS NULL')->execute([$userId, $purpose]);
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable("+{$ttlMinutes} minutes"))->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare('INSERT INTO account_tokens (user_id, token_hash, purpose, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, hash('sha256', $token), $purpose, $expiresAt]);
        return $token;
    }

    public function consume(string $token, string $purpose): ?int {
        $stmt = $this->db->prepare('SELECT id, user_id, token_hash FROM account_tokens WHERE purpose = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC');
        $stmt->execute([$purpose]);
        foreach ($stmt->fetchAll() as $row) {
            if (hash_equals($row['token_hash'], hash('sha256', $token))) {
                $this->db->prepare('UPDATE account_tokens SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
                return (int)$row['user_id'];
            }
        }
        return null;
    }
}