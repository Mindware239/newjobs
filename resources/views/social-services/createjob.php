<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Candidate Login | Mindware Infotech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine -->
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col text-gray-800">
<!-- ================= HEADER ================= -->
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center">

    <!-- LEFT : LOGO -->
    <div class="flex-1">
      <a href="<?= $base ?>" class="flex items-center gap-3">
        <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12 w-auto">
      </a>
    </div>

    <!-- CENTER : NAVIGATION -->
    <nav class="hidden md:flex items-center gap-8 justify-center">
      <a href="<?= rtrim($base,'/') ?>/social-services"
         class="font-medium hover:text-[#5b6bd5]">
        Home
      </a>
      <a href="<?= rtrim($base,'/') ?>/find-a-job"
         class="font-medium hover:text-[#5b6bd5]">
        Find a Job
      </a>
      <a href="<?= rtrim($base,'/') ?>/createjob"
         class="font-medium hover:text-[#5b6bd5]">
        Create Job
      </a>
      <a href="<?= $base ?>about"
         class="font-medium hover:text-[#5b6bd5]">
        About Us
      </a>
      <a href="<?= $base ?>contact"
         class="font-medium hover:text-[#5b6bd5]">
        Contact Us
      </a>
    </nav>

    <!-- RIGHT : EMPTY (BALANCER) -->
    <div class="flex-1"></div>
  <div class="flex gap-4">
            <a href="/login"
               class="px-4 py-2 border rounded-md transition"
               style="border-color:#5b6bd5; color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#5b6bd51a'"
               onmouseout="this.style.backgroundColor='transparent'">
                Employer Login
            </a>
  </div>
</header>

<!-- ================= MAIN ================= -->
<main class="flex-1 flex items-center justify-center px-4">

  <div class="w-full max-w-3xl" x-data="{ mode: 'login' }">

    <!-- ================= LOGIN VIEW ================= -->
    <div x-show="mode === 'login'"
         x-transition
         class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">

      <label class="block text-sm font-medium mb-2">
        Your email <span class="text-red-500">*</span>
      </label>

      <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
          <input type="email"
                 placeholder="Enter email address"
                 class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-md
                        focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
        </div>

        <button
          class="w-full sm:w-auto bg-[#5b6bd5] hover:bg-[#4a59c8]
                 text-white px-6 py-2.5 rounded-md transition">
          Send me a login code
        </button>
      </div>

      <div class="flex flex-col sm:flex-row sm:justify-between gap-3 text-sm mt-4">
        <button @click="mode='register'"
                class="text-[#5b6bd5] hover:underline text-left">
          Create account
        </button>

        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
          <a href="#" class="text-[#5b6bd5] hover:underline">I already have a code</a>
          <a href="#" class="text-[#5b6bd5] hover:underline">Use password</a>
        </div>
      </div>

    </div>

    <!-- ================= REGISTER VIEW ================= -->
    <div x-show="mode === 'register'"
         x-transition
         class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
          <label class="block text-sm font-medium">
            Your email address <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500 mb-1">
            You will need to verify your email address.
          </p>
          <input type="email"
                 placeholder="Enter an email address"
                 class="w-full border border-gray-300 rounded-md p-2.5
                        focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
        </div>

        <div>
          <label class="block text-sm font-medium">
            Create a password <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500 mb-1">
            Min. 8 characters with uppercase, lowercase, number & symbol.
          </p>
          <input type="password"
                 placeholder="********"
                 class="w-full border border-gray-300 rounded-md p-2.5
                        focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
        </div>

      </div>

      <button
        class="mt-6 w-full bg-[#5b6bd5] hover:bg-[#4a59c8]
               text-white py-2.5 rounded-md transition">
        Create account
      </button>

      <div class="mt-4 text-sm">
        <button @click="mode='login'"
                class="text-[#5b6bd5] hover:underline">
          Login
        </button>
      </div>

    </div>

  </div>

</main>

</body>
</html>
