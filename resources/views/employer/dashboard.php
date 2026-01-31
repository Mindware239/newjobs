<?php
$employer = $employer ?? null;
$stats = $stats ?? [];
$recentJobs = $recentJobs ?? [];
$recentApplications = $recentApplications ?? [];
$recentActivities = $recentActivities ?? [];
$upcomingInterviews = $upcomingInterviews ?? [];
$shortlistedCandidates = $shortlistedCandidates ?? [];
$notifications = $notifications ?? [];
$feedbacks = $feedbacks ?? [];
$hiringTeam = $hiringTeam ?? [];
$documents = $documents ?? [];
$employerName = $employer->company_name ?? 'Employer';
$currentDate = date('M d, Y');
?>
<style>
    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
<h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
    Welcome back,
    <span class="text-[#6C63FF]">
        <?= htmlspecialchars(explode(' ', $employerName)[0]) ?>
    </span>
</h1>
            <p class="text-sm text-gray-500 mt-1">Here's what's happening with your job postings today.</p>
        </div>
       
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Active Jobs -->
        <a href="/employer/jobs?status=published" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-[#7283ff]/30 shadow-sm flex items-center justify-between transition-colors group">
            <div>
                <p class="text-sm font-medium text-gray-500">Active Jobs</p>
                <div class="mt-1 flex items-center gap-2">
                    <h3 class="text-3xl font-bold text-gray-900"><?= $stats['active_jobs'] ?? 0 ?></h3>
                    <?php if (!empty($stats['active_jobs_growth'])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-600">
                        +<?= htmlspecialchars($stats['active_jobs_growth']) ?>%
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#7283ff]/15 text-[#3c50ff] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </a>

        <!-- Total Applications -->
        <a href="/employer/applications" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-blue-200 shadow-sm flex items-center justify-between transition-colors group">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Applications</p>
                <div class="mt-1 flex items-center gap-2">
                    <h3 class="text-3xl font-bold text-gray-900"><?= $stats['total_applications'] ?? 0 ?></h3>
                    <?php if (!empty($stats['total_applications_growth'])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-600">
                        +<?= htmlspecialchars($stats['total_applications_growth']) ?>%
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-6 w-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </a>

        <!-- New Applications -->
        <a href="/employer/applications?status=applied" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-emerald-200 shadow-sm flex items-center justify-between transition-colors group">
            <div>
                <p class="text-sm font-medium text-gray-500">New Applications</p>
                <div class="mt-1 flex items-center gap-2">
                    <h3 class="text-3xl font-bold text-gray-900"><?= $stats['new_applications'] ?? 0 ?></h3>
                    <?php if (!empty($stats['new_applications_growth'])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-600">
                        +<?= htmlspecialchars($stats['new_applications_growth']) ?>%
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </a>

        <!-- Interviews -->
        <a href="/employer/interviews" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-amber-200 shadow-sm flex items-center justify-between transition-colors group">
            <div>
                <p class="text-sm font-medium text-gray-500">Interviews Scheduled</p>
                <div class="mt-1 flex items-center gap-2">
                    <h3 class="text-3xl font-bold text-gray-900"><?= $stats['interviews'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- Charts & Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Jobs List -->
             <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#6d82ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h2 class="text-lg font-bold text-gray-900">Recent Jobs</h2>
                    </div>
                    <a href="/employer/jobs" class="inline-flex items-center gap-2 text-sm font-semibold text-[#6d82ff] hover:text-[#5f72ff] hover:underline">
                        View all
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (empty($recentJobs)): ?>
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <h3 class="text-gray-900 font-medium">No jobs posted yet</h3>
                            <p class="text-gray-500 text-sm mt-1">Create your first job posting to get started.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentJobs as $job): ?>
                            <div class="p-4 transition-colors flex items-center justify-between group cursor-pointer" onclick="window.location.href='/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>'">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900"><?= htmlspecialchars($job['title']) ?></h3>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span><?= htmlspecialchars($job['location_display'] ?? $job['location'] ?? 'Remote') ?></span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            <span><?= $job['applications_count'] ?? 0 ?> applications</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Posted <?= date('M d, Y', strtotime($job['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <?php
                                        $status = strtolower($job['status'] ?? '');
                                        $badgeClasses = 'px-3 py-1 rounded-full text-sm font-medium border ';
                                        if ($status === 'published') {
                                            $badgeClasses .= 'bg-success-10 text-success border-success-20';
                                        } elseif ($status === 'draft') {
                                            $badgeClasses .= 'bg-amber-50 text-amber-600 border-amber-100';
                                        } else {
                                            $badgeClasses .= 'bg-gray-50 text-gray-600 border-gray-200';
                                        }
                                    ?>
                                    <span class="<?= $badgeClasses ?>">
                                        <?= ucfirst($job['status'] ?? 'Draft') ?>
                                    </span>
                                    <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-[#3c50ff] hover:bg-blue-50 hover:shadow-md text-sm font-semibold transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
             </div>
              <!-- Chart Section -->
             <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        
                        <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-5 w-5 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg></span><h2 class="text-lg font-bold text-gray-900">Application Trends</h2>
                        <p class="text-sm text-gray-500">Application volume over the last 30 days</p>
                    </div>
                    <div class="text-right">
                         <span class="block text-2xl font-bold text-gray-900"><?= array_sum($applicationsByDate ?? []) ?></span>
                         <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total</span>
                    </div>
                </div>
                <div class="h-80 w-full">
                    <canvas id="applicationsTrendChart"></canvas>
                </div>
             </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="space-y-4">
                <h2 class="text-lg font-bold text-gray-900">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="/employer/jobs/create" class="flex flex-col items-center justify-center p-6 rounded-xl bg-white border border-gray-100 hover:bg-blue-50 hover:shadow-md transition-all duration-200 group h-40">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-[#3c50ff] flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="font-bold text-gray-900 text-sm group-hover:text-[#3c50ff] transition-colors">Post a Job</span>
                        <span class="text-xs text-gray-500 mt-1 text-center">Create new listing</span>
                    </a>
                    <a href="/employer/applications" class="flex flex-col items-center justify-center p-6 rounded-xl bg-white border border-gray-100 hover:bg-blue-50 hover:shadow-md transition-all duration-200 group h-40">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <span class="font-bold text-gray-900 text-sm group-hover:text-blue-600 transition-colors">View Applications</span>
                        <span class="text-xs text-gray-500 mt-1 text-center">Review candidates</span>
                    </a>
                    <a href="/employer/interviews" class="flex flex-col items-center justify-center p-6 rounded-xl bg-white border border-gray-100 hover:bg-[#ecfbf6] hover:shadow-md transition-all duration-200 group h-40">
                        <div class="w-10 h-10 rounded-lg bg-[#ecfbf6] text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="font-bold text-gray-900 text-sm group-hover:text-emerald-600 transition-colors">Schedule Interview</span>
                        <span class="text-xs text-gray-500 mt-1 text-center">Set up meetings</span>
                    </a>
                    <a href="/employer/applications" class="flex flex-col items-center justify-center p-6 rounded-xl bg-white border border-gray-100 hover:bg-[#fff6e9] hover:shadow-md transition-all duration-200 group h-40">
                        <div class="w-10 h-10 rounded-lg bg-[#fff6e9] text-amber-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <span class="font-bold text-gray-900 text-sm group-hover:text-amber-600 transition-colors">Search Candidates</span>
                        <span class="text-xs text-gray-500 mt-1 text-center">Find talent</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 h-[400px] flex flex-col">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex-shrink-0">Recent Activity</h2>
                <div class="flex-1 overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <?php 
                                $type = $activity['type'] ?? 'default';
                                $iconBg = 'bg-gray-50';
                                $iconText = 'text-gray-600';
                                $icon = '';
                                $title = 'Activity';
                                
                                switch($type) {
                                    case 'application':
                                        $iconBg = 'bg-blue-50';
                                        $iconText = 'text-blue-600';
                                        $title = 'New Application';
                                        $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12h2m-1-1v2"></path></svg>'; // Person with +
                                        break;
                                    case 'job_posted':
                                        $iconBg = 'bg-purple-50';
                                        $iconText = 'text-purple-600';
                                        $title = 'Job Posted';
                                        $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>'; // Briefcase
                                        break;
                                    case 'interview':
                                        $iconBg = 'bg-amber-50';
                                        $iconText = 'text-amber-600';
                                        $title = 'Interview Scheduled';
                                        $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'; // Clock
                                        break;
                                    default:
                                        $iconBg = 'bg-blue-50';
                                        $iconText = 'text-blue-600';
                                        $title = 'Notification';
                                        $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'; // Bell
                                }
                            ?>
                            <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl <?= $iconBg ?> <?= $iconText ?> flex items-center justify-center">
                                    <?= $icon ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($title) ?></h4>
                                    <p class="text-sm text-gray-500 mt-0.5 truncate"><?= htmlspecialchars($activity['message']) ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?= date('M d, H:i', strtotime($activity['created_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-center py-8">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-sm text-gray-500">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shortlisted Candidates -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Shortlisted</h2>
                    <a href="/employer/applications?status=shortlisted" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">View All</a>
                </div>
                 <div class="space-y-3">
                     <?php if (!empty($shortlistedCandidates)): ?>
                        <?php foreach ($shortlistedCandidates as $candidate): ?>
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group">
                                 <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 font-bold text-gray-600 overflow-hidden ring-2 ring-transparent group-hover:ring-[#7283ff]/30 transition-all">
                                    <?php if (!empty($candidate['profile_picture'])): ?>
                                        <img src="<?= htmlspecialchars($candidate['profile_picture']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= strtoupper(substr($candidate['name'] ?? 'C', 0, 1)) ?>
                                    <?php endif; ?>
                                 </div>
                                 <div class="flex-1 min-w-0">
                                     <h4 class="text-sm font-bold text-gray-900 truncate group-hover:text-[#3c50ff] transition-colors"><?= htmlspecialchars($candidate['name']) ?></h4>
                                     <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($candidate['job_title']) ?></p>
                                 </div>
                                 <div class="flex items-center text-amber-500 gap-1 text-xs font-bold">
                                     <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                     4.8
                                 </div>
                            </div>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <div class="text-center py-6">
                             <p class="text-sm text-gray-500">No shortlisted candidates</p>
                        </div>
                     <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        <?php
        // Keep existing PHP logic for data fetching
        $db = \App\Core\Database::getInstance();
        $jobIdsArray = [];
        if ($employer) {
            try {
                $jobIdsQuery = $db->fetchAll("SELECT id FROM jobs WHERE employer_id = :employer_id", ['employer_id' => $employer->id]);
                $jobIdsArray = array_column($jobIdsQuery, 'id');
            } catch (\Exception $e) {
                $jobIdsArray = [];
            }
        }

        $applicationsByDate = [];
        if (!empty($jobIdsArray)) {
            $placeholders = implode(',', array_fill(0, count($jobIdsArray), '?'));
            $sql = "SELECT DATE(applied_at) as date, COUNT(*) as count 
                    FROM applications 
                    WHERE job_id IN ($placeholders) 
                    AND applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(applied_at)
                    ORDER BY date ASC";
            try {
                $results = $db->fetchAll($sql, $jobIdsArray);
            } catch (\Exception $e) {
                $results = [];
            }

            // Create array with dates for last 30 days
            $dates = [];
            for ($i = 29; $i >= 0; $i--) {
                $dates[] = date('Y-m-d', strtotime("-$i days"));
            }

            $applicationsByDate = array_fill_keys($dates, 0);
            foreach ($results as $row) {
                if (isset($applicationsByDate[$row['date']])) {
                    $applicationsByDate[$row['date']] = (int)$row['count'];
                }
            }
        } else {
            // If no jobs, create empty data for 30 days
            for ($i = 29; $i >= 0; $i--) {
                $applicationsByDate[date('Y-m-d', strtotime("-$i days"))] = 0;
            }
        }
        ?>

        // Applications Trend Chart
        const ctx = document.getElementById('applicationsTrendChart').getContext('2d');
        
        // Gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(60, 80, 255, 0.20)');
        gradient.addColorStop(1, 'rgba(60, 80, 255, 0.00)');

        const applicationsData = <?= json_encode(array_values($applicationsByDate)) ?>;
        const labels = <?= json_encode(array_map(function ($date) {
                            return date('M d', strtotime($date));
                        }, array_keys($applicationsByDate))) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Applications',
                    data: applicationsData,
                    borderColor: '#3C50FF',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#3C50FF',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        titleFont: {
                            size: 13,
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 13,
                            family: "'Inter', sans-serif"
                        },
                        displayColors: false,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' applications';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            },
                            color: '#9CA3AF'
                        },
                        grid: {
                            color: '#F3F4F6',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            maxTicksLimit: 7,
                            font: {
                                size: 11
                            },
                            color: '#9CA3AF'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    </script>
</div>
