<?php
/**
 * Script to create reviews table
 * Run: php database/create_reviews_table.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    $sql = "CREATE TABLE IF NOT EXISTS `reviews` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `candidate_id` int(11) DEFAULT NULL,
      `reviewer_name` varchar(255) NOT NULL,
      `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Rating from 1 to 5',
      `title` varchar(255) DEFAULT NULL,
      `review_text` text DEFAULT NULL,
      `status` enum('pending','approved','rejected') DEFAULT 'approved',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_company_id` (`company_id`),
      KEY `idx_user_id` (`user_id`),
      KEY `idx_candidate_id` (`candidate_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($sql);
    
    echo "SUCCESS: Reviews table created or already exists.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

