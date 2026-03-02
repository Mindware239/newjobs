<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Bulk Uploader</h1>
    <?php if (!empty($error)): ?>
        <div class="mb-4 text-sm text-red-600"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-6">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="type" class="w-full px-4 py-2 border rounded-lg" required>
                <option value="College">College</option>
                <option value="HR">HR</option>
                <option value="Consultancy">Consultancy</option>
                <option value="Institution">Institution</option>
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Limit</label>
                <input type="number" name="limit_total" min="1" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input type="date" name="expires_at" class="w-full px-4 py-2 border rounded-lg">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border rounded-lg">
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Create</button>
        </div>
    </form>
</div>
