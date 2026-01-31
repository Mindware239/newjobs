<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class NotificationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $userId = $this->currentUser->id ?? 0;
        $rows = $db->fetchAll("SELECT * FROM sales_notifications WHERE user_id = :u ORDER BY created_at DESC LIMIT 100", ['u' => $userId]);
        $response->view('sales/notifications/index', [
            'title' => 'Notifications',
            'items' => $rows
        ], 200, 'sales/layout');
    }

    public function markRead(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE sales_notifications SET is_read = 1 WHERE user_id = :u", ['u' => $this->currentUser->id ?? 0]);
        $response->redirect('/sales/notifications');
    }
}

