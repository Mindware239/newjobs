<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Job Alert</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

<!-- ================= TOP HEADER ================= -->
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

    <a href="<?= $base ?>">
      <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12">
    </a>

    <div class="hidden md:flex items-center gap-6 text-sm">
      <a href="<?= $base ?>candidate/listings" class="text-gray-600 hover:text-black">
        Applications & saved listings
      </a>
      <span class="text-blue-600 font-medium">Job alerts</span>
      <a href="<?= $base ?>candidate/account" class="text-gray-600 hover:text-black">
        Account & profile
      </a>
      <a href="<?= $base ?>logout" class="text-gray-600 hover:text-black">
        Logout
      </a>
    </div>

  </div>
</header>

<!-- ================= SECOND NAV ================= -->
<nav class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-3 flex gap-6 text-sm text-gray-600">
    <a href="<?= $base ?>" class="hover:text-black">← Back to Home</a>
    <a href="<?= rtrim($base,'/') ?>/find-a-job" class="hover:text-black">Find a job</a>
    <a href="<?= $base ?>about" class="hover:text-black">About us</a>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="max-w-7xl mx-auto px-6 py-10">

  <!-- HEADER ROW -->
  <div class="flex items-center justify-between mb-6">
    <a href="<?= $base ?>candidate/subscriptions" class="text-sm text-blue-600 hover:underline">
      ← Cancel
    </a>

    <button type="submit" form="alertForm"
      class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-10 py-2.5 rounded-md">
      Submit
    </button>
  </div>

  <!-- FORM CARD -->
  <form id="alertForm" class="bg-gray-100 border border-gray-200 rounded-lg p-6">

    <!-- TOP GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

      <!-- NAME -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Name / subject <span class="text-red-500">*</span>
        </label>
        <p class="text-xs text-gray-500 mb-1">
          Appears in email notifications.
        </p>
        <input type="text"
               class="w-full border rounded-md p-2.5 bg-white"
               placeholder="e.g. Jobs in Delhi"
               required>
      </div>

      <!-- STATUS -->
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <p class="text-xs text-gray-500 mb-2">
          Enable or disable this alert.
        </p>
        <label class="flex items-center gap-3">
          <input type="checkbox" checked class="accent-blue-600">
          <span class="text-sm">Active / Subscribed</span>
        </label>
      </div>

      <!-- EMAIL -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Notification email address
        </label>
        <input type="email"
               class="w-full border rounded-md p-2.5 bg-white"
               value="sales@indianbarcode.com">
      </div>

      <!-- FREQUENCY -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Frequency of alert <span class="text-red-500">*</span>
        </label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Daily</option>
          <option>Weekly</option>
        </select>
      </div>

    </div>

    <!-- FILTERS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <div>
        <label class="block text-sm font-medium mb-1">Role types</label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Select any to include</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Workplace options</label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Select any to include</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Time commitment</label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Part-time</option>
          <option>Full-time</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">
          Minimum experience required (years)
        </label>
        <input type="number"
               class="w-full border rounded-md p-2.5 bg-white"
               value="0">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Pay terms</label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Select any to include</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Impact areas</label>
        <select class="w-full border rounded-md p-2.5 bg-white">
          <option>Select any to include</option>
        </select>
      </div>

    </div>

  </form>

</main>

</body>
</html>
