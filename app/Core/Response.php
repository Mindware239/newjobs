<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
        http_response_code($code);
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
        header("$name: $value");
    }

    public function json(array $data, int $code = 200): void
    {
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        
        // Clear any previous output
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function view(string $view, array $data = [], int $code = 200, string $layout = null): void
    {
        $this->setStatusCode($code);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        extract($data);
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            $this->setStatusCode(500);
            echo "View not found: $view";
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
        $this->setStatusCode($code);
        $this->setHeader('Location', $url);
        exit;
    }

    public function download(string $filePath, string $filename = null): void
    {
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
        echo $content;
        exit;
    }
}

