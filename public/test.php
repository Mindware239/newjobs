<?php
/**
 * Simple test page to verify the application is working
 */
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Ignore
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Job Portal - Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>Job Portal - System Check</h1>
    
    <div class="info">
        <h2>System Information</h2>
        <p><strong>PHP Version:</strong> <?= PHP_VERSION ?></p>
        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
        <p><strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></p>
    </div>

    <div class="info">
        <h2>Environment Check</h2>
        <?php
        $checks = [
            'Database Connection' => function() {
                try {
                    $host = $_ENV['DB_HOST'] ?? 'localhost';
                    $dbname = $_ENV['DB_NAME'] ?? 'mindwareinfotech';
                    $user = $_ENV['DB_USER'] ?? 'root';
                    $pass = $_ENV['DB_PASSWORD'] ?? '';
                    $port = $_ENV['DB_PORT'] ?? '3306';
                    
                    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                    $pdo = new PDO($dsn, $user, $pass);
                    $pdo->exec("USE `$dbname`");
                    return ['status' => true, 'message' => 'Connected successfully'];
                } catch (Exception $e) {
                    return ['status' => false, 'message' => $e->getMessage()];
                }
            },
            'Redis Extension' => function() {
                return extension_loaded('redis') 
                    ? ['status' => true, 'message' => 'Redis extension loaded']
                    : ['status' => false, 'message' => 'Redis extension not loaded (optional)'];
            },
            '.env File' => function() {
                return file_exists(__DIR__ . '/../.env')
                    ? ['status' => true, 'message' => '.env file exists']
                    : ['status' => false, 'message' => '.env file not found'];
            },
            'Vendor Autoload' => function() {
                return file_exists(__DIR__ . '/../vendor/autoload.php')
                    ? ['status' => true, 'message' => 'Composer autoload exists']
                    : ['status' => false, 'message' => 'Run: composer install'];
            }
        ];

        foreach ($checks as $name => $check) {
            $result = $check();
            $class = $result['status'] ? 'success' : 'error';
            echo "<p class='$class'><strong>$name:</strong> {$result['message']}</p>";
        }
        ?>
    </div>

    <div class="info">
        <h2>Available Endpoints</h2>
        <p><strong>Base URL:</strong> <?= 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['PHP_SELF']) ?></p>
        <ul>
            <li>POST /register - Register new user</li>
            <li>POST /login - Login</li>
            <li>GET /employer/dashboard - Dashboard (requires login)</li>
            <li>GET /employer/jobs - List jobs (requires login)</li>
        </ul>
        <p><em>Note: This is an API application. Use Postman or similar tool to test endpoints.</em></p>
    </div>

    <div class="info">
        <h2>Quick Start</h2>
        <ol>
            <li>Make sure MySQL is running in XAMPP</li>
            <li>Create database: <code>mindwareinfotech</code></li>
            <li>Run: <code>php scripts/migrate.php</code></li>
            <li>Test API endpoints using Postman or cURL</li>
        </ol>
    </div>
</body>
</html>

