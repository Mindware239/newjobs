<?php
/** @var array $blog */
?>
<div class="p-6">
  <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($blog['title'] ?? '') ?></h1>
  <?php if (!empty($blog['featured_image'])): ?>
    <img src="<?= htmlspecialchars($blog['featured_image']) ?>" alt="<?= htmlspecialchars($blog['title'] ?? '') ?>" class="w-full h-auto rounded mb-6">
  <?php endif; ?>
  <style>
    .blog-preview{color:#111827}
    .blog-preview h1{font-size:2rem;font-weight:800;margin:1.25rem 0}
    .blog-preview h2{font-size:1.75rem;font-weight:700;margin:1rem 0}
    .blog-preview h3{font-size:1.5rem;font-weight:700;margin:.85rem 0}
    .blog-preview p{margin:.85rem 0;line-height:1.85}
    .blog-preview ul,.blog-preview ol{margin:.75rem 0 1rem 1.25rem;padding:0}
    .blog-preview ul{list-style:disc;padding-left:1.5rem}
    .blog-preview ol{list-style:decimal;padding-left:1.5rem}
    .blog-preview li{margin:.4rem 0;line-height:1.75}
    .blog-preview ul li::marker,.blog-preview ol li::marker{color:#e15f55;font-weight:700}
    .blog-preview blockquote{border-left:4px solid #e5e7eb;padding:.5rem 1rem;margin:1rem 0;color:#4b5563;background:#f9fafb}
    .blog-preview table{width:100%;border-collapse:collapse;margin:1rem 0}
    .blog-preview th,.blog-preview td{border:1px solid #e5e7eb;padding:.6rem .9rem}
    .blog-preview tr:nth-child(odd){background:#fafafa}
    .blog-preview a{color:#2563eb;text-decoration:none;border-bottom:1px solid #bfdbfe}
    .blog-preview a:hover{color:#1d4ed8;border-bottom-color:#93c5fd}
    .blog-preview hr{border:0;border-top:1px solid #e5e7eb;margin:1.5rem 0}
    .blog-preview img{max-width:100%;height:auto;border-radius:.5rem;margin:1rem 0}
    .blog-preview code{background:#f3f4f6;border:1px solid #e5e7eb;padding:.15rem .35rem;border-radius:.3rem}
    .blog-preview pre{background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:.6rem;overflow:auto}
    /* CKEditor Styles mapping */
    .blog-preview .marker{background:#fff59e;padding:0 .25em;border-radius:.15rem}
    .blog-preview .special-container{border:1px solid #e5e7eb;background:#f9fafb;padding:1rem;border-radius:.5rem;margin:1rem 0}
    .blog-preview .italic-title{font-style:italic}
    .blog-preview .subtitle{font-size:1.1rem;color:#6b7280}
  </style>
  <div class="blog-preview">
    <?= $blog['content'] ?? '' ?>
  </div>
  <div class="mt-6">
    <a href="/blog/<?= htmlspecialchars($blog['slug'] ?? '') ?>" class="text-blue-600 hover:underline" target="_blank">View public page</a>
  </div>
</div>
