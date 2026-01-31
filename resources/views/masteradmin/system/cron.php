<div>
  <h1 class="text-2xl font-semibold mb-4">Cron Manager</h1>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded shadow p-4 lg:col-span-2">
      <p class="text-sm text-gray-600 mb-4">Trigger maintenance tasks manually for testing.</p>
      <form method="POST" action="/master/system/cron/run" class="flex gap-3 items-center">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <select name="task" class="px-3 py-2 border rounded">
          <option value="cleanup_sessions">Cleanup Sessions</option>
          <option value="reindex_jobs">Reindex Jobs (ES)</option>
          <option value="notify_expiring_subscriptions">Notify Expiring Subscriptions</option>
          <option value="auto_apply_candidates">Run Auto-Apply Now</option>
        </select>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Run</button>
      </form>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Auto-Apply Snapshot</h2>
        <?php $enabled = (int)($autoApply['enabled'] ?? 1) === 1; ?>
        <span class="px-2 py-1 rounded text-xs font-semibold <?= $enabled?'bg-green-100 text-green-800':'bg-red-100 text-red-800' ?>">
          <?= $enabled ? 'Enabled' : 'Disabled' ?>
        </span>
      </div>
      <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600">Auto-applies today</div>
          <div class="mt-1 text-xl font-bold text-gray-900"><?= (int)($metrics['auto_applies_today'] ?? 0) ?></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600">Failed auto-applies</div>
          <div class="mt-1 text-xl font-bold text-gray-900"><?= (int)($metrics['failed_auto_applies_today'] ?? 0) ?></div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600">Avg. match score</div>
          <div class="mt-1 text-xl font-bold text-gray-900"><?= number_format((float)($metrics['avg_match_score_today'] ?? 0.0), 1) ?>%</div>
        </div>
        <div class="rounded-md border border-gray-200 p-3">
          <div class="text-xs text-gray-600">Daily limit</div>
          <div class="mt-1 text-xl font-bold text-gray-900"><?= (int)($autoApply['daily_global_limit'] ?? 0) ?></div>
        </div>
      </div>
      <?php $alert = !empty($metrics['load_alert']); ?>
      <div class="mb-3">
        <span class="px-2 py-1 rounded text-xs font-semibold <?= $alert?'bg-orange-100 text-orange-800':'bg-green-100 text-green-800' ?>">
          <?= $alert?'High load (80%+ of daily limit)':'Normal load' ?>
        </span>
      </div>
      <div class="flex items-center gap-2">
        <form method="POST" action="/master/settings" class="inline">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="auto_apply_enabled" value="<?= $enabled ? '0' : '1' ?>">
          <button class="px-3 py-2 <?= $enabled ? 'bg-red-600' : 'bg-green-600' ?> text-white rounded text-sm">
            <?= $enabled ? 'Emergency Stop' : 'Enable Auto-Apply' ?>
          </button>
        </form>
        <a href="/master/settings" class="text-sm text-blue-600 hover:text-blue-800">Open Settings</a>
      </div>
    </div>
  </div>
</div>
