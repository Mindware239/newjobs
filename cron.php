<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Initialize services
use App\Services\CronService;

$cron = new CronService();
$tasks = [
    'monitor_aggregate_minute',
    'monitor_system_stats',
    'monitor_db_load',
    'monitor_queue_stats',
    'interview_reminders',
    'upgrade_reminders',
    'expire_premium_candidates',
    'reindex_jobs',
    'notify_expiring_subscriptions',
    'notify_incomplete_profiles',
    'notify_inactive_candidates',
    'notify_abandoned_job_views',
    'notify_subscription_limits',
    'auto_apply_candidates',
    'notify_profile_views',
    'notify_low_match_suggestions'
];

// Check if specific task requested via argument
if (isset($argv[1])) {
    $requestedTask = $argv[1];
    if ($requestedTask === 'all') {
        echo "Running all tasks...\n";
        foreach ($tasks as $task) {
            echo "Running $task...";
            try {
                $cron->runTask($task);
                echo " Done.\n";
            } catch (\Throwable $e) {
                echo " Error: " . $e->getMessage() . "\n";
            }
        }
    } elseif (in_array($requestedTask, $tasks)) {
        echo "Running $requestedTask...\n";
        $cron->runTask($requestedTask);
        echo "Done.\n";
    } else {
        echo "Unknown task: $requestedTask\n";
        echo "Available tasks: " . implode(', ', $tasks) . "\n";
    }
} else {
    // Default behavior if no args (e.g. run all or specific set)
    // For now, let's just print usage
    echo "Usage: php cron.php [task_name|all]\n";
    echo "Available tasks:\n";
    foreach ($tasks as $task) {
        echo " - $task\n";
    }
}
