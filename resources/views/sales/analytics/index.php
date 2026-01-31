<?php $title = 'Sales Analytics'; ?>
<div class="space-y-8" x-data="analyticsDashboard()">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Performance Analytics</h2>
            <p class="text-slate-500">Deep dive into your sales metrics and team performance.</p>
        </div>
        <div class="flex gap-3">
            <select x-model="days" @change="updateCharts" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 shadow-sm">
                <option value="7">Last 7 Days</option>
                <option value="14">Last 14 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl font-medium shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- Main Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Lead Trends -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Lead Acquisition Trends</h3>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">+12.5%</span>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="leadTrendsChart"></canvas>
            </div>
        </div>

        <!-- Team Performance -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
             <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Team Performance</h3>
                <button class="text-slate-400 hover:text-indigo-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg></button>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="teamPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Secondary Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stage Breakdown -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-1">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Pipeline Stages</h3>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="stageBreakdownChart"></canvas>
            </div>
        </div>

        <!-- Revenue Forecast -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Revenue Forecast</h3>
            <div class="relative h-64 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    function analyticsDashboard() {
        return {
            days: '14',
            charts: {},
            
            init() {
                this.$nextTick(() => {
                    this.initCharts();
                });
            },
            
            updateCharts() {
                // Simulate data update
                const multiplier = this.days / 14;
                this.charts.leadTrends.data.datasets[0].data = [12, 19, 3, 5, 2, 3, 15, 20, 25, 18, 12, 19, 15, 22].map(v => Math.floor(v * multiplier));
                this.charts.leadTrends.update();
            },

            initCharts() {
                // Common Options
                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { family: 'Inter' }, color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter' }, color: '#64748b' }
                        }
                    }
                };

                // Lead Trends
                const leadCtx = document.getElementById('leadTrendsChart').getContext('2d');
                const gradientLead = leadCtx.createLinearGradient(0, 0, 0, 400);
                gradientLead.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
                gradientLead.addColorStop(1, 'rgba(79, 70, 229, 0)');

                this.charts.leadTrends = new Chart(leadCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'New Leads',
                            data: [12, 19, 3, 5, 2, 3, 15, 20, 25, 18, 12, 19, 15, 22],
                            borderColor: '#4f46e5',
                            backgroundColor: gradientLead,
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6
                        }]
                    },
                    options: commonOptions
                });

                // Team Performance
                this.charts.teamPerformance = new Chart(document.getElementById('teamPerformanceChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Sarah', 'John', 'Emily', 'Mike', 'Jessica'],
                        datasets: [{
                            label: 'Deals Closed',
                            data: [12, 8, 15, 6, 10],
                            backgroundColor: [
                                '#4f46e5', '#818cf8', '#6366f1', '#a5b4fc', '#4338ca'
                            ],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: { legend: { display: false } }
                    }
                });

                // Stage Breakdown (Doughnut)
                this.charts.stageBreakdown = new Chart(document.getElementById('stageBreakdownChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['New', 'Contacted', 'Proposal', 'Negotiation', 'Closed'],
                        datasets: [{
                            data: [30, 20, 15, 10, 25],
                            backgroundColor: ['#e0e7ff', '#c7d2fe', '#818cf8', '#4f46e5', '#312e81'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                        }
                    }
                });

                // Revenue Forecast
                this.charts.revenue = new Chart(document.getElementById('revenueChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
                        datasets: [
                            {
                                label: 'Projected',
                                data: [65, 59, 80, 81, 56, 95],
                                backgroundColor: '#e2e8f0',
                                borderRadius: 4,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Actual',
                                data: [60, 55, 75, 85, 60, 100],
                                backgroundColor: '#4f46e5',
                                borderRadius: 4,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: commonOptions
                });
            }
        }
    }
</script>
