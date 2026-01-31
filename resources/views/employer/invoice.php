<?php ?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Invoice</h1>
        <a href="/employer/payments" class="text-purple-600 hover:text-purple-800">Back to Payments</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">Billing To</h2>
                <p class="text-gray-900 font-medium"><?= htmlspecialchars($employer->company_name ?? 'Company') ?></p>
                <p class="text-gray-600"><?= htmlspecialchars($employer->address ?? '') ?></p>
                <p class="text-gray-600"><?= htmlspecialchars(trim(($employer->city ?? '') . ', ' . ($employer->state ?? '') . ', ' . ($employer->country ?? ''), ', ')) ?></p>
                <p class="text-gray-600">Email: <?= htmlspecialchars($employer->billing_email ?? $employer->user()->email ?? '') ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-semibold mb-2">Invoice Details</h2>
                <p class="text-gray-600">Invoice No: <span class="font-semibold"><?= htmlspecialchars($payment['invoice_number'] ?? ('INV-' . ($payment['id'] ?? ''))) ?></span></p>
                <p class="text-gray-600">Date: <span class="font-semibold"><?= date('M d, Y', strtotime($payment['created_at'] ?? 'now')) ?></span></p>
                <p class="text-gray-600">Status: <span class="font-semibold"><?= ucfirst($payment['status'] ?? 'pending') ?></span></p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Summary</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Plan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cycle</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-2 text-sm">Subscription</td>
                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($plan['name'] ?? 'Plan') ?></td>
                            <td class="px-4 py-2 text-sm"><?= ucfirst($payment['billing_cycle'] ?? 'monthly') ?></td>
                            <td class="px-4 py-2 text-right font-semibold">₹<?= number_format((float)($payment['amount'] ?? 0), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600">Subtotal</p>
            </div>
            <div class="text-right">
                <p class="font-semibold">₹<?= number_format((float)($payment['amount'] ?? 0), 2) ?></p>
            </div>
            <div>
                <p class="text-gray-600">GST (18%)</p>
            </div>
            <div class="text-right">
                <?php $gst = round(((float)($payment['amount'] ?? 0)) * 0.18, 2); ?>
                <p class="font-semibold">₹<?= number_format($gst, 2) ?></p>
            </div>
            <div>
                <p class="text-gray-600">Total</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold">₹<?= number_format(((float)($payment['amount'] ?? 0)) + $gst, 2) ?></p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <?php if (!empty($payment['invoice_url'])): ?>
            <a href="<?= htmlspecialchars($payment['invoice_url']) ?>" target="_blank" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md">Download PDF</a>
            <?php endif; ?>
            <button onclick="window.print()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Print</button>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-3">Update Billing Information</h3>
        <form method="POST" action="/employer/payments/billing-info" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="company_name" placeholder="Company Name" value="<?= htmlspecialchars($employer->company_name ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <input type="email" name="billing_email" placeholder="Billing Email" value="<?= htmlspecialchars($employer->billing_email ?? $employer->user()->email ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <input type="text" name="billing_address" placeholder="Address" value="<?= htmlspecialchars($employer->address ?? '') ?>" class="px-3 py-2 border rounded-md col-span-1 md:col-span-2" />
            <input type="text" name="billing_city" placeholder="City" value="<?= htmlspecialchars($employer->city ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <input type="text" name="billing_state" placeholder="State" value="<?= htmlspecialchars($employer->state ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <input type="text" name="billing_postcode" placeholder="Postcode" value="<?= htmlspecialchars($employer->postcode ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <input type="text" name="billing_country" placeholder="Country" value="<?= htmlspecialchars($employer->country ?? '') ?>" class="px-3 py-2 border rounded-md" />
            <div class="md:col-span-2 text-right">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Save Billing Info</button>
            </div>
        </form>
    </div>
</div>
