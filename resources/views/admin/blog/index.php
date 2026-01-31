<?php
/** @var array $blogs */
?>
<div class="p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Blogs</h1>
    <a href="/admin/blog/create" class="px-3 py-2 bg-green-600 text-white rounded">Create</a>
  </div>
  <div class="bg-white border rounded">
    <div class="flex items-center justify-end p-3">
      <form id="bulkOrderForm" method="post" action="/admin/blog/reorder-bulk">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" id="orderInput" name="order" value="">
        <button class="px-3 py-2 border rounded">Save Drag Order</button>
      </form>
    </div>
    <table class="w-full" id="blogTable">
      <thead>
        <tr class="bg-gray-50">
          <th class="p-3 text-left">Title</th>
          <th class="p-3">Status</th>
          <th class="p-3">Published</th>
          <th class="p-3">Featured</th>
          <th class="p-3">Order</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($blogs as $b): ?>
          <tr class="border-t" draggable="true" data-id="<?= (int)$b['id'] ?>">
            <td class="p-3"><?= htmlspecialchars($b['title'] ?? '') ?></td>
            <td class="p-3 text-center"><?= (int)($b['status_id'] ?? 0) ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($b['published_at'] ?? '') ?></td>
            <td class="p-3 text-center">
              <?php if ((int)($b['is_featured'] ?? 0) === 1): ?>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Featured</span>
              <?php else: ?>
                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded">Normal</span>
              <?php endif; ?>
            </td>
            <td class="p-3 text-center">
              <form method="post" action="/admin/blog/<?= (int)$b['id'] ?>/reorder" class="inline-flex items-center gap-2">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="number" name="sort_order" class="w-20 border rounded px-2 py-1" value="<?= (int)($b['sort_order'] ?? 0) ?>">
                <button class="px-2 py-1 border rounded">Save</button>
              </form>
            </td>
            <td class="p-3 text-center space-x-2">
              <a class="text-blue-600" href="/admin/blog/<?= (int)$b['id'] ?>/edit">Edit</a>
              <a class="text-gray-600" href="/admin/blog/<?= (int)$b['id'] ?>/preview" target="_blank">Preview</a>
              <form class="inline" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/delete">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="text-red-600" onclick="return confirm('Delete this blog?')">Delete</button>
              </form>
              <form class="inline" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/publish">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="text-green-600">Publish</button>
              </form>
              <form class="inline" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/draft">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="text-gray-700">Draft</button>
              </form>
              <form class="inline-flex items-center gap-1" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/schedule">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="datetime-local" name="published_at" class="border rounded px-2 py-1">
                <button class="text-blue-700">Schedule</button>
              </form>
              <?php if ((int)($b['is_featured'] ?? 0) === 1): ?>
                <form class="inline" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/unfeature">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <button class="text-yellow-800">Unfeature</button>
                </form>
              <?php else: ?>
                <form class="inline" method="post" action="/admin/blog/<?= (int)$b['id'] ?>/feature">
                  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <button class="text-yellow-700">Feature</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <script>
      (function(){
        const table = document.getElementById('blogTable');
        let dragged;
        table.addEventListener('dragstart', (e) => {
          dragged = e.target.closest('tr');
          e.dataTransfer.effectAllowed = 'move';
        });
        table.addEventListener('dragover', (e) => {
          e.preventDefault();
          const tr = e.target.closest('tr');
          if (!tr || tr === dragged) return;
          const rect = tr.getBoundingClientRect();
          const next = (e.clientY - rect.top) / rect.height > 0.5;
          tr.parentNode.insertBefore(dragged, next ? tr.nextSibling : tr);
        });
        table.addEventListener('drop', (e) => {
          e.preventDefault();
        });
        document.getElementById('bulkOrderForm').addEventListener('submit', (e) => {
          const ids = Array.from(table.querySelectorAll('tbody tr')).map(tr => tr.dataset.id);
          document.getElementById('orderInput').value = ids.join(',');
        });
      })();
    </script>
  </div>
</div>
