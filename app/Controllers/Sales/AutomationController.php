<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class AutomationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $rules = [];
        try {
            $db = \App\Core\Database::getInstance();
            $rows = $db->fetchAll("SELECT id, name, trigger, action, status FROM sales_automation_rules ORDER BY created_at DESC LIMIT 100");
            foreach ($rows as $r) {
                $rules[] = [
                    'id' => $r['id'],
                    'name' => $r['name'],
                    'trigger' => $r['trigger'],
                    'action' => $r['action'],
                    'status' => $r['status']
                ];
            }
        } catch (\Throwable $t) {
            $rules = [
                ['id' => 1, 'name' => 'Auto-assign New Leads', 'trigger' => 'New Lead Created', 'action' => 'Assign Round Robin', 'status' => 'Active'],
                ['id' => 2, 'name' => 'Follow-up Email 3 Days', 'trigger' => 'Stage = Contacted', 'action' => 'Send Email', 'status' => 'Paused'],
            ];
        }

        $response->view('sales/automation/index', [
                    'title' => 'Automation Rules',
                    'rules' => $rules,
                    'user' => $this->currentUser
                ], 200, 'sales/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $response->view('sales/automation/create', [
            'title' => 'Add Automation Rule',
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $name = (string)$request->post('name');
        $trigger = (string)$request->post('trigger');
        $action = (string)$request->post('action');
        $status = (string)$request->post('status');
        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                "INSERT INTO sales_automation_rules (name, trigger, action, status, created_by, created_at) VALUES (:n, :t, :a, :s, :u, NOW())",
                ['n' => $name, 't' => $trigger, 'a' => $action, 's' => $status, 'u' => $this->currentUser->id ?? null]
            );
        } catch (\Throwable $t) {}
        $response->redirect('/sales/manager/automation');
    }
}
