<?php 
$title = 'Dashboard';
// Mock data/Defaults
$kpis = $kpis ?? ['total_revenue' => 0, 'active_deals' => 0, 'conversion_rate' => 0, 'avg_deal_size' => 0];
$leadSources = $leadSources ?? [];
$topPerformers = $topPerformers ?? [];
$todaysTasks = $todaysTasks ?? [];
?>

<div class="space-y-8">
    
    <!-- Welcome Section & Quick Actions -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 p-8 shadow-xl">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Dashboard Overview</h2>
                <p class="mt-2 text-slate-400 text-lg">Track your team's performance and pipeline progress.</p>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <a href="/sales/leads/create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg shadow-lg shadow-indigo-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add Lead
                </a>
                <a href="/sales/leads/create?type=deal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg shadow-emerald-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    New Deal
                </a>
                <a href="/sales/calls/create" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg shadow-lg shadow-sky-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V8a2 2 0 01-2 2H7l2 2 3 3 2-2v-1a2 2 0 012-2h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-2" /></svg>
                    Log Call
                </a>
                <a href="/sales/email/compose" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg shadow-lg shadow-teal-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Send Email
                </a>
                <a href="/sales/tasks/create" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg backdrop-blur-sm transition text-sm font-medium border border-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Schedule
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg backdrop-blur-sm transition text-sm font-medium border border-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Report
                </button>
            </div>
        </div>
        
        <!-- Decorative background elements -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
    </div>

    <!-- Stage KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
        <?php $stageMap = [
            'new' => ['label' => 'New', 'color' => 'blue'],
            'contacted' => ['label' => 'Contacted', 'color' => 'indigo'],
            'demo_done' => ['label' => 'Demo Done', 'color' => 'violet'],
            'follow_up' => ['label' => 'Follow Up', 'color' => 'amber'],
            'payment_pending' => ['label' => 'Payment', 'color' => 'orange'],
            'converted' => ['label' => 'Converted', 'color' => 'emerald'],
            'lost' => ['label' => 'Lost', 'color' => 'rose'],
        ]; ?>
        <?php foreach ($stageMap as $key => $cfg): ?>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $cfg['color'] ?>-50 text-<?= $cfg['color'] ?>-700"><?= $cfg['label'] ?></span>
                    </div>
                    <div class="text-2xl font-bold text-slate-800"><?= (int)($kpis[$key] ?? 0) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php 
        $kpiConfig = [
            ['label' => 'Total Revenue', 'value' => '$' . number_format($kpis['total_revenue'] ?? 0), 'change' => '+12.5%', 'trend' => 'up', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'indigo'],
            ['label' => 'Active Deals', 'value' => $kpis['active_deals'] ?? 0, 'change' => '+5', 'trend' => 'up', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => 'blue'],
            ['label' => 'Conversion Rate', 'value' => ($kpis['conversion_rate'] ?? 0) . '%', 'change' => '+2.1%', 'trend' => 'up', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'emerald'],
            ['label' => 'Avg Deal Size', 'value' => '$' . number_format($kpis['avg_deal_size'] ?? 0), 'change' => '-1.5%', 'trend' => 'down', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'amber'],
        ];
        foreach ($kpiConfig as $kpi): ?>
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-sm font-medium text-slate-500"><?= $kpi['label'] ?></p>
                        <p class="text-2xl font-bold text-slate-800 mt-1"><?= $kpi['value'] ?></p>
                    </div>
                    <div class="p-3 bg-<?= $kpi['color'] ?>-50 text-<?= $kpi['color'] ?>-600 rounded-lg group-hover:bg-<?= $kpi['color'] ?>-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $kpi['icon'] ?>" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm relative z-10">
                    <span class="<?= $kpi['trend'] === 'up' ? 'text-emerald-500' : 'text-rose-500' ?> flex items-center font-medium bg-slate-50 px-1.5 py-0.5 rounded">
                        <?php if($kpi['trend'] === 'up'): ?>
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        <?php else: ?>
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                        <?php endif; ?>
                        <?= $kpi['change'] ?>
                    </span>
                    <span class="text-slate-400 ml-2">vs last month</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Today's Tasks -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Today's Focus
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full"><?= count($todaysTasks) ?> Tasks</span>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if(empty($todaysTasks)): ?>
                        <div class="p-6 text-center text-slate-500 text-sm">No tasks scheduled for today. Good job!</div>
                    <?php else: ?>
                        <?php foreach($todaysTasks as $task): ?>
                        <div class="p-4 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($task['company_name'] ?? 'Unknown Lead') ?></h4>
                                    <p class="text-xs text-slate-500">Follow up with <?= htmlspecialchars($task['contact_name'] ?? 'Contact') ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <?= date('g:i A', strtotime($task['next_followup_at'])) ?>
                                </span>
                                <a href="/sales/leads/<?= $task['id'] ?>" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pipeline Overview -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Pipeline Overview</h3>
                    <a href="/sales/manager/pipeline" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        View Full Pipeline
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Proposal -->
                    <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-full shadow-sm">
                        <div class="p-3 border-b border-slate-100 bg-slate-50/50 rounded-t-xl flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Proposal</span>
                            <span class="bg-white border border-slate-200 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-full"><?= count($pipeline['proposal'] ?? []) ?></span>
                        </div>
                        <div class="p-3 space-y-3 flex-1">
                            <?php if (empty($pipeline['proposal'])): ?>
                                <div class="h-full flex items-center justify-center text-xs text-slate-400 italic">No deals</div>
                            <?php else: ?>
                                <?php foreach ($pipeline['proposal'] as $p): ?>
                                <a href="/sales/leads/<?= $p['id'] ?>" class="block bg-white p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition group">
                                    <h4 class="font-semibold text-slate-800 text-sm truncate group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($p['contact_name'] ?? $p['company_name']) ?></h4>
                                    <p class="text-xs text-slate-500 mt-1 truncate"><?= htmlspecialchars($p['company_name']) ?></p>
                                    <div class="mt-2 flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700">$<?= number_format($p['deal_value']) ?></span>
                                        <span class="text-slate-400"><?= date('M d', strtotime($p['updated_at'])) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Negotiation -->
                    <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-full shadow-sm">
                        <div class="p-3 border-b border-slate-100 bg-slate-50/50 rounded-t-xl flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Negotiation</span>
                            <span class="bg-white border border-slate-200 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-full"><?= count($pipeline['negotiation'] ?? []) ?></span>
                        </div>
                        <div class="p-3 space-y-3 flex-1">
                            <?php if (empty($pipeline['negotiation'])): ?>
                                <div class="h-full flex items-center justify-center text-xs text-slate-400 italic">No deals</div>
                            <?php else: ?>
                                <?php foreach ($pipeline['negotiation'] as $p): ?>
                                <a href="/sales/leads/<?= $p['id'] ?>" class="block bg-white p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md hover:border-amber-200 transition group border-l-4 border-l-amber-400">
                                    <h4 class="font-semibold text-slate-800 text-sm truncate group-hover:text-amber-600 transition-colors"><?= htmlspecialchars($p['contact_name'] ?? $p['company_name']) ?></h4>
                                    <p class="text-xs text-slate-500 mt-1 truncate"><?= htmlspecialchars($p['company_name']) ?></p>
                                    <div class="mt-2 flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700">$<?= number_format($p['deal_value']) ?></span>
                                        <span class="text-slate-400"><?= date('M d', strtotime($p['updated_at'])) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Closing -->
                    <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-full shadow-sm">
                        <div class="p-3 border-b border-slate-100 bg-slate-50/50 rounded-t-xl flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Closing</span>
                            <span class="bg-white border border-slate-200 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-full"><?= count($pipeline['closing'] ?? []) ?></span>
                        </div>
                        <div class="p-3 space-y-3 flex-1">
                            <?php if (empty($pipeline['closing'])): ?>
                                <div class="h-full flex items-center justify-center text-xs text-slate-400 italic">No deals</div>
                            <?php else: ?>
                                <?php foreach ($pipeline['closing'] as $p): ?>
                                <a href="/sales/leads/<?= $p['id'] ?>" class="block bg-white p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition group border-l-4 border-l-emerald-400">
                                    <h4 class="font-semibold text-slate-800 text-sm truncate group-hover:text-emerald-600 transition-colors"><?= htmlspecialchars($p['contact_name'] ?? $p['company_name']) ?></h4>
                                    <p class="text-xs text-slate-500 mt-1 truncate"><?= htmlspecialchars($p['company_name']) ?></p>
                                    <div class="mt-2 flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700">$<?= number_format($p['deal_value']) ?></span>
                                        <span class="text-slate-400"><?= date('M d', strtotime($p['updated_at'])) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Performance -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Team Performance</h3>
                    <a href="/sales/manager/team" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Add Member</a>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if (empty($teamPerformance)): ?>
                        <div class="p-6 text-center text-slate-500 text-sm">No team performance data available.</div>
                    <?php else: ?>
                        <?php foreach ($teamPerformance as $tp): ?>
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    <?= strtoupper(substr($tp['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($tp['name']) ?></p>
                                    <p class="text-xs text-slate-500">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600"><?= ucwords(str_replace('_',' ', $tp['role'] ?? '')) ?></span>
                                        <span class="ml-2"><?= (int)($tp['converted_leads'] ?? 0) ?> converted</span>
                                    </p>
                                </div>
                            </div>
                            <div class="w-56">
                                <?php $pct = (int)($tp['progress_pct'] ?? 0); ?>
                                <div class="text-xs text-slate-500 mb-1">Target <?= $pct ?>%</div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-orange-500 to-cyan-500 rounded-full" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                            <div class="text-sm font-bold text-slate-800">$<?= number_format((float)($tp['revenue_this_month'] ?? 0)) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Leads Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Recent Leads</h3>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 text-sm bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-600">Filter</button>
                        <a href="/sales/manager/leads" class="px-3 py-1.5 text-sm bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg text-indigo-700">View All</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-3 font-semibold">ID</th>
                                <th class="px-6 py-3 font-semibold">Company</th>
                                <th class="px-6 py-3 font-semibold">Deal</th>
                                <th class="px-6 py-3 font-semibold">Contact</th>
                                <th class="px-6 py-3 font-semibold">Stage</th>
                                <th class="px-6 py-3 font-semibold">Value</th>
                                <th class="px-6 py-3 font-semibold">Assigned To</th>
                                <th class="px-6 py-3 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($leads as $lead): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-bold text-slate-700">L<?= str_pad((string)$lead['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-800"><?= htmlspecialchars($lead['company_name'] ?? 'Unknown') ?></td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?= htmlspecialchars($lead['deal_name'] ?? 'Standard Plan') ?></td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?= htmlspecialchars($lead['contact_name'] ?? '') ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    $stageColor = match($lead['stage']) {
                                        'converted' => 'emerald',
                                        'lost' => 'rose',
                                        'new' => 'blue',
                                        'contacted' => 'indigo',
                                        'demo_done' => 'violet',
                                        'follow_up' => 'amber',
                                        'payment_pending' => 'orange',
                                        default => 'slate'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $stageColor ?>-50 text-<?= $stageColor ?>-700">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $lead['stage'] ?? ''))) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-700">
                                    $<?= number_format($lead['deal_value'] ?? $lead['amount'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name=<?= urlencode($lead['executive_name'] ?? 'Unassigned') ?>&background=random" alt=""/>
                                        <span class="text-xs text-slate-500"><?= htmlspecialchars($lead['executive_name'] ?? 'Unassigned') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="relative" x-data="{open:false}">
                                        <button @click="open=!open" class="text-slate-400 hover:text-indigo-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                        </button>
                                        <div x-show="open" @click.away="open=false" x-transition class="absolute right-0 mt-2 w-36 bg-white rounded-lg shadow border border-slate-200 z-50">
                                            <a href="/sales/leads/<?= $lead['id'] ?>" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">View Details</a>
                                            <a href="/sales/leads/<?= $lead['id'] ?>" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit Lead</a>
                                            <a href="/sales/tasks/create?lead_id=<?= $lead['id'] ?>" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Schedule Call</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column (1/3 width) -->
        <div class="space-y-8">
            
            <!-- Revenue Chart -->
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Revenue Trend</h3>
                    <select class="text-xs border-none bg-slate-50 rounded-lg text-slate-500 focus:ring-0">
                        <option>Last 7 Days</option>
                    </select>
                </div>
                <div class="relative h-48 w-full">
                     <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Lead Sources Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-base font-bold text-slate-800 mb-4">Lead Sources</h3>
                <div class="relative h-48 w-full flex justify-center">
                     <canvas id="leadSourceChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <?php 
                    $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                    $i = 0;
                    foreach($leadSources as $source => $count): 
                        $color = $colors[$i % count($colors)];
                        $i++;
                    ?>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: <?= $color ?>"></span>
                        <span class="text-slate-600 capitalize"><?= $source ?></span>
                        <span class="text-slate-400 ml-auto"><?= $count ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sales Funnel -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Sales Funnel</h3>
                    <span class="text-xs text-emerald-600 font-medium">+12.5%</span>
                </div>
                <div class="relative h-48 w-full">
                    <canvas id="salesFunnelChart"></canvas>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-base font-bold text-slate-800 mb-4">Top Performers</h3>
                <div class="space-y-4">
                    <?php if(empty($topPerformers)): ?>
                        <p class="text-sm text-slate-500 text-center">No data available yet.</p>
                    <?php else: ?>
                        <?php foreach($topPerformers as $idx => $performer): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 relative">
                                    <?= substr($performer['name'], 0, 1) ?>
                                    <?php if($idx === 0): ?>
                                        <div class="absolute -top-1 -right-1 text-amber-500">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($performer['name']) ?></p>
                                    <p class="text-xs text-slate-500"><?= $performer['deals_count'] ?> deals won</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-emerald-600">$<?= number_format($performer['total_revenue']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-base font-bold text-slate-800 mb-4">Recent Activity</h3>
                <div class="space-y-4 relative">
                    <div class="absolute left-1 top-2 bottom-2 w-0.5 bg-slate-100"></div>
                    <?php if (empty($activities)): ?>
                        <p class="text-sm text-slate-500 pl-4">No recent activity.</p>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                        <div class="flex gap-3 relative">
                            <div class="mt-1.5 flex-shrink-0 w-2.5 h-2.5 rounded-full bg-indigo-500 border-2 border-white ring-1 ring-slate-100 z-10"></div>
                            <div>
                                <p class="text-sm text-slate-600">
                                    <span class="font-semibold text-slate-800"><?= htmlspecialchars($act['user_name'] ?? 'User') ?></span> 
                                    <?= htmlspecialchars($act['description'] ?? 'updated lead ' . ($act['lead_name'] ?? '')) ?>
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5"><?= date('M d, H:i', strtotime($act['created_at'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="/sales/notifications" class="block text-center w-full mt-6 py-2 text-sm text-indigo-600 font-medium hover:bg-indigo-50 rounded-lg transition-colors">View All Activity</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perfCanvas = document.getElementById('performanceChart');
        const ctx = perfCanvas.getContext('2d');
        const rawLabels = <?= json_encode($chartData['labels'] ?? []) ?>;
        const rawData = <?= json_encode($chartData['data'] ?? []) ?>;
        const hasLabels = Array.isArray(rawLabels) && rawLabels.length > 0;
        const hasData = Array.isArray(rawData) && rawData.length > 0;
        const labelsPerf = hasLabels ? rawLabels : (function(){ const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; const res = []; for(let i=6;i>=0;i--){ const d=new Date(); d.setDate(d.getDate()-i); res.push(days[d.getDay()]); } return res; })();
        const dataPerf = hasData ? rawData : new Array(labelsPerf.length).fill(0);
        const perfChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsPerf,
                datasets: [{
                    label: 'Revenue',
                    data: dataPerf,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, display: false },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
        if (dataPerf.every(v => v === 0)) {
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 flex items-center justify-center text-slate-400 text-sm';
            overlay.textContent = 'No data';
            perfCanvas.parentElement.appendChild(overlay);
        }

        const leadCanvas = document.getElementById('leadSourceChart');
        const ctxSource = leadCanvas.getContext('2d');
        const sourceData = <?= json_encode($leadSources) ?>;
        let labels = Object.keys(sourceData || {});
        let data = Object.values(sourceData || {});
        const sum = (data || []).reduce((a,b)=>a+(Number(b)||0),0);
        if (!labels.length || sum === 0) {
            labels = ['No data'];
            data = [1];
        }
        const leadChart = new Chart(ctxSource, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: (labels.length === 1 && labels[0] === 'No data')
                        ? ['#cbd5e1']
                        : ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
        if (labels.length === 1 && labels[0] === 'No data') {
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 flex items-center justify-center text-slate-400 text-sm';
            overlay.textContent = 'No data';
            leadCanvas.parentElement.appendChild(overlay);
        }

        const funnelCtx = document.getElementById('salesFunnelChart').getContext('2d');
        const funnelLabels = ['New','Contacted','Demo Done','Follow Up','Payment','Converted','Lost'];
        const funnelCounts = [
            <?= (int)($kpis['new'] ?? 0) ?>,
            <?= (int)($kpis['contacted'] ?? 0) ?>,
            <?= (int)($kpis['demo_done'] ?? 0) ?>,
            <?= (int)($kpis['follow_up'] ?? 0) ?>,
            <?= (int)($kpis['payment_pending'] ?? 0) ?>,
            <?= (int)($kpis['converted'] ?? 0) ?>,
            <?= (int)($kpis['lost'] ?? 0) ?>
        ];
        const funnelChart = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: funnelLabels,
                datasets: [{
                    data: funnelCounts,
                    backgroundColor: ['#3b82f6','#6366f1','#8b5cf6','#f59e0b','#f97316','#10b981','#ef4444'],
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { display: false } },
                    y: { grid: { display: false } }
                }
            }
        });
        if (funnelCounts.every(v => (Number(v)||0) === 0)) {
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 flex items-center justify-center text-slate-400 text-sm';
            overlay.textContent = 'No data';
            document.getElementById('salesFunnelChart').parentElement.appendChild(overlay);
        }
    });
</script>
