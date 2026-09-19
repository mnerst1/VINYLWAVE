<?php
declare(strict_types=1);

class Csrf {
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generate(): string {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function getToken(): string {
        return self::generate();
    }

    public static function validate(string $token): bool {
        if (empty($token) || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    public static function verifyOrFail(string $token): void {
        if (!self::validate($token)) {
            http_response_code(403);
            exit('CSRF token validation failed');
        }
    }

    public static function input(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken()) . '">';
    }
}