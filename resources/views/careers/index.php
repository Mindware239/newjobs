<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>

<section class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-normal">Open Positions</h1>
            <p class="mt-4 text-lg text-gray-600">Find a role where your passion meets purpose</p>
        </div>

        <form method="GET" action="/careers" class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl mx-auto">
            <label class="block">
                <span class="sr-only">All Departments</span>
                <select name="department" onchange="this.form.submit()" class="w-full h-12 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900">
                    <option value="">All Departments</option>
                    <?php foreach (($departments ?? []) as $department): ?>
                        <?php $value = (string)($department['department'] ?? ''); ?>
                        <?php if ($value !== ''): ?>
                            <option value="<?= $h($value) ?>" <?= ($selectedDepartment ?? '') === $value ? 'selected' : '' ?>>
                                <?= $h($value) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block">
                <span class="sr-only">All Locations</span>
                <select name="location" onchange="this.form.submit()" class="w-full h-12 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900">
                    <option value="">All Locations</option>
                    <?php foreach (($locations ?? []) as $location): ?>
                        <?php $value = (string)($location['location'] ?? ''); ?>
                        <?php if ($value !== ''): ?>
                            <option value="<?= $h($value) ?>" <?= ($selectedLocation ?? '') === $value ? 'selected' : '' ?>>
                                <?= $h($value) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <?php if (!empty($selectedDepartment) || !empty($selectedLocation)): ?>
            <div class="mt-4 text-center">
                <a href="/careers" class="text-sm font-semibold text-gray-700 hover:text-gray-900">Clear filters</a>
            </div>
        <?php endif; ?>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php if (empty($jobs)): ?>
                <div class="md:col-span-2 lg:col-span-3 rounded-lg border border-gray-200 bg-gray-50 p-10 text-center">
                    <h2 class="text-xl font-bold text-gray-900">No open positions found</h2>
                    <p class="mt-2 text-gray-600">Try changing the department or location filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <article class="rounded-xl border border-gray-100 bg-white p-6 shadow-[0_10px_28px_rgba(15,23,42,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_36px_rgba(15,23,42,0.12)]">
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            <?= $h($job['department'] ?? '') ?>
                        </span>
                        <h2 class="mt-5 text-xl font-bold text-gray-900 leading-snug"><?= $h($job['title'] ?? '') ?></h2>
                        <div class="mt-4 flex items-center gap-2 text-sm font-medium text-gray-600">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10.5a2 2 0 100-4 2 2 0 000 4z"></path>
                            </svg>
                            <span><?= $h($job['location'] ?? '') ?></span>
                        </div>
                        <p class="mt-3 text-sm text-gray-600">
                            <?= $h($job['job_type'] ?? '') ?> &bull; <?= $h($job['work_type'] ?? '') ?>
                        </p>
                        <a href="/careers/<?= (int)($job['id'] ?? 0) ?>" class="mt-6 inline-flex h-11 w-full items-center justify-center rounded-lg bg-gray-900 px-4 text-sm font-bold text-white hover:bg-gray-800">
                            Apply Now
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>











