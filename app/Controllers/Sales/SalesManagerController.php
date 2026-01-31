<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class SalesManagerController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        if ($this->currentUser->role !== 'sales_manager' && $this->currentUser->role !== 'super_admin') {
             $response->redirect('/sales/executive/dashboard');
             return;
        }
        $db = \App\Core\Database::getInstance();
        $kpis = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'follow_up' => 0,
            'demo_done' => 0,
            'payment_pending' => 0,
            'converted' => 0,
            'lost' => 0,
            'total_revenue' => 0,
            'active_deals' => 0,
            'conversion_rate' => 0,
            'avg_deal_size' => 0
        ];
        try {
            $row = $db->fetchOne("SELECT COUNT(*) as c FROM sales_leads");
            $kpis['total'] = (int)($row['c'] ?? 0);
            foreach (['new','contacted','follow_up','demo_done','payment_pending','converted','lost'] as $s) {
                $r = $db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE stage = :s", ['s' => $s]);
                $kpis[$s] = (int)($r['c'] ?? 0);
            }
            
            // Calculate Revenue and other metrics
            $rev = $db->fetchOne("SELECT SUM(deal_value) as val FROM sales_leads WHERE stage = 'converted'");
            $kpis['total_revenue'] = (float)($rev['val'] ?? 0);
            
            $active = $db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE stage NOT IN ('lost', 'converted')");
            $kpis['active_deals'] = (int)($active['c'] ?? 0);
            
            if ($kpis['total'] > 0) {
                $kpis['conversion_rate'] = round(($kpis['converted'] / $kpis['total']) * 100, 1);
                if ($kpis['converted'] > 0) {
                    $kpis['avg_deal_size'] = round($kpis['total_revenue'] / $kpis['converted'], 2);
                }
            }
        } catch (\Throwable $t) {}

        // Fetch Pipeline Data (Top deals by stage)
        $pipeline = [
            'proposal' => [],
            'negotiation' => [],
            'closing' => []
        ];
        try {
            // Proposal -> demo_done
            $pipeline['proposal'] = $db->fetchAll(
                "SELECT l.*, c.name as company_name 
                 FROM sales_leads l 
                 LEFT JOIN companies c ON c.id = l.company_id 
                 WHERE l.stage = 'demo_done' 
                 ORDER BY l.deal_value DESC LIMIT 5"
            );
            
            // Negotiation -> follow_up (assuming high value follow-ups are in negotiation)
            $pipeline['negotiation'] = $db->fetchAll(
                "SELECT l.*, c.name as company_name 
                 FROM sales_leads l 
                 LEFT JOIN companies c ON c.id = l.company_id 
                 WHERE l.stage = 'follow_up' 
                 ORDER BY l.deal_value DESC LIMIT 5"
            );

            // Closing -> payment_pending
            $pipeline['closing'] = $db->fetchAll(
                "SELECT l.*, c.name as company_name 
                 FROM sales_leads l 
                 LEFT JOIN companies c ON c.id = l.company_id 
                 WHERE l.stage = 'payment_pending' 
                 ORDER BY l.deal_value DESC LIMIT 5"
            );
        } catch (\Throwable $t) {}

        // Fetch Recent Activity
        $activities = [];
        try {
            // Try fetching from sales_lead_activities first
            $activities = $db->fetchAll(
                "SELECT a.*, u.name as user_name, l.company_name as lead_name 
                 FROM sales_lead_activities a
                 LEFT JOIN users u ON u.id = a.user_id
                 LEFT JOIN sales_leads l ON l.id = a.lead_id
                 ORDER BY a.created_at DESC LIMIT 10"
            );
            
            // If empty, maybe fallback to checking updated_at on leads as a proxy for activity?
            // For now, let's assume the table might be empty and we want to show *something* real if possible.
            if (empty($activities)) {
                 $activities = $db->fetchAll(
                    "SELECT l.id as lead_id, l.updated_at as created_at, u.name as user_name, l.company_name as lead_name, 
                     CONCAT('Updated lead ', l.company_name) as description, 'update' as activity_type
                     FROM sales_leads l
                     LEFT JOIN users u ON u.id = l.assigned_to
                     ORDER BY l.updated_at DESC LIMIT 10"
                );
            }
        } catch (\Throwable $t) {}

        // Chart Data (Last 7 days revenue)
        $chartData = [
            'labels' => [],
            'data' => []
        ];
        try {
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $chartData['labels'][] = date('D', strtotime($date));
                
                $row = $db->fetchOne(
                    "SELECT SUM(deal_value) as val FROM sales_leads 
                     WHERE stage = 'converted' AND DATE(updated_at) = :d",
                    ['d' => $date]
                );
                $chartData['data'][] = (float)($row['val'] ?? 0);
            }
        } catch (\Throwable $t) {}

        $rows = $db->fetchAll(
            "SELECT l.*, u.email as executive_email, COALESCE(u.name, u.email) as executive_name 
             FROM sales_leads l 
             LEFT JOIN users u ON u.id = l.assigned_to 
             ORDER BY l.updated_at DESC 
             LIMIT 50"
        );

        $team = $db->fetchAll("SELECT id, email FROM users WHERE role IN ('sales_manager','sales_executive') ORDER BY email");

        // Fetch Lead Sources
        $leadSources = [];
        try {
            $rowsSrc = $db->fetchAll("SELECT source, COUNT(*) as c FROM sales_leads GROUP BY source");
            foreach ($rowsSrc as $r) {
                $leadSources[$r['source'] ?? 'Unknown'] = (int)$r['c'];
            }
        } catch (\Throwable $t) {}

        // Fetch Top Performers
        $topPerformers = [];
        try {
            $topPerformers = $db->fetchAll(
                "SELECT u.name, u.email, SUM(l.deal_value) as total_revenue, COUNT(l.id) as deals_count
                 FROM users u
                 JOIN sales_leads l ON l.assigned_to = u.id
                 WHERE l.stage = 'converted'
                 GROUP BY u.id
                 ORDER BY total_revenue DESC
                 LIMIT 5"
            );
        } catch (\Throwable $t) {}

        // Fetch Today's Tasks
        $todaysTasks = [];
        try {
            $todaysTasks = $db->fetchAll(
                "SELECT l.id, l.company_name, l.contact_name, l.next_followup_at, u.name as assignee_name
                 FROM sales_leads l
                 LEFT JOIN users u ON u.id = l.assigned_to
                 WHERE DATE(l.next_followup_at) = CURDATE()
                 ORDER BY l.next_followup_at ASC
                 LIMIT 10"
            );
        } catch (\Throwable $t) {}

        // Team Performance with monthly target progress
        $teamPerformance = [];
        try {
            $month = date('Y-m');
            $teamPerformance = $db->fetchAll("
                SELECT 
                    u.id,
                    COALESCE(u.name, u.email) as name,
                    u.role as role,
                    COUNT(l.id) as total_leads,
                    SUM(CASE WHEN l.stage = 'converted' THEN 1 ELSE 0 END) as converted_leads,
                    COALESCE(SUM(CASE WHEN l.stage = 'converted' AND DATE_FORMAT(l.updated_at, '%Y-%m') = :m THEN l.deal_value ELSE 0 END), 0) as revenue_this_month
                FROM users u
                LEFT JOIN sales_leads l ON l.assigned_to = u.id
                WHERE u.role IN ('sales_manager','sales_executive') AND u.status = 'active'
                GROUP BY u.id
                ORDER BY revenue_this_month DESC
                LIMIT 10
            ", ['m' => $month]);
            foreach ($teamPerformance as &$tp) {
                $trow = $db->fetchOne("SELECT revenue_target, deals_target FROM sales_targets WHERE user_id = :uid AND month = :m", ['uid' => $tp['id'], 'm' => $month]);
                $target = (float)($trow['revenue_target'] ?? 0);
                $tp['target_revenue'] = $target;
                $tp['progress_pct'] = $target > 0 ? min(100, round((($tp['revenue_this_month'] ?? 0) / $target) * 100)) : 0;
            }
            unset($tp);
        } catch (\Throwable $t) {}

        $response->view('sales/manager/dashboard', [
            'title' => 'Sales Manager Dashboard',
            'user' => $this->currentUser,
            'kpis' => $kpis,
            'leads' => $rows,
            'team' => $team,
            'pipeline' => $pipeline,
            'activities' => $activities,
            'chartData' => $chartData,
            'leadSources' => $leadSources,
            'topPerformers' => $topPerformers,
            'todaysTasks' => $todaysTasks,
            'teamPerformance' => $teamPerformance
        ], 200, 'sales/layout');
    }

    public function viewExecutive(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        if ($this->currentUser->role !== 'sales_manager' && $this->currentUser->role !== 'super_admin') {
            $response->redirect('/sales/executive/dashboard');
            return;
        }

        $id = (int)$request->param('id');
        $db = \App\Core\Database::getInstance();
        $targetUser = $db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);

        if (!$targetUser) {
            $response->redirect('/sales/manager/team');
            return;
        }

        // Fetch Executive Data (reusing logic from SalesExecutiveController)
        $kpis = [
            'my_leads' => 0,
            'today_followups' => 0,
            'demo_scheduled' => 0,
            'payments_pending' => 0,
            'conversions' => 0
        ];
        try {
            $kpis['my_leads'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u", ['u' => $id])['c'] ?? 0);
            $kpis['today_followups'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND DATE(next_followup_at) = CURDATE()", ['u' => $id])['c'] ?? 0);
            $kpis['demo_scheduled'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'demo_done'", ['u' => $id])['c'] ?? 0);
            $kpis['payments_pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'payment_pending'", ['u' => $id])['c'] ?? 0);
            $kpis['conversions'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE assigned_to = :u AND stage = 'converted'", ['u' => $id])['c'] ?? 0);
        } catch (\Throwable $t) {}

        $rows = $db->fetchAll(
            "SELECT l.* FROM sales_leads l WHERE l.assigned_to = :u ORDER BY l.updated_at DESC LIMIT 50",
            ['u' => $id]
        );

        // Pass target user as 'user' to render dashboard as them, but keep currentUser for permission checks if needed in layout (layout uses $user for profile)
        // Actually, layout uses $user for the profile dropdown. We should probably keep $this->currentUser as $user, but pass $targetUser as something else?
        // Or, if we want to "View As", maybe we WANT to see their name in top right?
        // The user said "view and all some page see all pages".
        // Let's pass $targetUser as $user so the dashboard renders with THEIR name, but maybe add a banner saying "Viewing as X"?
        // For now, let's just render the view. The view uses $user for "Welcome back, [Name]".
        
        // We'll use a modified view or just the executive view.
        $response->view('sales/executive/dashboard', [
            'title' => 'Dashboard: ' . ($targetUser['name'] ?? $targetUser['email']),
            'user' => (object)$targetUser, // Cast to object as view expects object
            'kpis' => $kpis,
            'leads' => $rows,
            'is_viewing_as' => true, // Flag to maybe show a "Back to Manager" button
            'manager_user' => $this->currentUser
        ], 200, 'sales/layout');
    }
}
