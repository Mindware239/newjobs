<?php /** @var array $tag */ ?>
<div class="p-6">
  <h1 class="text-xl font-semibold mb-4">Edit Tag</h1>
  <?php if (!empty($error)): ?>
    <div class="p-3 bg-red-100 text-red-700 rounded mb-4"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/admin/blog-tags/<?= (int)($tag['id'] ?? 0) ?>/update" class="space-y-4">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div>
      <label class="block text-sm font-medium">Name</label>
      <input name="name" class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($tag['name'] ?? '') ?>" required>
    </div>
    <div class="flex items-center gap-3">
      <button class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
      <a class="px-4 py-2 bg-gray-200 rounded" href="/admin/blog-tags">Cancel</a>
    </div>
  </form>
</div>
