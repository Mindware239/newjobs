<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$lines = static function ($value): array {
    $items = preg_split('/\r\n|\r|\n/', (string)$value) ?: [];
    return array_values(array_filter(array_map(static function ($item) {
        return trim((string)preg_replace('/^\s*[-*]\s*/', '', $item));
    }, $items), static fn($item) => $item !== ''));
};
$activeTab = ($activeTab ?? 'details') === 'apply' ? 'apply' : 'details';
$formData = $formData ?? [];
$errors = $errors ?? [];
?>

<section class="bg-white min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        <a href="/careers" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to jobs
        </a>

        <header class="mt-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-normal"><?= $h($job['title'] ?? '') ?></h1>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-sm font-semibold text-gray-600">
                <span class="rounded-full bg-gray-100 px-4 py-2"><?= $h($job['job_type'] ?? '') ?></span>
                <span class="rounded-full bg-gray-100 px-4 py-2"><?= $h($job['department'] ?? '') ?></span>
                <span class="rounded-full bg-gray-100 px-4 py-2"><?= $h($job['work_type'] ?? '') ?></span>
                <span class="rounded-full bg-gray-100 px-4 py-2"><?= $h($job['location'] ?? '') ?></span>
            </div>
        </header>

        <?php if (!empty($success)): ?>
            <div class="mt-8 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                <?= $h($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['upload']) || !empty($errors['general'])): ?>
            <div class="mt-8 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                <?= $h($errors['upload'] ?? $errors['general']) ?>
            </div>
        <?php endif; ?>

        <div class="mt-10 border-b border-gray-200">
            <nav class="flex justify-center gap-2" aria-label="Career tabs">
                <a href="/careers/<?= (int)($job['id'] ?? 0) ?>?tab=details" class="px-5 py-3 text-sm font-bold border-b-2 <?= $activeTab === 'details' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900' ?>">
                    Job Details
                </a>
                <a href="/careers/<?= (int)($job['id'] ?? 0) ?>?tab=apply" class="px-5 py-3 text-sm font-bold border-b-2 <?= $activeTab === 'apply' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900' ?>">
                    Application Form
                </a>
            </nav>
        </div>

        <?php if ($activeTab === 'details'): ?>
            <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="rounded-xl border border-gray-100 bg-white p-7 shadow-[0_10px_28px_rgba(15,23,42,0.08)]">
                    <h2 class="text-2xl font-bold text-gray-900">Responsibilities</h2>
                    <ul class="mt-5 space-y-3 text-gray-700">
                        <?php foreach ($lines($job['responsibilities'] ?? '') as $item): ?>
                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-gray-900 shrink-0"></span>
                                <span><?= $h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-7 shadow-[0_10px_28px_rgba(15,23,42,0.08)]">
                    <h2 class="text-2xl font-bold text-gray-900">Requirements</h2>
                    <ul class="mt-5 space-y-3 text-gray-700">
                        <?php foreach ($lines($job['requirements'] ?? '') as $item): ?>
                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-gray-900 shrink-0"></span>
                                <span><?= $h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <?php if (!empty($job['description'])): ?>
                <div class="mt-8 rounded-xl border border-gray-100 bg-gray-50 p-7">
                    <h2 class="text-2xl font-bold text-gray-900">About the role</h2>
                    <p class="mt-4 leading-7 text-gray-700"><?= nl2br($h($job['description'])) ?></p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mt-10 max-w-3xl mx-auto rounded-xl border border-gray-100 bg-white p-6 sm:p-8 shadow-[0_10px_28px_rgba(15,23,42,0.08)]">
                <form method="POST" action="/careers/apply/<?= (int)($job['id'] ?? 0) ?>" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <?php foreach ([['first_name', 'First Name'], ['last_name', 'Last Name'], ['email', 'Email'], ['phone', 'Phone']] as [$field, $label]): ?>
                            <label class="block">
                                <span class="block text-sm font-bold text-gray-700 mb-2"><?= $h($label) ?></span>
                                <input
                                    type="<?= $field === 'email' ? 'email' : 'text' ?>"
                                    name="<?= $h($field) ?>"
                                    value="<?= $h($formData[$field] ?? '') ?>"
                                    required
                                    class="h-12 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm text-gray-900 focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                                >
                                <?php if (!empty($errors[$field])): ?>
                                    <span class="mt-1 block text-xs font-semibold text-red-600"><?= $h($errors[$field]) ?></span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <label class="block">
                        <span class="block text-sm font-bold text-gray-700 mb-2">Resume</span>
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16"></path>
                            </svg>
                            <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="mt-4 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white">
                            <p class="mt-3 text-xs text-gray-500">PDF, DOC, or DOCX only</p>
                        </div>
                        <?php if (!empty($errors['resume'])): ?>
                            <span class="mt-1 block text-xs font-semibold text-red-600"><?= $h($errors['resume']) ?></span>
                        <?php endif; ?>
                    </label>

                    <label class="block">
                        <span class="block text-sm font-bold text-gray-700 mb-2">Cover letter</span>
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-7 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <input type="file" name="cover_letter" accept=".pdf,.doc,.docx" class="mt-4 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white">
                            <p class="mt-3 text-xs text-gray-500">Optional PDF, DOC, or DOCX upload</p>
                        </div>
                        <?php if (!empty($errors['cover_letter'])): ?>
                            <span class="mt-1 block text-xs font-semibold text-red-600"><?= $h($errors['cover_letter']) ?></span>
                        <?php endif; ?>
                    </label>

                    <button type="submit" class="h-12 w-full rounded-lg bg-orange-500 px-5 text-sm font-extrabold text-white hover:bg-orange-600">
                        Submit Application
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>











