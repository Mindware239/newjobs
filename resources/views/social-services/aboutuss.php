<?php $base = $base ?? '/'; ?>
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
<header class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-black font-medium mr-2">Candidates & Job Seekers:</span>
                <a href="/social-services/login" class="text-red-400 font-semibold hover:underline">Login/CreateAccount</a>
                
            </div>
            <a href="index" 
               class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white  hover:border-red-500 border-2 hover:text-red-400">
               Jobseekers
            </a>
        </div>

        <button @click="mobileMenu = !mobileMenu" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

  <nav class="hidden min-[900px]:block border-t border-gray-50">
<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">

<?php 
$current_page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
if ($current_page === '') $current_page = 'index';

$navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label' => 'Pricing', 'url' => 'pricing'],
    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutuss'],
    ['label' => 'Support', 'url' => 'supports'],
    ['label' => 'Specials', 'url' => 'specials'],
];


foreach($navItems as $item): 

$isActive = ($current_page === $item['url']);

$class = $isActive
    ? 'text-[#e15f55] border-b-2 border-[#e15f55]'
    : 'text-black border-b-2 border-transparent hover:text-[#e15f55] hover:border-[#e15f55]';
?>

<a href="<?= $base . $item['url']; ?>"
   class="py-4 text-[15px] font-semibold transition-all duration-300 <?= $class ?>">
   <?= $item['label']; ?>
</a>

<?php endforeach; ?>

</div>
</nav>

    <div x-show="mobileMenu" x-cloak x-transition id="mobileNav" class="min-[900px]:hidden bg-gray-50 px-4 border-t">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100 hover:text-[#e15f55]">
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-4 flex flex-col gap-3">
                <a href="candidate" class="w-full py-3 bg-[#5b6bd5] text-white text-center font-bold rounded">JOBSEEKERS</a>
                <div class="text-center text-sm py-2">
                    Employers: <a href="employers" class="text-[#5b6bd5] font-bold">Login</a>
                </div>
            </li>
        </ul>
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
