<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class PipelineController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $stages = ['new','contacted','follow_up','demo_done','payment_pending','converted','lost'];
        $columns = [];
        foreach ($stages as $s) {
            $columns[$s] = $db->fetchAll("SELECT l.* FROM sales_leads l WHERE l.stage = :s ORDER BY l.updated_at DESC LIMIT 50", ['s' => $s]);
        }
        $response->view('sales/pipeline', [
            'title' => 'Pipeline',
            'columns' => $columns
        ], 200, 'sales_manager/layout');
    }

    public function updateStage(Request $request, Response $response): void
    {
        $leadId = (int)$request->post('lead_id');
        $stage = (string)$request->post('stage');
        \App\Core\Database::getInstance()->query('UPDATE sales_leads SET stage = :s, updated_at = NOW() WHERE id = :id', ['s' => $stage, 'id' => $leadId]);
        $response->json(['success' => true]);
    }
}

