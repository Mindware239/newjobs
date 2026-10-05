<h1 class="text-3xl font-bold text-gray-900 mb-6">Billing Overview</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Current Plan</p>
        <p class="text-xl font-semibold mt-1"><?= htmlspecialchars($plan ? ($plan->attributes['name'] ?? 'Free') : 'Free') ?></p>
        <p class="text-gray-600 mt-1">Renewal: <?= htmlspecialchars($subscription ? ($subscription->attributes['next_billing_date'] ?? '—') : '—') ?></p>
        <a href="/employer/subscription/plans" class="mt-3 inline-block px-3 py-2 bg-primary text-white rounded-md hover:bg-primary-600">Manage plan</a>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-600">Balance Due</p>
        <p class="text-xl font-semibold mt-1">₹<?= number_format((float)($balanceDue ?? 0), 2) ?></p>
        <a href="/employer/billing/invoices" class="mt-3 inline-block text-primary hover:text-primary">View invoices</a>
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
        <a href="/employer/invoices/<?= (int)$lastPayment['id'] ?>" class="mt-3 inline-block text-primary hover:text-primary">View details</a>
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
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($t['status'] ?? '') === 'completed' ? 'bg-primary-50 text-primary' : (($t['status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($t['status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <?php if (!empty($t['id']) && $t['kind'] === 'subscription'): ?>
                            <a href="/employer/invoices/<?= (int)$t['id'] ?>" class="text-primary hover:text-primary text-sm">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-600">You don’t have any payments yet.</p>
        <a href="/employer/subscription/plans" class="text-primary hover:text-primary font-semibold">Buy a plan</a>
        <?php endif; ?>
        <div class="mt-4 flex gap-3">
            <a href="/employer/billing/invoices" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md">View all invoices</a>
            <a href="/employer/billing/transactions" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md">View all transactions</a>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Alerts</h2>
        <div class="space-y-3">
            <?php foreach (($alerts ?? []) as $alert): ?>
            <?php
                $severity = $alert['severity'] ?? 'info';
                $class = $severity === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : ($severity === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-primary-50 border-primary-100 text-primary-600');
            ?>
            <div class="p-3 border rounded-md <?= $class ?>">
                <?= htmlspecialchars($alert['message'] ?? '') ?>
                <?php if (!empty($alert['action_url'])): ?>
                <a href="<?= htmlspecialchars($alert['action_url']) ?>" class="ml-2 text-primary hover:text-primary">Open</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div class="p-3 bg-primary-50 border border-primary-100 rounded-md text-primary-600">Quota left
                <span class="ml-2">Contacts: <?= (int)($subscription ? ($subscription->attributes['contacts_used_this_month'] ?? 0) : 0) ?>/<?= (int)($plan ? ($plan->attributes['max_contacts_per_month'] ?? 0) : 0) ?></span></div>
        </div>
        <div class="mt-4">
            <a href="/employer/billing/settings" class="px-3 py-2 bg-primary text-white rounded-md hover:bg-primary-600">Update billing information</a>
        </div>
    </div>
</div>











