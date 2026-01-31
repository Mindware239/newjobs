<?php $base = rtrim($_ENV['APP_URL'] ?? '', '/'); ?>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Testimonials</h1>
        <a href="<?= $base ?>/admin/testimonials/create" class="px-4 py-2 rounded-lg bg-[#0EA57A] text-white font-semibold">Add Testimonial</a>
    </div>
    <?php if (!empty($_GET['success'])): ?>
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 text-green-700"><?= htmlspecialchars((string)$_GET['success']) ?></div>
    <?php endif; ?>
    <div class="flex items-center gap-3 mb-4">
        <form method="get" class="flex items-center gap-2">
            <select name="type" class="px-3 py-2 border rounded-lg">
                <option value="all" <?= ($filters['type'] ?? 'all')==='all'?'selected':'' ?>>All Types</option>
                <option value="client" <?= ($filters['type'] ?? '')==='client'?'selected':'' ?>>Client</option>
                <option value="candidate" <?= ($filters['type'] ?? '')==='candidate'?'selected':'' ?>>Candidate</option>
            </select>
            <select name="status" class="px-3 py-2 border rounded-lg">
                <option value="all" <?= ($filters['status'] ?? 'all')==='all'?'selected':'' ?>>All Status</option>
                <option value="active" <?= ($filters['status'] ?? '')==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '')==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Filter</button>
        </form>
    </div>
    <div class="overflow-x-auto bg-white rounded-xl border">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-gray-700">
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Message/Video</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($items ?? []) as $row): ?>
                <tr class="border-t">
                    <td class="p-3"><?= htmlspecialchars(ucfirst((string)($row['testimonial_type'] ?? ''))) ?></td>
                    <td class="p-3">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                            <?php endif; ?>
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars((string)($row['name'] ?? '')) ?></div>
                                <div class="text-gray-500"><?= htmlspecialchars(trim(((string)($row['designation'] ?? '') . ' ' . ($row['company'] ? '• ' . $row['company'] : '')))) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="p-3">
                        <?= htmlspecialchars((string)($row['title'] ?? '')) ?: '<span class="text-gray-400">—</span>' ?>
                    </td>
                    <td class="p-3">
                        <?php if (!empty($row['video_url'])): ?>
                            <a href="<?= htmlspecialchars((string)$row['video_url']) ?>" target="_blank" class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700 hover:bg-purple-200">Video</a>
                        <?php elseif (!empty($row['message'])): ?>
                            <span class="text-gray-600 line-clamp-2"><?= htmlspecialchars((string)$row['message']) ?></span>
                        <?php else: ?>
                            <span class="text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3">
                        <?php if ((int)($row['is_active'] ?? 0) === 1): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-200 text-gray-700">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= $base ?>/admin/testimonials/<?= (int)$row['id'] ?>/edit" class="px-3 py-1 rounded-md border text-gray-700 hover:bg-gray-50">Edit</a>
                            <form action="<?= $base ?>/admin/testimonials/<?= (int)$row['id'] ?>/toggle" method="post">
                                <button class="px-3 py-1 rounded-md border <?= ((int)$row['is_active']===1)?'text-orange-700':'text-green-700' ?> hover:bg-gray-50"><?= ((int)$row['is_active']===1)?'Deactivate':'Activate' ?></button>
                            </form>
                            <form action="<?= $base ?>/admin/testimonials/<?= (int)$row['id'] ?>/delete" method="post" onsubmit="return confirm('Delete this testimonial?')">
                                <button class="px-3 py-1 rounded-md border text-red-700 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <tr><td colspan="5" class="p-6 text-center text-gray-500">No testimonials found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
 </div>
