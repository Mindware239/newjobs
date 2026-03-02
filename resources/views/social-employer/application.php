<?php 
$apps = $apps ?? ($applications ?? []); 
$base = $base ?? '/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">

<!-- HEADER -->
<header class="bg-white shadow-sm border-b">
<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

<a href="<?= $base ?>" class="flex items-center gap-3">
<img src="<?php echo $base; ?>uploads/Mindware-infotech.png" class="h-11">
<span class="font-bold text-lg text-gray-700">Employer Panel</span>
</a>

<nav class="flex gap-7 text-sm font-medium">
<a href="/social-employer/newlisting" class="hover:text-red-600">Post Job</a>
<a href="/social-employer/listings" class="hover:text-red-600">Listings</a>
<a href="/social-employer/application" class="text-red-600 border-b-2 border-red-600 pb-1">Applications</a>
<a href="/social-employer/organisation" class="hover:text-red-600">Organizations</a>
<a href="/social-employer/account" class="hover:text-red-600">Account</a>
<a href="/logout" class="text-gray-500 hover:text-red-600">Logout</a>
</nav>

</div>
</header>

<!-- TOP BAR -->
<div class="bg-gray-900 text-gray-300 text-sm">
<div class="max-w-7xl mx-auto px-6 py-3 flex gap-6">
<a href="/" class="hover:text-white">Home</a>
<a href="/pricing" class="hover:text-white">Pricing</a>
<a href="/blog" class="hover:text-white">Insights</a>
<a href="/aboutus" class="hover:text-white">About</a>
<a href="/supports" class="hover:text-white">Support</a>
</div>
</div>

<!-- CONTENT -->
<div class="max-w-7xl mx-auto px-6 py-10">

<!-- TITLE -->
<div class="mb-8">
<h1 class="text-3xl font-bold text-gray-900">Applications</h1>
<p class="text-gray-500 mt-1">Review candidates who applied to your jobs</p>
</div>

<!-- FILTER CARD -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 grid md:grid-cols-2 gap-6">

<div>
<label class="text-xs font-semibold text-gray-500 uppercase">Search Candidate</label>
<input type="text" placeholder="Type name..."
class="mt-2 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 outline-none">
</div>

<div>
<label class="text-xs font-semibold text-gray-500 uppercase">Filter Job</label>
<select class="mt-2 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 outline-none">
<option>All Jobs</option>
</select>
</div>

</div>

<!-- RESULT HEADER -->
<div class="flex justify-between items-center mb-4">

<div class="flex gap-6 text-sm font-medium">
<span class="text-red-600 border-b-2 border-red-600 pb-1">All</span>
<span class="text-gray-400">Reviewed</span>
<span class="text-gray-400">Shortlisted</span>
<span class="text-gray-400">Accepted</span>
</div>

<div class="flex items-center gap-4 text-sm">
<span class="bg-gray-200 px-3 py-1 rounded-full font-semibold">
<?= count($apps) ?> results
</span>
<button onclick="location.reload()" class="text-red-600 font-medium hover:underline">
Refresh
</button>
</div>

</div>

<!-- TABLE CARD -->
<div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">

<?php if(empty($apps)): ?>

<div class="text-center py-24">
<div class="text-6xl mb-3">📄</div>
<p class="text-lg text-gray-500">No applications yet</p>
<p class="text-sm text-gray-400">When candidates apply, they’ll appear here.</p>
</div>

<?php else: ?>

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-500 uppercase text-xs sticky top-0">
<tr>
<th class="px-6 py-4 text-left">Candidate</th>
<th class="px-6 py-4">Email</th>
<th class="px-6 py-4">Job</th>
<th class="px-6 py-4">Applied</th>
<th class="px-6 py-4">Status</th>
<th class="px-6 py-4">Resume</th>
<th class="px-6 py-4">Cover Letter</th>
<th class="px-6 py-4 text-center">Action</th>
</tr>
</thead>

<tbody class="divide-y">

<?php foreach($apps as $a): ?>

<?php
$status = strtolower($a['application_status'] ?? 'applied');
$badge = match($status){
'accepted' => 'bg-green-100 text-green-700',
'rejected' => 'bg-red-100 text-red-700',
default => 'bg-blue-100 text-blue-700'
};
?>

<tr class="hover:bg-gray-50 transition">

<td class="px-6 py-4 font-semibold">
<?= htmlspecialchars(($a['first_name'] ?? '').' '.($a['last_name'] ?? '')) ?>
</td>

<td class="px-6 py-4 text-gray-600">
<?= htmlspecialchars($a['email'] ?? '-') ?>
</td>

<td class="px-6 py-4">
<?= htmlspecialchars($a['role_name'] ?? '-') ?>
</td>

<td class="px-6 py-4 text-gray-500">
<?= htmlspecialchars($a['submitted_at'] ?? '-') ?>
</td>

<td class="px-6 py-4">
<span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badge ?>">
<?= ucfirst($status) ?>
</span>
</td>

<td class="px-6 py-4">
<?php if(!empty($a['resume_file'])): ?>
<a target="_blank"
class="text-blue-600 hover:underline"
href="<?= rtrim($base,'/') ?>/uploads/applications/<?= htmlspecialchars($a['resume_file']) ?>">
View
</a>
<?php else: ?>
<span class="text-gray-400">None</span>
<?php endif; ?>
</td>

<td class="px-6 py-4">
<?php if(!empty($a['cover_letter'])): ?>
<div class="text-gray-700 text-sm max-w-xs truncate"><?= htmlspecialchars($a['cover_letter']) ?></div>
<?php else: ?>
<span class="text-gray-400">None</span>
<?php endif; ?>
</td>

<td class="px-6 py-4 text-center">
<form method="post" action="/social-employer/application/status" class="flex justify-center gap-2">

<input type="hidden" name="id" value="<?= (int)($a['application_id'] ?? 0) ?>">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<button name="status" value="accepted"
class="px-4 py-1.5 rounded-md text-xs font-semibold border border-green-600 text-green-600 hover:bg-green-600 hover:text-white transition">
Accept
</button>

<button name="status" value="rejected"
class="px-4 py-1.5 rounded-md text-xs font-semibold border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition">
Reject
</button>

</form>
</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
<?php endif; ?>
</div>

</div>
</body>
</html>
