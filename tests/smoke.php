<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Autoloader.php';

function check(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$token = Csrf::getToken();
check(strlen($token) === 64, 'CSRF token must be 32 random bytes encoded as hex');
check(Csrf::validate($token), 'Generated CSRF token must validate');
check(!Csrf::validate('invalid'), 'Invalid CSRF token must not validate');
check(class_exists('AccountToken'), 'AccountToken must autoload');
check(class_exists('LoginRateLimiter'), 'LoginRateLimiter must autoload');
check(str_contains(file_get_contents(__DIR__ . '/../.htaccess'), 'database'), 'Database directory must be denied by Apache rules');
echo "SMOKE_TESTS_OK\n";