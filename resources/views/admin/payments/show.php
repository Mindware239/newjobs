<div>
    <div class="mb-8">
        <a href="/admin/payments" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Back to Payments</a>
        <h1 class="text-3xl font-bold text-gray-900">Payment Details</h1>
        <p class="mt-2 text-sm text-gray-600">View transaction information</p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Transaction ID</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['transaction_id'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <?php 
                        $status = strtolower((string)($payment['status'] ?? ''));
                        $badge = 'bg-gray-100 text-gray-800';
                        if ($status === 'completed' || $status === 'success') $badge = 'bg-green-100 text-green-800';
                        elseif ($status === 'pending') $badge = 'bg-yellow-100 text-yellow-800';
                        elseif ($status === 'failed' || $status === 'refunded') $badge = 'bg-red-100 text-red-800';
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge ?>">
                            <?= ucfirst($status ?: 'unknown') ?>
                        </span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Amount</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars((string)($payment['currency'] ?? '')) ?> <?= number_format((float)($payment['amount'] ?? 0), 2) ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Method</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['payment_method'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Company</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['company_name'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Employer Email</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['employer_email'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Created At</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($payment['created_at']) ? date('M d, Y H:i', strtotime($payment['created_at'])) : '—' ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Updated At</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($payment['updated_at']) ? date('M d, Y H:i', strtotime($payment['updated_at'])) : '—' ?></div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Description</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['description'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Actions</h3>
                <div class="space-y-3">
                    <?php if (($payment['status'] ?? '') === 'completed'): ?>
                        <form method="POST" action="/admin/payments/<?= (int)$payment['id'] ?>/refund" class="space-y-2">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars((string)($payment['amount'] ?? '')) ?>" class="w-full px-3 py-2 border rounded" placeholder="Amount">
                            <input type="text" name="reason" class="w-full px-3 py-2 border rounded" placeholder="Reason">
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Refund</button>
                        </form>
                    <?php else: ?>
                        <div class="text-sm text-gray-600">Refund available only for completed payments.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
