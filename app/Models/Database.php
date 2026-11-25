<?php
declare(strict_types=1);
namespace App\Models;

use PDO;

final class Database {
    private static ?PDO $pdo = null;
    public static function connection(): PDO {
        if (!self::$pdo) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $name = getenv('DB_DATABASE') ?: 'cidadecolaborativa';
            self::$pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
            self::$pdo->exec("SET time_zone = '-03:00'");
        }
        return self::$pdo;
    }
    public static function query(string $sql, array $params = []): \PDOStatement {
        $stmt = self::connection()->prepare($sql); $stmt->execute($params); return $stmt;
    }
    public static function all(string $sql, array $params = []): array { return self::query($sql, $params)->fetchAll(); }
    public static function one(string $sql, array $params = []): ?array { return self::query($sql, $params)->fetch() ?: null; }
    public static function insert(string $table, array $data): int {
        // Table/column names originate only from application code, never from request input.
        $columns = implode(',', array_keys($data)); $marks = implode(',', array_fill(0, count($data), '?'));
        self::query("INSERT INTO $table ($columns) VALUES ($marks)", array_values($data));
        return (int) self::connection()->lastInsertId();
    }
    public static function transaction(callable $action): mixed {
        $pdo = self::connection(); $pdo->beginTransaction();
        try { $result = $action(); $pdo->commit(); return $result; }
        catch (\Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
    }
}
