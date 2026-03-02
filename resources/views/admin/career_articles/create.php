<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold mb-4">Add Article</h1>
    <form method="post" action="/admin/career-articles/store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Category</label>
            <select name="category_id" class="border px-3 py-2 rounded w-full">
                <option value="">Select</option>
                <?php foreach (($categories ?? []) as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Title</label>
            <input type="text" name="title" class="border px-3 py-2 rounded w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Slug</label>
            <input type="text" name="slug" class="border px-3 py-2 rounded w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Short Description</label>
            <textarea name="short_description" class="border px-3 py-2 rounded w-full" rows="3"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Content</label>
            <textarea name="content" class="border px-3 py-2 rounded w-full" rows="10"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Image URL</label>
                <input type="text" name="image" class="border px-3 py-2 rounded w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Author</label>
                <input type="text" name="author" class="border px-3 py-2 rounded w-full">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Status</label>
                <select name="status" class="border px-3 py-2 rounded w-full">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Published At</label>
                <input type="datetime-local" name="published_at" class="border px-3 py-2 rounded w-full">
            </div>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
            <a href="/admin/career-articles" class="px-4 py-2 border rounded">Cancel</a>
        </div>
    </form>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>
<script>
$(function(){
  $('[name="short_description"]').summernote({height:150});
  $('[name="content"]').summernote({height:400});
});
</script>
