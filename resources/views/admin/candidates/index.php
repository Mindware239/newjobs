<div>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Manage Candidates</h1>
            <p class="mt-2 text-sm text-gray-600">View, search, and manage all candidate profiles.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="/admin/candidates/add" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Candidate
            </a>
            <a href="/admin/candidates/import" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import
            </a>
            <a href="/admin/candidates/import-history" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                History
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Candidates</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Admin Created</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['admin'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Website</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['website'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Imported</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['excel'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Filter Candidates</h2>
            <a href="/admin/candidates" class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">Clear All</a>
        </div>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-5">
            <div class="lg:col-span-4">
                <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                           placeholder="Search by name, email, or phone..." 
                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Status</label>
                <select name="status" id="status" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                    <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="filter" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Type</label>
                <select name="filter" id="filter" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                    <option value="">All Types</option>
                    <option value="suspicious" <?= ($filters['filter'] ?? '') === 'suspicious' ? 'selected' : '' ?>>Suspicious</option>
                    <option value="premium" <?= ($filters['filter'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>
            
            <div class="lg:col-span-2">
                <label for="source" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Source</label>
                <select name="source" id="source" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                    <option value="" <?= ($filters['source'] ?? '') === '' ? 'selected' : '' ?>>All Sources</option>
                    <option value="admin_manual" <?= ($filters['source'] ?? '') === 'admin_manual' ? 'selected' : '' ?>>Manual (Admin)</option>
                    <option value="walk_in" <?= ($filters['source'] ?? '') === 'walk_in' ? 'selected' : '' ?>>Walk-in</option>
                    <option value="referral" <?= ($filters['source'] ?? '') === 'referral' ? 'selected' : '' ?>>Referral</option>
                    <option value="social_media" <?= ($filters['source'] ?? '') === 'social_media' ? 'selected' : '' ?>>Social Media</option>
                    <option value="job_fair" <?= ($filters['source'] ?? '') === 'job_fair' ? 'selected' : '' ?>>Job Fair</option>
                    <option value="excel" <?= ($filters['source'] ?? '') === 'excel' ? 'selected' : '' ?>>Imported (Excel)</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="location" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Location</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($filters['location'] ?? '') ?>"
                       placeholder="City or State"
                       class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">&nbsp;</label>
                <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Candidates Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Candidate</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stats & Premium</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Verification</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($candidates as $candidate): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <?php if (!empty($candidate['profile_picture'])): ?>
                                        <img class="h-12 w-12 rounded-full object-cover border border-gray-200 shadow-sm" src="<?= htmlspecialchars($candidate['profile_picture']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 shadow-sm">
                                            <span class="text-sm font-bold leading-none text-white"><?= strtoupper(substr($candidate['full_name'] ?? 'U', 0, 2)) ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                        <?= htmlspecialchars($candidate['full_name'] ?? 'Unknown') ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <div class="flex items-center">
                                            <svg class="flex-shrink-0 h-3.5 w-3.5 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <?= htmlspecialchars($candidate['city'] ?? '') ?><?= !empty($candidate['city']) && !empty($candidate['country']) ? ', ' : '' ?><?= htmlspecialchars($candidate['country'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                                $sourceRaw = (string)($candidate['source'] ?? '');
                                $sourceVal = strtolower(trim($sourceRaw));
                                if ($sourceVal === '' || $sourceVal === 'null') { $sourceVal = 'unknown'; }
                                if ($sourceVal === 'walk-in') { $sourceVal = 'walk_in'; }
                                if ($sourceVal === 'registration') { $sourceVal = 'website'; }
                                $sourceClass = 'bg-gray-100 text-gray-800';
                                if ($sourceVal === 'excel') $sourceClass = 'bg-purple-100 text-purple-800';
                                elseif ($sourceVal === 'admin_manual') $sourceClass = 'bg-blue-100 text-blue-800';
                                elseif ($sourceVal === 'website') $sourceClass = 'bg-pink-100 text-pink-800';
                                elseif ($sourceVal === 'walk_in' || $sourceVal === 'walk-in') $sourceClass = 'bg-green-100 text-green-800';
                                elseif ($sourceVal === 'referral') $sourceClass = 'bg-amber-100 text-amber-800';
                                elseif ($sourceVal === 'social_media') $sourceClass = 'bg-indigo-100 text-indigo-800';
                                elseif ($sourceVal === 'job_fair') $sourceClass = 'bg-teal-100 text-teal-800';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sourceClass ?>">
                                <?= $sourceVal === 'unknown' ? 'No Info' : ucfirst(str_replace('_', ' ', $sourceVal)) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $verified = (int)($candidate['email_verified'] ?? 0) === 1; ?>
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $verified ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-yellow-100 text-yellow-800 border border-yellow-200' ?>">
                                <?= $verified ? 'Verified' : 'Not Verified' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 flex items-center">
                                <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 01-2 2z"/>
                                </svg>
                                <?= htmlspecialchars($candidate['email'] ?? '') ?>
                            </div>
                            <div class="text-sm text-gray-500 flex items-center mt-1">
                                <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <?= htmlspecialchars($candidate['mobile'] ?? '') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                                    <?= number_format($candidate['applications_count'] ?? 0) ?> Apps
                                </span>
                            </div>
                            <?php 
                                $isPremium = ((int)($candidate['is_premium'] ?? 0) === 1) && !empty($candidate['premium_expires_at']) && strtotime($candidate['premium_expires_at']) > time(); 
                            ?>
                            <?php if($isPremium): ?>
                                <div class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gradient-to-r from-yellow-200 to-yellow-400 text-yellow-800 shadow-sm">
                                        <svg class="w-3 h-3 mr-1 self-center" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Premium
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                                $statusClass = match($candidate['user_status'] ?? '') {
                                    'active' => 'bg-green-100 text-green-800 border border-green-200',
                                    'blocked' => 'bg-red-100 text-red-800 border border-red-200',
                                    'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                    default => 'bg-gray-100 text-gray-800 border border-gray-200'
                                };
                            ?>
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                <?= ucfirst($candidate['user_status'] ?? 'unknown') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <div>
                                    <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors" id="menu-button-<?= $candidate['id'] ?>" aria-expanded="true" aria-haspopup="true">
                                        Actions
                                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" role="menu" aria-orientation="vertical" aria-labelledby="menu-button-<?= $candidate['id'] ?>" tabindex="-1" style="display: none;">
                                    <div class="py-1" role="none">
                                        <a href="/admin/candidates/<?= $candidate['id'] ?>" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">View Profile</a>
                                        
                                        <?php if (($candidate['user_status'] ?? '') === 'active'): ?>
                                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/block" class="block w-full text-left">
                                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="text-red-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Block User</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/unblock" class="block w-full text-left">
                                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="text-green-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Unblock User</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (!$isPremium): ?>
                                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/enable" class="block w-full text-left">
                                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <input type="hidden" name="days" value="30">
                                                <button type="submit" class="text-yellow-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Make Premium</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/disable" class="block w-full text-left">
                                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Remove Premium</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="mt-5 px-4 flex items-center justify-between sm:px-0">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-semibold text-gray-900"><?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?></span>
                    to
                    <span class="font-semibold text-gray-900"><?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?></span>
                    of
                    <span class="font-semibold text-gray-900"><?= $pagination['total'] ?></span>
                    results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <!-- Previous Page Link -->
                    <?php if ($pagination['page'] > 1): ?>
                        <a href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&location=<?= urlencode($filters['location'] ?? '') ?>&filter=<?= urlencode($filters['filter'] ?? '') ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php 
                    $start = max(1, $pagination['page'] - 2);
                    $end = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    if ($start > 1) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                    }

                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&location=<?= urlencode($filters['location'] ?? '') ?>&filter=<?= urlencode($filters['filter'] ?? '') ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i === $pagination['page'] ? 'text-blue-600 bg-blue-50 z-10' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($end < $pagination['totalPages']): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                    <?php endif; ?>

                    <!-- Next Page Link -->
                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&location=<?= urlencode($filters['location'] ?? '') ?>&filter=<?= urlencode($filters['filter'] ?? '') ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
