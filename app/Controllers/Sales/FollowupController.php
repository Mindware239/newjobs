<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class FollowupController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        
        // Get follow-ups for today and future
        $sql = "SELECT l.*, u.email as executive_email 
                FROM sales_leads l 
                LEFT JOIN users u ON u.id = l.assigned_to 
                WHERE l.next_followup_at IS NOT NULL 
                ORDER BY l.next_followup_at ASC 
                LIMIT 50";
        
        $followups = $db->fetchAll($sql);
        
        $response->view('sales/followups/index', [
            'title' => 'Follow-ups',
            'followups' => $followups,
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function schedule(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadId = (int)$request->param('id');
        $dt = (string)($request->post('followup_at') ?? date('Y-m-d H:i:s', time() + 86400));
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE sales_leads SET next_followup_at = :dt WHERE id = :id", ['dt' => $dt, 'id' => $leadId]);
        $response->json(['success' => true]);
    }

    public function markDone(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadId = (int)$request->param('id');
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE sales_leads SET next_followup_at = NULL WHERE id = :id", ['id' => $leadId]);
        $response->json(['success' => true]);
    }
}
