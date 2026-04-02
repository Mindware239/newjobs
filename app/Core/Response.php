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
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://www.gstatic.com https://connect.facebook.net https://snap.licdn.com https://www.googletagmanager.com https://cdn.quilljs.com https://cdnjs.cloudflare.com https://checkout.razorpay.com https://cdn.ckeditor.com https://code.jquery.com https://sdk.cashfree.com; "
             . "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.quilljs.com https://cdnjs.cloudflare.com https://cdnjs.cloudflare.com https://cdn.ckeditor.com; "
             . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:; "
             . "img-src 'self' data: https: https://www.facebook.com https://px.ads.linkedin.com https://www.google-analytics.com https://googleads.g.doubleclick.net https://*.razorpay.com https://*.cashfree.com; "
             . "connect-src 'self' https://unpkg.com https://cdn.jsdelivr.net https://www.gstatic.com https://*.gstatic.com https://www.googleapis.com https://firebasestorage.googleapis.com https://firebaseinstallations.googleapis.com https://fcmregistrations.googleapis.com https://fcm.googleapis.com https://*.firebaseio.com wss://*.firebaseio.com https://www.facebook.com https://connect.facebook.net https://px.ads.linkedin.com https://www.google-analytics.com https://googleads.g.doubleclick.net https://nominatim.openstreetmap.org https://countriesnow.space https://*.razorpay.com https://cdn.ckeditor.com https://c.cksource.com https://api.cashfree.com https://sandbox.cashfree.com https://sdk.cashfree.com; "
             . "frame-src 'self' https://checkout.razorpay.com https://*.razorpay.com https://sdk.cashfree.com https://api.cashfree.com https://sandbox.cashfree.com; "
             . "frame-ancestors 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self' https://api.cashfree.com https://sandbox.cashfree.com";
        header('Content-Security-Policy: ' . $csp);
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
        // Guard against header injection/newlines
        $safeName = str_replace(["\r", "\n"], '', $name);
        $safeValue = str_replace(["\r", "\n"], '', $value);
        $this->headers[$safeName] = $safeValue;
        header($safeName . ': ' . $safeValue);
    }

    public function json(array $data, int $code = 200, string $message = "Success", bool $status = true, ?array $errors = null): void
    {
        $this->ensureSecurityHeaders();
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        
        // Standardized format
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ];

        // Clear any previous output
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function error(string $message, int $code = 400, ?array $errors = null): void
    {
        $this->json([], $code, $message, false, $errors);
    }

    public function view(string $view, array $data = [], int $code = 200, ?string $layout = null): void
    {
        $this->ensureSecurityHeaders();
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $viewTemplate = $view;
        extract($data);
        $viewPath = __DIR__ . '/../../resources/views/' . $viewTemplate . '.php';

        if (!file_exists($viewPath)) {
            if ($layout) {
                $this->view('admin/error', [
                    'title' => 'Error',
                    'errorMessage' => "View not found: {$viewTemplate}"
                ], 500, $layout);
            }
            $this->setStatusCode(500);
            echo "View not found: $viewTemplate";
            exit;
        }

        // If layout is specified, wrap the view
        if ($layout) {
            $layoutPath = __DIR__ . '/../../resources/views/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                ob_start();
                require $viewPath;
                $content = ob_get_clean();
                // Make $content available to layout
                require $layoutPath;
            } else {
                require $viewPath;
            }
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

