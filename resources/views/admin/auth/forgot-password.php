<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Forgot Password' ?> - Jobsence</title>
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

        /* Alerts */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            border-radius: 10px; padding: 14px; margin-bottom: 24px;
            font-size: 13.5px; line-height: 1.5;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; }

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

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="forgotPasswordForm()">

<div class="login-card">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-logo"><span>JS</span></div>
        <h1 class="brand-name">Jobsence</h1>
    </div>

    <!-- Header -->
    <div class="header">
        <h2 class="title">Forgot Password?</h2>
        <p class="subtitle">Enter your email and we'll send you a reset link.</p>
    </div>

    <!-- Success alert -->
    <div x-show="success" x-cloak class="alert alert-success">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:2px;">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="successMessage"></span>
    </div>

    <!-- Error alert -->
    <div x-show="error" x-cloak class="alert alert-error">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:2px;">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span x-text="error"></span>
    </div>

    <form @submit.prevent="submitRequest">
        <div class="fields">
            <div>
                <label for="email" class="f-label">Email address</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="email" type="email" required
                           x-model="formData.email"
                           class="f-input"
                           placeholder="admin@company.com">
                </div>
            </div>

            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg x-show="isSubmitting" x-cloak class="spin" width="18" height="18" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                    <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-show="!isSubmitting">Send Reset Link</span>
                <span x-show="isSubmitting" x-cloak>Sending...</span>
            </button>
        </div>
    </form>

    <div class="footer">
        <a href="/admin/login" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Login
        </a>
        <p class="copy-text">&copy; 2025 Jobsence. All rights reserved.</p>
    </div>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function forgotPasswordForm() {
        return {
            isSubmitting: false,
            error: '',
            success: false,
            successMessage: '',
            formData: { email: '' },
            async submitRequest() {
                this.isSubmitting = true;
                this.error = '';
                this.success = false;
                try {
                    const response = await fetch('/admin/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                        },
                        body: JSON.stringify(this.formData)
                    });
                    const data = await response.json();
                    if (response.ok && (data.success || data.status)) {
                        this.success = true;
                        this.successMessage = data.message || 'Reset link sent successfully';
                    } else {
                        this.error = data.error || data.message || 'Failed to send reset link';
                    }
                } catch (error) {
                    this.error = 'An error occurred. Please try again.';
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
</body>
</html>
