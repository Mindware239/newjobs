<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Two-Factor Authentication' ?> - Mindware InfoTech</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white shadow-xl rounded-lg p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Two-Factor Authentication</h1>
            <p class="text-sm text-gray-600">
                We have sent a 6-digit verification code to
                <span class="font-semibold"><?= htmlspecialchars($email ?? 'your email') ?></span>.
                Enter the code below to continue.
            </p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/2fa/verify" class="space-y-4">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                <input
                    id="otp"
                    name="otp"
                    type="text"
                    maxlength="6"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter 6-digit code"
                    autofocus
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
            >
                Verify & Sign in
            </button>
        </form>

        <div class="text-center text-sm text-gray-500">
            <p>This code will expire in 10 minutes.</p>
        </div>
    </div>
</body>
</html>


