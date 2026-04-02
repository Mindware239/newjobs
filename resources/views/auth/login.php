<?php
// Login Page - Mindware Infotech
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Login | Mindware Jobs</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none}
        *,*::before,*::after{box-sizing:border-box;}
        html,body{margin:0;padding:0;height:100%;overflow:hidden;}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}

        .dot-grid{background-image:radial-gradient(circle,rgba(99,102,241,.18) 1px,transparent 1px);background-size:24px 24px;}
        .grad-text{background:linear-gradient(135deg,#4f46e5,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}

        .f-input{
            width:100%;padding:10px 14px;font-size:13.5px;color:#111827;
            background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px;
            outline:none;transition:all .2s;font-family:inherit;
        }
        .f-input::placeholder{color:#9ca3af;}
        .f-input:focus{border-color:#4f46e5;background:#fff;box-shadow:0 0 0 3px rgba(79,70,229,.1);}
        .f-input:hover:not(:focus){border-color:#d1d5db;}

        .btn-main{
            width:100%;padding:11px;display:flex;align-items:center;justify-content:center;gap:7px;
            background:linear-gradient(135deg,#4f46e5,#3b82f6);color:white;font-weight:700;
            font-size:13.5px;border:none;border-radius:10px;cursor:pointer;
            transition:all .2s;box-shadow:0 4px 14px rgba(79,70,229,.28);font-family:inherit;
        }
        .btn-main:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 20px rgba(79,70,229,.38);}
        .btn-main:disabled{opacity:.55;cursor:not-allowed;transform:none;box-shadow:none;}

        .soc-btn{
            display:flex;align-items:center;justify-content:center;padding:9px;
            border:1.5px solid #e5e7eb;border-radius:9px;background:white;
            text-decoration:none;transition:all .18s;
        }
        .soc-btn:hover{border-color:#c7d2fe;background:#f5f3ff;transform:translateY(-1px);box-shadow:0 3px 10px rgba(79,70,229,.1);}
        .soc-btn img{width:19px;height:19px;}

        .stat-c{
            flex:1;background:rgba(255,255,255,.85);border:1px solid rgba(199,210,254,.8);
            border-radius:11px;padding:11px 13px;backdrop-filter:blur(4px);
        }

        .avatar{width:26px;height:26px;border-radius:50%;border:2px solid white;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:white;margin-left:-6px;}
        .avatar:first-child{margin-left:0;}

        @keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.8);}}
        .pulse-dot{animation:pulse-dot 2s ease-in-out infinite;}
        @keyframes spin{to{transform:rotate(360deg);}}
        .spin{animation:spin .7s linear infinite;}
    </style>
</head>
<body>

<div x-data="loginForm()" x-init="init()" x-cloak
     class="flex" style="height:100vh;overflow:hidden;">

    <!-- ══════════════ LEFT PANEL ══════════════ -->
    <div class="hidden md:flex flex-col" style="width:50%;height:100vh;position:relative;overflow:hidden;
         background:linear-gradient(145deg,#eef2ff 0%,#e0e7ff 40%,#dbeafe 75%,#eff6ff 100%);">

        <div class="dot-grid" style="position:absolute;inset:0;opacity:.55;"></div>
        <div style="position:absolute;top:-80px;left:-80px;width:300px;height:300px;border-radius:50%;
                    background:rgba(99,102,241,.22);filter:blur(65px);"></div>
        <div style="position:absolute;top:45%;right:-60px;width:240px;height:240px;border-radius:50%;
                    background:rgba(59,130,246,.18);filter:blur(55px);"></div>
        <div style="position:absolute;bottom:-30px;left:25%;width:200px;height:200px;border-radius:50%;
                    background:rgba(139,92,246,.15);filter:blur(50px);"></div>

        <div style="position:relative;z-index:10;display:flex;flex-direction:column;height:100%;padding:32px 40px;">

            <!-- Logo -->
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                <div style="width:36px;height:36px;border-radius:10px;
                            background:linear-gradient(135deg,#4f46e5,#3b82f6);
                            display:flex;align-items:center;justify-content:center;
                            color:white;font-weight:800;font-size:16px;
                            box-shadow:0 4px 12px rgba(79,70,229,.35);">M</div>
                <span style="font-size:15px;font-weight:700;color:#1e1b4b;letter-spacing:-.3px;">Mindware</span>
            </div>

            <!-- Main content fills space -->
            <div style="flex:1;display:flex;flex-direction:column;justify-content:center;gap:16px;padding:20px 0;">

                <!-- Badge -->
                <div style="display:inline-flex;align-items:center;gap:7px;width:fit-content;
                            padding:5px 13px;border-radius:100px;
                            background:rgba(79,70,229,.1);border:1px solid rgba(79,70,229,.2);">
                    <span class="pulse-dot" style="width:6px;height:6px;border-radius:50%;background:#4f46e5;"></span>
                    <span style="font-size:11px;font-weight:600;color:#4338ca;letter-spacing:.5px;text-transform:uppercase;">For Candidates &amp; Employers</span>
                </div>

                <!-- Headings -->
                <div style="line-height:1.2;">
                    <div style="font-size:clamp(24px,2.8vw,36px);font-weight:800;color:#1e1b4b;letter-spacing:-1px;">Hire Smarter.</div>
                    <div class="grad-text" style="font-size:clamp(24px,2.8vw,36px);font-weight:800;letter-spacing:-1px;">Grow Faster.</div>
                </div>

                <!-- Desc -->
                <p style="font-size:13.5px;color:#4b5563;line-height:1.65;max-width:300px;margin:0;">
                    Modern SaaS recruitment platform connecting top talent with verified employers across India.
                </p>

                <!-- Features -->
                <div style="border-top:1px solid rgba(199,210,254,.6);padding-top:12px;display:flex;flex-direction:column;gap:2px;">
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;font-size:13px;color:#374151;">
                        <div style="width:28px;height:28px;border-radius:7px;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">🎯</div>
                        AI-powered job matching
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;font-size:13px;color:#374151;">
                        <div style="width:28px;height:28px;border-radius:7px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">✅</div>
                        Verified employer listings
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;font-size:13px;color:#374151;">
                        <div style="width:28px;height:28px;border-radius:7px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">📊</div>
                        Real-time application tracking
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;font-size:13px;color:#374151;">
                        <div style="width:28px;height:28px;border-radius:7px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">🔒</div>
                        Full privacy controls
                    </div>
                </div>

                <!-- Stats -->
                <div style="display:flex;gap:10px;">
                    <div class="stat-c">
                        <div style="font-size:19px;font-weight:800;color:#1e1b4b;">12<span style="color:#4f46e5;">K+</span></div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px;">Active Jobs</div>
                    </div>
                    <div class="stat-c">
                        <div style="font-size:19px;font-weight:800;color:#1e1b4b;">98<span style="color:#4f46e5;">%</span></div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px;">Verified</div>
                    </div>
                    <div class="stat-c">
                        <div style="font-size:19px;font-weight:800;color:#1e1b4b;">4.9<span style="color:#4f46e5;">★</span></div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px;">Avg Rating</div>
                    </div>
                </div>
            </div>

            <!-- Trust strip pinned bottom -->
            <div style="flex-shrink:0;display:flex;align-items:center;gap:10px;
                        background:rgba(255,255,255,.82);backdrop-filter:blur(8px);
                        border:1px solid rgba(199,210,254,.6);border-radius:14px;
                        padding:11px 15px;">
                <div style="display:flex;">
                    <div class="avatar" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">P</div>
                    <div class="avatar" style="background:linear-gradient(135deg,#3b82f6,#06b6d4);">R</div>
                    <div class="avatar" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">A</div>
                </div>
                <div style="font-size:12px;color:#4b5563;">
                    <span style="font-weight:600;color:#1e1b4b;">2,400+ candidates</span> hired this month
                </div>
                <span style="margin-left:auto;font-size:10.5px;font-weight:700;
                             background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;
                             border-radius:100px;padding:3px 9px;white-space:nowrap;">✓ Live</span>
            </div>

        </div>
    </div>

    <!-- ══════════════ RIGHT PANEL ══════════════ -->
    <div style="flex:1;height:100vh;overflow-y:auto;background:#ffffff;
                display:flex;align-items:center;justify-content:center;
                padding:32px 48px;">

        <div style="width:100%;max-width:368px;">

            <!-- Back link -->
            <a href="/" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;
                               font-weight:500;color:#9ca3af;text-decoration:none;margin-bottom:24px;
                               transition:color .2s;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Home
            </a>

            <!-- Brand -->
            <div style="display:flex;align-items:center;gap:11px;margin-bottom:20px;">
                <div style="width:40px;height:40px;border-radius:11px;flex-shrink:0;
                            background:linear-gradient(135deg,#4f46e5,#3b82f6);
                            display:flex;align-items:center;justify-content:center;
                            color:white;font-weight:800;font-size:18px;
                            box-shadow:0 4px 14px rgba(79,70,229,.22);">M</div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#111827;">Mindware</div>
                    <div style="font-size:11.5px;color:#9ca3af;">Recruitment Platform</div>
                </div>
            </div>

            <!-- Heading -->
            <h2 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-.5px;margin:0 0 3px;">Welcome Back</h2>
            <p style="font-size:13px;color:#9ca3af;margin:0 0 22px;">Login to your candidate or employer account</p>

            <!-- Registration success -->
            <div x-show="registrationSuccess"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display:flex;align-items:flex-start;gap:9px;margin-bottom:14px;
                        padding:11px 13px;background:#f0fdf4;border:1px solid #bbf7d0;
                        border-radius:10px;font-size:13px;font-weight:500;color:#166534;">
                <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span x-text="registrationMessage"></span>
            </div>

            <!-- Success message -->
            <div x-show="success"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display:flex;align-items:flex-start;gap:9px;margin-bottom:14px;
                        padding:11px 13px;background:#f0fdf4;border:1px solid #bbf7d0;
                        border-radius:10px;font-size:13px;font-weight:500;color:#166534;">
                <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span x-text="success"></span>
            </div>

            <!-- Error -->
            <div x-show="error"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display:flex;align-items:flex-start;gap:9px;margin-bottom:14px;
                        padding:11px 13px;background:#fef2f2;border:1px solid #fecaca;
                        border-radius:10px;font-size:13px;font-weight:500;color:#b91c1c;">
                <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="error"></span>
            </div>

            <form @submit.prevent="submitLogin()">

                <!-- Email -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                        Email Address <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="email"
                           x-model="formData.email"
                           @input="validateEmail()"
                           @blur="validateEmail()"
                           placeholder="you@example.com"
                           class="f-input">
                    <p x-show="emailError" x-text="emailError"
                       style="font-size:11.5px;color:#ef4444;margin-top:4px;"></p>
                </div>

                <!-- Password -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                        Password <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input :type="showPassword ? 'text' : 'password'"
                               x-model="formData.password"
                               placeholder="••••••••"
                               class="f-input"
                               style="padding-right:40px;">
                        <button type="button" @click="showPassword = !showPassword"
                                style="position:absolute;right:11px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;cursor:pointer;color:#9ca3af;
                                       display:flex;padding:3px;transition:color .2s;"
                                onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                            <svg x-show="!showPassword" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                    <label style="display:flex;align-items:center;gap:7px;cursor:pointer;">
                        <input type="checkbox" x-model="formData.remember"
                               style="width:13px;height:13px;accent-color:#4f46e5;cursor:pointer;">
                        <span style="font-size:12px;font-weight:500;color:#6b7280;">Remember me</span>
                    </label>
                    <a x-show="!hideForgot" href="/forgot-password"
                       style="font-size:12px;font-weight:600;color:#4f46e5;text-decoration:none;transition:color .2s;"
                       onmouseover="this.style.color='#4338ca';this.style.textDecoration='underline'"
                       onmouseout="this.style.color='#4f46e5';this.style.textDecoration='none'">
                        Forgot Password?
                    </a>
                </div>

                <!-- Sign In -->
                <button type="submit" :disabled="isSubmitting || !emailValid" class="btn-main" style="margin-bottom:14px;">
                    <template x-if="!isSubmitting">
                        <span style="display:flex;align-items:center;gap:7px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span style="display:flex;align-items:center;gap:7px;">
                            <svg class="spin" width="14" height="14" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25;"/>
                                <path fill="currentColor" style="opacity:.75;" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Signing in...
                        </span>
                    </template>
                </button>

                <!-- Divider -->
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="flex:1;height:1px;background:#f1f5f9;"></div>
                    <span style="font-size:11.5px;color:#d1d5db;font-weight:500;">or continue with</span>
                    <div style="flex:1;height:1px;background:#f1f5f9;"></div>
                </div>

                <!-- Social -->
                <?php
                    $roleParam = $_GET['role'] ?? null;
                    $redirectParam = $redirect ?? '';
                    $isEmployerContext = ($roleParam === 'employer') || (is_string($redirectParam) && strpos($redirectParam, '/employer/') === 0);
                    $oauthRedirect = $isEmployerContext ? '/employer/dashboard' : '/candidate/dashboard';
                ?>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-bottom:18px;">
                    <a href="/auth/google?redirect=<?= $oauthRedirect ?>" class="soc-btn" aria-label="Google">
                        <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google">
                    </a>
                    <a href="/auth/facebook?redirect=<?= $oauthRedirect ?>" class="soc-btn" aria-label="Facebook">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
                    </a>
                    <a href="/auth/linkedin?redirect=<?= $oauthRedirect ?>" class="soc-btn" aria-label="LinkedIn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn">
                    </a>
                    <a href="/auth/microsoft?redirect=<?= $oauthRedirect ?>" class="soc-btn" aria-label="Microsoft">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft">
                    </a>
                </div>

                <!-- Sign up -->
                <?php
                $signupUrl  = $isEmployerContext ? '/register-employer' : '/register-candidate';
                $signupText = $isEmployerContext ? 'Create employer account' : 'Create candidate account';
                ?>
                <p style="text-align:center;font-size:12.5px;color:#6b7280;margin:0;">
                    Don't have an account?
                    <a href="<?= $signupUrl ?>"
                       style="font-weight:700;color:#4f46e5;text-decoration:none;transition:color .2s;"
                       onmouseover="this.style.color='#4338ca';this.style.textDecoration='underline'"
                       onmouseout="this.style.color='#4f46e5';this.style.textDecoration='none'">
                        <?= $signupText ?>
                    </a>
                </p>

            </form>
        </div>
    </div>

</div>

<script>
    function loginForm() {
        const urlParams = new URLSearchParams(window.location.search);
        const registered = urlParams.get('registered');
        const registeredEmail = urlParams.get('email');
        const messageParam = urlParams.get('message');
        return {
            isSubmitting: false,
            showPassword: false,
            error: '<?= $error ?? '' ?>',
            success: messageParam ? decodeURIComponent(messageParam) : '',
            redirect: '<?= $redirect ?? '' ?>',
            emailValid: true,
            emailError: '',
            registrationSuccess: registered === '1',
            registrationMessage: registeredEmail ? `Account created for ${registeredEmail}. Please login.` : 'Account created successfully. Please login.',
            hideForgot: false,
            formData: { email: registeredEmail || '', password: '', remember: false },
            init() {
                if (this.registrationSuccess) {
                    try { if (window.MWMarketing) { window.MWMarketing.trackCompleteRegistration({ content_type: 'candidate', candidate_type: 'candidate', value: 0, currency: 'INR' }); } } catch(_){}
                    setTimeout(() => {
                        this.registrationSuccess = false;
                        const url = new URL(window.location);
                        url.searchParams.delete('registered');
                        url.searchParams.delete('email');
                        window.history.replaceState({}, '', url);
                    }, 8000);
                }
                if (this.success) {
                    setTimeout(() => {
                        this.success = '';
                        const url = new URL(window.location);
                        url.searchParams.delete('message');
                        window.history.replaceState({}, '', url);
                    }, 5000);
                }
            },
            validateEmail() {
                const email = this.formData.email;
                if (!email) { this.emailValid = true; this.emailError = ''; return; }
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(email)) { this.emailValid = false; this.emailError = 'Please enter a valid email address'; }
                else { this.emailValid = true; this.emailError = ''; }
            },
            async submitLogin() {
                this.validateEmail();
                if (!this.emailValid) return;
                this.isSubmitting = true; this.error = '';
                try {
                    const res = await fetch('/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                        body: JSON.stringify(this.formData)
                    });
                    const data = await res.json();
                    if (res.status === 403 && data && data.refresh_csrf && data.csrf_token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) { meta.setAttribute('content', data.csrf_token); }
                        const res2 = await fetch('/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': data.csrf_token },
                            body: JSON.stringify(this.formData)
                        });
                        const data2 = await res2.json();
                        const payload2 = data2.data || data2;
                        const loginSuccess2 = res2.ok && (data2.status === true || payload2.success === true || payload2.message === 'Login successful' || data2.message === 'Login successful');
                        if (loginSuccess2) {
                            let r = payload2.redirect_to || payload2.redirect || this.redirect;
                            if (!r) { r = (payload2.user && payload2.user.role === 'employer') ? '/employer/dashboard' : '/'; }
                            window.location.href = r; this.isSubmitting = false; return;
                        } else { this.error = payload2.error || payload2.message || data2.message || 'Please try again'; this.isSubmitting = false; return; }
                    }
                    const payload = data.data || data;
                    const loginSuccess = res.ok && (data.status === true || payload.success === true || payload.message === 'Login successful' || data.message === 'Login successful');
                    if (loginSuccess) {
                        let r = payload.redirect_to || payload.redirect || this.redirect;
                        if (!r) { r = (payload.user && payload.user.role === 'employer') ? '/employer/dashboard' : '/'; }
                        window.location.href = r;
                    } else { this.error = payload.error || payload.message || data.error || data.message || 'Please try again'; }
                } catch (e) { this.error = 'Please try again'; }
                this.isSubmitting = false;
            },
            getCsrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
        };
    }
</script>
</body>
</html>