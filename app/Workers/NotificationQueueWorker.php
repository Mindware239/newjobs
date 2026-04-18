<?php
declare(strict_types=1);

namespace App\Workers;

use App\Core\Database;
use App\Services\NotificationService;

/**
 * Enterprise Notification Queue Worker
 * Run this via Supervisor or cron (e.g. `php worker.php`)
 */
class NotificationQueueWorker
{
    private Database $db;
    private int $batchSize;
    private int $maxRetries;

    public function __construct(int $batchSize = 50, int $maxRetries = 3)
    {
        $this->db = Database::getInstance();
        $this->batchSize = $batchSize;
        $this->maxRetries = $maxRetries;
    }

    public function run(): void
    {
        echo "Starting Notification Queue Worker...\n";

        // Create table if not exists
        $this->ensureQueueTableExists();

        while (true) {
            $processed = $this->processBatch();
            
            if ($processed === 0) {
                // Sleep to prevent CPU spike when idle
                sleep(5);
            }
        }
    }

    private function processBatch(): int
    {
        $processed = 0;

        try {
            // Lock rows for processing (InnoDB row locking)
            $this->db->query("START TRANSACTION");

            // Fetch pending jobs (or retries that failed recently)
            $jobs = $this->db->fetchAll(
                "SELECT * FROM notification_queue 
                 WHERE status IN ('pending', 'failed') 
                 AND retries < :max
                 ORDER BY created_at ASC 
                 LIMIT {$this->batchSize} FOR UPDATE SKIP LOCKED",
                ['max' => $this->maxRetries]
            );

            if (empty($jobs)) {
                $this->db->query("COMMIT");
                return 0;
            }

            // Mark as processing
            $ids = array_column($jobs, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db->query("UPDATE notification_queue SET status = 'processing' WHERE id IN ($placeholders)", $ids);
            
            $this->db->query("COMMIT");

            // Process each job
            foreach ($jobs as $job) {
                $this->processJob($job);
                $processed++;
            }

        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            error_log("Queue Worker Transaction Error: " . $e->getMessage());
        }

        return $processed;
    }

    private function processJob(array $job): void
    {
        try {
            $data = json_decode($job['data'], true) ?? [];
            
            // Execute actual notification sending logic
            NotificationService::send(
                (int)$job['user_id'],
                $job['type'],
                $job['title'],
                $job['message'],
                $data,
                $job['link']
            );

            // Mark completed
            $this->db->query(
                "UPDATE notification_queue SET status = 'completed', updated_at = NOW() WHERE id = :id",
                ['id' => $job['id']]
            );
            
            echo "Processed notification ID: {$job['id']}\n";

        } catch (\Throwable $e) {
            // Mark failed and increment retry
            $this->db->query(
                "UPDATE notification_queue 
                 SET status = 'failed', retries = retries + 1, error_log = :error, updated_at = NOW() 
                 WHERE id = :id",
                [
                    'error' => substr($e->getMessage(), 0, 500),
                    'id' => $job['id']
                ]
            );
            error_log("Failed to process notification ID {$job['id']}: " . $e->getMessage());
        }
    }

    private function ensureQueueTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS notification_queue (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            data JSON NULL,
            link VARCHAR(255) NULL,
            status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            retries INT DEFAULT 0,
            error_log TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status, retries)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->db->query($sql);
    }
}
