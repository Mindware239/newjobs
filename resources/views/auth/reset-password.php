<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Reset Password - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="resetPasswordForm()" x-cloak>
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="/" class="text-2xl font-bold text-blue-600">Mindware Infotech</a>
                    <?php $isAdminPath = (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0); ?>
                    <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="text-gray-600 hover:text-gray-900">Back to Login</a>
                </div>
            </div>
        </nav>

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Reset Password</h2>
                    <p class="text-center text-gray-600 mb-6 text-sm">
                        Enter your new password below.
                    </p>
                    
                    <div x-show="success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-sm text-green-800" x-text="successMessage"></p>
                        <div class="mt-3">
                            <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="text-sm text-blue-600 hover:underline">Go to Login</a>
                        </div>
                    </div>

                    <div x-show="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-800" x-text="error"></p>
                    </div>

                    <form @submit.prevent="submitReset" class="space-y-6" x-show="!success">
                        <input type="hidden" x-model="formData.token">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>
                            <input type="password" 
                                   x-model="formData.password" 
                                   required
                                   minlength="8"
                                   placeholder="Enter new password (min 8 characters)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password
                            </label>
                            <input type="password" 
                                   x-model="formData.password_confirm" 
                                   required
                                   minlength="8"
                                   placeholder="Confirm new password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!isSubmitting">Reset Password</span>
                            <span x-show="isSubmitting">Resetting...</span>
                        </button>

                        <div class="text-center">
                            <a href="<?= $isAdminPath ? '/admin/login' : '/login' ?>" class="text-sm text-blue-600 hover:underline">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetPasswordForm() {
            // Get token from URL
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token') || '<?= htmlspecialchars($token ?? '') ?>';
            
            return {
                isSubmitting: false,
                error: '<?= htmlspecialchars($error ?? '') ?>',
                success: false,
                successMessage: '',
                formData: {
                    token: token,
                    password: '',
                    password_confirm: ''
                },
                async submitReset() {
                    this.isSubmitting = true;
                    this.error = '';
                    
                    if (!this.formData.token) {
                        this.error = 'Invalid reset token';
                        this.isSubmitting = false;
                        return;
                    }

                    if (this.formData.password.length < 8) {
                        this.error = 'Password must be at least 8 characters long';
                        this.isSubmitting = false;
                        return;
                    }

                    if (this.formData.password !== this.formData.password_confirm) {
                        this.error = 'Passwords do not match';
                        this.isSubmitting = false;
                        return;
                    }

                    try {
                        const response = await fetch('/reset-password', {
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
                            this.successMessage = data.message || 'Password reset successfully! You can now login with your new password.';
                            // Redirect to login after 3 seconds
                            setTimeout(() => {
                                window.location.href = '/login';
                            }, 3000);
                        } else {
                            this.error = data.error || 'Failed to reset password';
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

