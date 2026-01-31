<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class AnalyticsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $response->view('sales/analytics/index', [
            'title' => 'Sales Analytics',
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function leadTrends(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $days = (int)($request->get('days') ?? 14);
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT DATE(created_at) as d, COUNT(*) as c FROM sales_leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY d ORDER BY d ASC";
        $rows = $db->fetchAll($sql, ['days' => $days]);
        $response->json(['days' => $days, 'points' => $rows]);
    }

    public function teamPerformance(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT su.id, COALESCE(su.name, su.email) as name, COALESCE(COUNT(l.id),0) as leads, COALESCE(SUM(l.stage='converted'),0) as conversions FROM users su LEFT JOIN sales_leads l ON l.assigned_to = su.id AND l.is_archived = 0 WHERE su.role IN ('sales_executive', 'sales_manager') AND su.status = 'active' GROUP BY su.id ORDER BY leads DESC";
        $rows = $db->fetchAll($sql);
        $response->json(['team' => $rows]);
    }

    public function stageBreakdown(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $rows = $db->fetchAll("SELECT stage, COUNT(*) as c FROM sales_leads WHERE is_archived = 0 GROUP BY stage ORDER BY c DESC");
        $response->json(['stages' => $rows]);
    }

    public function sourceBreakdown(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $rows = $db->fetchAll("SELECT source, COUNT(*) as c FROM sales_leads WHERE is_archived = 0 GROUP BY source ORDER BY c DESC");
        $response->json(['sources' => $rows]);
    }

    public function conversionRate(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $days = (int)($request->get('days') ?? 14);
        $db = \App\Core\Database::getInstance();
        $created = $db->fetchAll("SELECT DATE(created_at) as d, COUNT(*) as c FROM sales_leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY d ORDER BY d ASC", ['days' => $days]);
        $converted = $db->fetchAll("SELECT DATE(updated_at) as d, COUNT(*) as c FROM sales_leads WHERE stage='converted' AND updated_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY d ORDER BY d ASC", ['days' => $days]);
        $response->json(['days' => $days, 'created' => $created, 'converted' => $converted]);
    }
}
