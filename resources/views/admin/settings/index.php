<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-2 text-sm text-gray-600">Configure platform settings and preferences</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="/admin/settings" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Platform Name</label>
                <input type="text" name="platform_name" value="<?= htmlspecialchars($settings['platform_name'] ?? 'Job Portal') ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Mode</label>
                <label class="flex items-center">
                    <input type="checkbox" name="maintenance_mode" value="1" 
                           <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                    <span class="ml-2 text-sm text-gray-600">Enable maintenance mode (only admins can access)</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Global Notification Channels</label>
                <div class="space-y-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                    <p class="text-xs text-gray-500 mb-4">Master switches. If disabled here, no one receives these notifications regardless of user preferences.</p>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="notifications_email" value="1" 
                               <?= ($settings['notifications_email'] ?? 1) ? 'checked' : '' ?>>
                        <span class="ml-2 text-sm text-gray-700 font-medium">Email Notifications</span>
                        <span class="ml-2 text-xs text-gray-500">(SMTP)</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="notifications_push" value="1" 
                               <?= ($settings['notifications_push'] ?? 1) ? 'checked' : '' ?>>
                        <span class="ml-2 text-sm text-gray-700 font-medium">Push Notifications</span>
                        <span class="ml-2 text-xs text-gray-500">(Mobile/Web Push)</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="notifications_in_app" value="1" 
                               <?= ($settings['notifications_in_app'] ?? 1) ? 'checked' : '' ?>>
                        <span class="ml-2 text-sm text-gray-700 font-medium">In-App Notifications</span>
                        <span class="ml-2 text-xs text-gray-500">(Bell Icon)</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="notifications_whatsapp" value="1" 
                               <?= ($settings['notifications_whatsapp'] ?? 0) ? 'checked' : '' ?>>
                        <span class="ml-2 text-sm text-gray-700 font-medium">WhatsApp Notifications</span>
                        <span class="ml-2 text-xs text-gray-500">(Twilio/Meta)</span>
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

