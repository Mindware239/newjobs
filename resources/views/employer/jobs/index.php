<?php
$jobs = $jobs ?? [];
$filters = $filters ?? [];
$employer = $employer ?? null;
$subscription = $subscription ?? null;
?>

<style>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
/* Vertical scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Jobs</h1>
            <p class="text-sm text-gray-600">Manage your job postings</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/employer/jobs/create"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 font-semibold shadow-sm transition-all text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Post a Job
            </a>
        </div>
    </div>

    <!-- Subscription Status -->
    <?php if (isset($subscription)): ?>
    <div class="bg-gradient-to-r from-[#f05537]-50 to-[#f05537]-50 rounded-lg shadow-sm border border-primary p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-primary text-white p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">
                        Current Plan: <span class="text-primary-600"><?= htmlspecialchars($subscription['name']) ?></span>
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $subscription['isActive'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $subscription['isActive'] ? 'Active' : 'Inactive' ?>
                        </span>
                        <?php if ($subscription['expiry']): ?>
                        <span class="text-xs text-gray-500">
                            Expires on <?= date('M j, Y', strtotime($subscription['expiry'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="/employer/subscription/plans" class="text-sm font-medium text-primary hover:text-primary-900 hover:underline">
                Manage Plan &rarr;
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $savedState = (string)($_GET['saved'] ?? '');
    $savedMessages = [
        'pending_review' => ['Job submitted for review', 'Our team will review it and it will go live soon – usually within 24 hours. We will notify you when it is published.', 'bg-yellow-50 border-yellow-300 text-yellow-900'],
        'published' => ['Your job is live!', 'Candidates can now find and apply to it.', 'bg-green-50 border-green-300 text-green-900'],
        'draft' => ['Draft saved', 'You can finish it and publish any time.', 'bg-blue-50 border-blue-300 text-blue-900'],
        'paid' => ['Payment received – job submitted', 'Thank you. Your ₹200 + GST job post is paid and the job has been submitted – it goes live after our check, usually within 24 hours. The receipt is in your email.', 'bg-green-50 border-green-300 text-green-900'],
        'pay' => ['Pay ₹200 + GST to post this job', 'Your job is saved as a draft. Your free job / plan credits are used – pay ₹200 + GST for this one job, or choose a plan for more jobs.', 'bg-yellow-50 border-yellow-300 text-yellow-900'],
        'updated' => ['Job updated', 'Your changes have been saved.', 'bg-green-50 border-green-300 text-green-900'],
    ];
    if (isset($savedMessages[$savedState])): [$sTitle, $sText, $sCls] = $savedMessages[$savedState]; ?>
    <div class="rounded-lg border px-4 py-3 <?= $sCls ?>" role="status">
        <div class="font-bold"><?= htmlspecialchars($sTitle) ?></div>
        <div class="text-sm"><?= htmlspecialchars($sText) ?></div>
    </div>
    <?php endif; ?>
    <div id="job-action-notice" class="hidden rounded-lg border px-4 py-3" role="alert"></div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
        <nav class="flex flex-wrap gap-2">
            <a href="/employer/jobs?status=all"
               class="px-4 py-2 rounded-lg font-medium text-sm transition-all <?= ($filters['status'] ?? 'all') === 'all' ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                All jobs
            </a>
            <a href="/employer/jobs?status=published"
               class="px-4 py-2 rounded-lg font-medium text-sm transition-all <?= ($filters['status'] ?? '') === 'published' ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Published
            </a>
            <a href="/employer/jobs?status=pending_review"
               class="px-4 py-2 rounded-lg font-medium text-sm transition-all <?= ($filters['status'] ?? '') === 'pending_review' ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Under review
            </a>
            <a href="/employer/jobs?status=draft"
               class="px-4 py-2 rounded-lg font-medium text-sm transition-all <?= ($filters['status'] ?? '') === 'draft' ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Drafts
            </a>
            <a href="/employer/jobs?status=closed"
               class="px-4 py-2 rounded-lg font-medium text-sm transition-all <?= ($filters['status'] ?? '') === 'closed' ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Closed
            </a>
        </nav>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select id="status-filter"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-white">
                    <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="pending_review" <?= ($filters['status'] ?? '') === 'pending_review' ? 'selected' : '' ?>>Under review</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="paused" <?= ($filters['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input type="text"
                           id="search-filter"
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                           placeholder="Job title..."
                           onkeypress="if(event.key === 'Enter') applyFilters()"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                <div class="relative">
                    <input type="text"
                           id="location-filter"
                           value="<?= htmlspecialchars($filters['location'] ?? '') ?>"
                           placeholder="Location..."
                           onkeypress="if(event.key === 'Enter') applyFilters()"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()"
                        class="w-full px-4 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-600 font-semibold shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Apply Filters</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Count -->
    <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-gray-700">
            <span class="text-gray-900"><?= count($jobs) ?></span> <?= count($jobs) == 1 ? 'job' : 'jobs' ?> found
        </div>
    </div>

    <!-- Jobs List (Cards) -->
    <div class="space-y-4">
        <?php if (empty($jobs)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No jobs found</h3>
                <p class="text-sm text-gray-600 mb-6">Get started by posting your first job.</p>
                <a href="/employer/jobs/create"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-600 font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Post a job</span>
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job):
                $statusColors = [
                    'published' => 'bg-green-100 text-green-800 border-green-300',
                    'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    'closed' => 'bg-red-100 text-red-800 border-red-300',
                    'paused' => 'bg-gray-100 text-gray-800 border-gray-300'
                ];
                $statusColor = $statusColors[$job['status'] ?? 'draft'] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                $jobStatus = (string)($job['status'] ?? '') ?: 'draft';
                $isActive = $jobStatus === 'published';
                $isInactive = !$isActive;
                $jobRef = htmlspecialchars((string)($job['slug'] ?? $job['id']), ENT_QUOTES);
                [$badgeLabel, $badgeCls] = [
                    'published' => ['Live', 'bg-green-100 text-green-700'],
                    'pending_review' => ['Under review', 'bg-yellow-100 text-yellow-800'],
                    'draft' => ['Draft', 'bg-gray-100 text-gray-700'],
                    'paused' => ['Paused', 'bg-blue-100 text-blue-700'],
                    'closed' => ['Closed', 'bg-gray-200 text-gray-700'],
                    'rejected' => ['Rejected', 'bg-red-100 text-red-700'],
                    'taken_down' => ['Taken down', 'bg-red-100 text-red-700'],
                    'archived' => ['Archived', 'bg-gray-200 text-gray-700'],
                ][$jobStatus] ?? [ucfirst($jobStatus), 'bg-gray-100 text-gray-700'];

                // Experience Logic
                $etype = $job['experience_type'] ?? '';
                $minExp = $job['min_experience'] ?? null;
                $maxExp = $job['max_experience'] ?? null;
                $expText = 'Any Exp';
                if ($etype === 'fresher') {
                    $expText = 'Fresher';
                } elseif ($etype === 'any') {
                    $expText = 'Any Exp';
                } elseif (($minExp !== null && $maxExp !== null) && (($minExp > 0) || ($maxExp > 0))) {
                    $expText = ($minExp == 1 ? '1 year' : $minExp . ' years') . ' - ' . ($maxExp == 1 ? '1 year' : $maxExp . ' years');
                }

                // Salary Logic
                $minSal = $job['salary_min'] ?? 0;
                $maxSal = $job['salary_max'] ?? 0;
                $salaryText = 'Not disclosed';
                if ($minSal > 0 || $maxSal > 0) {
                     $salaryText = '₹' . number_format($minSal) . ' - ₹' . number_format($maxSal) . ' per month';
                }
            ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 transition-all hover:shadow-md">
                    <!-- Top Row: Title, Status, Edit -->
                    <div class="flex flex-col sm:flex-row justify-between items-start mb-2 gap-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-lg font-bold text-primary hover:underline">
                                <a href="/employer/jobs/<?= htmlspecialchars((string)($job['slug'] ?? $job['id'])) ?>">
                                    <?= htmlspecialchars((string)$job['title']) ?>
                                </a>
                            </h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeCls ?>">
                                <?= htmlspecialchars($badgeLabel) ?>
                            </span>
                        </div>
                        <a href="/employer/jobs/<?= htmlspecialchars((string)($job['slug'] ?? $job['id'])) ?>/edit"
                           class="text-primary hover:text-primary-900 text-sm font-medium flex items-center gap-1 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Edit Job
                        </a>
                    </div>

                    <!-- Company Info -->
                    <div class="text-sm text-gray-500 mb-4">
                        <?= htmlspecialchars($employer->company_name ?? 'Company Name') ?> •
                        <?= htmlspecialchars($job['location_display'] ?? $job['location'] ?? 'Location') ?>
                    </div>

                    <!-- Details Row -->
                    <div class="text-sm text-gray-400 mb-6 flex flex-wrap gap-2 items-center">
                        <span><?= $expText ?></span>
                        <span>|</span>
                        <span><?= htmlspecialchars($job['education'] ?? 'Graduate') ?></span>
                        <span>|</span>
                        <span><?= htmlspecialchars($job['language'] ?? 'English') ?></span>
                        <span>|</span>
                        <span><?= $salaryText ?></span>
                        <span>|</span>
                        <span><?= ucfirst(str_replace('_', ' ', $job['employment_type'] ?? 'Full Time')) ?></span>
                    </div>

                    <!-- Metrics Boxes -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Responses -->
                        <a href="/employer/applications?job_id=<?= $job['id'] ?>" class="block group">
                            <div class="bg-white border border-primary rounded-lg p-4 flex items-center justify-between hover:border-primary transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-primary"><?= $job['applications_count'] ?? 0 ?> Responses</div>
                                        <div class="text-xs text-gray-500">From Candidates</div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-primary group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </a>

                        <!-- Hot Leads -->
                        <a href="/employer/applications?job_id=<?= $job['id'] ?>&status=shortlist" class="block group">
                            <div class="bg-white border border-orange-100 rounded-lg p-4 flex items-center justify-between hover:border-orange-300 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-orange-900"><?= $job['shortlisted_count'] ?? 0 ?> Hot Leads</div>
                                        <div class="text-xs text-gray-500">Shortlisted candidates</div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-orange-400 group-hover:text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </a>

                        <!-- Candidates -->
                        <a href="/employer/applications?job_id=<?= $job['id'] ?>&status=new" class="block group">
                            <div class="bg-white border border-primary rounded-lg p-4 flex items-center justify-between hover:border-primary transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-primary-900"><?= $job['new_applications_count'] ?? 0 ?> New Candidates</div>
                                        <div class="text-xs text-gray-500">Unreviewed applications</div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-primary group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </a>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-400">
                            Posted on: <?= date('jS F y', strtotime($job['created_at'])) ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if ($jobStatus === 'pending_review'): ?>
                                <span class="text-xs font-medium text-yellow-700">Awaiting approval</span>
                            <?php elseif (in_array($jobStatus, ['draft', 'rejected'], true)): ?>
                                <button onclick="updateJobStatus('<?= $jobRef ?>', 'submit')"
                                        class="px-4 py-1.5 bg-primary text-white text-sm font-medium rounded hover:opacity-90 transition-colors">
                                    <?= $jobStatus === 'rejected' ? 'Resubmit' : 'Publish' ?>
                                </button>
                            <?php elseif ($jobStatus === 'paused'): ?>
                                <button onclick="updateJobStatus('<?= $jobRef ?>', 'published')"
                                        class="px-4 py-1.5 bg-primary text-white text-sm font-medium rounded hover:opacity-90 transition-colors">
                                    Resume
                                </button>
                            <?php endif; ?>

                            <div class="relative inline-block text-left">
                                <button onclick="toggleActionsMenu(<?= $job['id'] ?>)"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                <!-- Dropdown Menu -->
                                <div id="actions-menu-<?= $job['id'] ?>"
                                     class="hidden absolute right-0 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                    <div class="py-1">
                                        <?php if ($isActive): ?>
                                            <button onclick="updateJobStatus('<?= $jobRef ?>', 'paused')"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Pause (hide temporarily)
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($jobStatus, ['published', 'paused', 'pending_review', 'draft'], true)): ?>
                                            <button onclick="updateJobStatus('<?= $jobRef ?>', 'closed')"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Close job
                                            </button>
                                        <?php endif; ?>
                                        <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Job</a>
                                        <button onclick="deleteJob('<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>')"
                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Delete Job
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const search = document.getElementById('search-filter').value;
    const location = document.getElementById('location-filter').value;

    const params = new URLSearchParams();
    if (status && status !== 'all') params.set('status', status);
    if (search) params.set('search', search);
    if (location) params.set('location', location);

    window.location.href = '/employer/jobs?' + params.toString();
}

function jobActionNotice(message, type, link, linkText) {
    const box = document.getElementById('job-action-notice');
    const cls = { error: 'bg-red-50 border-red-300 text-red-900', warning: 'bg-yellow-50 border-yellow-300 text-yellow-900', success: 'bg-green-50 border-green-300 text-green-900' };
    box.className = 'rounded-lg border px-4 py-3 ' + (cls[type] || cls.error);
    box.textContent = message;
    if (link) {
        const a = document.createElement('a');
        a.href = link; a.textContent = ' ' + (linkText || 'View plans →'); a.className = 'font-bold underline ml-1';
        box.appendChild(a);
    }
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function jobRequest(url, method, body) {
    return fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                   'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: body ? JSON.stringify(body) : undefined,
        credentials: 'same-origin'
    }).then(r => r.json().catch(() => ({})).then(data => ({ ok: r.ok, status: r.status, data })));
}

function updateJobStatus(jobRef, status) {
    const prompts = { submit: 'Submit this job for publishing?', published: 'Resume this job? It will be visible to candidates again.',
                      paused: 'Pause this job? It will be hidden from candidates until you resume it.', closed: 'Close this job? It will stop accepting applications.' };
    if (!confirm(prompts[status] || 'Change the job status?')) return;
    jobRequest('/employer/jobs/' + encodeURIComponent(jobRef) + '/status', 'POST', { status })
        .then(({ ok, status: code, data }) => {
            if (ok) { window.location.href = '/employer/jobs?saved=' + encodeURIComponent(data.status === 'pending_review' || data.status === 'published' ? data.status : 'updated'); return; }
            jobActionNotice(data.message || 'Could not update the job. Please try again.', code === 402 ? 'warning' : 'error', code === 402 ? data.redirect : null, data.pay_per_post ? 'Pay ₹200 + GST for this job →' : null);
        })
        .catch(() => jobActionNotice('Could not reach the server. Please check your connection and try again.'));
}

function deleteJob(jobSlug) {
    if (!confirm('Delete this job permanently? This cannot be undone.')) return;
    jobRequest('/employer/jobs/' + encodeURIComponent(jobSlug), 'DELETE')
        .then(({ ok, data }) => {
            if (ok && data.success) { window.location.reload(); return; }
            jobActionNotice(data.message || data.error || 'Could not delete the job.');
        })
        .catch(() => jobActionNotice('Could not reach the server. Please check your connection and try again.'));
}

function toggleActionsMenu(jobId) {
    const menu = document.getElementById('actions-menu-' + jobId);
    const allMenus = document.querySelectorAll('[id^="actions-menu-"]');

    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== 'actions-menu-' + jobId) {
            m.classList.add('hidden');
        }
    });

    // Toggle current menu
    menu.classList.toggle('hidden');
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.relative')) {
        const allMenus = document.querySelectorAll('[id^="actions-menu-"]');
        allMenus.forEach(m => {
            m.classList.add('hidden');
        });
    }
});
</script>











