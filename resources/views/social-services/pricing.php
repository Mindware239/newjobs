<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hire Talent | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
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

<body class="bg-white text-[#54595f]" x-data="{ mobileMenuOpen: false }">

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
                <a href="login" class="text-red-400 font-semibold hover:underline">Login</a>
                <span class="mx-1 text-gray-300">|</span>
                <a href="#" class="text-red-400 font-semibold hover:underline">Create account</a>
            </div>
            <a href="index" 
               class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white hover:text-red-400  border-2 hover:border-red-400">
                Jobseekers
            </a>
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <nav class="hidden min-[900px]:block border-t border-gray-50">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">
            <?php 
                $current_page = basename($_SERVER['PHP_SELF'], ".php");
                $navItems = [
                    ['label' => 'Home', 'url' => 'employers'],
                    ['label' => 'Pricing', 'url' => 'pricing'],
                    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
                    ['label' => 'About Us', 'url' => 'aboutus'],
                    ['label' => 'Support', 'url' => 'supportss'],
                    ['label' => 'Specials', 'url' => 'specials'],
                ];

                foreach($navItems as $item): 
                    $isActive = ($current_page == $item['url']);
                    $class = $isActive ? 'text-[#e15f55]' : 'text-black hover:text-[#e15f55]';
            ?>
                <a href="<?php echo $base . '/' . $item['url']; ?>" 
                   class="relative py-4 text-[15px] font-semibold transition-colors duration-300 <?php echo $class; ?>">
                    <?php echo $item['label']; ?>
                    <?php if($isActive): ?>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-[#e15f55]"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div x-show="mobileMenuOpen" x-cloak class="min-[900px]:hidden bg-gray-50 px-4 sm:px-6 shadow-inner">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100">
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-4 flex flex-col gap-3 pb-6">
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
            <div class="flex relative w-full p-2 sm:p-6 md:p-8 flex-wrap content-start bg-[#e15f55]">
                <div class="w-full mb-0 text-center">
                    <div class="elementor-widget-container">
                        <h1 class="m-0 p-0 text-white font-sans 
                            text-[16px] sm:text-[22px] md:text-[26px] lg:text-[30px] 
                            leading-[1.3em] tracking-tight font-bold">
                           Job listings: Great features, big value, unbeatable customer service.
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="bg-white font-sans text-[#54595f] antialiased">

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pt-16 pb-8 text-center">
        <h2 class="text-[#242222] text-[18px] md:text-[26px] font-bold leading-tight mb-6">
            Job listing credits
        </h2>
        <div class="text-[16px] leading-[1.6] max-w-3xl mx-auto space-y-4 text-[#222121]">
            <p>Two value-rich options to choose from. (Or keep scrolling for multi-listing packages, and save even more!)</p>
            <p>
                <strong class="text-black">Volunteer or internship positions to fill?</strong> 
                Scroll to the bottom of this page for <span class="font-bold">FREE</span> options.
            </p>
        </div>
    </section>

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-20">
            
            <div class="flex flex-col items-center">
                <h3 class="text-[#333] text-[20px] font-bold mb-2">Standard job listing</h3>
                <span class="text-[#333] text-[26px] font-semibold mb-6">$105</span>
                <a href="<?= $base ?>cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold tracking-wider uppercase px-4 py-3.5 rounded-[4px] mb-8">
                    Post a Job Now
                </a>
                <div class="bg-[#f8f9fa] p-6 md:p-8 rounded-lg text-left w-full text-[15px] leading-[1.7] border border-gray-50">
                    <p class="mb-4">Put your job in front of purpose-driven candidates with our value-packed job listing. Each listing includes:</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li>30-day listing</li>
                        <li>Your logo featured with each listing</li>
                        <li>Search optimization by keyword and location</li>
                        <li>Unlimited word count</li>
                        <li>Description storage for easy relisting</li>
                        <li>Hiring support from our customer service team</li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col items-center">
                <h3 class="text-[#333] text-[20px] font-bold mb-2">Premium job listing</h3>
                <span class="text-[#333] text-[26px] font-semibold mb-6">$180</span>
                <a href="<?= $base ?>cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold tracking-wider uppercase px-6 py-3.5 rounded-[4px] transition-all shadow-md shadow-blue-100 mb-8">
                    Post a Job Now
                </a>
                <div class="bg-[#f8f9fa] p-6 md:p-8 rounded-lg text-left w-full text-[15px] leading-[1.7] border border-gray-50">
                    <p class="mb-4">Stand out and maximize results with extra features designed to enhance visibility and encourage clicks. With a premium package your job listing will be:</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li>Top-ranked and highlighted in search results.</li>
                        <li>Branded with your logo in search results for greater visibility</li>
                        <li>Featured on the home page with your job title and logo</li>
                        <li>Additionally search-optimized with custom keywords</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-12 text-center border-t border-gray-100">
        <h4 class="text-[#000000] text-[28px] font-bold mb-8">Multi-listing packages</h4>
        <p class="text-[16px]">
            Credits never expire! Need a custom package to fit your needs? Just email us at 
            <a href="mailto:gm@mindwareinfotech.com" class="text-[#e15f55] font-bold hover:underline break-all">gm@mindwareinfotech.com</a> 
            for discount rates.
        </p>
    </section>

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="flex flex-col items-center p-8 bg-white border border-gray-100 rounded-xl shadow-sm">
                <h5 class="text-[18px] font-bold mb-3">3 Standard listings</h5>
                <span class="text-[26px] font-semibold text-[#333] mb-4">$275</span>
                <a href="cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Buy Now</a>
                <span class="text-[#e15f55] font-bold text-sm">15% discount</span>
            </div>
            <div class="flex flex-col items-center p-8 bg-white border border-gray-100 rounded-xl shadow-sm">
                <h5 class="text-[18px] font-bold mb-3">5 Standard listings</h5>
                <span class="text-[26px] font-semibold text-[#333] mb-4">$430</span>
                <a href="cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Buy Now</a>
                <span class="text-[#e15f55] font-bold text-sm">18% discount</span>
            </div>
            <div class="flex flex-col items-center p-8 bg-white border border-gray-100 rounded-xl shadow-sm">
                <h5 class="text-[18px] font-bold mb-3">10 Standard listings</h5>
                <span class="text-[26px] font-semibold text-[#333] mb-4">$755</span>
                <a href="cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Buy Now</a>
                <span class="text-[#e15f55] font-bold text-sm">28% discount</span>
            </div>
        </div>
    </section>

    <section class="bg-[#fafafa] py-20">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="text-center mb-16">
                <h3 class="text-[#242222] text-[28px] font-bold mb-4">Internship and volunteer listings</h3>
                <p class="text-[16px]">Same rich features as our standard job listings, at an unbeatable price.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="flex flex-col items-center">
                    <h4 class="text-[20px] font-bold mb-2">Internship listing</h4>
                    <span class="text-[26px] font-semibold mb-6">$30</span>
                    <a href="cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold uppercase px-4 py-3 rounded mb-8">Post a Job Now</a>
                    <div class="text-[14px] leading-relaxed text-left w-full space-y-4 bg-white p-8 rounded-lg border border-gray-100">
                        <p class="font-bold">Each internship listing includes:</p>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>30-day listing</li>
                            <li>Your logo featured</li>
                            <li>Unlimited word count</li>
                        </ul>
                        <p class="text-[#e15f55] font-bold uppercase tracking-tight">This offer is exclusively for internship positions.</p>
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    <h4 class="text-[20px] font-bold mb-2">Volunteer listing</h4>
                    <span class="text-[26px] font-semibold mb-6">$10</span>
                    <a href="cart" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold uppercase px-4 py-3 rounded mb-8">Post a Job Now</a>
                    <div class="text-[14px] leading-relaxed text-left w-full space-y-4 bg-white p-8 rounded-lg border border-gray-100">
                        <p class="font-bold">Each volunteer listing includes:</p>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>30-day listing</li>
                            <li>Search optimization</li>
                            <li>Description storage</li>
                        </ul>
                        <p class="text-[#e15f55] font-bold uppercase tracking-tight">This offer is exclusively for volunteer positions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

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
                    <ul class="flex flex-wrap justify-center gap-x-[25px] gap-y-4">
                        <li><a href="aboutus" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">About us</a></li>
                        <li><a href="/../contact" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Contact us</a></li>
                        <li><a href="hiringInsightSignUp" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Subscribe</a></li>
                        <li><a href="/../terms" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Terms & Conditions</a></li>
                        <li><a href="/../privacy" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Privacy policy</a></li>
                        <li><a href="supports" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Support</a></li>
                        <li><a href="employers" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Post a job</a></li>
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
                    <p>© <?php echo date("Y"); ?> Mindware Infotech.</p>
                </div>

            </div>
        </div>
    </section>
</footer>

</body>
</html>