<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Job</h1>
            <p class="mt-2 text-sm text-gray-600">Update job posting details</p>
        </div>
        <a href="/admin/jobs" class="text-primary hover:text-primary">Back to List</a>
    </div>

    <form action="/admin/jobs/update/<?= htmlspecialchars($job['slug']) ?>" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6 space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Job Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($job['company_name'] ?? '') ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Company Logo</label>
                <?php if (!empty($job['company_logo'])): ?>
                    <div class="mb-2">
                        <img src="<?= htmlspecialchars($job['company_logo']) ?>" alt="Logo" class="h-12 w-auto">
                    </div>
                <?php endif; ?>
                <input type="file" name="company_logo" accept="image/png, image/jpeg, image/jpg" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-50">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <?php 
                    $locs = json_decode($job['locations'] ?? '[]', true);
                    $locStr = !empty($locs) ? $locs[0]['city'] : '';
                ?>
                <input type="text" name="location" value="<?= htmlspecialchars($locStr) ?>" required placeholder="City, Country" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($job['category'] === $cat['name']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Experience (Years)</label>
                    <input type="number" name="min_experience" value="<?= htmlspecialchars($job['min_experience'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Experience (Years)</label>
                    <input type="number" name="max_experience" value="<?= htmlspecialchars($job['max_experience'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Salary (Monthly)</label>
                    <input type="number" name="salary_min" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 20000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Salary (Monthly)</label>
                    <input type="number" name="salary_max" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. 50000">
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                <textarea name="description" id="editor" rows="10" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"><?= htmlspecialchars($job['description']) ?></textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="job_type" value="internal" <?= ($job['job_type'] === 'internal' || empty($job['job_type'])) ? 'checked' : '' ?> class="text-primary" onchange="toggleApplyLink(false)">
                        <span class="ml-2">Internal Job (Apply on Portal)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="job_type" value="external" <?= ($job['job_type'] === 'external') ? 'checked' : '' ?> class="text-primary" onchange="toggleApplyLink(true)">
                        <span class="ml-2">External Job (Redirect to Company Site)</span>
                    </label>
                </div>
            </div>

            <div id="apply_link_container" class="col-span-2 <?= ($job['job_type'] === 'external') ? '' : 'hidden' ?>">
                <label class="block text-sm font-medium text-gray-700">External Apply Link</label>
                <input type="url" name="apply_link" id="apply_link" value="<?= htmlspecialchars($job['apply_link'] ?? '') ?>" <?= ($job['job_type'] === 'external') ? 'required' : '' ?> placeholder="https://company.com/careers/job-123" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <p class="mt-1 text-xs text-gray-500">Must be a valid URL starting with http:// or https://</p>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-200 flex space-x-4">
            <button type="submit" class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Update Job Post
            </button>
            <a href="/admin/jobs/delete/<?= htmlspecialchars($job['slug']) ?>" onclick="return confirm('Are you sure you want to delete this job?')" class="flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Delete Job
            </a>
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
        // Do not clear value on toggle in edit mode, unless desired
    }
}
</script>











