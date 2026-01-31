<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Forgot Password - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="forgotPasswordForm()" x-cloak>
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>

        <div class="min-h-screen grid grid-cols-1 md:grid-cols-2 gap-8 py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
                    <p class="text-gray-600 mb-6 text-sm">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                    
                    <div x-show="success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-sm text-green-800" x-text="successMessage"></p>
                        <div x-show="resetLink" class="mt-2">
                            <p class="text-xs text-green-700 mb-2">Development mode - Reset link:</p>
                            <a :href="resetLink" class="text-xs text-indigo-600 underline break-all" x-text="resetLink"></a>
                        </div>
                    </div>

                    <div x-show="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-800" x-text="error"></p>
                    </div>

                    <form @submit.prevent="submitRequest" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   x-model="formData.email" 
                                   required
                                   placeholder="your@email.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                            <span x-show="!isSubmitting">Send Reset Link</span>
                            <span x-show="isSubmitting">Sending...</span>
                        </button>

                        <div class="text-center">
                            <?php $isAdminPath = (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0); ?>
                            <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="text-sm text-indigo-600 hover:underline">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                <div class="px-6 md:px-12">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4 text-white">Reset Your Access Securely</h2>
                    <p class="text-indigo-200 mb-6 max-w-md">We’ll send you a secure, time‑limited link to reset your password.</p>
                    <ul class="space-y-3 text-indigo-200">
                        <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Encrypted reset links</li>
                        <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>Expires automatically</li>
                        <li class="flex items-center gap-3"><span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>No disruption to account</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function forgotPasswordForm() {
            return {
                isSubmitting: false,
                error: '',
                success: false,
                successMessage: '',
                resetLink: '',
                formData: {
                    email: ''
                },
                async submitRequest() {
                    this.isSubmitting = true;
                    this.error = '';
                    this.success = false;
                    
                    try {
                        const response = await fetch('/forgot-password', {
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
                            this.success = true;
                            this.successMessage = data.message;
                            if (data.reset_link) {
                                this.resetLink = data.reset_link;
                            }
                            // Optionally redirect after 3 seconds
                            setTimeout(() => {
                                if (data.reset_link) {
                                    window.location.href = data.reset_link;
                                }
                            }, 3000);
                        } else {
                            this.error = data.error || 'Failed to send reset link';
                        }
                    } catch (error) {
                        this.error = 'Error: ' + error.message;
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

