<div>
  <h1 class="text-2xl font-semibold mb-4">Support Tickets</h1>
  <div class="bg-white rounded shadow p-4 mb-4">
    <form method="GET" class="flex gap-3 items-end">
      <div>
        <label class="text-sm text-gray-600">Status</label>
        <select name="status" class="px-3 py-2 border rounded">
          <?php $s = $status ?? 'open'; ?>
          <option value="all" <?= $s==='all'?'selected':'' ?>>All</option>
          <option value="open" <?= $s==='open'?'selected':'' ?>>Open</option>
          <option value="assigned" <?= $s==='assigned'?'selected':'' ?>>Assigned</option>
          <option value="pending" <?= $s==='pending'?'selected':'' ?>>Pending</option>
          <option value="closed" <?= $s==='closed'?'selected':'' ?>>Closed</option>
          <option value="escalated" <?= $s==='escalated'?'selected':'' ?>>Escalated</option>
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
          <th class="px-4 py-2">Subject</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Priority</th>
          <th class="px-4 py-2">Updated</th>
          <th class="px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= (int)$t['id'] ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($t['subject'] ?? '') ?></td>
            <td class="px-4 py-2"><span class="px-2 py-1 text-xs rounded bg-gray-100"><?= htmlspecialchars($t['status'] ?? 'open') ?></span></td>
            <td class="px-4 py-2"><?= htmlspecialchars($t['priority'] ?? 'medium') ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($t['updated_at'] ?? '') ?></td>
            <td class="px-4 py-2"><a href="/support-exec/tickets/<?= (int)$t['id'] ?>" class="text-blue-600">View</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No tickets found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

