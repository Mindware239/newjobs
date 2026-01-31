<div class="p-6">
  <h1 class="text-xl font-semibold mb-4">Create Category</h1>
  <?php if (!empty($error)): ?>
    <div class="p-3 bg-red-100 text-red-700 rounded mb-4"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/admin/blog-categories/store" class="space-y-4">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div>
      <label class="block text-sm font-medium">Name</label>
      <input name="name" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm font-medium">Description</label>
      <textarea name="description" class="w-full border rounded px-3 py-2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium">Active</label>
      <select name="is_active" class="border rounded px-3 py-2">
        <option value="1">Yes</option>
        <option value="0">No</option>
      </select>
    </div>
    <div class="flex items-center gap-3">
      <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
      <a class="px-4 py-2 bg-gray-200 rounded" href="/admin/blog-categories">Cancel</a>
    </div>
  </form>
</div>
