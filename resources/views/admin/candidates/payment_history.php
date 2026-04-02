<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Payment History</h1>
        <p class="mt-2 text-sm text-gray-600">Payment history for candidate: <span class="font-semibold"><?= htmlspecialchars($candidate['full_name']) ?></span></p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
         <a href="/admin/candidates/<?= htmlspecialchars($candidate['id']) ?>" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Profile
        </a>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction ID</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gateway</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Purchase Date</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zM19.5 16.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No payment history</h3>
                                <p class="mt-1 text-sm text-gray-500">This candidate has not made any payments yet.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="font-mono"><?= htmlspecialchars($payment['payment_id'] ?: 'N/A') ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $payment['plan_type']))) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₹<?= htmlspecialchars(number_format((float)$payment['amount'], 2)) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars(ucfirst($payment['payment_method'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                                $status = strtolower($payment['status'] ?? 'unknown');
                                $statusClass = match($status) {
                                    'completed' => 'bg-green-100 text-green-800 border border-green-200',
                                    'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                    'failed' => 'bg-red-100 text-red-800 border border-red-200',
                                    'refunded' => 'bg-gray-100 text-gray-800 border border-gray-200',
                                    default => 'bg-gray-100 text-gray-800 border border-gray-200'
                                };
                            ?>
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= !empty($payment['created_at']) ? date('M d, Y, g:i A', strtotime($payment['created_at'])) : 'N/A' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= !empty($payment['expires_at']) ? date('M d, Y', strtotime($payment['expires_at'])) : 'N/A' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>