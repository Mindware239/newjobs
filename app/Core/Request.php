<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $params = [];
    private array $body = [];
    private array $query = [];
    private array $files = [];
    private array $headers = [];
    private array $attributes = [];
    private ?\App\Models\User $user = null;
    private string $rawBody = '';

    public function __construct()
    {
        $this->body = $_POST;
        $this->query = $_GET;
        $this->files = $_FILES;
        $this->headers = $this->getAllHeaders();
        
        $this->rawBody = (string)file_get_contents('php://input');

        // Handle JSON body for any request that has a body (POST, PUT, PATCH, DELETE)
        $contentType = $this->header('Content-Type') ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $decoded = json_decode($this->rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->body = array_merge($this->body, $decoded ?? []);
            }
        }
    }

    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function getJsonBody(): array
    {
        return $this->body;
    }

    public function getBody(): string
    {
        return $this->rawBody;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $key, $default = null): ?string
    {
        $key = strtolower($key);
        foreach ($this->headers as $headerKey => $headerValue) {
            if (strtolower($headerKey) === $key) {
                return $headerValue;
            }
        }
        return $default;
    }

    public function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || strpos($this->header('Accept') ?? '', 'application/json') !== false
            || strpos($this->header('Content-Type') ?? '', 'application/json') !== false;
    }

    public function setUser(\App\Models\User $user): void
    {
        $this->user = $user;
    }

    public function user(): ?\App\Models\User
    {
        return $this->user;
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH);
        
        // Handle subdirectory deployment
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        
        // Normalize slashes
        $scriptDir = str_replace('\\', '/', $scriptDir);
        
        // Remove trailing slash from script dir if not root
        if ($scriptDir !== '/' && substr($scriptDir, -1) === '/') {
            $scriptDir = substr($scriptDir, 0, -1);
        }

        // If script dir is in the path, remove it
        if ($scriptDir !== '/' && $scriptDir !== '.' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        } elseif (substr($scriptDir, -7) === '/public') {
            // Handle case where public folder is hidden by rewrite rules
            $baseDir = substr($scriptDir, 0, -7);
            if ($baseDir !== '/' && $baseDir !== '.' && strpos($path, $baseDir) === 0) {
                $path = substr($path, strlen($baseDir));
            }
        }

        return $path ?: '/';
    }

    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function query(?string $key = null, $default = null)
    {
        return $this->get($key, $default);
    }

    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        if ($this->getMethod() === 'GET') {
            return $this->get($key, $default);
        }
        return $this->post($key, $default);
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
}
