<?php
declare(strict_types=1);

class AuthController {
    public function showLogin(): void {
        if (User::current()) {
            header('Location: index.php');
            exit;
        }
        $error = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $returnTo = $_POST['return_to'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['auth_error'] = 'Введите email и пароль.';
            header('Location: index.php?page=login');
            exit;
        }

        $db = Database::connection();
        $limiter = new LoginRateLimiter($db);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!$limiter->isAllowed($email, $ip)) {
            $_SESSION['auth_error'] = 'Слишком много попыток. Повторите через 15 минут.';
            header('Location: index.php?page=login');
            exit;
        }

        try {
            $user = (new User($db))->authenticate($email, $password);
        } catch (RuntimeException $e) {
            $user = null;
        }
        if (!$user) {
            $limiter->recordFailure($email, $ip);
            $_SESSION['auth_error'] = 'Неверный адрес эл. почты или пароль.';
            header('Location: index.php?page=login');
            exit;
        }
        $limiter->clear($email, $ip);

        session_regenerate_id(true);

        // Merge guest cart with user cart
        $this->mergeGuestCart($user['id']);

        // Merge guest wishlist (localStorage ids posted by the browser as JSON)
        $guestWishlist = array_filter(array_map('intval', (array)json_decode((string)($_POST['guest_wishlist'] ?? '[]'), true)));
        if ($guestWishlist) {
            (new Wishlist(Database::connection()))->mergeGuest((int)$user['id'], $guestWishlist);
        }

        $_SESSION['user'] = $user;
        $_SESSION['toast'] = ['type' => 'success', 'message' => "С возвращением, {$user['name']}!"];

        if (!empty($returnTo) && str_starts_with($returnTo, 'index.php')) {
            header("Location: $returnTo");
            exit;
        }

