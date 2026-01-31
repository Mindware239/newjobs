<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class TargetsController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        try {
            $db = \App\Core\Database::getInstance();
            $month = (string)($request->get('month') ?? date('Y-m'));
            
            // Fetch team members (sales executives and managers)
            $team = $db->fetchAll("SELECT id, name, email, role FROM users WHERE role IN ('sales_executive', 'sales_manager') AND status = 'active' ORDER BY name");
            
            // Fetch targets for the month
            $targets = [];
            $rows = $db->fetchAll("SELECT * FROM sales_targets WHERE month = :m", ['m' => $month]);
            foreach ($rows as $r) {
                $targets[$r['user_id']] = $r;
            }
            
            // Calculate achieved figures
            $members = [];
            $totalTargetRevenue = 0;
            $totalAchievedRevenue = 0;
            $totalTargetDeals = 0;
            $totalAchievedDeals = 0;

            foreach ($team as $user) {
                $uid = $user['id'];
                $target = $targets[$uid] ?? ['revenue_target' => 0, 'deals_target' => 0];
                
                $revTarget = (float)$target['revenue_target'];
                $dealsTarget = (int)$target['deals_target'];

                // Calculate achieved
                $revRow = $db->fetchOne(
                    "SELECT SUM(deal_value) as val FROM sales_leads WHERE assigned_to = :uid AND stage = 'converted' AND DATE_FORMAT(updated_at, '%Y-%m') = :m",
                    ['uid' => $uid, 'm' => $month]
                );
                $achievedRevenue = (float)($revRow['val'] ?? 0);
                
                $dealsRow = $db->fetchOne(
                    "SELECT COUNT(*) as cnt FROM sales_leads WHERE assigned_to = :uid AND stage = 'converted' AND DATE_FORMAT(updated_at, '%Y-%m') = :m",
                    ['uid' => $uid, 'm' => $month]
                );
                $achievedDeals = (int)($dealsRow['cnt'] ?? 0);
                
                $members[] = [
                    'id' => $uid,
                    'name' => $user['name'] ?? explode('@', $user['email'])[0],
                    'email' => $user['email'],
                    'role' => ucwords(str_replace('_', ' ', $user['role'])),
                    'initials' => strtoupper(substr($user['name'] ?? $user['email'], 0, 2)),
                    'target_revenue' => $revTarget,
                    'target_deals' => $dealsTarget,
                    'achieved_revenue' => $achievedRevenue,
                    'achieved_deals' => $achievedDeals,
                    'percentage' => $revTarget > 0 ? round(($achievedRevenue / $revTarget) * 100) : 0
                ];

                $totalTargetRevenue += $revTarget;
                $totalAchievedRevenue += $achievedRevenue;
                $totalTargetDeals += $dealsTarget;
                $totalAchievedDeals += $achievedDeals;
            }

            $summary = [
                'revenue_goal' => $totalTargetRevenue,
                'revenue_achieved' => $totalAchievedRevenue,
                'revenue_percent' => $totalTargetRevenue > 0 ? round(($totalAchievedRevenue / $totalTargetRevenue) * 100) : 0,
                'deals_goal' => $totalTargetDeals,
                'deals_achieved' => $totalAchievedDeals,
                'deals_percent' => $totalTargetDeals > 0 ? round(($totalAchievedDeals / $totalTargetDeals) * 100) : 0,
                'participation' => count(array_filter($members, fn($m) => $m['achieved_deals'] > 0)),
                'total_members' => count($members)
            ];
            
            $response->view('sales/targets/index', [
                'title' => 'Sales Targets',
                'user' => $this->currentUser,
                'members' => $members,
                'month' => $month,
                'summary' => $summary
            ], 200, 'sales/layout');

        } catch (\Throwable $e) {
            // Log error
            error_log("Error in TargetsController: " . $e->getMessage());
            // Show friendly error
            $response->view('error', ['message' => 'Something went wrong while loading targets. Please try again later.'], 500);
        }
    }

    public function edit(Request $request, Response $response)
    {
        if (!$this->requireAuth($request, $response)) { return; }
        if ($this->currentUser->role !== 'sales_manager' && $this->currentUser->role !== 'super_admin') {
             $response->redirect('/sales/executive/dashboard');
             return;
        }

        $userId = (int)$request->param('id');
        $month = (string)($request->get('month') ?? date('Y-m'));
        
        $db = \App\Core\Database::getInstance();
        $targetUser = $db->fetchOne("SELECT id, name, email FROM users WHERE id = :id", ['id' => $userId]);
        
        if (!$targetUser) {
            $response->redirect('/sales/manager/targets');
            return;
        }

        $target = $db->fetchOne("SELECT * FROM sales_targets WHERE user_id = :u AND month = :m", ['u' => $userId, 'm' => $month]);
        
        $response->view('sales/targets/edit', [
            'title' => 'Edit Target',
            'user' => $this->currentUser,
            'targetUser' => $targetUser,
            'target' => $target ?? ['revenue_target' => 0, 'deals_target' => 0],
            'month' => $month
        ], 200, 'sales/layout');
    }

    public function store(Request $request, Response $response)
    {
        if (!$this->requireAuth($request, $response)) { return; }
        if ($this->currentUser->role !== 'sales_manager' && $this->currentUser->role !== 'super_admin') {
             $response->json(['error' => 'Unauthorized'], 403);
             return;
        }
        
        $userId = (int)$request->post('user_id');
        $month = (string)$request->post('month');
        $revenue = (float)$request->post('revenue_target');
        $deals = (int)$request->post('deals_target');
        
        if ($userId <= 0 || empty($month)) {
            $response->redirect('/sales/manager/targets');
            return;
        }

        $db = \App\Core\Database::getInstance();
        
        // Check if exists
        $exists = $db->fetchOne("SELECT id FROM sales_targets WHERE user_id = :u AND month = :m", ['u' => $userId, 'm' => $month]);
        
        if ($exists) {
            $db->execute("UPDATE sales_targets SET revenue_target = :r, deals_target = :d WHERE id = :id", [
                'r' => $revenue,
                'd' => $deals,
                'id' => $exists['id']
            ]);
        } else {
            $db->execute("INSERT INTO sales_targets (user_id, month, revenue_target, deals_target) VALUES (:u, :m, :r, :d)", [
                'u' => $userId,
                'm' => $month,
                'r' => $revenue,
                'd' => $deals
            ]);
        }
        
        $response->redirect('/sales/manager/targets?month=' . $month);
    }
}
