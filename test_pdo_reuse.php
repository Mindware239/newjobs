<?php
require_once __DIR__ . '/vendor/autoload.php';

// Test script for PDO reused named parameters (FIXED VERSION)
$db = \App\Core\Database::getInstance();

echo "Testing reused named parameters (with unique names)...\n";

try {
    $sql = "SELECT id, 'city' as type FROM cities WHERE name = :name1 OR slug = :slug1
            UNION
            SELECT id, 'state' as type FROM states WHERE name = :name2 OR slug = :slug2
            UNION
            SELECT id, 'country' as type FROM countries WHERE name = :name3 OR slug = :slug3";
    
    $params = [
        'name1' => 'Cochin', 'slug1' => 'cochin',
        'name2' => 'Cochin', 'slug2' => 'cochin',
        'name3' => 'Cochin', 'slug3' => 'cochin'
    ];
    
    $result = $db->fetchOne($sql, $params);
    
    echo "Query executed successfully!\n";
    print_r($result);
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
