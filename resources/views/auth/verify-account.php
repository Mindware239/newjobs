<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Verify Account') ?> - Jobsence</title>
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

        /* Alert */
        .alert-error {
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

        /* Terms */
        .terms-row {
            display: flex; align-items: flex-start; gap: 10px;
            margin-top: 4px;
        }
        .terms-check { width: 18px; height: 18px; accent-color: var(--primary); margin-top: 2px; }
        .terms-text { font-size: 13.5px; color: var(--slate-600); line-height: 1.5; }
        .terms-link { color: var(--primary); font-weight: 600; text-decoration: none; }

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
        .submit-btn:hover { background: var(--primary-hover); }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--slate-100);
        }

        .copy-text { font-size: 12px; color: var(--slate-400); }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-logo"><span>JS</span></div>
        <h1 class="brand-name">Jobsence</h1>
    </div>

    <!-- Header -->
    <div class="header">
        <h2 class="title">Activate Your Account</h2>
        <p class="subtitle">Please set a password for <?= htmlspecialchars($email ?? '') ?></p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert-error">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:2px;">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/verify-account">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        
        <div class="fields">
            <div>
                <label for="password" class="f-label">New Password</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="password" name="password" type="password" required
                           class="f-input"
                           placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="f-label">Confirm Password</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="f-input"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="terms-row">
                <input id="terms" name="terms" type="checkbox" required class="terms-check">
                <label for="terms" class="terms-text">
                    I agree to the <a href="/terms" class="terms-link">Terms of Service</a> and <a href="/privacy" class="terms-link">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="submit-btn">
                Activate Account & Login
            </button>
        </div>
    </form>

    <div class="footer">
        <p class="copy-text">&copy; 2025 Jobsence. All rights reserved.</p>
    </div>

</div>

</body>
</html>
