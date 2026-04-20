<?php
require_once 'app/Core/Database.php';
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'mindwareinfotech';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASSWORD'] = '';
try {
    $db = \App\Core\Database::getInstance();
    $result = $db->fetchOne('SELECT id FROM candidates WHERE user_id = 99');
    if ($result) {
        echo $result['id'];
    } else {
        echo "none";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
