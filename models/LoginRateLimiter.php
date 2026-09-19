<?php
declare(strict_types=1);

class LoginRateLimiter {
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public function __construct(private PDO $db) {}

    public function isAllowed(string $email, string $ip): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $stmt->execute([$this->normaliseEmail($email), $ip]);
        return (int)$stmt->fetchColumn() < self::MAX_ATTEMPTS;
    }

    public function recordFailure(string $email, string $ip): void {
        $stmt = $this->db->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
        $stmt->execute([$this->normaliseEmail($email), $ip]);
    }

    public function clear(string $email, string $ip): void {
        $stmt = $this->db->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
        $stmt->execute([$this->normaliseEmail($email), $ip]);
    }

    private function normaliseEmail(string $email): string {
        return strtolower(trim($email));
    }
}