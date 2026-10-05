<?php
$base = $base ?? '/';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/blog';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/blog';
?>
<style>
    [x-cloak] { display: none !important; }
    .blog-top-header {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }
    .blog-nav-link {
        color: #111827;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 0.5rem 0.25rem;
        transition: color 0.2s ease;
        white-space: nowrap;
    }
    .blog-nav-link:hover,
    .blog-nav-link.active {
        color: #f05537;
    }
    .blog-nav-dropdown {
        position: relative;
    }
    .blog-nav-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        min-width: 140px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 0.35rem;
        z-index: 60;
        display: none;
    }
    .blog-nav-dropdown:hover .blog-nav-dropdown-menu {
        display: block;
    }
    .blog-nav-dropdown-item {
        display: block;
        font-size: 12px;
        color: #374151;
        padding: 0.45rem 0.55rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .blog-nav-dropdown-item:hover {
        color: #f05537;
        background: #fff1ed;
    }
    .blog-mobile-link {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: #111827;
        padding: 0.55rem 0;
    }
    .blog-mobile-link:hover {
        color: #f05537;
    }
</style>

<header class="blog-top-header sticky top-0 z-50" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-6">
        <a href="<?= htmlspecialchars($base) ?>" class="shrink-0 flex items-center">
            <img src="<?= htmlspecialchars(rtrim($base, '/') . '/uploads/jobsence.png') ?>" alt="Jobsence" class="h-10 w-auto object-contain">
        </a>

        <nav class="hidden md:flex items-center gap-5 lg:gap-6">
            <a href="<?= htmlspecialchars($base) ?>" class="blog-nav-link <?= ($path === '/' ? 'active' : '') ?>">Home</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/career-advice') ?>" class="blog-nav-link">Career Advice</a>

            <div class="blog-nav-dropdown">
                <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/technology') ?>" class="blog-nav-link inline-flex items-center gap-1">
                    Technology
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                <div class="blog-nav-dropdown-menu">
                    <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/devops') ?>" class="blog-nav-dropdown-item">DevOps</a>
                    <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/data-science') ?>" class="blog-nav-dropdown-item">Data Science</a>
                </div>
            </div>

            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/interview-questions') ?>" class="blog-nav-link">Interview Questions</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/appraisals') ?>" class="blog-nav-link">Appraisals</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/insights') ?>" class="blog-nav-link">Insights</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/interview-advice') ?>" class="blog-nav-link">Interview Advice</a>
        </nav>

        <button
            type="button"
            class="md:hidden inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-700 hover:text-primary hover:border-primary"
            @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen.toString()"
            aria-label="Toggle blog menu"
        >
            <svg x-show="!mobileOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="mobileOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="md:hidden border-t border-gray-100 bg-white" x-show="mobileOpen" x-collapse x-cloak>
        <nav class="max-w-7xl mx-auto px-4 py-3">
            <a href="<?= htmlspecialchars($base) ?>" class="blog-mobile-link">Home</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/career-advice') ?>" class="blog-mobile-link">Career Advice</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/technology') ?>" class="blog-mobile-link">Technology</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/devops') ?>" class="blog-mobile-link pl-4">DevOps</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/data-science') ?>" class="blog-mobile-link pl-4">Data Science</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/interview-questions') ?>" class="blog-mobile-link">Interview Questions</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/appraisals') ?>" class="blog-mobile-link">Appraisals</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/insights') ?>" class="blog-mobile-link">Insights</a>
            <a href="<?= htmlspecialchars(rtrim($base, '/') . '/blog/category/interview-advice') ?>" class="blog-mobile-link">Interview Advice</a>
        </nav>
    </div>
</header>
