<?php $activePolicy = null; foreach ($versions as $vv) { if ((int)($vv['is_active'] ?? 0) === 1) { $activePolicy = $vv; break; } } ?>
<div class="space-y-8" x-data="cookieAdmin()" x-cloak>
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Cookie & Consent Management</h1>
    <div class="flex gap-2">
      <a href="/admin/cookies/export" class="px-3 py-2 bg-indigo-600 text-white rounded-md">Export CSV</a>
      <button @click="forceReconsent" class="px-3 py-2 bg-red-600 text-white rounded-md">Force Re‑consent</button>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <button @click="tab='overview'" :class="tab==='overview'?'bg-indigo-600 text-white shadow':'bg-white text-gray-700 hover:bg-indigo-50'" class="px-4 py-2 rounded-2xl border flex items-center gap-2"><span>🍪</span><span>Overview</span></button>
    <button @click="tab='categories'" :class="tab==='categories'?'bg-indigo-600 text-white shadow':'bg-white text-gray-700 hover:bg-indigo-50'" class="px-4 py-2 rounded-2xl border flex items-center gap-2"><span>📘</span><span>Categories</span></button>
    <button @click="tab='policy'" :class="tab==='policy'?'bg-indigo-600 text-white shadow':'bg-white text-gray-700 hover:bg-indigo-50'" class="px-4 py-2 rounded-2xl border flex items-center gap-2"><span>📄</span><span>Policy</span></button>
    <button @click="tab='definitions'" :class="tab==='definitions'?'bg-indigo-600 text-white shadow':'bg-white text-gray-700 hover:bg-indigo-50'" class="px-4 py-2 rounded-2xl border flex items-center gap-2"><span>🔧</span><span>Definitions</span></button>
  </div>

  <div x-show="tab==='overview'" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="bg-white border rounded-xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-indigo-300"></div>
        <div class="text-xs text-gray-500">Total Consents</div>
        <div class="text-3xl font-extrabold text-gray-900"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="mt-2">
          <?php $today = (int)(($trends['today_total'] ?? 0)); ?>
          <?php if ($today > 0): ?>
            <span class="soft-badge soft-badge-green">+<?= $today ?> today</span>
          <?php else: ?>
            <span class="soft-badge soft-badge-gray">0 today</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-300"></div>
        <div class="text-xs text-gray-500">Full Accept (all optional)</div>
        <div class="text-3xl font-extrabold text-gray-900">
          <?= (int)($full_accept_pct ?? 0) ?>%
        </div>
      </div>
      <div class="bg-white border rounded-xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-600 to-emerald-300"></div>
        <div class="text-xs text-gray-500">Optional Acceptance</div>
        <div class="text-3xl font-extrabold text-gray-900">
          <?= ($stats['total'] ?? 0) > 0 ? round((($stats['optional_any'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%
        </div>
        <?php $delta = (float)($trends['acceptance_delta'] ?? 0.0); $abs = abs($delta); ?>
        <div class="mt-2">
          <?php if ($abs < 0.5): ?>
            <span class="soft-badge soft-badge-orange">Stable</span>
          <?php elseif ($delta > 0): ?>
            <span class="soft-badge soft-badge-green">+<?= $delta ?>% vs last week</span>
          <?php else: ?>
            <span class="soft-badge soft-badge-red"><?= $delta ?>% vs last week</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-orange-300"></div>
        <div class="text-xs text-gray-500">Marketing</div>
        <div class="text-3xl font-extrabold text-gray-900">
          <?= ($stats['total'] ?? 0) > 0 ? round((($stats['marketing'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%
        </div>
        <?php $md = (float)($trends['marketing_delta'] ?? 0.0); $ma = abs($md); ?>
        <div class="mt-2">
          <?php if ($ma < 0.5): ?>
            <span class="soft-badge soft-badge-orange">Stable</span>
          <?php elseif ($md > 0): ?>
            <span class="soft-badge soft-badge-green">+<?= $md ?>% this week</span>
          <?php else: ?>
            <span class="soft-badge soft-badge-red"><?= $md ?>% this week</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-600 to-purple-300"></div>
        <div class="text-xs text-gray-500">Analytics</div>
        <div class="text-3xl font-extrabold text-gray-900">
          <?= ($stats['total'] ?? 0) > 0 ? round((($stats['analytics'] ?? 0)/($stats['total'] ?? 1))*100) : 0 ?>%
        </div>
        <?php $ad = (float)($trends['analytics_delta'] ?? 0.0); $aa = abs($ad); ?>
        <div class="mt-2">
          <?php if ($aa < 0.5): ?>
            <span class="soft-badge soft-badge-orange">Stable</span>
          <?php elseif ($ad > 0): ?>
            <span class="soft-badge soft-badge-green">+<?= $ad ?>% this week</span>
          <?php else: ?>
            <span class="soft-badge soft-badge-red"><?= $ad ?>% this week</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white border rounded-xl p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-800">Consent Breakdown</h2>
          <span class="text-xs text-indigo-600">Live</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="order-2 md:order-1 space-y-3">
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Strictly Necessary</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:100%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right">100%</span>
          </div>
          <?php $total = max(1,(int)($stats['total'] ?? 1)); $pct=function($n)use($total){return round(($n/$total)*100);} ?>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Functional</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-indigo-500 rounded-full" style="width:<?= $pct((int)($stats['functional'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['functional'] ?? 0)) ?>%</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Analytics</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-orange-500 rounded-full" style="width:<?= $pct((int)($stats['analytics'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['analytics'] ?? 0)) ?>%</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Advertising</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-purple-500 rounded-full" style="width:<?= $pct((int)($stats['marketing'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['marketing'] ?? 0)) ?>%</span>
          </div>
          </div>
          <div class="order-1 md:order-2 flex items-center justify-center">
            <div class="relative">
              <canvas id="consentDonut" width="180" height="180"></canvas>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                  <div class="text-xl font-extrabold text-gray-900"><?= (int)($acceptance_pct ?? 0) ?>%</div>
                  <div class="text-xs text-gray-500">accepted</div>
                </div>
              </div>
            </div>
          </div>
          <div class="order-3 space-y-2">
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-emerald-500"></span><span class="text-sm text-gray-600 flex-1">Full Accept</span><span class="text-sm font-semibold"><?= (int)($breakdown['full_accept'] ?? 0) ?></span></div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-indigo-500"></span><span class="text-sm text-gray-600 flex-1">Partial</span><span class="text-sm font-semibold"><?= (int)($breakdown['partial'] ?? 0) ?></span></div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-red-500"></span><span class="text-sm text-gray-600 flex-1">Rejected</span><span class="text-sm font-semibold"><?= (int)($breakdown['rejected'] ?? 0) ?></span></div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-gray-400"></span><span class="text-sm text-gray-600 flex-1">Total Users</span><span class="text-sm font-semibold"><?= (int)($breakdown['total'] ?? 0) ?></span></div>
          </div>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3">
          <button @click="tab='policy'" class="px-3 py-3 border rounded-lg text-left hover:border-indigo-400">
            <div class="text-sm font-semibold">New Policy</div>
            <div class="text-xs text-gray-500">Publish version</div>
          </button>
          <button @click="editDefinition(null); tab='definitions'" class="px-3 py-3 border rounded-lg text-left hover:border-indigo-400">
            <div class="text-sm font-semibold">Add Cookie</div>
            <div class="text-xs text-gray-500">Define a cookie</div>
          </button>
          <a href="/admin/cookies/export" class="px-3 py-3 border rounded-lg text-left hover:border-indigo-400">
            <div class="text-sm font-semibold">Export Data</div>
            <div class="text-xs text-gray-500">Download CSV</div>
          </a>
          <button @click="forceReconsent" class="px-3 py-3 border rounded-lg text-left hover:border-indigo-400">
            <div class="text-sm font-semibold">Re‑consent</div>
            <div class="text-xs text-gray-500">Force all users</div>
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white border rounded-xl p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-800">Recent Activity</h2>
          <span class="text-xs text-gray-500">Last 24 hours</span>
        </div>
        <div class="divide-y">
          <?php
            function rel_time($ts){
              $t = strtotime($ts ?? '');
              if(!$t) return '';
              $d = time()-$t;
              $d = max(0, $d);
              if($d < 60) return $d.'s ago';
              if($d < 3600) return floor($d/60).' minutes ago';
              if($d < 86400) return floor($d/3600).' hours ago';
              return floor($d/86400).' days ago';
            }
            foreach (($activities ?? []) as $a):
            $age = max(0, time() - (strtotime($a['created_at'] ?? '') ?: time()));
            if ($age > 86400) { continue; }
            $act=strtolower($a['action'] ?? '');
            $label = $act==='given'?'Full Accept':($act==='revoked'?'Rejected':'Policy Update');
            $loc = trim(($a['region_code'] ?? '').', '.($a['country_code'] ?? ''));
            $browser = $a['browser_name'] ?? '';
          ?>
          <div class="py-3 flex items-center justify-between hover:bg-indigo-50 rounded-lg px-2 transition">
            <div>
              <div class="text-sm">
                <span class="font-medium">
                  <?php if (!empty($a['email'])): ?>
                    <?= htmlspecialchars($a['email']) ?>
                  <?php elseif (!empty($a['anonymous_id'])): ?>
                    Anonymous (<?= htmlspecialchars(substr($a['anonymous_id'],0,8)) ?>…)
                  <?php else: ?>
                    visitor
                  <?php endif; ?>
                </span>
                <?= $act==='given'?'accepted all cookies':($act==='revoked'?'rejected optional cookies':'updated') ?>
              </div>
              <div class="text-xs text-gray-500">
                <?= rel_time($a['created_at'] ?? '') ?>
                <?php if ($loc !== ','): ?> · <?= htmlspecialchars($loc) ?><?php endif; ?>
                <?php if ($browser): ?> · <?= htmlspecialchars($browser) ?><?php endif; ?>
                <?php if (!empty($a['ip_hash'])): ?> · IP hash: <?= htmlspecialchars(substr($a['ip_hash'],0,10)) ?>…<?php endif; ?>
              </div>
            </div>
            <span class="px-2 py-0.5 rounded-full border text-xs
              <?php if($act==='given'): ?> border-green-200 text-green-700 bg-green-50
              <?php elseif($act==='revoked'): ?> border-red-200 text-red-700 bg-red-50
              <?php else: ?> border-indigo-200 text-indigo-700 bg-indigo-50 <?php endif; ?>">
              <?= $label ?>
            </span>
          </div>
          <?php endforeach; if (empty($activities)): ?>
          <div class="py-6 text-sm text-gray-500">No recent activity</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
          <h2 class="font-semibold text-gray-800">Active Policy</h2>
          <span class="soft-badge soft-badge-green">Live</span>
        </div>
        <div class="text-sm">
          <div class="flex items-center justify-between">
            <span>Version</span>
            <span class="soft-badge soft-badge-blue"><?= htmlspecialchars($activePolicy['version_number'] ?? '') ?></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span>Published</span>
            <span><?= htmlspecialchars($activePolicy['effective_from'] ?? '') ?></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span>Re‑consent required</span>
            <span><?= ((int)($activePolicy['requires_reconsent'] ?? 0)===1)?'Yes':'No' ?></span>
          </div>
          <a href="/cookie/policy" class="block mt-3 text-indigo-600">View Full Policy →</a>
        </div>
      </div>
    </div>
  </div>

  <div x-show="tab==='categories'" class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white border rounded-xl p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-800">Cookie Categories</h2>
          <button @click="saveCategories" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
            <span>💾</span><span>Save Changes</span>
          </button>
        </div>
        <div class="space-y-3">
          <?php foreach ($categories as $cat): ?>
          <?php
            $nm = (string)($cat['name'] ?? '');
            $emoji = '🍪'; $ring = 'hover:ring-indigo-300'; $iconbg = 'bg-indigo-100'; $icontext = 'text-indigo-700';
            if ($nm === 'Strictly Necessary') { $emoji = '🔒'; $ring = 'hover:ring-emerald-300'; $iconbg = 'bg-emerald-100'; $icontext = 'text-emerald-700'; }
            elseif ($nm === 'Functional') { $emoji = '⚙️'; $ring = 'hover:ring-indigo-300'; $iconbg = 'bg-indigo-100'; $icontext = 'text-indigo-700'; }
            elseif ($nm === 'Analytics & Performance') { $emoji = '📊'; $ring = 'hover:ring-orange-300'; $iconbg = 'bg-orange-100'; $icontext = 'text-orange-700'; }
            elseif ($nm === 'Advertising & Targeting') { $emoji = '📣'; $ring = 'hover:ring-purple-300'; $iconbg = 'bg-purple-100'; $icontext = 'text-purple-700'; }
          ?>
          <div class="border rounded-[14px] p-4 transition hover:shadow <?= $ring ?> hover:ring-2 hover:ring-offset-1" style="border-width:1.5px">
            <div class="flex items-start justify-between">
              <div>
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg <?= $iconbg ?> <?= $icontext ?> text-base"><?= $emoji ?></span>
              <span class="font-semibold text-gray-900"><?= htmlspecialchars($nm) ?></span>
              <?php if ((int)($cat['is_mandatory'] ?? 0) === 1): ?>
                <span class="soft-badge soft-badge-blue">Mandatory</span>
              <?php endif; ?>
              <?php if ((int)($cat['is_active'] ?? 1) === 1): ?>
                <span class="soft-badge soft-badge-green">Active</span>
              <?php else: ?>
                <span class="soft-badge soft-badge-gray">Disabled</span>
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
      <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">What do these mean?</h2>
        <div class="space-y-3 text-sm">
          <div class="px-3 py-2 rounded-lg border bg-emerald-50 text-emerald-700">Mandatory = Always Required</div>
          <div class="px-3 py-2 rounded-lg border bg-indigo-50 text-indigo-700">Active = Category is Enabled</div>
          <div class="px-3 py-2 rounded-lg border bg-orange-50 text-orange-700">GDPR Tip: Analytics & Advertising require explicit consent</div>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Category Acceptance</h2>
        <?php $total = max(1,(int)($stats['total'] ?? 1)); $pct=function($n)use($total){return round(($n/$total)*100);} ?>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Strictly Necessary</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:100%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right">100%</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Functional</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-indigo-500 rounded-full" style="width:<?= $pct((int)($stats['functional'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['functional'] ?? 0)) ?>%</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Analytics</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-orange-500 rounded-full" style="width:<?= $pct((int)($stats['analytics'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['analytics'] ?? 0)) ?>%</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 w-40">Advertising</span>
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-purple-500 rounded-full" style="width:<?= $pct((int)($stats['marketing'] ?? 0)) ?>%"></div></div>
            <span class="text-xs font-bold text-gray-700 w-10 text-right"><?= $pct((int)($stats['marketing'] ?? 0)) ?>%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="tab==='policy'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white border rounded-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-800">Policy Versions</h2>
        <button @click="policy.version=''" class="px-3 py-2 border rounded">New Version</button>
      </div>
      <div class="space-y-2">
        <?php foreach ($versions as $v): ?>
        <div class="border rounded p-3 flex items-center justify-between">
          <div>
            <div class="font-medium flex items-center gap-2">
              <?= htmlspecialchars($v['version_number']) ?>
              <?php if ((int)$v['is_active']===1): ?>
                <span class="soft-badge soft-badge-green">Active</span>
              <?php endif; ?>
            </div>
            <div class="text-xs text-gray-500 mt-1">Effective: <?= htmlspecialchars($v['effective_from']) ?> · Re‑consent: <?= ((int)$v['requires_reconsent']===1)?'Yes':'No' ?></div>
          </div>
          <a class="text-xs text-indigo-600 hover:underline" href="/cookie/policy?version=<?= urlencode($v['version_number']) ?>">View</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="bg-white border rounded-xl p-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-800">Policy Editor</h2>
        <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border"><?= htmlspecialchars($activePolicy['version_number'] ?? '') ?></span>
      </div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
      <input x-model="policy.version" class="w-full border rounded px-3 py-2 mb-3" placeholder="v-YYYYMMDD-HHMMSS">
      <label class="block text-sm font-medium text-gray-700 mb-1">Policy Text</label>
      <textarea id="policy-editor" x-model="policy.text" class="w-full border rounded px-3 py-2 h-40"></textarea>
      <label class="flex items-center gap-2 mt-3 text-sm">
        <input type="checkbox" x-model="policy.reconsent"> Requires Re‑consent from all users
      </label>
      <div class="mt-3">
        <button @click="updatePolicy" class="px-3 py-2 bg-green-600 text-white rounded-md">Publish New Policy</button>
      </div>
    </div>
  </div>

  <div x-show="tab==='definitions'" class="bg-white border rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-gray-800">Cookie Definitions</h2>
      <button @click="editDefinition(null)" class="px-3 py-2 bg-indigo-600 text-white rounded-md">Add Cookie Definition</button>
    </div>
    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-600">
            <th class="p-2">Cookie Name</th><th class="p-2">Category</th><th class="p-2">Provider</th><th class="p-2">Duration</th><th class="p-2">Secure</th><th class="p-2">Active</th><th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($definitions as $d): ?>
          <tr class="border-t">
            <td class="p-2"><?= htmlspecialchars($d['cookie_name']) ?></td>
            <td class="p-2"><?= htmlspecialchars($d['category_name']) ?></td>
            <td class="p-2"><?= htmlspecialchars($d['provider']) ?></td>
            <td class="p-2"><?= htmlspecialchars($d['duration_type']) ?><?= (int)$d['duration_days']>0?(' / '.(int)$d['duration_days'].' days'):'' ?></td>
            <td class="p-2"><?= ((int)$d['is_secure']===1)?'✓':'✗' ?></td>
            <td class="p-2"><?= ((int)$d['is_active']===1)?'On':'Off' ?></td>
            <td class="p-2"><button @click="editDefinition(<?= json_encode($d) ?>)" class="px-2 py-1 bg-gray-100 rounded">Edit</button></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <template x-if="showDefModal">
    <div class="fixed inset-0 bg-gradient-to-b from-slate-900/10 to-slate-900/40 backdrop-blur-sm flex items-center justify-center">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl ring-1 ring-slate-200">
        <h3 class="text-lg font-semibold mb-4">Cookie Definition</h3>
        <div class="space-y-3">
          <input x-model="def.id" type="hidden">
          <label class="block text-sm">Name <input x-model="def.cookie_name" class="w-full border rounded px-3 py-2"></label>
          <label class="block text-sm">Category ID <input x-model="def.category_id" class="w-full border rounded px-3 py-2" type="number"></label>
          <label class="block text-sm">Provider <input x-model="def.provider" class="w-full border rounded px-3 py-2" placeholder="e.g. Google, Facebook"></label>
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
    tab: 'overview',
    policy: (function(){ var ap = <?= json_encode($activePolicy ?? []) ?>; return { version: ap.version_number || '', text: ap.policy_text || '', reconsent: (parseInt(ap.requires_reconsent||0,10)===1) }; })(),
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
    },
    saveCategories(){
      const toast = document.getElementById('toast');
      if (toast) {
        toast.textContent = 'Changes saved';
        toast.style.display = 'block';
        setTimeout(()=>{ toast.style.display = 'none'; }, 2000);
      }
    }
  }
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var el = document.getElementById('consentDonut');
  if (el && window.Chart) {
    try {
      var data = [<?= (int)($breakdown['full_accept'] ?? 0) ?>, <?= (int)($breakdown['partial'] ?? 0) ?>, <?= (int)($breakdown['rejected'] ?? 0) ?>];
      var ctx = el.getContext('2d');
      window.MWCharts = window.MWCharts || {};
      if (window.MWCharts.consentDonut) {
        try { window.MWCharts.consentDonut.destroy(); } catch (e) {}
        window.MWCharts.consentDonut = null;
      }
      window.MWCharts.consentDonut = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Full Accept','Partial','Rejected'],
        datasets: [{
          data: data,
          backgroundColor: ['#10B981','#6366F1','#EF4444'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        animation: { animateRotate: true, duration: 800 },
        plugins: { legend: { display: false } }
      }
    });
    } catch (e) { console.warn('Failed to render consent donut', e); }
  }
});
</script>
<div id="toast" class="fixed bottom-6 right-6 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg" style="display:none"></div>
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
