<?php
$job = $job ?? [];
$applications = $applications ?? [];
$locations = $locations ?? [];
$skills = $skills ?? [];
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($job['title'] ?? 'Job Details') ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                Company: <?= htmlspecialchars($job['company_name'] ?? 'N/A') ?> ·
                Status: <span class="font-semibold"><?= htmlspecialchars($job['status'] ?? 'draft') ?></span>
            </p>
        </div>
        <a href="/admin/jobs" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            Back to Jobs
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Title</dt>
                        <dd class="mt-1 text-gray-900"><?= htmlspecialchars($job['title'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-gray-900">
                            <?= !empty($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : 'N/A' ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Salary Range</dt>
                        <dd class="mt-1 text-gray-900">
                            <?= ($job['salary_min'] ?? 0) > 0 ? number_format($job['salary_min']) : 'N/A' ?>
                            -
                            <?= ($job['salary_max'] ?? 0) > 0 ? number_format($job['salary_max']) : 'N/A' ?>
                            <span class="text-gray-500"><?= $job['currency'] ?? $job['salary_currency'] ?? 'INR' ?></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Applications</dt>
                        <dd class="mt-1 text-gray-900"><?= count($applications) ?></dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        <?= $job['description'] ?? '<p>No description.</p>' ?>
                    </div>
                </div>

                <?php if (!empty($skills)): ?>
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($skills as $skill): ?>
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
                                    <?= htmlspecialchars($skill['name'] ?? '') ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Applications (<?= count($applications) ?>)</h2>
                <?php if (empty($applications)): ?>
                    <p class="text-sm text-gray-500">No applications for this job yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Candidate</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Email</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Applied At</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <?= htmlspecialchars($application['full_name'] ?? 'Unknown') ?>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-gray-600">
                                            <?= htmlspecialchars($application['email'] ?? '') ?>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?= ($application['status'] ?? '') === 'shortlisted' ? 'bg-green-100 text-green-800' :
                                                    (($application['status'] ?? '') === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                                                <?= ucfirst($application['status'] ?? 'applied') ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-gray-600">
                                            <?= !empty($application['applied_at']) ? date('M d, Y', strtotime($application['applied_at'])) : 'N/A' ?>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap space-x-2">
                                            <?php if (!empty($application['candidate_id'])): ?>
                                                <a href="/admin/candidates/<?= (int)$application['candidate_id'] ?>"
                                                   class="text-blue-600 hover:text-blue-900">View candidate</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Moderation</h2>
                <form method="POST" action="/admin/jobs/<?= urlencode($job['slug'] ?? '') ?>/approve" class="mb-2">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                        Approve & Publish
                    </button>
                </form>
                <form method="POST" action="/admin/jobs/<?= urlencode($job['slug'] ?? '') ?>/reject" class="mb-2">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="text" name="reason" placeholder="Rejection reason"
                           class="w-full mb-2 px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                        Reject Job
                    </button>
                </form>
                <form method="POST" action="/admin/jobs/<?= urlencode($job['slug'] ?? '') ?>/take-down">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="text" name="reason" placeholder="Take down reason"
                           class="w-full mb-2 px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <button type="submit" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm font-medium">
                        Take Down
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


