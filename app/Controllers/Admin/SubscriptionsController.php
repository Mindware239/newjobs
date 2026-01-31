<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SubscriptionsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $subscriptions = $db->fetchAll(
            "SELECT es.*, e.company_name, sp.name as plan_name, u.email as employer_email
             FROM employer_subscriptions es
             LEFT JOIN employers e ON e.id = es.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             LEFT JOIN subscription_plans sp ON sp.id = es.plan_id
             ORDER BY es.created_at DESC"
        );

        $response->view('admin/subscriptions/index', [
            'title' => 'Manage Subscriptions',
            'subscriptions' => $subscriptions,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function plans(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $plans = $db->fetchAll("SELECT * FROM subscription_plans ORDER BY price_monthly ASC");

        $response->view('admin/subscriptions/plans', [
            'title' => 'Subscription Plans',
            'plans' => $plans,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function createPlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $data = $request->post();
        $db = Database::getInstance();

        $db->query(
            "INSERT INTO subscription_plans (name, slug, description, price_monthly, price_quarterly, price_annual, max_job_posts, features, is_active, created_at)
             VALUES (:name, :slug, :description, :price_monthly, :price_quarterly, :price_annual, :max_job_posts, :features, :is_active, NOW())",
            [
                'name' => $data['name'] ?? '',
                'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $data['name'] ?? '')),
                'description' => $data['description'] ?? '',
                'price_monthly' => (float)($data['price_monthly'] ?? 0),
                'price_quarterly' => (float)($data['price_quarterly'] ?? 0),
                'price_annual' => (float)($data['price_annual'] ?? 0),
                'max_job_posts' => (int)($data['max_job_posts'] ?? 0),
                'features' => json_encode($data['features'] ?? []),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]
        );

        $this->logAction('create_plan', $data);
        $response->redirect('/admin/subscriptions/plans');
    }

    public function updatePlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $data = $request->post();
        $db = Database::getInstance();

        $db->query(
            "UPDATE subscription_plans 
             SET name = :name, description = :description, price_monthly = :price_monthly, 
                 price_quarterly = :price_quarterly, price_annual = :price_annual,
                 max_job_posts = :max_job_posts, features = :features, is_active = :is_active
             WHERE id = :id",
            [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'price_monthly' => (float)($data['price_monthly'] ?? 0),
                'price_quarterly' => (float)($data['price_quarterly'] ?? 0),
                'price_annual' => (float)($data['price_annual'] ?? 0),
                'max_job_posts' => (int)($data['max_job_posts'] ?? 0),
                'features' => json_encode($data['features'] ?? []),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]
        );

        $this->logAction('update_plan', array_merge($data, ['plan_id' => $id]));
        $response->redirect('/admin/subscriptions/plans');
    }

    public function deletePlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $db->query("DELETE FROM subscription_plans WHERE id = :id", ['id' => $id]);

        $this->logAction('delete_plan', ['plan_id' => $id]);
        $response->redirect('/admin/subscriptions/plans');
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    private function logAction(string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :old_value, :new_value, :ip_address, NOW())",
                [
                    'user_id' => $this->currentUser->id,
                    'action' => $action,
                    'entity_type' => 'subscription_plan',
                    'entity_id' => $data['plan_id'] ?? null,
                    'old_value' => json_encode($data),
                    'new_value' => json_encode(['status' => 'changed']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

