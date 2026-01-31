<?php

/**
 * Cron Runner Script
 * Usage: php scripts/cron_runner.php [task_name]
 * Example: php scripts/cron_runner.php interview_reminders
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Get task from argument
$task = $argv[1] ?? null;

if (!$task) {
    echo "Usage: php scripts/cron_runner.php [task_name]\n";
    echo "Available tasks:\n";
    echo " - interview_reminders\n";
    echo " - expire_premium_candidates\n";
    echo " - reindex_jobs\n";
    echo " - notify_expiring_subscriptions\n";
    exit(1);
}

echo "Running task: {$task}...\n";

try {
    $cronService = new \App\Services\CronService();
    $cronService->runTask($task);
    echo "Task completed successfully.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
