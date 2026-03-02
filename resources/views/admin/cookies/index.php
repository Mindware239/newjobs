<?php $activePolicy = null; foreach ($versions as $vv) { if ((int)($vv['is_active'] ?? 0) === 1) { $activePolicy = $vv; break; } } ?>
<script>window.__ACTIVE_POLICY = <?= json_encode($activePolicy ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<div class="space-y-8" x-data="cookieAdmin()" x-cloak>
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Cookie & Consent Management</h1>
    <div class="flex gap-2">
      <a href="/admin/cookies/export" class="px-3 py-2 bg-indigo-600 text-white rounded-md">Export Consents CSV</a>
      <button @click="forceReconsent" class="px-3 py-2 bg-orange-600 text-white rounded-md">Force Re‑consent</button>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <button @click="tab='overview'" :class="tab==='overview'?'bg-indigo-600 text-white':'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Overview</button>
    <button @click="tab='categories'" :class="tab==='categories'?'bg-indigo-600 text-white':'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Categories</button>
    <button @click="tab='policy'" :class="tab==='policy'?'bg-indigo-600 text-white':'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Policy</button>
    <button @click="tab='definitions'" :class="tab==='definitions'?'bg-indigo-600 text-white':'bg-white text-gray-700'" class="px-4 py-2 rounded-lg border">Definitions</button>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-show="tab==='overview'">
    <div class="bg-white p-6 rounded-lg shadow lg:col-span-3">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="text-xs text-gray-500">Total Consents</div>
          <div class="text-2xl font-semibold text-gray-900"><?= (int)($stats['total'] ?? 0) ?></div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="text-xs text-gray-500">Acceptance (Any Optional)</div>
          <div class="text-2xl font-semibold text-gray-900"><?= ($stats['total'] ?? 0) > 0 ? round((($stats['optional_any'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="text-xs text-gray-500">Marketing Acceptance</div>
          <div class="text-2xl font-semibold text-gray-900"><?= ($stats['total'] ?? 0) > 0 ? round((($stats['marketing'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="text-xs text-gray-500">Analytics Acceptance</div>
          <div class="text-2xl font-semibold text-gray-900"><?= ($stats['total'] ?? 0) > 0 ? round((($stats['analytics'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%</div>
        </div>
      </div>
      <div class="mt-6">
        <canvas id="consentCategoryChart" height="120"></canvas>
      </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
      <div class="space-y-3">
        <button @click="tab='policy'" class="w-full px-3 py-2 border rounded">New Policy</button>
        <a href="/admin/cookies/export" class="block w-full px-3 py-2 border rounded text-center">Export Data</a>
        <button @click="editDefinition(null); tab='definitions'" class="w-full px-3 py-2 border rounded">Add Cookie</button>
        <button @click="forceReconsent" class="w-full px-3 py-2 border rounded">Re‑consent</button>
      </div>
      <div class="mt-6">
        <h3 class="font-semibold text-gray-800 mb-2">Active Policy</h3>
        <div class="text-sm">
          <div class="flex items-center justify-between">
            <span>Version</span>
            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border"><?= htmlspecialchars($activePolicy['version_number'] ?? '') ?></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span>Published</span>
            <span><?= htmlspecialchars($activePolicy['effective_from'] ?? '') ?></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span>Re‑consent</span>
            <span><?= ((int)($activePolicy['requires_reconsent'] ?? 0)===1)?'Yes':'No' ?></span>
          </div>
          <a href="/cookie/policy" class="block mt-3 text-indigo-600">View Full Policy →</a>
        </div>
      </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="font-semibold text-gray-800 mb-4">Categories</h2>
      <div class="space-y-4">
        <?php foreach ($categories as $cat): ?>
          <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-start justify-between">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></span>
                  <?php if ((int)($cat['is_mandatory'] ?? 0) === 1): ?>
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">Mandatory</span>
                  <?php endif; ?>
                  <?php if ((int)($cat['is_active'] ?? 1) === 1): ?>
                    <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-100">Active</span>
                  <?php else: ?>
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700 border border-gray-200">Disabled</span>
                  <?php endif; ?>
                </div>
                <p class="text-xs text-gray-600 mt-1"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
              </div>
              <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm">
                  <span>Mandatory</span>
                  <input type="checkbox" @change="toggleCategory(<?= (int)$cat['id'] ?>,'is_mandatory',$event.target.checked)" <?= ((int)$cat['is_mandatory']===1)?'checked':'' ?>>
                </label>
                <label class="flex items-center gap-2 text-sm">
                  <span>Active</span>
                  <input type="checkbox" @change="toggleCategory(<?= (int)$cat['id'] ?>,'is_active',$event.target.checked)" <?= ((int)$cat['is_active']===1)?'checked':'' ?>>
                </label>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  <div class="bg-white p-6 rounded-lg shadow lg:col-span-2" x-show="tab==='definitions'">
      <h2 class="font-semibold text-gray-800 mb-4">Cookie Definitions</h2>
      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600">
              <th class="p-2">Name</th><th class="p-2">Category</th><th class="p-2">Provider</th><th class="p-2">Duration</th><th class="p-2">Secure</th><th class="p-2">Active</th><th class="p-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($definitions as $d): ?>
            <tr class="border-t">
              <td class="p-2"><?= htmlspecialchars($d['cookie_name']) ?></td>
              <td class="p-2"><?= htmlspecialchars($d['category_name']) ?></td>
              <td class="p-2"><?= htmlspecialchars($d['provider']) ?></td>
              <td class="p-2"><?= htmlspecialchars($d['duration_type']) ?><?= (int)$d['duration_days']>0?(' / '.(int)$d['duration_days'].' days'):'' ?></td>
              <td class="p-2"><?= ((int)$d['is_secure']===1)?'Yes':'No' ?></td>
              <td class="p-2"><?= ((int)$d['is_active']===1)?'Yes':'No' ?></td>
              <td class="p-2">
                <button @click="editDefinition(<?= json_encode($d) ?>)" class="px-2 py-1 bg-gray-100 rounded">Edit</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        <button @click="editDefinition(null)" class="px-3 py-2 bg-indigo-600 text-white rounded-md">Add Definition</button>
      </div>
    </div>
  </div>

  <div class="bg-white p-6 rounded-lg shadow" x-show="tab==='categories'">
    <h2 class="font-semibold text-gray-800 mb-4">Policy Versions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <div class="space-y-2">
          <?php foreach ($versions as $v): ?>
          <div class="border rounded p-3 flex items-center justify-between">
            <div>
              <div class="font-medium flex items-center gap-2">
                <?= htmlspecialchars($v['version_number']) ?>
                <?php if ((int)$v['is_active']===1): ?>
                  <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-100">Active</span>
                <?php endif; ?>
              </div>
              <div class="text-xs text-gray-500 mt-1">Effective: <?= htmlspecialchars($v['effective_from']) ?> · Re‑consent: <?= ((int)$v['requires_reconsent']===1)?'Yes':'No' ?></div>
            </div>
            <a class="text-xs text-indigo-600 hover:underline" href="/cookie/policy?version=<?= urlencode($v['version_number']) ?>">View</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
        <input x-model="policy.version" class="w-full border rounded px-3 py-2 mb-3" placeholder="v-YYYYMMDD-HHMMSS">
        <label class="block text-sm font-medium text-gray-700 mb-1">Policy Text</label>
        <textarea id="policy-editor" x-model="policy.text" class="w-full border rounded px-3 py-2 h-40"></textarea>
        <label class="flex items-center gap-2 mt-3 text-sm">
          <input type="checkbox" x-model="policy.reconsent"> Requires Re‑consent
        </label>
        <div class="mt-3">
          <button @click="updatePolicy" class="px-3 py-2 bg-green-600 text-white rounded-md">Publish New Policy</button>
        </div>
      </div>
    </div>
  </div>

  <template x-if="showDefModal">
    <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Cookie Definition</h3>
        <div class="space-y-3">
          <input x-model="def.id" type="hidden">
          <label class="block text-sm">Name <input x-model="def.cookie_name" class="w-full border rounded px-3 py-2"></label>
          <label class="block text-sm">Category ID <input x-model="def.category_id" class="w-full border rounded px-3 py-2" type="number"></label>
          <label class="block text-sm">Provider <input x-model="def.provider" class="w-full border rounded px-3 py-2"></label>
          <label class="block text-sm">Purpose <textarea x-model="def.purpose" class="w-full border rounded px-3 py-2"></textarea></label>
          <div class="grid grid-cols-2 gap-2">
            <label class="block text-sm">Duration Type
              <select x-model="def.duration_type" class="w-full border rounded px-2 py-2">
                <option value="session">Session</option>
                <option value="persistent">Persistent</option>
              </select>
            </label>
            <label class="block text-sm">Duration Days <input x-model="def.duration_days" type="number" class="w-full border rounded px-3 py-2"></label>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="def.is_third_party"> Third‑party</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="def.is_secure"> Secure</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="def.is_http_only"> HttpOnly</label>
            <label class="block text-sm">SameSite
              <select x-model="def.same_site" class="w-full border rounded px-2 py-2">
                <option>Lax</option><option>Strict</option><option>None</option>
              </select>
            </label>
          </div>
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="def.is_active" checked> Active</label>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="showDefModal=false" class="px-3 py-2 bg-gray-100 rounded">Cancel</button>
          <button @click="saveDefinition" class="px-3 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
      </div>
    </div>
  </template>
</div>
<script>
function cookieAdmin(){
  return {
    policy: (function(){ var ap = window.__ACTIVE_POLICY || {}; return { version: ap.version_number || '', text: ap.policy_text || '', reconsent: (parseInt(ap.requires_reconsent||0,10)===1) }; })(),
    showDefModal: false,
    def: { id: null, category_id: '', cookie_name: '', provider: 'internal', purpose: '', duration_type: 'session', duration_days: 0, is_third_party: false, is_http_only: false, is_secure: false, same_site: 'Lax', is_active: true },
    async forceReconsent(){
      const res = await fetch('/admin/cookies/policy/force-reconsent', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' }});
      const data = await res.json(); if (data.success) { alert('Re‑consent flagged'); location.reload(); }
    },
    async updatePolicy(){
      const res = await fetch('/admin/cookies/policy/update', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify({ version_number: this.policy.version, policy_text: this.policy.text, requires_reconsent: this.policy.reconsent ? 1 : 0 })});
      const data = await res.json(); if (data.success) { alert('Policy updated'); location.reload(); }
    },
    async toggleCategory(id, field, val){
      const form = new FormData(); form.append('id', id); form.append('field', field); form.append('value', val ? '1' : '0');
      const res = await fetch('/admin/cookies/category/toggle', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: form });
      const data = await res.json(); if (!data.success) alert(data.error || 'Failed');
    },
    editDefinition(row){ this.showDefModal = true; if (row) { this.def = { ...row, is_third_party: !!(+row.is_third_party), is_http_only: !!(+row.is_http_only), is_secure: !!(+row.is_secure), is_active: !!(+row.is_active) }; } else { this.def = { id:null, category_id:'', cookie_name:'', provider:'internal', purpose:'', duration_type:'session', duration_days:0, is_third_party:false, is_http_only:false, is_secure:false, same_site:'Lax', is_active:true }; } },
    async saveDefinition(){
      const res = await fetch('/admin/cookies/definition/upsert', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-Token':document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify(this.def) });
      const data = await res.json(); if (data.success) { alert('Saved'); location.reload(); } else { alert(data.error || 'Failed'); }
    }
  }
}
</script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var el = document.getElementById('policy-editor');
    if (window.jQuery && el) {
      jQuery(el).summernote({
        placeholder: 'Enter cookie policy...',
        height: 240,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link']],
          ['view', ['codeview']]
        ]
      });
      jQuery(el).on('summernote.change', function(){
        var code = jQuery(el).summernote('code');
        el.value = code;
        el.dispatchEvent(new Event('input'));
      });
    }
  });
</script>
<script>
  (function(){
    var ctx=document.getElementById('consentCategoryChart'); if(!ctx||!window.Chart) return;
    var data={
      labels:['Functional','Analytics','Marketing','Performance'],
      datasets:[{
        label:'Accepted',
        data:[<?= (int)($stats['functional'] ?? 0) ?>,<?= (int)($stats['analytics'] ?? 0) ?>,<?= (int)($stats['marketing'] ?? 0) ?>,<?= (int)($stats['performance'] ?? 0) ?>],
        backgroundColor:['#6366F1','#10B981','#F59E0B','#8B5CF6']
      }]
    };
    new Chart(ctx,{type:'bar',data:data,options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
  })();
</script>
