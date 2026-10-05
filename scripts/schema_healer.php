<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\Database;

class SchemaHealer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function heal(): void
    {
        echo "Starting schema healing...\n";

        // 1. Applications table: Ensure applied_at exists
        $this->ensureColumn('applications', 'applied_at', 'DATETIME DEFAULT CURRENT_TIMESTAMP');

        // 2. Employer KYC: Ensure review_status exists
        $this->ensureColumn('employer_kyc_documents', 'review_status', "ENUM('pending','approved','rejected') DEFAULT 'pending'");
        $this->ensureIndex('employer_kyc_documents', 'idx_review_status', 'review_status');

        // 3. Activity Logs: Ensure actor_id and actor_type exist
        $this->ensureColumn('activity_logs', 'actor_id', 'BIGINT UNSIGNED NOT NULL');
        $this->ensureColumn('activity_logs', 'actor_type', "ENUM('employer','candidate','admin','system') NOT NULL");
        $this->ensureIndex('activity_logs', 'idx_actor', 'actor_type, actor_id');

        // 4. Job Categories: Ensure table exists
        $this->ensureJobCategories();

        // 5. Audit Logs: Ensure performed_by exists
        $this->ensureColumn('audit_logs', 'performed_by', 'BIGINT UNSIGNED NULL');
        $this->ensureColumn('audit_logs', 'metadata', 'JSON NULL');

        // 6. Safe Drop ip_address from cookie tables (GDPR compliance usually)
        $this->dropColumn('user_cookie_consents', 'ip_address');
        $this->dropColumn('cookie_consent_audit_logs', 'ip_address');

        // 7. Ensure Foreign Keys are safe for all modules
        $this->ensureForeignKey('career_applications', 'fk_career_applications_career_id', 'career_id', 'careers', 'id');
        
        // Cookie Consent Module
        $this->ensureForeignKey('cookie_definitions', 'fk_cookie_def_category', 'category_id', 'cookie_categories', 'id');
        $this->ensureForeignKey('user_cookie_consents', 'fk_consents_version', 'consent_version_id', 'cookie_policy_versions', 'id');
        $this->ensureForeignKey('cookie_consent_audit_logs', 'fk_audit_consent', 'consent_id', 'user_cookie_consents', 'id');
        
        // Tracking Module
        $this->ensureForeignKey('visitor_sessions', 'fk_sessions_visitor', 'visitor_id', 'tracking_visitors', 'id');
        $this->ensureForeignKey('behavior_events', 'fk_events_visitor', 'visitor_id', 'tracking_visitors', 'id');
        $this->ensureForeignKey('heatmap_events', 'fk_heatmap_visitor', 'visitor_id', 'tracking_visitors', 'id');

        echo "Schema healing completed!\n";
    }

    private function ensureColumn(string $table, string $column, string $definition): void
    {
        if (!$this->tableExists($table)) return;

        $stmt = $this->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if (!$stmt->fetch()) {
            echo "Adding column $column to $table...\n";
            try {
                $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            } catch (\Exception $e) {
                echo "Error adding column $column: " . $e->getMessage() . "\n";
            }
        }
    }

    private function dropColumn(string $table, string $column): void
    {
        if (!$this->tableExists($table)) return;

        $stmt = $this->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->fetch()) {
            echo "Dropping column $column from $table...\n";
            try {
                $this->db->exec("ALTER TABLE `$table` DROP COLUMN `$column` ");
            } catch (\Exception $e) {
                echo "Error dropping column $column: " . $e->getMessage() . "\n";
            }
        }
    }

    private function ensureIndex(string $table, string $indexName, string $columns): void
    {
        if (!$this->tableExists($table)) return;

        $stmt = $this->db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'");
        if (!$stmt->fetch()) {
            echo "Adding index $indexName to $table...\n";
            try {
                $this->db->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($columns)");
            } catch (\Exception $e) {
                echo "Error adding index $indexName: " . $e->getMessage() . "\n";
            }
        }
    }

    private function ensureForeignKey(string $table, string $fkName, string $column, string $refTable, string $refColumn): void
    {
        if (!$this->tableExists($table) || !$this->tableExists($refTable)) return;

        $dbName = $_ENV['DB_NAME'] ?? '';
        $stmt = $this->db->prepare("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.REFERENTIAL_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
        ");
        $stmt->execute([$dbName, $table, $fkName]);
        
        if (!$stmt->fetch()) {
            echo "Adding foreign key $fkName to $table...\n";
            try {
                $this->db->exec("ALTER TABLE `$table` ADD CONSTRAINT `$fkName` FOREIGN KEY (`$column`) REFERENCES `$refTable`(`$refColumn`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                echo "Error adding foreign key $fkName: " . $e->getMessage() . "\n";
            }
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function ensureJobCategories(): void
    {
        if (!$this->tableExists('job_categories')) {
            echo "Creating job_categories table...\n";
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `job_categories` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL,
                    `slug` VARCHAR(100) NOT NULL,
                    `description` TEXT DEFAULT NULL,
                    `image` VARCHAR(255) DEFAULT NULL,
                    `sort_order` INT(11) DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `slug` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }
}

$healer = new SchemaHealer();
$healer->heal();
