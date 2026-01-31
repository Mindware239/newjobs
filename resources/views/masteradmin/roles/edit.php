<h1 class="text-2xl font-bold mb-4">Edit Role</h1>
<form method="post" action="/master/roles/<?= (int)($role['id'] ?? 0) ?>/edit" class="space-y-4">
  <input type="text" name="name" value="<?= htmlspecialchars($role['name'] ?? '') ?>" class="border px-3 py-2 rounded w-full" />
  <input type="text" name="slug" value="<?= htmlspecialchars($role['slug'] ?? '') ?>" class="border px-3 py-2 rounded w-full" />
  <input type="text" name="description" value="<?= htmlspecialchars($role['description'] ?? '') ?>" class="border px-3 py-2 rounded w-full" />
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <?php foreach ($permissions as $p): $pid = (int)($p['id'] ?? 0); ?>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="permission_ids[]" value="<?= $pid ?>" <?= in_array($pid, $assigned ?? []) ? 'checked' : '' ?> />
        <span><?= htmlspecialchars($p['slug'] ?? '') ?></span>
      </label>
    <?php endforeach; ?>
  </div>
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
</form>

