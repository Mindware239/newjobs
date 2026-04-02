<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="w-full px-4 sm:px-6 lg:px-8 py-8 font-sans">

    <?php
    // ── Pull all data from the same variables your original backend provides ──
    $analytics   = $analytics   ?? [];
    $interviews  = $interviews  ?? [];
    $filters     = $filters     ?? [];
    $pagination  = $pagination  ?? [];

    $statusData  = $analytics['status']    ?? [];
    $kpis        = $analytics['kpis']      ?? [];
    $types       = $analytics['types']     ?? [];
    $timeseries  = $analytics['timeseries'] ?? [];
    $duration    = $analytics['duration']  ?? [];
    $flagData    = $analytics['flags']     ?? [];
    $liveCards   = $analytics['live']      ?? [];
    $platforms   = $analytics['platforms'] ?? [];

    $compTrend = ($kpis['completed_prev7d'] ?? 0) > 0
        ? round((($kpis['completed_7d'] ?? 0) - ($kpis['completed_prev7d'] ?? 0)) / max(1,($kpis['completed_prev7d']??1)) * 100)
        : 0;
    $cancelTrend = ($kpis['cancelled_prev7d'] ?? 0) > 0
        ? round((($kpis['cancelled_7d'] ?? 0) - ($kpis['cancelled_prev7d'] ?? 0)) / max(1,($kpis['cancelled_prev7d']??1)) * 100)
        : 0;

    $currentStatus = $filters['status'] ?? '';
    $currentType   = $filters['type']   ?? '';
    $currentPlatform = $filters['platform'] ?? '';
    $currentSearch   = $filters['search']   ?? '';
    $currentDateFrom = $filters['date_from'] ?? '';
    $currentDateTo   = $filters['date_to']   ?? '';
    ?>

    <!-- ══════════════ PAGE HEADER ══════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-md flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Interview Control Center</h1>
                <p class="text-xs text-gray-400 mt-0.5">Monitor, control, and audit all video interviews in real time</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-xs font-bold text-green-700">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Live Monitoring
            </span>
            <a href="/admin/interviews/export" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export
            </a>
        </div>
    </div>

    <!-- ══════════════ KPI CARDS ══════════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Total</div>
            <div class="text-2xl font-extrabold text-gray-900 font-mono"><?= number_format((int)($statusData['total'] ?? 0)) ?></div>
            <div class="text-xs text-gray-400 mt-1">This week: <?= (int)($statusData['this_week'] ?? 0) ?></div>
        </div>

        <div class="bg-white border border-red-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-1.5 mb-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-xs font-bold text-red-500 uppercase tracking-wide">Live Now</span>
            </div>
            <div class="text-2xl font-extrabold text-red-600 font-mono"><?= number_format((int)($kpis['live_now'] ?? 0)) ?></div>
            <div class="text-xs text-red-300 mt-1">Today: <?= (int)($statusData['today'] ?? 0) ?></div>
        </div>

        <div class="bg-white border border-green-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-green-600 uppercase tracking-wide">Completed</span>
                <span class="text-xs font-bold px-1.5 py-0.5 rounded <?= $compTrend >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' ?>">
                    <?= $compTrend >= 0 ? '▲' : '▼' ?><?= abs($compTrend) ?>%
                </span>
            </div>
            <div class="text-2xl font-extrabold text-green-700 font-mono"><?= number_format((int)($kpis['completed_7d'] ?? 0)) ?></div>
            <div class="text-xs text-green-300 mt-1">Last 7 days</div>
        </div>

        <div class="bg-white border border-orange-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-orange-500 uppercase tracking-wide">Cancelled</span>
                <span class="text-xs font-bold px-1.5 py-0.5 rounded <?= $cancelTrend <= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' ?>">
                    <?= $cancelTrend >= 0 ? '▲' : '▼' ?><?= abs($cancelTrend) ?>%
                </span>
            </div>
            <div class="text-2xl font-extrabold text-orange-600 font-mono"><?= number_format((int)($kpis['cancelled_7d'] ?? 0)) ?></div>
            <div class="text-xs text-orange-300 mt-1">Last 7 days</div>
        </div>

        <div class="bg-white border border-indigo-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-indigo-500 uppercase tracking-wide mb-2">Avg Duration</div>
            <div class="text-2xl font-extrabold text-indigo-700 font-mono"><?= (int)($kpis['avg_duration'] ?? 0) ?>m</div>
            <div class="text-xs text-indigo-300 mt-1">avg per interview</div>
        </div>

        <div class="bg-white border border-blue-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-2">Upcoming</div>
            <div class="text-2xl font-extrabold text-blue-700 font-mono"><?= number_format((int)($statusData['upcoming'] ?? 0)) ?></div>
            <div class="text-xs text-blue-300 mt-1">Scheduled ahead</div>
        </div>

    </div>

    <!-- ══════════════ TYPE PILLS ══════════════ -->
    <div class="bg-white border border-gray-100 rounded-xl px-5 py-3 shadow-sm mb-5 flex items-center gap-3 flex-wrap">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Types:</span>
        <?php
        $typeConfig = [
            'Video'      => ['key'=>'video',      'cls'=>'bg-blue-50 text-blue-700 border-blue-200'],
            'Phone'      => ['key'=>'phone',      'cls'=>'bg-green-50 text-green-700 border-green-200'],
            'On-site'    => ['key'=>'onsite',     'cls'=>'bg-violet-50 text-violet-700 border-violet-200'],
            'Telephonic' => ['key'=>'telephonic', 'cls'=>'bg-amber-50 text-amber-700 border-amber-200'],
            'Other'      => ['key'=>'other',      'cls'=>'bg-gray-100 text-gray-600 border-gray-200'],
        ];
        foreach ($typeConfig as $label => $tc): ?>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold <?= $tc['cls'] ?>">
            <?= $label ?> <span class="font-mono font-extrabold"><?= (int)($types[$tc['key']] ?? 0) ?></span>
        </span>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════ CHARTS ROW 1 ══════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        <!-- Interviews Over Time -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-50">
                <div class="text-sm font-extrabold text-gray-900">Interviews Over Time</div>
                <div class="text-xs text-gray-400 mt-0.5">Last 30 days — all statuses</div>
            </div>
            <div class="p-4"><canvas id="chartTimeseries" style="height:200px; max-height:200px;"></canvas></div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-50">
                <div class="text-sm font-extrabold text-gray-900">Status Distribution</div>
                <div class="text-xs text-gray-400 mt-0.5">Breakdown by current status</div>
            </div>
            <div class="p-4"><canvas id="chartDistribution" style="height:200px; max-height:200px;"></canvas></div>
        </div>

        <!-- Type & Platform Mix -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-50">
                <div class="text-sm font-extrabold text-gray-900">Type & Platform Mix</div>
                <div class="text-xs text-gray-400 mt-0.5">Interview type distribution</div>
            </div>
            <div class="p-4"><canvas id="chartTypePlatform" style="height:200px; max-height:200px;"></canvas></div>
        </div>

    </div>

    <!-- ══════════════ CHARTS ROW 2 ══════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

        <!-- Average Duration -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-50">
                <div class="text-sm font-extrabold text-gray-900">Average Duration</div>
                <div class="text-xs text-gray-400 mt-0.5">Minutes per day — last 30 days</div>
            </div>
            <div class="p-4"><canvas id="chartDuration" style="height:190px; max-height:190px;"></canvas></div>
        </div>

        <!-- Flagged Interviews -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-50">
                <div class="text-sm font-extrabold text-gray-900">Flagged Interviews</div>
                <div class="text-xs text-gray-400 mt-0.5">Daily force-end / flagged events</div>
            </div>
            <div class="p-4"><canvas id="chartFlags" style="height:190px; max-height:190px;"></canvas></div>
            <?php $flagList = $flagData['list'] ?? []; ?>
            <?php if (!empty($flagList)): ?>
            <div class="px-5 pb-4 space-y-2 border-t border-gray-50 pt-3">
                <?php foreach (array_slice($flagList, 0, 3) as $f): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-700 font-semibold truncate mr-3"><?= htmlspecialchars($f['job_title'] ?? '—') ?> · <?= htmlspecialchars($f['company_name'] ?? '—') ?></span>
                    <span class="text-gray-400 whitespace-nowrap font-mono"><?= date('d M, H:i', strtotime($f['created_at'] ?? 'now')) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ══════════════ LIVE CARDS ══════════════ -->
    <?php if (!empty($liveCards)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-5">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <h3 class="text-sm font-extrabold text-red-800">Live Interviews Right Now</h3>
            <span class="text-xs font-bold bg-red-100 text-red-600 border border-red-200 px-2 py-0.5 rounded-full"><?= count($liveCards) ?> active</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($liveCards as $li): ?>
            <div class="bg-white rounded-lg border border-red-100 px-4 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($li['job_title'] ?? '—') ?></div>
                    <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars($li['company_name'] ?? '') ?> · <?= htmlspecialchars($li['candidate_name'] ?? '') ?></div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <button onclick="adminJoinInterview(<?= (int)$li['id'] ?>, false)" class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all">Join</button>
                    <button onclick="adminJoinInterview(<?= (int)$li['id'] ?>, true)" class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 hover:bg-indigo-100 transition-all">Silent</button>
                    <button onclick="openAdminForceEndModal(<?= (int)$li['id'] ?>)" class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-600 hover:text-white transition-all">End</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════ FILTERS ══════════════ -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Filter Interviews</div>
        <form method="GET" action="/admin/interviews">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-3">
                <!-- Search -->
                <div class="lg:col-span-2 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>"
                           placeholder="Search by job, employer, candidate, email"
                           class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>
                <!-- Status -->
                <div class="relative">
                    <select name="status" class="w-full appearance-none px-3 py-2.5 pr-8 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="all"         <?= $currentStatus === 'all'         ? 'selected' : '' ?>>All Status</option>
                        <option value="scheduled"   <?= $currentStatus === 'scheduled'   ? 'selected' : '' ?>>Scheduled</option>
                        <option value="rescheduled" <?= $currentStatus === 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                        <option value="live"        <?= $currentStatus === 'live'        ? 'selected' : '' ?>>Live</option>
                        <option value="completed"   <?= $currentStatus === 'completed'   ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled"   <?= $currentStatus === 'cancelled'   ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                </div>
                <!-- Type -->
                <div class="relative">
                    <select name="type" class="w-full appearance-none px-3 py-2.5 pr-8 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="all"        <?= $currentType === 'all'        ? 'selected' : '' ?>>All Types</option>
                        <option value="video"      <?= $currentType === 'video'      ? 'selected' : '' ?>>Video</option>
                        <option value="phone"      <?= $currentType === 'phone'      ? 'selected' : '' ?>>Phone</option>
                        <option value="onsite"     <?= $currentType === 'onsite'     ? 'selected' : '' ?>>On-site</option>
                        <option value="telephonic" <?= $currentType === 'telephonic' ? 'selected' : '' ?>>Telephonic</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                </div>
                <!-- Platform -->
                <div class="relative">
                    <select name="platform" class="w-full appearance-none px-3 py-2.5 pr-8 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="all"            <?= $currentPlatform === 'all'            ? 'selected' : '' ?>>All Platforms</option>
                        <option value="Jitsi"          <?= $currentPlatform === 'Jitsi'          ? 'selected' : '' ?>>Jitsi</option>
                        <option value="Jitsi (auto)"   <?= $currentPlatform === 'Jitsi (auto)'   ? 'selected' : '' ?>>Jitsi (auto)</option>
                        <option value="Zoom"           <?= $currentPlatform === 'Zoom'           ? 'selected' : '' ?>>Zoom</option>
                        <option value="Google Meet"    <?= $currentPlatform === 'Google Meet'    ? 'selected' : '' ?>>Google Meet</option>
                        <option value="Microsoft Teams"<?= $currentPlatform === 'Microsoft Teams'? 'selected' : '' ?>>Microsoft Teams</option>
                        <option value="Phone"          <?= $currentPlatform === 'Phone'          ? 'selected' : '' ?>>Phone</option>
                        <option value="Telephonic"     <?= $currentPlatform === 'Telephonic'     ? 'selected' : '' ?>>Telephonic</option>
                        <option value="On-site"        <?= $currentPlatform === 'On-site'        ? 'selected' : '' ?>>On-site</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                </div>
            </div>
            <!-- Date row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <input type="date" name="date_from" value="<?= htmlspecialchars($currentDateFrom) ?>"
                       class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                <input type="date" name="date_to" value="<?= htmlspecialchars($currentDateTo) ?>"
                       class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filter
                </button>
                <a href="/admin/interviews"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-lg text-sm font-bold hover:bg-gray-100 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ══════════════ TABLE ══════════════ -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Interview Records</h2>
                <p class="text-xs text-gray-400 mt-0.5">All interviews matching filters</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Interview ID</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Job Title</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Employer</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Candidate</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Scheduled</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Type / Platform</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($interviews)): ?>
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center">
                            <div class="text-gray-300 text-4xl mb-3">🎥</div>
                            <div class="text-sm font-bold text-gray-400">No interviews found for the selected filters</div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php
                    $statusCls = [
                        'live'        => 'bg-red-50 text-red-700 border-red-100',
                        'scheduled'   => 'bg-blue-50 text-blue-700 border-blue-100',
                        'rescheduled' => 'bg-violet-50 text-violet-700 border-violet-100',
                        'completed'   => 'bg-green-50 text-green-700 border-green-100',
                        'cancelled'   => 'bg-gray-100 text-gray-600 border-gray-200',
                    ];
                    foreach ($interviews as $iv):
                        $ivs = strtolower((string)($iv['status'] ?? ''));
                        $sc  = $statusCls[$ivs] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                    ?>
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded">#<?= (int)($iv['id'] ?? 0) ?></span>
                        </td>
                        <td class="px-5 py-3.5 text-sm font-semibold text-gray-900 max-w-[150px] truncate"><?= htmlspecialchars($iv['job_title'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-sm text-gray-600"><?= htmlspecialchars($iv['company_name'] ?? '—') ?></td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-violet-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    <?= strtoupper(mb_substr($iv['candidate_name'] ?? '?', 0, 1)) ?>
                                </div>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($iv['candidate_name'] ?? '—') ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-mono text-gray-700"><?= htmlspecialchars((string)($iv['scheduled_date'] ?? '')) ?></div>
                            <div class="text-xs text-gray-400">
                                <?= htmlspecialchars((string)($iv['scheduled_time'] ?? '')) ?>
                                <?php if (!empty($iv['scheduled_end_time'])): ?> – <?= htmlspecialchars($iv['scheduled_end_time']) ?><?php endif; ?>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border <?= $sc ?>">
                                <?php if ($ivs === 'live'): ?><span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span><?php endif; ?>
                                <?= htmlspecialchars(strtoupper($ivs ?: '—')) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 border border-gray-200 font-semibold">
                                    <?= htmlspecialchars($iv['interview_type'] ?? '—') ?>
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold">
                                    <?= htmlspecialchars($iv['platform_label'] ?? '—') ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="/admin/interviews/<?= (int)$iv['id'] ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 text-xs font-bold transition-all">
                                    View
                                </a>
                                <?php if ($ivs === 'live'): ?>
                                <button type="button" onclick="adminJoinInterview(<?= (int)$iv['id'] ?>, false)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>Join
                                </button>
                                <button type="button" onclick="adminJoinInterview(<?= (int)$iv['id'] ?>, true)"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs font-bold hover:bg-indigo-100 transition-all">
                                    Silent
                                </button>
                                <button type="button" onclick="openAdminForceEndModal(<?= (int)$iv['id'] ?>)"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-100 text-xs font-bold hover:bg-red-600 hover:text-white transition-all">
                                    Force End
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination — uses same $pagination variable from original backend -->
        <?php if (!empty($pagination)):
            $page       = (int)($pagination['page'] ?? 1);
            $totalPages = (int)($pagination['totalPages'] ?? 1);
            $perPage    = (int)($filters['per_page'] ?? 20);
            $baseQ      = $_GET ?? [];
            $buildLink  = fn($p) => '/admin/interviews?' . http_build_query(array_merge($baseQ, ['page'=>$p,'per_page'=>$perPage]));
        ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between flex-wrap gap-3">
            <span class="text-xs text-gray-400 font-semibold">Page <?= $page ?> of <?= $totalPages ?></span>
            <div class="flex items-center gap-2">
                <a href="<?= $buildLink(max(1, $page - 1)) ?>"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-100 transition-all <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Previous
                </a>
                <a href="<?= $buildLink(min($totalPages, $page + 1)) ?>"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all shadow-sm <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                    Next
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end page wrap -->

<!-- ══════════════ FORCE END MODAL ══════════════ -->
<div id="adminForceEndModal" class="fixed inset-0 hidden z-50">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="relative z-50 max-w-md mx-auto mt-20 mx-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-gray-900">Force End Interview</h2>
                    <p class="text-xs text-gray-400">This action is logged in the audit trail</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Provide a reason for ending this interview. This will be stored in the audit log.</p>
            <form id="adminForceEndForm" class="space-y-4">
                <input type="hidden" id="forceEndInterviewId" name="interview_id" value="">
                <textarea id="forceEndReason" name="reason" rows="3"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all resize-none"
                    placeholder="Reason for force ending this interview…"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAdminForceEndModal()"
                            class="px-4 py-2.5 text-sm font-bold rounded-lg border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2.5 text-sm font-bold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all shadow-sm">
                        Force End
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════ CHART JS — uses $analytics (same as original) ══════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Pull data from PHP — exact same $analytics variable your backend already sends ──
    const analytics = <?= json_encode($analytics ?? []) ?>;

    // Chart.js global defaults
    Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#9CA3AF';
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.boxHeight = 8;
    Chart.defaults.plugins.legend.labels.padding = 12;
    Chart.defaults.plugins.tooltip.backgroundColor = '#111827';
    Chart.defaults.plugins.tooltip.titleColor = '#fff';
    Chart.defaults.plugins.tooltip.bodyColor = '#9CA3AF';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.scale.grid.color = '#F3F4F6';
    Chart.defaults.scale.grid.drawBorder = false;
    Chart.defaults.scale.ticks.padding = 6;

    const fmtDate = d => {
        try { return new Date(d).toLocaleDateString('en-IN', {day:'2-digit', month:'short'}); }
        catch(e) { return d; }
    };

    // ── 1. TIMESERIES (same data as your original chartTimeseries) ──
    const ts = analytics.timeseries || [];
    const tsCtx = document.getElementById('chartTimeseries');
    if (tsCtx && ts.length) {
        const c = tsCtx.getContext('2d');
        const blueG = c.createLinearGradient(0,0,0,190); blueG.addColorStop(0,'rgba(59,130,246,0.2)'); blueG.addColorStop(1,'rgba(59,130,246,0)');
        const greenG = c.createLinearGradient(0,0,0,190); greenG.addColorStop(0,'rgba(16,185,129,0.15)'); greenG.addColorStop(1,'rgba(16,185,129,0)');
        new Chart(tsCtx, {
            type: 'line',
            data: {
                labels: ts.map(t => fmtDate(t.date)),
                datasets: [
                    { label:'Scheduled', data: ts.map(t=>(t.scheduled||0)+(t.rescheduled||0)), borderColor:'#3B82F6', backgroundColor:blueG, borderWidth:2, tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
                    { label:'Live',      data: ts.map(t=>t.live||0),                            borderColor:'#EF4444', backgroundColor:'transparent', borderWidth:2, tension:0.4, pointRadius:2, pointHoverRadius:5 },
                    { label:'Completed', data: ts.map(t=>t.completed||0),                       borderColor:'#10B981', backgroundColor:greenG, borderWidth:2, tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
                    { label:'Cancelled', data: ts.map(t=>t.cancelled||0),                       borderColor:'#F59E0B', borderDash:[5,3], backgroundColor:'transparent', borderWidth:1.5, tension:0.4, pointRadius:2 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode:'index', intersect:false },
                plugins: { legend: { display:true, position:'top' } },
                scales: { x: { ticks:{ maxTicksLimit:7, maxRotation:0 } }, y: { beginAtZero:true, ticks:{ stepSize:1, callback: v => Number.isInteger(v)?v:null } } }
            }
        });
    }

    // ── 2. STATUS DISTRIBUTION DOUGHNUT (same data as your original chartDistribution) ──
    const distCtx = document.getElementById('chartDistribution');
    if (distCtx && ts.length) {
        const totals = ts.reduce((acc,t) => {
            acc.scheduled  += (t.scheduled||0)+(t.rescheduled||0);
            acc.live       += t.live||0;
            acc.completed  += t.completed||0;
            acc.cancelled  += t.cancelled||0;
            return acc;
        }, {scheduled:0,live:0,completed:0,cancelled:0});
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Scheduled','Live','Completed','Cancelled'],
                datasets: [{ data: [totals.scheduled, totals.live, totals.completed, totals.cancelled],
                    backgroundColor: ['#3B82F6','#EF4444','#10B981','#9CA3AF'],
                    borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { display:true, position:'right' } }
            }
        });
    }

    // ── 3. TYPE & PLATFORM MIX (same data as your original chartTypePlatform) ──
    const tpCtx = document.getElementById('chartTypePlatform');
    if (tpCtx) {
        const typeStats     = analytics.types     || {};
        const platformStats = analytics.platforms || {};
        const typeLabels    = Object.keys(typeStats).map(k=>k.charAt(0).toUpperCase()+k.slice(1));
        const typeVals      = Object.values(typeStats);
        const platEntries   = Object.entries(platformStats).sort((a,b)=>b[1]-a[1]).slice(0,5);
        const allLabels     = [...typeLabels, ...platEntries.map(([k])=>k)];
        const allVals       = [...typeVals, ...platEntries.map(([,v])=>v)];
        const colors        = ['#3B82F6','#10B981','#8B5CF6','#F59E0B','#9CA3AF','#6366F1','#14B8A6','#F97316'];
        new Chart(tpCtx, {
            type: 'bar',
            data: {
                labels: allLabels,
                datasets: [{ label:'Count', data: allVals,
                    backgroundColor: allLabels.map((_,i)=>colors[i%colors.length]),
                    borderRadius: 6, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display:false } },
                scales: { x: { beginAtZero:true, ticks:{stepSize:1, callback:v=>Number.isInteger(v)?v:null} }, y: { grid:{display:false} } }
            }
        });
    }

    // ── 4. AVG DURATION (same data as your original chartDuration) ──
    const durCtx = document.getElementById('chartDuration');
    if (durCtx) {
        const dur = analytics.duration || [];
        if (dur.length) {
            const c = durCtx.getContext('2d');
            const grad = c.createLinearGradient(0,0,0,180); grad.addColorStop(0,'rgba(16,185,129,0.25)'); grad.addColorStop(1,'rgba(16,185,129,0)');
            new Chart(durCtx, {
                type: 'line',
                data: { labels: dur.map(d=>fmtDate(d.date)),
                    datasets: [{ label:'Avg Minutes', data: dur.map(d=>d.avg_minutes||0),
                        borderColor:'#10B981', backgroundColor:grad, borderWidth:2.5, tension:0.45, fill:true, pointRadius:2.5, pointHoverRadius:5 }] },
                options: { responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{display:false} },
                    scales:{ x:{ticks:{maxTicksLimit:7,maxRotation:0}}, y:{beginAtZero:true} } }
            });
        }
    }

    // ── 5. FLAGGED (same data as your original chartFlags) ──
    const flagCtx = document.getElementById('chartFlags');
    if (flagCtx) {
        const flagSeries = (analytics.flags && analytics.flags.series) ? analytics.flags.series : [];
        if (flagSeries.length) {
            const c = flagCtx.getContext('2d');
            const grad = c.createLinearGradient(0,0,0,180); grad.addColorStop(0,'rgba(220,38,38,0.6)'); grad.addColorStop(1,'rgba(220,38,38,0.1)');
            new Chart(flagCtx, {
                type: 'bar',
                data: { labels: flagSeries.map(f=>fmtDate(f.date)),
                    datasets: [{ label:'Flagged', data: flagSeries.map(f=>f.count||0),
                        backgroundColor: grad, borderColor:'#DC2626', borderWidth:0, borderRadius:5, borderSkipped:false }] },
                options: { responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{display:false} },
                    scales:{ x:{ticks:{maxTicksLimit:7,maxRotation:0},grid:{display:false}}, y:{beginAtZero:true,ticks:{stepSize:1,callback:v=>Number.isInteger(v)?v:null}} } }
            });
        }
    }
});
</script>

