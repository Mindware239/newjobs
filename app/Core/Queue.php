<?php

declare(strict_types=1);

namespace App\Core;

class Queue
{
    private static $instances = [];
    private $name;

    private function __construct($name)
    {
        $this->name = $name;
    }

    public static function getInstance($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }
        return self::$instances[$name];
    }

    public function isAvailable(): bool
    {
        return false;
    }

    public function push($job, $data)
    {
        // This is a mock implementation. In a real application, this would
        // push the job to a queueing service like Redis or RabbitMQ.
        error_log("Queue '{$this->name}' is not available. Job '{$job}' was not queued.");
    }
}
