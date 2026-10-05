<?php
require_once 'vendor/autoload.php';
try {
    $db = \App\Core\Database::getInstance();
    $rows = $db->fetchAll('SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE "%cashfree%"');
    foreach($rows as $r) {
        echo $r['setting_key'] . ': ' . (empty($r['setting_value']) ? 'EMPTY' : 'SET') . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
