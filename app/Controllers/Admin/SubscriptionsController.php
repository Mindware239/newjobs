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
        $page = (int)($request->get('page', 1));
        $perPage = (int)($request->get('per_page', 20));
        if ($perPage < 10) $perPage = 10;
        if ($perPage > 100) $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $search = trim((string)$request->get('search', ''));
        $status = (string)$request->get('status', 'all');
        
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = "(e.company_name LIKE :q OR u.email LIKE :q)";
            $params['q'] = "%{$search}%";
        }
        if ($status !== 'all') {
            $where[] = "es.status = :status";
            $params['status'] = $status;
        }
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count
             FROM employer_subscriptions es
             LEFT JOIN employers e ON e.id = es.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             {$whereClause}",
            $params
        )['count'] ?? 0);

        $subscriptions = $db->fetchAll(
            "SELECT es.*, e.company_name, sp.name as plan_name, u.email as employer_email
             FROM employer_subscriptions es
             INNER JOIN (
                 SELECT employer_id, MAX(created_at) as max_created_at
                 FROM employer_subscriptions
                 GROUP BY employer_id
             ) as latest_sub ON es.employer_id = latest_sub.employer_id AND es.created_at = latest_sub.max_created_at
             LEFT JOIN employers e ON e.id = es.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             LEFT JOIN subscription_plans sp ON sp.id = es.plan_id
             {$whereClause}
             ORDER BY es.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = max(1, (int)ceil($total / $perPage));

        $response->view('admin/subscriptions/index', [
            'title' => 'Manage Subscriptions',
            'subscriptions' => $subscriptions,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $subscription = $db->fetchOne(
            "SELECT es.*, 
                    e.company_name, u.email as employer_email, 
                    sp.name as plan_name, sp.price_monthly, sp.price_quarterly, sp.price_annual
             FROM employer_subscriptions es
             LEFT JOIN employers e ON e.id = es.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             LEFT JOIN subscription_plans sp ON sp.id = es.plan_id
             WHERE es.id = :id",
            ['id' => $id]
        );

        if (!$subscription) {
            $response->redirect('/admin/subscriptions');
            return;
        }

        $payments = [];
        try {
            $payments = $db->fetchAll(
                "SELECT * FROM subscription_payments WHERE subscription_id = :sid ORDER BY created_at DESC",
                ['sid' => $id]
            );
        } catch (\Throwable $t) {}

        $plans = [];
        try {
            $plans = $db->fetchAll("SELECT id, name FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC, price_monthly ASC");
        } catch (\Throwable $t) {}

        $response->view('admin/subscriptions/show', [
            'title' => 'Subscription Details',
            'subscription' => $subscription,
            'payments' => $payments,
            'plans' => $plans,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function plans(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $plans = $db->fetchAll("SELECT * FROM subscription_plans ORDER BY sort_order ASC, price_monthly ASC");
        $editId = (int)($request->get('edit', 0));

        $response->view('admin/subscriptions/plans', [
            'title' => 'Subscription Plans',
            'plans' => $plans,
            'editId' => $editId,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    /**
     * Download subscription payment invoice as PDF (admin-safe, no employer redirect)
     */
    public function downloadInvoice(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $paymentId = (int)$request->param('payment_id');
        $db = Database::getInstance();
        $p = $db->fetchOne("SELECT * FROM subscription_payments WHERE id = :id", ['id' => $paymentId]);
        if (!$p) {
            $response->setStatusCode(404);
            $response->setBody('Invoice not found');
            return;
        }
        $employer = $db->fetchOne("SELECT e.*, u.email FROM employers e LEFT JOIN users u ON u.id = e.user_id WHERE e.id = :id", [
            'id' => (int)($p['employer_id'] ?? 0)
        ]) ?? [];
        $sub = $db->fetchOne("SELECT es.*, sp.name as plan_name FROM employer_subscriptions es LEFT JOIN subscription_plans sp ON sp.id = es.plan_id WHERE es.id = :id", [
            'id' => (int)($p['subscription_id'] ?? 0)
        ]) ?? [];
        $amount = (float)($p['amount'] ?? 0);
        $taxRate = (float)($_ENV['TAX_RATE'] ?? 0.18);
        $tax = round($amount * $taxRate, 2);
        $total = $amount + $tax;
        $invoiceNo = $p['invoice_number'] ?? ('INV-' . date('Ym') . '-' . sprintf('%06d', $paymentId));
        $createdAt = !empty($p['created_at']) ? date('d M Y, h:i A', strtotime($p['created_at'])) : date('d M Y, h:i A');
        $companyName  = $_ENV['COMPANY_NAME'] ?? 'Mindware Infotech';
        $companyAddr  = $_ENV['COMPANY_ADDRESS'] ?? 'Mindware, S-4, Pankaj Plaza, Pocket-7, Plot-7, Dwarka Sector-12, Delhi-110078';
        $companyCity  = $_ENV['COMPANY_CITY'] ?? 'Dwarka';
        $companyState = $_ENV['COMPANY_STATE'] ?? 'Delhi';
        $companyZip   = $_ENV['COMPANY_ZIP'] ?? '110078';
        $companyEmail = $_ENV['COMPANY_EMAIL'] ?? 'sales@mindwareinfotech.com';
        $companyPhone = $_ENV['COMPANY_PHONE'] ?? '+91-8527522688';
        $gst          = $_ENV['COMPANY_GSTIN'] ?? '07AFDPM9463K1ZY';
        $planName     = $sub['plan_name'] ?? ucfirst((string)($p['billing_cycle'] ?? 'subscription'));
        // Employer address (optional JSON)
        $addr = [];
        if (!empty($employer['address'])) {
            $decoded = json_decode((string)$employer['address'], true);
            if (is_array($decoded)) $addr = $decoded;
        }
        $eCompany = $employer['company_name'] ?? 'Employer';
        $eEmail   = $employer['email'] ?? '';
        $eStreet  = $addr['street'] ?? '';
        $eCity    = $addr['city'] ?? '';
        $eState   = $addr['state'] ?? '';
        $eZip     = $addr['postal_code'] ?? '';
        // Build minimal PDF HTML
        $html = '<!doctype html><html><head><meta charset="utf-8"><title>Invoice '.$invoiceNo.'</title>
        <style>
        body{font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;color:#111}
        .wrap{max-width:800px;margin:0 auto}
        .row{display:flex;justify-content:space-between;gap:20px}
        .box{border:1px solid #0a2d6c;padding:10px}
        h1{color:#0a2d6c;margin:0 0 6px 0}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{border:1px solid #0a2d6c;padding:8px}
        th{background:#0a2d6c;color:#fff;text-align:left}
        .totals td{font-weight:bold}
        .muted{color:#555}
        </style></head><body><div class="wrap">
        <div class="row">
          <div>
            <h1>INVOICE</h1>
            <div class="muted">Invoice #: '.$invoiceNo.'<br>Date & Time: '.$createdAt.'<br>Payment ID: '.htmlspecialchars((string)($p['gateway_payment_id'] ?? '-')).'<br>Gateway: '.strtoupper((string)($p['gateway'] ?? 'RAZORPAY')).'</div>
          </div>
          <div class="box">
            <div><strong>'.htmlspecialchars($companyName).'</strong></div>
            <div>'.nl2br(htmlspecialchars($companyAddr)).'</div>
            <div>'.$companyCity.', '.$companyState.' '.$companyZip.'</div>
            <div>'.$companyEmail.' | '.$companyPhone.'</div>
            <div><strong>GST:</strong> '.$gst.'</div>
          </div>
        </div>
        <div class="row" style="margin-top:10px">
          <div class="box" style="flex:1">
            <div><strong>Billed To</strong></div>
            <div>'.htmlspecialchars($eCompany).'</div>
            <div>'.htmlspecialchars(trim($eStreet)).'</div>
            <div>'.htmlspecialchars(trim($eCity.(($eCity&&$eState)?', ':'').$eState.' '.$eZip)).'</div>
            <div>'.htmlspecialchars($eEmail).'</div>
          </div>
        </div>
        <table>
          <thead><tr><th>Description</th><th style="text-align:center">Cycle</th><th style="text-align:center">Qty</th><th style="text-align:right">Unit</th><th style="text-align:right">Total</th></tr></thead>
          <tbody>
            <tr>
              <td>Subscription - '.htmlspecialchars($planName).'</td>
              <td style="text-align:center">'.ucfirst((string)($p['billing_cycle'] ?? 'monthly')).'</td>
              <td style="text-align:center">1</td>
              <td style="text-align:right">₹'.number_format($amount,2).'</td>
              <td style="text-align:right">₹'.number_format($amount,2).'</td>
            </tr>
          </tbody>
        </table>
        <table style="margin-top:8px">
          <tbody>
            <tr class="totals"><td style="width:70%">Subtotal</td><td style="text-align:right">₹'.number_format($amount,2).'</td></tr>
            <tr class="totals"><td>Tax ('.($taxRate*100).'%)</td><td style="text-align:right">₹'.number_format($tax,2).'</td></tr>
            <tr class="totals"><td>Grand Total</td><td style="text-align:right">₹'.number_format($total,2).'</td></tr>
          </tbody>
        </table>
        <p class="muted" style="margin-top:12px">This is a system generated invoice. No signature required.</p>
        </div></body></html>';
        // Render with Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();
        // Save to tmp and stream
        $dir = __DIR__ . '/../../../storage/tmp/invoices';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $tmpPath = $dir . '/invoice_' . $paymentId . '.pdf';
        file_put_contents($tmpPath, $pdfOutput);
        $response->download($tmpPath, $invoiceNo . '.pdf');
    }
    public function createPlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $data = $request->post();
        $db = Database::getInstance();

        // Build features JSON from form arrays if provided
        $featuresArray = [];
        $featureTexts = (array)($data['features'] ?? []);
        $featureEnabled = (array)($data['features_enabled'] ?? []);
        $featureIcons = (array)($data['features_icon'] ?? []);
        $featureCats = (array)($data['features_category'] ?? []);
        foreach ($featureTexts as $idx => $txt) {
            $txt = trim((string)$txt);
            if ($txt === '') continue;
            $enabled = isset($featureEnabled[$idx]) ? 1 : 1;
            $featuresArray[] = [
                'feature_text' => $txt,
                'is_enabled' => $enabled,
                'icon' => trim((string)($featureIcons[$idx] ?? '')),
                'category' => trim((string)($featureCats[$idx] ?? ''))
            ];
        }
        $db->query(
            "INSERT INTO subscription_plans (
                name, slug, description, plan_for,
                price_monthly, price_quarterly, price_annual,
                default_billing_cycle,
                max_job_posts, max_contacts_per_month, max_resume_downloads, max_chat_messages,
                resume_download_enabled, chat_enabled, candidate_mobile_visible, job_post_boost, ai_matching, analytics_dashboard,
                is_featured, sort_order, features, is_active, created_at
             )
             VALUES (
                :name, :slug, :description, :plan_for,
                :price_monthly, :price_quarterly, :price_annual,
                :default_billing_cycle,
                :max_job_posts, :max_contacts_per_month, :max_resume_downloads, :max_chat_messages,
                :resume_download_enabled, :chat_enabled, :candidate_mobile_visible, :job_post_boost, :ai_matching, :analytics_dashboard,
                :is_featured, :sort_order, :features, :is_active, NOW()
             )",
            [
                'name' => $data['name'] ?? '',
                'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $data['name'] ?? '')),
                'description' => $data['description'] ?? '',
                'plan_for' => strtolower((string)($data['plan_for'] ?? 'employer')),
                'price_monthly' => (float)($data['price_monthly'] ?? 0),
                'price_quarterly' => (float)($data['price_quarterly'] ?? 0),
                'price_annual' => (float)($data['price_annual'] ?? 0),
                'default_billing_cycle' => (isset($data['default_billing_cycle']) && in_array($data['default_billing_cycle'], ['monthly', 'quarterly', 'annual'], true)) ? $data['default_billing_cycle'] : 'monthly',
                'max_job_posts' => (int)($data['max_job_posts'] ?? 0),
                'max_contacts_per_month' => (int)($data['max_contacts_per_month'] ?? 0),
                'max_resume_downloads' => (int)($data['max_resume_downloads'] ?? 0),
                'max_chat_messages' => (int)($data['max_chat_messages'] ?? 0),
                'resume_download_enabled' => isset($data['resume_download_enabled']) ? 1 : 0,
                'chat_enabled' => isset($data['chat_enabled']) ? 1 : 0,
                'candidate_mobile_visible' => isset($data['candidate_mobile_visible']) ? 1 : 0,
                'job_post_boost' => isset($data['job_post_boost']) ? 1 : 0,
                'ai_matching' => isset($data['ai_matching']) ? 1 : 0,
                'analytics_dashboard' => isset($data['analytics_dashboard']) ? 1 : 0,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'features' => json_encode($featuresArray),
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

        // Build features JSON
        $featuresArray = [];
        $featureTexts = (array)($data['features'] ?? []);
        $featureEnabled = (array)($data['features_enabled'] ?? []);
        $featureIcons = (array)($data['features_icon'] ?? []);
        $featureCats = (array)($data['features_category'] ?? []);
        foreach ($featureTexts as $idx => $txt) {
            $txt = trim((string)$txt);
            if ($txt === '') continue;
            $enabled = isset($featureEnabled[$idx]) ? 1 : 1;
            $featuresArray[] = [
                'feature_text' => $txt,
                'is_enabled' => $enabled,
                'icon' => trim((string)($featureIcons[$idx] ?? '')),
                'category' => trim((string)($featureCats[$idx] ?? ''))
            ];
        }
        $db->query(
            "UPDATE subscription_plans 
             SET name = :name,
                 description = :description,
                 plan_for = :plan_for,
                 price_monthly = :price_monthly, 
                 price_quarterly = :price_quarterly,
                 price_annual = :price_annual,
                 default_billing_cycle = :default_billing_cycle,
                 max_job_posts = :max_job_posts,
                 max_contacts_per_month = :max_contacts_per_month,
                 max_resume_downloads = :max_resume_downloads,
                 max_chat_messages = :max_chat_messages,
                 resume_download_enabled = :resume_download_enabled,
                 chat_enabled = :chat_enabled,
                 candidate_mobile_visible = :candidate_mobile_visible,
                 job_post_boost = :job_post_boost,
                 ai_matching = :ai_matching,
                 analytics_dashboard = :analytics_dashboard,
                 is_featured = :is_featured,
                 sort_order = :sort_order,
                 features = :features,
                 is_active = :is_active
             WHERE id = :id",
            [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'plan_for' => strtolower((string)($data['plan_for'] ?? 'employer')),
                'price_monthly' => (float)($data['price_monthly'] ?? 0),
                'price_quarterly' => (float)($data['price_quarterly'] ?? 0),
                'price_annual' => (float)($data['price_annual'] ?? 0),
                'default_billing_cycle' => (isset($data['default_billing_cycle']) && in_array($data['default_billing_cycle'], ['monthly', 'quarterly', 'annual'], true)) ? $data['default_billing_cycle'] : 'monthly',
                'max_job_posts' => (int)($data['max_job_posts'] ?? 0),
                'max_contacts_per_month' => (int)($data['max_contacts_per_month'] ?? 0),
                'max_resume_downloads' => (int)($data['max_resume_downloads'] ?? 0),
                'max_chat_messages' => (int)($data['max_chat_messages'] ?? 0),
                'resume_download_enabled' => isset($data['resume_download_enabled']) ? 1 : 0,
                'chat_enabled' => isset($data['chat_enabled']) ? 1 : 0,
                'candidate_mobile_visible' => isset($data['candidate_mobile_visible']) ? 1 : 0,
                'job_post_boost' => isset($data['job_post_boost']) ? 1 : 0,
                'ai_matching' => isset($data['ai_matching']) ? 1 : 0,
                'analytics_dashboard' => isset($data['analytics_dashboard']) ? 1 : 0,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'features' => json_encode($featuresArray),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]
        );

        $this->logAction('update_plan', array_merge($data, ['plan_id' => $id]));
        $response->redirect('/admin/subscriptions/plans');
    }

    public function editPlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $plans = $db->fetchAll("SELECT * FROM subscription_plans ORDER BY sort_order ASC, price_monthly ASC");
        $response->view('admin/subscriptions/plans', [
            'title' => 'Edit Plan',
            'plans' => $plans,
            'editId' => $id,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function duplicatePlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM subscription_plans WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/subscriptions/plans?error=Plan%20not%20found');
            return;
        }
        $newName = ($row['name'] ?? 'Plan') . ' Copy';
        $newSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $newName)) . '-' . substr(uniqid('', true), -4);
        $row['name'] = $newName;
        $row['slug'] = $newSlug;
        $row['sort_order'] = (int)($row['sort_order'] ?? 0) + 1;
        unset($row['id'], $row['created_at'], $row['updated_at']);
        $columns = array_keys($row);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = "INSERT INTO subscription_plans (" . implode(',', $columns) . ", created_at) VALUES (" . implode(',', $placeholders) . ", NOW())";
        $params = [];
        foreach ($row as $k => $v) { $params[$k] = $v; }
        $db->query($sql, $params);
        $response->redirect('/admin/subscriptions/plans?success=Plan%20duplicated');
    }

    public function deletePlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        // Check if plan is used by any subscription
        $inUse = $db->fetchOne("SELECT COUNT(*) as count FROM employer_subscriptions WHERE plan_id = :id", ['id' => $id]);
        
        try {
            if ($inUse && (int)$inUse['count'] > 0) {
                // If plan is in use, we can't delete it directly due to FK constraints.
                // We will mark it as deleted (is_active = 0) and remove its slug to avoid conflicts if a new plan with same name is created.
                $db->query("UPDATE subscription_plans SET is_active = 0, slug = CONCAT(slug, '-deleted-', :id_val) WHERE id = :id", ['id' => $id, 'id_val' => $id]);
                $msg = 'Plan is currently in use by ' . $inUse['count'] . ' subscriptions. It has been deactivated and archived instead of deleted.';
            } else {
                // Not in use, safe to delete
                $db->query("DELETE FROM subscription_plans WHERE id = :id", ['id' => $id]);
                $msg = 'Plan deleted successfully.';
            }

            $this->logAction('delete_plan', ['plan_id' => $id, 'archived' => ($inUse && (int)$inUse['count'] > 0)]);
            
            if ($request->isAjax()) {
                $response->json(['success' => true, 'message' => $msg]);
                return;
            }
            $response->redirect('/admin/subscriptions/plans?success=' . urlencode($msg));
        } catch (\Exception $e) {
            error_log("Delete Plan Error: " . $e->getMessage());
            if ($request->isAjax()) {
                $response->json(['error' => 'Failed to process request: ' . $e->getMessage()], 500);
                return;
            }
            $response->redirect('/admin/subscriptions/plans?error=Failed%20to%20process%20request:%20' . urlencode($e->getMessage()));
        }
    }

    public function updateStatus(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $status = (string)$request->post('status', 'active');
        $reason = (string)$request->post('reason', '');
        $allowed = ['active', 'inactive', 'expired', 'cancelled', 'trial', 'grace', 'suspended'];
        if (!in_array($status, $allowed, true)) {
            $response->redirect("/admin/subscriptions/{$id}?error=Invalid%20status");
            return;
        }
        $db = Database::getInstance();
        $params = ['id' => $id, 'status' => $status];
        $sql = "UPDATE employer_subscriptions SET status = :status WHERE id = :id";
        if ($status === 'cancelled') {
            $sql = "UPDATE employer_subscriptions SET status = :status, cancelled_at = NOW(), cancellation_reason = :reason WHERE id = :id";
            $params['reason'] = $reason;
        }
        $db->query($sql, $params);
        $this->recordHistory($id, ['status' => $status, 'reason' => $reason]);
        $response->redirect("/admin/subscriptions/{$id}?success=Status%20updated");
    }

    public function changePlan(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $planId = (int)$request->post('plan_id');
        $billing = (string)$request->post('billing_cycle', 'monthly');
        if (!$planId) {
            $response->redirect("/admin/subscriptions/{$id}?error=Plan%20required");
            return;
        }
        if (!in_array($billing, ['monthly', 'quarterly', 'annual'], true)) {
            $billing = 'monthly';
        }
        $interval = $billing === 'annual' ? '12 MONTH' : ($billing === 'quarterly' ? '3 MONTH' : '1 MONTH');
        $db = Database::getInstance();
        $db->query(
            "UPDATE employer_subscriptions 
             SET plan_id = :pid, billing_cycle = :bc, status = 'active',
                 started_at = COALESCE(started_at, NOW()),
                 expires_at = DATE_ADD(NOW(), INTERVAL {$interval}),
                 next_billing_date = DATE_ADD(NOW(), INTERVAL {$interval})
             WHERE id = :id",
            ['pid' => $planId, 'bc' => $billing, 'id' => $id]
        );
        $this->recordHistory($id, ['plan_id' => $planId, 'billing_cycle' => $billing]);
        $response->redirect("/admin/subscriptions/{$id}?success=Plan%20updated");
    }

    public function extend(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $days = (int)$request->post('days', 0);
        if ($days <= 0) {
            $response->redirect("/admin/subscriptions/{$id}?error=Invalid%20days");
            return;
        }
        $db = Database::getInstance();
        $db->query(
            "UPDATE employer_subscriptions 
             SET expires_at = DATE_ADD(COALESCE(expires_at, NOW()), INTERVAL :days DAY)
             WHERE id = :id",
            ['days' => $days, 'id' => $id]
        );
        $this->recordHistory($id, ['extended_days' => $days]);
        $response->redirect("/admin/subscriptions/{$id}?success=Extended%20by%20{$days}%20days");
    }

    public function toggleAutoRenew(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $auto = $request->post('auto_renew') ? 1 : 0;
        $db = Database::getInstance();
        $db->query("UPDATE employer_subscriptions SET auto_renew = :a WHERE id = :id", ['a' => $auto, 'id' => $id]);
        $this->recordHistory($id, ['auto_renew' => $auto]);
        $response->redirect("/admin/subscriptions/{$id}?success=Auto%20renew%20updated");
    }

    public function resetUsage(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $db->query(
            "UPDATE employer_subscriptions 
             SET contacts_used_this_month = 0,
                 resume_downloads_used_this_month = 0,
                 chat_messages_used_this_month = 0,
                 last_usage_reset_at = NOW()
             WHERE id = :id",
            ['id' => $id]
        );
        $this->recordHistory($id, ['reset_usage' => 1]);
        $response->redirect("/admin/subscriptions/{$id}?success=Usage%20reset");
    }

    public function setGrace(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $days = (int)$request->post('grace_days', 3);
        if ($days < 0) $days = 0;
        $db = Database::getInstance();
        $db->query(
            "UPDATE employer_subscriptions 
             SET grace_period_ends_at = DATE_ADD(NOW(), INTERVAL :days DAY), status = CASE WHEN :days > 0 THEN 'grace' ELSE status END
             WHERE id = :id",
            ['days' => $days, 'id' => $id]
        );
        $this->recordHistory($id, ['grace_days' => $days]);
        $response->redirect("/admin/subscriptions/{$id}?success=Grace%20period%20updated");
    }

    public function addCredit(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $type = (string)$request->post('type', 'job_posts');
        $amount = (int)$request->post('amount', 0);
        $note = (string)$request->post('note', '');
        if ($amount <= 0) {
            $response->redirect("/admin/subscriptions/{$id}?error=Invalid%20amount");
            return;
        }
        $db = Database::getInstance();
        try {
            $db->query(
                "INSERT INTO subscription_credits (subscription_id, employer_id, type, amount, note, created_by_user_id, applied, created_at)
                 SELECT es.id, es.employer_id, :type, :amount, :note, :uid, 0, NOW() FROM employer_subscriptions es WHERE es.id = :id",
                ['type' => $type, 'amount' => $amount, 'note' => $note, 'uid' => (int)$this->currentUser->id, 'id' => $id]
            );
        } catch (\Throwable $t) {}
        if (in_array($type, ['job_posts', 'contacts', 'resume_downloads', 'chat_messages'], true)) {
            $fieldMap = [
                'job_posts' => 'job_posts_used',
                'contacts' => 'contacts_used_this_month',
                'resume_downloads' => 'resume_downloads_used_this_month',
                'chat_messages' => 'chat_messages_used_this_month'
            ];
            $field = $fieldMap[$type] ?? null;
            if ($field) {
                $db->query("UPDATE employer_subscriptions SET {$field} = GREATEST({$field} - :amt, 0) WHERE id = :id", ['amt' => $amount, 'id' => $id]);
            }
        }
        if ($type === 'days') {
            $db->query("UPDATE employer_subscriptions SET expires_at = DATE_ADD(COALESCE(expires_at, NOW()), INTERVAL :amt DAY) WHERE id = :id", ['amt' => $amount, 'id' => $id]);
        }
        $this->recordHistory($id, ['credit_type' => $type, 'credit_amount' => $amount]);
        $response->redirect("/admin/subscriptions/{$id}?success=Credit%20added");
    }

    public function regenerateInvoice(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $paymentId = (int)$request->param('payment_id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM subscription_payments WHERE id = :id", ['id' => $paymentId]);
        if (!$row) {
            $response->redirect('/admin/subscriptions?error=Payment%20not%20found');
            return;
        }
        $prefix = 'INV';
        $number = $prefix . '-' . date('Ym') . '-' . strtoupper(substr(uniqid('', true), -6));
        $url = '/employer/invoices/' . $paymentId;
        $db->query("UPDATE subscription_payments SET invoice_number = :num, invoice_url = :url WHERE id = :id", ['num' => $number, 'url' => $url, 'id' => $paymentId]);
        $response->redirect("/admin/subscriptions/{$row['subscription_id']}?success=Invoice%20regenerated");
    }

    public function cleanupDuplicates(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $db = Database::getInstance();
        $groups = $db->fetchAll(
            "SELECT employer_id, COUNT(*) as c 
             FROM employer_subscriptions 
             WHERE status IN ('active','trial','grace')
             GROUP BY employer_id
             HAVING c > 1"
        );
        $processed = 0;
        $demoted = 0;
        $reassigned = 0;
        foreach ($groups as $g) {
            $eid = (int)($g['employer_id'] ?? 0);
            if ($eid <= 0) continue;
            $subs = $db->fetchAll(
                "SELECT * FROM employer_subscriptions 
                 WHERE employer_id = :eid AND status IN ('active','trial','grace')
                 ORDER BY COALESCE(expires_at, '0000-00-00') DESC, COALESCE(started_at, created_at) DESC",
                ['eid' => $eid]
            );
            if (count($subs) <= 1) continue;
            $processed++;
            $primary = $subs[0];
            $primaryId = (int)$primary['id'];
            $db->beginTransaction();
            try {
                for ($i = 1; $i < count($subs); $i++) {
                    $other = $subs[$i];
                    $otherId = (int)$other['id'];
                    // Demote
                    $db->query(
                        "UPDATE employer_subscriptions 
                         SET status = 'inactive', grace_period_ends_at = NULL, cancelled_at = NOW(), cancellation_reason = 'duplicate cleanup' 
                         WHERE id = :id",
                        ['id' => $otherId]
                    );
                    $demoted++;
                    // Reassign pending payments to primary
                    $reassigned += (int)$db->query(
                        "UPDATE subscription_payments 
                         SET subscription_id = :primary 
                         WHERE subscription_id = :old AND status = 'pending'",
                        ['primary' => $primaryId, 'old' => $otherId]
                    )->rowCount();
                    // Log history
                    $this->recordHistory($otherId, ['status' => 'inactive', 'reason' => 'duplicate cleanup']);
                }
                $db->commit();
            } catch (\Throwable $t) {
                $db->rollback();
            }
        }
        $response->redirect("/admin/subscriptions?success=Cleanup%20done&processed={$processed}&demoted={$demoted}&reassigned={$reassigned}");
    }

    private function recordHistory(int $subscriptionId, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $sub = $db->fetchOne("SELECT employer_id, plan_id, started_at, expires_at FROM employer_subscriptions WHERE id = :id", ['id' => $subscriptionId]);
            if (!$sub) return;
            $db->query(
                "INSERT INTO subscription_history (subscription_id, employer_id, plan_id, status, started_at, ends_at, changed_by_user_id, change_reason, created_at)
                 VALUES (:sid, :eid, :pid, :status, :start, :end, :uid, :reason, NOW())",
                [
                    'sid' => $subscriptionId,
                    'eid' => (int)$sub['employer_id'],
                    'pid' => (int)$sub['plan_id'],
                    'status' => (string)($data['status'] ?? ''),
                    'start' => $sub['started_at'] ?? null,
                    'end' => $sub['expires_at'] ?? null,
                    'uid' => (int)$this->currentUser->id,
                    'reason' => (string)($data['reason'] ?? '')
                ]
            );
        } catch (\Throwable $t) {}
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
