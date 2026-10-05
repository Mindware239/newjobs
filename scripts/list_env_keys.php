<?php
require_once __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../');
    $dotenv->load();
    $keys = array_keys($_ENV);
    sort($keys);
    foreach($keys as $k) {
        echo $k . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
