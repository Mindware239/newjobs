<?php 
/**
 * @var string $title
 * @var \App\Models\User $user
 * @var array $notificationPrefs
 * @var \App\Models\Candidate|null $candidate
 */
?>
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="candidateSettings()">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
        <p class="text-gray-600">Manage your preferences and security settings</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Settings Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-20">
                <nav class="space-y-1">
                    <button @click="activeTab = 'account'" 
                            :class="activeTab === 'account' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Account
                    </button>
                    <button @click="activeTab = 'notifications'" 
                            :class="activeTab === 'notifications' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notifications
                    </button>
                    <button @click="activeTab = 'security'" 
                            :class="activeTab === 'security' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Security
                    </button>
                </nav>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="lg:col-span-3 space-y-6">

            <div x-show="activeTab === 'account'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Account</h2>
                    <p class="text-gray-600 mt-1">Manage your email and mobile verification</p>
                </div>

                <form @submit.prevent="saveAccount" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email"
                               x-model="accountData.email"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Mobile Number</label>
                        <input type="tel"
                               x-model="accountData.phone"
                               placeholder="+91 9876543210"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <div class="flex items-center gap-3 mt-3">
                            <span class="text-sm" :class="accountData.phoneVerified ? 'text-green-600 font-medium' : 'text-yellow-600 font-medium'">
                                <span x-text="accountData.phoneVerified ? 'Phone verified' : 'Phone not verified'"></span>
                            </span>
                            <button type="button"
                                    @click="sendPhoneOtp"
                                    :disabled="isSubmitting || !accountData.phone"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 disabled:opacity-50">
                                Send OTP
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Mobile Number</label>
                        <input type="tel"
                               x-model="accountData.additional_mobile"
                               placeholder="Optional second number"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Verify OTP</label>
                        <div class="flex gap-3">
                            <input type="text"
                                   x-model="phoneVerification.otp"
                                   maxlength="6"
                                   placeholder="Enter OTP"
                                   class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <button type="button"
                                    @click="verifyPhoneOtp"
                                    :disabled="isSubmitting || !phoneVerification.otp"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-green-50 text-green-700 hover:bg-green-100 disabled:opacity-50">
                                Verify
                            </button>
                        </div>
                        <p x-show="phoneVerification.otpPreview" class="text-xs text-blue-600 mt-2">
                            Test OTP: <span x-text="phoneVerification.otpPreview"></span>
                        </p>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-indigo-800 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Save Account</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Notification Settings -->
            <div x-show="activeTab === 'notifications'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Notification Preferences</h2>
                    <p class="text-gray-600 mt-1">Choose how and when you want to be notified</p>
                </div>

                <form @submit.prevent="saveSettings" class="space-y-6">
                    <template x-for="(channels, category) in notificationData" :key="category">
                        <div class="mb-6 border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <h3 class="text-lg font-medium text-gray-900 capitalize mb-4" x-text="category.replace(/_/g, ' ')"></h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <template x-for="(enabled, channel) in channels" :key="channel">
                                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" x-model="notificationData[category][channel]" class="form-checkbox h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700 capitalize" x-text="channel == 'sms' ? 'SMS' : channel.replace(/_/g, ' ')"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-indigo-800 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Save Preferences</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Settings -->
            <div x-show="activeTab === 'security'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Security</h2>
                    <p class="text-gray-600 mt-1">Update your password</p>
                </div>

                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                        <input type="password" 
                               x-model="passwordData.current_password"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input type="password" 
                               x-model="passwordData.new_password"
                               minlength="8"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" 
                               x-model="passwordData.confirm_password"
                               minlength="8"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
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
    </div>
</div>

<script>
function candidateSettings() {
    return {
        activeTab: 'account',
        isSubmitting: false,
        notificationData: <?= json_encode($notificationPrefs) ?>,
        accountData: {
            email: '<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>',
            phone: '<?= htmlspecialchars($user->phone ?? (($candidate->attributes['mobile'] ?? '') ?? ''), ENT_QUOTES) ?>',
            additional_mobile: '<?= htmlspecialchars((($notificationPrefs['contact']['additional_mobile'] ?? '') ?: ((json_decode((string)($candidate->attributes['preferences_data'] ?? '{}'), true)['contact']['additional_mobile'] ?? ''))), ENT_QUOTES) ?>',
            phoneVerified: <?= (($user->is_phone_verified ?? 0) == 1) ? 'true' : 'false' ?>
        },
        phoneVerification: {
            otp: '',
            otpPreview: ''
        },
        passwordData: {
            current_password: '',
            new_password: '',
            confirm_password: ''
        },
        async saveSettings() {
            this.isSubmitting = true;
            try {
                // Prepare payload
                const payload = {
                    notification_pref: this.notificationData
                };
                
                if (this.passwordData.new_password) {
                    payload.current_password = this.passwordData.current_password;
                    payload.new_password = this.passwordData.new_password;
                    payload.confirm_password = this.passwordData.confirm_password;
                }

                const response = await fetch('/candidate/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Settings updated successfully');
                    // Reset password fields
                    this.passwordData = {
                        current_password: '',
                        new_password: '',
                        confirm_password: ''
                    };
                } else {
                    alert(data.error || 'Failed to update settings');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async saveAccount() {
            this.isSubmitting = true;
            try {
                const payload = {
                    email: this.accountData.email,
                    phone: this.accountData.phone,
                    additional_mobile: this.accountData.additional_mobile
                };
                const response = await fetch('/candidate/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.success) {
                    this.accountData.phoneVerified = false;
                    alert('Account updated successfully');
                } else {
                    alert(data.error || 'Failed to update account');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async sendPhoneOtp() {
            if (!this.accountData.phone) return;
            this.isSubmitting = true;
            try {
                const response = await fetch('/candidate/settings/phone/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ phone: this.accountData.phone })
                });
                const data = await response.json();
                if (data.success) {
                    this.phoneVerification.otpPreview = data.otp_preview || '';
                    alert(data.message || 'OTP sent successfully');
                } else {
                    alert(data.error || 'Failed to send OTP');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async verifyPhoneOtp() {
            if (!this.phoneVerification.otp) return;
            this.isSubmitting = true;
            try {
                const response = await fetch('/candidate/settings/phone/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ otp: this.phoneVerification.otp })
                });
                const data = await response.json();
                if (data.success) {
                    this.accountData.phoneVerified = true;
                    this.phoneVerification.otp = '';
                    this.phoneVerification.otpPreview = '';
                    alert(data.message || 'Phone verified successfully');
                } else {
                    alert(data.error || 'Invalid OTP');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        
        async disablePush() {
            try {
                if (!window.firebase) return;
                const messaging = window.firebase.messaging();
                const token = await messaging.getToken();
                if (token) {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/api/push/unsubscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                        body: JSON.stringify({ token })
                    });
                    try { await messaging.deleteToken(token); } catch (_) {}
                    alert('Browser push disabled');
                }
            } catch (e) {}
        }
    }
}
</script>
