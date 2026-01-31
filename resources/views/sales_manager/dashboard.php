<?php
$title = 'Dashboard';
$stats = $stats ?? ['total' => 0, 'new' => 0, 'contacted' => 0, 'demo_done' => 0, 'follow_up' => 0, 'payment_pending' => 0, 'converted' => 0, 'lost' => 0];
$charts = $charts ?? ['funnel' => [], 'sources' => [], 'revenue_trend' => []];
$leads = $leads ?? [];
$execs = $execs ?? [];
$alerts = $alerts ?? ['overdue_followups' => [], 'idle_leads' => [], 'payment_pending' => []];
$teamPerformance = $teamPerformance ?? [];
$topPerformers = $topPerformers ?? [];
$activities = $activities ?? [];
$todaysTasks = $todaysTasks ?? [];

$leadSources = [];
if (!empty($charts['sources'])) {
    foreach ($charts['sources'] as $r) {
        $src = $r['source'] ?? 'Unknown';
        $leadSources[$src] = (int) ($r['count'] ?? 0);
    }
}
$chartLabels = [];
$chartValues = [];
if (!empty($charts['revenue_trend_month']) && !empty($charts['revenue_trend_month']['labels'])) {
    $chartLabels = $charts['revenue_trend_month']['labels'];
    $chartValues = array_map('floatval', $charts['revenue_trend_month']['revenue'] ?? []);
} elseif (!empty($charts['revenue_trend'])) {
    foreach ($charts['revenue_trend'] as $row) {
        $chartLabels[] = date('M d', strtotime($row['d'] ?? $row['date'] ?? 'now'));
        $chartValues[] = (float) ($row['total'] ?? 0);
    }
}
?>

