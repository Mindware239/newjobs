<div>
  <div class="mb-6">
    <a href="/master/verifications" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Employers KYC</a>
    <h1 class="text-3xl font-bold text-gray-900">Candidate Verifications</h1>
    <form method="GET" class="mt-4 flex items-center gap-3">
      <label class="text-sm text-gray-600">Status</label>
      <select name="status" class="px-3 py-2 border rounded">
        <?php $st = $filters['status'] ?? 'pending'; ?>
        <option value="pending" <?= $st==='pending'?'selected':'' ?>>Pending</option>
        <option value="assigned" <?= $st==='assigned'?'selected':'' ?>>Assigned</option>
        <option value="approved" <?= $st==='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= $st==='rejected'?'selected':'' ?>>Rejected</option>
      </select>
      <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
    </form>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach (($items ?? []) as $row): ?>
          <tr>
            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['full_name'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['email'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['document_type'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm">
              <?php $s = strtolower($row['status'] ?? 'pending'); $cls = $s==='approved'?'bg-green-100 text-green-800':($s==='rejected'?'bg-red-100 text-red-800':($s==='assigned'?'bg-blue-100 text-blue-800':'bg-yellow-100 text-yellow-800')); ?>
              <span class="px-2 py-1 rounded <?= $cls ?> text-xs font-semibold capitalize"><?= htmlspecialchars($s) ?></span>
            </td>
            <td class="px-6 py-4">
              <form method="POST" action="/master/verifications/candidates/assign" class="flex items-center gap-2">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="verification_id" value="<?= (int)($row['id'] ?? 0) ?>">
                <select name="executive_id" class="px-2 py-1 border rounded">
                  <?php $executives = $executives ?? []; foreach ($executives as $ex): ?>
                    <option value="<?= (int)$ex['id'] ?>" <?= ((int)($row['assigned_to'] ?? 0) === (int)$ex['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ex['email'] ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="px-3 py-1 bg-gray-800 text-white rounded">Assign</button>
              </form>
            </td>
            <td class="px-6 py-4">
              <a href="/master/verifications/candidates/<?= (int)($row['user_id'] ?? 0) ?>" class="text-blue-600 hover:text-blue-800">Open</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No verifications found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
