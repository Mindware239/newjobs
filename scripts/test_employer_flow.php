<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;
use App\Services\AuthService;
use App\Models\User;

try {
    $dotenv = Dotenv\Dotenv::createUnsafeMutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

$email = 'test_employer_' . time() . '@example.com';
$password = 'Password123!';
$data = [
    'full_name' => 'Test Employer',
    'company_name' => 'Test Company',
    'phone' => '1234567890',
    'email' => $email,
    'password' => $password,
    'confirm_password' => $password
];

echo "Registering employer: $email\n";

try {
    // Mock Request/Response for registerEmployer
    // Actually let's just use AuthService or logic from AuthController
    
    $user = new User();
    $user->fill([
        'name' => $data['full_name'],
        'email' => $data['email'],
        'role' => 'employer',
        'status' => 'active', // Should be active as per my read
        'phone' => $data['phone']
    ]);
    $user->setPassword($data['password']);
    
    if ($user->save()) {
        echo "User saved successfully. ID: {$user->id}\n";
    } else {
        echo "Failed to save user.\n";
        exit;
    }

    $authService = new AuthService();
    $loginUser = $authService->login($email, $password);
    
    if ($loginUser) {
        echo "Login successful for $email\n";
        echo "User Status: {$loginUser->status}\n";
        echo "User Role: {$loginUser->role}\n";
    } else {
        echo "Login FAILED for $email\n";
        // Check why
        $u = User::where('email', '=', $email)->first();
        if (!$u) {
            echo "User NOT found in DB even after save!\n";
        } else {
            echo "User found in DB. Status: {$u->status}. Verifying password...\n";
            if ($u->verifyPassword($password)) {
                echo "Password verification successful in script.\n";
            } else {
                echo "Password verification FAILED in script.\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
