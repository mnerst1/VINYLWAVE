<?php
declare(strict_types=1);
session_set_cookie_params([
    'httponly' => true,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../config/Autoloader.php';
ErrorHandler::register();

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'product':
        (new ProductController())->show((int)($_GET['id'] ?? 0));
        break;

    case 'load-more':
        (new HomeController())->loadMore();
        break;

    case 'recent-api':
        (new HomeController())->recentProducts();
        break;

    case 'cart':
        $c = new CartController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
        } else {
            $c->index();
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CartController())->add();
        } else {
            header('Location: index.php');
        }
        break;

    case 'checkout':
        $c = new CheckoutController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->place();
        } else {
            $c->show();
        }
        break;

    case 'login':
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->login();
        } else {
            $auth->showLogin();
        }
        break;

    case 'register':
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->register();
        } else {
            $auth->showRegister();
        }
        break;

    case 'forgot-password':
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->requestPasswordReset();
        } else {
            $auth->showForgotPassword();
        }
        break;

    case 'reset-password':
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->resetPassword();
        } else {
            $auth->showResetPassword();
        }
        break;

    case 'verify-email':
        (new AuthController())->verifyEmail();
        break;

    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->logout();
        } else {
            header('Location: index.php');
        }
        break;

    case 'profile':
        (new AuthController())->profile();
        break;

    // --- Wishlist ---
    case 'wishlist':
        (new WishlistController())->index();
        break;

    case 'wishlist-toggle':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new WishlistController())->toggle();
        } else {
            header('Location: index.php?page=wishlist');
        }
        break;

    // --- Compare (static page) ---
    case 'compare':
        require __DIR__ . '/../views/compare.php';
        break;

    case 'admin':
        (new AdminController())->index();
        break;

    case 'admin-product-save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->saveProduct();
        } else {
            header('Location: index.php?page=admin&tab=products');
        }
        break;

    case 'admin-product-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->deleteProduct();
        } else {
            header('Location: index.php?page=admin&tab=products');
        }
        break;

    case 'admin-product-bulk':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->bulkProducts();
        } else {
            header('Location: index.php?page=admin&tab=products');
        }
        break;

    case 'admin-products-export':
        (new AdminController())->exportProducts();
        break;

    case 'admin-products-import':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->importProducts();
        } else {
            header('Location: index.php?page=admin&tab=import');
        }
        break;

    case 'admin-artist-save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->saveArtist();
        } else {
            header('Location: index.php?page=admin&tab=artists');
        }
        break;

    case 'admin-artist-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->deleteArtist();
        } else {
            header('Location: index.php?page=admin&tab=artists');
        }
        break;

    case 'admin-review-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->deleteReview();
        } else {
            header('Location: index.php?page=admin&tab=reviews');
        }
        break;

    case 'admin-review-moderate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->moderateReview();
        } else {
            header('Location: index.php?page=admin&tab=moderation');
        }
        break;

    case 'admin-review-bulk-moderate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->bulkModerateReviews();
        } else {
            header('Location: index.php?page=admin&tab=moderation');
        }
        break;

    case 'admin-user-role':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->updateUserRole();
        } else {
            header('Location: index.php?page=admin&tab=users');
        }
        break;

    case 'admin-user-ban':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->toggleUserBan();
        } else {
            header('Location: index.php?page=admin&tab=users');
        }
        break;

    case 'admin-user-impersonate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->impersonate();
        } else {
            header('Location: index.php?page=admin&tab=users');
        }
        break;

    case 'admin-stop-impersonate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->stopImpersonate();
        } else {
            header('Location: index.php');
        }
        break;

    case 'admin-tracks-reorder':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->reorderTracks();
        } else {
            header('Location: index.php?page=admin&tab=products');
        }
        break;

    case 'admin-orders-bulk-status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->bulkOrderStatus();
        } else {
            header('Location: index.php?page=admin&tab=orders');
        }
        break;

    case 'admin-report-export':
        (new AdminController())->exportReport();
        break;

    case 'admin-order-status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController())->updateOrderStatus();
        } else {
            header('Location: index.php?page=admin&tab=orders');
        }
        break;

    case 'review-add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ReviewController())->add();
        } else {
            header('Location: index.php');
        }
        break;

    case 'checkout-extend-reservation':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CheckoutController())->extendReservation();
        } else {
            header('Location: index.php?page=checkout');
        }
        break;

    case 'checkout-calculate-shipping':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CheckoutController())->calculateShipping();
        } else {
            header('Location: index.php?page=checkout');
        }
        break;

    default:
        (new HomeController())->index();
        break;
}
