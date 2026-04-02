<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Login' ?> - Mindware InfoTech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }

        :root {
            --blue:       #3b82f6;
            --blue-mid:   #2563eb;
            --blue-dark:  #1d4ed8;
            --blue-deep:  #1e3a8a;
            --sky:        #38bdf8;
            --teal:       #06b6d4;
            --slate-50:   #f8fafc;
            --slate-100:  #f1f5f9;
            --slate-200:  #e2e8f0;
            --slate-400:  #94a3b8;
            --slate-500:  #64748b;
            --slate-700:  #334155;
            --slate-900:  #0f172a;
            --green:      #10b981;
            --font-body:  'Plus Jakarta Sans', sans-serif;
            --font-head:  'Outfit', sans-serif;
        }

        html, body {
            width: 100%; min-height: 100vh;
            font-family: var(--font-body);
            background: #edf2fb;
            overflow-x: hidden;
        }

        /* ──────────────────────────────
           FULL-WIDTH PAGE LAYOUT
        ────────────────────────────── */
        .page-wrap {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ──────────────────────────────
           LEFT — HERO PANEL
        ────────────────────────────── */
        .hero-panel {
            width: 52%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 40%, #e0f2fe 80%, #f0f9ff 100%);
            display: flex;
            flex-direction: column;
            padding: 48px 56px;
        }

        /* Soft blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }
        .blob-1 { width: 420px; height: 420px; top: -120px; left: -120px; background: rgba(59,130,246,0.14); }
        .blob-2 { width: 320px; height: 320px; bottom: -80px; right: -60px; background: rgba(6,182,212,0.12); }
        .blob-3 { width: 200px; height: 200px; top: 50%; left: 55%; background: rgba(56,189,248,0.1); }

        /* Dot pattern */
        .hero-panel::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(59,130,246,0.09) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        .hero-inner { position: relative; z-index: 2; display: flex; flex-direction: column; height: 100%; }

        /* Brand top */
        .brand-row {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 52px;
            animation: up 0.55s 0.05s both;
        }

        .brand-logo {
            width: 46px; height: 46px; border-radius: 13px;
            background: linear-gradient(135deg, var(--blue-mid), var(--blue-dark));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(37,99,235,0.35);
            flex-shrink: 0;
        }

        .brand-logo span {
            font-family: var(--font-head);
            font-size: 20px; font-weight: 800; color: #fff;
        }

        .brand-text-name {
            font-family: var(--font-head);
            font-size: 17px; font-weight: 700;
            color: var(--slate-900); letter-spacing: -0.2px;
        }

        .brand-text-sub {
            font-size: 11.5px; color: var(--slate-500);
            font-weight: 400; margin-top: 2px;
        }

        /* Hero heading */
        .hero-heading {
            font-family: var(--font-head);
            font-size: 38px; font-weight: 800;
            color: var(--slate-900);
            line-height: 1.18; letter-spacing: -0.8px;
            margin-bottom: 14px;
            animation: up 0.55s 0.12s both;
        }

        .hero-heading .hi { color: var(--blue-mid); }

        .hero-desc {
            font-size: 14.5px; color: var(--slate-500);
            font-weight: 400; line-height: 1.7;
            max-width: 380px;
            margin-bottom: 40px;
            animation: up 0.55s 0.18s both;
        }

        /* ── Security illustration (pure SVG/CSS) ── */
        .security-visual {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: up 0.55s 0.24s both;
        }

        /* Central shield */
        .shield-wrap {
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }

        .shield-glow {
            position: absolute;
            width: 260px; height: 260px; border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,0.12) 0%, transparent 70%);
        }

        .shield-ring-outer {
            position: absolute;
            width: 230px; height: 230px; border-radius: 50%;
            border: 1.5px dashed rgba(37,99,235,0.2);
            animation: rotateSlow 18s linear infinite;
        }

        .shield-ring-inner {
            position: absolute;
            width: 170px; height: 170px; border-radius: 50%;
            border: 1px dashed rgba(6,182,212,0.25);
            animation: rotateSlow 12s linear infinite reverse;
        }

        @keyframes rotateSlow { to { transform: rotate(360deg); } }

        .shield-body {
            width: 110px; height: 130px;
            position: relative; z-index: 3;
            filter: drop-shadow(0 8px 24px rgba(37,99,235,0.28));
        }

        /* Floating cards around shield */
        .float-card {
            position: absolute;
            background: #fff;
            border-radius: 14px;
            padding: 12px 16px;
            box-shadow: 0 4px 20px rgba(15,23,42,0.1), 0 1px 4px rgba(15,23,42,0.06);
            display: flex; align-items: center; gap: 10px;
            white-space: nowrap;
            z-index: 4;
        }

        .float-card-icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .float-card-title { font-size: 12px; font-weight: 600; color: var(--slate-900); }
        .float-card-sub   { font-size: 10.5px; color: var(--slate-400); margin-top: 1px; }

        /* Card positions */
        .fc-1 { top: 10px; left: 0px;   animation: floatA 4s ease-in-out infinite; }
        .fc-2 { top: 10px; right: 0px;  animation: floatB 4.5s ease-in-out infinite 0.5s; }
        .fc-3 { bottom: 10px; left: 10px; animation: floatA 5s ease-in-out infinite 1s; }
        .fc-4 { bottom: 10px; right: 0px; animation: floatB 4s ease-in-out infinite 0.8s; }

        @keyframes floatA { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-8px);} }
        @keyframes floatB { 0%,100%{transform:translateY(0);} 50%{transform:translateY(8px);} }

        .dot-green { width: 7px; height: 7px; border-radius: 50%; background: #10b981; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.5;transform:scale(0.75);} }

        /* Trust badges */
        .trust-row {
            display: flex; align-items: center; gap: 16px;
            margin-top: 36px;
            flex-wrap: wrap;
            animation: up 0.55s 0.32s both;
        }

        .trust-badge {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.7);
            border: 1px solid rgba(37,99,235,0.12);
            border-radius: 100px;
            padding: 7px 14px;
            backdrop-filter: blur(8px);
        }

        .trust-badge-icon { color: var(--blue-mid); display: flex; }
        .trust-badge span { font-size: 12px; font-weight: 500; color: var(--slate-700); }

        /* ──────────────────────────────
           RIGHT — FORM PANEL
        ────────────────────────────── */
        .form-panel {
            width: 48%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 60px;
            position: relative;
            box-shadow: -4px 0 40px rgba(15,23,42,0.05);
        }

        .form-panel::before {
            content: '';
            position: absolute; left: 0; top: 10%; bottom: 10%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(37,99,235,0.12), transparent);
        }

        /* Badge */
        .form-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: #eff6ff; color: var(--blue-mid);
            border: 1px solid #bfdbfe;
            border-radius: 8px; padding: 6px 13px;
            margin-bottom: 24px;
            width: fit-content;
            animation: up 0.5s 0.1s both;
        }

        .form-badge span {
            font-size: 11px; font-weight: 700;
            letter-spacing: 0.8px; text-transform: uppercase;
        }

        .form-title {
            font-family: var(--font-head);
            font-size: 31px; font-weight: 800;
            color: var(--slate-900); letter-spacing: -0.5px;
            line-height: 1.2; margin-bottom: 6px;
            animation: up 0.5s 0.15s both;
        }

        .form-subtitle {
            font-size: 14px; color: var(--slate-500);
            font-weight: 400; line-height: 1.5;
            margin-bottom: 32px;
            animation: up 0.5s 0.2s both;
        }

        /* Error */
        .error-alert {
            display: flex; align-items: flex-start; gap: 10px;
            background: #fef2f2; border: 1px solid #fecaca;
            color: #dc2626; border-radius: 12px;
            padding: 13px 15px; margin-bottom: 20px;
            font-size: 13.5px; line-height: 1.5;
        }

        /* Fields */
        .fields { display: flex; flex-direction: column; gap: 20px; animation: up 0.5s 0.22s both; }

        .f-label {
            font-size: 13px; font-weight: 600;
            color: var(--slate-700); margin-bottom: 7px;
            display: block; letter-spacing: 0.1px;
        }

        .f-wrap { position: relative; }

        .f-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400); pointer-events: none;
            display: flex; align-items: center;
        }

        .f-input {
            width: 100%; padding: 13.5px 14px 13.5px 44px;
            border: 1.5px solid var(--slate-200);
            border-radius: 12px; background: var(--slate-50);
            font-size: 14px; color: var(--slate-900);
            font-family: var(--font-body);
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
            outline: none;
        }

        .f-input::placeholder { color: var(--slate-400); font-size: 13.5px; }
        .f-input:hover:not(:focus) { border-color: #cbd5e1; }
        .f-input:focus {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
            background: #fff;
        }

        .pass-eye {
            position: absolute; right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--slate-400); padding: 4px;
            display: flex; transition: color 0.15s;
        }
        .pass-eye:hover { color: var(--slate-600); }

        /* Captcha */
        .captcha-row {
            display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
        }

        .captcha-img {
            height: 48px; border-radius: 10px;
            border: 1.5px solid var(--slate-200);
            cursor: pointer; transition: border-color 0.15s;
            background: var(--slate-50);
        }
        .captcha-img:hover { border-color: var(--blue-mid); }

        .captcha-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--slate-100); border: 1.5px solid var(--slate-200);
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; color: var(--slate-500);
            transition: background 0.15s, color 0.15s, border-color 0.15s;
            flex-shrink: 0;
        }
        .captcha-btn:hover { background: #dbeafe; color: var(--blue-mid); border-color: #bfdbfe; }

        .captcha-note {
            font-size: 11.5px; color: var(--slate-400); font-weight: 400;
        }

        .mono { font-family: 'SF Mono', 'Fira Code', monospace; letter-spacing: 0.15em; }

        /* Bottom row */
        .options-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 2px 0;
        }

        .rem-label {
            display: flex; align-items: center; gap: 9px; cursor: pointer;
        }

        .rem-check {
            width: 16px; height: 16px; border-radius: 5px;
            accent-color: var(--blue-mid); cursor: pointer; flex-shrink: 0;
        }

        .rem-text { font-size: 13.5px; color: var(--slate-600); font-weight: 400; }

        .forgot {
            font-size: 13.5px; font-weight: 600; color: var(--blue-mid);
            text-decoration: none; transition: color 0.15s;
        }
        .forgot:hover { color: var(--blue-dark); }

        /* Submit */
        .submit-btn {
            width: 100%; padding: 15px 20px;
            background: linear-gradient(135deg, var(--blue-mid) 0%, var(--blue-dark) 100%);
            color: #fff; border: none; border-radius: 13px;
            font-family: var(--font-body);
            font-size: 15px; font-weight: 600;
            cursor: pointer; margin-top: 4px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22), 0 8px 24px rgba(37,99,235,0.18);
            transition: opacity 0.15s, transform 0.12s, box-shadow 0.18s;
            letter-spacing: 0.1px;
        }
        .submit-btn:hover:not(:disabled) {
            box-shadow: 0 4px 12px rgba(37,99,235,0.32), 0 12px 32px rgba(37,99,235,0.22);
            transform: translateY(-1px);
        }
        .submit-btn:active:not(:disabled) { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.7s linear infinite; }

        /* SSL strip */
        .ssl-strip {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 10px; padding: 10px 16px;
            margin-top: 6px;
        }
        .ssl-strip span { font-size: 12px; color: #15803d; font-weight: 500; }

        /* Footer */
        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 22px; margin-top: 22px;
            border-top: 1px solid var(--slate-100);
            animation: up 0.5s 0.42s both;
        }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13.5px; font-weight: 500; color: var(--slate-500);
            text-decoration: none; transition: color 0.15s;
        }
        .back-link:hover { color: var(--blue-mid); }

        .copy-text { font-size: 12px; color: var(--slate-400); }

        @keyframes up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 960px) {
            .page-wrap { flex-direction: column; }
            .hero-panel, .form-panel { width: 100%; }
            .hero-panel { padding: 40px 32px; min-height: 420px; }
            .form-panel { padding: 40px 32px; box-shadow: none; }
            .form-panel::before { display: none; }
            .hero-heading { font-size: 30px; }
            .fc-3, .fc-4 { display: none; }
        }
    </style>
