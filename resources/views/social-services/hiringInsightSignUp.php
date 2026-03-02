<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hiring Insight Signup | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-slate-50 flex flex-col min-h-screen text-[#54595f]">

<header x-data="{ mobileMenu: false }"
        class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-black font-medium mr-2">Candidates & Job Seekers:</span>
                <a href="candidate" class="text-red-400 font-semibold hover:underline">Login</a>
                <span class="mx-1 text-gray-300">|</span>
                <a href="#" class="text-red-400 font-semibold hover:underline">Create account</a>
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

    <main class="flex-grow py-12 px-4 sm:px-6 md:px-10 lg:px-12" x-data="newsletterForm()">
        <div class="max-w-4xl mx-auto min-[900px]:ml-32">
            
            <div class="space-y-7 text-left">
                
                <div class="space-y-4">
                    <h1 class="font-bold text-black tracking-tight text-[22px] sm:text-[28px] md:text-[32px] lg:text-[36px]">Get inspired.</h1>
                    <p class="text-base font-bold text-black leading-relaxed">
                        Hiring Insight, just for you.
                    </p>
                    <p class="text-base text-black leading-relaxed">
                        Interested in the best ways to secure new talent, manage your team, and retain your people?
                        We’ve got you covered here and in Hiring Insight, our monthly newsletter for employers.
                        Sign up and get Hiring Insight delivered to your inbox each month, featuring practical, topical, dependable resources.
                    </p>    
                </div>

                <div>
                    <p class="text-lg text-black ">
                        You may unsubscribe at any time. For more details, see our 
                        <a href="/privacy" class="text-blue-600 text-lg underline">Privacy Promise</a>.
                    </p>
                </div>

                <form action="/process-hiring-signup" method="POST" class="space-y-6" @submit="return validate($event)">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email address *</label>
                            <input type="email" name="email" x-model="form.email"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all"
                                placeholder="name@gmail.com">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">First name *</label>
                            <input type="text" name="first_name" x-model="form.first_name"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md outline-none transition-all focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Last name *</label>
                            <input type="text" name="last_name" x-model="form.last_name"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 outline-none transition-all focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Organization *</label>
                            <input type="text" name="organization" x-model="form.organization"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 outline-none transition-all focus:border-blue-500"
                                placeholder="e.g., Mindware Infotech">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Role title *</label>
                            <input type="text" name="role_title" x-model="form.role_title"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 outline-none transition-all focus:border-blue-500"
                                placeholder="e.g., HR Manager">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">State *</label>
                            <input type="text" name="state" x-model="form.state"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 outline-none transition-all focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Zip code *</label>
                            <input type="text" name="zip" x-model="form.zip"
                                class="w-full p-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-4 focus:ring-blue-100 outline-none transition-all focus:border-blue-500">
                        </div>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox" name="consent" x-model="form.consent"
                            class="mt-1 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-600 leading-snug group-hover:text-slate-900 transition">
                            I agree to receive monthly Hiring Insight emails and accept the <a href="/terms" class="text-blue-600 underline">Terms & Conditions</a>.
                        </span>
                    </label>

                    <div x-show="error" 
                         x-transition 
                         x-cloak
                         class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-700 text-sm font-medium">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span x-text="error"></span>
                    </div>

                    <button type="submit"
                        class="w-full bg-red-500 hover:opacity-90 text-white py-5 rounded-2xl font-bold text-lg hover:bg-white hover:text-red-500 border-2 border-red-500 ">
                        Subscribe Now
                    </button>

                </form>
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

    <script>
    function newsletterForm() {
        return {
            form: {
                email: '',
                first_name: '',
                last_name: '',
                organization: '',
                role_title: '',
                state: '',
                zip: '',
                consent: false
            },
            error: '',

            validate(e) {
                for (let key in this.form) {
                    if (key === 'consent' && !this.form[key]) {
                        this.error = "You must agree to the terms to subscribe.";
                        return false;
                    }
                    if (typeof this.form[key] === 'string' && this.form[key].trim() === '') {
                        this.error = "Please fill in all required fields.";
                        return false;
                    }
                }

                this.error = '';
                return true;
            }
        }
    }
    </script>

</body>
</html>