        if ($user['role'] === 'admin') {
            header('Location: index.php?page=admin');
        } else {
            header('Location: index.php');
        }
        exit;
    }

    private function mergeGuestCart(int $userId): void {
        $guestCart = $_SESSION['cart'] ?? [];
        if (empty($guestCart)) return;

        $db = Database::connection();
        
        // Get or create user's persistent cart
        $stmt = $db->prepare("SELECT cart_data FROM user_carts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userCartRow = $stmt->fetch();
        
        $userCart = $userCartRow ? json_decode($userCartRow['cart_data'], true) : [];
        
        // Merge: user cart takes precedence, but add guest items not in user cart
        foreach ($guestCart as $productId => $item) {
            if (!isset($userCart[$productId])) {
                $userCart[$productId] = $item;
            } else {
                // If same product, take max quantity (or sum)
                $userCart[$productId]['qty'] = max($userCart[$productId]['qty'], $item['qty']);
            }
        }
        
        // Save merged cart
        $stmt = $db->prepare("
            INSERT INTO user_carts (user_id, cart_data, updated_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data), updated_at = NOW()
        ");
        $stmt->execute([$userId, json_encode($userCart)]);
        
        // Update session cart
        $_SESSION['cart'] = $userCart;
    }

    public function showRegister(): void {
        if (User::current()) {
            header('Location: index.php');
            exit;
        }
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$this->isStrongPassword($password)) {
            $_SESSION['auth_error'] = 'Пароль должен содержать минимум 12 символов, строчную и заглавную букву, а также цифру.';
            header('Location: index.php?page=register');
            exit;
        }

        $userModel = new User(Database::connection());
        try {
            $userId = $userModel->register($name, $email, $password, 'customer');
            session_regenerate_id(true);
            
            // Merge guest cart for new user
            $this->mergeGuestCart($userId);

            // Merge guest wishlist for new user
            $guestWishlist = array_filter(array_map('intval', (array)json_decode((string)($_POST['guest_wishlist'] ?? '[]'), true)));
            if ($guestWishlist) {
                (new Wishlist(Database::connection()))->mergeGuest($userId, $guestWishlist);
            }
            
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
                'avatar_url' => null
            ];
            $token = (new AccountToken(Database::connection()))->create($userId, 'verify_email', 1440);
            $this->sendAccountLink($email, 'Подтвердите email VINYLWAVE', 'verify-email', $token);
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Аккаунт создан. Проверьте почту и подтвердите email.'];
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['auth_error'] = $e->getMessage();
            header('Location: index.php?page=register');
            exit;
        }
    }

    public function showForgotPassword(): void {
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_success']);
        require __DIR__ . '/../views/auth/forgot-password.php';
    }

    public function requestPasswordReset(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $db = Database::connection();
            $user = (new User($db))->findByEmail($email);
            if ($user) {
                $token = (new AccountToken($db))->create((int)$user['id'], 'password_reset');
                $this->sendAccountLink($user['email'], 'Восстановление пароля VINYLWAVE', 'reset-password', $token);
            }
        }
        $_SESSION['auth_success'] = 'Если аккаунт существует, ссылка для восстановления уже отправлена.';
        header('Location: index.php?page=forgot-password');
        exit;
    }

    public function showResetPassword(): void {
        $token = (string)($_GET['token'] ?? '');
        require __DIR__ . '/../views/auth/reset-password.php';
    }

    public function resetPassword(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $token = (string)($_POST['token'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $userId = (new AccountToken(Database::connection()))->consume($token, 'password_reset');
        if (!$userId || !$this->isStrongPassword($password)) {
            $_SESSION['auth_error'] = 'Ссылка недействительна или пароль не соответствует требованиям.';
            header('Location: index.php?page=forgot-password');
            exit;
        }
        (new User(Database::connection()))->setPassword($userId, $password);
        $_SESSION['auth_success'] = 'Пароль обновлён. Теперь можно войти.';
        header('Location: index.php?page=login');
        exit;
    }

    public function verifyEmail(): void {
        $token = (string)($_GET['token'] ?? '');
        $userId = (new AccountToken(Database::connection()))->consume($token, 'verify_email');
        $_SESSION['auth_success'] = $userId ? 'Email успешно подтверждён.' : 'Ссылка подтверждения недействительна или устарела.';
        if ($userId) {
            (new User(Database::connection()))->markEmailVerified($userId);
        }
        header('Location: index.php?page=login');
        exit;
    }

    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 12
            && preg_match('/[a-z]/', $password)
            && preg_match('/[A-Z]/', $password)
            && preg_match('/\\d/', $password);
    }

    private function sendAccountLink(string $email, string $subject, string $page, string $token): void {
        $base = AppConfig::appUrl();
        $url = ($base !== '' ? $base . '/index.php' : 'http://localhost/index.php')
            . '?page=' . rawurlencode($page) . '&token=' . rawurlencode($token);
        EmailService::send($email, $subject, "Откройте ссылку:\n{$url}\n\nЕсли это были не вы, проигнорируйте письмо.");
    }

    public function logout(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $user = User::current();
        if ($user) {
            // Save cart to persistent storage
            $this->saveUserCart($user['id']);
        }
        unset($_SESSION['user']);
        unset($_SESSION['cart']);
        $_SESSION['toast'] = ['type' => 'info', 'message' => 'Вы успешно вышли из аккаунта.'];
        header('Location: index.php');
        exit;
    }

    private function saveUserCart(int $userId): void {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) return;
        
        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO user_carts (user_id, cart_data, updated_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data), updated_at = NOW()
        ");
        $stmt->execute([$userId, json_encode($cart)]);
    }

    public function profile(): void {
        $user = User::current();
        if (!$user) {
            header('Location: index.php?page=login&return_to=index.php?page=profile');
            exit;
        }

        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $order['items'] = $itemStmt->fetchAll();
        }
        unset($order);

        require __DIR__ . '/../views/profile.php';
    }
}
