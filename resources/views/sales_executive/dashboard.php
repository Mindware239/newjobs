<div>
  <h1 class="text-2xl font-semibold mb-4">Sales Executive Dashboard</h1>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-500">My Assigned</div>
      <div class="text-2xl font-bold mt-2"><?= (int)($stats['assigned'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-500">New</div>
      <div class="text-2xl font-bold mt-2"><?= (int)($stats['new'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-500">Contacted</div>
      <div class="text-2xl font-bold mt-2"><?= (int)($stats['contacted'] ?? 0) ?></div>
    </div>
    <div class="bg-white rounded shadow p-4">
      <div class="text-sm text-gray-500">Converted</div>
      <div class="text-2xl font-bold mt-2"><?= (int)($stats['converted'] ?? 0) ?></div>
    </div>
  </div>
</div>

