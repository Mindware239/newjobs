<?php $h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); ?>

<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Careers</h1>
            <p class="mt-2 text-sm text-gray-600">Manage Jobsence internal hiring openings</p>
        </div>
        <a href="/admin/careers/create" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">
            Add Opening
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"><?= $h($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"><?= $h($error) ?></div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Applications</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (empty($jobs)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                No career openings yet. <a href="/admin/careers/create" class="font-semibold text-primary hover:underline">Create one</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900"><?= $h($job['title'] ?? '') ?></div>
                                    <div class="mt-1 text-xs text-gray-500">Created <?= $h($job['created_at'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= $h($job['department'] ?? '') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= $h($job['location'] ?? '') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= $h($job['job_type'] ?? '') ?> / <?= $h($job['work_type'] ?? '') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= (int)($job['application_count'] ?? 0) ?></td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= ($job['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($h($job['status'] ?? 'inactive')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                                        <a href="/admin/careers/edit/<?= (int)($job['id'] ?? 0) ?>" class="text-primary hover:text-primary">Edit</a>
                                        <form method="POST" action="/admin/careers/toggle/<?= (int)($job['id'] ?? 0) ?>">
                                            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-gray-700 hover:text-gray-900">Toggle</button>
                                        </form>
                                        <form method="POST" action="/admin/careers/delete/<?= (int)($job['id'] ?? 0) ?>" onsubmit="return confirm('Delete this career opening and its applications?')">
                                            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>











