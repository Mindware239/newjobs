<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable $e) {}

use App\Core\Database;
use App\Models\User;
use App\Services\NotificationService;

$args = $argv;
array_shift($args);

$opts = [
    'user_id' => null,
    'email' => null,
    'token' => null,
    'title' => 'Test Push',
    'message' => 'This is a test push notification',
    'link' => null,
];

foreach ($args as $arg) {
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', substr($arg, 2), 2);
        $key = $parts[0];
        $val = $parts[1] ?? null;
        if (array_key_exists($key, $opts)) {
            $opts[$key] = $val;
        } elseif ($key === 'help') {
            echo "Usage:\n";
            echo "  php scripts/test_push.php --user_id=123 --token=FCM_TOKEN [--title=...] [--message=...] [--link=/path]\n";
            echo "  php scripts/test_push.php --email=user@example.com --token=FCM_TOKEN [--title=...] [--message=...] [--link=/path]\n";
            echo "If --token is omitted, existing users.fcm_token will be used.\n";
            exit(0);
        }
    }
}

if (empty($opts['user_id']) && empty($opts['email'])) {
    echo "Provide --user_id or --email\n";
    exit(1);
}

$db = Database::getInstance();
$user = null;

if (!empty($opts['user_id'])) {
    $user = User::find((int)$opts['user_id']);
} else {
    $row = $db->fetchOne("SELECT id FROM users WHERE email = :email LIMIT 1", ['email' => $opts['email']]);
    if ($row) {
        $user = User::find((int)$row['id']);
    }
}

if (!$user) {
    echo "User not found\n";
    exit(1);
}

if (!empty($opts['token'])) {
    try {
        $db->query("UPDATE users SET fcm_token = :token WHERE id = :id", [
            'token' => $opts['token'],
            'id' => (int)$user->id
        ]);
        echo "Set fcm_token for user {$user->id}\n";
    } catch (\Throwable $t) {
        echo "Failed to set fcm_token: " . $t->getMessage() . "\n";
        exit(1);
    }
}

$link = $opts['link'] ?? null;
$title = $opts['title'] ?? 'Test Push';
$message = $opts['message'] ?? 'This is a test push notification';

$ok = NotificationService::sendPush((int)$user->id, $title, $message, $link);
echo $ok ? "Push sent\n" : "Push failed\n";

