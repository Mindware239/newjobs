<div class="w-full px-4 sm:px-6 lg:px-8 py-8 font-sans">

    <?php
        $accountId     = (int)$account->id;
        $accountName   = htmlspecialchars($account->attributes['name'] ?? '');
        $accountUser   = htmlspecialchars($account->attributes['username'] ?? '');
        $accountStatus = $account->attributes['status'] ?? 'active';
        $csrf          = htmlspecialchars($_SESSION['csrf_token'] ?? '');
        $tab           = (string)($view ?? '');
        $isActive      = $accountStatus === 'active';
    ?>

    <!-- ── Page Header ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-violet-600 flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0 shadow-md">
                <?= strtoupper(mb_substr($accountName, 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Bulk Uploads</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold <?= $isActive ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $isActive ? 'bg-green-500 animate-pulse' : 'bg-red-500' ?>"></span>
                        <?= ucfirst($accountStatus) ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span class="font-semibold text-gray-700"><?= $accountName ?></span>
                    <span class="mx-1.5 text-gray-300">·</span>
                    <span class="font-mono text-xs text-gray-400"><?= $accountUser ?></span>
                </p>
            </div>
        </div>
        <a href="/admin/bulk-uploaders" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-blue-600 border border-gray-200 hover:border-blue-300 bg-white px-3 py-2 rounded-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <!-- ── Stats Grid ── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Uploaded</div>
            <div class="text-2xl font-extrabold text-gray-900 font-mono"><?= (int)($summary['total'] ?? 0) ?></div>
            <div class="text-xs text-gray-400 mt-1">All CVs received</div>
        </div>
        <div class="bg-white border border-green-100 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Processed</div>
            <div class="text-2xl font-extrabold text-green-700 font-mono"><?= (int)($summary['processed'] ?? 0) ?></div>
            <div class="text-xs text-green-400 mt-1">Successfully parsed</div>
        </div>
        <div class="bg-white border border-yellow-100 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Pending</div>
            <div class="text-2xl font-extrabold text-yellow-700 font-mono"><?= (int)($summary['pending'] ?? 0) ?></div>
            <div class="text-xs text-yellow-400 mt-1">Awaiting processing</div>
        </div>
        <div class="bg-white border border-red-100 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Failed</div>
            <div class="text-2xl font-extrabold text-red-700 font-mono"><?= (int)($summary['failed'] ?? 0) ?></div>
            <div class="text-xs text-red-400 mt-1">Parse errors</div>
        </div>
        <div class="bg-white border border-indigo-100 rounded-xl p-4 shadow-sm col-span-2 sm:col-span-1">
            <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Remaining</div>
            <div class="text-2xl font-extrabold text-indigo-700 font-mono"><?= (int)($summary['remaining'] ?? 0) ?></div>
            <div class="text-xs text-indigo-400 mt-1">Upload quota left</div>
        </div>
    </div>

    <!-- ── Quick Actions Bar ── -->
    <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 shadow-sm mb-5 flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mr-1">Quick Actions</span>

        <!-- Toggle Status -->
        <form method="post" action="/admin/bulk-uploaders/<?= $accountId ?>/toggle">
            <input type="hidden" name="_token" value="<?= $csrf ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-bold transition-all <?= $isActive ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white' : 'bg-green-50 text-green-600 border border-green-200 hover:bg-green-600 hover:text-white' ?>">
                <?php if ($isActive): ?>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Suspend
                <?php else: ?>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Activate
                <?php endif; ?>
            </button>
        </form>

        <!-- Reset Used -->
        <form method="post" action="/admin/bulk-uploaders/<?= $accountId ?>/reset">
            <input type="hidden" name="_token" value="<?= $csrf ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-bold bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset Used
            </button>
        </form>

        <!-- Divider -->
        <div class="hidden sm:block w-px h-6 bg-gray-200"></div>

        <!-- Add Limit -->
        <form method="post" action="/admin/bulk-uploaders/<?= $accountId ?>/credits" class="flex items-center gap-2">
            <input type="hidden" name="_token" value="<?= $csrf ?>">
            <input type="number" name="add" min="1" placeholder="Add CVs"
                   class="w-28 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono transition-all">
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-bold bg-green-600 text-white hover:bg-green-700 shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/></svg>
                Add Limit
            </button>
        </form>
    </div>

    <!-- ── Tabs ── -->
    <div class="flex items-center gap-2 mb-5 border-b border-gray-200 pb-0">
        <?php $tab = (string)($view ?? ''); ?>
        <a href="/admin/bulk-uploaders/<?= $accountId ?>/batches"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold rounded-t-lg border-b-2 transition-colors <?= $tab !== 'batches' ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Overview
        </a>
        <a href="/admin/bulk-uploaders/<?= $accountId ?>/batches?view=batches"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold rounded-t-lg border-b-2 transition-colors <?= $tab === 'batches' ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Batches
        </a>
    </div>

    <!-- ═══════════════ BATCHES VIEW ═══════════════ -->
    <?php if ($tab === 'batches'): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Upload Batches</h2>
                <p class="text-xs text-gray-400 mt-0.5">Each batch represents one bulk upload session</p>
            </div>
            <span class="text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full">
                <?= count($batches ?? []) ?> batches
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Batch ID</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Processed</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Failed</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach (($batches ?? []) as $b): ?>
                    <?php $bs = (string)($b['status'] ?? ''); ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-sm font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">#<?= (int)$b['id'] ?></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                                <?= $bs === 'completed' ? 'bg-green-50 text-green-700 border border-green-100' :
                                   ($bs === 'failed'    ? 'bg-red-50 text-red-700 border border-red-100' :
                                   ($bs === 'processing'? 'bg-blue-50 text-blue-700 border border-blue-100' :
                                                         'bg-yellow-50 text-yellow-700 border border-yellow-100')) ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?= $bs === 'completed' ? 'bg-green-500' : ($bs === 'failed' ? 'bg-red-500' : ($bs === 'processing' ? 'bg-blue-500 animate-pulse' : 'bg-yellow-500')) ?>"></span>
                                <?= ucfirst($bs) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm font-mono font-semibold text-gray-700"><?= (int)($b['total_files'] ?? 0) ?></td>
                        <td class="px-5 py-3.5 text-sm font-mono font-semibold text-green-600"><?= (int)($b['processed_files'] ?? 0) ?></td>
                        <td class="px-5 py-3.5 text-sm font-mono font-semibold text-red-500"><?= (int)($b['failed_files'] ?? 0) ?></td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 font-mono"><?= htmlspecialchars($b['created_at'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 font-mono"><?= htmlspecialchars($b['completed_at'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($batches ?? [])): ?>
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="text-gray-300 text-3xl mb-2">📦</div>
                            <div class="text-sm font-semibold text-gray-400">No batches uploaded yet</div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════ OVERVIEW VIEW ═══════════════ -->
    <?php if ($tab !== 'batches'): ?>

    <!-- Files Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Files <span class="text-gray-400 font-semibold text-sm">(latest 200)</span></h2>
                <p class="text-xs text-gray-400 mt-0.5">Review and approve individual uploaded CVs</p>
            </div>
            <a href="/admin/bulk-uploaders/<?= $accountId ?>/batches"
               class="inline-flex items-center gap-1.5 text-sm font-bold text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-3 py-2 rounded-lg transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-20">Batch</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">File</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-28">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Failure Reason</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-36">Processed At</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-56">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach (($files ?? []) as $f): ?>
                    <?php
                        $fid = (int)($f['id'] ?? 0);
                        $fs  = (string)($f['status'] ?? '');
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">#<?= (int)($f['batch_id'] ?? 0) ?></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <a href="/admin/resumes/<?= $fid ?>/download"
                                       class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline truncate max-w-xs block">
                                        <?= htmlspecialchars($f['filename'] ?? '') ?>
                                    </a>
                                    <a href="/admin/resumes/<?= $fid ?>"
                                       class="text-xs text-gray-400 hover:text-gray-600 hover:underline">
                                        Inspect →
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                                <?= $fs === 'processed' || $fs === 'success' ? 'bg-green-50 text-green-700 border border-green-100' :
                                   ($fs === 'failed'  ? 'bg-red-50 text-red-700 border border-red-100' :
                                                        'bg-yellow-50 text-yellow-700 border border-yellow-100') ?>">
                                <span class="w-1.5 h-1.5 rounded-full
                                    <?= $fs === 'processed' || $fs === 'success' ? 'bg-green-500' :
                                       ($fs === 'failed' ? 'bg-red-500' : 'bg-yellow-500 animate-pulse') ?>">
                                </span>
                                <?= htmlspecialchars($fs) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <?php if (!empty($f['failure_reason'])): ?>
                            <span class="text-xs text-red-500 bg-red-50 border border-red-100 px-2 py-1 rounded font-medium" title="<?= htmlspecialchars($f['failure_reason']) ?>">
                                <?= htmlspecialchars(mb_strimwidth($f['failure_reason'], 0, 40, '…')) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono"><?= htmlspecialchars($f['processed_at'] ?? '—') ?></td>
                        <td class="px-5 py-3.5">
                            <?php if ($fs === 'success'): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 border border-green-100 text-green-700 text-xs font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Published
                                </span>
                            <?php else: ?>
                            <div class="flex items-center gap-1.5">
                                <form method="post" action="/admin/resumes/<?= $fid ?>/approve">
                                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-700 border border-green-200 hover:bg-green-600 hover:text-white hover:border-green-600 text-xs font-bold transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Approve
                                    </button>
                                </form>
                                <form method="post" action="/admin/resumes/<?= $fid ?>/reject">
                                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white hover:border-red-600 text-xs font-bold transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Reject
                                    </button>
                                </form>
                                <form method="post" action="/admin/resumes/<?= $fid ?>/delete" onsubmit="return confirm('Delete this file permanently?');">
                                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 border border-gray-200 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all" title="Delete file">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($files ?? [])): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="text-gray-300 text-3xl mb-2">📂</div>
                            <div class="text-sm font-semibold text-gray-400">No files uploaded yet</div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Published Candidates Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Published Candidates</h2>
                <p class="text-xs text-gray-400 mt-0.5">Candidates whose CVs have been approved and published to the platform</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="get" action="/admin/bulk-uploaders/<?= $accountId ?>/batches" class="flex items-center gap-2">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
                               placeholder="Search name / email / phone"
                               class="pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg w-56 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>
                    <button type="submit" class="px-3.5 py-2 text-sm font-bold bg-gray-100 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-200 transition-all">
                        Search
                    </button>
                </form>
                <a href="/admin/bulk-uploaders/<?= $accountId ?>/candidates/export<?= !empty($search) ? ('?q=' . urlencode($search)) : '' ?>"
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-sm transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">City</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Email Status</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Last Email</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach (($candidates ?? []) as $c): ?>
                    <?php
                        $emailStatus = $c['email_status'] ?? null;
                        $emailError  = $c['email_error'] ?? null;
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded"><?= (int)$c['id'] ?></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-violet-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    <?= strtoupper(mb_substr($c['full_name'] ?? '?', 0, 1)) ?>
                                </div>
                                <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($c['full_name'] ?? '') ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-600 font-mono text-xs"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-sm text-gray-600 font-mono text-xs"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-sm text-gray-600"><?= htmlspecialchars($c['city'] ?? '—') ?></td>
                        <td class="px-5 py-3.5">
                            <?php if ($emailStatus === 'sent'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Sent
                                </span>
                            <?php elseif ($emailStatus === 'failed'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100 cursor-help" title="<?= htmlspecialchars($emailError ?? '') ?>">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Failed
                                </span>
                            <?php elseif ($emailStatus === 'pending'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                    Pending
                                </span>
                            <?php else: ?>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-400 border border-gray-100">No Email</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono"><?= htmlspecialchars($c['email_last_at'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono"><?= htmlspecialchars($c['created_at'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($candidates ?? [])): ?>
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="text-gray-300 text-3xl mb-2">👤</div>
                            <div class="text-sm font-semibold text-gray-400">No published candidates yet</div>
                            <div class="text-xs text-gray-300 mt-1">Approve uploaded CVs to see candidates here</div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

    <!-- Footer note -->
    <div class="flex items-center gap-2 text-xs text-gray-400 bg-white border border-gray-100 rounded-lg px-4 py-3 shadow-sm">
        <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        This uploader cannot access employer accounts, candidate profiles, or any other section of the admin panel.
    </div>

</div>