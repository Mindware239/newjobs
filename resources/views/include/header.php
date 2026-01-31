<?php 
$base = $base ?? '/'; 
// Auto-detect base if default or root
if ($base === '/' || $base === '') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // CRITICAL FIX: If REQUEST_URI contains /public/, always set base to root
    if (strpos($requestUri, '/public/') !== false) {
        $base = '/';
    } else {
        // Normal detection from script name
        $scriptDir = dirname($scriptName);
        $base = rtrim($scriptDir, '/\\') . '/';
        
        // Remove /public/ from base if it exists
        if (strpos($base, '/public/') !== false) {
            $base = str_replace('/public/', '/', $base);
        }
        
        // If script is in /public/ directory, ensure base is root
        if (strpos($scriptName, '/public/') !== false) {
            $base = '/';
        }
    }
    
    // Final cleanup: ensure base doesn't contain /public/ anywhere
    $base = str_replace('/public/', '/', $base);
    $base = rtrim($base, '/') . '/';
    
    // Ensure base is at least '/'
    if ($base === '' || $base === '//' || $base === '/public/') {
        $base = '/';
    }
}

// Fetch dynamic data for Mega Menu
$popularCategories = [];
$popularLocations = [];
$jobsInDemand = [
    ['label' => 'Fresher jobs', 'url' => 'jobs?experience=0-1'],
    ['label' => 'MNC jobs', 'url' => 'jobs?keyword=MNC'],
    ['label' => 'Remote jobs', 'url' => 'jobs?is_remote=1'],
    ['label' => 'Work from home jobs', 'url' => 'jobs?keyword=Work%20from%20home'],
    ['label' => 'Walk-in jobs', 'url' => 'jobs?keyword=Walk-in'],
    ['label' => 'Part-time jobs', 'url' => 'jobs?job_type=part_time']
];

