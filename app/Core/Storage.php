<?php

declare(strict_types=1);

namespace App\Core;

class Storage
{
    private string $driver;
    private array $config;

    public function __construct()
    {
        $this->driver = $_ENV['STORAGE_DRIVER'] ?? 'local';
        $this->config = [
            'local' => [
                // Default to project root; callers pass 'uploads/...'
                'path' => $_ENV['STORAGE_PATH'] ?? dirname(__DIR__, 2),
            ],
            's3' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
                'region' => $_ENV['AWS_REGION'] ?? 'us-east-1',
                'bucket' => $_ENV['AWS_BUCKET'] ?? '',
            ]
        ];
    }

    // Backward-compatibility for code that expects Laravel-like API
    public static function disk(string $name): self
    {
        $s = new self();
        // Map custom logical disks to our local driver unless S3 is used
        if ($name !== 's3') {
            $s->driver = 'local';
        } else {
            $s->driver = 's3';
        }
        return $s;
    }

    public function path(string $relative): string
    {
        if ($this->driver === 'local') {
            $base = rtrim($this->config['local']['path'], '/\\');
            $rel = ltrim($relative, '/\\');
            return $base . '/' . $rel;
        }
        throw new \RuntimeException('path() is not supported for driver: ' . $this->driver);
    }

    public function store(array $file, string $path = ''): string
    {
        if ($this->driver === 's3') {
            return $this->storeS3($file, $path);
        }
        return $this->storeLocal($file, $path);
    }

    private function storeLocal(array $file, string $path): string
    {
        $uploadDir = rtrim($this->config['local']['path'], '/\\') . '/' . ltrim($path, '/\\');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . '/' . $filename;

        $moved = false;
        $tmp = $file['tmp_name'] ?? '';
        if ($tmp && is_uploaded_file($tmp)) {
            $moved = @move_uploaded_file($tmp, $filepath);
        }
        if (!$moved && $tmp && file_exists($tmp)) {
            // Fallbacks for dev environments (some servers mark tmp as regular file)
            $moved = @rename($tmp, $filepath) || @copy($tmp, $filepath);
        }
        if (file_exists($filepath)) {
            $relative = trim($path, "/\\") . '/' . $filename;
            return $relative;
        }
        error_log('Storage.storeLocal failed: tmp=' . ($file['tmp_name'] ?? 'none') . ' dest=' . $filepath);

        throw new \RuntimeException('File upload failed');
    }

    private function storeS3(array $file, string $path): string
    {
        // S3 implementation stub
        // Would use AWS SDK here
        throw new \RuntimeException('S3 storage not implemented yet');
    }

    public function url(string $path): string
    {
        if ($this->driver === 's3') {
            return $this->config['s3']['bucket'] . '/' . $path;
        }
        $rel = ltrim($path, '/');
        $publicBase = $_ENV['STORAGE_PUBLIC_URL'] ?? '/';
        if ($publicBase === '/' || $publicBase === '') {
            return '/' . $rel;
        }
        return rtrim($publicBase, '/') . '/' . $rel;
    }

    public function delete(string $path): bool
    {
        if ($this->driver === 'local') {
            $fullPath = rtrim($this->config['local']['path'], '/\\') . '/' . ltrim($path, '/\\');
            return file_exists($fullPath) && unlink($fullPath);
        }
        // S3 delete stub
        return false;
    }
}

