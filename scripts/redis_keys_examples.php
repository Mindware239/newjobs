<?php

/**
 * Redis Key Design Examples
 * 
 * Caching Strategy:
 * - Cache job lists with TTL
 * - Cache employer dashboard stats
 * - Rate limiting keys
 * - Session storage
 */

// Job list cache example
$redis = \App\Core\RedisClient::getInstance();

// Cache job list for employer
$employerId = 1;
$cacheKey = "employer:jobs:{$employerId}:page:1";
$jobs = [/* job data */];
$redis->set($cacheKey, $jobs, 300); // 5 minutes TTL

// Cache dashboard stats
$dashboardKey = "employer:dashboard:{$employerId}";
$stats = [
    'total_jobs' => 10,
    'active_jobs' => 5,
    'total_applications' => 50
];
$redis->set($dashboardKey, $stats, 300);

// Rate limiting example
$ip = '192.168.1.1';
$rateLimitKey = "rate_limit:ip:{$ip}";
$current = $redis->getConnection()->get($rateLimitKey);
if ($current === false) {
    $redis->getConnection()->setex($rateLimitKey, 60, 1);
} else {
    $count = (int)$current;
    if ($count >= 100) {
        // Rate limit exceeded
    } else {
        $redis->getConnection()->incr($rateLimitKey);
    }
}

// Session storage
$sessionId = session_id();
$sessionKey = "session:{$sessionId}";
$sessionData = ['user_id' => 1, 'role' => 'employer'];
$redis->set($sessionKey, $sessionData, 7200); // 2 hours

// Queue keys
$queueKey = "queue:index_job";
$redis->getConnection()->lpush($queueKey, json_encode(['job_id' => 123]));

