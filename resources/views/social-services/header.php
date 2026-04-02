<?php $base = $base ?? '/'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <a href="employers"
               class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white  hover:border-red-500 border-2 hover:text-red-400">
                Employers
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
    if ($current_page === '') {
    $current_page = 'index';
    }

    $navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label' => 'Find a job', 'url' => 'find-a-job'],
    ['label' => 'Create job alerts', 'url' => 'social-services/login'],
    ['label' => 'Search Employers', 'url' => 'searchEmployers'],
    ['label' => 'Career Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutus'],
    ['label' => 'Support', 'url' => 'supports'],
    ];

    foreach ($navItems as $item):

    $isActive = ($current_page === $item['url']);

    $class = $isActive
        ? 'text-[#e15f55] border-b-2 border-[#e15f55]'
        : 'text-black border-b-2 border-transparent hover:text-[#e15f55] hover:border-[#e15f55]';
?>

<a href="<?php echo $base . $item['url']; ?>"
   class="py-4 text-[15px] font-semibold transition-all duration-300 <?php echo $class ?>">
   <?php echo $item['label']; ?>
</a>

<?php endforeach; ?>

</div>
</nav>

    <div x-show="mobileMenu" x-cloak x-transition id="mobileNav" class="min-[900px]:hidden bg-gray-50 px-4 border-t">
        <ul class="py-4 space-y-1">
            <?php foreach ($navItems as $item): ?>
            <li>
                <a href="<?php echo $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100 hover:text-[#e15f55]">
                    <?php echo $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-4 flex flex-col gap-3">
                <a href="candidate" class="w-full py-3 bg-[#5b6bd5] text-white text-center font-bold rounded">JOBSEEKERS</a>
                <div class="text-center text-sm py-2">
                    Employers: <a href="employers" class="text-[#5b6bd5] font-bold">Login/CreateAccount</a>
                </div>
            </li>
        </ul>
    </div>
</header>
