<div>
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">My Verification Queue</h1>
    <p class="mt-2 text-sm text-gray-600">Employers assigned to you</p>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Company</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Assigned</th>
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
          <td class="px-6 py-4 text-gray-500">Pending</td>
          <td class="px-6 py-4 text-right">
            <a href="/master/verifications/<?= (int)$e['id'] ?>" class="text-blue-600 hover:text-blue-800">Verify</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($employers)): ?>
        <tr><td colspan="4" class="px-6 py-6 text-center text-gray-500">No assigned verifications</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

