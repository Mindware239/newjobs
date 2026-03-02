<?php $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

$base = rtrim($scheme . '://' . $host, '/');
// ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Roles | mindware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Brand color helpers */
        .text-brand-blue { color: #5b6bd5; }
        .bg-brand-blue { background-color: #5b6bd5; }
        .text-brand-red { color: #e15f55; }
        .text-brand-gray { color: #54595f; }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased text-[#54595f]" x-data="{ mobileMenuOpen: false }">

<header class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>/uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-gray-400 font-medium mr-2">Employers:</span>
                <a href="employers" class="text-[#5b6bd5] font-semibold hover:underline">Login</a>
                <span class="mx-1 text-gray-300">|</span>
                <a href="#" class="text-[#5b6bd5] font-semibold hover:underline">Create account</a>
            </div>
            <a href="candidate" 
               class="bg-[#5b6bd5] text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-[#4a59c8] transition-all shadow-md shadow-blue-100">
                Jobseekers
            </a>
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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
                    ['label' => 'Support', 'url' => 'supports'],
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

    <div x-show="mobileMenuOpen" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="min-[900px]:hidden bg-gray-50 px-4 border-t border-gray-100">
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

<main class="w-full">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-12 md:py-20">

        <h1 class="text-[#242222] text-[22px] sm:text-[28px] md:text-[32px] lg:text-[36px] font-semibold leading-tight mb-8">
            Browse by role category
        </h1>

        <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-2 gap-x-16 text-black">
            <li>Accounting / Finance</li>
            <li>Administrative / Clerical</li>
            <li>Advocacy / Lobbying</li>
            <li>Animal Care</li>
            <li>Campaign Management / Canvassing / Field Organizer</li>
            <li>Child Care / After school / Counselor / Mentor</li>
            <li>Community Engagement</li>
            <li>Conservation</li>
            <li>Consulting</li>
            <li>Creative / Art Production</li>
            <li>Customer Service / Retail</li>
            <li>Development / Fundraising</li>
            <li>Direct Service / Social Service</li>
            <li>Education / Teaching</li>
            <li>Event Planning</li>
            <li>Executive / Senior Management</li>
            <li>Facilities & Warehouse Management / Equipment / Drivers</li>
            <li>Food Service</li>
            <li>Health / Medical / Nutrition</li>
            <li>Home Health Aid / Senior Care</li>
            <li>Horticulture / Groundskeeper</li>
            <li>Housing / Construction</li>
            <li>Human Resources / Recruiting</li>
            <li>Journalism / Broadcasting</li>
            <li>Legal</li>
            <li>Library Science</li>
            <li>Marketing / Communications / Public Relations</li>
            <li>Member / Membership Management</li>
            <li>Operations / Business Management</li>
            <li>Program / Project Management</li>
            <li>Public Policy / Administration</li>
            <li>Recreational / Camp Associates & Management</li>
            <li>Research</li>
            <li>Sales / Business Development</li>
            <li>Social Work / Counseling</li>
            <li>Technology / Data Management</li>
            <li>Training / Curriculum Development</li>
            <li>Unknown / Other</li>
            <li>Volunteer Services</li>
        </ul>

    </div>
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

                <div class="text-[#7a7a7a] text-[13px] font-sans text-center">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>

            </div>
        </div>
    </section>
</footer>

</body>
</html>
