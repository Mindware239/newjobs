<div class="max-w-3xl mx-auto">
  <div class="mb-6">
    <a href="/employer/verification" class="text-sm text-blue-600">&larr; Back</a>
  </div>
  <div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-xl font-semibold text-gray-900">Unlock Verification Report</h1>
    <p class="text-sm text-gray-600 mb-4">Complete payment to view and download the official employment verification report.</p>

    <div class="rounded-lg border p-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm text-gray-700">Price</div>
          <div class="text-2xl font-bold">₹<?= htmlspecialchars((string)($unlock['amount'] ?? '999')) ?></div>
        </div>
        <div>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Secure Checkout</span>
        </div>
      </div>
    </div>

    <form action="/employer/verification/mark-paid/<?= (int)$unlock['id'] ?>" method="post" class="space-y-4">
      <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Mark as Paid (Sandbox)</button>
      <p class="text-xs text-gray-500">Integrate Razorpay here. This button simulates payment for testing.</p>
    </form>
  </div>
</div>
