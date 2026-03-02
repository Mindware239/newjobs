<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bulk Uploaders</h1>
            <p class="mt-1 text-sm text-gray-600">Manage accounts that can upload resumes in bulk.</p>
        </div>
        <a href="/admin/bulk-uploaders/create" class="inline-flex justify-center items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path>
            </svg>
            Create Account
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach (($items ?? []) as $acc): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($acc->attributes['name'] ?? '') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($acc->attributes['username'] ?? '') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($acc->attributes['type'] ?? '') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= (int)($acc->attributes['limit_total'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= (int)($acc->attributes['limit_used'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= max(0, (int)($acc->attributes['limit_total'] ?? 0) - (int)($acc->attributes['limit_used'] ?? 0)) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($acc->attributes['expires_at'] ?? '') ?></td>
                        <td class="px-6 py-4 text-sm"><?= htmlspecialchars($acc->attributes['status'] ?? '') ?></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="inline-flex flex-col items-end gap-2">
                                <div class="flex items-center gap-2">
                                    <form method="post" action="/admin/bulk-uploaders/<?= (int)$acc->id ?>/toggle">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="w-32 px-3 py-1.5 rounded-md text-white <?= (($acc->attributes['status'] ?? '') === 'active') ? 'bg-red-600' : 'bg-green-600' ?>">
                                            <?= (($acc->attributes['status'] ?? '') === 'active') ? 'Suspend' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <form method="post" action="/admin/bulk-uploaders/<?= (int)$acc->id ?>/reset">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="w-32 px-3 py-1.5 rounded-md bg-gray-100 text-gray-700">Reset Used</button>
                                    </form>
                                    <a href="/admin/bulk-uploaders/<?= (int)$acc->id ?>/batches" class="w-32 inline-block px-3 py-1.5 rounded-md bg-gray-800 text-white text-center">View Uploads</a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="post" action="/admin/bulk-uploaders/<?= (int)$acc->id ?>/password" class="flex items-center gap-2">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="password" name="password" placeholder="New password" class="px-3 py-1.5 border rounded-md w-40">
                                        <button type="submit" class="w-32 px-3 py-1.5 rounded-md bg-indigo-600 text-white">Reset Password</button>
                                    </form>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="post" action="/admin/bulk-uploaders/<?= (int)$acc->id ?>/credits" class="flex items-center gap-2">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="number" name="add" min="1" placeholder="Add CVs" class="px-3 py-1.5 border rounded-md w-40">
                                        <button type="submit" class="w-32 px-3 py-1.5 rounded-md bg-green-600 text-white">Add Limit</button>
                                    </form>
                                    <form method="post" action="/admin/bulk-uploaders/<?= (int)$acc->id ?>/delete" onsubmit="return confirm('Delete this bulk uploader account and all its uploads?');">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="w-32 px-3 py-1.5 rounded-md bg-red-700 text-white">Delete Account</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-6 text-sm text-gray-500">Bulk uploaders cannot access candidate profiles or employer data.</div>
 </div>
