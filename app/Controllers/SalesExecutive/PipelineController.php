<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class PipelineController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);
        $stages = ['new','contacted','follow_up','demo_done','payment_pending','converted','lost'];
        $columns = [];
        foreach ($stages as $s) {
            $columns[$s] = $db->fetchAll("SELECT l.* FROM sales_leads l WHERE l.stage = :s AND l.assigned_to = :u ORDER BY l.updated_at DESC LIMIT 50", ['s' => $s, 'u' => $userId]);
        }
        $response->view('sales/pipeline', [
            'title' => 'My Pipeline',
            'columns' => $columns
        ], 200, 'masteradmin/layout');
    }
}

