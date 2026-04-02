<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Reset Password - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }

        :root {
            --blue:      #2563eb;
            --blue-dark: #1d4ed8;
            --blue-deep: #1e3a8a;
            --sky:       #38bdf8;
            --green:     #10b981;
            --font-body: 'Plus Jakarta Sans', sans-serif;
            --font-head: 'Outfit', sans-serif;
        }

        html, body {
            width: 100%; min-height: 100vh;
            font-family: var(--font-body);
            background: #edf2fb;
            overflow-x: hidden;
        }

        /* Dot grid */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.065) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none; z-index: 0;
        }

        /* Blobs */
        .blob {
            position: fixed; border-radius: 50%;
            filter: blur(64px); pointer-events: none; z-index: 0;
        }
        .blob-tl { width: 500px; height: 500px; top: -180px; left: -180px;
                   background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%); }
        .blob-br { width: 400px; height: 400px; bottom: -140px; right: -140px;
                   background: radial-gradient(circle, rgba(6,182,212,0.09) 0%, transparent 70%); }

        /* ══ FULL-WIDTH LAYOUT ══ */
        .page-wrap {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative; z-index: 1;
        }

        /* ══ LEFT — FORM PANEL ══ */
        .form-panel {
            width: 48%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 64px;
            position: relative;
        }

        .form-panel::after {
            content: '';
            position: absolute; right: 0; top: 10%; bottom: 10%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(37,99,235,0.12), transparent);
        }

        /* Brand */
        .brand-row {
            display: flex; align-items: center; gap: 11px;
            margin-bottom: 48px;
            animation: up 0.5s 0.05s both;
        }
        .brand-logo {
            width: 42px; height: 42px; border-radius: 11px;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 10px rgba(37,99,235,0.3); flex-shrink: 0;
        }
        .brand-logo span {
            font-family: var(--font-head);
            font-size: 19px; font-weight: 800; color: #fff;
        }
        .brand-name {
            font-family: var(--font-head);
            font-size: 16px; font-weight: 700; color: #0f172a;
        }
        .brand-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }

        /* Icon + Title block */
        .icon-area {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 26px;
            animation: up 0.5s 0.1s both;
        }
        .icon-box {
            width: 56px; height: 56px; border-radius: 16px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1.5px solid #bfdbfe;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .icon-text .step-label {
            font-size: 11px; font-weight: 600; color: var(--blue);
            text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;
        }
        .icon-text .step-title {
            font-family: var(--font-head);
            font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px;
        }

        /* Description */
        .form-desc {
            font-size: 14px; color: #64748b; line-height: 1.7;
            margin-bottom: 28px; max-width: 380px;
            animation: up 0.5s 0.14s both;
        }

        /* Steps */
        .steps-row {
            display: flex; align-items: center; gap: 0;
            margin-bottom: 28px;
            animation: up 0.5s 0.17s both;
        }
        .step-item { display: flex; align-items: center; gap: 8px; }
        .step-num {
            width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11.5px; font-weight: 700; flex-shrink: 0;
        }
        .step-num.done    { background: #dcfce7; color: var(--green); }
        .step-num.active  { background: var(--blue); color: #fff; }
        .step-num.pending { background: #f1f5f9; color: #94a3b8; }
        .step-lbl { font-size: 12px; font-weight: 500; color: #94a3b8; }
        .step-lbl.done   { color: var(--green); }
        .step-lbl.active { color: #0f172a; }
        .step-conn { flex: 1; height: 1.5px; background: #e2e8f0; margin: 0 10px; }

        /* Alerts */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            border-radius: 12px; padding: 13px 15px; margin-bottom: 20px;
            font-size: 13.5px; line-height: 1.55;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-icon    { flex-shrink: 0; margin-top: 1px; }

        /* Success CTA */
        .go-login-btn {
            display: inline-flex; align-items: center; gap: 7px;
            margin-top: 14px;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff; text-decoration: none;
            padding: 12px 20px; border-radius: 11px;
            font-size: 14px; font-weight: 600;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22);
            transition: transform 0.12s, box-shadow 0.18s;
        }
        .go-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(37,99,235,0.3);
        }

        /* Form fields */
        .form-fields { animation: up 0.5s 0.22s both; }

        .f-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px; letter-spacing: 0.1px;
        }
        .f-hint { font-size: 11.5px; color: #94a3b8; margin-top: 5px; }

        .f-wrap { position: relative; margin-bottom: 20px; }

        .f-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; display: flex;
        }

        .f-input {
            width: 100%; padding: 13.5px 46px 13.5px 44px;
            border: 1.5px solid #e2e8f0; border-radius: 12px;
            background: #f8fafc; font-size: 14px; color: #0f172a;
            font-family: var(--font-body);
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
            outline: none;
        }
        .f-input::placeholder { color: #94a3b8; font-size: 13.5px; }
        .f-input:hover:not(:focus) { border-color: #cbd5e1; }
        .f-input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
            background: #fff;
        }
        .f-input.match   { border-color: #10b981; }
        .f-input.mismatch{ border-color: #ef4444; }

        .pass-eye {
            position: absolute; right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; padding: 4px; display: flex;
            transition: color 0.15s;
        }
        .pass-eye:hover { color: #475569; }

        /* Strength bar */
        .strength-wrap { margin-top: 8px; margin-bottom: 4px; }
        .strength-track {
            height: 4px; border-radius: 100px;
            background: #e2e8f0; overflow: hidden; margin-bottom: 5px;
        }
        .strength-fill {
            height: 100%; border-radius: 100px;
            transition: width 0.3s, background 0.3s;
        }
        .strength-label { font-size: 11.5px; font-weight: 500; }

        /* Match indicator */
        .match-row {
            display: flex; align-items: center; gap: 6px;
            font-size: 11.5px; margin-top: 5px;
        }

        /* Submit */
        .submit-btn {
            width: 100%; padding: 14.5px 20px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: #fff; border: none; border-radius: 13px;
            font-family: var(--font-body);
            font-size: 15px; font-weight: 600;
            cursor: pointer; margin-bottom: 14px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22), 0 8px 24px rgba(37,99,235,0.16);
            transition: transform 0.12s, box-shadow 0.18s, opacity 0.15s;
        }
        .submit-btn:hover:not(:disabled) {
            box-shadow: 0 4px 12px rgba(37,99,235,0.3), 0 12px 32px rgba(37,99,235,0.2);
            transform: translateY(-1px);
        }
        .submit-btn:active:not(:disabled) { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.7s linear infinite; }

        /* Back link */
        .back-row { text-align: center; }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13.5px; font-weight: 500; color: #64748b;
            text-decoration: none; transition: color 0.15s;
        }
        .back-link:hover { color: var(--blue); }

        /* SSL strip */
        .ssl-strip {
            display: flex; align-items: center; gap: 7px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 10px; padding: 9px 14px; margin-bottom: 20px;
        }
        .ssl-strip span { font-size: 12px; color: #15803d; font-weight: 500; }

        /* Footer */
        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 20px; margin-top: 20px;
            border-top: 1px solid #f1f5f9;
            animation: up 0.5s 0.38s both;
        }
        .footer-copy { font-size: 12px; color: #94a3b8; }

        /* ══ RIGHT — HERO PANEL ══ */
        .hero-panel {
            width: 52%;
            background: linear-gradient(145deg, #dbeafe 0%, #eff6ff 40%, #e0f2fe 80%, #f0f9ff 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 64px;
            position: relative;
            overflow: hidden;
        }
        .hero-panel::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.08) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
        }
        .hero-panel::after {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 90% 10%, rgba(56,189,248,0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 5% 90%,  rgba(99,102,241,0.14) 0%, transparent 50%);
            pointer-events: none;
        }
        .h-ring {
            position: absolute; border-radius: 50%; pointer-events: none;
        }
        .h-ring-1 { top: -100px; right: -100px; width: 360px; height: 360px;
                    border: 1px solid rgba(37,99,235,0.1); }
        .h-ring-2 { bottom: -120px; left: -120px; width: 400px; height: 400px;
                    border: 1px solid rgba(37,99,235,0.08); }

        .hero-inner { position: relative; z-index: 2; }

        .hero-heading {
            font-family: var(--font-head);
            font-size: 34px; font-weight: 800;
            color: #0f172a; line-height: 1.2;
            letter-spacing: -0.6px; margin-bottom: 12px;
            animation: up 0.55s 0.15s both;
        }
        .hero-heading .hi { color: var(--blue); }

        .hero-desc {
            font-size: 14.5px; color: #475569;
            line-height: 1.7; max-width: 360px;
            margin-bottom: 40px;
            animation: up 0.55s 0.2s both;
        }

        /* Requirements checklist */
        .req-section {
            margin-bottom: 36px;
            animation: up 0.55s 0.25s both;
        }
        .req-label {
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.9px;
            margin-bottom: 14px;
        }
        .req-list { display: flex; flex-direction: column; gap: 10px; }
        .req-item {
            display: flex; align-items: center; gap: 11px;
            background: rgba(255,255,255,0.7);
            border: 1px solid rgba(37,99,235,0.1);
            border-radius: 11px; padding: 12px 14px;
            backdrop-filter: blur(6px);
            transition: border-color 0.2s;
        }
        .req-item.met { border-color: rgba(16,185,129,0.3); background: rgba(240,253,244,0.8); }
        .req-icon {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .req-text { font-size: 13px; font-weight: 500; color: #334155; }

        /* Feature cards */
        .feature-cards {
            display: flex; flex-direction: column; gap: 12px;
            animation: up 0.55s 0.32s both;
        }
        .feat-card {
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(37,99,235,0.1);
            border-radius: 14px; padding: 15px 18px;
            display: flex; align-items: center; gap: 13px;
            backdrop-filter: blur(6px);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .feat-card:hover { box-shadow: 0 4px 20px rgba(37,99,235,0.1); transform: translateX(4px); }
        .feat-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .feat-title { font-family: var(--font-head); font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .feat-desc  { font-size: 12px; color: #64748b; line-height: 1.45; }

        @keyframes up {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .page-wrap { flex-direction: column-reverse; }
            .form-panel, .hero-panel { width: 100%; padding: 40px 28px; }
            .form-panel::after { display: none; }
            .hero-heading { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="blob blob-tl"></div>
    <div class="blob blob-br"></div>

    <?php $isAdminPath = (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0); ?>

    <div class="page-wrap" x-data="resetPasswordForm()" x-cloak>

        <!-- ══ LEFT — FORM PANEL ══ -->
        <div class="form-panel">

            <!-- Brand -->
            <div class="brand-row">
                <div class="brand-logo"><span>M</span></div>
                <div>
                    <div class="brand-name">Mindware InfoTech</div>
                    <div class="brand-sub">Connecting Talent with Opportunities</div>
                </div>
            </div>

            <!-- Icon + Title -->
            <div class="icon-area">
                <div class="icon-box">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24">
                        <defs>
                            <linearGradient id="ig" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#3b82f6"/>
                                <stop offset="100%" stop-color="#1d4ed8"/>
                            </linearGradient>
                        </defs>
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke="url(#ig)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="icon-text">
                    <div class="step-label">Account Recovery</div>
                    <div class="step-title">Reset Password</div>
                </div>
            </div>

            <!-- Description -->
            <p class="form-desc">Choose a strong new password for your account. Make sure it's at least 8 characters with a mix of letters and numbers.</p>

            <!-- Steps -->
            <div class="steps-row">
                <div class="step-item">
                    <div class="step-num done">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="step-lbl done">Email Sent</span>
                </div>
                <div class="step-conn"></div>
                <div class="step-item">
                    <div class="step-num done">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="step-lbl done">Link Verified</span>
                </div>
                <div class="step-conn"></div>
                <div class="step-item">
                    <div class="step-num active">3</div>
                    <span class="step-lbl active">New Password</span>
                </div>
            </div>

            <!-- Success alert -->
            <div x-show="success" x-transition class="alert alert-success">
                <svg class="alert-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <div x-text="successMessage"></div>
                    <div style="font-size:12px;margin-top:6px;color:#16a34a;">Redirecting to login in 3 seconds...</div>
                    <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="go-login-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        Go to Login Now
                    </a>
                </div>
            </div>

            <!-- Error alert -->
            <div x-show="error" x-transition class="alert alert-error">
                <svg class="alert-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span x-text="error"></span>
            </div>

            <!-- Form -->
            <div class="form-fields" x-show="!success">
                <form @submit.prevent="submitReset">
                    <input type="hidden" x-model="formData.token">

                    <!-- New Password -->
                    <div>
                        <label class="f-label">New Password</label>
                        <div class="f-wrap">
                            <span class="f-icon">
                                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password"
                                   x-model="formData.password"
                                   :type="showPass1 ? 'text' : 'password'"
                                   required minlength="8"
                                   @input="checkStrength()"
                                   placeholder="Enter new password (min 8 characters)"
                                   class="f-input">
                            <button type="button" class="pass-eye" @click="showPass1 = !showPass1">
                                <svg x-show="!showPass1" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass1" x-cloak width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div class="strength-wrap" x-show="formData.password.length > 0">
                            <div class="strength-track">
                                <div class="strength-fill"
                                     :style="{ width: strengthPct + '%', background: strengthColor }">
                                </div>
                            </div>
                            <span class="strength-label" :style="{ color: strengthColor }" x-text="strengthLabel"></span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div style="margin-top:20px;">
                        <label class="f-label">Confirm Password</label>
                        <div class="f-wrap">
                            <span class="f-icon">
                                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            <input :type="showPass2 ? 'text' : 'password'"
                                   x-model="formData.password_confirm"
                                   required minlength="8"
                                   placeholder="Confirm your new password"
                                   :class="confirmClass"
                                   class="f-input">
                            <button type="button" class="pass-eye" @click="showPass2 = !showPass2">
                                <svg x-show="!showPass2" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass2" x-cloak width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Match indicator -->
                        <div class="match-row"
                             x-show="formData.password_confirm.length > 0">
                            <svg x-show="formData.password === formData.password_confirm" width="13" height="13" fill="#10b981" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <svg x-show="formData.password !== formData.password_confirm" width="13" height="13" fill="#ef4444" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="formData.password === formData.password_confirm ? 'Passwords match' : 'Passwords do not match'"
                                  :style="{ color: formData.password === formData.password_confirm ? '#10b981' : '#ef4444' }"
                                  style="font-size:11.5px; font-weight:500;"></span>
                        </div>
                    </div>

                    <!-- SSL strip -->
                    <div class="ssl-strip" style="margin-top:20px;">
                        <svg width="13" height="13" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Your new password is transmitted with 256-bit SSL encryption</span>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn" :disabled="isSubmitting">
                        <svg x-show="isSubmitting" x-cloak class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                            <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg x-show="!isSubmitting" width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span x-show="!isSubmitting">Reset Password</span>
                        <span x-show="isSubmitting" x-cloak>Resetting...</span>
                    </button>

                    <div class="back-row">
                        <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="back-link">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="form-footer">
                <span class="footer-copy">© 2025 Mindware InfoTech</span>
                <span class="footer-copy">Secure · Encrypted · Protected</span>
            </div>

        </div>

        <!-- ══ RIGHT — HERO PANEL ══ -->
        <div class="hero-panel">
            <div class="h-ring h-ring-1"></div>
            <div class="h-ring h-ring-2"></div>

            <div class="hero-inner">
                <h2 class="hero-heading">Create a <span class="hi">Strong</span><br>New Password<br>with Confidence.</h2>
                <p class="hero-desc">Your security is our top priority. Choose a password that's hard to guess and we'll protect it with enterprise-grade encryption.</p>

                <!-- Password requirements -->
                <div class="req-section">
                    <div class="req-label">Password Requirements</div>
                    <div class="req-list">
                        <div class="req-item" :class="{ met: formData.password.length >= 8 }">
                            <div class="req-icon" :style="formData.password.length >= 8 ? 'background:#dcfce7' : 'background:#f1f5f9'">
                                <svg width="14" height="14" :fill="formData.password.length >= 8 ? '#10b981' : '#94a3b8'" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="req-text">Minimum 8 characters</span>
                        </div>
                        <div class="req-item" :class="{ met: /[A-Z]/.test(formData.password) }">
                            <div class="req-icon" :style="/[A-Z]/.test(formData.password) ? 'background:#dcfce7' : 'background:#f1f5f9'">
                                <svg width="14" height="14" :fill="/[A-Z]/.test(formData.password) ? '#10b981' : '#94a3b8'" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="req-text">At least one uppercase letter</span>
                        </div>
                        <div class="req-item" :class="{ met: /[0-9]/.test(formData.password) }">
                            <div class="req-icon" :style="/[0-9]/.test(formData.password) ? 'background:#dcfce7' : 'background:#f1f5f9'">
                                <svg width="14" height="14" :fill="/[0-9]/.test(formData.password) ? '#10b981' : '#94a3b8'" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="req-text">At least one number</span>
                        </div>
                        <div class="req-item" :class="{ met: /[^A-Za-z0-9]/.test(formData.password) }">
                            <div class="req-icon" :style="/[^A-Za-z0-9]/.test(formData.password) ? 'background:#dcfce7' : 'background:#f1f5f9'">
                                <svg width="14" height="14" :fill="/[^A-Za-z0-9]/.test(formData.password) ? '#10b981' : '#94a3b8'" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="req-text">Special character (optional, recommended)</span>
                        </div>
                    </div>
                </div>

                <!-- Security cards -->
                <div class="feature-cards">
                    <div class="feat-card">
                        <div class="feat-icon" style="background:#eff6ff;">
                            <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="feat-title">Encrypted Storage</div>
                            <div class="feat-desc">Password is hashed & salted before storage — never stored in plain text.</div>
                        </div>
                    </div>
                    <div class="feat-card">
                        <div class="feat-icon" style="background:#f0fdf4;">
                            <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="feat-title">One-Time Reset Link</div>
                            <div class="feat-desc">This link becomes invalid immediately after your password is reset.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function resetPasswordForm() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token') || '<?= htmlspecialchars($token ?? '') ?>';

            return {
                isSubmitting: false,
                error: '<?= htmlspecialchars($error ?? '') ?>',
                success: false,
                successMessage: '',
                showPass1: false,
                showPass2: false,
                strengthPct: 0,
                strengthColor: '#e2e8f0',
                strengthLabel: '',
                formData: {
                    token: token,
                    password: '',
                    password_confirm: ''
                },

                get confirmClass() {
                    if (!this.formData.password_confirm.length) return '';
                    return this.formData.password === this.formData.password_confirm ? 'match' : 'mismatch';
                },

                checkStrength() {
                    const p = this.formData.password;
                    let score = 0;
                    if (p.length >= 8)  score++;
                    if (p.length >= 12) score++;
                    if (/[A-Z]/.test(p)) score++;
                    if (/[0-9]/.test(p)) score++;
                    if (/[^A-Za-z0-9]/.test(p)) score++;
                    if (score <= 1) { this.strengthPct = 20; this.strengthColor = '#ef4444'; this.strengthLabel = 'Weak'; }
                    else if (score === 2) { this.strengthPct = 40; this.strengthColor = '#f97316'; this.strengthLabel = 'Fair'; }
                    else if (score === 3) { this.strengthPct = 65; this.strengthColor = '#eab308'; this.strengthLabel = 'Good'; }
                    else if (score === 4) { this.strengthPct = 85; this.strengthColor = '#22c55e'; this.strengthLabel = 'Strong'; }
                    else { this.strengthPct = 100; this.strengthColor = '#10b981'; this.strengthLabel = 'Very Strong'; }
                },

                async submitReset() {
                    this.isSubmitting = true;
                    this.error = '';

                    if (!this.formData.token) {
                        this.error = 'Invalid reset token';
                        this.isSubmitting = false;
                        return;
                    }
                    if (this.formData.password.length < 8) {
                        this.error = 'Password must be at least 8 characters long';
                        this.isSubmitting = false;
                        return;
                    }
                    if (this.formData.password !== this.formData.password_confirm) {
                        this.error = 'Passwords do not match';
                        this.isSubmitting = false;
                        return;
                    }

                    try {
                        const response = await fetch('/reset-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        // Handle wrapped response format from Response::json()
                        const isSuccess = response.ok && (data.status === true || data.data?.success === true);
                        const successMsg = data.message || data.data?.message || 'Password reset successfully! You can now login with your new password.';

                        if (isSuccess) {
                            this.success = true;
                            this.successMessage = successMsg;
                            setTimeout(() => {
                                window.location.href = '/login';
                            }, 3000);
                        } else {
                            this.error = data.error || data.data?.error || data.message || 'Failed to reset password';
                        }
                    } catch (error) {
                        this.error = 'Error: ' + error.message;
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            }
        }
    </script>
</body>
</html>