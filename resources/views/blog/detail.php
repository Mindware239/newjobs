<?php
/** @var array|null $blog */
/** @var string $content */
/** @var array $toc */
/** @var array $categories */
/** @var array $tags */
/** @var array $meta */
?>
<?php if (!$blog): ?>
  <div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold mb-4">Post not found</h1>
    <a class="text-blue-600 hover:underline" href="/blog">Back to Blog</a>
  </div>
  <?php return; ?>
<?php endif; ?>
<div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
  <article class="md:col-span-2">
    <h1 class="text-3xl font-bold"><?= htmlspecialchars($blog['title'] ?? '') ?></h1>
    <?php if (!empty($blog['featured_image'])): ?>
      <img class="w-full h-auto rounded mt-4" src="<?= htmlspecialchars($blog['featured_image']) ?>" alt="<?= htmlspecialchars($blog['title'] ?? '') ?>">
    <?php endif; ?>
    <div class="text-sm text-gray-600 mt-2">
      <?= htmlspecialchars($blog['published_at'] ?? '') ?>
    </div>
    <div class="mt-4 flex items-center gap-3">
      <?php $shareUrl = htmlspecialchars($meta['canonical'] ?? ('/blog/' . ($blog['slug'] ?? ''))); ?>
      <?php $shareText = htmlspecialchars($blog['title'] ?? ''); ?>
      <a class="px-3 py-2 border rounded inline-flex items-center gap-2 hover:bg-gray-50" href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor"><path d="M19.633 7.997c.013.17.013.34.013.51 0 5.206-3.963 11.203-11.203 11.203-2.224 0-4.292-.652-6.033-1.78.31.036.607.049.93.049 1.844 0 3.54-.627 4.887-1.69a3.95 3.95 0 01-3.686-2.736c.24.036.481.062.735.062.353 0 .706-.049 1.036-.135a3.944 3.944 0 01-3.162-3.868v-.049c.53.296 1.139.472 1.79.496a3.936 3.936 0 01-1.758-3.28c0-.735.196-1.406.545-1.992a11.197 11.197 0 008.129 4.127 4.445 4.445 0 01-.098-.903 3.942 3.942 0 013.944-3.944c1.134 0 2.157.481 2.872 1.253a7.77 7.77 0 002.504-.954 3.978 3.978 0 01-1.734 2.177 7.881 7.881 0 002.268-.607 8.479 8.479 0 01-1.972 2.04z"/></svg>
        Twitter
      </a>
      <a class="px-3 py-2 border rounded inline-flex items-center gap-2 hover:bg-gray-50" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700" viewBox="0 0 24 24" fill="currentColor"><path d="M4.983 3.5C3.88 3.5 3 4.38 3 5.483c0 1.102.88 1.982 1.983 1.982 1.102 0 1.982-.88 1.982-1.982C6.965 4.38 6.085 3.5 4.983 3.5zM3.25 8.25h3.466V20.5H3.25V8.25zM9.6 8.25h3.322v1.666h.047c.463-.878 1.594-1.802 3.283-1.802 3.51 0 4.156 2.311 4.156 5.317V20.5h-3.466v-5.494c0-1.31-.023-2.996-1.827-2.996-1.829 0-2.109 1.432-2.109 2.907V20.5H9.6V8.25z"/></svg>
        LinkedIn
      </a>
      <a class="px-3 py-2 border rounded inline-flex items-center gap-2 hover:bg-gray-50" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.07C22 6.55 17.52 2 12 2S2 6.55 2 12.07c0 5.02 3.66 9.19 8.44 9.93v-7.02H7.9v-2.91h2.54V9.41c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.91h-2.34v7.02C18.34 21.26 22 17.09 22 12.07z"/></svg>
        Facebook
      </a>
      <a class="px-3 py-2 border rounded inline-flex items-center gap-2 hover:bg-gray-50" href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" viewBox="0 0 24 24" fill="currentColor"><path d="M20.52 3.48A11.75 11.75 0 0012 .75C5.66.75.75 5.66.75 12c0 1.97.49 3.86 1.43 5.55L.75 23.25l5.86-1.39A11.2 11.2 0 0012 23.25c6.34 0 11.25-4.91 11.25-11.25 0-3.01-1.17-5.84-3.23-7.99zM12 21.07c-1.82 0-3.6-.48-5.16-1.38l-.37-.22-3.48.83.83-3.39-.24-.39A9.45 9.45 0 1121.45 12 9.42 9.42 0 0112 21.07zm5.39-7.2c-.29-.15-1.72-.84-1.99-.93-.27-.1-.47-.15-.67.15-.2.29-.77.93-.94 1.12-.17.2-.35.22-.64.07-.29-.15-1.22-.45-2.32-1.43-.86-.76-1.44-1.69-1.61-1.98-.17-.29-.02-.45.13-.59.14-.14.29-.35.43-.52.14-.17.19-.3.29-.5.1-.2.05-.37-.03-.52-.08-.15-.67-1.62-.92-2.21-.24-.58-.49-.5-.67-.5-.17 0-.37 0-.57 0-.2 0-.52.07-.8.37s-1.05 1.03-1.05 2.52 1.08 2.92 1.23 3.12c.15.2 2.13 3.26 5.17 4.44.72.31 1.28.5 1.72.64.72.22 1.38.19 1.9.12.58-.09 1.78-.72 2.03-1.42.25-.7.25-1.3.17-1.42-.08-.12-.27-.19-.56-.34z"/></svg>
        WhatsApp
      </a>
    </div>
    <div class="prose max-w-none mt-8">
      <?= $content ?>
    </div>
    <div class="mt-8 flex flex-wrap gap-2">
      <?php foreach ($tags as $t): ?>
        <a class="px-2 py-1 text-sm border rounded hover:bg-gray-50" href="/blog/tag/<?= htmlspecialchars($t['slug'] ?? '') ?>">
          #<?= htmlspecialchars($t['name'] ?? '') ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($related)): ?>
      <div class="mt-10">
        <h4 class="text-xl font-semibold mb-3">You may also like</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($related as $rp): ?>
            <a href="/blog/<?= htmlspecialchars($rp['slug'] ?? '') ?>" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
              <div class="relative">
                <?php if (!empty($rp['featured_image'])): ?>
                  <img src="<?= htmlspecialchars($rp['featured_image']) ?>" alt="<?= htmlspecialchars($rp['title'] ?? '') ?>" class="w-full h-32 object-cover">
                <?php else: ?>
                  <div class="w-full h-32 bg-gradient-to-br from-gray-200 to-gray-300"></div>
                <?php endif; ?>
              </div>
              <div class="p-4">
                <div class="font-semibold line-clamp-2"><?= htmlspecialchars($rp['title'] ?? '') ?></div>
                <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($rp['published_at'] ?? '') ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </article>
  <aside>
    <div class="sticky top-6 space-y-6">
      <?php if (!empty($toc)): ?>
        <div class="border rounded p-4">
          <h3 class="font-semibold mb-2">Table of Contents</h3>
          <ul class="space-y-2 text-sm">
            <?php foreach ($toc as $item): ?>
              <li>
                <a class="text-blue-600 hover:underline" href="#<?= htmlspecialchars($item['id'] ?? '') ?>">
                  <?= htmlspecialchars($item['text'] ?? '') ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <div class="border rounded p-4">
        <h3 class="font-semibold mb-2">Categories</h3>
        <ul class="space-y-2">
          <?php foreach ($categories as $c): ?>
            <li>
              <a class="text-blue-600 hover:underline" href="/blog/category/<?= htmlspecialchars($c['slug'] ?? '') ?>">
                <?= htmlspecialchars($c['name'] ?? '') ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="border rounded p-4">
        <h3 class="font-semibold mb-2">Search</h3>
        <form action="/blog" method="GET" class="flex gap-2">
          <input type="text" name="search" placeholder="Search articles..." class="flex-1 px-3 py-2 border rounded">
          <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Search</button>
        </form>
      </div>
      <?php if (!empty($latestArticles)): ?>
      <div class="border rounded p-4">
        <h3 class="font-semibold mb-2">Recent Posts</h3>
        <ul class="space-y-3">
          <?php foreach ($latestArticles as $lp): ?>
            <li>
              <a href="/blog/<?= htmlspecialchars($lp['slug'] ?? '') ?>" class="flex items-center gap-3 group">
                <?php if (!empty($lp['featured_image'])): ?>
                  <img src="<?= htmlspecialchars($lp['featured_image']) ?>" alt="<?= htmlspecialchars($lp['title'] ?? '') ?>" class="w-14 h-14 object-cover rounded">
                <?php else: ?>
                  <div class="w-14 h-14 rounded bg-gray-200"></div>
                <?php endif; ?>
                <div>
                  <div class="text-sm font-medium group-hover:text-blue-600"><?= htmlspecialchars($lp['title'] ?? '') ?></div>
                  <div class="text-xs text-gray-500"><?= htmlspecialchars($lp['published_at'] ?? '') ?></div>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </aside>
  </div>
