<?php

/**
 * @var string $title
 * @var array $interviews
 * @var array $filters
 * @var \App\Models\User $user
 */

?>
<div>
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Interview Control Center</h1>
            <p class="mt-2 text-sm text-gray-600">
                Monitor, control, and audit all video interviews in real time.
            </p>
        </div>
    </div>

    <?php
    $analytics = $analytics ?? ['status' => [], 'types' => []];
    $status = $analytics['status'] ?? [];
    $types = $analytics['types'] ?? [];
    ?>
    <?php 
      $kpis = $analytics['kpis'] ?? [];
      $compTrend = ($kpis['completed_prev7d'] ?? 0) > 0 
        ? round((($kpis['completed_7d'] ?? 0) - ($kpis['completed_prev7d'] ?? 0)) / max(1, ($kpis['completed_prev7d'] ?? 1)) * 100) 
        : 0;
      $cancelTrend = ($kpis['cancelled_prev7d'] ?? 0) > 0 
        ? round((($kpis['cancelled_7d'] ?? 0) - ($kpis['cancelled_prev7d'] ?? 0)) / max(1, ($kpis['cancelled_prev7d'] ?? 1)) * 100) 
        : 0;
    ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="text-xs text-gray-500">Total</div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($status['total'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">This week: <?= (int)($status['this_week'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="text-xs text-gray-500">Live Now</div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($kpis['live_now'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">Today: <?= (int)($status['today'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center gap-2">
                <div class="text-xs text-gray-500">Completed (7d)</div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <?= $compTrend >= 0 ? '▲ +' . $compTrend . '%' : '▼ ' . $compTrend . '%' ?>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($kpis['completed_7d'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center gap-2">
                <div class="text-xs text-gray-500">Cancelled (7d)</div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-50 text-red-700 border border-red-200">
                    <?= $cancelTrend >= 0 ? '▲ +' . $cancelTrend . '%' : '▼ ' . $cancelTrend . '%' ?>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($kpis['cancelled_7d'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="text-xs text-gray-500">Avg Duration</div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($kpis['avg_duration'] ?? 0) ?>m</div>
            <div class="text-[11px] text-gray-500 mt-1">avg per interview</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <div class="text-xs text-gray-500">Upcoming</div>
            <div class="text-2xl font-bold text-gray-900"><?= (int)($status['upcoming'] ?? 0) ?></div>
        </div>
    </div>
    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm mb-6">
        <div class="text-sm font-semibold text-gray-700 mb-3">Types</div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">Video: <?= (int)($types['video'] ?? 0) ?></span>
            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">Phone: <?= (int)($types['phone'] ?? 0) ?></span>
            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">On-site: <?= (int)($types['onsite'] ?? 0) ?></span>
            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">Telephonic: <?= (int)($types['telephonic'] ?? 0) ?></span>
            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">Other: <?= (int)($types['other'] ?? 0) ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">Interviews Over Time</div>
            <div class="h-64">
                <canvas id="chartTimeseries"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">Status Distribution</div>
            <div class="h-64">
                <canvas id="chartDistribution"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">Type & Platform Mix</div>
            <div class="h-64">
                <canvas id="chartTypePlatform"></canvas>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">Average Duration (min)</div>
            <div class="h-64">
                <canvas id="chartDuration"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">Flagged Interviews</div>
            <div class="h-64">
                <canvas id="chartFlags"></canvas>
            </div>
            <?php $flags = $analytics['flags']['list'] ?? []; ?>
            <div class="mt-3 space-y-2">
                <?php foreach ($flags as $f): ?>
                    <div class="flex items-center justify-between text-sm">
                        <div class="truncate"><?= htmlspecialchars($f['job_title'] ?? '—') ?> • <?= htmlspecialchars($f['company_name'] ?? '—') ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y h:i A', strtotime($f['created_at'] ?? ''))) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php $liveCards = $analytics['live'] ?? []; ?>
    <?php if (!empty($liveCards)): ?>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
        <div class="text-sm font-semibold text-gray-700 mb-3">Live Interviews</div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($liveCards as $li): ?>
                <div class="rounded-lg border border-gray-200 p-4 flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="font-semibold truncate"><?= htmlspecialchars($li['job_title'] ?? '—') ?></div>
                        <div class="text-xs text-gray-500 truncate"><?= htmlspecialchars($li['company_name'] ?? '—') ?> • <?= htmlspecialchars($li['candidate_name'] ?? '—') ?></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="adminJoinInterview(<?= (int)$li['id'] ?>, false)" class="h-8 px-2.5 text-xs rounded-md bg-indigo-600 text-white">Join</button>
                        <button onclick="adminJoinInterview(<?= (int)$li['id'] ?>, true)" class="h-8 px-2.5 text-xs rounded-md bg-indigo-50 text-indigo-700">Silent</button>
                        <button onclick="openAdminForceEndModal(<?= (int)$li['id'] ?>)" class="h-8 px-2.5 text-xs rounded-md bg-red-50 text-red-700">End</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                placeholder="Search by job, employer, candidate, email"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            />

            <select
                name="status"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
                <?php
                $status = $filters['status'] ?? 'all';
                ?>
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="scheduled" <?= $status === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="rescheduled" <?= $status === 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                <option value="live" <?= $status === 'live' ? 'selected' : '' ?>>Live</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <select
                name="type"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
                <?php $type = $filters['type'] ?? 'all'; ?>
                <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Types</option>
                <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Video</option>
                <option value="phone" <?= $type === 'phone' ? 'selected' : '' ?>>Phone</option>
                <option value="onsite" <?= $type === 'onsite' ? 'selected' : '' ?>>On-site</option>
                <option value="telephonic" <?= $type === 'telephonic' ? 'selected' : '' ?>>Telephonic</option>
            </select>

            <select
                name="platform"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
                <?php $platform = $filters['platform'] ?? 'all'; ?>
                <option value="all" <?= $platform === 'all' ? 'selected' : '' ?>>All Platforms</option>
                <option value="Jitsi" <?= $platform === 'Jitsi' ? 'selected' : '' ?>>Jitsi</option>
                <option value="Jitsi (auto)" <?= $platform === 'Jitsi (auto)' ? 'selected' : '' ?>>Jitsi (auto)</option>
                <option value="Zoom" <?= $platform === 'Zoom' ? 'selected' : '' ?>>Zoom</option>
                <option value="Google Meet" <?= $platform === 'Google Meet' ? 'selected' : '' ?>>Google Meet</option>
                <option value="Microsoft Teams" <?= $platform === 'Microsoft Teams' ? 'selected' : '' ?>>Microsoft Teams</option>
                <option value="Phone" <?= $platform === 'Phone' ? 'selected' : '' ?>>Phone</option>
                <option value="Telephonic" <?= $platform === 'Telephonic' ? 'selected' : '' ?>>Telephonic</option>
                <option value="On-site" <?= $platform === 'On-site' ? 'selected' : '' ?>>On-site</option>
                <option value="Video" <?= $platform === 'Video' ? 'selected' : '' ?>>Video (Other)</option>
            </select>

            <input
                type="date"
                name="date_from"
                value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            />

            <input
                type="date"
                name="date_to"
                value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
            />

            <div class="flex items-center justify-end gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Filter
                </button>
                <a
                    href="/admin/interviews"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Interview ID</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Employer</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type / Platform</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($interviews)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500 text-sm">
                        No interviews found for the selected filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                            #<?= htmlspecialchars((string)$interview['id']) ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                            <?= htmlspecialchars((string)($interview['job_title'] ?? '—')) ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                            <?= htmlspecialchars((string)($interview['company_name'] ?? '—')) ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                            <?= htmlspecialchars((string)($interview['candidate_name'] ?? '—')) ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                            <div><?= htmlspecialchars((string)($interview['scheduled_date'] ?? '')) ?></div>
                            <div class="text-xs text-gray-500">
                                <?= htmlspecialchars((string)($interview['scheduled_time'] ?? '')) ?>
                                <?php if (!empty($interview['scheduled_end_time'])): ?>
                                    – <?= htmlspecialchars((string)$interview['scheduled_end_time']) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <?php
                            $statusLabel = strtoupper((string)($interview['status'] ?? 'unknown'));
                            $statusClass = 'bg-gray-100 text-gray-800';
                            if ($interview['status'] === 'live') {
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif ($interview['status'] === 'scheduled' || $interview['status'] === 'rescheduled') {
                                $statusClass = 'bg-blue-100 text-blue-800';
                            } elseif ($interview['status'] === 'completed') {
                                $statusClass = 'bg-gray-200 text-gray-800';
                            } elseif ($interview['status'] === 'cancelled') {
                                $statusClass = 'bg-red-100 text-red-800';
                            }
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 border border-gray-300">
                                    <?= htmlspecialchars((string)($interview['interview_type'] ?? '—')) ?>
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <?= htmlspecialchars((string)($interview['platform_label'] ?? '—')) ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a
                                href="/admin/interviews/<?= (int)$interview['id'] ?>"
                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 rounded-md text-xs text-gray-700 bg-white hover:bg-gray-50"
                            >
                                View
                            </a>
                            <?php if ($interview['status'] === 'live'): ?>
                                <button
                                    type="button"
                                    onclick="adminJoinInterview(<?= (int)$interview['id'] ?>, false)"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Join
                                </button>
                                <button
                                    type="button"
                                    onclick="adminJoinInterview(<?= (int)$interview['id'] ?>, true)"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100"
                                >
                                    Join Silently
                                </button>
                                <button
                                    type="button"
                                    onclick="openAdminForceEndModal(<?= (int)$interview['id'] ?>)"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100"
                                >
                                    Force End
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($pagination)): 
        $page = (int)($pagination['page'] ?? 1);
        $totalPages = (int)($pagination['totalPages'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 20);
        $baseQuery = $_GET;
        ?>
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Page <?= $page ?> of <?= $totalPages ?>
            </div>
            <div class="inline-flex items-center gap-2">
                <?php
                $buildLink = function($p) use ($baseQuery, $perPage) {
                    $baseQuery['page'] = $p;
                    $baseQuery['per_page'] = $perPage;
                    return '/admin/interviews?' . http_build_query($baseQuery);
                };
                ?>
                <a href="<?= $buildLink(max(1, $page-1)) ?>" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">Previous</a>
                <a href="<?= $buildLink(min($totalPages, $page+1)) ?>" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">Next</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="adminForceEndModal" class="fixed inset-0 hidden z-40">
    <div class="absolute inset-0 bg-gray-900 bg-opacity-40"></div>
    <div class="relative z-50 max-w-md mx-auto mt-24 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Force End Interview</h2>
        <p class="text-sm text-gray-600 mb-4">
            Provide a reason for ending this interview. This will be stored in the audit log.
        </p>
        <form id="adminForceEndForm" class="space-y-4">
            <input type="hidden" id="forceEndInterviewId" name="interview_id" value="">
            <textarea
                id="forceEndReason"
                name="reason"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                placeholder="Reason for force ending this interview"
            ></textarea>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    onclick="closeAdminForceEndModal()"
                    class="px-3 py-2 text-sm rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 text-sm rounded-md border border-transparent text-white bg-red-600 hover:bg-red-700"
                >
                    Force End
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    async function adminJoinInterview(id, silent) {
        try {
            const url = silent
                ? `/admin/interviews/${id}/join-silent`
                : `/admin/interviews/${id}/join`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data && data.success && data.join_url) {
                window.open(data.join_url, '_blank');
            } else {
                alert('Unable to join interview.');
            }
        } catch (e) {
            alert('Failed to join interview.');
        }
    }

    function openAdminForceEndModal(id) {
        document.getElementById('forceEndInterviewId').value = id;
        document.getElementById('forceEndReason').value = '';
        document.getElementById('adminForceEndModal').classList.remove('hidden');
    }

    function closeAdminForceEndModal() {
        document.getElementById('adminForceEndModal').classList.add('hidden');
    }

    document.getElementById('adminForceEndForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = document.getElementById('forceEndInterviewId').value;
        const reason = document.getElementById('forceEndReason').value;
        try {
            const res = await fetch(`/admin/interviews/${id}/force-end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({reason})
            });
            const data = await res.json();
            if (data && data.success) {
                closeAdminForceEndModal();
                window.location.reload();
            } else {
                alert('Failed to force end interview.');
            }
        } catch (e) {
            alert('Failed to force end interview.');
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const analytics = <?= json_encode($analytics ?? []) ?>;
        if (!analytics) return;

        const ts = analytics.timeseries || [];
        const tsLabels = ts.map(t => {
            const d = new Date(t.date);
            return d.toLocaleDateString();
        });
        const tsScheduled = ts.map(t => (t.scheduled || 0) + (t.rescheduled || 0));
        const tsLive = ts.map(t => t.live || 0);
        const tsCompleted = ts.map(t => t.completed || 0);
        const tsCancelled = ts.map(t => t.cancelled || 0);

        const timeseriesCanvas = document.getElementById('chartTimeseries');
        if (timeseriesCanvas) {
            new Chart(timeseriesCanvas, {
                type: 'line',
                data: {
                    labels: tsLabels,
                    datasets: [
                        {
                            label: 'Scheduled',
                            data: tsScheduled,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Live',
                            data: tsLive,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Completed',
                            data: tsCompleted,
                            borderColor: 'rgb(107, 114, 128)',
                            backgroundColor: 'rgba(107, 114, 128, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Cancelled',
                            data: tsCancelled,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        const distCanvas = document.getElementById('chartDistribution');
        if (distCanvas) {
            const totalScheduled = tsScheduled.reduce((a, b) => a + b, 0);
            const totalLive = tsLive.reduce((a, b) => a + b, 0);
            const totalCompleted = tsCompleted.reduce((a, b) => a + b, 0);
            const totalCancelled = tsCancelled.reduce((a, b) => a + b, 0);
            new Chart(distCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Scheduled', 'Live', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [totalScheduled, totalLive, totalCompleted, totalCancelled],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(34, 197, 94, 0.7)',
                            'rgba(107, 114, 128, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(34, 197, 94)',
                            'rgb(107, 114, 128)',
                            'rgb(239, 68, 68)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        const mixCanvas = document.getElementById('chartTypePlatform');
        if (mixCanvas) {
            const typeStats = analytics.types || {};
            const platformStats = analytics.platforms || {};
            const typeLabels = Object.keys(typeStats);
            const platformEntries = Object.entries(platformStats).sort((a, b) => b[1] - a[1]).slice(0, 5);
            const platformLabels = platformEntries.map(([k]) => k);
            const labels = [...typeLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)), ...platformLabels];
            const typeDataset = labels.map(l => {
                const key = l.toLowerCase();
                return typeStats[key] ?? 0;
            });
            const platformDataset = labels.map(l => platformStats[l] ?? 0);
            new Chart(mixCanvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Types',
                            data: typeDataset,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgb(59, 130, 246)'
                        },
                        {
                            label: 'Platforms',
                            data: platformDataset,
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgb(99, 102, 241)'
                        }
                    ]
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

        const durCanvas = document.getElementById('chartDuration');
        if (durCanvas) {
            const dur = analytics.duration || [];
            const durLabels = dur.map(d => {
                const dt = new Date(d.date);
                return dt.toLocaleDateString();
            });
            const durData = dur.map(d => d.avg_minutes || 0);
            new Chart(durCanvas, {
                type: 'line',
                data: {
                    labels: durLabels,
                    datasets: [{
                        label: 'Avg Minutes',
                        data: durData,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const flagsCanvas = document.getElementById('chartFlags');
        if (flagsCanvas) {
            const flagSeries = (analytics.flags && analytics.flags.series) ? analytics.flags.series : [];
            const flagLabels = flagSeries.map(f => {
                const dt = new Date(f.date);
                return dt.toLocaleDateString();
            });
            const flagData = flagSeries.map(f => f.count || 0);
            new Chart(flagsCanvas, {
                type: 'bar',
                data: {
                    labels: flagLabels,
                    datasets: [{
                        label: 'Forced End Events',
                        data: flagData,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>
