<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);

        $stats = ['assigned' => 0, 'new' => 0, 'contacted' => 0, 'converted' => 0];
        try {
            $stats['assigned'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM sales_leads WHERE assigned_to = :uid", ['uid' => $userId])['c'] ?? 0);
            $stats['new'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM sales_leads WHERE assigned_to = :uid AND stage = 'new'", ['uid' => $userId])['c'] ?? 0);
            $stats['contacted'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM sales_leads WHERE assigned_to = :uid AND stage = 'contacted'", ['uid' => $userId])['c'] ?? 0);
            $stats['converted'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM sales_leads WHERE assigned_to = :uid AND stage = 'converted'", ['uid' => $userId])['c'] ?? 0);
        } catch (\Throwable $t) {
            $stats = ['assigned' => 0, 'new' => 0, 'contacted' => 0, 'converted' => 0];
        }

        $response->view('sales_executive/dashboard', [
            'title' => 'Sales Executive Dashboard',
            'stats' => $stats
        ], 200, 'masteradmin/layout');
    }
}
