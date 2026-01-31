<?php

declare(strict_types=1);

namespace App\Workers;

use App\Core\RedisClient;

abstract class BaseWorker
{
    protected RedisClient $redis;
    protected string $queueName;

    public function __construct(string $queueName)
    {
        $this->redis = RedisClient::getInstance();
        $this->queueName = $queueName;
    }

    public static function enqueue(array $data): void
    {
        $worker = new static(static::getQueueName());
        $worker->push($data);
    }

    protected function push(array $data): void
    {
        if (!$this->redis->isAvailable()) {
            // Queue not available - log or skip
            error_log("Queue not available: {$this->queueName}");
            return;
        }
        $connection = $this->redis->getConnection();
        if ($connection) {
            $connection->lpush($this->queueName, json_encode($data));
        }
    }

    protected function pop(): ?array
    {
        if (!$this->redis->isAvailable()) {
            return null;
        }
        $connection = $this->redis->getConnection();
        if (!$connection) {
            return null;
        }
        $data = $connection->brpop($this->queueName, 5);
        if ($data && isset($data[1])) {
            return json_decode($data[1], true);
        }
        return null;
    }

    abstract public function process(array $data): bool;
    abstract protected static function getQueueName(): string;

    public function run(): void
    {
        echo "Worker started: {$this->queueName}\n";
        
        while (true) {
            $data = $this->pop();
            if ($data) {
                try {
                    $this->process($data);
                } catch (\Exception $e) {
                    error_log("Worker error: " . $e->getMessage());
                }
            }
        }
    }
}

