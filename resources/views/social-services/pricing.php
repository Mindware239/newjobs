<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hire Talent | Mindware Infotech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    .hero-soft {
      background: linear-gradient(
        180deg,
        #f9fafb 0%,
        #f1f5f9 50%,
        #ffffff 100%
      );
      border-bottom: 1px solid #e5e7eb;
    }
  </style>
</head>

<body class="bg-white text-gray-800">

<!-- ================= HEADER (FINAL – ALL DEVICES) ================= -->
<header class="border-b border-gray-200 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">

    <!-- LOGO -->
    <a href="<?php echo $base; ?>" class="flex items-center gap-3 shrink-0">
      <img src="<?php echo $base; ?>uploads/Mindware-infotech.png"
           class="h-10 sm:h-12 w-auto"
           alt="Mindware Infotech">
    </a>

    <!-- DESKTOP NAV -->
    <nav class="hidden md:flex items-center gap-8 text-sm">
      <a href="<?= rtrim($base,'/') ?>/employers" class="font-medium hover:text-[#5b6bd5]">Home</a>
      <a href="<?= rtrim($base,'/') ?>/pricing" class="font-medium hover:text-[#5b6bd5]">Pricing</a>
      <a href="<?= rtrim($base,'/') ?>/aboutus" class="font-medium hover:text-[#5b6bd5]">About us</a>
      <a href="<?= rtrim($base,'/') ?>/supports" class="font-medium hover:text-[#5b6bd5]">Support</a>
      <a href="<?= rtrim($base,'/') ?>/specials" class="font-medium hover:text-[#5b6bd5]">Specials</a>
    </nav>

    <!-- DESKTOP ACTIONS -->
    <div class="hidden md:flex items-center gap-3 text-sm">
      <span class="text-gray-600">Employers:</span>
      <a href="#" class="text-red-500 hover:underline">Login</a>
      <span>/</span>
      <a href="#" class="text-red-500 hover:underline">Create account</a>

      <a href="/social-services"
         class="ml-2 px-4 py-2 rounded-md text-white transition"
         style="background-color:#5b6bd5;"
         onmouseover="this.style.backgroundColor='#4a59c8'"
         onmouseout="this.style.backgroundColor='#5b6bd5'">
       Employers
      </a>
    </div>

    <!-- MOBILE MENU BUTTON -->
    <button class="md:hidden text-gray-700 text-2xl"
            onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
      ☰
    </button>
  </div>

  <!-- MOBILE MENU -->
  <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200 bg-white">
    <div class="px-4 py-4 flex flex-col gap-4 text-sm">

      <a href="<?= rtrim($base,'/') ?>/employers" class="hover:text-[#5b6bd5]">Home</a>
      <a href="<?= rtrim($base,'/') ?>/pricing" class="hover:text-[#5b6bd5]">Pricing</a>
      <a href="<?= rtrim($base,'/') ?>/aboutus" class="hover:text-[#5b6bd5]">About us</a>
      <a href="<?= rtrim($base,'/') ?>/supports" class="hover:text-[#5b6bd5]">Support</a>
      <a href="<?= rtrim($base,'/') ?>/specials" class="hover:text-[#5b6bd5]">Specials</a>

      <hr>

      <a href="#" class="text-red-500">Employer Login</a>
      <a href="#" class="text-red-500">Create account</a>

      <a href="/social-services"
         class="mt-2 inline-block text-center px-4 py-2 rounded-md text-white"
         style="background-color:#5b6bd5;">
        Jobseekers
      </a>
    </div>
  </div>
</header>

<!-- ================= HERO ================= -->
<section class="bg-gradient-to-b from-gray-50 via-gray-100 to-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-16 text-center">
    <h1 class="text-gray-900 text-2xl md:text-3xl font-light">
      Job listings: Great features, big value, unbeatable customer service.
    </h1>
  </div>
</section>

<!-- ================= PRICING CONTENT ================= -->
<section class="bg-white max-w-7xl mx-auto px-6 py-16 text-center">

  <h2 class="text-2xl font-semibold mb-4 text-gray-900">
    Job listing credits
  </h2>

  <p class="text-gray-600 max-w-3xl mx-auto mb-2">
    Two value-rich options to choose from.
    (Or keep scrolling for multi-listing packages, and save even more!)
  </p>

  <p class="text-sm text-gray-700 mb-16">
    <strong>Volunteer or internship positions to fill?</strong>
    Scroll to the bottom of this page for <strong>FREE</strong> options.
  </p>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-16 max-w-4xl mx-auto">

    <div class="text-center">
      <h3 class="text-xl font-medium mb-2 text-gray-900">Standard job listing</h3>
      <div class="text-3xl font-semibold mb-6 text-gray-900">₹8,500</div>
      <a href="<?= $base ?>createjob"
         class="inline-block text-white px-8 py-3 text-sm rounded-sm transition"
         style="background-color:#5b6bd5;"
         onmouseover="this.style.backgroundColor='#4a59c8'"
         onmouseout="this.style.backgroundColor='#5b6bd5'">
        POST A JOB NOW
      </a>
    </div>

    <div class="text-center">
      <h3 class="text-xl font-medium mb-2 text-gray-900">Premium job listing</h3>
      <div class="text-3xl font-semibold mb-6 text-gray-900">₹14,500</div>
      <a href="<?= $base ?>createjob"
         class="inline-block text-white px-8 py-3 text-sm rounded-sm transition"
         style="background-color:#5b6bd5;"
         onmouseover="this.style.backgroundColor='#4a59c8'"
         onmouseout="this.style.backgroundColor='#5b6bd5'">
        POST A JOB NOW
      </a>
    </div>

  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
    <p class="mb-3 md:mb-0">© 2026 Mindware Infotech. All Rights Reserved.</p>
    <div class="flex gap-6">
      <a href="<?= $base ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
      <a href="<?= $base ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
      <a href="<?= $base ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
    </div>
  </div>
</footer>

</body>
</html>
