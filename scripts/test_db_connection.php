<?php

/**
 * Test Database Connection
 * Run this to verify MySQL is running and accessible
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
try {
    $dotenv->load();
} catch (Exception $e) {
    // .env might not exist, use defaults
}

echo "Testing Database Connection...\n\n";

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'mindwareinfotech';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

echo "Connection Details:\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '***') . "\n\n";

try {
    $dsn = "mysql:host=$host;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ SUCCESS: Connected to MySQL server!\n\n";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✓ Database '$dbname' exists\n";
        
        // Try to use the database
        $pdo->exec("USE $dbname");
        echo "✓ Successfully connected to database '$dbname'\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "\n⚠ Database is empty. Run migrations:\n";
            echo "   php scripts/migrate.php\n";
        } else {
            echo "\n✓ Found " . count($tables) . " table(s) in database\n";
        }
    } else {
        echo "\n⚠ Database '$dbname' does not exist!\n";
        echo "Creating database...\n";
        
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✓ Database '$dbname' created successfully!\n";
            echo "\nNow run migrations:\n";
            echo "   php scripts/migrate.php\n";
        } catch (PDOException $e) {
            echo "✗ Failed to create database: " . $e->getMessage() . "\n";
            echo "\nPlease create the database manually in phpMyAdmin or MySQL.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ CONNECTION FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), '2002') !== false || strpos($e->getMessage(), 'refused') !== false) {
        echo "SOLUTION:\n";
        echo "1. Open XAMPP Control Panel\n";
        echo "2. Start MySQL service\n";
        echo "3. Wait for MySQL to start (green indicator)\n";
        echo "4. Try again\n";
    } elseif (strpos($e->getMessage(), '1045') !== false) {
        echo "SOLUTION:\n";
        echo "1. Check DB_USER and DB_PASSWORD in .env file\n";
        echo "2. Default XAMPP credentials: user=root, password=(empty)\n";
    } elseif (strpos($e->getMessage(), '1049') !== false) {
        echo "SOLUTION:\n";
        echo "1. Database '$dbname' does not exist\n";
        echo "2. Create it in phpMyAdmin or run this script again\n";
    }
    
    exit(1);
}

