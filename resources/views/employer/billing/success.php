<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Payment Successful</h1>
    <div class="mb-4 p-4 bg-green-50 text-green-800 rounded-md">Your payment has been processed successfully.</div>
    <div class="text-gray-700 mb-6">
        <span class="font-medium">Receipt #:</span>
        <span><?= isset($subPayId) && (int)$subPayId > 0 ? (int)$subPayId : '—' ?></span>
    </div>
    <div class="flex gap-3">
        <a href="/employer/billing/invoices" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">View Invoices</a>
        <a href="/employer/billing/transactions" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">View Transactions</a>
        <a href="/employer/subscription/dashboard" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Subscription Dashboard</a>
    </div>
</div>
