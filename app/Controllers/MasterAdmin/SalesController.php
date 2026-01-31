<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SalesController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();

        // 1. KPI Cards
        $totalLeads = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads")['c'] ?? 0);
        $newLeadsToday = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE DATE(created_at) = CURDATE()")['c'] ?? 0);
        $newLeadsWeek = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['c'] ?? 0);
        $convertedLeads = (int)($db->fetchOne("SELECT COUNT(*) as c FROM sales_leads WHERE stage = 'converted'")['c'] ?? 0);
        
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Financials (Using employer_payments for actual revenue)
        $totalRevenue = (float)($db->fetchOne("SELECT SUM(amount) as s FROM employer_payments WHERE status = 'paid'")['s'] ?? 0);
        $revenueMonth = (float)($db->fetchOne("SELECT SUM(amount) as s FROM employer_payments WHERE status = 'paid' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")['s'] ?? 0);
        
        // Pending Payments (Deal Value - Paid Amount) for non-lost leads
        $pendingPayments = (float)($db->fetchOne("SELECT SUM(deal_value - paid_amount) as s FROM sales_leads WHERE stage != 'lost' AND deal_value > paid_amount")['s'] ?? 0);
        
        $paidEmployers = (int)($db->fetchOne("SELECT COUNT(DISTINCT employer_id) as c FROM employer_payments WHERE status = 'paid'")['c'] ?? 0);
        $expiringSubs = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employer_subscriptions WHERE status = 'active' AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);

        $stats = [
            'total_leads' => $totalLeads,
            'new_leads_today' => $newLeadsToday,
            'new_leads_week' => $newLeadsWeek,
            'converted' => $convertedLeads,
            'conversion_rate' => $conversionRate,
            'total_revenue' => $totalRevenue,
            'revenue_month' => $revenueMonth,
            'pending_payments' => $pendingPayments,
            'paid_employers' => $paidEmployers,
            'expiring_subs' => $expiringSubs
        ];

        // 2. Charts Data
        // Funnel
        $funnelData = $db->fetchAll("SELECT stage, COUNT(*) as count FROM sales_leads GROUP BY stage ORDER BY count DESC");
        $funnel = [];
        foreach (['new', 'contacted', 'demo_done', 'follow_up', 'payment_pending', 'converted', 'lost'] as $stage) {
            $found = false;
            foreach ($funnelData as $row) {
                if ($row['stage'] === $stage) {
                    $funnel[$stage] = (int)$row['count'];
                    $found = true;
                    break;
                }
            }
            if (!$found) $funnel[$stage] = 0;
        }

        // Revenue Trend (Last 30 Days)
        $revenueTrend = $db->fetchAll("
            SELECT DATE(created_at) as date, SUM(amount) as total 
            FROM employer_payments 
            WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ");

        // Lead Source
        $sourceData = $db->fetchAll("SELECT source, COUNT(*) as count FROM sales_leads GROUP BY source");
        
        // Sales Team Performance
        $teamPerformance = $db->fetchAll("
            SELECT 
                u.id, 
                COALESCE(u.name, u.email) as name,
                COUNT(sl.id) as total_leads,
                SUM(CASE WHEN sl.stage = 'converted' THEN 1 ELSE 0 END) as converted_leads,
                SUM(sl.paid_amount) as revenue_generated
            FROM users u
            LEFT JOIN sales_leads sl ON u.id = sl.assigned_to
            WHERE u.role IN ('sales_manager', 'sales_executive')
            GROUP BY u.id
            ORDER BY revenue_generated DESC
        ");

        // 3. Lists
        $highValueLeads = $db->fetchAll("
            SELECT * FROM sales_leads 
            WHERE stage != 'lost' AND stage != 'converted' 
            ORDER BY deal_value DESC 
            LIMIT 5
        ");

        $overdueFollowups = $db->fetchAll("
            SELECT sl.*, COALESCE(u.name, u.email) as assigned_name
            FROM sales_leads sl
            LEFT JOIN users u ON sl.assigned_to = u.id
            WHERE sl.next_followup_at < NOW() AND sl.followup_status != 'done'
            ORDER BY sl.next_followup_at ASC
            LIMIT 5
        ");

        $response->view('masteradmin/sales/index', [
            'title' => 'Sales Overview',
            'stats' => $stats,
            'charts' => [
                'funnel' => $funnel,
                'revenue_trend' => $revenueTrend,
                'sources' => $sourceData,
                'team' => $teamPerformance
            ],
            'lists' => [
                'high_value' => $highValueLeads,
                'overdue' => $overdueFollowups
            ]
        ], 200, 'masteradmin/layout');
    }

    public function leads(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $search = trim((string)($request->get('search') ?? ''));
        $stageFilter = (string)($request->get('stage') ?? '');
        $assignedFilter = (int)($request->get('assigned_to') ?? 0);
        $sourceFilter = (string)($request->get('source') ?? '');
        $dateFilter = (string)($request->get('date') ?? '');

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(sl.company_name LIKE :q OR sl.contact_email LIKE :q OR sl.contact_name LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        if ($stageFilter !== '') {
            $where[] = "sl.stage = :stage";
            $params['stage'] = $stageFilter;
        }

        if ($assignedFilter > 0) {
            $where[] = "sl.assigned_to = :assigned";
            $params['assigned'] = $assignedFilter;
        }

        if ($sourceFilter !== '') {
            $where[] = "sl.source = :source";
            $params['source'] = $sourceFilter;
        }

        if ($dateFilter === 'today') {
            $where[] = "DATE(sl.next_followup_at) = CURDATE()";
        } elseif ($dateFilter === 'overdue') {
            $where[] = "sl.next_followup_at < NOW() AND sl.followup_status != 'done'";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Query sales_leads instead of employers
            $sql = "SELECT sl.*, 
                           COALESCE(u.name, u.email) as assigned_name,
                           u.email as assigned_email,
                           e.logo_url
                    FROM sales_leads sl
                    LEFT JOIN users u ON sl.assigned_to = u.id
                    LEFT JOIN employers e ON sl.employer_id = e.id
                    {$whereClause}
                    ORDER BY sl.created_at DESC
                    LIMIT 300";

            $leads = $db->fetchAll($sql, $params);

            // Fetch sales team for filters (Managers and Executives)
            $salesTeam = [];
            try {
                $salesTeam = $db->fetchAll("SELECT id, COALESCE(name, email) as name FROM users WHERE role IN ('sales_manager','sales_executive')");
            } catch (\Exception $e) {
                // Fallback if roles don't exist yet
            }

        $response->view('masteradmin/sales/leads', [
            'title' => 'Sales Leads',
            'leads' => $leads,
            'salesTeam' => $salesTeam,
            'search' => $search,
            'filters' => [
                'stage' => $stageFilter,
                'assigned_to' => $assignedFilter,
                'source' => $sourceFilter,
                'date' => $dateFilter
            ]
        ], 200, 'masteradmin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        
        // Fetch sales team for assignment
        $salesTeam = $db->fetchAll("SELECT id, COALESCE(name, email) as name FROM users WHERE role IN ('sales_manager','sales_executive')");

        $response->view('masteradmin/sales/create', [
            'title' => 'Add New Lead',
            'salesTeam' => $salesTeam
        ], 200, 'masteradmin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $input = $request->getParsedBody();

        $companyName = trim($input['company_name'] ?? '');
        $contactName = trim($input['contact_name'] ?? '');
        $contactEmail = trim($input['contact_email'] ?? '');
        $contactPhone = trim($input['contact_phone'] ?? '');
        $dealValue = (float)($input['deal_value'] ?? 0);
        $currency = $input['currency'] ?? 'INR';
        $source = $input['source'] ?? 'manual';
        $assignedTo = !empty($input['assigned_to']) ? (int)$input['assigned_to'] : null;
        $isUrgent = isset($input['is_urgent']) ? 1 : 0;
        $isFeatured = isset($input['is_featured']) ? 1 : 0;
        $stage = $input['stage'] ?? 'new';
        $nextFollowup = !empty($input['next_followup_at']) ? $input['next_followup_at'] : null;
        $internalNotes = trim($input['internal_notes'] ?? '');
        $followupStatus = !empty($nextFollowup) ? 'pending' : 'none';

        if (empty($companyName) || empty($contactName)) {
             $response->redirect('/master/sales/leads/create');
             return;
        }

        $db->execute("
            INSERT INTO sales_leads (
                company_name, contact_name, contact_email, contact_phone, 
                deal_value, currency, source, assigned_to, 
                is_urgent, is_featured, stage, next_followup_at, followup_status,
                internal_notes, created_at, updated_at
            ) VALUES (
                :company_name, :contact_name, :contact_email, :contact_phone,
                :deal_value, :currency, :source, :assigned_to,
                :is_urgent, :is_featured, :stage, :next_followup_at, :followup_status,
                :internal_notes, NOW(), NOW()
            )
        ", [
            'company_name' => $companyName,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'deal_value' => $dealValue,
            'currency' => $currency,
            'source' => $source,
            'assigned_to' => $assignedTo,
            'is_urgent' => $isUrgent,
            'is_featured' => $isFeatured,
            'stage' => $stage,
            'next_followup_at' => $nextFollowup,
            'followup_status' => $followupStatus,
            'internal_notes' => $internalNotes
        ]);

        $response->redirect('/master/sales/leads');
    }

    public function showLead(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->param('id');

        // Fetch Sales Lead
        $lead = $db->fetchOne(
            "SELECT sl.*, 
                    COALESCE(u.name, u.email) as assigned_name,
                    COALESCE(m.name, m.email) as manager_name
             FROM sales_leads sl
             LEFT JOIN users u ON sl.assigned_to = u.id
             LEFT JOIN users m ON sl.manager_id = m.id
             WHERE sl.id = :id",
            ['id' => $id]
        );

        if (!$lead) {
            $response->redirect('/master/sales/leads');
            return;
        }

        // Fetch Employer details if linked
        $employer = null;
        $subscription = null;
        $payments = [];
        $jobs = [];

        if (!empty($lead['employer_id'])) {
            $employer = $db->fetchOne("SELECT * FROM employers WHERE id = :id", ['id' => $lead['employer_id']]);
            
            if ($employer) {
                $subscription = $db->fetchOne(
                    "SELECT * FROM employer_subscriptions WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 1",
                    ['eid' => $lead['employer_id']]
                );

                $payments = $db->fetchAll(
                    "SELECT * FROM employer_payments WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 20",
                    ['eid' => $lead['employer_id']]
                );

                $jobs = $db->fetchAll(
                    "SELECT id, title, status, created_at FROM jobs WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 20",
                    ['eid' => $lead['employer_id']]
                );
            }
        }

        $response->view('masteradmin/sales/show', [
            'title' => 'Lead Details',
            'lead' => $lead,
            'employer' => $employer,
            'subscription' => $subscription,
            'payments' => $payments,
            'jobs' => $jobs
        ], 200, 'masteradmin/layout');
    }
}
