<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Dashboard</h1>
            <p class="text-sm text-gray-500">Real-time insights and performance monitoring</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Export Report
            </button>
            <a href="/master/sales/leads/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Lead
            </a>
        </div>
    </div>

    <!-- Alerts Section -->
    <?php if (!empty($lists['overdue'])): ?>
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">
                    You have <span class="font-bold"><?= count($lists['overdue']) ?> overdue follow-ups</span> requiring immediate attention.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Leads -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Leads</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_leads']) ?></dd>
                            <dd class="text-xs text-green-600 font-medium">+<?= $stats['new_leads_week'] ?> this week</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">₹<?= number_format($stats['total_revenue']) ?></dd>
                            <dd class="text-xs text-gray-500">₹<?= number_format($stats['revenue_month']) ?> this month</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Rate -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Conversion Rate</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= $stats['conversion_rate'] ?>%</dd>
                            <dd class="text-xs text-gray-500"><?= $stats['converted'] ?> converted leads</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Receivables</dt>
                            <dd class="text-2xl font-semibold text-gray-900">₹<?= number_format($stats['pending_payments']) ?></dd>
                            <dd class="text-xs text-red-500">From closed deals</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Funnel Chart -->
        <div class="bg-white p-6 rounded-lg shadow lg:col-span-1">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Lead Funnel</h3>
            <canvas id="funnelChart" height="250"></canvas>
        </div>
        
        <!-- Revenue Trend -->
        <div class="bg-white p-6 rounded-lg shadow lg:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend (30 Days)</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>
    </div>

    <!-- Charts Row 2 & Team -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Lead Source -->
        <div class="bg-white p-6 rounded-lg shadow lg:col-span-1">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Lead Sources</h3>
            <canvas id="sourceChart" height="200"></canvas>
        </div>

        <!-- Team Performance -->
        <div class="bg-white p-6 rounded-lg shadow lg:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Team Performance</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executive</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leads</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($charts['team'] as $member): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($member['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $member['total_leads'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $member['converted_leads'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">₹<?= number_format($member['revenue_generated'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($charts['team'])): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 text-sm">No sales team data available.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Lists Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- High Value Leads -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">High Value Pipeline</h3>
                <a href="/master/sales/leads" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
            </div>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($lists['high_value'] as $lead): ?>
                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="truncate">
                            <p class="text-sm font-medium text-indigo-600 truncate"><?= htmlspecialchars($lead['company_name']) ?></p>
                            <p class="text-xs text-gray-500"><?= ucfirst($lead['stage']) ?></p>
                        </div>
                        <div class="ml-2 flex-shrink-0 flex">
                            <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <?= $lead['currency'] ?> <?= number_format($lead['deal_value']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end">
                        <a href="/master/sales/leads/<?= $lead['id'] ?>" class="text-xs text-gray-400 hover:text-gray-600">View Details &rarr;</a>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if(empty($lists['high_value'])): ?>
                <li class="px-4 py-4 text-center text-gray-500 text-sm">No high value leads found.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Overdue Followups -->
        <div class="bg-white shadow rounded-lg">
             <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Overdue Follow-ups</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <?= count($lists['overdue']) ?> Action Required
                </span>
            </div>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($lists['overdue'] as $lead): ?>
                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="truncate">
                            <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($lead['company_name']) ?></p>
                            <p class="text-xs text-red-500">
                                Due: <?= date('M j, g:i A', strtotime($lead['next_followup_at'])) ?>
                            </p>
                        </div>
                        <div class="ml-2 flex-shrink-0">
                             <?php if($lead['assigned_name']): ?>
                             <span class="text-xs text-gray-500">Assigned: <?= htmlspecialchars($lead['assigned_name']) ?></span>
                             <?php else: ?>
                             <span class="text-xs text-red-400">Unassigned</span>
                             <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end">
                        <a href="/master/sales/leads/<?= $lead['id'] ?>" class="text-xs text-indigo-600 hover:text-indigo-900">Take Action &rarr;</a>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if(empty($lists['overdue'])): ?>
                <li class="px-4 py-4 text-center text-gray-500 text-sm">No overdue follow-ups. Great job!</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Funnel Chart
    const funnelCtx = document.getElementById('funnelChart').getContext('2d');
    new Chart(funnelCtx, {
        type: 'bar',
        data: {
            labels: ['New', 'Contacted', 'Demo', 'Follow Up', 'Payment Pending', 'Converted', 'Lost'],
            datasets: [{
                label: 'Leads',
                data: [
                    <?= $charts['funnel']['new'] ?? 0 ?>,
                    <?= $charts['funnel']['contacted'] ?? 0 ?>,
                    <?= $charts['funnel']['demo_done'] ?? 0 ?>,
                    <?= $charts['funnel']['follow_up'] ?? 0 ?>,
                    <?= $charts['funnel']['payment_pending'] ?? 0 ?>,
                    <?= $charts['funnel']['converted'] ?? 0 ?>,
                    <?= $charts['funnel']['lost'] ?? 0 ?>
                ],
                backgroundColor: [
                    '#3B82F6', '#6366F1', '#8B5CF6', '#F59E0B', '#F97316', '#10B981', '#EF4444'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Revenue Trend
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($charts['revenue_trend']) ?>;
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.date),
            datasets: [{
                label: 'Revenue',
                data: revenueData.map(d => d.total),
                borderColor: '#10B981',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Source Chart
    const sourceCtx = document.getElementById('sourceChart').getContext('2d');
    const sourceData = <?= json_encode($charts['sources']) ?>;
    new Chart(sourceCtx, {
        type: 'doughnut',
        data: {
            labels: sourceData.map(d => d.source.charAt(0).toUpperCase() + d.source.slice(1)),
            datasets: [{
                data: sourceData.map(d => d.count),
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'right' } }
        }
    });
</script>