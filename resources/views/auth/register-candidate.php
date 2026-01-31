<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Candidate Registration - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Password Strength Meter */
        .password-strength-meter {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: linear-gradient(to right, #ef4444 0%, #ef4444 33%, #e5e7eb 33%, #e5e7eb 100%); }
        .strength-fair { background: linear-gradient(to right, #f59e0b 0%, #f59e0b 66%, #e5e7eb 66%, #e5e7eb 100%); }
        .strength-good { background: linear-gradient(to right, #3b82f6 0%, #3b82f6 100%); }
        .strength-strong { background: linear-gradient(to right, #10b981 0%, #10b981 100%); }
        
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
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        /* Checkmark animation */
        .checkmark {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #10b981;
            position: relative;
            margin-right: 8px;
        }
        
        .checkmark::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        .crossmark {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e5e7eb;
            position: relative;
            margin-right: 8px;
        }
        
        .crossmark::after {
            content: '✕';
            position: absolute;
            left: 3px;
            top: -2px;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body class="min-h-screen">
    <div x-data="registrationForm()" x-cloak class="grid grid-cols-1 md:grid-cols-2 md:gap-8 min-h-screen">
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
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Create your account</h1>
            <p class="text-gray-600 mb-8">Join our trusted recruitment platform</p>
            <div class="max-w-md">

                <!-- Error Message -->
                <div x-show="error" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-md fade-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-800 font-medium" x-text="error"></p>
                    </div>
                </div>

                <!-- Success Message -->
                <div x-show="success" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-md fade-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-800 font-medium" x-text="success"></p>
                    </div>
                </div>

                <form @submit.prevent="submitRegistration()" class="space-y-6">
                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               x-model="formData.email"
                               @input="validateEmail()"
                               @blur="validateEmail()"
                               required
                               placeholder="your@email.com"
                               :class="emailValid ? 'border-green-500 focus:ring-green-500' : (emailError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                               class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition input-focus">
                        <p class="mt-1 text-sm text-gray-500">We'll use this to create your account. You can add more details after registration.</p>
                        <p x-show="emailError" class="mt-1 text-sm text-red-600" x-text="emailError"></p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   x-model="formData.password"
                                   @input="checkPasswordStrength()"
                                   required
                                   placeholder="Create a strong password"
                                   :class="passwordValid ? 'border-green-500 focus:ring-green-500' : (passwordError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                                   class="w-full px-4 py-3 pr-12 border-2 rounded-lg focus:outline-none transition input-focus">
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
                        
                        <!-- Password Strength Meter -->
                        <div x-show="formData.password.length > 0" class="mt-2 space-y-2 fade-in">
                            <div class="password-strength-meter" :class="passwordStrengthClass"></div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium" :class="passwordStrengthTextClass" x-text="passwordStrengthText"></span>
                                <span class="text-gray-500" x-text="formData.password.length + ' / 20 characters'"></span>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div x-show="formData.password.length > 0" class="mt-4 p-4 bg-gray-50 rounded-lg space-y-2 fade-in">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Password Requirements</p>
                            <div class="space-y-1.5">
                                <div class="flex items-center text-sm" :class="passwordChecks.lowercase ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.lowercase" class="checkmark"></span>
                                    <span x-show="!passwordChecks.lowercase" class="crossmark"></span>
                                    <span>At least one lowercase letter (a-z)</span>
                                </div>
                                <div class="flex items-center text-sm" :class="passwordChecks.uppercase ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.uppercase" class="checkmark"></span>
                                    <span x-show="!passwordChecks.uppercase" class="crossmark"></span>
                                    <span>At least one uppercase letter (A-Z)</span>
                                </div>
                                <div class="flex items-center text-sm" :class="passwordChecks.number ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.number" class="checkmark"></span>
                                    <span x-show="!passwordChecks.number" class="crossmark"></span>
                                    <span>At least one number (0-9)</span>
                                </div>
                                <div class="flex items-center text-sm" :class="passwordChecks.special ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.special" class="checkmark"></span>
                                    <span x-show="!passwordChecks.special" class="crossmark"></span>
                                    <span>At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                                </div>
                                <div class="flex items-center text-sm" :class="passwordChecks.length ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.length" class="checkmark"></span>
                                    <span x-show="!passwordChecks.length" class="crossmark"></span>
                                    <span>Between 8 and 20 characters (NIST recommended)</span>
                                </div>
                                <div class="flex items-center text-sm" :class="passwordChecks.noCommon ? 'text-green-600' : 'text-gray-500'">
                                    <span x-show="passwordChecks.noCommon" class="checkmark"></span>
                                    <span x-show="!passwordChecks.noCommon" class="crossmark"></span>
                                    <span>Not a common password (e.g., "password123")</span>
                                </div>
                            </div>
                            
                            <!-- Password Suggestions -->
                            <div x-show="!passwordValid && formData.password.length > 0" class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 rounded fade-in">
                                <p class="text-sm font-semibold text-blue-800 mb-1">💡 Password Suggestions:</p>
                                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                                    <template x-for="suggestion in passwordSuggestions" :key="suggestion">
                                        <li x-text="suggestion"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        
                        <p x-show="passwordError" class="mt-1 text-sm text-red-600" x-text="passwordError"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" 
                                   x-model="formData.password_confirm"
                                   @input="validatePasswordMatch()"
                                   required
                                   placeholder="Re-enter your password"
                                   :class="passwordMatch ? 'border-green-500 focus:ring-green-500' : (passwordMatchError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-green-500')"
                                   class="w-full px-4 py-3 pr-12 border-2 rounded-lg focus:outline-none transition input-focus">
                            <button type="button" 
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <p x-show="passwordMatch && formData.password_confirm.length > 0" class="mt-1 text-sm text-green-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Passwords match
                        </p>
                        <p x-show="passwordMatchError" class="mt-1 text-sm text-red-600" x-text="passwordMatchError"></p>
                    </div>

                    <div class="mt-2">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-3 bg-white text-xs text-gray-500">Or continue with</span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            <a href="/auth/google?redirect=/candidate/dashboard" class="flex items-center justify-center rounded-md border border-blue-300 bg-white hover:bg-blue-50 p-2" aria-label="Continue with Google">
                                <img alt="Google" class="h-6 w-6" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                            </a>
                            <a href="/auth/facebook?redirect=/candidate/dashboard" class="flex items-center justify-center rounded-md border border-blue-300 bg-white hover:bg-blue-50 p-2" aria-label="Continue with Facebook">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/>
                                    <path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/>
                                </svg>
                            </a>
                            <a href="/auth/linkedin?redirect=/candidate/dashboard" class="flex items-center justify-center rounded-md border border-blue-300 bg-white hover:bg-blue-50 p-2" aria-label="Continue with LinkedIn">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect width="24" height="24" rx="4" fill="#0A66C2"/>
                                    <path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/>
                                </svg>
                            </a>
                            <a href="/auth/microsoft?redirect=/candidate/dashboard" class="flex items-center justify-center rounded-md border border-blue-300 bg-white hover:bg-blue-50 p-2" aria-label="Continue with Microsoft">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
                                    <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
                                    <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
                                    <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               x-model="formData.agree_terms" 
                               required
                               class="mt-1 mr-3 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label class="text-sm text-gray-600">
                            I agree to the <a href="#" class="text-green-600 hover:underline font-semibold">Terms and Conditions</a> 
                            and <a href="#" class="text-green-600 hover:underline font-semibold">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="isSubmitting || !passwordValid || !passwordMatch || !emailValid || !formData.agree_terms"
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
                        Already have an account? <a href="/login" class="font-semibold text-gray-900 hover:underline">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-900 via-blue-950 to-gray-900 text-white flex items-center">
            <div class="px-6 md:pl-24 md:pr-16">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Discover Your Next Opportunity</h2>
                <p class="text-blue-100 mb-6 max-w-md">Connect with verified employers and take the next step in your career journey.</p>
                <ul class="space-y-3 text-blue-100">
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Verified employer listings</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Application tracking</li>
                    <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Profile visibility controls</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function registrationForm() {
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
                passwordStrength: 0,
                passwordStrengthText: '',
                passwordStrengthTextClass: '',
                passwordStrengthClass: '',
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
                    agree_terms: false
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
                
                checkPasswordStrength() {
                    const password = this.formData.password;
                    
                    // Reset checks
                    this.passwordChecks = {
                        lowercase: /[a-z]/.test(password),
                        uppercase: /[A-Z]/.test(password),
                        number: /[0-9]/.test(password),
                        special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password),
                        length: password.length >= 8 && password.length <= 20,
                        noCommon: !this.isCommonPassword(password)
                    };
                    
                    // Calculate strength score (0-100)
                    let score = 0;
                    if (this.passwordChecks.lowercase) score += 15;
                    if (this.passwordChecks.uppercase) score += 15;
                    if (this.passwordChecks.number) score += 15;
                    if (this.passwordChecks.special) score += 20;
                    if (this.passwordChecks.length) score += 20;
                    if (this.passwordChecks.noCommon) score += 15;
                    
                    // Adjust for length bonus
                    if (password.length >= 12) score += 5;
                    if (password.length >= 16) score += 5;
                    
                    this.passwordStrength = score;
                    
                    // Set strength text and class
                    if (score < 30) {
                        this.passwordStrengthText = 'Very Weak';
                        this.passwordStrengthTextClass = 'text-red-600';
                        this.passwordStrengthClass = 'strength-weak';
                    } else if (score < 50) {
                        this.passwordStrengthText = 'Weak';
                        this.passwordStrengthTextClass = 'text-orange-600';
                        this.passwordStrengthClass = 'strength-weak';
                    } else if (score < 70) {
                        this.passwordStrengthText = 'Fair';
                        this.passwordStrengthTextClass = 'text-yellow-600';
                        this.passwordStrengthClass = 'strength-fair';
                    } else if (score < 85) {
                        this.passwordStrengthText = 'Good';
                        this.passwordStrengthTextClass = 'text-blue-600';
                        this.passwordStrengthClass = 'strength-good';
                    } else {
                        this.passwordStrengthText = 'Strong';
                        this.passwordStrengthTextClass = 'text-green-600';
                        this.passwordStrengthClass = 'strength-strong';
                    }
                    
                    // Generate suggestions
                    this.generatePasswordSuggestions();
                    
                    // Validate password
                    this.passwordValid = Object.values(this.passwordChecks).every(check => check === true);
                    
                    if (!this.passwordValid && password.length > 0) {
                        this.passwordError = 'Password does not meet all requirements';
                    } else {
                        this.passwordError = '';
                    }
                    
                    // Re-validate password match
                    this.validatePasswordMatch();
                },
                
                isCommonPassword(password) {
                    const commonPasswords = [
                        'password', 'password123', '12345678', '123456789', '1234567890',
                        'qwerty123', 'admin123', 'letmein', 'welcome123', 'monkey123',
                        'dragon', 'master', 'sunshine', 'princess', 'football'
                    ];
                    return commonPasswords.includes(password.toLowerCase());
                },
                
                generatePasswordSuggestions() {
                    this.passwordSuggestions = [];
                    
                    if (!this.passwordChecks.lowercase) {
                        this.passwordSuggestions.push('Add lowercase letters (a-z)');
                    }
                    if (!this.passwordChecks.uppercase) {
                        this.passwordSuggestions.push('Add uppercase letters (A-Z)');
                    }
                    if (!this.passwordChecks.number) {
                        this.passwordSuggestions.push('Add numbers (0-9)');
                    }
                    if (!this.passwordChecks.special) {
                        this.passwordSuggestions.push('Add special characters (!@#$%^&*)');
                    }
                    if (!this.passwordChecks.length) {
                        if (this.formData.password.length < 8) {
                            this.passwordSuggestions.push('Make it at least 8 characters long');
                        } else {
                            this.passwordSuggestions.push('Keep it under 20 characters');
                        }
                    }
                    if (!this.passwordChecks.noCommon) {
                        this.passwordSuggestions.push('Avoid common passwords - use a unique combination');
                    }
                    if (this.passwordChecks.length && this.formData.password.length < 12) {
                        this.passwordSuggestions.push('Consider making it 12+ characters for better security');
                    }
                },
                
                validatePasswordMatch() {
                    if (!this.formData.password_confirm) {
                        this.passwordMatch = false;
                        this.passwordMatchError = '';
                        return;
                    }
                    
                    if (this.formData.password === this.formData.password_confirm) {
                        this.passwordMatch = true;
                        this.passwordMatchError = '';
                    } else {
                        this.passwordMatch = false;
                        this.passwordMatchError = 'Passwords do not match';
                    }
                },
                
                async submitRegistration() {
                    this.error = '';
                    this.success = '';
                    
                    // Final validation
                    this.validateEmail();
                    this.checkPasswordStrength();
                    this.validatePasswordMatch();
                    
                    if (!this.emailValid) {
                        this.error = 'Please enter a valid email address';
                        return;
                    }
                    
                    if (!this.passwordValid) {
                        this.error = 'Password does not meet all security requirements';
                        return;
                    }
                    
                    if (!this.passwordMatch) {
                        this.error = 'Passwords do not match';
                        return;
                    }
                    
                    if (!this.formData.agree_terms) {
                        this.error = 'Please agree to the Terms and Conditions';
                        return;
                    }
                    
                    this.isSubmitting = true;
                    try {
                        const response = await fetch('/register-candidate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.success = data.message || 'Registration successful! Please check your email for confirmation. Redirecting to login...';
                            // Show success message for 3 seconds before redirecting
                            setTimeout(() => {
                                window.location.href = data.redirect || '/login?registered=1';
                            }, 3000);
                        } else {
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat();
                                this.error = errorMessages.join(', ');
                            } else {
                                this.error = data.error || 'Registration failed. Please try again.';
                            }
                        }
                    } catch (error) {
                        this.error = 'An error occurred. Please try again.';
                        console.error('Registration error:', error);
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
