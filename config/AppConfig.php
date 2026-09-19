<?php
declare(strict_types=1);

class AppConfig {
    public static function isProduction(): bool {
        Env::load();
        return strtolower((string)Env::get('APP_ENV', 'development')) === 'production';
    }

    public static function isDebug(): bool {
        Env::load();
        return !self::isProduction() && filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    }

    public static function appUrl(): string {
        Env::load();
        return rtrim((string)Env::get('APP_URL', ''), '/');
    }
}