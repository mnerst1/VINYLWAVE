<?php
declare(strict_types=1);

class Env {
    private static bool $loaded = false;

    public static function load(string $path = null): void {
        if (!self::$loaded) {
            $path = $path ?? __DIR__ . '/../.env';
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) continue;
                    if (str_contains($line, '=')) {
                        [$key, $value] = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value);
                        if (!array_key_exists($key, $_ENV)) {
                            $_ENV[$key] = $value;
                            putenv("$key=$value");
                        }
                    }
                }
            }
            self::$loaded = true;
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::load();
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}