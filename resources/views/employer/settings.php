<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var \App\Models\User $user
 * @var \App\Models\EmployerSetting $settings
 * @var array $notificationPrefs
 */
$timezone = $settings->attributes['timezone'] ?? 'Asia/Kolkata';
$notificationPrefs = $notificationPrefs ?? [];
?>
<link href="/css/output.css" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-6xl mx-auto" x-data="settingsPage()">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
        <p class="text-gray-600">Manage your account settings and preferences</p>
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
                    <button @click="activeTab = 'company'" 
                            :class="activeTab === 'company' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Company
                    </button>
                    <button @click="activeTab = 'notifications'" 
                            :class="activeTab === 'notifications' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notifications
                    </button>
                    <button @click="activeTab = 'preferences'" 
                            :class="activeTab === 'preferences' ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-lg border-2 font-medium transition-all duration-200 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Preferences
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
            <!-- Account Settings -->
            <div x-show="activeTab === 'account'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Account Settings</h2>
                        <p class="text-gray-600 mt-1">Manage your account information</p>
                    </div>
                </div>

                <form @submit.prevent="updateAccount" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" 
                               x-model="accountData.email"
                               required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <p class="text-xs text-gray-500 mt-1">We'll send important updates to this email</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" 
                               x-model="accountData.phone"
                               placeholder="+91 1234567890"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"> 
                        <p class="text-xs text-gray-500 mt-1">Optional - for important notifications</p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Account Status</p>
                            <p class="text-sm text-gray-500">
                                <span x-show="accountData.emailVerified" class="text-green-600 font-medium">✓ Verified</span>
                                <span x-show="!accountData.emailVerified" class="text-yellow-600 font-medium">⚠ Not Verified</span>
                            </p>
                        </div>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-indigo-800 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Save Changes</span>
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

            <!-- Company Information -->
            <div x-show="activeTab === 'company'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Company Information</h2>
                        <p class="text-gray-600 mt-1">Update your company details</p>
                    </div>
                </div>

                <form @submit.prevent="updateCompany" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Company Name *</label>
                            <input type="text" 
                                   x-model="companyData.company_name"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Website</label>
                            <input type="url" 
                                   x-model="companyData.website"
                                   placeholder="https://example.com"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Company Description</label>
                        <textarea x-model="companyData.description"
                                  rows="4"
                                  placeholder="Tell us about your company..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Industry</label>
                            <input type="text" 
                                   x-model="companyData.industry"
                                   placeholder="e.g. IT/Software, Healthcare, Finance"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Enter your company's industry</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Company Size</label>
                            <select x-model="companyData.company_size"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                <option value="">Select Size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="501-1000">501-1000 employees</option>
                                <option value="1000+">1000+ employees</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm hover:shadow-md">
                                
                            <span x-show="!isSubmitting">Save Changes</span>
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

                <form @submit.prevent="updateNotifications" class="space-y-6">
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

            <!-- Preferences -->
            <div x-show="activeTab === 'preferences'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Preferences</h2>
                    <p class="text-gray-600 mt-1">Customize your experience</p>
                </div>

                <form @submit.prevent="updatePreferences" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label>
                        <select x-model="preferencesData.timezone"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                            <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                            <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                            <option value="Europe/Paris">Europe/Paris (CET)</option>
                            <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                            <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">This affects how dates and times are displayed</p>
                    </div>

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

            <!-- Security -->
            <div x-show="activeTab === 'security'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Security</h2>
                    <p class="text-gray-600 mt-1">Manage your password and security settings</p>
                </div>

                <form @submit.prevent="updatePassword" class="space-y-6">
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
                               minlength="8"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-indigo-700">
                                    <strong>Password Tips:</strong> Use a combination of letters, numbers, and special characters for better security.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-lg hover:from-red-700 hover:to-red-800 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
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
function settingsPage() {
    return {
        activeTab: 'account',
        isSubmitting: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        accountData: {
            email: '<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>',
            phone: '<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES) ?>',
            emailVerified: <?= ($user->is_email_verified ?? 0) ? 'true' : 'false' ?>
        },
        companyData: {
            company_name: '<?= htmlspecialchars($employer->company_name ?? '', ENT_QUOTES) ?>',
            website: '<?= htmlspecialchars($employer->website ?? '', ENT_QUOTES) ?>',
            description: <?= json_encode($employer->description ?? '') ?>,
            industry: '<?= htmlspecialchars($employer->industry ?? '', ENT_QUOTES) ?>',
            company_size: '<?= htmlspecialchars($employer->size ?? '', ENT_QUOTES) ?>'
        },
        notificationData: <?= json_encode($notificationPrefs) ?>,
        preferencesData: {
            timezone: '<?= htmlspecialchars($timezone, ENT_QUOTES) ?>'
        },
        passwordData: {
            current_password: '',
            new_password: '',
            confirm_password: ''
        },
        async updateAccount() {
            this.isSubmitting = true;
            try {
                const response = await fetch('/employer/settings/account', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.accountData)
                });
                const data = await response.json();
                if (data.success) {
                    this.showSuccess('Account updated successfully');
                } else {
                    this.showError(data.error || 'Failed to update account');
                }
            } catch (error) {
                this.showError('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async updateCompany() {
            this.isSubmitting = true;
            try {
                const response = await fetch('/employer/settings/company', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.companyData)
                });
                const data = await response.json();
                if (data.success) {
                    this.showSuccess('Company information updated successfully');
                } else {
                    this.showError(data.error || 'Failed to update company information');
                }
            } catch (error) {
                this.showError('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async updateNotifications() {
            this.isSubmitting = true;
            try {
                const response = await fetch('/employer/settings/preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        timezone: this.preferencesData.timezone,
                        notification_pref: this.notificationData
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.showSuccess('Notification preferences updated successfully');
                } else {
                    this.showError(data.error || 'Failed to update preferences');
                }
            } catch (error) {
                this.showError('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async updatePreferences() {
            this.isSubmitting = true;
            try {
                const response = await fetch('/employer/settings/preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        timezone: this.preferencesData.timezone,
                        notification_pref: this.notificationData
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.showSuccess('Preferences updated successfully');
                } else {
                    this.showError(data.error || 'Failed to update preferences');
                }
            } catch (error) {
                this.showError('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async updatePassword() {
            if (this.passwordData.new_password !== this.passwordData.confirm_password) {
                this.showError('New passwords do not match');
                return;
            }
            if (this.passwordData.new_password.length < 8) {
                this.showError('Password must be at least 8 characters long');
                return;
            }
            this.isSubmitting = true;
            try {
                const response = await fetch('/employer/settings/password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.passwordData)
                });
                const data = await response.json();
                if (data.success) {
                    this.showSuccess('Password updated successfully');
                    this.passwordData = { current_password: '', new_password: '', confirm_password: '' };
                } else {
                    this.showError(data.error || 'Failed to update password');
                }
            } catch (error) {
                this.showError('An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        async disablePush() {
            try {
                if (!window.firebase) return;
                const messaging = window.firebase.messaging();
                const token = await messaging.getToken(); // might fail if no token
                
                if (token) {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/api/push/unsubscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                        body: JSON.stringify({ token })
                    });
                    try { await messaging.deleteToken(token); } catch (_) {}
                    alert('Browser push disabled');
                } else {
                    alert('No active push token found');
                }
            } catch (e) {
                console.error('Error disabling push:', e);
            }
        },
        showSuccess(message) {
            // Create and show success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            notification.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        },
        showError(message) {
            // Create and show error notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            notification.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
