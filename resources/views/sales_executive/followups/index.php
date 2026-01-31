<div>
  <h1 class="text-2xl font-semibold mb-4">My Follow-ups</h1>
  <div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left text-sm">
          <th class="px-4 py-2">Lead</th>
          <th class="px-4 py-2">Scheduled At</th>
          <th class="px-4 py-2">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($it['company_name'] ?? '') ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($it['follow_up_at'] ?? '') ?></td>
          <td class="px-4 py-2"><span class="px-2 py-1 rounded bg-gray-100 text-xs"><?= htmlspecialchars($it['status'] ?? 'pending') ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No follow-ups</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

