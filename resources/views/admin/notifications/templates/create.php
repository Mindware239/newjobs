<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create Notification Template</h1>
        <a href="/admin/notification-templates" class="text-blue-600 hover:text-blue-800">&larr; Back to Templates</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="/admin/notification-templates" class="space-y-6">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Key</label>
                    <input type="text" name="event_key" required placeholder="e.g. job_posted_admin"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <p class="mt-1 text-xs text-gray-500">Unique identifier for the event trigger.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                    <select name="channel" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="email">Email</option>
                        <option value="in_app">In-App</option>
                        <option value="sms">SMS</option>
                        <option value="push">Push</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject / Title</label>
                <input type="text" name="subject" placeholder="Email Subject or Notification Title"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Content / Body</label>
                <textarea name="content" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Use {{variable}} for dynamic content"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Available Variables (Comma separated)</label>
                <input type="text" name="variables" placeholder="e.g. name, job_title, company"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>
