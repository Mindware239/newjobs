<div>
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
    <p class="mt-2 text-sm text-gray-600">Manage application configuration and view system status</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
    <?php
      $cards = [
        ['label'=>'Database','ok'=>!empty($status['database']),'icon'=>'db'],
        ['label'=>'Redis','ok'=>!empty($status['redis']),'icon'=>'redis','warn'=>true],
        ['label'=>'Elasticsearch','ok'=>!empty($status['elasticsearch']),'icon'=>'search','warn'=>true],
        ['label'=>'Google OAuth','ok'=>!empty($status['google_oauth']),'icon'=>'shield'],
        ['label'=>'Apple OAuth','ok'=>!empty($status['apple_oauth']),'icon'=>'shield'],
        ['label'=>'Razorpay','ok'=>!empty($status['razorpay']),'icon'=>'card'],
      ];
      foreach ($cards as $c):
        $ok = !empty($c['ok']);
        $warn = !empty($c['warn']) && !$ok;
        $pillColor = $ok ? 'bg-green-100 text-green-700' : ($warn ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
        $dotColor = $ok ? 'bg-green-500' : ($warn ? 'bg-yellow-500' : 'bg-red-500');
        $text = $ok ? ($c['label']==='Database' ? 'Connected' : 'Configured') : ($warn ? 'Disabled' : 'Unavailable');
    ?>
    <div class="bg-white rounded-2xl shadow p-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
          <?php if ($c['icon']==='db'): ?>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="8" ry="3" stroke-width="2"></ellipse><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"></path></svg>
          <?php elseif ($c['icon']==='redis'): ?>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 8l9-4 9 4-9 4-9-4z"></path><path stroke-width="2" d="M3 12l9 4 9-4"></path><path stroke-width="2" d="M3 16l9 4 9-4"></path></svg>
          <?php elseif ($c['icon']==='search'): ?>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="2"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20l-3-3"></path></svg>
          <?php elseif ($c['icon']==='shield'): ?>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l9 4v6c0 7-9 10-9 10S3 19 3 12V6l9-4z"></path></svg>
          <?php else: ?>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" ry="2" stroke-width="2"></rect><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 9h20"></path></svg>
          <?php endif; ?>
        </span>
        <div>
          <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($c['label']) ?></div>
          <div class="text-xs text-gray-500">Service status</div>
        </div>
      </div>
      <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold <?= $pillColor ?>">
        <span class="w-2 h-2 rounded-full <?= $dotColor ?>"></span>
        <?= $text ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
      <div class="flex items-start gap-3 mb-4">
        <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
        </span>
        <div>
          <h2 class="text-xl font-semibold">Application Settings</h2>
          <div class="text-sm text-gray-600">General configuration options</div>
        </div>
      </div>
      <form method="POST" action="/master/settings" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Name</label>
          <input type="text" name="app_name" value="<?= htmlspecialchars($app['name'] ?? '') ?>" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">URL</label>
          <input type="text" name="app_url" value="<?= htmlspecialchars($app['url'] ?? '') ?>" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Timezone</label>
          <input type="text" name="app_timezone" value="<?= htmlspecialchars($app['timezone'] ?? '') ?>" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Locale</label>
          <input type="text" name="app_locale" value="<?= htmlspecialchars($app['locale'] ?? '') ?>" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Debug</label>
          <select name="app_debug" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain">
            <?php $dbg = !empty($app['debug']); ?>
            <option value="true" <?= $dbg?'selected':'' ?>>true</option>
            <option value="false" <?= !$dbg?'selected':'' ?>>false</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <h3 class="text-lg font-semibold mt-4 mb-2">Email Settings</h3>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-600 mb-1">Email Footer Text</label>
          <p class="text-xs text-gray-500 mb-2">HTML allowed. Appears at the bottom of all emails.</p>
          <textarea name="email_footer" rows="3" class="w-full px-3 py-2 border rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigoMain"><?= htmlspecialchars($settings['email_footer'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2 flex justify-end">
          <button class="px-5 py-2.5 bg-indigo-500 text-white rounded-xl shadow hover:bg-indigo-700">Save Settings</button>
        </div>
      </form>
    </div>
    <?php
      function iconFor($t){
        switch($t){
          case 'success': return '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
          case 'warning': return '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10 3h4l9 16H1L10 3z"></path></svg>';
          case 'db': return '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="6" ry="2.2" stroke-width="2"></ellipse></svg>';
          case 'mail': return '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8l8 5 8-5"></path></svg>';
          default: return '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"></circle></svg>';
        }
      }
      function colorFor($t){
        switch($t){
          case 'success': return 'bg-green-100 text-green-700';
          case 'warning': return 'bg-yellow-100 text-yellow-800';
          case 'db': return 'bg-purple-100 text-purple-700';
          case 'mail': return 'bg-indigo-100 text-indigo-700';
          default: return 'bg-slate-100 text-slate-700';
        }
      }
    ?>
    <div class="bg-white rounded-2xl shadow p-6">
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-start gap-3">
          <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity h-5 w-5 text-primary"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg>          </span>
          <div>
            <div class="text-xl font-semibold">Live Activity</div>
            <div class="text-sm text-gray-600">Real-time system events</div>
          </div>
        </div>
        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
          <span class="w-2 h-2 rounded-full bg-green-500"></span> Live
        </span>
      </div>
      <div class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">Active Users</div>
            <div class="mt-1 text-lg font-semibold text-gray-900"><span id="hm-active-total">0</span></div>
            <div class="mt-2 flex flex-wrap gap-1">
              <span id="hm-active-admin" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">Admin 0</span>
              <span id="hm-active-employer" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">Employer 0</span>
              <span id="hm-active-candidate" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">Candidate 0</span>
            </div>
          </div>
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">Requests Per Minute</div>
            <div class="mt-1 text-lg font-semibold text-gray-900"><span id="hm-rpm-current">0</span></div>
            <div class="mt-2 text-xs text-gray-600">Peak 5m <span id="hm-rpm-peak">0</span></div>
          </div>
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">Errors</div>
            <div class="mt-1">
              <span id="hm-errors-pill" class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">0</span>
            </div>
          </div>
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">Queue</div>
            <div class="mt-1 text-lg font-semibold text-gray-900"><span id="hm-queue-queued">0</span></div>
            <div class="mt-2 text-xs text-gray-600">Failed <span id="hm-queue-failed">0</span> • Avg <span id="hm-queue-avg">0</span>ms</div>
          </div>
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">DB Slow Queries</div>
            <div class="mt-1">
              <span id="hm-db-slow-pill" class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">0</span>
            </div>
          </div>
          <div class="rounded-md border border-gray-200 p-3">
            <div class="text-xs text-gray-600">Server</div>
            <div class="mt-1 text-sm text-gray-800">CPU <span id="hm-cpu">-</span>% • RAM <span id="hm-ram">-</span>% • Disk <span id="hm-disk">-</span>%</div>
            <div class="mt-2">
              <span id="hm-server-pill" class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-800">OK</span>
            </div>
          </div>
        </div>
      </div>
      <div class="relative">
        <div class="h-[420px] overflow-y-auto pr-2">
          <ul class="space-y-4">
            <?php foreach($liveEvents as $ev): ?>
              <li class="flex items-start gap-3">
                <div class="relative flex flex-col items-center">
                  <span class="w-7 h-7 rounded-full flex items-center justify-center <?= colorFor($ev['type']) ?>">
                    <?= iconFor($ev['type']) ?>
                  </span>
                  <span class="flex-1 w-px bg-slate-200 grow"></span>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <div class="text-sm text-gray-800"><?= htmlspecialchars($ev['title']) ?></div>
                    <?php if (!empty($ev['new'])): ?>
                      <span class="px-2 py-0.5 rounded-full bg-purple-600 text-white text-xs">New</span>
                    <?php endif; ?>
                  </div>
                  <div class="text-xs text-gray-500"><?= htmlspecialchars($ev['time']) ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="text-xs text-gray-500 mt-3">Showing last <?= count($liveEvents) ?> events</div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      var $ = function(id){ return document.getElementById(id); };
      var setPill = function(el, level){
        el.classList.remove('bg-green-100','text-green-800','bg-orange-100','text-orange-800','bg-red-100','text-red-800');
        if (level === 'green') { el.classList.add('bg-green-100','text-green-800'); }
        else if (level === 'orange') { el.classList.add('bg-orange-100','text-orange-800'); }
        else { el.classList.add('bg-red-100','text-red-800'); }
      };
      var update = function(d){
        if (!d) return;
        var roles = d.active_users_roles || {};
        if ($('hm-active-total')) $('hm-active-total').textContent = d.active_users_total || 0;
        if ($('hm-active-admin')) $('hm-active-admin').textContent = 'Admin ' + (roles.Admin || 0);
        if ($('hm-active-employer')) $('hm-active-employer').textContent = 'Employer ' + (roles.Employer || 0);
        if ($('hm-active-candidate')) $('hm-active-candidate').textContent = 'Candidate ' + (roles.Candidate || 0);
        if ($('hm-rpm-current')) $('hm-rpm-current').textContent = d.rpm_current || 0;
        if ($('hm-rpm-peak')) $('hm-rpm-peak').textContent = d.rpm_peak_5m || 0;
        var errCount = Array.isArray(d.errors) ? d.errors.length : (d.errors || 0);
        var errEl = $('hm-errors-pill'); if (errEl) { errEl.textContent = errCount; setPill(errEl, errCount === 0 ? 'green' : (errCount <= 3 ? 'orange' : 'red')); }
        var q = d.queue || {};
        if ($('hm-queue-queued')) $('hm-queue-queued').textContent = q.queued || 0;
        if ($('hm-queue-failed')) {
          $('hm-queue-failed').textContent = q.failed || 0;
          var level = (q.failed || 0) === 0 ? 'green' : ((q.failed || 0) <= 3 ? 'orange' : 'red');
          var pill = $('hm-queue-failed').parentElement;
        }
        if ($('hm-queue-avg')) $('hm-queue-avg').textContent = q.avg_ms || 0;
        var slowCount = Array.isArray(d.db_slow) ? d.db_slow.length : (d.db_slow || 0);
        var slowEl = $('hm-db-slow-pill'); if (slowEl) { slowEl.textContent = slowCount; setPill(slowEl, slowCount === 0 ? 'green' : 'orange'); }
        var cpu = d.server && d.server.cpu != null ? d.server.cpu : '-';
        var ram = d.server && d.server.ram != null ? d.server.ram : '-';
        var disk = d.server && d.server.disk != null ? d.server.disk : '-';
        if ($('hm-cpu')) $('hm-cpu').textContent = cpu;
        if ($('hm-ram')) $('hm-ram').textContent = ram;
        if ($('hm-disk')) $('hm-disk').textContent = disk;
        var risk = 0;
        if (typeof cpu === 'number' && cpu >= 80) risk++;
        if (typeof disk === 'number' && disk >= 85) risk++;
        if (typeof ram === 'number' && ram >= 85) risk++;
        var serverEl = $('hm-server-pill'); if (serverEl) { setPill(serverEl, risk === 0 ? 'green' : (risk === 1 ? 'orange' : 'red')); serverEl.textContent = risk === 0 ? 'OK' : (risk === 1 ? 'High' : 'Critical'); }
      };
      var refresh = function(){
        try {
          fetch('/master/settings/live', { headers: { 'Accept': 'application/json' }})
            .then(function(r){ return r.json(); })
            .then(update)
            .catch(function(){});
        } catch(e) {}
      };
      refresh();
      setInterval(refresh, 15000);
    })();
  </script>

  <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
      <h2 class="text-xl font-semibold mb-4">Auto-Apply Jobs Monitoring</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-md border border-gray-200 p-4">
          <div class="text-sm text-gray-600">Auto-applies today</div>
          <div class="mt-2 text-2xl font-bold text-gray-900"><?= (int)($metrics['auto_applies_today'] ?? 0) ?></div>
        </div>
        <div class="rounded-md border border-gray-200 p-4">
          <div class="text-sm text-gray-600">Failed auto-applies</div>
          <div class="mt-2 text-2xl font-bold text-gray-900"><?= (int)($metrics['failed_auto_applies_today'] ?? 0) ?></div>
        </div>
        <div class="rounded-md border border-gray-200 p-4">
          <div class="text-sm text-gray-600">Avg. match score</div>
          <div class="mt-2 text-2xl font-bold text-gray-900"><?= number_format((float)($metrics['avg_match_score_today'] ?? 0.0), 1) ?>%</div>
        </div>
        <div class="rounded-md border border-gray-200 p-4">
          <div class="text-sm text-gray-600">Server load</div>
          <?php $alert = !empty($metrics['load_alert']); ?>
          <div class="mt-2">
            <span class="px-2 py-1 rounded text-xs font-semibold <?= $alert?'bg-orange-100 text-orange-800':'bg-green-100 text-green-800' ?>">
              <?= $alert?'High (80%+ of daily limit)':'Normal' ?>
            </span>
          </div>
        </div>
      </div>
      <div class="mt-6">
        <form method="POST" action="/master/settings" class="inline-flex items-center gap-3">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="auto_apply_enabled" value="0">
          <button class="px-4 py-2 bg-red-600 text-white rounded">Emergency Stop</button>
          <span class="text-sm text-gray-600">Disables auto-apply immediately</span>
        </form>
      </div>
      <?php
        $hourly = $metrics['hourly_auto_applies'] ?? null;
        if (!is_array($hourly)) {
          $total = (int)($metrics['auto_applies_today'] ?? 0);
          $hourly = [];
          for ($i=0;$i<24;$i++) {
            $hourly[$i] = $total > 0 ? (int)floor($total/24) + ($i%6===0 ? 1 : 0) : 0;
          }
        }
        $maxVal = max(1, max($hourly));
        $W = 860; $H = 220; $PL = 44; $PR = 20; $PT = 16; $PB = 34;
        $chartW = $W - $PL - $PR; $chartH = $H - $PT - $PB;
        $points = [];
        for ($i=0;$i<24;$i++) {
          $x = $PL + ($chartW * ($i/(23)));
          $y = $PT + ($chartH - (($hourly[$i]/$maxVal) * $chartH));
          $points[] = [$x,$y,$hourly[$i],$i];
        }
        $path = '';
        foreach ($points as $idx=>$p) {
          $path .= ($idx===0 ? 'M ' : ' L ').round($p[0],2).','.round($p[1],2);
        }
        $area = $path.' L '.($PL+$chartW).','.($PT+$chartH).' L '.$PL.','.($PT+$chartH).' Z';
        $success = max(0, (int)($metrics['auto_applies_today'] ?? 0) - (int)($metrics['failed_auto_applies_today'] ?? 0));
        $successRate = ($metrics['auto_applies_today'] ?? 0) > 0 ? round(($success/ (int)$metrics['auto_applies_today']) * 100, 1) : 0;
      ?>
      <div class="mt-6 rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 bg-gray-50">
          <div>
            <div class="text-sm font-medium text-gray-800">Today Activity</div>
            <div class="text-xs text-gray-500">Hourly auto-applies</div>
          </div>
          <div class="flex items-center gap-4">
            <div class="text-xs text-gray-600">Max <?= (int)$maxVal ?>/hr</div>
            <div class="inline-flex items-center gap-2 text-sm">
              <span class="w-3 h-3 rounded bg-indigo-600"></span>
              <span class="text-gray-700">Auto-apply</span>
            </div>
          </div>
        </div>
        <div class="relative" id="aa-chart" style="height:<?= $H ?>px">
          <div id="aa-tip" class="absolute pointer-events-none px-2 py-1 rounded bg-black/70 text-white text-xs hidden"></div>
          <svg width="100%" viewBox="0 0 <?= $W ?> <?= $H ?>" preserveAspectRatio="none">
            <defs>
              <linearGradient id="aaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.25"></stop>
                <stop offset="100%" stop-color="#4f46e5" stop-opacity="0.02"></stop>
              </linearGradient>
              <pattern id="gridDots" width="6" height="6" patternUnits="userSpaceOnUse">
                <circle cx="3" cy="3" r="0.8" fill="#e5e7eb"></circle>
              </pattern>
            </defs>
            <rect x="<?= $PL ?>" y="<?= $PT ?>" width="<?= $chartW ?>" height="<?= $chartH ?>" fill="url(#gridDots)"></rect>
            <g stroke="#9ca3af" stroke-width="1" stroke-dasharray="4 4" fill="none">
              <line x1="<?= $PL ?>" y1="<?= $PT ?>" x2="<?= $PL+$chartW ?>" y2="<?= $PT ?>"></line>
              <line x1="<?= $PL ?>" y1="<?= $PT + $chartH/2 ?>" x2="<?= $PL+$chartW ?>" y2="<?= $PT + $chartH/2 ?>"></line>
              <line x1="<?= $PL ?>" y1="<?= $PT + $chartH ?>" x2="<?= $PL+$chartW ?>" y2="<?= $PT + $chartH ?>"></line>
            </g>
            <g fill="#6b7280" font-size="10">
              <text x="<?= $PL-6 ?>" y="<?= $PT+4 ?>" text-anchor="end"><?= (int)$maxVal ?></text>
              <text x="<?= $PL-6 ?>" y="<?= $PT + $chartH/2 + 4 ?>" text-anchor="end"><?= (int)round($maxVal/2) ?></text>
              <text x="<?= $PL-6 ?>" y="<?= $PT + $chartH + 4 ?>" text-anchor="end">0</text>
            </g>
            <g fill="#6b7280" font-size="10">
              <text x="<?= $PL ?>" y="<?= $PT + $chartH + 18 ?>">0h</text>
              <text x="<?= $PL + $chartW*0.25 ?>" y="<?= $PT + $chartH + 18 ?>">6h</text>
              <text x="<?= $PL + $chartW*0.5 ?>" y="<?= $PT + $chartH + 18 ?>">12h</text>
              <text x="<?= $PL + $chartW*0.75 ?>" y="<?= $PT + $chartH + 18 ?>">18h</text>
              <text x="<?= $PL + $chartW ?>" y="<?= $PT + $chartH + 18 ?>" text-anchor="end">23h</text>
            </g>
            <path id="aaArea" d="<?= $area ?>" fill="url(#aaGrad)"></path>
            <path id="aaLine" d="<?= $path ?>" stroke="#4f46e5" stroke-width="2" fill="none"></path>
            <?php foreach ($points as $pt): ?>
              <circle class="aa-dot" cx="<?= round($pt[0],2) ?>" cy="<?= round($pt[1],2) ?>" r="3.5" fill="#4f46e5" data-hour="<?= (int)$pt[3] ?>" data-val="<?= (int)$pt[2] ?>"></circle>
            <?php endforeach; ?>
          </svg>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded bg-indigo-600"></span>
            <span>Total <?= (int)($metrics['auto_applies_today'] ?? 0) ?></span>
          </div>
          <div>Failed <?= (int)($metrics['failed_auto_applies_today'] ?? 0) ?></div>
          <div>Success <?= (int)$success ?></div>
          <div>Success Rate <?= number_format($successRate,1) ?>%</div>
        </div>
      </div>
      <script>
        (function(){
          var tip=document.getElementById('aa-tip');
          var container=document.getElementById('aa-chart');
          var dots=document.querySelectorAll('.aa-dot');
          var line=document.getElementById('aaLine');
          if(line){
            var len=line.getTotalLength();
            line.style.strokeDasharray=len+' '+len;
            line.style.strokeDashoffset=len;
            line.style.transition='stroke-dashoffset 800ms ease';
            setTimeout(function(){ line.style.strokeDashoffset='0'; }, 60);
          }
          dots.forEach(function(d){
            d.addEventListener('mouseenter', function(e){
              var h=d.getAttribute('data-hour');
              var v=d.getAttribute('data-val');
              var rect=container.getBoundingClientRect();
              tip.textContent=h+'h • '+v+' jobs';
              tip.classList.remove('hidden');
              var x=e.clientX-rect.left+10;
              var y=e.clientY-rect.top-14;
              var maxX=container.clientWidth - tip.offsetWidth - 8;
              var maxY=container.clientHeight - tip.offsetHeight - 8;
              tip.style.left=Math.max(8, Math.min(maxX, x))+'px';
              tip.style.top=Math.max(8, Math.min(maxY, y))+'px';
            });
            d.addEventListener('mouseleave', function(){
              tip.classList.add('hidden');
            });
          });
        })();
      </script>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Auto-Apply Settings</h2>
      <form method="POST" action="/master/settings" class="grid grid-cols-1 gap-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="flex items-center justify-between">
          <label class="text-sm text-gray-700">Enable Auto-Apply</label>
          <input type="checkbox" name="auto_apply_enabled" value="1" <?= !empty($autoApply['enabled']) ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Minimum Match Score</label>
          <input type="number" min="0" max="100" name="auto_apply_min_match_score" value="<?= (int)($autoApply['min_match_score'] ?? 70) ?>" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Max per Candidate / Day</label>
          <input type="number" min="0" name="auto_apply_max_per_candidate_per_day" value="<?= (int)($autoApply['max_per_candidate_per_day'] ?? 3) ?>" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Company Cooldown (days)</label>
          <input type="number" min="0" name="auto_apply_company_cooldown_days" value="<?= (int)($autoApply['company_cooldown_days'] ?? 30) ?>" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Daily Global Limit</label>
          <input type="number" min="0" name="auto_apply_daily_global_limit" value="<?= (int)($autoApply['daily_global_limit'] ?? 1000) ?>" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Min Profile Strength (%)</label>
          <input type="number" min="0" max="100" name="auto_apply_min_profile_strength" value="<?= (int)($autoApply['min_profile_strength'] ?? 60) ?>" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Mandatory Sections (comma-separated keys)</label>
          <input type="text" name="auto_apply_mandatory_sections" value="<?= htmlspecialchars($autoApply['mandatory_sections'] ?? '') ?>" class="w-full px-3 py-2 border rounded" placeholder="education,experience,skills">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Pause After Rejections (count)</label>
            <input type="number" min="0" name="auto_apply_pause_rejections_threshold" value="<?= (int)($autoApply['pause_rejections_threshold'] ?? 0) ?>" class="w-full px-3 py-2 border rounded">
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Pause Window (days)</label>
            <input type="number" min="0" name="auto_apply_pause_rejections_days" value="<?= (int)($autoApply['pause_rejections_days'] ?? 30) ?>" class="w-full px-3 py-2 border rounded">
          </div>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Blacklist Candidate User IDs (comma-separated)</label>
          <input type="text" name="auto_apply_blacklist_candidates" value="<?= htmlspecialchars($autoApply['blacklist_candidates'] ?? '') ?>" class="w-full px-3 py-2 border rounded" placeholder="12,45,78">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Blacklist Employer IDs (comma-separated)</label>
          <input type="text" name="auto_apply_blacklist_employers" value="<?= htmlspecialchars($autoApply['blacklist_employers'] ?? '') ?>" class="w-full px-3 py-2 border rounded" placeholder="5,9,14">
        </div>
        <div>
          <button class="w-full px-4 py-2 bg-blue-600 text-white rounded">Save Auto-Apply Settings</button>
        </div>
      </form>
    </div>
  </div>
</div>
