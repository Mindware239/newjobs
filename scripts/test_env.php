<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing .env file parsing...\n\n";

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    
    echo "✓ SUCCESS: .env file parsed correctly!\n\n";
    echo "Sample values:\n";
    echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'not set') . "\n";
    echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
    echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'not set') . "\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

