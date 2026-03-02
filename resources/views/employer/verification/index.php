<div class="max-w-5xl mx-auto">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Candidate Employment Verification</h1>
    <p class="text-sm text-gray-600">View badge and unlock detailed verification reports.</p>
  </div>
  <div class="bg-white shadow rounded-lg">
    <div class="p-6 border-b">
      <h2 class="text-lg font-semibold">Employment Records</h2>
    </div>
    <?php if (!empty($unlocked ?? 0)): ?>
      <div class="px-6 pt-4">
        <div class="rounded-md bg-green-50 border border-green-200 text-green-800 p-3 text-sm">
          Verification unlocked. You can view details and download reports.
        </div>
      </div>
    <?php endif; ?>
    <div class="divide-y">
      <?php foreach (($records ?? []) as $r): ?>
        <div class="p-6 flex items-center justify-between">
          <div>
            <div class="font-medium text-gray-900"><?= htmlspecialchars($r['company_name'] ?? '') ?></div>
            <?php
              $sd = (string)($r['start_date'] ?? '');
              $ed = (string)($r['end_date'] ?? '');
              $sdText = ($sd && $sd !== '0000-00-00') ? date('M Y', strtotime($sd)) : 'Not specified';
              $edText = ($ed && $ed !== '0000-00-00') ? date('M Y', strtotime($ed)) : 'Present';
            ?>
            <div class="text-sm text-gray-600"><?= htmlspecialchars($r['designation'] ?? '') ?> · <?= htmlspecialchars($sdText) ?> to <?= htmlspecialchars($edText) ?></div>
            <div class="mt-2">
              <?php if ((int)($r['badge'] ?? 0) === 1): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Employment Verified ✔</span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Under Review</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-right">
            <?php if (!empty($unlocked ?? 0)): ?>
              <a href="/employer/verification/details/<?= (int)($unlocked ?? 0) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                </svg>
                View details
              </a>
            <?php else: ?>
            <a href="/employer/verification/unlock/<?= (int)$r['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17a2 2 0 01-2-2v-3a2 2 0 114 0v3a2 2 0 01-2 2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10V7a5 5 0 0110 0v3"></path>
                <rect x="5" y="10" width="14" height="10" rx="2" ry="2" stroke-width="2"></rect>
              </svg>
              Buy a plan to unlock
            </a>
            <?php endif; ?>
            <div class="text-xs text-gray-500 mt-1">₹<?= htmlspecialchars((string)($price ?? '999')) ?> one-time</div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($records ?? [])): ?>
        <div class="p-6 text-sm text-gray-600">No records found for this candidate.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
