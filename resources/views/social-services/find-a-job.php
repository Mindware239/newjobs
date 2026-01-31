<?php
$base = $base ?? '/';

$job = [
    'title' => 'Chief Program Officer',
    'company' => 'Rising Sun Center for Opportunity',
    'posted' => 'January 9, 2026',
    'expires' => 'February 9, 2026',
    'description' =>
        'Rising Sun Center for Opportunity seeks a strategic, equity-centered, and emotionally intelligent Chief Program Officer (CPO) to lead and evolve workforce development programs at the intersection of climate justice, economic mobility, racial equity.',
    'location' => '1116 36th St., Oakland, CA United States',
    'type' => 'Full-time',
    'workplace' => 'Hybrid',
    'salary_min' => '$153,000.00',
    'salary_max' => '$193,000.00',
    'mission' => 'Community Improvement & Capacity Building',
    'role' => 'Executive / Senior Management',
    'logo' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find a Job | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        body {
            background: #f9fafb; /* gray-50 */
            font-family: Arial, Helvetica, sans-serif;
        }

        .job-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .job-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="text-gray-800">

<!-- ================= HEADER ================= -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <a href="<?= $base ?>" class="flex items-center gap-3">
            <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-8">
            <a href="<?= rtrim($base,'/') ?>/social-services" class="hover:text-black">Home</a>
            <a href="<?= rtrim($base,'/') ?>/find-a-job" class="font-medium hover:text-[#5b6bd5]">Find a Job</a>
            <a href="<?= rtrim($base,'/') ?>/createjob" class="hover:text-black">Create Job</a>
            <a href="<?= $base ?>about" class="hover:text-black">About Us</a>
            <a href="<?= $base ?>contact" class="hover:text-black">Contact Us</a>
        </nav>
 <div class="flex gap-4">
            <a href="/login"
               class="px-4 py-2 border rounded-md transition"
               style="border-color:#5b6bd5; color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#5b6bd51a'"
               onmouseout="this.style.backgroundColor='transparent'">
                Employer Login
            </a>
  </div>
    </div>
</header>

<!-- ================= PAGE TITLE ================= -->
<div class="max-w-7xl mx-auto px-6 mt-10">
    <h1 class="text-3xl font-bold text-gray-900">Find a job</h1>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="job-container">

    <!-- LEFT FILTERS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200">
        <p class="text-sm mb-4 text-gray-600">
            Search job postings by using one or more of the filters below.
        </p>

        <label class="text-sm block mb-1">Keywords</label>
        <input class="w-full p-2 mb-4 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#5b6bd5]"
               placeholder="e.g. development, remote">

        <label class="text-sm block mb-1">State</label>
        <select class="w-full p-2 mb-4 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#5b6bd5]">
            <option>Select a state</option>
        </select>

        <label class="text-sm block mb-1">City or zip code</label>
        <input class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#5b6bd5]"
               placeholder="Enter city or zip">
    </div>

    <!-- RIGHT JOB CARD -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 relative">

        <span class="absolute top-0 right-0 bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-bl-xl">
            Featured
        </span>

        <div class="flex justify-between gap-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900"><?= $job['title']; ?></h2>
                <p class="text-gray-600 mt-1"><?= $job['company']; ?></p>
            </div>

            <div class="bg-gray-50 w-32 h-20 flex items-center justify-center rounded-lg border border-gray-200">
                <img src="<?= $job['logo']; ?>" class="max-h-14 object-contain">
            </div>
        </div>

        <div class="text-red-600 text-sm mt-4">
            Posted: <?= $job['posted']; ?><br>
            Expires: <?= $job['expires']; ?>
        </div>

        <p class="mt-4 text-sm leading-relaxed text-gray-700">
            <?= $job['description']; ?>
        </p>

        <ul class="mt-4 text-sm list-disc pl-5 space-y-1 text-gray-700">
            <li><?= $job['location']; ?></li>
            <li><?= $job['type']; ?></li>
            <li><?= $job['workplace']; ?></li>
            <li><?= $job['salary_min']; ?> (salary min)</li>
            <li><?= $job['salary_max']; ?> (salary max)</li>
            <li>Mission focus: <?= $job['mission']; ?></li>
            <li>Role category: <?= $job['role']; ?></li>
        </ul>

    </div>

</div>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200 mt-20">
    <div class="max-w-7xl mx-auto px-6 py-8 text-sm text-gray-600 flex justify-between">
        <p>© <?= date('Y'); ?> Mindware Infotech. All Rights Reserved.</p>
        <div class="flex gap-4">
            <a href="<?= $base ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
            <a href="<?= $base ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
            <a href="<?= $base ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>
