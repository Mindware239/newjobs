<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title ?? 'Reset Password') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto mt-10 bg-white border rounded-xl shadow-sm p-6">
    <h1 class="text-xl font-semibold mb-2">Reset Password</h1>
    <p class="text-sm text-gray-600 mb-4">Enter your email address and we will send you a reset link.</p>
    <?php if (!empty($sent)): ?>
      <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 p-3 text-sm">
        If an account exists for <strong><?= htmlspecialchars($email ?? '') ?></strong>, a reset link has been sent.
      </div>
    <?php endif; ?>
    <form method="post" action="/social-services/forgot-password" class="space-y-4">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <label class="block text-sm font-medium">Email</label>
      <input type="email" name="email" required class="w-full border border-gray-300 rounded-md p-2.5">
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
