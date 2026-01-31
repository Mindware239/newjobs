<div>
  <div class="mb-6 flex items-center justify-between">
    <div>
      <a href="/master/verifications" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Back to Verifications</a>
      <h1 class="text-3xl font-bold text-gray-900">Verify: <?= htmlspecialchars($employer['company_name'] ?? 'Employer') ?></h1>
      <p class="text-sm text-gray-600">Email: <?= htmlspecialchars($employer['employer_email'] ?? '') ?></p>
    </div>
    <div class="space-x-2">
      <form method="POST" action="/master/verifications/approve" class="inline">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="employer_id" value="<?= (int)($employer['id'] ?? 0) ?>">
        <button class="px-4 py-2 bg-green-600 text-white rounded-md">Approve KYC</button>
      </form>
      <form method="POST" action="/master/verifications/reject" class="inline">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="employer_id" value="<?= (int)($employer['id'] ?? 0) ?>">
        <input type="text" name="reason" placeholder="Reason" class="px-2 py-1 border rounded">
        <button class="px-4 py-2 bg-red-600 text-white rounded-md">Reject</button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">KYC Documents</h2>
          <form method="POST" action="/master/verifications/set-level" class="flex items-center gap-2">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="employer_id" value="<?= (int)($employer['id'] ?? 0) ?>">
            <select name="level" class="px-2 py-1 border rounded">
              <?php $lvl = $employer['kyc_level'] ?? 'basic'; ?>
              <option value="basic" <?= $lvl==='basic'?'selected':'' ?>>Basic</option>
              <option value="full" <?= $lvl==='full'?'selected':'' ?>>Full</option>
            </select>
            <button class="px-3 py-1 bg-gray-800 text-white rounded">Set Level</button>
          </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <?php foreach (($documents ?? []) as $doc): ?>
            <div class="border rounded p-4">
              <div class="font-medium text-gray-900 mb-1">Type: <?= htmlspecialchars($doc['doc_type'] ?? '') ?></div>
              <div class="text-sm text-gray-600 mb-2">Status: <?= htmlspecialchars($doc['review_status'] ?? 'pending') ?></div>
              <a href="<?= htmlspecialchars($doc['file_url'] ?? '#') ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View Document</a>
              <?php if (!empty($doc['ocr'])): ?>
                <pre class="mt-2 text-xs bg-gray-50 p-2 rounded overflow-x-auto"><?= htmlspecialchars(json_encode($doc['ocr'])) ?></pre>
              <?php endif; ?>
              <div class="mt-3 space-y-2">
                <form method="POST" action="/master/verifications/doc/approve" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                  <input type="text" name="notes" placeholder="Notes (optional)" class="px-2 py-1 border rounded w-full">
                  <button class="px-3 py-1 bg-green-600 text-white rounded">Approve</button>
                </form>
                <form method="POST" action="/master/verifications/doc/reject" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                  <input type="text" name="notes" placeholder="Reason" class="px-2 py-1 border rounded w-full" required>
                  <button class="px-3 py-1 bg-red-600 text-white rounded">Reject</button>
                </form>
                <form method="POST" action="/master/verifications/doc/evidence" enctype="multipart/form-data" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                  <input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png" class="px-2 py-1 border rounded w-full">
                  <button class="px-3 py-1 bg-blue-600 text-white rounded">Upload Evidence</button>
                </form>
                <form method="POST" action="/master/verifications/doc/reverify" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                  <button class="px-3 py-1 bg-yellow-600 text-white rounded">Trigger Re-Verification</button>
                </form>
                <?php if (!empty($doc['review_notes'])): ?>
                  <div class="text-xs text-gray-700">Notes: <?= nl2br(htmlspecialchars($doc['review_notes'])) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($documents)): ?>
            <div class="text-gray-500">No documents uploaded yet.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Escalations</h2>
        <form method="POST" action="/master/verifications/escalate" class="flex items-center gap-2">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="employer_id" value="<?= (int)($employer['id'] ?? 0) ?>">
          <input type="text" name="reason" placeholder="Add escalation note" class="px-3 py-2 border rounded w-full">
          <button class="px-4 py-2 bg-yellow-600 text-white rounded">Escalate</button>
        </form>
        <?php if (!empty($employer['kyc_escalated'])): ?>
          <div class="mt-3 text-sm text-gray-700">Reason: <?= htmlspecialchars($employer['kyc_escalation_reason'] ?? '') ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Status</h2>
        <?php $s = strtolower($employer['kyc_status'] ?? 'pending'); $cls = $s==='approved'?'bg-green-100 text-green-800':($s==='rejected'?'bg-red-100 text-red-800':'bg-yellow-100 text-yellow-800'); ?>
        <div class="px-3 py-1 rounded inline-flex <?= $cls ?> font-semibold text-sm"><?= ucfirst($s) ?></div>
        <?php if ($s==='rejected' && !empty($employer['kyc_rejection_reason'])): ?>
          <div class="mt-3 text-sm text-gray-700">Reason: <?= htmlspecialchars($employer['kyc_rejection_reason']) ?></div>
        <?php endif; ?>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Employer Details</h2>
        <dl class="grid grid-cols-2 gap-3 text-sm">
          <div><dt class="text-gray-500">Company</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($employer['company_name'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">Website</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($employer['website'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">Industry</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($employer['industry'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">Size</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($employer['size'] ?? '') ?></dd></div>
          <div class="col-span-2"><dt class="text-gray-500">Address</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($employer['city'] ?? '') ?>, <?= htmlspecialchars($employer['state'] ?? '') ?>, <?= htmlspecialchars($employer['country'] ?? '') ?></dd></div>
        </dl>
      </div>
    </div>
  </div>
</div>