</head>
<body x-data="loginForm()">

<div class="page-wrap">

    <!-- ═══════════════════════════════════════
         LEFT HERO PANEL
    ════════════════════════════════════════ -->
    <div class="hero-panel">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <div class="hero-inner">

            <!-- Brand -->
            <div class="brand-row">
                <div class="brand-logo"><span>M</span></div>
                <div>
                    <div class="brand-text-name">Mindware InfoTech</div>
                    <div class="brand-text-sub">Connecting Talent with Opportunities</div>
                </div>
            </div>

            <!-- Headline -->
            <h1 class="hero-heading">Your Recruitment<br>Portal, <span class="hi">Secured</span><br>& Simplified.</h1>
            <p class="hero-desc">Enterprise-grade security meets effortless hiring management. Sign in to access your full admin dashboard.</p>

            <!-- ── Security Illustration ── -->
            <div class="security-visual">
                <div class="shield-wrap" style="width:360px; height:320px; position:relative;">
                    <div class="shield-glow"></div>
                    <div class="shield-ring-outer"></div>
                    <div class="shield-ring-inner"></div>

                    <!-- Shield SVG -->
                    <svg class="shield-body" viewBox="0 0 110 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="sg" x1="0" y1="0" x2="110" y2="130" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#3b82f6"/>
                                <stop offset="100%" stop-color="#1d4ed8"/>
                            </linearGradient>
                            <linearGradient id="sg2" x1="0" y1="0" x2="110" y2="0" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <!-- Shield body -->
                        <path d="M55 4L8 22v36c0 28 20 52 47 60 27-8 47-32 47-60V22L55 4z" fill="url(#sg)"/>
                        <!-- Shield shine -->
                        <path d="M55 4L8 22v36c0 28 20 52 47 60" fill="url(#sg2)" stroke="none"/>
                        <!-- Border highlight -->
                        <path d="M55 4L8 22v36c0 28 20 52 47 60 27-8 47-32 47-60V22L55 4z" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                        <!-- Lock icon inside -->
                        <rect x="37" y="54" width="36" height="28" rx="5" fill="rgba(255,255,255,0.22)"/>
                        <path d="M44 54v-7a11 11 0 0122 0v7" stroke="rgba(255,255,255,0.9)" stroke-width="3" stroke-linecap="round" fill="none"/>
                        <circle cx="55" cy="65" r="4" fill="#fff"/>
                        <rect x="53.5" y="65" width="3" height="7" rx="1.5" fill="#fff"/>
                        <!-- Check mark at bottom -->
                        <circle cx="55" cy="97" r="10" fill="rgba(255,255,255,0.18)"/>
                        <path d="M50 97l3.5 3.5L61 92" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>

                    <!-- Floating cards -->
                    <div class="float-card fc-1">
                        <div class="float-card-icon" style="background:#eff6ff;">
                            <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="float-card-title">SSL Encrypted</div>
                            <div class="float-card-sub">256-bit TLS</div>
                        </div>
                    </div>

                    <div class="float-card fc-2">
                        <div class="float-card-icon" style="background:#f0fdf4;">
                            <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="float-card-title">2FA Ready</div>
                            <div class="float-card-sub">Extra layer</div>
                        </div>
                    </div>

                    <div class="float-card fc-3">
                        <div class="float-card-icon" style="background:#fff7ed;">
                            <svg width="18" height="18" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="float-card-title">CAPTCHA</div>
                            <div class="float-card-sub">Bot protection</div>
                        </div>
                    </div>

                    <div class="float-card fc-4">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="dot-green"></span>
                            <div>
                                <div class="float-card-title">System Online</div>
                                <div class="float-card-sub">All services active</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Trust badges -->
            <div class="trust-row">
                <div class="trust-badge">
                    <span class="trust-badge-icon">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <span>Secure Login</span>
                </div>
                <div class="trust-badge">
                    <span class="trust-badge-icon">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <span>Data Protected</span>
                </div>
                <div class="trust-badge">
                    <span class="trust-badge-icon">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <span>99.9% Uptime</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════
         RIGHT FORM PANEL
    ════════════════════════════════════════ -->
    <div class="form-panel">

        <div class="form-badge">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>Admin Portal</span>
        </div>

        <h2 class="form-title">Welcome back 👋</h2>
        <p class="form-subtitle">Sign in to your secure admin account to continue managing your portal.</p>

        <?php if (isset($error) && $error): ?>
        <div class="error-alert">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px;">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" @submit.prevent="submitForm()">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect ?? '/admin/dashboard') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="fields">

                <!-- Email -->
                <div>
                    <label for="email" class="f-label">Email address</label>
                    <div class="f-wrap">
                        <span class="f-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input id="email" name="email" type="email" required
                               x-model="formData.email"
                               class="f-input"
                               placeholder="admin@company.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="f-label">Password</label>
                    <div class="f-wrap">
                        <span class="f-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password" name="password"
                               :type="showPassword ? 'text' : 'password'"
                               required x-model="formData.password"
                               class="f-input"
                               placeholder="Minimum 8 characters">
                        <button type="button" class="pass-eye" @click="showPassword = !showPassword">
                            <svg x-show="!showPassword" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Captcha -->
                <div>
                    <label for="captcha" class="f-label">Verification Code</label>
                    <div class="captcha-row">
                        <img id="captcha-image" src="/admin/captcha/generate" alt="CAPTCHA"
                             class="captcha-img" @click="refreshCaptcha()" @error="captchaError = true">
                        <button type="button" class="captcha-btn" @click="refreshCaptcha()" title="Refresh captcha">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        <span class="captcha-note">Click image to refresh</span>
                        <span x-show="captchaError" x-cloak class="text-red-600 text-xs ml-3">
                            Captcha image failed to load. Enable PHP GD extension and refresh.
                        </span>
                    </div>
                    <div class="f-wrap">
                        <span class="f-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <input id="captcha" name="captcha_code" type="text" required
                               x-model="formData.captcha"
                               class="f-input mono"
                               placeholder="Enter code above"
                               maxlength="6" autocomplete="off">
                    </div>
                </div>

                <!-- Options -->
                <div class="options-row">
                    <label class="rem-label" for="remember">
                        <input id="remember" name="remember" type="checkbox" class="rem-check">
                        <span class="rem-text">Remember me</span>
                    </label>
                    <a href="/admin/forgot-password" class="forgot">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="submit-btn" :disabled="isSubmitting">
                    <svg x-show="isSubmitting" x-cloak class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                        <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span x-show="!isSubmitting">Sign in to Dashboard</span>
                    <span x-show="isSubmitting" x-cloak>Signing in...</span>
                    <svg x-show="!isSubmitting" width="16" height="16" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>

                <!-- SSL strip -->
                <div class="ssl-strip">
                    <svg width="14" height="14" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Protected by 256-bit SSL encryption · Your data is always safe</span>
                </div>

            </div>
        </form>

        <div class="form-footer">
            <a href="/" class="back-link">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
            <span class="copy-text">© 2025 Mindware InfoTech</span>
        </div>

    </div>

</div><!-- end page-wrap -->

<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function loginForm() {
        return {
            showPassword: false,
            isSubmitting: false,
                    captchaError: false,
                    formData: { email: '', password: '', captcha: '' },
            refreshCaptcha() {
                const img = document.getElementById('captcha-image');
                        if (img) {
                            this.captchaError = false;
                            img.src = '/admin/captcha/generate?' + Date.now();
                        }
            },
            submitForm() {
                this.isSubmitting = true;
                const form = document.querySelector('form');
                if (form) form.submit();
            }
        }
    }
</script>
</body>
</html>
