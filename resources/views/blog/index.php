<?php
/** @var array $blogs */
/** @var array $categories */
/** @var array $tags */
/** @var array $pagination */
/** @var array $featured */
/** @var array $byCategory */
?>
<?php $search = $search ?? trim((string)($_GET['search'] ?? '')); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <form method="GET" action="/blog" class="flex gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search articles..." class="flex-1 px-4 py-2 border rounded">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Search</button>
    </form>
  </div>
  <!-- <h1 class="text-3xl font-extrabold mb-6">Mindware Infotech Blog</h1> -->
  <?php if (!empty($featured)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
      <?php foreach ($featured as $f): ?>
        <a href="/blog/<?= htmlspecialchars($f['slug'] ?? '') ?>" class="group relative rounded-xl overflow-hidden bg-gray-100">
          <?php if (!empty($f['featured_image'])): ?>
            <img src="<?= htmlspecialchars($f['featured_image']) ?>" alt="<?= htmlspecialchars($f['title'] ?? '') ?>" class="w-full h-56 object-cover">
          <?php else: ?>
            <div class="w-full h-56 bg-gradient-to-br from-gray-200 to-gray-300"></div>
          <?php endif; ?>
          <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
          <div class="absolute bottom-0 left-0 right-0 p-4">
            <h3 class="text-white text-lg font-bold"><?= htmlspecialchars($f['title'] ?? '') ?></h3>
            <p class="text-white/90 text-sm line-clamp-2"><?= htmlspecialchars($f['excerpt'] ?? '') ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($search !== ''): ?>
    <h2 class="text-xl font-semibold mb-4">Search results</h2>
    <?php if (empty($blogs)): ?>
      <p class="text-gray-600">No articles found.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach ($blogs as $p): ?>
          <a href="/blog/<?= htmlspecialchars($p['slug'] ?? '') ?>" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
            <div class="relative">
              <?php if (!empty($p['featured_image'])): ?>
                <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['title'] ?? '') ?>" class="w-full h-40 object-cover">
              <?php else: ?>
                <div class="w-full h-40 bg-gradient-to-br from-gray-200 to-gray-300"></div>
              <?php endif; ?>
            </div>
            <div class="p-4">
              <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($p['title'] ?? '') ?></h3>
              <p class="text-gray-600 text-sm line-clamp-2"><?= htmlspecialchars($p['excerpt'] ?? '') ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php elseif (!empty($byCategory) && is_array($byCategory)): ?>
    <?php foreach ($byCategory as $group): ?>
      <?php $cat = $group['category'] ?? []; $posts = $group['posts'] ?? []; ?>
      <?php if (!empty($posts)): ?>
        <section class="py-8 mb-8" style="background-color:#E7F7F3;">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 uppercase"><?= htmlspecialchars($cat['name'] ?? '') ?></h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php foreach ($posts as $p): ?>
                <a href="/blog/<?= htmlspecialchars($p['slug'] ?? '') ?>" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
                  <div class="relative">
                    <?php if (!empty($p['featured_image'])): ?>
                      <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['title'] ?? '') ?>" class="w-full h-40 object-cover">
                    <?php else: ?>
                      <div class="w-full h-40 bg-gradient-to-br from-gray-200 to-gray-300"></div>
                    <?php endif; ?>
                  </div>
                  <div class="p-4">
                    <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($p['title'] ?? '') ?></h3>
                    <p class="text-gray-600 text-sm line-clamp-2"><?= htmlspecialchars($p['excerpt'] ?? '') ?></p>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php
    $page = (int)($pagination['page'] ?? 1);
    $per = (int)($pagination['per_page'] ?? 12);
    $total = (int)($pagination['total'] ?? 0);
    $pages = $per > 0 ? max(1, (int)ceil($total / $per)) : 1;
  ?>
  <div class="flex items-center gap-2 mt-4">
    <?php if ($page > 1): ?>
      <a class="px-3 py-2 border rounded" href="/blog?page=<?= $page - 1 ?>">Prev</a>
    <?php endif; ?>
    <span class="text-gray-600">Page <?= $page ?> of <?= $pages ?></span>
    <?php if ($page < $pages): ?>
      <a class="px-3 py-2 border rounded" href="/blog?page=<?= $page + 1 ?>">Next</a>
    <?php endif; ?>
  </div>
</div>
