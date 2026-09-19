<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/CartTrait.php';
require_once __DIR__ . '/../models/InventoryReservation.php';
require_once __DIR__ . '/../models/ShippingCalculator.php';

class CheckoutController {
    use CartTrait;
    private const RESERVATION_TTL = 15; // minutes

    public function show(): void {
        $items = $this->getCartItems();
        if (!$items) {
            header('Location: index.php?page=cart');
            exit;
        }
        
        // Create inventory reservations for this session
        $this->createReservations($items);
        
        // Calculate shipping options
        $city = $_POST['city'] ?? $_SESSION['shipping_city'] ?? '';
        $shipping = null;
        if ($city) {
            $shippingCalc = new ShippingCalculator(Database::connection());
            $shipping = $shippingCalc->calculate($items, $city);
            $_SESSION['shipping_city'] = $city;
            $_SESSION['shipping_option'] = $_POST['shipping_option'] ?? 'standard';
        }
        
        $currentUser = User::current();
        require __DIR__ . '/../views/checkout.php';
    }

    // AJAX endpoint to calculate shipping
    public function calculateShipping(): void {
        header('Content-Type: application/json');
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        
        $items = $this->getCartItems();
        if (!$items) {
            echo json_encode(['error' => 'Корзина пуста']);
            return;
        }
        
        $city = trim($_POST['city'] ?? '');
        if (!$city) {
            echo json_encode(['error' => 'Укажите город']);
            return;
        }
        
        $shippingCalc = new ShippingCalculator(Database::connection());
        $shipping = $shippingCalc->calculate($items, $city);
        
        $_SESSION['shipping_city'] = $city;
        $_SESSION['shipping_option'] = 'standard';
        
        echo json_encode(['shipping' => $shipping]);
    }

    private function createReservations(array $items): void {
        $sessionId = session_id();
        $reservationModel = new InventoryReservation(Database::connection());
        
        // Clean up any existing reservations for this session
        $reservationModel->release($sessionId);
        
        // Create new reservations
        foreach ($items as $item) {
            $reservationModel->create($sessionId, $item['id'], $item['qty'], self::RESERVATION_TTL);
        }
    }

    private function confirmReservations(array $items): void {
        $sessionId = session_id();
        $reservationModel = new InventoryReservation(Database::connection());
        
        // Release reservations (stock already deducted in place())
        $reservationModel->release($sessionId);
    }

    private function releaseReservations(): void {
        $sessionId = session_id();
        $reservationModel = new InventoryReservation(Database::connection());
        $reservationModel->release($sessionId);
    }

    public function place(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $items = $this->getCartItems();
        if (!$items) {
            header('Location: index.php?page=cart');
            exit;
        }

        $currentUser = User::current();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $shippingOption = $_POST['shipping_option'] ?? 'standard';

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$address || !$city) {
            $error = 'Заполните все поля корректно.';
            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $subtotal = array_reduce($items, fn($s, $i) => $s + $i['price'] * $i['qty'], 0);
        $promo = strtoupper(trim($_POST['promo'] ?? ''));
        $discount = $promo === 'TRAVIS10' ? $subtotal * 0.10 : 0;
        
        // Calculate shipping
        $shippingCalc = new ShippingCalculator(Database::connection());
        $shipping = $shippingCalc->calculate($items, $city);
        $shippingCost = $shipping[$shippingOption]['cost'] ?? $shipping['standard']['cost'];
        
        $total = max(0, $subtotal - $discount + $shippingCost);

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $userId = $currentUser ? (int)$currentUser['id'] : null;

            // Lock product rows to prevent race conditions
            $ids = array_column($items, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $lockStmt = $db->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
            $lockStmt->execute($ids);
            $lockedStock = [];
            foreach ($lockStmt->fetchAll() as $row) {
                $lockedStock[$row['id']] = (int)$row['stock'];
            }

            // Verify stock availability (considering reservations)
            $reservationModel = new InventoryReservation($db);
            foreach ($items as $item) {
                $reservedByOthers = $reservationModel->getReservedQuantity($item['id'], session_id());
                $available = ($lockedStock[$item['id']] ?? 0) - $reservedByOthers;
                
                if ($item['qty'] > $available) {
                    throw new RuntimeException("Недостаточно товара «{$item['name']}» на складе. Доступно: {$available}");
                }
            }

            $s = $db->prepare("INSERT INTO orders(user_id, customer_name, email, address, city, total, promo_code, status, shipping_cost, shipping_method) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $s->execute([$userId, $name, $email, $address, $city, $total, $promo ?: null, 'paid', $shippingCost, $shippingOption]);
            $orderId = (int)$db->lastInsertId();

            $it = $db->prepare("INSERT INTO order_items(order_id, product_id, product_name, quantity, unit_price, size) VALUES(?, ?, ?, ?, ?, ?)");
            $productModel = new Product($db);
            foreach ($items as $i) {
                $it->execute([$orderId, $i['id'], $i['name'], $i['qty'], $i['price'], $i['size']]);
                $productModel->decrementStock((int)$i['id'], (int)$i['qty']);
            }
            
            // Confirm and release reservations
            $this->confirmReservations($items);
            
            $db->commit();
            unset($_SESSION['cart']);
            unset($_SESSION['shipping_city'], $_SESSION['shipping_option']);
            $_SESSION['toast'] = ['type' => 'success', 'message' => "Заказ #{$orderId} успешно оформлен!"];
            $orderIdForView = $orderId;
            require __DIR__ . '/../views/success.php';
        } catch (Throwable $e) {
            $db->rollBack();
            $this->releaseReservations();
            $error = $e->getMessage();
            require __DIR__ . '/../views/checkout.php';
        }
    }
    
    // AJAX endpoint to extend reservation (call periodically from checkout page)
    public function extendReservation(): void {
        header('Content-Type: application/json');
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $sessionId = session_id();
        $reservationModel = new InventoryReservation(Database::connection());
        $success = $reservationModel->extendExpiry($sessionId, self::RESERVATION_TTL);
        echo json_encode(['success' => $success]);
    }
}
