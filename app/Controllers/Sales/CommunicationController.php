<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class CommunicationController extends BaseController
{
    public function log(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadId = (int)$request->param('id');
        $type = (string)($request->post('type') ?? 'note');
        $content = (string)($request->post('content') ?? '');
        $db = \App\Core\Database::getInstance();
        $db->query(
            "INSERT INTO sales_lead_activities (lead_id, user_id, type, title, description, created_at) VALUES (:lid, :uid, :type, :title, :desc, NOW())",
            [
                'lid' => $leadId,
                'uid' => $this->currentUser->id ?? null,
                'type' => $type,
                'title' => 'Communication',
                'desc' => $content
            ]
        );
        $response->json(['success' => true]);
    }
}
