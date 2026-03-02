

<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Social Services Jobs | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #5b6bd5; border-radius: 10px; }
    </style>
</head>

<body class="bg-gray-50 text-[#54595f] font-sans" x-data="{ mobileMenu: false }">
<?php include __DIR__ . '/header.php'; ?>

<section class="relative bg-gradient-to-b from-white to-slate-50 py-14">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="flex flex-col items-center text-center">
            <h1 class="text-black text-[28px] sm:text-[36px] md:text-[42px] font-extrabold tracking-tight">Find your career and your calling.</h1>
            <p class="mt-2 text-slate-600 text-sm sm:text-base">Search purpose-driven roles across nonprofits, social enterprises, and mission-first teams.</p>
            <div class="mt-8 w-full bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                <form method="get" action="/find-a-job" class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-stretch">
                    <div class="lg:col-span-5">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input name="keyword" type="search" placeholder="eg. development, program manager" class="w-full h-12 pl-9 pr-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#5b6bd5] outline-none" aria-label="Keywords">
                        </div>
                    </div>
                    <div class="lg:col-span-5 grid grid-cols-12 gap-3">
                        <div class="col-span-8">
                            <div class="relative">
                                <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input name="location" type="text" placeholder="Search location" class="w-full h-12 pl-9 pr-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#5b6bd5] outline-none">
                            </div>
                        </div>
                        <div class="col-span-4">
                            <select name="radius" class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#5b6bd5] text-sm cursor-pointer outline-none">
                                <option value="5">5 mi</option>
                                <option value="25">25 mi</option>
                                <option value="50">50 mi</option>
                            </select>
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <button type="submit" class="w-full h-12 bg-[#e15f55] text-white font-bold rounded-xl hover:bg-white hover:text-[#e15f55] border-2 border-[#e15f55] transition">
                            Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 w-full">
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-sm">
                    <span class="block text-slate-500">Active roles</span>
                    <span class="text-black font-bold">Hundreds posted</span>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-sm">
                    <span class="block text-slate-500">Trusted employers</span>
                    <span class="text-black font-bold">Verified organizations</span>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-sm">
                    <span class="block text-slate-500">Smart alerts</span>
                    <span class="text-black font-bold">Personalized matches</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section x-data="{ tab: 'mission' }" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10">

    <!-- Tabs -->
    <div class="flex gap-3">
        <button
            @click="tab = 'mission'"
            :class="tab === 'mission' ? 'bg-[#e15f55] text-white' : 'bg-slate-100 text-slate-700'"
            class="px-4 py-2 text-sm font-semibold rounded-full transition">
            MISSION FOCUS AREA
        </button>

        <button
            @click="tab = 'role'"
            :class="tab === 'role' ? 'bg-[#e15f55] text-white' : 'bg-slate-100 text-slate-700'"
            class="px-4 py-2 text-sm font-semibold rounded-full transition">
            ROLE CATEGORY
        </button>
    </div>

    <!-- Content Box -->
    <div class="mt-6 bg-white border border-slate-200 rounded-2xl p-6">

        <!-- MISSION TAB -->
        <div x-show="tab === 'mission'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                <a href="/find-a-job?mission=aging-seniors" class="inline-block px-3 py-2 bg-slate-100 rounded-lg hover:bg-[#ffe7e5] hover:text-[#e15f55] transition">Aging / Seniors</a>
                <a href="/find-a-job?mission=aging-seniors" class="block hover:text-[#e15f55] transition">Aging / Seniors</a>
                <a href="/find-a-job?mission=sustainable-energy" class="block hover:text-[#e15f55] transition">Alternative & Sustainable Energy</a>
                <a href="/find-a-job?mission=animal-related" class="block hover:text-[#e15f55] transition">Animal-Related</a>
                <a href="/find-a-job?mission=arts-culture" class="block hover:text-[#e15f55] transition">Arts, Culture & Humanities</a>
                <a href="/find-a-job?mission=association-union" class="block hover:text-[#e15f55] transition">Association / Mutual & Membership Benefit / Union</a>

            <div class="space-y-3">
                <a href="/find-a-job?mission=broadcast" class="block hover:text-[#e15f55] transition">Broadcast / Journalism</a>
                <a href="/find-a-job?mission=childcare" class="block hover:text-[#e15f55] transition">Childcare / Preschool / After-school Care</a>
                <a href="/find-a-job?mission=civil-rights" class="block hover:text-[#e15f55] transition">Civil Rights, Social Action & Advocacy</a>
                <a href="/find-a-job?mission=community-improvement" class="block hover:text-[#e15f55] transition">Community Improvement & Capacity Building</a>
                <a href="/find-a-job?mission=environment" class="block hover:text-[#e15f55] transition">Conservation / Environment Advocacy</a>
            </div>

            <div class="space-y-3">
                <a href="/find-a-job?mission=crime-legal" class="block hover:text-[#e15f55] transition">Crime & Legal-Related</a>
                <a href="/find-a-job?mission=disability" class="block hover:text-[#e15f55] transition">Disability Related</a>
                <a href="/find-a-job?mission=disease-medical" class="block hover:text-[#e15f55] transition">Disease & Medical Disorder Related</a>
                <a href="/find-a-job?mission=education" class="block hover:text-[#e15f55] transition">Education</a>
                <a href="/find-a-job?mission=employment" class="block hover:text-[#e15f55] transition">Employment</a>
            </div>
        </div>

        <!-- ROLE TAB -->
        <div x-show="tab === 'role'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                <a href="/find-a-job?role=accounting-finance" class="inline-block px-3 py-2 bg-slate-100 rounded-lg hover:bg-[#ffe7e5] hover:text-[#e15f55] transition">Accounting / Finance</a>
                <a href="/find-a-job?role=accounting-finance" class="block hover:text-[#e15f55] transition">Accounting / Finance</a>
                <a href="/find-a-job?role=administrative" class="block hover:text-[#e15f55] transition">Administrative / Clerical</a>
                <a href="/find-a-job?role=advocacy" class="block hover:text-[#e15f55] transition">Advocacy / Lobbying</a>
                <a href="/find-a-job?role=animal-care" class="block hover:text-[#e15f55] transition">Animal Care</a>
                <a href="/find-a-job?role=campaign" class="block hover:text-[#e15f55] transition">Campaign Management / Canvassing</a>

            <div class="space-y-3">
                <a href="/find-a-job?role=child-care" class="block hover:text-[#e15f55] transition">Child Care / Counselor</a>
                <a href="/find-a-job?role=community-engagement" class="block hover:text-[#e15f55] transition">Community Engagement</a>
                <a href="/find-a-job?role=conservation" class="block hover:text-[#e15f55] transition">Conservation</a>
                <a href="/find-a-job?role=consulting" class="block hover:text-[#e15f55] transition">Consulting</a>
                <a href="/find-a-job?role=creative" class="block hover:text-[#e15f55] transition">Creative / Art Production</a>
            </div>

            <div class="space-y-3">
                <a href="/find-a-job?role=customer-service" class="block hover:text-[#e15f55] transition">Customer Service / Retail</a>
                <a href="/find-a-job?role=development" class="block hover:text-[#e15f55] transition">Development / Fundraising</a>
                <a href="/find-a-job?role=direct-service" class="block hover:text-[#e15f55] transition">Direct Service / Social Service</a>
                <a href="/find-a-job?role=education-teaching" class="block hover:text-[#e15f55] transition">Education / Teaching</a>
                <a href="/find-a-job?role=event-planning" class="block hover:text-[#e15f55] transition">Event Planning</a>
            </div>
        </div>

        <!-- View All -->
        <div class="text-right mt-6 text-sm">
            <a href="/roles" class="text-[#e15f55] hover:underline font-medium">
                View all
            </a>
        </div>

    </div>
</section>

<section class="my-12 px-4 sm:px-6 md:px-10 lg:px-12 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-8 bg-white border border-slate-200 px-4 sm:px-6 py-6 rounded-2xl">
            <div class="flex justify-between items-center mb-6 border-b border-gray-300 pb-4">
                <h2 class="text-xl sm:text-2xl text-[#070707] font-bold">Career Insight</h2>
                <a href="hiringInsight" class="text-sm font-semibold text-gray-500 hover:text-[#e15f55] flex items-center gap-1 transition-colors">
                    View all articles
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <?php 
                $firstArticle = $insights[0] ?? null; 
            ?>
            <?php if ($firstArticle): ?>
                <div class="grid md:grid-cols-2 gap-8 items-center mb-10">
                    <div class="flex justify-center">
                        <a href="<?= htmlspecialchars($firstArticle['url']) ?>">
                            <img src="<?= htmlspecialchars($firstArticle['img'] ?: 'https://via.placeholder.com/800x450?text=Career+Insight') ?>" class="w-full h-48 sm:h-64 object-cover rounded-md shadow-sm" alt="<?= htmlspecialchars($firstArticle['title']) ?>">
                        </a>
                    </div>
                    <div>
                        <a href="<?= htmlspecialchars($firstArticle['url']) ?>">
                            <h3 class="text-xl sm:text-2xl font-bold text-black mb-3"><?= htmlspecialchars($firstArticle['title']) ?></h3>
                        </a>
                        <?php if (!empty($firstArticle['desc'])): ?>
                        <p class="text-black leading-relaxed mb-4 text-sm sm:text-base">
                            <?= ($firstArticle['desc']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
                $more = array_slice($insights ?? [], 1, 4); 
            ?>
            <?php if (!empty($more)): ?>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 w-full">
                    <?php foreach ($more as $a): ?>
                    <a href="<?= htmlspecialchars($a['url']) ?>" class="group cursor-pointer">
                        <img src="<?= htmlspecialchars($a['img'] ?: 'https://via.placeholder.com/400x220?text=Article') ?>" class="w-full h-24 sm:h-32 object-cover mb-3 rounded shadow-sm group-hover:opacity-80 transition" alt="<?= htmlspecialchars($a['title']) ?>">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-800"><?= htmlspecialchars($a['title']) ?></h4>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-4 bg-white border border-slate-200 p-4 sm:p-6 rounded-2xl space-y-6 lg:sticky lg:top-28">
            <div class="flex justify-between items-end border-b border-gray-300 pb-2">
                <h3 class="text-xl font-bold text-gray-900">Featured Jobs</h3>
                <a href="find-a-job" class="text-sm font-semibold text-gray-500 hover:text-[#e15f55]">View all</a>
            </div>

            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                <?php foreach (($featuredJobs ?? []) as $job): ?>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 group hover:bg-white transition">
                        <a href="<?= htmlspecialchars($job['url']) ?>" class="flex gap-4">
                            <img src="<?= htmlspecialchars($job['img']) ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg object-contain shrink-0 bg-white"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($job['company'] ?? $job['title']) ?>&background=ffffff&color=54595f'">
                            <div>
                                <h4 class="font-bold text-black text-sm"><?= htmlspecialchars($job['title']) ?></h4>
                                <?php if (!empty($job['loc'])): ?><p class="text-xs text-gray-600 mt-1"><?= htmlspecialchars($job['loc']) ?></p><?php endif; ?>
                                <?php if (!empty($job['salary'])): ?><p class="text-xs font-semibold text-black mt-2"><?= htmlspecialchars($job['salary']) ?></p><?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($featuredJobs)): ?>
                    <div class="text-sm text-gray-600">No featured jobs yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="my-12 px-4 sm:px-6 md:px-10 lg:px-12 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 p-6 sm:p-10 text-center rounded-2xl">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Be inspired</h3>
            <p class="text-gray-600 text-sm mb-6 uppercase tracking-widest">SIGN UP FOR EMAILS</p>
            <button class="bg-red-500 text-white px-8 py-3 rounded-lg text-sm font-semibold hover:bg-white hover:text-red-500 border-2 border-red-500">Subscribe Now</button>
        </div>
        <div class="bg-white border border-slate-200 p-6 sm:p-10 text-center rounded-2xl">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Get discovered</h3>
            <p class="text-gray-600 text-sm mb-6 uppercase tracking-widest">START APPLYING</p>
            <button class="bg-red-500 text-white px-8 py-3 rounded-lg text-sm font-semibold hover:bg-white hover:text-red-500 border-2 border-red-500">Apply Now</button>
        </div>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-0">
            <div class="lg:col-span-9 bg-white border border-slate-200 rounded-2xl lg:rounded-r-none lg:rounded-l-2xl px-6 sm:px-10 py-10">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                    <h2 class="text-lg font-bold text-gray-900">Featured employers</h2>
                    <a href="searchEmployers" class="text-sm text-gray-500 hover:text-[#e15f55] font-medium">View all employers</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <?php foreach (($featuredOrgs ?? []) as $org): ?>
                        <a href="<?= htmlspecialchars($org['url']) ?>" class="bg-white rounded-xl p-4 flex items-center justify-center shadow-sm h-24 sm:h-32 border border-slate-200">
                            <img src="<?= htmlspecialchars($org['logo']) ?>" alt="<?= htmlspecialchars($org['name']) ?>" class="max-h-full object-contain"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($org['name']) ?>&background=ffffff&color=54595f'">
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($featuredOrgs)): ?>
                        <div class="text-sm text-gray-600">No employers to display yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="lg:col-span-3 bg-white border border-slate-200 lg:border-l-0 rounded-2xl lg:rounded-l-none lg:rounded-r-2xl px-8 py-10">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-base font-bold text-gray-900">Quick search</h3>
                    <a href="http://localhost:8000/find-a-job" class="text-sm text-black hover:text-[#e15f55]">More jobs</a>
                </div>
                <ul class="space-y-4 text-sm sm:text-base text-black font-medium">
                    <li class="flex items-center gap-2 hover:text-[#e15f55] cursor-pointer"><span class="text-[#e15f55] text-[10px]">●</span> DC Nonprofit Jobs</li>
                    <li class="flex items-center gap-2 hover:text-[#e15f55] cursor-pointer"><span class="text-[#e15f55] text-[10px]">●</span> Tri-state Nonprofit Jobs</li>
                    <li class="flex items-center gap-2 hover:text-[#e15f55] cursor-pointer"><span class="text-[#e15f55] text-[10px]">●</span> Los Angeles Nonprofit Jobs</li>
                    <li class="flex items-center gap-2 hover:text-[#e15f55] cursor-pointer"><span class="text-[#e15f55] text-[10px]">●</span> Bay Area Nonprofit Jobs</li>
                </ul>
            </aside>
        </div>
    </div>
</section>

<section class="mt-8 mb-16 mx-auto px-4 sm:px-6 md:px-10 lg:px-12 max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 min-h-[200px] shadow-lg rounded-xl overflow-hidden border">
        <div class="p-8 bg-white flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Welcome!</h2>
            <p class="text-gray-700 leading-relaxed text-sm">
                At Mindware Infotech our mission is to help purpose-driven organizations and talented professionals connect, so together you can do the best work possible.
                <a href="aboutus" class="text-[#e15f55] font-bold underline ml-1">Click to learn more.</a>
            </p>
        </div>
        <div class="p-8 flex flex-col justify-center items-center border-t-8 md:border-t-0 md:border-l-8 border-cyan-500 bg-gray-50 text-center gap-4">
            <p class="text-xl font-bold text-black uppercase tracking-tight">Find opportunities</p>
            <button  onclick="window.location.href='social-services/login'" class="bg-red-500 text-white px-6 py-3 rounded-full font-bold text-sm transition-all hover:bg-white hover:text-red-500 border-2 border-red-500">
                SET UP JOB ALERT
            </button>
        </div>
    </div>
</section>

<footer class="w-full">
    <section class="bg-[#f2f2f2] py-4 border-b border-[#eeeeee]">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="text-center">
                <p class="text-[#333333] text-[18px]">
                    Need help? Email
                    <a href="mailto:gm@mindwareinfotech.com" class="text-red-500 font-bold hover:underline break-all">
                        gm@mindwareinfotech.com
                    </a>.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-[#232323] py-12">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="flex flex-col items-center">
                <div class="mb-8">
                    <img width="127" height="70" src="/uploads/Mindware-infotech.png" class="h-auto w-32 brightness-0 invert" alt="Logo">
                </div>

                <nav class="mb-8">
                    <ul class="flex flex-wrap justify-center gap-x-6 gap-y-3">
                        <li><a href="aboutus" class="text-white text-sm font-medium hover:text-[#e15f55] transition">About us</a></li>
                        <li><a href="/../contact" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Contact us</a></li>
                        <li><a href="hiringInsightSignUp" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Subscribe</a></li>
                        <li><a href="/../terms" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Terms & Conditions</a></li>
                        <li><a href="/../privacy" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Privacy policy</a></li>
                        <li><a href="supports" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Support</a></li>
                        <li><a href="employers" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Post a job</a></li>
                    </ul>
                </nav>

                <div class="flex justify-center mb-8">
                    <a href="https://www.linkedin.com/company/mindwareinfotech/" target="_blank" class="bg-[#444444] hover:bg-[#0077b5] transition-all p-3 rounded-full">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 310 310">
                            <path d="M72.16,99.73H9.927c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5H72.16c2.762,0,5-2.238,5-5V104.73 C77.16,101.969,74.922,99.73,72.16,99.73z"></path>
                            <path d="M41.066,0.341C18.422,0.341,0,18.743,0,41.362C0,63.991,18.422,82.4,41.066,82.4 c22.626,0,41.033-18.41,41.033-41.038C82.1,18.743,63.692,0.341,41.066,0.341z"></path>
                            <path d="M230.454,94.761c-24.995,0-43.472,10.745-54.679,22.954V104.73c0-2.761-2.238-5-5-5h-59.599 c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5h62.097c2.762,0,5-2.238,5-5v-98.918c0-33.333,9.054-46.319,32.29-46.319 c25.306,0,27.317,20.818,27.317,48.034v97.204c0,2.762,2.238,5,5,5H305c2.762,0,5-2.238,5-5V194.995 C310,145.43,300.549,94.761,230.454,94.761z"></path>
                        </svg>
                    </a>
                </div>

                <div class="text-[#7a7a7a] text-[13px] text-center">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech.</p>
                </div>
            </div>
        </div>
    </section>
</footer>

</body>
</html>
