<div>
  <h1 class="text-2xl font-semibold mb-4">Notifications</h1>
  <form method="post" action="/sales-executive/notifications/mark-read" class="bg-white rounded shadow">
    <div class="p-4">
      <div class="space-y-3">
        <?php foreach ($items as $n): ?>
          <label class="flex items-center gap-3 border rounded p-3">
            <input type="checkbox" name="ids[]" value="<?= (int)$n['id'] ?>">
            <div>
              <div class="text-sm font-medium"><?= htmlspecialchars($n['type'] ?? '') ?></div>
              <div class="text-xs text-gray-500">Lead: <?= htmlspecialchars($n['company_name'] ?? '-') ?> • <?= htmlspecialchars($n['message'] ?? '') ?></div>
            </div>
            <?php if ((int)($n['is_read'] ?? 0) === 0): ?>
              <span class="ml-auto px-2 py-1 rounded bg-purple-100 text-purple-700 text-xs">New</span>
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <div class="p-6 text-center text-gray-500">No notifications</div>
        <?php endif; ?>
      </div>
      <div class="mt-4">
        <button class="px-4 py-2 bg-purple-600 text-white rounded">Mark Selected as Read</button>
      </div>
    </div>
  </form>
</div>

