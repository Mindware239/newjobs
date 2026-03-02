<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Article Categories</h1>
        <a href="/admin/article-categories/create" class="px-4 py-2 bg-blue-600 text-white rounded">Add Category</a>
    </div>
    <div class="bg-white border rounded">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Slug</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr class="border-t">
                    <td class="px-3 py-2"><?= htmlspecialchars($c['name'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($c['slug'] ?? '') ?></td>
                    <td class="px-3 py-2">
                        <a class="text-blue-600" href="/admin/article-categories/<?= (int)$c['id'] ?>/edit">Edit</a>
                        <span class="mx-1">|</span>
                        <form method="post" action="/admin/article-categories/<?= (int)$c['id'] ?>/delete" class="inline">
                            <button class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