<!-- ══════════════ MODAL + JOIN JS (same as original) ══════════════ -->
<script>
async function adminJoinInterview(id, silent) {
    try {
        const url = silent ? `/admin/interviews/${id}/join-silent` : `/admin/interviews/${id}/join`;
        const res = await fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        if (data && data.success && data.join_url) {
            window.open(data.join_url, '_blank');
        } else {
            alert('Unable to join interview.');
        }
    } catch(e) { alert('Failed to join interview.'); }
}

function openAdminForceEndModal(id) {
    document.getElementById('forceEndInterviewId').value = id;
    document.getElementById('forceEndReason').value = '';
    document.getElementById('adminForceEndModal').classList.remove('hidden');
}

function closeAdminForceEndModal() {
    document.getElementById('adminForceEndModal').classList.add('hidden');
}

document.getElementById('adminForceEndForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id     = document.getElementById('forceEndInterviewId').value;
    const reason = document.getElementById('forceEndReason').value;
    try {
        const res = await fetch(`/admin/interviews/${id}/force-end`, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({reason})
        });
        const data = await res.json();
        if (data && data.success) { closeAdminForceEndModal(); window.location.reload(); }
        else { alert('Failed to force end interview.'); }
    } catch(e) { alert('Failed to force end interview.'); }
});
</script>