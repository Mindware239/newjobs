<div class="p-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Edit Category</h1>
    <form method="post" action="/admin/article-categories/<?= (int)($category['id'] ?? 0) ?>/update" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" class="border px-3 py-2 rounded w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Slug</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($category['slug'] ?? '') ?>" class="border px-3 py-2 rounded w-full">
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            <a href="/admin/article-categories" class="px-4 py-2 border rounded">Back</a>
        </div>
    </form>
</div>
