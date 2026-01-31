<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Employer;
use App\Models\User;
use App\Models\EmployerKycDocument;

class EmployersController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $kycStatus = $request->get('kyc_status', 'all');

        // Filter by user status; employers table does not have a status column
        $where = ["u.status != 'deleted'"];
        $params = [];

        if ($search) {
            $where[] = "(e.company_name LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== 'all') {
            $where[] = "u.status = :status";
            $params['status'] = $status;
        }

        if ($kycStatus !== 'all') {
            $where[] = "e.kyc_status = :kyc_status";
            $params['kyc_status'] = $kycStatus;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM employers e
             INNER JOIN users u ON u.id = e.user_id
             {$whereClause}",
            $params
        )['count'] ?? 0);

        // Get employers
        $employers = $db->fetchAll(
            "SELECT e.*, u.email, u.phone, u.status as user_status, u.last_login,
                    (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = e.id) as jobs_count,
                    (SELECT COUNT(*) FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = e.id) as applications_received,
                    (SELECT name FROM subscription_plans sp INNER JOIN employer_subscriptions es ON es.plan_id = sp.id WHERE es.employer_id = e.id AND es.status = 'active' LIMIT 1) as subscription_plan
             FROM employers e
             INNER JOIN users u ON u.id = e.user_id
             {$whereClause}
             ORDER BY e.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        $response->view('admin/employers/index', [
            'title' => 'Manage Employers',
            'employers' => $employers,
            'companies' => $this->mapCompaniesByEmployerId($employers),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'kyc_status' => $kycStatus
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    private function mapCompaniesByEmployerId(array $employers): array
    {
        $db = Database::getInstance();
        $ids = array_filter(array_map(fn($e) => (int)($e['id'] ?? 0), $employers));
        if (empty($ids)) return [];
        $in = implode(',', array_map('intval', $ids));
        try {
            $rows = $db->fetchAll("SELECT id, employer_id, is_featured, featured_order FROM companies WHERE employer_id IN ($in)");
            $map = [];
            foreach ($rows as $r) {
                $map[(int)$r['employer_id']] = $r;
            }
            return $map;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setFeatured(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $employerId = (int)$request->param('id');
        $isFeatured = (int)($request->post('is_featured') ?? 0) === 1;
        $order = (int)($request->post('featured_order') ?? 0);

        $db = Database::getInstance();
        $companyModel = new \App\Models\Company();
        $companyModel->ensureFeaturedSchema();

        // Ensure company record exists for employer
        $company = $companyModel->findByEmployerId($employerId);
        if (!$company) {
            $employer = $db->fetchOne("SELECT * FROM employers WHERE id = :id", ['id' => $employerId]);
            if ($employer) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $employer['company_name'] ?? 'company'), '-'));
                try {
                    $db->execute(
                        "INSERT INTO companies (employer_id, name, short_name, slug, logo_url, website, created_at, updated_at) 
                         VALUES (:eid, :name, :short, :slug, :logo, :web, NOW(), NOW())",
                        [
                            'eid' => $employerId,
                            'name' => $employer['company_name'] ?? 'Company',
                            'short' => $employer['company_name'] ?? 'Company',
                            'slug' => $slug,
                            'logo' => $employer['logo_url'] ?? null,
                            'web' => $employer['website'] ?? null
                        ]
                    );
                    $company = $companyModel->findByEmployerId($employerId);
                } catch (\Exception $e) {
                    error_log('Failed to create company for employer: ' . $e->getMessage());
                }
            }
        }

        if (!empty($company['id'])) {
            $companyModel->setFeatured((int)$company['id'], $isFeatured, $order);
        }

        $response->redirect('/admin/employers');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $employer = $db->fetchOne(
            "SELECT e.*, u.email, u.phone, u.status as user_status, u.last_login, u.created_at as user_created_at
             FROM employers e
             INNER JOIN users u ON u.id = e.user_id
             WHERE e.id = :id",
            ['id' => $id]
        );

        if (!$employer) {
            $response->redirect('/admin/employers');
            return;
        }

        // Get jobs
        $jobs = $db->fetchAll(
            "SELECT * FROM jobs WHERE employer_id = :employer_id ORDER BY created_at DESC",
            ['employer_id' => $id]
        );

        // Get subscription
        $subscription = $db->fetchOne(
            "SELECT es.*, sp.name as plan_name, sp.price_monthly, sp.price_quarterly, sp.price_annual
             FROM employer_subscriptions es
             INNER JOIN subscription_plans sp ON sp.id = es.plan_id
             WHERE es.employer_id = :employer_id AND es.status = 'active'
             ORDER BY es.created_at DESC LIMIT 1",
            ['employer_id' => $id]
        );

        // Get payments
        $payments = $db->fetchAll(
            "SELECT * FROM employer_payments WHERE employer_id = :employer_id ORDER BY created_at DESC LIMIT 20",
            ['employer_id' => $id]
        );

        // Get KYC documents (use ID for ordering to avoid relying on created_at column)
        $kycDocuments = $db->fetchAll(
            "SELECT * FROM employer_kyc_documents WHERE employer_id = :employer_id ORDER BY id DESC",
            ['employer_id' => $id]
        );

        // Get login history (ordered by login time)
        $loginHistory = $db->fetchAll(
            "SELECT * FROM login_history WHERE user_id = :user_id ORDER BY logged_in_at DESC LIMIT 20",
            ['user_id' => $employer['user_id']]
        );

        $response->view('admin/employers/show', [
            'title' => 'Employer Details - ' . ($employer['company_name'] ?? 'Unknown'),
            'employer' => $employer,
            'jobs' => $jobs,
            'subscription' => $subscription,
            'payments' => $payments,
            'kycDocuments' => $kycDocuments,
            'loginHistory' => $loginHistory,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function approveKyc(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $db->query(
            "UPDATE employers SET kyc_status = 'approved', kyc_approved_at = NOW() WHERE id = :id",
            ['id' => $id]
        );

        $this->logAction('approve_kyc', ['employer_id' => $id]);
        
        try {
            $row = $db->fetchOne("SELECT u.email, e.company_name FROM employers e INNER JOIN users u ON u.id = e.user_id WHERE e.id = :id", ['id' => $id]);
            if ($row && !empty($row['email'])) {
                \App\Services\NotificationService::queueEmail(
                    $row['email'],
                    'employer_kyc_approved',
                    [
                        'company_name' => (string)($row['company_name'] ?? ''),
                        'employer_id' => (int)$id
                    ]
                );
            }
        } catch (\Throwable $t) {}

        $response->redirect('/admin/employers/' . $id);
    }

    public function rejectKyc(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $reason = $request->post('reason', '');
        $db = Database::getInstance();

        $db->query(
            "UPDATE employers SET kyc_status = 'rejected', kyc_rejection_reason = :reason WHERE id = :id",
            ['id' => $id, 'reason' => $reason]
        );

        $this->logAction('reject_kyc', ['employer_id' => $id, 'reason' => $reason]);
        
        try {
            $row = $db->fetchOne("SELECT u.email, e.company_name FROM employers e INNER JOIN users u ON u.id = e.user_id WHERE e.id = :id", ['id' => $id]);
            if ($row && !empty($row['email'])) {
                \App\Services\NotificationService::queueEmail(
                    $row['email'],
                    'employer_kyc_rejected',
                    [
                        'company_name' => (string)($row['company_name'] ?? ''),
                        'reason' => (string)$reason,
                        'employer_id' => (int)$id
                    ]
                );
            }
        } catch (\Throwable $t) {}

        $response->redirect('/admin/employers/' . $id);
    }

    public function approveKycDocument(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $employerId = (int)$request->param('id');
        $docId = (int)$request->param('doc_id');
        $notes = $request->post('notes', '');

        $doc = EmployerKycDocument::find($docId);
        /** @var \App\Models\EmployerKycDocument|null $doc */
        if ($doc && (int)($doc->attributes['employer_id'] ?? 0) === $employerId) {
            $doc->approve($this->currentUser->id, $notes);
            $this->logAction('approve_kyc_document', ['employer_id' => $employerId, 'doc_id' => $docId]);
            $this->recalculateKycStatus($employerId);
        }

        $response->redirect('/admin/employers/' . $employerId);
    }

    public function rejectKycDocument(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $employerId = (int)$request->param('id');
        $docId = (int)$request->param('doc_id');
        $notes = $request->post('notes', '');

        $doc = EmployerKycDocument::find($docId);
        /** @var \App\Models\EmployerKycDocument|null $doc */
        if ($doc && (int)($doc->attributes['employer_id'] ?? 0) === $employerId) {
            $doc->reject($this->currentUser->id, $notes);
            $this->logAction('reject_kyc_document', ['employer_id' => $employerId, 'doc_id' => $docId, 'notes' => $notes]);
            $this->recalculateKycStatus($employerId);
        }

        $response->redirect('/admin/employers/' . $employerId);
    }

    private function recalculateKycStatus(int $employerId): void
    {
        try {
            $db = Database::getInstance();
            $counts = $db->fetchOne(
                "SELECT 
                    COUNT(*) AS total,
                    SUM(review_status = 'approved') AS approved,
                    SUM(review_status = 'rejected') AS rejected
                 FROM employer_kyc_documents
                 WHERE employer_id = :employer_id",
                ['employer_id' => $employerId]
            );

            $total = (int)($counts['total'] ?? 0);
            $approved = (int)($counts['approved'] ?? 0);
            $rejected = (int)($counts['rejected'] ?? 0);

            $status = 'pending';
            if ($total > 0 && $approved === $total && $rejected === 0) {
                $status = 'approved';
            } elseif ($rejected > 0) {
                $status = 'rejected';
            } else {
                $status = 'pending';
            }

            $db->query(
                "UPDATE employers SET kyc_status = :status WHERE id = :id",
                ['status' => $status, 'id' => $employerId]
            );
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function block(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $employer = Employer::find($id);

        if ($employer) {
            $user = User::find($employer->user_id);
            if ($user) {
                $user->status = 'blocked';
                $user->save();

                $this->logAction('block_employer', ['employer_id' => $id]);
            }
        }

        $response->redirect('/admin/employers/' . $id);
    }

    public function unblock(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $employer = Employer::find($id);

        if ($employer) {
            $user = User::find($employer->user_id);
            if ($user) {
                $user->status = 'active';
                $user->save();

                $this->logAction('unblock_employer', ['employer_id' => $id]);
            }
        }

        $response->redirect('/admin/employers/' . $id);
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
                    'entity_type' => 'employer',
                    'entity_id' => $data['employer_id'] ?? null,
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

