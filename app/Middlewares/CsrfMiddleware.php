<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    private array $excludedMethods = ['GET', 'HEAD', 'OPTIONS'];
    private array $whitelist = [
        '/webhook/razorpay',
        '/gateway/cashfree/webhook',
        '/candidate/premium/cashfree/webhook',
        '/payments/razorpay/webhook',
        '/payments/cashfree/webhook',
        '/api/v1/payments/razorpay/webhook',
        '/api/v1/payments/cashfree/webhook'
    ];

    public function handle(Request $request, Response $response, ?callable $next = null): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        
        if (in_array($method, $this->excludedMethods)) {
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        // Skip CSRF for whitelisted routes
        foreach ($this->whitelist as $pattern) {
            if ($path === $pattern || strpos($path, $pattern) === 0) {
                if (is_callable($next)) {
                    $next($request, $response);
                }
                return;
            }
        }

        // Skip CSRF for API requests
        if (strpos($path, '/api/') === 0) {
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        // Get token from header or POST data
        $token = $request->header('X-CSRF-Token') ?? $request->post('_token') ?? ($_COOKIE['XSRF-TOKEN'] ?? null);
        
        // Ensure session is started before checking sessionToken
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $issuedAt = (int)($_SESSION['csrf_token_time'] ?? 0);

        // Debug logging with token fragments
        $tokenFrag = $token ? substr((string)$token, 0, 10) . '...' : 'missing';
        $sessionFrag = $sessionToken ? substr((string)$sessionToken, 0, 10) . '...' : 'missing';
        if (!hash_equals((string)$sessionToken, (string)$token)) {
            error_log("CSRF Mismatch - Path: " . $request->getPath() . " | Got: " . (string)$token . " | Expected: " . (string)$sessionToken);
        }
        // error_log("CSRF Check - Path: " . $request->getPath() . " | Got: $tokenFrag | Expected: $sessionFrag");

        if (!$token || !$sessionToken) {
            error_log("CSRF token missing for path: " . $request->getPath());
            $new = self::generateToken(true);
            self::setCsrfCookie($new);
            $this->terminate(403, 'Your session was refreshed. Please try again.', $new, $request, $response);
            return;
        }

        if (!hash_equals((string)$sessionToken, (string)$token)) {
            error_log("CSRF mismatch for path: " . $request->getPath());
            $new = self::generateToken(true);
            self::setCsrfCookie($new);
            $this->terminate(403, 'We refreshed your session. Please try again.', $new, $request, $response);
            return;
        }

        $maxAge = 86400;
        if ($issuedAt > 0 && (time() - $issuedAt) > $maxAge) {
            error_log("CSRF expired for path: " . $request->getPath());
            $new = self::generateToken(true);
            self::setCsrfCookie($new);
            $this->terminate(403, 'Your session expired and was refreshed. Please try again.', $new, $request, $response);
            return;
        }

        if (is_callable($next)) {
            $next($request, $response);
        }
    }

    private function terminate(int $code, string $message, string $newToken, Request $request, Response $response): void
    {
        // Ensure session is written before exit
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if ($request->isAjax()) {
            $response->json(['error' => $message, 'refresh_csrf' => true, 'csrf_token' => $newToken], $code);
        } else {
            $response->setStatusCode($code);
            echo "Security verification failed. Please go back, refresh the page and try again.";
            exit;
        }
    }

    private static function getCookieDomain(): string
    {
        $hostHeader = (string)($_SERVER['HTTP_HOST'] ?? '');
        if (!$hostHeader) {
            return '';
        }

        $hostNoPort = preg_replace('/:\d+$/', '', $hostHeader);
        if (preg_match('/^(localhost|127\.0\.0\.1)$/i', $hostNoPort) || filter_var($hostNoPort, FILTER_VALIDATE_IP)) {
            return '';
        }

        $parts = explode('.', $hostNoPort);
        if (count($parts) >= 2) {
            return '.' . implode('.', array_slice($parts, -2));
        }

        return '';
    }

    private static function setCsrfCookie(string $token): void
    {
        if (!headers_sent()) {
            $current = $_COOKIE['XSRF-TOKEN'] ?? '';
            if ($current === $token) {
                return;
            }

            $ttl = 86400; // 24 hours
            $domain = self::getCookieDomain();
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
            setcookie('XSRF-TOKEN', $token, time() + $ttl, '/', $domain, $secure, false);
        }
    }

    public static function generateToken(bool $forceNew = false): string
    {
        $now = time();
        $issued = (int)($_SESSION['csrf_token_time'] ?? 0);
        $maxAge = 86400; // 24 hours

        if ($forceNew || empty($_SESSION['csrf_token']) || ($issued > 0 && ($now - $issued) > $maxAge)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = $now;
        }
        return $_SESSION['csrf_token'];
    }
}

