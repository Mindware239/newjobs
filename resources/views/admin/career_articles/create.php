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
            <input type="text" name="title" class="border px-3 py-2 rounded w-full" placeholder="Clear headline that describes the article">
        </div>
        <div>
            <label class="block text-sm font-medium">Slug</label>
            <input type="text" name="slug" class="border px-3 py-2 rounded w-full" placeholder="example-article-slug">
        </div>
        <div>
            <label class="block text-sm font-medium">Short Description</label>
            <textarea name="short_description" class="border px-3 py-2 rounded w-full" rows="3" placeholder="Teaser (1–2 sentences) to summarize the article"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Content</label>
            <textarea id="ca-content" name="content" class="border px-3 py-2 rounded w-full" rows="10"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Image URL</label>
                <input type="text" name="image" class="border px-3 py-2 rounded w-full" placeholder="https://example.com/image.jpg">
            </div>
            <div>
                <label class="block text-sm font-medium">Author</label>
                <input type="text" name="author" class="border px-3 py-2 rounded w-full" placeholder="Author name">
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
<script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
<script>
if (window.CKEDITOR) {
  CKEDITOR.replace('short_description', {
    allowedContent: true,
    extraPlugins: 'pastefromword,justify,format,table,tabletools,tableselection,clipboard',
    height: 180,
    toolbar: [
      { name: 'basicstyles', items: ['Bold','Italic','Underline','RemoveFormat'] },
      { name: 'paragraph', items: ['NumberedList','BulletedList','Outdent','Indent','Blockquote','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
      { name: 'links', items: ['Link','Unlink'] },
      { name: 'insert', items: ['HorizontalRule'] },
      { name: 'document', items: ['Maximize'] }
    ]
  });
  CKEDITOR.replace('ca-content', {
    allowedContent: true,
    extraPlugins: 'pastefromword,justify,format,table,tabletools,tableselection,clipboard',
    height: 380,
    toolbar: [
      { name: 'document', items: ['Source','Maximize'] },
      { name: 'clipboard', items: ['Undo','Redo','Cut','Copy','Paste','PasteText','PasteFromWord'] },
      { name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','RemoveFormat'] },
      { name: 'paragraph', items: ['NumberedList','BulletedList','Outdent','Indent','Blockquote','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
      { name: 'links', items: ['Link','Unlink'] },
      { name: 'insert', items: ['Table','HorizontalRule','SpecialChar'] },
      { name: 'styles', items: ['Styles','Format'] }
    ]
  });
}
</script>
