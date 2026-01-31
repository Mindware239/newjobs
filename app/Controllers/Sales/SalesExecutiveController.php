<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class SalesExecutiveController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        // Allow manager to view executive dashboard? Maybe. For now, strict.
        if (!in_array($this->currentUser->role, ['sales_executive', 'sales_manager', 'super_admin'])) {
            $response->redirect('/');
            return;
        }
        $db = \App\Core\Database::getInstance();
        $userId = $this->currentUser->id ?? 0;
        $kpis = [
            'my_leads' => 0,
            'today_followups' => 0,
            'demo_scheduled' => 0,
            'payments_pending' => 0,
            'conversions' => 0
        ];
        try {
            $kpis['my_leads'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u", ['u' => $userId])['c'] ?? 0);
            $kpis['today_followups'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND DATE(next_followup_at) = CURDATE()", ['u' => $userId])['c'] ?? 0);
            $kpis['demo_scheduled'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'demo_done'", ['u' => $userId])['c'] ?? 0);
            $kpis['payments_pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'payment_pending'", ['u' => $userId])['c'] ?? 0);
            $kpis['conversions'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'converted'", ['u' => $userId])['c'] ?? 0);
        } catch (\Throwable $t) {}

        $rows = $db->fetchAll(
            "SELECT l.* FROM sales_leads l WHERE l.assigned_to = :u ORDER BY l.updated_at DESC LIMIT 50",
            ['u' => $userId]
        );

        $response->view('sales/executive/dashboard', [
            'title' => 'Sales Executive Dashboard',
            'user' => $this->currentUser,
            'kpis' => $kpis,
            'leads' => $rows
        ], 200, 'sales/layout');
    }
}

