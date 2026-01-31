<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class FollowupController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $uid = (int)($this->currentUser->id ?? 0);
        $rows = $db->fetchAll("SELECT f.*, l.company_name FROM sales_followups f LEFT JOIN sales_leads l ON l.id = f.lead_id WHERE f.user_id = :u ORDER BY f.follow_up_at ASC LIMIT 200", ['u' => $uid]);
        $response->view('sales_executive/followups/index', [
            'title' => 'My Follow-ups',
            'items' => $rows
        ], 200, 'masteradmin/layout');
    }
}

