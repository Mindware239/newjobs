<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/..');
$dotenv->load();
use App\Core\Database;
$db = Database::getInstance();
$res = $db->fetchAll("DESCRIBE employers");
print_r($res);
