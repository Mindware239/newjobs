<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 */
?>

<h1 class="text-3xl font-bold text-gray-900 mb-6">Payments</h1>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="text-sm text-gray-600">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>" class="w-full px-3 py-2 border rounded-md" />
        </div>
        <div>
            <label class="text-sm text-gray-600">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>" class="w-full px-3 py-2 border rounded-md" />
        </div>
        <div>
            <label class="text-sm text-gray-600">Status</label>
            <select name="status" class="w-full px-3 py-2 border rounded-md">
                <?php $statuses = ['', 'pending','processing','completed','failed','refunded','cancelled']; ?>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= (($filters['status'] ?? '') === $st ? 'selected' : '') ?>><?= $st ? ucfirst($st) : 'All' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Filter</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Subscription Payments</h2>
        <?php if (!empty($subscriptionPayments)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cycle</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Gateway</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($subscriptionPayments as $p): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= date('M d, Y', strtotime($p['created_at'] ?? 'now')) ?></td>
                        <td class="px-4 py-2 text-sm font-semibold">₹<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= ucfirst($p['billing_cycle'] ?? 'monthly') ?></td>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= strtoupper($p['gateway'] ?? 'razorpay') ?></td>
                        <td class="px-4 py-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($p['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : (($p['status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($p['status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <?php if (!empty($p['id'])): ?>
                            <a href="/employer/invoices/<?= (int)$p['id'] ?>" class="text-purple-600 hover:text-purple-800 text-sm">View Invoice</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-gray-600">No subscription payments found.</p>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Add-on Payments</h2>
        <?php if (!empty($employerPayments)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($employerPayments as $p): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= date('M d, Y', strtotime($p['created_at'] ?? 'now')) ?></td>
                        <td class="px-4 py-2 text-sm font-semibold">₹<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($p['item'] ?? ($p['description'] ?? 'Addon')) ?></td>
                        <td class="px-4 py-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($p['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : (($p['status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($p['status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <?php if (!empty($p['invoice_url'])): ?>
                            <a href="<?= htmlspecialchars($p['invoice_url']) ?>" target="_blank" class="text-purple-600 hover:text-purple-800 text-sm">Invoice</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-gray-600">No add-on payments found.</p>
        <?php endif; ?>
    </div>
</div>

