<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">My Uploads</h1>
            <p class="text-sm text-gray-600">Total: <?= (int)($stats['total'] ?? 0) ?> • Processed: <?= (int)($stats['processed'] ?? 0) ?> • Pending: <?= (int)($stats['pending'] ?? 0) ?> • Failed: <?= (int)($stats['failed'] ?? 0) ?></p>
        </div>
        <a href="/bulk/upload" class="inline-block px-3 py-2 rounded-md bg-green-600 text-white">Upload</a>
    </div>
    <div class="mb-4">
        <p class="text-sm text-gray-700">Remaining uploads: <?= (int)($remaining ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2">File</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Failure</th>
                    <th class="px-3 py-2">Processed At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($files ?? []) as $f): ?>
                    <tr class="border-t">
                        <td class="px-3 py-2">
                            <a class="text-blue-700 hover:underline" href="/bulk/files/<?= (int)($f['id'] ?? 0) ?>/download">
                                <?= htmlspecialchars($f['filename'] ?? '') ?>
                            </a>
                        </td>
                        <td class="px-3 py-2">
                            <?php $s = (string)($f['status'] ?? ''); ?>
                            <span class="<?= $s === 'processed' ? 'text-green-700' : ($s === 'failed' ? 'text-red-700' : 'text-yellow-700') ?>">
                                <?= htmlspecialchars($s) ?>
                            </span>
                        </td>
                        <td class="px-3 py-2"><?= htmlspecialchars($f['failure_reason'] ?? '') ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($f['processed_at'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($files)): ?>
            <div class="p-6 text-gray-600">No uploads yet.</div>
        <?php endif; ?>
    </div>
</div>
