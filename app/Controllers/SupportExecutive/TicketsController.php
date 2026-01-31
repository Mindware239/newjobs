<?php

declare(strict_types=1);

namespace App\Controllers\SupportExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class TicketsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);
        $status = (string)$request->get('status', 'open');
        $where = ["(assigned_to = :uid OR assigned_to IS NULL)"];
        $params = ['uid' => $userId];
        if ($status !== 'all') { $where[] = 'status = :status'; $params['status'] = $status; }
        $sql = 'SELECT * FROM support_tickets ' . (count($where)?'WHERE '.implode(' AND ',$where):'') . ' ORDER BY updated_at DESC LIMIT 100';
        try { $tickets = $db->fetchAll($sql, $params); } catch (\Throwable $t) { $tickets = []; }
        $response->view('support_executive/tickets/index', [
            'title' => 'Support Tickets',
            'tickets' => $tickets,
            'status' => $status
        ], 200, 'masteradmin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->param('id');
        try { $ticket = $db->fetchOne('SELECT * FROM support_tickets WHERE id = :id', ['id' => $id]); } catch (\Throwable $t) { $ticket = null; }
        $messages = $ticket ? $db->fetchAll('SELECT * FROM support_ticket_messages WHERE ticket_id = :id ORDER BY created_at ASC', ['id' => $id]) : [];
        if (!$ticket) { $response->redirect('/support-exec/tickets'); return; }
        $response->view('support_executive/tickets/show', [
            'title' => 'Ticket #' . $id,
            'ticket' => $ticket,
            'messages' => $messages
        ], 200, 'masteradmin/layout');
    }

    public function assign(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $assignee = (int)$request->post('assigned_to', (string)$this->currentUser->id);
        $db->query('UPDATE support_tickets SET assigned_to = :uid, status = "assigned" WHERE id = :id', ['uid' => $assignee, 'id' => $id]);
        $this->log('assign_ticket', $id);
        $response->redirect('/support-exec/tickets/' . $id);
    }

    public function reply(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $body = trim((string)$request->post('body', ''));
        if ($body !== '') {
            $db->query('INSERT INTO support_ticket_messages (ticket_id, sender_user_id, body, created_at) VALUES (:tid, :uid, :body, NOW())', ['tid' => $id, 'uid' => (int)$this->currentUser->id, 'body' => $body]);
            $db->query('UPDATE support_tickets SET status = "pending", updated_at = NOW() WHERE id = :id', ['id' => $id]);
            $this->log('reply_ticket', $id);
        }
        $response->redirect('/support-exec/tickets/' . $id);
    }

    public function close(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $db->query('UPDATE support_tickets SET status = "closed", updated_at = NOW() WHERE id = :id', ['id' => $id]);
        $this->log('close_ticket', $id);
        $response->redirect('/support-exec/tickets/' . $id);
    }

    public function escalate(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $db->query('UPDATE support_tickets SET status = "escalated", updated_at = NOW() WHERE id = :id', ['id' => $id]);
        $this->log('escalate_ticket', $id);
        $response->redirect('/support-exec/tickets/' . $id);
    }

    private function log(string $action, int $ticketId): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, 'support_ticket', :entity_id, NULL, NULL, :ip, NOW())",
                [
                    'user_id' => (int)($this->currentUser->id ?? 0),
                    'action' => $action,
                    'entity_id' => $ticketId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Throwable $t) {}
    }
}

