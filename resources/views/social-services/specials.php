<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Specials | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .hero-soft {
            background: linear-gradient(180deg,#f9fafb 0%,#f1f5f9 50%,#ffffff 100%);
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>

<body class="bg-white text-[#54595f]" x-data="{ mobileOpen: false }">
<header class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-gray-400 font-medium mr-2">Employers:</span>
                <a href="employers" class="text-red-500 font-semibold hover:underline">Login/CreateAccount</a>
            </div>
            <a href="index" 
               class="bg-red-500 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white hover:text-red-500 border-2 border-red-500">
                Jobseekers
            </a>
        </div>

        <button @click="mobileOpen = !mobileOpen" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="hidden min-[900px]:block border-t border-gray-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">
        <?php 
            $current_page = basename($_SERVER['PHP_SELF'], ".php");

            $navItems = [
                ['label' => 'Home', 'url' => 'index'],
                ['label' => 'Pricing', 'url' => 'pricing'],
                ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
                ['label' => 'About Us', 'url' => 'aboutus'],
                ['label' => 'Support', 'url' => 'supportss'],
                ['label' => 'Specials', 'url' => 'specials'],
            ];

            foreach($navItems as $item): 
                $isActive = ($current_page == $item['url']);
                $class = $isActive ? 'text-red-600' : 'text-black hover:text-red-600';
        ?>
            <a href="<?php echo $base . '/' . $item['url']; ?>" 
               class="relative py-4 text-[15px] font-semibold transition-colors duration-300 <?php echo $class; ?>">
                <?php echo $item['label']; ?>
                <?php if($isActive): ?>
                    <span class="absolute bottom-0 left-0 w-full h-0.5 bg-red-600"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

    <div x-show="mobileOpen" x-cloak x-transition class="min-[900px]:hidden bg-gray-50 px-4">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100">
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

<section class="relative w-full overflow-hidden bg-white">
    <div class="flex w-full min-h-px mx-auto">
        <div class="relative flex w-full min-h-px">
            <div class="flex relative w-full p-[10px] flex-wrap content-start bg-[#c54d4d]">
                <div class="w-full mb-0 text-center py-6">
                    <div class="elementor-widget-container max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
                        <h1 class="m-0 p-0 text-[#333333] font-sans 
                            text-[22px] sm:text-[28px] md:text-[32px] lg:text-[36px] 
                            leading-[1.3em] tracking-tight">
                           Save even more with our promotions and specials.
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="w-full font-sans antialiased text-[#7a7a7a]">

    <section class="max-w-[1140px] mx-auto py-[27px] px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="flex flex-col">
            <h2 class="text-[#333333] font-bold text-[23px] mb-[10px] leading-tight">
                New to Mindware Infotech?
            </h2>
            <div class="text-[12px] leading-[1.5]">
                <p>
                    After you’ve created your employer account, 
                    <a href="mailto:gm@mindwareinfotech.com?subject=Special%20offer" class="text-[#e15f55] font-semibold hover:underline break-all">email us</a> 
                    to let us know you are a new customer and we will send you a promo code for a discount on your first purchase! 
                    <span class="block mt-[6px] text-[10px] opacity-80">(Note: This cannot be combined with any other offer.)</span>
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <hr class="border-t border-[#eeeeee]">
    </div>

    <section class="max-w-[1140px] mx-auto py-[27px] px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="flex flex-col">
            <h2 class="text-[#333333] font-bold text-[23px] mb-[12px] leading-tight">
                Partner discount
            </h2>
            
            <div class="mb-[12px]">
                <a href="https://gcn.org/" target="_blank" class="inline-block">
                    <img src="https://workforgood.org/wp-content/uploads/2024/12/logo-2-1024x394-1.webp" 
                         class="max-w-[210px] h-auto" alt="GCN Logo">
                </a>
            </div>

            <div class="text-[12px] leading-[1.5] space-y-2">
                <p>
                    <a href="https://gcn.org/membership/" class="text-[#e15f55] font-bold hover:underline">
                        Georgia Center for Nonprofits
                    </a> members enjoy 15% off all postings and packages.
                </p>
                <p>Remember: Posting credits <strong>never expire</strong>.</p>
                <p>
                    For more information, contact Senior Manager Chelle Shell at 
                    <a href="mailto:gm@mindwareinfotech.com" class="text-[#e15f55] hover:underline break-all">gm@mindwareinfotech.com</a>.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <hr class="border-t border-[#eeeeee]">
    </div>

    <section class="max-w-[1140px] mx-auto py-[27px] px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="flex flex-col">
            <h2 class="text-[#333333] font-bold text-[23px] mb-[10px] leading-tight">
                Partnership opportunities
            </h2>
            <div class="text-[12px] leading-[1.5]">
                <p>
                    Would your organization like to partner with Mindware Infotech to offer discounts to your constituents? Just email us at 
                    <a href="mailto:hello@mindwareinfotech.com" class="text-[#e15f55] font-semibold hover:underline break-all">hello@mindwareinfotech.com</a> 
                    and we’ll chat about it!
                </p>
            </div>
        </div>
    </section>

</main>

<footer class="w-full">
    <section class="bg-[#f2f2f2] py-[15px] border-b border-[#eeeeee]">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="text-center">
                <p class="text-[#333333] font-sans text-[15px] m-0">
                    Need help? Email 
                    <a href="mailto:gm@mindwareinfotech.com" class="text-red-500 font-bold hover:underline break-all">
                        gm@mindwareinfotech.com
                    </a>.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-[#232323] py-[50px]">
        <div class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="flex flex-col items-center">
                
                <div class="mb-[30px]">
                    <img width="127" height="70" 
                         src="/uploads/Mindware-infotech.png" 
                         class="h-auto w-[127px] brightness-0 invert" 
                         alt="Mindware Infotech Logo">
                </div>

                <nav class="mb-[30px]">
                    <ul class="flex flex-wrap justify-center gap-x-[25px] gap-y-2">
                        <li><a href="aboutus" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">About us</a></li>
                        <li><a href="/../contact" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Contact us</a></li>
                        <li><a href="hiringInsightSignUp" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Subscribe</a></li>
                        <li><a href="/../terms" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Terms & Conditions</a></li>
                        <li><a href="/../privacy" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Privacy policy</a></li>
                        <li><a href="supports" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Support</a></li>
                        <li><a href="employers" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-red-400 transition-colors">Post a job</a></li>
                      </ul>
                </nav>

                <div class="flex justify-center mb-[25px]">
                    <a href="https://www.linkedin.com/company/mindwareinfotech/" target="_blank" 
                       class="bg-[#444444] hover:bg-[#0077b5] transition-all duration-300 p-3 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 310 310" xmlns="http://www.w3.org/2000/svg">
                            <path d="M72.16,99.73H9.927c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5H72.16c2.762,0,5-2.238,5-5V104.73 C77.16,101.969,74.922,99.73,72.16,99.73z"></path>
                            <path d="M41.066,0.341C18.422,0.341,0,18.743,0,41.362C0,63.991,18.422,82.4,41.066,82.4 c22.626,0,41.033-18.41,41.033-41.038C82.1,18.743,63.692,0.341,41.066,0.341z"></path>
                            <path d="M230.454,94.761c-24.995,0-43.472,10.745-54.679,22.954V104.73c0-2.761-2.238-5-5-5h-59.599 c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5h62.097c2.762,0,5-2.238,5-5v-98.918c0-33.333,9.054-46.319,32.29-46.319 c25.306,0,27.317,20.818,27.317,48.034v97.204c0,2.762,2.238,5,5,5H305c2.762,0,5-2.238,5-5V194.995 C310,145.43,300.549,94.761,230.454,94.761z"></path>
                        </svg>
                    </a>
                </div>

                <div class="text-[#7a7a7a] text-[13px] font-sans">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>

            </div>
        </div>
    </section>
</footer>

</body>
</html>