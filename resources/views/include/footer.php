</main>
<?php
$base = $base ?? '/';
$careerLocations = [];
$careerDepartments = [];
try {
    $careerLocations = \App\Models\Career::locations(true, 6);
    $careerDepartments = \App\Models\Career::departments(true, 6);
} catch (\Throwable $e) {
    error_log('Footer careers links failed: ' . $e->getMessage());
}
?>

<footer class="bg-[#fdfdfd] border-t border-gray-100 pt-16 pb-6 font-sans overflow-hidden">
    <div class="container mx-auto px-6 lg:px-[7.5rem]">
        <!-- ══════════════════════════════
             TOP SECTION: MULTI-COLUMN GRID
        ══════════════════════════════ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-10 lg:gap-8 mb-16">
            
            <!-- Column 1: Branding (Spans 2 on Mobile/Tablet for better flow) -->
            <div class="sm:col-span-2 lg:col-span-2 flex flex-col items-center md:items-start text-center md:text-left">
                <a href="<?= $base ?>" class="inline-block mb-5 transition-transform hover:scale-105 duration-300">
                    <img src="<?= $base ?>uploads/jobsence.png" alt="jobsence" class="h-14 w-auto object-contain" />
                </a>
                <p class="text-gray-500 text-sm leading-relaxed mb-6 max-w-xs">
                    Jobsence connects talented professionals with trusted employers across India. Empowering careers since 2025.
                </p>
            </div>

            <!-- Column 2: For Job Seekers -->
            <div>
                <h5 class="text-slate-900 font-bold mb-6 text-[15px] tracking-tight uppercase">Job Seekers</h5>
                <ul class="space-y-3">
                    <li><a href="<?= $base ?>jobs" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Browse Jobs</a></li>
                    <li><a href="<?= $base ?>blog" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Career Resources</a></li>
                    <li><a href="<?= $base ?>candidate/resume/builder" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Resume Builder</a></li>
                    <li><a href="<?= $base ?>skill-development" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Skill Development / कौशल विकास</a></li>
                    <li><a href="<?= $base ?>skills" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Skills &amp; Mentors</a></li>
                    <li><a href="<?= $base ?>blog/category/interview-questions" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Interview Tips</a></li>
                </ul>
            </div>

            <!-- Column 3: For Employers -->
            <div>
                <h5 class="text-slate-900 font-bold mb-6 text-[15px] tracking-tight uppercase">Employers</h5>
                <ul class="space-y-3">
                    <li><a href="<?= $base ?>employer/jobs/create" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Post a Job</a></li>
                    <li><a href="<?= $base ?>employer/applications" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Browse Resumes</a></li>
                    <li><a href="<?= $base ?>employer/dashboard" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Employer Dashboard</a></li>
                    <li><a href="<?= $base ?>employer/job-posting" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Recruitment Solutions</a></li>
                    <li><a href="<?= $base ?>employer/job-posting" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Pricing Plans</a></li>
                </ul>
            </div>

            <!-- Column 4: Careers Into Jobsence -->
            <div>
                <h5 class="text-slate-900 font-bold mb-6 text-[15px] tracking-tight uppercase">Company</h5>
                <ul class="space-y-3">
                    <li><a href="<?= $base ?>careers?type=internal" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Internal Hiring</a></li>
                    <li><a href="<?= $base ?>careers" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Work With Us</a></li>
                    <li><a href="<?= $base ?>careers" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Join Our Team</a></li>
                    <li><a href="<?= $base ?>careers" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Jobsence Openings</a></li>
                    <li><a href="<?= $base ?>apply/internship" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Internship Program</a></li>
                </ul>
            </div>

            <!-- Column 5: Categories -->
            <div>
                <h5 class="text-slate-900 font-bold mb-6 text-[15px] tracking-tight uppercase">Categories</h5>
                <ul class="space-y-3">
                    <li><a href="<?= $base ?>jobs?category=it-software" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">IT & Software</a></li>
                    <li><a href="<?= $base ?>jobs?category=sales-marketing" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Sales & Marketing</a></li>
                    <li><a href="<?= $base ?>jobs?category=accounting-finance" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Accounting & Finance</a></li>
                    <li><a href="<?= $base ?>jobs?category=hr-admin" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">HR & Admin</a></li>
                    <li><a href="<?= $base ?>jobs?category=customer-support" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Customer Support</a></li>
                    <li><a href="<?= $base ?>jobs?category=engineering" class="text-gray-500 hover:text-primary transition-all duration-200 text-sm font-medium hover:pl-1">Engineering</a></li>
                </ul>
            </div>
        </div>

        <!-- ══════════════════════════════
             MIDDLE SECTION: LOCATIONS & NEWSLETTER
        ══════════════════════════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 py-12 border-y border-gray-100">
            <!-- Newsletter (4 cols) -->
            <div class="lg:col-span-5">
                <h5 class="text-slate-900 font-bold mb-3">Newsletter Subscription</h5>
                <p class="text-gray-500 text-sm mb-6">Stay updated with the latest job opportunities and career tips.</p>
                <form action="<?= $base ?>newsletter/subscribe" method="POST" class="flex flex-col sm:flex-row gap-3">
                    <input type="email" name="email" required placeholder="Enter your email" 
                           class="flex-1 bg-gray-50 border border-gray-200 rounded-xl py-3.5 px-5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all duration-300" />
                    <button type="submit" class="sm:w-auto w-full px-8 py-3.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-hover shadow-lg shadow-primary/20 transition-all duration-300">
                        Subscribe
                    </button>
                </form>
            </div>

            <!-- Job Locations (Dynamic) (7 cols) -->
            <div class="lg:col-span-7 lg:pl-10">
                <h5 class="text-slate-900 font-bold mb-6">Careers in Jobsence</h5>
                <div class="flex flex-wrap gap-x-6 gap-y-3">
                    <?php if (!empty($careerLocations)): ?>
                        <?php foreach ($careerLocations as $item): ?>
                            <?php $location = trim((string)($item['location'] ?? '')); ?>
                            <?php if ($location !== ''): ?>
                                <a href="<?= $base ?>careers?location=<?= urlencode($location) ?>" class="text-gray-500 hover:text-primary transition-colors duration-200 text-[13.5px] font-medium border-b border-transparent hover:border-primary">
                                    Jobs in <?= htmlspecialchars($location) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php 
                        $staticLocs = ['Bangalore', 'Delhi/NCR', 'Noida', 'Gurgaon', 'Mumbai', 'Hyderabad'];
                        foreach($staticLocs as $sl): 
                        ?>
                            <a href="<?= $base ?>jobs-in-<?= strtolower(str_replace('/', '-', $sl)) ?>" class="text-gray-500 hover:text-primary transition-colors duration-200 text-[13.5px] font-medium border-b border-transparent hover:border-primary"><?= $sl ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════
             BOTTOM FOOTER: LINKS & COPYRIGHT
        ══════════════════════════════ -->
        <div class="pt-10 pb-4">
            <!-- Professional Responsive Bottom Bar -->
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                
                <!-- Bottom Links (Center on Mobile, Left on Desktop) -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-6 gap-y-4 text-[13px] font-medium text-gray-500">
                    <a href="<?= $base ?>about" class="hover:text-primary transition-colors whitespace-nowrap">About</a>
                    <a href="<?= $base ?>employer/job-posting" class="hover:text-primary transition-colors whitespace-nowrap">Pricing</a>
                    <a href="<?= $base ?>jobs" class="hover:text-primary transition-colors whitespace-nowrap">Jobs</a>
                    <a href="<?= $base ?>contact" class="hover:text-primary transition-colors whitespace-nowrap">Contact</a>
                    <a href="<?= $base ?>terms" class="hover:text-primary transition-colors whitespace-nowrap">Terms</a>
                    <a href="<?= $base ?>privacy" class="hover:text-primary transition-colors whitespace-nowrap">Privacy</a>
                    <a href="<?= $base ?>refund-cancellation-policy" class="hover:text-primary transition-colors whitespace-nowrap">Refunds</a>
                    <a href="<?= $base ?>blog" class="hover:text-primary transition-colors whitespace-nowrap" target="_blank">Blog</a>
                    <a href="<?= $base ?>sitemap.xml" class="hover:text-primary transition-colors whitespace-nowrap">Sitemap</a>
                    <a href="<?= $base ?>grievances" class="hover:text-primary transition-colors whitespace-nowrap">Grievances</a>
                </div>

                <!-- Copyright (Center on Mobile, Right on Desktop) -->
                <div class="text-gray-400 text-[13px] font-medium text-center lg:text-right">
                    © <?= date('Y'); ?> <span class="text-slate-800 font-bold">Jobsence</span>. All Rights Reserved.
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ══════════════════════════════
     INTERACTIVE ELEMENTS
══════════════════════════════ -->

<!-- Premium Back to Top Button -->
<button id="backToTop" aria-label="Back to top" class="fixed bottom-6 right-6 w-11 h-11 bg-white border border-gray-100 text-primary rounded-xl shadow-2xl flex items-center justify-center translate-y-24 opacity-0 transition-all duration-500 hover:bg-primary hover:text-white group z-50">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Back to Top functionality
        const backToTopBtn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.classList.remove('translate-y-24', 'opacity-0');
            } else {
                backToTopBtn.classList.add('translate-y-24', 'opacity-0');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Mobile menu toggle (preserved logic)
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        if (menuButton && mobileMenu) {
            menuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
</body>
</html>
