<?php

declare(strict_types=1);

namespace App\Controllers\SalesExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SalesLead;
use App\Models\SalesLeadNote;
use App\Models\SalesLeadActivity;

class LeadController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);
        $status = (string)$request->get('stage', 'all');
        $where = ['assigned_to = :uid'];
        $params = ['uid' => $userId];
        if ($status !== 'all') { $where[] = 'stage = :stage'; $params['stage'] = $status; }
        $sql = 'SELECT * FROM sales_leads WHERE ' . implode(' AND ', $where) . ' ORDER BY updated_at DESC LIMIT 100';
        try { $leads = $db->fetchAll($sql, $params); } catch (\Throwable $t) { $leads = []; }
        $response->view('sales_executive/leads/index', [
            'title' => 'My Leads',
            'leads' => $leads,
            'stage' => $status
        ], 200, 'masteradmin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->redirect('/sales-executive/leads'); return; }
        $db = \App\Core\Database::getInstance();
        $notes = $db->fetchAll("SELECT n.*, su.name as user_name FROM sales_lead_notes n LEFT JOIN sales_users su ON su.user_id = n.user_id WHERE n.lead_id = :id ORDER BY n.created_at DESC", ['id' => $id]);
        $activities = $db->fetchAll("SELECT a.*, ss.name as new_stage FROM sales_lead_activities a LEFT JOIN sales_stages ss ON ss.id = a.new_stage_id WHERE a.lead_id = :id ORDER BY a.created_at DESC", ['id' => $id]);
        $payments = $db->fetchAll("SELECT * FROM sales_payments WHERE lead_id = :id ORDER BY created_at DESC", ['id' => $id]);
        $response->view('sales/leads/show', [
            'title' => 'Lead Details',
            'lead' => $lead->toArray(),
            'notes' => $notes,
            'activities' => $activities,
            'payments' => $payments
        ], 200, 'masteradmin/layout');
    }

    public function updateStage(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $stage = (string)$request->post('stage');
        $lead->fill(['stage' => $stage]);
        $row = \App\Core\Database::getInstance()->fetchOne("SELECT id FROM sales_stages WHERE slug = :s OR name = :s LIMIT 1", ['s' => $stage]);
        if ($row && isset($row['id'])) { $lead->stage_id = (int)$row['id']; }
        $lead->save();
        $act = new SalesLeadActivity();
        $act->fill(['lead_id' => $id, 'user_id' => $this->currentUser->id ?? null, 'type' => 'status_change', 'new_stage_id' => $lead->stage_id ?? null, 'title' => 'Stage updated', 'description' => 'Stage changed to ' . $stage]);
        $act->save();
        $response->redirect('/sales-executive/leads/' . $id);
    }

    public function addNote(Request $request, Response $response): void
    {
        $leadId = (int)$request->param('id');
        $note = new SalesLeadNote();
        $note->fill([
            'lead_id' => $leadId,
            'user_id' => $this->currentUser->id ?? 0,
            'note_text' => (string)$request->post('content')
        ]);
        $note->save();
        $act = new SalesLeadActivity();
        $act->fill(['lead_id' => $leadId, 'user_id' => $this->currentUser->id ?? null, 'type' => 'note', 'title' => 'Note added', 'description' => (string)$request->post('content')]);
        $act->save();
        $response->redirect('/sales-executive/leads/' . $leadId);
    }

    public function addActivity(Request $request, Response $response): void
    {
        $leadId = (int)$request->param('id');
        $act = new SalesLeadActivity();
        $act->fill([
            'lead_id' => $leadId,
            'user_id' => $this->currentUser->id ?? null,
            'type' => (string)$request->post('type', 'custom'),
            'title' => (string)$request->post('title', ''),
            'description' => (string)$request->post('description', '')
        ]);
        $act->save();
        $response->redirect('/sales-executive/leads/' . $leadId);
    }

    public function scheduleFollowup(Request $request, Response $response): void
    {
        $leadId = (int)$request->param('id');
        $dt = (string)$request->post('follow_up_at');
        \App\Core\Database::getInstance()->query('UPDATE sales_leads SET next_followup_at = :dt WHERE id = :id', ['dt' => $dt, 'id' => $leadId]);
        $response->redirect('/sales-executive/leads/' . $leadId);
    }
}

