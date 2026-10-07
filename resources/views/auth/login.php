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
        /* ---- Four separate logins on one page ---- */
        body { flex-direction: column; padding: 20px 16px 40px; }
        .role-tabs { width: 100%; max-width: 980px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .role-tab { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 14px 8px; border-radius: 16px; border: 2px solid var(--slate-200); background: #fff; cursor: pointer; font-family: var(--font-head); text-decoration: none; color: var(--slate-900); }
        .role-tab .ic { font-size: 26px; line-height: 1; }
        .role-tab b { font-size: 17px; font-weight: 800; }
        .role-tab small { font-size: 12px; font-weight: 700; color: var(--slate-500); }
        .role-tab.on { border-color: var(--rc); box-shadow: 0 0 0 3px color-mix(in srgb, var(--rc) 18%, transparent); }
        .role-tab.on b { color: var(--rc); }
        .login-shell { width: 100%; max-width: 980px; display: grid; grid-template-columns: 1fr 440px; gap: 18px; align-items: start; }
        .role-info { background: #fff; border: 1px solid var(--slate-200); border-radius: 20px; padding: 26px; border-top: 5px solid var(--rc); }
        .role-info h2 { font-family: var(--font-head); font-size: 24px; font-weight: 800; margin-bottom: 4px; color: var(--rc); }
        .role-info .tag { font-size: 14px; color: var(--slate-600); margin-bottom: 14px; }
        .role-info ul { list-style: none; display: grid; gap: 9px; margin-bottom: 16px; }
        .role-info li { display: flex; gap: 8px; font-size: 14px; line-height: 1.45; color: var(--slate-700); }
        .role-info li::before { content: '✔'; color: var(--rc); font-weight: 800; }
        .role-info .how { font-size: 13px; background: var(--slate-50); border-radius: 10px; padding: 10px 12px; margin-bottom: 14px; color: var(--slate-700); }
        .role-info .reg { display: flex; flex-direction: column; gap: 8px; }
        .role-info .reg a { display: block; text-align: center; padding: 10px; border-radius: 12px; border: 2px solid var(--rc); color: var(--rc); font-weight: 800; text-decoration: none; font-size: 14px; }
        .lang-hi { display: block; }
        .login-shell .login-card { max-width: none; }
        @media (max-width: 860px) {
            .login-shell { grid-template-columns: 1fr; }
            .role-info { order: 2; }
        }
        @media (max-width: 600px) { .role-tabs { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) {
            .role-tab b { font-size: 14px; }
            .role-tab small { display: none; }
            .login-card { padding: 24px 18px; }
        }
    </style>
</head>
<body x-data="loginForm()">
<?php
$roles = require __DIR__ . '/_login_roles.php';
$tab = $tab ?? 'candidate';
$bi = static fn(array $p): string => htmlspecialchars($p[0], ENT_QUOTES, 'UTF-8') . ' · ' . htmlspecialchars($p[1], ENT_QUOTES, 'UTF-8');
?>
<nav class="role-tabs" aria-label="Choose login">
    <?php foreach ($roles as $key => $r): ?>
        <a href="<?= $r['href'] ?>" class="role-tab" style="--rc:<?= $r['color'] ?>" :class="tab === '<?= $key ?>' ? 'on' : ''"
           @click.prevent="setTab('<?= $key ?>')" :aria-current="tab === '<?= $key ?>' ? 'page' : null">
            <span class="ic" aria-hidden="true"><?= $r['icon'] ?></span>
            <b><?= htmlspecialchars($r['title'][1], ENT_QUOTES, 'UTF-8') ?></b>
            <small><?= htmlspecialchars($r['title'][0], ENT_QUOTES, 'UTF-8') ?></small>
        </a>
    <?php endforeach; ?>
</nav>

<div class="login-shell">
    <?php foreach ($roles as $key => $r): ?>
        <aside class="role-info" style="--rc:<?= $r['color'] ?>" x-show="tab === '<?= $key ?>'" <?= $key === $tab ? '' : 'x-cloak' ?>>
            <h2><?= $r['icon'] ?> <?= htmlspecialchars($r['title'][1], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($r['title'][0], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="tag"><?= $bi($r['tagline']) ?></p>
            <ul>
                <?php foreach ($r['points'] as $pt): ?><li><span><?= $bi($pt) ?></span></li><?php endforeach; ?>
            </ul>
            <p class="how"><b>🔑 <?= $bi(['लॉगिन कैसे करें', 'How to log in']) ?>:</b> <?= $bi($r['how']) ?></p>
            <div class="reg">
                <span style="font-size:13px;font-weight:700;color:var(--slate-600)"><?= $bi(['नए हैं?', 'New here?']) ?></span>
                <?php foreach ($r['register'] as [$url, $label]): ?><a href="<?= $url ?>"><?= $bi($label) ?></a><?php endforeach; ?>
            </div>
        </aside>
    <?php endforeach; ?>

<div class="login-card">

    <!-- Brand -->
    <div class="brand">
        <a href="/" class="brand-logo-link" aria-label="Jobsence home"><img src="/uploads/jobsence.png" alt="Jobsence" style="height:56px;width:auto;display:block;margin:0 auto"></a>
    </div>

    <!-- Header -->
    <div class="header">
        <h2 class="title" x-text="{candidate: 'Job Seeker Login', employer: 'Employer Login', mentor: 'Mentor Login', senior: 'Senior Citizen Login'}[tab]">Login</h2>
        <p class="subtitle" x-text="{candidate: 'जॉब सीकर लॉगिन · नौकरी, इंटर्नशिप, स्किल', employer: 'एम्प्लॉयर लॉगिन · कंपनी / HR', mentor: 'मेंटर लॉगिन · मेंटर, संस्थान, इंटर्नशिप प्रदाता, भर्ती कंपनी', senior: 'वरिष्ठ नागरिक लॉगिन · 59+ और उन्हें जोड़ने वाली संस्थाएँ'}[tab]"></p>
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

    <!-- Mentor login (and job seekers who registered through a Jobsence form): mobile / email → email OTP -->
    <form x-show="tab === 'mentor' || tab === 'senior' || m.fallback" x-cloak @submit.prevent="m.step === 1 ? mIdentify() : mVerify()" novalidate>
        <div class="fields">
            <p x-show="m.fallback" style="font-size:13px;color:#334155;margin:0 0 4px;background:#f0fdf4;border-radius:10px;padding:10px">✅ आपका Jobsence रजिस्ट्रेशन मिला – ईमेल OTP से अपना डैशबोर्ड खोलें। · We found your Jobsence registration – open your dashboard with the email OTP.</p>
            <div x-show="m.step === 1">
                <label for="m_identifier" class="f-label">रजिस्ट्रेशन वाला मोबाइल नंबर या ईमेल · Registered mobile number or email</label>
                <div class="f-wrap">
                    <input id="m_identifier" type="text" x-model.trim="m.identifier" class="f-input" style="padding-left:14px" placeholder="98XXXXXXXX / +977… / you@example.com" autocomplete="username">
                </div>
            </div>
            <div x-show="m.step === 2" x-cloak>
                <p style="font-size:13px;color:#334155;margin:0 0 10px">OTP भेजा गया · OTP sent to <b x-text="m.masked"></b>
                    <button type="button" @click="m.step = 1; m.otp = ''; m.fallback = false; error = ''" style="border:0;background:none;color:#f05537;font-weight:700;cursor:pointer">बदलें · Change</button></p>
                <label for="m_otp" class="f-label">ईमेल OTP · Email OTP</label>
                <div class="f-wrap">
                    <input id="m_otp" type="text" x-ref="motp" x-model.trim="m.otp" class="f-input" style="padding-left:14px;letter-spacing:.4em;font-size:20px;text-align:center" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="••••••">
                </div>
                <div class="options-row" style="margin-top:10px;justify-content:flex-end">
                    <button type="button" class="forgot" style="border:0;background:none;cursor:pointer" :disabled="wait > 0" @click="mIdentify(true)"
                            x-text="wait > 0 ? ('Resend in ' + wait + 's') : 'OTP दोबारा · Resend'"></button>
                </div>
            </div>
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <span x-show="!isSubmitting" x-text="m.step === 1 ? 'OTP भेजें · Send OTP' : 'लॉगिन करें · Log in'"></span>
                <span x-show="isSubmitting" x-cloak>…</span>
            </button>
            <p x-show="tab === 'mentor'" style="font-size:12px;color:#64748b;margin:4px 2px 0;text-align:center">पहले रजिस्ट्रेशन ईमेल के लिंक से लॉगिन होता था – अब यहाँ भी। · Previously only via the link in your registration email – now here too.</p>
        </div>
    </form>

    <div x-show="tab !== 'mentor' && tab !== 'senior' && !m.fallback">
    <!-- Mode Toggle -->
    <div class="auth-toggle">
        <button type="button" @click="authMode = 'quick'; error = ''" :class="authMode === 'quick' ? 'active' : ''" class="auth-toggle-btn">मोबाइल / ईमेल · Mobile / Email</button>
        <button type="button" @click="authMode = 'password'; error = ''" :class="authMode === 'password' ? 'active' : ''" class="auth-toggle-btn">पासवर्ड · Password</button>
    </div>

    <!-- Quick login: mobile or email → (remembered device ? in : email OTP) -->
    <form x-show="authMode === 'quick'" @submit.prevent="step === 1 ? identify() : (step === 3 ? verifyPin() : verifyOtp())" novalidate>
        <div class="fields">
            <div x-show="step === 1">
                <label for="identifier" class="f-label">मोबाइल नंबर या ईमेल · Mobile number or email</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <input id="identifier" type="text" x-model.trim="quick.identifier" class="f-input" placeholder="98XXXXXXXX / you@example.com" autocomplete="username" inputmode="email" autofocus>
                </div>
                <p style="font-size:12px;color:#64748b;margin:8px 2px 0">इस डिवाइस पर पहले ईमेल OTP से लॉगिन किया है तो सीधे अंदर। / Logged in on this device before? You go straight in.</p>
            </div>

            <!-- Optional login PIN (set in Login & devices) – email OTP is always one click away -->
            <div x-show="step === 3" x-cloak>
                <p style="font-size:13px;color:#334155;margin:0 0 10px"><b x-text="quick.identifier"></b>
                    <button type="button" @click="step = 1; quick.pin = ''; error = ''" style="border:0;background:none;color:#f05537;font-weight:700;cursor:pointer">बदलें · Change</button></p>
                <label for="pin" class="f-label">🔒 अपना लॉगिन PIN · Your login PIN</label>
                <div class="f-wrap">
                    <input id="pin" type="password" x-ref="pin" x-model.trim="quick.pin" class="f-input" style="padding-left:14px;letter-spacing:.4em;font-size:20px;text-align:center" inputmode="numeric" autocomplete="current-password" maxlength="6" placeholder="••••">
                </div>
                <div class="options-row" style="margin-top:10px">
                    <label class="rem-label" for="remember_device_pin">
                        <input id="remember_device_pin" type="checkbox" x-model="quick.remember" class="rem-check">
                        <span class="rem-text">इस डिवाइस को याद रखें · Remember this device</span>
                    </label>
                    <button type="button" class="forgot" style="border:0;background:none;cursor:pointer" @click="identify(false, true)">PIN भूल गए? ईमेल OTP · Forgot PIN? Email OTP</button>
                </div>
            </div>

            <div x-show="step === 2" x-cloak>
                <p style="font-size:13px;color:#334155;margin:0 0 10px">OTP भेजा गया · OTP sent to <b x-text="maskedEmail"></b>
                    <button type="button" @click="step = 1; quick.otp = ''; error = ''" style="border:0;background:none;color:#f05537;font-weight:700;cursor:pointer">बदलें · Change</button></p>
                <label for="otp" class="f-label">ईमेल OTP · Email OTP</label>
                <div class="f-wrap">
                    <input id="otp" type="text" x-ref="otp" x-model.trim="quick.otp" class="f-input" style="padding-left:14px;letter-spacing:.4em;font-size:20px;text-align:center" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="••••••">
                </div>
                <div class="options-row" style="margin-top:10px">
                    <label class="rem-label" for="remember_device">
                        <input id="remember_device" type="checkbox" x-model="quick.remember" class="rem-check">
                        <span class="rem-text">इस डिवाइस को याद रखें · Remember this device</span>
                    </label>
                    <button type="button" class="forgot" style="border:0;background:none;cursor:pointer" :disabled="wait > 0" @click="identify(true)"
                            x-text="wait > 0 ? ('Resend in ' + wait + 's') : 'OTP दोबारा · Resend'"></button>
                </div>
                <p style="font-size:11px;color:#94a3b8;margin:6px 2px 0">साझा / साइबर कैफ़े कंप्यूटर पर टिक हटाएँ। · Untick on a shared or cyber-café computer.</p>
            </div>

            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg x-show="isSubmitting" x-cloak class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4"/>
                    <path fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-show="!isSubmitting" x-text="step === 1 ? 'आगे बढ़ें · Continue' : (step === 3 ? 'PIN से लॉगिन करें · Log in with PIN' : 'लॉगिन करें · Log in')"></span>
                <span x-show="isSubmitting" x-cloak>…</span>
            </button>
            <p x-show="notFound" x-cloak style="font-size:13px;margin:4px 0 0;text-align:center">
                नया खाता बनाएँ · Create an account:
                <a href="/register-candidate" class="footer-link">Candidate</a> · <a href="/register-employer" class="footer-link">Employer</a>
            </p>
        </div>
    </form>

    <!-- Password login -->
    <form x-show="authMode === 'password'" x-cloak @submit.prevent="submitLogin">
        <div class="fields">
            <div>
                <label for="email" class="f-label">Email Address</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="email" type="email" x-model="formData.email" class="f-input" placeholder="you@example.com" autocomplete="email">
                </div>
            </div>

            <div>
                <label for="password" class="f-label">Password</label>
                <div class="f-wrap">
                    <span class="f-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="password" :type="showPassword ? 'text' : 'password'" x-model="formData.password" class="f-input" placeholder="••••••••" autocomplete="current-password">
                    <button type="button" class="pass-eye" @click="showPassword = !showPassword" aria-label="Show password">
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

            <div class="options-row">
                <label class="rem-label" for="remember">
                    <input id="remember" type="checkbox" x-model="formData.remember" class="rem-check">
                    <span class="rem-text">इस डिवाइस को याद रखें · Remember this device</span>
                </label>
                <a href="/forgot-password" class="forgot">Forgot?</a>
            </div>

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
    </div><!-- /account login forms -->

    <!-- Social -->
    <div x-show="tab !== 'mentor' && tab !== 'senior' && !m.fallback">
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
    <?php $socialRedirect = $oauthRedirect; $socialClass = 'social-btn'; require __DIR__ . '/_social_buttons.php'; ?>
    </div><!-- /social -->

    <!-- Sign Up -->
    <div class="footer">
        <?php
            $signupUrl  = $isEmployerContext ? '/register-employer' : '/register-candidate';
            $signupText = $isEmployerContext ? 'Create employer account' : 'Create candidate account';
        ?>
        <p class="footer-text" x-show="tab !== 'mentor' && tab !== 'senior'">
            Don't have an account? <br>
            <a :href="tab === 'employer' ? '/register-employer' : '/register-candidate'" class="footer-link" x-text="tab === 'employer' ? 'Create employer account' : 'Create job seeker account'"><?= $signupText ?></a>
        </p>
        <p class="footer-text" x-show="tab === 'senior'" x-cloak>
            रजिस्टर नहीं किया? · Not registered yet? <br>
            <a href="/apply/senior-citizen-jobs" class="footer-link">Register free as a senior citizen (59+)</a>
        </p>
        <p class="footer-text" x-show="tab === 'mentor'" x-cloak>
            मेंटर नहीं बने? · Not registered yet? <br>
            <a href="/apply/skill-provider" class="footer-link">Register free as a mentor</a>
        </p>
        <a href="/" class="back-home">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>
    </div>

</div>
</div><!-- /.login-shell -->

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function loginForm() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            isSubmitting: false,
            showPassword: false,
            authMode: urlParams.get('mode') === 'password' ? 'password' : 'quick',
            error: '<?= $error ?? '' ?>',
            success: urlParams.get('message') || '',
            registrationSuccess: urlParams.get('registered') === '1',
            registrationMessage: urlParams.get('email') ? `Account created for ${urlParams.get('email')}. Please login.` : 'Account created successfully.',
            formData: { email: urlParams.get('email') || '', password: '', remember: true },
            step: 1, notFound: false, maskedEmail: '', wait: 0, timer: null,
            tab: '<?= $tab ?? 'candidate' ?>',
            m: { identifier: '', otp: '', step: 1, masked: '', role: 'provider', fallback: false },
            setTab(t) {
                this.tab = t; this.error = ''; this.notFound = false; this.m.fallback = false; this.m.step = 1;
                const paths = { candidate: '/login/job-seeker', employer: '/login/employer', mentor: '/login/mentor', senior: '/login/senior' };
                try { history.replaceState(null, '', paths[t] + window.location.search); } catch (e) {}
            },
            wrongTab(data) {
                if (data.status !== 'wrong_tab') return false;
                this.setTab(data.tab); this.error = data.error; return true;
            },
            async mIdentify(resend) {
                this.error = '';
                if (!this.m.identifier) { this.error = 'मोबाइल नंबर या ईमेल भरें · Enter your mobile number or email'; return false; }
                this.isSubmitting = true;
                let found = false;
                try {
                    const { ok, data } = await this.post(this.tab === 'senior' ? '/login/senior/identify' : '/login/mentoring/identify', { identifier: this.m.identifier, role: this.m.role });
                    if (ok && data.status === 'otp_sent') {
                        found = true; this.m.masked = data.email; this.m.step = 2; this.countdown(data.wait || 30);
                        this.$nextTick(() => this.$refs.motp && this.$refs.motp.focus());
                        if (resend) this.success = 'OTP दोबारा भेजा गया · OTP sent again';
                    } else if (!this.m.fallback) {
                        this.error = data.error || 'कुछ गलत हुआ · Something went wrong';
                    }
                } catch (e) { this.error = 'नेटवर्क त्रुटि · Network error, please retry'; }
                this.isSubmitting = false;
                return found;
            },
            async mVerify() {
                this.error = '';
                if (!/^\d{6}$/.test(this.m.otp)) { this.error = '6 अंकों का OTP भरें · Enter the 6-digit OTP'; return; }
                this.isSubmitting = true;
                try {
                    const { ok, data } = await this.post(this.tab === 'senior' ? '/login/senior/verify' : '/login/mentoring/verify', { otp: this.m.otp });
                    if (ok && data.status === 'logged_in') { window.location.href = data.redirect || (this.tab === 'senior' ? '/senior' : '/mentoring'); return; }
                    this.error = data.error || 'OTP सही नहीं है · Incorrect OTP';
                } catch (e) { this.error = 'नेटवर्क त्रुटि · Network error, please retry'; }
                this.isSubmitting = false;
            },
            quick: { identifier: urlParams.get('email') || '', otp: '', pin: '', remember: true },
            redirectTo: urlParams.get('redirect') || '',
            async post(url, body) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                    body: JSON.stringify(Object.assign({ redirect: this.redirectTo }, body))
                });
                let data = {};
                try { data = await res.json(); } catch (e) {}
                return { ok: res.ok, data };
            },
            countdown(sec) {
                this.wait = sec; clearInterval(this.timer);
                this.timer = setInterval(() => { if (--this.wait <= 0) clearInterval(this.timer); }, 1000);
            },
            async identify(resend, useOtp) {
                this.error = ''; this.notFound = false;
                if (!this.quick.identifier) { this.error = 'मोबाइल नंबर या ईमेल भरें · Enter your mobile number or email'; return; }
                this.isSubmitting = true;
                try {
                    const { ok, data } = await this.post('/login/identify', { identifier: this.quick.identifier, as: this.tab, method: (useOtp || resend) ? 'otp' : '' });
                    if (this.wrongTab(data)) { this.isSubmitting = false; return; }
                    if (!ok && data.status === 'not_found' && this.tab === 'candidate') {
                        // Registered through a Jobsence form (skill / internship / job) but no account: try that.
                        this.m.identifier = this.quick.identifier; this.m.role = 'seeker'; this.m.fallback = true; this.isSubmitting = false;
                        if (await this.mIdentify()) return;
                        this.m.fallback = false; this.m.role = 'provider'; this.isSubmitting = true;
                    }
                    if (ok && data.status === 'logged_in') { window.location.href = data.redirect || '/'; return; }
                    if (ok && data.status === 'pin_required') {
                        this.step = 3; this.quick.pin = '';
                        this.$nextTick(() => this.$refs.pin && this.$refs.pin.focus());
                        this.isSubmitting = false; return;
                    }
                    if (ok && data.status === 'otp_sent') {
                        this.maskedEmail = data.email; this.step = 2; this.countdown(data.wait || 30);
                        this.$nextTick(() => this.$refs.otp && this.$refs.otp.focus());
                        if (resend) this.success = 'OTP दोबारा भेजा गया · OTP sent again';
                    } else {
                        this.notFound = data.status === 'not_found';
                        this.error = data.error || 'कुछ गलत हुआ, दोबारा प्रयास करें · Something went wrong, please try again';
                    }
                } catch (e) { this.error = 'नेटवर्क त्रुटि · Network error, please retry'; }
                this.isSubmitting = false;
            },
            async verifyOtp() {
                this.error = '';
                if (!/^\d{6}$/.test(this.quick.otp)) { this.error = '6 अंकों का OTP भरें · Enter the 6-digit OTP'; return; }
                this.isSubmitting = true;
                try {
                    const { ok, data } = await this.post('/login/verify', { identifier: this.quick.identifier, otp: this.quick.otp, remember: this.quick.remember });
                    if (ok && data.status === 'logged_in') { window.location.href = data.redirect || '/'; return; }
                    this.error = data.error || 'OTP सही नहीं है · Incorrect OTP';
                } catch (e) { this.error = 'नेटवर्क त्रुटि · Network error, please retry'; }
                this.isSubmitting = false;
            },
            async verifyPin() {
                this.error = '';
                if (!/^\d{4,6}$/.test(this.quick.pin)) { this.error = '4–6 अंकों का PIN भरें · Enter your 4–6 digit PIN'; return; }
                this.isSubmitting = true;
                try {
                    const { ok, data } = await this.post('/login/pin', { identifier: this.quick.identifier, pin: this.quick.pin, remember: this.quick.remember });
                    if (ok && data.status === 'logged_in') { window.location.href = data.redirect || '/'; return; }
                    this.quick.pin = '';
                    this.error = data.error || 'PIN सही नहीं है · Incorrect PIN';
                    if (data.status === 'pin_locked') { this.isSubmitting = false; await this.identify(false, true); return; }
                } catch (e) { this.error = 'नेटवर्क त्रुटि · Network error, please retry'; }
                this.isSubmitting = false;
            },
            async submitLogin() {
                this.isSubmitting = true; this.error = '';
                try {
                    const res = await fetch('/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                        body: JSON.stringify(Object.assign({ redirect: this.redirectTo, as: this.tab }, this.formData))
                    });
                    const data = await res.json();
                    if (this.wrongTab(data)) { this.isSubmitting = false; return; }
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
