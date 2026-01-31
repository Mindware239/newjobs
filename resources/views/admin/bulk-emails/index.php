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
        <div class="p-6">
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
                                    <td class="p-4 align-middle"><?= $campaign->id ?></td>
                                    <td class="p-4 align-middle font-medium"><?= htmlspecialchars($campaign->subject) ?></td>
                                    <td class="p-4 align-middle">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 <?= $campaign->status === 'sent' ? 'border-transparent bg-green-500 text-white shadow hover:bg-green-600' : ($campaign->status === 'failed' ? 'border-transparent bg-red-500 text-white shadow hover:bg-red-600' : 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80') ?>">
                                            <?= ucfirst($campaign->status) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 align-middle"><?= number_format($campaign->recipient_count) ?></td>
                                    <td class="p-4 align-middle"><?= number_format($campaign->success_count) ?></td>
                                    <td class="p-4 align-middle"><?= date('M j, Y H:i', strtotime($campaign->created_at)) ?></td>
                                    <td class="p-4 align-middle text-right">
                                        <a href="/admin/marketing/campaigns/<?= $campaign->id ?>" class="text-sm font-medium text-primary hover:underline">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
