<div class="max-w-5xl mx-auto">
  <div class="mb-8">
    <a href="/candidate/verification" class="text-sm text-blue-600">&larr; Back</a>
  </div>
  <div class="bg-white shadow rounded-lg p-6 mb-6">
    <h2 class="text-lg font-semibold mb-2">Employment</h2>
    <div class="text-gray-900 font-medium"><?= htmlspecialchars($record['company_name'] ?? '') ?></div>
    <div class="text-sm text-gray-600"><?= htmlspecialchars($record['designation'] ?? '') ?> · <?= htmlspecialchars($record['start_date'] ?? '') ?> to <?= htmlspecialchars($record['end_date'] ?? 'Present') ?></div>
    <div class="mt-2">
      <?php $status = $record['status_overall'] ?? 'under_review'; ?>
      <?php if ($status === 'verified'): ?>
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Verified</span>
      <?php elseif ($status === 'not_verified'): ?>
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Not Verified</span>
      <?php else: ?>
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Under Review</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-semibold mb-4">Upload Documents</h3>
      <form action="/candidate/verification/<?= (int)($record['id'] ?? 0) ?>/documents" method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
          <label class="block text-sm font-medium text-gray-700">Document Type</label>
          <select name="doc_type" class="mt-1 block w-full rounded-md border-gray-300" required>
            <option value="offer_letter">Offer Letter</option>
            <option value="relieving_letter">Relieving Letter</option>
            <option value="experience_letter">Experience Letter</option>
            <option value="salary_slip">Salary Slip</option>
            <option value="bank_statement">Bank Statement</option>
            <option value="form16">Form 16</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Upload PDF/DOC/JPG (max 5MB)</label>
          <input type="file" name="file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg" class="mt-1 block w-full rounded-md border-gray-300" required />
        </div>
        <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Upload</button>
      </form>

      <div class="mt-6">
        <h4 class="text-sm font-semibold mb-2">Uploaded Documents</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <?php foreach (($documents ?? []) as $doc): ?>
            <?php 
              $meta = [];
              if (!empty($doc['metadata'])) {
                $decoded = json_decode($doc['metadata'], true);
                if (is_array($decoded)) { $meta = $decoded; }
              }
              $name = $meta['original_name'] ?? basename((string)($doc['file_path'] ?? ''));
            ?>
            <li class="flex items-center justify-between">
              <span><?= htmlspecialchars(($doc['doc_type'] ?? '') . ' — ' . $name) ?></span>
              <a href="<?= htmlspecialchars($doc['file_path'] ?? '') ?>" target="_blank" class="text-blue-600 hover:text-blue-800">Preview</a>
            </li>
          <?php endforeach; ?>
          <?php if (empty($documents ?? [])): ?>
            <li class="text-gray-500">No documents uploaded yet.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-semibold mb-4">HR Verification</h3>
      <form action="/candidate/verification/<?= (int)($record['id'] ?? 0) ?>/hr" method="post" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">HR Official Email</label>
          <input type="email" name="hr_email" class="mt-1 block w-full rounded-md border-gray-300" placeholder="hr@companydomain.com" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">HR Phone</label>
          <input type="text" name="hr_phone" class="mt-1 block w-full rounded-md border-gray-300" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Reporting Manager Email (optional)</label>
          <input type="email" name="manager_email" class="mt-1 block w-full rounded-md border-gray-300" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Company Website</label>
          <input type="text" name="company_website" class="mt-1 block w-full rounded-md border-gray-300" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">CIN (optional)</label>
            <input type="text" name="cin" class="mt-1 block w-full rounded-md border-gray-300" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">GST (optional)</label>
            <input type="text" name="gst" class="mt-1 block w-full rounded-md border-gray-300" />
          </div>
        </div>
        <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Send Verification Email</button>
      </form>
      <div class="mt-4 text-sm text-gray-600">
        <?php if (!empty($request)): ?>
          <div>Request sent to <?= htmlspecialchars($request['hr_email'] ?? '') ?>, expires <?= htmlspecialchars($request['expires_at'] ?? '') ?></div>
        <?php else: ?>
          <div>No HR request sent yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
