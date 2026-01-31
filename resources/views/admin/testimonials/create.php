<?php $base = rtrim($_ENV['APP_URL'] ?? '', '/'); ?>
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold mb-6">Add Testimonial</h1>
    <?php if (!empty($error)): ?>
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="<?= $base ?>/admin/testimonials/store" method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
            <label class="block text-sm font-semibold mb-1">Type</label>
            <select name="testimonial_type" class="w-full px-3 py-2 border rounded-lg">
                <option value="client">Client</option>
                <option value="candidate">Candidate</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Title</label>
            <input type="text" name="title" class="w-full px-3 py-2 border rounded-lg" placeholder="Short headline" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Name</label>
                <input type="text" name="name" class="w-full px-3 py-2 border rounded-lg" required />
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Designation</label>
                <input type="text" name="designation" class="w-full px-3 py-2 border rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Company</label>
                <input type="text" name="company" class="w-full px-3 py-2 border rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Profile Image</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border rounded-lg" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Text Testimonial</label>
            <textarea name="message" rows="4" class="w-full px-3 py-2 border rounded-lg"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Video URL (YouTube or MP4)</label>
            <input type="url" name="video_url" class="w-full px-3 py-2 border rounded-lg" />
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Upload Video (MP4/WebM/Ogg)</label>
            <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg" class="w-full px-3 py-2 border rounded-lg" />
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded">
            <label for="is_active" class="text-sm">Active</label>
        </div>
        <div class="pt-2">
            <button class="px-4 py-2 rounded-lg bg-[#0EA57A] text-white font-semibold">Save</button>
            <a href="<?= $base ?>/admin/testimonials" class="ml-2 px-4 py-2 rounded-lg border">Cancel</a>
        </div>
    </form>
 </div>
