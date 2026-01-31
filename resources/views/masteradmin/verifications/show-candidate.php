<div>
  <div class="mb-6 flex items-center justify-between">
    <div>
      <a href="/master/verifications/candidates" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Back to Candidate Verifications</a>
      <h1 class="text-3xl font-bold text-gray-900">Verify Candidate</h1>
      <p class="text-sm text-gray-600">Email: <?= htmlspecialchars($candidate['email'] ?? '') ?></p>
    </div>
    <div>
      <?php $verified = (int)($candidate['is_verified'] ?? 0) === 1; ?>
      <div class="px-3 py-1 rounded inline-flex <?= $verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?> font-semibold text-sm">
        <?= $verified ? 'Verified' : 'Verification Pending' ?>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">Documents</h2>
        </div>
        <div class="space-y-3">
          <?php foreach (($verifications ?? []) as $v): ?>
            <div class="border rounded p-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">Type: <?= htmlspecialchars($v['document_type'] ?? '') ?></div>
                  <div class="text-sm text-gray-600">Status: <?= htmlspecialchars($v['status'] ?? 'pending') ?></div>
                  <?php if (!empty($v['document_path'])): ?>
                    <a href="<?= htmlspecialchars($v['document_path']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View Document</a>
                  <?php endif; ?>
                  <?php if (!empty($v['remarks'])): ?>
                    <div class="mt-2 text-xs text-gray-700">Notes: <?= nl2br(htmlspecialchars($v['remarks'])) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="mt-3 space-y-2">
                <form method="POST" action="/master/verifications/candidates/approve" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="verification_id" value="<?= (int)($v['id'] ?? 0) ?>">
                  <button class="px-3 py-1 bg-green-600 text-white rounded">Approve</button>
                </form>
                <form method="POST" action="/master/verifications/candidates/reject" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="verification_id" value="<?= (int)($v['id'] ?? 0) ?>">
                  <input type="text" name="notes" placeholder="Reason" class="px-2 py-1 border rounded w-full" required>
                  <button class="px-3 py-1 bg-red-600 text-white rounded">Reject</button>
                </form>
                <form method="POST" action="/master/verifications/candidates/evidence" enctype="multipart/form-data" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="verification_id" value="<?= (int)($v['id'] ?? 0) ?>">
                  <input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png" class="px-2 py-1 border rounded w-full">
                  <button class="px-3 py-1 bg-blue-600 text-white rounded">Upload Evidence</button>
                </form>
                <form method="POST" action="/master/verifications/candidates/reverify" class="flex items-center gap-2">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="verification_id" value="<?= (int)($v['id'] ?? 0) ?>">
                  <button class="px-3 py-1 bg-yellow-600 text-white rounded">Trigger Re-Verification</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($verifications)): ?>
            <div class="text-gray-500">No verification records found for this candidate.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Candidate Details</h2>
        <dl class="grid grid-cols-2 gap-3 text-sm">
          <div><dt class="text-gray-500">Name</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($candidate['full_name'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">City</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($candidate['city'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">State</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($candidate['state'] ?? '') ?></dd></div>
          <div><dt class="text-gray-500">Country</dt><dd class="text-gray-900 mt-1"><?= htmlspecialchars($candidate['country'] ?? '') ?></dd></div>
          <div class="col-span-2"><dt class="text-gray-500">Resume</dt><dd class="text-gray-900 mt-1"><?php if (!empty($candidate['resume_url'])): ?><a href="<?= htmlspecialchars($candidate['resume_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View</a><?php else: ?>N/A<?php endif; ?></dd></div>
        </dl>
      </div>
    </div>
  </div>
</div>
