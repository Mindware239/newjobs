<h1 class="text-2xl font-bold mb-4">Create Role</h1>
<?php if (!empty($error)): ?>
<div class="mb-4 p-3 bg-red-50 text-red-800 rounded"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post" action="/master/roles/create" class="space-y-4">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
  <div class="border rounded p-4">
    <p class="text-sm font-semibold mb-3">Assign to existing role (optional)</p>
    <select name="assign_role_slug" class="border px-3 py-2 rounded w-full">
      <option value="">Select existing role</option>
      <?php foreach (($availableRoles ?? []) as $r): ?>
        <option value="<?= htmlspecialchars($r['slug'] ?? '') ?>">
          <?= htmlspecialchars(($r['name'] ?? '') . ' (' . ($r['slug'] ?? '') . ')') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <p class="text-xs text-gray-500 mt-2">Or create a new role below</p>
  </div>
  <input type="text" name="name" placeholder="Name" class="border px-3 py-2 rounded w-full" />
  <input type="text" name="slug" placeholder="Slug" class="border px-3 py-2 rounded w-full" />
  <input type="text" name="description" placeholder="Description" class="border px-3 py-2 rounded w-full" />
  <div class="border rounded p-4">
    <p class="text-sm font-semibold mb-3">Create and assign user to this role</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input type="email" name="user_email" placeholder="User Email" class="border px-3 py-2 rounded w-full" />
      <input type="text" name="user_name" placeholder="Full Name (optional)" class="border px-3 py-2 rounded w-full" />
      <input type="password" name="user_password" placeholder="Password" class="border px-3 py-2 rounded w-full" />
    </div>
  </div>
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
</form>
