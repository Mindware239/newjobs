<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Maintain specific brand color palette */
        .text-brand-blue { color: #5b6bd5; }
        .bg-brand-blue { background-color: #5b6bd5; }
        .text-brand-red { color: #e15f55; }
        .text-brand-gray { color: #54595f; }
    </style>
</head>

<body class="bg-white font-sans antialiased text-[#54595f]" x-data="{ mobileMenuOpen: false }">
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
if ($current_page === '') $current_page = 'index';

$navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label'=> 'Find a job','url'=> 'find-a-job'],
    ['label'=> 'Create job alerts','url'=> 'social-services/login'],
    ['label'=> 'Search Employers','url'=> 'searchEmployers'],
    ['label' => 'Career Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutus'],
    ['label' => 'Support', 'url' => 'supports'],
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

<main class="w-full">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-12 md:py-20">
        
        <h1 class="text-[#242222] text-[22px] sm:text-[28px] md:text-[32px] lg:text-[36px] leading-tight mb-8">
            Need help?
        </h1>

        <div class="text-[#020202] text-base leading-[1.7] max-w-3xl">
            
            <p class="mb-6">
                The Mindware Infotech platform has recently been migrated to a new system! 
                Changes in your user experience should be minimal. 
            </p>

            <p class="mb-6">
                However, <strong class="text-[#333]">you will see new options when logging in</strong> for the first time.
            </p>

            <ol class="list-decimal list-outside ml-5 mb-8 space-y-4">
                <li class="pl-2">You will be prompted to provide the email attached to your account in order to receive a one-time login code.</li>
                <li class="pl-2">Check your inbox for the login code. Please allow a few minutes for your email server to receive the code. If the email has not arrived in your inbox, be sure to check your junk or spam folder.</li>
                <li class="pl-2">Once you’ve received your login code, you can use it to log in. From there, you can update your account information, including your password.</li>
            </ol>

            <p class="mb-10 p-5 bg-blue-50/50 rounded-r-lg border-l-4 border-red-500 italic">
                You will be able to reset your password once you log in, but please note that you will always have the option to log in via a one-time login code.
            </p>

            <h3 class="text-[#333333] font-bold text-[20px] mb-4">
                Still have questions? We are happy to help!
            </h3>

            <div class="space-y-3">
                <p>
                    For further troubleshooting, please take a look at our 
                    <a href="frequentlyCandidateAskedQuestions" class="text-[#e15f55] font-bold hover:underline decoration-2 underline-offset-4">Jobseeker FAQs</a>
                    and 
                    <a href="frequentlyEmployerAskedQuestions" class="text-[#e15f55] font-bold hover:underline decoration-2 underline-offset-4">Employer FAQs</a>.
                </p>

                <p class="text-base text-gray-600">
                       Need more help? 
               
            </p>
            </div>
        </div>
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

                <div class="text-[#7a7a7a] text-[13px] font-sans">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech.</p>
                </div>

            </div>
        </div>
    </section>
</footer>

</body>
</html>
