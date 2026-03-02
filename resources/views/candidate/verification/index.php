<div class="max-w-5xl mx-auto">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Employment Verification</h1>
    <p class="text-sm text-gray-600">Add previous employment and track verification status.</p>
  </div>

  <div class="bg-white shadow rounded-lg p-6 mb-10">
    <h2 class="text-lg font-semibold mb-4">Add Employment</h2>
    <form action="/candidate/verification" method="post" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Company Name</label>
        <input name="company_name" required class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Designation</label>
        <input name="designation" required class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Employee ID</label>
        <input name="employee_id" class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" name="start_date" required class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">End Date</label>
        <input type="date" name="end_date" class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div class="sm:col-span-2 flex items-center gap-3">
        <input id="consent" type="checkbox" name="consent" value="1" required class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
        <label for="consent" class="text-sm text-gray-700">
          I authorize the portal to contact previous employer for verification.
        </label>
      </div>
      <div class="sm:col-span-2">
        <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save & Continue</button>
      </div>
    </form>
  </div>

  <div class="bg-white shadow rounded-lg">
    <div class="p-6 border-b">
      <h2 class="text-lg font-semibold">Your Employment Records</h2>
    </div>
    <div class="divide-y">
      <?php foreach (($records ?? []) as $r): ?>
        <div class="p-6 flex items-center justify-between">
          <div>
            <div class="font-medium text-gray-900"><?= htmlspecialchars($r['company_name'] ?? '') ?></div>
            <div class="text-sm text-gray-600"><?= htmlspecialchars($r['designation'] ?? '') ?> · <?= htmlspecialchars($r['start_date'] ?? '') ?> to <?= htmlspecialchars($r['end_date'] ?? 'Present') ?></div>
            <div class="mt-2">
              <?php $status = $r['status_overall'] ?? 'under_review'; ?>
              <?php if ($status === 'verified'): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Verified</span>
              <?php elseif ($status === 'not_verified'): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Not Verified</span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Under Review</span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <a href="/candidate/verification/<?= (int)$r['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm">Open</a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($records ?? [])): ?>
        <div class="p-6 text-sm text-gray-600">No employment records yet. Add one above.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
