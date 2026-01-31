<?php
/**
 * @var string $title
 * @var array $job
 * @var \App\Models\Employer $employer
 * @var \App\Models\Employer $jobEmployer
 * @var array $locations
 * @var array $skills
 */
?>

<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Preview Banner -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Preview Mode</p>
                        <p class="text-xs text-blue-600">This is how your job will appear to candidates on the website</p>
                    </div>
                </div>
                <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>/edit"
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition duration-150">
                    Edit Job
                </a>
            </div>
        </div>

        <!-- Job Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($job['title']) ?></h1>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($jobEmployer->company_name ?? ($employer->company_name ?? 'Company Name')) ?></span>
                        <?php if ($jobEmployer->website ?? $employer->website ?? null): ?>
                            <a href="<?= htmlspecialchars($jobEmployer->website ?? $employer->website) ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button class="px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium transition duration-150">
                        Apply now
                    </button>
                    <button class="p-2.5 border border-gray-300 rounded-md hover:bg-gray-50 transition duration-150">
                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Location -->
            <?php if (!empty($locations)): ?>
                <div class="flex items-center text-gray-600 mt-4">
                    <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>
                        <?php
                        $locParts = [];
                        foreach ($locations as $loc) {
                            $parts = array_filter([$loc['city'], $loc['state'], $loc['country']]);
                            if (!empty($parts)) {
                                $locParts[] = implode(', ', $parts);
                            }
                        }
                        echo htmlspecialchars(implode(' | ', $locParts));
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Job Details -->
        <div class="p-6 space-y-6">
            <!-- Job Type -->
            <div class="border-b border-gray-200 pb-5">
                <div class="flex items-center mb-3">
                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-800">Job Type</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <?= ucfirst(str_replace('_', ' ', $job['employment_type'] ?? 'Full-time')) ?>
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <?= ($job['is_remote'] ?? 0) ? 'Remote' : 'On-Site' ?>
                    </span>
                </div>
            </div>

            <!-- Skills -->
            <?php if (!empty($skills)): ?>
                <div class="border-b border-gray-200 pb-5">
                    <div class="flex items-center mb-3">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.082 12.082 0 0118 12.08c0 4.237-2.36 7.928-6 9.92-3.64-1.992-6-5.683-6-9.92 0-.446.036-.884.104-1.31L12 14z"></path>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-800">Required Skills</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill): ?>
                            <?php $name = is_array($skill) ? ($skill['name'] ?? '') : (string)$skill; ?>
                            <?php if ($name): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 text-blue-700 border border-blue-200">
                                    <?= htmlspecialchars($name) ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Salary -->
            <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                <div class="border-b border-gray-200 pb-5">
                    <div class="flex items-center mb-3">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-800">Salary</h2>
                    </div>
                    <p class="text-gray-700">
                        <?php if (!empty($job['salary_min']) && !empty($job['salary_max'])): ?>
                            <?= htmlspecialchars($job['currency'] ?? 'INR') ?> <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?> per month
                        <?php elseif (!empty($job['salary_min'])): ?>
                            <?= htmlspecialchars($job['currency'] ?? 'INR') ?> <?= number_format($job['salary_min']) ?>+ per month
                        <?php elseif (!empty($job['salary_max'])): ?>
                            Up to <?= htmlspecialchars($job['currency'] ?? 'INR') ?> <?= number_format($job['salary_max']) ?> per month
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Full Job Description -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Job Description</h2>
                <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                    <?= $job['description'] ?? '<p class="text-gray-500 italic">No description provided.</p>' ?>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">For Interview & Details</h3>
                <div class="space-y-2 text-gray-700">
                    <p><strong>Company:</strong> <?= htmlspecialchars($jobEmployer->company_name ?? 'N/A') ?></p>
                    <?php if ($jobEmployer->website ?? null): ?>
                        <p><strong>Website:</strong> <a href="<?= htmlspecialchars($jobEmployer->website) ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($jobEmployer->website) ?></a></p>
                    <?php endif; ?>
                    <?php if ($jobEmployer->city ?? null): ?>
                        <p><strong>Location:</strong> <?= htmlspecialchars($jobEmployer->city) ?><?= $jobEmployer->state ? ', ' . htmlspecialchars($jobEmployer->state) : '' ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-6 border-t border-gray-200 flex items-center justify-between bg-gray-50">
            <button class="flex items-center px-4 py-2 text-gray-600 hover:text-gray-900 transition duration-150">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                </svg>
                Report job
            </button>
            <div class="flex items-center space-x-4">
                <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>/edit"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150">
                    Edit Job
                </a>
                <a href="/employer/jobs"
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                    Back to Jobs
                </a>
            </div>
        </div>
    </div>
</div>
