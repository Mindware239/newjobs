<div>
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">System Monitor</h1>
      <div class="text-sm text-gray-600">Performance analysis and root cause</div>
    </div>
    <div class="text-xs text-gray-600">Request ID: <span id="sm-rid">-</span></div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
      <div class="text-lg font-semibold mb-3">Performance Trends</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">RPM (req/min, 1h) <span id="u-rpm-1h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-rpm-1h" class="h-16"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Avg Response Time (ms, 1h) <span id="u-resp-1h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-resp-1h" class="h-16"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Errors/min (1h) <span id="u-err-1h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-err-1h" class="h-16"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">CPU (% , 1h) <span id="u-cpu-1h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-cpu-1h" class="h-16"></div>
        </div>
      </div>
      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">RPM (req/min, 24h) <span id="u-rpm-24h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-rpm-24h" class="h-16"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Avg Response Time (ms, 24h) <span id="u-resp-24h" class="float-right text-[10px] text-gray-500"></span></div>
          <div id="chart-resp-24h" class="h-16"></div>
        </div>
      </div>
      <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">CPU (%)</div>
          <div id="donut-cpu" class="h-24"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">RAM (%)</div>
          <div id="donut-ram" class="h-24"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Error Rate (last min, %)</div>
          <div id="donut-err" class="h-24"></div>
        </div>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6">
      <div class="text-lg font-semibold mb-3">Alert Center</div>
      <ul id="sm-alerts" class="space-y-2"></ul>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
      <div class="text-lg font-semibold mb-3">Database Load Insights</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Slow Queries Trend (1h)</div>
          <div id="chart-slow-1h" class="h-16"></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600 mb-2">Top Slow Tables</div>
          <ul id="sm-top-slowest" class="text-sm text-gray-800 space-y-1"></ul>
        </div>
      </div>
      <div class="mt-4">
        <div class="text-xs text-gray-600 mb-1">Average Query Time</div>
        <div id="sm-avg-per-table" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"></div>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6">
      <div class="text-lg font-semibold mb-3">Queue & Cron</div>
      <div class="rounded-md border border-gray-200 p-3 mb-3">
        <div class="text-xs text-gray-600 mb-2">Processed / min (1h)</div>
        <div id="chart-processed-1h" class="h-16"></div>
      </div>
      <div class="rounded-md border border-gray-200 p-3 mb-3">
        <div class="text-xs text-gray-600 mb-2">Failed jobs (1h)</div>
        <div id="chart-failed-1h" class="h-16"></div>
      </div>
      <div>
        <div class="text-xs text-gray-600 mb-1">Long-running jobs</div>
        <ul id="sm-long-running" class="text-sm text-gray-800 space-y-1 max-h-40 overflow-y-auto"></ul>
      </div>
      <div class="mt-3">
        <div class="text-xs text-gray-600 mb-1">Last cron</div>
        <div id="sm-last-cron" class="text-sm text-gray-800"></div>
      </div>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow p-6 lg:col-span-3">
      <div class="flex items-center justify-between">
        <div class="text-lg font-semibold">Request Tracing</div>
        <div class="flex items-center gap-2">
          <input id="trace-rid" type="text" class="px-2 py-1 border rounded text-sm" placeholder="Request ID">
          <input id="trace-path" type="text" class="px-2 py-1 border rounded text-sm" placeholder="Endpoint path">
          <button id="trace-search" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Search</button>
        </div>
      </div>
      <div class="mt-3 max-h-64 overflow-y-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600">
              <th class="px-2 py-1">Time</th>
              <th class="px-2 py-1">Endpoint</th>
              <th class="px-2 py-1">Status</th>
              <th class="px-2 py-1">Role</th>
              <th class="px-2 py-1">Duration</th>
              <th class="px-2 py-1">Request ID</th>
            </tr>
          </thead>
          <tbody id="trace-rows"></tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    (function(){
      var $ = function(id){ return document.getElementById(id); };
      var spark = function(el, arr){
        if (!el) return;
        var w = el.clientWidth || 300, h = el.clientHeight || 64;
        var max = Math.max.apply(null, arr.concat([1]));
        var min = Math.min.apply(null, arr.concat([0]));
        var pad = 4;
        var dx = (w - pad*2) / Math.max(1, arr.length - 1);
        var scale = function(v){ if (max === min) return h/2; return h - pad - ((v - min) / (max - min)) * (h - pad*2); };
        var d = '';
        for (var i=0;i<arr.length;i++){ var x = pad + i*dx; var y = scale(arr[i]); d += (i===0 ? 'M ' : ' L ') + Math.round(x) + ' ' + Math.round(y); }
        el.innerHTML = '<svg width="100%" height="'+h+'"><path d="'+d+'" stroke="#4f46e5" stroke-width="2" fill="none"/></svg>';
      };
      var donut = function(el, val){
        if (!el) return;
        var w = el.clientWidth || 140, h = el.clientHeight || 96;
        var r = Math.min(w,h)/2 - 6;
        var cx = w/2, cy = h/2;
        var pct = Math.max(0, Math.min(100, Math.round(val)));
        var end = pct/100*2*Math.PI;
        var x = cx + r * Math.sin(end);
        var y = cy - r * Math.cos(end);
        var large = pct > 50 ? 1 : 0;
        var level = pct >= 85 ? 'red' : pct >= 65 ? 'orange' : 'green';
        var color = level==='red' ? '#ef4444' : level==='orange' ? '#f59e0b' : '#10b981';
        var bg = '#e5e7eb';
        var path = 'M '+cx+' '+(cy-r)+' A '+r+' '+r+' 0 1 1 '+(cx)+' '+(cy+r)+' A '+r+' '+r+' 0 1 1 '+cx+' '+(cy-r);
        var arc = 'M '+cx+' '+(cy-r)+' A '+r+' '+r+' 0 '+large+' 1 '+x+' '+y;
        el.innerHTML = '<svg width="100%" height="'+h+'"><path d="'+path+'" stroke="'+bg+'" stroke-width="10" fill="none"/><path d="'+arc+'" stroke="'+color+'" stroke-width="10" fill="none"/><text x="'+cx+'" y="'+cy+'" dominant-baseline="middle" text-anchor="middle" font-size="14" fill="#111827">'+pct+'%</text></svg>';
      };
      var badge = function(text, level){
        var base = 'px-2 py-1 rounded text-xs font-semibold ';
        var cls = level==='red' ? 'bg-red-100 text-red-800' : level==='orange' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800';
        return '<span class="'+base+cls+'">'+text+'</span>';
      };
      var setText = function(id, text){ var el = $(id); if (el) el.textContent = text; };
      var loadTrends = function(){
        fetch('/master/system/monitor/trends', { headers:{ 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){
            spark($('chart-rpm-1h'), d.rpm_1h || []);
            spark($('chart-resp-1h'), d.resp_ms_1h || []);
            spark($('chart-err-1h'), d.error_1h || []);
            spark($('chart-cpu-1h'), d.cpu_1h || []);
            spark($('chart-rpm-24h'), d.rpm_24h || []);
            spark($('chart-resp-24h'), d.resp_ms_24h || []);
            var cpuNow = (d.cpu_1h || []).slice(-1)[0]; if (cpuNow==null) cpuNow = 0;
            var ramNow = (d.ram_1h || []).slice(-1)[0]; if (ramNow==null) ramNow = 0;
            donut($('donut-cpu'), cpuNow || 0);
            donut($('donut-ram'), ramNow || 0);
            var errCnt = (d.error_1h || []).slice(-1)[0] || 0;
            var rpmNow = (d.rpm_1h || []).slice(-1)[0] || 0;
            var errRate = rpmNow>0 ? Math.round((errCnt / rpmNow) * 100) : 0;
            donut($('donut-err'), errRate);
            setText('u-rpm-1h', (rpmNow||0)+' req/min');
            setText('u-resp-1h', ((d.resp_ms_1h||[]).slice(-1)[0]||0)+' ms');
            setText('u-err-1h', (errCnt||0)+' errors/min');
            setText('u-cpu-1h', (cpuNow||0)+' %');
            setText('u-rpm-24h', ((d.rpm_24h||[]).slice(-1)[0]||0)+' req/min');
            setText('u-resp-24h', ((d.resp_ms_24h||[]).slice(-1)[0]||0)+' ms');
          });
      };
      var loadDb = function(){
        fetch('/master/system/monitor/db', { headers:{ 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){
            spark($('chart-slow-1h'), d.slow_trend_1h || []);
            var top = d.top_slowest || [];
            var avg = d.avg_per_table || {};
            var ul = $('sm-top-slowest'); if (ul) { ul.innerHTML = top.map(function(t){ return '<li class="flex items-center justify-between"><span>'+t+'</span>'+badge((avg[t]||0)+'ms', (avg[t]||0) >= 1000 ? 'orange' : 'green')+'</li>'; }).join(''); }
            var grid = $('sm-avg-per-table'); if (grid) {
              var keys = Object.keys(avg);
              grid.innerHTML = keys.map(function(k){ return '<div class="rounded border border-gray-200 px-2 py-1 flex items-center justify-between"><span>'+k+'</span>'+badge((avg[k]||0)+'ms', (avg[k]||0) >= 1000 ? 'orange' : 'green')+'</div>'; }).join('');
            }
          });
      };
      var loadQueue = function(){
        fetch('/master/system/monitor/queue', { headers:{ 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){
            spark($('chart-processed-1h'), d.processed_per_min || []);
            spark($('chart-failed-1h'), d.failed_trend_1h || []);
            var list = $('sm-long-running'); if (list) {
              var items = d.long_running || [];
              list.innerHTML = items.map(function(it){ return '<li class="flex items-center justify-between"><span>'+it.job_type+' #'+it.id+'</span>'+badge((it.processing_time_ms||0)+'ms', (it.processing_time_ms||0) >= 10000 ? 'red' : 'orange')+'</li>'; }).join('');
            }
            var cron = d.last_cron || {};
            var lc = $('sm-last-cron'); if (lc) {
              var level = (cron.status||'').toLowerCase()==='ok' ? 'green' : 'orange';
              lc.innerHTML = badge((cron.status||'N/A')+' • '+(cron.duration_ms||0)+'ms', level) + ' <span class="text-xs text-gray-500">'+(cron.time||'')+'</span>';
            }
          });
      };
      var fmtUnit = function(type, value){
        if (type==='error_rate') return value+' errors';
        if (type==='rpm_spike') return value+' req/min';
        if (type==='queue_backlog') return value+' failed';
        if (type==='db_slow') return value+' slow';
        if (type==='cpu_high' || type==='ram_high') return value+'%';
        return String(value);
      };
      var loadAlerts = function(){
        fetch('/master/system/monitor/alerts', { headers:{ 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){
            var ul = $('sm-alerts'); if (!ul) return;
            var items = d.items || [];
            if (items.length === 0) { ul.innerHTML = '<li class="text-sm text-gray-700">'+badge('All good','green')+'</li>'; return; }
            ul.innerHTML = items.map(function(a){
              var name = a.type.replace(/_/g,' ');
              return '<li class="flex items-center justify-between"><span class="text-sm text-gray-800">'+name+'</span>'+badge(fmtUnit(a.type, a.value), a.level)+'</li>';
            }).join('');
          });
      };
      var trace = function(){
        var params = [];
        var rid = $('trace-rid').value.trim();
        var path = $('trace-path').value.trim();
        if (rid) params.push('rid='+encodeURIComponent(rid));
        if (path) params.push('path='+encodeURIComponent(path));
        var url = '/master/system/monitor/trace' + (params.length ? ('?'+params.join('&')) : '');
        fetch(url, { headers:{ 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){
            var rows = $('trace-rows'); if (!rows) return;
            var items = d.items || [];
            rows.innerHTML = items.map(function(it){
              var msg = {};
              try { msg = JSON.parse(it.message || '{}'); } catch(e){}
              var status = msg.status || '-';
              var role = msg.role || '-';
              var rid = msg.request_id || '-';
              return '<tr><td class="px-2 py-1">'+(it.created_at||'')+'</td><td class="px-2 py-1">'+(it.module||'')+'</td><td class="px-2 py-1">'+status+'</td><td class="px-2 py-1">'+role+'</td><td class="px-2 py-1">'+(it.duration_ms||0)+'ms</td><td class="px-2 py-1">'+rid+'</td></tr>';
            }).join('');
          });
      };
      var ridHdr = (function(){
        var rid = '-';
        try {
          rid = (document.cookie || '').split('; ').find(function(x){ return x.indexOf('XSRF-TOKEN=')===0; }) ? '-' : '-';
        } catch(e){}
        $('sm-rid').textContent = rid;
      })();
      $('trace-search').addEventListener('click', trace);
      loadTrends(); loadDb(); loadQueue(); loadAlerts();
      setInterval(loadTrends, 15000);
      setInterval(loadDb, 60000);
      setInterval(loadQueue, 20000);
      setInterval(loadAlerts, 15000);
    })();
  </script>
</div>
