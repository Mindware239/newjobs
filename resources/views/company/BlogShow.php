<?php
$blog = $blog ?? [];

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($blog['title']) ?></title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<div class="max-w-5xl mx-auto px-6 py-10">

    <!-- Blog Header -->
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-blue-600 text-sm hover:underline">← Back</a>
    </div>

    <article class="bg-white p-8 rounded-xl shadow">

        <?php if (!empty($blog['image'])): ?>
            <img src="<?= e($blog['image']) ?>" class="w-full h-80 object-cover rounded mb-6">
        <?php endif; ?>

        <h1 class="text-3xl font-bold mb-2"><?= e($blog['title']) ?></h1>

        <p class="text-gray-500 text-sm mb-6">
            Published on <?= date('F d, Y', strtotime($blog['created_at'])) ?>
        </p>

        <div class="prose max-w-none text-gray-800 leading-relaxed">
            <?= nl2br($blog['content']) ?>
        </div>

    </article>

</div>

</body>
</html>
