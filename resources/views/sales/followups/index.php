<?php
$title = 'Follow-ups';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Scheduled Follow-ups</h1>
            <p class="text-slate-500 dark:text-slate-400">Track and manage upcoming client interactions</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Sync Calendar
            </button>
        </div>
    </div>

    <!-- Calendar / List View Toggle (Mock) -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-1 inline-flex mb-4">
        <button class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-sm font-medium">List View</button>
        <button class="px-4 py-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 text-sm font-medium">Calendar</button>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                        <th class="p-4">Lead / Company</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Scheduled Time</th>
                        <th class="p-4">Assigned To</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php if (empty($followups)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                No follow-ups scheduled at the moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($followups as $f): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4">
                                    <div class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($f['company_name']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Stage: <?= ucfirst(str_replace('_', ' ', $f['stage'])) ?></div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($f['contact_name']) ?>
                                    <div class="text-xs text-slate-400"><?= htmlspecialchars($f['contact_phone'] ?? '') ?></div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 bg-amber-50 dark:bg-amber-900/20 rounded text-amber-600 dark:text-amber-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-800 dark:text-white"><?= date('M j, Y', strtotime($f['next_followup_at'])) ?></div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400"><?= date('g:i A', strtotime($f['next_followup_at'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($f['executive_email'] ?? 'Unassigned') ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/sales/leads/<?= $f['id'] ?>" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">View Lead</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
