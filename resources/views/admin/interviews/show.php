<?php
/**
 * @var string $title
 * @var array $interview
 * @var array $events
 * @var array $timeline
 * @var \App\Models\User $user
 */
?>
<div>
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Interview #<?= (int)$interview['id'] ?></h1>
        <p class="text-sm text-gray-600">Job: <?= htmlspecialchars((string)($interview['job_title'] ?? '—')) ?> • Employer: <?= htmlspecialchars((string)($interview['company_name'] ?? '—')) ?> • Candidate: <?= htmlspecialchars((string)($interview['candidate_name'] ?? '—')) ?></p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Live Status</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="liveMetrics">
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Status</div>
                        <div id="m_status" class="text-lg font-semibold">—</div>
                    </div>
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Elapsed</div>
                        <div id="m_elapsed" class="text-lg font-semibold">—</div>
                    </div>
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Participants</div>
                        <div id="m_participants" class="text-lg font-semibold">—</div>
                    </div>
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Screen Sharing</div>
                        <div id="m_screen" class="text-lg font-semibold">—</div>
                    </div>
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Recording</div>
                        <div id="m_recording" class="text-lg font-semibold">—</div>
                    </div>
                    <div class="p-4 border rounded">
                        <div class="text-xs text-gray-500">Risk Score</div>
                        <div id="m_risk" class="text-lg font-semibold">—</div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <button onclick="adminJoinInterview(<?= (int)$interview['id'] ?>, false)" class="px-3 py-2 text-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Join</button>
                    <button onclick="adminJoinInterview(<?= (int)$interview['id'] ?>, true)" class="px-3 py-2 text-sm rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Join Silently</button>
                    <button onclick="openAdminForceEndModal(<?= (int)$interview['id'] ?>)" class="px-3 py-2 text-sm rounded-md text-red-700 bg-red-50 hover:bg-red-100">Force End</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Summary</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($events as $e): ?>
                        <div class="p-3 border rounded">
                            <div class="text-xs text-gray-500"><?= htmlspecialchars((string)$e['event_type']) ?></div>
                            <div class="text-lg font-semibold"><?= (int)$e['cnt'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                <div class="space-y-3 max-h-96 overflow-auto">
                    <?php foreach ($timeline as $t): ?>
                        <div class="flex items-center justify-between border-b pb-2">
                            <div class="text-sm text-gray-700"><?= htmlspecialchars((string)$t['event_type']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars((string)$t['actor_role']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars(date('M d, Y h:i A', strtotime((string)$t['created_at']))) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Interview Details</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="text-gray-900"><?= htmlspecialchars((string)$interview['status']) ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Scheduled</dt><dd class="text-gray-900"><?= htmlspecialchars((string)$interview['scheduled_start']) ?> → <?= htmlspecialchars((string)$interview['scheduled_end']) ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Started</dt><dd class="text-gray-900"><?= htmlspecialchars((string)($interview['started_at'] ?? '—')) ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Ended</dt><dd class="text-gray-900"><?= htmlspecialchars((string)($interview['ended_at'] ?? '—')) ?></dd></div>
                </dl>
                <div class="mt-4">
                    <a href="/admin/interviews/<?= (int)$interview['id'] ?>/logs" class="inline-flex items-center px-3 py-2 rounded-md text-sm border border-gray-300 bg-white hover:bg-gray-50">View Audit Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function fetchMetrics() {
    try {
        const res = await fetch('/admin/interviews/<?= (int)$interview['id'] ?>/metrics');
        const data = await res.json();
        if (!data || !data.success) return;
        document.getElementById('m_status').textContent = String(data.status || '—').toUpperCase();
        document.getElementById('m_elapsed').textContent = formatDuration(data.elapsed_seconds || 0);
        document.getElementById('m_participants').textContent = String(data.participants ?? '—');
        document.getElementById('m_screen').textContent = data.screen_sharing ? 'ON' : 'OFF';
        document.getElementById('m_recording').textContent = data.recording ? 'ON' : 'OFF';
        document.getElementById('m_risk').textContent = (data.risk_score ?? 0) + '%';
    } catch (_) {}
}

function formatDuration(sec) {
    const s = Math.max(0, parseInt(sec, 10) || 0);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const rem = s % 60;
    if (h > 0) return `${h}h ${m}m`;
    if (m > 0) return `${m}m ${rem}s`;
    return `${rem}s`;
}

setInterval(fetchMetrics, 5000);
fetchMetrics();
</script>

