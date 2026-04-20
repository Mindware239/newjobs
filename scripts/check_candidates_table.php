<?php
require_once 'app/Core/Database.php';

// Mock $_ENV for Database constructor
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'mindwareinfotech';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASSWORD'] = '';

try {
    $db = \App\Core\Database::getInstance();
    $result = $db->fetchAll("DESCRIBE candidates");
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
