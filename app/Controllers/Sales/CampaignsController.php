<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class CampaignsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $campaigns = [];
        try {
            $db = \App\Core\Database::getInstance();
            $campaigns = $db->fetchAll("SELECT id, name, status, type, budget, start_date, end_date, COALESCE(leads,0) as leads, COALESCE(conversions,0) as conversions FROM sales_campaigns ORDER BY created_at DESC LIMIT 100");
        } catch (\Throwable $t) {
            $campaigns = [
                ['id' => 1, 'name' => 'Q4 Sales Drive', 'status' => 'Active', 'type' => 'Email', 'budget' => 5000, 'start_date' => '2025-10-01', 'end_date' => '2025-12-31', 'leads' => 150, 'conversions' => 12],
                ['id' => 2, 'name' => 'New Year Promo', 'status' => 'Scheduled', 'type' => 'Social', 'budget' => 2000, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'leads' => 0, 'conversions' => 0],
            ];
        }

        $response->view('sales/campaigns/index', [
                    'title' => 'Campaigns',
                    'campaigns' => $campaigns,
                    'user' => $this->currentUser
                ], 200, 'sales/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $response->view('sales/campaigns/create', [
            'title' => 'Create Campaign',
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $name = (string)$request->post('name');
        $type = (string)$request->post('type');
        $status = (string)$request->post('status');
        $description = (string)$request->post('description');
        
        // New fields
        $startDate = (string)$request->post('start_date');
        $endDate = (string)$request->post('end_date');
        $budget = (float)$request->post('budget');
        $expectedRevenue = (float)$request->post('expected_revenue');
        $expectedLeads = (int)$request->post('expected_leads');
        $audience = (string)$request->post('audience');
        $channel = (string)$request->post('channel');
        
        if (empty($name) || empty($type)) {
            $response->redirect('/sales/manager/campaigns/create');
            return;
        }

        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                "INSERT INTO sales_campaigns (
                    name, type, status, description, 
                    start_date, end_date, budget, expected_revenue, expected_leads, audience, channel,
                    created_by, created_at
                ) VALUES (
                    :n, :t, :s, :d, 
                    :sd, :ed, :b, :er, :el, :a, :c,
                    :u, NOW()
                )",
                [
                    'n' => $name, 
                    't' => $type, 
                    's' => $status, 
                    'd' => $description,
                    'sd' => empty($startDate) ? null : $startDate,
                    'ed' => empty($endDate) ? null : $endDate,
                    'b' => $budget,
                    'er' => $expectedRevenue,
                    'el' => $expectedLeads,
                    'a' => $audience,
                    'c' => $channel,
                    'u' => $this->currentUser->id ?? null
                ]
            );
        } catch (\Throwable $t) {
            // Log error?
        }
        $response->redirect('/sales/manager/campaigns');
    }

    public function edit(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $id = (int)$request->getRouteParam('id');
        $db = \App\Core\Database::getInstance();
        $campaign = $db->fetchOne("SELECT * FROM sales_campaigns WHERE id = :id", ['id' => $id]);
        
        if (!$campaign) {
            $response->redirect('/sales/manager/campaigns');
            return;
        }

        $response->view('sales/campaigns/edit', [
            'title' => 'Edit Campaign',
            'user' => $this->currentUser,
            'campaign' => $campaign
        ], 200, 'sales/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $id = (int)$request->getRouteParam('id');
        $name = (string)$request->post('name');
        $type = (string)$request->post('type');
        $status = (string)$request->post('status');
        $description = (string)$request->post('description');
        $startDate = (string)$request->post('start_date');
        $endDate = (string)$request->post('end_date');
        $budget = (float)$request->post('budget');
        $expectedRevenue = (float)$request->post('expected_revenue');
        $expectedLeads = (int)$request->post('expected_leads');
        $audience = (string)$request->post('audience');
        $channel = (string)$request->post('channel');

        if (empty($name) || empty($type)) {
            $response->redirect("/sales/manager/campaigns/{$id}/edit");
            return;
        }

        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                "UPDATE sales_campaigns SET 
                    name = :n, type = :t, status = :s, description = :d, 
                    start_date = :sd, end_date = :ed, budget = :b, 
                    expected_revenue = :er, expected_leads = :el,
                    audience = :a, channel = :c,
                    updated_at = NOW()
                WHERE id = :id",
                [
                    'n' => $name, 
                    't' => $type, 
                    's' => $status, 
                    'd' => $description,
                    'sd' => empty($startDate) ? null : $startDate,
                    'ed' => empty($endDate) ? null : $endDate,
                    'b' => $budget,
                    'er' => $expectedRevenue,
                    'el' => $expectedLeads,
                    'a' => $audience,
                    'c' => $channel,
                    'id' => $id
                ]
            );
        } catch (\Throwable $t) {
            // Log error
        }
        $response->redirect('/sales/manager/campaigns');
    }

    public function destroy(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $id = (int)$request->getRouteParam('id');
        if ($id <= 0) {
            $response->redirect('/sales/manager/campaigns');
            return;
        }

        try {
            $db = \App\Core\Database::getInstance();
            $db->query("DELETE FROM sales_campaigns WHERE id = :id", ['id' => $id]);
        } catch (\Throwable $t) {
            // Log error
        }
        $response->redirect('/sales/manager/campaigns');
    }
}
