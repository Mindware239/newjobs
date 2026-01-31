<?php
/**
 * @var array $interview
 * @var array $events
 * @var array $timeline
 */
$jobTitle = (string)($interview['job_title'] ?? 'Interview');
$candidateName = (string)($interview['candidate_name'] ?? 'Candidate');
?>

<div class="min-h-screen">
    <div class="border-b border-white/10 bg-gray-950/60 backdrop-blur supports-[backdrop-filter]:bg-gray-950/40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <div>
                <div class="text-sm text-white/60">Interview analytics</div>
                <div class="text-lg font-semibold text-white"><?= htmlspecialchars($jobTitle) ?></div>
                <div class="text-xs text-white/60"><?= htmlspecialchars($candidateName) ?></div>
            </div>
            <div class="flex items-center gap-2">
                <a href="/interviews/<?= (int)$interview['id'] ?>/room" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                    Back to room
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 bg-white/5 border border-white/10 rounded-2xl p-5">
            <div class="text-sm font-semibold text-white">Event totals</div>
            <div class="mt-4 space-y-2">
                <?php if (empty($events)): ?>
                    <div class="text-sm text-white/60">No events logged yet.</div>
                <?php else: ?>
                    <?php foreach ($events as $e): ?>
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-sm text-white/80"><?= htmlspecialchars((string)$e['event_type']) ?></div>
                            <div class="text-sm font-semibold text-white"><?= (int)$e['cnt'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-7 bg-white/5 border border-white/10 rounded-2xl p-5">
            <div class="text-sm font-semibold text-white">Timeline</div>
            <div class="mt-4 space-y-2 max-h-[70vh] overflow-auto">
                <?php if (empty($timeline)): ?>
                    <div class="text-sm text-white/60">No timeline events yet.</div>
                <?php else: ?>
                    <?php foreach ($timeline as $t): ?>
                        <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm text-white/90"><?= htmlspecialchars((string)$t['event_type']) ?></div>
                                <div class="text-xs text-white/60"><?= htmlspecialchars((string)$t['created_at']) ?></div>
                            </div>
                            <div class="text-xs text-white/60 mt-1">Actor: <?= htmlspecialchars((string)($t['actor_role'] ?? '')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

