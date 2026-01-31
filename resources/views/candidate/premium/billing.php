<?php
$content = ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Billing & Receipts</h1>
            <p class="text-gray-600 mt-1">Manage your premium subscription and view billing history</p>
        </div>
        <a href="/candidate/premium/plans" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
            Manage Plan
        </a>
    </div>

    <?php 
    $isPremium = $candidate->isPremium();
    $premiumExpires = $candidate->attributes['premium_expires_at'] ?? null;
    $currentPlanType = null;
    if (!empty($items) && isset($items[0])) {
        foreach ($items as $item) {
            if (($item['status'] ?? '') === 'completed') {
                $currentPlanType = $item['plan_type'] ?? null;
                break;
            }
        }
    }
    ?>

    <!-- Active Premium Status -->
    <?php if ($isPremium && $premiumExpires): ?>
    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-blue-700 font-medium">Active Premium Subscription</div>
                    <div class="text-2xl font-bold text-blue-900 mt-1">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $currentPlanType ?? 'Premium'))) ?>
                    </div>
                    <div class="text-sm text-blue-600 mt-1">
                        Expires: <?= date('M d, Y', strtotime($premiumExpires)) ?>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">
                    <span>⭐ Premium Active</span>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8 text-center">
        <p class="text-gray-600 mb-4">You don't have an active premium subscription</p>
        <a href="/candidate/premium/plans" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            Upgrade to Premium
        </a>
    </div>
    <?php endif; ?>

    <!-- Premium Features Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Premium Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Top Profile Visibility</div>
                    <div class="text-sm text-gray-600">Show your profile at the top to recruiters</div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Higher Search Visibility</div>
                    <div class="text-sm text-gray-600">Priority placement in search results</div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Verified Badge</div>
                    <div class="text-sm text-gray-600">Stand out with a verified profile badge</div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Unlimited Applications</div>
                    <div class="text-sm text-gray-600">Apply to as many jobs as you want</div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Advanced Analytics</div>
                    <div class="text-sm text-gray-600">Track your profile performance</div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold text-gray-900">Priority Support</div>
                    <div class="text-sm text-gray-600">Get faster response from support team</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Billing History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No billing history found
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $row): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('M d, Y', strtotime($row['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($row['plan_type'] ?? 'Unknown')))) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= strtoupper($row['payment_method'] ?? '—') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                ₹<?= number_format((float)($row['amount'] ?? 0), 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = strtolower($row['status'] ?? 'pending');
                                $statusClasses = [
                                    'completed' => 'bg-blue-100 text-blue-800',
                                    'pending' => 'bg-gray-100 text-gray-800',
                                    'refunded' => 'bg-gray-200 text-gray-700',
                                    'failed' => 'bg-gray-300 text-gray-800'
                                ];
                                $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <?php if (($row['status'] ?? '') === 'completed' && !empty($row['receipt_url'])): ?>
                                    <a href="<?= htmlspecialchars($row['receipt_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                                        Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
