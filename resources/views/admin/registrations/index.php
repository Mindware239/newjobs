<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$query = static fn(array $extra = []) => http_build_query(array_filter(array_merge($filters, $extra), static fn($v) => $v !== '' && $v !== null));
$pages = max(1, (int)ceil($total / $perPage));
$payBadge = ['paid' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'failed' => 'bg-red-100 text-red-800'];
$revenue = array_sum(array_column($stats, 'revenue'));
?>
<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Registrations</h1>
            <p class="mt-2 text-sm text-gray-600">Skill development, internship, full-time, part-time, work-from-home and mentor forms from <a class="text-primary font-semibold hover:underline" href="/apply" target="_blank">/apply</a></p>
        </div>
        <a href="/admin/registrations/export?<?= $h($query()) ?>" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">Export CSV</a>
    </div>

    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <?php foreach ($typeLabels as $type => $label): $s = $stats[$type] ?? ['paid' => 0, 'unpaid' => 0]; ?>
            <a href="?<?= $h($query(['type' => $type, 'page' => null])) ?>" class="rounded-lg bg-white p-3 shadow hover:ring-2 hover:ring-primary <?= $filters['type'] === $type ? 'ring-2 ring-primary' : '' ?>">
                <div class="text-xs font-bold uppercase text-gray-500 truncate" title="<?= $h($label) ?>"><?= $h(str_replace(' Registration', '', $label)) ?></div>
                <div class="mt-1 text-xl font-bold text-gray-900"><?= (int)$s['paid'] ?> <span class="text-xs font-semibold text-gray-500">paid</span></div>
                <div class="text-xs text-gray-500"><?= (int)$s['unpaid'] ?> unpaid</div>
            </a>
        <?php endforeach; ?>
        <div class="rounded-lg bg-white p-3 shadow">
            <div class="text-xs font-bold uppercase text-gray-500">Collected</div>
            <div class="mt-1 text-xl font-bold text-gray-900">₹<?= $h(number_format((float)$revenue, 0)) ?></div>
        </div>
    </div>
    <?php $skillRevenue = (float)($stats['skill']['revenue'] ?? 0); $agencyShare = round($skillRevenue * \App\Models\PortalRegistration::AGENCY_SHARE, 2); ?>
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <b>Skill development fees:</b> ₹<?= $h(number_format($skillRevenue, 2)) ?> collected ·
        <b><?= (int)(\App\Models\PortalRegistration::AGENCY_SHARE * 100) ?>% to skill development agencies:</b> ₹<?= $h(number_format($agencyShare, 2)) ?> ·
        <b>Remaining (after company operating expenses) → Jobsence skill development centre:</b> ₹<?= $h(number_format($skillRevenue - $agencyShare, 2)) ?>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-3 rounded-lg bg-white p-4 shadow">
        <input type="text" name="q" value="<?= $h($filters['q']) ?>" placeholder="Name, mobile, email, reg no, city, PIN, category" class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select name="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All forms</option>
            <?php foreach ($typeLabels as $type => $label): ?>
                <option value="<?= $h($type) ?>" <?= $filters['type'] === $type ? 'selected' : '' ?>><?= $h($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="payment_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All payments</option>
            <?php foreach (['paid', 'pending', 'failed'] as $p): ?>
                <option value="<?= $p ?>" <?= $filters['payment_status'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $h($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= $h(ucwords(str_replace('_', ' ', $s))) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="flex gap-2">
            <select name="state" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">All states</option>
                <?php foreach ($states as $st): ?>
                    <option value="<?= $h($st) ?>" <?= $filters['state'] === $st ? 'selected' : '' ?>><?= $h($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php foreach (['Reg. No.', 'Name / Contact', 'Form', 'Location', 'Skills / Categories', 'Payment', 'Status', 'Created'] as $th): ?>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500"><?= $th ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">No registrations found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-bold"><a class="text-primary hover:underline" href="/admin/registrations/<?= (int)$r['id'] ?>"><?= $h($r['reg_no']) ?></a></td>
                            <td class="px-4 py-3 text-sm"><div class="font-semibold text-gray-900"><?= $h($r['full_name']) ?></div><div class="text-gray-500"><a href="tel:+91<?= $h($r['mobile']) ?>" class="hover:underline"><?= $h($r['mobile']) ?></a></div><div class="text-gray-500 text-xs"><a href="mailto:<?= $h($r['email']) ?>" class="hover:underline"><?= $h($r['email']) ?></a></div></td>
                            <td class="px-4 py-3 text-sm"><?= $h(str_replace(' Registration', '', $typeLabels[$r['type']] ?? $r['type'])) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $h(implode(', ', array_filter([$r['city'], $r['district']]))) ?><div class="text-gray-500"><?= $h($r['state']) ?> <?= $h($r['pincode']) ?></div></td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs" title="<?= $h($r['categories']) ?>">
                                <?php if ($r['type'] === 'skill'): foreach (array_slice(array_filter(array_map('trim', explode(',', (string)$r['categories']))), 0, 5) as $ci => $cs): ?>
                                    <div class="truncate"><?= $ci + 1 ?>. <?= $h($cs) ?></div>
                                <?php endforeach; else: ?><div class="truncate"><?= $h($r['categories']) ?></div><?php endif; ?>
                            </td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $payBadge[$r['payment_status']] ?? '' ?>"><?= $h(ucfirst($r['payment_status'])) ?></span></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $h(ucwords(str_replace('_', ' ', in_array($r['type'], ['ngo', 'nearpro'], true) && $r['status'] === 'selected' ? 'verified' : $r['status']))) ?>
                                <?php if (!empty($r['valid_until'])): ?><div class="text-xs <?= strtotime((string)$r['valid_until']) < time() ? 'text-red-600' : 'text-gray-500' ?>">valid till <?= $h(date('d M Y', strtotime((string)$r['valid_until']))) ?></div><?php endif; ?></td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap"><?= $h(date('d M Y', strtotime((string)$r['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-gray-600">Page <?= $page ?> of <?= $pages ?> (<?= (int)$total ?> records)</span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a class="rounded-lg border px-3 py-1.5 font-semibold" href="?<?= $h($query(['page' => $page - 1])) ?>">Previous</a><?php endif; ?>
                <?php if ($page < $pages): ?><a class="rounded-lg border px-3 py-1.5 font-semibold" href="?<?= $h($query(['page' => $page + 1])) ?>">Next</a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
