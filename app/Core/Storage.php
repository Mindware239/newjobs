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
                'path' => $_ENV['STORAGE_PATH'] ?? __DIR__ . '/../../storage/uploads',
            ],
            's3' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
                'region' => $_ENV['AWS_REGION'] ?? 'us-east-1',
                'bucket' => $_ENV['AWS_BUCKET'] ?? '',
            ]
        ];
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
        $uploadDir = $this->config['local']['path'] . '/' . $path;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $path . '/' . $filename;
        }

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
        return $_ENV['APP_URL'] . '/storage/uploads/' . $path;
    }

    public function delete(string $path): bool
    {
        if ($this->driver === 'local') {
            $fullPath = $this->config['local']['path'] . '/' . $path;
            return file_exists($fullPath) && unlink($fullPath);
        }
        // S3 delete stub
        return false;
    }
}

