<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $managerId = (int)($this->currentUser->id ?? 0);
        $whereManager = $managerId > 0 ? ' WHERE (manager_id = :mid OR manager_id IS NULL)' : '';
        $paramsManager = $managerId > 0 ? ['mid' => $managerId] : [];

        $stats = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'demo_done' => 0,
            'follow_up' => 0,
            'payment_pending' => 0,
            'converted' => 0,
            'lost' => 0,
        ];
        try {
            $rows = $db->fetchAll('SELECT stage, COUNT(*) as c FROM sales_leads' . ($whereManager ? $whereManager . ' ' : ' ') . 'GROUP BY stage', $paramsManager);
            foreach ($rows as $r) { $stats[$r['stage']] = (int)$r['c']; $stats['total'] += (int)$r['c']; }
        } catch (\Throwable $t) {}

        $leads = [];
        try {
            $leadSql = '
                SELECT sl.*, COALESCE(u.name, u.email) as executive_name
                FROM sales_leads sl
                LEFT JOIN users u ON u.id = sl.assigned_to
            ' . ($whereManager ? $whereManager : '') . '
                ORDER BY sl.updated_at DESC
                LIMIT 100
            ';
            $leads = $db->fetchAll($leadSql, $paramsManager);
        } catch (\Throwable $t) {}

        $execs = [];
        try {
            $execs = $db->fetchAll('SELECT u.id, u.email FROM users u INNER JOIN role_user ru ON ru.user_id = u.id INNER JOIN roles r ON r.id = ru.role_id WHERE r.slug = "sales_executive"');
        } catch (\Throwable $t) {}

        $pipeline = [
            'proposal' => [],
            'negotiation' => [],
            'closing' => []
        ];
        try {
            $proposalSql = "
                SELECT id, company_name, contact_name, deal_value, updated_at
                FROM sales_leads
                " . ($managerId > 0 ? " WHERE (manager_id = :mid OR manager_id IS NULL) " : " WHERE 1=1 ") . "
                  AND stage IN ('contacted','demo_done')
                ORDER BY updated_at DESC
                LIMIT 10
            ";
            $negotiationSql = "
                SELECT id, company_name, contact_name, deal_value, updated_at
                FROM sales_leads
                " . ($managerId > 0 ? " WHERE (manager_id = :mid OR manager_id IS NULL) " : " WHERE 1=1 ") . "
                  AND stage = 'follow_up'
                ORDER BY updated_at DESC
                LIMIT 10
            ";
            $closingSql = "
                SELECT id, company_name, contact_name, deal_value, updated_at
                FROM sales_leads
                " . ($managerId > 0 ? " WHERE (manager_id = :mid OR manager_id IS NULL) " : " WHERE 1=1 ") . "
                  AND stage = 'payment_pending'
                ORDER BY updated_at DESC
                LIMIT 10
            ";
            $pipeline['proposal'] = $db->fetchAll($proposalSql, $paramsManager);
            $pipeline['negotiation'] = $db->fetchAll($negotiationSql, $paramsManager);
            $pipeline['closing'] = $db->fetchAll($closingSql, $paramsManager);
        } catch (\Throwable $t) {}

        $charts = [
            'funnel' => [],
            'sources' => [],
            'revenue_trend' => []
        ];
        try {
            $funnelData = $db->fetchAll('SELECT stage, COUNT(*) as count FROM sales_leads' . ($whereManager ? $whereManager . ' ' : ' ') . 'GROUP BY stage', $paramsManager);
            $stageOrder = ['new','contacted','demo_done','follow_up','payment_pending','converted','lost'];
            foreach ($stageOrder as $st) {
                $charts['funnel'][$st] = 0;
            }
            foreach ($funnelData as $row) {
                $st = (string)$row['stage'];
                $charts['funnel'][$st] = (int)$row['count'];
            }
        } catch (\Throwable $t) {}
        try {
            $srcData = $db->fetchAll('SELECT source, COUNT(*) as count FROM sales_leads' . ($whereManager ? $whereManager . ' ' : ' ') . 'GROUP BY source', $paramsManager);
            $charts['sources'] = array_map(function($r){ return ['source'=>$r['source'] ?? 'unknown','count'=>(int)$r['count']]; }, $srcData);
        } catch (\Throwable $t) {}
        try {
            $revSql = "
                SELECT DATE(p.updated_at) as d, SUM(p.amount) as total
                FROM sales_payments p
                INNER JOIN sales_leads l ON l.id = p.lead_id
                WHERE p.status = 'paid'
                  AND p.updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                GROUP BY DATE(p.updated_at)
                ORDER BY d ASC
            ";
            $charts['revenue_trend'] = $db->fetchAll($revSql, $paramsManager);
        } catch (\Throwable $t) {}

        // Summary: Yearly revenue and YoY growth
        $summary = [
            'revenue_year_total' => 0.0,
            'revenue_growth_pct' => 0.0
        ];
        try {
            $currYearSql = "
                SELECT SUM(p.amount) as total
                FROM sales_payments p
                INNER JOIN sales_leads l ON l.id = p.lead_id
                WHERE p.status = 'paid'
                  AND YEAR(p.updated_at) = YEAR(CURDATE())
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
            ";
            $prevYearSql = "
                SELECT SUM(p.amount) as total
                FROM sales_payments p
                INNER JOIN sales_leads l ON l.id = p.lead_id
                WHERE p.status = 'paid'
                  AND YEAR(p.updated_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))
                  " . ($managerId > 0 ? " AND l.manager_id = :mid " : "") . "
            ";
            $curr = $db->fetchOne($currYearSql, $paramsManager);
            $prev = $db->fetchOne($prevYearSql, $paramsManager);
            $currTotal = (float)($curr['total'] ?? 0);
            $prevTotal = (float)($prev['total'] ?? 0);
            $summary['revenue_year_total'] = $currTotal;
            $summary['revenue_growth_pct'] = $prevTotal > 0 ? (($currTotal - $prevTotal) / $prevTotal) * 100 : 0;
        } catch (\Throwable $t) {}

        $charts['revenue_trend_month'] = ['labels' => [], 'revenue' => [], 'leads' => []];
        try {
            $revMonthRows = $db->fetchAll("
                SELECT DATE_FORMAT(p.updated_at, '%Y-%m') as ym, DATE_FORMAT(p.updated_at, '%b') as m, SUM(p.amount) as total
                FROM sales_payments p
                INNER JOIN sales_leads l ON l.id = p.lead_id
                WHERE p.status = 'paid'
                  AND YEAR(p.updated_at) = YEAR(CURDATE())
                  " . ($managerId > 0 ? " AND l.manager_id = :mid " : "") . "
                GROUP BY ym
                ORDER BY ym ASC
            ", $paramsManager);
            $leadMonthRows = $db->fetchAll("
                SELECT DATE_FORMAT(sl.created_at, '%Y-%m') as ym, DATE_FORMAT(sl.created_at, '%b') as m, COUNT(sl.id) as c
                FROM sales_leads sl
                " . ($managerId > 0 ? " WHERE (sl.manager_id = :mid OR sl.manager_id IS NULL) AND YEAR(sl.created_at) = YEAR(CURDATE()) " : " WHERE YEAR(sl.created_at) = YEAR(CURDATE()) ") . "
                GROUP BY ym
                ORDER BY ym ASC
            ", $paramsManager);
            $revMap = [];
            foreach ($revMonthRows as $r) { $revMap[$r['ym']] = ['label' => $r['m'], 'val' => (float)($r['total'] ?? 0)]; }
            $leadMap = [];
            foreach ($leadMonthRows as $r) { $leadMap[$r['ym']] = ['label' => $r['m'], 'val' => (int)($r['c'] ?? 0)]; }
            $currentMonth = (int)date('n');
            for ($m = 1; $m <= $currentMonth; $m++) {
                $ym = date('Y-') . str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                $label = date('M', mktime(0, 0, 0, $m, 1));
                $charts['revenue_trend_month']['labels'][] = $label;
                $charts['revenue_trend_month']['revenue'][] = isset($revMap[$ym]) ? (float)$revMap[$ym]['val'] : 0.0;
                $charts['revenue_trend_month']['leads'][] = isset($leadMap[$ym]) ? (int)$leadMap[$ym]['val'] : 0;
            }
        } catch (\Throwable $t) {}

        // Additional sections for premium design
        $topPerformers = [];
        try {
            $tpSql = "
                SELECT COALESCE(u.name, u.email) as name,
                       SUM(sl.deal_value) as total_revenue,
                       COUNT(sl.id) as deals_count
                FROM users u
                JOIN sales_leads sl ON sl.assigned_to = u.id
                WHERE sl.stage = 'converted'
                  " . ($managerId > 0 ? " AND sl.manager_id = :mid " : "") . "
                GROUP BY u.id
                ORDER BY total_revenue DESC
                LIMIT 5
            ";
            $topPerformers = $db->fetchAll($tpSql, $paramsManager);
        } catch (\Throwable $t) {}

        $activities = [];
        try {
            $actSql = "
                SELECT a.*, COALESCE(u.name, u.email) as user_name, sl.company_name as lead_name
                FROM sales_lead_activities a
                LEFT JOIN users u ON u.id = a.user_id
                LEFT JOIN sales_leads sl ON sl.id = a.lead_id
                " . ($managerId > 0 ? " WHERE (sl.manager_id = :mid OR sl.manager_id IS NULL) " : "") . "
                ORDER BY a.created_at DESC
                LIMIT 10
            ";
            $activities = $db->fetchAll($actSql, $paramsManager);
            if (empty($activities)) {
                $activities = $db->fetchAll("
                    SELECT l.id as lead_id, l.updated_at as created_at, COALESCE(u.name, u.email) as user_name, l.company_name as lead_name,
                           CONCAT('Updated lead ', l.company_name) as description, 'update' as activity_type
                    FROM sales_leads l
                    LEFT JOIN users u ON u.id = l.assigned_to
                    " . ($managerId > 0 ? " WHERE l.manager_id = :mid " : "") . "
                    ORDER BY l.updated_at DESC
                    LIMIT 10
                ", $paramsManager);
            }
        } catch (\Throwable $t) {}

        $teamPerformance = [];
        try {
            $tpPerfSql = "
                SELECT u.id, COALESCE(u.name, u.email) as name, u.role,
                       COUNT(sl.id) as converted_leads,
                       SUM(CASE WHEN sl.stage = 'converted' AND DATE_FORMAT(sl.updated_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN sl.deal_value ELSE 0 END) as revenue_this_month
                FROM users u
                LEFT JOIN sales_leads sl ON sl.assigned_to = u.id
                WHERE u.role IN ('sales_manager','sales_executive')
                " . ($managerId > 0 ? " AND (sl.manager_id = :mid OR sl.manager_id IS NULL) " : "") . "
                GROUP BY u.id
                ORDER BY revenue_this_month DESC
            ";
            $teamPerformance = $db->fetchAll($tpPerfSql, $paramsManager);
            $month = date('Y-m');
            foreach ($teamPerformance as &$tp) {
                $trow = $db->fetchOne("SELECT revenue_target FROM sales_targets WHERE user_id = :uid AND month = :m", ['uid' => $tp['id'], 'm' => $month]);
                $target = (float)($trow['revenue_target'] ?? 0);
                $tp['target_revenue'] = $target;
                $tp['progress_pct'] = $target > 0 ? min(100, round((($tp['revenue_this_month'] ?? 0) / $target) * 100)) : 0;
            }
            unset($tp);
        } catch (\Throwable $t) {}

        $alerts = [
            'overdue_followups' => [],
            'idle_leads' => [],
            'payment_pending' => []
        ];
        $todaysTasks = [];
        $todaysActivities = [];
        try {
            $alerts['overdue_followups'] = $db->fetchAll("
                SELECT l.*, COALESCE(u.name, u.email) as assigned_name
                FROM sales_leads l
                LEFT JOIN users u ON u.id = l.assigned_to
                WHERE l.next_followup_at < NOW()
                  AND (l.followup_status IS NULL OR l.followup_status != 'done')
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                ORDER BY l.next_followup_at ASC
                LIMIT 10
            ", $paramsManager);
        } catch (\Throwable $t) {}
        try {
            $alerts['idle_leads'] = $db->fetchAll("
                SELECT l.*
                FROM sales_leads l
                WHERE l.updated_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
                  AND l.stage NOT IN ('converted','lost')
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                ORDER BY l.updated_at ASC
                LIMIT 10
            ", $paramsManager);
        } catch (\Throwable $t) {}
        try {
            $alerts['payment_pending'] = $db->fetchAll("
                SELECT p.*, l.company_name
                FROM sales_payments p
                INNER JOIN sales_leads l ON l.id = p.lead_id
                WHERE p.status = 'pending'
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                ORDER BY p.created_at DESC
                LIMIT 10
            ", $paramsManager);
        } catch (\Throwable $t) {}
        try {
            $todaysTasks = $db->fetchAll("
                SELECT l.id, l.company_name, l.contact_name, l.next_followup_at, l.followup_status,
                       COALESCE(u.name, u.email) as assignee_name
                FROM sales_leads l
                LEFT JOIN users u ON u.id = l.assigned_to
                WHERE DATE(l.next_followup_at) = CURDATE()
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                ORDER BY l.next_followup_at ASC
                LIMIT 10
            ", $paramsManager);
        } catch (\Throwable $t) {}
        try {
            $todaysActivities = $db->fetchAll("
                SELECT a.*, COALESCE(u.name, u.email) as user_name, l.company_name
                FROM sales_activities a
                LEFT JOIN users u ON u.id = a.user_id
                LEFT JOIN sales_leads l ON l.id = a.lead_id
                WHERE DATE(a.created_at) = CURDATE()
                  " . ($managerId > 0 ? " AND (l.manager_id = :mid OR l.manager_id IS NULL) " : "") . "
                ORDER BY a.created_at DESC
                LIMIT 20
            ", $paramsManager);
        } catch (\Throwable $t) {}

        $response->view('sales_manager/dashboard', [
            'title' => 'Sales Manager Dashboard',
            'stats' => $stats,
            'leads' => $leads,
            'execs' => $execs,
            'charts' => $charts,
            'alerts' => $alerts,
            'topPerformers' => $topPerformers,
            'activities' => $activities,
            'teamPerformance' => $teamPerformance,
            'summary' => $summary,
            'todaysTasks' => $todaysTasks,
            'todaysActivities' => $todaysActivities,
            'pipeline' => $pipeline
        ], 200, 'sales_manager/layout');
    }

    public function assign(Request $request, Response $response): void
    {
        $id = (int)$request->post('id');
        $assigned = (int)$request->post('assigned_to');
        try {
            Database::getInstance()->query('UPDATE sales_leads SET assigned_to = :uid WHERE id = :id', ['uid' => $assigned, 'id' => $id]);
        } catch (\Throwable $t) {}
        $response->redirect('/sales-manager/dashboard');
    }

    public function stage(Request $request, Response $response): void
    {
        $id = (int)$request->post('id');
        $stage = (string)$request->post('stage', 'new');
        $allowed = ['new','contacted','demo_done','follow_up','payment_pending','converted','lost'];
        if (!in_array($stage, $allowed, true)) { $stage = 'new'; }
        try {
            Database::getInstance()->query('UPDATE sales_leads SET stage = :stage WHERE id = :id', ['stage' => $stage, 'id' => $id]);
        } catch (\Throwable $t) {}
        $response->redirect('/sales-manager/dashboard');
    }
}
