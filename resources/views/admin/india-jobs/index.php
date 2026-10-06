<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$query = static fn(array $extra = []) => http_build_query(array_filter(array_merge($filters, $extra), static fn($v) => $v !== '' && $v !== null));
$pages = max(1, (int)ceil($total / $perPage));
$csrf = $h($_SESSION['csrf_token'] ?? '');
?>
<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Jobs in India</h1>
            <p class="mt-2 text-sm text-gray-600">Railways, Army, Police, State Govt, PSU, bank and company notifications shown on <a class="text-primary font-semibold hover:underline" href="/india-jobs" target="_blank">/india-jobs</a>. Full details and apply links are free for everyone (job seekers use Jobsence free).</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/india-jobs/sources" class="inline-flex items-center justify-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-bold text-white hover:bg-gray-900">Sources &amp; feeds</a>
            <a href="/admin/india-jobs/new" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">+ Add job</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"><?= $h($flash) ?></div>
    <?php endif; ?>

    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <?php foreach ($types as $k => $label): ?>
            <a href="?<?= $h($query(['type' => $k, 'page' => null])) ?>" class="rounded-lg bg-white p-3 shadow hover:ring-2 hover:ring-primary <?= $filters['type'] === $k ? 'ring-2 ring-primary' : '' ?>">
                <div class="text-xs font-bold uppercase text-gray-500 truncate"><?= $h($label[1]) ?></div>
                <div class="mt-1 text-xl font-bold text-gray-900"><?= (int)($counts[$k] ?? 0) ?> <span class="text-xs font-semibold text-gray-500">live</span></div>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-3 rounded-lg bg-white p-4 shadow">
        <input type="text" name="q" value="<?= $h($filters['q']) ?>" placeholder="Title, organisation, location, qualification" class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select name="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All types</option>
            <?php foreach ($types as $k => $label): ?><option value="<?= $h($k) ?>" <?= $filters['type'] === $k ? 'selected' : '' ?>><?= $h($label[1]) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php foreach (['Job', 'Organisation', 'State', 'Last date', 'Status', 'Actions'] as $th): ?>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500"><?= $th ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (!$rows): ?>
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">No jobs yet. Add one manually, or add a feed URL to a source.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): $expired = $r['last_date'] && strtotime((string)$r['last_date']) < strtotime('today'); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm max-w-xs"><a class="font-semibold text-gray-900 hover:underline" href="/admin/india-jobs/<?= (int)$r['id'] ?>/edit"><?= $h($r['title']) ?></a>
                                <div class="text-xs text-gray-500"><?= $h($types[$r['org_type']][1] ?? $r['org_type']) ?><?= $r['source_id'] ? ' · feed' : ' · manual' ?></div></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $h($r['org_name']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $h($r['state'] ?: 'All India') ?></td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap <?= $expired ? 'text-red-600' : 'text-gray-700' ?>"><?= $r['last_date'] ? $h(date('d M Y', strtotime((string)$r['last_date']))) : '—' ?></td>
                            <td class="px-4 py-3 text-sm"><?php if (!$r['is_active']): ?><span class="rounded-full px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800">Hidden</span><?php elseif ($expired): ?><span class="rounded-full px-2.5 py-1 text-xs font-bold bg-yellow-100 text-yellow-800">Closed</span><?php else: ?><span class="rounded-full px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800">Live</span><?php endif; ?></td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <a class="font-semibold text-primary hover:underline" href="/india-jobs/<?= (int)$r['id'] ?>-<?= $h($r['slug']) ?>" target="_blank">View</a> ·
                                <a class="font-semibold text-primary hover:underline" href="/admin/india-jobs/<?= (int)$r['id'] ?>/edit">Edit</a> ·
                                <form method="POST" action="/admin/india-jobs/<?= (int)$r['id'] ?>/feature" style="display:inline"><input type="hidden" name="_token" value="<?= $csrf ?>"><button class="font-semibold <?= !empty($r['is_featured']) ? 'text-amber-600' : 'text-gray-500' ?> hover:underline" type="submit" title="Show in Featured on the homepage"><?= !empty($r['is_featured']) ? '★ Featured' : '☆ Feature' ?></button></form> ·
                                <form method="POST" action="/admin/india-jobs/<?= (int)$r['id'] ?>/toggle" style="display:inline"><input type="hidden" name="_token" value="<?= $csrf ?>"><button class="font-semibold text-gray-700 hover:underline" type="submit"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button></form> ·
                                <form method="POST" action="/admin/india-jobs/<?= (int)$r['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this job permanently?')"><input type="hidden" name="_token" value="<?= $csrf ?>"><button class="font-semibold text-red-600 hover:underline" type="submit">Delete</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-gray-600">Page <?= $page ?> of <?= $pages ?> (<?= (int)$total ?> jobs)</span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a class="rounded-lg border px-3 py-1.5 font-semibold" href="?<?= $h($query(['page' => $page - 1])) ?>">Previous</a><?php endif; ?>
                <?php if ($page < $pages): ?><a class="rounded-lg border px-3 py-1.5 font-semibold" href="?<?= $h($query(['page' => $page + 1])) ?>">Next</a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