<div class="space-y-8">

    <!-- Welcome Section & Quick Actions -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 p-8 shadow-xl">
        <div class="relative z-10 flex flex-row lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-6">
                <a href="/sales/leads/create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg shadow-lg shadow-indigo-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus w-6 h-6">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" x2="19" y1="8" y2="14"></line>
                        <line x1="22" x2="16" y1="11" y2="11"></line>
                    </svg> Add Lead
                </a>
                <a href="/sales/leads/create?type=deal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-lg shadow-emerald-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-6 h-6">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                        <path d="M10 9H8"></path>
                        <path d="M16 13H8"></path>
                        <path d="M16 17H8"></path>
                    </svg> New Deal
                </a>
                <a href="/sales/calls/create" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg shadow-lg shadow-sky-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone w-6 h-6">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg> Log Call
                </a>
                <a href="/sales/email/compose" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg shadow-lg shadow-teal-500/30 transition text-sm font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail w-6 h-6">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg> Send Email
                </a>
                <a href="/sales/tasks/create" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg backdrop-blur-sm transition text-sm font-medium border border-white/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-6 h-6">
                        <path d="M8 2v4"></path>
                        <path d="M16 2v4"></path>
                        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                        <path d="M3 10h18"></path>
                    </svg> Schedule
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg backdrop-blur-sm transition text-sm font-medium border border-white/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column w-6 h-6">
                        <path d="M3 3v16a2 2 0 0 0 2 2h16"></path>
                        <path d="M18 17V9"></path>
                        <path d="M13 17V5"></path>
                        <path d="M8 17v-3"></path>
                    </svg> Report
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
                    <div class="text-2xl font-bold text-slate-800"><?= (int) ($stats[$key] ?? 0) ?></div>
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
        foreach ($kpiConfig as $kpi):
        ?>
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
                        <?php if ($kpi['trend'] === 'up'): ?>
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        <?php else: ?>
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        <?php endif; ?>
                        <?= $kpi['change'] ?>
                    </span>
                    <span class="text-slate-400 ml-2">vs last month</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Row: Revenue Trend + Lead Sources -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-600">Revenue Trend</span>
                        <div class="mt-2 flex items-end gap-3">
                            <h3 class="text-3xl font-bold text-slate-800">$<?= number_format(array_sum($chartValues)) ?></h3>
                            <span class="inline-flex items-center text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded"><?= isset($summary['revenue_growth_pct']) ? '+' . round($summary['revenue_growth_pct'], 1) . '%' : '+0%' ?></span>
                        </div>
                        <p class="text-slate-400 text-xs mt-1">Total revenue this year</p>
                    </div>
                    <select class="text-xs border-none bg-slate-50 rounded-lg text-slate-500 focus:ring-0">
                        <option>Last 7 Days</option>
                        <option selected>This Year</option>
                    </select>
                </div>
                <div class="relative h-56 w-full">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-base font-bold text-slate-800 mb-2">Lead Sources</h3>
                <p class="text-xs text-slate-400 mb-4">Distribution by channel</p>
                <div class="relative h-56 w-full flex justify-center">
                    <canvas id="leadSourceChart"></canvas>
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    <?php
                    $palette = [
                        'website' => '#f59e0b',
                        'form' => '#f59e0b',
                        'referral' => '#14b8a6',
                        'social_media' => '#8b5cf6',
                        'cold_call' => '#3b82f6',
                        'events' => '#10b981'
                    ];
                    foreach ($leadSources as $source => $count):
                        $key = strtolower(str_replace([' ', '-'], '_', $source));
                        $color = $palette[$key] ?? '#cbd5e1';
                    ?>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded" style="background-color: <?= $color ?>"></span>
                            <span class="text-slate-600"><?= htmlspecialchars(ucwords($source)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Funnel Row (Full Width) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Sales Funnel</h3>
                    <p class="text-xs text-slate-400">Lead progression overview</p>
                </div>
                <span class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-full">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <span class="text-xs font-medium text-emerald-600"><?= isset($summary['funnel_growth_pct']) ? '+' . round($summary['funnel_growth_pct'], 1) . '%' : '+0%' ?></span>
                </span>
            </div>
            <div class="relative h-72 w-full mt-2">
                <canvas id="salesFunnelChart"></canvas>
            </div>
        </div>

        <!-- Today's Tasks -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Today's Activity
                    <p class="text-xs text-slate-500">Track daily team activities</p>
                </h3>
                <span class="text-xs font-medium px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full"><?= count($todaysActivities ?? []) ?> Items</span>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($todaysActivities)): ?>
                    <div class="p-6 text-center text-slate-500 text-sm">No activity recorded today.</div>
                <?php else: ?>
                    <?php foreach ($todaysActivities as $act): ?>
                        <div class="p-4 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <?php
                                    $t = strtolower((string)($act['type'] ?? ''));
                                    $statusBadge = 'pending';
                                    if (strpos($t, 'completed') !== false || strpos($t, 'paid') !== false) { $statusBadge = 'completed'; }
                                    $dotColor = $statusBadge === 'completed' ? 'bg-emerald-400' : 'bg-amber-400';
                                ?>
                                <div class="w-2 h-2 rounded-full <?= $dotColor ?>"></div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($act['title'] ?? ucfirst($t)) ?></h4>
                                    <p class="text-xs text-slate-500">
                                        <?= htmlspecialchars($act['company_name'] ?? 'Unknown') ?>
                                        <span class="mx-1">·</span>
                                        <?= htmlspecialchars($act['user_name'] ?? '') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <?= !empty($act['created_at']) ? date('g:i A', strtotime($act['created_at'])) : '' ?>
                                </span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full <?= $statusBadge === 'completed' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' ?>"><?= $statusBadge ?></span>
                                <a href="/sales/leads/<?= (int)($act['lead_id'] ?? 0) ?>" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Pipeline Overview -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Pipeline Overview</h3>
                    <a href="/sales/manager/pipeline" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        View Full Pipeline
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
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
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600"><?= ucwords(str_replace('_', ' ', $tp['role'] ?? '')) ?></span>
                                            <span class="ml-2"><?= (int) ($tp['converted_leads'] ?? 0) ?> converted</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="w-56">
                                    <?php $pct = (int) ($tp['progress_pct'] ?? 0); ?>
                                    <div class="text-xs text-slate-500 mb-1">Target <?= $pct ?>%</div>
                                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-orange-500 to-cyan-500 rounded-full" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-slate-800">$<?= number_format((float) ($tp['revenue_this_month'] ?? 0)) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>





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
                            <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-bold text-slate-700">L<?= str_pad((string) $lead['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-800"><?= htmlspecialchars($lead['company_name'] ?? 'Unknown') ?></td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?= htmlspecialchars($lead['deal_name'] ?? 'Standard Plan') ?></td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?= htmlspecialchars($lead['contact_name'] ?? '') ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    $stageColor = match ($lead['stage']) {
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
                                        <div
    x-show="open"
    @click.away="open=false"
    x-transition
    class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow border border-slate-200 z-50"
>
    <a href="/sales/leads/<?= $lead['id'] ?>"
       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
        <span>View Details</span>
    </a>

    <a href="/sales/leads/<?= $lead['id'] ?>"
       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
        </svg>
        <span>Edit Lead</span>
    </a>

    <a href="/sales/tasks/create?lead_id=<?= $lead['id'] ?>"
       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
        </svg>
        <span>Schedule Call</span>
    </a>
</div>

                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    <!-- Top Performers -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4">Top Performers</h3>
        <div class="space-y-4">
            <?php if (empty($topPerformers)): ?>
                <p class="text-sm text-slate-500 text-center">No data available yet.</p>
            <?php else: ?>
                <?php foreach ($topPerformers as $idx => $performer): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 relative">
                                <?= substr($performer['name'], 0, 1) ?>
                                <?php if ($idx === 0): ?>
                                    <div class="absolute -top-1 -right-1 text-amber-500">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
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


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perfCanvas = document.getElementById('performanceChart');
        const ctx = perfCanvas.getContext('2d');
        const rawLabels = <?= json_encode($chartLabels) ?>;
        const rawData = <?= json_encode($chartValues) ?>;
        const hasLabels = Array.isArray(rawLabels) && rawLabels.length > 0;
        const hasData = Array.isArray(rawData) && rawData.length > 0;
        const labelsPerf = hasLabels ? rawLabels : (function() {
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const res = [];
            for (let i = 6; i >= 0; i--) {
                const d = new Date();
                d.setDate(d.getDate() - i);
                res.push(days[d.getDay()]);
            }
            return res;
        })();
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        display: false
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
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
        const sum = (data || []).reduce((a, b) => a + (Number(b) || 0), 0);
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
                    backgroundColor: (labels.length === 1 && labels[0] === 'No data') ?
                        ['#cbd5e1'] :
                        labels.map(function(l) {
                            const k = l.toLowerCase().replace(/[\s-]+/g, '_');
                            const palette = {
                                website: '#f59e0b',
                                form: '#f59e0b',
                                referral: '#14b8a6',
                                social_media: '#8b5cf6',
                                cold_call: '#3b82f6',
                                events: '#10b981'
                            };
                            return palette[k] || '#cbd5e1';
                        }),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
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
        const funnelLabels = ['New', 'Contacted', 'Demo Done', 'Follow Up', 'Payment', 'Converted', 'Lost'];
        const funnelCounts = [
            <?= (int) ($stats['new'] ?? 0) ?>,
            <?= (int) ($stats['contacted'] ?? 0) ?>,
            <?= (int) ($stats['demo_done'] ?? 0) ?>,
            <?= (int) ($stats['follow_up'] ?? 0) ?>,
            <?= (int) ($stats['payment_pending'] ?? 0) ?>,
            <?= (int) ($stats['converted'] ?? 0) ?>,
            <?= (int) ($stats['lost'] ?? 0) ?>
        ];
        const funnelChart = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: funnelLabels,
                datasets: [{
                    data: funnelCounts,
                    backgroundColor: [
                        'hsl(199, 89%, 48%)', // New
                        'hsl(271, 81%, 56%)', // Contacted
                        'hsl(172, 66%, 50%)', // Demo Done
                        'hsl(45, 93%, 47%)', // Follow Up
                        'hsl(24, 95%, 53%)', // Payment
                        'hsl(142, 71%, 45%)', // Converted
                        'hsl(0, 84%, 60%)' // Lost
                    ],
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(items) {
                                return items[0].label || '';
                            },
                            label: function(item) {
                                return 'count: ' + (item.parsed.x ?? 0);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'hsl(220, 9%, 46%)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'hsl(220, 9%, 46%)'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        if (funnelCounts.every(v => (Number(v) || 0) === 0)) {
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 flex items-center justify-center text-slate-400 text-sm';
            overlay.textContent = 'No data';
            document.getElementById('salesFunnelChart').parentElement.appendChild(overlay);
        }
    });
</script>
