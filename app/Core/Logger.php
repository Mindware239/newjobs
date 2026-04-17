<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static ?MonologLogger $instance = null;

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = new MonologLogger('api');
            
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            $handler = new RotatingFileHandler($logDir . '/api.log', 30, MonologLogger::DEBUG);
            $formatter = new LineFormatter(null, null, true, true);
            $handler->setFormatter($formatter);
            
            self::$instance->pushHandler($handler);
        }
        return self::$instance;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::getInstance()->critical($message, $context);
    }
}
