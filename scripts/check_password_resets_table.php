<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

try {
    $db = Database::getInstance();
    $res = $db->fetchOne('SHOW CREATE TABLE password_resets');
    print_r($res);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
