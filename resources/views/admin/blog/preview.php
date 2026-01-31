<?php
/** @var array $blog */
?>
<div class="p-6">
  <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($blog['title'] ?? '') ?></h1>
  <?php if (!empty($blog['featured_image'])): ?>
    <img src="<?= htmlspecialchars($blog['featured_image']) ?>" alt="<?= htmlspecialchars($blog['title'] ?? '') ?>" class="w-full h-auto rounded mb-6">
  <?php endif; ?>
  <div class="prose max-w-none">
    <?= $blog['content'] ?? '' ?>
  </div>
  <div class="mt-6">
    <a href="/blog/<?= htmlspecialchars($blog['slug'] ?? '') ?>" class="text-blue-600 hover:underline" target="_blank">View public page</a>
  </div>
</div>

