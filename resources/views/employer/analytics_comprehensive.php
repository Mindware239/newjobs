<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $stats
 * @var array $jobs
 */
?>

<div x-data="analyticsDashboard()" x-init="init()" class="space-y-8 pb-12">
    <!-- Header with Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p class="text-gray-600 mt-1">Comprehensive insights into your hiring process</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button @click="exportReport()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Report
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <select x-model="filters.job_id" @change="loadAllData()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">All Jobs</option>
                <?php foreach ($jobs ?? [] as $job): ?>
                    <option value="<?= $job->id ?>"><?= htmlspecialchars($job->title) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select x-model="filters.timeframe" @change="handleTimeframeChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
                <option value="6m">Last 6 Months</option>
                <option value="1y">Last Year</option>
                <option value="custom">Custom Range</option>
            </select>

            <div x-show="filters.timeframe === 'custom'" class="contents">
                <input type="date" x-model="filters.date_from" @change="loadAllData()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <input type="date" x-model="filters.date_to" @change="loadAllData()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Jobs -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Jobs</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['jobs']['total'] ?? 0 ?></p>
                </div>
                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium"><?= $stats['jobs']['active'] ?? 0 ?></span>
                <span class="text-gray-500 ml-2">active</span>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Applications</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.total || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium" x-text="(funnelData.conversion_rate || 0) + '%'">0%</span>
                <span class="text-gray-500 ml-2">hire rate</span>
            </div>
        </div>

        <!-- Interviews -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interviews</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.stages?.interviewed?.count || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Hired -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Hired</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.stages?.hired?.count || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-green-50 text-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Hiring Funnel -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Hiring Funnel</h2>
                <button @click="loadFunnelData()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Refresh</button>
            </div>
            <div class="relative h-80">
                <canvas id="funnelChart"></canvas>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-6">
                <template x-for="(stage, key) in funnelData.stages" :key="key">
                    <div class="text-center p-2 rounded-lg bg-gray-50" x-show="key !== 'rejected'">
                        <div class="text-lg font-bold text-blue-600" x-text="stage.count || 0"></div>
                        <div class="text-xs text-gray-600 capitalize" x-text="key"></div>
                        <div class="text-xs text-gray-400" x-text="(stage.percentage || 0) + '%'"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Time to Hire -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Time to Hire</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600">Posted to Application</div>
                    <div class="text-2xl font-bold text-blue-700" x-text="(timeToHireData.avg_days_posted_to_application || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg border-l-4 border-indigo-500">
                    <div class="text-sm text-gray-600">Total Time to Hire</div>
                    <div class="text-2xl font-bold text-indigo-700" x-text="(timeToHireData.avg_days_total_time_to_hire || 0) + ' days'"></div>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="timeToHireChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Secondary Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Candidate Sources -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Candidate Sources</h2>
            <div class="relative h-64 flex justify-center">
                <canvas id="sourcesChart"></canvas>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <template x-for="(count, source) in sourcesData.counts" :key="source">
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded" x-show="count > 0">
                        <span class="text-sm text-gray-600 capitalize" x-text="source"></span>
                        <span class="text-sm font-bold text-gray-900" x-text="count"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Interview Outcomes -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Interview Outcomes</h2>
            <div class="relative h-64 flex justify-center">
                <canvas id="outcomesChart"></canvas>
            </div>
            <div class="mt-6 flex justify-center gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" x-text="outcomesData.passed || 0"></div>
                    <div class="text-xs text-gray-500">Passed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600" x-text="outcomesData.failed || 0"></div>
                    <div class="text-xs text-gray-500">Failed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-400" x-text="outcomesData.no_show || 0"></div>
                    <div class="text-xs text-gray-500">No Show</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tertiary Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Location Analytics -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Applications by Location</h2>
            <div class="relative h-64 mb-6">
                <canvas id="locationChart"></canvas>
            </div>
            <div class="overflow-y-auto max-h-48">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2">City</th>
                            <th class="px-4 py-2 text-right">Apps</th>
                            <th class="px-4 py-2 text-right">Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="city in (locationData.top_cities || [])" :key="city.city">
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium" x-text="city.city"></td>
                                <td class="px-4 py-2 text-right" x-text="city.applications"></td>
                                <td class="px-4 py-2 text-right text-green-600" x-text="city.hired"></td>
                            </tr>
                        </template>
                        <tr x-show="!locationData.top_cities?.length">
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500">No location data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Overview -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Activity Overview</h2>
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-gray-800" x-text="activityData.summary?.total_profiles_viewed || 0"></div>
                    <div class="text-xs text-gray-500">Profiles Viewed</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-gray-800" x-text="activityData.summary?.total_resumes_downloaded || 0"></div>
                    <div class="text-xs text-gray-500">Resumes</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-gray-800" x-text="activityData.summary?.days_with_job_creation || 0"></div>
                    <div class="text-xs text-gray-500">Job Created</div>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function analyticsDashboard() {
    return {
        filters: {
            job_id: '',
            date_from: '',
            date_to: '',
            timeframe: '30d',
            location: ''
        },
        funnelData: {},
        timeToHireData: {},
        locationData: {},
        activityData: {},
        sourcesData: {},
        outcomesData: {},
        charts: {
            funnel: null,
            timeToHire: null,
            location: null,
            activity: null,
            sources: null,
            outcomes: null
        },
        chartsRendered: {
            funnel: false,
            timeToHire: false,
            location: false,
            activity: false,
            sources: false,
            outcomes: false
        },

        init() {
            this.handleTimeframeChange();
            // Initial load
            this.loadAllData();
            
            // Handle resize
            window.addEventListener('resize', () => {
                this.resizeCharts();
            });
        },

        handleTimeframeChange() {
            if (this.filters.timeframe === 'custom') return;
            
            const end = new Date();
            const start = new Date();
            
            switch(this.filters.timeframe) {
                case '7d': start.setDate(end.getDate() - 7); break;
                case '30d': start.setDate(end.getDate() - 30); break;
                case '90d': start.setDate(end.getDate() - 90); break;
                case '6m': start.setMonth(end.getMonth() - 6); break;
                case '1y': start.setFullYear(end.getFullYear() - 1); break;
            }
            
            this.filters.date_to = end.toISOString().split('T')[0];
            this.filters.date_from = start.toISOString().split('T')[0];
            this.loadAllData();
        },

        async loadAllData() {
            // Reset rendered states when data reloads
            this.chartsRendered = {
                funnel: false,
                timeToHire: false,
                location: false,
                activity: false,
                sources: false,
                outcomes: false
            };
            
            await Promise.all([
                this.loadFunnelData(),
                this.loadTimeToHire(),
                this.loadLocationData(),
                this.loadActivityData(),
                this.loadSourcesData(),
                this.loadOutcomesData()
            ]);
        },

        async loadFunnelData() {
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    job_id: this.filters.job_id || ''
                });
                const response = await fetch(`/api/employer/analytics/funnel?${params}`);
                this.funnelData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderFunnelChart(), 100);
                });
            } catch (error) {
                console.error('Error loading funnel data:', error);
            }
        },

        renderFunnelChart() {
            if (this.chartsRendered.funnel) return;
            
            const ctx = document.getElementById('funnelChart');
            if (!ctx) return;

            if (this.charts.funnel) {
                this.charts.funnel.destroy();
            }

            const stages = this.funnelData.stages || {};
            this.charts.funnel = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Applied', 'Shortlisted', 'Interviewed', 'Offered', 'Hired'],
                    datasets: [{
                        label: 'Candidates',
                        data: [
                            stages.applied?.count || 0,
                            stages.shortlisted?.count || 0,
                            stages.interviewed?.count || 0,
                            stages.offered?.count || 0,
                            stages.hired?.count || 0
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(99, 102, 241, 0.6)',
                            'rgba(245, 158, 11, 0.6)',
                            'rgba(31, 41, 55, 0.6)'
                        ],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Candidates';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
            this.chartsRendered.funnel = true;
        },

        async loadTimeToHire() {
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    job_id: this.filters.job_id || ''
                });
                const response = await fetch(`/api/employer/analytics/time-to-hire?${params}`);
                this.timeToHireData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderTimeToHireChart(), 100);
                });
            } catch (error) {
                console.error('Error loading time to hire:', error);
            }
        },

        renderTimeToHireChart() {
            if (this.chartsRendered.timeToHire) return;

            const ctx = document.getElementById('timeToHireChart');
            if (!ctx) return;

            if (this.charts.timeToHire) {
                this.charts.timeToHire.destroy();
            }

            this.charts.timeToHire = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Applied', 'Shortlisted', 'Interview', 'Offer', 'Hired'],
                    datasets: [{
                        label: 'Avg Days',
                        data: [
                            0,
                            this.timeToHireData.avg_days_application_to_shortlisted || 0,
                            this.timeToHireData.avg_days_shortlisted_to_interview || 0,
                            this.timeToHireData.avg_days_interview_to_offer || 0,
                            this.timeToHireData.avg_days_offer_to_hire || 0
                        ],
                        borderColor: 'rgba(79, 70, 229, 1)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Days'
                            }
                        }
                    }
                }
            });
            this.chartsRendered.timeToHire = true;
        },

        async loadLocationData() {
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    job_id: this.filters.job_id || ''
                });
                const response = await fetch(`/api/employer/analytics/location?${params}`);
                this.locationData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderLocationChart(), 100);
                });
            } catch (error) {
                console.error('Error loading location data:', error);
            }
        },

        renderLocationChart() {
            if (this.chartsRendered.location) return;

            const ctx = document.getElementById('locationChart');
            if (!ctx) return;

            if (this.charts.location) {
                this.charts.location.destroy();
            }

            const cities = Object.entries(this.locationData.by_city || {})
                .sort((a, b) => b[1].applications - a[1].applications)
                .slice(0, 10);

            this.charts.location = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cities.map(c => c[0]),
                    datasets: [{
                        label: 'Applications',
                        data: cities.map(c => c[1].applications),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            this.chartsRendered.location = true;
        },

        async loadActivityData() {
            try {
                const days = this.filters.timeframe === '7d' ? 7 : this.filters.timeframe === '30d' ? 30 : 90;
                const response = await fetch(`/api/employer/analytics/activity?days=${days}`);
                this.activityData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderActivityChart(), 100);
                });
            } catch (error) {
                console.error('Error loading activity data:', error);
            }
        },

        renderActivityChart() {
            if (this.chartsRendered.activity) return;

            const ctx = document.getElementById('activityChart');
            if (!ctx) return;

            if (this.charts.activity) {
                this.charts.activity.destroy();
            }

            const daily = this.activityData.daily_activity || [];
            
            this.charts.activity = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: daily.map(d => {
                        const date = new Date(d.date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Views',
                        data: daily.map(d => d.profiles_viewed || 0),
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    }, {
                        label: 'Resumes',
                        data: daily.map(d => d.resumes_downloaded || 0),
                        borderColor: 'rgb(16, 185, 129)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
            this.chartsRendered.activity = true;
        },

        async loadSourcesData() {
            try {
                const params = new URLSearchParams({
                    ...this.filters
                });
                const response = await fetch(`/api/employer/analytics/sources?${params}`);
                this.sourcesData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderSourcesChart(), 100);
                });
            } catch (error) {
                console.error('Error loading sources data:', error);
            }
        },

        renderSourcesChart() {
            if (this.chartsRendered.sources) return;

            const ctx = document.getElementById('sourcesChart');
            if (!ctx) return;

            if (this.charts.sources) {
                this.charts.sources.destroy();
            }

            const data = this.sourcesData.counts || {};
            const labels = Object.keys(data).map(k => k.charAt(0).toUpperCase() + k.slice(1));
            const values = Object.values(data);

            this.charts.sources = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(245, 158, 11, 0.6)',
                            'rgba(139, 92, 246, 0.6)',
                            'rgba(107, 114, 128, 0.6)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
            this.chartsRendered.sources = true;
        },

        async loadOutcomesData() {
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    job_id: this.filters.job_id || ''
                });
                const response = await fetch(`/api/employer/analytics/interview-outcomes?${params}`);
                this.outcomesData = await response.json();
                this.$nextTick(() => {
                    setTimeout(() => this.renderOutcomesChart(), 100);
                });
            } catch (error) {
                console.error('Error loading outcomes data:', error);
            }
        },

        renderOutcomesChart() {
            if (this.chartsRendered.outcomes) return;

            const ctx = document.getElementById('outcomesChart');
            if (!ctx) return;

            if (this.charts.outcomes) {
                this.charts.outcomes.destroy();
            }

            this.charts.outcomes = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Passed', 'Failed', 'No Show'],
                    datasets: [{
                        data: [
                            this.outcomesData.passed || 0,
                            this.outcomesData.failed || 0,
                            this.outcomesData.no_show || 0
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(239, 68, 68, 0.6)',
                            'rgba(156, 163, 175, 0.6)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
            this.chartsRendered.outcomes = true;
        },

        exportReport() {
            const params = new URLSearchParams({
                ...this.filters,
                type: 'analytics',
                format: 'csv'
            });
            window.open(`/api/employer/analytics/export?${params}`, '_blank');
        },

        resizeCharts() {
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.resize();
            });
        }
    }
}
</script>
