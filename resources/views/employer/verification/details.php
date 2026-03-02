<div class="max-w-5xl mx-auto">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Verification Details</h1>
      <p class="text-sm text-gray-600">Unlocked and approved. Summary shown with privacy safeguards.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="/employer/verification/report/<?= (int)($unlock['id'] ?? 0) ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path></svg>
        Download official report
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Employment</h2>
        <div class="grid md:grid-cols-3 gap-4">
          <div><div class="text-xs text-gray-500">Candidate</div><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars(($employment['first_name'] ?? '').' '.($employment['last_name'] ?? '')) ?></div></div>
          <div><div class="text-xs text-gray-500">Company</div><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employment['company_name'] ?? '') ?></div></div>
          <div><div class="text-xs text-gray-500">Designation</div><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employment['designation'] ?? '') ?></div></div>
          <div><div class="text-xs text-gray-500">Period</div><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employment['start_date'] ?? '') ?> to <?= htmlspecialchars($employment['end_date'] ?? 'Present') ?></div></div>
          <div><div class="text-xs text-gray-500">Status</div><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Verified</span></div>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Documents (summary)</h2>
        <?php
          $types = [
            'offer_letter' => 'Offer Letter',
            'relieving_letter' => 'Relieving Letter',
            'experience_letter' => 'Experience Letter',
            'salary_slip' => 'Salary Slip',
            'salary_slips' => 'Salary Slip',
            'bank_statement' => 'Bank Statement',
            'form16' => 'Form 16'
          ];
          $grouped = [];
          foreach (($documents ?? []) as $d) { $grouped[$d['doc_type']][] = $d; }
        ?>
        <div class="grid md:grid-cols-2 gap-4">
          <?php foreach ($types as $key => $label): $files = $grouped[$key] ?? []; $has = !empty($files); ?>
            <div class="rounded-xl border p-4 <?= $has ? 'border-indigo-200 bg-white' : 'border-gray-200 bg-gray-50' ?>">
              <div class="flex items-center justify-between">
                <div class="font-medium text-sm"><?= $label ?></div>
                <?php if ($has): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Present</span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">Not provided</span>
                <?php endif; ?>
              </div>
              <?php if ($has): ?>
                <div class="mt-2 text-xs text-gray-500">Count: <?= count($files) ?> · Preview available in official report</div>
              <?php else: ?>
                <div class="mt-2 text-xs text-gray-500">No file listed</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3 text-xs text-gray-500">Raw files are not exposed here. Use the official report for controlled viewing.</div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">HR Response</h2>
        <?php $st = strtolower(trim((string)($hr_response['status'] ?? ''))); ?>
        <div class="text-sm text-gray-700">
          <div class="mb-2"><span class="text-gray-500">Status:</span>
            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= ($st==='verified'?'bg-green-100 text-green-700':($st==='declined'?'bg-red-100 text-red-700':'bg-gray-100 text-gray-700')) ?>">
              <?= $st ? ucfirst($st) : 'Pending' ?>
            </span>
          </div>
          <div class="mb-2"><span class="text-gray-500">HR Email:</span> <span class="font-medium"><?= htmlspecialchars($hr_email_masked ?? '') ?></span></div>
          <?php if (!empty($hr_response)): ?>
            <div class="text-xs text-gray-600 mt-2">Remarks:</div>
            <div class="mt-1 rounded border bg-gray-50 p-3 text-sm text-gray-800"><?= htmlspecialchars($hr_response['remarks'] ?? '—') ?></div>
          <?php else: ?>
            <div class="text-sm text-gray-600">No HR response captured.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
