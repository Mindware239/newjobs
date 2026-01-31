<h1 class="text-3xl font-bold text-gray-900 mb-6">Billing Overview</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Current Plan</p>
        <p class="text-xl font-semibold mt-1"><?= htmlspecialchars($plan ? ($plan->attributes['name'] ?? 'Free') : 'Free') ?></p>
        <p class="text-gray-600 mt-1">Renewal: <?= htmlspecialchars($subscription ? ($subscription->attributes['next_billing_date'] ?? '—') : '—') ?></p>
        <a href="/employer/subscription/plans" class="mt-3 inline-block px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Manage plan</a>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Balance Due</p>
        <p class="text-xl font-semibold mt-1">₹<?= number_format((float)($balanceDue ?? 0), 2) ?></p>
        <a href="/employer/billing/invoices" class="mt-3 inline-block text-indigo-600 hover:text-indigo-800">View invoices</a>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Upcoming Payment</p>
        <p class="text-xl font-semibold mt-1">₹<?= $upcomingAmount ? number_format((float)$upcomingAmount, 2) : '—' ?></p>
        <p class="text-gray-600 mt-1">On <?= $upcomingDate ? date('M d, Y', strtotime($upcomingDate)) : '—' ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Last Payment</p>
        <p class="text-xl font-semibold mt-1">₹<?= $lastPayment ? number_format((float)($lastPayment['amount'] ?? 0), 2) : '—' ?></p>
        <p class="text-gray-600 mt-1"><?= $lastPayment ? date('M d, Y', strtotime($lastPayment['created_at'])) : '—' ?></p>
        <?php if ($lastPayment): ?>
        <a href="/employer/invoices/<?= (int)$lastPayment['id'] ?>" class="mt-3 inline-block text-indigo-600 hover:text-indigo-800">View details</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
        <h2 class="text-xl font-bold mb-4">Recent Transactions</h2>
        <?php if (!empty($recentTransactions)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($recentTransactions as $t): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700"><?= date('M d, Y', strtotime($t['created_at'] ?? 'now')) ?></td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            <?= htmlspecialchars(($t['kind'] === 'subscription' ? ($t['billing_cycle'] ?? 'Monthly') . ' Subscription' : ($t['item'] ?? 'Add-on'))) ?>
                        </td>
                        <td class="px-4 py-2 text-sm font-semibold">₹<?= number_format((float)($t['amount'] ?? 0), 2) ?></td>
                        <td class="px-4 py-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($t['status'] ?? '') === 'completed' ? 'bg-indigo-100 text-indigo-800' : (($t['status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($t['status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <?php if (!empty($t['id']) && $t['kind'] === 'subscription'): ?>
                            <a href="/employer/invoices/<?= (int)$t['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-600">You don’t have any payments yet.</p>
        <a href="/employer/subscription/plans" class="text-indigo-600 hover:text-indigo-800 font-semibold">Buy a plan</a>
        <?php endif; ?>
        <div class="mt-4 flex gap-3">
            <a href="/employer/billing/invoices" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md">View all invoices</a>
            <a href="/employer/billing/transactions" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md">View all transactions</a>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Alerts</h2>
        <div class="space-y-3">
            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-md text-yellow-700">Payment failed last time
                <a href="/employer/billing/transactions" class="ml-2 text-indigo-600 hover:text-indigo-800">Retry payment</a></div>
            <div class="p-3 bg-red-50 border border-red-200 rounded-md text-red-700">No valid payment method
                <a href="/employer/billing/payment-methods" class="ml-2 text-indigo-600 hover:text-indigo-800">Add card</a></div>
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-md text-indigo-700">Quota left
                <span class="ml-2">Contacts: <?= (int)($subscription ? ($subscription->attributes['contacts_used_this_month'] ?? 0) : 0) ?>/<?= (int)($plan ? ($plan->attributes['max_contacts_per_month'] ?? 0) : 0) ?></span></div>
        </div>
        <div class="mt-4">
            <a href="/employer/billing/settings" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update billing information</a>
        </div>
    </div>
</div>
