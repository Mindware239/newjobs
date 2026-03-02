<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Resume Upload</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto py-12 px-4">
        <div class="bg-white rounded-xl shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Bulk Resume Upload</h1>
                <a href="/bulk/logout" class="text-sm text-gray-600">Logout</a>
            </div>
            <p class="text-sm text-gray-500 mb-4">Remaining uploads: <?= (int)($remaining ?? 0) ?></p>
            <?php if (!empty($error)): ?>
                <div class="mb-4 text-sm text-red-600"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <input type="file" name="resumes[]" multiple accept=".pdf,.doc,.docx" class="w-full px-4 py-2 border rounded-lg">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Upload</button>
            </form>
        </div>
    </div>
</body>
</html>
