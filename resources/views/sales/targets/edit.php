<?php
$title = 'Edit Target | ' . ($targetUser['name'] ?? $targetUser['email']);
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Target</h1>
            <p class="text-slate-500 dark:text-slate-400">Set monthly goals for <?= htmlspecialchars($targetUser['name'] ?? $targetUser['email']) ?></p>
        </div>
        <a href="/sales/manager/targets?month=<?= $month ?>" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Targets
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <form action="/sales/manager/targets" method="POST" class="space-y-6">
            <input type="hidden" name="user_id" value="<?= $targetUser['id'] ?>">
            <input type="hidden" name="month" value="<?= $month ?>">
            
            <div class="grid grid-cols-1 gap-6">
                <!-- User Info (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sales Executive</label>
                    <input type="text" value="<?= htmlspecialchars($targetUser['name'] ?? $targetUser['email']) ?>" readonly disabled 
                           class="w-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm text-slate-500 cursor-not-allowed">
                </div>

                <!-- Month (Read-only for context) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Month</label>
                    <input type="month" value="<?= $month ?>" readonly disabled 
                           class="w-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm text-slate-500 cursor-not-allowed">
                </div>

                <!-- Revenue Target -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Revenue Target (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-slate-400">₹</span>
                        <input type="number" step="0.01" name="revenue_target" value="<?= $target['revenue_target'] ?? 0 ?>" required
                               class="w-full pl-8 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Target revenue amount for this month.</p>
                </div>

                <!-- Deals Target -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deals Target</label>
                    <input type="number" name="deals_target" value="<?= $target['deals_target'] ?? 0 ?>" required
                           class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    <p class="mt-1 text-xs text-slate-500">Number of deals to be closed.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Target
                </button>
            </div>
        </form>
    </div>
</div>
