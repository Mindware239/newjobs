<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Payment Failed</h1>
    <div class="mb-4 p-4 bg-red-50 text-red-800 rounded-md"><?= htmlspecialchars($reason ?? 'Payment failed') ?></div>
    <a href="/employer/billing/transactions" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md">Back to Transactions</a>
</div>

