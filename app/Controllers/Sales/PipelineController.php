<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SalesLead;

class PipelineController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $stages = ['new','contacted','follow_up','demo_done','payment_pending','converted','lost'];
        
        $userFilter = "";
        $params = [];
        if ($this->currentUser->role === 'sales_executive') {
            $userFilter = " AND l.assigned_to = :uid";
            $params['uid'] = $this->currentUser->id;
        }

        $columns = [];
        foreach ($stages as $s) {
            $p = $params;
            $p['s'] = $s;
            $columns[$s] = $db->fetchAll("SELECT l.* FROM sales_leads l WHERE l.stage = :s {$userFilter} ORDER BY l.updated_at DESC LIMIT 50", $p);
        }
        $response->view('sales/pipeline', [
            'title' => 'Customer Pipeline',
            'columns' => $columns,
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function move(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->post('id');
        $stage = (string)$request->post('stage');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $lead->fill(['stage' => $stage]);
        // $row = \App\Core\Database::getInstance()->fetchOne("SELECT id FROM sales_stages WHERE slug = :s OR name = :s LIMIT 1", ['s' => $stage]);
        // if ($row && isset($row['id'])) { $lead->stage_id = (int)$row['id']; }
        $lead->save();
        $act = new \App\Models\SalesActivity();
        $act->fill(['lead_id' => $id, 'user_id' => $this->currentUser->id ?? null, 'type' => 'status_change', 'new_stage_id' => null, 'title' => 'Stage moved', 'description' => 'Moved to ' . $stage]);
        $act->save();
        $response->json(['success' => true]);
    }
}
