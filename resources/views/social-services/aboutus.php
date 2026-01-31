<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-800">

<!-- ================= HEADER (FINAL – ALL DEVICES) ================= -->
<header class="border-b border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">

        <!-- LOGO -->
        <a href="<?= $base ?>" class="flex items-center gap-3 shrink-0">
            <img src="<?= $base ?>uploads/Mindware-infotech.png"
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

<!-- ================= CONTENT ================= -->
<main class="max-w-7xl mx-auto px-6 py-16">

    <h1 class="text-3xl font-semibold mb-6">
        About us
    </h1>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-10">
        Since our founding in 1999 as Opportunity Knocks, one of the first job boards
        focused exclusively on mission-driven careers, our team at Mindware Infotech
        has helped more than 30,000 organizations find the talent they need.
    </p>

    <h2 class="text-lg font-semibold mb-3">
        Nonprofit Focus and Expertise
    </h2>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-10">
        We’re a mission-focused organization ourselves. Everything we do is designed
        to give back to organizations that create real impact. That’s why we make
        hiring as simple as possible with dependable support, practical tools,
        and insights from industry experts.
    </p>

    <h2 class="text-lg font-semibold mb-3">
        More Talent. More Options.
    </h2>

    <p class="max-w-4xl text-gray-700 leading-relaxed">
        With over 100,000 visitors viewing hundreds of job postings every month,
        Mindware Infotech is the trusted platform for professionals looking to start,
        grow, or transition their careers in purpose-driven organizations. We connect
        employers with candidates who truly believe in the work they do.
    </p>

</main>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
        <p>© 2026 Mindware Infotech. All Rights Reserved.</p>
        <div class="flex gap-6 mt-3 md:mt-0">
            <a href="<?= $base ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
            <a href="<?= $base ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
            <a href="<?= $base ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>
