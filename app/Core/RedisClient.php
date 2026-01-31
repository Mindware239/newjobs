<?php

declare(strict_types=1);

namespace App\Core;

use Redis;
use RedisException;

class RedisClient
{
    private static ?RedisClient $instance = null;
    private ?Redis $connection = null;

    private function __construct()
    {
        if (!extension_loaded('redis')) {
            // Redis not available - use in-memory fallback
            $this->connection = null;
            return;
        }
        
        $this->connection = new Redis();
        
        $host = $_ENV['REDIS_HOST'] ?? 'localhost';
        $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
        $password = $_ENV['REDIS_PASSWORD'] ?? null;
        $database = (int)($_ENV['REDIS_DB'] ?? 0);

        try {
            $this->connection->connect($host, $port);
            if ($password) {
                $this->connection->auth($password);
            }
            $this->connection->select($database);
        } catch (RedisException $e) {
            // Redis connection failed - use in-memory fallback
            $this->connection = null;
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?Redis
    {
        return $this->connection;
    }

    public function isAvailable(): bool
    {
        return $this->connection !== null;
    }

    private static array $memoryCache = [];
    private static array $memoryCacheExpiry = [];

    public function get(string $key)
    {
        if (!$this->isAvailable()) {
            // In-memory fallback
            if (isset(self::$memoryCache[$key])) {
                if (!isset(self::$memoryCacheExpiry[$key]) || self::$memoryCacheExpiry[$key] > time()) {
                    $value = self::$memoryCache[$key];
                    // If stored as JSON string, decode it
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        return $decoded !== null ? $decoded : $value;
                    }
                    return $value;
                }
                unset(self::$memoryCache[$key], self::$memoryCacheExpiry[$key]);
            }
            return null;
        }
        
        $value = $this->connection->get($key);
        if ($value === false) {
            return null;
        }
        
        // Try to decode JSON, return as-is if not JSON
        $decoded = json_decode($value, true);
        return $decoded !== null ? $decoded : $value;
    }

    public function set(string $key, $value, int $ttl = 0): bool
    {
        if (!$this->isAvailable()) {
            // In-memory fallback
            self::$memoryCache[$key] = $value;
            if ($ttl > 0) {
                self::$memoryCacheExpiry[$key] = time() + $ttl;
            }
            return true;
        }
        
        $serialized = json_encode($value);
        return $ttl > 0 
            ? $this->connection->setex($key, $ttl, $serialized)
            : $this->connection->set($key, $serialized);
    }

    public function delete(string $key): bool
    {
        if (!$this->isAvailable()) {
            unset(self::$memoryCache[$key], self::$memoryCacheExpiry[$key]);
            return true;
        }
        
        return (bool)$this->connection->del($key);
    }

    public function exists(string $key): bool
    {
        if (!$this->isAvailable()) {
            return isset(self::$memoryCache[$key]);
        }
        
        return (bool)$this->connection->exists($key);
    }

    public function increment(string $key, int $by = 1): int
    {
        if (!$this->isAvailable()) {
            if (!isset(self::$memoryCache[$key])) {
                self::$memoryCache[$key] = 0;
            }
            self::$memoryCache[$key] += $by;
            return self::$memoryCache[$key];
        }
        
        return $this->connection->incrBy($key, $by);
    }

    public function expire(string $key, int $ttl): bool
    {
        if (!$this->isAvailable()) {
            self::$memoryCacheExpiry[$key] = time() + $ttl;
            return true;
        }
        
        return $this->connection->expire($key, $ttl);
    }
}

