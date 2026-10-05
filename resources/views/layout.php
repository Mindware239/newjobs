<!DOCTYPE html>
<html lang="en">
<head>
    <?php if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    } ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <!-- SEO Generated -->
    <?php
    $__seo = \App\Services\SeoService::getInstance();
    if (!empty($meta) && is_array($meta) && !empty($meta['title'])) {
        // Pages that compute their own meta (e.g. blog posts)
        $__base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
        $__canon = (string)($meta['canonical'] ?? '');
        $__title = (string)$meta['title'];
        $__seo->setMeta(array_filter([
            'title' => stripos($__title, 'Jobsence') === false ? $__title . ' | Jobsence' : $__title,
            'h1' => (string)$meta['title'],
            'description' => (string)($meta['description'] ?? ''),
            'canonical' => $__canon === '' ? null : (preg_match('#^https?://#', $__canon) ? $__canon : $__base . '/' . ltrim($__canon, '/')),
        ], static fn($v) => $v !== null && $v !== ''));
    } elseif (!$__seo->isResolved() && !empty($title) && is_string($title)) {
        // Fall back to the page title the controller passed, instead of the generic site title
        $__seo->setMeta(['title' => stripos($title, 'Jobsence') === false ? $title . ' | Jobsence' : $title]);
    }
    ?>
    <?= $__seo->render() ?>
    <!-- End SEO -->
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="/js/consent-manager.js"></script>
    <script defer src="/js/script-loader.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">
    <style>
        :root {
            --color-primary: #f05537;
            --color-primary-hover: #FF6A3D;
            --color-light-blue: #fff1ed;
            --color-cyan: #FF6A3D;
            --color-success: #5BC08A;
            --color-warning: #F4C15D;
            --color-heading: #1a1a1a;
            --color-secondary: #4b5563;
            --color-muted: #9ca3af;
            --color-page-bg: #FFFFFF;
            --color-white: #FFFFFF;
            --color-border: #e5e7eb;
            --color-input-bg: #f9fafb;
            --color-sidebar-bg: #f9fafb;
            --color-active-menu-bg: #fff1ed;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            font-weight: 600;
            background-color: var(--color-page-bg);
            color: var(--color-heading)
        }

        /* Override potential blue/purple/indigo colors from Tailwind or other sources */
        .text-primary, .text-primary-600, .text-primary, .text-primary, .text-primary, .text-primary {
            color: var(--color-primary) !important
        }

        .bg-primary, .bg-primary-600, .bg-primary, .bg-primary, .bg-primary, .bg-primary {
            background-color: var(--color-primary) !important
        }

        .hover\:bg-primary-600:hover, .hover\:bg-primary:hover, .hover\:bg-primary:hover, .hover\:bg-primary:hover {
            background-color: var(--color-primary-hover) !important
        }

        .bg-primary-50 {
            background-color: var(--color-active-menu-bg) !important
        }

        .focus\:ring-primary:focus, .focus\:ring-primary:focus, .focus\:ring-primary:focus {
            --tw-ring-color: rgba(240, 85, 55, 0.2) !important;
            border-color: var(--color-primary) !important;
        }

        a {
            color: var(--color-secondary);
            transition: color 0.3s ease;
        }

        a:hover {
            color: var(--color-primary);
        }

        .bg-gray-50 {
            background-color: var(--color-page-bg) !important
        }

        .bg-gray-100 {
            background-color: var(--color-input-bg) !important
        }

        .bg-gray-200 {
            background-color: var(--color-border) !important
        }

        .bg-white {
            background-color: var(--color-white) !important
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isBlogRoute = preg_match('#^/blog(?:/|$)#', $requestPath) === 1;
include __DIR__ . ($isBlogRoute ? '/include/blog_header.php' : '/include/header.php');
?>
<main>
    <?php echo $content ?? ''; ?>
</main>
<?php include __DIR__ . '/include/footer.php'; ?>
<script>
    // Global script to fix any dynamic blue elements
    document.addEventListener('DOMContentLoaded', () => {
        const primaryColor = '#f05537';
        const primaryHover = '#FF6A3D';
        
        // Fix any inline styles or problematic classes
        const fixColors = () => {
            document.querySelectorAll('[class*="blue-"], [class*="indigo-"], [class*="purple-"]').forEach(el => {
                if (el.classList.contains('text-primary') || el.classList.contains('text-primary')) {
                    el.style.color = primaryColor;
                }
                if (el.classList.contains('bg-primary') || el.classList.contains('bg-primary')) {
                    el.style.backgroundColor = primaryColor;
                }
            });
        };
        
        fixColors();
        // Run again if content changes (e.g. Alpine.js renders)
        const observer = new MutationObserver(fixColors);
        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>
</body>
</html>



