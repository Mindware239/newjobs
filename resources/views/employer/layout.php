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
    <title><?= $title ?? 'Employer Dashboard' ?> - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --success: 146 75% 45%;
            --color-active-menu-bg: #eef2ff;
        }

        .text-success {
            color: hsl(var(--success));
        }

        .bg-success-10 {
            background-color: hsla(var(--success), .12);
        }

        .border-success-20 {
            border-color: hsla(var(--success), .20);
        }

        .bg-blue-50,
        .hover\:bg-blue-50:hover,
        .bg-indigo-50,
        .bg-purple-50 {
            background-color: var(--color-active-menu-bg) !important;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom text-sm size (1.2rem) */
        .text-sm {
            font-size: 1.0rem !important;
            line-height: 1.5rem !important;
        }

        /* Custom font sizes for headings */
        .text-heading {
            font-size: 1.0rem;
            line-height: 1.5rem;
        }

        /* Responsive text sizes */
        @media (max-width: 640px) {
            .text-heading {
                font-size: 1.1rem;
                line-height: 1.4rem;
            }
        }
    </style>
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

<body class="bg-gray-50 antialiased text-gray-800" x-data="{ sidebarOpen: false, sidebarCollapsed: false, toggleSidebar() { if (window.innerWidth >= 1024) { this.sidebarCollapsed = !this.sidebarCollapsed; localStorage.setItem('employerSidebarCollapsed', JSON.stringify(this.sidebarCollapsed)); } else { this.sidebarOpen = !this.sidebarOpen; } } }" @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true" x-init="if (window.innerWidth >= 1024) { sidebarOpen = true; sidebarCollapsed = JSON.parse(localStorage.getItem('employerSidebarCollapsed') || 'false'); }" style="background-color: #f3f4f6; min-height: 100vh;">
    <!-- Top Navigation Bar -->
    <nav class="fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 shadow-sm h-16 transition-all duration-300 lg:pl-64" :style="(window.innerWidth >= 1024) ? ('padding-left:' + (sidebarCollapsed ? '5rem' : '16rem')) : ''">
        <div class="px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex justify-between items-center h-full">
                <div class="flex items-center flex-1 gap-4">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Search Bar (Global) -->
                    <div class="hidden md:flex flex-1 max-w-xl">
                        <div class="relative w-full group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#7283ff] transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                placeholder="Search candidates, jobs, or keywords..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#7283ff] focus:border-transparent sm:text-sm transition-all duration-200 hover:bg-gray-100 focus:shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Date Display -->
                    <div class="hidden xl:flex items-center gap-2 text-gray-500 text-sm font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span><?= date('M d, Y') ?></span>
                    </div>

                    <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

                    <!-- Profile Dropdown -->
                    <div x-data="{ profileDropdownOpen: false }" class="relative">
                        <button @click="profileDropdownOpen = !profileDropdownOpen"
                            @click.away="profileDropdownOpen = false"
                            class="flex items-center gap-4 focus:outline-none group">
                            <div class="h-9 w-9 rounded-full bg-[#7283ff]/20 flex items-center justify-center text-white font-bold ring-2 ring-transparent group-hover:ring-[#7283ff]/30 transition-all duration-200 overflow-hidden">
                                <?= strtoupper(substr($employer->company_name ?? 'E', 0, 1)) ?>
                            </div>
                            <div class="flex flex-col items-start hidden md:block">
                                <span class="text-sm font-semibold text-gray-900 group-hover:text-[#7283ff] transition-colors"><?= $employer->company_name ?? 'Employer' ?></span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 group-hover:text-gray-600 transition-colors hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="profileDropdownOpen"
                            x-cloak
                            @click.away="profileDropdownOpen = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="/employer/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            <a href="/employer/company-profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Company Profile</a>
                            <a href="/employer/kyc" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Documents</a>
                            <a href="/employer/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a href="/employer/settings?tab=notifications" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Email Notifications</a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form action="/logout" method="POST" class="block">
                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Backdrop for mobile -->
        <div x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden transition-opacity duration-300"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="bg-gradient-to-b from-[#6d82ff] to-[#7d8aff] text-white transition-all duration-300 overflow-visible fixed top-0 left-0 h-screen z-50 shadow-xl w-64 flex flex-col border-r border-[#6d82ff]/20" :style="(window.innerWidth >= 1024) ? ('width:' + (sidebarCollapsed ? '5rem' : '16rem')) : 'width:16rem'">

            <!-- Logo / Brand -->
            <div class="h-16 flex items-center px-8 border-b border-[#7283ff]/20">
                <div class="flex items-center gap-3">
                    <button @click="toggleSidebar()" class="p-2 rounded-lg bg-white text-gray-700 border border-white/20 shadow-sm hover:bg-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-label="Toggle Sidebar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="ml-2 text-xl font-bold text-white tracking-tight font-display" x-show="!sidebarCollapsed" x-transition>Mindware</span>
                </div>
            </div>

            <!-- Create New Button -->
            <div class="p-4 pb-2 flex-shrink-0">
                <a href="/employer/jobs/create" class="w-full px-4 py-3 bg-white text-indigo-600 font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-indigo-900/10 hover:shadow-xl hover:shadow-indigo-900/20 transition-all duration-200 transform hover:-translate-y-0.5 group">
                    <svg class="h-5 w-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition>Create New</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/20 scrollbar-track-transparent">
                <a href="/employer/dashboard" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/dashboard') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/jobs') === false) ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/dashboard') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/jobs') === false) ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Dashboard</span>
                </a>

                <a href="/employer/jobs" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/jobs') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/jobs') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Jobs</span>
                    <?php if (($jobCount ?? 0) > 0): ?>
                        <span x-show="!sidebarCollapsed" class="ml-auto px-2 py-0.5 rounded-md text-xs font-bold min-w-[20px] text-center <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/jobs') !== false ? 'bg-white/20 text-white' : 'bg-white/20 text-white' ?>"><?= $jobCount ?? 0 ?></span>
                    <?php endif; ?>
                </a>

                <a href="/employer/applications" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/applications') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/applications') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Candidates</span>
                    <?php if (($applicationCount ?? 0) > 0): ?>
                        <span x-show="!sidebarCollapsed" class="ml-auto px-2 py-0.5 rounded-md text-xs font-bold min-w-[20px] text-center <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/applications') !== false ? 'bg-white/20 text-white' : 'bg-white/20 text-white' ?>"><?= $applicationCount ?? 0 ?></span>
                    <?php endif; ?>
                </a>

                <a href="/employer/interviews" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/interviews') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/interviews') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Interviews</span>
                </a>

                <a href="/employer/messages" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/messages') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/messages') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Messages</span>
                    <?php if (($unreadCount ?? 0) > 0): ?>
                        <span x-show="!sidebarCollapsed" class="ml-auto w-2 h-2 rounded-full animate-pulse shadow-sm <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/messages') !== false ? 'bg-white' : 'bg-white' ?>"></span>
                    <?php endif; ?>
                </a>

               <a href="/employer/analytics" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/analytics') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                 <!-- Analytics Icon (your provided SVG) -->
                   <svg xmlns="http://www.w3.org/2000/svg"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/analytics') !== false
    ? 'text-white'
    : 'text-white/70 group-hover:text-white' ?>">

        <path stroke-linecap="round" stroke-linejoin="round" 
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
    </svg>

    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>
        Analytics
    </span>
