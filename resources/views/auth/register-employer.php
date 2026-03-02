<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Employer Registration - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        [x-cloak]{display:none!important;}
    </style>
    <script>
        function employerRegistrationForm() {
            return {
                isSubmitting: false,
                error: '',
                success: '',
                showPassword: false,
                showConfirmPassword: false,
                emailValid: true,
                emailError: '',
                passwordValid: false,
                passwordError: '',
                passwordMatch: false,
                passwordMatchError: '',
                passwordStrengthText: '',
                passwordStrengthTextClass: '',
                passwordStrengthBarClass: 'bg-red-500',
                passwordStrengthBarStyle: 'width: 0%',
                passwordChecks: {
                    lowercase: false,
                    uppercase: false,
                    number: false,
                    special: false,
                    length: false,
                    noCommon: false
                },
                passwordSuggestions: [],
                formData: {
                    email: '',
                    password: '',
                    password_confirm: '',
                    agree_terms: false,
                    company_type: '',
                    industry: '',
                    industry_custom: ''
                },
                init() {},
                validateEmail() {
                    const email = this.formData.email;
                    if (!email) { this.emailValid = true; this.emailError = ''; return; }
                    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
                    if (!emailRegex.test(email)) { this.emailValid = false; this.emailError = 'Please enter a valid email address'; } else { this.emailValid = true; this.emailError = ''; }
                },
                checkPasswordStrength() {
                    const pw = this.formData.password || '';
                    this.passwordChecks = {
                        lowercase: /[a-z]/.test(pw),
                        uppercase: /[A-Z]/.test(pw),
                        number: /[0-9]/.test(pw),
                        special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(pw),
                        length: pw.length >= 8 && pw.length <= 20,
                        noCommon: !['password', 'password123', '12345678', '123456789', 'qwerty', 'admin', 'welcome'].includes(pw.toLowerCase())
                    };
                    let score = 0;
                    if (this.passwordChecks.lowercase) score += 15;
                    if (this.passwordChecks.uppercase) score += 15;
                    if (this.passwordChecks.number) score += 15;
                    if (this.passwordChecks.special) score += 20;
                    if (this.passwordChecks.length) score += 20;
                    if (this.passwordChecks.noCommon) score += 15;
                    if (pw.length >= 12) score += 5;
                    if (pw.length >= 16) score += 5;
                    if (score < 30) { this.passwordStrengthText = 'Very Weak'; this.passwordStrengthTextClass = 'text-red-600'; this.passwordStrengthBarClass = 'bg-red-500'; this.passwordStrengthBarStyle = 'width: 20%'; }
                    else if (score < 50) { this.passwordStrengthText = 'Weak'; this.passwordStrengthTextClass = 'text-orange-600'; this.passwordStrengthBarClass = 'bg-orange-500'; this.passwordStrengthBarStyle = 'width: 40%'; }
                    else if (score < 70) { this.passwordStrengthText = 'Fair'; this.passwordStrengthTextClass = 'text-yellow-600'; this.passwordStrengthBarClass = 'bg-yellow-500'; this.passwordStrengthBarStyle = 'width: 60%'; }
                    else if (score < 90) { this.passwordStrengthText = 'Good'; this.passwordStrengthTextClass = 'text-green-600'; this.passwordStrengthBarClass = 'bg-green-500'; this.passwordStrengthBarStyle = 'width: 80%'; }
                    else { this.passwordStrengthText = 'Strong'; this.passwordStrengthTextClass = 'text-green-700'; this.passwordStrengthBarClass = 'bg-green-600'; this.passwordStrengthBarStyle = 'width: 100%'; }
                    this.passwordValid = Object.values(this.passwordChecks).every(Boolean);
                    this.passwordError = this.passwordValid || pw.length === 0 ? '' : 'Password does not meet all requirements';
                    this.validatePasswordMatch();
                },
                validatePasswordMatch() {
                    if (!this.formData.password_confirm) { this.passwordMatch = false; this.passwordMatchError = ''; return; }
                    if (this.formData.password === this.formData.password_confirm) { this.passwordMatch = true; this.passwordMatchError = ''; }
                    else { this.passwordMatch = false; this.passwordMatchError = 'Passwords do not match'; }
                },
                async submitRegistration() {
                    this.error = ''; this.success = '';
                    this.validateEmail(); this.passwordValid = (this.formData.password || '').length >= 8;
                    if (!this.emailValid) { this.error = 'Please enter a valid email address'; return; }
                    if (!this.passwordValid) { this.error = 'Please enter a valid password'; return; }
                    if (!this.passwordMatch) { this.error = 'Passwords do not match'; return; }
                    this.isSubmitting = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const fd = new FormData();
                        fd.append('email', this.formData.email);
                        fd.append('password', this.formData.password);
                        fd.append('confirm_password', this.formData.password_confirm);
                        fd.append('role', 'employer');
                        fd.append('_token', csrf);
                        const response = await fetch('/register-employer', { method: 'POST', body: fd });
                        let data;
                        try { data = await response.json(); } catch(e) { this.error = 'Registration failed: Invalid server response.'; return; }
                        if (response.ok && data.success) {
                            this.success = data.message || 'Registration successful! Redirecting...';
                            setTimeout(() => { window.location.href = data.redirect || '/employer/profile?setup=1'; }, 1500);
                        } else {
                            const errorMsg = data.error || data.message || data.errors || 'Registration failed';
                            this.error = typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg;
                        }
                    } catch (err) {
                        this.error = 'An error occurred. Please try again.';
                    } finally { this.isSubmitting = false; }
                }
            }
        }
    </script>
    <?php if(true): ?>
