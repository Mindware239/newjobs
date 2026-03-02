<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Organizations</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
.container-wf{max-width:1200px;}
</style>
</head>

<body class="bg-white text-gray-800" x-data="{ showCreate:false }">

<!-- HEADER -->
<header class="border-b">
  <div class="container-wf mx-auto px-6 py-4 flex justify-between items-center">
    <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-10 md:h-14 w-auto">
            </a>
        </div>
 <nav class="flex gap-6 text-sm text-gray-700">
      <a href="/social-employer/newlisting">＋ Post a job</a>
      <a href="/social-employer/listings" class="text-red-500">Job listings</a>
      <a href="/social-employer/application">Applications</a>
      <a href="/social-employer/organisation"class="text-red-500">Organizations & users</a>
       <a href="/social-employer/account"class="text-red-500">Account & profile</a>
      <a href="/logout">Logout</a>
    </nav>
  </div>
</header>

<!-- BLACK BAR -->
<nav class="bg-black text-white">
  <div class="container-wf mx-auto px-6 py-3 flex gap-6 text-sm">
    <a href="/" class="hover:underline">◀ Back to Home</a>
    <a href="/pricing" class="hover:underline">Pricing</a>
    <a href="/blog" class="hover:underline">Hiring insights</a>
    <a href="/aboutus" class="hover:underline">About us</a>
    <a href="/supports" class="hover:underline">Get Help</a>
  </div>
</nav>

<!-- MAIN -->
<div class="container-wf mx-auto px-6 py-16">

<!-- ================= SEARCH ================= -->
<div x-show="!showCreate">

<h3 class="font-semibold mb-1">
Search for an organization by name
</h3>

<p class="text-sm text-gray-600 mb-4">
Please only create a new organization if you cannot find it in our database
</p>

<div class="flex items-center gap-6">

<input 
class="w-[420px] border rounded px-4 py-2"
placeholder="Begin typing to search">

<button 
@click="showCreate=true"
class="text-red-500 text-sm hover:underline">
Not found? Create a new organization
</button>

</div>

</div>

<!-- ================= LIST ================= -->
<div class="mt-10">
<?php $organizations = $organizations ?? []; ?>
<?php if (!empty($organizations)): ?>
  <div class="flex justify-between items-center mb-3">
    <h3 class="font-semibold">Your organizations</h3>
    <span class="text-sm text-gray-500"><?= count($organizations) ?> total</span>
  </div>
  <div class="overflow-x-auto bg-white border rounded">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-4 py-3">Logo</th>
          <th class="px-4 py-3 text-left">Name</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Website</th>
          <th class="px-4 py-3">Staff</th>
          <th class="px-4 py-3">Mission Focus</th>
          <th class="px-4 py-3">Created</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($organizations as $org): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <?php
                $logo = (string)($org['logo_url'] ?? '');
                $website = (string)($org['website'] ?? '');
                $host = '';
                if ($website !== '') {
                  $url = preg_match('~^https?://~i', $website) ? $website : ('http://' . $website);
                  $host = parse_url($url, PHP_URL_HOST) ?: '';
                }
                $fallback = $host ? ('https://www.google.com/s2/favicons?domain=' . $host . '&sz=64') : '/uploads/default-company.png';
                $img = $logo ?: $fallback;
              ?>
              <div class="w-10 h-10 border rounded bg-white flex items-center justify-center">
                <img src="<?= htmlspecialchars($img) ?>" alt="Logo" class="max-w-full max-h-full object-contain" onerror="this.onerror=null;this.src='/uploads/mindware-infotechlogo.png'">
              </div>
            </td>
            <td class="px-4 py-3 font-medium text-gray-900">
              <?= htmlspecialchars($org['organization_name'] ?? '-') ?>
            </td>
            <td class="px-4 py-3">
              <?= htmlspecialchars($org['organization_type'] ?? '-') ?>
            </td>
            <td class="px-4 py-3">
              <?php if (!empty($org['website'])): ?>
                <a href="<?= htmlspecialchars($org['website']) ?>" target="_blank" class="text-blue-600 hover:underline">Visit</a>
              <?php else: ?>
                <span class="text-gray-400">—</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
              <?= (int)($org['staff_count'] ?? 0) ?>
            </td>
            <td class="px-4 py-3">
              <?= htmlspecialchars($org['mission_focus'] ?? ($org['org_mission_focus'] ?? '')) ?>
            </td>
            <td class="px-4 py-3 text-gray-500">
              <?= htmlspecialchars($org['created_at'] ?? '-') ?>
            </td>
            <td class="px-4 py-3">
              <a href="/social/organizations/<?= (int)($org['id'] ?? 0) ?>/edit" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 hover:bg-gray-50">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <p class="text-sm text-gray-500">No organizations yet. Create one below.</p>
