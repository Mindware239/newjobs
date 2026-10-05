<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Login | Jobsence Jobs</title>
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
            padding: 40px;
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
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 48px; height: 48px; border-radius: 12px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
        }

        .brand-logo span {
            font-family: var(--font-head);
            font-size: 22px; font-weight: 800; color: #fff;
        }

        .brand-name {
            font-family: var(--font-head);
            font-size: 20px; font-weight: 700;
            color: var(--slate-900);
            letter-spacing: -0.5px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .title {
            font-family: var(--font-head);
            font-size: 24px; font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 14px; color: var(--slate-500);
        }

        /* Alert */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            border-radius: 10px; padding: 12px; margin-bottom: 20px;
            font-size: 13px; line-height: 1.5;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; }

        /* Auth Mode Toggle */
        .auth-toggle {
            display: flex;
            gap: 4px;
            background: var(--slate-100);
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .auth-toggle-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: var(--slate-500);
        }
        .auth-toggle-btn.active {
            background: #fff;
            color: var(--slate-900);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Form */
        .fields { display: flex; flex-direction: column; gap: 16px; }

        .f-label {
            font-size: 13px; font-weight: 600;
            color: var(--slate-700); margin-bottom: 6px;
            display: block;
        }

        .f-wrap { position: relative; }

        .f-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400); pointer-events: none;
            display: flex;
        }

        .f-input {
            width: 100%; padding: 11px 12px 11px 40px;
            border: 1px solid #d1d5db;
            border-radius: 10px; background: #fff;
            font-size: 14px; color: var(--slate-900);
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
            position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--slate-400); padding: 4px;
            display: flex;
        }

        /* Options */
        .options-row {
            display: flex; align-items: center; justify-content: space-between;
        }

        .rem-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .rem-check { width: 15px; height: 15px; accent-color: var(--primary); }
        .rem-text { font-size: 13px; color: var(--slate-600); }

        .forgot {
            font-size: 13px; font-weight: 600; color: var(--primary);
            text-decoration: none;
        }

        /* Submit */
        .submit-btn {
            width: 100%; padding: 12px;
            background: var(--primary);
            color: #fff; border: none; border-radius: 10px;
            font-family: var(--font-body);
            font-size: 15px; font-weight: 600;
            cursor: pointer; margin-top: 4px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.2s;
        }
        .submit-btn:hover:not(:disabled) { background: var(--primary-hover); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Social */
        .social-header {
            display: flex; align-items: center; gap: 10px; margin: 20px 0;
        }
        .social-line { flex: 1; height: 1px; background: var(--slate-200); }
        .social-text { font-size: 12px; color: var(--slate-400); font-weight: 500; }

        .social-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .social-btn {
            display: flex; align-items: center; justify-content: center;
            padding: 10px; border: 1px solid var(--slate-200); border-radius: 10px;
            transition: all 0.2s;
        }
        .social-btn:hover { background: var(--slate-50); border-color: var(--slate-400); }
        .social-btn img { width: 20px; height: 20px; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--slate-100);
        }

        .footer-text { font-size: 13px; color: var(--slate-500); }
        .footer-link { font-weight: 700; color: var(--primary); text-decoration: none; }

        .back-home {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 500; color: var(--slate-500);
            text-decoration: none; margin-top: 16px;
        }
        .back-home:hover { color: var(--primary); }

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
    </div>

    <!-- Header -->
    <div class="header">
        <h2 class="title">Welcome Back</h2>
        <p class="subtitle">Login to your candidate or employer account</p>
    </div>

    <!-- Alerts -->
    <div x-show="registrationSuccess" x-cloak class="alert alert-success">
        <span x-text="registrationMessage"></span>
    </div>
    <div x-show="success" x-cloak class="alert alert-success">
        <span x-text="success"></span>
    </div>
    <div x-show="error" x-cloak class="alert alert-error">
        <span x-text="error"></span>
    </div>

    <!-- Mode Toggle -->
    <div class="auth-toggle">
        <button type="button" @click="authMode = 'password'" :class="authMode === 'password' ? 'active' : ''" class="auth-toggle-btn">Email Password</button>
        <button type="button" @click="authMode = 'otp'" :class="authMode === 'otp' ? 'active' : ''" class="auth-toggle-btn" disabled style="opacity:0.5;cursor:not-allowed;">Mobile OTP</button>
    </div>

    <form @submit.prevent="submitLogin">
        <div class="fields">
            <!-- Email -->
            <div>
                <label for="email" class="f-label">Email Address</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="email" type="email" required x-model="formData.email" class="f-input" placeholder="you@example.com">
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
                    <input id="password" :type="showPassword ? 'text' : 'password'" required x-model="formData.password" class="f-input" placeholder="••••••••">
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

            <!-- Options -->
            <div class="options-row">
                <label class="rem-label" for="remember">
                    <input id="remember" type="checkbox" x-model="formData.remember" class="rem-check">
                    <span class="rem-text">Remember me</span>
                </label>
                <a href="/forgot-password" class="forgot">Forgot?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg x-show="isSubmitting" x-cloak class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                    <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-show="!isSubmitting">Sign In</span>
                <span x-show="isSubmitting" x-cloak>Signing in...</span>
            </button>
        </div>
    </form>

    <!-- Social -->
    <div class="social-header">
        <div class="social-line"></div>
        <span class="social-text">or continue with</span>
        <div class="social-line"></div>
    </div>

    <?php
        $roleParam = $_GET['role'] ?? null;
        $redirectParam = $redirect ?? '';
        $isEmployerContext = ($roleParam === 'employer') || (is_string($redirectParam) && strpos($redirectParam, '/employer/') === 0);
        $oauthRedirect = $isEmployerContext ? '/employer/dashboard' : '/candidate/dashboard';
    ?>
    <div class="social-grid">
        <a href="/auth/google?redirect=<?= $oauthRedirect ?>" class="social-btn"><img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google"></a>
        <a href="/auth/facebook?redirect=<?= $oauthRedirect ?>" class="social-btn"><img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook"></a>
        <a href="/auth/linkedin?redirect=<?= $oauthRedirect ?>" class="social-btn"><img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn"></a>
        <a href="/auth/microsoft?redirect=<?= $oauthRedirect ?>" class="social-btn"><img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft"></a>
    </div>

    <!-- Sign Up -->
    <div class="footer">
        <?php
            $signupUrl  = $isEmployerContext ? '/register-employer' : '/register-candidate';
            $signupText = $isEmployerContext ? 'Create employer account' : 'Create candidate account';
        ?>
        <p class="footer-text">
            Don't have an account? <br>
            <a href="<?= $signupUrl ?>" class="footer-link"><?= $signupText ?></a>
        </p>
        <a href="/" class="back-home">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>
    </div>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function loginForm() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            isSubmitting: false,
            showPassword: false,
            authMode: 'password',
            error: '<?= $error ?? '' ?>',
            success: urlParams.get('message') || '',
            registrationSuccess: urlParams.get('registered') === '1',
            registrationMessage: urlParams.get('email') ? `Account created for ${urlParams.get('email')}. Please login.` : 'Account created successfully.',
            formData: { email: urlParams.get('email') || '', password: '', remember: false },
            async submitLogin() {
                this.isSubmitting = true; this.error = '';
                try {
                    const res = await fetch('/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                        body: JSON.stringify(this.formData)
                    });
                    const data = await res.json();
                    if (res.ok && (data.success || data.status)) {
                        window.location.href = data.redirect || data.redirect_to || '/';
                    } else {
                        this.error = data.error || data.message || 'Login failed. Please try again.';
                    }
                } catch (e) { this.error = 'An error occurred. Please try again.'; }
                this.isSubmitting = false;
            },
            getCsrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
        };
    }
</script>
</body>
</html>
