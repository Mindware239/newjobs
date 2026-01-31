<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Login - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Smooth animations */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Input focus effects */
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>
<body class="min-h-screen">
    <div x-data="loginForm()" x-init="init()" x-cloak class="grid grid-cols-1 md:grid-cols-2 md:gap-8 min-h-screen">
        <div class="bg-white flex flex-col justify-center px-6 md:px-16 py-10">
            <a href="/" class="mb-8 text-sm text-gray-600 hover:text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Home
            </a>
            <div class="flex items-center gap-3 mb-6">
                <div class="h-10 w-10 rounded-md bg-gray-900 text-white flex items-center justify-center font-bold">M</div>
                <div class="text-xl font-semibold">Mindware</div>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Welcome back</h1>
            <p class="text-gray-600 mb-8">Sign in to access your dashboard</p>

            <div class="max-w-md">
                <div x-show="registrationSuccess" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <div class="flex-1">
                            <p class="text-sm text-blue-800 font-semibold">Registration Successful!</p>
                            <p class="text-xs text-blue-700 mt-1" x-text="registrationMessage"></p>
                        </div>
                    </div>
                </div>

                <div x-show="error" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <p class="text-sm text-red-800 font-medium" x-text="error"></p>
                    </div>
                </div>

                <form @submit.prevent="submitLogin()" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" x-model="formData.email" @input="validateEmail()" @blur="validateEmail()" required placeholder="Enter your email" :class="emailValid ? 'border-blue-600 focus:ring-blue-600' : (emailError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-600')" class="w-full px-4 py-3 border-2 rounded-md focus:outline-none transition">
                        <p x-show="emailError" class="mt-1 text-sm text-red-600" x-text="emailError"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="formData.password" required placeholder="Enter your password" class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required</p>
                        <div class="mt-2 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" x-model="formData.remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                Remember me
                            </label>
                            <a x-show="!hideForgot" href="/forgot-password" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" :disabled="isSubmitting || !emailValid" class="w-full px-4 py-3 bg-gray-900 text-white rounded-md hover:bg-black disabled:opacity-50 disabled:cursor-not-allowed font-semibold transition">
                        <span x-show="!isSubmitting" class="flex items-center justify-center">Sign In</span>
                        <span x-show="isSubmitting" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Signing in...
                        </span>
                    </button>

                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-3 bg-white text-xs text-gray-500">Or continue with</span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            <a href="/auth/google<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" class="flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 p-2" aria-label="Continue with Google">
                                <img alt="Google" class="h-6 w-6" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                            </a>
                            <a href="/auth/facebook<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" class="flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 p-2" aria-label="Continue with Facebook">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/>
                                    <path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/>
                                </svg>
                            </a>
                            <a href="/auth/linkedin<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" class="flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 p-2" aria-label="Continue with LinkedIn">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect width="24" height="24" rx="4" fill="#0A66C2"/>
                                    <path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/>
                                </svg>
                            </a>
                            <a href="/auth/microsoft<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" class="flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 p-2" aria-label="Continue with Microsoft">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
                                    <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
                                    <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
                                    <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600">Don't have an account? <a href="/register-candidate" class="font-semibold text-gray-900 hover:underline">Sign up</a></p>

                    <div class="mt-4 flex items-start gap-3 text-gray-600 text-xs">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-600">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span>Your data is secure. We use industry‑standard encryption to protect your personal information.</span>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-900 via-blue-950 to-gray-900 text-white flex items-center">
            <div class="px-6 md:pl-24 md:pr-16">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Discover Your Next Opportunity</h2>
                <p class="text-blue-100 mb-6 max-w-md">Connect with verified employers and take the next step in your career journey.</p>
                <ul class="space-y-3 text-blue-100">
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Verified employer listings</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Application tracking</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Profile visibility controls</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function loginForm() {
            // Check for registration success parameter
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
                registrationMessage: registeredEmail 
                    ? `Your account with ${registeredEmail} has been created successfully! Please check your email for confirmation. You can now log in.`
                    : 'Your account has been created successfully! Please check your email for confirmation. You can now log in.',
                hideForgot: <?php 
                    $r = $redirect ?? ''; 
                    echo (strpos($r, '/sales-exec') !== false || strpos($r, '/sales-manager') !== false) ? 'true' : 'false'; 
                ?>,
                formData: {
                    email: registeredEmail || '',
                    password: '',
                    remember: false
                },
                init() {
                    // Auto-hide success message after 10 seconds
                    if (this.registrationSuccess) {
                        setTimeout(() => {
                            this.registrationSuccess = false;
                            // Clean URL
                            const url = new URL(window.location);
                            url.searchParams.delete('registered');
                            url.searchParams.delete('email');
                            window.history.replaceState({}, '', url);
                        }, 10000);
                    }
                },
                
                validateEmail() {
                    const email = this.formData.email;
                    if (!email) {
                        this.emailValid = true;
                        this.emailError = '';
                        return;
                    }
                    
                    // International email validation (RFC 5322 compliant)
                    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
                    
                    if (!emailRegex.test(email)) {
                        this.emailValid = false;
                        this.emailError = 'Please enter a valid email address';
                    } else {
                        this.emailValid = true;
                        this.emailError = '';
                    }
                },
                
                async submitLogin() {
                    // Validate email before submission
                    this.validateEmail();
                    if (!this.emailValid) {
                        this.error = 'Please enter a valid email address';
                        return;
                    }
                    
                    this.isSubmitting = true;
                    this.error = '';
                    
                    try {
                        const response = await fetch('/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && (data.success || data.message === 'Login successful')) {
                            // Redirect based on role or redirect parameter from server
                            let redirectUrl = data.redirect_to || this.redirect;
                            if (!redirectUrl) {
                                if (data.user && data.user.role === 'employer') {
                                    redirectUrl = '/employer/dashboard';
                                } else {
                                    redirectUrl = '/';
                                }
                            }
                            // Force redirect immediately
                            window.location.href = redirectUrl;
                            return;
                        } else {
                            this.error = data.error || data.message || 'Invalid email or password. Please try again.';
                        }
                    } catch (error) {
                        this.error = 'An error occurred. Please try again.';
                        console.error('Login error:', error);
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
