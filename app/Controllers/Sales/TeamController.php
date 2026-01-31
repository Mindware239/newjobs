<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class TeamController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $executives = $db->fetchAll("SELECT id, COALESCE(name, email) as name, email, role FROM users WHERE role IN ('sales_executive', 'sales_manager') AND status = 'active' ORDER BY role, email");
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
            'title' => 'Team Management',
            'executives' => $executives,
            'kpis' => $kpis,
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function add(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $name = (string)$request->post('name');
        $email = (string)$request->post('email');
        $role = (string)($request->post('role') ?? 'sales_executive');
        
        // Map simplified role names to DB roles if necessary
        if ($role === 'executive') $role = 'sales_executive';
        if ($role === 'manager') $role = 'sales_manager';

        $db = \App\Core\Database::getInstance();
        
        // Check if user exists
        $exists = $db->fetchOne("SELECT id FROM users WHERE email = :e", ['e' => $email]);
        if ($exists) {
            // Update role if exists
            $db->query("UPDATE users SET role = :r, name = :n WHERE id = :id", ['r' => $role, 'n' => $name, 'id' => $exists['id']]);
        } else {
            // Create new user
            $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
            $db->query(
                "INSERT INTO users (name, email, password_hash, role, status, is_email_verified) VALUES (:n, :e, :p, :r, 'active', 1)",
                ['n' => $name, 'e' => $email, 'p' => $passwordHash, 'r' => $role]
            );
        }
        
        $response->redirect('/sales/team');
    }

    public function remove(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $db = \App\Core\Database::getInstance();
        // Don't delete, just remove sales role or deactivate
        $db->query("UPDATE users SET role = 'candidate', status = 'inactive' WHERE id = :id AND role IN ('sales_manager', 'sales_executive')", ['id' => $id]);
        $response->redirect('/sales/team');
    }
}
