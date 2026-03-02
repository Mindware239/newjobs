<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$dbName = $db->query("SELECT DATABASE()")->fetchColumn();
$db->exec("CREATE TABLE IF NOT EXISTS schema_migrations (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL, checksum VARCHAR(64) NOT NULL, applied_at DATETIME NOT NULL, UNIQUE KEY uniq_file (filename))");

$migrationsDir = __DIR__ . '/migrations';
$migrations = glob($migrationsDir . '/*.sql');
sort($migrations);

echo "Running migrations...\n";

foreach ($migrations as $migration) {
    $sql = file_get_contents($migration);
    $filename = basename($migration);
    $checksum = hash('sha256', (string)$sql);
    
    echo "Executing: $filename\n";
    if (trim((string)$sql) === '') {
        echo "↷ $filename skipped (empty)\n";
        continue;
    }
    $existing = $db->prepare("SELECT checksum FROM schema_migrations WHERE filename = :f");
    $existing->execute(['f' => $filename]);
    $row = $existing->fetch(PDO::FETCH_ASSOC);
    if ($row && ($row['checksum'] ?? '') === $checksum) {
        echo "↷ $filename skipped (already applied)\n";
        continue;
    }
    $statements = array_filter(array_map('trim', preg_split('/;\s*/', (string)$sql)));
    $appliedAny = false;
    foreach ($statements as $stmt) {
        if ($stmt === '') { continue; }
        $skip = false;
        if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+(?:COLUMN\s+)?`?(\w+)`?/i', $stmt, $m)) {
            $table = $m[1]; $column = $m[2];
            $check = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND COLUMN_NAME = :col");
            $check->execute(['db' => $dbName, 'tbl' => $table, 'col' => $column]);
            if ((int)$check->fetchColumn() > 0) { $skip = true; }
        } elseif (preg_match('/CREATE\s+(UNIQUE\s+)?INDEX\s+`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $stmt, $m)) {
            $index = $m[2]; $table = $m[3];
            $check = $db->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND INDEX_NAME = :idx");
            $check->execute(['db' => $dbName, 'tbl' => $table, 'idx' => $index]);
            if ((int)$check->fetchColumn() > 0) { $skip = true; }
        } elseif (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+UNIQUE\s+INDEX\s+`?(\w+)`?/i', $stmt, $m)) {
            $table = $m[1]; $index = $m[2];
            $check = $db->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND INDEX_NAME = :idx");
            $check->execute(['db' => $dbName, 'tbl' => $table, 'idx' => $index]);
            if ((int)$check->fetchColumn() > 0) { $skip = true; }
        } elseif (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+MODIFY\s+`?(\w+)`?\s+JSON/i', $stmt, $m)) {
            $table = $m[1]; $column = $m[2];
            $check = $db->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND COLUMN_NAME = :col");
            $check->execute(['db' => $dbName, 'tbl' => $table, 'col' => $column]);
            $dtype = strtolower((string)$check->fetchColumn());
            if ($dtype === 'json') { $skip = true; }
        }
        if ($skip) {
            echo "↷ skipped idempotent: " . substr($stmt, 0, 60) . "...\n";
            continue;
        }
        try {
            $db->exec($stmt);
            $appliedAny = true;
        } catch (PDOException $e) {
            echo "✗ failed: " . $e->getMessage() . "\n";
        }
    }
    if ($appliedAny) {
        $ins = $db->prepare("INSERT INTO schema_migrations (filename, checksum, applied_at) VALUES (:f, :c, NOW()) ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), applied_at = VALUES(applied_at)");
        $ins->execute(['f' => $filename, 'c' => $checksum]);
        echo "✓ $filename completed\n";
    } else {
        echo "↷ $filename no changes applied\n";
    }
}

echo "Migrations completed!\n";

