<?php

declare(strict_types=1);

namespace App\Helpers;

class PaymentLogger
{
    private static string $logDir = __DIR__ . '/../../storage/logs';

    public static function logWebhook(string $gateway, string $message, array $context = []): void
    {
        self::log('webhook', "[{$gateway}] {$message}", $context);
    }

    public static function logPayment(string $gateway, string $message, array $context = []): void
    {
        self::log('payment', "[{$gateway}] {$message}", $context);
    }

    public static function logError(string $gateway, string $message, array $context = []): void
    {
        self::log('payment', "ERROR [{$gateway}] {$message}", $context, 'ERROR');
    }

    private static function log(string $file, string $message, array $context = [], string $level = 'INFO'): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }

        $logFile = self::$logDir . '/' . $file . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextString" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
