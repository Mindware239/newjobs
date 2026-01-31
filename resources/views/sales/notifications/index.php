<?php
?><div class="space-y-4">
    <div class="flex justify-between items-center">
        <div class="text-xl font-semibold">Notifications</div>
        <form action="/sales/notifications/read" method="post">
            <button class="px-3 py-2 bg-purple-600 text-white rounded">Mark All Read</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow divide-y">
        <?php foreach ($items as $n): ?>
            <div class="p-4 flex items-start justify-between">
                <div>
                    <div class="text-sm font-medium"><?= htmlspecialchars($n['type'] ?? '') ?></div>
                    <div class="text-sm text-gray-700"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($n['created_at'] ?? '') ?></div>
                </div>
                <?php if (!empty($n['link'])): ?>
                    <a href="<?= htmlspecialchars($n['link']) ?>" class="text-purple-600 text-sm">Open</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
