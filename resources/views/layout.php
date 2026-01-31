<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <!-- SEO Generated -->
    <?= \App\Services\SeoService::getInstance()->render() ?>
    <!-- End SEO -->
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --color-primary:#5B6BD5;
            --color-primary-hover:#4F5FCC;
            --color-light-blue:#C9D0FF;
            --color-cyan:#59C7DF;
            --color-success:#5BC08A;
            --color-warning:#F4C15D;
            --color-heading:#2F3045;
            --color-secondary:#6B6F8D;
            --color-muted:#A8AEC4;
            --color-page-bg:#F0F1F6;
            --color-white:#FFFFFF;
            --color-border:#E3E5ED;
            --color-input-bg:#F7F8FC;
            --color-sidebar-bg:#F5F6FA;
            --color-active-menu-bg:#E9ECFF;
        }
        body{font-family:'Inter',sans-serif;background-color:var(--color-page-bg);color:var(--color-heading)}
        a{color:var(--color-secondary)}
        a:hover{color:var(--color-primary)}
        .bg-gray-50{background-color:var(--color-page-bg)!important}
        .bg-gray-100{background-color:var(--color-input-bg)!important}
        .bg-gray-200{background-color:var(--color-border)!important}
        .bg-white{background-color:var(--color-white)!important}
        .bg-blue-50,.hover\:bg-blue-50:hover,.bg-indigo-50,.bg-purple-50{background-color:var(--color-active-menu-bg)!important}
        .text-blue-700,.text-blue-600,.text-indigo-700,.text-purple-700{color:var(--color-primary)!important}
        .bg-blue-600{background-color:var(--color-primary)!important}
        .hover\:bg-blue-700:hover{background-color:var(--color-primary-hover)!important}
        .bg-green-600{background-color:var(--color-success)!important}
        .hover\:bg-green-700:hover{background-color:var(--color-success)!important}
        .bg-orange-600,.bg-orange-500{background-color:var(--color-primary)!important}
        .hover\:bg-orange-700:hover,.hover\:bg-orange-600:hover{background-color:var(--color-primary-hover)!important}
        .text-orange-600,.text-orange-500{color:var(--color-primary)!important}
        .bg-orange-100,.bg-orange-200{background-color:var(--color-active-menu_bg,var(--color-active_menu_bg));background-color:var(--color-active-menu-bg)!important}
        .border-orange-300,.border-orange-200{border-color:var(--color-border)!important}
        .bg-gray-800{background-color:var(--color-heading)!important}
        .text-gray-900,.text-gray-800{color:var(--color-heading)!important}
        .text-gray-700,.text-gray-600{color:var(--color-secondary)!important}
        .text-gray-500{color:var(--color-muted)!important}
        .placeholder-gray-500::placeholder{color:var(--color-muted)!important}
        .border-gray-100,.border-gray-200,.border-gray-300{border-color:var(--color-border)!important}
        input,select,textarea{background-color:var(--color-input-bg);border-color:var(--color-border);color:var(--color-heading)}
        input:focus,select:focus,textarea:focus{outline:none;border-color:var(--color-primary)}
        .hover\:text-orange-600:hover,.hover\:text-green-700:hover{color:var(--color-primary)!important}
        .text-green-500{color:var(--color-success)!important}
        [class*="bg-gradient"]{background-image:none!important}
    </style>
    <?= $extra_head ?? '' ?>
</head>
<body class="bg-gray-50 antialiased text-gray-800" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 800)">
    <!-- Skeleton Loader -->
    <div x-show="!loaded" x-transition.opacity.duration.500ms class="fixed inset-0 bg-white z-50 flex flex-col overflow-hidden">
        <!-- Header Skeleton -->
        <div class="h-20 border-b border-gray-100 flex items-center px-6 lg:px-[7.5rem] justify-between bg-white shrink-0">
            <div class="w-40 h-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="hidden md:flex gap-8">
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="flex gap-4">
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
            </div>
        </div>
        
        <!-- Content Skeleton -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="h-8 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                    <div class="h-64 bg-gray-100 rounded-xl animate-pulse"></div>
                    <div class="space-y-3">
                        <div class="h-4 bg-gray-100 rounded w-full animate-pulse"></div>
                        <div class="h-4 bg-gray-100 rounded w-full animate-pulse"></div>
                        <div class="h-4 bg-gray-100 rounded w-5/6 animate-pulse"></div>
                    </div>
                </div>
                <!-- Sidebar Area -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="h-40 bg-gray-100 rounded-xl animate-pulse"></div>
                    <div class="h-40 bg-gray-100 rounded-xl animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
    <?php 
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'; 
    $base = $base ?? '/';
    if ($base === '/' || $base === '') {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $base = rtrim($scriptDir, '/\\') . '/';
    }
    ?>
    <?php $isBlog = strpos($path, '/blog') === 0; ?>
    <?php if ($isBlog): ?>
        <?php
            $menuCategories = $categoriesMenu ?? ($categories ?? []);
            $catByName = [];
            if (!empty($menuCategories) && is_array($menuCategories)) {
                foreach ($menuCategories as $c) {
                    $key = strtolower(trim($c['name'] ?? ''));
                    if ($key !== '') $catByName[$key] = $c;
                }
            }
            $topLinks = [
                'Home' => '/blog',
                'Career Advice' => !empty($catByName['career advice']) ? '/blog/category/' . htmlspecialchars($catByName['career advice']['slug']) : '/blog',
                'Interview Questions' => !empty($catByName['interview questions']) ? '/blog/category/' . htmlspecialchars($catByName['interview questions']['slug']) : '/blog',
                'Appraisals' => !empty($catByName['appraisals']) ? '/blog/category/' . htmlspecialchars($catByName['appraisals']['slug']) : '/blog',
                'Insights' => !empty($catByName['insights']) ? '/blog/category/' . htmlspecialchars($catByName['insights']['slug']) : '/blog',
                'Interview Advice' => !empty($catByName['interview advice']) ? '/blog/category/' . htmlspecialchars($catByName['interview advice']['slug']) : '/blog',
            ];
            $techSubs = [
                'Mobile Applications',
                'Frontend',
                'Backend',
                'DevOps',
                'Data Science',
                'Quality Assurance',
                'Project Management',
                'SAP/Oracle',
                'UI/UX Design',
            ];
        ?>
        <nav class="bg-white border-b sticky top-0 z-50" x-data="{ techOpen:false, mobileOpen:false, mobileTech:false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
