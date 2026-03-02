<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1">Bulk Uploads</h1>
                    <p class="text-sm text-gray-600">Account: <?= htmlspecialchars($account->attributes['name'] ?? '') ?> (<?= htmlspecialchars($account->attributes['username'] ?? '') ?>)</p>
                </div>
                <a href="/admin/bulk-uploaders" class="text-sm text-blue-600">Back</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mt-4">

                <div class="rounded-md bg-gray-50 p-3 border border-gray-200">
                    <div class="text-xs text-gray-500">Total Uploaded</div>
                    <div class="text-xl font-semibold"><?= (int)($summary['total'] ?? 0) ?></div>
                </div>
                <div class="rounded-md bg-green-50 p-3 border border-green-100">
                    <div class="text-xs text-green-600">Processed</div>
                    <div class="text-xl font-semibold text-green-700"><?= (int)($summary['processed'] ?? 0) ?></div>
                </div>
                <div class="rounded-md bg-yellow-50 p-3 border border-yellow-100">
                    <div class="text-xs text-yellow-600">Pending</div>
                    <div class="text-xl font-semibold text-yellow-700"><?= (int)($summary['pending'] ?? 0) ?></div>
                </div>
                <div class="rounded-md bg-red-50 p-3 border border-red-100">
                    <div class="text-xs text-red-600">Failed</div>
                    <div class="text-xl font-semibold text-red-700"><?= (int)($summary['failed'] ?? 0) ?></div>
                </div>
                <div class="rounded-md bg-indigo-50 p-3 border border-indigo-100">
                    <div class="text-xs text-indigo-600">Remaining</div>
                    <div class="text-xl font-semibold text-indigo-700"><?= (int)($summary['remaining'] ?? 0) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4">
                <form method="post" action="/admin/bulk-uploaders/<?= (int)$account->id ?>/toggle">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-md text-white <?= (($account->attributes['status'] ?? '') === 'active') ? 'bg-red-600' : 'bg-green-600' ?>">
                        <?= (($account->attributes['status'] ?? '') === 'active') ? 'Suspend' : 'Activate' ?>
                    </button>
                </form>
                <form method="post" action="/admin/bulk-uploaders/<?= (int)$account->id ?>/reset">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700">Reset Used</button>
                </form>
                <form method="post" action="/admin/bulk-uploaders/<?= (int)$account->id ?>/credits" class="flex items-center gap-2">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="number" name="add" min="1" placeholder="Add CVs" class="px-3 py-1.5 border rounded-md w-28">
                    <button type="submit" class="px-3 py-1.5 rounded-md bg-green-600 text-white">Add Limit</button>
                </form>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <?php $tab = (string)($view ?? ''); ?>
                <a href="/admin/bulk-uploaders/<?= (int)$account->id ?>/batches"
                   class="px-3 py-1.5 rounded-md <?= $tab === 'batches' ? 'bg-gray-100 text-gray-700' : 'bg-blue-600 text-white' ?>">Overview</a>
                <a href="/admin/bulk-uploaders/<?= (int)$account->id ?>/batches?view=batches"
                   class="px-3 py-1.5 rounded-md <?= $tab === 'batches' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' ?>">Batches</a>
            </div>
        </div>
        <?php if (($view ?? '') === 'batches'): ?>
        <div class="bg-white rounded-xl shadow p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Batches</h2>
            <table class="min-w-full text-left">
                <thead>
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Total</th>
                        <th class="px-3 py-2">Processed</th>
                        <th class="px-3 py-2">Failed</th>
                        <th class="px-3 py-2">Created</th>
                        <th class="px-3 py-2">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($batches ?? []) as $b): ?>
                        <tr class="border-t">
                            <td class="px-3 py-2"><?= (int)$b['id'] ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($b['status'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= (int)($b['total_files'] ?? 0) ?></td>
                            <td class="px-3 py-2"><?= (int)($b['processed_files'] ?? 0) ?></td>
                            <td class="px-3 py-2"><?= (int)($b['failed_files'] ?? 0) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($b['completed_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($batches)): ?>
                        <tr><td colspan="7" class="px-3 py-4 text-gray-600">No batches yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php if (($view ?? '') !== 'batches'): ?>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Files (latest 200)</h2>
                <a href="/admin/bulk-uploaders/<?= (int)$account->id ?>/batches" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700">Refresh</a>
            </div>
            <table class="min-w-full text-left table-fixed">
                <thead>
                    <tr>
                        <th class="px-3 py-2 w-20">Batch</th>
                        <th class="px-3 py-2">File</th>
                        <th class="px-3 py-2 w-28">Status</th>
                        <th class="px-3 py-2">Failure</th>
                        <th class="px-3 py-2 w-32">Processed</th>
                        <th class="px-3 py-2 w-48">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($files ?? []) as $f): ?>
                        <tr class="border-t">
                            <td class="px-3 py-2"><?= (int)($f['batch_id'] ?? 0) ?></td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <a class="text-blue-700 hover:underline" href="/admin/resumes/<?= (int)($f['id'] ?? 0) ?>/download">
                                        <?= htmlspecialchars($f['filename'] ?? '') ?>
                                    </a>
                                    <a class="text-gray-700 hover:underline" href="/admin/resumes/<?= (int)($f['id'] ?? 0) ?>">Inspect</a>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <?php $s = (string)($f['status'] ?? ''); ?>
                                <span class="<?= $s === 'processed' ? 'text-green-700' : ($s === 'failed' ? 'text-red-700' : 'text-yellow-700') ?>">
                                    <?= htmlspecialchars($s) ?>
                                </span>
                            </td>
                            <td class="px-3 py-2"><?= htmlspecialchars($f['failure_reason'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($f['processed_at'] ?? '') ?></td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <?php $sid = (int)($f['id'] ?? 0); $s = (string)($f['status'] ?? ''); ?>
                                    <?php if ($s !== 'success'): ?>
                                        <form method="post" action="/admin/resumes/<?= $sid ?>/approve">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button class="px-3 py-1.5 rounded-md bg-green-600 text-white">Approve</button>
                                        </form>
                                        <form method="post" action="/admin/resumes/<?= $sid ?>/reject">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button class="px-3 py-1.5 rounded-md bg-red-600 text-white">Reject</button>
                                        </form>
                                        <form method="post" action="/admin/resumes/<?= $sid ?>/delete" onsubmit="return confirm('Delete this file permanently?');" title="Delete file">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button class="px-3 py-1.5 rounded-md bg-red-700 text-white inline-flex items-center gap-2">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M8 6V4h8v2m-1 3v9m-6-9v9M5 6l1 14a2 2 0 002 2h8a2 2 0 002-2l1-14"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="px-3 py-1.5 rounded-md bg-green-100 text-green-700">Published</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($files)): ?>
                        <tr><td colspan="6" class="px-3 py-4 text-gray-600">No files uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-xl shadow p-6 mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Published Candidates</h2>
                <div class="flex items-center gap-2">
                    <form method="get" action="/admin/bulk-uploaders/<?= (int)$account->id ?>/batches" class="flex items-center gap-2">
                        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search name/email/phone" class="px-3 py-1.5 border rounded-md w-64">
                        <button type="submit" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700">Search</button>
                    </form>
                    <a class="px-3 py-1.5 rounded-md bg-blue-600 text-white" href="/admin/bulk-uploaders/<?= (int)$account->id ?>/candidates/export<?= !empty($search) ? ('?q=' . urlencode($search)) : '' ?>">Export CSV</a>
                </div>
            </div>
            <table class="min-w-full text-left">
                <thead>
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Phone</th>
                        <th class="px-3 py-2">City</th>
                        <th class="px-3 py-2">Email Status</th>
                        <th class="px-3 py-2">Last Email</th>
                        <th class="px-3 py-2">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($candidates ?? []) as $c): ?>
                        <tr class="border-t">
                            <td class="px-3 py-2"><?= (int)$c['id'] ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['full_name'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['email'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['city'] ?? '') ?></td>
                            <td class="px-3 py-2">
                                <?php 
                                $status = $c['email_status'] ?? null;
                                $error = $c['email_error'] ?? null;
                                if ($status === 'sent'): ?>
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">Sent</span>
                                <?php elseif ($status === 'failed'): ?>
                                    <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs" title="<?= htmlspecialchars($error ?? '') ?>">Failed</span>
                                <?php elseif ($status === 'pending'): ?>
                                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 text-xs">Pending</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs">No Email</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['email_last_at'] ?? '') ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($c['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($candidates)): ?>
                        <tr><td colspan="8" class="px-3 py-2 text-gray-600">No published candidates yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
</div>
