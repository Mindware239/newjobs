<div class="w-full px-6">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Employment Verification</h1>
    <p class="text-sm text-gray-600">Manage verifications, review documents and HR responses.</p>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
      <div class="h-10 w-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white bg-indigo-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check h-5 w-5"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="m9 15 2 2 4-4"></path></svg></div>
      <div>
        <div class="text-2xl font-semibold text-gray-900"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Total</div>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
      <div class="h-10 w-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white bg-green-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check h-5 w-5"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg></div>
      <div>
        <div class="text-2xl font-semibold text-green-700"><?= (int)($stats['verified'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Verified</div>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
     <div class="h-10 w-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white bg-yellow-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-5 w-5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
      <div>
        <div class="text-2xl font-semibold text-yellow-700"><?= (int)($stats['under_review'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Under Review</div>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
      <div class="h-10 w-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white bg-blue-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-5 w-5"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg></div>
      <div>
        <div class="text-2xl font-semibold text-indigo-700"><?= (int)($stats['pending_hr'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Pending HR</div>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
      <div class="h-10 w-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white bg-red-500"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x h-5 w-5"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg></div>
      <div>
        <div class="text-2xl font-semibold text-red-700"><?= (int)($stats['not_verified'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Not Verified</div>
      </div>
    </div>
  </div>
  <form method="get" class="flex items-center gap-3 mb-6">
    <div class="flex-1 relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>      </span>
      <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Search candidate or company..." class="w-full rounded-xl border-gray-300 pl-10 px-3 py-2">
    </div>
    <div class="flex items-center gap-2">
      <select name="status" class="rounded-xl border-gray-300 px-3 py-2">
        <option value="all" <?= (($filters['status'] ?? 'all') === 'all') ? 'selected' : '' ?>>All</option>
        <option value="under_review" <?= (($filters['status'] ?? '') === 'under_review') ? 'selected' : '' ?>>Under Review</option>
        <option value="verified" <?= (($filters['status'] ?? '') === 'verified') ? 'selected' : '' ?>>Verified</option>
        <option value="not_verified" <?= (($filters['status'] ?? '') === 'not_verified') ? 'selected' : '' ?>>Not Verified</option>
        <option value="pending_hr" <?= (($filters['status'] ?? '') === 'pending_hr') ? 'selected' : '' ?>>Pending HR</option>
      </select>
    </div>
    <button class="px-4 py-2 bg-blue-600 text-white rounded-md">Filter</button>
  </form>
  <?php if (!empty($warning ?? null)): ?>
    <div class="mb-4 p-3 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded">
      <?= htmlspecialchars($warning) ?>
    </div>
  <?php endif; ?>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">HR Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Docs</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Sent</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
          <th class="px-6 py-3"></th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach (($records ?? []) as $r): ?>
          <tr>
            <td class="px-6 py-4">
              <?php $name = trim((string)($r['full_name'] ?? '')); $parts = preg_split('/\s+/', $name); $initials = strtoupper(substr($parts[0] ?? '',0,1) . substr($parts[count($parts)-1] ?? '',0,1)); ?>
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-purple-600 text-white flex items-center justify-center text-sm font-semibold"><?= htmlspecialchars($initials ?: 'NA') ?></div>
                <div>
                  <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($name) ?></div>
                  <div class="text-xs text-gray-500"><?= htmlspecialchars($r['candidate_email'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($r['company_name'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($r['designation'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm text-gray-600">
              <?php
                $sdRaw = $r['start_date'] ?? '';
                $edRaw = $r['end_date'] ?? '';
                $sd = $sdRaw ? date('Y-m-d', strtotime($sdRaw)) : '';
                $ed = ($edRaw && $edRaw !== '0000-00-00') ? date('Y-m-d', strtotime($edRaw)) : 'Present';
              ?>
              <?= htmlspecialchars($sd) ?> to <?= htmlspecialchars($ed) ?>
            </td>
            <td class="px-6 py-4">
              <?php $status = $r['status_overall'] ?? 'under_review'; ?>
              <?php if ($status === 'verified'): ?>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4"/></svg> Verified</span>
              <?php elseif ($status === 'not_verified'): ?>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 9l6 6M15 9l-6 6"/></svg> Not Verified</span>
              <?php elseif ($status === 'under_review'): ?>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8v5h5"/></svg> Under Review</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16v14H4zM4 18l4-4"/></svg> Pending HR</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($r['req_hr_email'] ?? '') ?></td>
            <td class="px-6 py-4 text-sm">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700">
                <?= (int)($r['dcnt.doc_count'] ?? $r['doc_count'] ?? 0) ?>
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              <?php $cnt = (int)($r['req_count'] ?? 0); ?>
              <span class="font-medium"><?= $cnt ?></span>
              <span class="ml-2 text-gray-500"><?= !empty($r['req_created_at']) ? date('Y-m-d', strtotime($r['req_created_at'])) : '' ?></span>
            </td>
            <td class="px-6 py-4 text-xs font-mono text-gray-700">
              <?php $tok = (string)($r['req_token'] ?? ''); ?>
              <?= $tok ? htmlspecialchars(substr($tok, 0, 8) . '…') : '—' ?>
            </td>
            <td class="px-6 py-4 text-right">
              <a href="/admin/verification/<?= (int)$r['id'] ?>" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-indigo-300 text-indigo-700 hover:bg-indigo-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7zm11 4a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                <span>View</span>
              </a>
              <form method="post" action="/admin/verification/<?= (int)$r['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this employment verification record and all related documents/logs?');">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-red-300 text-red-700 hover:bg-red-50 ml-2" type="submit" title="Delete record">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M6 6l1 14h10l1-14M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  <span>Delete</span>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($records ?? [])): ?>
          <tr><td colspan="9" class="px-6 py-4 text-sm text-gray-600">No records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
