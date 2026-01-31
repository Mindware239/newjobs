<?php 
/**
 * @var string $title
 * @var string $message
 * @var \App\Models\User $user
 */
?>

<div class="max-w-2xl mx-auto mt-8">
    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Profile Incomplete</h2>
            <p class="mt-2 text-gray-600"><?= htmlspecialchars($message) ?></p>
        </div>
        
        <div class="mt-8">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-sm text-blue-800">
                    <strong>User ID:</strong> <?= htmlspecialchars($user->id ?? 'N/A') ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($user->email ?? 'N/A') ?><br>
                    <strong>Role:</strong> <?= htmlspecialchars($user->role ?? 'N/A') ?>
                </p>
            </div>
        </div>
        
        <div class="mt-8 flex justify-center space-x-4">
            <a href="/register-employer" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Complete Registration
            </a>
            <a href="/logout" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Logout
            </a>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                If you believe this is an error, please contact support with your User ID: <strong><?= htmlspecialchars($user->id ?? 'N/A') ?></strong>
            </p>
        </div>
    </div>
</div>

