<?php
?><div class="space-y-6">
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="font-semibold mb-4">Add Lead</div>
        <form action="/sales/leads" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input name="company_name" required placeholder="Company Name" class="border rounded px-3 py-2">
            <input name="contact_name" placeholder="Contact Name" class="border rounded px-3 py-2">
            <input name="contact_email" type="email" placeholder="Contact Email" class="border rounded px-3 py-2">
            <input name="contact_phone" placeholder="Contact Phone" class="border rounded px-3 py-2">
            <input name="deal_value" type="number" step="0.01" placeholder="Deal Value" class="border rounded px-3 py-2">
            <select name="currency" class="border rounded px-3 py-2">
                <?php foreach (['INR','USD','EUR'] as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?></option>
                <?php endforeach; ?>
            </select>
            <select name="stage" class="border rounded px-3 py-2">
                <?php foreach (['new','contacted','follow_up','demo_done','payment_pending','converted','lost'] as $s): ?>
                    <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <select name="source" class="border rounded px-3 py-2">
                <?php foreach (['form','import','referral','cold_call'] as $s): ?>
                    <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <input name="next_followup_at" type="datetime-local" placeholder="Next Follow-up" class="border rounded px-3 py-2">
            <?php if (!empty($team)): ?>
                <select name="assigned_to" class="border rounded px-3 py-2">
                    <option value="">Assign to...</option>
                    <?php foreach ($team as $member): ?>
                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name'] ?: $member['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="assigned_to" value="<?= $user->id ?>">
            <?php endif; ?>
            <div class="md:col-span-2">
                <button class="px-4 py-2 bg-purple-600 text-white rounded">Save Lead</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="font-semibold mb-4">CSV Upload</div>
        <form method="post" enctype="multipart/form-data" action="#" class="space-y-3">
            <input type="file" name="csv" accept=".csv" class="border rounded px-3 py-2">
            <button class="px-4 py-2 border border-purple-600 text-purple-600 rounded">Upload (UI demo)</button>
        </form>
        <div class="text-xs text-gray-500 mt-2">Expected columns: company_name, contact_name, contact_email, contact_phone, stage, source, deal_value, currency, next_followup_at, assigned_to</div>
    </div>
</div>
