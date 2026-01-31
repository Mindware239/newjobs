<?php
/**
 * Seed Resume Templates
 * Run this script to automatically insert resume templates into the database
 * Usage: php scripts/seed_resume_templates.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    try {
        $dotenv->load();
    } catch (Exception $e) {
        // .env might have issues, use defaults
    }
}

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'mindwareinfotech';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Connected to database: {$dbname}\n\n";
    
    // Check if templates already exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM resume_templates");
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        echo "⚠ Templates already exist ({$count} templates found).\n";
        echo "Do you want to add more templates? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtolower($line)) !== 'y') {
            echo "Skipping seed. Existing templates will be kept.\n";
            exit(0);
        }
    }
    
    // Read SQL file
    $seedFile = __DIR__ . '/../database/seeds/001_seed_resume_templates.sql';
    
    if (!file_exists($seedFile)) {
        throw new Exception("Seed file not found: {$seedFile}");
    }
    
    $sql = file_get_contents($seedFile);
    
    // Remove comments
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = trim($sql);
    
    if (empty($sql)) {
        throw new Exception("Seed file is empty");
    }
    
    // Execute INSERT statement
    $pdo->beginTransaction();
    
    try {
        // Use INSERT IGNORE to skip duplicates
        $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
        $pdo->exec($sql);
        
        $pdo->commit();
        
        // Count inserted templates
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM resume_templates");
        $newCount = $stmt->fetch()['count'];
        
        echo "\n✓ Successfully seeded resume templates!\n";
        echo "✓ Total templates in database: {$newCount}\n\n";
        
        // Show template list
        $stmt = $pdo->query("SELECT id, name, slug, is_premium, is_active FROM resume_templates ORDER BY sort_order");
        $templates = $stmt->fetchAll();
        
        echo "Templates:\n";
        foreach ($templates as $template) {
            $premium = $template['is_premium'] ? '[Premium]' : '[Free]';
            $active = $template['is_active'] ? '✓' : '✗';
            echo "  {$active} #{$template['id']} {$template['name']} ({$template['slug']}) {$premium}\n";
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'SQLSTATE') === 0) {
        echo "\nTip: Make sure:\n";
        echo "  1. MySQL is running\n";
        echo "  2. Database '{$dbname}' exists\n";
        echo "  3. resume_templates table exists (run migrations first)\n";
    }
    exit(1);
}

echo "\n✓ Done!\n";
