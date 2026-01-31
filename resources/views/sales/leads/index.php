<?php
// Ensure leads array exists
if (!isset($leads)) {
    $leads = [];
}
// Ensure team array exists
if (!isset($team)) {
    $team = [];
}

$statusColors = [
    'new' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
    'contacted' => 'bg-blue-100 text-blue-700 border-blue-200',
    'follow_up' => 'bg-amber-100 text-amber-700 border-amber-200',
    'demo_done' => 'bg-purple-100 text-purple-700 border-purple-200',
    'payment_pending' => 'bg-orange-100 text-orange-700 border-orange-200',
    'converted' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'lost' => 'bg-slate-100 text-slate-700 border-slate-200',
];
?>

<div x-data="{ 
    selectedLeads: [], 
    toggleSelection(id) {
        if (this.selectedLeads.includes(id)) {
            this.selectedLeads = this.selectedLeads.filter(x => x !== id);
        } else {
            this.selectedLeads.push(id);
        }
    },
    toggleAll() {
        if (this.selectedLeads.length === <?= count($leads) ?>) {
            this.selectedLeads = [];
        } else {
            this.selectedLeads = [<?= implode(',', array_column($leads, 'id')) ?>];
        }
    }
}" class="flex flex-col h-[calc(100vh-8rem)]">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Leads & CRM</h2>
            <p class="text-slate-500 text-sm mt-1">Manage and track your leads efficiently.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" placeholder="Search leads..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-64 shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <a href="/sales/leads/create" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Lead
            </a>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6" x-data="{ showFilters: true }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="showFilters = !showFilters" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                </button>
                <div class="h-4 w-px bg-slate-200"></div>
                
                <!-- Bulk Actions (Visible when items selected) -->
                <div x-show="selectedLeads.length > 0" x-transition class="flex items-center gap-2">
                    <span class="text-sm text-slate-500"><span x-text="selectedLeads.length" class="font-bold text-slate-800"></span> selected</span>
                    <form action="/sales/leads/bulk-assign" method="post" class="flex items-center gap-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <template x-for="id in selectedLeads">
                            <input type="hidden" name="lead_ids[]" :value="id">
                        </template>
                        <select name="executive_id" class="text-sm border-slate-200 rounded-lg py-1.5 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Assign to...</option>
                            <?php foreach ($team as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['email']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">Apply</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filter Inputs -->
        <form x-show="showFilters" x-collapse method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Stage</label>
                <select name="stage" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Stages</option>
                    <?php foreach (['new','contacted','follow_up','demo_done','payment_pending','converted','lost'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['stage'] ?? '')===$s?'selected':'' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Executive</label>
                <select name="executive" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="0">All Executives</option>
                    <?php foreach ($team as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= (int)($filters['executive'] ?? 0)===(int)$t['id']?'selected':'' ?>><?= htmlspecialchars($t['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Date Range</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="from" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                    <span class="text-slate-400">-</span>
                    <input type="date" name="to" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                </div>
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg font-medium hover:bg-indigo-100 transition-colors">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Leads Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex-1 overflow-hidden flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-10">
                            <input type="checkbox" @click="toggleAll()" :checked="selectedLeads.length === <?= count($leads) ?> && <?= count($leads) ?> > 0" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Company / Contact</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Deal Value</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stage</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Next Follow-up</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Owner</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach ($leads as $l): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" :value="<?= $l['id'] ?>" @change="toggleSelection(<?= $l['id'] ?>)" x-model="selectedLeads" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-600 flex items-center justify-center font-bold text-sm border border-indigo-200/50">
                                        <?= strtoupper(substr($l['company_name'], 0, 1)) ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($l['company_name']) ?></div>
                                        <div class="text-sm text-slate-500"><?= htmlspecialchars($l['contact_name'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($l['currency'] ?? '$') ?> <?= number_format((float)($l['deal_value'] ?? 0)) ?></div>
                                <?php if (($l['paid_amount'] ?? 0) > 0): ?>
                                    <div class="text-xs font-medium text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded inline-block mt-1">Paid: <?= number_format((float)$l['paid_amount']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?= $statusColors[$l['stage']] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $l['stage'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($l['next_followup_at'])): ?>
                                    <div class="flex flex-col">
                                        <span class="text-sm text-slate-700 font-medium"><?= date('M j, Y', strtotime($l['next_followup_at'])) ?></span>
                                        <span class="text-xs text-slate-400"><?= date('g:i A', strtotime($l['next_followup_at'])) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">No follow-up set</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <img class="h-6 w-6 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name=<?= urlencode($l['executive_email']) ?>&background=random" alt="">
                                    <span class="text-sm text-slate-600 max-w-[120px] truncate" title="<?= htmlspecialchars($l['executive_email'] ?? '') ?>"><?= htmlspecialchars($l['executive_email'] ?? '-') ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/sales/leads/<?= $l['id'] ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (Mock) -->
        <div class="bg-white px-6 py-4 border-t border-slate-200 flex items-center justify-between">
            <div class="text-sm text-slate-500">
                Showing <span class="font-medium text-slate-900">1</span> to <span class="font-medium text-slate-900"><?= count($leads) ?></span> of <span class="font-medium text-slate-900"><?= count($leads) ?></span> results
            </div>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm text-slate-400 cursor-not-allowed" disabled>Previous</button>
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors">Next</button>
            </div>
        </div>
    </div>
</div>

