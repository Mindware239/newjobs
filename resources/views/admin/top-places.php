<?php
/** Admin: top places bids (companies, resume boosts), monthly place 1 bookings, logo rights. */
use App\Services\TopPlaces\TopBidding as TB;

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$csrf = '<input type="hidden" name="_token" value="' . $h($_SESSION['csrf_token'] ?? '') . '">';
$rs = static fn($n) => '₹' . number_format((float)$n, 0);
$badge = ['active' => 'bg-blue-100 text-blue-800', 'won' => 'bg-amber-100 text-amber-800', 'waiting' => 'bg-gray-100 text-gray-700', 'paid' => 'bg-green-100 text-green-800',
    'lapsed' => 'bg-red-100 text-red-800', 'lost' => 'bg-gray-100 text-gray-500', 'cancelled' => 'bg-gray-100 text-gray-500', 'pending' => 'bg-amber-100 text-amber-800', 'hidden' => 'bg-red-100 text-red-800'];
?>
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Top places &amp; resume boosts</h1>
            <p class="mt-2 text-sm text-gray-600">Company bids (<?= TB::COMPANY_SLOTS ?> places a day), place 1 for a month (<?= $rs(TB::MONTH_PRICE) ?>), logos (<?= $rs(TB::LOGO_PRICE) ?>) and job seekers' resume boosts (<?= TB::RESUME_SLOTS ?> per category, <?= TB::RESUME_DAYS ?> days, max <?= TB::RESUME_MAX_VIEWS ?> employers). All + 18% GST. Bidding 10 AM – 6 PM.
                Public pages: <a class="text-primary font-semibold" href="/top-places" target="_blank">/top-places</a> · <a class="text-primary font-semibold" href="/resume-boost" target="_blank">/resume-boost</a></p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="kind" class="rounded-lg border-gray-300 text-sm"><?php foreach (['' => 'All', 'company' => 'Companies', 'resume' => 'Resume boosts'] as $k => $l): ?><option value="<?= $k ?>" <?= $kind === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
            <select name="status" class="rounded-lg border-gray-300 text-sm"><option value="">Any status</option><?php foreach (['active', 'won', 'waiting', 'paid', 'lapsed', 'lost'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
            <button class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500"><tr><th class="px-4 py-3">Date / period</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Bidder</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Bid (+GST)</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Views</th></tr></thead>
            <tbody>
            <?php foreach ($bids as $b): ?>
                <tr class="border-t">
                    <td class="px-4 py-2"><?= $h(date('d M Y', strtotime((string)$b['slot_date']))) ?><?= $b['kind'] === 'resume' ? ' – ' . $h(date('d M', strtotime(TB::periodEnd((string)$b['slot_date'])))) : '' ?></td>
                    <td class="px-3 py-2"><?= $b['kind'] === 'company' ? 'Company' : 'Resume boost' ?></td>
                    <td class="px-3 py-2"><b><?= $h($b['bidder_name']) ?></b><div class="text-xs text-gray-500"><?= $h($b['email']) ?></div></td>
                    <td class="px-3 py-2"><?= $h($b['category']) ?></td>
                    <td class="px-3 py-2"><?= $rs($b['amount']) ?> <span class="text-xs text-gray-500">(₹<?= number_format(TB::withGst((float)$b['amount']), 2) ?>)</span></td>
                    <td class="px-3 py-2"><span class="rounded px-2 py-0.5 text-xs font-bold <?= $badge[$b['status']] ?? '' ?>"><?= $h($b['status']) ?></span><?= $b['status'] === 'won' && $b['pay_deadline'] ? '<div class="text-xs text-gray-500">pay by ' . $h(date('d M H:i', strtotime((string)$b['pay_deadline']))) . '</div>' : '' ?></td>
                    <td class="px-3 py-2"><?= $b['kind'] === 'resume' && $b['status'] === 'paid' ? TB::boostViews((int)$b['id']) . ' / ' . TB::RESUME_MAX_VIEWS : '' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bids): ?><tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No bids yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="overflow-x-auto rounded-xl bg-white shadow">
            <h2 class="px-4 pt-4 font-bold">Place 1 – monthly bookings</h2>
            <table class="min-w-full text-sm"><tbody>
                <?php foreach ($months as $m): ?><tr class="border-t"><td class="px-4 py-2"><?= $h($m['company_name']) ?></td><td class="px-3 py-2"><?= $h(date('d M', strtotime((string)$m['start_date']))) ?> – <?= $h(date('d M Y', strtotime((string)$m['end_date']))) ?></td><td class="px-3 py-2"><span class="rounded px-2 py-0.5 text-xs font-bold <?= $badge[$m['status']] ?? '' ?>"><?= $h($m['status']) ?></span></td></tr><?php endforeach; ?>
                <?php if (!$months): ?><tr><td class="px-4 py-4 text-gray-500">None yet.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
        <div class="overflow-x-auto rounded-xl bg-white shadow">
            <h2 class="px-4 pt-4 font-bold">Logo rights</h2>
            <table class="min-w-full text-sm"><tbody>
                <?php foreach ($logos as $l): ?><tr class="border-t">
                    <td class="px-4 py-2"><?php if (!empty($l['logo_url'])): ?><img src="<?= $h($l['logo_url']) ?>" alt="" class="inline h-8 w-8 object-contain mr-2"><?php endif; ?><?= $h($l['company_name']) ?></td>
                    <td class="px-3 py-2"><span class="rounded px-2 py-0.5 text-xs font-bold <?= $badge[$l['status']] ?? 'bg-green-100 text-green-800' ?>"><?= $h($l['status']) ?></span></td>
                    <td class="px-3 py-2"><form method="POST" action="/admin/top-places/logo/<?= (int)$l['id'] ?>"><?= $csrf ?><input type="hidden" name="status" value="<?= $l['status'] === 'active' ? 'hidden' : 'active' ?>"><button class="text-xs font-bold text-primary"><?= $l['status'] === 'active' ? 'Hide logo' : 'Show logo' ?></button></form></td>
                </tr><?php endforeach; ?>
                <?php if (!$logos): ?><tr><td class="px-4 py-4 text-gray-500">None yet.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>
</div>
