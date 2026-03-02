<?php $scheme = $_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'); $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; $base = isset($base) && is_string($base) ? $base : ($scheme . '://' . $host . '/'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Job Listings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>

<style>
  body { font-size: 14px; color: #333; }
  .container-wf { max-width: 1200px; }
</style>
</head>

<body class="bg-white min-h-screen flex flex-col">

<!-- ================= TOP WHITE HEADER ================= -->
<header class="border-b">
  <div class="container-wf mx-auto px-6 py-4 flex items-center justify-between">
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

<!-- ================= BLACK NAV ================= -->
<nav class="bg-black text-white">
  <div class="container-wf mx-auto px-6 py-3 flex gap-6 text-sm">
    <a href="/" class="hover:underline">◀ Back to Home</a>
    <a href="/pricing" class="hover:underline">Pricing</a>
    <a href="/blog" class="hover:underline">Hiring insights</a>
    <a href="/aboutus" class="hover:underline">About us</a>
    <a href="/supports" class="hover:underline">Get Help</a>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="flex-1">
  <div class="container-wf mx-auto px-6 py-10">

    <?php if (!empty($_GET['success'])): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md mb-6" role="alert">
      <span class="font-semibold">Success:</span>
      <span class="ml-1"><?= htmlspecialchars($_GET['success']) ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
      <span class="font-semibold">Error:</span>
      <span class="ml-1"><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
    <?php endif; ?>

    <!-- TITLE + BUTTON -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl md:text-2xl font-semibold text-gray-900">Manage your listings for jobs & role openings</h1>
<a href="/social-employer/newlisting"
   class="bg-[#e56254] hover:bg-[#d65244] text-white px-5 py-2 rounded-md text-sm shadow-sm transition flex items-center gap-2">
  ＋ Create a new listing
</a>

    </div>

    <!-- FILTER BAR -->
    <div class="bg-gray-50 p-6 rounded-md grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border border-gray-200">
      <div>
        <label class="block text-xs mb-1 text-gray-600">
          Search by role name or description
        </label>
        <input type="text"
               placeholder="Enter a keyword"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      <div>
        <label class="block text-xs mb-1 text-gray-600">
          Filter by organization
        </label>
        <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option>Select an organization (account or listed by)</option>
        </select>
      </div>
    </div>

    <!-- TABS + RESULTS -->
    <div class="flex justify-between items-center border-b pb-3 text-sm text-gray-600">
      <div class="flex gap-6">
        <span class="bg-[#6b7bd6] text-white px-3 py-1 rounded shadow-sm">All</span>
        <span>Drafts</span>
        <span>Pending</span>
        <span>Scheduled</span>
        <span>Active / Live</span>
        <span>Expired</span>
      </div>

      <div class="flex items-center gap-6">
        <span class="text-gray-700"><?= count($jobs ?? []) ?> results</span>
        <a href="/social-employer/listings" class="text-red-600 hover:text-red-700">Refresh ↻</a>
      </div>
    </div>

    <?php if (empty($jobs)): ?>
    <!-- EMPTY STATE -->
    <div class="text-center py-24 text-gray-600 text-sm">
      No listings found
    </div>
    <?php else: ?>
    <!-- LISTINGS GRID -->
    <div class="grid gap-5 mt-6">
        <?php foreach ($jobs as $job): ?>
        <?php
          $roleName = htmlspecialchars($job['role_name'] ?? 'Untitled Role');
          $orgName = htmlspecialchars($job['organization_name'] ?? 'Unknown Org');
          $location = htmlspecialchars($job['job_location'] ?? 'Remote');
          $payType = htmlspecialchars($job['pay_type'] ?? '');
          $minPay = htmlspecialchars($job['min_pay'] ?? '0');
          $maxPay = htmlspecialchars($job['max_pay'] ?? '0');
          $posted = !empty($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : '';
          $status = strtolower($job['status'] ?? 'draft');
          $statusMap = [
            'draft' => 'bg-gray-100 text-gray-700 border border-gray-200',
            'active' => 'bg-green-100 text-green-700 border border-green-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
            'scheduled' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
            'expired' => 'bg-red-100 text-red-700 border border-red-200'
          ];
          $statusClass = $statusMap[$status] ?? $statusMap['draft'];
        ?>
        <div class="border border-gray-200 rounded-xl p-5 bg-white shadow-sm hover:shadow-md transition">
          <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-lg text-gray-900 truncate"><?= $roleName ?></h3>
                <span class="capitalize text-xs px-2 py-0.5 rounded <?= $statusClass ?>"><?= htmlspecialchars($job['status'] ?? 'draft') ?></span>
              </div>
              <p class="text-sm text-gray-600 font-medium"><?= $orgName ?></p>
              <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4 text-pink-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
                  <?= $location ?>
                </span>
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                  <?= $payType ?>: $<?= $minPay ?> - $<?= $maxPay ?>
                </span>
                <?php if ($posted): ?>
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 00-2 2v13a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm0 15H5V10h14v9z"/></svg>
                  Posted: <?= $posted ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
              <a href="/social-employer/job/<?= (int)$job['id'] ?>/edit" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">Edit</a>
              <form action="/social-employer/job/<?= (int)$job['id'] ?>/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');" class="inline">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="px-3 py-1.5 text-sm rounded-md border border-red-300 text-red-600 hover:bg-red-50">Delete</button>
              </form>
              <?php if (($job['status'] ?? 'draft') !== 'draft'): ?>
              <form action="/social-employer/job/<?= (int)$job['id'] ?>/status" method="POST" class="inline">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="status" value="draft">
                <button type="submit" class="px-3 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Move to Draft</button>
              </form>
              <?php else: ?>
              <form action="/social-employer/job/<?= (int)$job['id'] ?>/status" method="POST" class="inline">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="status" value="active">
                <button type="submit" class="px-3 py-1.5 text-sm rounded-md bg-green-600 text-white hover:bg-green-700">Publish</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</main>

</body>
</html>
