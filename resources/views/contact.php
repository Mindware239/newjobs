<?php
$base = $base ?? '/';
$status = $_GET['status'] ?? null;
$message_text = htmlspecialchars($_GET['msg'] ?? '');
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Contact Us | Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        /* Subtle dot grid */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.045) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none; z-index: 0;
        }

        /* Skeleton */
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e8edf3 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.6s infinite;
            border-radius: 8px;
        }

        /* Fade up */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .afu     { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }
        .d1      { animation-delay: 0.08s; }
        .d2      { animation-delay: 0.16s; }
        .d3      { animation-delay: 0.24s; }
        .d4      { animation-delay: 0.32s; }
        .d5      { animation-delay: 0.40s; }

        /* ── HERO ── */
        .hero-band {
            background: linear-gradient(145deg, #1a337a 0%, #1e4fd8 48%, #2563eb 75%, #1d4ed8 100%);
            position: relative; overflow: hidden;
            padding: 80px 0 88px;
        }
        .hero-band::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1.5px 1.5px, rgba(255,255,255,0.09) 1px, transparent 0);
            background-size: 28px 28px;
        }
        .hero-band::after {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 88% 5%,  rgba(56,189,248,0.22) 0%, transparent 52%),
                radial-gradient(ellipse at 5%  92%, rgba(99,102,241,0.16) 0%, transparent 48%);
        }
        .hero-z { position: relative; z-index: 2; }

        .hero-tag {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.11);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 100px; padding: 6px 16px;
            font-size: 11.5px; font-weight: 600;
            color: rgba(255,255,255,0.82);
            letter-spacing: 0.6px; text-transform: uppercase;
            margin-bottom: 22px;
        }
        .hero-tag-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #38bdf8; flex-shrink: 0;
            animation: blink 2.2s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.4;transform:scale(0.7);} }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(38px, 5.5vw, 58px);
            font-weight: 800; color: #fff;
            line-height: 1.14; letter-spacing: -1.2px;
            margin-bottom: 18px;
        }
        .hero-title .hi { color: #38bdf8; }

        .hero-desc {
            font-size: 16px; color: rgba(255,255,255,0.62);
            max-width: 540px; line-height: 1.78;
            margin: 0 auto 40px;
        }

        .qc-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 100px; padding: 9px 18px;
            font-size: 13px; color: rgba(255,255,255,0.82);
            font-weight: 500; text-decoration: none;
            transition: background 0.18s;
        }
        .qc-pill:hover { background: rgba(255,255,255,0.18); }

        /* ── STATS BAR ── */
        .stats-bar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(15,23,42,0.05);
            position: relative; z-index: 1;
        }
        .stat-item { text-align: center; }
        .stat-num {
            font-family: 'Outfit', sans-serif;
            font-size: 30px; font-weight: 800;
            color: #2563eb; letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .stat-lbl { font-size: 12.5px; color: #64748b; font-weight: 500; margin-top: 4px; }

        /* ── CARDS ── */
        .card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e8edf5;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04), 0 6px 24px rgba(15,23,42,0.06);
        }

        /* ── CONTACT INFO ITEMS ── */
        .info-row {
            display: flex; align-items: flex-start; gap: 13px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child { border-bottom: none; }

        .info-icon {
            width: 38px; height: 38px; min-width: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .info-lbl {
            font-size: 10.5px; font-weight: 700;
            color: #94a3b8; text-transform: uppercase;
            letter-spacing: 0.6px; margin-bottom: 3px;
        }
        .info-val { font-size: 13.5px; font-weight: 500; color: #1e293b; line-height: 1.5; }
        .info-link {
            font-size: 13.5px; font-weight: 500;
            color: #2563eb; text-decoration: none;
            transition: color 0.15s;
        }
        .info-link:hover { color: #1d4ed8; text-decoration: underline; }

        /* ── SOCIAL BUTTONS ── */
        .soc-btn {
            width: 36px; height: 36px; border-radius: 9px;
            background: #f1f5f9; border: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; text-decoration: none;
            transition: all 0.15s;
        }
        .soc-btn:hover { background: #dbeafe; color: #2563eb; border-color: #bfdbfe; transform: translateY(-2px); }

        /* ── WHY CHOOSE US ── */
        .why-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .why-item:last-child { border-bottom: none; }
        .why-icon {
            width: 38px; height: 38px; min-width: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .why-title { font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
        .why-desc  { font-size: 12.5px; color: #64748b; line-height: 1.55; }

        /* ── FULL-WIDTH MAP ── */
        .map-section {
            position: relative; z-index: 1;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .map-frame { width: 100%; height: 380px; display: block; border: none; }

        /* ── FORM ── */
        .f-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }
        .f-label .req { color: #ef4444; margin-left: 2px; }

        .f-wrap { position: relative; }
        .f-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
            display: flex; align-items: center;
        }

        .f-input, .f-select, .f-textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 11px;
            background: #f8fafc;
            font-size: 13.5px; color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
            outline: none;
        }
        .f-input, .f-select { padding: 12.5px 14px 12.5px 42px; }
        .f-textarea { padding: 12.5px 14px; resize: vertical; min-height: 130px; }
        .f-input::placeholder, .f-textarea::placeholder { color: #94a3b8; }
        .f-input:hover, .f-select:hover, .f-textarea:hover { border-color: #cbd5e1; }
        .f-input:focus, .f-select:focus, .f-textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.09);
            background: #fff;
        }
        .f-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
            cursor: pointer;
        }

        /* ── SUBMIT BUTTON ── */
        .submit-btn {
            width: 100%; padding: 14px 20px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff; border: none; border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22), 0 8px 24px rgba(37,99,235,0.14);
            transition: transform 0.12s, box-shadow 0.18s;
        }
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(37,99,235,0.3), 0 12px 32px rgba(37,99,235,0.18);
        }
        .submit-btn:active { transform: translateY(0); }

        .resp-strip {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 10px; padding: 10px 16px; margin-top: 12px;
        }
        .resp-strip span { font-size: 12.5px; color: #15803d; font-weight: 500; }

        /* ── FAQ ── */
        .faq-item {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .faq-item:hover { box-shadow: 0 4px 16px rgba(37,99,235,0.08); border-color: #bfdbfe; }

        .faq-btn {
            width: 100%; display: flex; align-items: center;
            justify-content: space-between; gap: 16px;
            padding: 18px 22px;
            background: none; border: none;
            cursor: pointer; text-align: left;
        }
        .faq-q {
            font-size: 14.5px; font-weight: 600;
            color: #0f172a; line-height: 1.45;
            flex: 1;
        }
        .faq-chevron {
            width: 20px; height: 20px; min-width: 20px;
            border-radius: 6px;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.18s, transform 0.25s;
            flex-shrink: 0;
        }
        .faq-chevron.open { background: #dbeafe; transform: rotate(180deg); }
        .faq-chevron svg { width: 12px; height: 12px; color: #64748b; }
        .faq-chevron.open svg { color: #2563eb; }

        .faq-body {
            padding: 0 22px 18px;
            font-size: 13.5px; color: #64748b;
            line-height: 1.7;
            border-top: 1px solid #f1f5f9;
        }

        /* ── TOAST ── */
        #toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; pointer-events: none;
            display: flex; flex-direction: column; gap: 10px;
        }
        .toast {
            opacity: 0; transform: translateX(110%);
            transition: all 0.4s cubic-bezier(0.22,1,0.36,1);
            pointer-events: auto; cursor: pointer;
            width: 340px; border-radius: 14px; padding: 15px 16px;
            box-shadow: 0 8px 32px rgba(15,23,42,0.14);
            display: flex; align-items: flex-start; gap: 12px;
            background: #fff;
        }
        .toast.show { opacity: 1; transform: translateX(0); }
        .toast-success { border: 1px solid #bbf7d0; }
        .toast-error   { border: 1px solid #fecaca; }
        .t-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .t-title { font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700; margin-bottom: 2px; }
        .t-msg   { font-size: 12.5px; color: #64748b; line-height: 1.5; }

        /* Responsive 2-col grid */
        @media (max-width: 640px) {
            .two-col { grid-template-columns: 1fr !important; }
            .hero-title { font-size: 32px; }
        }
    </style>
</head>
<body x-data="{ loaded: false, faq: null }" x-init="setTimeout(() => loaded = true, 600)">

    <!-- Skeleton -->
    <div x-show="!loaded" x-transition.opacity.duration.400ms class="fixed inset-0 bg-white z-50 overflow-hidden flex flex-col">
        <div class="h-20 border-b border-gray-100 flex items-center px-8 justify-between shrink-0">
            <div class="skeleton w-36 h-8"></div>
            <div class="hidden md:flex gap-6"><div class="skeleton w-14 h-4"></div><div class="skeleton w-20 h-4"></div><div class="skeleton w-14 h-4"></div></div>
            <div class="flex gap-3"><div class="skeleton w-24 h-9"></div><div class="skeleton w-28 h-9"></div></div>
        </div>
        <div class="skeleton mx-6 mt-6 h-60 rounded-2xl"></div>
        <div class="grid grid-cols-3 gap-5 mx-6 mt-5">
            <div class="skeleton h-72 rounded-2xl"></div>
            <div class="skeleton h-72 rounded-2xl col-span-2"></div>
        </div>
    </div>

    <?php require 'include/header.php'; ?>

    <!-- ════════════════════════ HERO ════════════════════════ -->
    <div class="hero-band">
        <div class="hero-z container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl text-center">
            <div class="afu d1">
                <div class="hero-tag" style="display:inline-flex;">
                    <span class="hero-tag-dot"></span>
                    We respond within 24 hours
                </div>
            </div>
            <h1 class="hero-title afu d2">Let's <span class="hi">Build</span> Together</h1>
            <p class="hero-desc afu d3">Have a question, proposal, or just want to say hello? We're ready to help you find the best talent or your next career move.</p>
            <div class="flex flex-wrap items-center justify-center gap-3 afu d4">
                <a href="mailto:gm@mindwareinfotech.com" class="qc-pill">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    gm@mindwareinfotech.com
                </a>
                <a href="tel:+918800122315" class="qc-pill">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +91 8800122315
                </a>
                <span class="qc-pill">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Dwarka, New Delhi
                </span>
            </div>
        </div>
    </div>

    <!-- ════════════════════════ STATS ════════════════════════ -->
    <div class="stats-bar">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl py-7">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0">
                <div class="stat-item py-2 px-4 afu d1">
                    <div class="stat-num">2,400+</div>
                    <div class="stat-lbl">Active Jobs Listed</div>
                </div>
                <div class="stat-item py-2 px-4 afu d2" style="border-left:1px solid #e2e8f0;">
                    <div class="stat-num">500+</div>
                    <div class="stat-lbl">Partner Companies</div>
                </div>
                <div class="stat-item py-2 px-4 afu d3" style="border-left:1px solid #e2e8f0;">
                    <div class="stat-num">&lt;24h</div>
                    <div class="stat-lbl">Response Time</div>
                </div>
                <div class="stat-item py-2 px-4 afu d4" style="border-left:1px solid #e2e8f0;">
                    <div class="stat-num">99.9%</div>
                    <div class="stat-lbl">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════ MAIN CONTACT GRID ════════════════════════ -->
    <section style="position:relative;z-index:1;padding:56px 0;">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                <!-- ── LEFT (2 cols) ── -->
                <div class="lg:col-span-2 flex flex-col gap-6">

                    <!-- Contact Details -->
                    <div class="card p-6 afu d1">
                        <!-- Card header -->
                        <div style="display:flex;align-items:center;gap:11px;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
                            <div style="width:40px;height:40px;min-width:40px;border-radius:11px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;">
                                <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <div style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:700;color:#0f172a;">Our Details</div>
                                <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">Get in touch with us</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="info-row">
                            <div class="info-icon" style="background:#eff6ff;">
                                <svg width="17" height="17" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="info-lbl">Email Address</div>
                                <a href="mailto:gm@mindwareinfotech.com" class="info-link">gm@mindwareinfotech.com</a>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="info-row">
                            <div class="info-icon" style="background:#f0fdf4;">
                                <svg width="17" height="17" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <div class="info-lbl">Call Us</div>
                                <a href="tel:+918800122315" class="info-link">+91 8800122315</a>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="info-row">
                            <div class="info-icon" style="background:#fff7ed;">
                                <svg width="17" height="17" fill="none" stroke="#f97316" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <div class="info-lbl">Office Location</div>
                                <div class="info-val" style="font-size:12.5px;color:#475569;line-height:1.55;">S4, Pankaj Plaza, Plot No-7,<br>Pocket-7, Sector-12, Dwarka,<br>New Delhi – 110078</div>
                            </div>
                        </div>

                        <!-- Hours -->
                        <div class="info-row">
                            <div class="info-icon" style="background:#faf5ff;">
                                <svg width="17" height="17" fill="none" stroke="#8b5cf6" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                            </div>
                            <div>
                                <div class="info-lbl">Business Hours</div>
                                <div class="info-val" style="font-size:12.5px;color:#475569;">Mon – Sat: 9:00 AM – 6:00 PM IST</div>
                            </div>
                        </div>

                        <!-- Social -->
                        <div style="margin-top:18px;padding-top:16px;border-top:1px solid #f1f5f9;">
                            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;">Connect Online</div>
                            <div style="display:flex;gap:8px;">
                                <a href="#" aria-label="LinkedIn" class="soc-btn"><svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.483v-5.467c0-1.312-.469-2.213-1.644-2.213-1.229 0-1.967.842-1.967 2.22v5.459h-3.483s.047-9.426 0-10.435h3.483v1.488c.516-.723 1.34-1.745 3.128-1.745 2.288 0 3.998 1.496 3.998 4.706v5.986zM5.312 8.761c-1.218 0-1.986-.777-1.986-1.854 0-1.096.786-1.855 1.986-1.855 1.2 0 1.95.759 1.95 1.855 0 1.077-.759 1.854-1.95 1.854zm1.743 11.691H3.568V10.017h3.487v10.435z"/></svg></a>
                                <a href="#" aria-label="Twitter" class="soc-btn"><svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M18.901 1.996h3.693l-8.086 9.247 9.387 11.23h-7.615l-6.075-7.142-7.394 7.142H.912l8.32-9.52-8.634-10.704h7.828l5.584 6.945 4.881-5.696zm-1.868 18.005h1.5l-6.52-7.46-5.187 7.46H9.19l7.466-10.704-5.35-6.14h1.76l4.42 5.074 5.76-5.074z"/></svg></a>
                                <a href="#" aria-label="Instagram" class="soc-btn"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
                            </div>
                        </div>
                    </div>

                    <!-- Why Choose Us -->
                    <div class="card p-6 afu d2">
                        <div style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">Why Choose Us?</div>
                        <div class="why-item">
                            <div class="why-icon" style="background:#eff6ff;">
                                <svg width="17" height="17" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <div class="why-title">Fast Response</div>
                                <div class="why-desc">All inquiries answered within 24 business hours.</div>
                            </div>
                        </div>
                        <div class="why-item">
                            <div class="why-icon" style="background:#f0fdf4;">
                                <svg width="17" height="17" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <div class="why-title">Trusted Platform</div>
                                <div class="why-desc">500+ companies trust us for their recruitment needs.</div>
                            </div>
                        </div>
                        <div class="why-item">
                            <div class="why-icon" style="background:#fff7ed;">
                                <svg width="17" height="17" fill="none" stroke="#f97316" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <div class="why-title">Secure & Private</div>
                                <div class="why-desc">Your data is always safe and never shared.</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── RIGHT — FORM (3 cols) ── -->
                <div class="lg:col-span-3 afu d2">
                    <div class="card p-8 md:p-10 h-full">

                        <!-- Form header -->
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;padding-bottom:22px;border-bottom:1px solid #f1f5f9;">
                            <div style="width:48px;height:48px;min-width:48px;border-radius:13px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1.5px solid #bfdbfe;display:flex;align-items:center;justify-content:center;">
                                <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <div>
                                <div style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">Send us an Inquiry</div>
                                <div style="font-size:13px;color:#64748b;margin-top:2px;">Fill in the form and we'll get back to you shortly.</div>
                            </div>
                        </div>

                        <form action="<?php echo $base; ?>contact" method="POST" style="display:flex;flex-direction:column;gap:18px;">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="text" name="_hp_email" style="display:none;" tabindex="-1" autocomplete="off">

                            <!-- Name + Email -->
                            <div class="two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                                <div>
                                    <label for="name" class="f-label">Full Name <span class="req">*</span></label>
                                    <div class="f-wrap">
                                        <span class="f-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                                        <input type="text" id="name" name="name" required class="f-input" placeholder="Your full name">
                                    </div>
                                </div>
                                <div>
                                    <label for="email" class="f-label">Email Address <span class="req">*</span></label>
                                    <div class="f-wrap">
                                        <span class="f-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                                        <input type="email" id="email" name="email" required class="f-input" placeholder="name@gmail.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Phone + Company -->
                            <div class="two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                                <div>
                                    <label for="phone" class="f-label">Phone Number</label>
                                    <div class="f-wrap">
                                        <span class="f-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span>
                                        <input type="tel" id="phone" name="phone" class="f-input" placeholder="+91 98765 43210">
                                    </div>
                                </div>
                                <div>
                                    <label for="company" class="f-label">Company / Organization</label>
                                    <div class="f-wrap">
                                        <span class="f-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
                                        <input type="text" id="company" name="company" class="f-input" placeholder="Your company name">
                                    </div>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="f-label">Subject / Reason <span class="req">*</span></label>
                                <div class="f-wrap">
                                    <span class="f-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></span>
                                    <select id="subject" name="subject" required class="f-select">
                                        <option value="" disabled selected>Select a reason...</option>
                                        <option value="Employer Inquiry">Employer Inquiry (Hiring)</option>
                                        <option value="Candidate Support">Candidate Support (Job Seeker)</option>
                                        <option value="Technical Issue">Technical Issue</option>
                                        <option value="Partnership">Partnership / Media</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message" class="f-label">Your Message <span class="req">*</span></label>
                                <textarea id="message" name="message" rows="5" required class="f-textarea" placeholder="Tell us how we can help you in detail..."></textarea>
                            </div>

                            <!-- Submit -->
                            <div>
                                <button type="submit" class="submit-btn">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    Submit Inquiry
                                </button>
                                <div class="resp-strip">
                                    <svg width="13" height="13" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                                    <span>We typically respond within <strong>24 business hours</strong></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ════════════════════════ FULL-WIDTH MAP ════════════════════════ -->
    <div class="map-section">
        <!-- Header bar -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl" style="padding-top:28px;padding-bottom:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,0.2);"></div>
                    <div>
                        <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:700;color:#0f172a;">Find Our Office</div>
                        <div style="font-size:12.5px;color:#64748b;margin-top:1px;">S4, Pankaj Plaza, Sector-12, Dwarka, New Delhi – 110078</div>
                    </div>
                </div>
                <a href="https://maps.google.com/?q=Mindware+Infotech+Dwarka+Delhi" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:7px;background:#2563eb;color:#fff;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Open in Google Maps
                </a>
            </div>
        </div>
        <!-- Map -->
        <iframe
            class="map-frame"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502.830026217462!2d77.040846!3d28.590775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d10664e42a98f%3A0x6b72a6b47c0a68d!2sMindware%20Infotech!5e0!3m2!1sen!2sin!4v1672531200000!5m2!1sen!2sin"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            title="Mindware Infotech Office Location">
        </iframe>
    </div>

    <!-- ════════════════════════ FAQ ════════════════════════ -->
    <section style="position:relative;z-index:1;padding:64px 0;background:#f8fafc;">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">

            <!-- Section header -->
            <div style="text-align:center;margin-bottom:40px;" class="afu d1">
                <div style="display:inline-flex;align-items:center;gap:6px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;border-radius:100px;padding:5px 14px;font-size:11px;font-weight:700;letter-spacing:0.7px;text-transform:uppercase;margin-bottom:14px;">
                    <svg width="11" height="11" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    FAQ
                </div>
                <h2 style="font-family:'Outfit',sans-serif;font-size:clamp(24px,3.5vw,30px);font-weight:800;color:#0f172a;letter-spacing:-0.4px;margin-bottom:10px;">Frequently Asked Questions</h2>
                <p style="font-size:14px;color:#64748b;line-height:1.65;max-width:480px;margin:0 auto;">Quick answers to common questions about contacting and working with us.</p>
            </div>

            <!-- FAQ Items -->
            <div style="display:flex;flex-direction:column;gap:10px;" class="afu d2">

                <?php
                $faqs = [
                    ["How quickly will I receive a response?",
                     "Our team responds to all inquiries within 24 business hours, Monday through Saturday, 9 AM–6 PM IST. For urgent matters, please call us directly."],
                    ["Can employers post jobs directly through the contact form?",
                     "Yes! Select 'Employer Inquiry (Hiring)' and describe your requirements. Our dedicated recruitment team will reach out and guide you through the process."],
                    ["I'm a job seeker. How can Mindware help me?",
                     "Select 'Candidate Support' in the form. Our team will review your profile and connect you with suitable job openings and career guidance tailored to your background."],
                    ["Is there a fee for contacting Mindware Infotech?",
                     "Absolutely not. Reaching out to us is completely free for both employers and job seekers. We only charge for premium placement services."],
                ];
                foreach ($faqs as $i => $faq):
                ?>
                <div class="faq-item" x-data="{ open: false }">
                    <button type="button" class="faq-btn" @click="open = !open">
                        <span class="faq-q"><?= $faq[0] ?></span>
                        <span class="faq-chevron" :class="{ open: open }">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:12px;height:12px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </button>
                    <div x-show="open" x-collapse class="faq-body">
                        <?= $faq[1] ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <?php require 'include/footer.php'; ?>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script>
        const status = "<?php echo $status; ?>";
        const messageText = "<?php echo $message_text; ?>";

        function showToast(type, message) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const isSuccess = type === 'success';
            const msg = (message || (isSuccess ? 'Your message was sent successfully!' : 'An error occurred. Please try again.'))
                .replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>');

            const toast = document.createElement('div');
            toast.className = `toast ${isSuccess ? 'toast-success' : 'toast-error'}`;
            toast.innerHTML = `
                <div class="t-icon" style="background:${isSuccess ? '#dcfce7' : '#fee2e2'};">
                    ${isSuccess
                        ? `<svg width="17" height="17" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`
                        : `<svg width="17" height="17" fill="#ef4444" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`
                    }
                </div>
                <div>
                    <div class="t-title" style="color:${isSuccess ? '#15803d' : '#dc2626'};">${isSuccess ? 'Message Sent!' : 'Error'}</div>
                    <div class="t-msg">${msg}</div>
                </div>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            const t = setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 6000);
            toast.addEventListener('click', () => { clearTimeout(t); toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); });
        }

        window.onload = function() {
            if (status) setTimeout(() => showToast(status, messageText), 700);
        };
    </script>

</body>
</html>