<?php
/** @var array $tags */
?>
<div class="p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Blog Tags</h1>
    <a href="/admin/blog-tags/create" class="px-3 py-2 bg-green-600 text-white rounded">Create</a>
  </div>
  <div class="bg-white border rounded">
    <table class="w-full">
      <thead>
        <tr class="bg-gray-50">
          <th class="p-3 text-left">Name</th>
          <th class="p-3 text-left">Slug</th>
          <th class="p-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tags as $t): ?>
          <tr class="border-t">
            <td class="p-3">#<?= htmlspecialchars($t['name'] ?? '') ?></td>
            <td class="p-3"><?= htmlspecialchars($t['slug'] ?? '') ?></td>
            <td class="p-3 text-center space-x-2">
              <a class="text-blue-600" href="/admin/blog-tags/<?= (int)$t['id'] ?>/edit">Edit</a>
              <form class="inline" method="post" action="/admin/blog-tags/<?= (int)$t['id'] ?>/delete">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="text-red-600" onclick="return confirm('Delete this tag?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
