<div>
  <h1 class="text-2xl font-semibold mb-4">Ticket #<?= (int)($ticket['id'] ?? 0) ?></h1>
  <div class="bg-white rounded shadow p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <div class="text-sm text-gray-600">Subject</div>
        <div class="font-semibold"><?= htmlspecialchars($ticket['subject'] ?? '') ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Status</div>
        <div><span class="px-2 py-1 text-xs rounded bg-gray-100"><?= htmlspecialchars($ticket['status'] ?? 'open') ?></span></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Priority</div>
        <div class="font-semibold"><?= htmlspecialchars($ticket['priority'] ?? 'medium') ?></div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded shadow p-4 mb-4">
    <h2 class="text-lg font-semibold mb-3">Messages</h2>
    <div class="space-y-3">
      <?php foreach ($messages as $m): ?>
        <div class="border rounded p-3">
          <div class="text-xs text-gray-500">By #<?= (int)$m['sender_user_id'] ?> • <?= htmlspecialchars($m['created_at'] ?? '') ?></div>
          <div class="mt-1 text-gray-800"><?= nl2br(htmlspecialchars($m['body'] ?? '')) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
        <div class="text-gray-500">No messages yet</div>
      <?php endif; ?>
    </div>
    <form method="POST" action="/support-exec/tickets/reply" class="mt-4">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <input type="hidden" name="id" value="<?= (int)($ticket['id'] ?? 0) ?>">
      <textarea name="body" rows="3" class="w-full px-3 py-2 border rounded" placeholder="Write a reply..."></textarea>
      <div class="mt-2 flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Send Reply</button>
        <button formaction="/support-exec/tickets/assign" class="px-4 py-2 bg-yellow-600 text-white rounded">Assign to Me</button>
        <button formaction="/support-exec/tickets/close" class="px-4 py-2 bg-green-600 text-white rounded">Close Ticket</button>
        <button formaction="/support-exec/tickets/escalate" class="px-4 py-2 bg-red-600 text-white rounded">Escalate</button>
      </div>
    </form>
  </div>
</div>

