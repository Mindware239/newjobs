<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$renderValue = static function (string $v) use ($h, $reg): string {
    $out = $h($v);
    foreach (['photo' => 'photo', 'selfie' => 'selfie'] as $kind => $text) {
        $src = '/admin/registrations/' . (int)$reg['id'] . '/file/' . $kind;
        $out = str_replace('__file:' . $kind, '<a target="_blank" href="' . $src . '"><img src="' . $src . '" alt="' . $text . '" style="max-height:160px;border-radius:10px;border:1px solid #e5e7eb"></a>', $out);
    }
    foreach (['resume' => 'Download resume', 'video' => 'Watch demo video', 'address_proof' => 'View address proof'] as $kind => $text) {
        $out = str_replace('__file:' . $kind, '<a class="font-semibold text-primary hover:underline" target="_blank" href="/admin/registrations/' . (int)$reg['id'] . '/file/' . $kind . '">' . $text . '</a>', $out);
    }
    return preg_replace('#(https?://[^\s<]+)#', '<a class="text-primary hover:underline break-all" target="_blank" rel="noopener noreferrer" href="$1">$1</a>', $out);
};
?>
<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="/admin/registrations" class="text-sm font-semibold text-gray-500 hover:text-gray-900">&larr; All registrations</a>
            <h1 class="mt-2 text-3xl font-bold text-gray-900"><?= $h($reg['reg_no']) ?> · <?= $h($reg['full_name']) ?></h1>
            <p class="text-sm text-gray-600"><?= $h($form['title'][1]) ?></p>
        </div>
        <div class="flex gap-2">
            <?php if (!empty($reg['mobile'])): ?>
                <a href="https://wa.me/91<?= $h($reg['whatsapp'] ?: $reg['mobile']) ?>" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700">WhatsApp</a>
            <?php endif; ?>
            <?php if (!empty($reg['email'])): ?>
                <a href="mailto:<?= $h($reg['email']) ?>" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-bold text-white hover:bg-gray-900">Email</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"><?= $h($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <?php foreach ($sections as $title => $fields): ?>
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b px-5 py-3 text-sm font-bold uppercase text-gray-500"><?= $h($title) ?></div>
                    <dl class="divide-y divide-gray-100">
                        <?php foreach ($fields as $k => $v): ?>
                            <div class="grid grid-cols-3 gap-4 px-5 py-2.5 text-sm">
                                <dt class="font-semibold text-gray-500"><?= $h($k) ?></dt>
                                <dd class="col-span-2 text-gray-900 whitespace-pre-line"><?= $v !== '' ? $renderValue($v) : '—' ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
            <?php endforeach; ?>
        </div>

        <div>
            <form method="POST" action="/admin/registrations/<?= (int)$reg['id'] ?>" class="rounded-lg bg-white p-5 shadow space-y-4">
                <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                <div class="text-sm font-bold uppercase text-gray-500">Scrutiny</div>
                <label class="block text-sm font-semibold text-gray-700">Status
                    <select name="status" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $h($s) ?>" <?= $reg['status'] === $s ? 'selected' : '' ?>><?= $h(ucwords(str_replace('_', ' ', $s))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block text-sm font-semibold text-gray-700">Internal notes
                    <textarea name="admin_notes" rows="6" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= $h($reg['admin_notes']) ?></textarea>
                </label>
                <button type="submit" class="w-full rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-600">Save</button>
            </form>

            <?php if (in_array($reg['type'], ['skill', 'provider'], true)): ?>
            <div class="mt-6 rounded-lg bg-white p-5 shadow space-y-3">
                <div class="text-sm font-bold uppercase text-gray-500">Mentor matching</div>
                <?php if ($reg['type'] === 'skill'): ?>
                    <p class="text-xs text-gray-500">Selecting this candidate sends a request to the best-matching mentor automatically. Mentors accept or decline by email; if none is available the candidate is offered online / offline alternatives.</p>
                <?php endif; ?>
                <?php if (empty($mentorAssignments)): ?>
                    <p class="text-sm text-gray-500">No mentor requests yet.</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($mentorAssignments as $a): $badge = ['pending' => 'bg-yellow-100 text-yellow-800', 'accepted' => 'bg-green-100 text-green-800'][$a['status']] ?? 'bg-gray-100 text-gray-700'; ?>
                            <li class="py-2">
                                <div class="font-semibold text-gray-900"><?= $h($a['mentor_name'] ?? $a['candidate_name'] ?? '') ?> <span class="text-gray-400"><?= $h($a['mentor_reg_no'] ?? $a['candidate_reg_no'] ?? '') ?></span></div>
                                <div class="text-gray-600"><?= $h($a['skill']) ?> · <?= $a['mode'] === 'offline_ncr' ? 'Offline NCR' : 'Online' ?> · <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge ?>"><?= $h(ucfirst($a['status'])) ?></span></div>
                                <div class="text-xs text-gray-400"><?= $h($a['created_at']) ?> · via <?= $h(str_replace('_', ' ', $a['source'])) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($reg['type'] === 'skill' && $reg['payment_status'] === 'paid'): ?>
                    <div class="pt-2 border-t">
                        <div class="text-xs font-bold uppercase text-gray-500 mb-2">Suggested mentors (from the candidate's chosen skills)</div>
                        <?php if (empty($mentorSuggestions)): ?>
                            <p class="text-sm text-gray-500">No selected mentor teaches these skills yet.</p>
                        <?php endif; ?>
                        <?php foreach ($mentorSuggestions as $m): ?>
                            <form method="POST" action="/admin/registrations/<?= (int)$reg['id'] ?>/mentor-request" class="flex items-center justify-between gap-2 py-1.5 text-sm">
                                <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="mentor_id" value="<?= (int)$m['id'] ?>">
                                <span><b><?= $h($m['full_name']) ?></b> – <?= $h($m['matched_skill']) ?> <span class="text-gray-400">(<?= $h(implode(' + ', array_map(static fn($x) => $x === 'offline_ncr' ? 'Offline' : 'Online', $m['modes']))) ?>, <?= (int)$m['active_students'] ?> students)</span></span>
                                <button type="submit" class="rounded bg-gray-900 px-3 py-1 text-xs font-bold text-white">Send request</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
