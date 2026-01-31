<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Change Password - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <?php require __DIR__ . '/../include/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="changePassword()">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Change Password</h2>
                    <p class="text-gray-600 mt-1">Update your account password</p>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div x-show="message" x-transition x-cloak class="mb-6 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                <p x-text="message"></p>
            </div>

            <form @submit.prevent="updatePassword" class="space-y-6 max-w-lg">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                    <input type="password" 
                           x-model="passwordData.current_password"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" 
                           x-model="passwordData.new_password"
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" 
                           x-model="passwordData.confirm_password"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-indigo-800 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isSubmitting">Update Password</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function changePassword() {
            return {
                passwordData: {
                    current_password: '',
                    new_password: '',
                    confirm_password: ''
                },
                isSubmitting: false,
                message: '',
                messageType: '',

                async updatePassword() {
                    if (this.passwordData.new_password !== this.passwordData.confirm_password) {
                        this.message = 'New passwords do not match';
                        this.messageType = 'error';
                        return;
                    }

                    this.isSubmitting = true;
                    this.message = '';

                    try {
                        const response = await fetch('/candidate/update-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.passwordData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message;
                            this.messageType = 'success';
                            this.passwordData = {
                                current_password: '',
                                new_password: '',
                                confirm_password: ''
                            };
                        } else {
                            this.message = data.error || 'Failed to update password';
                            this.messageType = 'error';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.message = 'An error occurred while updating password';
                        this.messageType = 'error';
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>