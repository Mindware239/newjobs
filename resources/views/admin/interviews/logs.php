<?php
/**
 * @var string $title
 * @var array $interview
 * @var array $logs
 * @var \App\Models\User $user
 */
?>
<div>
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Audit Logs — Interview #<?= (int)$interview['id'] ?></h1>
        <p class="text-sm text-gray-600">Job: <?= htmlspecialchars((string)($interview['job_title'] ?? '—')) ?> • Employer: <?= htmlspecialchars((string)($interview['company_name'] ?? '—')) ?></p>
        <div class="mt-3">
            <a href="/admin/interviews/<?= (int)$interview['id'] ?>" class="inline-flex items-center px-3 py-2 rounded-md text-sm border border-gray-300 bg-white hover:bg-gray-50">Back to Interview</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Event</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Payload</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">IP</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No logs recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                <?= htmlspecialchars(date('M d, Y h:i A', strtotime((string)$log['created_at']))) ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?= htmlspecialchars((string)$log['actor_role']) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?= htmlspecialchars((string)$log['event_type']) ?></td>
                            <td class="px-4 py-3 text-gray-700">
                                <pre class="text-xs whitespace-pre-wrap"><?= htmlspecialchars((string)($log['payload'] ?? '')) ?></pre>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?= htmlspecialchars((string)($log['ip_address'] ?? '')) ?></td>
                            <td class="px-4 py-3 text-gray-700">
                                <span class="text-xs"><?= htmlspecialchars((string)($log['user_agent'] ?? '')) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

