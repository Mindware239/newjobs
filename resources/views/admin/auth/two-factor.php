<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Two-Factor Authentication' ?> - Mindware InfoTech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #edf2fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        /* Dot grid bg */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(37,99,235,0.07) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }
        /* Soft blobs */
        body::after {
            content: '';
            position: fixed;
            top: -160px; left: -160px;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .blob-r {
            position: fixed;
            bottom: -140px; right: -140px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(6,182,212,0.09) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Card */
        .card {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            background: #fff;
            border-radius: 24px;
            padding: 44px 40px 36px;
            box-shadow:
                0 0 0 1px rgba(15,23,42,0.06),
                0 4px 6px rgba(15,23,42,0.04),
                0 20px 48px rgba(15,23,42,0.12);
            animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(22px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Brand top */
        .brand-top {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 32px;
            animation: up 0.5s 0.08s both;
        }
        .brand-logo {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 10px rgba(37,99,235,0.3);
            flex-shrink: 0;
        }
        .brand-logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 17px; font-weight: 800; color: #fff;
        }
        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 15px; font-weight: 700; color: #0f172a; letter-spacing: -0.2px;
        }

        /* Shield icon area */
        .shield-area {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 28px;
            animation: up 0.5s 0.13s both;
        }
        .shield-circle {
            width: 72px; height: 72px; border-radius: 20px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1.5px solid #bfdbfe;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }
        .shield-circle::after {
            content: '';
            position: absolute; inset: -6px;
            border-radius: 26px;
            border: 1px dashed rgba(37,99,235,0.2);
            animation: rotateSlow 12s linear infinite;
        }
        @keyframes rotateSlow { to { transform: rotate(360deg); } }

        /* OTP dots indicator */
        .otp-dots {
            display: flex; align-items: center; gap: 7px;
            margin-top: 4px;
        }
        .otp-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #e2e8f0;
            transition: background 0.2s, transform 0.2s;
        }
        .otp-dot.active { background: #2563eb; transform: scale(1.2); }

        /* Heading */
        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 23px; font-weight: 800;
            color: #0f172a; letter-spacing: -0.4px;
            text-align: center; margin-bottom: 8px;
            animation: up 0.5s 0.16s both;
        }
        .card-desc {
            font-size: 13.5px; color: #64748b;
            text-align: center; line-height: 1.65;
            margin-bottom: 28px;
            animation: up 0.5s 0.2s both;
        }
        .card-desc strong { color: #1e40af; font-weight: 600; }

        /* Error */
        .error-box {
            display: flex; align-items: flex-start; gap: 9px;
            background: #fef2f2; border: 1px solid #fecaca;
            color: #dc2626; border-radius: 11px;
            padding: 11px 13px; margin-bottom: 18px;
            font-size: 13px; line-height: 1.5;
        }

        /* Form */
        .form-wrap { animation: up 0.5s 0.24s both; }

        .f-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 8px; letter-spacing: 0.1px;
        }

        .otp-input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e2e8f0;
            border-radius: 13px;
            background: #f8fafc;
            font-size: 22px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-weight: 600;
            color: #0f172a;
            letter-spacing: 0.35em;
            text-align: center;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
            margin-bottom: 16px;
        }
        .otp-input::placeholder {
            color: #cbd5e1; font-size: 15px; letter-spacing: 0.2em;
        }
        .otp-input:hover:not(:focus) { border-color: #cbd5e1; }
        .otp-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
            background: #fff;
        }

        /* Submit */
        .submit-btn {
            width: 100%; padding: 14px 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff; border: none; border-radius: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22), 0 8px 24px rgba(37,99,235,0.16);
            transition: transform 0.12s, box-shadow 0.18s, opacity 0.15s;
            margin-bottom: 14px;
        }
        .submit-btn:hover {
            box-shadow: 0 4px 12px rgba(37,99,235,0.3), 0 12px 32px rgba(37,99,235,0.2);
            transform: translateY(-1px);
        }
        .submit-btn:active { transform: translateY(0); }

        /* Timer strip */
        .timer-strip {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 10px 14px;
            margin-bottom: 20px;
        }
        .timer-strip span { font-size: 12.5px; color: #64748b; font-weight: 500; }
        .timer-value { color: #2563eb !important; font-weight: 700 !important; font-variant-numeric: tabular-nums; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 16px;
        }
        .divider-line { flex: 1; height: 1px; background: #f1f5f9; }
        .divider-text { font-size: 11.5px; color: #94a3b8; font-weight: 500; }

        /* Resend */
        .resend-row {
            text-align: center;
            font-size: 13px; color: #64748b;
        }
        .resend-btn {
            background: none; border: none; cursor: pointer;
            font-size: 13px; font-weight: 600; color: #2563eb;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 0; transition: color 0.15s;
        }
        .resend-btn:hover { color: #1d4ed8; }

        /* SSL */
        .ssl-row {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            margin-top: 20px; padding-top: 18px;
            border-top: 1px solid #f1f5f9;
        }
        .ssl-row span { font-size: 11.5px; color: #94a3b8; }
        .ssl-dot { color: #10b981; }

        /* Back link */
        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 5px;
            margin-top: 14px;
            font-size: 13px; color: #94a3b8; text-decoration: none;
            transition: color 0.15s;
        }
        .back-link:hover { color: #2563eb; }

        @keyframes up {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="blob-r"></div>

    <div class="card">

        <!-- Brand -->
        <div class="brand-top">
            <div class="brand-logo"><span>M</span></div>
            <span class="brand-name">Mindware InfoTech</span>
        </div>

        <!-- Shield icon -->
        <div class="shield-area">
            <div class="shield-circle">
                <svg width="34" height="34" fill="none" viewBox="0 0 24 24">
                    <defs>
                        <linearGradient id="sg" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#1d4ed8"/>
                        </linearGradient>
                    </defs>
                    <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.25C17.25 22.15 21 17.25 21 12V6L12 2z" fill="url(#sg)"/>
                    <rect x="8.5" y="11" width="7" height="5.5" rx="1" fill="rgba(255,255,255,0.25)"/>
                    <path d="M9.5 11V9a2.5 2.5 0 015 0v2" stroke="rgba(255,255,255,0.9)" stroke-width="1.4" stroke-linecap="round" fill="none"/>
                    <circle cx="12" cy="13.2" r="1" fill="#fff"/>
                    <rect x="11.3" y="13.2" width="1.4" height="2" rx="0.7" fill="#fff"/>
                </svg>
            </div>

            <!-- OTP progress dots -->
            <div class="otp-dots" id="otp-dots">
                <div class="otp-dot" id="d0"></div>
                <div class="otp-dot" id="d1"></div>
                <div class="otp-dot" id="d2"></div>
                <div class="otp-dot" id="d3"></div>
                <div class="otp-dot" id="d4"></div>
                <div class="otp-dot" id="d5"></div>
            </div>
        </div>

        <!-- Title -->
        <h1 class="card-title">Two-Factor Authentication</h1>
        <p class="card-desc">
            We sent a 6-digit code to<br>
            <strong><?= htmlspecialchars($email ?? 'your email') ?></strong><br>
            Enter it below to verify your identity.
        </p>

        <!-- Error -->
        <?php if (!empty($error)): ?>
        <div class="error-box">
            <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px;">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="form-wrap">
            <form method="POST" action="/admin/2fa/verify">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <label for="otp" class="f-label">Verification Code</label>
                <input
                    id="otp"
                    name="otp"
                    type="text"
                    maxlength="6"
                    required
                    class="otp-input"
                    placeholder="· · · · · ·"
                    autofocus
                    autocomplete="one-time-code"
                    inputmode="numeric"
                >

                <button type="submit" class="submit-btn">
                    <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Verify & Sign in
                </button>
            </form>

            <!-- Timer -->
            <div class="timer-strip">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" d="M12 7v5l3 2"/>
                </svg>
                <span>Code expires in</span>
                <span class="timer-value" id="timer">10:00</span>
            </div>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">didn't receive it?</span>
                <div class="divider-line"></div>
            </div>

            <div class="resend-row">
                <span>Check spam folder or </span>
                <button class="resend-btn" onclick="resendCode()">Resend Code</button>
            </div>
        </div>

        <!-- SSL -->
        <div class="ssl-row">
            <svg width="12" height="12" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span class="ssl-dot">●</span>
            <span>Secured with 256-bit SSL encryption</span>
        </div>

        <a href="/admin/login" class="back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Login
        </a>
    </div>

    <script>
        // OTP dots updater
        const otpInput = document.getElementById('otp');
        const dots = [0,1,2,3,4,5].map(i => document.getElementById('d'+i));

        otpInput.addEventListener('input', () => {
            const len = otpInput.value.replace(/\D/g,'').length;
            dots.forEach((d, i) => {
                d.classList.toggle('active', i < len);
            });
            // Filter non-numeric
            otpInput.value = otpInput.value.replace(/\D/g,'');
        });

        // Countdown timer (10 min)
        let seconds = 600;
        const timerEl = document.getElementById('timer');

        function updateTimer() {
            const m = String(Math.floor(seconds / 60)).padStart(2,'0');
            const s = String(seconds % 60).padStart(2,'0');
            timerEl.textContent = m + ':' + s;
            if (seconds <= 60) timerEl.style.color = '#dc2626';
            if (seconds > 0) { seconds--; setTimeout(updateTimer, 1000); }
            else { timerEl.textContent = 'Expired'; timerEl.style.color = '#dc2626'; }
        }
        updateTimer();

        // Resend (placeholder — wire to your backend)
        function resendCode() {
            alert('A new code has been sent to your email.');
        }
    </script>
</body>
</html>