<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organization Details | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<?php $base = $base ?? '/'; ?>
<body class="bg-slate-50 min-h-screen font-sans">
<?php include __DIR__ . '/header.php'; ?>

<main class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10">
    <?php if (!$org): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <p class="text-slate-700">Organization not found.</p>
            <a href="<?= $base ?>searchEmployers" class="text-[#e15f55] font-bold hover:underline">Back to Search Employers</a>
        </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <section class="lg:col-span-8">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="p-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900"><?= htmlspecialchars($org['name']) ?></h1>
                </div>
            </div>
            <div class="mt-6 space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="font-bold text-slate-900 mb-2">Website</h2>
                    <?php if (!empty($org['website'])): ?>
                        <a href="<?= htmlspecialchars($org['website']) ?>" target="_blank" class="text-[#e15f55] hover:underline"><?= htmlspecialchars($org['website']) ?></a>
                    <?php else: ?>
                        <span class="text-slate-600">Not provided</span>
                    <?php endif; ?>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="font-bold text-slate-900 mb-2">Organization Type</h2>
                    <p class="text-slate-700"><?= htmlspecialchars($org['type']) ?><?= $org['is_agency'] ? ' (Agency)' : '' ?></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="font-bold text-slate-900 mb-2">Staff Count</h2>
                    <p class="text-slate-700"><?= (int)$org['staff_count'] > 0 ? (int)$org['staff_count'] : 'Not specified' ?></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="font-bold text-slate-900 mb-2">Our Mission</h2>
                    <p class="text-slate-700 mb-2"><?= htmlspecialchars($org['mission_focus']) ?></p>
                    <p class="text-slate-700"><?= nl2br(htmlspecialchars($org['mission'])) ?></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="font-bold text-slate-900 mb-2">Impact</h2>
                    <p class="text-slate-700"><?= nl2br(htmlspecialchars($org['impact'])) ?></p>
                </div>
            </div>
        </section>
        <aside class="lg:col-span-4">
            <div class="bg-white border border-slate-200 rounded-xl p-6 flex items-center justify-center">
                <img src="<?= htmlspecialchars($org['logo']) ?>" alt="<?= htmlspecialchars($org['name']) ?>" class="w-[260px] h-[120px] object-contain" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($org['name']) ?>&background=ffffff&color=54595f'">
            </div>
        </aside>
    </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
