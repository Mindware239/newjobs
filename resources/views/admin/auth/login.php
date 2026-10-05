<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Login' ?> - Jobsence</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }

        :root {
            --primary:    #f05537;
            --primary-hover: #d94426;
            --slate-50:   #f8fafc;
            --slate-100:  #f1f5f9;
            --slate-200:  #e2e8f0;
            --slate-400:  #94a3b8;
            --slate-500:  #64748b;
            --slate-600:  #475569;
            --slate-700:  #334155;
            --slate-900:  #0f172a;
            --font-body:  'Plus Jakarta Sans', sans-serif;
            --font-head:  'Outfit', sans-serif;
        }

        html, body {
            width: 100%; min-height: 100vh;
            font-family: var(--font-body);
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--slate-900);
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--slate-200);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 54px; height: 54px; border-radius: 14px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }

        .brand-logo span {
            font-family: var(--font-head);
            font-size: 24px; font-weight: 800; color: #fff;
        }

        .brand-name {
            font-family: var(--font-head);
            font-size: 22px; font-weight: 700;
            color: var(--slate-900);
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 13px; color: var(--slate-500);
            margin-top: 4px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .title {
            font-family: var(--font-head);
            font-size: 24px; font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px; color: var(--slate-500);
        }

        /* Alert */
        .error-alert {
            display: flex; align-items: flex-start; gap: 10px;
            background: #fef2f2; border: 1px solid #fee2e2;
            color: #b91c1c; border-radius: 10px;
            padding: 14px; margin-bottom: 24px;
            font-size: 13.5px; line-height: 1.5;
        }

        /* Form */
        .fields { display: flex; flex-direction: column; gap: 20px; }

        .f-label {
            font-size: 14px; font-weight: 600;
            color: var(--slate-700); margin-bottom: 8px;
            display: block;
        }

        .f-wrap { position: relative; }

        .f-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400); pointer-events: none;
            display: flex;
        }

        .f-input {
            width: 100%; padding: 12px 14px 12px 44px;
            border: 1px solid #d1d5db;
            border-radius: 10px; background: #fff;
            font-size: 15px; color: var(--slate-900);
            font-family: var(--font-body);
            transition: all 0.2s;
            outline: none;
        }

        .f-input::placeholder { color: #9ca3af; }
        .f-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(240, 85, 55, 0.1);
        }

        .pass-eye {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--slate-400); padding: 4px;
            display: flex;
        }

        /* Captcha */
        .captcha-row {
            display: flex; align-items: center; gap: 10px; margin-bottom: 8px;
        }

        .captcha-img {
            height: 42px; border-radius: 8px;
            border: 1px solid #d1d5db;
            cursor: pointer;
        }

        .captcha-btn {
            width: 36px; height: 36px; border-radius: 8px;
            background: #f3f4f6; border: 1px solid #d1d5db;
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; color: var(--slate-500);
        }

        .captcha-note {
            font-size: 11px; color: var(--slate-500);
        }

        .mono { font-family: 'SF Mono', 'Fira Code', monospace; letter-spacing: 0.1em; }

        /* Options */
        .options-row {
            display: flex; align-items: center; justify-content: space-between;
        }

        .rem-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .rem-check { width: 16px; height: 16px; accent-color: var(--primary); }
        .rem-text { font-size: 14px; color: var(--slate-600); }

        .forgot {
            font-size: 14px; font-weight: 600; color: var(--primary);
            text-decoration: none;
        }

        /* Submit */
        .submit-btn {
            width: 100%; padding: 14px;
            background: var(--primary);
            color: #fff; border: none; border-radius: 10px;
            font-family: var(--font-body);
            font-size: 16px; font-weight: 600;
            cursor: pointer; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: background 0.2s;
        }
        .submit-btn:hover:not(:disabled) { background: var(--primary-hover); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--slate-100);
        }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 14px; font-weight: 500; color: var(--slate-500);
            text-decoration: none; transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        .copy-text { font-size: 12px; color: var(--slate-400); margin-top: 16px; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.7s linear infinite; }
    </style>
</head>
<body x-data="loginForm()">

<div class="login-card">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-logo"><span>JS</span></div>
        <h1 class="brand-name">Jobsence</h1>
        <p class="brand-sub">Admin Control Panel</p>
    </div>

    <!-- Header -->
    <div class="header">
        <h2 class="title">Welcome back</h2>
        <p class="subtitle">Please enter your credentials to log in.</p>
    </div>

    <?php if (isset($error) && $error): ?>
    <div class="error-alert">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:2px;">
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
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="email" name="email" type="email" required
                           x-model="formData.email"
                           class="f-input"
                           placeholder="name@company.com">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="f-label">Password</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="password" name="password"
                           :type="showPassword ? 'text' : 'password'"
                           required x-model="formData.password"
                           class="f-input"
                           placeholder="••••••••">
                    <button type="button" class="pass-eye" @click="showPassword = !showPassword">
                        <svg x-show="!showPassword" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Captcha -->
            <div>
                <label for="captcha" class="f-label">Verification</label>
                <div class="captcha-row">
                    <img id="captcha-image" src="/admin/captcha/generate" alt="CAPTCHA"
                         class="captcha-img" @click="refreshCaptcha()" @error="captchaError = true">
                    <button type="button" class="captcha-btn" @click="refreshCaptcha()" title="Refresh captcha">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                    <span class="captcha-note">Click code to refresh</span>
                </div>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <input id="captcha" name="captcha_code" type="text" required
                           x-model="formData.captcha"
                           class="f-input mono"
                           placeholder="Enter verification code"
                           maxlength="6" autocomplete="off">
                </div>
            </div>

            <!-- Options -->
            <div class="options-row">
                <label class="rem-label" for="remember">
                    <input id="remember" name="remember" type="checkbox" class="rem-check">
                    <span class="rem-text">Keep me logged in</span>
                </label>
                <a href="/admin/forgot-password" class="forgot">Forgot?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg x-show="isSubmitting" x-cloak class="spin" width="18" height="18" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                    <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-show="!isSubmitting">Sign In</span>
                <span x-show="isSubmitting" x-cloak>Signing in...</span>
            </button>

        </div>
    </form>

    <div class="footer">
        <a href="/" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>
        <p class="copy-text">&copy; 2025 Jobsence. All rights reserved.</p>
    </div>

</div>

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
