<?php
declare(strict_types=1);

/**
 * One PDO connection, shared by the whole request.
 *
 * Why a class and not a global $conn?
 *  - the connection is created lazily (only when a page actually needs the DB)
 *  - the PDO settings live in exactly one place
 * Sinhala: හැම page එකකම අලුත් connection එකක් හදනවා වෙනුවට එකම connection එක share කරනවා.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require dirname(__DIR__) . '/config.php';

        if ($config['driver'] === 'mysql') {
            $my  = $config['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $my['host'],
                $my['port'],
                $my['database']
            );
            self::$pdo = new PDO($dsn, $my['username'], $my['password'], self::options());
        } else {
            $path = $config['sqlite_path'];
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            self::$pdo = new PDO('sqlite:' . $path, null, null, self::options());
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }

        return self::$pdo;
    }

    public static function driverName(): string
    {
        return self::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    private static function options(): array
    {
        return [
            // Throw exceptions instead of silently returning false.
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Rows come back as associative arrays: $row['title']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use real prepared statements, not PDO's string-building emulation.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
}
