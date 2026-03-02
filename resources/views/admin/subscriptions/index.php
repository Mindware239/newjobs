<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Subscriptions</h1>
        <p class="mt-2 text-sm text-gray-600">View and manage all employer subscriptions</p>
    </div>

    <!-- Filters -->
    <form method="GET" action="/admin/subscriptions" class="mb-4">
        <div class="bg-white rounded-lg shadow p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Company or Email">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <?php $s = $filters['status'] ?? 'all'; ?>
                    <option value="all" <?= $s === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="active" <?= $s === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $s === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="expired" <?= $s === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="cancelled" <?= $s === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                <?php $pp = (int)($pagination['perPage'] ?? 20); ?>
                <select name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="10" <?= $pp === 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $pp === 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $pp === 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $pp === 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Apply</button>
            </div>
        </div>
    </form>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <a href="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>" class="text-blue-600 hover:text-blue-800">
                            <?= htmlspecialchars($subscription['company_name'] ?? 'N/A') ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($subscription['plan_name'] ?? 'N/A') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= ($subscription['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst($subscription['status'] ?? 'unknown') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= !empty($subscription['started_at']) ? date('M d, Y', strtotime($subscription['started_at'])) : '—' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= !empty($subscription['expires_at']) ? date('M d, Y', strtotime($subscription['expires_at'])) : 'N/A' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php 
        $page = (int)($pagination['page'] ?? 1);
        $perPage = (int)($pagination['perPage'] ?? 20);
        $total = (int)($pagination['total'] ?? 0);
        $totalPages = (int)($pagination['totalPages'] ?? 1);
        $search = htmlspecialchars((string)($filters['search'] ?? ''), ENT_QUOTES);
        $status = htmlspecialchars((string)($filters['status'] ?? 'all'), ENT_QUOTES);
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= (($page - 1) * $perPage) + 1 ?> to <?= min($page * $perPage, $total) ?> of <?= $total ?> results
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="/admin/subscriptions?page=<?= $page - 1 ?>&search=<?= $search ?>&status=<?= $status ?>&per_page=<?= $perPage ?>" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="/admin/subscriptions?page=<?= $i ?>&search=<?= $search ?>&status=<?= $status ?>&per_page=<?= $perPage ?>" class="px-4 py-2 border border-gray-300 rounded-md <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="/admin/subscriptions?page=<?= $page + 1 ?>&search=<?= $search ?>&status=<?= $status ?>&per_page=<?= $perPage ?>" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="mt-4 text-sm text-gray-600">
        <?= $total ?> total result<?= $total === 1 ? '' : 's' ?>
    </div>
    <?php endif; ?>
</div>
