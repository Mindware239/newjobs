<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Login | Mindware Jobs</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>[x-cloak]{display:none}</style>
</head>

<body class="min-h-screen bg-slate-100">
    <div x-data="loginForm()" x-init="init()" x-cloak class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-5xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden grid md:grid-cols-2">
            <!-- LEFT BANNER -->
            <div class="hidden md:flex flex-col justify-center p-10 bg-gradient-to-br from-indigo-600 to-blue-800 text-white">
                <h1 class="text-4xl font-bold mb-4">Hire Smarter. Grow Faster.</h1>
                <p class="text-indigo-100 mb-10">Modern SaaS job platform for hiring and career success.</p>
                <ul class="space-y-4 text-indigo-100">
                    <li class="flex items-center gap-3"><i class="fas fa-check-circle text-green-400"></i> Verified employers</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check-circle text-green-400"></i> AI‑powered job matching</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check-circle text-green-400"></i> Smart hiring tools</li>
                </ul>
            </div>

            <!-- RIGHT FORM -->
            <div class="p-10 md:p-14">
                <a href="/" class="inline-flex items-center gap-2 mb-6 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="text-center mb-8">
                    <div class="mx-auto mb-3 h-12 w-12 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">M</div>
                    <h2 class="text-3xl font-bold">Welcome Back</h2>
                    <p class="text-gray-500 mt-1">Login to your account</p>
                </div>

                <!-- SUCCESS -->
                <div x-show="registrationSuccess" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm" x-text="registrationMessage"></div>

                <!-- ERROR -->
                <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                <form @submit.prevent="submitLogin()" class="space-y-5">
                    <!-- EMAIL -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" x-model="formData.email" @input="validateEmail()" @blur="validateEmail()" placeholder="you@example.com" class="mt-1 w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <p x-show="emailError" class="text-sm text-red-600 mt-1" x-text="emailError"></p>
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <div class="relative mt-1">
                            <input :type="showPassword ? 'text':'password'" x-model="formData.password" placeholder="••••••••" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            <button type="button" @click="showPassword=!showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <div class="flex justify-between mt-2 text-sm text-gray-500">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" x-model="formData.remember"> Remember me
                            </label>
                            <a x-show="!hideForgot" href="/forgot-password" class="text-indigo-600 hover:underline">Forgot Password?</a>
                        </div>
                    </div>

                    <!-- LOGIN BUTTON -->
                    <button type="submit" :disabled="isSubmitting || !emailValid" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition disabled:opacity-60">
                        <span x-show="!isSubmitting">Sign In</span>
                        <span x-show="isSubmitting">Signing in...</span>
                    </button>

                    <!-- SOCIAL LOGIN -->
                    <div class="mt-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-grow border-t"></div>
                            <span class="px-3 text-gray-400 text-sm">or continue with</span>
                            <div class="flex-grow border-t"></div>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <?php
                                $roleParam = $_GET['role'] ?? null;
                                $redirectParam = $redirect ?? '';
                                $isEmployerContext = ($roleParam === 'employer') || (is_string($redirectParam) && strpos($redirectParam, '/employer/') === 0);
                                $oauthRedirect = $isEmployerContext ? '/employer/dashboard' : '/candidate/dashboard';
                            ?>
                            <a href="/auth/google?redirect=<?= $oauthRedirect ?>" class="flex items-center justify-center border rounded-lg p-3 hover:bg-slate-50 transition" aria-label="Continue with Google">
                                <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google" class="h-6 w-6">
                            </a>
                            <a href="/auth/facebook?redirect=<?= $oauthRedirect ?>" class="flex items-center justify-center border rounded-lg p-3 hover:bg-slate-50 transition" aria-label="Continue with Facebook">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook" class="h-6 w-6">
                            </a>
                            <a href="/auth/linkedin?redirect=<?= $oauthRedirect ?>" class="flex items-center justify-center border rounded-lg p-3 hover:bg-slate-50 transition" aria-label="Continue with LinkedIn">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn" class="h-6 w-6">
                            </a>
                            <a href="/auth/microsoft?redirect=<?= $oauthRedirect ?>" class="flex items-center justify-center border rounded-lg p-3 hover:bg-slate-50 transition" aria-label="Continue with Microsoft">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft" class="h-6 w-6">
                            </a>
                        </div>
                    </div>

                    <!-- SIGNUP -->
                    <?php
                    $roleParam = $_GET['role'] ?? null;
                    $redirectParam = $redirect ?? '';
                    $isEmployerContext = ($roleParam === 'employer') || (is_string($redirectParam) && strpos($redirectParam, '/employer/') === 0);
                    $signupUrl = $isEmployerContext ? '/register-employer' : '/register-candidate';
                    $signupText = $isEmployerContext ? 'Create employer account' : 'Create candidate account';
                    ?>
                    <p class="mt-6 text-center text-gray-600 text-sm">
                        Don’t have an account?
                        <a href="<?= $signupUrl ?>" class="font-semibold text-indigo-600 hover:underline"><?= $signupText ?></a>
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
            return {
                isSubmitting: false,
                showPassword: false,
                error: '<?= $error ?? '' ?>',
                redirect: '<?= $redirect ?? '' ?>',
                emailValid: true,
                emailError: '',
                registrationSuccess: registered === '1',
                registrationMessage: registeredEmail ? `Account created for ${registeredEmail}. Please login.` : 'Account created successfully. Please login.',
                hideForgot: false,
                formData: {
                    email: registeredEmail || '',
                    password: '',
                    remember: false
                },
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
                },
                validateEmail() {
                    const email = this.formData.email;
                    if (!email) { this.emailValid = true; this.emailError = ''; return; }
                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regex.test(email)) {
                        this.emailValid = false;
                        this.emailError = 'Please enter a valid email address';
                    } else {
                        this.emailValid = true;
                        this.emailError = '';
                    }
                },
                async submitLogin() {
                    this.validateEmail();
                    if (!this.emailValid) return;
                    this.isSubmitting = true;
                    this.error = '';
                    try {
                        const res = await fetch('/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(this.formData)
                        });
                        const data = await res.json();
                        if (res.status === 403 && data && data.refresh_csrf && data.csrf_token) {
                            const meta = document.querySelector('meta[name="csrf-token"]');
                            if (meta) { meta.setAttribute('content', data.csrf_token); }
                            // retry once silently
                            const res2 = await fetch('/login', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-Token': data.csrf_token
                                },
                                body: JSON.stringify(this.formData)
                            });
                            const data2 = await res2.json();
                            if (res2.ok && (data2.success || data2.message === 'Login successful')) {
                                let redirectUrl = data2.redirect_to || this.redirect;
                                if (!redirectUrl) {
                                    redirectUrl = (data2.user && data2.user.role === 'employer') ? '/employer/dashboard' : '/';
                                }
                                window.location.href = redirectUrl;
                                this.isSubmitting = false;
                                return;
                            } else {
                                this.error = data2.error || 'Please try again';
                                this.isSubmitting = false;
                                return;
                            }
                        }
                        if (res.ok && (data.success || data.message === 'Login successful')) {
                            let redirectUrl = data.redirect_to || this.redirect;
                            if (!redirectUrl) {
                                redirectUrl = (data.user && data.user.role === 'employer') ? '/employer/dashboard' : '/';
                            }
                            window.location.href = redirectUrl;
                        } else {
                            this.error = data.error || data.message || 'Please try again';
                        }
                    } catch (e) {
                        this.error = 'Please try again';
                    }
                    this.isSubmitting = false;
                },
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            };
        }
    </script>
</body>
</html>
