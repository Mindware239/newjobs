<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Finance & Payments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and track all transaction history</p>
        </div>
        <div class="mt-4 md:mt-0">
            <!-- Potential Export/Action buttons could go here -->
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Search</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="Search by Company, Transaction ID..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Status</label>
                <?php $s = $status ?? 'all'; ?>
                <select name="status" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors text-sm appearance-none">
                    <option value="all" <?= $s==='all'?'selected':'' ?>>All Status</option>
                    <option value="completed" <?= $s==='completed'?'selected':'' ?>>Completed</option>
                    <option value="pending" <?= $s==='pending'?'selected':'' ?>>Pending</option>
                    <option value="failed" <?= $s==='failed'?'selected':'' ?>>Failed</option>
                    <option value="refunded" <?= $s==='refunded'?'selected':'' ?>>Refunded</option>
                </select>
            </div>

            <div class="flex items-end">
                <button class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter Results
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company / User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">#<?= (int)$p['id'] ?></span>
                                <div class="text-xs text-gray-400 mt-0.5 font-mono"><?= htmlspecialchars($p['transaction_id'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs mr-3">
                                        <?= strtoupper(substr($p['company_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($p['company_name'] ?? 'Unknown Company') ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($p['employer_email'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">₹<?= number_format((float)($p['amount'] ?? 0), 2) ?></div>
                                <div class="text-xs text-gray-400 uppercase"><?= htmlspecialchars($p['currency'] ?? 'INR') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $st = strtolower($p['status'] ?? ''); 
                                    $badgeClass = match($st) {
                                        'completed', 'success' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-600/20',
                                        'pending' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-600/20',
                                        'failed' => 'bg-red-100 text-red-700 ring-1 ring-red-600/20',
                                        'refunded' => 'bg-purple-100 text-purple-700 ring-1 ring-purple-600/20',
                                        default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-600/20'
                                    };
                                    $icon = match($st) {
                                        'completed', 'success' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                                        'pending' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                                        'failed' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                                        'refunded' => '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>',
                                        default => ''
                                    };
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                    <?= $icon ?>
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M d, Y', strtotime($p['created_at'] ?? 'now')) ?>
                                <div class="text-xs text-gray-400"><?= date('h:i A', strtotime($p['created_at'] ?? 'now')) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/finance/payments/<?= (int)$p['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors text-xs uppercase tracking-wide">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-50 rounded-full p-4 mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-medium text-gray-900">No payments found</p>
                                    <p class="text-sm mt-1">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['totalPages'] > 1): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing page <span class="font-medium"><?= $pagination['page'] ?></span> of <span class="font-medium"><?= $pagination['totalPages'] ?></span>
                <span class="mx-1">•</span>
                Total <span class="font-medium"><?= $pagination['total'] ?></span> records
            </div>
            <div class="flex items-center gap-2">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="?page=<?= $pagination['page'] - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50 transition-colors">Previous</a>
                <?php else: ?>
                    <span class="px-3 py-1 bg-gray-100 border border-gray-200 rounded text-sm text-gray-400 cursor-not-allowed">Previous</span>
                <?php endif; ?>

                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                    <a href="?page=<?= $pagination['page'] + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                <?php else: ?>
                    <span class="px-3 py-1 bg-gray-100 border border-gray-200 rounded text-sm text-gray-400 cursor-not-allowed">Next</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>