<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$v = static fn(string $k) => $h($job[$k] ?? '');
$err = static fn(string $k) => isset($errors[$k]) ? '<p class="mt-1 text-xs font-semibold text-red-600">' . $h($errors[$k]) . '</p>' : '';
$input = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm';
$id = (int)($job['id'] ?? 0);
?>
<div>
    <div class="mb-6">
        <a href="/admin/india-jobs" class="text-sm font-semibold text-gray-500 hover:text-gray-900">&larr; Jobs in India</a>
        <h1 class="mt-2 text-3xl font-bold text-gray-900"><?= $id ? 'Edit job' : 'Add job' ?></h1>
        <p class="text-sm text-gray-600">Copy the facts from the official notification and always link to it. Keep the summary in your own words.</p>
    </div>

    <form method="POST" action="<?= $id ? '/admin/india-jobs/' . $id : '/admin/india-jobs' ?>" class="rounded-lg bg-white p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
        <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 mb-1">Job title *</label><input class="<?= $input ?>" name="title" value="<?= $v('title') ?>" maxlength="255" placeholder="RRB Group D Recruitment 2026 – 32,000 posts" required><?= $err('title') ?></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Organisation *</label><input class="<?= $input ?>" name="org_name" value="<?= $v('org_name') ?>" maxlength="190" placeholder="Railway Recruitment Boards (RRB)" required><?= $err('org_name') ?></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Type *</label>
            <select class="<?= $input ?>" name="org_type"><?php foreach ($types as $k => $label): ?><option value="<?= $h($k) ?>" <?= ($job['org_type'] ?? '') === $k ? 'selected' : '' ?>><?= $h($label[1]) ?></option><?php endforeach; ?></select><?= $err('org_type') ?></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Listing kind</label>
            <select class="<?= $input ?>" name="kind"><?php foreach (['job' => 'Job', 'internship' => 'Internship', 'skill' => 'Skill development', 'apprenticeship' => 'Apprenticeship'] as $k => $l): ?><option value="<?= $h($k) ?>" <?= ($job['kind'] ?? 'job') === $k ? 'selected' : '' ?>><?= $h($l) ?></option><?php endforeach; ?></select></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">State</label>
            <select class="<?= $input ?>" name="state"><option value="">All India</option><?php foreach ($states as $st): ?><option value="<?= $h($st) ?>" <?= ($job['state'] ?? '') === $st ? 'selected' : '' ?>><?= $h($st) ?></option><?php endforeach; ?></select></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Location / city</label><input class="<?= $input ?>" name="location" value="<?= $v('location') ?>" maxlength="190"></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Qualification</label><input class="<?= $input ?>" name="qualification" value="<?= $v('qualification') ?>" maxlength="255" placeholder="10th pass + ITI"></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Vacancies</label><input class="<?= $input ?>" name="vacancies" value="<?= $v('vacancies') ?>" inputmode="numeric"><?= $err('vacancies') ?></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Salary / pay</label><input class="<?= $input ?>" name="salary" value="<?= $v('salary') ?>" maxlength="120" placeholder="Level 1 – ₹18,000/month"></div>
        <div><label class="block text-sm font-bold text-gray-700 mb-1">Last date to apply</label><input class="<?= $input ?>" type="date" name="last_date" value="<?= $v('last_date') ?>"><?= $err('last_date') ?></div>
        <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 mb-1">Official notification link *</label><input class="<?= $input ?>" name="source_url" value="<?= $v('source_url') ?>" maxlength="500" placeholder="https://…gov.in/…"><?= $err('source_url') ?></div>
        <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 mb-1">Official apply link</label><input class="<?= $input ?>" name="apply_url" value="<?= $v('apply_url') ?>" maxlength="500"><?= $err('apply_url') ?></div>
        <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 mb-1">Short summary (shown to pass holders in the list)</label><textarea class="<?= $input ?>" name="summary" rows="2" maxlength="1000"><?= $v('summary') ?></textarea></div>
        <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 mb-1">Full details (pass holders only)</label><textarea class="<?= $input ?>" name="details" rows="8"><?= $v('details') ?></textarea></div>
        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700"><input type="checkbox" name="is_active" value="1" <?= !empty($job['is_active']) ? 'checked' : '' ?>> Live on the website</label>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-primary-600"><?= $id ? 'Save changes' : 'Add job' ?></button>
            <a href="/admin/india-jobs" class="rounded-lg border px-5 py-2 text-sm font-semibold">Cancel</a>
        </div>
    </form>
</div>
