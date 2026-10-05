<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$job = $job ?? [];
$errors = $errors ?? [];
$applications = $applications ?? [];
?>

<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Career Opening</h1>
            <p class="mt-2 text-sm text-gray-600"><?= $h($job['title'] ?? '') ?></p>
        </div>
        <a href="/admin/careers" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"><?= $h($errors['general']) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-lg bg-white p-6 shadow">
            <form method="POST" action="/admin/careers/edit/<?= (int)($job['id'] ?? 0) ?>" class="space-y-6">
                <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                <?php include __DIR__ . '/form.php'; ?>
                <div class="flex justify-end gap-3 border-t pt-6">
                    <a href="/admin/careers" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">Update Opening</button>
                </div>
            </form>
        </div>

        <aside class="rounded-lg bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-gray-900">Applications</h2>
            <p class="mt-1 text-sm text-gray-600"><?= count($applications) ?> submitted</p>
            <div class="mt-5 space-y-4">
                <?php if (empty($applications)): ?>
                    <p class="text-sm text-gray-500">No applications yet.</p>
                <?php else: ?>
                    <?php foreach ($applications as $application): ?>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="font-bold text-gray-900"><?= $h(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?></div>
                            <div class="mt-1 text-sm text-gray-600"><?= $h($application['email'] ?? '') ?></div>
                            <div class="mt-1 text-sm text-gray-600"><?= $h($application['phone'] ?? '') ?></div>
                            <div class="mt-3 space-y-1 text-xs font-semibold">
                                <a class="block text-primary hover:underline" href="/uploads/careers/resumes/<?= rawurlencode((string)($application['resume'] ?? '')) ?>" target="_blank">Resume</a>
                                <?php if (!empty($application['cover_letter'])): ?>
                                    <a class="block text-primary hover:underline" href="/uploads/careers/cover_letters/<?= rawurlencode((string)$application['cover_letter']) ?>" target="_blank">Cover Letter</a>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3 text-xs text-gray-500"><?= $h($application['created_at'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>











