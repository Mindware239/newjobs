<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .break-all { word-break: break-all; }
    </style>
</head>

<body class="bg-white text-[#54595f]" x-data="{ mobileMenu: false }">

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
                <a href="candidate" class="text-red-400 font-semibold hover:underline">Login/Create account</a>
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
                $current_page = basename($_SERVER['PHP_SELF'], ".php");
                $navItems = [
                    ['label' => 'Home', 'url' => 'index'],
                    ['label'=> 'Find a job','url'=> 'find-a-job'],
                    ['label'=> 'Create job alerts','url'=> 'newsubscriptions'],
                    ['label'=> 'Search Employers','url'=> 'searchEmployers'],
                    ['label' => 'Career Insight', 'url' => 'hiringInsight'],
                    ['label' => 'About Us', 'url' => 'aboutus'],
                    ['label' => 'Support', 'url' => 'supports'],
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

<main class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-16">
    <div class="mb-12">
        <h1 class="text-[16px] sm:text-[22px] md:text-[26px] lg:text-[30px] font-bold mb-4 text-black">Frequently Asked Questions: Candidates</h1>
        <p class="text-lg text-gray-600 max-w-3xl">
            Mindware Infotech's upgraded site is now live! We’ve worked hard to bring you a more streamlined, secure, and practical way to manage your account and apply for positions.
        </p>
    </div>

    <div class="bg-blue-50 border-l-4 border-red-500 p-6 mb-12 rounded-r-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-red-500">
                    Need further support? Reach out to us at 
                    <a href="mailto:gm@mindwareinfotech.com" class="font-bold underline break-all">gm@mindwareinfotech.com</a>
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-12">
        
        <section>
            <h2 class="text-lg font-bold text-red-500 border-b pb-2 mb-6 uppercase tracking-wide">
                Creating Your Account and Logging In
            </h2>
            <div class="space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-semibold mb-2">How do I create an account?</h3>
                    <p class="text-gray-700">Go <a href="login" class="text-[#e15f55] hover:underline">here</a> and complete the form.</p>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">Why can’t I have multiple accounts with one email address?</h3>
                    <p class="text-gray-700">Our system will only allow an email address to be associated with one account.</p>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">What do I do if I forgot my username and/or password?</h3>
                    <div class="text-gray-700 space-y-4">
                        <p>Your username is your email address. You can login without a password by following these steps:</p>
                        <ol class="list-decimal ml-6 space-y-1">
                            <li>From the homepage, click <strong>"Login"</strong>.</li>
                            <li>Type your email address and click <strong>"Send me a login code."</strong></li>
                            <li>Enter the code sent to your inbox on the following page.</li>
                        </ol>
                        <p class="text-sm italic mt-2">If you no longer remember your email, contact us  <a href="mailto:gm@mindwareinfotech.com" class="text-base text-[#e15f55] underline break-all">gm@mindwareinfotech.com</a>.</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">How does Mindware Infotech keep my information confidential?</h3>
                    <p class="text-gray-700">Your information is never public. Only approved employers can see your profile if you set your visibility to "Allow employers to see your profile."</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-bold text-red-500 border-b pb-2 mb-6 uppercase tracking-wide">
                Managing Your Candidate Account
            </h2>
            <div class="space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-semibold mb-2">Can I remove my profile if I've accepted a job?</h3>
                    <p class="text-gray-700">First – congratulations! We recommend keeping your account active since you are only visible to recruiters when you actively apply. If you want permanent deletion, please contact us for identity verification.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-bold text-red-500 border-b pb-2 mb-6 uppercase tracking-wide">
                Applying for Jobs
            </h2>
            <div class="space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-semibold mb-2">How do I apply for a job?</h3>
                    <p class="text-gray-700">Navigate to the job and click <strong>"Apply Now"</strong>. If not logged in, you'll be prompted for a login code. We recommend uploading materials in <strong>PDF form</strong> to keep formatting consistent.</p>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">How do I upload a resume?</h3>
                    <p class="text-gray-700">you upload a resume <strong>each time you apply</strong>. This allows you to tailor your resume specifically to each job listing.</p>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">Can I delete a job application or reapply?</h3>
                    <p class="text-gray-700">No. Applications are sent immediately to employers. Please double-check your information before submitting.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-bold text-red-500 border-b pb-2 mb-6 uppercase tracking-wide">
                Job Alerts
            </h2>
            <div class="space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-semibold mb-2">What are job alerts?</h3>
                    <p class="text-gray-700">Notifications about new opportunities matching your keywords, location, and pay range. You can receive these daily or weekly.</p>
                </div>
                <div>
                    <h3 class="text-base font-semibold mb-2">How do I create or edit job alerts?</h3>
                    <p class="text-gray-700">Log in, go to <strong>"Your Account,"</strong> and click <strong>"Manage your alerts & notifications."</strong> From there you can create, edit, or remove alerts.</p>
                </div>
            </div>
        </section>

    </div>
</main>

<footer class="w-full">
    <section class="bg-[#f2f2f2] py-[15px] border-b border-[#eeeeee]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
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
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="flex flex-col items-center">
                
                <div class="mb-[30px]">
                    <img width="127" height="70" 
                         src="/uploads/Mindware-infotech.png" 
                         class="h-auto w-[127px] brightness-0 invert" 
                         alt="Mindware Infotech Logo">
                </div>

                <nav class="mb-[30px]">
                    <ul class="flex flex-wrap justify-center gap-x-[25px] gap-y-2">
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
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>

            </div>
        </div>
    </section>
</footer>

</body>
</html>