<?php
$job = $job ?? [];
$locations = $locations ?? [];
$skills = $skills ?? [];
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Job</h1>
        <a href="/employer/jobs/<?= $job['id'] ?? '' ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Cancel
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form id="edit-job-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Job Title *</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($job['title'] ?? '') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Job Type *</label>
                    <select name="job_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                        <option value="full_time" <?= ($job['job_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                        <option value="part_time" <?= ($job['job_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                        <option value="contract" <?= ($job['job_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
                        <option value="internship" <?= ($job['job_type'] ?? '') === 'internship' ? 'selected' : '' ?>>Internship</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea name="description" rows="6" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Experience Required</label>
                    <input type="text" name="experience_required" value="<?= htmlspecialchars($job['experience_required'] ?? '') ?>"
                           placeholder="e.g., 2-5 years"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min Salary</label>
                    <input type="number" name="salary_min" value="<?= $job['salary_min'] ?? '' ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Salary</label>
                    <input type="number" name="salary_max" value="<?= $job['salary_max'] ?? '' ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Openings</label>
                    <input type="number" name="openings" value="<?= $job['openings'] ?? 1 ?>" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500">
                    <option value="draft" <?= ($job['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($job['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="/employer/jobs/<?= $job['id'] ?? '' ?>" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 shadow-md">
                    Update Job
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('edit-job-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/employer/jobs/<?= $job['id'] ?? '' ?>', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            window.location.href = '/employer/jobs/<?= $job['id'] ?? '' ?>';
        } else {
            alert('Error: ' + (result.error || 'Failed to update job'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>

