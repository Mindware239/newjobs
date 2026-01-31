<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My Reviews' ?> - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none;
            color: white;
            transition: all 0.2s ease;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-3 bg-blue-600 rounded-xl shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Reviews</h1>
                    <p class="text-gray-600 mt-1">
                        Your reviews, questions and answers will appear on the employer’s Company Page.
                        They are not associated with your name.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-indigo-700">Reviews (<?= count($userReviews ?? []) ?>)</span>
                        <?php if (!empty($userReviews)): ?>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Sort</label>
                            <?php $sort = $_GET['sort'] ?? 'newest'; ?>
                            <form method="GET">
                                <select name="sort" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md">
                                    <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
                                    <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Highest rating</option>
                                </select>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="/candidate/reviews/create" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Write a review
                    </a>
                </div>
            </div>

            <div class="p-6">
                <?php if (empty($userReviews)): ?>
                    <div class="flex flex-col items-center text-center py-12">
                        <div class="mb-4 bg-indigo-50 p-4 rounded-full">
                            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            Unlock all reviews
                        </h3>

                        <p class="text-gray-600 mb-8 max-w-sm">
                            Access all reviews by writing yours. Share your experience to help others.
                        </p>
                        <p class="text-sm text-gray-500">Use the button at the top-right to start.</p>
                    </div>
                <?php else: ?>
                    <?php
                        $sort = $_GET['sort'] ?? 'newest';
                        if ($sort === 'rating') {
                            usort($userReviews, fn($a,$b) => ($b['rating']??0) <=> ($a['rating']??0));
                        }
                    ?>
                    <div class="space-y-6">
                        <?php foreach ($userReviews as $review): ?>
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?= htmlspecialchars($review['title'] ?? 'Review') ?>
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <?php if (!empty($review['company_logo'])): ?>
                                            <img src="<?= htmlspecialchars($review['company_logo']) ?>" alt="Logo" class="w-6 h-6 rounded-full object-cover">
                                        <?php endif; ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <?= htmlspecialchars($review['company_name'] ?? 'Company') ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center mb-3">
                                    <div class="flex text-yellow-400">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <svg class="w-5 h-5 <?= $i <= ($review['rating'] ?? 0) ? 'fill-current' : 'text-gray-300' ?>" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>
                                <?php 
                                    $text = trim($review['review_text'] ?? '');
                                    $short = mb_strlen($text) > 280 ? mb_substr($text,0,280) . '…' : $text;
                                ?>
                                <p class="text-gray-600 whitespace-pre-line" id="rv-<?= (int)$review['id'] ?>">
                                    <?= htmlspecialchars($short) ?>
                                </p>
                                <?php if (mb_strlen($text) > 280): ?>
                                    <button class="mt-2 text-sm text-indigo-600 hover:text-indigo-800" onclick="document.getElementById('rv-<?= (int)$review['id'] ?>').textContent = '<?= htmlspecialchars($text, ENT_QUOTES) ?>'; this.remove();">
                                        Read more
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>
