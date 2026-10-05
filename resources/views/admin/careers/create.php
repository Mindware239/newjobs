<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$job = $job ?? [];
$errors = $errors ?? [];
?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Add Career Opening</h1>
        <p class="mt-2 text-sm text-gray-600">Create an internal hiring role for the public careers page</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"><?= $h($errors['general']) ?></div>
    <?php endif; ?>

    <div class="rounded-lg bg-white p-6 shadow">
        <form method="POST" action="/admin/careers/create" class="space-y-6">
            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
            <?php include __DIR__ . '/form.php'; ?>
            <div class="flex justify-end gap-3 border-t pt-6">
                <a href="/admin/careers" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">Create Opening</button>
            </div>
        </form>
    </div>
</div>











