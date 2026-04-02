<?php
/**
 * @var string $title
 * @var array $interview
 * @var array $events
 * @var array $timeline
 * @var \App\Models\User $user
 */
$ivId        = (int)$interview['id'];
$jobTitle    = htmlspecialchars((string)($interview['job_title']     ?? '—'));
$employer    = htmlspecialchars((string)($interview['company_name'] ?? '—'));
$candidate   = htmlspecialchars((string)($interview['candidate_name'] ?? '—'));
$ivStatus    = strtolower((string)($interview['status'] ?? 'unknown'));
$schedStart  = htmlspecialchars((string)($interview['scheduled_start'] ?? '—'));
$schedEnd    = htmlspecialchars((string)($interview['scheduled_end']   ?? '—'));
$startedAt   = htmlspecialchars((string)($interview['started_at']      ?? '—'));
$endedAt     = htmlspecialchars((string)($interview['ended_at']        ?? '—'));
$csrf        = htmlspecialchars($_SESSION['csrf_token'] ?? '');

$statusColor = match($ivStatus) {
    'live'        => 'bg-red-50 text-red-700 border-red-200',
    'completed'   => 'bg-green-50 text-green-700 border-green-200',
    'scheduled'   => 'bg-blue-50 text-blue-700 border-blue-200',
    'rescheduled' => 'bg-violet-50 text-violet-700 border-violet-200',
    'cancelled'   => 'bg-gray-100 text-gray-600 border-gray-200',
    default       => 'bg-gray-100 text-gray-500 border-gray-200',
};
$isLive = $ivStatus === 'live';
?>

