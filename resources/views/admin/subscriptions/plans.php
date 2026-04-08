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
        <a href="/admin/subscriptions/plans" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-900 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8"></path></svg>
          View all
        </a>
      </div>
    </div>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3 animate-fade-in">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
      <?= htmlspecialchars($_GET['success']) ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 animate-fade-in">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
      <?= htmlspecialchars($_GET['error']) ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 gap-8">
    <!-- Plan Editor Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg <?= $editing ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600' ?> flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900"><?= $editing ? 'Edit Plan' : 'Create New Plan' ?></h3>
            <p class="text-xs text-gray-500">Configure your subscription plan details and limits</p>
          </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($editing ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700') ?>">
          <?= $editing ? 'Editing: ' . htmlspecialchars($editing['name']) : 'New Plan Mode' ?>
        </span>
      </div>

      <?php
        $features = [];
        if ($editing && !empty($editing['features'])) {
          $decoded = json_decode($editing['features'], true);
          if (is_array($decoded)) { $features = $decoded; }
        }
      ?>

      <form id="planForm" method="post" action="<?= $editing ? '/admin/subscriptions/plans/'.$editing['id'] : '/admin/subscriptions/plans' ?>" class="p-6">
        <?php if ($editing): ?>
          <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <!-- Form Tabs -->
        <div class="flex border-b border-gray-200 mb-6 space-x-8">
          <button type="button" onclick="switchTab('general')" class="tab-btn active pb-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600 transition-all" id="tab-general">General Information</button>
          <button type="button" onclick="switchTab('pricing')" class="tab-btn pb-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all" id="tab-pricing">Pricing & Limits</button>
          <button type="button" onclick="switchTab('features')" class="tab-btn pb-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all" id="tab-features">Features List</button>
        </div>

        <!-- General Tab -->
        <div id="section-general" class="tab-section space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Plan Category</label>
              <div class="relative">
                <select name="plan_for" class="w-full border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all appearance-none" id="planForSelect">
                  <?php $pf = strtolower($editing['plan_for'] ?? 'employer'); ?>
                  <option value="employer" <?= $pf==='employer'?'selected':'' ?>>Employer Plan</option>
                  <option value="candidate" <?= $pf==='candidate'?'selected':'' ?>>Candidate Plan</option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Plan Name</label>
              <input name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" placeholder="e.g., Premium Pro" class="w-full border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">Description</label>
            <textarea name="description" class="w-full border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all" rows="2" placeholder="Brief summary of the plan's value proposition"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
          </div>
          <div class="flex flex-wrap gap-6">
            <div class="flex items-center gap-3 bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 flex-1 min-w-[200px]">
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900">Active Status</p>
                <p class="text-xs text-gray-500">Enable/disable this plan</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" <?= (int)($editing['is_active'] ?? 0) === 1 ? 'checked' : '' ?> class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 flex-1 min-w-[200px]">
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900">Featured Badge</p>
                <p class="text-xs text-gray-500">Highlight as "Most Popular"</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_featured" <?= (int)($editing['is_featured'] ?? 0) === 1 ? 'checked' : '' ?> class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
              </label>
            </div>
            <div class="space-y-2 flex-1 min-w-[200px]">
              <label class="block text-sm font-semibold text-gray-700">Display Order</label>
              <input type="number" name="sort_order" value="<?= htmlspecialchars((string)($editing['sort_order'] ?? '0')) ?>" class="w-full border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
          </div>
        </div>

        <!-- Pricing Tab -->
        <div id="section-pricing" class="tab-section hidden space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100">
              <label class="block text-sm font-bold text-gray-700 mb-2">Monthly Price</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₹</span>
                <input type="number" step="0.01" name="price_monthly" value="<?= htmlspecialchars((string)($editing['price_monthly'] ?? '0')) ?>" class="w-full border-gray-300 rounded-xl pl-8 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 transition-all">
              </div>
            </div>
            <div class="space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100">
              <label class="block text-sm font-bold text-gray-700 mb-2">Quarterly Price</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₹</span>
                <input type="number" step="0.01" name="price_quarterly" value="<?= htmlspecialchars((string)($editing['price_quarterly'] ?? '0')) ?>" class="w-full border-gray-300 rounded-xl pl-8 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 transition-all">
              </div>
            </div>
            <div class="space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100">
              <label class="block text-sm font-bold text-gray-700 mb-2">Annual Price</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₹</span>
                <input type="number" step="0.01" name="price_annual" value="<?= htmlspecialchars((string)($editing['price_annual'] ?? '0')) ?>" class="w-full border-gray-300 rounded-xl pl-8 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 transition-all">
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-100">
            <div class="space-y-4">
              <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider">Quota Limits</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-500">Max Job Posts</label>
                  <input type="number" name="max_job_posts" value="<?= htmlspecialchars((string)($editing['max_job_posts'] ?? '0')) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-500">Contacts / mo</label>
                  <input type="number" name="max_contacts_per_month" value="<?= htmlspecialchars((string)($editing['max_contacts_per_month'] ?? '0')) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-500">Resume Downloads</label>
                  <input type="number" name="max_resume_downloads" value="<?= htmlspecialchars((string)($editing['max_resume_downloads'] ?? '0')) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-500">Chat Messages</label>
                  <input type="number" name="max_chat_messages" value="<?= htmlspecialchars((string)($editing['max_chat_messages'] ?? '0')) ?>" class="w-full border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider">Feature Toggles</h4>
              <div class="grid grid-cols-1 gap-2">
                <?php
                  $toggles = [
                    'resume_download_enabled' => 'Resume Download Access',
                    'chat_enabled' => 'Candidate Messaging',
                    'candidate_mobile_visible' => 'Mobile Number Visibility',
                    'job_post_boost' => 'Job Posting Boost',
                    'ai_matching' => 'AI Smart Matching',
                    'analytics_dashboard' => 'Advanced Analytics'
                  ];
                ?>
                <?php foreach ($toggles as $key => $label): ?>
                  <label class="flex items-center justify-between p-2.5 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer group">
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors"><?= $label ?></span>
                    <input type="checkbox" name="<?= $key ?>" <?= (int)($editing[$key] ?? 0) === 1 ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Features Tab -->
        <div id="section-features" class="tab-section hidden space-y-6">
          <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              </div>
              <div>
                <h4 class="font-bold text-indigo-900">Custom Plan Features</h4>
                <p class="text-sm text-indigo-700">Add granular features that appear in comparison tables</p>
              </div>
            </div>
            <div id="candidatePreset" class="hidden">
              <button type="button" onclick="loadCandidatePreset()" class="px-4 py-2 bg-white text-indigo-600 border border-indigo-200 rounded-xl font-semibold hover:bg-indigo-50 transition-colors shadow-sm">
                Load Candidate Presets
              </button>
            </div>
          </div>

          <div id="featuresList" class="space-y-3">
            <?php if (!empty($features)): ?>
              <?php foreach ($features as $idx => $f): ?>
                <div class="feature-row flex flex-wrap items-center gap-4 bg-white border border-gray-200 rounded-2xl p-4 shadow-sm group hover:border-indigo-300 transition-all">
                  <div class="flex-1 min-w-[300px] space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Feature Description</label>
                    <input name="features[]" value="<?= htmlspecialchars((string)($f['feature_text'] ?? '')) ?>" placeholder="e.g., Access to Premium Database" class="w-full border-none p-0 text-sm font-medium focus:ring-0">
                  </div>
                  <div class="w-24 space-y-1 border-l border-gray-100 pl-4">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Status</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" name="features_enabled[<?= $idx ?>]" <?= ((int)($f['is_enabled'] ?? 1) === 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                      <span class="text-xs font-medium text-gray-600">Active</span>
                    </label>
                  </div>
                  <div class="w-32 space-y-1 border-l border-gray-100 pl-4">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Icon Class</label>
                    <input name="features_icon[<?= $idx ?>]" value="<?= htmlspecialchars((string)($f['icon'] ?? '')) ?>" placeholder="fas fa-check" class="w-full border-none p-0 text-xs text-gray-500 focus:ring-0">
                  </div>
                  <div class="w-32 space-y-1 border-l border-gray-100 pl-4">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Category</label>
                    <input name="features_category[<?= $idx ?>]" value="<?= htmlspecialchars((string)($f['category'] ?? '')) ?>" placeholder="General" class="w-full border-none p-0 text-xs text-gray-500 focus:ring-0">
                  </div>
                  <button type="button" class="p-2 text-gray-300 hover:text-red-600 transition-colors" onclick="this.closest('.feature-row').remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <button type="button" onclick="addFeature()" class="w-full py-4 border-2 border-dashed border-gray-200 rounded-2xl text-gray-400 font-medium hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/30 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Feature Row
          </button>
        </div>

        <!-- Footer Actions -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-end gap-4">
          <?php if ($editing): ?>
            <a href="/admin/subscriptions/plans" class="px-6 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">Discard Changes</a>
          <?php endif; ?>
          <button type="submit" class="px-10 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-[0.98]">
            <?= $editing ? 'Save Plan Updates' : 'Publish New Plan' ?>
          </button>
        </div>
      </form>
    </div>

    <!-- Plans Comparison Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900">Live Plans Matrix</h3>
            <p class="text-xs text-gray-500">Quick overview of all currently active subscription plans</p>
          </div>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-50/50">
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Plan Details</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Category</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Pricing (₹)</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Quotas</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Status</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Management</th>
            </tr>
          </thead>
              <tbody class="divide-y divide-gray-100">
                <?php foreach ($plans as $p): ?>
                  <?php if ((int)($p['is_active'] ?? 1) === 0 && strpos((string)($p['slug'] ?? ''), '-deleted-') !== false) continue; ?>
                  <?php $pf = strtolower((string)($p['plan_for'] ?? 'employer')); ?>
              <tr class="hover:bg-indigo-50/20 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <?php if ((int)($p['is_featured'] ?? 0) === 1): ?>
                      <div class="w-2 h-10 bg-yellow-400 rounded-full"></div>
                    <?php endif; ?>
                    <div>
                      <div class="font-bold text-gray-900"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></div>
                      <div class="text-[11px] text-gray-400 max-w-[200px] truncate"><?= htmlspecialchars((string)($p['description'] ?? '')) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight <?= $pf === 'candidate' ? 'bg-pink-50 text-pink-600' : 'bg-indigo-50 text-indigo-600' ?>">
                    <?= $pf ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900">₹<?= number_format((float)($p['price_monthly'] ?? 0), 0) ?><span class="text-[10px] text-gray-400 font-normal">/mo</span></div>
                  <div class="text-[11px] text-gray-500 font-medium">₹<?= number_format((float)($p['price_annual'] ?? 0), 0) ?><span class="text-[10px] text-gray-400 font-normal">/yr</span></div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-center gap-4">
                    <div class="text-center" title="Job Posts">
                      <div class="text-xs font-bold text-gray-700"><?= (int)($p['max_job_posts'] ?? 0) ?></div>
                      <div class="text-[9px] text-gray-400 uppercase">Jobs</div>
                    </div>
                    <div class="text-center" title="Contacts">
                      <div class="text-xs font-bold text-gray-700"><?= (int)($p['max_contacts_per_month'] ?? 0) ?></div>
                      <div class="text-[9px] text-gray-400 uppercase">Contacts</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= ((int)($p['is_active'] ?? 0) === 1) ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' ?>">
                    <div class="w-1.5 h-1.5 rounded-full <?= ((int)($p['is_active'] ?? 0) === 1) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' ?>"></div>
                    <?= ((int)($p['is_active'] ?? 0) === 1) ? 'Active' : 'Offline' ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="/admin/subscriptions/plans/<?= (int)$p['id'] ?>/edit" class="p-2 rounded-lg bg-gray-50 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all" title="Edit Plan">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                    <form method="post" action="/admin/subscriptions/plans/<?= (int)$p['id'] ?>/duplicate" class="inline">
                      <button type="submit" class="p-2 rounded-lg bg-gray-50 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all" title="Duplicate Plan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                      </button>
                    </form>
                    <button type="button" onclick="deletePlan(<?= (int)$p['id'] ?>)" class="p-2 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-all" title="Delete Plan">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
  .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
  .tab-btn.active { border-bottom-color: #4f46e5; color: #4f46e5; }
</style>

<script>
  function switchTab(tabId) {
    document.querySelectorAll('.tab-section').forEach(el => el.classList.add('hidden'));
    document.getElementById('section-' + tabId).classList.remove('hidden');
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
      btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeBtn = document.getElementById('tab-' + tabId);
    activeBtn.classList.add('active', 'border-indigo-600', 'text-indigo-600');
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
  }

  function addFeature() {
    const c = document.getElementById('featuresList');
    const idx = c.children.length;
    const row = document.createElement('div');
    row.className = 'feature-row flex flex-wrap items-center gap-4 bg-white border border-gray-200 rounded-2xl p-4 shadow-sm group hover:border-indigo-300 transition-all';
    row.innerHTML = `
      <div class="flex-1 min-w-[300px] space-y-1">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Feature Description</label>
        <input name="features[]" placeholder="e.g., Access to Premium Database" class="w-full border-none p-0 text-sm font-medium focus:ring-0">
      </div>
      <div class="w-24 space-y-1 border-l border-gray-100 pl-4">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Status</label>
        <label class="inline-flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="features_enabled[${idx}]" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
          <span class="text-xs font-medium text-gray-600">Active</span>
        </label>
      </div>
      <div class="w-32 space-y-1 border-l border-gray-100 pl-4">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Icon Class</label>
        <input name="features_icon[${idx}]" placeholder="fas fa-check" class="w-full border-none p-0 text-xs text-gray-500 focus:ring-0">
      </div>
      <div class="w-32 space-y-1 border-l border-gray-100 pl-4">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Category</label>
        <input name="features_category[${idx}]" placeholder="General" class="w-full border-none p-0 text-xs text-gray-500 focus:ring-0">
      </div>
      <button type="button" class="p-2 text-gray-300 hover:text-red-600 transition-colors" onclick="this.closest('.feature-row').remove()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
      </button>
    `;
    c.appendChild(row);
  }

  async function deletePlan(id) {
    if (!confirm('Are you sure you want to delete this subscription plan? This action cannot be undone if no active subscriptions exist.')) return;
    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";
      const res = await fetch('/admin/subscriptions/plans/' + id, { 
        method: 'DELETE',
        headers: {
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      
      if (res.redirected) {
        window.location.href = res.url;
        return;
      }

      const data = await res.json();
      if (res.ok) {
        window.location.href = '/admin/subscriptions/plans?success=' + encodeURIComponent(data.message || 'Plan deleted successfully');
      } else {
        alert(data.error || data.message || 'Failed to delete plan. It might be in use by active subscriptions.');
      }
    } catch (e) {
      console.error('Delete error:', e);
      alert('An error occurred while deleting the plan. Please try refreshing the page.');
    }
  }

  function handlePlanTypeChange() {
    const type = document.getElementById('planForSelect').value;
    const presetSection = document.getElementById('candidatePreset');
    if (type === 'candidate') {
      presetSection.classList.remove('hidden');
    } else {
      presetSection.classList.add('hidden');
    }
  }

  function loadCandidatePreset() {
    const list = document.getElementById('featuresList');
    list.innerHTML = '';
    const presets = [
      'Top profile visibility to recruiters',
      'Higher search ranking in results',
      'Priority in job recommendations',
      'Verified profile badge',
      'Unlimited job applications'
    ];
    presets.forEach(txt => {
      const row = document.createElement('div');
      // ... same as addFeature but with value
      addFeature();
      const lastRow = list.lastElementChild;
      lastRow.querySelector('input[name="features[]"]').value = txt;
    });
  }

  document.getElementById('planForSelect').addEventListener('change', handlePlanTypeChange);
  handlePlanTypeChange();
</script>
</div>
