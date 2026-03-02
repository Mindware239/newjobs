<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Job listings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>

<style>
  body {
    font-size: 14px;
    color: #333;
  }
  .container-wf {
    max-width: 1200px;
  }
</style>
</head>

<body class="bg-white min-h-screen flex flex-col">

<!-- ================= TOP WHITE HEADER ================= -->
<header class="border-b">
  <div class="container-wf mx-auto px-6 py-4 flex items-center justify-between">
    <div class="text-3xl font-serif font-bold">
      Mindware Infotech
    </div>

    <nav class="flex items-center gap-6 text-sm text-gray-700">
      <a class="flex items-center gap-1">＋ Post a job</a>
      <a class="text-red-500 font-medium">Job listings</a>
      <a>Applications</a>
      <a>Organizations & users</a>
      <a>Account & profile</a>
      <a>Logout</a>
    </nav>
  </div>
</header>

<!-- ================= BLACK NAV ================= -->
<nav class="bg-black text-white">
  <div class="container-wf mx-auto px-6 py-3 flex gap-6 text-sm">
    <a>◀ Back to Home</a>
    <a>Pricing</a>
    <a>Hiring insights</a>
    <a>About us</a>
    <a>Get Help</a>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="flex-1">
  <div class="container-wf mx-auto px-6 py-10">

    <?php if (!empty($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 relative" role="alert">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline"><?= htmlspecialchars($_GET['success']) ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 relative" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
    <?php endif; ?>

    <!-- TITLE + BUTTON -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="font-semibold">
        Manage your listings for jobs & role openings. 🤓
      </h1>
<a href="/newlisting"
   class="bg-[#e56254] text-white px-5 py-2 rounded-md text-sm flex items-center gap-2">
  ＋ Create a new listing
</a>

    </div>

    <!-- FILTER BAR -->
    <div class="bg-gray-100 p-6 rounded-md grid grid-cols-2 gap-6 mb-6">
      <div>
        <label class="block text-xs mb-1 text-gray-600">
          Search by role name or description
        </label>
        <input type="text"
               placeholder="Enter a keyword"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs mb-1 text-gray-600">
          Filter by organization
        </label>
        <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-400">
          <option>Select an organization (account or listed by)</option>
        </select>
      </div>
    </div>

    <!-- TABS + RESULTS -->
    <div class="flex justify-between items-center border-b pb-3 text-sm text-gray-500">
      <div class="flex gap-6">
        <span class="bg-[#6b7bd6] text-white px-3 py-1 rounded">All</span>
        <span>Drafts</span>
        <span>Pending</span>
        <span>Scheduled</span>
        <span>Active / Live</span>
        <span>Expired</span>
      </div>

      <div class="flex items-center gap-6">
        <span><?= count($jobs ?? []) ?> results</span>
        <a href="/employer/social-jobs" class="text-red-500 cursor-pointer">Refresh ↻</a>
      </div>
    </div>

    <?php if (empty($jobs)): ?>
    <!-- EMPTY STATE -->
    <div class="text-center py-24 text-gray-500 text-sm">
      No listings found
    </div>
    <?php else: ?>
    <!-- LISTINGS GRID -->
    <div class="grid gap-4 mt-6">
        <?php foreach ($jobs as $job): ?>
        <div class="border rounded-lg p-4 flex justify-between items-start bg-white hover:shadow-md transition">
            <div>
                <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($job['role_name'] ?? 'Untitled Role') ?></h3>
                <p class="text-sm text-gray-600 font-medium"><?= htmlspecialchars($job['organization_name'] ?? 'Unknown Org') ?></p>
                <div class="text-xs text-gray-500 mt-2 flex gap-4">
                    <span>📍 <?= htmlspecialchars($job['job_location'] ?? 'Remote') ?></span>
                    <span>💰 <?= htmlspecialchars($job['pay_type'] ?? '') ?>: $<?= htmlspecialchars($job['min_pay'] ?? '0') ?> - $<?= htmlspecialchars($job['max_pay'] ?? '0') ?></span>
                    <span>📅 Posted: <?= date('M d, Y', strtotime($job['created_at'])) ?></span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Status: <span class="capitalize px-2 py-0.5 rounded bg-gray-100 border"><?= htmlspecialchars($job['status'] ?? 'draft') ?></span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="/employer/social-jobs/<?= $job['id'] ?>/edit" class="text-blue-600 hover:underline text-sm">Edit</a>
                <form action="/employer/social-jobs/<?= $job['id'] ?>/delete" method="POST" onsubmit="return confirm('Are you sure?');">
                    <button type="submit" class="text-red-500 hover:underline text-sm">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</main>

</body>
</html>
