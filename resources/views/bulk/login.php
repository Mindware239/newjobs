<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Uploader Login</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div x-data="{error: '<?= htmlspecialchars($error ?? '') ?>'}" class="bg-white rounded-xl shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6">Bulk Uploader Login</h1>
        <div x-show="error" class="mb-4 text-sm text-red-600" x-text="error"></div>
        <form method="post" class="space-y-4">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div>
                <label class="block text-sm font-semibold mb-1">Username</label>
                <input type="text" name="username" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg">Login</button>
        </form>
    </div>
</body>
</html>
