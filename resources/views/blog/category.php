<?php
/** @var array $category */
/** @var array $blogs */
/** @var array $pagination */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($category['name'] ?? 'Category') ?></h1>
  <div class="space-y-4">
    <?php if (!empty($blogs)): ?>
      <?php foreach ($blogs as $b): ?>
        <a href="/blog/<?= htmlspecialchars($b['slug'] ?? '') ?>" class="block border rounded-lg p-4 hover:shadow">
          <h2 class="text-xl font-semibold"><?= htmlspecialchars($b['title'] ?? '') ?></h2>
          <p class="text-gray-600 mt-2"><?= htmlspecialchars($b['excerpt'] ?? '') ?></p>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-gray-500">No posts found in this category</div>
    <?php endif; ?>
  </div>
  <?php
    $page = (int)($pagination['page'] ?? 1);
    $per = (int)($pagination['per_page'] ?? 12);
    $total = (int)($pagination['total'] ?? 0);
    $pages = $per > 0 ? max(1, (int)ceil($total / $per)) : 1;
  ?>
  <div class="flex items-center gap-2 mt-4">
    <?php if ($page > 1): ?>
      <a class="px-3 py-2 border rounded" href="/blog/category/<?= htmlspecialchars($category['slug'] ?? '') ?>?page=<?= $page - 1 ?>">Prev</a>
    <?php endif; ?>
    <span class="text-gray-600">Page <?= $page ?> of <?= $pages ?></span>
    <?php if ($page < $pages): ?>
      <a class="px-3 py-2 border rounded" href="/blog/category/<?= htmlspecialchars($category['slug'] ?? '') ?>?page=<?= $page + 1 ?>">Next</a>
    <?php endif; ?>
  </div>
</div>
