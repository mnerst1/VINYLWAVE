<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $baseDir = __DIR__ . '/../';
    
    $map = [
        'Database' => 'config/Database.php',
        'UploadService' => 'config/UploadService.php',
        'ImageOptimizer' => 'config/ImageOptimizer.php',
        'VideoThumbnail' => 'config/VideoThumbnail.php',
        'Env' => 'config/Env.php',
        'Csrf' => 'config/Csrf.php',
        'AppConfig' => 'config/AppConfig.php',
        'ErrorHandler' => 'config/ErrorHandler.php',
        'EmailService' => 'config/EmailService.php',
        'User' => 'models/User.php',
        'Artist' => 'models/Artist.php',
        'Product' => 'models/Product.php',
        'Review' => 'models/Review.php',
        'Wishlist' => 'models/Wishlist.php',
        'AccountToken' => 'models/AccountToken.php',
        'LoginRateLimiter' => 'models/LoginRateLimiter.php',
        'SalesReport' => 'models/SalesReport.php',
        'ProductCsv' => 'models/ProductCsv.php',
        'CartTrait' => 'models/CartTrait.php',
        'InventoryReservation' => 'models/InventoryReservation.php',
        'ShippingCalculator' => 'models/ShippingCalculator.php',
        'HomeController' => 'controllers/HomeController.php',
        'ProductController' => 'controllers/ProductController.php',
        'CartController' => 'controllers/CartController.php',
        'CheckoutController' => 'controllers/CheckoutController.php',
        'AuthController' => 'controllers/AuthController.php',
        'AdminController' => 'controllers/AdminController.php',
        'ReviewController' => 'controllers/ReviewController.php',
        'WishlistController' => 'controllers/WishlistController.php',
    ];
    
    if (isset($map[$class])) {
        require_once $baseDir . $map[$class];
    }
});