<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $capturedLastInsertId = '0';

    private function __construct()
    {
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname   = $_ENV['DB_NAME'] ?? 'mindwareinfotech';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $port     = $_ENV['DB_PORT'] ?? '3306';
        $charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset};port={$port}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                $options
            );
        } catch (PDOException $e) {
            $this->connection = null;

            error_log(
                'Database connection failed: ' . $e->getMessage()
            );
        }
    }

    // =========================
    // SINGLETON INSTANCE
    // =========================
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // =========================
    // CONNECTION STATUS
    // =========================
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }

    // =========================
    // MAIN QUERY METHOD (READ)
    // =========================
    public function query(
        string $sql,
        array $params = []
    ): ?PDOStatement {
        if (!$this->isConnected()) {
            return null;
        }

        $start = microtime(true);

        try {
            $stmt = $this->connection->prepare($sql);

            $stmt->execute(
                $this->normalizeParams($sql, $params)
            );

            $this->logSlowQuery($sql, $start);

            return $stmt;
        } catch (PDOException $e) {

            error_log(
                'Query failed: ' .
                $e->getMessage() .
                ' | SQL: ' . $sql
            );

            return null;
        }
    }

    // =========================
    // WRITE METHOD
    // =========================
    public function execute(
        string $sql,
        array $params = []
    ): bool {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $stmt = $this->connection->prepare($sql);

            $stmt->execute(
                $this->normalizeParams($sql, $params)
            );

            // Capture last insert ID
            try {
                $this->capturedLastInsertId =
                    $this->connection->lastInsertId();
            } catch (\Throwable $e) {
            }

            return true;

        } catch (PDOException $e) {

            error_log(
                'Execute failed: ' .
                $e->getMessage() .
                ' | SQL: ' . $sql
            );

            return false;
        }
    }

    // =========================
    // FETCH METHODS
    // =========================
    public function fetch(
        string $sql,
        array $params = []
    ): ?array {
        $stmt = $this->query($sql, $params);

        if (!$stmt) {
            return null;
        }

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function fetchOne(
        string $sql,
        array $params = []
    ): ?array {
        return $this->fetch($sql, $params);
    }

    public function fetchAll(
        string $sql,
        array $params = []
    ): array {
        $stmt = $this->query($sql, $params);

        return $stmt
            ? $stmt->fetchAll()
            : [];
    }

    // =========================
    // PARAM NORMALIZER
    // =========================
    private function normalizeParams(
        string $sql,
        array $params
    ): array {

        if (empty($params)) {
            return [];
        }

        // Positional params
        if (strpos($sql, '?') !== false) {
            return array_values($params);
        }

        // Named params
        $normalized = [];

        foreach ($params as $key => $value) {

            $normalized[
                is_int($key)
                    ? $key
                    : ltrim((string) $key, ':')
            ] = $value;
        }

        return $normalized;
    }

    // =========================
    // SLOW QUERY LOGGER
    // =========================
    private function logSlowQuery(
        string $sql,
        float $start
    ): void {

        if (!$this->isConnected()) {
            return;
        }

        $duration = (int) round(
            (microtime(true) - $start) * 1000
        );

        // Ignore fast queries
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

            foreach ($patterns as $pattern) {

                if (preg_match($pattern, $sql, $matches)) {
                    $table = $matches[1];
                    break;
                }
            }

            $stmt = $this->connection->prepare(
                "INSERT INTO system_logs
                (
                    type,
                    module,
                    table_name,
                    message,
                    user_id,
                    duration_ms,
                    created_at
                )
                VALUES
                (
                    'slow_query',
                    'db',
                    :table_name,
                    :message,
                    :user_id,
                    :duration,
                    NOW()
                )"
            );

            $stmt->execute([
                'table_name' => $table,
                'message'    => substr($sql, 0, 255),
                'user_id'    => (int) ($_SESSION['user_id'] ?? 0),
                'duration'   => $duration
            ]);

        } catch (\Throwable $e) {
            // silently ignore logging failures
        }
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
        return $this->connection?->beginTransaction() ?? false;
    }

    public function commit(): bool
    {
        return $this->connection?->commit() ?? false;
    }

    public function rollback(): bool
    {
        return $this->connection?->rollBack() ?? false;
    }

    public function inTransaction(): bool
    {
        return $this->connection?->inTransaction() ?? false;
    }
}