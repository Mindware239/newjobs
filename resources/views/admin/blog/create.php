<?php
/** @var array $categories */
/** @var array $tags */
?>
<div class="p-6">
  <h1 class="text-xl font-semibold mb-4">Create Blog</h1>
  <?php if (!empty($error)): ?>
    <div class="p-3 bg-red-100 text-red-700 rounded mb-4"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/admin/blog/store" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div>
      <label class="block text-sm font-medium">Title</label>
      <input name="title" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm font-medium">Excerpt</label>
      <textarea name="excerpt" class="w-full border rounded px-3 py-2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium">Content</label>
      <textarea id="editor" name="content" class="w-full border rounded px-3 py-2" rows="10"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium">Featured Image</label>
      <input type="file" name="featured_image" accept="image/*">
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Status ID</label>
        <input type="number" name="status_id" class="w-full border rounded px-3 py-2" value="0">
      </div>
      <div>
        <label class="block text-sm font-medium">Published At</label>
        <input type="datetime-local" name="published_at" class="w-full border rounded px-3 py-2">
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Meta Title</label>
        <input name="meta_title" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Meta Description</label>
        <input name="meta_description" class="w-full border rounded px-3 py-2">
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium">Meta Keywords</label>
      <textarea name="meta_keywords" class="w-full border rounded px-3 py-2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium">Canonical URL</label>
      <input name="canonical_url" class="w-full border rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm font-medium">Categories</label>
      <div class="grid grid-cols-2 gap-2">
        <?php foreach ($categories as $c): ?>
          <label class="flex items-center gap-2">
            <input type="checkbox" name="category_ids[]" value="<?= (int)$c['id'] ?>">
            <span><?= htmlspecialchars($c['name']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium">Tags</label>
      <div class="flex flex-wrap gap-3">
        <?php foreach ($tags as $t): ?>
          <label class="flex items-center gap-2">
            <input type="checkbox" name="tag_ids[]" value="<?= (int)$t['id'] ?>">
            <span>#<?= htmlspecialchars($t['name']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
      <a class="px-4 py-2 bg-gray-200 rounded" href="/admin/blog">Cancel</a>
    </div>
  </form>
  <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
  <script>
    if (window.CKEDITOR) {
      CKEDITOR.replace('editor', { removePlugins: 'image' });
    }
  </script>
</div>
