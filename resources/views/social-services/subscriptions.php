<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job alerts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white min-h-screen flex flex-col text-gray-800">

<!-- ================= TOP HEADER ================= -->
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

    <a href="<?= $base ?>">
      <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12">
    </a>

    <div class="hidden md:flex items-center gap-8 text-sm">
      <a href="<?= $base ?>candidate/listings" class="text-gray-600 hover:text-black">
        Applications & saved listings
      </a>
      <span class="text-blue-600 font-medium">
        Job alerts
      </span>
      <a href="<?= $base ?>candidate/account" class="text-gray-600 hover:text-black">
        Account & profile
      </a>
      <a href="<?= $base ?>logout" class="text-gray-600 hover:text-black">
        Logout
      </a>
    </div>

  </div>
</header>

<!-- ================= BLACK NAV (REFERENCE STYLE) ================= -->
<nav class="bg-black">
  <div class="max-w-7xl mx-auto px-6">
    <ul class="flex items-center gap-8 text-sm text-white py-3">
      <li><a href="<?= $base ?>" class="hover:text-gray-300">← Back to Home</a></li>
      <li><a href="<?= rtrim($base,'/') ?>/find-a-job" class="hover:text-gray-300">Find a job</a></li>
      <li><a href="<?= $base ?>about" class="hover:text-gray-300">About us</a></li>
      <li><a href="<?= $base ?>help" class="hover:text-gray-300">Get Help</a></li>
    </ul>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="flex-1">
  <div class="max-w-7xl mx-auto px-6 py-10">

    <!-- TOP STRIP (LIKE WORKFORGOOD) -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm font-medium">
        Subscribe to job alerts to receive emails when jobs are posted! 🤩
      </p>

      <a href="<?= $base ?>candidate/subscriptions/new"
         class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-md">
        + New subscription
      </a>
    </div>

    <!-- DIVIDER + META -->
    <div class="flex items-center justify-between border-t border-gray-200 pt-4 text-sm text-gray-500">
      <span>0 results of 0 total</span>
      <a href="#" class="text-blue-600 hover:underline">Refresh ↻</a>
    </div>

    <!-- EMPTY STATE (CENTER, SIMPLE) -->
    <div class="flex justify-center items-center min-h-[300px]">
      <p class="text-sm text-gray-500">
        No subscriptions found.
      </p>
    </div>

  </div>
</main>

</body>
</html>
