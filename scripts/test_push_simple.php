<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

use App\Services\NotificationService;
use App\Core\Database;

$userId = 30; // From user screenshot
echo "Testing push notification for User ID: $userId\n";

// Ensure token is active
$db = Database::getInstance();
// Check if user has any token
$row = $db->fetchOne("SELECT token FROM user_push_tokens WHERE user_id = :id AND is_active = 1", ['id' => $userId]);

if (!$row) {
    echo "No active token found for user $userId. Please allow notifications on the frontend first.\n";
    // Let's try to find ANY token for this user
    $anyToken = $db->fetchOne("SELECT token FROM user_push_tokens WHERE user_id = :id", ['id' => $userId]);
    if ($anyToken) {
        echo "Found inactive token. Activating it...\n";
        $db->query("UPDATE user_push_tokens SET is_active = 1 WHERE user_id = :id", ['id' => $userId]);
        $row = ['token' => $anyToken['token']];
    } else {
        echo "No tokens at all for user $userId.\n";
        exit(1);
    }
}

$token = $row['token'];
echo "Found token: " . substr($token, 0, 20) . "...\n";

// Send Push
echo "Sending push notification...\n";
$success = NotificationService::sendPush(
    $userId, 
    "Test Notification from Trae", 
    "This is a test message to verify the new credentials.", 
    "/candidate/dashboard"
);

if ($success) {
    echo "SUCCESS: Push notification sent to FCM.\n";
} else {
    echo "FAILURE: Failed to send push notification. Check error logs.\n";
}
