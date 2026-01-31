<?php
$job = $job ?? [];
$applications = $applications ?? [];
$locations = $locations ?? [];
$skills = $skills ?? [];
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
/* Prose styles for description */
.prose {
    color: #374151;
    line-height: 1.75;
}
.prose h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #111827;
}
.prose h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #111827;
}
.prose p {
    margin-top: 1rem;
    margin-bottom: 1rem;
}
.prose ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
}
.prose li {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
.prose strong {
    font-weight: 700;
    color: #111827;
}
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= htmlspecialchars($job['title'] ?? 'Job Details') ?></h1>
            <p class="text-sm text-gray-600 mt-1">Job ID: <?= $job['id'] ?? 'N/A' ?></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>/edit" 
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            <a href="/employer/jobs" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Jobs
            </a>
        </div>
    </div>

    <!-- Job Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-gradient-to-br from-green-50 to-white rounded-xl shadow-md border border-green-100 p-4 sm:p-6">
            <p class="text-sm font-semibold text-gray-600 mb-2">Status</p>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold rounded-lg <?= ($job['status'] ?? '') === 'published' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white border border-green-400' : (($job['status'] ?? '') === 'closed' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white border border-red-400' : 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white border border-yellow-300') ?>">
                <?php if (($job['status'] ?? '') === 'published'): ?>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                <?php endif; ?>
                <?= ucfirst($job['status'] ?? 'draft') ?>
            </span>
        </div>
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-6">
            <p class="text-sm font-semibold text-gray-600 mb-2">Applications</p>
            <p class="text-3xl font-bold text-gray-900"><?= $applicationsCount ?? 0 ?></p>
            <?php if (($newApplicationsCount ?? 0) > 0): ?>
                <p class="text-sm text-purple-600 font-semibold mt-1">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?= $newApplicationsCount ?> new
                    </span>
                </p>
            <?php endif; ?>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl shadow-md border border-purple-100 p-4 sm:p-6">
            <p class="text-sm font-semibold text-gray-600 mb-2">Openings</p>
            <p class="text-3xl font-bold text-gray-900"><?= $job['openings'] ?? 1 ?></p>
        </div>
    </div>

    <!-- Job Details -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Job Details
            </h2>
        </div>
        <div class="p-4 sm:p-6 space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Job Title</label>
                <p class="text-base text-gray-900 font-semibold"><?= htmlspecialchars($job['title'] ?? 'N/A') ?></p>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                    <?= $job['description'] ?? 'N/A' ?>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Job Type</label>
                    <p class="text-base text-gray-900 font-semibold"><?= ucfirst(str_replace('_', ' ', $job['job_type'] ?? 'N/A')) ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Experience Required</label>
                    <p class="text-base text-gray-900 font-semibold"><?= htmlspecialchars($job['experience_required'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Salary Range</label>
                    <p class="text-base text-gray-900 font-semibold">
                        <?= ($job['salary_min'] ?? 0) > 0 ? number_format($job['salary_min']) : 'N/A' ?> - 
                        <?= ($job['salary_max'] ?? 0) > 0 ? number_format($job['salary_max']) : 'N/A' ?> 
                        <span class="text-sm text-gray-600"><?= $job['salary_currency'] ?? 'INR' ?></span>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Location</label>
                    <p class="text-base text-gray-900 font-semibold"><?= htmlspecialchars($job['location'] ?? 'N/A') ?></p>
                </div>
            </div>
            <?php if (!empty($skills)): ?>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Required Skills</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill): ?>
                            <span class="px-3 py-1.5 bg-purple-100 text-purple-800 rounded-lg text-sm font-semibold shadow-sm">
                                <?= htmlspecialchars($skill['name'] ?? '') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="bg-gray-50 rounded-lg p-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Posted Date</label>
                <p class="text-base text-gray-900 font-semibold"><?= date('M d, Y', strtotime($job['created_at'] ?? 'now')) ?></p>
            </div>
        </div>
    </div>

    <!-- Applications -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Applications (<?= $applicationsCount ?? 0 ?>)
                </h2>
                <a href="/employer/applications?job_id=<?= $job['id'] ?? '' ?>" 
                   class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-700 text-sm font-semibold transition hover:underline">
                    View all
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
        <div class="p-4 sm:p-6">
            <?php if (empty($applications)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm font-medium text-gray-900">No applications yet</p>
                    <p class="mt-1 text-sm text-gray-500">Applications will appear here when candidates apply.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($applications, 0, 5) as $application): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-purple-200 transition-all">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Application #<?= $application['id'] ?? 'N/A' ?></p>
                                <p class="text-sm text-gray-600 mt-1">Status: 
                                    <span class="font-semibold <?= ($application['status'] ?? '') === 'shortlisted' ? 'text-green-600' : (($application['status'] ?? '') === 'rejected' ? 'text-red-600' : 'text-gray-600') ?>">
                                        <?= ucfirst($application['status'] ?? 'pending') ?>
                                    </span>
                                </p>
                            </div>
                            <a href="/employer/applications/<?= $application['id'] ?? '' ?>" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 text-sm font-semibold transition-colors">
                                View
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

