<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($title ?? 'Interview') ?> - Mindware Infotech</title>
    <!-- CRITICAL: Allow Jitsi to load resources -->
    <meta http-equiv="Content-Security-Policy" content="frame-src 'self' https://*.jit.si https://meet.jit.si https://*.jitsi.net https://*.jitsi.org; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.jit.si https://meet.jit.si https://*.jitsi.net https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://*.jit.si https://meet.jit.si https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https: blob:; media-src 'self' https://*.jit.si https://meet.jit.si https://*.jitsi.net; connect-src 'self' https://*.jit.si https://meet.jit.si https://*.jitsi.net wss://*.jit.si wss://meet.jit.si; worker-src 'self' blob:;">
    <link href="/css/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        html, body { height: 100%; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100">
    <?php echo $content ?? ''; ?>
</body>
</html>

