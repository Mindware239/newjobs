<?php
$title = 'Edit Notification Template';
$layout = 'admin/layout';
?>

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Template</h1>
        <a href="/admin/notification-templates" class="text-gray-600 hover:text-gray-900">Back to List</a>
    </div>

    <form method="POST" action="/admin/notification-templates/<?= $template['id'] ?>" class="space-y-6">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event Key</label>
                <input type="text" name="event_key" required 
                       value="<?= htmlspecialchars($template['event_key']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <p class="mt-1 text-xs text-gray-500">Unique identifier for the event trigger.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                <select name="channel" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <?php
                    $channels = ['email', 'in_app', 'sms', 'push', 'whatsapp'];
                    foreach ($channels as $ch):
                    ?>
                        <option value="<?= $ch ?>" <?= $template['channel'] === $ch ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', '-', $ch)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject / Title</label>
            <input type="text" name="subject" 
                   value="<?= htmlspecialchars($template['subject'] ?? '') ?>"
                   placeholder="Email Subject or Notification Title"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Content / Body</label>
            <textarea name="content" rows="6" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-md" 
                      placeholder="Use {{variable}} for dynamic content"><?= htmlspecialchars($template['content'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Available Variables (Comma separated)</label>
            <input type="text" name="variables" 
                   value="<?= htmlspecialchars($template['variables'] ?? '') ?>"
                   placeholder="e.g. name, job_title, company"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" <?= $template['is_active'] ? 'checked' : '' ?>>
                <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Update Template
            </button>
        </div>
    </form>
</div>
