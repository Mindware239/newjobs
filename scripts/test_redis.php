<?php

/**
 * Test Redis Connection and Functionality
 * Run this script to verify your Redis setup
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Testing Redis Connection...\n\n";

// Check if Redis extension is loaded
if (!extension_loaded('redis')) {
    echo "✗ Redis extension is not installed!\n\n";
    echo "Options:\n";
    echo "1. Install Redis extension (see INSTALL_REDIS_WINDOWS.txt)\n";
    echo "2. Use Docker: docker-compose up -d redis\n";
    echo "3. Continue without Redis (some features won't work)\n\n";
    exit(1);
}

try {
    use App\Core\RedisClient;
    
    $host = $_ENV['REDIS_HOST'] ?? 'localhost';
    $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
    
    echo "Connecting to: $host:$port\n";
    
    $redis = RedisClient::getInstance();
    
    echo "✓ Connection successful!\n\n";
    
    // Test 1: Basic set/get
    echo "Test 1: Basic Set/Get\n";
    $testKey = 'test:connection:' . time();
    $testValue = ['message' => 'Hello Redis!', 'timestamp' => time()];
    $redis->set($testKey, $testValue, 60);
    $retrieved = $redis->get($testKey);
    
    if ($retrieved && $retrieved['message'] === 'Hello Redis!') {
        echo "  ✓ Set/Get works correctly\n";
    } else {
        echo "  ✗ Set/Get failed\n";
    }
    
    // Test 2: Queue operations
    echo "\nTest 2: Queue Operations\n";
    $queueKey = 'queue:test';
    $redis->getConnection()->lpush($queueKey, json_encode(['test' => 'data']));
    $queueData = $redis->getConnection()->rpop($queueKey);
    
    if ($queueData) {
        echo "  ✓ Queue push/pop works\n";
    } else {
        echo "  ✗ Queue operations failed\n";
    }
    
    // Test 3: Rate limiting simulation
    echo "\nTest 3: Rate Limiting\n";
    $rateKey = 'rate_limit:test:' . time();
    $redis->getConnection()->setex($rateKey, 60, 1);
    $count = $redis->getConnection()->get($rateKey);
    
    if ($count) {
        echo "  ✓ Rate limiting keys work\n";
    } else {
        echo "  ✗ Rate limiting failed\n";
    }
    
    // Test 4: Cache simulation
    echo "\nTest 4: Caching\n";
    $cacheKey = 'cache:test:' . time();
    $cacheData = ['stats' => ['total' => 100, 'active' => 50]];
    $redis->set($cacheKey, $cacheData, 300);
    $cached = $redis->get($cacheKey);
    
    if ($cached && isset($cached['stats'])) {
        echo "  ✓ Caching works correctly\n";
    } else {
        echo "  ✗ Caching failed\n";
    }
    
    // Get Redis info
    echo "\nRedis Server Info:\n";
    $info = $redis->getConnection()->info();
    echo "  Version: " . ($info['redis_version'] ?? 'unknown') . "\n";
    echo "  Used Memory: " . ($info['used_memory_human'] ?? 'unknown') . "\n";
    echo "  Connected Clients: " . ($info['connected_clients'] ?? 'unknown') . "\n";
    
    // Cleanup
    $redis->delete($testKey);
    $redis->delete($cacheKey);
    
    echo "\n✓ All tests passed! Redis is ready to use.\n";
    
} catch (\Exception $e) {
    echo "\n✗ Connection failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Make sure Redis server is running\n";
    echo "2. Check REDIS_HOST and REDIS_PORT in .env file\n";
    echo "3. For Docker: docker-compose up -d redis\n";
    echo "4. Test manually: redis-cli ping (should return PONG)\n";
    exit(1);
}

