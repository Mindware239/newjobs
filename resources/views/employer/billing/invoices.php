<h1 class="text-3xl font-bold text-gray-900 mb-6">Invoices</h1>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>" class="px-3 py-2 border rounded-md" />
        <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>" class="px-3 py-2 border rounded-md" />
        <select name="status" class="px-3 py-2 border rounded-md">
            <?php $statuses = ['all','completed','failed','pending','refunded']; ?>
            <?php foreach ($statuses as $st): ?>
                <option value="<?= $st ?>" <?= (($filters['status'] ?? 'all') === $st ? 'selected' : '') ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
    </form>
    <div class="mt-3 text-sm text-gray-600">
        Tip: Click an invoice to view details and download.
    </div>
    <div class="mt-4">
        <a href="/employer/billing/overview" class="text-indigo-600 hover:text-indigo-800">Back to Billing Overview</a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Invoice #</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Billing Cycle</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td class="px-4 py-2 text-sm"><?= htmlspecialchars($inv['invoice_number'] ?? ('INV-' . (int)($inv['id'] ?? 0))) ?></td>
                    <td class="px-4 py-2 text-sm"><?= date('M d, Y', strtotime($inv['created_at'] ?? 'now')) ?></td>
                    <td class="px-4 py-2 text-sm"><?= ucfirst($inv['billing_cycle'] ?? 'monthly') ?></td>
                    <td class="px-4 py-2 text-sm font-semibold">₹<?= number_format((float)($inv['amount'] ?? 0), 2) ?></td>
                    <td class="px-4 py-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            <?= ucfirst($inv['status'] ?? 'pending') ?>
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="/employer/invoices/<?= (int)$inv['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        <?php if (!empty($inv['invoice_url'])): ?>
                        <a href="<?= htmlspecialchars($inv['invoice_url']) ?>" target="_blank" class="ml-3 text-gray-600 hover:text-gray-800 text-sm">Download</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
