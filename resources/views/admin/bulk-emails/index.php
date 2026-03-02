<?php
$title = $title ?? 'Bulk Notification Campaigns';
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold tracking-tight"><?= htmlspecialchars($title) ?></h1>
        <a href="/admin/marketing/campaigns/create" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
            Create Campaign
        </a>
    </div>

    <div class="rounded-md border bg-card text-card-foreground shadow-sm">
        <div class="p-6 space-y-4">
            <div>
                <h2 class="text-xl font-semibold">Manage Campaigns</h2>
                <p class="text-sm text-muted-foreground">View, filter, and manage notification campaigns</p>
            </div>
            <form method="GET" class="flex items-center gap-3">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search by subject or title" class="flex h-10 w-[28rem] rounded-md border border-input bg-background px-3 py-2 text-sm">
                <select name="status" class="flex h-10 w-44 rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <?php $st = $filters['status'] ?? 'all'; ?>
                    <option value="all" <?= $st === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="sent" <?= $st === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="processing" <?= $st === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="failed" <?= $st === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
                <select name="per_page" class="flex h-10 w-28 rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <?php $pp = (int)($pagination['perPage'] ?? 20); ?>
                    <?php foreach ([10,20,30,50] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $pp === $opt ? 'selected' : '' ?>><?= $opt ?>/page</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4">Filter</button>
            </form>

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">ID</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Subject</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Recipients</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Success</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created At</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <?php if (empty($campaigns)): ?>
                            <tr>
                                <td colspan="7" class="p-4 text-center text-muted-foreground">No campaigns found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                    <td class="p-4 align-middle"><?= (int)($campaign['id'] ?? 0) ?></td>
                                    <td class="p-4 align-middle font-medium"><?= htmlspecialchars((string)($campaign['subject'] ?? '')) ?></td>
                                    <td class="p-4 align-middle">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 <?= ($campaign['status'] ?? '') === 'sent' ? 'border-transparent bg-green-500 text-white shadow hover:bg-green-600' : (($campaign['status'] ?? '') === 'failed' ? 'border-transparent bg-red-500 text-white shadow hover:bg-red-600' : 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80') ?>">
                                            <?= ucfirst((string)($campaign['status'] ?? '')) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 align-middle"><?= number_format((int)($campaign['recipient_count'] ?? 0)) ?></td>
                                    <td class="p-4 align-middle"><?= number_format((int)($campaign['success_count'] ?? 0)) ?></td>
                                    <td class="p-4 align-middle"><?= htmlspecialchars((string)(isset($campaign['created_at']) ? date('M j, Y H:i', strtotime($campaign['created_at'])) : '')) ?></td>
                                    <td class="p-4 align-middle text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="/admin/marketing/campaigns/<?= (int)($campaign['id'] ?? 0) ?>" class="text-sm font-medium text-primary hover:underline">View</a>
                                            <button type="button" data-id="<?= (int)($campaign['id'] ?? 0) ?>" class="text-sm font-medium text-red-600 hover:underline js-delete-campaign">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing <?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?> to <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> of <?= $pagination['total'] ?> results
                </div>
                <div class="flex items-center gap-2">
                    <?php $cur = (int)$pagination['page']; $tp = (int)$pagination['totalPages']; ?>
                    <?php if ($cur > 1): ?>
                        <a href="?page=<?= $cur - 1 ?>&per_page=<?= $pagination['perPage'] ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>"
                           class="px-3 py-2 border rounded-md hover:bg-muted">Prev</a>
                    <?php endif; ?>
                    <?php
                        $start = max(1, $cur - 2);
                        $end = min($tp, $cur + 2);
                        for ($p = $start; $p <= $end; $p++):
                    ?>
                        <a href="?page=<?= $p ?>&per_page=<?= $pagination['perPage'] ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>"
                           class="px-3 py-2 border rounded-md <?= $p === $cur ? 'bg-muted' : 'hover:bg-muted' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($cur < $tp): ?>
                        <a href="?page=<?= $cur + 1 ?>&per_page=<?= $pagination['perPage'] ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>"
                           class="px-3 py-2 border rounded-md hover:bg-muted">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.js-delete-campaign').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (!id) return;
        if (!confirm('Delete this campaign? This cannot be undone.')) return;
        fetch('/admin/marketing/campaigns/' + id + '/delete', {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res && res.success) {
                location.reload();
            } else {
                alert('Failed to delete campaign');
            }
        })
        .catch(() => alert('Failed to delete campaign'));
    });
});
</script>
