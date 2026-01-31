<?php

declare(strict_types=1);

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SalesLead;
use App\Models\SalesNote;
use App\Models\LeadAssignment;

class LeadController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $db = \App\Core\Database::getInstance();
        $stage = (string)($request->get('stage') ?? '');
        $exec = (int)($request->get('executive') ?? 0);
        
        // If user is executive, enforce filter
        if ($this->currentUser->role === 'sales_executive') {
            $exec = $this->currentUser->id;
        }

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
        
        // Only show team filter to managers
        $team = [];
        if ($this->currentUser->role !== 'sales_executive') {
            $team = $db->fetchAll("SELECT id, COALESCE(name, email) as name, email FROM users WHERE role IN ('sales_manager','sales_executive') AND status = 'active' ORDER BY role, name");
        }

        $response->view('sales/leads/index', [
            'title' => 'Leads',
            'leads' => $rows,
            'filters' => ['stage' => $stage,'executive' => $exec,'from' => $dateFrom,'to' => $dateTo],
            'team' => $team,
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        
        $team = [];
        // Managers/Admins can see the team list to assign leads
        if ($this->currentUser->role !== 'sales_executive') {
            $db = \App\Core\Database::getInstance();
            $team = $db->fetchAll("SELECT id, name, email FROM users WHERE role IN ('sales_manager','sales_executive') AND status = 'active' ORDER BY name");
        }

        $response->view('sales/leads/create', [
            'title' => 'Add Lead',
            'team' => $team,
            'user' => $this->currentUser
        ], 200, 'sales/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $lead = new SalesLead();
        $lead->fill([
            'company_name' => (string)$request->post('company_name'),
            'contact_name' => (string)$request->post('contact_name'),
            'contact_email' => (string)$request->post('contact_email'),
            'contact_phone' => (string)$request->post('contact_phone'),
            'stage' => (string)($request->post('stage') ?? 'new'),
            'assigned_to' => (int)($request->post('assigned_to') ?? 0),
            'source' => (string)($request->post('source') ?? 'form'),
            'deal_value' => (float)($request->post('deal_value') ?? 0),
            'currency' => (string)($request->post('currency') ?? 'INR'),
            'next_followup_at' => (string)($request->post('next_followup_at') ?? null)
        ]);
        $lead->save();
        $response->redirect('/sales/leads');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->redirect('/sales/leads'); return; }
        $db = \App\Core\Database::getInstance();
        $notes = $db->fetchAll("SELECT n.*, su.name as user_name FROM sales_lead_notes n LEFT JOIN users su ON su.id = n.user_id WHERE n.lead_id = :id ORDER BY n.created_at DESC", ['id' => $id]);
        $activities = $db->fetchAll("SELECT a.*, ss.name as new_stage FROM sales_lead_activities a LEFT JOIN sales_stages ss ON ss.id = a.new_stage_id WHERE a.lead_id = :id ORDER BY a.created_at DESC", ['id' => $id]);
        $payments = $db->fetchAll("SELECT * FROM sales_payments WHERE lead_id = :id ORDER BY created_at DESC", ['id' => $id]);
        $response->view('sales/leads/show', [
            'title' => 'Lead Details',
            'lead' => $lead->toArray(),
            'notes' => $notes,
            'activities' => $activities,
            'payments' => $payments
        ], 200, 'sales/layout');
    }

    public function assign(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $execId = (int)$request->post('executive_id');
        $lead->fill(['assigned_to' => $execId]);
        $lead->save();
        $assign = new LeadAssignment();
        $assign->fill(['lead_id' => $id, 'assigned_to_id' => $execId, 'assigned_by_id' => $this->currentUser->id ?? 0]);
        $assign->save();
        $response->json(['success' => true]);
    }

    public function updateStage(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $id = (int)$request->param('id');
        $lead = SalesLead::find($id);
        if (!$lead) { $response->json(['error' => 'Lead not found'], 404); return; }
        $stage = (string)$request->post('stage');
        $lead->fill(['stage' => $stage]);
        // Map stage to stage_id if exists
        $row = \App\Core\Database::getInstance()->fetchOne("SELECT id FROM sales_stages WHERE slug = :s OR name = :s LIMIT 1", ['s' => $stage]);
        if ($row && isset($row['id'])) { $lead->stage_id = (int)$row['id']; }
        $lead->save();
        $act = new \App\Models\SalesActivity();
        $act->fill(['lead_id' => $id, 'user_id' => $this->currentUser->id ?? null, 'type' => 'status_change', 'new_stage_id' => $lead->stage_id ?? null, 'title' => 'Stage updated', 'description' => 'Stage changed to ' . $stage]);
        $act->save();
        $response->json(['success' => true]);
    }

    public function addNote(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadId = (int)$request->param('id');
        $note = new SalesNote();
        $note->fill([
            'lead_id' => $leadId,
            'user_id' => $this->currentUser->id ?? 0,
            'note_text' => (string)$request->post('content')
        ]);
        $note->save();
        $act = new \App\Models\SalesActivity();
        $act->fill(['lead_id' => $leadId, 'user_id' => $this->currentUser->id ?? null, 'type' => 'note', 'title' => 'Note added', 'description' => (string)$request->post('content')]);
        $act->save();
        $response->redirect('/sales/leads/' . $leadId);
    }

    public function deleteNote(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadId = (int)$request->param('lead_id');
        $id = (int)$request->param('id');
        $note = SalesNote::find($id);
        if ($note) { $note->delete(); }
        $response->redirect('/sales/leads/' . $leadId);
    }

    public function bulkAssign(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) { return; }
        $leadIds = (array)$request->post('lead_ids', []);
        $execId = (int)$request->post('executive_id', 0);
        if (empty($leadIds) || $execId <= 0) {
            $response->redirect('/sales/leads');
            return;
        }
        $db = \App\Core\Database::getInstance();
        foreach ($leadIds as $lid) {
            $id = (int)$lid;
            $lead = SalesLead::find($id);
            if (!$lead) { continue; }
            $lead->fill(['assigned_to' => $execId]);
            $lead->save();
            $assign = new LeadAssignment();
            $assign->fill([
                'lead_id' => $id,
                'assigned_to_id' => $execId,
                'assigned_by_id' => $this->currentUser->id ?? 0,
                'assigned_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
            $assign->save();
        }
        $response->redirect('/sales/leads');
    }
}