<?php endif; ?>
</div>


<!-- ================= CREATE ================= -->
<div x-show="showCreate" x-transition>

<div class="flex justify-between mb-6">
  <a @click="showCreate=false" 
     class="text-red-500 text-sm cursor-pointer">
     Cancel
  </a>

  <a @click="showCreate=false" 
     class="text-red-500 text-sm cursor-pointer">
     Cancel & search again
  </a>
</div>

<h2 class="font-semibold mb-1">
Create a new organization
</h2>

<p class="text-sm text-gray-600 mb-10">
Please tell us a little about this organization.
</p>

<!-- ✅ FORM CONNECTED TO BACKEND -->
<form method="POST" action="/social/organizations/store" enctype="multipart/form-data">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<div class="grid grid-cols-2 gap-10">

<!-- Organization name -->
<div>
<label class="font-medium block mb-1">
Organization name *
</label>
<input name="organization_name" required
class="w-full border px-3 py-2 rounded"
placeholder="Enter an organization name">
</div>

<!-- Acronyms -->
<div>
<label class="font-medium block mb-1">
Common short names or acronyms
</label>
<input name="acronyms"
class="w-full border px-3 py-2 rounded">
</div>

<!-- Type -->
<div>
<label class="font-medium block mb-1">
Type of organization *
</label>
<select name="organization_type" required
class="w-full border px-3 py-2 rounded">
<option value="">Select</option>
<option>Non-Profit Organization</option>
<option>Private Business</option>
<option>Government</option>
</select>
</div>

<!-- Agency -->
<div class="flex items-center gap-2 mt-6">
<input type="checkbox" name="is_agency" value="1">
<span class="text-sm">Is this a recruiting or staffing agency?</span>
</div>

<!-- Website -->
<div>
<label class="font-medium block mb-1">Website</label>
<input name="website"
class="w-full border px-3 py-2 rounded">
</div>

<!-- Logo -->
<div>
  <label class="font-medium block mb-1">Logo</label>
  <input type="file" name="logo" accept=".png,.jpg,.jpeg" class="w-full border px-3 py-2 rounded">
  <p class="text-xs text-gray-500 mt-1">PNG/JPG up to 5MB</p>
  </div>

<!-- EIN -->
<div>
<label class="font-medium block mb-1">EIN</label>
<input name="ein"
class="w-full border px-3 py-2 rounded">
</div>

<!-- Staff -->
<div>
<label class="font-medium block mb-1">Number of staff</label>
<input type="number" name="staff_count" value="0"
class="w-full border px-3 py-2 rounded">
</div>

<!-- Mission focus -->
<div>
<label class="font-medium block mb-1">
Primary mission focus
</label>
<select name="mission_focus"
class="w-full border px-3 py-2 rounded">
<option>Healthcare</option>
<option>Education</option>
<option>Environment</option>
<option>Community</option>
</select>
</div>

<!-- Mission -->
<div>
<label class="font-medium block mb-1">
Organization’s mission
</label>
<textarea name="mission"
class="w-full border px-3 py-2 rounded"></textarea>
</div>

<!-- Impact -->
<div>
<label class="font-medium block mb-1">
Who does this organization serve?
</label>
<textarea name="impact"
class="w-full border px-3 py-2 rounded"></textarea>
</div>

</div>

<!-- SUBMIT -->
<button
type="submit"
class="mt-10 bg-[#f3b3ac] text-white px-14 py-3 rounded hover:opacity-90">
Create Organization
</button>

</form>

</div>

</div>

</body>
</html>
