<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private bool $securityHeadersSent = false;
    
    private function ensureSecurityHeaders(): void
    {
        if ($this->securityHeadersSent) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');

        // ✅ FIXED CSP (Razorpay + Cashfree + existing services SAFE)
        $csp = "default-src 'self'; "

            // 🔥 SCRIPT (FIXED HERE)
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' 
            https://checkout.razorpay.com 
            https://cdn.razorpay.com 
            https://*.razorpay.com 
            https://unpkg.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com 
            https://www.gstatic.com https://connect.facebook.net https://snap.licdn.com 
            https://www.googletagmanager.com https://cdn.quilljs.com https://cdnjs.cloudflare.com 
            https://cdn.ckeditor.com https://code.jquery.com https://sdk.cashfree.com; "

            // STYLE
            . "style-src 'self' 'unsafe-inline' 
            https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 
            https://cdn.jsdelivr.net https://cdn.quilljs.com https://cdn.ckeditor.com; "

            // FONT
            . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com 
            https://cdn.jsdelivr.net data:; "

            // IMAGE
            . "img-src 'self' data: https: 
            https://*.razorpay.com https://*.cashfree.com 
            https://www.facebook.com https://px.ads.linkedin.com 
            https://www.google-analytics.com https://googleads.g.doubleclick.net; "

            // 🔥 CONNECT (IMPORTANT FIX)
            . "connect-src 'self' 
            https://*.razorpay.com 
            https://api.razorpay.com 
            https://lumberjack.razorpay.com 
            https://api.cashfree.com https://sandbox.cashfree.com https://sdk.cashfree.com
            https://unpkg.com https://cdn.jsdelivr.net https://www.gstatic.com 
            https://*.gstatic.com https://www.googleapis.com 
            https://firebasestorage.googleapis.com https://firebaseinstallations.googleapis.com 
            https://fcmregistrations.googleapis.com https://fcm.googleapis.com 
            https://*.firebaseio.com wss://*.firebaseio.com 
            https://www.facebook.com https://connect.facebook.net 
            https://px.ads.linkedin.com https://www.google-analytics.com 
            https://googleads.g.doubleclick.net 
            https://nominatim.openstreetmap.org https://countriesnow.space https://restcountries.com; "

            // FRAME (PAYMENT POPUP)
            . "frame-src 'self' 
            https://checkout.razorpay.com 
            https://*.razorpay.com 
            https://api.cashfree.com https://sandbox.cashfree.com https://sdk.cashfree.com; "

            . "frame-ancestors 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self' https://api.cashfree.com https://sandbox.cashfree.com;";

        header('Content-Security-Policy: ' . preg_replace('/\s+/', ' ', $csp));

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        $this->securityHeadersSent = true;
    }

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
        http_response_code($code);
    }

    public function setHeader(string $name, string $value): void
    {
        $safeName = str_replace(["\r", "\n"], '', $name);
        $safeValue = str_replace(["\r", "\n"], '', $value);
        $this->headers[$safeName] = $safeValue;
        header($safeName . ': ' . $safeValue);
    }

    public function json(array $data, int $code = 200): void
    {
        $this->ensureSecurityHeaders();
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');

        if (ob_get_level() > 0) {
            ob_clean();
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    public function error(string $message, int $code = 400, ?array $errors = null): void
    {
        $payload = [
            'status' => false,
            'success' => false,
            'message' => $message,
            'data' => null,
            'error' => $message,
            'errors' => $errors
        ];
        $this->json($payload, $code);
    }

    public function view(string $view, array $data = [], int $code = 200, ?string $layout = null): void
    {
        $this->ensureSecurityHeaders();
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');

        extract($data);
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            $this->setStatusCode(500);
            echo "View not found: $view";
            exit;
        }

        if ($layout) {
            $layoutPath = __DIR__ . '/../../resources/views/' . $layout . '.php';
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require $layoutPath;
        } else {
            require $viewPath;
        }

        exit;
    }

    public function redirect(string $url, int $code = 302): void
    {
        $this->ensureSecurityHeaders();
        $this->setStatusCode($code);
        $this->setHeader('Location', $url);
        exit;
    }

    public function download(string $filePath, ?string $filename = null): void
    {
        $this->ensureSecurityHeaders();

        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            echo "File not found";
            exit;
        }

        $filename = $filename ?? basename($filePath);

        $this->setHeader('Content-Type', 'application/octet-stream');
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string)filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function setBody(string $content): void
    {
        $this->ensureSecurityHeaders();
        echo $content;
        exit;
    }
}
