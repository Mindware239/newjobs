<!DOCTYPE html>
<html lang="en">
<head>
    <?php if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); } ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= $title ?? 'Admin Panel' ?> - Job Portal Admin</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        :root{
            --color-primary:#5B6BD5;
            --color-primary-hover:#4F5FCC;
            --color-secondary:#6B6F8D;
            --color-heading:#2F3045;
            --color-page-bg:#F0F1F6;
            --color-white:#FFFFFF;
            --color-border:#E3E5ED;
            --color-input-bg:#F7F8FC;
            --color-active-menu-bg:#E9ECFF;
        }
        body{background-color:var(--color-page-bg);color:var(--color-heading)}
        .bg-white{background-color:var(--color-white)!important}
        .border-gray-200,.border-gray-100,.border-gray-300{border-color:var(--color-border)!important}
        .text-gray-900,.text-gray-800{color:var(--color-heading)!important}
        .text-gray-700,.text-gray-600{color:var(--color-secondary)!important}
        .bg-blue-600{background-color:var(--color-primary)!important}
        .hover\:bg-blue-700:hover{background-color:var(--color-primary-hover)!important}
        .bg-red-600{background-color:var(--color-primary)!important}
        .hover\:bg-red-700:hover{background-color:var(--color-primary-hover)!important}
        .bg-blue-50,.hover\:bg-blue-50:hover,.bg-indigo-50,.bg-emerald-50,.bg-teal-50,.bg-purple-50,.bg-sky-50{background-color:var(--color-active-menu-bg)!important}
        .text-blue-700,.text-indigo-700,.text-emerald-700,.text-teal-700,.text-purple-700,.text-sky-700{color:var(--color-primary)!important}
        [class*="bg-gradient"]{background-image:none!important}
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 text-gray-900 shadow-sm transform transition-transform duration-300 ease-in-out"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         x-show="sidebarOpen"
         x-cloak>
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 bg-white">
                <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 bg-white">
                <div class="px-3 space-y-1">
                    <a href="/admin/dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="/admin/candidates" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/candidates') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Candidates
                    </a>
                     <a href="/admin/interviews" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/interviews') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                        Interviews 
                    </a>

                    <a href="/admin/employers" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/employers') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', 'kyc_status=pending') === false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Employers
                    </a>

                    <a href="/admin/employers?kyc_status=pending" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/employers') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', 'kyc_status=pending') !== false) ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        KYC Review
                    </a>

                    <a href="/admin/jobs" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/jobs') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Jobs
                    </a>
                    <a href="/admin/companies/featured" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/companies/featured') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Featured Companies
                    </a>
                    <a href="/admin/job-categories" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/job-categories') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Job Categories
                    </a> 
                            
                    

                    <a href="/admin/testimonials" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/testimonials') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/create') === false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6M5 20l4-4h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12z"></path>
                        </svg>
                        Testimonials
                    </a>
                    
                    <a href="/admin/blog" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog/create') === false) ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7v14H5V3h10l4 4zM9 9h6M9 13h6M9 17h4"></path>
                        </svg>
                        Blogs
                    </a>
                    
                    <a href="/admin/blog-categories" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-categories') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-categories/create') === false) ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8"></path>
                        </svg>
                        Blog Categories
                    </a>
                    <a href="/admin/blog-categories/create" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-categories/create') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Blog Category
                    </a>
                    <a href="/admin/blog-tags" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-tags') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-tags/create') === false) ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6"></path>
                        </svg>
                        Blog Tags
                    </a>
                    <a href="/admin/blog-tags/create" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/blog-tags/create') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Blog Tag
                    </a>



                    <a href="/admin/payments" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/payments') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Payments
                    </a>

                    <a href="/admin/subscriptions" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/subscriptions') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Subscriptions
                    </a>
                    
                    <a href="/admin/bulk-emails" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/bulk-emails') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm6 8a6 6 0 10-12 0h12zM6 8V6a2 2 0 012-2h8a2 2 0 012 2v2"></path>
                        </svg>
                        Bulk Emails
                    </a>
                    <a href="/admin/notification-templates" class="flex items-center px-6 py-3 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200 <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/notifications') !== false ? 'bg-indigo-50 text-indigo-600' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
    <span class="font-medium">Notifications</span>
</a>

                    <a href="/admin/reports" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/reports') !== false ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Reports
                    </a>

                    <a href="/admin/settings" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50 hover:text-gray-800 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') !== false ? 'bg-gray-50 text-gray-800 font-semibold' : '' ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </div>
            </nav>

            <!-- User Info -->
            <div class="border-t border-gray-200 p-4 bg-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white">
                            <span class="text-sm font-medium"><?= strtoupper(substr((isset($user) && is_object($user) && isset($user->email)) ? (string)$user->email : ((isset($user) && is_array($user) && isset($user['email'])) ? (string)$user['email'] : 'A'), 0, 1)) ?></span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium"><?= htmlspecialchars((isset($user) && is_object($user) && isset($user->email)) ? (string)$user->email : ((isset($user) && is_array($user) && isset($user['email'])) ? (string)$user['email'] : 'Admin')) ?></p>
                        <p class="text-xs text-gray-500"><?= ucfirst((isset($user) && is_object($user) && isset($user->role)) ? (string)$user->role : ((isset($user) && is_array($user) && isset($user['role'])) ? (string)$user['role'] : 'admin')) ?></p>
                    </div>
                </div>
                <a href="/admin/logout" class="mt-3 block w-full text-center px-4 py-2 text-sm text-gray-700 hover:text-white hover:bg-red-600 rounded-md">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64">
        <!-- Top Bar -->
        <div class="sticky top-0 z-40 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex-1 flex justify-end items-center space-x-4">
                    <span class="text-sm text-gray-600">Welcome, <?= htmlspecialchars((isset($user) && is_object($user) && isset($user->email)) ? (string)$user->email : ((isset($user) && is_array($user) && isset($user['email'])) ? (string)$user['email'] : 'Admin')) ?></span>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>
    </div>

    <!-- Sidebar Overlay (Mobile) -->
    <div x-show="sidebarOpen && window.innerWidth < 1024" 
         @click="sidebarOpen = false"
         x-cloak
         class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"></div>
</body>
</html>
