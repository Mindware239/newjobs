<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class NotificationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $rows = $db->fetchAll("SELECT n.*, l.company_name FROM sales_notifications n LEFT JOIN sales_leads l ON l.id = n.lead_id WHERE n.user_id = :uid ORDER BY n.created_at DESC LIMIT 100", ['uid' => (int)($this->currentUser->id ?? 0)]);
        $response->view('sales_executive/notifications/index', [
            'title' => 'Notifications',
            'items' => $rows
        ], 200, 'masteradmin/layout');
    }

    public function markRead(Request $request, Response $response): void
    {
        $ids = (array)$request->post('ids', []);
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE sales_notifications SET is_read = 1 WHERE id IN ($placeholders)";
            \App\Core\Database::getInstance()->query($sql, $ids);
        }
        $response->redirect('/sales-executive/notifications');
    }
}

