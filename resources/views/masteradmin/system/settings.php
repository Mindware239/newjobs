<?php
/**
 * @var string $title
 * @var array $settings
 */
?>
<div>
  <h1 class="text-2xl font-semibold mb-4"><?= $title ?></h1>
  
  <div class="bg-white rounded shadow p-6">
    <form method="POST" action="/master/system/settings/save">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      
      <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Email Settings</h2>
        
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2">Email Footer Text</label>
          <p class="text-sm text-gray-500 mb-2">This text will appear at the bottom of all system emails. HTML is allowed.</p>
          <textarea name="settings[email_footer]" rows="4" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="&copy; 2024 Mindware Infotech..."><?= htmlspecialchars($settings['email_footer'] ?? '') ?></textarea>
        </div>
      </div>
      
      <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">General Settings</h2>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Support Email</label>
            <input type="email" name="settings[support_email]" 
                   value="<?= htmlspecialchars($settings['support_email'] ?? '') ?>"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Support Phone</label>
            <input type="text" name="settings[support_phone]" 
                   value="<?= htmlspecialchars($settings['support_phone'] ?? '') ?>"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 transition">
          Save Settings
        </button>
      </div>
    </form>
  </div>
</div>
