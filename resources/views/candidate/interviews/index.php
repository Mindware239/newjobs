<?php
$candidate = $candidate ?? null;
$unreadMessages = $unreadMessages ?? 0;
$unreadNotifications = $unreadNotifications ?? 0;
$upcoming = $upcoming ?? [];
$past = $past ?? [];

ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="p-3 bg-white border border-gray-200 rounded-xl shadow-sm text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Interviews</h1>
                <p class="text-gray-600 mt-1">Manage your upcoming and past interviews</p>
            </div>
        </div>
    </div>

    <!-- Upcoming Interviews -->
    <div class="mb-12">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
            Upcoming Interviews
            <?php if (!empty($upcoming)): ?>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full"><?= count($upcoming) ?></span>
            <?php endif; ?>
        </h2>

        <?php if (empty($upcoming)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-50 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No upcoming interviews</h3>
                <p class="text-gray-500 text-sm">You don't have any interviews scheduled at the moment.</p>
                <div class="mt-6">
                    <a href="/candidate/jobs" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        Find more jobs
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($upcoming as $interview): ?>
                    <?php $joinUrl = !empty($interview['meeting_link']) ? $interview['meeting_link'] : ('/interviews/' . (int)($interview['id'] ?? 0) . '/room'); ?>
                    <div class="bg-white rounded-xl shadow-md border-l-4 border-purple-500 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($interview['company_logo'])): ?>
                                        <img src="<?= htmlspecialchars($interview['company_logo']) ?>" class="w-12 h-12 rounded-lg object-contain bg-gray-50 p-1 border border-gray-100">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 font-bold border border-purple-100">
                                            <?= strtoupper(substr($interview['company_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($interview['job_title']) ?></h3>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($interview['company_name']) ?></p>
                                    </div>
                                </div>
                                <div class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded uppercase tracking-wide">
                                    <?= htmlspecialchars($interview['interview_type']) ?>
                                </div>
                            </div>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center gap-3 text-sm text-gray-700">
                                    <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium"><?= htmlspecialchars($interview['date_formatted']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($interview['time_range']) ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($interview['is_video']): ?>
                                    <div class="flex items-center gap-3 text-sm text-gray-700">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-medium">Video Interview</p>
                                            <a href="<?= htmlspecialchars($joinUrl) ?>" target="_blank" class="text-xs text-blue-600 hover:underline truncate block">Join Meeting</a>
                                        </div>
                                    </div>
                                <?php elseif ($interview['is_phone']): ?>
                                    <div class="flex items-center gap-3 text-sm text-gray-700">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">Phone Interview</p>
                                            <p class="text-xs text-gray-500">Employer will call you</p>
                                        </div>
                                    </div>
                                <?php elseif ($interview['is_telephonic']): ?>
                                    <div class="flex items-center gap-3 text-sm text-gray-700">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">Telephonic Interview</p>
                                            <p class="text-xs text-gray-500">Employer will call you</p>
                                        </div>
                                    </div>
                                <?php elseif ($interview['is_onsite']): ?>
                                    <div class="flex items-center gap-3 text-sm text-gray-700">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">On-site Interview</p>
                                            <p class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars($interview['location']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <a href="/candidate/jobs/<?= htmlspecialchars($interview['job_slug'] ?? $interview['job_id']) ?>" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">
                                    View Job Details
                                </a>
                                <?php if ($interview['is_video']): ?>
                                    <a href="<?= htmlspecialchars($joinUrl) ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                                        Join Now
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past Interviews -->
    <?php if (!empty($past)): ?>
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="w-2 h-8 bg-gray-300 rounded-full"></span>
            Past Interviews
        </h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job & Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($past as $interview): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <?php if (!empty($interview['company_logo'])): ?>
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-100" src="<?= htmlspecialchars($interview['company_logo']) ?>" alt="">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold border border-gray-200">
                                                <?= strtoupper(substr($interview['company_name'] ?? 'C', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($interview['job_title']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($interview['company_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($interview['date_formatted']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($interview['time_range']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= htmlspecialchars($interview['interview_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Completed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/candidate/jobs/<?= htmlspecialchars($interview['job_slug'] ?? $interview['job_id']) ?>" class="text-blue-600 hover:text-blue-900">View Job</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = $title ?? 'My Interviews';
require __DIR__ . '/../layout.php';
?>
