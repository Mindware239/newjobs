<?php
require_once 'app/Models/Model.php';
require_once 'app/Models/Candidate.php';
require_once 'app/Core/Database.php';

// Mock $_ENV for Database constructor
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'mindwareinfotech';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASSWORD'] = '';

try {
    $userId = 99; // Assuming user_id 99 exists and has a candidate profile
    $candidate = \App\Models\Candidate::findByUserId($userId);
    
    if (!$candidate) {
        echo "Candidate not found for user_id $userId\n";
        exit;
    }
    
    $oldName = $candidate->full_name;
    $newName = "Test Name " . rand(1000, 9999);
    
    echo "Old Name: $oldName\n";
    echo "Updating to: $newName\n";
    
    $candidate->full_name = $newName;
    $result = $candidate->save();
    
    echo "Save Result: " . ($result ? "Success" : "Failure") . "\n";
    
    // Fetch again to verify
    $verifyCandidate = \App\Models\Candidate::findByUserId($userId);
    echo "Verified Name: " . $verifyCandidate->full_name . "\n";
    
    if ($verifyCandidate->full_name === $newName) {
        echo "VERIFIED: Data saved successfully.\n";
    } else {
        echo "FAILED: Data not saved.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
