<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $dotenv = Dotenv\Dotenv::createUnsafeMutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

try {
    $db = Database::getInstance();
    $roles = $db->fetchAll("SELECT r.* FROM roles r INNER JOIN role_user ru ON r.id = ru.role_id WHERE ru.user_id = 100");
    echo "Roles for user 100:\n";
    print_r($roles);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
