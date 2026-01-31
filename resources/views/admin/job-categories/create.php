<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create Job Category</h1>
        <p class="mt-2 text-sm text-gray-600">Add a new job category/industry</p>
    </div>

    <?php if (isset($error)): ?>
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="/admin/job-categories" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., IT / Software, Manufacturing, etc.">
                    <p class="mt-1 text-xs text-gray-500">This will be displayed to employers when posting jobs</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Brief description of this category"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Category Image/Icon
                    </label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Upload an image or icon for this category (recommended: 200x200px or square images)</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            Sort Order
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="<?= htmlspecialchars($formData['sort_order'] ?? '0') ?>"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_active" value="1" checked class="mr-2">
                                <span>Active</span>
                            </label>
                            <label class="inline-flex items-center ml-6">
                                <input type="radio" name="is_active" value="0" class="mr-2">
                                <span>Inactive</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4 border-t">
                    <a href="/admin/job-categories" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Create Category
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