</a>



                <?php $billingActive = strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/') !== false; ?>

                <div x-data="{ open: <?= $billingActive ? 'true' : 'false' ?> }">
                    <button @click="open = !open" type="button"
                        class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 group
                                <?= $billingActive ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= $billingActive ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="flex-1 text-left" x-show="!sidebarCollapsed" x-transition>Billing & Invoices</span>
                        <svg class="h-4 w-4 transition-transform duration-200 <?= $billingActive ? 'text-white/70' : 'text-white/50 group-hover:text-white/80' ?>" :class="open ? 'rotate-180' : 'rotate-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open && !sidebarCollapsed" x-cloak x-transition class="mt-1 ml-4 space-y-1 border-l border-white/10 pl-2">
                        <a href="/employer/billing/overview"
                            class="block px-3 py-2 rounded-lg text-sm transition-all <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/overview') !== false ? 'bg-white/20 text-white font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                            Billing Overview
                        </a>
                        <a href="/employer/billing/transactions"
                            class="block px-3 py-2 rounded-lg text-sm transition-all <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/transactions') !== false ? 'bg-white/20 text-white font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                            Transactions
                        </a>
                        <a href="/employer/billing/invoices"
                            class="block px-3 py-2 rounded-lg text-sm transition-all <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/invoices') !== false ? 'bg-white/20 text-white font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                            Invoices
                        </a>
                        <a href="/employer/billing/payment-methods"
                            class="block px-3 py-2 rounded-lg text-sm transition-all <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/payment-methods') !== false ? 'bg-white/20 text-white font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                            Payment Methods
                        </a>
                        <a href="/employer/billing/settings"
                            class="block px-3 py-2 rounded-lg text-sm transition-all <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/billing/settings') !== false ? 'bg-white/20 text-white font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                            Billing Settings
                        </a>
                    </div>
                </div>

                <a href="/employer/subscription/plans" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/subscription') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/subscription') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Subscription Plans</span>
                </a>
                <a href="/employer/settings" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/settings') !== false ? 'bg-white/20 text-white font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/employer/settings') !== false ? 'text-white' : 'text-white/70 group-hover:text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Settings</span>
                </a>
            </nav>

            <!-- Bottom Section (Logout) -->
            <div class="p-4 border-t border-white/10 mt-auto bg-[#7283ff]/10">
                <a href="/logout" @click="if (window.innerWidth < 1024) sidebarOpen = false" class="group flex items-center px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white transition-all duration-200 rounded-xl">
                    <svg class="h-5 w-5 mr-3 flex-shrink-0 transition-transform duration-200 group-hover:translate-x-1 text-white/70 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="flex-1" x-show="!sidebarCollapsed" x-transition>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:pl-64 pt-16 min-h-screen transition-all duration-300" :style="(window.innerWidth >= 1024) ? ('padding-left:' + (sidebarCollapsed ? '5rem' : '16rem')) : ''">
            <div class="w-full max-w-none px-4 sm:px-6 lg:px-8 py-8">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    <?= $scripts ?? '' ?>
</body>

</html>
