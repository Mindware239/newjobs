<?php
$title = $title ?? 'Campaign Details';
$campaign = $campaign ?? [];
?>
<div class="space-y-6">
    <div class="flex items-center space-x-2">
        <a href="/admin/marketing/campaigns" class="text-muted-foreground hover:text-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="text-2xl font-bold tracking-tight"><?= htmlspecialchars($title) ?></h1>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-md border bg-card text-card-foreground shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Overview</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                        <dd class="text-lg font-semibold capitalize"><?= htmlspecialchars($campaign['status']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Recipients</dt>
                        <dd class="text-lg font-semibold"><?= number_format($campaign['recipient_count']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Success</dt>
                        <dd class="text-lg font-semibold text-green-600"><?= number_format($campaign['success_count']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Failed</dt>
                        <dd class="text-lg font-semibold text-red-600"><?= number_format($campaign['failure_count'] ?? 0) ?></dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-muted-foreground">Sent At</dt>
                        <dd class="text-base"><?= $campaign['sent_at'] ? date('F j, Y g:i A', strtotime($campaign['sent_at'])) : 'Pending' ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="rounded-md border bg-card text-card-foreground shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Campaign Content</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-muted-foreground block">Subject</span>
                        <span class="text-base"><?= htmlspecialchars($campaign['subject']) ?></span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-muted-foreground block">Filters</span>
                        <pre class="text-xs bg-muted p-2 rounded overflow-auto mt-1"><?= htmlspecialchars(json_encode(json_decode($campaign['filters'] ?? '{}'), JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-md border bg-card text-card-foreground shadow-sm md:col-span-2">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Message Body</h3>
                <div class="prose max-w-none dark:prose-invert p-4 border rounded bg-background">
                    <?= $campaign['message'] // Already HTML ?>
                </div>
            </div>
        </div>
    </div>
</div>
