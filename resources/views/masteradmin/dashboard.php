<?php if (!empty($error)): ?>
<div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">
    <?= htmlspecialchars($error) ?>
</div>

<div class="bg-white rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-extrabold text-purple-700">Assigned Staff</h2>
        <a href="/master/roles" class="px-3 py-1.5 rounded bg-purple-600 text-white">Manage Roles</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-600">
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Role</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Last Login</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($assignedUsers ?? []) as $u): ?>
                    <tr class="border-t">
                        <td class="py-2 pr-4 text-gray-900"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                        <td class="py-2 pr-4">
                            <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 text-xs"><?= htmlspecialchars($u['role_slug'] ?? $u['role'] ?? '') ?></span>
                        </td>
                        <td class="py-2 pr-4">
                            <span class="px-2 py-0.5 rounded <?= (($u['status'] ?? '') === 'active') ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?> text-xs">
                                <?= htmlspecialchars($u['status'] ?? 'inactive') ?>
                            </span>
                        </td>
                        <td class="py-2 pr-4 text-gray-700"><?= htmlspecialchars($u['last_login'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
</div>
<?php endif; ?>


<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-3xl font-bold text-slate-800">Dashboard</h1><p class="text-slate-500 mt-1">Welcome back! Here's what's happening today.</p></div>
    <div class="flex items-center gap-3">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar h-4 w-4 text-slate-500"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
            </span>
            <input id="dash-date" type="date" class="pl-10 pr-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
        </div>
        <a id="gen-report" href="/master/reports?date=<?= htmlspecialchars(date('Y-m-d')) ?>" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3 3 7-7"></path></svg>
            Generate Report
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <a href="/master/employers" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Total Employers</div>
                <div class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                    <?= (int) ($stats['total_employers'] ?? 0) ?>
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                    </span>
                </div>
            </div>
        </div>
    </a>
    <a href="/master/candidates" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-emerald-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20v-2a5 5 0 015-5h4a5 5 0 015 5v2"></path><circle cx="12" cy="7" r="4" stroke-width="2"></circle></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Total Candidates</div>
                <div class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                    <?= (int) ($stats['total_candidates'] ?? 0) ?>
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>
            </div>
        </div>
    </a>
    <a href="/admin/jobs" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-200 flex items-center justify-center group-hover:bg-amber-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-amber-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Active Jobs</div>
                <div class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                    <?= (int) ($stats['active_jobs'] ?? 0) ?>
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                </div>
            </div>
        </div>
    </a>
    <a href="/master/payments" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-emerald-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2"
                       viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1v22" />
                    <path d="M17 5c0-2-2.5-3-5-3s-5 1-5 3 2 3 5 3 5 1 5 3-2.5 3-5 3-5-1-5-3" />
                </svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Revenue This Month</div>
                <div class="text-3xl font-extrabold text-gray-900">₹<?= number_format((float) ($stats['revenue_month'] ?? 0), 2) ?></div>
            </div>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <a href="/master/reports" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-emerald-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Applications Today</div>
                <div class="mt-1 text-3xl font-extrabold text-gray-900"><?= (int) ($stats['applications_today'] ?? 0) ?></div>
            </div>
        </div>
    </a>
    <a href="/master/reports" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-purple-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Auto-Apply Today</div>
                <div class="mt-1 text-3xl font-extrabold text-gray-900"><?= (int) ($stats['auto_apply_today'] ?? 0) ?></div>
            </div>
        </div>
    </a>
    <a href="/master/reports" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-amber-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Auto-Apply Avg Score</div>
                <div class="mt-1 text-3xl font-extrabold text-gray-900"><?= number_format((float) ($stats['auto_apply_avg_score_today'] ?? 0.0), 1) ?>%</div>
            </div>
        </div>
    </a>
    <a href="/master/logs" class="block bg-white p-6 rounded-2xl shadow border border-gray-200 transform hover:scale-105 transition duration-300 ease-in-out hover:shadow-xl cursor-pointer group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center group-hover:bg-red-600 transition-colors duration-300">
                <svg class="h-6 w-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"></path></svg>
            </div>
            <div>
                <div class="text-sm text-gray-600">Auto-Apply Errors</div>
                <div class="mt-1 text-3xl font-extrabold text-gray-900"><?= (int) ($stats['auto_apply_failed_today'] ?? 0) ?></div>
            </div>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow lg:col-span-2">
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="text-lg font-semibold">Platform Overview</div>
                <div class="text-xs text-gray-500">Monthly growth analytics</div>
            </div>
            <div class="text-xs text-gray-500">Last 6 months</div>
        </div>
        <?php
        $months = $series['months'] ?? [];
        $apps = $series['applications'] ?? [];
        $autos = $series['auto_apply'] ?? [];
        $jobs = $series['jobs'] ?? [];
        $count = count($months);
        $maxv = 1;
        for ($i = 0; $i < $count; $i++) {
            $maxv = max($maxv, (int) ($apps[$i] ?? 0), (int) ($autos[$i] ?? 0), (int) ($jobs[$i] ?? 0));
        }
        $W = 965;
        $H = 320;
        $PL = 65;
        $PR = 5;
        $PT = 5;
        $PB = 35;
        $chartW = $W - $PL - $PR;
        $chartH = $H - $PT - $PB;
        $stepX = $count > 1 ? $chartW / ($count - 1) : $chartW;
        $toX = function ($i) use ($PL, $stepX) {
            return $PL + ($i * $stepX);
        };
        $toY = function ($v) use ($PT, $chartH, $maxv) {
            $ratio = $maxv > 0 ? ($v / $maxv) : 0;
            return $PT + ($chartH - ($ratio * $chartH));
        };
        $pathJobs = '';
        $pathAuto = '';
        $pathApps = '';
        for ($i = 0; $i < $count; $i++) {
            $x = $toX($i);
            $yj = $toY((int) ($jobs[$i] ?? 0));
            $ya = $toY((int) ($autos[$i] ?? 0));
            $yapp = $toY((int) ($apps[$i] ?? 0));
            $pathJobs .= ($i === 0 ? 'M' : 'L') . $x . ' ' . $yj . ' ';
            $pathAuto .= ($i === 0 ? 'M' : 'L') . $x . ' ' . $ya . ' ';
            $pathApps .= ($i === 0 ? 'M' : 'L') . $x . ' ' . $yapp . ' ';
        }
        // Area fill path for Applications (close to baseline)
        $baselineY = $PT + $chartH;
        $areaApps = $pathApps . 'L ' . $toX($count - 1) . ' ' . $baselineY . ' L ' . $toX(0) . ' ' . $baselineY . ' Z';
        ?>
        <div id="platform-chart" class="relative" style="width: 100%; height: <?= $H ?>px;">
        <svg width="<?= $W ?>" height="<?= $H ?>" viewBox="0 0 <?= $W ?> <?= $H ?>" class="w-full h-full">
            <rect x="0" y="0" width="<?= $W ?>" height="<?= $H ?>" fill="transparent"></rect>
            <?php
            $gridSteps = 4;
            for ($g = 0; $g <= $gridSteps; $g++):
                $gy = $PT + ($chartH / $gridSteps) * $g;
                ?>
                <line x1="<?= $PL ?>" y1="<?= $gy ?>" x2="<?= $PL + $chartW ?>" y2="<?= $gy ?>" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 3"></line>
            <?php endfor; ?>
            <?php for ($v = 0; $v <= $count; $v++):
                $vx = $PL + ($v * $stepX); ?>
                <line x1="<?= $vx ?>" y1="<?= $PT ?>" x2="<?= $vx ?>" y2="<?= $PT + $chartH ?>" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 3"></line>
            <?php endfor; ?>
            <line x1="<?= $PL ?>" y1="<?= $PT + $chartH ?>" x2="<?= $PL + $chartW ?>" y2="<?= $PT + $chartH ?>" stroke="#94A3B8" stroke-width="1"></line>
            <?php for ($i = 0; $i < $count; $i++):
                $x = $toX($i); ?>
                <line x1="<?= $x ?>" y1="<?= $PT + $chartH ?>" x2="<?= $x ?>" y2="<?= $PT + $chartH + 4 ?>" stroke="#CBD5E1" stroke-width="1"></line>
                <text x="<?= $x ?>" y="<?= $PT + $chartH + 18 ?>" text-anchor="middle" font-size="11" fill="#475569"><?= htmlspecialchars($months[$i] ?? '') ?></text>
            <?php endfor; ?>
            <path id="appsArea" d="<?= $areaApps ?>" fill="url(#colorApps)" stroke="none"></path>
            <path id="jobsLine" d="<?= $pathJobs ?>" fill="none" stroke="#6366F1" stroke-width="2"></path>
            <path id="autoLine" d="<?= $pathAuto ?>" fill="none" stroke="#8B5CF6" stroke-width="2"></path>
            <path id="appsLine" d="<?= $pathApps ?>" fill="none" stroke="#10B981" stroke-width="2"></path>
            <?php for ($i = 0; $i < $count; $i++):
                $x = $toX($i);
                $yj = $toY((int) ($jobs[$i] ?? 0));
                $ya = $toY((int) ($autos[$i] ?? 0));
                $yapp = $toY((int) ($apps[$i] ?? 0)); ?>
                <circle class="chart-dot" data-series="apps" data-month="<?= htmlspecialchars($months[$i] ?? '') ?>" data-jobs="<?= (int) ($jobs[$i] ?? 0) ?>" data-apps="<?= (int) ($apps[$i] ?? 0) ?>" data-auto="<?= (int) ($autos[$i] ?? 0) ?>" cx="<?= $x ?>" cy="<?= $yapp ?>" r="3.5" fill="#10B981"></circle>
                <circle class="chart-dot" data-series="jobs" data-month="<?= htmlspecialchars($months[$i] ?? '') ?>" data-jobs="<?= (int) ($jobs[$i] ?? 0) ?>" data-apps="<?= (int) ($apps[$i] ?? 0) ?>" data-auto="<?= (int) ($autos[$i] ?? 0) ?>" cx="<?= $x ?>" cy="<?= $yj ?>" r="3" fill="#2563EB"></circle>
                <circle class="chart-dot" data-series="auto" data-month="<?= htmlspecialchars($months[$i] ?? '') ?>" data-jobs="<?= (int) ($jobs[$i] ?? 0) ?>" data-apps="<?= (int) ($apps[$i] ?? 0) ?>" data-auto="<?= (int) ($autos[$i] ?? 0) ?>" cx="<?= $x ?>" cy="<?= $ya ?>" r="3" fill="#8B5CF6"></circle>
            <?php endfor; ?>
            <defs>
                <linearGradient id="colorJobs" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stop-color="#6366F1" stop-opacity="0.3"></stop>
                    <stop offset="95%" stop-color="#6366F1" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="colorApps" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stop-color="#10B981" stop-opacity="0.3"></stop>
                    <stop offset="95%" stop-color="#10B981" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="colorVol" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stop-color="#8B5CF6" stop-opacity="0.3"></stop>
                    <stop offset="95%" stop-color="#8B5CF6" stop-opacity="0"></stop>
                </linearGradient>
            </defs>
        </svg>
        <div id="chart-tip" class="hidden absolute z-20 bg-slate-900 text-white text-sm px-3 py-2 rounded-md shadow"></div>
        </div>
        <div class="flex items-center gap-6 mt-2">
            <span class="inline-flex items-center gap-2 text-sm text-gray-700"><span class="w-3 h-3 rounded bg-blue-600"></span>Jobs</span>
            <span class="inline-flex items-center gap-2 text-sm text-gray-700"><span class="w-3 h-3 rounded bg-green-500"></span>Applications</span>
            <span class="inline-flex items-center gap-2 text-sm text-gray-700"><span class="w-3 h-3 rounded bg-purple-600"></span>Auto-Apply</span>
        </div>
        <script>
            (function(){
                var tip=document.getElementById('chart-tip');
                var container=document.getElementById('platform-chart');
                var dots=document.querySelectorAll('.chart-dot');
                var area=document.getElementById('appsArea');
                function showTip(e){
                    var d=e.target;
                    var m=d.getAttribute('data-month');
                    var j=d.getAttribute('data-jobs');
                    var a=d.getAttribute('data-apps');
                    var au=d.getAttribute('data-auto');
                    tip.innerHTML=m+'<br>jobs: '+j+'<br>applications: '+a+'<br>auto-apply: '+au;
                    var rect=container.getBoundingClientRect();
                    tip.classList.remove('hidden');
                    var x=e.clientX-rect.left+12;
                    var y=e.clientY-rect.top-10;
                    var maxX=(container.clientWidth - tip.offsetWidth - 8);
                    var maxY=(container.clientHeight - tip.offsetHeight - 8);
                    x=Math.max(8, Math.min(x, maxX));
                    y=Math.max(8, Math.min(y, maxY));
                    tip.style.left=x+'px';
                    tip.style.top=y+'px';
                    tip.classList.remove('hidden');
                    d.setAttribute('r', String(parseFloat(d.getAttribute('r')||'3')+1.2));
                    var s=d.getAttribute('data-series');
                    var map={jobs:'jobsLine',auto:'autoLine',apps:'appsLine'}; var id=map[s];
                    var line=id?document.getElementById(id):null;
                    if(line){ line.style.transition='stroke-width 150ms ease, filter 150ms ease'; line.style.strokeWidth='3.5'; line.style.filter='drop-shadow(0 0 4px '+(s==='jobs'?'#2563EB':(s==='auto'?'#8B5CF6':'#10B981'))+')'; }
                }
                function moveTip(e){
                    var rect=container.getBoundingClientRect();
                    var x=e.clientX-rect.left+12;
                    var y=e.clientY-rect.top-10;
                    var maxX=(container.clientWidth - tip.offsetWidth - 8);
                    var maxY=(container.clientHeight - tip.offsetHeight - 8);
                    x=Math.max(8, Math.min(x, maxX));
                    y=Math.max(8, Math.min(y, maxY));
                    tip.style.left=x+'px';
                    tip.style.top=y+'px';
                }
                function hideTip(e){ tip.classList.add('hidden'); var d=e && e.target; if(d && d.tagName==='circle'){ var r=d.getAttribute('data-base-r')||'3'; d.setAttribute('r', r); var s=d.getAttribute('data-series'); var map={jobs:'jobsLine',auto:'autoLine',apps:'appsLine'}; var id=map[s]; var line=id?document.getElementById(id):null; if(line){ line.style.strokeWidth='2'; line.style.filter='none'; } } }
                dots.forEach(function(d){
                    d.setAttribute('data-base-r', d.getAttribute('r')||'3');
                    d.style.transition='r 150ms ease, filter 150ms ease';
                    d.addEventListener('mouseenter', showTip);
                    d.addEventListener('mousemove', moveTip);
                    d.addEventListener('mouseleave', hideTip);
                });
                ['jobsLine','autoLine','appsLine'].forEach(function(id){
                    var p=document.getElementById(id); if(!p) return;
                    var len=p.getTotalLength();
                    p.style.strokeDasharray=len+' '+len;
                    p.style.strokeDashoffset=len;
                    p.style.transition='stroke-dashoffset 800ms ease';
                    setTimeout(function(){ p.style.strokeDashoffset='0'; }, 50);
                });
                if(area){ area.style.opacity='0'; area.style.transition='opacity 600ms ease'; setTimeout(function(){ area.style.opacity='1'; }, 80); }
                var date=document.getElementById('dash-date'); var btn=document.getElementById('gen-report');
                if(date && btn){ date.addEventListener('change', function(){ btn.href='/master/reports?date='+encodeURIComponent(date.value||''); }); }
            })();
        </script>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow">
        <div class="text-lg font-semibold mb-2">Application Status</div>
        <div class="text-xs text-gray-500 mb-4">Current processing status</div>
        <?php
        $approved = (int) ($distribution['approved'] ?? 0);
        $pending = (int) ($distribution['pending'] ?? 0);
        $rejected = (int) ($distribution['rejected'] ?? 0);
        $tot = max(1, $approved + $pending + $rejected);
        $r = 60;
        $circ = 2 * M_PI * $r;
        $segA = ($approved / $tot) * $circ;
        $segP = ($pending / $tot) * $circ;
        $segR = ($rejected / $tot) * $circ;
        ?>
        <div class="flex items-center gap-8">
            <div class="relative" style="width:160px; height:160px;">
            <svg width="160" height="160" viewBox="0 0 160 160" class="w-full h-full">
                <g transform="translate(80,80) rotate(-90)">
                    <circle r="<?= $r ?>" cx="0" cy="0" fill="none" stroke="#E5E7EB" stroke-width="18"></circle>
                    <circle class="donut-seg" data-label="Approved" data-value="<?= $approved ?>" data-color="#10B981" r="<?= $r ?>" cx="0" cy="0" fill="none" stroke="#10B981" stroke-width="18"
                            stroke-dasharray="0 <?= $circ ?>" stroke-dashoffset="0"></circle>
                    <circle class="donut-seg" data-label="Pending" data-value="<?= $pending ?>" data-color="#F59E0B" r="<?= $r ?>" cx="0" cy="0" fill="none" stroke="#F59E0B" stroke-width="18"
                            stroke-dasharray="0 <?= $circ ?>" stroke-dashoffset="-<?= $segA ?>"></circle>
                    <circle class="donut-seg" data-label="Rejected" data-value="<?= $rejected ?>" data-color="#EF4444" r="<?= $r ?>" cx="0" cy="0" fill="none" stroke="#EF4444" stroke-width="18"
                            stroke-dasharray="0 <?= $circ ?>" stroke-dashoffset="-<?= $segA + $segP ?>"></circle>
                </g>
            </svg>
            <div id="donut-center" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                <div class="text-xs text-slate-500">Total</div>
                <div class="text-lg font-semibold text-slate-800"><?= $tot ?></div>
            </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded bg-green-500"></span>Approved: <?= $approved ?></div>
                <div class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded bg-yellow-500"></span>Pending: <?= $pending ?></div>
                <div class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded bg-red-500"></span>Rejected: <?= $rejected ?></div>
            </div>
        </div>
        <script>
            (function(){
                var segs=document.querySelectorAll('.donut-seg');
                var center=document.getElementById('donut-center');
                var total=<?= $tot ?>;
                // Animate draw-in
                var circ=<?= $circ ?>;
                var a=<?= $segA ?>, p=<?= $segP ?>, r=<?= $segR ?>;
                var values=[a,p,r];
                segs.forEach(function(s){ s.style.transition='stroke-dasharray 800ms ease, stroke-width 150ms ease, filter 150ms ease'; });
                setTimeout(function(){
                    if(segs[0]) segs[0].setAttribute('stroke-dasharray', a+' '+(circ-a));
                    if(segs[1]) segs[1].setAttribute('stroke-dasharray', p+' '+(circ-p));
                    if(segs[2]) segs[2].setAttribute('stroke-dasharray', r+' '+(circ-r));
                }, 60);
                function enter(e){
                    var s=e.target; var val=parseFloat(s.getAttribute('data-value')||'0'); var color=s.getAttribute('data-color')||'#000';
                    s.style.strokeWidth='22'; s.style.filter='drop-shadow(0 0 6px '+color+')';
                    var pct=total>0?Math.round((val/total)*100):0;
                    if(center){ center.innerHTML='<div class="text-xs" style="color:'+color+'">'+(s.getAttribute('data-label')||'')+'</div><div class="text-lg font-semibold" style="color:'+color+'">'+val+' • '+pct+'%</div>'; }
                }
                function leave(e){
                    var s=e.target; s.style.strokeWidth='18'; s.style.filter='none';
                    if(center){ center.innerHTML='<div class="text-xs text-slate-500">Total</div><div class="text-lg font-semibold text-slate-800">'+total+'</div>'; }
                }
                segs.forEach(function(s){ s.addEventListener('mouseenter', enter); s.addEventListener('mouseleave', leave); });
            })();
        </script>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-800">Jobs by Category</h3>
            <p class="text-sm text-slate-500">Distribution across sectors</p>
        </div>
        <?php
        $items = array_slice(($topCategories ?? []), 0, 6);
        $maxC = 1;
        foreach ($items as $tc) {
            $maxC = max($maxC, (int) ($tc['c'] ?? 0));
        }
        $tickStep = 15;
        $scaleMax = max(60, (int) ceil($maxC / $tickStep) * $tickStep);
        $W = 445;
        $H = 280;
        $YL = 85;
        $PT = 5;
        $PB = 35;
        $PR = 5;
        $chartW = $W - $YL - $PR;
        $chartH = $H - $PT - $PB;
        $rows = count($items);
        $rowGap = $rows > 0 ? floor($chartH / $rows) : 40;
        $barH = max(14, $rowGap - 20);
        $toX = function ($val) use ($YL, $chartW, $scaleMax) {
            return $YL + ($scaleMax > 0 ? ($val / $scaleMax) * $chartW : 0);
        };
        ?>
        <div class="relative" style="width: 100%; height: <?= $H ?>px;">
            <svg class="w-full h-full" width="<?= $W ?>" height="<?= $H ?>" viewBox="0 0 <?= $W ?> <?= $H ?>">
                <defs>
                    <clipPath id="jbcat-clip"><rect x="<?= $YL ?>" y="<?= $PT ?>" height="<?= $chartH ?>" width="<?= $chartW ?>"></rect></clipPath>
                </defs>
                <g>
                    <?php for ($t = 0; $t <= $scaleMax; $t += $tickStep):
                        $x = $toX($t); ?>
                        <line stroke-dasharray="3 3" stroke="#E2E8F0" fill="none" x1="<?= $x ?>" y1="<?= $PT ?>" x2="<?= $x ?>" y2="<?= $PT + $chartH ?>"></line>
                    <?php endfor; ?>
                </g>
                <g>
                    <line x1="<?= $YL ?>" y1="<?= $PT + $chartH ?>" x2="<?= $YL + $chartW ?>" y2="<?= $PT + $chartH ?>" stroke="#94A3B8"></line>
                    <?php for ($t = 0; $t <= $scaleMax; $t += $tickStep):
                        $x = $toX($t); ?>
                        <line x1="<?= $x ?>" y1="<?= $PT + $chartH + 6 ?>" x2="<?= $x ?>" y2="<?= $PT + $chartH ?>" stroke="#94A3B8"></line>
                        <text x="<?= $x ?>" y="<?= $PT + $chartH + 18 ?>" text-anchor="middle" font-size="12" fill="#94A3B8"><?= $t ?></text>
                    <?php endfor; ?>
                </g>
                <g>
                    <?php
                    for ($i = 0; $i < $rows; $i++):
                        $tc = $items[$i];
                        $label = (string) ($tc['name'] ?? 'N/A');
                        $val = (int) ($tc['c'] ?? 0);
                        $yCenter = $PT + ($i * $rowGap) + ($rowGap / 2);
                        $y = $yCenter - ($barH / 2);
                        $xStart = $YL;
                        $xEndBg = $YL + $chartW;
                        $xEndVal = $toX($val);
                        ?>
                        <text x="<?= $YL - 6 ?>" y="<?= $yCenter ?>" text-anchor="end" font-size="12" fill="#94A3B8"><?= htmlspecialchars($label) ?></text>
                        <rect x="<?= $xStart ?>" y="<?= $y ?>" width="<?= $xEndBg - $xStart ?>" height="<?= $barH ?>" rx="<?= $barH / 2 ?>" fill="#E5E7EB"></rect>
                        <rect class="cat-bar" data-label="<?= htmlspecialchars($label) ?>" data-value="<?= $val ?>" x="<?= $xStart ?>" y="<?= $y ?>" width="<?= max(0, $xEndVal - $xStart) ?>" height="<?= $barH ?>" rx="<?= $barH / 2 ?>" fill="#6366F1"></rect>
                    <?php endfor; ?>
                </g>
            </svg>
            <div id="jbcat-tip" class="hidden absolute z-10 bg-slate-900 text-white text-sm px-3 py-2 rounded-md shadow"></div>
        </div>
        <script>
            (function(){
                var tip=document.getElementById('jbcat-tip');
                var bars=document.querySelectorAll('.cat-bar');
                function showTip(e){
                    var t=e.target;
                    var l=t.getAttribute('data-label')||'';
                    var v=t.getAttribute('data-value')||'';
                    tip.textContent=l+' • '+v;
                    tip.style.left=(e.offsetX+16)+'px';
                    tip.style.top=(e.offsetY-10)+'px';
                    tip.classList.remove('hidden');
                }
                function moveTip(e){
                    tip.style.left=(e.offsetX+16)+'px';
                    tip.style.top=(e.offsetY-10)+'px';
                }
                function hideTip(){ tip.classList.add('hidden'); }
                bars.forEach(function(b){ 
                    b.addEventListener('mouseenter', showTip);
                    b.addEventListener('mousemove', moveTip);
                    b.addEventListener('mouseleave', hideTip);
                    b.addEventListener('click', function(e){ var l=e.target.getAttribute('data-label')||''; if(l){ window.location.href='/master/reports?category='+encodeURIComponent(l); } });
                });
            })();
        </script>
    </div>
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold text-slate-800">Recent Activity</div>
                <div class="text-sm text-slate-500">Latest platform events</div>
            </div>
            <a href="/master/logs" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">View All</a>
        </div>
        <div class="mt-4 space-y-3">
            <?php
            $list = array_slice(($recentApplications ?? []), 0, 5);
            foreach ($list as $ra):
                $status = strtolower((string) ($ra['status'] ?? ''));
                $pill = 'bg-blue-50 text-blue-600 border-blue-200';
                $iconType = 'user';
                if ($status === 'shortlisted' || $status === 'approved') {
                    $pill = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                    $iconType = 'check';
                } elseif ($status === 'rejected') {
                    $pill = 'bg-red-50 text-red-600 border-red-200';
                    $iconType = 'x';
                } elseif ($status === 'pending' || $status === 'applied') {
                    $pill = 'bg-blue-50 text-blue-600 border-blue-200';
                }
                $title = (string) ($ra['job_title'] ?? 'Job');
                $subtitle = (string) ($ra['company_name'] ?? 'Employer');
                $appliedAt = (string) ($ra['applied_at'] ?? '');
                $timeText = '';
                if ($appliedAt !== '') {
                    $ts = strtotime($appliedAt);
                    if ($ts) {
                        $diff = time() - $ts;
                        if ($diff < 60) {
                            $timeText = 'Just now';
                        } elseif ($diff < 3600) {
                            $timeText = floor($diff / 60) . ' minutes ago';
                        } elseif ($diff < 86400) {
                            $timeText = floor($diff / 3600) . ' hours ago';
                        } else {
                            $timeText = floor($diff / 86400) . ' days ago';
                        }
                    }
                }
                ?>
            <a href="/master/reports?application=<?= (int)($ra['id'] ?? 0) ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-all duration-200 border border-slate-100 transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                    <?php if ($iconType === 'check'): ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?php elseif ($iconType === 'x'): ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <?php else: ?>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0"></path></svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-0.5">
                        <div class="truncate text-sm font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors"><?= htmlspecialchars($title) ?></div>
                        <span class="ml-2 shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-xs font-medium <?= $pill ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 mt-0.5">
                        <span class="truncate font-medium"><?= htmlspecialchars($subtitle) ?></span>
                        <span class="text-slate-400"><?= htmlspecialchars($timeText) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-200">
        <div class="flex items-center justify-between mb-4">
             <div class="text-lg font-semibold">Top Employers</div>
             <a href="/master/employers" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">View All</a>
        </div>
        <div class="space-y-3">
            <?php foreach (($topEmployers ?? []) as $te): ?>
            <a href="/master/employers?search=<?= urlencode((string)($te['company_name'] ?? '')) ?>" class="flex items-center justify-between border-b border-slate-100 pb-2 flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-all duration-200 border border-transparent hover:border-slate-100 transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                <div class="flex items-center gap-3">
                     <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm">
                        <?= strtoupper(substr($te['company_name'] ?? 'E', 0, 1)) ?>
                     </div>
                     <div class="text-sm text-gray-900 font-medium group-hover:text-indigo-700 transition-colors">
                        <?= htmlspecialchars($te['company_name'] ?? 'Employer') ?>
                     </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold">
                        <?= (int) ($te['jobs'] ?? 0) ?> jobs
                    </span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-200">
        <div class="text-lg font-semibold mb-4">Quick Actions</div>
        <div class="grid grid-cols-2 gap-4">
            <a href="/master/settings" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md transform hover:scale-105 transition-all duration-300 hover:shadow-lg group">
                <svg class="h-6 w-6 mb-2 group-hover:animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-sm font-medium">Settings</span>
            </a>
            <a href="/master/system/cron" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-md transform hover:scale-105 transition-all duration-300 hover:shadow-lg group">
                <svg class="h-6 w-6 mb-2 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-medium">Cron</span>
            </a>
            <a href="/master/reports" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-md transform hover:scale-105 transition-all duration-300 hover:shadow-lg group">
                <svg class="h-6 w-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="text-sm font-medium">Reports</span>
            </a>
            <a href="/master/logs" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md transform hover:scale-105 transition-all duration-300 hover:shadow-lg group">
                <svg class="h-6 w-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span class="text-sm font-medium">Logs</span>
            </a>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="text-lg font-semibold">Revenue Trend</div>
                <div class="text-xs text-gray-500">Last 6 months</div>
            </div>
            <?php
            $momLbl = '';
            $momClr = 'bg-slate-100 text-slate-700';
            if ($rcount >= 2) {
                $prev = (float)($rvals[$rcount-2] ?? 0);
                $curr = (float)($rvals[$rcount-1] ?? 0);
                $diff = $curr - $prev;
                $pct = ($prev > 0) ? round(($diff/$prev)*100, 1) : ($curr > 0 ? 100.0 : 0.0);
                if ($diff > 0) { $momLbl = '+'.$pct.'%'; $momClr = 'bg-emerald-100 text-emerald-700'; }
                elseif ($diff < 0) { $momLbl = $pct.'%'; $momClr = 'bg-red-100 text-red-700'; }
                else { $momLbl = '0%'; $momClr = 'bg-slate-100 text-slate-700'; }
            }
            ?>
            <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $momClr ?>"><?= $momLbl ?></span>
        </div>
        <?php
        $rmonths = $series['revenue_months'] ?? [];
        $rvals = $series['revenue'] ?? [];
        $rcount = count($rmonths);
        $rmax = 1;
        for ($i = 0; $i < $rcount; $i++) { $rmax = max($rmax, (float)($rvals[$i] ?? 0)); }
        $RW = 445; $RH = 180; $RPL = 40; $RPR = 10; $RPT = 10; $RPB = 30;
        $RchartW = $RW - $RPL - $RPR; $RchartH = $RH - $RPT - $RPB;
        $RstepX = $rcount > 1 ? $RchartW / ($rcount - 1) : $RchartW;
        $rToX = function ($i) use ($RPL, $RstepX) { return $RPL + ($i * $RstepX); };
        $rToY = function ($v) use ($RPT, $RchartH, $rmax) { $ratio = $rmax > 0 ? ($v / $rmax) : 0; return $RPT + ($RchartH - ($ratio * $RchartH)); };
        $rPath = '';
        for ($i = 0; $i < $rcount; $i++) { $x = $rToX($i); $y = $rToY((float)($rvals[$i] ?? 0)); $rPath .= ($i === 0 ? 'M' : 'L') . $x . ' ' . $y . ' '; }
        $rBaseY = $RPT + $RchartH;
        $rArea = $rPath . 'L ' . $rToX($rcount - 1) . ' ' . $rBaseY . ' L ' . $rToX(0) . ' ' . $rBaseY . ' Z';
        ?>
        <div id="revenue-chart" class="relative" style="width:100%; height: <?= $RH ?>px;">
            <svg width="<?= $RW ?>" height="<?= $RH ?>" viewBox="0 0 <?= $RW ?> <?= $RH ?>" class="w-full h-full">
                <rect x="0" y="0" width="<?= $RW ?>" height="<?= $RH ?>" fill="transparent"></rect>
                <line x1="<?= $RPL ?>" y1="<?= $RPT + $RchartH ?>" x2="<?= $RPL + $RchartW ?>" y2="<?= $RPT + $RchartH ?>" stroke="#94A3B8" stroke-width="1"></line>
                <?php for ($i = 0; $i < $rcount; $i++): $x = $rToX($i); ?>
                    <line x1="<?= $x ?>" y1="<?= $RPT + $RchartH ?>" x2="<?= $x ?>" y2="<?= $RPT + $RchartH + 4 ?>" stroke="#CBD5E1" stroke-width="1"></line>
                    <text x="<?= $x ?>" y="<?= $RPT + $RchartH + 18 ?>" text-anchor="middle" font-size="11" fill="#475569"><?= htmlspecialchars($rmonths[$i] ?? '') ?></text>
                <?php endfor; ?>
                <path id="revArea" d="<?= $rArea ?>" fill="url(#revGradient)" stroke="none"></path>
                <path id="revLine" d="<?= $rPath ?>" fill="none" stroke="#EF4444" stroke-width="2"></path>
                <?php for ($i = 0; $i < $rcount; $i++): $x = $rToX($i); $y = $rToY((float)($rvals[$i] ?? 0)); ?>
                    <circle class="rev-dot" data-month="<?= htmlspecialchars($rmonths[$i] ?? '') ?>" data-val="<?= (float)($rvals[$i] ?? 0) ?>" cx="<?= $x ?>" cy="<?= $y ?>" r="3" fill="#EF4444"></circle>
                <?php endfor; ?>
                <defs>
                    <linearGradient id="revGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stop-color="#EF4444" stop-opacity="0.20"></stop>
                        <stop offset="95%" stop-color="#EF4444" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
            </svg>
            <div id="rev-tip" class="hidden absolute z-20 bg-slate-900 text-white text-sm px-3 py-2 rounded-md shadow"></div>
        </div>
        <script>
            (function(){
                var tip=document.getElementById('rev-tip');
                var container=document.getElementById('revenue-chart');
                var dots=document.querySelectorAll('.rev-dot');
                function showTip(e){
                    var d=e.target;
                    var m=d.getAttribute('data-month')||'';
                    var v=d.getAttribute('data-val')||'0';
                    tip.textContent=m+' • ₹'+Number(v).toFixed(2);
                    var rect=container.getBoundingClientRect();
                    var x=e.clientX-rect.left+12;
                    var y=e.clientY-rect.top-10;
                    var maxX=(container.clientWidth - tip.offsetWidth - 8);
                    var maxY=(container.clientHeight - tip.offsetHeight - 8);
                    x=Math.max(8, Math.min(x, maxX));
                    y=Math.max(8, Math.min(y, maxY));
                    tip.style.left=x+'px';
                    tip.style.top=y+'px';
                    tip.classList.remove('hidden');
                }
                function moveTip(e){
                    var rect=container.getBoundingClientRect();
                    var x=e.clientX-rect.left+12;
                    var y=e.clientY-rect.top-10;
                    var maxX=(container.clientWidth - tip.offsetWidth - 8);
                    var maxY=(container.clientHeight - tip.offsetHeight - 8);
                    x=Math.max(8, Math.min(x, maxX));
                    y=Math.max(8, Math.min(y, maxY));
                    tip.style.left=x+'px';
                    tip.style.top=y+'px';
                }
                function hideTip(){ tip.classList.add('hidden'); }
                dots.forEach(function(d){ d.addEventListener('mouseenter', showTip); d.addEventListener('mousemove', moveTip); d.addEventListener('mouseleave', hideTip); });
                var line=document.getElementById('revLine'); if(line){ var len=line.getTotalLength(); line.style.strokeDasharray=len+' '+len; line.style.strokeDashoffset=len; line.style.transition='stroke-dashoffset 800ms ease'; setTimeout(function(){ line.style.strokeDashoffset='0'; },50); }
                var area=document.getElementById('revArea'); if(area){ area.style.opacity='0'; area.style.transition='opacity 600ms ease'; setTimeout(function(){ area.style.opacity='1'; },80); }
            })();
        </script>
    </div>
    <div class="hidden lg:block"></div>
    <div class="hidden lg:block"></div>
</div>

<!-- Floating Chat Widget -->
<div id="chat-widget" class="fixed bottom-6 right-6 z-50">
    <!-- Chat Toggle Button -->
    <button id="chat-toggle" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-lg transition-transform transform hover:scale-110 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-200 flex flex-col transition-all duration-300 transform origin-bottom-right scale-95 opacity-0">
        <!-- Chat Header -->
        <div class="bg-indigo-600 p-4 text-white flex justify-between items-center">
            <h3 class="font-bold">Live Support</h3>
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
        </div>
        
        <!-- Chat Messages -->
        <div id="chat-messages" class="p-4 h-64 overflow-y-auto bg-gray-50 space-y-3">
            <div class="flex justify-start">
                <div class="bg-white p-2 rounded-lg rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[80%] border border-gray-100">
                    Hello! How can I help you today?
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-3 bg-white border-t border-gray-100">
            <div class="flex gap-2">
                <input type="text" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <button class="bg-indigo-600 text-white rounded-full p-2 hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('chat-toggle');
        const chatWindow = document.getElementById('chat-window');
        const messagesContainer = document.getElementById('chat-messages');
        
        let isOpen = false;

        toggleBtn.addEventListener('click', function() {
            isOpen = !isOpen;
            if (isOpen) {
                chatWindow.classList.remove('hidden');
                // Small timeout to allow display:block to apply before transition
                setTimeout(() => {
                    chatWindow.classList.remove('scale-95', 'opacity-0');
                    chatWindow.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                chatWindow.classList.remove('scale-100', 'opacity-100');
                chatWindow.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    chatWindow.classList.add('hidden');
                }, 300);
            }
        });

        // Simulate live messages
        setTimeout(() => {
            const msg = document.createElement('div');
            msg.className = 'flex justify-start animate-pulse';
            msg.innerHTML = '<div class="bg-white p-2 rounded-lg rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[80%] border border-gray-100">System is running smoothly.</div>';
            messagesContainer.appendChild(msg);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 3000);
    });
</script>
