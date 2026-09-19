<?php
declare(strict_types=1);

class ErrorHandler {
    public static function register(): void {
        error_reporting(E_ALL);
        ini_set('display_errors', AppConfig::isDebug() ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');

        set_exception_handler(static function (Throwable $error): void {
            error_log(sprintf('[uncaught] %s in %s:%d', $error->getMessage(), $error->getFile(), $error->getLine()));
            http_response_code(500);
            echo AppConfig::isDebug()
                ? 'Application error: ' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')
                : 'An unexpected error occurred. Please try again later.';
        });
    }
}