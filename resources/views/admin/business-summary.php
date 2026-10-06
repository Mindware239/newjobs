<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$inr = static fn($v) => '₹' . number_format((float)$v, 0);
$usd = static fn($v) => (float)$v > 0 ? 'USD ' . number_format((float)$v, 0) : '–';
$num = static fn($v) => number_format((int)$v);
$periods = \App\Services\Admin\BusinessSummary::PERIODS;
$rangeText = $rangeFrom === null && $rangeTo === null ? 'All time'
    : (($rangeFrom ? date('d M Y', strtotime($rangeFrom)) : 'Start') . ' – ' . ($rangeTo ? date('d M Y', strtotime($rangeTo . ' -1 day')) : 'today'));
$maxDay = max(1, max(array_column($daily, 'inr')));
$roleNames = ['candidate' => 'Job seekers (candidate accounts)', 'employer' => 'Employers (company accounts)'];
?>
<div>
    <div class="mb-6 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Registrations &amp; payments by use case</h1>
            <p class="mt-2 text-sm text-gray-600">How many registered for each use case and how much was received against each part · <b><?= $h($rangeText) ?></b></p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <select name="period" class="rounded-lg border-gray-300 text-sm" onchange="this.form.querySelectorAll('.cust').forEach(e => e.style.display = this.value === 'custom' ? '' : 'none')">
                <?php foreach ($periods as $k => $l): ?><option value="<?= $h($k) ?>" <?= $period === $k ? 'selected' : '' ?>><?= $h($l) ?></option><?php endforeach; ?>
            </select>
            <input class="cust rounded-lg border-gray-300 text-sm" type="date" name="from" value="<?= $h($from) ?>" style="<?= $period === 'custom' ? '' : 'display:none' ?>" aria-label="From">
            <input class="cust rounded-lg border-gray-300 text-sm" type="date" name="to" value="<?= $h($to) ?>" style="<?= $period === 'custom' ? '' : 'display:none' ?>" aria-label="To">
            <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">Show</button>
        </form>
    </div>

    <!-- Headline numbers -->
    <div class="mb-6 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <?php foreach ([
            ['Registered', $num($totals['registered']), 'all forms'],
            ['Completed / paid', $num($totals['completed']), $num($totals['pending']) . ' payment pending'],
            ['Payments received', $num($totals['payments']), 'paid registrations, plans & passes'],
            ['₹ received (registrations)', $inr($totals['inr']), 'incl. GST ' . $inr($totals['gst'])],
            ['USD received', $usd($totals['usd']), 'abroad plans & unlocks'],
            ['₹ employer subscriptions', $inr($subsInr), 'subscription plans'],
        ] as [$label, $value, $sub]): ?>
            <div class="rounded-xl bg-white p-4 shadow">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500"><?= $h($label) ?></div>
                <div class="mt-1 text-2xl font-extrabold text-gray-900"><?= $h($value) ?></div>
                <div class="text-xs text-gray-500"><?= $h($sub) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Use-case table -->
    <div class="mb-8 overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Use case / form</th>
                    <th class="px-3 py-3 text-right">Registered</th>
                    <th class="px-3 py-3 text-right">Completed / paid</th>
                    <th class="px-3 py-3 text-right">Payment pending</th>
                    <th class="px-3 py-3 text-right">Payments</th>
                    <th class="px-3 py-3 text-right">₹ received</th>
                    <th class="px-3 py-3 text-right">of which GST</th>
                    <th class="px-3 py-3 text-right">USD</th>
                </tr>
            </thead>
            <?php foreach ($groups as $key => $g): $s = $g['sum']; ?>
                <tbody class="border-t-4 border-gray-100">
                    <tr class="bg-orange-50/60 font-extrabold text-gray-900">
                        <td class="px-4 py-2.5"><?= $h($g['title']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $num($s['registered']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $num($s['completed']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $num($s['pending']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $num($s['payments']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $inr($s['inr']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $inr($s['gst']) ?></td>
                        <td class="px-3 py-2.5 text-right"><?= $usd($s['usd']) ?></td>
                    </tr>
                    <?php foreach ($g['rows'] as $r): ?>
                        <tr class="text-gray-700 hover:bg-gray-50">
                            <td class="px-4 py-2 pl-8"><a class="text-primary hover:underline" href="/admin/registrations?type=<?= $h(rawurlencode($r['type'])) ?>"><?= $h($r['label']) ?></a> <span class="text-xs text-gray-400"><?= $h($r['type']) ?></span></td>
                            <td class="px-3 py-2 text-right"><?= $num($r['registered']) ?></td>
                            <td class="px-3 py-2 text-right"><?= $num($r['completed']) ?></td>
                            <td class="px-3 py-2 text-right"><a class="hover:underline" href="/admin/registrations?type=<?= $h(rawurlencode($r['type'])) ?>&payment_status=pending"><?= $num($r['pending']) ?></a></td>
                            <td class="px-3 py-2 text-right"><?= $num($r['payments']) ?></td>
                            <td class="px-3 py-2 text-right"><?= $inr($r['inr']) ?></td>
                            <td class="px-3 py-2 text-right text-gray-500"><?= $inr($r['gst']) ?></td>
                            <td class="px-3 py-2 text-right"><?= $usd($r['usd']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
            <tfoot class="border-t-4 border-gray-200 bg-gray-900 font-extrabold text-white">
                <tr>
                    <td class="px-4 py-3">Total – all registrations</td>
                    <td class="px-3 py-3 text-right"><?= $num($totals['registered']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $num($totals['completed']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $num($totals['pending']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $num($totals['payments']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $inr($totals['inr']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $inr($totals['gst']) ?></td>
                    <td class="px-3 py-3 text-right"><?= $usd($totals['usd']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="-mt-6 mb-8 text-xs text-gray-500">Registered / completed / pending count forms created in the period; payments and amounts count money received (paid_at) in the period. Free registrations (mentors, internship providers, hiring companies, jobs abroad) count as completed when submitted. ₹ amounts include GST.</p>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <!-- Accounts -->
        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="mb-3 text-lg font-bold text-gray-900">New accounts by type</h2>
            <?php if (!$accounts): ?><p class="text-sm text-gray-500">No new accounts in this period.</p><?php endif; ?>
            <table class="w-full text-sm">
                <?php foreach ($accounts as $role => $n): ?>
                    <tr class="border-b border-gray-100"><td class="py-2"><?= $h($roleNames[$role] ?? ucwords(str_replace('_', ' ', (string)$role))) ?></td><td class="py-2 text-right font-bold"><?= $num($n) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
        <!-- Employer subscriptions -->
        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="mb-3 text-lg font-bold text-gray-900">Employer subscription payments</h2>
            <?php if (!$subs): ?><p class="text-sm text-gray-500">No completed subscription payments in this period.</p><?php endif; ?>
            <table class="w-full text-sm">
                <?php foreach ($subs as $s): ?>
                    <tr class="border-b border-gray-100"><td class="py-2"><?= $h($s['plan']) ?></td><td class="py-2 text-right text-gray-500"><?= $num($s['payments']) ?> payments</td><td class="py-2 text-right font-bold"><?= ($s['currency'] ?? 'INR') === 'USD' ? 'USD ' : '₹' ?><?= $h(number_format((float)$s['amount'], 0)) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <a href="/admin/payments" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">All payments →</a>
        </div>
    </div>

    <!-- 30-day trend -->
    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="mb-1 text-lg font-bold text-gray-900">₹ received from registrations – last 30 days</h2>
        <p class="mb-3 text-xs text-gray-500">Each bar is one day; hover for the amount.</p>
        <div class="flex h-32 items-end gap-1" role="img" aria-label="Daily amounts received, last 30 days">
            <?php foreach ($daily as $d): $pct = $d['inr'] > 0 ? max(3, (int)round($d['inr'] / $maxDay * 100)) : 1; ?>
                <div class="flex-1 rounded-t bg-orange-400 hover:bg-orange-600" style="height:<?= $pct ?>%" title="<?= $h(date('d M', strtotime($d['d'])) . ': ₹' . number_format($d['inr'], 0) . ' (' . $d['n'] . ' payments)') ?>"></div>
            <?php endforeach; ?>
        </div>
        <div class="mt-1 flex justify-between text-xs text-gray-400"><span><?= $h(date('d M', strtotime($daily[0]['d']))) ?></span><span><?= $h(date('d M', strtotime(end($daily)['d']))) ?></span></div>
    </div>
</div>
