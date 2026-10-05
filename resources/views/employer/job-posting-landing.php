<?php
/**
 * @var array $stats
 * @var array $plans
 * @var array $testimonials
 * @var array $blogs
 * @var array $companyLogos
 * @var string $base
 */
$base = $base ?? '/';
$assetUrl = static function (?string $url) use ($base): string {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }
    if (function_exists('fix_url')) {
        return fix_url($url);
    }
    $url = str_replace('\\', '/', $url);
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    return rtrim($base, '/') . '/' . ltrim($url, '/');
};
$excerptText = static function (array $row, int $limit = 150): string {
    $text = trim(strip_tags((string)($row['excerpt'] ?? '')));
    if ($text === '') {
        $text = trim(strip_tags((string)($row['meta_description'] ?? '')));
    }
    if ($text === '') {
        $text = trim(strip_tags((string)($row['content'] ?? '')));
    }
    return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
};

/**
 * Format large numbers to readable strings (K, Cr, L)
 */
$formatNum = static function ($num, $suffix = '+'): string {
    $num = (float)$num;
    if ($num >= 10000000) {
        return round($num / 10000000, 1) . ' Cr' . $suffix;
    }
    if ($num >= 100000) {
        return round($num / 100000, 1) . ' L' . $suffix;
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K' . $suffix;
    }
    return (string)$num;
};

$candCount = $formatNum($stats['candidates'] ?? 0);
$empCount = $formatNum($stats['employers'] ?? 0);
?>

