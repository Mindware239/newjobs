<?php
$fieldError = static function (array $errors, string $field): string {
    return !empty($errors[$field])
        ? '<span class="mt-1 block text-xs font-semibold text-red-600">' . htmlspecialchars((string)$errors[$field], ENT_QUOTES, 'UTF-8') . '</span>'
        : '';
};
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <label class="block md:col-span-2">
        <span class="block text-sm font-bold text-gray-700 mb-2">Job Title</span>
        <input type="text" name="title" value="<?= $h($job['title'] ?? '') ?>" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
        <?= $fieldError($errors, 'title') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Department</span>
        <input type="text" name="department" value="<?= $h($job['department'] ?? '') ?>" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Sales, IT, Support">
        <?= $fieldError($errors, 'department') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Location</span>
        <input type="text" name="location" value="<?= $h($job['location'] ?? '') ?>" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Delhi, Noida">
        <?= $fieldError($errors, 'location') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Job Type</span>
        <select name="job_type" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
            <?php foreach (['Full Time', 'Part Time', 'Contract', 'Internship'] as $option): ?>
                <option value="<?= $h($option) ?>" <?= ($job['job_type'] ?? 'Full Time') === $option ? 'selected' : '' ?>><?= $h($option) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Work Type</span>
        <select name="work_type" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
            <?php foreach (['On-site', 'Remote', 'Hybrid'] as $option): ?>
                <option value="<?= $h($option) ?>" <?= ($job['work_type'] ?? 'On-site') === $option ? 'selected' : '' ?>><?= $h($option) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="block md:col-span-2">
        <span class="block text-sm font-bold text-gray-700 mb-2">Description</span>
        <textarea name="description" rows="4" required class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary"><?= $h($job['description'] ?? '') ?></textarea>
        <?= $fieldError($errors, 'description') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Responsibilities</span>
        <textarea name="responsibilities" rows="8" required class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="One responsibility per line"><?= $h($job['responsibilities'] ?? '') ?></textarea>
        <?= $fieldError($errors, 'responsibilities') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Requirements</span>
        <textarea name="requirements" rows="8" required class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="One requirement per line"><?= $h($job['requirements'] ?? '') ?></textarea>
        <?= $fieldError($errors, 'requirements') ?>
    </label>

    <label class="block">
        <span class="block text-sm font-bold text-gray-700 mb-2">Status</span>
        <select name="status" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
            <option value="active" <?= ($job['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($job['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <?= $fieldError($errors, 'status') ?>
    </label>
</div>











