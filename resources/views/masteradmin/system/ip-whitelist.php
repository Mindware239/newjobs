<div>
  <h1 class="text-2xl font-semibold mb-4">IP Whitelist</h1>
  <div class="bg-white rounded shadow p-4 mb-4">
    <form method="POST" action="/master/system/ip-whitelist/save" class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div>
        <label class="text-sm text-gray-600">IP Address</label>
        <input type="text" name="ip_address" placeholder="127.0.0.1" class="w-full px-3 py-2 border rounded" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">Label</label>
        <input type="text" name="label" placeholder="Office" class="w-full px-3 py-2 border rounded">
      </div>
      <div class="flex items-end">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
      </div>
    </form>
  </div>
  <div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left text-sm">
          <th class="px-4 py-2">IP</th>
          <th class="px-4 py-2">Label</th>
          <th class="px-4 py-2">Active</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($items ?? []) as $w): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($w['ip_address'] ?? '') ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($w['label'] ?? '') ?></td>
            <td class="px-4 py-2"><?= (int)($w['active'] ?? 0) ? 'Yes' : 'No' ?></td>
            <td class="px-4 py-2">
              <form method="POST" action="/master/system/ip-whitelist/toggle" class="inline">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="ip_address" value="<?= htmlspecialchars($w['ip_address'] ?? '') ?>">
                <button class="px-3 py-1 bg-yellow-600 text-white rounded">Toggle</button>
              </form>
              <form method="POST" action="/master/system/ip-whitelist/delete" class="inline ml-2">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="ip_address" value="<?= htmlspecialchars($w['ip_address'] ?? '') ?>">
                <button class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($items ?? [])): ?>
          <tr>
            <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="text-lg font-medium">No IPs Whitelisted</p>
                    <p class="text-sm mt-1">Access is currently open to all IP addresses.</p>
                </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

