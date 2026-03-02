<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
  <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($title ?? 'Cookie Policy') ?></h1>
  <div class="text-sm text-gray-600 mb-4">
    Version: <span class="font-semibold"><?= htmlspecialchars($policy['version_number']) ?></span>
    · Effective: <?= htmlspecialchars($policy['effective_from']) ?>
    · Requires re-consent: <?= ((int)($policy['requires_reconsent'] ?? 0)===1)?'Yes':'No' ?>
  </div>
  <div id="policy-content">
    <?= $policy['policy_text'] ?? '<p>No policy content available.</p>' ?>
  </div>
</div>
