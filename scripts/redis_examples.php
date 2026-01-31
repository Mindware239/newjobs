<?php

/**
 * Redis Usage Examples in This Project
 * Shows how Redis is used throughout the application
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\RedisClient;

echo "Redis Usage Examples in This Project\n";
echo "====================================\n\n";

$redis = RedisClient::getInstance();

// Example 1: Caching Dashboard Stats (DashboardController.php)
echo "1. CACHING DASHBOARD STATS\n";
echo "   Location: app/Controllers/Employer/DashboardController.php\n";
echo "   Code:\n";
echo "   \$cacheKey = \"employer:dashboard:{\$employerId}\";\n";
echo "   \$stats = ['total_jobs' => 10, 'active_jobs' => 5];\n";
echo "   \$redis->set(\$cacheKey, \$stats, 300); // Cache for 5 minutes\n";
echo "   \$cached = \$redis->get(\$cacheKey);\n\n";

// Example 2: Rate Limiting (RateLimitMiddleware.php)
echo "2. RATE LIMITING\n";
echo "   Location: app/Middlewares/RateLimitMiddleware.php\n";
echo "   Code:\n";
echo "   \$key = \"rate_limit:user:{\$userId}\";\n";
echo "   \$count = \$redis->getConnection()->get(\$key);\n";
echo "   if (\$count >= 100) { /* Block request */ }\n";
echo "   \$redis->getConnection()->incr(\$key);\n\n";

// Example 3: Session Storage
echo "3. SESSION STORAGE\n";
echo "   Code:\n";
echo "   \$sessionKey = \"session:{\$sessionId}\";\n";
echo "   \$sessionData = ['user_id' => 1, 'role' => 'employer'];\n";
echo "   \$redis->set(\$sessionKey, \$sessionData, 7200); // 2 hours\n\n";

// Example 4: Queue System (Workers)
echo "4. QUEUE SYSTEM\n";
echo "   Location: app/Workers/\n";
echo "   Code:\n";
echo "   // Enqueue job\n";
echo "   \$redis->getConnection()->lpush('queue:index_job', json_encode(\$data));\n";
echo "   \n";
echo "   // Worker processes\n";
echo "   \$data = \$redis->getConnection()->brpop('queue:index_job', 5);\n\n";

// Example 5: Cache Job Lists
echo "5. CACHING JOB LISTS\n";
echo "   Code:\n";
echo "   \$cacheKey = \"employer:jobs:{\$employerId}:page:{\$page}\";\n";
echo "   \$jobs = [/* job data */];\n";
echo "   \$redis->set(\$cacheKey, \$jobs, 300);\n\n";

// Show actual Redis keys pattern
echo "Redis Key Patterns Used:\n";
echo "------------------------\n";
echo "- employer:dashboard:{id}        - Dashboard cache\n";
echo "- employer:jobs:{id}:page:{n}    - Job list cache\n";
echo "- rate_limit:user:{id}           - User rate limit\n";
echo "- rate_limit:ip:{ip}             - IP rate limit\n";
echo "- session:{session_id}          - Session data\n";
echo "- queue:index_job                - Job indexing queue\n";
echo "- queue:email                    - Email queue\n";
echo "- queue:webhook                  - Webhook queue\n\n";

echo "Benefits:\n";
echo "---------\n";
echo "✓ Faster response times (caching)\n";
echo "✓ Reduced database load\n";
echo "✓ Better session management\n";
echo "✓ Background job processing\n";
echo "✓ API abuse prevention (rate limiting)\n";

