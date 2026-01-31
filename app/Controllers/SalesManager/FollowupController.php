<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class FollowupController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $rows = $db->fetchAll("SELECT f.*, l.company_name FROM sales_followups f LEFT JOIN sales_leads l ON l.id = f.lead_id ORDER BY f.follow_up_at ASC LIMIT 200");
        $response->view('sales_manager/followups/index', [
            'title' => 'Follow-ups',
            'items' => $rows
        ], 200, 'sales_manager/layout');
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $status = (string)$request->post('status', 'done');
        \App\Core\Database::getInstance()->query('UPDATE sales_followups SET status = :s, updated_at = NOW() WHERE id = :id', ['s' => $status, 'id' => $id]);
        $response->redirect('/sales-manager/followups');
    }
}

