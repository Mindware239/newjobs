<div>
  <div class="mb-6">
    <h1 class="text-2xl font-semibold">Auto-Create Panel Engine</h1>
    <?php if (!empty($error)): ?><div class="mt-2 text-red-600 text-sm"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="mt-2 text-green-600 text-sm"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  </div>
  <div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="/master/system/panel-builder">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Panel Type</label>
          <select name="panel_type" class="w-full px-3 py-2 border rounded" required>
            <option value="sales_executive">Sales Executive</option>
            <option value="verification_executive">Verification Executive</option>
            <option value="support_executive">Support Executive</option>
            <option value="finance_manager">Finance Manager</option>
            <option value="system_auditor">System Auditor</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">User Email Prefix</label>
          <input type="text" name="prefix" placeholder="sales" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Count</label>
          <input type="number" name="count" value="3" min="1" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password (optional)</label>
          <input type="text" name="password" placeholder="default is 'password'" class="w-full px-3 py-2 border rounded">
        </div>
      </div>
      <div class="mt-4">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Generate</button>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-lg font-semibold mb-3">Seed Permissions</h2>
    <p class="text-sm text-gray-600 mb-4">Seed Sales, Verification, Support, Finance, and System/Audit permissions.</p>
    <a href="/master/system/permissions/seed" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded">Seed Now</a>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-lg font-semibold mb-3">Seed Sample Tickets</h2>
    <p class="text-sm text-gray-600 mb-4">Insert sample support tickets to validate Support Executive workflow.</p>
    <a href="/master/system/tickets/seed" class="inline-block px-4 py-2 bg-green-600 text-white rounded">Insert Samples</a>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mt-6">
    <h2 class="text-lg font-semibold mb-3">Seed Sample Sales Leads</h2>
    <p class="text-sm text-gray-600 mb-4">Insert sample leads to validate Sales Manager/Executive panels.</p>
    <a href="/master/system/leads/seed" class="inline-block px-4 py-2 bg-green-600 text-white rounded">Insert Leads</a>
  </div>
</div>

