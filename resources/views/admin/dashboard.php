<div>
    <!-- <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">Platform overview and statistics</p>
    </div> -->

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Employers</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_employers'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Candidates</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_candidates'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Jobs</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_jobs'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Applications</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_applications'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Revenue Today</p>
            <p class="text-2xl font-semibold text-gray-900">₹<?= number_format($revenue['today'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Revenue This Week</p>
            <p class="text-2xl font-semibold text-gray-900">₹<?= number_format($revenue['week'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Revenue This Month</p>
            <p class="text-2xl font-semibold text-gray-900">₹<?= number_format($revenue['month'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Revenue YTD</p>
            <p class="text-2xl font-semibold text-gray-900">₹<?= number_format($revenue['ytd'] ?? 0, 2) ?></p>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Alerts & Notifications</h2>
        <div class="space-y-3">
            <?php foreach ($alerts as $alert): ?>
            <div class="bg-<?= $alert['type'] === 'error' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') ?>-50 border border-<?= $alert['type'] === 'error' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') ?>-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-<?= $alert['type'] === 'error' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') ?>-800">
                            <?= htmlspecialchars($alert['title']) ?>
                        </h3>
                        <p class="mt-1 text-sm text-<?= $alert['type'] === 'error' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') ?>-700">
                            <?= htmlspecialchars($alert['message']) ?>
                        </p>
                    </div>
                    <a href="<?= htmlspecialchars($alert['link'] ?? '#') ?>" class="text-sm font-medium text-<?= $alert['type'] === 'error' ? 'red' : ($alert['type'] === 'warning' ? 'yellow' : 'blue') ?>-600 hover:underline">
                        View →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Signups (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="signupsChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Posting Trends (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="jobsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Signups Chart
    const signupsCtx = document.getElementById('signupsChart');
    if (signupsCtx) {
        const signupsData = <?= json_encode($dailySignups ?? []) ?>;
        new Chart(signupsCtx, {
            type: 'line',
            data: {
                labels: signupsData.map(d => d.date),
                datasets: [{
                    label: 'Employers',
                    data: signupsData.map(d => d.employers || 0),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Candidates',
                    data: signupsData.map(d => d.candidates || 0),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Jobs Chart
    const jobsCtx = document.getElementById('jobsChart');
    if (jobsCtx) {
        const jobsData = <?= json_encode($jobTrends ?? []) ?>;
        new Chart(jobsCtx, {
            type: 'bar',
            data: {
                labels: jobsData.map(d => d.date),
                datasets: [{
                    label: 'Jobs Posted',
                    data: jobsData.map(d => d.count || 0),
                    backgroundColor: 'rgba(147, 51, 234, 0.5)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Candidates by Category</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Candidates</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Applications</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach (($candidateByCategory ?? []) as $row): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['category'] ?? '') ?></td>
                        <td class="px-4 py-2 text-sm"><?= (int)($row['candidates'] ?? 0) ?></td>
                        <td class="px-4 py-2 text-sm"><?= (int)($row['applications'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($candidateByCategory ?? [])): ?>
                    <tr><td class="px-4 py-2 text-sm text-gray-500" colspan="3">No data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Candidate Locations</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Candidates</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach (($candidateLocations ?? []) as $row): ?>
                    <?php
                        $parts = array_filter([
                            trim($row['city'] ?? ''),
                            trim($row['state'] ?? ''),
                            trim($row['country'] ?? '')
                        ]);
                        $loc = !empty($parts) ? implode(', ', $parts) : 'Unknown';
                    ?>
                    <tr>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($loc) ?></td>
                        <td class="px-4 py-2 text-sm"><?= (int)($row['candidates'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($candidateLocations ?? [])): ?>
                    <tr><td class="px-4 py-2 text-sm text-gray-500" colspan="2">No data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
