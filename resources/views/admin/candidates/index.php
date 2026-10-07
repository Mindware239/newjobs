<?php
/** @var array $candidates */
/** @var array $pagination */
/** @var array $filters */
/** @var array $stats */
/** @var \App\Models\User $user */
?>
<div>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Manage Candidates</h1>
            <p class="mt-2 text-sm text-gray-600">Advanced recruitment CRM for candidate management.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Export Data
                </button>
                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        <a href="?export=csv&<?= http_build_query($filters) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as CSV</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as Excel</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as PDF</a>
                    </div>
                </div>
            </div>
            <a href="/admin/candidates/add" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Candidate
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Candidates</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900"><?= number_format($stats['total'] ?? 0) ?></p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center text-sm">
                <span class="text-green-600 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    +<?= $stats['new_today'] ?? 0 ?>
                </span>
                <span class="text-gray-500 ml-2">since today</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Verified Profiles</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900"><?= number_format($stats['verified'] ?? 0) ?></p>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $stats['total'] > 0 ? ($stats['verified'] / $stats['total'] * 100) : 0 ?>%"></div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Premium Members</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900"><?= number_format($stats['premium'] ?? 0) ?></p>
                </div>
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-500">
                <span class="font-medium text-gray-900"><?= number_format($stats['premium'] ?? 0) ?></span> active premium subscriptions
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Immediate Joiners</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">0</p>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-500">
                Based on notice period data
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div x-data="{ showAdvanced: false, dateRange: '<?= $filters['date_range'] ?? '' ?>' }" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 transition-all duration-300">
        <form method="GET" id="filterForm">
            <!-- Global Search & Basic Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                           placeholder="Global search (Name, Email, Phone, ID)..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm">
                </div>

                <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="all">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>

                <select name="role" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">All Job Roles</option>
                    <option value="Software Developer" <?= ($filters['role'] ?? '') === 'Software Developer' ? 'selected' : '' ?>>Software Developer</option>
                    <option value="UI/UX Designer" <?= ($filters['role'] ?? '') === 'UI/UX Designer' ? 'selected' : '' ?>>UI/UX Designer</option>
                    <option value="HR Manager" <?= ($filters['role'] ?? '') === 'HR Manager' ? 'selected' : '' ?>>HR Manager</option>
                    <option value="Sales Executive" <?= ($filters['role'] ?? '') === 'Sales Executive' ? 'selected' : '' ?>>Sales Executive</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors text-sm font-medium">Apply</button>
                    <button @click.prevent="showAdvanced = !showAdvanced" class="py-2 px-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium flex items-center">
                        <svg :class="{'rotate-180': showAdvanced}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <a href="/admin/candidates" class="py-2 px-3 bg-gray-100 text-gray-500 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Advanced Filters Section (Collapsible) -->
            <div x-show="showAdvanced" x-collapse x-cloak class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Location Group -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Location & Region</h4>
                    <div class="space-y-3">
                        <input type="text" name="country" placeholder="Country" value="<?= htmlspecialchars($filters['country'] ?? '') ?>" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                        <input type="text" name="state" placeholder="State" value="<?= htmlspecialchars($filters['state'] ?? '') ?>" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                        <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($filters['city'] ?? '') ?>" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                    </div>
                </div>

                <!-- Experience & Salary -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Experience & Salary</h4>
                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <input type="number" name="min_experience" placeholder="Min Exp" value="<?= htmlspecialchars($filters['min_experience'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <input type="number" name="max_experience" placeholder="Max Exp" value="<?= htmlspecialchars($filters['max_experience'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                        </div>
                        <div class="flex gap-2">
                            <input type="number" name="min_salary" placeholder="Min Salary" value="<?= htmlspecialchars($filters['min_salary'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <input type="number" name="max_salary" placeholder="Max Salary" value="<?= htmlspecialchars($filters['max_salary'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                        </div>
                        <select name="notice_period" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">Any Notice Period</option>
                            <option value="0">Immediate</option>
                            <option value="15">15 Days</option>
                            <option value="30">30 Days</option>
                            <option value="60">60+ Days</option>
                        </select>
                    </div>
                </div>

                <!-- Profile & Verification -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Verification & Documents</h4>
                    <div class="space-y-3">
                        <select name="verification_status" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">Verification Status</option>
                            <option value="verified" <?= ($filters['verification_status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                            <option value="not_verified" <?= ($filters['verification_status'] ?? '') === 'not_verified' ? 'selected' : '' ?>>Not Verified</option>
                        </select>
                        <select name="has_resume" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">Resume Status</option>
                            <option value="1" <?= ($filters['has_resume'] ?? '') === '1' ? 'selected' : '' ?>>Resume Uploaded</option>
                            <option value="0" <?= ($filters['has_resume'] ?? '') === '0' ? 'selected' : '' ?>>No Resume</option>
                        </select>
                        <select name="gender" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">Any Gender</option>
                            <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                        <select name="signup_via" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">Any sign-in method</option>
                            <?php foreach (['facebook' => 'Facebook login', 'google' => 'Google login', 'linkedin' => 'LinkedIn login', 'email' => 'Email / mobile OTP only'] as $sv => $sl): ?>
                                <option value="<?= $sv ?>" <?= ($filters['signup_via'] ?? '') === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Date Ranges -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Date Filter</h4>
                    <div class="space-y-3">
                        <select name="date_type" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="created_at" <?= ($filters['date_type'] ?? '') === 'created_at' ? 'selected' : '' ?>>Registration Date</option>
                            <option value="last_login" <?= ($filters['date_type'] ?? '') === 'last_login' ? 'selected' : '' ?>>Last Login</option>
                            <option value="updated_at" <?= ($filters['date_type'] ?? '') === 'updated_at' ? 'selected' : '' ?>>Profile Update</option>
                        </select>
                        <select name="date_range" x-model="dateRange" class="block w-full py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <option value="">All Time</option>
                            <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="yesterday" <?= ($filters['date_range'] ?? '') === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                            <option value="last_7_days" <?= ($filters['date_range'] ?? '') === 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="last_30_days" <?= ($filters['date_range'] ?? '') === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
                            <option value="custom" <?= ($filters['date_range'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                        <div x-show="dateRange === 'custom'" class="flex gap-2">
                            <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                            <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>" class="w-1/2 py-2 px-3 border border-gray-200 rounded-lg text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filter Chips -->
    <?php if(!empty($filters)): ?>
    <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach($filters as $key => $value): if(empty($value) || in_array($key, ['page', 'per_page'])) continue; ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary text-xs font-medium border border-primary-100">
                <?= ucfirst(str_replace('_', ' ', $key)) ?>: <?= htmlspecialchars((string)$value) ?>
                <a href="<?= '?' . http_build_query(array_diff_key($filters, [$key => ''])) ?>" class="ml-2 hover:text-primary-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Candidate CRM Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ selected: [] }">
        <!-- Table Actions Bar -->
        <form method="POST" action="/admin/candidates/bulk-action" id="bulkForm" class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="flex items-center gap-4">
                <input type="checkbox" @change="if($el.checked) { selected = Array.from(document.querySelectorAll('.cand-check')).map(el => el.value) } else { selected = [] }" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <div x-show="selected.length > 0" class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700"><span x-text="selected.length"></span> selected</span>
                    <select name="action" @change="if($el.value === 'export') { $el.closest('form').action = '/admin/candidates/export-selected'; $el.closest('form').submit(); } else { $el.closest('form').submit(); }" class="py-1 px-3 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate Selected</option>
                        <option value="block">Block Selected</option>
                        <option value="export">Export Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <template x-for="id in selected">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-gray-400">View:</span>
                <button type="button" class="p-1.5 text-primary bg-primary-50 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg></button>
                <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg></button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Candidate Profile</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Applied Role & Exp</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Contact & Social</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if(empty($candidates)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-24 text-center">
                                <div class="max-w-xs mx-auto">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No candidates found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters to find what you're looking for.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($candidates as $candidate): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <input type="checkbox" value="<?= $candidate['id'] ?>" x-model="selected" class="cand-check h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 relative">
                                    <?php 
                                        $initials = strtoupper(substr($candidate['full_name'] ?? 'U', 0, 1));
                                        $colors = [
                                            'bg-blue-100 text-blue-700',
                                            'bg-green-100 text-green-700',
                                            'bg-purple-100 text-purple-700',
                                            'bg-pink-100 text-pink-700',
                                            'bg-indigo-100 text-indigo-700',
                                            'bg-yellow-100 text-yellow-700',
                                            'bg-orange-100 text-orange-700',
                                            'bg-teal-100 text-teal-700'
                                        ];
                                        $colorClass = $colors[$candidate['id'] % count($colors)];
                                    ?>
                                    <?php if (!empty($candidate['profile_picture'])): ?>
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-100" 
                                             src="<?= htmlspecialchars($candidate['profile_picture']) ?>" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                             alt="">
                                        <span class="hidden items-center justify-center h-10 w-10 rounded-lg font-bold <?= $colorClass ?>">
                                            <?= $initials ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-lg font-bold <?= $colorClass ?>">
                                            <?= $initials ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if($candidate['is_premium']): ?>
                                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($candidate['full_name'] ?? 'Unknown') ?></div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <?= htmlspecialchars($candidate['city'] ?? 'Location not set') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($candidate['professional_title'] ?? 'Role not specified') ?></div>
                            <div class="text-xs text-gray-500 mt-1">Exp: <?= $candidate['total_experience'] ?? 'Fresher' ?> • Salary: <?= $candidate['current_salary'] ?? 'N/A' ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs text-gray-700 flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 01-2 2z"/></svg>
                                    <?= htmlspecialchars($candidate['email'] ?? '') ?>
                                </div>
                                <div class="text-xs text-gray-700 flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <?php $cMobile = ($candidate['mobile'] ?? '') !== '' ? (string)$candidate['mobile'] : (string)($candidate['user_phone'] ?? ''); ?>
                                    <?= $cMobile !== '' ? htmlspecialchars($cMobile) : '<span class="text-red-500">no mobile yet</span>' ?>
                                </div>
                                <?php // Social sign-in: which provider, and the email that provider gave us (shown when it differs from the account email). ?>
                                <?php foreach (['facebook' => ['Facebook', 'bg-blue-50 text-blue-700'], 'google' => ['Google', 'bg-red-50 text-red-700'], 'linkedin' => ['LinkedIn', 'bg-sky-50 text-sky-700']] as $sp => [$spLabel, $spCls]): ?>
                                    <?php if (!empty($candidate[$sp . '_id'])): ?>
                                        <div class="text-xs"><span class="px-1.5 py-0.5 rounded font-semibold <?= $spCls ?>"><?= $spLabel ?></span>
                                            <?php if (!empty($candidate[$sp . '_email']) && strcasecmp((string)$candidate[$sp . '_email'], (string)($candidate['email'] ?? '')) !== 0): ?>
                                                <span class="text-gray-500"><?= htmlspecialchars((string)$candidate[$sp . '_email']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs text-gray-600">Joined: <?= date('M d, Y', strtotime($candidate['created_at'])) ?></div>
                            <div class="text-xs text-gray-400 mt-1">Last Login: <?= $candidate['last_login'] ? date('M d, H:i', strtotime($candidate['last_login'])) : 'Never' ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $candidate['user_status'] === 'active' ? 'bg-green-50 text-green-700 border border-green-100' : 
                                   ($candidate['user_status'] === 'blocked' ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-yellow-50 text-yellow-700 border border-yellow-100') ?>">
                                <?= ucfirst($candidate['user_status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/admin/candidates/<?= $candidate['id'] ?>" class="p-1.5 text-gray-400 hover:text-primary transition-colors" title="View Profile">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <?php if(!empty($candidate['resume_url'])): ?>
                                    <a href="<?= $candidate['resume_url'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-green-600 transition-colors" title="Download Resume">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <div class="relative" x-data="{ menu: false }">
                                    <button @click="menu = !menu" @click.away="menu = false" class="p-1.5 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                    </button>
                                    <div x-show="menu" x-cloak class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                        <div class="py-1">
                                            <a href="/admin/candidates/<?= $candidate['id'] ?>/edit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Details</a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Assign Recruiter</a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add Internal Note</a>
                                            <hr class="my-1 border-gray-100">
                                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/block">
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Block Candidate</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?></span> to <span class="font-medium"><?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?></span> of <span class="font-medium"><?= $pagination['total'] ?></span> results
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <select name="per_page" onchange="window.location.href = '?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>&per_page=' + this.value" class="py-1 px-2 border border-gray-300 rounded text-xs bg-white">
                        <option value="20" <?= $pagination['perPage'] == 20 ? 'selected' : '' ?>>20 per page</option>
                        <option value="50" <?= $pagination['perPage'] == 50 ? 'selected' : '' ?>>50 per page</option>
                        <option value="100" <?= $pagination['perPage'] == 100 ? 'selected' : '' ?>>100 per page</option>
                    </select>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php if($pagination['page'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['totalPages'], $pagination['page'] + 2); $i++): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i == $pagination['page'] ? 'text-primary bg-primary-50 border-primary-100 z-10' : 'text-gray-700 hover:bg-gray-50' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if($pagination['page'] < $pagination['totalPages']): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }
    @keyframes ping { 75%, 100% { transform: scale(2); opacity: 0; } }
</style>
