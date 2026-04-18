<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Forgot Password - Mindware Infotech</title>
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

        /* ── Dot grid ── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.065) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none; z-index: 0;
        }

        /* ── Blobs ── */
        .blob {
            position: fixed; border-radius: 50%;
            filter: blur(64px); pointer-events: none; z-index: 0;
        }
        .blob-tl { width: 500px; height: 500px; top: -180px; left: -180px;
                   background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%); }
        .blob-br { width: 400px; height: 400px; bottom: -140px; right: -140px;
                   background: radial-gradient(circle, rgba(6,182,212,0.09) 0%, transparent 70%); }

        /* ══════════════════════════════
           FULL-WIDTH PAGE
        ══════════════════════════════ */
        .page-wrap {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative; z-index: 1;
        }

        /* ══════════════════════════════
           LEFT — FORM PANEL
        ══════════════════════════════ */
        .form-panel {
            width: 48%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 64px;
            position: relative;
        }

        /* Subtle right border glow */
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
            font-size: 16px; font-weight: 700; color: #0f172a; letter-spacing: -0.2px;
        }
        .brand-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }

        /* Icon area */
        .icon-area {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 28px;
            animation: up 0.5s 0.1s both;
        }

        .icon-box {
            width: 56px; height: 56px; border-radius: 16px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1.5px solid #bfdbfe;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .icon-text { }
        .icon-text .step-label {
            font-size: 11px; font-weight: 600; color: #2563eb;
            text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;
        }
        .icon-text .step-title {
            font-family: var(--font-head);
            font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px;
        }

        /* Description */
        .form-desc {
            font-size: 14px; color: #64748b; line-height: 1.7;
            margin-bottom: 32px;
            max-width: 380px;
            animation: up 0.5s 0.15s both;
        }

        /* Steps indicator */
        .steps-row {
            display: flex; align-items: center; gap: 0;
            margin-bottom: 32px;
            animation: up 0.5s 0.18s both;
        }
        .step-item {
            display: flex; align-items: center; gap: 8px;
        }
        .step-num {
            width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11.5px; font-weight: 700; flex-shrink: 0;
        }
        .step-num.active { background: var(--blue); color: #fff; }
        .step-num.done   { background: #dcfce7; color: var(--green); }
        .step-num.pending{ background: #f1f5f9; color: #94a3b8; }
        .step-label-text { font-size: 12px; font-weight: 500; color: #94a3b8; }
        .step-label-text.active { color: #0f172a; }
        .step-connector {
            flex: 1; height: 1.5px; background: #e2e8f0; margin: 0 10px;
        }

        /* Alerts */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            border-radius: 12px; padding: 13px 15px; margin-bottom: 20px;
            font-size: 13.5px; line-height: 1.55;
            animation: up 0.4s both;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-icon { flex-shrink: 0; margin-top: 1px; }

        /* Dev link */
        .dev-link-wrap {
            margin-top: 8px; padding-top: 8px;
            border-top: 1px solid rgba(21,128,61,0.15);
        }
        .dev-label { font-size: 11px; color: #16a34a; font-weight: 500; margin-bottom: 4px; }
        .dev-link { font-size: 11.5px; color: #2563eb; word-break: break-all; text-decoration: underline; }

        /* Form */
        .form-fields { animation: up 0.5s 0.22s both; }

        .f-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px; letter-spacing: 0.1px;
        }

        .f-wrap { position: relative; margin-bottom: 20px; }

        .f-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; display: flex;
        }

        .f-input {
            width: 100%; padding: 13.5px 14px 13.5px 44px;
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
            letter-spacing: 0.1px;
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
        .back-row {
            text-align: center;
        }
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

        /* ══════════════════════════════
           RIGHT — HERO PANEL
        ══════════════════════════════ */
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

        /* Dot pattern */
        .hero-panel::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.08) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
        }
        /* Light mesh */
        .hero-panel::after {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 90% 10%,  rgba(56,189,248,0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 5%  90%, rgba(99,102,241,0.14) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Rings */
        .h-ring {
            position: absolute; border-radius: 50%; pointer-events: none;
        }
        .h-ring-1 { top: -100px; right: -100px; width: 360px; height: 360px;
                    border: 1px solid rgba(37,99,235,0.1); }
        .h-ring-2 { bottom: -120px; left: -120px; width: 400px; height: 400px;
                    border: 1px solid rgba(37,99,235,0.08); }

        .hero-inner { position: relative; z-index: 2; }

        /* Hero heading */
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

        /* Feature cards */
        .feature-cards {
            display: flex; flex-direction: column; gap: 14px;
            margin-bottom: 40px;
            animation: up 0.55s 0.25s both;
        }

        .feat-card {
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(37,99,235,0.1);
            border-radius: 14px; padding: 16px 18px;
            display: flex; align-items: flex-start; gap: 14px;
            backdrop-filter: blur(6px);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .feat-card:hover {
            box-shadow: 0 4px 20px rgba(37,99,235,0.1);
            transform: translateX(4px);
        }

        .feat-icon {
            width: 40px; height: 40px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .feat-title {
            font-family: var(--font-head);
            font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 3px;
        }
        .feat-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }

        /* How it works */
        .how-section {
            animation: up 0.55s 0.32s both;
        }

        .how-label {
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.9px;
            margin-bottom: 14px;
        }

        .how-steps {
            display: flex; flex-direction: column; gap: 12px;
        }

        .how-step {
            display: flex; align-items: center; gap: 12px;
        }

        .how-num {
            width: 28px; height: 28px; border-radius: 8px;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff; font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(37,99,235,0.25);
        }

        .how-text { font-size: 13.5px; color: #334155; font-weight: 500; }

        @keyframes up {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .page-wrap { flex-direction: column-reverse; }
            .form-panel, .hero-panel { width: 100%; padding: 40px 28px; }
            .form-panel::after { display: none; }
            .hero-panel { min-height: 360px; }
            .hero-heading { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="blob blob-tl"></div>
    <div class="blob blob-br"></div>

    <div class="page-wrap" x-data="forgotPasswordForm()" x-cloak>

        <!-- ══════════════════════════════
             LEFT — FORM PANEL
        ══════════════════════════════ -->
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
                            <linearGradient id="kg" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#3b82f6"/>
                                <stop offset="100%" stop-color="#1d4ed8"/>
                            </linearGradient>
                        </defs>
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke="url(#kg)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                <div class="icon-text">
                    <div class="step-label">Account Recovery</div>
                    <div class="step-title">Forgot Password?</div>
                </div>
            </div>

            <!-- Description -->
            <p class="form-desc">
                No worries! Enter your registered email address below and we'll send you a secure link to reset your password.
            </p>

            <!-- Steps indicator -->
            <div class="steps-row">
                <div class="step-item">
                    <div class="step-num active">1</div>
                    <span class="step-label-text active">Enter Email</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-num pending">2</div>
                    <span class="step-label-text">Check Inbox</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-num pending">3</div>
                    <span class="step-label-text">Reset Password</span>
                </div>
            </div>

            <!-- Success alert -->
            <div x-show="success" x-transition class="alert alert-success">
                <svg class="alert-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <span x-text="successMessage"></span>
                    <div x-show="resetLink" class="dev-link-wrap">
                        <div class="dev-label">Development mode — Reset link:</div>
                        <a :href="resetLink" class="dev-link" x-text="resetLink"></a>
                    </div>
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
            <div class="form-fields">
                <form @submit.prevent="submitRequest">

                    <label class="f-label">Email Address</label>
                    <div class="f-wrap">
                        <span class="f-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input type="email"
                               x-model="formData.email"
                               required
                               placeholder="admin@company.com"
                               class="f-input">
                    </div>

                    <!-- SSL strip -->
                    <div class="ssl-strip">
                        <svg width="13" height="13" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Reset link is encrypted & expires in 15 minutes</span>
                    </div>

                    <button type="submit" class="submit-btn" :disabled="isSubmitting">
                        <svg x-show="isSubmitting" x-cloak class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                            <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg x-show="!isSubmitting" width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span x-show="!isSubmitting">Send Reset Link</span>
                        <span x-show="isSubmitting" x-cloak>Sending...</span>
                    </button>

                    <div class="back-row">
                        <?php $isAdminPath = (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0); ?>
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

        <!-- ══════════════════════════════
             RIGHT — HERO PANEL
        ══════════════════════════════ -->
        <div class="hero-panel">
            <div class="h-ring h-ring-1"></div>
            <div class="h-ring h-ring-2"></div>

            <div class="hero-inner">

                <h2 class="hero-heading">Reset Your<br>Access <span class="hi">Securely</span><br>& Quickly.</h2>
                <p class="hero-desc">We'll send you a secure, time-limited link to reset your password. Your account stays fully protected throughout the process.</p>

                <!-- Feature cards -->
                <div class="feature-cards">
                    <div class="feat-card">
                        <div class="feat-icon" style="background:#eff6ff;">
                            <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="feat-title">Encrypted Reset Links</div>
                            <div class="feat-desc">Every link is uniquely generated with 256-bit encryption and tied to your account only.</div>
                        </div>
                    </div>

                    <div class="feat-card">
                        <div class="feat-icon" style="background:#fff7ed;">
                            <svg width="20" height="20" fill="none" stroke="#f97316" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" d="M12 7v5l3 2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="feat-title">Expires Automatically</div>
                            <div class="feat-desc">Reset links expire after 15 minutes to prevent unauthorized use, even if forwarded.</div>
                        </div>
                    </div>

                    <div class="feat-card">
                        <div class="feat-icon" style="background:#f0fdf4;">
                            <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="feat-title">No Account Disruption</div>
                            <div class="feat-desc">Your account remains active and all your data stays safe during the reset process.</div>
                        </div>
                    </div>
                </div>

                <!-- How it works -->
                <div class="how-section">
                    <div class="how-label">How it works</div>
                    <div class="how-steps">
                        <div class="how-step">
                            <div class="how-num">1</div>
                            <span class="how-text">Enter your registered email address</span>
                        </div>
                        <div class="how-step">
                            <div class="how-num">2</div>
                            <span class="how-text">Check your inbox for the reset link</span>
                        </div>
                        <div class="how-step">
                            <div class="how-num">3</div>
                            <span class="how-text">Click the link and set a new password</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- end page-wrap -->

    <script>
        function forgotPasswordForm() {
            return {
                isSubmitting: false,
                error: '',
                success: false,
                successMessage: '',
                resetLink: '',
                formData: { email: '' },

                async submitRequest() {
                    this.isSubmitting = true;
                    this.error = '';
                    this.success = false;

                    try {
                        const endpoint = window.location.pathname || '/forgot-password';
                        const response = await fetch(endpoint, {
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
                        const isSuccess = response.ok && (data.success === true || data.status === true || data.data?.success === true);
                        const successMsg = data.message || data.data?.message || 'Reset link sent successfully';
                        const resetLink = data.data?.reset_link;

                        if (isSuccess) {
                            this.success = true;
                            this.successMessage = successMsg;
                            if (resetLink) {
                                this.resetLink = resetLink;
                            }
                            setTimeout(() => {
                                if (resetLink) {
                                    window.location.href = resetLink;
                                }
                            }, 3000);
                        } else {
                            this.error = data.error || data.message || 'Failed to send reset link';
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
