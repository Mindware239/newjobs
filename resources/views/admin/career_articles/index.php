<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Career Articles</h1>
        <a href="/admin/career-articles/create" class="px-4 py-2 bg-blue-600 text-white rounded">Add Article</a>
    </div>
    <form method="get" class="mb-4">
        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search..." class="border px-3 py-2 rounded w-64">
        <button class="px-3 py-2 border rounded">Search</button>
    </form>
    <div class="bg-white border rounded">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left">Title</th>
                    <th class="px-3 py-2 text-left">Category</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Author</th>
                    <th class="px-3 py-2 text-left">Published</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($articles as $a): ?>
                <tr class="border-t">
                    <td class="px-3 py-2"><?= htmlspecialchars($a['title'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($a['category_name'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($a['status'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($a['author'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($a['published_at'] ?? '') ?></td>
                    <td class="px-3 py-2">
                        <a class="text-blue-600" href="/admin/career-articles/<?= (int)$a['id'] ?>/preview">Preview</a>
                        <span class="mx-1">|</span>
                        <a class="text-blue-600" href="/admin/career-articles/<?= (int)$a['id'] ?>/edit">Edit</a>
                        <span class="mx-1">|</span>
                        <form method="post" action="/admin/career-articles/<?= (int)$a['id'] ?>/delete" class="inline">
                            <button class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
