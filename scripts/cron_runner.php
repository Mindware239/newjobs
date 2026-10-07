<?php

/**
 * Cron Runner Script
 * Usage: php scripts/cron_runner.php [task_name]
 * Example: php scripts/cron_runner.php interview_reminders
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../');
$dotenv->safeLoad();
// Local machines: .env.local points at the local database (same as index.php); never present on the server.
if (is_file(__DIR__ . '/../.env.local')) {
    \Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../', '.env.local')->load();
}
// All dates the site shows (posted times, last dates …) are Indian time unless APP_TIMEZONE says otherwise.
date_default_timezone_set((string)($_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata') ?: 'Asia/Kolkata');

// Get task from argument
$task = $argv[1] ?? null;

if (!$task) {
    echo "Usage: php scripts/cron_runner.php [task_name]\n";
    echo "Available tasks:\n";
    echo " - interview_reminders\n";
    echo " - expire_premium_candidates\n";
    echo " - reindex_jobs\n";
    echo " - notify_expiring_subscriptions\n";
    echo " - registration_payment_reminders (run every 15 minutes)\n";
    echo " - india_jobs_fetch (run every hour – imports enabled Jobs in India feeds)\n";
    echo " - monthly_newsletter (run every hour – monthly update to paid members, sent in batches)\n";
    echo " - blog_import (also runs inside india_jobs_fetch – publishes content/blogs/*.html)\n";
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
