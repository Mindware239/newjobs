<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class TeamController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $executives = $db->fetchAll("SELECT id, name, email, role FROM sales_users WHERE is_active = 1 ORDER BY role, name");
        $kpis = [];
        foreach ($executives as $e) {
            $uid = (int)$e['id'];
            $kpis[$uid] = [
                'leads' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u", ['u' => $uid])['c'] ?? 0),
                'conversions' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage='converted'", ['u' => $uid])['c'] ?? 0),
                'pending_followups' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND next_followup_at IS NOT NULL AND DATE(next_followup_at) >= CURDATE()", ['u' => $uid])['c'] ?? 0)
            ];
        }
        $response->view('sales/team/index', [
            'title' => 'Team',
            'executives' => $executives,
            'kpis' => $kpis
        ], 200, 'sales_manager/layout');
    }
}

