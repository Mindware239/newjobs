<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
    <p class="text-sm text-gray-500 mt-1">Manage user roles and their access levels</p>
  </div>
  <a href="/master/roles/create" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors duration-200 flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Create New Role
  </a>
</div>

<?php if (!empty($created_user_email)): ?>
<div class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 shadow-sm flex flex-col gap-1">
  <div class="font-medium flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    User created successfully
  </div>
  <div class="text-sm ml-7">
    <span class="font-semibold">Email:</span> <?= htmlspecialchars($created_user_email) ?>
    <?php if (!empty($created_user_role)): ?>
      <span class="ml-3 text-xs bg-green-200 text-green-800 px-2 py-0.5 rounded-full">Role: <?= htmlspecialchars($created_user_role) ?></span>
    <?php endif; ?>
    <?php if (!empty($created_user_password)): ?>
      <div class="mt-1 p-2 bg-white/50 rounded border border-green-100 inline-block">
        <span class="font-semibold text-green-800">Password:</span> 
        <code class="font-mono bg-green-100 px-1 rounded"><?= htmlspecialchars($created_user_password) ?></code>
        <span class="text-xs text-green-600 ml-1">(Please copy and store securely)</span>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
  <form method="GET" class="p-4 flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search Roles</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by name or slug..." class="pl-9 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-10 border px-3">
      </div>
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Per Page</label>
      <select name="perPage" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-10 border px-3 pr-8">
        <?php foreach ([10,20,30,50] as $pp): ?>
          <option value="<?= $pp ?>" <?= (int)($perPage ?? 10) === $pp ? 'selected' : '' ?>><?= $pp ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="h-10 px-6 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-colors">Apply Filters</button>
  </form>
</div>

<div class="grid grid-cols-1 gap-6">
  <?php foreach ($roles as $r): ?>
    <?php 
      $rid = (int)($r['id'] ?? 0); 
      $userCount = (int)($counts[$rid] ?? 0);
      $users = $assignedUsers[$rid] ?? [];
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
      <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-1">
            <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($r['name'] ?? $r['slug'] ?? '') ?></h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 font-mono">
              <?= htmlspecialchars($r['slug'] ?? '') ?>
            </span>
          </div>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($r['description'] ?? 'No description provided.') ?></p>
        </div>
        
        <div class="flex items-center gap-4 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100">
          <div class="text-sm text-gray-600 flex items-center gap-2">
            <div class="flex -space-x-2 overflow-hidden">
               <!-- Placeholder avatars if we had them -->
               <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs text-gray-500 font-medium">
                 <?= $userCount ?>
               </div>
            </div>
            <span>User<?= $userCount !== 1 ? 's' : '' ?> Assigned</span>
          </div>
          
          <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
          
          <div class="flex items-center gap-2">
            <a href="/master/roles/<?= $rid ?>/edit" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Role">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
            </a>
            <?php if ($userCount === 0): ?>
            <form method="POST" action="/master/roles/<?= $rid ?>/delete" onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.');" class="inline">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <button class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Role">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </form>
            <?php else: ?>
              <span class="p-2 text-gray-300 cursor-not-allowed" title="Cannot delete role with assigned users">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <?php if (!empty($users)): ?>
      <div class="border-t border-gray-100 bg-gray-50/50">
        <details class="group">
          <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors list-none select-none">
            <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-open:rotate-90 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              View <?= count($users) ?> Assigned Users
            </span>
            <span class="text-xs text-gray-500 opacity-0 group-open:opacity-100 transition-opacity">Click to collapse</span>
          </summary>
          
          <div class="px-4 pb-4">
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                            <?= strtoupper(substr($u['email'] ?? 'U', 0, 1)) ?>
                          </div>
                          <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['email'] ?? '') ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($u['phone'] ?? 'No phone') ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= (($u['status'] ?? '') === 'active') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                          <?= htmlspecialchars($u['status'] ?? 'inactive') ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <div class="text-xs">
                          Last Login: <span class="font-medium"><?= htmlspecialchars($u['last_login'] ?? 'Never') ?></span>
                        </div>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                           <button type="button" onclick="document.getElementById('reset-form-<?= $u['id'] ?>').classList.toggle('hidden');" class="text-indigo-600 hover:text-indigo-900 text-xs border border-indigo-200 rounded px-2 py-1 hover:bg-indigo-50 transition-colors">
                             Reset Password
                           </button>
                           
                           <form id="reset-form-<?= $u['id'] ?>" method="POST" action="/master/roles/<?= $rid ?>/users/<?= (int)($u['id'] ?? 0) ?>/reset-password" class="hidden absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10 p-3">
                              <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                              <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                              <div class="flex gap-2">
                                <input type="password" name="new_password" placeholder="min 8 chars" class="block w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 px-2 py-1 border" minlength="8" required>
                                <button type="submit" class="bg-indigo-600 text-white text-xs px-2 py-1 rounded hover:bg-indigo-700">Save</button>
                              </div>
                              <div class="mt-1 text-right">
                                <button type="button" onclick="document.getElementById('reset-form-<?= $u['id'] ?>').classList.add('hidden')" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                              </div>
                           </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </details>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php 
  $pages = max(1, (int)ceil(($total ?? 0) / ($perPage ?? 10))); 
?>
<div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
  <div class="text-sm text-gray-500">
    Showing <span class="font-medium"><?= ($page - 1) * $perPage + 1 ?></span> to <span class="font-medium"><?= min($total, $page * $perPage) ?></span> of <span class="font-medium"><?= $total ?></span> results
  </div>
  <div class="flex gap-1">
    <?php for ($p=1; $p<=$pages; $p++): ?>
      <a href="?search=<?= urlencode($search ?? '') ?>&perPage=<?= (int)($perPage ?? 10) ?>&page=<?= $p ?>" class="px-3 py-1 text-sm font-medium rounded-md transition-colors <?= ($p === (int)($page ?? 1)) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' ?>">
        <?= $p ?>
      </a>
    <?php endfor; ?>
  </div>
</div>