<!-- Swiper.js & Google Fonts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    [x-cloak] { display: none !important; }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* ── MARQUEE ANIMATION ── */
    @keyframes marquee {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 40s linear infinite;
        width: max-content;
    }

    /* ── HERO FLOAT ── */
    @keyframes float {
        0%,100% { transform: translateY(0); }
        50%      { transform: translateY(-14px); }
    }
    .animate-float { animation: float 5s ease-in-out infinite; }

    /* ── FADE IN ── */
    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .animate-fadeup { animation: fadeUp .7s ease forwards; }

    /* ── SWIPER PAGINATION ── */
    .swiper-pagination-bullet-active {
        background: #ff5a36 !important;
        width: 24px !important;
        border-radius: 4px !important;
    }

    /* ── PRICING CARD HOVER ── */
    .plan-card { transition: all .3s ease; }
    .plan-card:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(255,90,54,0.12); }

    /* ── FAQ ANSWER TRANSITION ── */
    .faq-answer { overflow:hidden; transition: max-height .35s ease, opacity .35s ease; max-height:0; opacity:0; }
    .faq-answer.open { max-height:400px; opacity:1; }

    /* ── HERO GRADIENT BG ── */
    .hero-gradient {
        background: linear-gradient(180deg, #fff1ed 0%, #fff8f5 100%);
        position: relative;
    }
    .hero-glow {
        position: absolute;
        top: -10%;
        right: -10%;
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(255, 90, 54, 0.08) 0%, transparent 70%);
        filter: blur(100px);
        z-index: 1;
        pointer-events: none;
    }
    .hero-gradient::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: #fff8f5;
        clip-path: ellipse(85% 100% at 50% 100%);
        z-index: 5;
    }

    /* ── GLASSMORPHISM ── */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* ── FLOATING CARD ── */
    @keyframes float-slow {
        0%, 100% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-15px) translateX(5px); }
    }
    .animate-float-slow {
        animation: float-slow 8s ease-in-out infinite;
    }

    /* ── BUTTON LIFT ── */
    .btn-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .btn-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(255, 90, 54, 0.25);
    }
    .btn-lift::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.2);
        transition: left 0.3s ease;
    }
    .btn-lift:hover::before {
        left: 100%;
    }

    /* ── SECTION LABEL ── */
    .section-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #ff5a36;
    }

    /* ── FEATURE CARDS ── */
    .callback-banner {
        background: linear-gradient(90deg, #fff3f0 0%, #fff9f6 100%);
        border: 1px solid #ff5a3620;
        position: relative;
    }
    .callback-banner-img {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 120%;
        width: auto;
        object-contain: cover;
        pointer-events: none;
        z-index: 1;
    }
    .feature-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
    }

    /* ── TABLE STYLES ── */
    .comparison-table tbody tr:nth-child(odd) {
        background: #fafbfc;
    }
    .comparison-table tbody tr:hover {
        background: #f0f9ff;
    }
    .col-premium { background: #fff8f6 !important; }
    .col-premium:hover { background: #fff0e6 !important; }

    /* ── BLOG CARDS ── */
    .blog-card {
        transition: all 0.4s ease;
    }
    .blog-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    .blog-thumb { transition: transform .4s ease; }
    .blog-card:hover .blog-thumb { transform: scale(1.06); }

    /* ── TESTIMONIAL CARDS ── */
    .testimonial-card {
        transition: all 0.4s ease;
    }
    .testimonial-card:hover {
        box-shadow: 0 20px 60px rgba(255,90,54,0.1);
    }

    /* ── GRADIENT TEXT ── */
    .gradient-text {
        background: linear-gradient(135deg, #ff5a36 0%, #ff8066 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Responsive fixes */
    @media (max-width: 768px) {
        .hero-gradient { min-height: 500px; }
        .pricing-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div x-data="employerLanding()" x-cloak class="bg-white font-['Plus_Jakarta_Sans'] text-[#1a1a2e] overflow-x-hidden">

    <!-- ══════════════════════════════════════════════════════
         1. HERO SECTION
         ══════════════════════════════════════════════════════ -->
    <section class="hero-gradient pt-24 pb-16 lg:pt-36 lg:pb-52 overflow-hidden relative min-h-0 lg:min-h-[820px] flex items-center">
        <div class="hero-glow"></div>
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8 relative z-10 w-full">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-24">

                <!-- LEFT: TEXT CONTENT -->
                <div class="lg:w-[52%] text-center lg:text-left animate-fadeup relative z-20 order-1">
                    <div class="inline-flex items-center gap-2.5 bg-orange-50/80 backdrop-blur-sm rounded-full px-6 py-2.5 border border-orange-100 mb-8 shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ff5a36] animate-pulse"></span>
                        <span class="text-[11px] font-black text-[#ff5a36] uppercase tracking-[0.2em]">India's #1 Job Posting Platform</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-[72px] font-[900] leading-[1.08] text-gray-900 mb-8 tracking-tight max-w-[900px]">
                        Post Jobs on Jobsence – <br class="hidden lg:block">
                        India's #1 Leading Job Posting Platform
                    </h1>

                    <p class="text-lg lg:text-2xl text-gray-500 font-medium mb-12 max-w-2xl mx-auto lg:mx-0 leading-relaxed opacity-90">
                        Create &amp; quickly publish job listings in just <strong class="text-gray-900 font-[800]">2 minutes</strong>. Reach <strong class="text-gray-900 font-[800]"><?= $candCount ?> verified candidates</strong> across all industries, roles &amp; geographies.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                        <a href="/employer/jobs/create"
                           style="background-color: #ff5a36 !important; color: #ffffff !important;"
                           class="btn-lift px-10 py-4 font-bold text-[17px] rounded-full shadow-[0_20px_40px_rgba(255,90,54,0.25)] hover:opacity-95 transition-all w-full sm:w-auto text-center whitespace-nowrap">
                            Post a free job
                        </a>
                        <a href="#pricing"
                           style="background-color: #ffffff !important; color: #ff5a36 !important; border: 2px solid #ff5a36 !important;"
                           class="btn-lift px-10 py-4 font-bold text-[17px] rounded-full hover:bg-orange-50 w-full sm:w-auto text-center whitespace-nowrap transition-all">
                            Explore plans
                        </a>
                    </div>
                </div>

                <!-- RIGHT: IMAGE & ANALYTICS CARD -->
                <div class="lg:w-[48%] relative w-full flex justify-center lg:justify-end items-center mt-12 lg:mt-0 order-2">
                    <div class="relative w-full max-w-[420px] lg:max-w-[620px] lg:scale-110 xl:scale-125 origin-center lg:origin-right">
                        
                        <!-- Main Person Image -->
                        <div class="relative z-20">
                            <img src="/assets/images/banner (2).png" 
                                 class="w-full max-w-[280px] sm:max-w-[320px] lg:max-w-[780px] h-auto object-contain mx-auto drop-shadow-[0_25px_60px_rgba(0,0,0,0.12)]"
                                 alt="Recruitment Success">
                        </div>

                        <!-- Floating Response Analytics Card -->
                        <div class="absolute -left-28 top-1/4 bg-white/95 rounded-[36px] shadow-[0_50px_120px_rgba(0,0,0,0.18)] p-10 w-80 animate-float-slow z-30 hidden xl:block border border-white/50 backdrop-blur-2xl">
                            <div class="mb-10">
                                <p class="text-[11px] font-[900] text-gray-400 uppercase tracking-[0.2em] mb-4">Total Responses</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-6xl font-black text-gray-900"><?= $formatNum(($stats['interviews'] ?? 0) + ($stats['jobs'] ?? 0), '') ?></span>
                                    <div class="w-16 h-16 rounded-[24px] bg-orange-50 flex items-center justify-center text-[#ff5a36] shadow-inner border border-white">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-8">
                                <?php 
                                $displayCandidates = [];
                                if (!empty($recentCandidates)) {
                                    foreach ($recentCandidates as $idx => $rcan) {
                                        $displayCandidates[] = [
                                            (string)($rcan['name'] ?? 'Candidate'),
                                            $idx === 0 ? '#fff3f0' : ($idx === 1 ? '#eff6ff' : '#f5f3ff'),
                                            $idx === 0 ? '#ff5a36' : ($idx === 1 ? '#2563eb' : '#7c3aed'),
                                            $idx === 0 ? '88%' : ($idx === 1 ? '72%' : '64%'),
                                            $assetUrl($rcan['profile_image'] ?? '')
                                        ];
                                    }
                                } else {
                                    $displayCandidates = [
                                        ['Priyanka Chaudhary','#fff3f0','#ff5a36','88%',''],
                                        ['Ishita Sharma','#eff6ff','#2563eb','72%',''],
                                        ['Vikram Deshmukh','#f5f3ff','#7c3aed','64%','']
                                    ];
                                }
                                foreach($displayCandidates as $r): 
                                    $initial = strtoupper(substr($r[0], 0, 1));
                                ?>
                                <div class="flex items-center gap-5 group">
                                    <?php if(!empty($r[4])): ?>
                                        <img src="<?= htmlspecialchars($r[4]) ?>" class="w-14 h-14 rounded-full object-cover shrink-0 shadow-lg border-2 border-white" alt="<?= htmlspecialchars($r[0]) ?>">
                                    <?php else: ?>
                                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-sm font-black text-white shrink-0 shadow-lg border-2 border-white" style="background:<?= $r[2] ?>"><?= $initial ?></div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center mb-2.5">
                                            <p class="text-[13px] font-[800] text-gray-800"><?= htmlspecialchars($r[0]) ?></p>
                                            <span class="text-[10px] font-black text-[#ff5a36] bg-orange-50 px-2.5 py-1 rounded-md border border-orange-100/50">Shortlist</span>
                                        </div>
                                        <div class="h-2.5 bg-gray-100 rounded-full w-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000 ease-out group-hover:brightness-110" style="background:<?= $r[2] ?>; width:<?= $r[3] ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-10 pt-8 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex -space-x-4">
                                    <?php for($i=1;$i<=4;$i++): ?>
                                    <div class="w-10 h-10 rounded-full border-4 border-white bg-gray-200 overflow-hidden shadow-sm">
                                        <img src="https://i.pravatar.cc/100?u=<?= $i ?>" class="w-full h-full object-cover" />
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs font-black text-gray-400">+120 More</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="relative -mt-40 z-40 mb-20">
        <div class="max-w-[1300px] mx-auto px-6">
            <div class="">
                <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    
                    <a href="#post-type" class="block py-8 lg:py-2 lg:px-14 text-center group" style="text-decoration:none" aria-label="Create and publish a job listing">
                        <div class="w-20 h-20 rounded-[28px] bg-orange-50/50 flex items-center justify-center text-[#ff5a36] mx-auto mb-8 group-hover:scale-110 transition-transform shadow-sm border border-orange-100/50">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-xl font-[800] text-gray-900 mb-3 group-hover:text-[#ff5a36] transition-colors">Create &amp; publish →</h3>
                        <p class="text-base font-medium text-gray-500 leading-snug">Job listings in just <strong class="text-gray-900 font-[800]">2 minutes</strong></p>
                        <span class="pt-cta">Post a job now</span>
                    </a>

                    <div class="py-8 lg:py-2 lg:px-14 text-center group">
                        <div class="w-20 h-20 rounded-[28px] bg-blue-50/50 flex items-center justify-center text-blue-600 mx-auto mb-8 group-hover:scale-110 transition-transform shadow-sm border border-blue-100/50">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="text-xl font-[800] text-gray-900 mb-3 group-hover:text-blue-600 transition-colors"><?= $candCount ?> candidates</h3>
                        <p class="text-base font-medium text-gray-500 leading-snug">Reach across <strong class="text-gray-900 font-[800]">industries &amp; roles</strong></p>
                    </div>

                    <div class="py-8 lg:py-2 lg:px-14 text-center group">
                        <div class="w-20 h-20 rounded-[28px] bg-purple-50/50 flex items-center justify-center text-purple-600 mx-auto mb-8 group-hover:scale-110 transition-transform shadow-sm border border-purple-100/50">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h3 class="text-xl font-[800] text-gray-900 mb-3 group-hover:text-purple-600 transition-colors">Manage in one place</h3>
                        <p class="text-base font-medium text-gray-500 leading-snug"><strong class="text-gray-900 font-[800]">Post, edit, &amp; track</strong> with ease</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- What do you want to post? (full-time / part-time / one-time / near-me service) -->
    <style>
        .pt-cta { display: inline-block; margin-top: 14px; padding: 8px 18px; border-radius: 999px; background: #ff5a36; color: #fff; font-weight: 800; font-size: .9rem; }
        .pt-wrap { max-width: 1300px; margin: 0 auto 70px; padding: 0 24px; scroll-margin-top: 130px; }
        .pt-wrap h2 { font-size: clamp(1.5rem, 3.5vw, 2.1rem); font-weight: 900; color: #111827; text-align: center; margin: 0 0 6px; }
        .pt-wrap > p { text-align: center; color: #6b7280; margin: 0 0 24px; }
        .pt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .pt-opt { display: flex; flex-direction: column; gap: 6px; padding: 22px; border-radius: 20px; border: 2px solid #f3f4f6; background: #fff; text-decoration: none; box-shadow: 0 6px 20px rgba(17, 24, 39, .05); transition: transform .15s, border-color .15s; }
        .pt-opt:hover, .pt-opt:focus-visible { transform: translateY(-3px); border-color: #ff5a36; }
        .pt-opt .ico { font-size: 2rem; }
        .pt-opt b { font-size: 1.1rem; color: #111827; }
        .pt-opt span { color: #6b7280; font-size: .92rem; line-height: 1.4; }
        .pt-opt em { margin-top: auto; padding-top: 10px; font-style: normal; font-weight: 800; color: #ff5a36; }
    </style>
    <section id="post-type" class="pt-wrap" aria-labelledby="post-type-title">
        <h2 id="post-type-title">What do you want to post? <span style="color:#ff5a36">/ आप क्या पोस्ट करना चाहते हैं?</span></h2>
        <p>Pick one – the job form opens with the right type already selected.</p>
        <div class="pt-grid">
            <a class="pt-opt" href="/employer/jobs/create?type=full_time"><span class="ico" aria-hidden="true">💼</span><b>Full-time job</b><span>Regular salaried role – office, factory, shop, MSME, kirana store and more.</span><em>Post full-time →</em></a>
            <a class="pt-opt" href="/employer/jobs/create?type=part_time"><span class="ico" aria-hidden="true">⏰</span><b>Part-time job</b><span>A few hours or days – exhibition staff, promoters, models, bouncers, drivers, helpers.</span><em>Post part-time →</em></a>
            <a class="pt-opt" href="/employer/jobs/create?type=one_time"><span class="ico" aria-hidden="true">🛠️</span><b>One-time job</b><span>A single task – plumber, electrician, carpenter, welder, painter, event help.</span><em>Post one-time job →</em></a>
            <a class="pt-opt" href="/near-me"><span class="ico" aria-hidden="true">📍</span><b>Near-me service required</b><span>Need a plumber, electrician, carpenter, hair dresser or salon near you? Find verified local service providers.</span><em>Find service near me →</em></a>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         2. STATS SECTION
         ══════════════════════════════════════════════════════ -->
    <section class="py-16 bg-gradient-to-r from-gray-50 to-gray-50 border-y border-gray-200">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-10">
                <?php
                $statItems = [
                    [$formatNum($stats['employers'] ?? 0), 'Active Employers'],
                    [$formatNum($stats['jobs'] ?? 0), 'Daily Job Posts'],
                    [$formatNum($stats['candidates'] ?? 0), 'Verified Talent'],
                    [$formatNum($stats['verified_profiles'] ?? 0), 'Identity Verified'],
                ];
                foreach($statItems as $st):
                ?>
                <div class="text-center group hover:scale-105 transition-transform">
                    <p class="text-5xl lg:text-[56px] font-[900] tracking-tight mb-2 text-gray-900 group-hover:text-[#ff5a36] transition-colors"><?= htmlspecialchars($st[0]) ?></p>
                    <p class="text-[11px] font-black text-gray-500 uppercase tracking-widest"><?= htmlspecialchars($st[1]) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         3. PRICING SECTION
         ══════════════════════════════════════════════════════ -->
    <section id="pricing" class="py-24 bg-white">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">

            <div class="text-center mb-16">
                <p class="section-label mb-4">Simple & Transparent</p>
                <h2 class="text-4xl lg:text-5xl font-[900] text-gray-900 mb-4 tracking-tight">Flexible Pricing Plans</h2>
                <p class="text-xl text-gray-600 font-medium max-w-2xl mx-auto">Choose the perfect plan to attract quality candidates and scale your hiring</p>
            </div>

            <!-- Billing Toggle -->
            <div class="flex items-center justify-center gap-8 mb-16 flex-wrap">
                <span class="text-lg font-bold transition-colors" :class="billingCycle==='monthly'?'text-gray-900':'text-gray-400'">Monthly</span>
                <button @click="toggleCycle()"
                        class="relative w-16 h-8 rounded-full bg-gray-200 transition-all p-1 flex items-center hover:bg-gray-300">
                    <div class="w-6 h-6 bg-[#ff5a36] rounded-full shadow-md transform transition-transform duration-400 flex items-center justify-center"
                         :class="billingCycle==='monthly'?'translate-x-0':'translate-x-8'">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></path></svg>
                    </div>
                </button>
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold" :class="billingCycle==='annual'?'text-gray-900':'text-gray-400'">Annual</span>
                    <span class="px-4 py-1.5 bg-gradient-to-r from-green-500 to-green-600 text-white text-[10px] font-black rounded-full uppercase">Save 20%</span>
                </div>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="pricing-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12 mt-20">
                <?php foreach ($plans as $plan): ?>
                <?php
                    $attrs    = is_object($plan) ? $plan->attributes : $plan;
                    $tier     = strtolower($attrs['tier'] ?? 'basic');
                    $isFree   = $tier === 'free';
                    $isPrem   = $tier === 'premium';
                    $isEnt    = $tier === 'enterprise';
                    $planName = htmlspecialchars($attrs['name'] ?? 'Plan');
                    $monthlyPrice = (float)($attrs['price_monthly'] ?? 0);
                    $annualPrice = (float)($attrs['price_annual'] ?? 0);
                    $displayPrice = $monthlyPrice > 0 ? number_format($monthlyPrice) : '0';
                    $displayPriceAnnual = $annualPrice > 0 ? number_format(round($annualPrice/12)) : '0';
                ?>
                <div class="plan-card relative bg-white p-10 flex flex-col rounded-[40px] border-2 <?= $isPrem ? 'border-[#ff5a36] shadow-[0_30px_70px_rgba(255,90,54,0.18)]' : 'border-gray-100 hover:border-gray-200' ?> transition-all">
                    
                    <?php if($isPrem): ?>
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-[#ff5a36] text-white text-[10px] font-[900] uppercase tracking-[0.2em] px-8 py-3 rounded-full shadow-[0_20px_40px_rgba(255,90,54,0.4)] z-50 whitespace-nowrap border-[6px] border-white">
                        ⭐ Most Popular
                    </div>
                    <?php endif; ?>

                    <div class="mb-10">
                        <h3 class="text-3xl font-[900] text-gray-900 mb-2"><?= $planName ?></h3>
                        <?php if($isFree): ?>
                        <p class="text-xs text-green-600 font-black uppercase tracking-[0.1em] mt-2">Always Free</p>
                        <?php else: ?>
                        <div class="mt-4">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-gray-900">₹<?= $monthlyPrice > 0 ? $displayPrice : 'Contact' ?></span>
                                <?php if($monthlyPrice > 0): ?>
                                <span class="text-sm text-gray-500 font-bold">/month</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 font-medium mt-1">*Excl. GST</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Features List -->
                    <div class="flex-1 mb-10">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-[0.08em] mb-5 pb-4 border-b border-gray-200">Key Features</p>
                        <ul class="space-y-3.5">
                            <?php
                            $jobPostings = $attrs['max_job_posts'] ?? 1;
                            $resumeDownloads = $attrs['max_resume_downloads'] ?? 0;
                            
                            $features  = [
                                $isFree ? 'Upto 250 char JD' : 'Unlimited job description',
                                $jobPostings > 5 ? 'Multi-location posting' : 'Single location',
                                $resumeDownloads === -1 ? 'Unlimited applies' : ($resumeDownloads . ' applies/month'),
                                'Validity: ' . ($isFree ? '15' : '90') . ' days',
                            ];
                            if(!$isFree) $features[] = 'View contact details';
                            if($isPrem || $isEnt) { 
                                $features[] = 'Featured on search'; 
                                $features[] = 'Job branding'; 
                            }
                            if($isEnt) {
                                $features[] = 'Dedicated support';
                                $features[] = 'Custom integrations';
                            }
                            
                            foreach($features as $feat):
                            ?>
                            <li class="flex items-start gap-3 text-sm font-semibold text-gray-700">
                                <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></path></svg>
                                </div>
                                <span><?= htmlspecialchars($feat) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Discount Badge for Paid Plans -->
                    <?php if(!$isFree): ?>
                    <div class="flex items-center gap-3 mb-8 bg-orange-50 rounded-2xl px-4 py-3 border border-orange-100">
                        <svg class="w-5 h-5 text-[#ff5a36] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.778 8.222c-4.296-4.296-11.26-4.296-15.556 0A1 1 0 01.808 6.808c4.764-4.763 12.484-4.763 17.248 0a1 1 0 01-1.414 1.414zM14.95 11.05a7 7 0 00-9.9 0 1 1 0 01-1.414-1.414 9 9 0 0112.728 0 1 1 0 01-1.414 1.414zM12.12 13.88a3 3 0 00-4.242 0 1 1 0 01-1.415-1.415 5 5 0 017.072 0 1 1 0 01-1.415 1.415zM9 16a1 1 0 110-2 1 1 0 010 2z"/></path></svg>
                        <span class="text-xs font-black text-[#ff5a36] uppercase tracking-[0.08em]">10% OFF on 5+ posts</span>
                    </div>
                    <?php endif; ?>

                    <!-- CTA Button -->
                    <form action="/employer/select-plan" method="POST" class="flex flex-col gap-3">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="plan_id" value="<?= htmlspecialchars($attrs['id'] ?? '') ?>">
                        <input type="hidden" name="billing_cycle" :value="billingCycle">
                        
                        <div class="flex gap-3">
                            <?php if(!$isFree): ?>
                            <select name="quantity" class="border border-gray-300 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff5a36] w-24 shrink-0 transition-all">
                                <?php for($q=1;$q<=10;$q++): ?>
                                <option value="<?= $q ?>"><?= str_pad($q,2,'0',STR_PAD_LEFT) ?> <?= $q > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php endif; ?>
                            <button type="submit"
                                    style="background-color: <?= $isFree ? '#ffffff' : '#ff5a36' ?> !important; color: <?= $isFree ? '#ff5a36' : '#ffffff' ?> !important; border: 2.5px solid #ff5a36 !important;"
                                    class="flex-1 py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-xl hover:shadow-orange-200/50 hover:-translate-y-0.5">
                                <?= $isFree ? 'Get Started' : 'Buy Now' ?>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pricing Notes -->
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                <p class="text-xs text-gray-600 font-medium leading-relaxed">
                    <strong class="text-gray-900">📋 Important:</strong> Free job postings are available for company accounts with official email domains (new customers only). 
                    One free job per company at a time. Paid plans require job credit consumption within 30 days. 
                    All prices exclude GST.
                </p>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         4. WHY CHOOSE JOBSENCE
         ══════════════════════════════════════════════════════ -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="section-label mb-4">Why Choose Us</p>
                <h2 class="text-4xl lg:text-5xl font-[900] text-gray-900 tracking-tight mb-4">The Modern Standard for Recruiting</h2>
                <p class="text-xl text-gray-600 font-medium max-w-2xl mx-auto">Everything you need to find, attract, and hire the best talent—faster and smarter</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $features = [
                    ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','Fast Hiring','Post jobs and start receiving quality applications in minutes','#fff3f0','#ff5a36'],
                    ['M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z','Verified Talent','Access verified profiles from ' . $candCount . ' candidates','#eff6ff','#2563eb'],
                    ['M13 10V3L4 14h7v7l9-11h-7z','AI Powered','Smart matching engine finds the best fit for your role','#f5f3ff','#7c3aed'],
                    ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Best Value','Affordable pricing with no hidden fees or commitments','#fef9c3','#ca8a04'],
                ];
                foreach($features as $f):
                ?>
                <div class="feature-card group p-8 rounded-2xl bg-white border border-gray-200 hover:border-orange-200">
                    <div class="w-14 h-14 rounded-2xl mb-6 flex items-center justify-center text-white shadow-lg" style="background:<?= $f[4] ?>" >
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $f[0] ?>"/></svg>
                    </div>
                    <h4 class="text-lg font-black text-gray-900 mb-2"><?= $f[1] ?></h4>
                    <p class="text-sm text-gray-600 font-medium leading-relaxed"><?= $f[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         5. COMPARISON TABLE
         ══════════════════════════════════════════════════════ -->
    <section class="py-24 bg-white">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-[900] text-gray-900 tracking-tight">Feature Comparison</h2>
            </div>
            <div class="rounded-3xl shadow-lg border border-gray-200 overflow-hidden">
                <table class="comparison-table w-full text-left">
                    <thead>
                        <tr class="bg-gray-900 text-white">
                            <th class="px-8 py-6 text-xs font-black uppercase tracking-[0.12em]">Features</th>
                            <th class="px-6 py-6 text-xs font-black uppercase tracking-[0.12em] text-center">Standard</th>
                            <th class="px-6 py-6 text-xs font-black uppercase tracking-[0.12em] text-center col-premium bg-[#ff5a36]">Hot Vacancy</th>
                            <th class="px-6 py-6 text-xs font-black uppercase tracking-[0.12em] text-center">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        $comparisonRows = [
                            ['Job Posts per Month','Posting Limit','1 Post','5 Posts','Unlimited'],
                            ['Resume Downloads','Monthly Limit','5','50','Unlimited'],
                            ['Job Description Length','Content','Limited','Unlimited','Unlimited'],
                            ['Location Targeting','Coverage','1 Location','3 Locations','All India'],
                            ['AI Matching','Candidate Quality','Basic','Advanced','Premium'],
                            ['Job Branding','Visibility','No','Yes','Yes'],
                            ['Featured Position','Search Boost','No','Yes','Yes'],
                            ['Support','Assistance','Email','Priority','Dedicated Manager'],
                        ];
                        foreach($comparisonRows as $row):
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-5">
                                <p class="font-black text-gray-900 text-sm"><?= htmlspecialchars($row[0]) ?></p>
                                <p class="text-xs text-gray-500 font-bold mt-0.5"><?= htmlspecialchars($row[1]) ?></p>
                            </td>
                            <?php foreach([$row[2],$row[3],$row[4]] as $ci=>$cell): ?>
                            <td class="px-6 py-5 text-center <?= $ci===1?'col-premium':'' ?>">
                                <span class="font-bold text-sm <?= $ci===1?'text-[#ff5a36]':' text-gray-700' ?>"><?= htmlspecialchars($cell) ?></span>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         6. BLOG SECTION
         ══════════════════════════════════════════════════════ -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="section-label mb-4">Resources</p>
                <h2 class="text-4xl font-[900] text-gray-900 mb-4 tracking-tight">Latest from Jobsence Blog</h2>
                <p class="text-xl text-gray-600 font-medium">Insights, tips and updates about hiring and recruitment</p>
            </div>
            <div class="swiper blogSwiper overflow-visible">
                <div class="swiper-wrapper">
                    <?php
                    $blogPosts = [];
                    if (!empty($blogs) && is_array($blogs)) {
                        foreach ($blogs as $blog) {
                            $blog = is_object($blog) ? $blog->attributes : $blog;
                            $blogPosts[] = [
                                (string)($blog['title'] ?? ''),
                                $excerptText($blog),
                                $assetUrl($blog['featured_image'] ?? ''),
                                $base . 'blog/' . urlencode((string)($blog['slug'] ?? ($blog['id'] ?? ''))),
                                (string)($blog['category_name'] ?? 'Hiring'),
                                !empty($blog['published_at']) ? date('M d, Y', strtotime((string)$blog['published_at'])) : '',
                            ];
                        }
                    }
                    if (!empty($blogPosts)):
                        foreach($blogPosts as $bp):
                    ?>
                    <div class="swiper-slide h-auto">
                        <div class="blog-card h-full bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="overflow-hidden h-[200px] bg-gradient-to-br from-orange-50 to-orange-100">
                                <?php if (!empty($bp[2])): ?>
                                <img src="<?= htmlspecialchars($bp[2]) ?>" class="blog-thumb w-full h-full object-cover" alt="<?= htmlspecialchars($bp[0]) ?>">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-[#ff5a36]">
                                    <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2zM14 4v6h6"/></svg>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="text-xs font-black text-[#ff5a36] uppercase tracking-[0.1em]"><?= htmlspecialchars($bp[4] ?? 'Hiring') ?></span>
                                    <?php if (!empty($bp[5])): ?><span class="text-xs text-gray-400 font-bold"><?= htmlspecialchars($bp[5]) ?></span><?php endif; ?>
                                </div>
                                <h4 class="text-base font-black text-gray-900 mb-3 leading-snug line-clamp-2 group-hover:text-[#ff5a36]"><?= htmlspecialchars($bp[0]) ?></h4>
                                <p class="text-sm text-gray-600 font-medium leading-relaxed mb-4 line-clamp-2"><?= htmlspecialchars($bp[1]) ?></p>
                                <a href="<?= htmlspecialchars($bp[3] ?? '#') ?>" class="inline-flex items-center gap-2 text-sm font-black text-[#ff5a36] hover:gap-3 transition-all">
                                    Read more
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="swiper-slide h-auto">
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                            <p class="text-sm font-bold text-gray-500">Blog posts coming soon. Check back later!</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-center gap-4 mt-10">
                    <button class="blog-prev w-11 h-11 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-[#ff5a36] hover:text-[#ff5a36] hover:bg-orange-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="blog-next w-11 h-11 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-[#ff5a36] hover:text-[#ff5a36] hover:bg-orange-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

   <!-- contact cta -->
    <section class="py-12 px-6 lg:px-8">
        <div class="max-w-[1400px] mx-auto">
            <div class="callback-banner rounded-[32px] px-10 py-12 flex flex-col lg:flex-row items-center justify-center lg:justify-between gap-8 shadow-sm relative overflow-hidden min-h-[160px]">
                
                <!-- Left: Support Person Image (Positioned like reference) -->
                <img src="/assets/images/call-center.jpg" class="callback-banner-img hidden lg:block" alt="Support">

                <!-- Center: Text Content -->
                <div class="relative z-10 text-center flex-1 lg:ml-48">
                    <h3 class="text-2xl lg:text-3xl font-[800] text-gray-900 mb-2 tracking-tight">Not sure which offering is right for you?</h3>
                    <p class="text-base text-gray-600 font-medium">Leave your contact details and we'll get back to you shortly.</p>
                </div>

                <!-- Right: Action Button -->
                <button onclick="document.getElementById('callbackModal').classList.remove('hidden')"
                        style="background-color: #ff5a36 !important; color: #ffffff !important;"
                        class="relative z-10 shrink-0 px-10 py-4 font-[900] text-base rounded-2xl shadow-[0_10px_25px_rgba(255,90,54,0.3)] hover:-translate-y-0.5 transition-all whitespace-nowrap">
                    Request callback
                </button>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         8. TESTIMONIALS
         ══════════════════════════════════════════════════════ -->
    <section class="py-24 bg-white overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="section-label mb-4">Success Stories</p>
                <h2 class="text-4xl lg:text-5xl font-[900] text-gray-900 mb-4 tracking-tight">Here's why recruiters trust us</h2>
                <p class="text-xl text-gray-600 font-medium">Join thousands of companies who've revolutionized their hiring with Jobsence</p>
            </div>
            <div class="swiper testimonialSwiper overflow-visible">
                <div class="swiper-wrapper">
                    <?php if(!empty($testimonials)): ?>
                        <?php foreach($testimonials as $t): ?>
                        <?php
                            $attr = is_object($t) ? $t->attributes : $t;
                            $testimonialName = (string)($attr['name'] ?? 'Client');
                            $testimonialInitial = strtoupper(substr($testimonialName, 0, 1));
                            $testimonialDesig = (string)($attr['designation'] ?? '');
                            $testimonialCompany = (string)($attr['company'] ?? '');
                            $testimonialMessage = (string)($attr['message'] ?? '');
                            $testimonialImage = $assetUrl($attr['image'] ?? '');
                        ?>
                        <div class="swiper-slide h-auto">
                            <div class="testimonial-card h-full p-8 rounded-3xl bg-white border border-gray-200 flex flex-col">
                                <div class="mb-6 flex items-center gap-2">
                                    <?php for($i=0; $i<5; $i++): ?>
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-base text-gray-700 font-medium leading-relaxed mb-8 flex-1 italic">"<?= htmlspecialchars($testimonialMessage) ?>"</p>
                                <div class="flex items-center gap-4 border-t border-gray-200 pt-6">
                                    <?php if(!empty($testimonialImage)): ?>
                                    <img src="<?= htmlspecialchars($testimonialImage) ?>"
                                         class="w-14 h-14 rounded-2xl object-cover shrink-0 border-2 border-orange-100"
                                         alt="<?= htmlspecialchars($testimonialName) ?>">
                                    <?php else: ?>
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#ff5a36] to-[#ff8066] flex items-center justify-center text-white font-black text-lg shrink-0 border-2 border-orange-100">
                                        <?= htmlspecialchars($testimonialInitial) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-base font-black text-gray-900"><?= htmlspecialchars($testimonialName) ?></p>
                                        <?php if(!empty($testimonialDesig)): ?><p class="text-xs text-[#ff5a36] font-black uppercase tracking-[0.08em] mt-0.5"><?= htmlspecialchars($testimonialDesig) ?></p><?php endif; ?>
                                        <?php if(!empty($testimonialCompany)): ?><p class="text-xs text-gray-500 font-bold"><?= htmlspecialchars($testimonialCompany) ?></p><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide h-auto">
                            <div class="h-full p-8 rounded-2xl bg-gray-50 border border-dashed border-gray-300 text-center">
                                <p class="text-sm font-bold text-gray-500">Testimonials coming soon!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-center gap-4 mt-10">
                    <button class="testi-prev w-11 h-11 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-[#ff5a36] hover:text-[#ff5a36] hover:bg-orange-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="testi-next w-11 h-11 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-[#ff5a36] hover:text-[#ff5a36] hover:bg-orange-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         9. TRUSTED COMPANIES
         ══════════════════════════════════════════════════════ -->
    <section class="py-16 bg-gray-50 border-y border-gray-200">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-8 text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-[900] text-gray-900 tracking-tight">
                Trusted by <span class="gradient-text"><?= $empCount ?> Employers</span>
            </h2>
            <p class="text-gray-600 font-medium mt-2">Including startups, SMEs, and Fortune 500 companies</p>
        </div>
        <div class="overflow-hidden">
            <div class="flex flex-nowrap gap-12 items-center justify-center animate-marquee hover:[animation-play-state:paused]">
                <?php
                if(!empty($companyLogos)):
                    foreach(array_merge($companyLogos,$companyLogos) as $logo):
                    $companyName = htmlspecialchars($logo['company_name'] ?? 'Company');
                    $logoUrl = $assetUrl($logo['logo_url'] ?? '');
                ?>
                <div class="shrink-0">
                    <?php if(!empty($logoUrl)): ?>
                    <img src="<?= $logoUrl ?>" alt="<?= $companyName ?>"
                         class="h-12 w-auto object-contain grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300">
                    <?php else: ?>
                    <div class="h-12 px-6 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                        <span class="text-xs font-black text-gray-500"><?= $companyName ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         10. FAQ SECTION
         ══════════════════════════════════════════════════════ -->
    <section class="py-24 bg-white">
        <div class="max-w-[900px] mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-[900] text-gray-900 tracking-tight mb-4">Frequently Asked Questions</h2>
                <p class="text-lg text-gray-600 font-medium">Everything you need to know about Jobsence job posting</p>
            </div>
            <div class="divide-y divide-gray-200 border-t border-gray-200" id="faqAccordion">
                <?php
                $faqs = [
                    ['Who can use the free job posting feature?', 'Free job posting is available for company accounts with official email domains, limited to new customers. Only one free job per company can stay active at a time.'],
                    ['How long does a job post stay active?', 'Free posts stay active for 15 days, while paid posts remain active for 90 days from the date of posting.'],
                    ['Can I edit my job post after posting?', 'Yes, you can edit your job post anytime. Changes will be reflected immediately for new applications.'],
                    ['How do I receive applications?', 'Applications appear in your dashboard in real-time. You can shortlist, reject, or contact candidates directly.'],
                    ['What is the refund policy?', 'All job posting purchases are non-refundable as per our terms. However, you can repost unused credits.'],
                    ['Do I get support with hiring?', 'Yes, our support team is available via email and priority chat during business hours to assist you.'],
                    ['Can I post multiple jobs?', 'Absolutely! Choose a plan with multiple postings and manage all jobs from one dashboard.'],
                    ['How are candidates verified on Jobsence?', 'All candidates are verified through Aadhaar, PAN, and other identity documents for quality assurance.'],
                ];
                foreach($faqs as $i=>$faq):
                ?>
                <div class="faq-item py-6 group">
                    <button type="button" class="w-full flex items-center justify-between gap-6 text-left" onclick="toggleFaq(<?= $i ?>)" aria-controls="faq-answer-<?= $i ?>">
                        <span class="text-lg font-black text-gray-900 pr-4 group-hover:text-[#ff5a36] transition-colors"><?= htmlspecialchars($faq[0]) ?></span>
                        <div id="faq-icon-<?= $i ?>" class="w-6 h-6 flex items-center justify-center text-gray-400 shrink-0 transition-all duration-300 group-hover:text-[#ff5a36]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12M6 12h12"/></svg>
                        </div>
                    </button>
                    <div class="faq-answer" id="faq-answer-<?= $i ?>">
                        <p class="text-base text-gray-600 font-medium leading-relaxed pt-5 pr-12"><?= htmlspecialchars($faq[1]) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</div>

<!-- ══════════════════════════════════════════════════════
     CALLBACK MODAL
     ══════════════════════════════════════════════════════ -->
<div id="callbackModal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-md px-4 py-6"
     onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative animate-fadeup">
        <button onclick="document.getElementById('callbackModal').classList.add('hidden')"
                class="absolute top-5 right-5 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-700 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 class="text-2xl font-[900] text-gray-900 mb-2">Request a Callback</h3>
        <p class="text-sm text-gray-600 font-medium mb-8">Our team will get back to you within 2 hours</p>
        <form action="/employer/callback" method="POST" class="space-y-5">
            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-wider mb-2 block">Full Name *</label>
                <input type="text" name="name" placeholder="John Doe" required
                       class="w-full border-2 border-gray-200 rounded-xl px-5 py-3.5 text-sm font-medium placeholder-gray-400 focus:outline-none focus:border-[#ff5a36] transition-colors">
            </div>
            <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-wider mb-2 block">Phone Number *</label>
                <input type="tel" name="phone" placeholder="+91 98765 43210" required
                       class="w-full border-2 border-gray-200 rounded-xl px-5 py-3.5 text-sm font-medium placeholder-gray-400 focus:outline-none focus:border-[#ff5a36] transition-colors">
            </div>
            <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-wider mb-2 block">Company Name</label>
                <input type="text" name="company" placeholder="Your company name"
                       class="w-full border-2 border-gray-200 rounded-xl px-5 py-3.5 text-sm font-medium placeholder-gray-400 focus:outline-none focus:border-[#ff5a36] transition-colors">
            </div>
            <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-[#ff5a36] to-[#ff7e5f] text-white font-black text-sm rounded-xl hover:shadow-xl hover:shadow-orange-200 transition-all uppercase tracking-wide mt-6">
                Request Callback
            </button>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    /* FAQ Accordion Toggle */
    function toggleFaq(idx) {
        const answer = document.getElementById('faq-answer-' + idx);
        const icon   = document.getElementById('faq-icon-' + idx);
        if (!answer || !icon) return;
        const isOpen = answer.classList.contains('open');

        // Close all FAQs
        document.querySelectorAll('.faq-answer').forEach(el => el.classList.remove('open'));
        document.querySelectorAll('[id^="faq-icon-"]').forEach(el => {
            el.classList.remove('text-[#ff5a36]');
            el.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12M6 12h12"/></svg>';
        });

        if (!isOpen) {
            answer.classList.add('open');
            icon.classList.add('text-[#ff5a36]');
            icon.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 12h12"/></svg>';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleFaq(0));

    /* Alpine Data Function */
    function employerLanding() {
        return {
            billingCycle: 'monthly',
            init() {
                // Testimonials Swiper
                new Swiper('.testimonialSwiper', {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    autoplay: { delay: 5000, disableOnInteraction: false },
                    navigation: { prevEl: '.testi-prev', nextEl: '.testi-next' },
                    breakpoints: { 
                        640: { slidesPerView: 2, spaceBetween: 20 }, 
                        1024: { slidesPerView: 3, spaceBetween: 24 } 
                    }
                });

                // Blog Swiper
                new Swiper('.blogSwiper', {
                    slidesPerView: 1.1,
                    spaceBetween: 20,
                    loop: true,
                    autoplay: { delay: 6000, disableOnInteraction: false },
                    navigation: { prevEl: '.blog-prev', nextEl: '.blog-next' },
                    breakpoints: { 
                        640: { slidesPerView: 2, spaceBetween: 20 }, 
                        1024: { slidesPerView: 3, spaceBetween: 24 } 
                    }
                });
            },
            toggleCycle() {
                this.billingCycle = this.billingCycle === 'monthly' ? 'annual' : 'monthly';
            }
        }
    }
</script>