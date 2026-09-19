<?php
declare(strict_types=1);

class User {
    public function __construct(private PDO $db) {}

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Full row including password hash and ban status (admin use only). */
    public function findFullById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allUsers(string $search = ''): array {
        $sql = "SELECT u.*, 
                       (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders_count,
                       (SELECT COALESCE(SUM(o.total), 0) FROM orders o WHERE o.user_id = u.id AND o.status != 'cancelled') AS total_spent
                FROM users u";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE u.name LIKE :q OR u.email LIKE :q";
            $params['q'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function setRole(int $id, string $role): bool {
        if (!in_array($role, ['customer', 'moderator', 'admin'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }

    public function setBanned(int $id, bool $banned, string $reason = ''): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_banned = ?, ban_reason = ? WHERE id = ?");
        return $stmt->execute([$banned ? 1 : 0, $banned && $reason !== '' ? $reason : null, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countCustomers(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, email, role, avatar_url, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (password_verify($password, $user['password'])) {
            if (!empty($user['is_banned'])) {
                throw new RuntimeException(
                    'Аккаунт заблокирован' . (!empty($user['ban_reason']) ? ": {$user['ban_reason']}" : '.')
                );
            }
            return [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar_url' => $user['avatar_url']
            ];
        }

        return null;
    }

    public function register(string $name, string $email, string $password, string $role = 'customer'): int {
        $email = strtolower(trim($email));
        if ($this->findByEmail($email)) {
            throw new RuntimeException("Пользователь с таким email уже зарегистрирован.");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([trim($name), $email, $hash, $role]);
        return (int)$this->db->lastInsertId();
    }

    public function setPassword(int $id, string $password): void {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function markEmailVerified(int $id): void {
        $stmt = $this->db->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function current(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool {
        return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    public static function isModerator(): bool {
        return isset($_SESSION['user']) && in_array(($_SESSION['user']['role'] ?? ''), ['moderator', 'admin'], true);
    }

    public static function isImpersonating(): bool {
        return !empty($_SESSION['impersonator_id']);
    }

    /** Remember the acting admin, then act as the given user. */
    public static function startImpersonation(int $userId, array $user): void {
        $_SESSION['impersonator_id'] = (int)User::current()['id'];
        $_SESSION['user'] = $user;
    }

    /** Restore the original admin account. */
    public static function stopImpersonation(): ?array {
        if (empty($_SESSION['impersonator_id'])) {
            return null;
        }
        $db = Database::connection();
        $model = new self($db);
        $admin = $model->findById((int)$_SESSION['impersonator_id']);
        unset($_SESSION['impersonator_id']);
        if ($admin) {
            $_SESSION['user'] = $admin;
        }
        return $admin;
    }

    public static function isCustomer(): bool {
        return isset($_SESSION['user']) && in_array(($_SESSION['user']['role'] ?? ''), ['customer', 'admin'], true);
    }
}
