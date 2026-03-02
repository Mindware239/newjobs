<div>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Heatmap Dashboard</h1>
    <form method="GET" class="flex gap-2">
      <input name="page" value="<?= htmlspecialchars($page ?? '') ?>" placeholder="/candidate/jobs/slug" class="border rounded px-3 py-2 w-80">
      <button class="px-3 py-2 bg-indigo-600 text-white rounded">Filter</button>
    </form>
  </div>
  <div class="bg-white rounded-lg shadow p-6 overflow-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="text-left text-gray-600">
          <th class="p-2">Page</th><th class="p-2">X</th><th class="p-2">Y</th><th class="p-2">Scroll</th><th class="p-2">Viewport</th><th class="p-2">Time</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $e): ?>
        <tr class="border-t">
          <td class="p-2"><?= htmlspecialchars($e['page_url']) ?></td>
          <td class="p-2"><?= (int)$e['click_x'] ?></td>
          <td class="p-2"><?= (int)$e['click_y'] ?></td>
          <td class="p-2"><?= (int)$e['scroll_depth'] ?></td>
          <td class="p-2"><?= (int)$e['viewport_width'] ?> × <?= (int)$e['viewport_height'] ?></td>
          <td class="p-2"><?= htmlspecialchars($e['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
