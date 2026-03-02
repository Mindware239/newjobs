<?php
$companyName = "Mindware Infotech";
$base = $base ?? '/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications & Saved Listings</title>

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

<!-- HEADER -->
<header class="bg-white border-b">
<div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">

<img src="<?= rtrim($base, '/') ?>/uploads/Mindware-infotech.png" class="h-11">

 <nav class="hidden min-[900px]:flex items-center gap-4 lg:gap-8 text-base font-medium">
        <a href="/candidatelisting" class="hover:text-custom-red transition-colors pb-1">Applications & saved listings</a>
        <a href="/social-candidate/candidatesubscriptions" class="hover:text-custom-red transition-colors pb-1">Job alerts</a>
        <a href="<?= rtrim($base,'/') ?>/social-candidate/accountcandidate" class="text-custom-red border-b-2 border-custom-red pb-1">Account & profile</a>
        <a href="/social-services/logout" class="text-black hover:text-custom-red transition-colors pb-1">Logout</a>
    </nav>

</div>
</header>

<!-- BLACK NAV -->
<div class="bg-black text-white text-sm">
<div class="max-w-7xl mx-auto px-6 py-3 flex gap-8">
<a href="<?= rtrim($base,'/') ?>/social-services" class="hover:text-[#5b6bd5]">Back to Home</a>
<a href="<?= rtrim($base,'/') ?>/find-a-job" class="hover:text-[#5b6bd5]">Find a job</a>
<a href="<?= rtrim($base,'/') ?>/searchemployers" class="hover:text-[#5b6bd5]">Search employers</a>
<a href="<?= rtrim($base,'/') ?>/career-insights" class="hover:text-[#5b6bd5]">Career insights</a>
<a href="<?= rtrim($base,'/') ?>/about" class="hover:text-[#5b6bd5]">About us</a>
<a href="<?= rtrim($base,'/') ?>/help" class="hover:text-[#5b6bd5]">Get Help</a>
</div>
</div>

<!-- MAIN -->
<div class="max-w-7xl mx-auto px-6 py-10">

<!-- SEARCH FILTER -->
<div class="bg-gray-200 p-6 rounded grid md:grid-cols-2 gap-6">

<div>
<label class="block text-sm mb-1">Search by keyword</label>
<input type="text" placeholder="Enter a keyword"
class="w-full px-4 py-2 border rounded">
</div>

<div>
<label class="block text-sm mb-1">Filter by organization</label>
<select class="w-full px-4 py-2 border rounded">
<option>Select an organization</option>
</select>
</div>

</div>

<!-- TABS -->
<div class="flex gap-6 mt-8 border-b pb-2 text-sm items-center">

<button class="px-4 py-1 bg-blue-900 text-white rounded">All</button>
<button class="text-gray-400">Applied To</button>
<button class="text-gray-400">Not Yet Applied To</button>

<div class="ml-auto text-gray-500">
<?= count($jobs) ?> results
</div>

</div>

<!-- LISTINGS -->
<div class="mt-8 space-y-6">

<?php if(empty($jobs)): ?>

<div class="text-center text-gray-400 py-16 text-lg">
No applications found
</div>

<?php else: ?>

<?php foreach($jobs as $job): ?>

<div class="bg-white p-6 rounded shadow-sm border">

<div class="flex justify-between">

<div>
<h2 class="font-bold text-lg">
<?= htmlspecialchars($job['role_name']) ?>
</h2>

<p class="text-sm text-gray-500 mt-1">
<?= htmlspecialchars($job['organization_name'] ?? '') ?>
</p>
</div>

<div>

<?php
$status = strtolower($job['application_status']);

$btnColor = match($status){
'accepted' => 'bg-green-500',
'rejected' => 'bg-red-500',
default => 'bg-red-400'
};
?>

<span class="<?= $btnColor ?> text-white px-4 py-2 rounded text-sm">
<?= ucfirst($status) ?>
</span>

</div>

</div>

<?php $desc = strip_tags($job['full_description'] ?? ($job['short_description'] ?? 'No description available')); ?>
<p class="mt-4 text-sm text-gray-600">
<?= htmlspecialchars(substr($desc,0,200)) ?>...
</p>

<a href="/job-details?id=<?= (int)($job['id'] ?? 0) ?>" class="text-red-500 text-sm mt-4 inline-block">
Open listing
</a>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</body>
</html>
