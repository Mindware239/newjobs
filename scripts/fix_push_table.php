<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

try {
    $db = Database::getInstance();
    
    echo "Dropping old table if exists...\n";
    $db->query("DROP TABLE IF EXISTS user_push_tokens");
    
    echo "Creating user_push_tokens table...\n";
    $sql = "CREATE TABLE user_push_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        token VARCHAR(255) NOT NULL,
        device VARCHAR(50) DEFAULT '',
        browser VARCHAR(50) DEFAULT '',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_token (user_id, token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->query($sql);
    echo "Table created successfully.\n";
    
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
