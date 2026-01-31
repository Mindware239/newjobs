<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Jobs</h1>
        <p class="mt-2 text-sm text-gray-600">View and moderate all job postings</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                   placeholder="Search jobs" 
                   class="px-4 py-2 border border-gray-300 rounded-md">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
            </select>
            <select name="sort" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="created_at" <?= ($filters['sort'] ?? 'created_at') === 'created_at' ? 'selected' : '' ?>>Newest First</option>
                <option value="views" <?= ($filters['sort'] ?? '') === 'views' ? 'selected' : '' ?>>Most Views</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applications</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($job['title'] ?? 'N/A') ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($job['company_name'] ?? 'N/A') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= number_format($job['applications_count'] ?? 0) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= ($job['status'] ?? '') === 'published' ? 'bg-green-100 text-green-800' : 
                                (($job['status'] ?? '') === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst($job['status'] ?? 'unknown') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/admin/jobs/<?= urlencode($job['slug'] ?? $job['id']) ?>" class="text-blue-600 hover:text-blue-900">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?> to <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> of <?= $pagination['total'] ?> results
        </div>
        <div class="flex space-x-2">
            <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

