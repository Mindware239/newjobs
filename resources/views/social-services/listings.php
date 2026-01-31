<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applications & Saved Listings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col text-gray-800">

<!-- ================= WHITE HEADER ================= -->
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="<?= $base ?>">
      <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12">
    </a>

    <div class="hidden md:flex items-center gap-6 text-sm text-gray-600">
      <span class="text-[#5b6bd5] font-medium">
        Applications & saved listings
      </span>
      <a href="#" class="hover:text-[#5b6bd5]">Job alerts</a>
      <a href="#" class="hover:text-[#5b6bd5]">Account & profile</a>
      <a href="#" class="hover:text-[#5b6bd5]">Logout</a>
    </div>
  </div>
</header>

<!-- ================= SECONDARY NAV ================= -->
<nav class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6">
    <ul class="flex items-center gap-8 text-sm text-gray-600 py-3">
      <li>
        <a href="<?= rtrim($base,'/') ?>/social-services"
           class="hover:text-[#5b6bd5]">
          ← Back to Home
        </a>
      </li>
      <li>
        <a href="<?= rtrim($base,'/') ?>/find-a-job"
           class="hover:text-[#5b6bd5]">
          Find a job
        </a>
      </li>
      <li>
        <a href="<?= rtrim($base,'/') ?>/about"
           class="hover:text-[#5b6bd5]">
          About us
        </a>
      </li>
    </ul>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="flex-1 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6 py-10" x-data="listingPage()">

    <!-- FILTER BAR -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

      <div>
        <label class="block text-sm font-medium mb-1">
          Search by keyword
        </label>
        <input type="text"
               x-model="keyword"
               placeholder="Enter a keyword"
               class="w-full border border-gray-300 rounded-md p-2.5
                      focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">
          Filter by organization
        </label>
        <select x-model="organization"
                class="w-full border border-gray-300 rounded-md p-2.5
                       focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
          <option value="">Select an organization</option>
          <option>NGO One</option>
          <option>NGO Two</option>
        </select>
      </div>

    </div>

    <!-- TABS -->
    <div class="flex items-center justify-between mt-6 border-b border-gray-200 pb-3">

      <div class="flex gap-3">
        <button @click="tab='all'"
                :class="tab==='all'
                  ? 'bg-[#5b6bd5] text-white'
                  : 'bg-gray-100 text-gray-600'"
                class="px-5 py-2 rounded-md text-sm transition">
          All
        </button>

        <button @click="tab='applied'"
                :class="tab==='applied'
                  ? 'bg-[#5b6bd5] text-white'
                  : 'bg-gray-100 text-gray-600'"
                class="px-5 py-2 rounded-md text-sm transition">
          Applied To
        </button>

        <button @click="tab='not_applied'"
                :class="tab==='not_applied'
                  ? 'bg-[#5b6bd5] text-white'
                  : 'bg-gray-100 text-gray-600'"
                class="px-5 py-2 rounded-md text-sm transition">
          Not Yet Applied To
        </button>
      </div>

      <div class="flex items-center gap-6 text-sm text-gray-500">
        <span>0 results of 0 total</span>
        <a href="#"
           class="text-[#5b6bd5] hover:underline flex items-center gap-1">
          Refresh ↻
        </a>
      </div>

    </div>

    <!-- EMPTY STATE -->
    <div class="text-center py-20 text-gray-500 text-sm">
      No saved listings found.
    </div>

  </div>
</main>

<!-- ================= ALPINE ================= -->
<script>
function listingPage() {
  return {
    tab: 'all',
    keyword: '',
    organization: ''
  }
}
</script>

</body>
</html>
