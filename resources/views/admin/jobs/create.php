<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Job</h1>
            <p class="mt-2 text-sm text-gray-600">Post a new job on behalf of the portal</p>
        </div>
        <a href="/admin/jobs" class="text-primary hover:text-primary">Back to List</a>
    </div>

    <form action="/admin/jobs/store" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6 space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Job Title</label>
                <input type="text" name="title" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                <input type="text" name="company_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Company Logo</label>
                <input type="file" name="company_logo" accept="image/png, image/jpeg, image/jpg" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-50">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" required placeholder="City, Country" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Experience (Years)</label>
                    <input type="number" name="min_experience" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Experience (Years)</label>
                    <input type="number" name="max_experience" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Salary (Monthly)</label>
                    <input type="number" name="salary_min" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 20000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Salary (Monthly)</label>
                    <input type="number" name="salary_max" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 50000">
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                <textarea name="description" id="editor" rows="10" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="job_type" value="internal" checked class="text-primary" onchange="toggleApplyLink(false)">
                        <span class="ml-2">Internal Job (Apply on Portal)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="job_type" value="external" class="text-primary" onchange="toggleApplyLink(true)">
                        <span class="ml-2">External Job (Redirect to Company Site)</span>
                    </label>
                </div>
            </div>

            <div id="apply_link_container" class="col-span-2 hidden">
                <label class="block text-sm font-medium text-gray-700">External Apply Link</label>
                <input type="url" name="apply_link" id="apply_link" placeholder="https://company.com/careers/job-123" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <p class="mt-1 text-xs text-gray-500">Must be a valid URL starting with http:// or https://</p>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-200">
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-900 border-primary bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Create Job Post
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
CKEDITOR.replace('editor', {
    height: 400,
    baseFloatZIndex: 10005,
    removeButtons: 'PasteFromWord'
});

function toggleApplyLink(show) {
    const container = document.getElementById('apply_link_container');
    const input = document.getElementById('apply_link');
    if (show) {
        container.classList.remove('hidden');
        input.required = true;
    } else {
        container.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
}
</script>











