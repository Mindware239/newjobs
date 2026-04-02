<?php
$appName = $_ENV['APP_NAME'] ?? 'Job Portal';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
$loginLink = rtrim($appUrl, '/') . '/bulk/login';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Bulk Uploader Account is Ready</title>
</head>
<body>
    <p>Hello <?= htmlspecialchars($name) ?>,</p>
    <p>A Bulk Uploader account has been created for you on <?= htmlspecialchars($appName) ?>.</p>
    <p>You can now log in and start uploading candidate resumes in bulk.</p>
    <p>
        <strong>Login URL:</strong> <a href="<?= htmlspecialchars($loginLink) ?>"><?= htmlspecialchars($loginLink) ?></a><br>
        <strong>Username:</strong> <?= htmlspecialchars($username) ?><br>
        <strong>Password:</strong> <?= htmlspecialchars($password) ?><br>
    </p>
    <p>Please keep these credentials safe. You can change your password after logging in.</p>
    <p>Thank you,</p>
    <p>The <?= htmlspecialchars($appName) ?> Team</p>
</body>
</html>