try {
    // Only attempt DB connection if we can find the class
    if (class_exists('\\App\\Core\\Database')) {
        $db = \App\Core\Database::getInstance();
        
        // Fetch Categories (limit 6)
        try {
            $cats = $db->fetchAll("SELECT name FROM categories ORDER BY id ASC LIMIT 6");
            foreach ($cats as $c) {
                $popularCategories[] = ['label' => $c['name'] . ' jobs', 'url' => 'jobs?industry=' . urlencode($c['name'])];
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist
            $popularCategories = [
                ['label' => 'IT jobs', 'url' => 'jobs?keyword=IT'],
                ['label' => 'Sales jobs', 'url' => 'jobs?keyword=Sales'],
                ['label' => 'Marketing jobs', 'url' => 'jobs?keyword=Marketing'],
                ['label' => 'Data Science jobs', 'url' => 'jobs?keyword=Data%20Science'],
                ['label' => 'HR jobs', 'url' => 'jobs?keyword=HR'],
                ['label' => 'Engineering jobs', 'url' => 'jobs?keyword=Engineering']
            ];
        }

        // Fetch Locations (limit 6)
        try {
            $locs = $db->fetchAll("
                SELECT c.name as city, COUNT(jl.id) as count 
                FROM job_locations jl 
                JOIN cities c ON jl.city_id = c.id 
                GROUP BY c.id, c.name 
                ORDER BY count DESC 
                LIMIT 6
            ");
            foreach ($locs as $l) {
                $popularLocations[] = ['label' => 'Jobs in ' . $l['city'], 'url' => 'jobs?location=' . urlencode($l['city'])];
            }
        } catch (\Exception $e) {
             // Fallback
             $popularLocations = [
                ['label' => 'Jobs in Delhi', 'url' => 'jobs?location=Delhi'],
                ['label' => 'Jobs in Mumbai', 'url' => 'jobs?location=Mumbai'],
                ['label' => 'Jobs in Bangalore', 'url' => 'jobs?location=Bangalore'],
                ['label' => 'Jobs in Hyderabad', 'url' => 'jobs?location=Hyderabad'],
                ['label' => 'Jobs in Chennai', 'url' => 'jobs?location=Chennai'],
                ['label' => 'Jobs in Pune', 'url' => 'jobs?location=Pune']
            ];
        }
    }
} catch (\Exception $e) {
    // Silent fail, use defaults
}

// Ensure arrays are not empty if DB failed completely
if (empty($popularCategories)) {
    $popularCategories = [
        ['label' => 'IT jobs', 'url' => 'jobs?keyword=IT'],
        ['label' => 'Sales jobs', 'url' => 'jobs?keyword=Sales'],
        ['label' => 'Marketing jobs', 'url' => 'jobs?keyword=Marketing'],
        ['label' => 'Data Science jobs', 'url' => 'jobs?keyword=Data%20Science'],
        ['label' => 'HR jobs', 'url' => 'jobs?keyword=HR'],
        ['label' => 'Engineering jobs', 'url' => 'jobs?keyword=Engineering']
    ];
}
if (empty($popularLocations)) {
    $popularLocations = [
        ['label' => 'Jobs in Delhi', 'url' => 'jobs?location=Delhi'],
        ['label' => 'Jobs in Mumbai', 'url' => 'jobs?location=Mumbai'],
        ['label' => 'Jobs in Bangalore', 'url' => 'jobs?location=Bangalore'],
        ['label' => 'Jobs in Hyderabad', 'url' => 'jobs?location=Hyderabad'],
        ['label' => 'Jobs in Chennai', 'url' => 'jobs?location=Chennai'],
        ['label' => 'Jobs in Pune', 'url' => 'jobs?location=Pune']
    ];
}
?>
<!-- Header Component -->
<header class="sticky top-0 z-50 transition-all duration-300" x-data="{ mobileMenuOpen: false, showUserMenu: false }">
    <!-- Background & Border Layer (Absolute) -->
    <div class="absolute inset-0 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm -z-10"></div>

    <!-- Main Header Content -->
    <div class="relative z-10 container mx-auto px-4 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Logo & Mobile Toggle -->
        <div class="flex items-center gap-4">
             <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = true" 
                    class="lg:hidden text-gray-700 hover:text-blue-600 focus:outline-none p-2 -ml-2 rounded-xl hover:bg-blue-50 transition-colors duration-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            
            <?php 
            $dashboardLink = '#';
            if (isset($_SESSION['user_role'])) {
                $role = $_SESSION['user_role'];
                if ($role === 'candidate') $dashboardLink = $base . 'candidate/dashboard';
                elseif ($role === 'employer') $dashboardLink = $base . 'employer/dashboard';
                elseif ($role === 'admin') $dashboardLink = $base . 'admin/dashboard';
            }
            ?>
            <a href="<?php echo isset($_SESSION['user_id']) ? $dashboardLink : $base; ?>" class="flex items-center gap-3 group">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-100 rounded-full scale-0 group-hover:scale-110 transition-transform duration-300 opacity-50"></div>
                    <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Mindware Infotech" class="relative h-12 md:h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                </div>
            </a>
        </div>
        
        <!-- Desktop Navigation -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <nav class="hidden lg:flex items-center gap-1">
            <a href="<?php echo $base; ?>jobs" class="px-4 py-2 rounded-full text-gray-600 font-medium hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                Jobs
            </a>
            <a href="<?php echo $base; ?>company/featured" class="px-4 py-2 rounded-full text-gray-600 font-medium hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                Featured Companies
            </a>
            
            <!-- Redesigned Social Services Button -->
            <a href="<?php echo $base; ?>social-services" 
               target="_blank"
               class="ml-2 flex items-center gap-2 px-5 py-2.5 rounded-full bg-gray-600 text-white font-semibold shadow-md hover:shadow-lg hover:bg-gray-700 hover:text-white transform hover:-translate-y-0.5 transition-all duration-200 group">
                Social Services Jobs
            </a>
        </nav>
        <?php endif; ?>
        
        <!-- Right Side Actions -->
        <div class="hidden lg:flex items-center gap-3">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php 
                    $role = $_SESSION['user_role'] ?? null;
                    $unreadMsgs = isset($unreadMessages) ? (int)$unreadMessages : 0;
                    $unreadNotifs = isset($unreadNotifications) ? (int)$unreadNotifications : 0;
                ?>
                <?php if ($role === 'candidate'): ?>
                    <div class="flex items-center gap-2 mr-4 border-r border-gray-200 pr-4">
                        <a href="<?php echo $base; ?>candidate/jobs" class="flex items-center gap-2 p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200" title="All Jobs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium">All Jobs</span>
                        </a>
                        <a href="<?php echo $base; ?>candidate/jobs/saved" class="flex items-center gap-2 p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200" title="Saved Jobs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path></svg>
                            <span class="text-sm font-medium">Saved Jobs</span>
                        </a>
                        <a href="<?php echo $base; ?>candidate/resume/builder" class="flex items-center gap-2 p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200" title="Resume Builder">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-medium">Resume Builder</span>
                        </a>
                        <a href="<?php echo $base; ?>candidate/notifications" class="relative flex items-center gap-2 p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200" title="Notifications">
                            <div class="relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 7 7.388 7 8.75V14.16c0 .54-.214 1.06-.595 1.44L5 17h5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                                <?php if ($unreadNotifs > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold ring-2 ring-white animate-pulse"><?php echo $unreadNotifs; ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm font-medium">Notifications</span>
                        </a>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ showUserMenu: false }" @click.away="showUserMenu = false">
                        <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-3 pl-2 pr-1 py-1 rounded-full hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-all duration-200 group">
                            <?php 
                            if (isset($candidate)) {
                                $candidateName = $candidate->attributes['full_name'] ?? null;
                                $user = $candidate->user();
                                if (empty($candidateName) && $user) {
                                    $candidateName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? null;
                                }
                                $displayName = $candidateName ?: 'User';
                                $initials = strtoupper(substr($displayName, 0, 1));
                                $userEmail = $user->attributes['email'] ?? '';
                                $profilePic = $candidate->attributes['profile_picture'] ?? null;
                            } else {
                                $user = \App\Models\User::find($_SESSION['user_id'] ?? 0);
                                $displayName = $user ? ($user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? $user->attributes['email'] ?? 'User') : 'User';
                                $initials = strtoupper(substr($displayName, 0, 1));
                                $userEmail = $user ? ($user->attributes['email'] ?? '') : '';
                                $profilePic = null;
                            }
                            ?>
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md overflow-hidden ring-2 ring-blue-100 group-hover:ring-blue-300 transition-all">
                                <?php if($profilePic): ?>
                                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                        <?= $initials ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="hidden md:flex flex-col items-start mr-1">
                                <span class="text-sm font-semibold text-gray-700 leading-tight group-hover:text-blue-700"><?= htmlspecialchars($displayName) ?></span>
                                <?php if (isset($candidate) && $candidate->isPremium()): ?>
                                    <span class="text-[10px] font-bold text-amber-500 flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        PREMIUM
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Candidate</span>
                                <?php endif; ?>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="showUserMenu" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden"
                             style="display: none;">
                            
                            <!-- Header -->
                            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($displayName) ?></p>
                                <p class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($userEmail) ?></p>
                            </div>
                            
                            <div class="py-2">
                                <a href="<?php echo $base; ?>candidate/profile" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>My Profile</span>
                                </a>
                                
                                <a href="<?php echo $base; ?>candidate/chat" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors justify-between">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span>Messages</span>
                                    </div>
                                    <?php if ($unreadMsgs > 0): ?>
                                    <span class="bg-blue-600 text-white text-xs rounded-full px-2 py-0.5 font-bold shadow-sm shadow-blue-200"><?= $unreadMsgs ?></span>
                                    <?php endif; ?>
                                </a>

                                <a href="<?php echo $base; ?>candidate/premium/plans" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span>Upgrade to Premium</span>
                            </a>

                            <a href="<?php echo $base; ?>candidate/premium/billing" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Billing & Receipts</span>
                            </a>

                            <a href="<?php echo $base; ?>candidate/reviews" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <span>My Reviews</span>
                            </a>



                            <a href="<?php echo $base; ?>candidate/profile/complete" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Settings</span>
                            </a>

                            <a href="<?php echo $base; ?>candidate/help" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Help</span>
                            </a>

                            <a href="<?php echo $base; ?>candidate/privacy" class="flex items-center gap-3 px-6 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Privacy Centre</span>
                            </a>
                            </div>
                            
                            <div class="border-t border-gray-100 my-1"></div>
                            
                            <a href="<?php echo $base; ?>logout" class="flex items-center gap-3 px-6 py-3 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Admin/Employer Dashboard Link -->
                     <a href="<?php echo $dashboardLink; ?>" class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span>Dashboard</span>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <!-- Not Logged In Actions -->
                <a href="<?php echo $base; ?>login" class="flex items-center gap-2 px-6 py-2.5 rounded-full bg-blue-600 text-white font-medium  hover:text-white hover:bg-blue-700 shadow-lg shadow-blue-200 hover:shadow-blue-300 hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Post a Job</span>
                </a>
                <a href="<?php echo $base; ?>login" class="flex items-center gap-2 px-6 py-2.5 rounded-full border-2 border-blue-100 text-blue-700 font-medium hover:border-blue-600 hover:bg-blue-50 transition-all duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Employee Login</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu (Teleported to Body) -->
    <template x-teleport="body">
        <div style="position: relative; z-index: 9999;">
            <!-- Mobile Menu Backdrop -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="lg:hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[9990]" x-cloak></div>
            
            <!-- Mobile Menu Drawer -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="lg:hidden fixed inset-y-0 left-0 w-[85%] max-w-sm bg-white shadow-2xl z-[9999] overflow-y-auto flex flex-col" x-cloak>
                
                <!-- Header: Logo & Close -->
                <div class="flex items-center justify-between p-5 border-b border-gray-100 shrink-0">
                     <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Mindware" class="h-8 w-auto">
                     <button @click="mobileMenuOpen = false" class="p-2 -mr-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-full transition-colors">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (($_SESSION['user_role'] ?? null) === 'candidate'): ?>
                            <!-- Logged In Candidate View -->
                            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 px-6 py-8 text-white">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-xl font-bold border-2 border-white/50 overflow-hidden">
                                        <?php if(isset($profilePicture) && $profilePicture): ?>
                                            <img src="<?= htmlspecialchars($profilePicture) ?>" class="w-full h-full object-cover" />
                                        <?php else: ?>
                                            <?= $initials ?? 'U' ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg"><?= htmlspecialchars($candidateName ?? 'User') ?></h3>
                                        <p class="text-blue-100 text-sm">Candidate Account</p>
                                    </div>
                                </div>
                                <?php if (isset($isPremiumUser) && !$isPremiumUser): ?>
                                <a href="<?php echo $base; ?>candidate/premium/plans" class="mt-6 block w-full py-2 bg-amber-400 text-amber-900 text-center font-bold rounded-lg shadow-lg hover:bg-amber-300 transition-colors">
                                    ✨ Upgrade to Premium
                                </a>
                                <?php endif; ?>
                            </div>

                            <div class="p-4 space-y-1">
                                <a href="<?php echo $base; ?>candidate/jobs" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    All Jobs
                                </a>
                                <a href="<?php echo $base; ?>candidate/jobs/saved" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"/></svg>
                                    Saved Jobs
                                </a>
                                <a href="<?php echo $base; ?>candidate/resume/builder" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Resume Builder
                                </a>
                                <a href="<?php echo $base; ?>candidate/applications" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    My Applications
                                </a>
                                <a href="<?php echo $base; ?>candidate/reviews" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    <span>My Reviews</span>
                                </a>
                                <a href="<?php echo $base; ?>candidate/notifications" class="flex items-center justify-between gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 7 7.388 7 8.75V14.16c0 .54-.214 1.06-.595 1.44L5 17h5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                                        <span>Notifications</span>
                                    </div>
                                    <?php if ($unreadNotifs > 0): ?>
                                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $unreadNotifs; ?></span>
                                    <?php endif; ?>
                                </a>
                                 <a href="<?php echo $base; ?>social-services" class="flex items-center gap-3 px-4 py-3 text-teal-700 bg-teal-50 rounded-xl transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Social Services Jobs
                                </a>
                                <a href="<?php echo $base; ?>candidate/profile" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    My Profile
                                </a>
                                <a href="<?php echo $base; ?>candidate/chat" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    Messages
                                </a>
                                <a href="<?php echo $base; ?>candidate/premium/billing" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Billing & Receipts
                                </a>
                                <a href="<?php echo $base; ?>candidate/profile/complete" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Settings
                                </a>
                                <a href="<?php echo $base; ?>candidate/help" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Help
                                </a>
                                <a href="<?php echo $base; ?>candidate/privacy" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Privacy Centre
                                </a>
                                <a href="<?php echo $base; ?>logout" class="flex items-center gap-3 px-4 py-3 text-red-600 rounded-xl hover:bg-red-50 hover:text-red-700 transition-colors">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Other Roles (Admin/Employer) -->
                             <div class="p-6">
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl mb-3">
                                        <?= strtoupper(substr($_SESSION['user_role'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <h3 class="font-bold text-gray-900 capitalize"><?= $_SESSION['user_role'] ?? 'User' ?></h3>
                                </div>
                                <a href="<?php echo $dashboardLink; ?>" class="block w-full py-3 bg-blue-600 text-white text-center rounded-xl font-semibold shadow-md hover:bg-blue-700 transition-colors">
                                    Go to Dashboard
                                </a>
                             </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Guest Mobile View (Premium Look) -->
                        <div class="py-4 px-4 space-y-1">
                             <a href="<?php echo $base; ?>" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                 Home
                             </a>

                             <!-- Jobs -->
                             <a href="<?php echo $base; ?>jobs" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                 Jobs
                             </a>

                             <a href="<?php echo $base; ?>company/featured" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                 Featured Companies
                             </a>

                             <a href="<?php echo $base; ?>social-services" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                 Social Services
                             </a>

                             <a href="<?php echo $base; ?>about" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                 About Us
                             </a>
                             
                             <a href="<?php echo $base; ?>contact" class="flex items-center gap-3 px-4 py-3 text-gray-700 font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                 Contact
                             </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Buttons -->
                <div class="p-5 border-t border-gray-100 bg-gray-50/50 space-y-3 shrink-0">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="grid grid-cols-2 gap-3">
                             <a href="<?php echo $base; ?>login" class="flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                 Employee Login
                             </a>

                             <a href="<?php echo $base; ?>login?role=employer" class="flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                 Employer Login
                             </a>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                             <a href="<?php echo $base; ?>register-candidate" class="flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-md hover:bg-blue-700 hover:shadow-lg transition-all">
                                 Register Employee
                             </a>
                             <a href="<?php echo $base; ?>register-employer" class="flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-md hover:bg-blue-700 hover:shadow-lg transition-all">
                                 Register Employer
                             </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base; ?>logout" class="flex items-center justify-center w-full px-4 py-2.5 rounded-lg bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition-colors">
                            Sign Out
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </template>
</header>
