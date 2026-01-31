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
<!-- ================= HEADER ================= -->
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
      <a href="<?= rtrim($base,'/') ?>/social-services" class="font-medium hover:text-[#5b6bd5]">Home</a>
      <a href="<?= rtrim($base,'/') ?>/pricing" class="font-medium hover:text-[#5b6bd5]">Pricing</a>
      <a href="<?= rtrim($base,'/') ?>/aboutus" class="font-medium hover:text-[#5b6bd5]">About us</a>
      <a href="<?= rtrim($base,'/') ?>/supports" class="font-medium hover:text-[#5b6bd5]">Support</a>
      <a href="<?= rtrim($base,'/') ?>/specials" class="font-medium hover:text-[#5b6bd5]">Specials</a>
    </nav>

    <!-- RIGHT ACTION -->
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

    <!-- MOBILE BUTTON -->
    <button class="md:hidden text-gray-700"
            onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
      ☰
    </button>

  </div>

  <!-- MOBILE MENU -->
  <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200 bg-white">
    <div class="px-4 py-4 flex flex-col gap-4 text-sm">

      <a href="<?= rtrim($base,'/') ?>/social-services" class="hover:text-[#5b6bd5]">Home</a>
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
        Employers
      </a>

    </div>
  </div>
</header>

<!-- ================= BLACK HERO STRIP ================= -->
<!-- ================= HERO (FIXED – NO BLACK) ================= -->
<section class="hero-soft">
  <div class="max-w-7xl mx-auto px-6 py-20 text-center">
    <h1 class="text-3xl md:text-4xl font-light text-gray-900">
      Find top talent to drive your mission.
    </h1>
  </div>
</section>

<!-- ================= CONTENT ================= -->
<section class="max-w-7xl mx-auto px-6 py-16">

  <p class="max-w-4xl text-gray-700 leading-relaxed">
    We understand the needs of mission-driven organizations.
    You want diverse, qualified talent passionate about making a difference,
    and not just a paycheck — and finding them needs to be simple,
    affordable, and effective.
  </p>

  <p class="mt-10 text-lg font-medium">
    Let <strong>Mindware Jobs</strong> work for you.
    <a  href="<?= rtrim($base,'/') ?>/pricing"  class="text-red-500 hover:underline">
      Post a job now!
    </a>
  </p>

</section>
<!-- ================= EMPLOYER BENEFITS ================= -->
<section class="max-w-7xl mx-auto px-6 pb-20">

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <!-- CARD 1 -->
    <div class="border-8 border-blue-700 p-8">
      <h3 class="text-xl font-semibold mb-6">
        Reach a diverse network of purpose-driven professionals.
      </h3>

      <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
        <li>Hundreds of thousands of registered professionals.</li>
        <li>
          Over 80% of active resumes have 4+ years experience
          working in the nonprofit sector.
        </li>
      </ul>
    </div>

    <!-- CARD 2 -->
    <div class="border-8 border-black p-8 relative">
      <h3 class="text-xl font-semibold mb-6">
        Save money with one or more of our affordable listing options.
      </h3>

      <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
        <li>$105 job listings. <a href="#" class="text-red-500 underline">Buy now!</a></li>
        <li>Save big with a multi-listing package.</li>
        <li>Stand out and maximize results with Premium listings.</li>
        <li>$30 internships and FREE volunteer listing.</li>
      </ul>

      <a
         class="block text-center text-red-500 font-medium mt-8">
        LEARN MORE
      </a>
    </div>

    <!-- CARD 3 -->
    <div class="border-8 border-teal-300 p-8">
      <h3 class="text-xl font-semibold mb-6">
        Stay up-to-date on all of the most recent hiring headlines.
      </h3>

      <ul class="list-disc pl-5 space-y-3 text-sm text-gray-700">
        <li>
          <a href="#" class="text-red-500 underline">
            Subscribe to Hiring Insight
          </a>,
          our monthly thought-leadership series.
        </li>
        <li>
          Read our latest Hiring Insight article,
          <a href="#" class="text-red-500 underline">
            Effective job postings: 4 DOs and 4 DON’Ts
          </a>
        </li>
      </ul>
    </div>

  </div>

