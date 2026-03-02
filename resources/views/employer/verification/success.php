<div class="max-w-3xl mx-auto">
  <div class="mb-6">
    <a href="/employer/verification" class="text-sm text-blue-600">&larr; Back</a>
  </div>
  <div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-xl font-semibold text-gray-900">Payment Successful</h1>
    <p class="text-sm text-gray-600 mb-4">You can now view and download the official employment verification report.</p>
    <div class="rounded-lg border p-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm text-gray-700">Invoice</div>
          <div class="text-sm font-medium"><?= htmlspecialchars((string)($unlock['invoice_number'] ?? '')) ?></div>
        </div>
        <div>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Unlocked</span>
        </div>
      </div>
    </div>
    <div class="mb-6">
      <div class="text-sm text-gray-700">Employment</div>
      <div class="mt-1 text-gray-900 font-medium"><?= htmlspecialchars(($employment['first_name'] ?? '') . ' ' . ($employment['last_name'] ?? '')) ?></div>
      <div class="text-sm text-gray-600"><?= htmlspecialchars($employment['company_name'] ?? '') ?> · <?= htmlspecialchars($employment['designation'] ?? '') ?></div>
      <div class="text-xs text-gray-500"><?= htmlspecialchars($employment['start_date'] ?? '') ?> to <?= htmlspecialchars($employment['end_date'] ?? 'Present') ?></div>
    </div>
    <div>
      <a href="/employer/verification/details/<?= (int)($unlock['id'] ?? 0) ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">View Verification Details</a>
      <a href="/employer/verification/report/<?= (int)($unlock['id'] ?? 0) ?>" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Download Verification Report</a>
    </div>
  </div>
</div>
