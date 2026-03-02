<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Ensuring fluid breaks for mobile email links */
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
                <span class="text-gray-400 font-medium mr-2">Employers:</span>
                <a href="login" class="text-red-400 font-semibold hover:underline">Login/Create account</a>
            </div>
            <a href="candidate" 
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
                    ['label' => 'Home', 'url' => 'index'],
                    ['label' => 'Pricing', 'url' => 'pricing'],
                    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
                    ['label' => 'About Us', 'url' => 'aboutus'],
                    ['label' => 'Support', 'url' => 'supports'],
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

<main class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-16">
    <div class="mb-12">
        <h1 class="text-[16px] sm:text-[22px] md:text-[26px] lg:text-[30px] font-bold mb-4 text-[#54595f]">Frequently Asked Questions: Employers</h1>
        <div class="space-y-4 max-w-4xl">
            <p class="text-base text-gray-700 leading-relaxed">
                Mindware Infotech’s upgraded site is now live! We’ve worked hard to bring you a more streamlined, secure, and practical way to list your job openings and find top talent.
            </p>
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                <p class="text-amber-800 text-sm italic">
                    <strong>Note:</strong> We are currently updating this FAQ page to align with our upgraded account system. More questions and answers will be added soon!
                </p>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-100 p-6 mb-12 rounded-xl flex items-start md:items-center gap-4">
        <div class="bg-red-500 p-3 rounded-full text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-gray-900">Need immediate help?</h3>
            <p class="text-gray-600">Our support team is ready to assist you at <a href="mailto:gm@mindwareinfotech.com" class="text-red-500 font-semibold hover:underline break-all">gm@mindwareinfotech.com</a></p>
        </div>
    </div>

    <div class="space-y-16">
        <section>
            <h2 class="text-lg font-extrabold text-red-500 border-b-2 border-gray-100 pb-3 mb-8 uppercase tracking-wider">
                Creating Your Account and Logging In
            </h2>
            <div class="grid gap-10 max-w-5xl">
                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">How do I create an account?</h3>
                    <p class="text-gray-700">Go <a href="login" class="text-red-500 font-medium underline">here</a> and complete the form.</p>
                </article>

                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">Forgot username and/or password?</h3>
                    <div class="text-gray-700 space-y-3">
                        <p>Your username is your email address. To login without a password:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li>Click <strong>"Login"</strong> on the homepage.</li>
                            <li>Enter your email and click <strong>"Send me a login code."</strong></li>
                            <li>Check your inbox and enter the provided code on mindwareinfotech.com.</li>
                        </ul>
                    </div>
                </article>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-red-500 border-b-2 border-gray-100 pb-3 mb-8 uppercase tracking-wider">
                Managing Your Employer Profile & Team
            </h2>
            <div class="grid gap-10 max-w-5xl">
                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">How do I add or remove users?</h3>
                    <p class="text-gray-700 leading-relaxed">
                        You must have <strong>"Manager"</strong> access. Go to <strong>Your Account > Organizations & Roles > Manage organizations & roles</strong>. 
                        Click <strong>"Manage Users"</strong> to add new members (using the red button) or edit/remove existing ones (using "Edit details").
                    </p>
                </article>

                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">What are the different access levels?</h3>
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                        <ul class="space-y-4">
                            <li class="flex gap-2"><strong>View only:</strong> See listings and applicants, but cannot edit.</li>
                            <li class="flex gap-2"><strong>Manage listings:</strong> Post and edit live jobs and view applicants.</li>
                            <li class="flex gap-2"><strong>Manage users:</strong> Change permissions or remove team members.</li>
                        </ul>
                    </div>
                </article>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-red-500 border-b-2 border-gray-100 pb-3 mb-8 uppercase tracking-wider">
                Posting and Managing Your Job Listings
            </h2>
            <div class="grid gap-10 max-w-5xl">
                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">How do I post a job listing?</h3>
                    <p class="text-gray-700">
                        Log in and select <strong>"Manage your jobs"</strong> from the "Your Account" dropdown. 
                        Click the green <strong>"Add new listing"</strong> button and follow the 6-step process to define your role, location, and application preferences.
                    </p>
                </article>

                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">What is the difference between Standard and Premium?</h3>
                    <div class="grid md:grid-cols-2 gap-6 mt-4">
                        <div class="border border-gray-200 p-4 rounded-lg">
                            <h4 class="font-bold text-gray-800 mb-2">Standard Includes:</h4>
                            <ul class="text-sm text-gray-600 list-inside list-disc space-y-1">
                                <li>30-day listing</li>
                                <li>Logo branding</li>
                                <li>Unlimited word count</li>
                            </ul>
                        </div>
                        <div class="border-2 border-[#5b6bd5] p-4 rounded-lg bg-blue-50/30">
                            <h4 class="font-bold text-[#5b6bd5] mb-2">Premium Adds:</h4>
                            <ul class="text-sm text-gray-700 list-inside list-disc space-y-1">
                                <li>Top-ranked placement</li>
                                <li>Homepage featured rotation</li>
                                <li>Enhanced SEO optimization</li>
                            </ul>
                        </div>
                    </div>
                </article>

                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">Can I use screening questions?</h3>
                    <p class="text-gray-700">
                        Yes. In <strong>Step 4</strong> of the posting process, you can add yes-or-no questions and require specific answers before a candidate is allowed to apply.
                    </p>
                </article>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-extrabold text-red-500 border-b-2 border-gray-100 pb-3 mb-8 uppercase tracking-wider">
                Optimizing & Purchases
            </h2>
            <div class="grid gap-10 max-w-5xl">
                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">Why avoid contact info in the description?</h3>
                    <p class="text-gray-700">
                        Applying through our system allows you to manage all resumes in one online portal and collaborate with colleagues. You also get immediate email notifications for every applicant.
                    </p>
                </article>

                <article>
                    <h3 class="text-base font-bold mb-3 text-gray-900">Billing & Invoices</h3>
                    <p class="text-gray-700">
                        We offer <strong>30-day net</strong> terms. Invoices are emailed electronically and must be paid within 30 days. Please note that invoices unpaid after 45 days incur a <strong>$15 late fee</strong>.
                    </p>
                </article>
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

                <div class="text-[#7a7a7a] text-[13px] font-sans text-center">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>
            </div>
        </div>
    </section>
</footer>

</body>
</html>