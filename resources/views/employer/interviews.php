<?php

/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $interviews
 * @var array $stats
 * @var array $filters
 */
$currentStatus = $filters['status'] ?? 'all';
$currentType = $filters['type'] ?? 'all';
$currentSort = $filters['sort_by'] ?? 'date';
$searchQuery = $filters['search'] ?? '';
?>
<div class="mb-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Interviews</h1>
            <p class="text-sm text-gray-600">Manage and track all your candidate interviews</p>
        </div>
        <button onclick="openScheduleModal()" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 w-full sm:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Schedule Interview</span>
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Interviews -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-gray-50 text-gray-600 rounded-lg group-hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['total'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">All time interviews</p>
            </div>
        </div>

        <!-- Upcoming -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Upcoming</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['upcoming'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">Scheduled future</p>
            </div>
        </div>

        <!-- Today -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Today</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['today'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">Happening today</p>
            </div>
        </div>

        <!-- This Week -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Week</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['this_week'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">Next 7 days</p>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-green-600 uppercase tracking-wider">Done</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['completed'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">Successfully finished</p>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-red-50 text-red-600 rounded-lg group-hover:bg-red-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Declined</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1"><?= $stats['cancelled'] ?? 0 ?></p>
                <p class="text-xs text-gray-500">Withdrawn/Missed</p>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-8">
        <!-- Status Filter Tabs -->
        <div class="bg-gray-100/80 p-1.5 rounded-xl inline-flex flex-wrap gap-1 overflow-x-auto max-w-full">
            <a href="?status=all&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                All
            </a>
            <a href="?status=upcoming&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'upcoming' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                Upcoming
            </a>
            <a href="?status=today&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'today' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                Today
            </a>
            <a href="?status=week&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                This Week
            </a>
            <a href="?status=completed&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'completed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                Completed
            </a>
            <a href="?status=cancelled&type=<?= $currentType ?>&sort_by=<?= $currentSort ?>&search=<?= urlencode($searchQuery) ?>" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap <?= $currentStatus === 'cancelled' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' ?>">
                Declined
            </a>
        </div>

        <!-- Search and Filters -->
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Search -->
            <div class="relative flex-1 sm:min-w-[240px]">
                <input type="text" 
                       id="searchInput"
                       value="<?= htmlspecialchars($searchQuery) ?>"
                       placeholder="Search..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow">
                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Type Filter -->
            <div class="relative">
                <select id="typeFilter" 
                        onchange="applyFilters()"
                        class="appearance-none w-full pl-4 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm cursor-pointer hover:border-gray-300 transition-colors">
                    <option value="all" <?= $currentType === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="phone" <?= $currentType === 'phone' ? 'selected' : '' ?>>Phone</option>
                    <option value="video" <?= $currentType === 'video' ? 'selected' : '' ?>>Video</option>
                    <option value="onsite" <?= $currentType === 'onsite' ? 'selected' : '' ?>>On-site</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Sort -->
            <div class="relative">
                <select id="sortFilter" 
                        onchange="applyFilters()"
                        class="appearance-none w-full pl-4 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm cursor-pointer hover:border-gray-300 transition-colors">
                    <option value="date" <?= $currentSort === 'date' ? 'selected' : '' ?>>Latest First</option>
                    <option value="candidate" <?= $currentSort === 'candidate' ? 'selected' : '' ?>>Candidate Name</option>
                    <option value="job" <?= $currentSort === 'job' ? 'selected' : '' ?>>Job Title</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Interviews List -->
    <?php if (empty($interviews)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Interviews Found</h3>
            <p class="text-sm text-gray-600 mb-6"><?= $currentStatus !== 'all' ? 'No interviews match your current filter.' : 'Schedule your first interview to get started.' ?></p>
            <?php if ($currentStatus === 'all'): ?>
                <button onclick="openScheduleModal()" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Schedule Interview</span>
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php
            foreach ($interviews as $interview):
                $statusColor = match ($interview['status']) {
                    'scheduled', 'rescheduled' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'live' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'completed' => 'bg-green-50 text-green-700 border-green-200',
                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                    default => 'bg-gray-50 text-gray-700 border-gray-200'
                };
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 overflow-hidden"
                     data-interview-id="<?= $interview['id'] ?>"
                     data-scheduled-start="<?= htmlspecialchars($interview['scheduled_start']) ?>"
                     data-scheduled-end="<?= htmlspecialchars($interview['scheduled_end']) ?>"
                     data-location="<?= htmlspecialchars($interview['location'] ?? '') ?>"
                     data-meeting-link="<?= htmlspecialchars($interview['meeting_link'] ?? '') ?>">
                    <div class="p-6 flex flex-col lg:flex-row gap-6 items-start lg:items-center">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <?php if (!empty($interview['candidate_picture'])): ?>
                                <img src="<?= htmlspecialchars($interview['candidate_picture']) ?>" 
                                     alt="<?= htmlspecialchars($interview['candidate_name'] ?? 'Candidate') ?>"
                                     class="w-16 h-16 rounded-xl object-cover border border-gray-100">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xl border border-blue-100">
                                    <?= strtoupper(substr($interview['candidate_name'] ?? 'C', 0, 1)) . strtoupper(substr(explode(' ', $interview['candidate_name'] ?? '')[1] ?? '', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Main Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                <?= htmlspecialchars($interview['candidate_name'] ?? 'Unknown Candidate') ?>
                            </h3>
                            
                            <div class="flex items-center text-blue-600 mb-4 gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <a href="/employer/jobs/<?= htmlspecialchars($interview['job_slug'] ?? $interview['job_id']) ?>" class="font-medium hover:underline">
                                    <?= htmlspecialchars($interview['job_title'] ?? 'Unknown Job') ?>
                                </a>
                            </div>
                            
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">
                                    <?= htmlspecialchars((string)($interview['interview_type'] ?? '—')) ?>
                                </span>
                                <?php if (!empty($interview['platform_label'])): ?>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <?= htmlspecialchars((string)$interview['platform_label']) ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500">
                                <!-- Date/Time -->
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span><?= $interview['formatted_date'] ?> • <?= $interview['formatted_time'] ?> - <?= $interview['formatted_end_time'] ?></span>
                                </div>
                                
                                <!-- Phone -->
                                <?php if (!empty($interview['candidate_phone'])): ?>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <span>Phone</span>
                                </div>
                                <?php endif; ?>

                                <!-- Email -->
                                <?php if (!empty($interview['candidate_email'])): ?>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span><?= htmlspecialchars($interview['candidate_email']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Side -->
                        <div class="flex flex-col items-start lg:items-end gap-4 w-full lg:w-auto lg:min-w-[240px]">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $statusColor ?> min-w-[120px] text-center">
                                <?= ucfirst($interview['status']) ?>
                            </span>

                            <div class="flex flex-wrap items-center gap-2 w-full lg:justify-end">
                                <?php if (($interview['interview_type'] ?? '') === 'video' && !empty($interview['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($interview['meeting_link']) ?>"
                                       class="px-3 py-1.5 text-xs font-bold text-white bg-purple-600 rounded hover:bg-purple-700 transition-colors flex items-center gap-1 shadow-sm"
                                       target="_blank" rel="noopener noreferrer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        Join
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array($interview['status'], ['scheduled', 'rescheduled'])): ?>
                                    <?php if ($interview['is_past']): ?>
                                        <button onclick="markComplete(<?= $interview['id'] ?>)" 
                                                class="px-3 py-1.5 text-xs font-bold text-green-700 bg-green-50 border border-green-600 rounded hover:bg-green-100 transition-colors flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Complete
                                        </button>
                                    <?php endif; ?>
                                    <button
    onclick="rescheduleInterview(<?= $interview['id'] ?>)"
    class="
        inline-flex items-center justify-center gap-1.5
        h-9 px-3
        text-sm font-medium
        rounded-md

        border border-green-500/30
        bg-white
        text-green-600

        transition-colors
        hover:bg-green-500/10
        hover:text-green-600

        focus:outline-none
        focus:ring-2 focus:ring-green-500/30 focus:ring-offset-2
    "
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-3.5 w-3.5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
        <path d="M3 3v5h5"></path>
        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
        <path d="M16 16h5v5"></path>
    </svg>

    Reschedule
</button>

                                    <button onclick="cancelInterview(<?= $interview['id'] ?>)" 
                                            class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 border border-red-600 rounded hover:bg-red-100 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Decline
                                    </button>
                                <?php endif; ?>
                                
                                <a href="/employer/applications/<?= $interview['application_id'] ?>" 
                                   class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Schedule Interview Modal -->
<div id="scheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="if(event.target === this) closeScheduleModal()">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Schedule Interview</h2>
                <button onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-6">To schedule a new interview, please go to the candidate's application page and click "Schedule Interview".</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeScheduleModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors">
                    Close
                </button>
                <a href="/employer/applications" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-semibold shadow-sm hover:shadow transition-all">
                    Go to Applications
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Interview Modal -->
<div id="rescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="if(event.target === this) closeRescheduleModal()">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Reschedule Interview</h2>
                <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="rescheduleInterviewForm" onsubmit="submitRescheduleInterview(event)" class="p-6 space-y-6">
            <input type="hidden" id="rescheduleInterviewId" name="interview_id">
            
            <!-- Date -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Date *</label>
                <input type="date" 
                       id="reschedule_date"
                       name="scheduled_date" 
                       required
                       min="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
            </div>

            <!-- Time Range -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Start Time *</label>
                    <input type="time" 
                           id="reschedule_start_time"
                           name="scheduled_time" 
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">End Time *</label>
                    <input type="time" 
                           id="reschedule_end_time"
                           name="end_time" 
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                </div>
            </div>

            <!-- Timezone -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Timezone</label>
                <select name="timezone" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                    <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">America/New_York (EST)</option>
                    <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                    <option value="Europe/London">Europe/London (GMT)</option>
                </select>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Location</label>
                <input type="text" 
                       id="reschedule_location"
                       name="location" 
                       placeholder="Enter interview location"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
            </div>

            <!-- Meeting Link -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Meeting Link</label>
                <input type="url" 
                       id="reschedule_meeting_link"
                       name="meeting_link" 
                       placeholder="https://meet.example.com/room-id"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
            </div>
            <div id="rescheduleError" class="hidden text-sm rounded-xl border p-3 bg-red-50 text-red-700 border-red-200"></div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" 
                        onclick="closeRescheduleModal()" 
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-amber-600 text-white rounded-xl hover:bg-amber-700 font-semibold shadow-sm hover:shadow transition-all border border-amber-700">
                    Reschedule Interview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    const sort = document.getElementById('sortFilter').value;
    const status = '<?= $currentStatus ?>';
    
    const params = new URLSearchParams({
        status: status,
        type: type,
        sort_by: sort,
        search: search
    });
    
    window.location.href = '/employer/interviews?' + params.toString();
}

// Search on Enter key
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// Auto-update end time in reschedule modal
document.addEventListener('DOMContentLoaded', function() {
    const rescheduleStartTime = document.getElementById('reschedule_start_time');
    const rescheduleEndTime = document.getElementById('reschedule_end_time');
    
    if (rescheduleStartTime && rescheduleEndTime) {
        rescheduleStartTime.addEventListener('change', function() {
            if (this.value) {
                const [hours, minutes] = this.value.split(':');
                const startDate = new Date();
                startDate.setHours(parseInt(hours), parseInt(minutes));
                startDate.setHours(startDate.getHours() + 1); // Add 1 hour
                
                const endHours = String(startDate.getHours()).padStart(2, '0');
                const endMinutes = String(startDate.getMinutes()).padStart(2, '0');
                rescheduleEndTime.value = `${endHours}:${endMinutes}`;
            }
        });
    }
});

function openScheduleModal() {
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

function rescheduleInterview(interviewId) {
    // Get interview data from the page (from the interview card)
    const interviewCard = document.querySelector(`[data-interview-id="${interviewId}"]`);
    if (interviewCard) {
        const interviewData = {
            scheduled_start: interviewCard.dataset.scheduledStart,
            scheduled_end: interviewCard.dataset.scheduledEnd,
            location: interviewCard.dataset.location || '',
            meeting_link: interviewCard.dataset.meetingLink || ''
        };
        
        document.getElementById('rescheduleInterviewId').value = interviewId;
        
        // Set date and time
        if (interviewData.scheduled_start) {
            const startDate = new Date(interviewData.scheduled_start);
            document.getElementById('reschedule_date').value = startDate.toISOString().split('T')[0];
            document.getElementById('reschedule_start_time').value = startDate.toTimeString().slice(0, 5);
        }
        
        if (interviewData.scheduled_end) {
            const endDate = new Date(interviewData.scheduled_end);
            document.getElementById('reschedule_end_time').value = endDate.toTimeString().slice(0, 5);
        }
        
        // Set location and meeting link
        if (interviewData.location) {
            document.getElementById('reschedule_location').value = interviewData.location;
        }
        if (interviewData.meeting_link) {
            document.getElementById('reschedule_meeting_link').value = interviewData.meeting_link;
        }
        
        document.getElementById('rescheduleModal').classList.remove('hidden');
    } else {
        // Fallback: just open modal (user can fill manually)
        document.getElementById('rescheduleInterviewId').value = interviewId;
        document.getElementById('rescheduleModal').classList.remove('hidden');
    }
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    document.getElementById('rescheduleInterviewForm').reset();
}

async function submitRescheduleInterview(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const interviewId = formData.get('interview_id');
    
    const data = {
        scheduled_start: formData.get('scheduled_date') + ' ' + formData.get('scheduled_time'),
        scheduled_end: formData.get('scheduled_date') + ' ' + formData.get('end_time'),
        timezone: formData.get('timezone') || 'Asia/Kolkata',
        location: formData.get('location') || '',
        meeting_link: formData.get('meeting_link') || ''
    };

    const errorBox = document.getElementById('rescheduleError');
    if (errorBox) {
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    }
    if (!formData.get('scheduled_date') || !formData.get('scheduled_time') || !formData.get('end_time')) {
        if (errorBox) {
            errorBox.textContent = 'Please fill date, start time, and end time';
            errorBox.classList.remove('hidden');
        }
        return;
    }
    const start = new Date(`${formData.get('scheduled_date')}T${formData.get('scheduled_time')}:00`);
    const end = new Date(`${formData.get('scheduled_date')}T${formData.get('end_time')}:00`);
    if (end <= start) {
        if (errorBox) {
            errorBox.textContent = 'End time must be after start time';
            errorBox.classList.remove('hidden');
        }
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Rescheduling...';

    try {
        const response = await fetch(`/employer/interviews/${interviewId}/reschedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(data)
        });

        const result = await response.json().catch(() => ({}));
        
        if (response.ok && result.success) {
            alert('Interview rescheduled successfully!');
            closeRescheduleModal();
            location.reload();
        } else {
            if (errorBox) {
                errorBox.textContent = result.error || result.message || 'Failed to reschedule interview';
                errorBox.classList.remove('hidden');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        if (errorBox) {
            errorBox.textContent = 'An error occurred while rescheduling the interview';
            errorBox.classList.remove('hidden');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

async function cancelInterview(interviewId) {
    if (!confirm('Are you sure you want to cancel this interview? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/employer/interviews/${interviewId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Interview cancelled successfully!');
            location.reload();
        } else {
            alert(result.error || 'Failed to cancel interview');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the interview');
    }
}

async function markComplete(interviewId) {
    if (!confirm('Mark this interview as completed?')) {
        return;
    }

    try {
        const response = await fetch(`/employer/interviews/${interviewId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Interview marked as completed!');
            location.reload();
        } else {
            alert(result.error || 'Failed to complete interview');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while completing the interview');
    }
}
</script>
