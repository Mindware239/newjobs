<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$jitsiDomain = trim((string)($_ENV['JITSI_DOMAIN'] ?? 'meet.jit.si'));
$jitsiDomain = rtrim((string)preg_replace('#^https?://#i', '', $jitsiDomain), '/');
$jitsiDomain = preg_replace('/[^A-Za-z0-9.-]/', '', $jitsiDomain) ?: 'meet.jit.si';
if ($jitsiDomain === '' || strtolower($jitsiDomain) === 'your-jitsi-domain.com') {
    $jitsiDomain = 'meet.jit.si';
}
$jitsiHttps = 'https://' . htmlspecialchars($jitsiDomain, ENT_QUOTES);
$jitsiWss = 'wss://' . htmlspecialchars($jitsiDomain, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($title ?? 'Interview') ?> - Jobsence</title>
    <!-- Allow embedded Jitsi media, workers, websocket signaling, and screen-share support. -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; frame-src 'self' <?= $jitsiHttps ?> https://*.jit.si https://meet.jit.si https://*.jitsi.net https://*.jitsi.org https://sdk.cashfree.com https://api.cashfree.com https://sandbox.cashfree.com; child-src 'self' blob: <?= $jitsiHttps ?> https://*.jit.si https://meet.jit.si https://*.jitsi.net https://*.jitsi.org; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: <?= $jitsiHttps ?> https://*.jit.si https://meet.jit.si https://*.jitsi.net https://*.jitsi.org https://cdn.jsdelivr.net https://sdk.cashfree.com; style-src 'self' 'unsafe-inline' <?= $jitsiHttps ?> https://*.jit.si https://meet.jit.si https://fonts.googleapis.com; font-src 'self' data: <?= $jitsiHttps ?> https://fonts.gstatic.com https://*.jit.si https://meet.jit.si; img-src 'self' data: blob: https:; media-src 'self' blob: <?= $jitsiHttps ?> https://*.jit.si https://meet.jit.si https://*.jitsi.net; connect-src 'self' <?= $jitsiHttps ?> <?= $jitsiWss ?> https://*.jit.si https://meet.jit.si https://*.jitsi.net https://*.jitsi.org wss://*.jit.si wss://meet.jit.si wss://*.jitsi.net wss://*.jitsi.org https://api.cashfree.com https://sandbox.cashfree.com https://sdk.cashfree.com; form-action 'self' https://api.cashfree.com https://sandbox.cashfree.com; worker-src 'self' blob:;">
    <link href="/css/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Nunito Sans', sans-serif; font-weight: 600; }
        [x-cloak] { display: none !important; }
        html, body { height: 100%; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100">
    <?php echo $content ?? ''; ?>
</body>
</html>












