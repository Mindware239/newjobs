<?php

declare(strict_types=1);

namespace App\Controllers\SalesManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SalesLead;
use App\Models\SalesLeadNote;
use App\Models\SalesLeadActivity;
use App\Models\SalesLeadAssignment;

class LeadController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $stage = (string)($request->get('stage') ?? '');
        $exec = (int)($request->get('executive') ?? 0);
        $dateFrom = (string)($request->get('from') ?? '');
        $dateTo = (string)($request->get('to') ?? '');
        $where = ['1=1'];
        $params = [];
        if ($stage) { $where[] = 'l.stage = :stage'; $params['stage'] = $stage; }
        if ($exec) { $where[] = 'l.assigned_to = :exec'; $params['exec'] = $exec; }
        if ($dateFrom) { $where[] = 'DATE(l.created_at) >= :df'; $params['df'] = $dateFrom; }
        if ($dateTo) { $where[] = 'DATE(l.created_at) <= :dt'; $params['dt'] = $dateTo; }
        $sql = "SELECT l.*, u.email as executive_email FROM sales_leads l LEFT JOIN users u ON u.id = l.assigned_to WHERE " . implode(' AND ', $where) . " ORDER BY l.updated_at DESC LIMIT 100";
        $rows = $db->fetchAll($sql, $params);
        $team = $db->fetchAll("SELECT id, name, email FROM sales_users WHERE role IN ('manager','executive') AND is_active = 1 ORDER BY role, name");
        $response->view('sales/leads/index', [
            'title' => 'Leads',
            'leads' => $rows,
            'filters' => ['stage' => $stage,'executive' => $exec,'from' => $dateFrom,'to' => $dateTo],
            'team' => $team
        ], 200, 'sales_manager/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('sales/leads/create', [
            'title' => 'Add Lead'
        ], 200, 'sales_manager/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $lead = new SalesLead();
        $lead->fill([
            'company_name' => (string)$request->post('company_name'),
            'contact_name' => (string)$request->post('contact_name'),
            'contact_email' => (string)$request->post('contact_email'),
            'contact_phone' => (string)$request->post('contact_phone'),
            'stage' => (string)($request->post('stage') ?? 'new'),
            'assigned_to' => (int)($request->post('assigned_to') ?? 0),
            'source' => (string)($request->post('source') ?? 'form')
        ]);
        $lead->save();
        $response->redirect('/sales-manager/leads');
    }

    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->redirect('/sales-manager/leads'); return; }
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
        ], 200, 'sales_manager/layout');
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $payload = $request->all();
        foreach (['company_name','contact_name','contact_email','contact_phone','source','notes'] as $f) {
            if (isset($payload[$f])) { $lead->$f = $payload[$f]; }
        }
        $lead->save();
        $response->redirect('/sales-manager/leads/' . $id);
    }

    public function assign(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $execId = (int)$request->post('executive_id');
        $lead->fill(['assigned_to' => $execId]);
        $lead->save();
        $assign = new SalesLeadAssignment();
        $assign->fill(['lead_id' => $id, 'assigned_to_id' => $execId, 'assigned_by_id' => $this->currentUser->id ?? 0, 'assigned_at' => date('Y-m-d H:i:s'), 'is_active' => 1]);
        $assign->save();
        $response->json(['success' => true]);
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
        $response->json(['success' => true]);
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
        $response->redirect('/sales-manager/leads/' . $leadId);
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
        $response->redirect('/sales-manager/leads/' . $leadId);
    }

    public function scheduleFollowup(Request $request, Response $response): void
    {
        $leadId = (int)$request->param('id');
        $dt = (string)$request->post('follow_up_at');
        \App\Core\Database::getInstance()->query('UPDATE sales_leads SET next_followup_at = :dt WHERE id = :id', ['dt' => $dt, 'id' => $leadId]);
        $response->redirect('/sales-manager/leads/' . $leadId);
    }

    public function importCsv(Request $request, Response $response): void
    {
        $response->json(['message' => 'Import processing not implemented'], 202);
    }
}

