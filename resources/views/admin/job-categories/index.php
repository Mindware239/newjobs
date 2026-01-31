<div>
    <!-- <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Job Categories</h1>
            <p class="mt-2 text-sm text-gray-600">Manage job categories and industries</p>
        </div>
        <a href="/admin/job-categories/create" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            + Add New Category
        </a>
    </div> -->
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Job Categories</h1>
        <p class="mt-2 text-sm text-gray-600">Manage job categories and industries</p>
    </div>

    <div class="flex items-center gap-3">
        <!-- Search -->
        <form method="GET" class="flex items-center gap-2">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                placeholder="Search category..."
                class="w-64 px-3 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:outline-none text-sm"
            >
            <button
                type="submit"
                class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm hover:bg-gray-200">
                Search
            </button>

            <?php if (!empty($_GET['search'])): ?>
                <a href="/admin/job-categories"
                   class="px-3 py-2 text-sm text-gray-600 hover:underline">
                    Reset
                </a>
            <?php endif; ?>
        </form>

        <!-- Add Button -->
        <a href="/admin/job-categories/create"
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            + Add New Category
        </a>
    </div>
</div>


    <?php if (isset($_GET['success'])): ?>
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        <?= htmlspecialchars($_GET['success']) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sort Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No categories found. <a href="/admin/job-categories/create" class="text-blue-600 hover:underline">Create one</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $category['id'] ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($category['name']) ?>
                        </div>
                        <?php if (!empty($category['description'])): ?>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= htmlspecialchars(substr($category['description'], 0, 60)) ?><?= strlen($category['description']) > 60 ? '...' : '' ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?= htmlspecialchars($category['slug']) ?></code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            <?= $category['job_count'] ?? 0 ?> jobs
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $category['sort_order'] ?? 0 ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= ($category['is_active'] ?? 1) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ($category['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/admin/job-categories/<?= $category['id'] ?>/edit" class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                        <button onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>')" 
                                class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
            to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_items']) ?> 
            of <?= $pagination['total_items'] ?> categories
        </div>
        <div class="flex space-x-2">
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
            
            if ($startPage > 1): ?>
                <a href="?page=1" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="px-4 py-2 text-gray-500">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($_GET['search'] ?? '') ?>" 
                   class="px-4 py-2 border rounded-md <?= $i === $pagination['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($endPage < $pagination['total_pages']): ?>
                <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                    <span class="px-4 py-2 text-gray-500">...</span>
                <?php endif; ?>
                <a href="?page=<?= $pagination['total_pages'] ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    <?= $pagination['total_pages'] ?>
                </a>
            <?php endif; ?>
            
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteCategory(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        return;
    }
    
    fetch(`/admin/job-categories/${id}/delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category deleted successfully');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the category');
    });
}
</script>

