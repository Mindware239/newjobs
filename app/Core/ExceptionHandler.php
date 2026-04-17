<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(Throwable $e): void
    {
        $code = $e->getCode();
        $statusCode = ($code >= 400 && $code < 600) ? (int)$code : 500;
        
        $message = $e->getMessage();
        
        // Don't show detailed DB errors in production
        if ($statusCode === 500 && ($_ENV['APP_ENV'] ?? 'production') === 'production') {
            $message = "An internal server error occurred.";
        }

        Logger::error("Exception: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $response = new Response();
        $response->json([], $statusCode, $message, false, [
            'type' => get_class($e),
            'debug' => ($_ENV['APP_ENV'] ?? 'production') === 'development' ? [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ]);
    }

    public static function handleError(int $level, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $level)) {
            return;
        }
        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }
}
