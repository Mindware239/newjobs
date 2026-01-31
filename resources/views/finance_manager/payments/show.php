<?php
// Helper for status badge
$status = !empty($payment['status']) ? $payment['status'] : 'pending';
$statusColors = [
    'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'failed' => 'bg-red-100 text-red-800 border-red-200',
    'refunded' => 'bg-gray-100 text-gray-800 border-gray-200',
];
$statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';

// Helper for meta data
$meta = $payment['meta'] ?? '';
$metaData = json_decode($meta, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $metaData = [];
}

// Helper for Initials
$companyName = $payment['company_name'] ?? 'Unknown Company';
$initials = strtoupper(substr($companyName, 0, 2));

// Address string
$rawAddress = $payment['address'] ?? '';
$streetAddress = $rawAddress;

// Try to decode if it looks like JSON
if (strpos($rawAddress, '{') === 0) {
    $decodedAddress = json_decode($rawAddress, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedAddress)) {
        // Extract street or combine available parts if street is missing
        $streetAddress = $decodedAddress['street'] ?? $decodedAddress['line1'] ?? $decodedAddress['address'] ?? '';
    }
}

$addressParts = [
    $streetAddress,
    $payment['city'] ?? '',
    $payment['state'] ?? '',
    $payment['postal_code'] ?? '',
    $payment['country'] ?? ''
];
$address = implode(', ', array_filter($addressParts, fn($part) => !empty($part) && is_string($part)));
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/finance/payments" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Payments
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Details</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center gap-3">
                Payment #<?= $payment['id'] ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium border <?= $statusColor ?>">
                    <?= ucfirst($status) ?>
                </span>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Transaction recorded on <?= date('F j, Y \a\t g:i A', strtotime($payment['created_at'])) ?>
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <?php if ($status === 'pending'): ?>
            <form method="POST" action="/finance/payments/approve" onsubmit="return confirm('Are you sure you want to approve this payment manually?');">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$payment['id'] ?>">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Approve Payment
                </button>
            </form>
            <?php endif; ?>
            
            <?php if ($status === 'completed' || $status === 'paid'): ?>
            <button onclick="document.getElementById('refund-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                Issue Refund
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Transaction Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Amount Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="text-sm text-gray-500 mb-1">Total Amount</div>
                            <div class="text-3xl font-bold text-gray-900">
                                <?= $payment['currency'] ?? 'INR' ?> <?= number_format((float)$payment['amount'], 2) ?>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Gateway Transaction ID</div>
                                <div class="text-sm font-mono bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block mt-1">
                                    <?= htmlspecialchars($payment['txn_id'] ?? 'N/A') ?>
                                </div>
                            </div>
                            <div class="flex gap-8">
                                <div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">Gateway</div>
                                    <div class="font-medium mt-1 capitalize"><?= htmlspecialchars($payment['gateway'] ?? 'Unknown') ?></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">Method</div>
                                    <div class="font-medium mt-1 capitalize"><?= htmlspecialchars($payment['payment_method'] ?? 'Unknown') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($metaData)): ?>
                <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Additional Metadata</h4>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                        <?php foreach ($metaData as $key => $value): ?>
                        <div class="sm:col-span-1">
                            <dt class="text-xs font-medium text-gray-500 uppercase"><?= str_replace('_', ' ', $key) ?></dt>
                            <dd class="mt-1 text-sm text-gray-900 break-words">
                                <?php if(is_array($value) || is_object($value)): ?>
                                    <pre class="text-xs bg-white p-2 rounded border"><?= json_encode($value, JSON_PRETTY_PRINT) ?></pre>
                                <?php else: ?>
                                    <?= htmlspecialchars((string)$value) ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php endif; ?>
            </div>

            <!-- Timeline/Log (Placeholder for future) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Timeline</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Payment initiated</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="<?= $payment['created_at'] ?>"><?= date('M j, Y H:i', strtotime($payment['created_at'])) ?></time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- If we had update time or status change logs, they would go here -->
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full <?= $status === 'completed' ? 'bg-emerald-500' : 'bg-gray-400' ?> flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Current Status: <?= ucfirst($status) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column: Payer Details -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payer Information</h3>
                
                <div class="flex items-center mb-6">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg mr-4">
                        <?= $initials ?>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-900"><?= htmlspecialchars($companyName) ?></h4>
                        <p class="text-sm text-gray-500">Employer Account</p>
                    </div>
                </div>

                <div class="space-y-4 border-t border-gray-100 pt-4">
                    <div>
                        <div class="text-xs text-gray-500 uppercase mb-1">Contact Person</div>
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($payment['employer_name'] ?? 'N/A') ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-gray-500 uppercase mb-1">Email Address</div>
                        <div class="flex items-center text-sm text-gray-900">
                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <?= htmlspecialchars($payment['employer_email'] ?? 'N/A') ?>
                        </div>
                    </div>

                    <?php if (!empty($payment['employer_phone'])): ?>
                    <div>
                        <div class="text-xs text-gray-500 uppercase mb-1">Phone</div>
                        <div class="flex items-center text-sm text-gray-900">
                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <?= htmlspecialchars($payment['employer_phone']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($address)): ?>
                    <div>
                        <div class="text-xs text-gray-500 uppercase mb-1">Billing Address</div>
                        <div class="flex items-start text-sm text-gray-900">
                            <svg class="h-4 w-4 text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span><?= htmlspecialchars($address) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Support/Help Card -->
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">Need to contact the employer?</h4>
                <p class="text-sm text-blue-700 mb-4">Use the contact details above to resolve any payment discrepancies.</p>
                <a href="mailto:<?= htmlspecialchars($payment['employer_email'] ?? '') ?>" class="text-sm font-medium text-blue-600 hover:text-blue-500">Send Email &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div id="refund-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Issue Refund</h3>
            <form method="POST" action="/finance/payments/refund">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)$payment['id'] ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Refund</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" step="0.01" name="amount" max="<?= $payment['amount'] ?>" value="<?= htmlspecialchars((string)$payment['amount']) ?>" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <input type="text" name="reason" required class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="e.g. Requested by customer">
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="document.getElementById('refund-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
