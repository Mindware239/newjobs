<?php
$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$csrf = '<input type="hidden" name="_token" value="' . $h($_SESSION['csrf_token'] ?? '') . '">';
$badge = ['live' => 'bg-green-100 text-green-800', 'hidden' => 'bg-red-100 text-red-800', 'closed' => 'bg-gray-100 text-gray-700'];
?>
<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Free job posts</h1>
            <p class="mt-2 text-sm text-gray-600">Jobs posted free by companies on <a class="text-primary font-semibold hover:underline" href="/jobs-by-state" target="_blank">/jobs-by-state</a>. Hide anything that asks candidates for money, is fake or is not a real job.</p>
        </div>
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <?php foreach (['' => 'All', 'live' => 'Live', 'hidden' => 'Hidden', 'closed' => 'Closed'] as $k => $l): ?><option value="<?= $h($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= $h($l) ?></option><?php endforeach; ?>
            </select>
            <input name="q" value="<?= $h($q) ?>" placeholder="Title, company, city, email" class="rounded-lg border-gray-300 text-sm">
            <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
    </div>
    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                <tr><th class="px-4 py-3">Posted</th><th class="px-3 py-3">Job</th><th class="px-3 py-3">Company</th><th class="px-3 py-3">Place</th><th class="px-3 py-3">Contact</th><th class="px-3 py-3">Views</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"></th></tr>
            </thead>
            <tbody>
                <?php if (!$posts): ?><tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No posts.</td></tr><?php endif; ?>
                <?php foreach ($posts as $p): ?>
                    <tr class="border-t border-gray-100 align-top">
                        <td class="px-4 py-2 whitespace-nowrap"><?= $h(date('d M Y, h:i A', strtotime((string)$p['published_at']))) ?></td>
                        <td class="px-3 py-2"><a class="font-semibold text-primary hover:underline" href="<?= $h(\App\Models\FreeJobPost::url($p)) ?>" target="_blank"><?= $h($p['title']) ?></a><div class="text-xs text-gray-500"><?= $h(mb_strimwidth((string)$p['description'], 0, 120, '…')) ?></div></td>
                        <td class="px-3 py-2"><?= $h($p['company_name']) ?><div class="text-xs text-gray-500"><?= $h(\App\Models\FreeJobPost::COMPANY_TYPES[$p['company_type']][1] ?? $p['company_type']) ?> · user #<?= (int)$p['user_id'] ?></div></td>
                        <td class="px-3 py-2"><?= $h($p['city']) ?>, <?= $h($p['state']) ?></td>
                        <td class="px-3 py-2 text-xs"><?= $h($p['contact_person']) ?><br><?= $h($p['phone']) ?><br><?= $h($p['email']) ?></td>
                        <td class="px-3 py-2"><?= (int)$p['views'] ?></td>
                        <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge[$p['status']] ?? '' ?>"><?= $h($p['status']) ?></span></td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <form method="POST" action="/admin/free-jobs/<?= (int)$p['id'] ?>/status"><?= $csrf ?>
                                <?php if ($p['status'] === 'live'): ?><input type="hidden" name="status" value="hidden"><button class="rounded bg-red-600 px-3 py-1 text-xs font-bold text-white">Hide</button>
                                <?php else: ?><input type="hidden" name="status" value="live"><button class="rounded bg-green-600 px-3 py-1 text-xs font-bold text-white">Make live</button><?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