<a href="<?= $base ?>blog" class="flex items-center">
    <img
        src="<?= $base ?>uploads/Mindware-infotech.png"
        alt="Mindware Infotech"
        class="h-12 w-auto"
    />
</a>
                    <button class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-gray-100"
                            @click="mobileOpen = !mobileOpen" aria-label="Toggle menu">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden md:flex items-center gap-6 text-sm font-semibold">
                        <a href="<?= $topLinks['Home'] ?>" class="text-gray-800 hover:text-green-700">HOME</a>
                        <a href="<?= $topLinks['Career Advice'] ?>" class="text-gray-800 hover:text-green-700">CAREER ADVICE</a>
                        <div class="relative">
                            <button @click="techOpen=!techOpen" @click.outside="techOpen=false" class="flex items-center gap-1 text-gray-800 hover:text-green-700">
                                TECHNOLOGY
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="techOpen" class="absolute left-0 mt-3 w-64 bg-white shadow-xl border rounded-xl z-50 p-2">
                                <?php foreach ($techSubs as $name): ?>
                                    <?php $lower = strtolower($name); ?>
                                    <?php if (!empty($catByName[$lower])): ?>
                                        <a href="/blog/category/<?= htmlspecialchars($catByName[$lower]['slug']) ?>" class="block px-3 py-2 text-gray-800 hover:text-green-700">
                                            <?= htmlspecialchars($name) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="<?= $topLinks['Interview Questions'] ?>" class="text-gray-800 hover:text-green-700">INTERVIEW QUESTIONS</a>
                        <a href="<?= $topLinks['Appraisals'] ?>" class="text-gray-800 hover:text-green-700">APPRAISALS</a>
                        <a href="<?= $topLinks['Insights'] ?>" class="text-gray-800 hover:text-green-700">INSIGHTS</a>
                        <a href="<?= $topLinks['Interview Advice'] ?>" class="text-gray-800 hover:text-green-700">INTERVIEW ADVICE</a>
                    </div>
                </div>
            </div>
            <div class="md:hidden border-t" x-show="mobileOpen" x-transition>
                <div class="px-4 py-3 space-y-2 text-sm font-semibold">
                    <a href="<?= $topLinks['Home'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">HOME</a>
                    <a href="<?= $topLinks['Career Advice'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">CAREER ADVICE</a>
                    <button class="w-full flex items-center justify-between px-2 py-2 text-gray-800 hover:text-green-700"
                            @click="mobileTech = !mobileTech">
                        <span>TECHNOLOGY</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="mobileTech" x-transition class="ml-2 space-y-1">
                        <?php foreach ($techSubs as $name): ?>
                            <?php $lower = strtolower($name); ?>
                            <?php if (!empty($catByName[$lower])): ?>
                                <a href="/blog/category/<?= htmlspecialchars($catByName[$lower]['slug']) ?>" class="block px-2 py-1 text-gray-700 hover:text-green-700"><?= htmlspecialchars($name) ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= $topLinks['Interview Questions'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">INTERVIEW QUESTIONS</a>
                    <a href="<?= $topLinks['Appraisals'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">APPRAISALS</a>
                    <a href="<?= $topLinks['Insights'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">INSIGHTS</a>
                    <a href="<?= $topLinks['Interview Advice'] ?>" class="block px-2 py-2 text-gray-800 hover:text-green-700">INTERVIEW ADVICE</a>
                </div>
            </div>
        </nav>
    <?php else: ?>
        <?php $GLOBALS['layout_header'] = 1; include __DIR__ . '/include/header.php'; ?>
    <?php endif; ?>
    <main>
        <?= $content ?? '' ?>
    </main>
    <?php if ($isBlog): ?>
        <footer class="bg-white border-t mt-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h4 class="font-semibold mb-3">Categories</h4>
                        <ul class="space-y-2">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <li>
                                        <a class="text-gray-700 hover:text-green-700" href="/blog/category/<?= htmlspecialchars($c['slug'] ?? '') ?>">
                                            <?= htmlspecialchars($c['name'] ?? '') ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a class="text-gray-700 hover:text-green-700" href="/blog">Blog</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-3">Useful Links</h4>
                        <ul class="space-y-2">
                            <li><a class="text-gray-700 hover:text-green-700" href="/terms">Terms of Service</a></li>
                            <li><a class="text-gray-700 hover:text-green-700" href="/privacy">Privacy Policy</a></li>
                            <li><a class="text-gray-700 hover:text-green-700" href="/contact">Contact Us</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-3">Latest Articles</h4>
                        <ul class="space-y-2">
                            <?php if (!empty($latestArticles)): ?>
                                <?php foreach ($latestArticles as $la): ?>
                                    <li>
                                        <a class="text-gray-700 hover:text-green-700" href="/blog/<?= htmlspecialchars($la['slug'] ?? '') ?>">
                                            <?= htmlspecialchars($la['title'] ?? '') ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-gray-500">No recent articles</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    <?php else: ?>
        <?php include __DIR__ . '/include/footer.php'; ?>
    <?php endif; ?>
</body>
</html>
