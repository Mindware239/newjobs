<?php
$base = $base ?? '/';

// Fetch latest blogs (keeping existing PHP logic)
$db = \App\Core\Database::getInstance();
$latestBlogs = [];
try {
    $latestBlogs = $db->fetchAll("SELECT * FROM blogs WHERE published_at IS NOT NULL ORDER BY published_at DESC LIMIT 2");
} catch (\Exception $e) {
    // Fail silently
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>About Us | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#7e3aecff', // indigo-600
                        secondary: '#eef2ff', // indigo-50
                        accent: '#6c6ed8ff', // indigo-500
                    }
                }
            }
        }
    </script>

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <style>
        /* Fix header visibility - User reported issue */
        header {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        html, body {
            overflow-x: hidden;
            width: 100%;
        }
        .container {
            width: 100%;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            margin-left: auto;
            margin-right: auto;
            max-width: 1280px;
        }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-white text-gray-800 antialiased" x-data="{ loaded: false }" x-init="setTimeout(() => { loaded = true; AOS.init({once: true}); }, 100)">
    <?php require 'include/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-blue-600 text-white pt-32 pb-20 lg:pt-40 lg:pb-32">
        <!-- Abstract Shapes -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-20 left-20 w-72 h-72 rounded-full bg-white blur-3xl"></div>
            <div class="absolute bottom-20 right-20 w-96 h-96 rounded-full bg-purple-300 blur-3xl"></div>
        </div>
        
        <div class="relative container mx-auto px-4 text-center z-10" data-aos="fade-up">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md rounded-full px-5 py-2 mb-8 border border-white/20">
                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="text-black font-medium text-sm">Trusted by 100,000+ Professionals</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight tracking-tight">
                About Us
            </h1>
            
            <p class="text-lg md:text-xl text-indigo-50 max-w-2xl mx-auto leading-relaxed mb-10">
                A global job portal connecting exceptional talent with trusted employers worldwide. 
                Transforming careers and empowering businesses since 2020.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/jobs" class="inline-flex items-center justify-center h-12 px-8 rounded-lg bg-white text-indigo-700 hover:bg-indigo-50 font-bold transition-all transform hover:-translate-y-1 shadow-lg">
                    Find Jobs
                </a>
                <a href="/register-employer" class="inline-flex items-center justify-center h-12 px-8 rounded-lg border border-white/30 text-white hover:bg-white/10 font-bold transition-all transform hover:-translate-y-1">
                    Post a Job
                </a>
            </div>
        </div>

        <!-- Wave Decoration -->
        <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-[calc(100%+1.3px)] h-[60px] md:h-[120px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-white"></path>
            </svg>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="relative z-20 -mt-16 container mx-auto px-4 mb-20">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <!-- Stat 1 -->
            <div class="bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 text-center group border border-gray-100" data-aos="fade-up" data-aos-delay="0">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">50K+</div>
                <div class="text-gray-500 text-sm md:text-base font-medium">Jobs Posted</div>
            </div>
            <!-- Stat 2 -->
            <div class="bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 text-center group border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">10K+</div>
                <div class="text-gray-500 text-sm md:text-base font-medium">Companies</div>
            </div>
            <!-- Stat 3 -->
            <div class="bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 text-center group border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">100K+</div>
                <div class="text-gray-500 text-sm md:text-base font-medium">Job Seekers</div>
            </div>
            <!-- Stat 4 -->
            <div class="bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 text-center group border border-gray-100" data-aos="fade-up" data-aos-delay="300">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">25K+</div>
                <div class="text-gray-500 text-sm md:text-base font-medium">Successful Hires</div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-20 bg-white overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Content -->
                <div class="order-2 lg:order-1" data-aos="fade-right">
                    <div class="inline-flex items-center gap-2 bg-indigo-50 rounded-full px-4 py-2 mb-6">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-indigo-600 text-sm font-bold uppercase tracking-wide">Our Mission</span>
                    </div>
                    
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 leading-tight">
                        Empowering Careers,<br />
                        <span class="text-indigo-600">Simplifying Hiring</span>
                    </h2>
                    
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        Mindware Infotech Job Portal is built to connect skilled professionals with trusted 
                        employers across the globe. Our platform focuses on transparency, verified hiring, 
                        and long-term career growth.
                    </p>
                    
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        From fresh graduates to experienced professionals, we help candidates showcase 
                        their skills confidently while enabling employers to hire faster, smarter, 
                        and more securely.
                    </p>
                    
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-5 py-3 border border-gray-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-900 font-semibold">Quality Jobs</span>
                        </div>
                        <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-5 py-3 border border-gray-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-gray-900 font-semibold">Resume Builder</span>
                        </div>
                        <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-5 py-3 border border-gray-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span class="text-gray-900 font-semibold">Top Talents</span>
                        </div>
                    </div>
                </div>

                <!-- Abstract Visual -->
                <div class="order-1 lg:order-2 relative" data-aos="fade-left">
                    <div class="relative">
                        <!-- Decorative Blurs -->
                        <div class="absolute -top-6 -left-6 w-32 h-32 bg-indigo-200/50 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-6 -right-6 w-40 h-40 bg-purple-200/50 rounded-full blur-2xl"></div>
                        
                        <div class="relative grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Card 1 -->
                            <div class="bg-gradient-to-br from-indigo-600 to-indigo-500 rounded-3xl p-8 text-white h-64 md:h-72 flex flex-col justify-end shadow-xl transform hover:-translate-y-2 transition-transform duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe w-10 h-10 mb-4 opacity-80"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                <h4 class="font-bold text-xl">Global Network</h4>
                                <p class="text-indigo-100 text-sm mt-1">Connecting talent worldwide</p>
                            </div>
                            
                            <!-- Card 2 -->
                            <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 h-64 md:h-72 flex flex-col justify-end transform hover:-translate-y-2 transition-transform duration-300">
                                <svg class="w-10 h-10 mb-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <h4 class="font-bold text-xl text-gray-900">Verified Employers</h4>
                                <p class="text-gray-500 text-sm mt-1">100% trusted companies</p>
                            </div>
                            
                            <!-- Card 3 (Wide) -->
                            <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 h-auto min-h-[12rem] flex flex-col justify-center col-span-1 md:col-span-2 transform hover:-translate-y-2 transition-transform duration-300">
                                <div class="flex items-center gap-6">
                                    <div class="flex -space-x-4">
                                        <div class="w-12 h-12 rounded-full border-4 border-white bg-gray-200"></div>
                                        <div class="w-12 h-12 rounded-full border-4 border-white bg-gray-300"></div>
                                        <div class="w-12 h-12 rounded-full border-4 border-white bg-gray-400"></div>
                                        <div class="w-12 h-12 rounded-full border-4 border-white bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600">+2K</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-3xl text-gray-900">25,000+</div>
                                        <div class="text-gray-500 font-medium">Successful placements</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 md:py-28 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <div class="inline-flex items-center gap-2 bg-indigo-50 rounded-full px-4 py-2 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target w-4 h-4 text-primary"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                    <span class="text-indigo-600 text-sm font-semibold">How It Works</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                    Your Journey to Success
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    A simple and effective hiring journey for candidates and employers worldwide.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="group relative" data-aos="fade-up" data-aos-delay="0">
                    <div class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 h-full relative overflow-hidden border border-gray-100">
                        <div class="absolute top-4 right-4 text-6xl font-black text-gray-50 group-hover:text-indigo-50 transition-colors select-none">01</div>
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 relative z-10">Create Account</h3>
                        <p class="text-gray-600 leading-relaxed relative z-10">Register as a candidate or employer in minutes with our simple signup process.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="group relative" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 h-full relative overflow-hidden border border-gray-100">
                        <div class="absolute top-4 right-4 text-6xl font-black text-gray-50 group-hover:text-indigo-50 transition-colors select-none">02</div>
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 relative z-10">Build Profile</h3>
                        <p class="text-gray-600 leading-relaxed relative z-10">Create your professional profile or post jobs with our intuitive tools.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="group relative" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 h-full relative overflow-hidden border border-gray-100">
                        <div class="absolute top-4 right-4 text-6xl font-black text-gray-50 group-hover:text-indigo-50 transition-colors select-none">03</div>
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target w-8 h-8 text-primary"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 relative z-10">Discover Matches</h3>
                        <p class="text-gray-600 leading-relaxed relative z-10">Our AI matches you with perfect opportunities or top talent automatically.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="group relative" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 h-full relative overflow-hidden border border-gray-100">
                        <div class="absolute top-4 right-4 text-6xl font-black text-gray-50 group-hover:text-indigo-50 transition-colors select-none">04</div>
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big w-8 h-8 text-primary"><path d="M21.801 10A10 10 0 1 1 17 3.335"></path><path d="m9 11 3 3L22 4"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 relative z-10">Get Hired</h3>
                        <p class="text-gray-600 leading-relaxed relative z-10">Connect directly and land your dream job or hire the best candidates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div data-aos="fade-right">
                    <div class="inline-flex items-center gap-2 bg-indigo-50 rounded-full px-4 py-2 mb-6">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <span class="text-indigo-600 text-sm font-semibold">Our Values</span>
                    </div>
                    
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 leading-tight">
                        We're Only Working<br />
                        <span class="text-indigo-600">With The Best</span>
                    </h2>
                    
                    <p class="text-lg text-gray-600 leading-relaxed mb-10">
                        We collaborate with trusted employers and empower candidates through verified 
                        jobs, professional tools, and global opportunities.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group border border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-2">Trust & Transparency</h4>
                            <p class="text-sm text-gray-500">We verify all employers and ensure genuine job listings.</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group border border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe w-6 h-6 text-primary"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-2">Global Reach</h4>
                            <p class="text-sm text-gray-500">Connect with opportunities worldwide across diverse industries.</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group border border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-2">Career Growth</h4>
                            <p class="text-sm text-gray-500">Access tools and guidance to accelerate your professional journey.</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group border border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award w-6 h-6 text-primary"><path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path><circle cx="12" cy="8" r="6"></circle></svg>
                            </div>
                            <h4 class="font-bold text-gray-900 mb-2">Quality First</h4>
                            <p class="text-sm text-gray-500">We prioritize quality matches over quantity for better outcomes.</p>
                        </div>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-left">
                    <div class="grid grid-cols-1 md:grid-cols-5 md:grid-rows-4 gap-4 h-auto md:h-[500px]">
                        <!-- Large Blue Card -->
                        <div class="md:col-span-3 md:row-span-4 h-80 md:h-auto bg-indigo-50 rounded-3xl relative overflow-hidden shadow-2xl p-8 flex flex-col justify-end">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></div>
                                    <span class="text-white/90 text-sm font-medium">Live hiring</span>
                                </div>
                                <div class="text-white font-bold text-2xl md:text-3xl leading-tight">Find Your Dream Job Today</div>
                            </div>
                        </div>
                        
                        <!-- Satisfaction Card -->
                        <div class="md:col-span-2 md:row-span-2 h-40 md:h-auto bg-white rounded-3xl shadow-xl border border-gray-100 p-6 flex flex-col justify-center">
                            <svg class="w-8 h-8 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <div class="text-3xl font-bold text-gray-900">98%</div>
                            <div class="text-gray-500 text-sm">Satisfaction Rate</div>
                        </div>
                        
                        <!-- Countries Card -->
                        <div class="md:col-span-2 md:row-span-2 h-40 md:h-auto bg-gray-50 rounded-3xl shadow-xl p-6 flex flex-col justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe w-6 h-6 text-primary"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                            <div class="text-3xl font-bold">150+</div>
                            <div class="text-white/90 text-sm">Countries Reached</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative overflow-hidden py-20 md:py-28 bg-indigo-200">
        <!-- Abstract Shapes -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute top-10 right-10 w-64 h-64 rounded-full bg-white blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-80 h-80 rounded-full bg-purple-400 blur-3xl"></div>
        </div>
        
        <div class="relative container mx-auto px-4 text-center z-10" data-aos="zoom-in">
            <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-6">
                Ready to Transform Your Career?
            </h2>
            <p class="text-indigo-600 text-lg max-w-2xl mx-auto mb-10">
                Join thousands of professionals who have found their dream jobs through our platform.
                Start your journey today.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/register-candidate" class="inline-flex items-center justify-center h-14 px-10 rounded-xl bg-white text-indigo-700 hover:bg-gray-100 font-bold text-lg transition-all transform hover:-translate-y-1 shadow-lg">
                    Get Started Free
                </a>
                <a href="/contact" class="inline-flex items-center justify-center h-14 px-10 rounded-xl border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold text-lg transition-all transform hover:-translate-y-1">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>

    <?php require 'include/footer.php'; ?>
</body>
</html>
