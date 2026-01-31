<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Employer;

try {
    $db = Database::getInstance();
    echo "✓ Database connection successful\n\n";
    
    // Check if tables exist
    $tables = ['users', 'employers', 'employer_settings'];
    foreach ($tables as $table) {
        try {
            $result = $db->fetchOne("SHOW TABLES LIKE '$table'");
            if ($result) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "✗ Table '$table' does NOT exist\n";
            }
        } catch (\Exception $e) {
            echo "✗ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // Test User creation
    echo "Testing User creation...\n";
    $testUser = new User();
    $testUser->fill([
        'email' => 'test@example.com',
        'role' => 'employer',
        'status' => 'active',
        'phone' => '+911234567890'
    ]);
    $testUser->setPassword('testpassword123');
    
    if ($testUser->save()) {
        echo "✓ User created successfully. ID: " . $testUser->id . "\n";
        
        // Test Employer creation
        echo "Testing Employer creation...\n";
        $testEmployer = new Employer();
        $testEmployer->fill([
            'user_id' => $testUser->id,
            'company_name' => 'Test Company',
            'company_slug' => 'test-company',
            'country' => 'India',
            'kyc_status' => 'pending'
        ]);
        
        if ($testEmployer->save()) {
            echo "✓ Employer created successfully. ID: " . $testEmployer->id . "\n";
            
            // Clean up test data
            $testEmployer->delete();
            $testUser->delete();
            echo "✓ Test data cleaned up\n";
        } else {
            echo "✗ Failed to create employer\n";
        }
    } else {
        echo "✗ Failed to create user\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

