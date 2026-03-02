<div class="max-w-5xl mx-auto px-4 py-8">
    <a href="/admin/bulk-uploaders/<?= (int)($file['bulk_account_id'] ?? 0) ?>/batches" class="text-sm text-blue-600">&larr; Back</a>
    <div class="mt-4 bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-semibold">Resume Details</h1>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($file['filename'] ?? '') ?></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/resumes/<?= (int)($file['id'] ?? 0) ?>/download" class="px-3 py-1.5 rounded-md bg-gray-800 text-white">Download</a>
                <?php $s = (string)($file['status'] ?? ''); ?>
                <?php if ($s !== 'success'): ?>
                <form method="post" action="/admin/resumes/<?= (int)($file['id'] ?? 0) ?>/approve">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="px-3 py-1.5 rounded-md bg-green-600 text-white">Publish</button>
                </form>
                <form method="post" action="/admin/resumes/<?= (int)($file['id'] ?? 0) ?>/reject" class="flex items-center gap-2">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="text" class="px-3 py-1.5 border rounded-md" name="reason" placeholder="Reason">
                    <button class="px-3 py-1.5 rounded-md bg-red-600 text-white">Reject</button>
                </form>
                <?php endif; ?>
                <form method="post" action="/admin/resumes/<?= (int)($file['id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this file permanently?');">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-800">Delete</button>
                </form>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs text-gray-500">Status</div>
                <div class="text-xl font-semibold"><?= htmlspecialchars($file['status'] ?? '') ?></div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs text-gray-500">Candidate</div>
                <div class="text-xl font-semibold">
                    <?= !empty($candidate) ? htmlspecialchars($candidate->attributes['full_name'] ?? ('#'.$candidate->id)) : 'Not linked' ?>
                </div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs text-gray-500">Processed At</div>
                <div class="text-xl font-semibold"><?= htmlspecialchars($file['processed_at'] ?? '') ?></div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs text-gray-500">Failure</div>
                <div class="text-xl font-semibold"><?= htmlspecialchars($file['failure_reason'] ?? '') ?></div>
            </div>
        </div>
        <h2 class="text-lg font-semibold mb-2">Parsed Data</h2>
        <?php if (!empty($parsed)): ?>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium mb-1">Basics</h3>
                    <div class="text-sm text-gray-700">Name: <?= htmlspecialchars($parsed['name'] ?? '') ?></div>
                    <div class="text-sm text-gray-700">Email: <?= htmlspecialchars($parsed['email'] ?? '') ?></div>
                    <div class="text-sm text-gray-700">Phone: <?= htmlspecialchars($parsed['phone'] ?? '') ?></div>
                    <div class="text-sm text-gray-700">Location: <?= htmlspecialchars($parsed['location'] ?? '') ?></div>
                </div>
                <div>
                    <h3 class="font-medium mb-1">Skills</h3>
                    <div class="text-sm text-gray-700"><?= htmlspecialchars(implode(', ', (array)($parsed['skills'] ?? []))) ?></div>
                </div>
                <div>
                    <h3 class="font-medium mb-1">Education</h3>
                    <pre class="text-xs bg-gray-50 p-3 rounded"><?= htmlspecialchars(json_encode($parsed['education'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                </div>
                <div>
                    <h3 class="font-medium mb-1">Experience</h3>
                    <pre class="text-xs bg-gray-50 p-3 rounded"><?= htmlspecialchars(json_encode($parsed['experience'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                </div>
            </div>
        <?php else: ?>
            <div class="p-4 bg-yellow-50 rounded">No parsed data yet. Status: <?= htmlspecialchars($file['status'] ?? '') ?></div>
        <?php endif; ?>
    </div>
</div>
