<?php
$status = strtolower($payment['status'] ?? 'pending');
$statusLabel = strtoupper($status);
$statusColor = match($status) {
  'completed' => '#16a34a',
  'pending'   => '#f59e0b',
  'failed'    => '#dc2626',
  default     => '#64748b'
};

$amount   = (float)($payment['amount'] ?? 0);
$taxRate  = (float)($_ENV['TAX_RATE'] ?? 0.18);
$tax      = round($amount * $taxRate, 2);
$total    = $amount + $tax;
$created  = date('d M Y, h:i A', strtotime($payment['created_at'] ?? 'now'));
$billingCycle = ucfirst(strtolower((string)($payment['billing_cycle'] ?? ($subscription['billing_cycle'] ?? 'monthly'))));
$planName = (string)($plan->attributes['name'] ?? 'Subscription');
$invoiceNumber = (string)($payment['invoice_number'] ?? $invoice['invoice_number'] ?? ('INV-' . ($payment['id'] ?? '')));
$gatewayPaymentId = (string)($payment['gateway_payment_id'] ?? $payment['payment_id'] ?? '-');
$gatewayLabel = strtoupper((string)($payment['gateway'] ?? $payment['method'] ?? 'RAZORPAY'));
$pdfPath = (string)($invoice['pdf_path'] ?? '');

$addr = json_decode($employer->attributes['address'] ?? '', true) ?: [];
$street = $addr['street'] ?? '';
$city   = $addr['city'] ?? '';
$state  = $addr['state'] ?? '';
$pin    = $addr['postal_code'] ?? '';

$companyName  = $_ENV['COMPANY_NAME'] ?? 'Mindware Infotech';
$companyAddr  = $_ENV['COMPANY_ADDRESS'] ?? 'Mindware, S-4, Pankaj Plaza, Pocket-7, Plot-7,<br>Dwarka Sector-12, Delhi-110078';
$companyCity  = $_ENV['COMPANY_CITY'] ?? 'Dwarka';
$companyState = $_ENV['COMPANY_STATE'] ?? 'Delhi';
$companyZip   = $_ENV['COMPANY_ZIP'] ?? '110078';
$companyEmail = $_ENV['COMPANY_EMAIL'] ?? 'sales@mindwareinfotech.com';
$companyPhone = $_ENV['COMPANY_PHONE'] ?? '+91-8527522688';
$gst          = $_ENV['COMPANY_GSTIN'] ?? '07AFDPM9463K1ZY';

/* IMPORTANT → use PUBLIC URL not local disk path */
$logo = '/uploads/Mindware-infotech.png';
$qr   = '/uploads/qr.jpeg';
?>

