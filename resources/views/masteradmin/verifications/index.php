<div>
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Verification Management</h1>
    <p class="mt-2 text-sm text-gray-600">Assign, track and approve employer KYC</p>
    <div class="mt-2">
      <a href="/master/verifications/candidates" class="text-blue-600 hover:text-blue-800">Go to Candidate Verifications →</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-sm text-gray-500">Pending</div>
      <div class="text-2xl font-semibold text-yellow-600 mt-1"><?= (int)($stats['pending'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-sm text-gray-500">Approved</div>
      <div class="text-2xl font-semibold text-green-600 mt-1"><?= (int)($stats['approved'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-sm text-gray-500">Rejected</div>
      <div class="text-2xl font-semibold text-red-600 mt-1"><?= (int)($stats['rejected'] ?? 0) ?></div>
    </div>
    <a href="/master/verifications/queue" class="bg-white rounded-lg shadow p-4 hover:bg-gray-50">
      <div class="text-sm text-gray-500">My Queue</div>
      <div class="text-sm font-medium text-blue-600 mt-1">View assigned verifications →</div>
    </a>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search company or email" class="px-4 py-2 border border-gray-300 rounded-md">
      <select name="status" class="px-4 py-2 border border-gray-300 rounded-md">
        <option value="pending" <?= ($filters['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Filter</button>
    </form>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Company</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Level</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Assigned To</th>
          <th class="px-6 py-3"></th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach (($employers ?? []) as $e): ?>
        <tr>
          <td class="px-6 py-4">
            <div class="font-medium text-gray-900"><?= htmlspecialchars($e['company_name'] ?? '') ?></div>
            <div class="text-xs text-gray-500">#<?= (int)($e['id'] ?? 0) ?></div>
          </td>
          <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($e['employer_email'] ?? '') ?></td>
          <td class="px-6 py-4">
            <?php $s = strtolower($e['kyc_status'] ?? 'pending'); $cls = $s==='approved'?'bg-green-100 text-green-800':($s==='rejected'?'bg-red-100 text-red-800':'bg-yellow-100 text-yellow-800'); ?>
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $cls ?>"><?= ucfirst($s) ?></span>
          </td>
          <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($e['kyc_level'] ?? 'basic') ?></td>
          <td class="px-6 py-4">
            <form method="POST" action="/master/verifications/assign" class="flex items-center gap-2">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="employer_id" value="<?= (int)$e['id'] ?>">
              <select name="executive_id" class="px-2 py-1 border rounded">
                <option value="">Unassigned</option>
                <?php foreach (($executives ?? []) as $ex): ?>
                  <option value="<?= (int)$ex['id'] ?>" <?= ((int)($e['kyc_assigned_to'] ?? 0) === (int)$ex['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ex['email'] ?? '') ?> (<?= htmlspecialchars($ex['role'] ?? '') ?>)</option>
                <?php endforeach; ?>
              </select>
              <button class="px-3 py-1 bg-gray-800 text-white rounded">Assign</button>
            </form>
          </td>
          <td class="px-6 py-4 text-right">
            <a href="/master/verifications/<?= (int)$e['id'] ?>" class="text-blue-600 hover:text-blue-800">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($employers)): ?>
        <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">No records found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
