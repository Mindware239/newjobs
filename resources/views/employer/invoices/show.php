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

<?php if ($status === 'completed'): ?>
<div style="max-width:800px;margin:20px auto 0;background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px;border-radius:6px;font-family:Arial;">
  Payment successful. Your subscription is active.
  <a href="/employer/billing/transactions" style="margin-left:8px;color:#047857;text-decoration:underline;">View Transactions</a>
  <a href="/employer/subscription/dashboard" style="margin-left:12px;color:#047857;text-decoration:underline;">Go to Dashboard</a>
  </div>
<?php elseif ($status === 'pending'): ?>
<div style="max-width:800px;margin:20px auto 0;background:#fffbeb;border:1px solid #f59e0b;color:#92400e;padding:12px;border-radius:6px;font-family:Arial;">
  Payment is pending. Redirecting to Transactions...
  <a href="/employer/billing/transactions" style="margin-left:8px;color:#92400e;text-decoration:underline;">Click here if not redirected</a>
  </div>
<script>setTimeout(function(){ window.location.href = '/employer/billing/transactions?pending=1'; }, 3000);</script>
<?php endif; ?>

<div id="invoiceWrap">

<div id="invoice">

<!-- HEADER -->
<div class="header">
  <div class="left">
    <img src="<?= $logo ?>" class="logo">
    <div class="company">
      <b><?= $companyName ?></b><br>
      <?= $companyAddr ?><br>
      <?= $companyCity ?>, <?= $companyState ?> <?= $companyZip ?><br>
      <?= $companyEmail ?> | <?= $companyPhone ?><br>
      GST: <?= $gst ?>
    </div>
  </div>

  <div class="right">
    <div class="qr-box">
      <img src="<?= $qr ?>" class="qr-top">
    </div>

    <div>
      <h1>INVOICE</h1>
      <span class="status" style="background:<?= $statusColor ?>"><?= $statusLabel ?></span>
    </div>
  </div>
</div>


<!-- META -->
<table class="meta">
<tr>
  <td><b>Invoice</b><br><?= $payment['invoice_number'] ?? 'INV-'.$payment['id'] ?></td>
  <td><b>Date & Time</b><br><?= $created ?></td>
  <td><b>Payment ID</b><br><?= $payment['gateway_payment_id'] ?? '-' ?></td>
  <td><b>Gateway</b><br><?= strtoupper($payment['gateway'] ?? 'RAZORPAY') ?></td>
</tr>
</table>

<!-- ADDRESS -->
<table class="addr">
<tr>
<td>
  <b>FROM</b><br>
  <?= $companyName ?><br>
  <?= $companyAddr ?><br>
  <?= $companyCity ?>, <?= $companyState ?> <?= $companyZip ?><br>
  <?= $companyEmail ?><br>
  <?= $companyPhone ?><br>
  GST: <?= $gst ?>
</td>
<td>
  <b>BILLED TO</b><br>
  <?= $employer->attributes['company_name'] ?><br>
  <?= $street ?><br>
  <?= $city ?>, <?= $state ?> <?= $pin ?><br>
  <?= $employer->user()->attributes['email'] ?>
</td>
</tr>
</table>

<!-- TABLE -->
<table class="items">
<thead>
<tr>
  <th>Description</th>
  <th>Cycle</th>
  <th>Qty</th>
  <th>Unit</th>
  <th>Total</th>
</tr>
</thead>
<tbody>
<tr>
  <td>Subscription - <?= htmlspecialchars($plan->attributes['name']) ?></td>
  <td>Monthly</td>
  <td>1</td>
  <td>₹<?= number_format($amount,2) ?></td>
  <td>₹<?= number_format($amount,2) ?></td>
</tr>
</tbody>
</table>

<!-- TOTAL -->
<table class="summary">
<tr><td>Subtotal</td><td>₹<?= number_format($amount,2) ?></td></tr>
<tr><td>Tax (<?= $taxRate * 100 ?>%)</td><td>₹<?= number_format($tax,2) ?></td></tr>
<tr class="total"><td>GRAND TOTAL</td><td>₹<?= number_format($total,2) ?></td></tr>
</table>

<!-- TERMS -->
<div class="terms">
  <b>Terms & Conditions</b><br>
  Payment is due immediately on receipt of this invoice.<br>
  Late payments may be charged as per applicable laws.
</div>

<!-- FOOTER -->
<div class="foot">
  <!-- <img src="<?= $qr ?>" class="qr"> -->
  <div>
    This is a system generated invoice. No signature required.<br>
    Thank you for choosing <?= $companyName ?>.
  </div>
</div>

</div>

<div class="no-print">
  <button onclick="window.print()">Download Invoice (PDF)</button>
  <?php if ($status === 'pending'): ?>
    <a href="/employer/billing/transactions" style="margin-left:12px;color:#2563eb;text-decoration:underline;">Transactions</a>
  <?php endif; ?>
  <?php if ($status === 'completed'): ?>
    <a href="/employer/subscription/dashboard" style="margin-left:12px;color:#2563eb;text-decoration:underline;">Subscription</a>
  <?php endif; ?>
  
</div>

</div>

<style>
body { margin:0; background:#f0f0f0; font-family:Arial; }

#invoiceWrap { display:flex; justify-content:center; padding:20px; }

#invoice {
  width:800px;
  background:white;
  padding:25px;
  box-shadow:0 0 10px #aaa;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

/* HEADER */
.header {
  display:flex;
  justify-content:space-between;
  border-bottom:2px solid #000;
  padding-bottom:10px;
}
/* HEADER RIGHT SIDE QR */
.right {
  display:flex;
  align-items:center;
  gap:15px;
  text-align:right;
}

.qr-box {
  border:1px solid #000;
  padding:4px;
}

.qr-top {
  height:100px;
}

.logo { height:60px }
.company { font-size:12px }
.right { text-align:right }
.right h1 { margin:0 }
.status { color:white; padding:4px 10px; font-size:12px; border-radius:4px }

/* TABLES */
.meta, .addr, .items, .summary {
  width:100%;
  border-collapse:collapse;
  margin-top:12px;
}
.meta td, .addr td {
  border:1px solid #000;
  padding:6px;
  font-size:12px;
}
.items th {
  background:#1e40af;
  color:white;
  padding:6px;
}
.items td {
  border:1px solid #000;
  padding:6px;
  text-align:right;
}
.items td:first-child { text-align:left }

/* TOTAL */
.summary {
  width:40%;
  float:right;
}
.summary td {
  border:1px solid #000;
  padding:6px;
}
.summary .total {
  background:#e0f2fe;
  font-weight:bold;
}

/* FOOTER */
.terms { margin-top:15px; font-size:12px }
.foot {
  margin-top:20px;
  font-size:11px;
  border-top:1px solid #000;
  padding-top:8px;
  display:flex;
  justify-content:space-between;
}
.qr { height:80px }

/* BUTTON */
.no-print {
  text-align:right;
  margin:15px;
}
button {
  background:#2563eb;
  color:white;
  border:none;
  padding:8px 18px;
  font-size:14px;
  border-radius:4px;
}

/* ✅ PRINT FIX */
@media print {

  body {
    background:white !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  body * {
    visibility: hidden;
  }

  #invoiceWrap, #invoiceWrap * {
    visibility: visible;
  }

  #invoiceWrap {
    position:absolute;
    left:0;
    top:0;
    width:100%;
  }

  .no-print {
    display:none !important;
  }

  table, .terms, .foot {
    page-break-inside: avoid;
  }

  @page {
    size: A4;
    margin:10mm;
  }
}
</style>
