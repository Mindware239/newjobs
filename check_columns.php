<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Env.php';

try {
    $db = \App\Core\Database::getInstance();
    $columns = $db->fetchAll("DESCRIBE applications");
    foreach ($columns as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