</head>
<body class="min-h-screen">
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            try{ if(window.MWMarketing){ MWMarketing.trackInitiateRegistration({role:'employer'}); } }catch(_){}
        });
    </script>
    <div x-data="employerRegistrationForm()" x-init="init()" x-cloak class="grid grid-cols-1 md:grid-cols-2 md:gap-8 min-h-screen">
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
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Create your employer account</h1>
            <p class="text-gray-600 mb-8">Join our trusted recruitment platform</p>
            <div class="max-w-md">
                <div x-show="error"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-800 font-medium" x-text="error"></p>
                    </div>
                </div>
                <div x-show="success"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-800 font-medium" x-text="success"></p>
                    </div>
                </div>
                <form @submit.prevent="submitRegistration()" class="space-y-6" novalidate>
                    <div x-show="true">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email"
                               x-model="formData.email"
                               @input="validateEmail()"
                               @blur="validateEmail()"
                               required
                               placeholder="your@email.com"
                               :class="emailValid ? 'border-green-500 focus:ring-green-500' : (emailError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                               class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition">
                        <p class="mt-1 text-sm text-gray-500">We'll use this to create your account. You can add more details after registration.</p>
                        <p x-show="emailError" class="mt-1 text-sm text-red-600" x-text="emailError"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password"
                                   x-model="formData.password"
                                   @input="checkPasswordStrength()"
                                   required
                                   placeholder="Create a strong password"
                                   :class="passwordValid ? 'border-green-500 focus:ring-green-500' : (passwordError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                                   class="w-full px-4 py-3 pr-12 border-2 rounded-lg focus:outline-none transition">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="false" class="mt-2 space-y-2">
                            <div class="h-1.5 rounded bg-gray-200 overflow-hidden">
                                <div class="h-1.5" :class="passwordStrengthBarClass" :style="passwordStrengthBarStyle"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium" :class="passwordStrengthTextClass" x-text="passwordStrengthText"></span>
                                <span class="text-gray-500" x-text="formData.password.length + ' / 20 characters'"></span>
                            </div>
                        </div>
                        <p x-show="passwordError" class="mt-1 text-sm text-red-600" x-text="passwordError"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="confirm_password"
                                   x-model="formData.password_confirm"
                                   @input="validatePasswordMatch()"
                                   required
                                   placeholder="Re-enter your password"
                                   :class="passwordMatch ? 'border-green-500 focus:ring-green-500' : (passwordMatchError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                                   class="w-full px-4 py-3 pr-12 border-2 rounded-lg focus:outline-none transition">
                            <button type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268-2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <p x-show="passwordMatch && formData.password_confirm.length > 0" class="mt-1 text-sm text-green-600">Passwords match</p>
                        <p x-show="passwordMatchError" class="mt-1 text-sm text-red-600" x-text="passwordMatchError"></p>
                    </div>
                    <div class="mt-2" x-show="true">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-3 bg-white text-xs text-gray-500">Or continue with</span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            <a href="/auth/google?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Google">
                                <img alt="Google" class="h-6 w-6" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                            </a>
                            <a href="/auth/facebook?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Facebook">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/>
                                    <path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/>
                                </svg>
                            </a>
                            <a href="/auth/linkedin?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with LinkedIn">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect width="24" height="24" rx="4" fill="#0A66C2"/>
                                    <path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/>
                                </svg>
                            </a>
                            <a href="/auth/microsoft?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Microsoft">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
                                    <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
                                    <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
                                    <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-show="false">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Company Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="formData.company_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                                required>
                                <option value="" disabled selected>Select Company Type</option>
                                <option value="proprietorship">Proprietorship</option>
                                <option value="partnership">Partnership</option>
                                <option value="private_limited">Private Limited</option>
                                <option value="public_limited">Public Limited</option>
                                <option value="llp">Limited Liability Partnership (LLP)</option>
                                <option value="opc">One Person Company (OPC)</option>
                                <option value="government">Government / PSU</option>
                                <option value="non_profit">Non-Profit (NGO / Trust)</option>
                                <option value="startup">Startup</option>
                                <option value="freelancer">Freelancer / Individual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Industry Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="formData.industry"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                                required>
                                <option value="" disabled selected>Select Industry</option>
                                <option value="IT/Software">IT/Software</option>
                                <option value="Finance">Finance</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Education">Education</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Retail">Retail</option>
                                <option value="Real Estate">Real Estate</option>
                                <option value="Hospitality">Hospitality</option>
                                <option value="Other">Other</option>
                            </select>
                            <div x-show="formData.industry === 'Other'" class="mt-2">
                                <input type="text"
                                       x-model="formData.industry_custom"
                                       placeholder="Enter your industry"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start" x-show="true">
                        <input type="checkbox"
                               x-model="formData.agree_terms"
                               required
                               class="mt-1 mr-3 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label class="text-sm text-gray-600">
                            I agree to the <a href="#" class="text-green-600 hover:underline font-semibold">Terms and Conditions</a>
                            and <a href="#" class="text-green-600 hover:underline font-semibold">Privacy Policy</a>
                        </label>
                    </div>
                    <button type="submit"
                            :disabled="isSubmitting || !emailValid || (formData.password || '').length < 6"
                            class="w-full px-4 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                        <span x-show="!isSubmitting" class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Create Account
                        </span>
                        <span x-show="isSubmitting" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating Account...
                        </span>
                    </button>
                    <div class="text-sm text-gray-600">
                        Already have an account? <a href="/login?role=employer" class="font-semibold text-gray-900 hover:underline">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-900 via-blue-950 to-gray-900 text-white flex items-center">
            <div class="px-6 md:pl-24 md:pr-16">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Hire Faster with Mindware</h2>
                <p class="text-blue-100 mb-6 max-w-md">Connect with verified candidates and manage applications efficiently.</p>
                <ul class="space-y-3 text-blue-100">
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Verified candidate profiles</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Application tracking</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Company branding controls</li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        function employerRegistrationForm() {
            return {
                isSubmitting: false,
                error: '',
                success: '',
                showPassword: false,
                showConfirmPassword: false,
                emailValid: true,
                emailError: '',
                passwordValid: false,
                passwordError: '',
                passwordMatch: false,
                passwordMatchError: '',
                passwordStrengthText: '',
                passwordStrengthTextClass: '',
                passwordStrengthBarClass: 'bg-red-500',
                passwordStrengthBarStyle: 'width: 0%',
                passwordChecks: {
                    lowercase: false,
                    uppercase: false,
                    number: false,
                    special: false,
                    length: false,
                    noCommon: false
                },
                passwordSuggestions: [],
                formData: {
                    email: '',
                    password: '',
                    password_confirm: '',
                    agree_terms: false,
                    company_type: '',
                    industry: '',
                    industry_custom: ''
                },
                init() {},
                validateEmail() {
                    const email = this.formData.email;
                    if (!email) { this.emailValid = true; this.emailError = ''; return; }
                    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
                    if (!emailRegex.test(email)) { this.emailValid = false; this.emailError = 'Please enter a valid email address'; } else { this.emailValid = true; this.emailError = ''; }
                },
                checkPasswordStrength() {
                    const pw = this.formData.password || '';
                    this.passwordChecks = {
                        lowercase: /[a-z]/.test(pw),
                        uppercase: /[A-Z]/.test(pw),
                        number: /[0-9]/.test(pw),
                        special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(pw),
                        length: pw.length >= 8 && pw.length <= 20,
                        noCommon: !['password', 'password123', '12345678', '123456789', 'qwerty', 'admin', 'welcome'].includes(pw.toLowerCase())
                    };
                    let score = 0;
                    if (this.passwordChecks.lowercase) score += 15;
                    if (this.passwordChecks.uppercase) score += 15;
                    if (this.passwordChecks.number) score += 15;
                    if (this.passwordChecks.special) score += 20;
                    if (this.passwordChecks.length) score += 20;
                    if (this.passwordChecks.noCommon) score += 15;
                    if (pw.length >= 12) score += 5;
                    if (pw.length >= 16) score += 5;
                    if (score < 30) { this.passwordStrengthText = 'Very Weak'; this.passwordStrengthTextClass = 'text-red-600'; this.passwordStrengthBarClass = 'bg-red-500'; this.passwordStrengthBarStyle = 'width: 20%'; }
                    else if (score < 50) { this.passwordStrengthText = 'Weak'; this.passwordStrengthTextClass = 'text-orange-600'; this.passwordStrengthBarClass = 'bg-orange-500'; this.passwordStrengthBarStyle = 'width: 40%'; }
                    else if (score < 70) { this.passwordStrengthText = 'Fair'; this.passwordStrengthTextClass = 'text-yellow-600'; this.passwordStrengthBarClass = 'bg-yellow-500'; this.passwordStrengthBarStyle = 'width: 60%'; }
                    else if (score < 90) { this.passwordStrengthText = 'Good'; this.passwordStrengthTextClass = 'text-green-600'; this.passwordStrengthBarClass = 'bg-green-500'; this.passwordStrengthBarStyle = 'width: 80%'; }
                    else { this.passwordStrengthText = 'Strong'; this.passwordStrengthTextClass = 'text-green-700'; this.passwordStrengthBarClass = 'bg-green-600'; this.passwordStrengthBarStyle = 'width: 100%'; }
                    this.passwordValid = Object.values(this.passwordChecks).every(Boolean);
                    this.passwordError = this.passwordValid || pw.length === 0 ? '' : 'Password does not meet all requirements';
                    this.validatePasswordMatch();
                },
                validatePasswordMatch() {
                    if (!this.formData.password_confirm) { this.passwordMatch = false; this.passwordMatchError = ''; return; }
                    if (this.formData.password === this.formData.password_confirm) { this.passwordMatch = true; this.passwordMatchError = ''; }
                    else { this.passwordMatch = false; this.passwordMatchError = 'Passwords do not match'; }
                },
                async submitRegistration() {
                    this.error = ''; this.success = '';
                    this.validateEmail(); this.passwordValid = (this.formData.password || '').length >= 8;
                    if (!this.emailValid) { this.error = 'Please enter a valid email address'; return; }
                    if (!this.passwordValid) { this.error = 'Please enter a valid password'; return; }
                    if (!this.passwordMatch) { this.error = 'Passwords do not match'; return; }
                    this.isSubmitting = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const fd = new FormData();
                        fd.append('email', this.formData.email);
                        fd.append('password', this.formData.password);
                        fd.append('confirm_password', this.formData.password_confirm);
                        fd.append('role', 'employer');
                        fd.append('_token', csrf);
                        const response = await fetch('/register-employer', { method: 'POST', body: fd });
                        let data;
                        try { data = await response.json(); } catch(e) { this.error = 'Registration failed: Invalid server response.'; return; }
                        if (response.ok && data.success) {
                            this.success = data.message || 'Registration successful! Redirecting...';
                            setTimeout(() => { window.location.href = data.redirect || '/employer/profile?setup=1'; }, 1500);
                        } else {
                            const errorMsg = data.error || data.message || data.errors || 'Registration failed';
                            this.error = typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg;
                        }
                    } catch (err) {
                        this.error = 'An error occurred. Please try again.';
                    } finally { this.isSubmitting = false; }
                }
            }
        }
    </script>
    <div x-ignore inert x-cloak x-show="false" class="min-h-screen flex flex-col">
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>
        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 md:px-16 py-12 w-full flex">
                <!-- Left Sidebar -->
                <div class="w-64 pr-8 hidden lg:block">
                    <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Registration Steps</h2>
                        <div class="relative step-connector">
                            <div class="step-item mb-6" :class="{'text-green-600': currentStep === 1, 'text-gray-400': currentStep !== 1}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 1, 'bg-gray-100 text-gray-400': currentStep !== 1}">
                                        <i class="fas fa-user-tie text-xs"></i>
                                    </div>
                                    <span class="font-medium">Basic Information</span>
                                </div>
                            </div>
                            <div class="step-item mb-6" :class="{'text-green-600': currentStep === 2, 'text-gray-400': currentStep !== 2}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 2, 'bg-gray-100 text-gray-400': currentStep !== 2}">
                                        <i class="fas fa-map-marker-alt text-xs"></i>
                                    </div>
                                    <span class="font-medium">Address Information</span>
                                </div>
                            </div>
                            <div class="step-item" :class="{'text-green-600': currentStep === 3, 'text-gray-400': currentStep !== 3}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 3, 'bg-gray-100 text-gray-400': currentStep !== 3}">
                                        <i class="fas fa-file-alt text-xs"></i>
                                    </div>
                                    <span class="font-medium">Document Verifications</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Form Content -->
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                        <div class="mb-8 text-center">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Employer Registration</h1>
                            <p class="text-gray-600">Create your employer account and start posting jobs</p>
                        </div>
                        <form @submit.prevent="submitRegistration()" class="space-y-8" novalidate>
                            <!-- Step 1: Basic Information -->
                            <div x-show="currentStep === 1" x-transition>
                                <div class="mb-6">
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center">
                                            <div class="w-full border-t border-gray-200"></div>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-3 bg-white text-xs text-gray-500">Or continue with</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-4 gap-3">
                                        <a href="/auth/google?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Google">
                                            <img alt="Google" class="h-6 w-6" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                                        </a>
                                        <!-- <a href="/auth/facebook?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Facebook">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/>
                                                <path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/>
                                            </svg>
                                        </a> -->
                                        <a href="/auth/linkedin?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with LinkedIn">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect width="24" height="24" rx="4" fill="#0A66C2"/>
                                                <path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/>
                                            </svg>
                                        </a>
                                        <a href="/auth/microsoft?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Microsoft">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
                                                <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
                                                <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
                                                <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-info-circle mr-2"></i> Basic Information
                                    </h2>
                                    <p class="text-sm text-green-600">Tell us about your company</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                            </div>
                                            <input type="email" x-model="formData.email" required
                                                placeholder="your@email.com"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                            <input :type="showPassword ? 'text' : 'password'" x-model="formData.password" required
                                                placeholder="Create a strong password"
                                                :class="passwordValid ? 'w-full pl-10 px-4 py-3 border rounded-lg border-blue-600 focus:ring-2 focus:ring-blue-600 focus:border-blue-600' : (passwordError ? 'w-full pl-10 px-4 py-3 border rounded-lg border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'w-full pl-10 px-4 py-3 border rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-blue-600')">
                                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                        <div x-show="formData.password.length > 0" class="mt-2 space-y-2">
                                            <div class="h-2 rounded bg-gray-200 overflow-hidden">
                                                <div class="h-2" :class="passwordStrengthBarClass" :style="passwordStrengthBarStyle"></div>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="font-medium" :class="passwordStrengthTextClass" x-text="passwordStrengthText"></span>
                                                <span class="text-gray-500" x-text="formData.password.length + ' / 20 characters'"></span>
                                            </div>
                                            <div x-show="!passwordValid && formData.password.length > 0" class="mt-3 p-3 bg-green-50 border-l-4 border-green-500 rounded">
                                                <p class="text-sm text-gray-700">Password Suggestions:</p>
                                                <ul class="list-disc list-inside text-sm text-gray-600">
                                                    <template x-for="suggestion in passwordSuggestions" :key="suggestion">
                                                        <li x-text="suggestion"></li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <p x-show="passwordError" class="mt-1 text-sm text-red-600" x-text="passwordError"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                            <input type="password" x-model="formData.confirm_password" required
                                                placeholder="Re-enter your password"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-show="false" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    
                                    <div x-data="{open:false, q:'', hover:-1}" @keydown.escape.window="open=false">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <button type="button" @click="open=!open" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white flex items-center justify-between">
                                                <span class="flex items-center gap-2">
                                                    <template x-if="countries.find(c => c.name === formData.country)">
                                                        <img :src="flagUrlFromIso(countries.find(c => c.name === formData.country)?.code || '')" width="24" height="18" class="inline-block rounded-sm border border-gray-200">
                                                    </template>
                                                    <span x-text="formData.country || 'Select Country'"></span>
                                                </span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <div x-show="open" x-transition class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow">
                                                <div class="p-2">
                                                    <input type="text" x-model="q" placeholder="Search country" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                                </div>
                                                <ul class="max-h-60 overflow-auto">
                                                    <template x-for="(country, idx) in countries.filter(c => c.name.toLowerCase().includes(q.toLowerCase()))" :key="country.name">
                                                        <li @mouseenter="hover=idx" @mouseleave="hover=-1" @click="formData.country = country.name; open=false; updateCountryType(); updatePhoneCodeFromCountry()"
                                                            :class="hover===idx ? 'bg-gray-50' : ''"
                                                            class="px-3 py-2 cursor-pointer flex items-center gap-2">
                                                            <img :src="flagUrlFromIso(country.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                            <span class="text-sm" x-text="country.name"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <select x-model="formData.country" class="hidden">
                                                <option value="">Select Country</option>
                                                <template x-for="country in countries" :key="country.name">
                                                    <option :value="country.name" x-text="country.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Company Type <span class="text-red-500">*</span>
                                        </label>
    
                                        <select
                                            x-model="formData.company_type"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                            required>
                                            <option value="" disabled selected>Select Company Type</option>
    
                                            <option value="proprietorship">Proprietorship</option>
                                            <option value="partnership">Partnership</option>
                                            <option value="private_limited">Private Limited</option>
                                            <option value="public_limited">Public Limited</option>
                                            <option value="llp">Limited Liability Partnership (LLP)</option>
                                            <option value="opc">One Person Company (OPC)</option>
                                            <option value="government">Government / PSU</option>
                                            <option value="non_profit">Non-Profit (NGO / Trust)</option>
                                            <option value="startup">Startup</option>
                                            <option value="freelancer">Freelancer / Individual</option>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="false" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-building text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.company_name" required
                                                placeholder="Your Company Name"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                        <div class="flex">
                                            <div class="relative">
                                                <button type="button" @click="phoneCodeOpen = !phoneCodeOpen" class="px-3 py-3 border border-gray-300 rounded-l-lg bg-gray-50 w-48 flex items-center gap-2">
                                                    <template x-if="countries.find(c => c.phone === formData.country_code)">
                                                        <img :src="flagUrlFromIso(countries.find(c => c.phone === formData.country_code)?.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                    </template>
                                                    <span class="text-sm" x-text="formData.country_code || '+Code'"></span>
                                                </button>
                                                <div x-show="phoneCodeOpen" x-transition @click.away="phoneCodeOpen = false" @keydown.escape.window="phoneCodeOpen = false" class="absolute z-50 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow">
                                                    <div class="p-2">
                                                        <input type="text" x-model="phoneCodeSearch" placeholder="Search code or country" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                                    </div>
                                                    <ul class="max-h-60 overflow-auto">
                                                        <template x-for="country in countries.filter(c => (c.name.toLowerCase().includes(phoneCodeSearch.toLowerCase()) || (c.phone || '').includes(phoneCodeSearch)))" :key="country.name">
                                                            <li @click="formData.country_code = country.phone; phoneCodeOpen = false; updatePhoneCodeFromCountry()"
                                                                class="px-3 py-2 cursor-pointer flex items-center gap-2 hover:bg-gray-50">
                                                                <img :src="flagUrlFromIso(country.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                                <span class="text-sm" x-text="(country.phone || '') + ' (' + country.name + ')'"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                                <select x-model="formData.country_code" class="hidden">
                                                    <template x-for="country in countries" :key="country.name">
                                                        <option :value="country.phone" x-text="country.phone"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <input type="tel" x-model="formData.phone"
                                                placeholder="Phone number"
                                                class="flex-1 px-4 py-3 border border-gray-300 rounded-r-lg -ml-px focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"
                                                x-on:input="sanitizePhone()"
                                                :maxlength="String(formData.country_code || '').startsWith('+91') ? 10 : 15"
                                                inputmode="numeric">
                                        </div>
                                    </div>
                                </div>
                                

                                <div x-show="false" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Website</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-globe text-gray-400"></i>
                                            </div>
                                            <input type="url" x-model="formData.website"
                                                placeholder="https://www.company.com"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Size <span class="text-red-500">*</span></label>
                                        <select x-model="formData.company_size"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                            <option value="">Select Size</option>
                                            <option value="1-10">1-10 employees</option>
                                            <option value="11-50">11-50 employees</option>
                                            <option value="51-200">51-200 employees</option>
                                            <option value="201-500">201-500 employees</option>
                                            <option value="501-1000">501-1000 employees</option>
                                            <option value="1001+">1001+ employees</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div x-show="false" class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Description</label>
                                    <textarea x-model="formData.description" rows="4"
                                        placeholder="Brief description about your company..."
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"></textarea>
                                </div>
                                <div x-show="false" class="flex justify-between">
                                    <button type="button" @click="saveStep(1)"
                                        :disabled="isSaving"
                                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                        <i class="fas fa-save mr-1"></i>
                                        <span x-show="!isSaving">Save</span>
                                        <span x-show="isSaving">Saving...</span>
                                    </button>
                                    <button type="button" @click="currentStep = 2"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                                        <i class="fas fa-arrow-right mr-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Step 2: Address Information -->
                            <div x-show="currentStep === 2" x-transition>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2"></i> Address Information
                                    </h2>
                                    <p class="text-sm text-green-600">Where is your company located?</p>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span x-text="flagFromIso(countries.find(c => c.name === formData.country)?.code || '')"></span>
                                        </div>
                                        <select x-model="formData.country" @change="updatePhoneCodeFromCountry()"
                                            class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                            <option value="">Select Country</option>
                                            <template x-for="country in countries" :key="country.code">
                                                <option :value="country.name" x-text="`${country.name} (${country.phone || ''})`"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map-marked-alt text-gray-400"></i>
                                        </div>
                                        <input type="text" x-model="formData.address.street" required
                                            placeholder="Street address"
                                            class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-city text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.city" required
                                                placeholder="City"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">State/Province <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-flag text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.state" required
                                                placeholder="State"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-mail-bulk text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.postal_code" required
                                                placeholder="Postal code"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-red-600 mb-4" x-show="addressError" x-text="addressError"></p>
                                <div class="mb-6">
                                    <div class="flex justify-between mb-2">
                                        <button type="button" @click="autoDetectLocation()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Use my location
                                        </button>
                                        <div class="text-sm text-gray-500" x-show="formData.address.lat && formData.address.lng">
                                            <span>Lat: </span><span x-text="formData.address.lat && formData.address.lat.toFixed ? formData.address.lat.toFixed(5) : formData.address.lat"></span>,
                                            <span>Lng: </span><span x-text="formData.address.lng && formData.address.lng.toFixed ? formData.address.lng.toFixed(5) : formData.address.lng"></span>
                                        </div>
                                    </div>
                                    <div id="employer-map" class="w-full h-64 rounded-lg border border-gray-200"></div>
                                    <p class="mt-2 text-sm text-gray-600">Drag the marker to your exact location. Address will update automatically.</p>
                                </div>
                                <div class="flex justify-between">
                                    <div class="flex gap-3">
                                        <button type="button" @click="currentStep = 1"
                                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-1"></i> Back
                                        </button>
                                        <button type="button" @click="saveStep(2)"
                                            :disabled="isSaving"
                                            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                            <i class="fas fa-save mr-1"></i>
                                            <span x-show="!isSaving">Save</span>
                                            <span x-show="isSaving">Saving...</span>
                                        </button>
                                    </div>
                                    <button type="button" @click="goToStep3()" :disabled="!addressValid()"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-arrow-right mr-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Step 3: KYC Documentation -->
                            <div x-show="currentStep === 3" x-transition>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-file-alt mr-2"></i> Document Verifications
                                    </h2>
                                    <p class="text-sm text-green-600">Upload documents for verification (optional now)</p>
                                </div>
                                <div class="space-y-6">
                                    <!-- Business License -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-file-contract text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Business License / Registration Certificate <span class="text-red-500">* 2 MB Only</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.businessLicense.click()"
                                            @dragover.prevent="dragOver($event, 'business_license')"
                                            @dragleave.prevent="dragLeave($event, 'business_license')"
                                            @drop.prevent="dropFile($event, 'business_license')">
                                            <input type="file" @change="handleFileUpload($event, 'business_license')" x-ref="businessLicense" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.business_license">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">PDF, JPG, PNG (Max 2MB)</p>
                                            </template>
                                            <template x-if="formData.documents.business_license">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.business_license.preview" 
                                                             :src="formData.documents.business_license.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.business_license.preview && formData.documents.business_license.previewURL" 
                                                                :src="formData.documents.business_license.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.business_license.preview && !formData.documents.business_license.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.business_license.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.business_license.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.business_license.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('business_license')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('business_license')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Tax ID -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-file-invoice text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Tax ID / GST Number <span class="text-red-500">* 2 MB ONLY</span></h3>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">GST Number / Tax ID</label>
                                            <input type="text" x-model="formData.tax_id"
                                                placeholder="GST Number / Tax ID"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.taxId.click()"
                                            @dragover.prevent="dragOver($event, 'tax_id')"
                                            @dragleave.prevent="dragLeave($event, 'tax_id')"
                                            @drop.prevent="dropFile($event, 'tax_id')">
                                            <input type="file" @change="handleFileUpload($event, 'tax_id')" x-ref="taxId" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.tax_id">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">PDF, JPG, PNG (Max 2MB)</p>
                                            </template>
                                            <template x-if="formData.documents.tax_id">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.tax_id.preview" 
                                                             :src="formData.documents.tax_id.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.tax_id.preview && formData.documents.tax_id.previewURL" 
                                                                :src="formData.documents.tax_id.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.tax_id.preview && !formData.documents.tax_id.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.tax_id.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.tax_id.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.tax_id.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('tax_id')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('tax_id')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Address Proof -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-home text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Address Proof <span class="text-red-500">* 2 MB ONLY</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.addressProof.click()"
                                            @dragover.prevent="dragOver($event, 'address_proof')"
                                            @dragleave.prevent="dragLeave($event, 'address_proof')"
                                            @drop.prevent="dropFile($event, 'address_proof')">
                                            <input type="file" @change="handleFileUpload($event, 'address_proof')" x-ref="addressProof" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.address_proof">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Utility bill, Bank statement, etc. (PDF, JPG, PNG) Max 2MB</p>
                                            </template>
                                            <template x-if="formData.documents.address_proof">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.address_proof.preview" 
                                                             :src="formData.documents.address_proof.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.address_proof.preview && formData.documents.address_proof.previewURL" 
                                                                :src="formData.documents.address_proof.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.address_proof.preview && !formData.documents.address_proof.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.address_proof.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.address_proof.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.address_proof.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('address_proof')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('address_proof')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Director ID (for International) -->
                                    <div x-show="formData.company_type === 'international'" class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-id-card text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Director/Authorized Person ID <span class="text-red-500">*</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.directorId.click()"
                                            @dragover.prevent="dragOver($event, 'director_id')"
                                            @dragleave.prevent="dragLeave($event, 'director_id')"
                                            @drop.prevent="dropFile($event, 'director_id')">
                                            <input type="file" @change="handleFileUpload($event, 'director_id')" x-ref="directorId" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.director_id">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Passport, National ID, etc. (PDF, JPG, PNG) Max 2MB</p>
                                            </template>
                                            <template x-if="formData.documents.director_id">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.director_id.preview" 
                                                             :src="formData.documents.director_id.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.director_id.preview && formData.documents.director_id.previewURL" 
                                                                :src="formData.documents.director_id.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.director_id.preview && !formData.documents.director_id.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.director_id.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.director_id.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.director_id.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('director_id')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('director_id')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Additional Documents -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-paperclip text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Additional Documents (Optional)</h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.otherDoc.click()"
                                            @dragover.prevent="dragOver($event, 'other')"
                                            @dragleave.prevent="dragLeave($event, 'other')"
                                            @drop.prevent="dropFile($event, 'other')">
                                            <input type="file" @change="handleFileUpload($event, 'other')" x-ref="otherDoc" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.other">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Any other relevant documents</p>
                                            </template>
                                            <template x-if="formData.documents.other">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.other.preview" 
                                                             :src="formData.documents.other.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.other.preview && formData.documents.other.previewURL" 
                                                                :src="formData.documents.other.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.other.preview && !formData.documents.other.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.other.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.other.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.other.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('other')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('other')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <!-- Terms and Conditions -->
                                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="flex items-start">
                                        <input type="checkbox" x-model="formData.accept_terms" required
                                            class="mt-1 mr-2 text-green-600">
                                        <span class="text-sm text-gray-700">
                                            I agree to the <a href="#" class="text-green-600 hover:underline">Terms and Conditions</a>
                                            and <a href="#" class="text-green-600 hover:underline">Privacy Policy</a> <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="flex justify-between mt-8">
                                    <div class="flex gap-3">
                                        <button type="button" @click="currentStep = 2"
                                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-1"></i> Back
                                        </button>
                                        <button type="button" @click="saveStep(3)"
                                            :disabled="isSaving"
                                            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                            <i class="fas fa-save mr-1"></i>
                                            <span x-show="!isSaving">Save</span>
                                            <span x-show="isSaving">Saving...</span>
                                        </button>
                                    </div>
                                    <button type="submit"
                                        :disabled="isSubmitting"
                                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition flex items-center">
                                        <span x-show="!isSubmitting">Create Account</span>
                                        <span x-show="isSubmitting">Submitting...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
