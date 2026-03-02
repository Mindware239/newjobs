<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article | Career Insight</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-[#54595f]">
<?php include __DIR__ . '/header.php'; ?>
<div class="border-t border-gray-200">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-3
                flex items-center justify-between">

        <!-- LEFT SIDE -->
        <div class="flex items-center gap-6">

            <a href="/hiringInsight"
               class="text-sm text-[#5b6bd5] font-semibold hover:underline">
                ← Back to articles
            </a>

            <a href="<?= $prevUrl ?? '#' ?>"
               class="text-sm text-red-500 font-semibold hover:underline flex items-center gap-1">
                ← Previous post
            </a>

        </div>

        <!-- RIGHT SIDE -->
        <a href="<?= $nextUrl ?? '#' ?>"
           class="text-sm text-red-500 font-semibold hover:underline flex items-center gap-1">
            Next post →
        </a>

    </div>
</div>

<main class="max-w-[1340px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10">
    <div class="grid grid-rows-1 lg:grid-cols-12 gap-8">
    <article class="lg:col-span-8 bg-white border border-slate-200 overflow-hidden">
        <div class="p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">
                <?php echo htmlspecialchars($article['title'] ?? '') ?>
            </h1>
            <p class="text-sm font-semibold text-slate-700 mb-6">
                <?php echo htmlspecialchars($article['date'] ?? '') ?>
                <?php if (!empty($article['category'])): ?>
                    · <?php echo htmlspecialchars($article['category']) ?>
                <?php endif; ?>
                <?php if (!empty($article['author'])): ?>
                    · By <?php echo htmlspecialchars($article['author']) ?>
                <?php endif; ?>
            </p>
            <?php if (!empty($article['img'])): ?>
                <img src="<?php echo htmlspecialchars($article['img']) ?>" alt="Article image"
                     class="w-full h-auto rounded mb-8">
            <?php endif; ?>
            <?php if (!empty($article['short_description'])): ?>
                <div class="text-slate-800 leading-relaxed mb-6">
                    <?php echo $article['short_description'] ?>
                </div>
            <?php endif; ?>
            <div class="prose max-w-none">
                <?php echo $article['content'] ?? '' ?>
            </div>

            <div class="mt-10 border-t border-[#54595f] pt-4">
                <div class="flex items-center gap-4">
                    <span class="font-semibold text-gray-700 px-6 h-8 flex items-center justify-center">
                        Share this article
                    </span>
                    <ul class="flex gap-4 text-lg">
                        <li>
                            <a href="mailto:?subject=<?= urlencode($article['title'] ?? '') ?>&body=<?= urlencode($article['url'] ?? '') ?>"
                               class="text-red-500 hover:opacity-70">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($article['url'] ?? '') ?>"
                               target="_blank"
                               class="text-[#1877f2] hover:opacity-70">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($article['url'] ?? '') ?>&text=<?= urlencode($article['title'] ?? '') ?>"
                               target="_blank"
                               class="text-black hover:opacity-70">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($article['url'] ?? '') ?>"
                               target="_blank"
                               class="text-[#0a66c2] hover:opacity-70">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://pinterest.com/pin/create/button/?url=<?= urlencode($article['url'] ?? '') ?>"
                               target="_blank"
                               class="text-[#e60023] hover:opacity-70">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.reddit.com/submit?url=<?= urlencode($article['url'] ?? '') ?>"
                               target="_blank"
                               class="text-orange-500 hover:opacity-70">
                                <i class="fab fa-reddit-alien"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($related)): ?>
            <div class="mt-10 border-t border-[#54595f] pt-6">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Related articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($related as $r): ?>
                    <div class="bg-white border border-slate-200 overflow-hidden">
                        <?php if (!empty($r['img'])): ?>
                        <img src="<?= htmlspecialchars($r['img']) ?>" alt="" class="w-full h-40 object-cover">
                        <?php endif; ?>
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-slate-900 mb-2">
                                <a href="/hiringInsight/article?id=<?= (int)$r['id'] ?>"
                                   class="text-[#5b6bd5] font-bold hover:underline">
                                    <?= htmlspecialchars($r['title']) ?>
                                </a>
                            </h3>
                            <p class="text-sm font-semibold text-slate-700">
                                <?= htmlspecialchars($r['date']) ?>
                                <?php if (!empty($r['category'])): ?>
                                    · <?= htmlspecialchars($r['category']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
</article> 
    <aside class="lg:col-span-4">
        <div class="bg-white border border-slate-200 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Latest articles</h3>
                <ul class="space-y-3">
                    <?php foreach (($latest ?? []) as $la): ?>
                        <li class="pb-2 border-b border-[#eeeeee]">
                            <a href="/hiringInsight/article?id=<?= (int)$la['id'] ?>" class="text-[#e15f55] font-bold hover:underline">
                                <?= htmlspecialchars($la['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
