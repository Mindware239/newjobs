<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Specials | Mindware Infotech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    .hero-soft {
      background: linear-gradient(180deg,#f9fafb 0%,#f1f5f9 50%,#ffffff 100%);
      border-bottom: 1px solid #e5e7eb;
    }
  </style>
</head>

<body class="bg-white text-gray-800">
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

    <!-- LOGO (ALWAYS VISIBLE) -->
    <a href="<?= $base ?>" class="flex items-center gap-2">
      <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-10 md:h-12 w-auto">
    </a>

    <!-- DESKTOP NAV -->
    <nav class="hidden lg:flex items-center gap-8 text-sm">
      <a href="<?= rtrim($base,'/') ?>/employers" class="font-medium hover:text-[#5b6bd5]">Home</a>
      <a href="<?= rtrim($base,'/') ?>/pricing" class="font-medium hover:text-[#5b6bd5]">Pricing</a>
      <a href="<?= rtrim($base,'/') ?>/aboutus" class="font-medium hover:text-[#5b6bd5]">About us</a>
      <a href="<?= rtrim($base,'/') ?>/supports" class="font-medium hover:text-[#5b6bd5]">Support</a>
      <a href="<?= rtrim($base,'/') ?>/specials" class="font-medium hover:text-[#5b6bd5]">Specials</a>
    </nav>

    <!-- DESKTOP RIGHT ACTIONS -->
    <div class="hidden lg:flex items-center gap-3 text-sm">
      <span class="text-gray-600">Employers:</span>
      <a href="#" class="text-red-500 hover:underline">Login</a>
      <span>/</span>
      <a href="#" class="text-red-500 hover:underline">Create account</a>

      <a href="/social-services"
         class="ml-3 px-4 py-2 rounded-md text-white transition"
         style="background-color:#5b6bd5;">
       Employers
      </a>
    </div>

    <!-- MOBILE BUTTON (ALWAYS VISIBLE) -->
    <button id="menuBtn" class="lg:hidden text-2xl text-gray-700">
      ☰
    </button>
  </div>

  <!-- MOBILE MENU -->
  <div id="mobileMenu" class="hidden lg:hidden border-t border-gray-200 bg-white">
    <div class="px-4 py-4 space-y-4 text-sm">

      <!-- NAV LINKS -->
      <a href="<?= rtrim($base,'/') ?>/employers" class="block">Home</a>
      <a href="<?= rtrim($base,'/') ?>/pricing" class="block">Pricing</a>
      <a href="<?= rtrim($base,'/') ?>/aboutus" class="block">About us</a>
      <a href="<?= rtrim($base,'/') ?>/supports" class="block">Support</a>
      <a href="<?= rtrim($base,'/') ?>/specials" class="block">Specials</a>

      <hr>

      <!-- EMPLOYER ACTIONS -->
      <div class="space-y-2">
        <a href="#" class="block text-red-500">Login</a>
        <a href="#" class="block text-red-500">Create account</a>

        <a href="/social-services"
           class="inline-block mt-2 px-4 py-2 rounded-md text-white"
           style="background-color:#5b6bd5;">
        Employers
        </a>
      </div>

    </div>
  </div>
</header>


<!-- ================= HERO ================= -->
<section class="hero-soft">
  <div class="max-w-7xl mx-auto px-6 py-16 text-center">
    <h1 class="text-2xl md:text-3xl font-light text-gray-900">
      Save even more with our promotions and specials.
    </h1>
  </div>
</section>

<!-- ================= CONTENT ================= -->
<main class="max-w-4xl mx-auto px-6 py-16">

  <h2 class="text-lg font-semibold text-red-500 mb-3">
    New to Mindware Infotech?
  </h2>

  <p class="mb-10 text-gray-700 leading-relaxed">
    After you’ve created your employer account,
    <span class="text-red-500">email us</span> to let us know you are a new customer
    and we will send you a promo code for a discount on your first purchase!
    <br>
    <span class="text-sm text-gray-600">
      (Note: This cannot be combined with any other offer.)
    </span>
  </p>

  <hr class="my-12">

  <h2 class="text-lg font-semibold text-red-500 mb-6">
    Partner discount
  </h2>

  <img src="https://gcn.org/wp-content/uploads/2019/04/GCN-logo.png"
       class="h-20 mb-6" alt="GCN">

  <p class="mb-3 text-gray-700">
    <span class="text-red-500 font-medium">Georgia Center for Nonprofits</span>
    members enjoy <strong>15% off</strong> all postings and packages.
  </p>

  <p class="mb-3 text-gray-700">
    Remember: Posting credits <strong>never expire</strong>.
  </p>

  <p class="text-gray-700 mb-12">
    For more information, contact Senior Manager Chelle Shell at
    <a href="mailto:chelle@workforgood.org" class="text-red-500 hover:underline">
      chelle@workforgood.org
    </a>
  </p>

  <hr class="my-12">

  <h2 class="text-lg font-semibold text-red-500 mb-3">
    Partnership opportunities
  </h2>

  <p class="text-gray-700 leading-relaxed">
    Would your organization like to partner with Mindware Infotech to offer
    discounts to your constituents? Email us at
    <a href="mailto:hello@mindwareinfotech.com" class="text-red-500 hover:underline">
      hello@mindwareinfotech.com
    </a>.
  </p>

</main>

<!-- ================= HELP STRIP ================= -->
<section class="bg-gray-100 py-6">
  <div class="max-w-7xl mx-auto px-6 text-center text-sm">
    Need help? Email
    <a href="mailto:hello@mindwareinfotech.com" class="text-red-500 hover:underline">
      hello@mindwareinfotech.com
    </a>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
    <p>© 2026 Mindware Infotech. All Rights Reserved.</p>
    <div class="flex gap-6 mt-3 md:mt-0">
      <a href="<?= $base ?>terms">Terms</a>
      <a href="<?= $base ?>privacy">Privacy</a>
      <a href="<?= $base ?>contact">Contact</a>
    </div>
  </div>
</footer>

<script>
  document.getElementById('menuBtn').addEventListener('click', function () {
    document.getElementById('mobileMenu').classList.toggle('hidden');
  });
</script>

</body>
</html>