<div class="w-full px-4 sm:px-6 lg:px-8 py-7 font-sans">

    <!-- ══════ BACK + PAGE HEADER ══════ -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <a href="/admin/interviews"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-blue-600 mb-3 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Back to Interviews
            </a>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-md flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Interview <span class="font-mono text-blue-600">#<?= $ivId ?></span></h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border <?= $statusColor ?>">
                            <?php if ($isLive): ?><span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span><?php endif; ?>
                            <?= strtoupper($ivStatus) ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="font-semibold text-gray-600"><?= $jobTitle ?></span>
                        <span class="mx-1.5 text-gray-300">·</span><?= $employer ?>
                        <span class="mx-1.5 text-gray-300">·</span><?= $candidate ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Top-right action buttons -->
        <div class="flex items-center gap-2 flex-wrap mt-1">
            <a href="/admin/interviews/<?= $ivId ?>/logs"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Audit Logs
            </a>
            <button onclick="adminJoinInterview(<?= $ivId ?>, false)"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Join
            </button>
            <button onclick="adminJoinInterview(<?= $ivId ?>, true)"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                Join Silently
            </button>
            <button onclick="openAdminForceEndModal(<?= $ivId ?>)"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Force End
            </button>
        </div>
    </div>

    <!-- ══════ MAIN GRID ══════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- LEFT (2/3) -->
        <div class="lg:col-span-2 space-y-5">

            <!-- ── LIVE METRICS ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <?php if ($isLive): ?>
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        <?php endif; ?>
                        <span class="text-sm font-extrabold text-gray-900">Live Status</span>
                    </div>
                    <span id="lastRefreshed" class="text-xs text-gray-400 font-mono">Refreshes every 5s</span>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="liveMetrics">

                        <!-- Status -->
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Status</div>
                            <div id="m_status" class="text-lg font-extrabold text-gray-900 font-mono">—</div>
                        </div>

                        <!-- Elapsed -->
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                            <div class="text-xs font-bold text-indigo-400 uppercase tracking-wide mb-2">Elapsed</div>
                            <div id="m_elapsed" class="text-lg font-extrabold text-indigo-700 font-mono">—</div>
                        </div>

                        <!-- Participants -->
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                            <div class="text-xs font-bold text-blue-400 uppercase tracking-wide mb-2">Participants</div>
                            <div id="m_participants" class="text-lg font-extrabold text-blue-700 font-mono">—</div>
                        </div>

                        <!-- Screen Sharing -->
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Screen Sharing</div>
                            <div id="m_screen" class="text-lg font-extrabold text-gray-700 font-mono">—</div>
                        </div>

                        <!-- Recording -->
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Recording</div>
                            <div id="m_recording" class="text-lg font-extrabold text-gray-700 font-mono">—</div>
                        </div>

                        <!-- Risk Score -->
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Risk Score</div>
                            <div id="m_risk" class="text-lg font-extrabold text-gray-700 font-mono">—</div>
                            <div id="m_risk_bar" class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div id="m_risk_fill" class="h-full rounded-full bg-green-400 transition-all duration-500" style="width:0%"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ── EVENT SUMMARY ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <div class="text-sm font-extrabold text-gray-900">Event Summary</div>
                    <div class="text-xs text-gray-400 mt-0.5">All logged events for this interview session</div>
                </div>
                <div class="p-5">
                    <?php if (empty($events)): ?>
                    <div class="text-center py-8">
                        <div class="text-3xl text-gray-200 mb-2">📋</div>
                        <div class="text-sm font-semibold text-gray-400">No events recorded yet</div>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php
                        $eventColors = [
                            'join'        => 'bg-green-50  border-green-100  text-green-700',
                            'leave'       => 'bg-red-50    border-red-100    text-red-700',
                            'start'       => 'bg-blue-50   border-blue-100   text-blue-700',
                            'end'         => 'bg-gray-100  border-gray-200   text-gray-700',
                            'screen'      => 'bg-indigo-50 border-indigo-100 text-indigo-700',
                            'record'      => 'bg-violet-50 border-violet-100 text-violet-700',
                            'force_end'   => 'bg-red-50    border-red-100    text-red-700',
                            'reschedule'  => 'bg-amber-50  border-amber-100  text-amber-700',
                        ];
                        foreach ($events as $e):
                            $eType = strtolower((string)$e['event_type']);
                            $eCls  = 'bg-gray-50 border-gray-100 text-gray-700';
                            foreach ($eventColors as $key => $cls) {
                                if (str_contains($eType, $key)) { $eCls = $cls; break; }
                            }
                        ?>
                        <div class="border rounded-xl p-4 <?= $eCls ?>">
                            <div class="text-xs font-semibold opacity-70 mb-1 truncate"><?= htmlspecialchars((string)$e['event_type']) ?></div>
                            <div class="text-2xl font-extrabold font-mono"><?= (int)$e['cnt'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── TIMELINE ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-extrabold text-gray-900">Timeline</div>
                        <div class="text-xs text-gray-400 mt-0.5">Chronological event log for this interview</div>
                    </div>
                    <span class="text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full">
                        <?= count($timeline) ?> events
                    </span>
                </div>
                <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                    <?php if (empty($timeline)): ?>
                    <div class="px-6 py-10 text-center">
                        <div class="text-3xl text-gray-200 mb-2">🕐</div>
                        <div class="text-sm font-semibold text-gray-400">No timeline events yet</div>
                    </div>
                    <?php else: ?>
                    <?php
                    $timelineIconMap = [
                        'join'       => ['bg'=>'bg-green-100', 'ic'=>'text-green-600', 'path'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                        'leave'      => ['bg'=>'bg-red-100',   'ic'=>'text-red-600',   'path'=>'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
                        'start'      => ['bg'=>'bg-blue-100',  'ic'=>'text-blue-600',  'path'=>'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'end'        => ['bg'=>'bg-gray-100',  'ic'=>'text-gray-600',  'path'=>'M21 12a9 9 0 11-18 0 9 9 0 0118 0z M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z'],
                        'force_end'  => ['bg'=>'bg-red-100',   'ic'=>'text-red-700',   'path'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                        'screen'     => ['bg'=>'bg-indigo-100','ic'=>'text-indigo-600','path'=>'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        'record'     => ['bg'=>'bg-violet-100','ic'=>'text-violet-600','path'=>'M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                    ];
                    foreach ($timeline as $idx => $t):
                        $tType = strtolower((string)$t['event_type']);
                        $icon  = ['bg'=>'bg-gray-100','ic'=>'text-gray-500','path'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'];
                        foreach ($timelineIconMap as $key => $ic) {
                            if (str_contains($tType, $key)) { $icon = $ic; break; }
                        }
                        $actorColors = ['employer'=>'bg-blue-50 text-blue-700','candidate'=>'bg-green-50 text-green-700','admin'=>'bg-red-50 text-red-700'];
                        $actor = strtolower((string)$t['actor_role']);
                        $actorCls = $actorColors[$actor] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <div class="flex items-start gap-4 px-6 py-3.5 hover:bg-gray-50/60 transition-colors">
                        <!-- Icon -->
                        <div class="w-8 h-8 rounded-lg <?= $icon['bg'] ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 <?= $icon['ic'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $icon['path'] ?>"/>
                            </svg>
                        </div>
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars((string)$t['event_type']) ?></div>
                            <?php if (!empty($t['metadata'])): ?>
                            <div class="text-xs text-gray-400 mt-0.5 truncate"><?= htmlspecialchars((string)$t['metadata']) ?></div>
                            <?php endif; ?>
                        </div>
                        <!-- Actor + Time -->
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold <?= $actorCls ?>"><?= ucfirst($actor) ?></span>
                            <span class="text-xs text-gray-400 font-mono whitespace-nowrap"><?= date('d M, H:i', strtotime((string)$t['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end left col -->

        <!-- RIGHT (1/3) -->
        <div class="space-y-5">

            <!-- ── INTERVIEW DETAILS ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <div class="text-sm font-extrabold text-gray-900">Interview Details</div>
                </div>
                <div class="px-5 py-4 space-y-0 divide-y divide-gray-50">
                    <?php
                    $details = [
                        ['label'=>'Interview ID',  'val'=>"#{$ivId}",  'mono'=>true],
                        ['label'=>'Status',        'val'=>strtoupper($ivStatus), 'badge'=>$statusColor],
                        ['label'=>'Job Title',     'val'=>$jobTitle],
                        ['label'=>'Employer',      'val'=>$employer],
                        ['label'=>'Candidate',     'val'=>$candidate],
                        ['label'=>'Scheduled',     'val'=>"{$schedStart} → {$schedEnd}"],
                        ['label'=>'Started',       'val'=>$startedAt],
                        ['label'=>'Ended',         'val'=>$endedAt],
                        ['label'=>'Type',          'val'=>htmlspecialchars($interview['interview_type'] ?? '—')],
                        ['label'=>'Platform',      'val'=>htmlspecialchars($interview['platform_label'] ?? '—')],
                    ];
                    foreach ($details as $d): ?>
                    <div class="flex items-start justify-between py-3 gap-4">
                        <dt class="text-xs font-bold text-gray-400 uppercase tracking-wide flex-shrink-0 mt-0.5"><?= $d['label'] ?></dt>
                        <?php if (!empty($d['badge'])): ?>
                        <dd><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border <?= $d['badge'] ?>"><?= $d['val'] ?></span></dd>
                        <?php else: ?>
                        <dd class="text-xs font-semibold text-gray-900 text-right <?= !empty($d['mono']) ? 'font-mono' : '' ?>"><?= $d['val'] ?></dd>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="px-5 pb-5">
                    <a href="/admin/interviews/<?= $ivId ?>/logs"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        View Full Audit Logs
                    </a>
                </div>
            </div>

            <!-- ── QUICK ACTIONS ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <div class="text-sm font-extrabold text-gray-900">Quick Actions</div>
                </div>
                <div class="p-4 space-y-2">
                    <button onclick="adminJoinInterview(<?= $ivId ?>, false)"
                            class="w-full inline-flex items-center gap-2 px-4 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Join as Observer
                    </button>
                    <button onclick="adminJoinInterview(<?= $ivId ?>, true)"
                            class="w-full inline-flex items-center gap-2 px-4 py-3 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        Join Silently
                    </button>
                    <button onclick="openAdminForceEndModal(<?= $ivId ?>)"
                            class="w-full inline-flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-100 text-red-600 rounded-xl text-sm font-bold hover:bg-red-600 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Force End Interview
                    </button>
                </div>
            </div>

            <!-- ── RISK INDICATOR ── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <div class="text-sm font-extrabold text-gray-900">Risk Monitor</div>
                    <div class="text-xs text-gray-400 mt-0.5">Live anomaly scoring — auto updates</div>
                </div>
                <div class="px-5 py-4">
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Risk Score</span>
                        <span id="sidebar_risk" class="text-2xl font-extrabold font-mono text-gray-900">—</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-3">
                        <div id="sidebar_risk_fill" class="h-full rounded-full transition-all duration-700 bg-green-400" style="width:0%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-300 font-mono">
                        <span>0%</span><span>50%</span><span>100%</span>
                    </div>
                    <div id="sidebar_risk_label" class="mt-2 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Normal
                        </span>
                    </div>
                </div>
            </div>

        </div><!-- end right col -->
    </div><!-- end grid -->

</div><!-- end page -->

<!-- ══════════════ FORCE END MODAL ══════════════ -->
<div id="adminForceEndModal" class="fixed inset-0 hidden z-50">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeAdminForceEndModal()"></div>
    <div class="relative z-50 max-w-md mx-auto mt-20 px-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="bg-red-50 border-b border-red-100 px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900">Force End Interview</h2>
                    <p class="text-xs text-gray-400">Logged in the admin audit trail</p>
                </div>
            </div>
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-4">Provide a reason. This will be stored permanently in the audit log and cannot be undone.</p>
                <form id="adminForceEndForm" class="space-y-4">
                    <input type="hidden" id="forceEndInterviewId" name="interview_id" value="">
                    <textarea id="forceEndReason" name="reason" rows="3"
                              class="w-full px-3.5 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all resize-none"
                              placeholder="Reason for force ending this interview…"></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeAdminForceEndModal()"
                                class="px-4 py-2.5 text-sm font-bold rounded-xl border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition-all shadow-sm">
                            Force End
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════ JS (all original logic preserved) ══════════════ -->
<script>
// ── Metrics fetch (same logic, enhanced display) ──
async function fetchMetrics() {
    try {
        const res = await fetch('/admin/interviews/<?= $ivId ?>/metrics');
        const data = await res.json();
        if (!data || !data.success) return;

        const status = String(data.status || '—').toUpperCase();
        document.getElementById('m_status').textContent = status;

        const elapsed = formatDuration(data.elapsed_seconds || 0);
        document.getElementById('m_elapsed').textContent = elapsed;
        document.getElementById('m_participants').textContent = String(data.participants ?? '—');

        const screenEl = document.getElementById('m_screen');
        screenEl.textContent = data.screen_sharing ? 'ON' : 'OFF';
        screenEl.className   = 'text-lg font-extrabold font-mono ' + (data.screen_sharing ? 'text-indigo-600' : 'text-gray-400');

        const recEl = document.getElementById('m_recording');
        recEl.textContent = data.recording ? 'ON' : 'OFF';
        recEl.className   = 'text-lg font-extrabold font-mono ' + (data.recording ? 'text-violet-600' : 'text-gray-400');

        const risk = Math.min(100, Math.max(0, parseInt(data.risk_score ?? 0, 10)));
        document.getElementById('m_risk').textContent = risk + '%';
        document.getElementById('sidebar_risk').textContent = risk + '%';

        const riskColor  = risk >= 70 ? 'bg-red-500' : risk >= 40 ? 'bg-amber-400' : 'bg-green-400';
        const riskLabel  = risk >= 70 ? ['bg-red-50 text-red-700 border-red-100','bg-red-500','High Risk']
                         : risk >= 40 ? ['bg-amber-50 text-amber-700 border-amber-100','bg-amber-400','Moderate']
                         :              ['bg-green-50 text-green-700 border-green-100','bg-green-500','Normal'];

        ['m_risk_fill','sidebar_risk_fill'].forEach(id => {
            const el = document.getElementById(id);
            el.style.width = risk + '%';
            el.className   = 'h-full rounded-full transition-all duration-700 ' + riskColor;
        });

        document.getElementById('sidebar_risk_label').innerHTML =
            `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border ${riskLabel[0]}">
                <span class="w-1.5 h-1.5 rounded-full ${riskLabel[1]}"></span>${riskLabel[2]}
            </span>`;

        document.getElementById('lastRefreshed').textContent = 'Updated ' + new Date().toLocaleTimeString();
    } catch(_) {}
}

function formatDuration(sec) {
    const s = Math.max(0, parseInt(sec, 10) || 0);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const r = s % 60;
    if (h > 0) return `${h}h ${m}m`;
    if (m > 0) return `${m}m ${r}s`;
    return `${r}s`;
}

setInterval(fetchMetrics, 5000);
fetchMetrics();

// ── Join / Force End (same as original) ──
async function adminJoinInterview(id, silent) {
    try {
        const url  = silent ? `/admin/interviews/${id}/join-silent` : `/admin/interviews/${id}/join`;
        const res  = await fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        if (data && data.success && data.join_url) window.open(data.join_url, '_blank');
        else alert('Unable to join interview.');
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
        const res  = await fetch(`/admin/interviews/${id}/force-end`, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({reason})
        });
        const data = await res.json();
        if (data && data.success) { closeAdminForceEndModal(); window.location.reload(); }
        else alert('Failed to force end interview.');
    } catch(e) { alert('Failed to force end interview.'); }
});
</script>