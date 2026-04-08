<?php
$title = 'Subscription Dashboard';

function render_usage_bar($used, $limit, $label) {
    $percentage = ($limit > 0) ? ($used / $limit) * 100 : 0;
    $percentage = min(100, $percentage);
    $bar_color = $percentage > 90 ? 'bg-red-500' : ($percentage > 70 ? 'bg-yellow-500' : 'bg-indigo-600');

    echo '<div class="mb-4">';
    echo '    <div class="flex justify-between items-center mb-1">';
    echo '        <span class="text-sm font-medium text-gray-700">' . htmlspecialchars($label) . '</span>';
    echo '        <span class="text-sm font-mono text-gray-500">' . (int)$used . ' / ' . ($limit > 0 ? (int)$limit : '∞') . '</span>';
    echo '    </div>';
    echo '    <div class="w-full bg-gray-200 rounded-full h-2.5">';
    echo '        <div class="' . $bar_color . ' h-2.5 rounded-full" style="width: ' . $percentage . '%"></div>';
    echo '    </div>';
    echo '</div>';
}

$status = $subscription['status'] ?? 'inactive';
$status_color = match($status) {
    'active' => 'bg-green-100 text-green-800',
    'trial' => 'bg-blue-100 text-blue-800',
    'pending' => 'bg-yellow-100 text-yellow-800',
    'cancelled' => 'bg-gray-100 text-gray-800',
    default => 'bg-red-100 text-red-800',
};
?>

<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between pb-6 border-b border-gray-200 mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Subscription Dashboard
            </h1>
            <p class="mt-1 text-sm text-gray-500">Manage your plan, track usage, and view billing history.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="/employer/subscription/plans" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Change Plan
            </a>
        </div>
    </div>

    <?php if ($subscription): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Current Plan & Usage -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Current Plan -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Current Plan</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold"><?= htmlspecialchars($plan['name'] ?? 'N/A') ?></dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= ucfirst($subscription['billing_cycle'] ?? 'N/A') ?></dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= !empty($subscription['expires_at']) ? date('F j, Y', strtotime($subscription['expires_at'])) : 'N/A' ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Usage -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Usage This Cycle</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <?php render_usage_bar($usage['job_posts']['used'], $usage['job_posts']['limit'], 'Job Posts'); ?>
                    <?php render_usage_bar($usage['resume_views']['used'], $usage['resume_views']['limit'], 'Resume Views'); ?>
                    <?php render_usage_bar($usage['contacts_views']['used'], $usage['contacts_views']['limit'], 'Contact Views'); ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Plan Features & Payment History -->
        <div class="space-y-8">
            <!-- Plan Features -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Plan Features</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <ul class="divide-y divide-gray-200">
                        <?php 
                        $features = [];
                        if (!empty($plan['features']) && is_string($plan['features'])) {
                            $decoded = json_decode($plan['features'], true);
                            if (is_array($decoded)) {
                                $features = $decoded;
                            }
                        }
                        ?>
                        <?php foreach ($features as $feature): ?>
                        <li class="py-3 px-6 flex items-center">
                            <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="ml-3 text-sm text-gray-700"><?= htmlspecialchars($feature) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Payment History</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <ul class="divide-y divide-gray-200">
                        <?php if (empty($payments)): ?>
                            <li class="py-4 px-6 text-center text-sm text-gray-500">No payments found.</li>
                        <?php else: ?>
                            <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                            <li class="py-3 px-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">₹<?= number_format($payment['amount'], 2) ?></p>
                                        <p class="text-xs text-gray-500">#<?= htmlspecialchars($payment['invoice_number'] ?? $payment['id']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600"><?= !empty($payment['paid_at']) ? date('M d, Y', strtotime($payment['paid_at'])) : 'N/A' ?></p>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="text-center py-3 px-6 border-t border-gray-200">
                        <a href="/employer/billing/invoices" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all invoices &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="text-center bg-white shadow sm:rounded-lg p-12">
            <h3 class="text-lg font-medium text-gray-900">No Active Subscription</h3>
            <p class="mt-1 text-sm text-gray-500">You do not have an active subscription. Please choose a plan to continue.</p>
            <div class="mt-6">
                <a href="/employer/subscription/plans" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    View Plans
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
