<?php
// ============================================================
//  NexVest — Database Connection (PDO Singleton)
//  config/database.php
// ============================================================

declare(strict_types=1);

class DB {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo !== null) return self::$pdo;

        $cfg = CONFIG['db'];
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset={$cfg['charset']}";

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            // If .env is not configured, redirect to installer
            $envFile = (defined('ROOT') ? ROOT : __DIR__ . '/..') . '/.env';
            if (!file_exists($envFile) || !trim((string)env('DB_NAME', ''))) {
                header('Location: /install.php');
                exit;
            }
            http_response_code(503);
            $isJson = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
                      str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isJson) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'error' => 'Database connection failed. Please contact support.']));
            }
            die('<!DOCTYPE html><html><head><title>Service Unavailable</title><style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#F0F2F5}.box{background:#fff;border:1px solid #ddd;border-radius:8px;padding:2rem;max-width:420px;text-align:center}.box h2{color:#C0392B;margin-bottom:.5rem}a{color:#1A6DB5}</style></head><body><div class="box"><h2>Service Unavailable</h2><p>The database is not responding. Please check your configuration.</p><br><a href="/install.php">Run Setup Wizard</a></div></body></html>');
        }

        return self::$pdo;
    }

    // ── Shorthand helpers ─────────────────────────────────────

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string {
        self::query($sql, $params);
        return self::connect()->lastInsertId();
    }

    public static function execute(string $sql, array $params = []): int {
        return self::query($sql, $params)->rowCount();
    }

    public static function beginTransaction(): void { self::connect()->beginTransaction(); }
    public static function commit(): void           { self::connect()->commit(); }
    public static function rollback(): void         { self::connect()->rollBack(); }
}
