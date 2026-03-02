<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Status</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto py-12 px-4">
        <div class="bg-white rounded-xl shadow p-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Batch #<?= (int)$batch->id ?></h1>
                <a href="/bulk/batches/<?= (int)$batch->id ?>" class="text-sm text-blue-600">Refresh</a>
            </div>
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">Total</div>
                    <div class="text-xl font-semibold"><?= (int)($stats['total'] ?? 0) ?></div>
                </div>
                <div class="rounded-lg bg-green-50 p-4">
                    <div class="text-xs text-green-600">Processed</div>
                    <div class="text-xl font-semibold text-green-700"><?= (int)($stats['processed'] ?? 0) ?></div>
                </div>
                <div class="rounded-lg bg-yellow-50 p-4">
                    <div class="text-xs text-yellow-600">Pending</div>
                    <div class="text-xl font-semibold text-yellow-700"><?= (int)($stats['pending'] ?? 0) ?></div>
                </div>
                <div class="rounded-lg bg-red-50 p-4">
                    <div class="text-xs text-red-600">Failed</div>
                    <div class="text-xl font-semibold text-red-700"><?= (int)($stats['failed'] ?? 0) ?></div>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-600">Remaining uploads: <?= (int)($remaining ?? 0) ?></p>
                <?php if ((int)($remaining ?? 0) > 0): ?>
                    <a href="/bulk/upload" class="inline-block px-3 py-2 rounded-md bg-green-600 text-white">Upload Remaining</a>
                <?php else: ?>
                    <span class="text-sm text-red-600">Limit exhausted</span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-gray-600 mb-4">Status: <?= htmlspecialchars($batch->attributes['status'] ?? '') ?></p>
            <?php if (!empty($files)): ?>
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="py-2 px-2">File</th>
                        <th class="py-2 px-2">Status</th>
                        <th class="py-2 px-2">Failure</th>
                        <th class="py-2 px-2">Processed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $f): ?>
                        <tr class="border-t">
                            <td class="py-2 px-2"><?= htmlspecialchars($f->attributes['filename'] ?? '') ?></td>
                            <td class="py-2 px-2">
                                <?php $s = (string)($f->attributes['status'] ?? ''); ?>
                                <span class="<?= $s === 'processed' ? 'text-green-700' : ($s === 'failed' ? 'text-red-700' : 'text-yellow-700') ?>">
                                    <?= htmlspecialchars($s) ?>
                                </span>
                            </td>
                            <td class="py-2 px-2"><?= htmlspecialchars($f->attributes['failure_reason'] ?? '') ?></td>
                            <td class="py-2 px-2"><?= htmlspecialchars($f->attributes['processed_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-sm text-gray-500">No files in this batch.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
