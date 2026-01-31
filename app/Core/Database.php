<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $capturedLastInsertId = '0';

    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'mindwareinfotech';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset;port=$port";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // IMPORTANT: Use real prepared statements
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            $errorMsg = "Database connection failed: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'could not find driver')) {
                $errorMsg .= "\n\nEnable PDO MySQL driver in php.ini (extension=pdo_mysql)";
            } elseif (str_contains($e->getMessage(), '2002')) {
                $errorMsg .= "\n\nMake sure MySQL service is running.";
            } elseif (str_contains($e->getMessage(), '1049')) {
                $errorMsg .= "\n\nDatabase '$dbname' does not exist.";
            }

            throw new \RuntimeException($errorMsg);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // =========================
    // MAIN QUERY METHOD (READ)
    // =========================
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $start = microtime(true);

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->normalizeParams($sql, $params));

        $this->logSlowQuery($sql, $start);

        return $stmt;
    }

    // =========================
    // WRITE METHOD (INSERT/UPDATE/DELETE)
    // =========================
    public function execute(string $sql, array $params = []): void
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->normalizeParams($sql, $params));

        // Capture last insert ID ONLY for write operations
        try {
            $this->capturedLastInsertId = $this->connection->lastInsertId();
        } catch (\Throwable $e) {}
    }

    // =========================
    // HELPERS
    // =========================
    private function normalizeParams(string $sql, array $params): array
    {
        if (empty($params)) {
            return [];
        }

        // Positional parameters
        if (strpos($sql, '?') !== false) {
            return array_values($params);
        }

        // Named parameters
        $normalized = [];

        foreach ($params as $key => $value) {
            $normalized[is_int($key) ? $key : ltrim((string)$key, ':')] = $value;
        }

        return $normalized;
    }

    private function logSlowQuery(string $sql, float $start): void
    {
        $duration = (int) round((microtime(true) - $start) * 1000);

        // Log only slow queries (>= 1 second)
        if ($duration < 1000) {
            return;
        }

        try {
            $table = null;

            $patterns = [
                '/\bFROM\s+`?([a-zA-Z0-9_]+)`?/i',
                '/\bUPDATE\s+`?([a-zA-Z0-9_]+)`?/i',
                '/\bINSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i',
                '/\bDELETE\s+FROM\s+`?([a-zA-Z0-9_]+)`?/i'
            ];

            foreach ($patterns as $p) {
                if (preg_match($p, $sql, $m)) {
                    $table = $m[1];
                    break;
                }
            }

            $stmt = $this->connection->prepare(
                "INSERT INTO system_logs 
                (type, module, table_name, message, user_id, duration_ms, created_at) 
                VALUES ('slow_query', 'db', :t, :msg, :uid, :dur, NOW())"
            );

            $stmt->execute([
                't'   => $table,
                'msg' => substr($sql, 0, 255),
                'uid' => (int) ($_SESSION['user_id'] ?? 0),
                'dur' => $duration
            ]);
        } catch (\Throwable $e) {
            // silently ignore logging issues
        }
    }

    // =========================
    // FETCH HELPERS
    // =========================
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    // =========================
    // LAST INSERT ID
    // =========================
    public function lastInsertId(): string
    {
        return $this->capturedLastInsertId;
    }

    // =========================
    // TRANSACTIONS
    // =========================
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}
