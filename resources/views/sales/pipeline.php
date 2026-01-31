<?php
// Mock data if not provided
if (!isset($columns)) {
    $columns = [
        'new' => [
            ['id' => 101, 'company_name' => 'Acme Corp', 'title' => 'Enterprise License', 'value' => 12000, 'owner' => 'John D.', 'date' => '2d ago', 'priority' => 'high'],
            ['id' => 102, 'company_name' => 'TechStart Inc', 'title' => 'Basic Plan', 'value' => 2400, 'owner' => 'Sarah M.', 'date' => '4h ago', 'priority' => 'medium'],
        ],
        'contacted' => [
            ['id' => 103, 'company_name' => 'Global Systems', 'title' => 'Custom Integration', 'value' => 45000, 'owner' => 'Mike R.', 'date' => '1d ago', 'priority' => 'high'],
        ],
        'follow_up' => [
            ['id' => 104, 'company_name' => 'Nebula Stream', 'title' => 'SaaS Platform', 'value' => 8500, 'owner' => 'John D.', 'date' => '3d ago', 'priority' => 'low'],
            ['id' => 105, 'company_name' => 'Blue Ocean', 'title' => 'Consulting', 'value' => 5000, 'owner' => 'Sarah M.', 'date' => '5d ago', 'priority' => 'medium'],
        ],
        'demo_done' => [
            ['id' => 106, 'company_name' => 'Quantum Dynamics', 'title' => 'Full Suite', 'value' => 22000, 'owner' => 'Mike R.', 'date' => '1w ago', 'priority' => 'high'],
        ],
        'payment_pending' => [
            ['id' => 107, 'company_name' => 'Stellar Innovations', 'title' => 'Pro Subscription', 'value' => 3600, 'owner' => 'John D.', 'date' => '2h ago', 'priority' => 'medium'],
        ],
        'converted' => [
            ['id' => 108, 'company_name' => 'Alpha Group', 'title' => 'Annual Contract', 'value' => 60000, 'owner' => 'Sarah M.', 'date' => '2w ago', 'priority' => 'high'],
        ],
        'lost' => [],
    ];
}

$stages = [
    'new' => ['label' => 'New Lead', 'color' => 'bg-indigo-100 text-indigo-700', 'border' => 'border-indigo-200'],
    'contacted' => ['label' => 'Contacted', 'color' => 'bg-blue-100 text-blue-700', 'border' => 'border-blue-200'],
    'follow_up' => ['label' => 'Follow Up', 'color' => 'bg-amber-100 text-amber-700', 'border' => 'border-amber-200'],
    'demo_done' => ['label' => 'Demo Done', 'color' => 'bg-purple-100 text-purple-700', 'border' => 'border-purple-200'],
    'payment_pending' => ['label' => 'Payment Pending', 'color' => 'bg-orange-100 text-orange-700', 'border' => 'border-orange-200'],
    'converted' => ['label' => 'Converted', 'color' => 'bg-emerald-100 text-emerald-700', 'border' => 'border-emerald-200'],
    'lost' => ['label' => 'Lost', 'color' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200'],
];
?>

<div class="flex flex-col h-[calc(100vh-8rem)]"> <!-- Full height minus header -->
    
    <!-- Pipeline Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Deals Pipeline</h2>
            <p class="text-slate-500 text-sm mt-1">Manage your deal stages and track progress.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex -space-x-2 mr-2">
                <img class="w-8 h-8 rounded-full border-2 border-white ring-1 ring-slate-200" src="https://ui-avatars.com/api/?name=John+Doe&background=random" alt="User">
                <img class="w-8 h-8 rounded-full border-2 border-white ring-1 ring-slate-200" src="https://ui-avatars.com/api/?name=Sarah+Smith&background=random" alt="User">
                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-medium text-slate-500 ring-1 ring-slate-200">+3</div>
            </div>
            <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Deal
            </button>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
        <div class="inline-flex h-full gap-5 min-w-full">
            <?php foreach ($stages as $key => $meta): 
                $deals = $columns[$key] ?? [];
                $count = count($deals);
                $totalValue = array_sum(array_column($deals, 'value'));
            ?>
            <div class="w-80 flex-shrink-0 flex flex-col h-full rounded-2xl bg-slate-100/50 border border-slate-200/60">
                
                <!-- Column Header -->
                <div class="p-3 border-b border-slate-200/60 bg-slate-50/50 rounded-t-2xl backdrop-blur-sm sticky top-0 z-10">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wide"><?= $meta['label'] ?></span>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full <?= $meta['color'] ?>"><?= $count ?></span>
                    </div>
                    <div class="h-1 w-full bg-slate-200 rounded-full overflow-hidden mt-2">
                        <div class="h-full <?= str_replace(['bg-', 'text-'], 'bg-', $meta['color']) ?> opacity-50 w-full"></div>
                    </div>
                    <div class="text-xs text-slate-400 font-medium mt-2 text-right">
                        $<?= number_format($totalValue) ?>
                    </div>
                </div>

                <!-- Cards Container -->
                <div class="p-3 flex-1 overflow-y-auto space-y-3 custom-scrollbar">
                    <?php if (empty($deals)): ?>
                        <div class="flex flex-col items-center justify-center h-32 text-slate-400 border-2 border-dashed border-slate-200 rounded-xl">
                            <svg class="w-6 h-6 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-xs font-medium">No deals</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($deals as $deal): ?>
                        <div class="group bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-indigo-300 transition-all cursor-move relative">
                            <!-- Priority Indicator -->
                            <?php if (($deal['priority'] ?? '') === 'high'): ?>
                                <div class="absolute top-4 right-4 w-2 h-2 rounded-full bg-red-500 ring-2 ring-red-100" title="High Priority"></div>
                            <?php endif; ?>

                            <h4 class="font-semibold text-slate-800 mb-0.5 pr-4"><?= htmlspecialchars($deal['title'] ?? 'Deal') ?></h4>
                            <p class="text-sm text-slate-500 mb-3"><?= htmlspecialchars($deal['company_name'] ?? 'Company') ?></p>
                            
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                    $<?= number_format($deal['value'] ?? 0) ?>
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
                                    SaaS
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600 border border-slate-200">
                                        <?= substr($deal['owner'] ?? 'U', 0, 1) ?>
                                    </div>
                                    <span class="text-xs text-slate-400"><?= htmlspecialchars($deal['date'] ?? 'today') ?></span>
                                </div>
                                <button class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-indigo-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* Custom Scrollbar for columns */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
