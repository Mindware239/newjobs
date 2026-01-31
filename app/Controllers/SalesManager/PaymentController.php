<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class PaymentController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $pending = $db->fetchAll("SELECT p.*, l.company_name FROM sales_payments p LEFT JOIN sales_leads l ON l.id = p.lead_id WHERE p.status='pending' ORDER BY p.created_at DESC LIMIT 100");
        $history = $db->fetchAll("SELECT p.*, l.company_name FROM sales_payments p LEFT JOIN sales_leads l ON l.id = p.lead_id WHERE p.status='paid' ORDER BY p.updated_at DESC LIMIT 100");
        $response->view('sales/payments/index', [
            'title' => 'Payments',
            'pending' => $pending,
            'history' => $history
        ], 200, 'sales_manager/layout');
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $status = (string)$request->post('status', 'paid');
        \App\Core\Database::getInstance()->query('UPDATE sales_payments SET status = :s, updated_at = NOW() WHERE id = :id', ['s' => $status, 'id' => $id]);
        $response->redirect('/sales-manager/payments');
    }
}

