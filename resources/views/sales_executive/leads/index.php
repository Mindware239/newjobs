<div>
  <h1 class="text-2xl font-semibold mb-4">My Leads</h1>
  <div class="bg-white rounded shadow p-4 mb-4">
    <form method="GET" class="flex gap-3 items-end">
      <div>
        <label class="text-sm text-gray-600">Stage</label>
        <?php $st = $stage ?? 'all'; ?>
        <select name="stage" class="px-3 py-2 border rounded">
          <?php foreach (['all','new','contacted','demo_done','follow_up','payment_pending','converted','lost'] as $s): ?>
            <option value="<?= $s ?>" <?= $st===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="px-4 py-2 bg-blue-600 text-white rounded">Filter</button>
    </form>
  </div>
  <div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left text-sm">
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Company</th>
          <th class="px-4 py-2">Contact</th>
          <th class="px-4 py-2">Stage</th>
          <th class="px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leads as $l): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= (int)$l['id'] ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($l['company_name'] ?? '') ?></td>
          <td class="px-4 py-2">
            <div class="text-sm text-gray-700"><?= htmlspecialchars($l['contact_name'] ?? '') ?></div>
            <div class="text-xs text-gray-500"><?= htmlspecialchars($l['contact_email'] ?? '') ?></div>
            <div class="text-xs text-gray-500"><?= htmlspecialchars($l['contact_phone'] ?? '') ?></div>
          </td>
          <td class="px-4 py-2"><span class="px-2 py-1 text-xs rounded bg-gray-100"><?= htmlspecialchars($l['stage'] ?? 'new') ?></span></td>
          <td class="px-4 py-2">
            <form method="POST" action="/sales-executive/leads/update" class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <select name="stage" class="px-2 py-1 border rounded">
                <?php foreach (['contacted','demo_done','follow_up','payment_pending','converted','lost'] as $s): ?>
                  <option value="<?= $s ?>" <?= ($l['stage'] === $s)?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="notes" class="px-2 py-1 border rounded" placeholder="Notes" value="<?= htmlspecialchars($l['notes'] ?? '') ?>">
              <button class="px-3 py-1 bg-green-600 text-white rounded">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($leads)): ?>
          <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No leads found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
