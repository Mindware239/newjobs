<?php
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $notifications
 */
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
        <p class="mt-1 text-sm text-gray-600">Track applications, messages, interviews, and account updates.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button type="button" data-action="mark-all" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Mark all as read
        </button>
        <button type="button" data-action="clear-read" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Clear read
        </button>
    </div>
</div>

<div class="rounded-lg border border-gray-200 bg-white shadow-sm">
    <?php if (empty($notifications)): ?>
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
            <p class="mt-1 text-sm text-gray-500">New employer notifications will appear here.</p>
        </div>
    <?php else: ?>
        <ul id="employer-notifications" class="divide-y divide-gray-100">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $id = (int)($notification['id'] ?? 0);
                $isRead = (int)($notification['is_read'] ?? 0) === 1;
                $link = (string)($notification['link'] ?? '');
                ?>
                <li data-id="<?= $id ?>" data-read="<?= $isRead ? '1' : '0' ?>" class="<?= $isRead ? 'opacity-75' : 'bg-primary-50/40' ?>">
                    <div class="flex items-start gap-4 p-5">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="text-sm font-bold text-gray-900"><?= $h($notification['title'] ?? 'Notification') ?></h2>
                                <span class="whitespace-nowrap text-xs text-gray-500"><?= $h(date('M d, Y H:i', strtotime((string)($notification['created_at'] ?? 'now')))) ?></span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-gray-700"><?= $h($notification['message'] ?? '') ?></p>
                            <div class="mt-3 flex flex-wrap items-center gap-4">
                                <?php if ($link !== ''): ?>
                                    <a href="<?= $h($link) ?>" class="text-sm font-semibold text-primary hover:text-primary-600">View details</a>
                                <?php endif; ?>
                                <?php if (!$isRead): ?>
                                    <button type="button" data-action="mark-read" data-id="<?= $id ?>" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Mark as read</button>
                                <?php endif; ?>
                                <button type="button" data-action="delete" data-id="<?= $id ?>" class="text-xs font-semibold text-red-500 hover:text-red-600">Delete</button>
                            </div>
                        </div>
                        <?php if (!$isRead): ?>
                            <span data-dot class="mt-2 h-2.5 w-2.5 rounded-full bg-primary"></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('employer-notifications');
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const post = (url) => fetch(url, {method: 'POST', headers: {'X-CSRF-Token': token}});

    const markRowRead = (row) => {
        row.dataset.read = '1';
        row.classList.add('opacity-75');
        row.classList.remove('bg-primary-50/40');
        row.querySelector('[data-dot]')?.remove();
        row.querySelector('[data-action="mark-read"]')?.remove();
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const action = button.dataset.action;
        if (action === 'mark-all') {
            await post('/employer/notifications/read-all');
            list?.querySelectorAll('li').forEach(markRowRead);
            return;
        }

        if (action === 'clear-read') {
            await post('/employer/notifications/delete-read');
            list?.querySelectorAll('li[data-read="1"]').forEach((row) => row.remove());
            return;
        }

        const id = button.dataset.id;
        const row = id && list ? list.querySelector(`li[data-id="${id}"]`) : null;
        if (!id || !row) return;

        if (action === 'mark-read') {
            await post(`/employer/notifications/${id}/read`);
            markRowRead(row);
        }

        if (action === 'delete') {
            const response = await post(`/employer/notifications/${id}/delete`);
            const result = await response.json().catch(() => ({}));
            if (result.success) row.remove();
        }
    });
});
</script>











