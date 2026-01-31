<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Login' ?> - Mindware InfoTech</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .captcha-image {
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center" x-data="loginForm()">
    <div class="w-full max-w-6xl mx-auto bg-white shadow-2xl rounded-lg overflow-hidden" style="min-height: 600px;">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Panel - Promotional -->
            <div class="lg:w-1/2 bg-gradient-to-br from-blue-600 to-blue-800 p-12 flex flex-col justify-between text-white relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                </div>
                
                <div class="relative z-10">
                    <!-- Logo -->
                    <div class="flex items-center mb-8">
                        <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-3xl font-bold text-blue-600">M</span>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-3xl font-bold">Mindware InfoTech</h1>
                            <p class="text-blue-200 text-sm">Connecting Talent with <span class="text-blue-200">Opportunities</span>
                            </p>
                        </div>
                    </div>

                    <!-- Tagline -->
                    <div class="mb-12">
                        <h2 class="text-4xl font-bold mb-2">Find Your Dream Job at Your Fingertips</h2>
                    </div>

                    <!-- Platform Card -->
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-8 mb-8 border border-white border-opacity-30">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold">Job Portal Platform</h3>
                            <p class="text-blue-100 text-sm mt-2">Complete recruitment solution</p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="relative z-10 flex space-x-8">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">Easy Management</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">Secure Platform</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Login Form -->
            <div class="lg:w-1/2 p-12 flex flex-col justify-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Sign in</h2>
                    <p class="text-gray-600 mb-8">Welcome back to Admin Login</p>

                    <?php if (isset($error) && $error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="/admin/login" class="space-y-6" @submit.prevent="submitForm()">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect ?? '/admin/dashboard') ?>">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Your email</label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   required 
                                   x-model="formData.email"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="email@address.com">
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input id="password" 
                                       name="password" 
                                       :type="showPassword ? 'text' : 'password'" 
                                       required 
                                       x-model="formData.password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition pr-12"
                                       placeholder="Minimum 8 characters required">
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Captcha Field -->
                        <div>
                            <label for="captcha" class="block text-sm font-medium text-gray-700 mb-2">Captcha Code</label>
                            <div class="flex items-center space-x-3 mb-2">
                                <img id="captcha-image" 
                                     src="/admin/captcha/generate" 
                                     alt="CAPTCHA" 
                                     class="captcha-image h-12"
                                     @click="refreshCaptcha()"
                                     style="cursor: pointer;">
                                <button type="button" 
                                        @click="refreshCaptcha()"
                                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                            <input id="captcha" 
                                   name="captcha_code" 
                                   type="text" 
                                   required 
                                   x-model="formData.captcha"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="Enter the code above"
                                   maxlength="6"
                                   autocomplete="off">
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input id="remember" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>

                        <!-- Forgot Password -->
                        <div class="text-right">
                            <a href="/admin/forgot-password" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Forgot password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Sign in</span>
                            <span x-show="isSubmitting" x-cloak>Signing in...</span>
                        </button>
                    </form>

                    <!-- Footer Link -->
                    <div class="mt-6 text-center">
                        <a href="/" class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function loginForm() {
            return {
                showPassword: false,
                isSubmitting: false,
                formData: {
                    email: '',
                    password: '',
                    captcha: ''
                },
                refreshCaptcha() {
                    const img = document.getElementById('captcha-image');
                    if (img) {
                        img.src = '/admin/captcha/generate?' + new Date().getTime();
                    }
                },
                submitForm() {
                    this.isSubmitting = true;
                    const form = document.querySelector('form');
                    if (form) {
                        form.submit();
                    }
                }
            }
        }
    </script>
</body>
</html>
