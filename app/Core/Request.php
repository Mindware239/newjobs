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

    public function __construct()
    {
        $this->body = $_POST;
        $this->query = $_GET;
        $this->files = $_FILES;
        $this->headers = getallheaders() ?: [];
        
        // Handle JSON body for POST requests if Content-Type is application/json
        if ($this->getMethod() === 'POST' && empty($this->body)) {
            $contentType = $this->header('Content-Type') ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $jsonBody = file_get_contents('php://input');
                $this->body = json_decode($jsonBody, true) ?? [];
            }
        }
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
            // e.g. scriptDir is /sub/public but path is /sub/login
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

    public function header(string $key, $default = null): ?string
    {
        $key = strtolower($key);
        foreach ($this->headers as $headerKey => $value) {
            if (strtolower($headerKey) === $key) {
                return $value;
            }
        }
        return $default;
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

    public function getJsonBody(): array
    {
        if (!empty($this->body) && $this->getMethod() === 'POST') {
            return $this->body;
        }
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
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

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest' ||
               strtolower($this->header('Content-Type', '')) === 'application/json';
    }
}

