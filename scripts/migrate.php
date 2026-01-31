<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$migrationsDir = __DIR__ . '/migrations';
$migrations = glob($migrationsDir . '/*.sql');
sort($migrations);

echo "Running migrations...\n";

foreach ($migrations as $migration) {
    $sql = file_get_contents($migration);
    $filename = basename($migration);
    
    echo "Executing: $filename\n";
    if (trim((string)$sql) === '') {
        echo "↷ $filename skipped (empty)\n";
        continue;
    }
    
    try {
        $db->exec($sql);
        echo "✓ $filename completed\n";
    } catch (PDOException $e) {
        echo "✗ $filename failed: " . $e->getMessage() . "\n";
    }
}

echo "Migrations completed!\n";

