<div>
  <h1 class="text-2xl font-semibold mb-4">Follow-ups</h1>
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left text-sm">
          <th class="px-4 py-2">Lead</th>
          <th class="px-4 py-2">Scheduled At</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($it['company_name'] ?? '') ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($it['follow_up_at'] ?? '') ?></td>
          <td class="px-4 py-2"><span class="px-2 py-1 rounded bg-gray-100 text-xs"><?= htmlspecialchars($it['status'] ?? 'pending') ?></span></td>
          <td class="px-4 py-2">
            <form action="/sales-manager/followups/<?= (int)$it['id'] ?>/update-status" method="post" class="inline-flex gap-2">
              <select name="status" class="border rounded px-2 py-1">
                <?php foreach (['pending','done','rescheduled'] as $s): ?>
                  <option value="<?= $s ?>" <?= (($it['status'] ?? '')===$s)?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <button class="px-3 py-1 bg-purple-600 text-white rounded">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No follow-ups found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
