<?php

/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $stats
 * @var array $jobs
 */
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    [x-cloak] {
        display: none !important;
    }

    canvas {
        max-width: 100% !important;
        height: 100% !important;
        display: block !important;
    }

    #funnelChart,
    #timeToHireChart,
    #locationChart,
    #activityChart {
        width: 100% !important;
        height: 256px !important;
        display: block !important;
    }
</style>

<div x-data="analyticsDashboard()" x-init="init()" class="space-y-8 pb-8">
    <!-- Header with Filters -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p class="text-gray-600 mt-1">Comprehensive insights into your hiring process</p>
            </div>
            <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                <select x-model="filters.job_id" @change="loadAllData()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">All Jobs</option>
                    <?php foreach ($jobs ?? [] as $job): ?>
                        <option value="<?= $job->id ?>"><?= htmlspecialchars($job->title) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" x-model="filters.date_from" @change="updateTimeframe()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <input type="date" x-model="filters.date_to" @change="updateTimeframe()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <select x-model="filters.timeframe" @change="applyTimeframe()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="custom">Custom Range</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d" selected>Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="6m">Last 6 Months</option>
                    <option value="1y">Last Year</option>
                </select>
                <button @click="exportReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div x-show="loading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 mt-2">Loading analytics data...</p>
        </div>
    </div>

    <!-- Interview Outcomes -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Interview Outcomes</h2>
            <span class="text-xs text-gray-500" title="Passed, failed, and no-show counts">?</span>
        </div>
        <div x-show="!outcomesLoaded" class="text-center py-8 text-gray-500">
            <p>Loading interview outcomes...</p>
        </div>
        <div class="space-y-6">
            <div class="h-64">
                <canvas id="outcomesChart"></canvas>
            </div>
            <div x-show="outcomesData.total" class="grid grid-cols-1 md-grid-cols-3 gap-4" x-cloak>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Passed</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="outcomesData.passed || 0"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Failed</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="outcomesData.failed || 0"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">No-show</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="outcomesData.no_show || 0"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offer Acceptance -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Offer Acceptance Rate</h2>
            <span class="text-xs text-gray-500" title="Offers accepted ÷ offers made">?</span>
        </div>
        <div x-show="!acceptanceLoaded" class="text-center py-8 text-gray-500">
            <p>Loading offer acceptance...</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg border border-gray-200">
                <div class="text-xs text-gray-600 mb-1">Offers Made</div>
                <div class="text-2xl font-bold text-gray-900" x-text="acceptanceData.offers_made || 0"></div>
            </div>
            <div class="p-4 rounded-lg border border-gray-200">
                <div class="text-xs text-gray-600 mb-1">Offers Accepted</div>
                <div class="text-2xl font-bold text-gray-900" x-text="acceptanceData.offers_accepted || 0"></div>
            </div>
            <div class="p-4 rounded-lg border border-gray-200">
                <div class="text-xs text-gray-600 mb-1">Acceptance Rate</div>
                <div class="text-2xl font-bold text-gray-900" x-text="(acceptanceData.acceptance_rate || 0) + '%'"></div>
            </div>
        </div>
    </div>

    <!-- Candidate Sources -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Candidate Source Tracking</h2>
            <span class="text-xs text-gray-500" title="Distribution of application sources">?</span>
        </div>
        <div x-show="!sourcesLoaded" class="text-center py-8 text-gray-500">
            <p>Loading sources data...</p>
        </div>
        <div class="space-y-6">
            <div class="h-64">
                <canvas id="sourcesChart"></canvas>
            </div>
            <div x-show="sourcesData.total" class="grid grid-cols-1 md-grid-cols-5 gap-4" x-cloak>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Paid</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="sourcesData.counts?.paid || 0"></div>
                    <div class="text-xs text-gray-500" x-text="(sourcesData.percentages?.paid || 0) + '%'"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Organic</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="sourcesData.counts?.organic || 0"></div>
                    <div class="text-xs text-gray-500" x-text="(sourcesData.percentages?.organic || 0) + '%'"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Referral</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="sourcesData.counts?.referral || 0"></div>
                    <div class="text-xs text-gray-500" x-text="(sourcesData.percentages?.referral || 0) + '%'"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Social</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="sourcesData.counts?.social || 0"></div>
                    <div class="text-xs text-gray-500" x-text="(sourcesData.percentages?.social || 0) + '%'"></div>
                </div>
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-600 mb-1">Other</div>
                    <div class="text-2xl font-bold text-gray-900" x-text="sourcesData.counts?.other || 0"></div>
                    <div class="text-xs text-gray-500" x-text="(sourcesData.percentages?.other || 0) + '%'"></div>
                </div>
            </div>
        </div>
    </div>

   <!-- Job Statistics - Muted Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="/employer/jobs" class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Jobs</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['jobs']['total'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </a>
    <a href="/employer/jobs?status=published" class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Published</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['jobs']['published'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </a>
    <a href="/employer/jobs?status=draft" class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Drafts</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['jobs']['draft'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
        </div>
    </a>
    <a href="/employer/jobs?status=closed" class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Closed</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['jobs']['closed'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </a>
</div>

<!-- Application Statistics - Muted Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Applications -->
    <a href="/employer/applications" 
       class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Applications</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['applications']['total'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2">
                    </path>
                </svg>
            </div>
        </div>
    </a>

    <!-- Pending -->
    <a href="/employer/applications?status=applied" 
       class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['applications']['pending'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </a>

    <!-- Shortlisted -->
    <a href="/employer/applications?status=shortlisted" 
       class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Shortlisted</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['applications']['shortlisted'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </a>

    <!-- Rejected -->
    <a href="/employer/applications?status=rejected" 
       class="p-6 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all cursor-pointer group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rejected</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['applications']['rejected'] ?></p>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </a>

</div>

    <!-- Hiring Funnel Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Hiring Funnel Analytics</h2>
                <p class="text-sm text-gray-600 mt-1">Track applicant journey through each stage</p>
            </div>
            <button @click="loadFunnelData()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Refresh</button>
        </div>
        <div x-show="!funnelData.stages" class="text-center py-8 text-gray-500">
            <p>Loading funnel data...</p>
        </div>
        <div class="space-y-6">
            <div class="h-64 relative" style="min-height: 256px; position: relative;">
                <!-- Canvas is always in DOM, never conditionally rendered -->
                <canvas
                    id="funnelChart"
                    width="800"
                    height="256"
                    style="display: block !important; width: 100% !important; height: 256px !important; position: relative; z-index: 1;">
                </canvas>
                <div x-show="!funnelData.stages" class="absolute inset-0 flex items-center justify-center bg-gray-50" style="z-index: 0; pointer-events: none;">
                    <p class="text-gray-500">Loading chart...</p>
                </div>
            </div>
            <div x-show="funnelData.stages" class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <div class="text-2xl font-bold text-blue-600" x-text="funnelData.stages?.applied?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Applied</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.applied?.percentage || 0) + '%'"></div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                    <div class="text-2xl font-bold text-green-600" x-text="funnelData.stages?.shortlisted?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Shortlisted</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.shortlisted?.percentage || 0) + '%'"></div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                    <div class="text-2xl font-bold text-purple-600" x-text="funnelData.stages?.interviewed?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Interviewed</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.interviewed?.percentage || 0) + '%'"></div>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                    <div class="text-2xl font-bold text-orange-600" x-text="funnelData.stages?.offered?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Offered</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.offered?.percentage || 0) + '%'"></div>
                </div>
                <div class="text-center p-4 bg-emerald-50 rounded-lg border-l-4 border-emerald-500">
                    <div class="text-2xl font-bold text-emerald-600" x-text="funnelData.stages?.hired?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Hired</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.hired?.percentage || 0) + '%'"></div>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                    <div class="text-2xl font-bold text-red-600" x-text="funnelData.stages?.rejected?.count || 0"></div>
                    <div class="text-sm text-gray-600 mt-1">Rejected</div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(funnelData.stages?.rejected?.percentage || 0) + '%'"></div>
                </div>
            </div>
            <div x-show="funnelData.stages" class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Conversion Rate:</span>
                        <span class="font-semibold text-gray-900 ml-2" x-text="(funnelData.conversion_rate || 0) + '%'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Drop-off (Applied→Shortlisted):</span>
                        <span class="font-semibold text-red-600 ml-2" x-text="(funnelData.drop_off_rates?.applied_to_shortlisted || 0) + '%'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Drop-off (Shortlisted→Interview):</span>
                        <span class="font-semibold text-red-600 ml-2" x-text="(funnelData.drop_off_rates?.shortlisted_to_interview || 0) + '%'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Drop-off (Interview→Offer):</span>
                        <span class="font-semibold text-red-600 ml-2" x-text="(funnelData.drop_off_rates?.interview_to_offer || 0) + '%'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time to Hire Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Time to Hire Metrics</h2>
        <div x-show="!timeToHireData.avg_days_total_time_to_hire" class="text-center py-8 text-gray-500">
            <p>Loading time-to-hire data...</p>
        </div>
        <div x-show="timeToHireData.avg_days_total_time_to_hire" class="space-y-6" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600 mb-1">Posted to Application</div>
                    <div class="text-2xl font-bold text-blue-600" x-text="(timeToHireData.avg_days_posted_to_application || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                    <div class="text-sm text-gray-600 mb-1">Application to Shortlisted</div>
                    <div class="text-2xl font-bold text-green-600" x-text="(timeToHireData.avg_days_application_to_shortlisted || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                    <div class="text-sm text-gray-600 mb-1">Shortlisted to Interview</div>
                    <div class="text-2xl font-bold text-purple-600" x-text="(timeToHireData.avg_days_shortlisted_to_interview || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                    <div class="text-sm text-gray-600 mb-1">Interview to Offer</div>
                    <div class="text-2xl font-bold text-orange-600" x-text="(timeToHireData.avg_days_interview_to_offer || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg border-l-4 border-indigo-500">
                    <div class="text-sm text-gray-600 mb-1">Offer to Hire</div>
                    <div class="text-2xl font-bold text-indigo-600" x-text="(timeToHireData.avg_days_offer_to_hire || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-emerald-50 rounded-lg border-l-4 border-emerald-500">
                    <div class="text-sm text-gray-600 mb-1">Total Time to Hire</div>
                    <div class="text-2xl font-bold text-emerald-600" x-text="(timeToHireData.avg_days_total_time_to_hire || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                    <div class="text-sm text-gray-600 mb-1">Longest Open Job</div>
                    <div class="text-2xl font-bold text-red-600" x-text="(timeToHireData.longest_open_job_days || 0) + ' days'"></div>
                </div>
                <div class="p-4 bg-teal-50 rounded-lg border-l-4 border-teal-500">
                    <div class="text-sm text-gray-600 mb-1">Fastest Filled Job</div>
                    <div class="text-2xl font-bold text-teal-600" x-text="(timeToHireData.fastest_filled_job_days || 0) + ' days'"></div>
                </div>
            </div>
            <div class="h-64" style="display: block;">
                <canvas id="timeToHireChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Location Analytics Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Location-Based Analytics</h2>
        <div x-show="!locationData.by_city" class="text-center py-8 text-gray-500">
            <p>Loading location data...</p>
        </div>
        <div class="space-y-6">
            <div class="h-64" style="display: block;">
                <canvas id="locationChart"></canvas>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Top Cities by Applications</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="(data, city) in Object.entries(locationData.by_city || {}).slice(0, 10)" :key="city">
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span class="text-sm font-medium text-gray-900" x-text="city"></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-600" x-text="data[1].applications + ' apps'"></span>
                                    <span class="text-sm text-green-600 font-semibold" x-text="data[1].hired + ' hired'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Top Successful Cities</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="city in (locationData.top_cities || []).slice(0, 10)" :key="city.city">
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <div>
                                    <span class="text-sm font-medium text-gray-900" x-text="city.city"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="city.state + ', ' + city.country"></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-green-600" x-text="city.success_rate + '%'"></div>
                                    <div class="text-xs text-gray-600" x-text="city.hired + '/' + city.applications"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Engagement Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Job Engagement Metrics</h2>
        <div x-show="!engagementData.length" class="text-center py-8 text-gray-500">
            <p>Loading engagement data...</p>
        </div>
        <div x-show="engagementData.length" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saves</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Engagement Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="job in engagementData" :key="job.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="job.title"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="job.views"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="job.saves"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="job.shares"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="job.applications"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="(parseFloat(job.application_rate) || 0).toFixed(2) + '%'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="parseFloat(job.engagement_score) >= 70 ? 'bg-green-100 text-green-800' : parseFloat(job.engagement_score) >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                                    x-text="(parseFloat(job.engagement_score) || 0).toFixed(1)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Candidate Quality Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Candidate Quality Analytics</h2>
        <div x-show="!qualityData.length" class="text-center py-8 text-gray-500">
            <p>Loading quality data...</p>
        </div>
        <div x-show="qualityData.length" class="space-y-4">
            <template x-for="job in qualityData" :key="job.job_id">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-3" x-text="job.job_title"></h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Resume Completeness</div>
                            <div class="text-xl font-bold text-blue-600" x-text="(parseFloat(job.avg_resume_completeness) || 0).toFixed(1) + '%'"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Skill Match</div>
                            <div class="text-xl font-bold text-green-600" x-text="(parseFloat(job.avg_skill_match) || 0).toFixed(1) + '%'"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Interview Score</div>
                            <div class="text-xl font-bold text-purple-600" x-text="(parseFloat(job.avg_interview_score) || 0).toFixed(1)"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Overall Score</div>
                            <div class="text-xl font-bold text-indigo-600" x-text="(parseFloat(job.avg_overall_score) || 0).toFixed(1)"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Communication Analytics Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Communication Analytics</h2>
        <div x-show="!communicationData.messages_sent" class="text-center py-8 text-gray-500">
            <p>Loading communication data...</p>
        </div>
        <div x-show="communicationData.messages_sent" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Messages Sent</div>
                <div class="text-2xl font-bold text-blue-600" x-text="communicationData.messages_sent || 0"></div>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Replies Received</div>
                <div class="text-2xl font-bold text-green-600" x-text="communicationData.replies_received || 0"></div>
            </div>
            <div class="p-4 bg-purple-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Avg Response Time</div>
                <div class="text-2xl font-bold text-purple-600" x-text="(communicationData.avg_response_time_hours || 0).toFixed(1) + ' hrs'"></div>
            </div>
            <div class="p-4 bg-orange-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Interview Invites Sent</div>
                <div class="text-2xl font-bold text-orange-600" x-text="communicationData.interview_invites_sent || 0"></div>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Invites Read</div>
                <div class="text-2xl font-bold text-yellow-600" x-text="communicationData.interview_invites_read || 0"></div>
            </div>
            <div class="p-4 bg-red-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Missed Interviews</div>
                <div class="text-2xl font-bold text-red-600" x-text="communicationData.missed_interviews || 0"></div>
            </div>
        </div>
    </div>

    <!-- Notification Performance Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Notification Performance</h2>
        <div x-show="!notificationData.total_sent" class="text-center py-8 text-gray-500">
            <p>Loading notification data...</p>
        </div>
        <div x-show="notificationData.total_sent" class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Total Sent</div>
                    <div class="text-2xl font-bold text-blue-600" x-text="notificationData.total_sent || 0"></div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Delivered</div>
                    <div class="text-2xl font-bold text-green-600" x-text="notificationData.delivered || 0"></div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(notificationData.delivery_rate || 0) + '% delivery rate'"></div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Opened</div>
                    <div class="text-2xl font-bold text-purple-600" x-text="notificationData.opened || 0"></div>
                    <div class="text-xs text-gray-500 mt-1" x-text="(notificationData.open_rate || 0) + '% open rate'"></div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Failed</div>
                    <div class="text-2xl font-bold text-red-600" x-text="notificationData.failed || 0"></div>
                </div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-2">Reminder Performance</div>
                <div class="flex items-center gap-4">
                    <span class="text-sm">Reminders Sent: <strong x-text="notificationData.reminders_sent || 0"></strong></span>
                    <span class="text-sm">Success Rate: <strong class="text-green-600" x-text="(notificationData.reminder_success_rate || 0) + '%'"></strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Employer Activity Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Employer Activity Tracking</h2>
        <div x-show="!activityData.summary" class="text-center py-8 text-gray-500">
            <p>Loading activity data...</p>
        </div>
        <div x-show="activityData.summary" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Jobs Created This Month</div>
                    <div class="text-2xl font-bold text-blue-600" x-text="activityData.summary?.days_with_job_creation || 0"></div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Profiles Viewed</div>
                    <div class="text-2xl font-bold text-green-600" x-text="activityData.summary?.total_profiles_viewed || 0"></div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Resumes Downloaded</div>
                    <div class="text-2xl font-bold text-purple-600" x-text="activityData.summary?.total_resumes_downloaded || 0"></div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Subscription & ROI Section -->
    <div x-show="subscriptionData.has_subscription" class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Subscription & ROI Analytics</h2>
        <div x-show="!subscriptionData.has_subscription" class="text-center py-8 text-gray-500">
            <p>No active subscription found</p>
        </div>
        <div x-show="subscriptionData.has_subscription" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Plan</div>
                <div class="text-lg font-bold text-blue-600" x-text="subscriptionData.plan_name || 'N/A'"></div>
                <div class="text-xs text-gray-500 mt-1" x-text="'₹' + (subscriptionData.plan_price || 0)"></div>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Job Slots</div>
                <div class="text-lg font-bold text-green-600" x-text="(subscriptionData.job_slots_used || 0) + '/' + (subscriptionData.job_slots_total || 0)"></div>
                <div class="text-xs text-gray-500 mt-1" x-text="(subscriptionData.job_slots_remaining || 0) + ' remaining'"></div>
            </div>
            <div class="p-4 bg-purple-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Cost per Job</div>
                <div class="text-lg font-bold text-purple-600" x-text="'₹' + (subscriptionData.cost_per_job || 0)"></div>
            </div>
            <div class="p-4 bg-orange-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Cost per Hire</div>
                <div class="text-lg font-bold text-orange-600" x-text="'₹' + (subscriptionData.cost_per_hire || 0)"></div>
            </div>
            <div class="p-4 bg-indigo-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Hiring ROI</div>
                <div class="text-lg font-bold text-indigo-600" x-text="(subscriptionData.hiring_roi || 0) + '%'"></div>
            </div>
            <div class="p-4 bg-teal-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Jobs Posted</div>
                <div class="text-lg font-bold text-teal-600" x-text="subscriptionData.jobs_posted || 0"></div>
            </div>
            <div class="p-4 bg-emerald-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Total Hires</div>
                <div class="text-lg font-bold text-emerald-600" x-text="subscriptionData.hires || 0"></div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">Expires At</div>
                <div class="text-lg font-bold text-gray-600" x-text="subscriptionData.expires_at ? new Date(subscriptionData.expires_at).toLocaleDateString() : 'N/A'"></div>
            </div>
        </div>
    </div>

    <!-- Top Performing Jobs (Existing) -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Top Performing Jobs</h2>
        <?php if (empty($stats['top_jobs'])): ?>
            <p class="text-gray-600">No jobs with applications yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stats['top_jobs'] as $job): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors cursor-pointer">
                                        <?= htmlspecialchars($job['title']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="/employer/applications?job_id=<?= $job['id'] ?>" class="text-sm text-gray-900 hover:text-green-600 transition-colors cursor-pointer font-semibold">
                                        <?= $job['applications_count'] ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="/employer/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold transition-colors">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Applications Over Time (Existing) -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Applications Over Time (Last 6 Months)</h2>
        <?php if (empty($stats['applications_by_month'])): ?>
            <p class="text-gray-600">No application data available yet.</p>
        <?php else: ?>
            <div class="h-64 mb-4">
                <canvas id="applicationsOverTimeChart"></canvas>
            </div>
            <div class="space-y-2">
                <?php foreach ($stats['applications_by_month'] as $month => $count): ?>
                    <a href="/employer/applications?month=<?= $month ?>" class="flex items-center hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer group">
                        <div class="w-32 text-sm text-gray-600 group-hover:text-gray-900 font-medium"><?= date('M Y', strtotime($month . '-01')) ?></div>
                        <div class="flex-1 bg-gray-200 rounded-full h-6 mr-4 group-hover:bg-gray-300 transition-colors">
                            <div class="bg-blue-600 h-6 rounded-full group-hover:bg-blue-700 transition-colors" style="width: <?= min(100, ($count / max(1, max($stats['applications_by_month']))) * 100) ?>%"></div>
                        </div>
                        <div class="w-16 text-sm font-medium text-gray-900 text-right group-hover:text-blue-600 transition-colors"><?= $count ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Security & Audit Logs Tab -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Security & Audit Logs</h2>
            <div class="flex gap-2">
                <select x-model="securityFilters.type" @change="loadSecurityLogs()" class="px-3 py-1 text-sm border border-gray-300 rounded">
                    <option value="">All Types</option>
                    <option value="successful">Successful</option>
                    <option value="failed">Failed</option>
                </select>
                <button @click="loadSecurityLogs()" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Refresh</button>
            </div>
        </div>
        <div x-show="!securityLogs.logs" class="text-center py-8 text-gray-500">
            <p>Loading security logs...</p>
        </div>
        <div x-show="securityLogs.logs" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session Duration</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="log in securityLogs.logs" :key="log.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="new Date(log.logged_in_at).toLocaleString()"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="log.ip_address || 'N/A'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="log.login_successful ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    x-text="log.login_successful ? 'Success' : 'Failed'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="log.session_duration_seconds ? Math.round(log.session_duration_seconds / 60) + ' min' : 'N/A'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Ensure function is available globally before Alpine.js initializes
    // Use IIFE to ensure it's defined immediately
    (function() {
        window.analyticsDashboard = function() {
            return {
                loading: false,
                filters: {
                    job_id: '',
                    date_from: '',
                    date_to: '',
                    timeframe: '30d',
                    location: ''
                },
                securityFilters: {
                    type: ''
                },
                funnelData: {
                    stages: null
                },
                timeToHireData: {},
                locationData: {},
                engagementData: [],
                qualityData: [],
                communicationData: {},
                notificationData: {},
                activityData: {},
                subscriptionData: {},
                sourcesData: {},
                outcomesData: {},
                acceptanceData: {},
                outcomesLoaded: false,
                sourcesLoaded: false,
                acceptanceLoaded: false,
                securityLogs: {},
                charts: {},
                chartsRendered: {
                    funnel: false,
                    timeToHire: false,
                    location: false,
                    activity: false,
                    sources: false,
                    outcomes: false
                },

                init() {
                    this.setDefaultDates();
                    this.loadAllData();
                },

                setDefaultDates() {
                    const today = new Date();
                    const thirtyDaysAgo = new Date(today);
                    thirtyDaysAgo.setDate(today.getDate() - 30);
                    this.filters.date_to = today.toISOString().split('T')[0];
                    this.filters.date_from = thirtyDaysAgo.toISOString().split('T')[0];
                },

                applyTimeframe() {
                    const today = new Date();
                    let startDate = new Date();

                    switch (this.filters.timeframe) {
                        case '7d':
                            startDate.setDate(today.getDate() - 7);
                            break;
                        case '30d':
                            startDate.setDate(today.getDate() - 30);
                            break;
                        case '90d':
                            startDate.setDate(today.getDate() - 90);
                            break;
                        case '6m':
                            startDate.setMonth(today.getMonth() - 6);
                            break;
                        case '1y':
                            startDate.setFullYear(today.getFullYear() - 1);
                            break;
                        default:
                            return;
                    }

                    this.filters.date_from = startDate.toISOString().split('T')[0];
                    this.filters.date_to = today.toISOString().split('T')[0];
                    this.loadAllData();
                },

                updateTimeframe() {
                    this.filters.timeframe = 'custom';
                },

                async loadAllData() {
                    this.loading = true;
                    try {
                        await Promise.all([
                            this.loadFunnelData(),
                            this.loadTimeToHire(),
                            this.loadLocationData(),
                            this.loadSourcesData(),
                            this.loadInterviewOutcomes(),
                            this.loadOfferAcceptance(),
                            this.loadEngagementData(),
                            this.loadQualityData(),
                            this.loadCommunicationData(),
                            this.loadNotificationData(),
                            this.loadActivityData(),
                            this.loadSubscriptionROI(),
                            this.loadSecurityLogs()
                        ]);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadFunnelData() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/funnel?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.funnelData = data || {
                            stages: {
                                applied: {
                                    count: 0,
                                    percentage: 0
                                },
                                shortlisted: {
                                    count: 0,
                                    percentage: 0
                                },
                                interviewed: {
                                    count: 0,
                                    percentage: 0
                                },
                                offered: {
                                    count: 0,
                                    percentage: 0
                                },
                                hired: {
                                    count: 0,
                                    percentage: 0
                                },
                                rejected: {
                                    count: 0,
                                    percentage: 0
                                }
                            },
                            drop_off_rates: {},
                            conversion_rate: 0,
                            total: 0
                        };
                        // Render chart after data is set - use $nextTick for Alpine
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.renderFunnelChart();
                            }, 300);
                        });
                    } catch (error) {
                        console.error('Error loading funnel data:', error);
                        this.funnelData = {
                            stages: {
                                applied: {
                                    count: 0,
                                    percentage: 0
                                },
                                shortlisted: {
                                    count: 0,
                                    percentage: 0
                                },
                                interviewed: {
                                    count: 0,
                                    percentage: 0
                                },
                                offered: {
                                    count: 0,
                                    percentage: 0
                                },
                                hired: {
                                    count: 0,
                                    percentage: 0
                                },
                                rejected: {
                                    count: 0,
                                    percentage: 0
                                }
                            },
                            drop_off_rates: {},
                            conversion_rate: 0,
                            total: 0
                        };
                        // Still render chart with empty data
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.renderFunnelChart();
                            }, 300);
                        });
                    }
                },

                renderFunnelChart() {
                    // Use Alpine's $nextTick to ensure DOM is ready
                    this.$nextTick(() => {
                        // Wait for next frame to ensure rendering is complete
                        requestAnimationFrame(() => {
                            this._renderFunnelChartInternal();
                        });
                    });
                },

                _renderFunnelChartInternal() {
                    // Prevent multiple renders
                    if (this.chartsRendered.funnel && this.charts.funnel) {
                        console.log('Chart already rendered, skipping...');
                        return;
                    }

                    // Get canvas element
                    const canvas = document.getElementById('funnelChart');

                    // Null check - canvas must exist
                    if (!canvas) {
                        console.warn('Funnel chart canvas not found, retrying in 200ms...');
                        setTimeout(() => this._renderFunnelChartInternal(), 200);
                        return;
                    }

                    // Verify canvas is connected to DOM
                    if (!canvas.isConnected) {
                        console.warn('Canvas not connected to DOM, retrying...');
                        setTimeout(() => this._renderFunnelChartInternal(), 200);
                        return;
                    }

                    // Check if canvas is visible and has dimensions
                    const rect = canvas.getBoundingClientRect();
                    const style = window.getComputedStyle(canvas);

                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        console.warn('Canvas not visible, retrying...', {
                            width: rect.width,
                            height: rect.height,
                            display: style.display,
                            visibility: style.visibility
                        });
                        setTimeout(() => this._renderFunnelChartInternal(), 200);
                        return;
                    }

                    // CRITICAL: Test getContext BEFORE passing to Chart.js
                    let testContext;
                    try {
                        testContext = canvas.getContext('2d');
                    } catch (e) {
                        console.error('Cannot get 2D context from canvas:', e);
                        return;
                    }

                    if (!testContext) {
                        console.error('Canvas context is null');
                        return;
                    }

                    // If chart already rendered, don't re-render
                    if (this.chartsRendered.funnel && this.charts.funnel) {
                        console.log('Funnel chart already rendered, skipping...');
                        return;
                    }

                    // Only destroy if chart exists but wasn't marked as rendered
                    if (this.charts.funnel) {
                        try {
                            this.charts.funnel.destroy();
                        } catch (e) {
                            console.warn('Error destroying existing chart:', e);
                        }
                        this.charts.funnel = null;
                    }

                    // Small delay to ensure cleanup is complete
                    setTimeout(() => {
                        // Re-verify canvas still exists and is valid
                        const canvasCheck = document.getElementById('funnelChart');
                        if (!canvasCheck || !canvasCheck.isConnected) {
                            console.error('Canvas disappeared during cleanup');
                            return;
                        }

                        // Final context check
                        try {
                            const finalContext = canvasCheck.getContext('2d');
                            if (!finalContext) {
                                console.error('Cannot get context after cleanup');
                                return;
                            }
                        } catch (e) {
                            console.error('Error getting context after cleanup:', e);
                            return;
                        }

                        try {
                            // Always render, even with empty data
                            const stages = this.funnelData?.stages || {
                                applied: {
                                    count: 0
                                },
                                shortlisted: {
                                    count: 0
                                },
                                interviewed: {
                                    count: 0
                                },
                                offered: {
                                    count: 0
                                },
                                hired: {
                                    count: 0
                                }
                            };

                            console.log('Rendering funnel chart with data:', stages);

                            // Now safe to create Chart - canvas is validated
                            this.charts.funnel = new Chart(canvasCheck, {
                                type: 'bar',
                                data: {
                                    labels: ['Applied', 'Shortlisted', 'Interviewed', 'Offered', 'Hired'],
                                    datasets: [{
                                        label: 'Count',
                                        data: [
                                            stages.applied?.count || 0,
                                            stages.shortlisted?.count || 0,
                                            stages.interviewed?.count || 0,
                                            stages.offered?.count || 0,
                                            stages.hired?.count || 0
                                        ],
                                        backgroundColor: [
                                            'rgba(59, 130, 246, 0.7)',
                                            'rgba(34, 197, 94, 0.7)',
                                            'rgba(168, 85, 247, 0.7)',
                                            'rgba(249, 115, 22, 0.7)',
                                            'rgba(16, 185, 129, 0.7)'
                                        ],
                                        borderColor: [
                                            'rgb(59, 130, 246)',
                                            'rgb(34, 197, 94)',
                                            'rgb(168, 85, 247)',
                                            'rgb(249, 115, 22)',
                                            'rgb(16, 185, 129)'
                                        ],
                                        borderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return context.parsed.y + ' candidates';
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                            console.log('Funnel chart rendered successfully');
                            this.chartsRendered.funnel = true; // Mark as rendered - prevents future re-renders
                        } catch (error) {
                            console.error('Error rendering funnel chart:', error);
                            this.chartsRendered.funnel = false; // Mark as not rendered on error
                        }
                    }, 50);
                },

                async loadTimeToHire() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/time-to-hire?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.timeToHireData = data || {
                            avg_days_posted_to_application: 0,
                            avg_days_application_to_shortlisted: 0,
                            avg_days_shortlisted_to_interview: 0,
                            avg_days_interview_to_offer: 0,
                            avg_days_offer_to_hire: 0,
                            avg_days_total_time_to_hire: 0,
                            longest_open_job_days: 0,
                            fastest_filled_job_days: 0
                        };
                        this.$nextTick(() => {
                            setTimeout(() => this.renderTimeToHireChart(), 200);
                        });
                    } catch (error) {
                        console.error('Error loading time to hire:', error);
                        this.timeToHireData = {
                            avg_days_posted_to_application: 0,
                            avg_days_application_to_shortlisted: 0,
                            avg_days_shortlisted_to_interview: 0,
                            avg_days_interview_to_offer: 0,
                            avg_days_offer_to_hire: 0,
                            avg_days_total_time_to_hire: 0,
                            longest_open_job_days: 0,
                            fastest_filled_job_days: 0
                        };
                    }
                },

                renderTimeToHireChart() {
                    // Prevent multiple renders
                    if (this.chartsRendered.timeToHire && this.charts.timeToHire) {
                        console.log('Time to hire chart already rendered, skipping...');
                        return;
                    }

                    const ctx = document.getElementById('timeToHireChart');
                    if (!ctx) {
                        console.warn('Time to hire chart canvas not found, retrying...');
                        setTimeout(() => this.renderTimeToHireChart(), 100);
                        return;
                    }

                    if (!ctx.isConnected) {
                        console.warn('Time to hire canvas not connected, retrying...');
                        setTimeout(() => this.renderTimeToHireChart(), 100);
                        return;
                    }

                    if (!this.timeToHireData || this.timeToHireData.avg_days_total_time_to_hire === undefined) {
                        return;
                    }

                    const rect = ctx.getBoundingClientRect();
                    const style = window.getComputedStyle(ctx);
                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        setTimeout(() => this.renderTimeToHireChart(), 100);
                        return;
                    }

                    // Only destroy if not already rendered
                    if (this.charts.timeToHire && !this.chartsRendered.timeToHire) {
                        try {
                            this.charts.timeToHire.destroy();
                        } catch (e) {
                            console.warn('Error destroying time to hire chart:', e);
                        }
                        this.charts.timeToHire = null;
                    }

                    // If already rendered, don't re-render
                    if (this.chartsRendered.timeToHire && this.charts.timeToHire) {
                        return;
                    }

                    setTimeout(() => {
                        try {
                            const canvasCheck = document.getElementById('timeToHireChart');
                            if (!canvasCheck || !canvasCheck.isConnected) {
                                console.error('Time to hire canvas disappeared before render');
                                return;
                            }
                            let finalContext;
                            try {
                                finalContext = canvasCheck.getContext('2d');
                            } catch (e) {
                                console.error('Error getting time to hire canvas context:', e);
                                return;
                            }
                            if (!finalContext) {
                                console.error('Cannot get 2d context for time to hire chart');
                                return;
                            }
                            this.charts.timeToHire = new Chart(canvasCheck, {
                                type: 'line',
                                data: {
                                    labels: ['Posted→App', 'App→Short', 'Short→Int', 'Int→Offer', 'Offer→Hire', 'Total'],
                                    datasets: [{
                                        label: 'Days',
                                        data: [
                                            this.timeToHireData.avg_days_posted_to_application || 0,
                                            this.timeToHireData.avg_days_application_to_shortlisted || 0,
                                            this.timeToHireData.avg_days_shortlisted_to_interview || 0,
                                            this.timeToHireData.avg_days_interview_to_offer || 0,
                                            this.timeToHireData.avg_days_offer_to_hire || 0,
                                            this.timeToHireData.avg_days_total_time_to_hire || 0
                                        ],
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true
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
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                            this.chartsRendered.timeToHire = true; // Mark as rendered
                        } catch (error) {
                            console.error('Error rendering time to hire chart:', error);
                            this.chartsRendered.timeToHire = false;
                        }
                    }, 100);
                },

                async loadLocationData() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/location?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.locationData = data || {
                            by_city: {},
                            by_state: {},
                            by_country: {},
                            top_cities: []
                        };
                        this.$nextTick(() => {
                            setTimeout(() => this.renderLocationChart(), 200);
                        });
                    } catch (error) {
                        console.error('Error loading location data:', error);
                        this.locationData = {
                            by_city: {},
                            by_state: {},
                            by_country: {},
                            top_cities: []
                        };
                    }
                },

                async loadSourcesData() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters
                        });
                        const response = await fetch(`/api/employer/analytics/sources?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.sourcesData = data || { counts: {}, percentages: {}, total: 0 };
                        this.$nextTick(() => setTimeout(() => this.renderSourcesChart(), 200));
                        this.sourcesLoaded = true;
                    } catch (error) {
                        console.error('Error loading sources data:', error);
                        this.sourcesData = { counts: {}, percentages: {}, total: 0 };
                        this.sourcesLoaded = true;
                    }
                },

                renderSourcesChart() {
                    const ctx = document.getElementById('sourcesChart');
                    if (!ctx) return;
                    if (!ctx.isConnected) {
                        setTimeout(() => this.renderSourcesChart(), 100);
                        return;
                    }
                    const rect = ctx.getBoundingClientRect();
                    const style = window.getComputedStyle(ctx);
                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        setTimeout(() => this.renderSourcesChart(), 100);
                        return;
                    }
                    if (this.charts.sources) {
                        try { this.charts.sources.destroy(); } catch(e) {}
                        this.charts.sources = null;
                    }
                    const canvasCheck = document.getElementById('sourcesChart');
                    if (!canvasCheck || !canvasCheck.isConnected) return;
                    let finalContext;
                    try { finalContext = canvasCheck.getContext('2d'); } catch(e) { finalContext = null; }
                    if (!finalContext) {
                        setTimeout(() => this.renderSourcesChart(), 100);
                        return;
                    }
                    const labels = ['Paid','Organic','Referral','Social','Other'];
                    const dataVals = [
                        this.sourcesData.counts?.paid || 0,
                        this.sourcesData.counts?.organic || 0,
                        this.sourcesData.counts?.referral || 0,
                        this.sourcesData.counts?.social || 0,
                        this.sourcesData.counts?.other || 0
                    ];
                    this.charts.sources = new Chart(canvasCheck, {
                        type: 'pie',
                        data: {
                            labels,
                            datasets: [{
                                data: dataVals,
                                backgroundColor: [
                                    'rgba(31, 41, 55, 0.25)',
                                    'rgba(99, 102, 241, 0.25)',
                                    'rgba(34, 197, 94, 0.25)',
                                    'rgba(168, 85, 247, 0.25)',
                                    'rgba(107, 114, 128, 0.25)'
                                ],
                                borderColor: 'rgba(0,0,0,0.05)',
                                borderWidth: 1
                            }]
                        },
                        options: { plugins: { legend: { position: 'bottom' } } }
                    });
                    this.chartsRendered.sources = true;
                },

                async loadInterviewOutcomes() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/interview-outcomes?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.outcomesData = data || { passed: 0, failed: 0, no_show: 0, total: 0 };
                        this.$nextTick(() => setTimeout(() => this.renderOutcomesChart(), 200));
                        this.outcomesLoaded = true;
                    } catch (error) {
                        console.error('Error loading interview outcomes:', error);
                        this.outcomesData = { passed: 0, failed: 0, no_show: 0, total: 0 };
                        this.outcomesLoaded = true;
                    }
                },

                renderOutcomesChart() {
                    const ctx = document.getElementById('outcomesChart');
                    if (!ctx) return;
                    if (!ctx.isConnected) {
                        setTimeout(() => this.renderOutcomesChart(), 100);
                        return;
                    }
                    const rect = ctx.getBoundingClientRect();
                    const style = window.getComputedStyle(ctx);
                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        setTimeout(() => this.renderOutcomesChart(), 100);
                        return;
                    }
                    if (this.charts.outcomes) {
                        try { this.charts.outcomes.destroy(); } catch(e) {}
                        this.charts.outcomes = null;
                    }
                    const canvasCheck = document.getElementById('outcomesChart');
                    if (!canvasCheck || !canvasCheck.isConnected) return;
                    let finalContext;
                    try { finalContext = canvasCheck.getContext('2d'); } catch(e) { finalContext = null; }
                    if (!finalContext) {
                        setTimeout(() => this.renderOutcomesChart(), 100);
                        return;
                    }
                    this.charts.outcomes = new Chart(canvasCheck, {
                        type: 'bar',
                        data: {
                            labels: ['Passed','Failed','No-show'],
                            datasets: [{
                                label: 'Count',
                                data: [
                                    this.outcomesData.passed || 0,
                                    this.outcomesData.failed || 0,
                                    this.outcomesData.no_show || 0
                                ],
                                backgroundColor: 'rgba(59, 130, 246, 0.3)',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                    this.chartsRendered.outcomes = true;
                },

                async loadOfferAcceptance() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/offer-acceptance?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.acceptanceData = data || { offers_made: 0, offers_accepted: 0, acceptance_rate: 0 };
                        this.acceptanceLoaded = true;
                    } catch (error) {
                        console.error('Error loading offer acceptance:', error);
                        this.acceptanceData = { offers_made: 0, offers_accepted: 0, acceptance_rate: 0 };
                        this.acceptanceLoaded = true;
                    }
                },

                renderLocationChart() {
                    // Prevent multiple renders
                    if (this.chartsRendered.location && this.charts.location) {
                        console.log('Location chart already rendered, skipping...');
                        return;
                    }

                    const ctx = document.getElementById('locationChart');
                    if (!ctx) {
                        console.warn('Location chart canvas not found, retrying...');
                        setTimeout(() => this.renderLocationChart(), 100);
                        return;
                    }

                    if (!ctx.isConnected) {
                        console.warn('Location canvas not connected, retrying...');
                        setTimeout(() => this.renderLocationChart(), 100);
                        return;
                    }

                    if (!this.locationData || !this.locationData.by_city || Object.keys(this.locationData.by_city).length === 0) {
                        return;
                    }

                    const rect = ctx.getBoundingClientRect();
                    const style = window.getComputedStyle(ctx);
                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        setTimeout(() => this.renderLocationChart(), 100);
                        return;
                    }

                    // Only destroy if not already rendered
                    if (this.charts.location && !this.chartsRendered.location) {
                        try {
                            this.charts.location.destroy();
                        } catch (e) {
                            console.warn('Error destroying location chart:', e);
                        }
                        this.charts.location = null;
                    }

                    // If already rendered, don't re-render
                    if (this.chartsRendered.location && this.charts.location) {
                        return;
                    }

                    setTimeout(() => {
                        try {
                            const canvasCheck = document.getElementById('locationChart');
                            if (!canvasCheck || !canvasCheck.isConnected) {
                                console.error('Location canvas disappeared before render');
                                return;
                            }
                            let finalContext;
                            try {
                                finalContext = canvasCheck.getContext('2d');
                            } catch (e) {
                                console.error('Error getting location canvas context:', e);
                                return;
                            }
                            if (!finalContext) {
                                console.error('Cannot get 2d context for location chart');
                                return;
                            }
                            const cities = Object.entries(this.locationData.by_city || {})
                                .sort((a, b) => (b[1]?.applications || 0) - (a[1]?.applications || 0))
                                .slice(0, 10);

                            this.charts.location = new Chart(canvasCheck, {
                                type: 'bar',
                                data: {
                                    labels: cities.map(c => c[0]),
                                    datasets: [{
                                        label: 'Applications',
                                        data: cities.map(c => c[1].applications),
                                        backgroundColor: 'rgba(59, 130, 246, 0.7)'
                                    }, {
                                        label: 'Hired',
                                        data: cities.map(c => c[1].hired),
                                        backgroundColor: 'rgba(34, 197, 94, 0.7)'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                            this.chartsRendered.location = true; // Mark as rendered
                        } catch (error) {
                            console.error('Error rendering location chart:', error);
                            this.chartsRendered.location = false;
                        }
                    }, 100);
                },

                async loadEngagementData() {
                    try {
                        const params = new URLSearchParams({
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/job-engagement?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.engagementData = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading engagement data:', error);
                        this.engagementData = [];
                    }
                },

                async loadQualityData() {
                    try {
                        const params = new URLSearchParams({
                            job_id: this.filters.job_id || ''
                        });
                        const response = await fetch(`/api/employer/analytics/candidate-quality?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.qualityData = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading quality data:', error);
                        this.qualityData = [];
                    }
                },

                async loadCommunicationData() {
                    try {
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch(`/api/employer/analytics/communication?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.communicationData = data || {
                            messages_sent: 0,
                            replies_received: 0,
                            avg_response_time_hours: 0,
                            interview_invites_sent: 0,
                            interview_invites_read: 0,
                            missed_interviews: 0
                        };
                    } catch (error) {
                        console.error('Error loading communication data:', error);
                        this.communicationData = {
                            messages_sent: 0,
                            replies_received: 0,
                            avg_response_time_hours: 0,
                            interview_invites_sent: 0,
                            interview_invites_read: 0,
                            missed_interviews: 0
                        };
                    }
                },

                async loadNotificationData() {
                    try {
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch(`/api/employer/analytics/notifications?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.notificationData = data || {
                            total_sent: 0,
                            delivered: 0,
                            opened: 0,
                            failed: 0,
                            delivery_rate: 0,
                            open_rate: 0,
                            reminders_sent: 0,
                            reminder_success_rate: 0
                        };
                    } catch (error) {
                        console.error('Error loading notification data:', error);
                        this.notificationData = {
                            total_sent: 0,
                            delivered: 0,
                            opened: 0,
                            failed: 0,
                            delivery_rate: 0,
                            open_rate: 0,
                            reminders_sent: 0,
                            reminder_success_rate: 0
                        };
                    }
                },

                async loadActivityData() {
                    try {
                        const days = this.filters.timeframe === '7d' ? 7 : this.filters.timeframe === '30d' ? 30 : this.filters.timeframe === '90d' ? 90 : 30;
                        const response = await fetch(`/api/employer/analytics/activity?days=${days}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.activityData = data || {
                            daily_activity: [],
                            summary: {
                                days_with_job_creation: 0,
                                total_profiles_viewed: 0,
                                total_resumes_downloaded: 0,
                                first_action: null,
                                last_action: null
                            }
                        };
                        this.$nextTick(() => {
                            setTimeout(() => this.renderActivityChart(), 200);
                        });
                    } catch (error) {
                        console.error('Error loading activity data:', error);
                        this.activityData = {
                            daily_activity: [],
                            summary: {
                                days_with_job_creation: 0,
                                total_profiles_viewed: 0,
                                total_resumes_downloaded: 0,
                                first_action: null,
                                last_action: null
                            }
                        };
                    }
                },

                renderActivityChart() {
                    // Prevent multiple renders
                    if (this.chartsRendered.activity && this.charts.activity) {
                        console.log('Activity chart already rendered, skipping...');
                        return;
                    }

                    const ctx = document.getElementById('activityChart');
                    if (!ctx) {
                        console.warn('Activity chart canvas not found, retrying...');
                        setTimeout(() => this.renderActivityChart(), 100);
                        return;
                    }

                    if (!ctx.isConnected) {
                        console.warn('Activity canvas not connected, retrying...');
                        setTimeout(() => this.renderActivityChart(), 100);
                        return;
                    }

                    if (!this.activityData || !this.activityData.daily_activity || this.activityData.daily_activity.length === 0) {
                        return;
                    }

                    const rect = ctx.getBoundingClientRect();
                    const style = window.getComputedStyle(ctx);
                    if (rect.width === 0 || rect.height === 0 || style.display === 'none' || style.visibility === 'hidden') {
                        setTimeout(() => this.renderActivityChart(), 100);
                        return;
                    }

                    // Only destroy if not already rendered
                    if (this.charts.activity && !this.chartsRendered.activity) {
                        try {
                            this.charts.activity.destroy();
                        } catch (e) {
                            console.warn('Error destroying activity chart:', e);
                        }
                        this.charts.activity = null;
                    }

                    // If already rendered, don't re-render
                    if (this.chartsRendered.activity && this.charts.activity) {
                        return;
                    }

                    setTimeout(() => {
                        try {
                            const canvasCheck = document.getElementById('activityChart');
                            if (!canvasCheck || !canvasCheck.isConnected) {
                                console.error('Activity canvas disappeared before render');
                                return;
                            }
                            let finalContext;
                            try {
                                finalContext = canvasCheck.getContext('2d');
                            } catch (e) {
                                console.error('Error getting activity canvas context:', e);
                                return;
                            }
                            if (!finalContext) {
                                console.error('Cannot get 2d context for activity chart');
                                return;
                            }
                            const daily = this.activityData.daily_activity || [];
                            this.charts.activity = new Chart(canvasCheck, {
                                type: 'line',
                                data: {
                                    labels: daily.map(d => new Date(d.date).toLocaleDateString()),
                                    datasets: [{
                                        label: 'Jobs Created',
                                        data: daily.map(d => d.jobs_created || 0),
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4
                                    }, {
                                        label: 'Profiles Viewed',
                                        data: daily.map(d => d.profiles_viewed || 0),
                                        borderColor: 'rgb(34, 197, 94)',
                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                        tension: 0.4
                                    }, {
                                        label: 'Resumes Downloaded',
                                        data: daily.map(d => d.resumes_downloaded || 0),
                                        borderColor: 'rgb(168, 85, 247)',
                                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                            this.chartsRendered.activity = true; // Mark as rendered
                        } catch (error) {
                            console.error('Error rendering activity chart:', error);
                            this.chartsRendered.activity = false;
                        }
                    }, 100);
                },

                async loadSubscriptionROI() {
                    try {
                        const response = await fetch('/api/employer/analytics/subscription-roi');
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.subscriptionData = data || {
                            has_subscription: false,
                            message: 'No active subscription found'
                        };
                    } catch (error) {
                        console.error('Error loading subscription data:', error);
                        this.subscriptionData = {
                            has_subscription: false,
                            message: 'No active subscription found'
                        };
                    }
                },

                async loadSecurityLogs() {
                    try {
                        const params = new URLSearchParams({
                            ...this.filters,
                            type: this.securityFilters.type || ''
                        });
                        const response = await fetch(`/api/employer/analytics/security-logs?${params}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        this.securityLogs = data || {
                            logs: [],
                            pagination: {
                                page: 1,
                                per_page: 50,
                                total: 0,
                                total_pages: 0
                            }
                        };
                    } catch (error) {
                        console.error('Error loading security logs:', error);
                        this.securityLogs = {
                            logs: [],
                            pagination: {
                                page: 1,
                                per_page: 50,
                                total: 0,
                                total_pages: 0
                            }
                        };
                    }
                },

                exportReport() {
                    const params = new URLSearchParams({
                        ...this.filters,
                        type: 'analytics',
                        format: 'csv'
                    });
                    window.open(`/api/employer/analytics/export?${params}`, '_blank');
                }
            };
        };
    })(); // Immediately invoke to define the function globally
</script>

<?php if (!empty($stats['applications_by_month'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var canvas = document.getElementById('applicationsOverTimeChart');
            if (!canvas || typeof canvas.getContext !== 'function') {
                console.warn('applicationsOverTimeChart not found or no context, skipping');
                return;
            }
            var months = <?= json_encode(array_keys($stats['applications_by_month'])) ?>;
            var counts = <?= json_encode(array_values($stats['applications_by_month'])) ?>;
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: months.map(function(m) {
                        var date = new Date(m + '-01');
                        return date.toLocaleDateString('en-US', {
                            month: 'short',
                            year: 'numeric'
                        });
                    }),
                    datasets: [{
                        label: 'Applications',
                        data: counts,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
<?php endif; ?>
