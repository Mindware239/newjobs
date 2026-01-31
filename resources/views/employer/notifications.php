<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $notifications
 */
?>

<h1 class="text-3xl font-bold text-gray-900 mb-6">Notifications</h1>

<div class="bg-white rounded-lg shadow-md p-6">
    <?php if (empty($notifications)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
            <p class="mt-1 text-sm text-gray-500">You're all caught up! New notifications will appear here.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($notifications as $notification): ?>
                <div class="border-b border-gray-200 pb-4 last:border-0">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm text-gray-900"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?= date('M d, Y H:i', strtotime($notification['created_at'] ?? 'now')) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

