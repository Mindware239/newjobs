<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class InvoiceController extends BaseController
{
    public function download(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $payment = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id AND employer_id = :eid', [
            'id' => $id,
            'eid' => (int)$employer->id
        ]);
        if (!$payment) { $response->redirect('/employer/billing/transactions'); return; }

        $invoice = $db->fetchOne('SELECT * FROM invoices WHERE id = (SELECT invoice_id FROM employer_payments WHERE employer_id = :eid AND meta LIKE :like ORDER BY id DESC LIMIT 1)', [
            'eid' => (int)$employer->id,
            'like' => '%"subscription_payment_id":' . $id . '%'
        ]);

        $response->view('employer/invoices/show', [
            'title' => 'Invoice',
            'employer' => $employer,
            'invoice' => $invoice ?? [],
            'payment' => [
                'status' => ($invoice ? 'completed' : 'pending'),
                'method' => 'RAZORPAY',
                'amount' => (float)($payment['amount'] ?? 0)
            ]
        ], 200, 'employer/layout');
    }
}

