<?php
declare(strict_types=1);

require_once __DIR__ . '/Env.php';

class Database {
    private static ?PDO $pdo = null;

    public static function connection(): PDO {
        if (self::$pdo === null) {
            Env::load();
            $dsn = "mysql:host=" . Env::get('DB_HOST', '127.0.0.1') 
                 . ";port=" . Env::get('DB_PORT', '3306')
                 . ";dbname=" . Env::get('DB_NAME', 'vinylwave')
                 . ";charset=" . Env::get('DB_CHARSET', 'utf8mb4');
            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }
        return self::$pdo;
    }

    public static function reconnect(): void {
        self::$pdo = null;
        self::connection();
    }
}
