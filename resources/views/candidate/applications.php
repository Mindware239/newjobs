<?php
$candidate = $candidate ?? null;
$user = $candidate ? $candidate->user() : null;
$userEmail = $user ? $user->attributes['email'] ?? '' : '';
$unreadMessages = $unreadMessages ?? 0;
$unreadNotifications = $unreadNotifications ?? 0;
ob_start();
?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-3 bg-white border border-gray-200 rounded-xl shadow-sm text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Applications</h1>
                    <p class="text-gray-600 mt-1">Track all your job applications and their status</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-6 mb-8">
            <a href="/candidate/applications" class="col-span-2 md:col-span-1 bg-white rounded-xl shadow-sm hover:shadow-md p-4 md:p-6 border-l-4 border-blue-500 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div class="text-xs md:text-sm font-semibold text-gray-600">Total Applications</div>
                    <div class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></div>
            </a>
            
            <a href="/candidate/applications?status=applied" class="bg-white rounded-xl shadow-sm hover:shadow-md p-4 md:p-6 border-l-4 border-blue-500 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div class="text-xs md:text-sm font-semibold text-gray-600">Applied</div>
                    <div class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900"><?= $stats['applied'] ?? 0 ?></div>
            </a>
            
            <a href="/candidate/applications?status=shortlisted" class="bg-white rounded-xl shadow-sm hover:shadow-md p-4 md:p-6 border-l-4 border-blue-500 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div class="text-xs md:text-sm font-semibold text-gray-600">Shortlisted</div>
                    <div class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900"><?= $stats['shortlisted'] ?? 0 ?></div>
            </a>
            
            <a href="/candidate/applications?status=interview" class="bg-white rounded-xl shadow-sm hover:shadow-md p-4 md:p-6 border-l-4 border-blue-500 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div class="text-xs md:text-sm font-semibold text-gray-600">Interview</div>
                    <div class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900"><?= $stats['interview'] ?? 0 ?></div>
            </a>
            
            <a href="/candidate/applications?status=rejected" class="col-span-2 md:col-span-1 bg-white rounded-xl shadow-sm hover:shadow-md p-4 md:p-6 border-l-4 border-gray-400 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div class="text-xs md:text-sm font-semibold text-gray-600">Rejected</div>
                    <div class="p-1.5 md:p-2 bg-gray-50 text-gray-600 rounded-lg group-hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900"><?= $stats['rejected'] ?? 0 ?></div>
            </a>
        </div>

        <!-- Applications List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <?php if (empty($applications)): ?>
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No applications yet</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">You haven't applied to any jobs yet. Start your search and find your next opportunity.</p>
                    <a href="/candidate/jobs" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all shadow-sm hover:shadow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Jobs
                    </a>
                </div>
            <?php else: ?>
                <!-- Mobile Card View -->
                <div class="md:hidden space-y-4">
                    <?php foreach ($applications as $app): ?>
                        <?php
                        $status = strtolower($app['status'] ?? 'applied');
                        $statusColors = [
                            'applied' => 'bg-blue-100 text-blue-700',
                            'screening' => 'bg-blue-50 text-blue-600',
                            'shortlisted' => 'bg-blue-200 text-blue-800',
                            'interview' => 'bg-blue-600 text-white',
                            'offer' => 'bg-blue-800 text-white',
                            'hired' => 'bg-blue-900 text-white',
                            'rejected' => 'bg-gray-100 text-gray-700'
                        ];
                        $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg border border-blue-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($app['job_title'] ?? 'Unknown') ?></h3>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($app['company_name'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $colorClass ?>">
                                    <?= htmlspecialchars($app['status_label'] ?? ucfirst($status)) ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Applied: <?= htmlspecialchars($app['applied_at'] ?? 'N/A') ?>
                            </div>

                            <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                                <?php if (!empty($app['job_id'])): ?>
                                    <a href="/candidate/jobs/<?= htmlspecialchars($app['job_slug'] ?? $app['job_id'] ?? '') ?>" class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Job
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($app['employer_id'])): ?>
                                    <button onclick="startMessage(<?= $app['employer_id'] ?>, <?= $app['job_id'] ?? 'null' ?>, <?= $app['id'] ?? 'null' ?>)" 
                                            class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        Message
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Job Title</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Applied Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($applications as $app): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($app['job_id'])): ?>
                                            <a href="/candidate/jobs/<?= htmlspecialchars($app['job_slug'] ?? $app['job_id'] ?? '') ?>" class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors flex items-center gap-3">
                                                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <?= htmlspecialchars($app['job_title'] ?? 'Unknown') ?>
                                            </a>
                                        <?php else: ?>
                                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($app['job_title'] ?? 'Unknown') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="/candidate/jobs?company=<?= urlencode($app['company_name'] ?? '') ?>" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($app['company_name'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($app['applied_at'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $status = strtolower($app['status'] ?? 'applied');
                                        $statusColors = [
                                            'applied' => 'bg-blue-100 text-blue-700',
                                            'screening' => 'bg-blue-50 text-blue-600',
                                            'shortlisted' => 'bg-blue-200 text-blue-800',
                                            'interview' => 'bg-blue-600 text-white',
                                            'offer' => 'bg-blue-800 text-white',
                                            'hired' => 'bg-blue-900 text-white',
                                            'rejected' => 'bg-gray-100 text-gray-700'
                                        ];
                                        $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-700';
                                        ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $colorClass ?>">
                                            <?= htmlspecialchars($app['status_label'] ?? ucfirst($status)) ?>
                                        </span>
                                        
                                        <!-- Interview Details -->
                                        <?php if (!empty($app['interview']) && $status === 'interview'): 
                                            $interview = $app['interview'];
                                        ?>
                                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-sm font-semibold text-blue-900">Interview Scheduled</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs text-gray-700">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span><strong>Date:</strong> <?= htmlspecialchars($interview['date']) ?></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span><strong>Time:</strong> <?= htmlspecialchars($interview['start_time']) ?> - <?= htmlspecialchars($interview['end_time']) ?></span>
                                                    </div>
                                                    <?php if ($interview['type'] === 'onsite' && !empty($interview['location'])): ?>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            </svg>
                                                            <span><strong>Location:</strong> <?= htmlspecialchars($interview['location']) ?></span>
                                                        </div>
                                                    <?php elseif ($interview['type'] === 'video' && !empty($interview['meeting_link'])): ?>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                            </svg>
                                                            <a href="<?= htmlspecialchars($interview['meeting_link']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium">
                                                                <strong>Join Meeting:</strong> Click here to join video call
                                                            </a>
                                                        </div>
                                                    <?php elseif ($interview['type'] === 'phone'): ?>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                            </svg>
                                                            <span><strong>Type:</strong> Phone Interview</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <?php if (!empty($app['job_id'])): ?>
                                                <a href="/candidate/jobs/<?= htmlspecialchars($app['job_slug'] ?? $app['job_id'] ?? '') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-lg hover:bg-blue-200 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Job
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($app['employer_id'])): ?>
                                                <button onclick="startMessage(<?= $app['employer_id'] ?>, <?= $app['job_id'] ?? 'null' ?>, <?= $app['id'] ?? 'null' ?>)" 
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-lg hover:bg-blue-200 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                    Message
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function startMessage(employerId, jobId, applicationId) {
            try {
                const response = await fetch('/candidate/chat/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        employer_id: employerId,
                        job_id: jobId || null,
                        initial_message: applicationId ? `Regarding application #${applicationId}` : ''
                    })
                });

                const data = await response.json();
                if (data.success && data.conversation_id) {
                    window.location.href = `/candidate/chat/${data.conversation_id}`;
                } else {
                    alert(data.error || 'Failed to start conversation');
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
                alert('Error: ' + error.message);
            }
        }
    </script>
<?php
$content = ob_get_clean();
$title = $title ?? 'My Applications';
require __DIR__ . '/../layout.php';
?>


