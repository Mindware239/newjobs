<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($title ?? 'Candidate Dashboard') ?> - Mindware Infotech</title>
    <link rel="icon" type="image/png" href="/uploads/Mindware-infotech.png">
    <link href="/css/output.css" rel="stylesheet">
    <script>
        // Define candidateNav function before Alpine.js loads
        function candidateNav() {
            return {
                // Alpine.js data for candidate pages
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <?= $extra_head ?? '' ?>
    <script>
        (function initWebPush(){
            const cfg = {
                apiKey: "<?= htmlspecialchars($_ENV['FCM_WEB_API_KEY'] ?? '') ?>",
                projectId: "<?= htmlspecialchars($_ENV['FCM_WEB_PROJECT_ID'] ?? '') ?>",
                messagingSenderId: "<?= htmlspecialchars($_ENV['FCM_WEB_MESSAGING_SENDER_ID'] ?? '') ?>",
                appId: "<?= htmlspecialchars($_ENV['FCM_WEB_APP_ID'] ?? '') ?>",
                vapidKey: "<?= htmlspecialchars($_ENV['FCM_VAPID_KEY'] ?? '') ?>"
            };
            const hasCfg = cfg.apiKey && cfg.projectId && cfg.messagingSenderId && cfg.appId;
            if (!hasCfg) { console.warn('FCM web config missing; push disabled'); return; }
            if (!('serviceWorker' in navigator) || !window.Notification) { return; }
            const loadScript = (src) => new Promise((res, rej) => { const s=document.createElement('script'); s.src=src; s.onload=res; s.onerror=rej; document.head.appendChild(s); });
            Promise.resolve()
                .then(() => loadScript('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js'))
                .then(() => loadScript('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js'))
                .then(async () => {
                    try {
                        firebase.initializeApp({
                            apiKey: cfg.apiKey,
                            projectId: cfg.projectId,
                            messagingSenderId: cfg.messagingSenderId,
                            appId: cfg.appId
                        });
                        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                        const messaging = firebase.messaging();
                        await Notification.requestPermission();
                        const token = await messaging.getToken({ vapidKey: cfg.vapidKey, serviceWorkerRegistration: registration });
                        if (token) {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            await fetch('/api/push/register', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                                body: JSON.stringify({ token })
                            });
                        }
                    } catch (err) { console.warn('Push init failed', err); }
                });
        })();
    </script>
</head>
<body class="bg-gray-50 antialiased text-gray-800">
    <div x-data="candidateNav()" x-cloak>
        <!-- Shared Header -->
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>
        
        <!-- Main Content -->
        <main>
            <?php 
            if (isset($content) && !empty($content)) {
                echo $content;
            } else {
                // Content should be set by the view file
                echo '';
            }
            ?>
        <?php require __DIR__ . '/../include/footer.php'; ?>
    </div>
</body>
</html>
