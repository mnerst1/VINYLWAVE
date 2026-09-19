<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/CartTrait.php';

class CartController {
    use CartTrait;

    public function index(): void {
        if (User::isAdmin()) {
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Режим администратора: покупки отключены для админ-аккаунта.'];
            header('Location: index.php?page=admin');
            exit;
        }
        $this->loadUserCart();
        $items = $this->getCartItems();
        require __DIR__ . '/../views/cart.php';
    }

    private function loadUserCart(): void {
        $user = User::current();
        if (!$user || !empty($_SESSION['cart'])) return;

        $db = Database::connection();
        $stmt = $db->prepare("SELECT cart_data FROM user_carts WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        
        if ($row && !empty($row['cart_data'])) {
            $_SESSION['cart'] = json_decode($row['cart_data'], true) ?? [];
        }
    }

    private function saveUserCart(): void {
        $user = User::current();
        if (!$user) return;
        
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) return;

        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO user_carts (user_id, cart_data, updated_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE cart_data = VALUES(cart_data), updated_at = NOW()
        ");
        $stmt->execute([$user['id'], json_encode($cart)]);
    }

    public function add(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        if (User::isAdmin()) {
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Режим администратора: покупки отключены для админ-аккаунта.'];
            header('Location: index.php?page=admin');
            exit;
        }
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $size = $_POST['size'] ?? null;
        $p = (new Product(Database::connection()))->find($id);
        if (!$p) { header('Location: index.php'); exit; }
        $_SESSION['cart'][$id] = ['qty' => min($p['stock'], ($_SESSION['cart'][$id]['qty'] ?? 0) + $qty), 'size' => $size];
        $this->saveUserCart();
        header('Location: index.php?page=cart');
        exit;
    }

    public function update(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        foreach (($_POST['qty'] ?? []) as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id]['qty'] = $qty;
            }
        }
        $this->saveUserCart();
        header('Location: index.php?page=cart');
        exit;
    }
}
