<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $stats
 * @var array $jobs
 */
?>

<div x-data="analyticsDashboard()" x-init="init()" class="space-y-6 pb-12">
    <!-- Header with Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p class="text-gray-600 mt-1">Comprehensive insights into your hiring process and performance</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button @click="exportReport()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium">
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
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow h-full">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Jobs</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['jobs']['total'] ?? 0 ?></p>
                </div>
                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium"><?= $stats['jobs']['active'] ?? 0 ?></span>
                <span class="text-gray-500 ml-2">active</span>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow h-full">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Applications</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.total || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium" x-text="(funnelData.conversion_rate || 0) + '%'">0%</span>
                <span class="text-gray-500 ml-2">hire rate</span>
            </div>
        </div>

        <!-- Interviews -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow h-full">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interviews</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.stages?.interviewed?.count || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Hired -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow h-full">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Hired</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1" x-text="funnelData.stages?.hired?.count || 0">0</p>
                </div>
                <div class="p-3 rounded-full bg-green-50 text-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Recruitment Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Hiring Funnel -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 h-full">
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

        <!-- Time to Hire & Offer Acceptance -->
        <div class="flex flex-col gap-6 h-full">
            <!-- Time to Hire -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex-1">
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
                <div class="relative h-48">
                    <canvas id="timeToHireChart"></canvas>
                </div>
            </div>
            
            <!-- Offer Acceptance -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Offer Acceptance</h2>
                    <span class="text-2xl font-bold text-green-600" x-text="(offerData.acceptance_rate || 0) + '%'">0%</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <div class="text-sm text-gray-500">Offers Made</div>
                        <div class="text-xl font-bold text-gray-900" x-text="offerData.offers_made || 0">0</div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg text-center">
                        <div class="text-sm text-green-600">Offers Accepted</div>
                        <div class="text-xl font-bold text-green-700" x-text="offerData.offers_accepted || 0">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Candidate & Job Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Candidate Sources -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 h-full">
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

        <!-- Location Analytics -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 h-full">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Applications by Location</h2>
            <div class="relative h-64 mb-6">
                <canvas id="locationChart"></canvas>
            </div>
            <div class="overflow-y-auto max-h-48 border-t border-gray-100 pt-4">
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
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Interview Outcomes</h2>
            <span class="text-xs text-gray-500" title="Passed, failed, and no-show counts">?</span>
        </div>
        <div x-show="!outcomesLoaded" class="text-center py-8 text-gray-500">
            <p>Loading interview outcomes...</p>
        </div>
        <div class="space-y-6">
            <div class="relative h-64">
                <canvas id="outcomesChart"></canvas>
            </div>
            <div x-show="outcomesData.total" class="grid grid-cols-1 md:grid-cols-3 gap-4" x-cloak>
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

    <!-- Section 3: Detailed Job Analysis -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Job Engagement & Quality -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Job Performance & Quality</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Job Title</th>
                            <th class="px-4 py-3 text-center">Views</th>
                            <th class="px-4 py-3 text-center">Apps</th>
                            <th class="px-4 py-3 text-center">Conversion</th>
                            <th class="px-4 py-3 text-center">Resume Score</th>
                            <th class="px-4 py-3 text-center">Skill Match</th>
                            <th class="px-4 py-3 text-center">Engagement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(job, index) in jobEngagementData" :key="index">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="job.title"></td>
                                <td class="px-4 py-3 text-center" x-text="job.views || 0"></td>
                                <td class="px-4 py-3 text-center" x-text="job.applications || 0"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium" 
                                          :class="(job.application_rate || 0) > 10 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                          x-text="Math.round(job.application_rate || 0) + '%'">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center" x-text="getQualityScore(job.id, 'resume') + '%'"></td>
                                <td class="px-4 py-3 text-center" x-text="getQualityScore(job.id, 'skill') + '%'"></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" :style="'width: ' + (job.engagement_score || 0) + '%'"></div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!jobEngagementData.length">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No job engagement data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 4: Communication & Notifications -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Communication Stats -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Communication Effectiveness</h2>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600">Messages Sent</div>
                    <div class="text-2xl font-bold text-blue-700" x-text="communicationData.messages_sent || 0">0</div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600">Replies Received</div>
                    <div class="text-2xl font-bold text-green-700" x-text="communicationData.replies_received || 0">0</div>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <div class="text-sm text-gray-600">Avg Response Time</div>
                    <div class="text-2xl font-bold text-yellow-700" x-text="(communicationData.avg_response_time_hours || 0) + ' hrs'">0 hrs</div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-gray-600">Missed Interviews</div>
                    <div class="text-2xl font-bold text-red-700" x-text="communicationData.missed_interviews || 0">0</div>
                </div>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span class="text-gray-700 font-medium">Interview Invites Read Rate</span>
                <span class="text-blue-600 font-bold" x-text="calculateReadRate() + '%'">0%</span>
            </div>
        </div>

        <!-- Notification Performance -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Notification System</h2>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-3 border border-gray-100 rounded-lg">
                    <div class="text-2xl font-bold text-gray-800" x-text="notificationData.total_sent || 0">0</div>
                    <div class="text-xs text-gray-500">Total Sent</div>
                </div>
                <div class="text-center p-3 border border-gray-100 rounded-lg">
                    <div class="text-2xl font-bold text-green-600" x-text="(notificationData.delivery_rate || 0) + '%'">0%</div>
                    <div class="text-xs text-gray-500">Delivery Rate</div>
                </div>
                <div class="text-center p-3 border border-gray-100 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600" x-text="(notificationData.open_rate || 0) + '%'">0%</div>
                    <div class="text-xs text-gray-500">Open Rate</div>
                </div>
                <div class="text-center p-3 border border-gray-100 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600" x-text="(notificationData.reminder_success_rate || 0) + '%'">0%</div>
                    <div class="text-xs text-gray-500">Reminder Success</div>
                </div>
            </div>
            <div class="relative h-40">
                <canvas id="notificationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Section 5: Activity & Security -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

        <!-- Security & System -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">System & Security</h2>
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Subscription ROI</h3>
                <div class="p-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-100 text-sm">Cost Per Hire</p>
                            <p class="text-2xl font-bold" x-text="'$' + (subscriptionData.cost_per_hire || 0)">$0</p>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-100 text-sm">Value Provided</p>
                            <p class="text-2xl font-bold" x-text="'$' + (subscriptionData.value_provided || 0)">$0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Recent Security Events</h3>
                <div class="overflow-y-auto h-40 border border-gray-100 rounded-lg">
                    <table class="min-w-full text-xs text-left">
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="log in (securityLogs.logs || [])" :key="log.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-900" x-text="log.action"></td>
                                    <td class="px-3 py-2 text-gray-500 text-right" x-text="new Date(log.created_at).toLocaleDateString()"></td>
                                </tr>
                            </template>
                            <tr x-show="!securityLogs.logs?.length">
                                <td colspan="2" class="px-3 py-4 text-center text-gray-500">No security logs available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
        outcomesData: {}, // Kept for future use if needed
        outcomesLoaded: false,
        jobEngagementData: [],
        candidateQualityData: [],
        communicationData: {},
        notificationData: {},
        subscriptionData: {},
        securityLogs: {},
        offerData: {},
        acceptanceLoaded: false,
        charts: {
            funnel: null,
            timeToHire: null,
            location: null,
            activity: null,
            sources: null,
            outcomes: null,
            notification: null
        },
        chartsRendered: {
            funnel: false,
            timeToHire: false,
            location: false,
            activity: false,
            sources: false,
            outcomes: false,
            notification: false
        },

        init() {
            this.handleTimeframeChange();
            this.loadAllData();
            
            window.addEventListener('resize', () => {
                this.resizeCharts();
            });
        },

        handleTimeframeChange() {
            const today = new Date();
            let from = new Date();
            
            switch(this.filters.timeframe) {
                case '7d': from.setDate(today.getDate() - 7); break;
                case '30d': from.setDate(today.getDate() - 30); break;
                case '90d': from.setDate(today.getDate() - 90); break;
                case '6m': from.setMonth(today.getMonth() - 6); break;
                case '1y': from.setFullYear(today.getFullYear() - 1); break;
                case 'custom': return; 
            }
            
            if (this.filters.timeframe !== 'custom') {
                this.filters.date_to = today.toISOString().split('T')[0];
                this.filters.date_from = from.toISOString().split('T')[0];
                this.loadAllData();
            }
        },

        async loadAllData() {
            await Promise.all([
                this.loadFunnelData(),
                this.loadTimeToHire(),
                this.loadLocationData(),
                this.loadActivityData(),
                this.loadSourcesData(),
                this.loadOutcomesData(),
                this.loadJobEngagement(),
                this.loadCandidateQuality(),
                this.loadCommunication(),
                this.loadNotifications(),
                this.loadSubscription(),
                this.loadSecurityLogs(),
                this.loadOfferData()
            ]);
        },

        async loadFunnelData() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/funnel?${params}`);
                this.funnelData = await response.json();
                this.$nextTick(() => setTimeout(() => this._renderFunnelChartInternal(), 100));
            } catch (e) { console.error(e); }
        },

        _renderFunnelChartInternal() {
            const ctx = document.getElementById('funnelChart');
            if (!ctx || this.charts.funnel) {
                if(this.charts.funnel) {
                     this.charts.funnel.data.datasets[0].data = Object.values(this.funnelData.stages || {}).map(s => s.count || 0).slice(0, 5);
                     this.charts.funnel.update();
                }
                return; 
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
                            'rgba(139, 92, 246, 0.6)',
                            'rgba(99, 102, 241, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(5, 150, 105, 0.6)'
                        ],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        },

        async loadTimeToHire() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/time-to-hire?${params}`);
                this.timeToHireData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderTimeToHireChart(), 100));
            } catch (e) { console.error(e); }
        },

        renderTimeToHireChart() {
            const ctx = document.getElementById('timeToHireChart');
            if (!ctx || this.charts.timeToHire) return;

            this.charts.timeToHire = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['App', 'Shortlist', 'Interview', 'Offer', 'Hire'],
                    datasets: [{
                        label: 'Avg Days',
                        data: [
                            0,
                            this.timeToHireData.avg_days_application_to_shortlisted || 0,
                            this.timeToHireData.avg_days_shortlisted_to_interview || 0,
                            this.timeToHireData.avg_days_interview_to_offer || 0,
                            this.timeToHireData.avg_days_offer_to_hire || 0
                        ],
                        borderColor: 'rgb(79, 70, 229)',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        },

        async loadLocationData() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/location?${params}`);
                this.locationData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderLocationChart(), 100));
            } catch (e) { console.error(e); }
        },

        renderLocationChart() {
            const ctx = document.getElementById('locationChart');
            if (!ctx || this.charts.location) return;

            const cities = (this.locationData.top_cities || []).slice(0, 5);
            this.charts.location = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cities.map(c => c.city),
                    datasets: [{
                        label: 'Applications',
                        data: cities.map(c => c.applications),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        },

        async loadActivityData() {
            try {
                const days = this.filters.timeframe === '7d' ? 7 : 30;
                const response = await fetch(`/api/employer/analytics/activity?days=${days}`);
                this.activityData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderActivityChart(), 100));
            } catch (e) { console.error(e); }
        },

        renderActivityChart() {
            const ctx = document.getElementById('activityChart');
            if (!ctx || this.charts.activity) return;

            const daily = (this.activityData.daily_activity || []).reverse();
            this.charts.activity = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: daily.map(d => new Date(d.date).toLocaleDateString(undefined, {month:'short', day:'numeric'})),
                    datasets: [
                        {
                            label: 'Views',
                            data: daily.map(d => d.profiles_viewed || 0),
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.2
                        },
                        {
                            label: 'Resumes',
                            data: daily.map(d => d.resumes_downloaded || 0),
                            borderColor: 'rgb(16, 185, 129)',
                            tension: 0.2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false }
                }
            });
        },

        async loadSourcesData() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/sources?${params}`);
                this.sourcesData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderSourcesChart(), 100));
            } catch (e) { console.error(e); }
        },

        renderSourcesChart() {
            const ctx = document.getElementById('sourcesChart');
            if (!ctx || this.charts.sources) return;

            const data = this.sourcesData.counts || {};
            this.charts.sources = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#6B7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        },

        async loadOutcomesData() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/interview-outcomes?${params}`);
                this.outcomesData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderOutcomesChart(), 100));
                this.outcomesLoaded = true;
            } catch (e) { 
                console.error(e); 
                this.outcomesLoaded = true;
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
                type: 'bar',
                data: {
                    labels: ['Passed', 'Failed', 'No-show'],
                    datasets: [{
                        label: 'Count',
                        data: [
                            this.outcomesData.passed || 0,
                            this.outcomesData.failed || 0,
                            this.outcomesData.no_show || 0
                        ],
                        backgroundColor: ['#10B981', '#EF4444', '#9CA3AF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            this.chartsRendered.outcomes = true;
        },

        async loadJobEngagement() {
            try {
                const params = new URLSearchParams({ job_id: this.filters.job_id });
                const response = await fetch(`/api/employer/analytics/job-engagement?${params}`);
                this.jobEngagementData = await response.json();
            } catch (e) { console.error(e); }
        },

        async loadCandidateQuality() {
            try {
                const params = new URLSearchParams({ job_id: this.filters.job_id });
                const response = await fetch(`/api/employer/analytics/candidate-quality?${params}`);
                this.candidateQualityData = await response.json();
            } catch (e) { console.error(e); }
        },
        
        getQualityScore(jobId, type) {
            const job = this.candidateQualityData.find(j => j.job_id == jobId);
            if (!job) return 0;
            if (type === 'resume') return Math.round(job.avg_resume_completeness || 0);
            if (type === 'skill') return Math.round(job.avg_skill_match || 0);
            return 0;
        },

        async loadCommunication() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/communication?${params}`);
                this.communicationData = await response.json();
            } catch (e) { console.error(e); }
        },

        calculateReadRate() {
            if (!this.communicationData.interview_invites_sent) return 0;
            return Math.round((this.communicationData.interview_invites_read / this.communicationData.interview_invites_sent) * 100);
        },

        async loadNotifications() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/notifications?${params}`);
                this.notificationData = await response.json();
                this.$nextTick(() => setTimeout(() => this.renderNotificationChart(), 100));
            } catch (e) { console.error(e); }
        },

        renderNotificationChart() {
            const ctx = document.getElementById('notificationChart');
            if (!ctx || this.charts.notification) return;

            this.charts.notification = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sent', 'Delivered', 'Opened'],
                    datasets: [{
                        label: 'Count',
                        data: [
                            this.notificationData.total_sent || 0,
                            this.notificationData.delivered || 0,
                            this.notificationData.opened || 0
                        ],
                        backgroundColor: ['#E5E7EB', '#10B981', '#3B82F6'],
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        },

        async loadSubscription() {
            try {
                const response = await fetch(`/api/employer/analytics/subscription-roi`);
                this.subscriptionData = await response.json();
            } catch (e) { console.error(e); }
        },

        async loadSecurityLogs() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/security-logs?${params}`);
                this.securityLogs = await response.json();
            } catch (e) { console.error(e); }
        },

        async loadOfferData() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/employer/analytics/offer-acceptance?${params}`);
                this.offerData = await response.json();
                this.acceptanceLoaded = true;
            } catch (e) { console.error(e); }
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
