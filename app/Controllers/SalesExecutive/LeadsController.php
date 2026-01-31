<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class LeadsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);
        $status = (string)$request->get('stage', 'all');
        $where = ['assigned_to = :uid'];
        $params = ['uid' => $userId];
        if ($status !== 'all') { $where[] = 'stage = :stage'; $params['stage'] = $status; }
        $sql = 'SELECT * FROM sales_leads WHERE ' . implode(' AND ', $where) . ' ORDER BY updated_at DESC LIMIT 100';
        try { $leads = $db->fetchAll($sql, $params); } catch (\Throwable $t) { $leads = []; }
        $response->view('sales_executive/leads/index', [
            'title' => 'My Leads',
            'leads' => $leads,
            'stage' => $status
        ], 200, 'masteradmin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $stage = (string)$request->post('stage', 'contacted');
        $notes = (string)$request->post('notes', '');
        $allowed = ['new','contacted','demo_done','follow_up','payment_pending','converted','lost'];
        if (!in_array($stage, $allowed, true)) { $stage = 'contacted'; }
        try {
            $db->query('UPDATE sales_leads SET stage = :stage, notes = :notes, updated_at = NOW() WHERE id = :id', ['stage' => $stage, 'notes' => $notes, 'id' => $id]);
        } catch (\Throwable $t) {}
        $response->redirect('/sales-executive/leads');
    }
}