<div class="p-6">
    <?php if ($status === 'completed'): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center text-green-800">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Payment successful. Your subscription is active.</span>
        </div>
        <div class="flex gap-4">
            <a href="/employer/billing/transactions" class="text-green-700 hover:text-green-900 font-medium underline">View Transactions</a>
            <a href="/employer/subscription/dashboard" class="text-green-700 hover:text-green-900 font-medium underline">Go to Dashboard</a>
        </div>
    </div>
    <?php elseif ($status === 'pending'): ?>
    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center text-yellow-800">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>Payment is pending. Redirecting to Transactions...</span>
        </div>
        <a href="/employer/billing/transactions" class="text-yellow-700 hover:text-yellow-900 font-medium underline">Click here if not redirected</a>
    </div>
    <script>setTimeout(function(){ window.location.href = '/employer/billing/transactions?pending=1'; }, 3000);</script>
    <?php endif; ?>

    <div id="invoiceWrap" class="flex justify-center bg-gray-50 py-10 rounded-xl">
        <div id="invoice" class="bg-white p-8 shadow-lg w-full max-w-3xl border-t-[8px] border-t-[#FF9933] border-b-[8px] border-b-[#138808] relative overflow-hidden">
            <!-- Saffron Top Border (Already handled by border-t) -->
            <!-- White middle implicitly handled by background -->
            <!-- Green Bottom Border (Already handled by border-b) -->
            
            <!-- HEADER -->
            <div class="flex justify-between items-start border-b-2 border-[#000080] pb-6 mb-8">
                <div class="flex gap-6">
                    <img src="<?= $logo ?>" class="h-16 object-contain">
                    <div class="text-xs leading-relaxed text-gray-700">
                        <b class="text-sm text-black"><?= $companyName ?></b><br>
                        <?= $companyAddr ?><br>
                        <?= $companyCity ?>, <?= $companyState ?> <?= $companyZip ?><br>
                        <?= $companyEmail ?> | <?= $companyPhone ?><br>
                        <span class="font-bold text-[#000080]">GST: <?= $gst ?></span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="inline-block border-2 border-[#000080] p-1 mb-4 rounded-md">
                        <img src="<?= $qr ?>" class="h-24">
                    </div>
                    <div>
                        <h1 class="text-3xl font-black mb-1 text-[#000080]">INVOICE</h1>
                        <span class="status-badge inline-block px-3 py-1 rounded text-white text-xs font-bold" style="background:<?= $statusColor ?>"><?= $statusLabel ?></span>
                    </div>
                </div>
            </div>

            <!-- META -->
            <div class="grid grid-cols-4 border border-[#000080] mb-8 text-xs bg-gray-50">
                <div class="p-3 border-r border-[#000080]"><b class="text-[#000080]">Invoice</b><br><?= htmlspecialchars($invoiceNumber) ?></div>
                <div class="p-3 border-r border-[#000080]"><b class="text-[#000080]">Date & Time</b><br><?= $created ?></div>
                <div class="p-3 border-r border-[#000080]"><b class="text-[#000080]">Payment ID</b><br><?= htmlspecialchars($gatewayPaymentId) ?></div>
                <div class="p-3"><b class="text-[#000080]">Gateway</b><br><?= htmlspecialchars($gatewayLabel) ?></div>
            </div>

            <!-- ADDRESS -->
            <div class="grid grid-cols-2 border border-[#000080] mb-8 text-xs">
                <div class="p-3 border-r border-[#000080] bg-orange-50/30">
                    <b class="text-sm text-[#FF9933]">FROM</b><br>
                    <span class="font-bold text-gray-800"><?= $companyName ?></span><br>
                    <?= $companyAddr ?><br>
                    <?= $companyCity ?>, <?= $companyState ?> <?= $companyZip ?><br>
                    <?= $companyEmail ?><br>
                    <?= $companyPhone ?><br>
                    <span class="font-bold text-[#000080]">GST: <?= $gst ?></span>
                </div>
                <div class="p-3 bg-green-50/30">
                    <b class="text-sm text-[#138808]">BILLED TO</b><br>
                    <span class="font-bold text-sm text-gray-800"><?= $employer->attributes['company_name'] ?></span><br>
                    <?= $street ?><br>
                    <?= $city ?>, <?= $state ?> <?= $pin ?><br>
                    <?= $employer->user()->attributes['email'] ?>
                </div>
            </div>

            <!-- TABLE -->
            <table class="w-full border-collapse border border-[#000080] mb-8 text-xs">
                <thead>
                    <tr class="bg-[#000080] text-white">
                        <th class="p-2 text-left border border-[#000080]">Description</th>
                        <th class="p-2 text-center border border-[#000080]">Cycle</th>
                        <th class="p-2 text-center border border-[#000080]">Qty</th>
                        <th class="p-2 text-right border border-[#000080]">Unit</th>
                        <th class="p-2 text-right border border-[#000080]">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border border-[#000080]">Subscription - <?= htmlspecialchars($planName) ?></td>
                        <td class="p-3 text-center border border-[#000080]"><?= htmlspecialchars($billingCycle) ?></td>
                        <td class="p-3 text-center border border-[#000080]">1</td>
                        <td class="p-3 text-right border border-[#000080]">₹<?= number_format($amount,2) ?></td>
                        <td class="p-3 text-right border border-[#000080]">₹<?= number_format($amount,2) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- TOTALS -->
            <div class="flex justify-end mb-8">
                <table class="w-1/2 border-collapse border border-[#000080] text-xs">
                    <tr><td class="p-2 border border-[#000080] font-medium text-gray-700">Subtotal</td><td class="p-2 border border-[#000080] text-right font-medium text-gray-900">₹<?= number_format($amount,2) ?></td></tr>
                    <tr><td class="p-2 border border-[#000080] text-gray-700">Tax (<?= $taxRate * 100 ?>%)</td><td class="p-2 border border-[#000080] text-right text-gray-900">₹<?= number_format($tax,2) ?></td></tr>
                    <tr class="bg-blue-50"><td class="p-2 border border-[#000080] font-bold text-[#000080]">GRAND TOTAL</td><td class="p-2 border border-[#000080] text-right font-bold text-sm text-[#000080]">₹<?= number_format($total,2) ?></td></tr>
                </table>
            </div>

            <!-- TERMS -->
            <div class="text-xs mb-8">
                <b class="text-sm">Terms & Conditions</b><br>
                <p class="mt-1 text-gray-600">Payment is due immediately on receipt of this invoice. Late payments may be charged as per applicable laws.</p>
            </div>

            <!-- FOOTER -->
            <div class="border-t border-black pt-4 flex justify-between items-center text-[10px] text-gray-500 italic">
                <div>
                    This is a system generated invoice. No signature required.<br>
                    Thank you for choosing <?= $companyName ?>.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-center gap-4 no-print">
        <?php if ($pdfPath !== ''): ?>
            <a href="<?= htmlspecialchars($pdfPath) ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold shadow-md transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4m4 5v1a2 2 0 01-2 2H6a2 2 0 01-2-2v-1"/></svg>
                Download PDF
            </a>
        <?php else: ?>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold shadow-md transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Invoice (PDF)
            </button>
        <?php endif; ?>
        <?php if ($status === 'completed'): ?>
            <a href="/employer/subscription/dashboard" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2 rounded-lg font-bold shadow-sm transition flex items-center">
                Go to Subscription
            </a>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    /* Hide EVERYTHING by default */
    * {
        visibility: hidden !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Show ONLY the invoice content and its parents */
    #invoiceWrap, #invoiceWrap * {
        visibility: visible !important;
    }

    /* Force invoice to top of page to avoid blank space */
    #invoiceWrap {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
        display: block !important;
    }

    #invoice {
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important; /* Removed unnecessary outer border */
    }

    /* Refine borders: use the original navy color but ensure visibility */
    .border, .border-t, .border-b, .border-l, .border-r, 
    .border-t-2, .border-b-2, .border-l-2, .border-r-2,
    [class*="border-"], 
    table, th, td {
        border-color: #000080 !important; /* Use navy for a professional look */
        border-style: solid !important;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th, td {
        border: 1px solid #000080 !important;
    }

    /* Remove extra pages and scrollbars */
    html, body {
        height: auto !important;
        overflow: visible !important;
        background: white !important;
    }

    .no-print, nav, aside, header, footer {
        display: none !important;
    }

    @page {
        size: A4;
        margin: 10mm;
    }

    /* Keep the status badge background but make it readable */
    .status-badge {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 1px solid #000 !important;
        color: #000 !important;
        font-weight: bold !important;
    }

    /* Section adjustments for paper */
    [class*="bg-"] {
        background-color: transparent !important;
    }
    
    .bg-white {
        background-color: #fff !important;
    }

    /* Ensure logo and QR are clear */
    img {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Ensure text is black for best printing */
    .text-gray-700, .text-gray-600, .text-gray-500 {
        color: #000 !important;
    }
}
</style>
