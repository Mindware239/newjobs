<h1 class="text-3xl font-bold text-gray-900 mb-6">Transactions</h1>

<?php $sum = $summary ?? ['total'=>0,'paid'=>0.0,'pending'=>0.0,'failed'=>0]; ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-xs text-gray-500">Total Transactions</div>
        <div class="mt-1 text-xl font-semibold text-gray-900"><?= (int)($sum['total'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-xs text-gray-500">Total Paid</div>
        <div class="mt-1 text-xl font-semibold text-green-700">₹<?= number_format((float)($sum['paid'] ?? 0), 2) ?></div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-xs text-gray-500">Pending Amount</div>
        <div class="mt-1 text-xl font-semibold text-yellow-700">₹<?= number_format((float)($sum['pending'] ?? 0), 2) ?></div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-xs text-gray-500">Failed Transactions</div>
        <div class="mt-1 text-xl font-semibold text-red-700"><?= (int)($sum['failed'] ?? 0) ?></div>
    </div>
</div>

<div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
        <div>
            <label class="block text-xs text-[#3c50ff] mb-1">From Date</label>
            <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>" class="w-full px-3 py-2 border rounded-md" />
        </div>
        <div>
            <label class="block text-xs text-[#3c50ff] mb-1">To Date</label>
            <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>" class="w-full px-3 py-2 border rounded-md" />
        </div>
        <div>
            <label class="block text-xs text-[#3c50ff] mb-1">Transaction Type</label>
            <select name="method" class="w-full px-3 py-2 border rounded-md">
                <?php $methods = ['all','card','upi','netbanking','wallet','razorpay','stripe','payu','cashfree']; ?>
                <?php foreach ($methods as $m): ?>
                    <option value="<?= $m ?>" <?= (($filters['method'] ?? 'all') === $m ? 'selected' : '') ?>><?= strtoupper($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs text-[#3c50ff] mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border rounded-md">
                <?php $statuses = ['all','completed','failed','pending','refunded']; ?>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= (($filters['status'] ?? 'all') === $st ? 'selected' : '') ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-50 text-[#3c50ff] hover:bg-blue-100 hover:shadow-md text-sm font-semibold transition-colors shadow-sm">Filter</button>
            <a href="/employer/billing/transactions" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Reset Filters</a>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Transaction ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Gateway ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Date & Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach (($rows ?? []) as $r): ?>
                <?php 
                    $isSub = ($r['kind'] ?? '') === 'subscription';
                    $st = strtolower((string)($r['status'] ?? 'pending'));
                    $badge = 'bg-gray-100 text-gray-800';
                    if ($st === 'completed' || $st === 'success') $badge = 'bg-green-100 text-green-700';
                    elseif ($st === 'pending') $badge = 'bg-yellow-100 text-yellow-700';
                    elseif ($st === 'failed' || $st === 'refunded') $badge = 'bg-red-100 text-red-700';
                    $gateway = strtoupper($r['gateway'] ?? ($isSub ? 'RAZORPAY' : '-'));
                    $gid = $r['gateway_payment_id'] ?? $r['gateway_order_id'] ?? '';
                    $hasGid = $isSub && !!$gid;
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm"><?= $isSub ? ('SUB-' . (int)$r['id']) : ('ADD-' . (int)$r['id']) ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($hasGid): ?>
                            <div class="flex items-center gap-2">
                                <span><?= htmlspecialchars($gid) ?></span>
                                <?php if ($gateway === 'RAZORPAY'): ?>
                                    <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Razorpay_logo.svg" alt="Razorpay" class="h-4 opacity-80" />
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Not generated</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm"><?= date('M d, Y H:i', strtotime($r['created_at'] ?? 'now')) ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($isSub): ?>
                            Subscription • <?= ucfirst($r['billing_cycle'] ?? 'monthly') ?>
                        <?php else: ?>
                            <?= htmlspecialchars($r['description'] ?? $r['item'] ?? 'Add-on') ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm"><?= $isSub ? $gateway : '-' ?></td>
                    <td class="px-4 py-3 text-sm font-semibold">₹<?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge ?>">
                            <?= ucfirst($r['status'] ?? 'pending') ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if ($st === 'completed' || $st === 'success'): ?>
                            <?php if ($isSub): ?>
                                <a href="/employer/invoices/<?= (int)$r['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                                    <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path></svg>
                                    View Invoice
                                </a>
                            <?php else: ?>
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-gray-100 text-gray-500 rounded-md cursor-not-allowed" disabled>
                                    Pay Now
                                </button>
                            <?php endif; ?>
                        <?php elseif ($st === 'failed'): ?>
                            <a href="/employer/billing/pay/<?= (int)$r['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-[#3c50ff] hover:bg-blue-50 hover:shadow-md text-sm font-semibold transition-colors shadow-sm">
                                Pay Now
                            </a>
                        <?php else: ?>
                            <a href="/employer/billing/pay/<?= (int)$r['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-[#3c50ff] hover:bg-blue-50 hover:shadow-md text-sm font-semibold transition-colors shadow-sm">
                                Pay Now
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-600">No transactions found for selected filters</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php 
    $pg = $pagination ?? ['page'=>1,'pages'=>1,'total'=>0,'per_page'=>10];
    $page = (int)($pg['page'] ?? 1);
    $pages = (int)($pg['pages'] ?? 1);
    $total = (int)($pg['total'] ?? 0);
    $perPage = (int)($pg['per_page'] ?? 10);
    $start = $total ? (($page - 1) * $perPage + 1) : 0;
    $end = min($total, $page * $perPage);
    $q = $_GET ?? [];
    unset($q['page']);
    $baseQs = http_build_query($q);
    function pageUrl($i, $baseQs) {
        $pref = $baseQs ? ($baseQs . '&') : '';
        return '/employer/billing/transactions?' . $pref . 'page=' . (int)$i;
    }
?>
<div class="mt-4 flex items-center justify-between">
    <div class="text-sm text-gray-600">Showing <?= (int)$start ?>–<?= (int)$end ?> of <?= (int)$total ?></div>
    <div class="flex items-center gap-2">
        <a href="<?= pageUrl(max(1, $page-1), $baseQs) ?>" class="px-3 py-1.5 text-sm rounded-md bg-gray-100 text-gray-700 <?= $page <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200' ?>">Previous</a>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= pageUrl($i, $baseQs) ?>" class="px-3 py-1.5 text-sm rounded-md <?= $i === $page ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"><?= (int)$i ?></a>
        <?php endfor; ?>
        <a href="<?= pageUrl(min($pages, $page+1), $baseQs) ?>" class="px-3 py-1.5 text-sm rounded-md bg-gray-100 text-gray-700 <?= $page >= $pages ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200' ?>">Next</a>
    </div>
</div>
