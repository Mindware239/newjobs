<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Subscription Dashboard</h1>
        <p class="text-xl text-gray-600">Manage your subscription and track usage</p>
    </div>

    <?php if (!$subscription): ?>
    <!-- No Subscription -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="text-lg font-semibold text-yellow-800 mb-2">No Active Subscription</h3>
        <p class="text-yellow-700 mb-6">You don't have an active subscription plan. Subscribe to unlock premium features.</p>
        <a href="/employer/subscription/plans" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
            View Plans & Subscribe
        </a>
    </div>
    <?php else: ?>
    
    <!-- Current Plan Card -->
    <div class="bg-white rounded-xl shadow-lg border-2 border-blue-500 mb-8">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($plan['name'] ?? 'No Plan') ?></h2>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($plan['description'] ?? '') ?></p>
                </div>
                <?php if ($plan['is_featured'] ?? 0): ?>
                <span class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold">MOST POPULAR</span>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Status</p>
                    <p class="text-lg font-semibold">
                        <span class="px-3 py-1 rounded-full text-sm <?= ($subscription['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ucfirst($subscription['status'] ?? 'inactive') ?>
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Started</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?= $subscription['started_at'] ? date('M d, Y', strtotime($subscription['started_at'])) : 'N/A' ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Expires</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?= $subscription['expires_at'] ? date('M d, Y', strtotime($subscription['expires_at'])) : 'Never' ?>
                    </p>
                </div>
            </div>
            
            <?php 
            $expiresAt = $subscription['expires_at'] ?? null;
            if ($expiresAt) {
                $daysLeft = (strtotime($expiresAt) - time()) / (60 * 60 * 24);
                if ($daysLeft <= 7 && $daysLeft > 0) {
            ?>
            <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Renewal Reminder:</strong> Your subscription expires in <?= (int)$daysLeft ?> day(s). 
                            <a href="/employer/subscription/plans?upgrade=1" class="font-semibold underline">Renew now</a>
                        </p>
                    </div>
                </div>
            </div>
            <?php } elseif ($daysLeft <= 0) { ?>
            <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong>Subscription Expired:</strong> Your subscription has expired. 
                            <a href="/employer/subscription/plans?upgrade=1" class="font-semibold underline">Renew now</a> to continue using premium features.
                        </p>
                    </div>
                </div>
            </div>
            <?php 
                }
            }
            ?>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Job Posts -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">Job Posts</h3>
                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900"><?= $usage['job_posts_used'] ?></p>
                <p class="ml-2 text-sm text-gray-600">
                    / <?= $usage['job_posts_limit'] == -1 ? '∞' : $usage['job_posts_limit'] ?>
                </p>
            </div>
            <?php if ($usage['job_posts_limit'] != -1): ?>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= min(100, ($usage['job_posts_used'] / max(1, $usage['job_posts_limit'])) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contacts -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">Contacts This Month</h3>
                <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900"><?= $usage['contacts_used'] ?></p>
                <p class="ml-2 text-sm text-gray-600">
                    / <?= $usage['contacts_limit'] == -1 ? '∞' : $usage['contacts_limit'] ?>
                </p>
            </div>
            <?php if ($usage['contacts_limit'] != -1): ?>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: <?= min(100, ($usage['contacts_used'] / max(1, $usage['contacts_limit'])) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Resume Downloads -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">Resume Downloads</h3>
                <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900"><?= $usage['resume_downloads_used'] ?></p>
                <p class="ml-2 text-sm text-gray-600">
                    / <?= $usage['resume_downloads_limit'] == -1 ? '∞' : $usage['resume_downloads_limit'] ?>
                </p>
            </div>
            <?php if ($usage['resume_downloads_limit'] != -1): ?>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: <?= min(100, ($usage['resume_downloads_used'] / max(1, $usage['resume_downloads_limit'])) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Chat Messages -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600">Chat Messages</h3>
                <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900"><?= $usage['chat_messages_used'] ?></p>
                <p class="ml-2 text-sm text-gray-600">
                    / <?= $usage['chat_messages_limit'] == -1 ? '∞' : $usage['chat_messages_limit'] ?>
                </p>
            </div>
            <?php if ($usage['chat_messages_limit'] != -1): ?>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: <?= min(100, ($usage['chat_messages_used'] / max(1, $usage['chat_messages_limit'])) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="/employer/subscription/plans?upgrade=1" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <svg class="mx-auto h-8 w-8 text-blue-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
            <h3 class="font-semibold text-gray-900 mb-2">Upgrade Plan</h3>
            <p class="text-sm text-gray-600">Get more features and higher limits</p>
        </a>
        
        <a href="/employer/subscription/plans" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <svg class="mx-auto h-8 w-8 text-green-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <h3 class="font-semibold text-gray-900 mb-2">Change Plan</h3>
            <p class="text-sm text-gray-600">Switch to a different subscription</p>
        </a>
        
        <a href="/employer/payments" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <svg class="mx-auto h-8 w-8 text-purple-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="font-semibold text-gray-900 mb-2">Payment History</h3>
            <p class="text-sm text-gray-600">View invoices and receipts</p>
        </a>
    </div>

    <!-- Payment History -->
    <?php if (!empty($payments)): ?>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $payment['created_at'] ? date('M d, Y', strtotime($payment['created_at'])) : 'N/A' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₹<?= number_format($payment['amount'] ?? 0, 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= ($payment['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : 
                                    (($payment['status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($payment['status'] ?? 'unknown') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if (!empty($payment['invoice_url'])): ?>
                            <a href="<?= htmlspecialchars($payment['invoice_url']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>                                    <!-- Remove the following line as it's not needed -->