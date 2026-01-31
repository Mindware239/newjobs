<?php $lead = $lead ?? []; ?>
<div class="space-y-6" x-data="leadPage()">
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xl font-semibold"><?= htmlspecialchars($lead['company_name'] ?? 'Lead') ?></div>
                <div class="text-sm text-gray-500">Contact: <?= htmlspecialchars($lead['contact_name'] ?? '') ?> • <?= htmlspecialchars($lead['contact_email'] ?? '') ?> • <?= htmlspecialchars($lead['contact_phone'] ?? '') ?></div>
            </div>
            <div class="flex items-center gap-2">
                <form action="/sales/leads/<?= (int)($lead['id'] ?? 0) ?>/stage" method="post" class="flex gap-2">
                    <select name="stage" class="border rounded">
                        <?php foreach (['new','contacted','follow_up','demo_done','payment_pending','converted','lost'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($lead['stage'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="px-3 py-2 border border-purple-600 text-purple-600 rounded">Update Stage</button>
                </form>
                <form action="/sales/leads/<?= (int)($lead['id'] ?? 0) ?>/assign" method="post" class="flex gap-2">
                    <input name="executive_id" placeholder="Executive ID" class="border rounded px-2">
                    <button class="px-3 py-2 bg-purple-600 text-white rounded">Assign</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow">
                <div class="p-4 font-semibold">Notes</div>
                <div class="p-4 border-t space-y-3">
                    <form action="/sales/leads/<?= (int)($lead['id'] ?? 0) ?>/note" method="post" class="flex gap-2">
                        <input name="content" placeholder="Add note" class="flex-1 border rounded px-3 py-2">
                        <button class="px-3 py-2 bg-purple-600 text-white rounded">Add</button>
                    </form>
                    <?php foreach ($notes as $n): ?>
                        <div class="border rounded p-3 flex items-start justify-between">
                            <div>
                                <div class="text-sm text-gray-800"><?= htmlspecialchars($n['note_text'] ?? '') ?></div>
                                <div class="text-xs text-gray-500">By <?= htmlspecialchars($n['user_name'] ?? '') ?> • <?= htmlspecialchars($n['created_at'] ?? '') ?></div>
                            </div>
                            <form action="/sales/leads/<?= (int)($lead['id'] ?? 0) ?>/notes/<?= (int)$n['id'] ?>/delete" method="post">
                                <button class="text-red-600 text-xs">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow">
                <div class="p-4 font-semibold">Activity Timeline</div>
                <div class="p-4 border-t space-y-3">
                    <?php foreach ($activities as $a): ?>
                        <div class="flex gap-3 items-start">
                            <div class="h-2 w-2 rounded-full bg-purple-600 mt-2"></div>
                            <div>
                                <div class="text-sm font-medium capitalize"><?= htmlspecialchars($a['type'] ?? 'activity') ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($a['created_at'] ?? '') ?></div>
                                <div class="text-sm text-gray-700"><?= htmlspecialchars($a['data'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-4">
                <div class="font-semibold mb-2">Follow-up Scheduler</div>
                <div class="space-y-2">
                    <input type="datetime-local" x-model="follow" class="border rounded px-3 py-2 w-full">
                    <button class="px-3 py-2 bg-purple-600 text-white rounded w-full" @click="scheduleFollow()">Schedule</button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <div class="font-semibold mb-2">Demo Scheduling</div>
                <div class="space-y-2">
                    <input type="datetime-local" x-model="demo" class="border rounded px-3 py-2 w-full">
                    <button class="px-3 py-2 border border-purple-600 text-purple-600 rounded w-full" @click="scheduleDemo()">Schedule Demo</button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <div class="font-semibold mb-2">Payment Status</div>
                <?php foreach ($payments as $p): ?>
                    <div class="flex items-center justify-between border rounded p-3 mb-2">
                        <div>
                            <div class="text-sm">Amount: ₹<?= htmlspecialchars($p['amount'] ?? '0') ?></div>
                            <div class="text-xs text-gray-500">Status: <span class="px-2 py-0.5 rounded text-white <?= ($p['status'] ?? '')==='success'?'bg-green-600':'bg-yellow-600' ?>"><?= htmlspecialchars($p['status'] ?? '') ?></span></div>
                        </div>
                        <div class="flex gap-2">
                            <form action="/sales/payments/<?= (int)$p['id'] ?>/mark-paid" method="post"><button class="px-3 py-2 bg-green-600 text-white rounded">Mark Paid</button></form>
                            <form action="/sales/payments/<?= (int)$p['id'] ?>/generate-link" method="post"><button class="px-3 py-2 border border-purple-600 text-purple-600 rounded">Generate Link</button></form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function leadPage(){
    return {
        follow:'', demo:'',
        scheduleFollow(){ alert('Follow-up scheduled: '+this.follow); },
        scheduleDemo(){ alert('Demo scheduled: '+this.demo); }
    }
}
</script>
