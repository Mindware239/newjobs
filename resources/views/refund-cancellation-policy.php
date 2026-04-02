<?php
$base = $base ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund & Cancellation Policy | Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
        }

        /* ── Dot grid bg ── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.04) 1px, transparent 1px);
            background-size: 34px 34px;
            pointer-events: none; z-index: 0;
        }

        /* ── HERO ── */
        .hero-band {
            background: linear-gradient(145deg, #1a3272 0%, #1e4fd8 50%, #2563eb 100%);
            position: relative; overflow: hidden;
            padding: 96px 0 80px;
        }
        .hero-band::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1.5px 1.5px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 26px 26px; pointer-events: none;
        }
        .hero-glow {
            position: absolute; border-radius: 50%; pointer-events: none;
        }
        .hero-glow-1 { top: -80px; right: -80px; width: 420px; height: 420px;
            background: radial-gradient(circle, rgba(56,189,248,0.18) 0%, transparent 70%); }
        .hero-glow-2 { bottom: -80px; left: -60px; width: 360px; height: 360px;
            background: radial-gradient(circle, rgba(99,102,241,0.14) 0%, transparent 70%); }
        .hero-z { position: relative; z-index: 2; }

        .hero-chip {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22);
            border-radius: 100px; padding: 6px 16px;
            font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.82);
            letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px;
        }
        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(32px, 5vw, 52px); font-weight: 800;
            color: #fff; line-height: 1.13; letter-spacing: -1px; margin-bottom: 14px;
        }
        .hero-title .hi { color: #38bdf8; }
        .hero-desc { font-size: 15.5px; color: rgba(255,255,255,0.62); line-height: 1.75; max-width: 520px; margin: 0 auto; }

        /* ── Meta bar ── */
        .meta-bar {
            background: #fff; border-bottom: 1px solid #e8edf5;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
            position: relative; z-index: 1;
        }
        .meta-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 100px;
            padding: 5px 14px; font-size: 12px; font-weight: 600; color: #475569;
        }

        /* ── Layout ── */
        .page-wrap {
            position: relative; z-index: 1;
            max-width: 1152px; margin: 0 auto;
            padding: 52px 24px 80px;
            display: grid; grid-template-columns: 260px 1fr;
            gap: 36px; align-items: start;
        }
        @media (max-width: 900px) { .page-wrap { grid-template-columns: 1fr; } .toc-sticky { display: none; } }

        /* ── TOC Sidebar ── */
        .toc-sticky { position: sticky; top: 100px; }
        .toc-card {
            background: #fff; border: 1px solid #e8edf5; border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04), 0 6px 20px rgba(15,23,42,0.06);
            padding: 20px;
        }
        .toc-title {
            font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 700;
            color: #94a3b8; text-transform: uppercase; letter-spacing: 0.7px;
            margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;
        }
        .toc-link {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: 9px; margin-bottom: 2px;
            font-size: 12.5px; font-weight: 500; color: #64748b;
            text-decoration: none; transition: all 0.15s;
        }
        .toc-link:hover { background: #eff6ff; color: #2563eb; }
        .toc-link.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .toc-num {
            width: 20px; height: 20px; min-width: 20px; border-radius: 6px;
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: #94a3b8;
        }
        .toc-link:hover .toc-num, .toc-link.active .toc-num {
            background: #dbeafe; color: #2563eb;
        }

        /* Contact box in sidebar */
        .contact-box {
            margin-top: 16px; background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px;
        }
        .contact-box-title { font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 10px; }
        .contact-line { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #1d4ed8; margin-bottom: 6px; font-weight: 500; }
        .contact-line:last-child { margin-bottom: 0; }
        .contact-line a { color: #1d4ed8; text-decoration: none; }
        .contact-line a:hover { text-decoration: underline; }

        /* ── MAIN CONTENT ── */
        .content-area { display: flex; flex-direction: column; gap: 24px; }

        /* ── Policy Section Card ── */
        .policy-card {
            background: #fff; border: 1px solid #e8edf5; border-radius: 18px;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04), 0 6px 20px rgba(15,23,42,0.06);
            overflow: hidden;
        }
        .policy-card-header {
            display: flex; align-items: center; gap: 14px;
            padding: 22px 28px; border-bottom: 1px solid #f1f5f9;
        }
        .policy-icon {
            width: 42px; height: 42px; min-width: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .policy-num {
            font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 800;
            color: #94a3b8; text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 3px;
        }
        .policy-title {
            font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800;
            color: #0f172a; letter-spacing: -0.2px; line-height: 1.2;
        }
        .policy-body { padding: 22px 28px; }
        .policy-body p { font-size: 14px; color: #475569; line-height: 1.75; margin-bottom: 14px; }
        .policy-body p:last-child { margin-bottom: 0; }

        /* ── Highlight strip (important note) ── */
        .note-strip {
            display: flex; align-items: flex-start; gap: 12px;
            background: #fefce8; border: 1px solid #fde68a; border-radius: 11px;
            padding: 14px 16px; margin-bottom: 16px;
        }
        .note-strip.green { background: #f0fdf4; border-color: #bbf7d0; }
        .note-strip.blue  { background: #eff6ff; border-color: #bfdbfe; }
        .note-strip.red   { background: #fef2f2; border-color: #fecaca; }
        .note-strip p { font-size: 13px; color: #64748b; line-height: 1.65; margin: 0; }
        .note-strip.green p { color: #15803d; }
        .note-strip.blue  p { color: #1d4ed8; }
        .note-strip.red   p { color: #dc2626; }

        /* ── Two-column service grid ── */
        .service-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 4px; }
        @media (max-width: 640px) { .service-grid { grid-template-columns: 1fr; } }
        .service-col { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .service-col-title {
            display: flex; align-items: center; gap: 8px;
            font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 700;
            color: #0f172a; margin-bottom: 10px;
        }
        .service-col-badge {
            font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-blue  { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #dcfce7; color: #15803d; }

        /* ── Bullet list ── */
        .policy-list { list-style: none; padding: 0; margin: 0; }
        .policy-list li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 8px 0; border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px; color: #475569; line-height: 1.55;
        }
        .policy-list li:last-child { border-bottom: none; }
        .li-dot {
            width: 18px; height: 18px; min-width: 18px; border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
        }

        /* ── Timeline (refund process) ── */
        .timeline { display: flex; flex-direction: column; gap: 0; }
        .tl-item { display: flex; gap: 16px; }
        .tl-left { display: flex; flex-direction: column; align-items: center; }
        .tl-circle {
            width: 36px; height: 36px; min-width: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; color: #fff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 2px 8px rgba(37,99,235,0.25);
            flex-shrink: 0;
        }
        .tl-line { width: 2px; flex: 1; background: #e2e8f0; margin: 4px 0; min-height: 24px; }
        .tl-item:last-child .tl-line { display: none; }
        .tl-content { padding-bottom: 20px; padding-top: 6px; }
        .tl-label { font-size: 13.5px; font-weight: 600; color: #0f172a; margin-bottom: 3px; }
        .tl-desc  { font-size: 12.5px; color: #64748b; line-height: 1.6; }

        /* ── Alert card (failed transactions, fraud) ── */
        .alert-card {
            display: flex; align-items: flex-start; gap: 14px;
            background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; padding: 16px;
        }
        .alert-card.red { background: #fef2f2; border-color: #fecaca; }

        /* ── Contact card ── */
        .contact-card {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            border-radius: 18px; padding: 28px 32px;
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 24px; flex-wrap: wrap;
        }
        .contact-card-title { font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 6px; letter-spacing: -0.3px; }
        .contact-card-sub   { font-size: 13.5px; color: rgba(255,255,255,0.65); line-height: 1.6; max-width: 420px; }
        .contact-info-list  { display: flex; flex-direction: column; gap: 10px; min-width: 220px; }
        .contact-info-item  {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px; padding: 10px 14px;
        }
        .contact-info-item span { font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 500; }
        .contact-info-item a  { font-size: 13px; color: #fff; font-weight: 600; text-decoration: none; }
        .contact-info-item a:hover { text-decoration: underline; }

        /* ── Fade-up ── */
        @keyframes fadeUp { from{opacity:0;transform:translateY(18px);} to{opacity:1;transform:translateY(0);} }
        .afu { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }
        .d1{animation-delay:0.05s;} .d2{animation-delay:0.1s;} .d3{animation-delay:0.15s;} .d4{animation-delay:0.2s;}
    </style>
</head>
<body>

    <?php require 'include/header.php'; ?>

    <!-- ════════════════ HERO ════════════════ -->
    <div class="hero-band"><!-- offset for fixed header -->
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="hero-z" style="max-width:1100px;margin:0 auto;padding:0 24px;text-align:center;">
            <div class="afu d1">
                <div class="hero-chip">Legal Document</div>
            </div>
            <h1 class="hero-title afu d2">Refund &amp; <span class="hi">Cancellation</span> Policy</h1>
            <p class="hero-desc afu d3">We believe in transparency. This policy clearly outlines when and how refunds or cancellations are handled for all Mindware Infotech services.</p>
        </div>
    </div>

    <!-- ════════════════ META BAR ════════════════ -->
    <div class="meta-bar">
        <div style="max-width:1100px;margin:0 auto;padding:14px 24px;display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
            <span class="meta-pill">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Last Updated: March 07, 2026
            </span>
            <span class="meta-pill">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Effective Immediately
            </span>
            <span class="meta-pill">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                Mindware Infotech
            </span>
        </div>
    </div>

    <!-- ════════════════ MAIN PAGE ════════════════ -->
    <div class="page-wrap">

        <!-- ── TOC SIDEBAR ── -->
        <aside class="toc-sticky">
            <div class="toc-card">
                <div class="toc-title">On This Page</div>
                <a href="#paid-services"       class="toc-link active"><span class="toc-num">1</span>Paid Services</a>
                <a href="#refund-policy"       class="toc-link"><span class="toc-num">2</span>Refund Policy</a>
                <a href="#refund-processing"   class="toc-link"><span class="toc-num">3</span>Refund Processing</a>
                <a href="#cancellation-policy" class="toc-link"><span class="toc-num">4</span>Cancellation Policy</a>
                <a href="#failed-transactions" class="toc-link"><span class="toc-num">5</span>Failed Transactions</a>
                <a href="#fraud-cases"         class="toc-link"><span class="toc-num">6</span>Fraudulent Cases</a>
                <a href="#contact"             class="toc-link"><span class="toc-num">7</span>Contact Us</a>
            </div>

            <div class="contact-box" style="margin-top:14px;">
                <div class="contact-box-title">Need Help?</div>
                <div class="contact-line">
                    <svg width="13" height="13" fill="none" stroke="#1d4ed8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <a href="mailto:gm@indianbarcode.com">gm@indianbarcode.com</a>
                </div>
                <div class="contact-line">
                    <svg width="13" height="13" fill="none" stroke="#1d4ed8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <a href="tel:+919717122688">+91-9717122688</a>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1px solid #bfdbfe;font-size:11.5px;color:#1d4ed8;line-height:1.55;">
                    Refund requests must be submitted within <strong>7 days</strong> of the transaction date.
                </div>
            </div>
        </aside>

        <!-- ── CONTENT ── -->
        <div class="content-area">

            <!-- ① Paid Services -->
            <div class="policy-card afu d1" id="paid-services">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;">
                        <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 01</div>
                        <div class="policy-title">Paid Services</div>
                    </div>
                </div>
                <div class="policy-body">
                    <p>Our platform offers a range of paid services for both job seekers and employers. All services listed below are activated immediately upon successful payment confirmation.</p>
                    <div class="service-grid">
                        <!-- Candidate Services -->
                        <div class="service-col">
                            <div class="service-col-title">
                                <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Candidate Services
                                <span class="service-col-badge badge-blue">Job Seekers</span>
                            </div>
                            <ul class="policy-list">
                                <li><div class="li-dot" style="background:#eff6ff;"><svg width="10" height="10" fill="#2563eb" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Profile Boost</li>
                                <li><div class="li-dot" style="background:#eff6ff;"><svg width="10" height="10" fill="#2563eb" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Featured Profile Visibility</li>
                                <li><div class="li-dot" style="background:#eff6ff;"><svg width="10" height="10" fill="#2563eb" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Auto Job Apply</li>
                                <li><div class="li-dot" style="background:#eff6ff;"><svg width="10" height="10" fill="#2563eb" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>AI Job Suggestions</li>
                                <li><div class="li-dot" style="background:#eff6ff;"><svg width="10" height="10" fill="#2563eb" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Premium Candidate Plans</li>
                            </ul>
                        </div>
                        <!-- Employer Services -->
                        <div class="service-col">
                            <div class="service-col-title">
                                <svg width="16" height="16" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Employer Services
                                <span class="service-col-badge badge-green">Recruiters</span>
                            </div>
                            <ul class="policy-list">
                                <li><div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Employer Subscription Plans</li>
                                <li><div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Featured Job Listings</li>
                                <li><div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Resume Database Access</li>
                                <li><div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>Job Promotion Services</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ② Refund Policy -->
            <div class="policy-card afu d2" id="refund-policy">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#fefce8,#fef9c3);border:1px solid #fde68a;">
                        <svg width="20" height="20" fill="none" stroke="#d97706" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 02</div>
                        <div class="policy-title">Refund Policy</div>
                    </div>
                </div>
                <div class="policy-body">
                    <div class="note-strip" style="background:#fefce8;border-color:#fde68a;">
                        <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p style="color:#92400e;">Due to the digital and instant nature of our services, <strong>payments are generally non-refundable</strong> once the service has been activated.</p>
                    </div>
                    <p>However, we understand exceptional circumstances arise. Refunds may be considered in the following situations:</p>
                    <ul class="policy-list">
                        <li>
                            <div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span><strong>Duplicate payment</strong> — the same transaction was charged more than once by mistake.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span><strong>Service not activated</strong> — payment was completed but the service failed to activate due to a technical error on our end.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#f0fdf4;"><svg width="10" height="10" fill="#10b981" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span><strong>Unauthorized transaction</strong> — a fraudulent charge is verified by our security team.</span>
                        </li>
                    </ul>
                    <div class="note-strip blue" style="margin-top:16px;margin-bottom:0;">
                        <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                        <p><strong>Important:</strong> All refund requests must be submitted within <strong>7 days</strong> of the original transaction date. Requests beyond this window will not be accepted.</p>
                    </div>
                </div>
            </div>

            <!-- ③ Refund Processing -->
            <div class="policy-card afu d2" id="refund-processing">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;">
                        <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 03</div>
                        <div class="policy-title">Refund Processing</div>
                    </div>
                </div>
                <div class="policy-body">
                    <p>Once a refund request is reviewed and approved, we follow this process:</p>
                    <div class="timeline" style="margin-top:16px;">
                        <div class="tl-item">
                            <div class="tl-left"><div class="tl-circle">1</div><div class="tl-line"></div></div>
                            <div class="tl-content">
                                <div class="tl-label">Submit Refund Request</div>
                                <div class="tl-desc">Email us at <a href="mailto:gm@indianbarcode.com" style="color:#2563eb;">gm@indianbarcode.com</a> within 7 days of the transaction with your Transaction ID, registered email, and reason.</div>
                            </div>
                        </div>
                        <div class="tl-item">
                            <div class="tl-left"><div class="tl-circle">2</div><div class="tl-line"></div></div>
                            <div class="tl-content">
                                <div class="tl-label">Review & Verification</div>
                                <div class="tl-desc">Our team reviews and verifies the request, typically within 2–3 business days.</div>
                            </div>
                        </div>
                        <div class="tl-item">
                            <div class="tl-left"><div class="tl-circle">3</div><div class="tl-line"></div></div>
                            <div class="tl-content">
                                <div class="tl-label">Approval & Initiation</div>
                                <div class="tl-desc">If approved, the refund is initiated to the original payment method used during the transaction.</div>
                            </div>
                        </div>
                        <div class="tl-item">
                            <div class="tl-left"><div class="tl-circle">4</div></div>
                            <div class="tl-content">
                                <div class="tl-label">Credit to Account</div>
                                <div class="tl-desc">Refunds are credited within <strong>5–10 business days</strong>, depending on your payment gateway or bank's processing time.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ④ Cancellation Policy -->
            <div class="policy-card afu d3" id="cancellation-policy">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#faf5ff,#ede9fe);border:1px solid #ddd6fe;">
                        <svg width="20" height="20" fill="none" stroke="#7c3aed" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 04</div>
                        <div class="policy-title">Cancellation Policy</div>
                    </div>
                </div>
                <div class="policy-body">
                    <p>You may cancel your subscription or premium service at any time from your account settings. Please note the following terms that apply to all cancellations:</p>
                    <ul class="policy-list">
                        <li>
                            <div class="li-dot" style="background:#faf5ff;"><svg width="10" height="10" fill="#7c3aed" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span>Cancellation prevents future renewals or billing cycles.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#faf5ff;"><svg width="10" height="10" fill="#7c3aed" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span><strong>No refund</strong> will be issued for the remaining active subscription period after cancellation.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#faf5ff;"><svg width="10" height="10" fill="#7c3aed" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
                            <span>Services already activated or consumed cannot be cancelled or refunded retroactively.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ⑤ Failed Transactions -->
            <div class="policy-card afu d3" id="failed-transactions">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;">
                        <svg width="20" height="20" fill="none" stroke="#ea580c" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 05</div>
                        <div class="policy-title">Failed Transactions</div>
                    </div>
                </div>
                <div class="policy-body">
                    <div class="alert-card" style="margin-bottom:16px;">
                        <svg width="20" height="20" fill="none" stroke="#ea580c" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#9a3412;margin-bottom:3px;">Payment Deducted but Service Not Activated?</div>
                            <div style="font-size:12.5px;color:#7c2d12;line-height:1.6;">The amount is usually <strong>automatically reversed</strong> by the payment gateway or bank within <strong>5–7 working days</strong>.</div>
                        </div>
                    </div>
                    <p>If the amount is not automatically reversed within 7 working days, please contact our support team immediately with your transaction details. We will investigate and coordinate with the payment gateway on your behalf.</p>
                </div>
            </div>

            <!-- ⑥ Fraudulent / Misuse -->
            <div class="policy-card afu d4" id="fraud-cases">
                <div class="policy-card-header">
                    <div class="policy-icon" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fecaca;">
                        <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="policy-num">Section 06</div>
                        <div class="policy-title">Fraudulent or Misuse Cases</div>
                    </div>
                </div>
                <div class="policy-body">
                    <p>Mindware Infotech reserves the right to deny refund requests in the following circumstances:</p>
                    <ul class="policy-list" style="margin-bottom:16px;">
                        <li>
                            <div class="li-dot" style="background:#fef2f2;"><svg width="10" height="10" fill="#dc2626" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div>
                            <span>The purchased service has already been used or partially consumed.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#fef2f2;"><svg width="10" height="10" fill="#dc2626" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div>
                            <span>Fraudulent activity or abuse of our refund process is suspected.</span>
                        </li>
                        <li>
                            <div class="li-dot" style="background:#fef2f2;"><svg width="10" height="10" fill="#dc2626" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div>
                            <span>The user has violated our Terms of Service or platform guidelines.</span>
                        </li>
                    </ul>
                    <div class="note-strip red">
                        <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <p>Users found to be misusing refund requests or violating platform terms may have their accounts suspended.</p>
                    </div>
                </div>
            </div>

            <!-- ⑦ Contact Card -->
            <div class="contact-card afu d4" id="contact">
                <div>
                    <div class="contact-card-title">Questions or Refund Requests?</div>
                    <div class="contact-card-sub">Please include your <strong style="color:#fff;">Transaction ID</strong>, registered email address, payment date, and reason for the refund request in your message.</div>
                </div>
                <div class="contact-info-list">
                    <div class="contact-info-item">
                        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:gm@indianbarcode.com">gm@indianbarcode.com</a>
                    </div>
                    <div class="contact-info-item">
                        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:+919717122688">+91-9717122688</a>
                    </div>
                    <div style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:10px 14px;">
                        <span style="font-size:11.5px;color:rgba(255,255,255,0.6);line-height:1.6;">Response time: <strong style="color:#fff;">1–2 business days</strong><br>Mon – Sat: 9:00 AM – 6:00 PM IST</span>
                    </div>
                </div>
            </div>

        </div><!-- /content-area -->
    </div><!-- /page-wrap -->

    <?php require 'include/footer.php'; ?>

    <script>
        // ── TOC active link on scroll ──
        const sections = document.querySelectorAll('[id]');
        const tocLinks = document.querySelectorAll('.toc-link');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    tocLinks.forEach(l => l.classList.remove('active'));
                    const active = document.querySelector(`.toc-link[href="#${entry.target.id}"]`);
                    if (active) active.classList.add('active');
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px' });
        sections.forEach(s => observer.observe(s));
    </script>

</body>
</html>