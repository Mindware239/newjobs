<div class="p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($article['title'] ?? '') ?></h1>
        <a href="/admin/career-articles" class="px-4 py-2 border rounded">Back</a>
    </div>
    <p class="text-sm text-slate-600 mb-2"><?= htmlspecialchars($article['category_name'] ?? '') ?></p>
    <p class="text-sm text-slate-600 mb-6"><?= htmlspecialchars($article['published_at'] ?? '') ?></p>
    <?php if (!empty($article['image'])): ?>
        <img src="<?= htmlspecialchars($article['image']) ?>" class="w-full h-auto rounded mb-6" alt="">
    <?php endif; ?>
    <div class="prose max-w-none">
        <div class="text-slate-800 leading-relaxed"><?= $article['short_description'] ?? '' ?></div>
        <div class="mt-6 text-slate-800 leading-relaxed"><?= $article['content'] ?? '' ?></div>
    </div>
</div>
