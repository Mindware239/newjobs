<?php

/**
 * Test script to diagnose password reset token issues
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

echo "=== Password Reset Token Diagnostic ===\n\n";

// Check database connection
try {
    $db = \App\Core\Database::getInstance();
    echo "✓ Database connection: OK\n";
} catch (\Exception $e) {
    echo "✗ Database connection: FAILED - " . $e->getMessage() . "\n";
    exit(1);
}

// Check if password_resets table exists
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'password_resets'");
    if ($result) {
        echo "✓ password_resets table: EXISTS\n";
    } else {
        echo "✗ password_resets table: DOES NOT EXIST\n";
        echo "  Run: mysql -u root -p mindwareinfotech < scripts/migrations/031_create_password_resets_table.sql\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "\n";
    exit(1);
}

// Check table structure
try {
    $columns = $db->fetchAll("DESCRIBE password_resets");
    echo "\nTable structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch (\Exception $e) {
    echo "✗ Error checking structure: " . $e->getMessage() . "\n";
}

// Check for existing tokens
try {
    $count = $db->fetchOne("SELECT COUNT(*) as count FROM password_resets");
    echo "\nExisting tokens in database: " . ($count['count'] ?? 0) . "\n";
    
    // Show recent tokens
    $recent = $db->fetchAll(
        "SELECT id, email, LEFT(token, 20) as token_preview, user_id, expires_at, created_at 
         FROM password_resets 
         ORDER BY created_at DESC 
         LIMIT 5"
    );
    
    if (!empty($recent)) {
        echo "\nRecent tokens:\n";
        foreach ($recent as $row) {
            $expired = strtotime($row['expires_at']) < time() ? ' (EXPIRED)' : '';
            echo "  - ID: {$row['id']}, User: {$row['user_id']}, Email: {$row['email']}, Expires: {$row['expires_at']}{$expired}\n";
            echo "    Token: {$row['token_preview']}...\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Error checking tokens: " . $e->getMessage() . "\n";
}

// Test token insertion
echo "\n=== Testing Token Storage ===\n";
try {
    $testToken = bin2hex(random_bytes(32));
    $testUserId = 1; // Assuming user ID 1 exists
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Check if user exists
    $user = $db->fetchOne("SELECT id FROM users WHERE id = :id", ['id' => $testUserId]);
    if (!$user) {
        echo "⚠ Test user (ID: {$testUserId}) does not exist. Skipping insertion test.\n";
    } else {
        $db->query(
            "INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email, :token, :user_id, :expires_at)",
            [
                'email' => 'test@example.com',
                'token' => $testToken,
                'user_id' => $testUserId,
                'expires_at' => $expiresAt
            ]
        );
        echo "✓ Test token inserted successfully\n";
        
        // Test retrieval
        $retrieved = $db->fetchOne(
            "SELECT user_id, email, expires_at FROM password_resets WHERE token = :token AND expires_at > NOW()",
            ['token' => $testToken]
        );
        
        if ($retrieved) {
            echo "✓ Test token retrieved successfully\n";
        } else {
            echo "✗ Test token retrieval FAILED\n";
        }
        
        // Clean up test token
        $db->query("DELETE FROM password_resets WHERE token = :token", ['token' => $testToken]);
        echo "✓ Test token cleaned up\n";
    }
} catch (\Exception $e) {
    echo "✗ Token storage test FAILED: " . $e->getMessage() . "\n";
    echo "  Error trace: " . $e->getTraceAsString() . "\n";
}

// Check Redis
echo "\n=== Checking Redis ===\n";
try {
    $redis = \App\Core\RedisClient::getInstance();
    if ($redis->isAvailable()) {
        echo "✓ Redis: AVAILABLE\n";
    } else {
        echo "⚠ Redis: NOT AVAILABLE (will use database only)\n";
    }
} catch (\Exception $e) {
    echo "⚠ Redis: ERROR - " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\nNext steps:\n";
echo "1. Check the error log: storage/logs/php_errors.log\n";
echo "2. Try requesting a password reset again\n";
echo "3. Check the logs for detailed token validation information\n";

