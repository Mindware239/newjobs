<?php /** @var array $plans */ ?>
<?php
  $editId = isset($editId) ? (int)$editId : (isset($_GET['edit']) ? (int)$_GET['edit'] : 0);
  $editing = null;
  if ($editId > 0 && is_array($plans)) {
    foreach ($plans as $p) { if ((int)$p['id'] === $editId) { $editing = $p; break; } }
  }
?>
<div class="p-6">
  <div class="rounded-2xl p-6 mb-8 bg-white border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
        <p class="text-gray-600 text-sm mt-1">Create, edit and curate premium plans shown across the platform</p>
      </div>
      <div class="space-x-3">
        <a href="/admin/subscriptions/plans" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-900">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8"></path></svg>
          View all
        </a>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-xl font-bold">Plan Editor</h3>
        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($editing ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700') ?>"><?= $editing ? 'Editing' : 'Create new' ?></span>
      </div>
      <?php
        $features = [];
        if ($editing && !empty($editing['features'])) {
          $decoded = json_decode($editing['features'], true);
          if (is_array($decoded)) { $features = $decoded; }
        }
      ?>
      <form id="planForm" method="post" action="<?= $editing ? '/admin/subscriptions/plans/'.$editing['id'] : '/admin/subscriptions/plans' ?>">
        <?php if ($editing): ?>
          <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="planFields">
          <div>
            <label class="block text-sm font-medium mb-1">Plan For</label>
            <select name="plan_for" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500" id="planForSelect">
              <?php $pf = strtolower($editing['plan_for'] ?? 'employer'); ?>
              <option value="employer" <?= $pf==='employer'?'selected':'' ?>>Employer</option>
              <option value="candidate" <?= $pf==='candidate'?'selected':'' ?>>Candidate</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <!-- <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500" rows="2"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
          </div> -->
          <div data-section="shared">
            <label class="block text-sm font-medium mb-1">Price Monthly</label>
            <input type="number" step="0.01" name="price_monthly" value="<?= htmlspecialchars((string)($editing['price_monthly'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="shared">
            <label class="block text-sm font-medium mb-1">Price Quarterly</label>
            <input type="number" step="0.01" name="price_quarterly" value="<?= htmlspecialchars((string)($editing['price_quarterly'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="shared">
            <label class="block text-sm font-medium mb-1">Price Annual</label>
            <input type="number" step="0.01" name="price_annual" value="<?= htmlspecialchars((string)($editing['price_annual'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="shared">
            <label class="block text-sm font-medium mb-1">Default Billing Cycle</label>
            <?php $dbc = strtolower($editing['default_billing_cycle'] ?? 'monthly'); ?>
            <select name="default_billing_cycle" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
              <option value="monthly" <?= $dbc==='monthly'?'selected':'' ?>>Monthly</option>
              <option value="quarterly" <?= $dbc==='quarterly'?'selected':'' ?>>Quarterly</option>
              <option value="annual" <?= $dbc==='annual'?'selected':'' ?>>Annual</option>
            </select>
          </div>
          <div data-section="shared">
            <label class="block text-sm font-medium mb-1">Sort Order</label>
            <input type="number" name="sort_order" value="<?= htmlspecialchars((string)($editing['sort_order'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="employer">
            <label class="block text-sm font-medium mb-1">Max Job Posts</label>
            <input type="number" name="max_job_posts" value="<?= htmlspecialchars((string)($editing['max_job_posts'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="employer">
            <label class="block text-sm font-medium mb-1">Contacts / month</label>
            <input type="number" name="max_contacts_per_month" value="<?= htmlspecialchars((string)($editing['max_contacts_per_month'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="employer">
            <label class="block text-sm font-medium mb-1">Resume downloads</label>
            <input type="number" name="max_resume_downloads" value="<?= htmlspecialchars((string)($editing['max_resume_downloads'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div data-section="employer">
            <label class="block text-sm font-medium mb-1">Chat messages</label>
            <input type="number" name="max_chat_messages" value="<?= htmlspecialchars((string)($editing['max_chat_messages'] ?? '0')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
          </div>
          <div class="md:col-span-2 grid grid-cols-2 gap-3" data-section="employer">
            <?php
              $toggles = [
                'resume_download_enabled' => 'Resume download enabled',
                'chat_enabled' => 'Chat enabled',
                'candidate_mobile_visible' => 'Candidate mobile visible',
                'job_post_boost' => 'Job post boost',
                'ai_matching' => 'AI matching',
                'analytics_dashboard' => 'Analytics dashboard',
                'is_featured' => 'Featured badge',
                'is_active' => 'Active'
              ];
            ?>
            <?php foreach ($toggles as $key => $label): ?>
              <?php $checked = (int)($editing[$key] ?? 0) === 1 ? 'checked' : ''; ?>
              <label class="inline-flex items-center justify-between gap-3 text-sm bg-gray-50 px-3 py-2 rounded-lg border">
                <span class="font-medium"><?= $label ?></span>
                <input type="checkbox" name="<?= $key ?>" <?= $checked ?> class="rounded border accent-indigo-600">
              </label>
            <?php endforeach; ?>
          </div>
          <div class="md:col-span-2" data-section="candidate">
            <div class="p-3 border rounded-lg bg-indigo-50 text-indigo-700 text-sm">
              Candidate premium benefits are managed via the Features list below.
              <button type="button" class="ml-3 px-3 py-1.5 border rounded-lg bg-white hover:bg-indigo-100" id="loadCandidatePresetBtn">Load candidate preset</button>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm font-medium mb-2">Features</label>
          <div id="featuresList" class="space-y-2">
            <?php if (!empty($features)): ?>
              <?php foreach ($features as $idx => $f): ?>
                <div class="flex items-center gap-2">
                  <input name="features[]" value="<?= htmlspecialchars((string)($f['feature_text'] ?? '')) ?>" class="flex-1 border rounded px-3 py-2">
                  <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="features_enabled[<?= $idx ?>]" <?= ((int)($f['is_enabled'] ?? 1) === 1) ? 'checked' : '' ?> class="rounded border">
                    <span>Enabled</span>
                  </label>
                  <input name="features_icon[<?= $idx ?>]" value="<?= htmlspecialchars((string)($f['icon'] ?? '')) ?>" placeholder="Icon (optional)" class="border rounded px-2 py-1 w-32">
                  <input name="features_category[<?= $idx ?>]" value="<?= htmlspecialchars((string)($f['category'] ?? '')) ?>" placeholder="Category (optional)" class="border rounded px-2 py-1 w-32">
                  <button type="button" class="text-red-600" onclick="this.parentElement.remove()">Remove</button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <button type="button" class="mt-2 px-3 py-1.5 border rounded" onclick="addFeature()">Add feature</button>
        </div>
        <div class="mt-4">
          <button class="px-4 py-2 bg-blue-600 text-white rounded"><?= $editing ? 'Update Plan' : 'Create Plan' ?></button>
          <?php if ($editing): ?>
            <a href="/admin/subscriptions/plans" class="ml-2 text-sm text-gray-600">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-xl font-bold">Plans Comparison</h3>
        <div class="text-sm text-gray-600">Overview</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Plan</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Monthly</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Annual</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Contacts/mo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Resume DL</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Chat Msg</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Featured</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($plans as $p): ?>
            <?php $pf = strtolower((string)($p['plan_for'] ?? 'employer')); ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars((string)($p['description'] ?? '')) ?></div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full <?= $pf === 'candidate' ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700' ?>">
                  <?php if ($pf === 'candidate'): ?>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Candidate
                  <?php else: ?>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M4 6h16M4 18h16"/></svg>
                    Employer
                  <?php endif; ?>
                </span>
              </td>
              <td class="px-4 py-3">₹<?= number_format((float)($p['price_monthly'] ?? 0), 2) ?></td>
              <td class="px-4 py-3">₹<?= number_format((float)($p['price_annual'] ?? 0), 2) ?></td>
              <td class="px-4 py-3"><?= (int)($p['max_contacts_per_month'] ?? 0) ?></td>
              <td class="px-4 py-3"><?= (int)($p['max_resume_downloads'] ?? 0) ?></td>
              <td class="px-4 py-3"><?= (int)($p['max_chat_messages'] ?? 0) ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full <?= ((int)($p['is_active'] ?? 0) === 1) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' ?>">
                  <?php if (((int)($p['is_active'] ?? 0)) === 1): ?>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Active
                  <?php else: ?>
                    Inactive
                  <?php endif; ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <?php if (((int)($p['is_featured'] ?? 0)) === 1): ?>
                  <span class="inline-flex items-center text-yellow-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                  </span>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <a href="/admin/subscriptions/plans/<?= (int)$p['id'] ?>/edit" class="px-2.5 py-1 rounded bg-indigo-600 text-white text-xs">Edit</a>
                  <form method="post" action="/admin/subscriptions/plans/<?= (int)$p['id'] ?>/duplicate" class="inline">
                    <button class="px-2.5 py-1 rounded bg-gray-100 hover:bg-gray-200 text-xs">Duplicate</button>
                  </form>
                  <button class="px-2.5 py-1 rounded bg-red-600 text-white text-xs" onclick="deletePlan(<?= (int)$p['id'] ?>)">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
</div>
<script>
  function addFeature() {
    const c = document.getElementById('featuresList');
    const idx = c.children.length;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 bg-gray-50 border rounded-lg p-2';
    row.innerHTML = `
      <input name="features[]" class="flex-1 border rounded-lg px-3 py-2 bg-white" placeholder="Feature">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="features_enabled[${idx}]" checked class="rounded border accent-indigo-600">
        <span>Enabled</span>
      </label>
      <input name="features_icon[${idx}]" placeholder="Icon" class="border rounded-lg px-2 py-1 w-32 bg-white">
      <input name="features_category[${idx}]" placeholder="Category" class="border rounded-lg px-2 py-1 w-32 bg-white">
      <button type="button" class="text-red-600 hover:text-red-700" onclick="this.parentElement.remove()">Remove</button>
    `;
    c.appendChild(row);
  }
  async function deletePlan(id) {
    if (!confirm('Delete this plan?')) return;
    try {
      const res = await fetch('/admin/subscriptions/plans/' + id, { method: 'DELETE' });
      if (res.ok) location.reload();
    } catch (e) {
      alert('Failed to delete');
    }
  }
  function setPlanForUI() {
    const sel = document.getElementById('planForSelect');
    const val = (sel?.value || 'employer').toLowerCase();
    const container = document.getElementById('planFields');
    if (!container) return;
    container.querySelectorAll('[data-section]').forEach(el => {
      const sec = el.getAttribute('data-section');
      const show = sec === 'shared' || sec === val;
      el.style.display = show ? '' : 'none';
    });
    const presetBtn = document.getElementById('loadCandidatePresetBtn');
    if (presetBtn) presetBtn.style.display = val === 'candidate' ? '' : 'none';
  }
  function loadCandidatePreset() {
    const preset = [
      'Top profile visibility to recruiters',
      'Higher search ranking in results',
      'Priority in job recommendations',
      'Verified profile badge',
      'Unlimited job applications',
      'Advanced analytics dashboard',
      'Priority customer support',
      'Hidden job opportunities',
      'AI-powered profile enhancement',
      'Resume builder & templates'
    ];
    const list = document.getElementById('featuresList');
    preset.forEach((txt, i) => {
      const row = document.createElement('div');
      row.className = 'flex items-center gap-2 bg-gray-50 border rounded-lg p-2';
      row.innerHTML = `
        <input name="features[]" class="flex-1 border rounded-lg px-3 py-2 bg-white" value="${txt}">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="features_enabled[${i}]" checked class="rounded border accent-indigo-600">
          <span>Enabled</span>
        </label>
        <input name="features_icon[${i}]" placeholder="Icon" class="border rounded-lg px-2 py-1 w-32 bg-white">
        <input name="features_category[${i}]" placeholder="Category" class="border rounded-lg px-2 py-1 w-32 bg-white">
        <button type="button" class="text-red-600 hover:text-red-700" onclick="this.parentElement.remove()">Remove</button>
      `;
      list.appendChild(row);
    });
  }
  document.getElementById('planForSelect')?.addEventListener('change', setPlanForUI);
  document.getElementById('loadCandidatePresetBtn')?.addEventListener('click', loadCandidatePreset);
  setPlanForUI();
  // Method override for PUT via fetch when the router requires it
  document.getElementById('planForm')?.addEventListener('submit', async function(e) {
    const isEdit = this.querySelector('input[name=\"_method\"][value=\"PUT\"]');
    if (!isEdit) return; // normal POST for create
    e.preventDefault();
    const fd = new FormData(this);
    const url = this.getAttribute('action');
    const res = await fetch(url, { method: 'PUT', body: fd });
    if (res.ok) location.href = '/admin/subscriptions/plans';
    else alert('Update failed');
  });
</script>
