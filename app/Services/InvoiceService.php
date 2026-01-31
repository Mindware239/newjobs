<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Dompdf\Dompdf;

class InvoiceService
{
    public static function generate(int $employerId, float $amount, string $currency = 'INR', array $meta = []): int
    {
        $db = Database::getInstance();
        $taxRate = (float)($_ENV['TAX_RATE'] ?? 0.18);
        $tax = round($amount * $taxRate, 2);
        $total = $amount + $tax;

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . substr(uniqid('', true), -6);
        $db->query('INSERT INTO invoices (invoice_number, employer_id, plan_id, subscription_id, subtotal, tax, total, status, issued_at, due_date, pdf_path)
                    VALUES (:num, :eid, :plan, :sub, :subtot, :tax, :total, :status, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), :pdf)', [
            'num' => $invoiceNumber,
            'eid' => $employerId,
            'plan' => $meta['plan_id'] ?? null,
            'sub' => $meta['subscription_id'] ?? null,
            'subtot' => $amount,
            'tax' => $tax,
            'total' => $total,
            'status' => 'paid',
            'pdf' => ''
        ]);
        $invoiceId = (int)$db->lastInsertId();

        $company = $_ENV['COMPANY_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Service Provider');
        $html = '<html><head><style>body{font-family:Arial} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ddd;padding:8px} h1{margin-bottom:10px}</style></head><body>' .
                '<h1>Invoice</h1>' .
                '<p><strong>Invoice #:</strong> ' . $invoiceNumber . '</p>' .
                '<p><strong>Date:</strong> ' . date('M d, Y') . '</p>' .
                '<p><strong>From:</strong> ' . htmlspecialchars($company) . '</p>' .
                '<table><thead><tr><th>Description</th><th>Amount</th></tr></thead><tbody>' .
                '<tr><td>Subscription Payment</td><td>₹' . number_format($amount, 2) . '</td></tr>' .
                '</tbody></table>' .
                '<p><strong>Tax:</strong> ₹' . number_format($tax, 2) . '</p>' .
                '<p><strong>Total:</strong> ₹' . number_format($total, 2) . '</p>' .
                '</body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $dir = __DIR__ . '/../../public/storage/uploads/employers/' . $employerId;
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $pdfPath = $dir . '/' . $invoiceNumber . '.pdf';
        file_put_contents($pdfPath, $pdfOutput);

        $relPath = '/public/storage/uploads/employers/' . $employerId . '/' . $invoiceNumber . '.pdf';
        $db->query('UPDATE invoices SET pdf_path = :pdf WHERE id = :id', ['pdf' => $relPath, 'id' => $invoiceId]);

        return $invoiceId;
    }
}

