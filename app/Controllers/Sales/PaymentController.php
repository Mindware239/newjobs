<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SalesPayment;

class PaymentController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $pending = $db->fetchAll("SELECT p.*, l.company_name FROM sales_payments p LEFT JOIN sales_leads l ON l.id = p.lead_id WHERE p.status='pending' ORDER BY p.created_at DESC LIMIT 100");
        $history = $db->fetchAll("SELECT p.*, l.company_name FROM sales_payments p LEFT JOIN sales_leads l ON l.id = p.lead_id WHERE p.status='success' ORDER BY p.updated_at DESC LIMIT 100");
        $response->view('sales/payments/index', [
            'title' => 'Payments',
            'pending' => $pending,
            'history' => $history
        ], 200, 'sales/layout');
    }

    public function markPaid(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $payment = SalesPayment::find($id);
        if (!$payment) { $response->json(['error' => 'Payment not found'], 404); return; }
        $payment->fill(['status' => 'success', 'paid_at' => date('Y-m-d H:i:s')]);
        $payment->save();
        $response->json(['success' => true]);
    }

    public function generateLink(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $payment = SalesPayment::find($id);
        if (!$payment) { $response->json(['error' => 'Payment not found'], 404); return; }
        $payment->fill(['payment_link' => '/pay/' . $id]);
        $payment->save();
        $response->json(['success' => true, 'link' => $payment->payment_link]);
    }
}
