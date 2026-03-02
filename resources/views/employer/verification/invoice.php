<div class="max-w-3xl mx-auto">
  <div class="mb-6">
    <a href="/employer/verification" class="text-sm text-blue-600">&larr; Back</a>
  </div>
  <div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-xl font-semibold text-gray-900">Invoice</h1>
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <div class="text-sm text-gray-600">Invoice Number</div>
        <div class="font-medium text-gray-900"><?= htmlspecialchars((string)($unlock['invoice_number'] ?? '')) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Amount</div>
        <div class="font-medium text-gray-900">₹<?= htmlspecialchars((string)($unlock['amount'] ?? '999')) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Status</div>
        <div class="font-medium text-gray-900"><?= htmlspecialchars((string)($unlock['status'] ?? '')) ?></div>
      </div>
    </div>
    <div class="mt-6">
      <a href="/employer/verification/report/<?= (int)($unlock['id'] ?? 0) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Download Verification Report</a>
    </div>
  </div>
</div>
