<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class NotificationTemplatesController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $db = Database::getInstance();
        $templates = [];
        try {
            $templates = $db->fetchAll("SELECT * FROM notification_templates ORDER BY created_at DESC");
        } catch (\Throwable $t) {
            $templates = [];
        }
        $response->view('admin/notifications/templates/index', [
            'title' => 'Notification Templates',
            'templates' => $templates
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $response->view('admin/notifications/templates/create', [
            'title' => 'Create Notification Template'
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $eventKey = trim((string)$request->post('event_key', ''));
        $channel = trim((string)$request->post('channel', 'email'));
        $subject = trim((string)$request->post('subject', ''));
        $content = (string)$request->post('content', '');
        $variables = trim((string)$request->post('variables', ''));
        $isActive = (int)($request->post('is_active', 1)) === 1 ? 1 : 0;

        if ($eventKey === '' || $channel === '') {
            $response->view('admin/notifications/templates/create', [
                'title' => 'Create Notification Template',
                'error' => 'Event key and channel are required'
            ], 422, 'admin/layout');
            return;
        }

        $db = Database::getInstance();
        try {
            $existing = $db->fetchOne(
                "SELECT id FROM notification_templates WHERE event_key = :k AND channel = :c",
                ['k' => $eventKey, 'c' => $channel]
            );
            if ($existing) {
                $response->view('admin/notifications/templates/create', [
                    'title' => 'Create Notification Template',
                    'error' => 'Template for this event and channel already exists'
                ], 422, 'admin/layout');
                return;
            }
            $db->query(
                "INSERT INTO notification_templates (event_key, channel, subject, content, variables, is_active, created_at, updated_at)
                 VALUES (:k, :c, :s, :b, :v, :a, NOW(), NOW())",
                ['k' => $eventKey, 'c' => $channel, 's' => $subject ?: null, 'b' => $content ?: null, 'v' => $variables ?: null, 'a' => $isActive]
            );
            $response->redirect('/admin/notification-templates?success=Template%20created');
        } catch (\Throwable $t) {
            $response->view('admin/notifications/templates/create', [
                'title' => 'Create Notification Template',
                'error' => 'Failed to create template'
            ], 500, 'admin/layout');
        }
    }

    public function edit(Request $request, Response $response, array $params = []): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)($params['id'] ?? (int)$request->param('id'));
        if ($id <= 0) {
            $response->redirect('/admin/notification-templates');
            return;
        }
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM notification_templates WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/notification-templates?error=Template%20not%20found');
            return;
        }
        $response->view('admin/notifications/templates/edit', [
            'title' => 'Edit Notification Template',
            'template' => $row
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params = []): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)($params['id'] ?? (int)$request->param('id'));
        if ($id <= 0) {
            $response->redirect('/admin/notification-templates');
            return;
        }
        $eventKey = trim((string)$request->post('event_key', ''));
        $channel = trim((string)$request->post('channel', 'email'));
        $subject = trim((string)$request->post('subject', ''));
        $content = (string)$request->post('content', '');
        $variables = trim((string)$request->post('variables', ''));
        $isActive = (int)($request->post('is_active', 0)) === 1 ? 1 : 0;

        if ($eventKey === '' || $channel === '') {
            $db = Database::getInstance();
            $row = $db->fetchOne("SELECT * FROM notification_templates WHERE id = :id", ['id' => $id]);
            $response->view('admin/notifications/templates/edit', [
                'title' => 'Edit Notification Template',
                'error' => 'Event key and channel are required',
                'template' => $row ?: []
            ], 422, 'admin/layout');
            return;
        }

        $db = Database::getInstance();
        try {
            $dup = $db->fetchOne(
                "SELECT id FROM notification_templates WHERE event_key = :k AND channel = :c AND id != :id",
                ['k' => $eventKey, 'c' => $channel, 'id' => $id]
            );
            if ($dup) {
                $row = $db->fetchOne("SELECT * FROM notification_templates WHERE id = :id", ['id' => $id]);
                $response->view('admin/notifications/templates/edit', [
                    'title' => 'Edit Notification Template',
                    'error' => 'Another template with same event and channel exists',
                    'template' => $row ?: []
                ], 422, 'admin/layout');
                return;
            }
            $db->query(
                "UPDATE notification_templates
                 SET event_key = :k, channel = :c, subject = :s, content = :b, variables = :v, is_active = :a, updated_at = NOW()
                 WHERE id = :id",
                ['k' => $eventKey, 'c' => $channel, 's' => $subject ?: null, 'b' => $content ?: null, 'v' => $variables ?: null, 'a' => $isActive, 'id' => $id]
            );
            $response->redirect('/admin/notification-templates?success=Template%20updated');
        } catch (\Throwable $t) {
            $row = $db->fetchOne("SELECT * FROM notification_templates WHERE id = :id", ['id' => $id]);
            $response->view('admin/notifications/templates/edit', [
                'title' => 'Edit Notification Template',
                'error' => 'Failed to update template',
                'template' => $row ?: []
            ], 500, 'admin/layout');
        }
    }

    public function delete(Request $request, Response $response, array $params = []): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)($params['id'] ?? (int)$request->param('id'));
        if ($id <= 0) {
            $response->json(['error' => 'Invalid template id'], 400);
            return;
        }
        $db = Database::getInstance();
        try {
            $db->query("DELETE FROM notification_templates WHERE id = :id", ['id' => $id]);
            $response->json(['success' => true]);
        } catch (\Throwable $t) {
            $response->json(['error' => 'Failed to delete template'], 500);
        }
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
