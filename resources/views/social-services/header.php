<?php $base = $base ?? '/'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<header class="w-full sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="h-16 md:h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="<?php echo $base; ?>" class="flex items-center gap-3">
                    <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-8 sm:h-10 md:h-12 w-auto">
                </a>
            </div>
            <div class="hidden min-[900px]:flex items-center gap-6">
                <div class="text-[14px] text-slate-700">
                    <span class="mr-2">Candidates & Job Seekers:</span>
                    <a href="/social-services/login" class="text-[#e15f55] font-bold hover:underline">Login/CreateAccount</a>
                </div>
                <a href="employers" class="rounded-xl px-5 py-2.5 text-sm font-bold bg-[#e15f55] text-white hover:bg-white hover:text-[#e15f55] border border-[#e15f55] transition">
                    EMPLOYERS
                </a>
            </div>
            <button @click="mobileMenu = !mobileMenu" class="min-[900px]:hidden p-2 rounded-md border border-slate-300 text-slate-700">
                <i class="fa-solid fa-bars" x-show="!mobileMenu"></i>
                <i class="fa-solid fa-xmark" x-show="mobileMenu" x-cloak></i>
            </button>
        </div>
        <nav class="hidden min-[900px]:block">
            <div class="flex items-center justify-between bg-slate-100 border border-slate-200 rounded-2xl px-4">
                <?php
                    $current_page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
                    if ($current_page === '') { $current_page = 'index'; }
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
                            ? 'text-[#e15f55] font-bold'
                            : 'text-slate-800 hover:text-[#e15f55]';
                ?>
                    <a href="<?php echo $base . $item['url']; ?>" class="relative py-3 px-3 text-[15px] transition <?php echo $class; ?>">
                        <?php echo $item['label']; ?>
                        <?php if ($isActive): ?>
                            <span class="absolute left-3 right-3 -bottom-2 h-0.5 bg-[#e15f55] rounded"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
    </div>
    <div x-show="mobileMenu" x-cloak x-transition class="min-[900px]:hidden border-t border-slate-200 bg-white">
        <div class="px-4 py-3">
            <div class="grid grid-cols-1 gap-1">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?php echo $item['url']; ?>" class="px-3 py-3 rounded-lg text-[15px] font-semibold text-slate-800 hover:bg-slate-100">
                        <?php echo $item['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <a href="candidate" class="flex-1 mr-2 rounded-xl px-4 py-3 bg-[#5b6bd5] text-white text-center font-bold">JOBSEEKERS</a>
                <a href="employers" class="flex-1 ml-2 rounded-xl px-4 py-3 border border-[#e15f55] text-[#e15f55] text-center font-bold">EMPLOYERS</a>
            </div>
        </div>
    </div>
</header>