</section>
<!-- ================= TRUSTED BY + TESTIMONIAL ================= -->
<section class="max-w-7xl mx-auto px-6 pb-24">

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">

    <!-- LEFT: LOGOS -->
    <div class="lg:col-span-2">
      <h2 class="text-xl font-medium mb-6">
        Post a job with us today, you’ll be in good company.
      </h2>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

        <!-- Logo box -->
        <div class="border p-4 flex items-center justify-center">
          <img src="https://images.unsplash.com/photo-1516383740770-fbcc5ccbece0?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8N3x8Y29tcGFueSUyMHdhbGxwYXBlcnxlbnwwfHwwfHx8MA%3D%3D" class="max-h-12" alt="CARE">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://images.unsplash.com/photo-1748655873477-07caa3f88660?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDA5fHxuZ28lMjBjb21wYW55JTIwbG9nb3xlbnwwfHwwfHx8MA%3D%3D" class="max-h-12" alt="Parkinsons Foundation">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://images.unsplash.com/photo-1766999222149-4bc1cbf591a8?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTZ8fG5nbyUyMGNvbXBhbnklMjBsb2dvfGVufDB8fDB8fHww" class="max-h-12" alt="United Way">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://media.istockphoto.com/id/179016919/photo/company-logo.webp?a=1&b=1&s=612x612&w=0&k=20&c=e5r_xD30rcbM7wTEO5kK8uGWXEGNXZu9BYQvHVW9AME=" class="max-h-12" alt="Salvation Army">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://media.istockphoto.com/id/2201256735/photo/vat-paper-text-on-yellow-background.webp?a=1&b=1&s=612x612&w=0&k=20&c=SyskSHc14HqfPbEJ8XkeRtAzUTLXBBCyNpdb4w4afPY=" class="max-h-12" alt="American Cancer Society">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://images.unsplash.com/photo-1748959504388-9eb3143984e6?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTAzfHxuZ28lMjBjb21wYW55JTIwbG9nb3xlbnwwfHwwfHx8MA%3D%3D" class="max-h-12" alt="American Red Cross">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://plus.unsplash.com/premium_photo-1760340866038-604d67a83d20?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTU4fHxuZ28lMjBjb21wYW55JTIwbG9nb3xlbnwwfHwwfHx8MA%3D%3D" class="max-h-12" alt="Cystic Fibrosis Foundation">
        </div>

        <div class="border p-4 flex items-center justify-center">
          <img src="https://images.unsplash.com/photo-1656234948440-1e9bdcdc8fa6?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MzgzfHxuZ28lMjBjb21wYW55JTIwbG9nb3xlbnwwfHwwfHx8MA%3D%3D" class="max-h-12" alt="Girl Scouts">
        </div>

      </div>
    </div>

    <!-- RIGHT: TESTIMONIAL -->
    <div class="bg-gray-100 p-6 text-sm leading-relaxed">
      <h3 class="text-lg font-medium mb-4">Testimonials</h3>

      <p class="italic mb-4">
        “My days are jam-packed and my need for qualified employees is immense.
        The value-added service provided by your team is greatly appreciated.”
      </p>

      <p class="font-medium">Jinger Robins</p>
      <p class="text-gray-600">
        Executive Director<br>
        SafePath Children’s Advocacy Center
      </p>
    </div>

  </div>

</section>

<!-- ================= HELP STRIP ================= -->
<section class="bg-gray-100 py-6">
  <div class="max-w-7xl mx-auto px-6 text-center text-sm">
    <span class="text-gray-800">
      Need help? Email
      <a href="mailto:hello@mindwareinfotech.com" class="text-red-500 hover:underline">
        hello@mindwareinfotech.com
      </a>
    </span>
  </div>
</section>
<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">

        <!-- Left -->
        <p class="mb-3 md:mb-0">
            © 2026 Mindware Infotech. All Rights Reserved.
        </p>

        <!-- Right -->
        <div class="flex gap-6">
            <a href="<?= $base ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
            <a href="<?= $base ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
            <a href="<?= $base ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
        </div>

    </div>
</footer>


</body>
</html>
