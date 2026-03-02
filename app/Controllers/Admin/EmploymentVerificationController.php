<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\EmploymentVerificationService;
use App\Services\EmploymentVerificationPDFService;

class EmploymentVerificationController extends BaseController
{
    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        // Ensure verification tables exist before querying
        \App\Services\EmploymentVerificationService::ensureSchema();
        $db = Database::getInstance();
        $status = (string)$request->get('status', 'all');
        $q = trim((string)$request->get('q', ''));
        $where = [];
        $params = [];
        if ($status !== 'all') {
            if ($status === 'pending_hr') {
                $where[] = "EXISTS (
                    SELECT 1 FROM verification_requests vr
                    WHERE vr.employment_id = er.id
                      AND vr.id = (SELECT MAX(vr2.id) FROM verification_requests vr2 WHERE vr2.employment_id = er.id)
                      AND vr.status IN ('pending','email_sent')
                )";
            } else {
                $where[] = 'status_overall = :s'; 
                $params['s'] = $status;
            }
        }
        if ($q !== '') { $where[] = '(c.full_name LIKE :q OR er.company_name LIKE :q)'; $params['q'] = "%{$q}%"; }
        $sql = 'SELECT er.*, c.full_name, req.hr_email AS req_hr_email, req.token AS req_token, req.created_at AS req_created_at, req.status AS req_status, dcnt.doc_count, rc.req_count
                FROM employment_records er 
                INNER JOIN candidates c ON c.id = er.candidate_id
                LEFT JOIN (
                    SELECT vr.employment_id, vr.hr_email, vr.token, vr.created_at, vr.status
                    FROM verification_requests vr
                    WHERE vr.id IN (
                        SELECT MAX(vr2.id) FROM verification_requests vr2 GROUP BY vr2.employment_id
                    )
                ) AS req ON req.employment_id = er.id
                LEFT JOIN (
                    SELECT employment_id, COUNT(*) AS doc_count
                    FROM employment_documents
                    GROUP BY employment_id
                ) AS dcnt ON dcnt.employment_id = er.id
                LEFT JOIN (
                    SELECT employment_id, COUNT(*) AS req_count
                    FROM verification_requests
                    GROUP BY employment_id
                ) AS rc ON rc.employment_id = er.id';
        if (!empty($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY er.created_at DESC LIMIT 300';
        $rows = [];
        $warning = null;
        try {
            $rows = $db->fetchAll($sql, $params);
            if (!empty($rows)) {
                foreach ($rows as &$r) {
                    $eid = (int)($r['id'] ?? 0);
                    $count = (int)($r['doc_count'] ?? 0);
                    try {
                        $cand = $db->fetchOne("SELECT verification_data FROM candidates WHERE id = :cid", ['cid' => (int)($r['candidate_id'] ?? 0)]);
                        if (!empty($cand['verification_data'])) {
                            $v = json_decode((string)$cand['verification_data'], true) ?: [];
                            $emps = is_array($v['employments'] ?? null) ? $v['employments'] : [];
                            foreach ($emps as $e) {
                                $match = ((int)($e['employment_id'] ?? 0) === $eid);
                                if (!$match) {
                                    $cmpA = strtolower(trim((string)($e['company'] ?? '')));
                                    $cmpB = strtolower(trim((string)($r['company_name'] ?? '')));
                                    $match = ($cmpA && $cmpB && $cmpA === $cmpB);
                                }
                                if ($match && !empty($e['documents']) && is_array($e['documents'])) {
                                    foreach ($e['documents'] as $u) {
                                        if ($u) $count++;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $ignore) {}
                    $r['doc_count'] = $count;
                    $r['req_count'] = (int)($r['req_count'] ?? 0);
                }
                unset($r);
            }
        } catch (\Throwable $e) {
            $warning = 'Employment verification tables are not available. Please run migrations.';
        }
        // Stats for header cards
        $stats = [
            'total' => 0, 'verified' => 0, 'under_review' => 0, 'not_verified' => 0, 'pending_hr' => 0
        ];
        try {
            $stats['total'] = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM employment_records")['c'] ?? 0);
            $stats['verified'] = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM employment_records WHERE status_overall = 'verified'")['c'] ?? 0);
            $stats['under_review'] = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM employment_records WHERE status_overall = 'under_review'")['c'] ?? 0);
            $stats['not_verified'] = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM employment_records WHERE status_overall = 'not_verified'")['c'] ?? 0);
            $stats['pending_hr'] = (int)($db->fetchOne("
                SELECT COUNT(*) AS c FROM employment_records er
                WHERE EXISTS (
                    SELECT 1 FROM verification_requests vr 
                    WHERE vr.employment_id = er.id 
                      AND vr.id = (SELECT MAX(vr2.id) FROM verification_requests vr2 WHERE vr2.employment_id = er.id)
                      AND vr.status IN ('pending','email_sent')
                )
            ")['c'] ?? 0);
        } catch (\Throwable $t) {}
        $response->view('admin/verification/index', [
            'title' => 'Employment Verification',
            'records' => $rows,
            'filters' => ['status' => $status, 'q' => $q],
            'warning' => $warning,
            'stats' => $stats,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        \App\Services\EmploymentVerificationService::ensureSchema();
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT er.*, c.full_name FROM employment_records er INNER JOIN candidates c ON c.id = er.candidate_id WHERE er.id = :id", ['id' => $id]);
        if (!$row) { $response->redirect('/admin/verification'); return; }
        $docs = $db->fetchAll("SELECT * FROM employment_documents WHERE employment_id = :id", ['id' => $id]);
        try {
            $cand = $db->fetchOne("SELECT verification_data FROM candidates WHERE id = :cid", ['cid' => (int)($row['candidate_id'] ?? 0)]);
            if (!empty($cand['verification_data'])) {
                $v = json_decode((string)$cand['verification_data'], true) ?: [];
                $emps = is_array($v['employments'] ?? null) ? $v['employments'] : [];
                foreach ($emps as $e) {
                    $match = ((int)($e['employment_id'] ?? 0) === (int)$id);
                    if (!$match) {
                        $cmpA = strtolower(trim((string)($e['company'] ?? '')));
                        $cmpB = strtolower(trim((string)($row['company_name'] ?? '')));
                        $match = ($cmpA && $cmpB && $cmpA === $cmpB);
                    }
                    if ($match && !empty($e['documents']) && is_array($e['documents'])) {
                        foreach ($e['documents'] as $t => $u) {
                            if (!$u) continue;
                            $docs[] = [
                                'doc_type' => (string)$t,
                                'file_path' => (string)$u
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $ignore) {}
        $logs = $db->fetchAll("SELECT * FROM verification_logs WHERE employment_id = :id ORDER BY created_at DESC", ['id' => $id]);
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE employment_id = :id ORDER BY created_at DESC LIMIT 1", ['id' => $id]);
        $resp = $db->fetchOne("SELECT * FROM verification_responses WHERE request_id IN (SELECT id FROM verification_requests WHERE employment_id = :eid) ORDER BY responded_at DESC LIMIT 1", ['eid' => $id]);
        $emailLog = null;
        if ($req && !empty($req['token'])) {
            try {
                $emailLog = $db->fetchOne("
                    SELECT subject, content, status, created_at 
                    FROM notification_logs 
                    WHERE channel = 'email' 
                      AND template_key = 'hr_verification_request'
                      AND metadata LIKE :m
                    ORDER BY created_at DESC
                    LIMIT 1
                ", ['m' => '%' . (string)$req['token'] . '%']);
            } catch (\Throwable $ignore) {}
        }
        $response->view('admin/verification/show', [
            'title' => 'Verification Detail',
            'record' => $row,
            'documents' => $docs,
            'logs' => $logs,
            'request' => $req,
            'response' => $resp,
            'email_log' => $emailLog,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function approve(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        EmploymentVerificationService::setOverallStatus($id, 'verified');
        $response->redirect('/admin/verification/' . $id . '?msg=verified');
    }

    public function reject(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        EmploymentVerificationService::setOverallStatus($id, 'not_verified');
        $response->redirect('/admin/verification/' . $id . '?msg=not_verified');
    }

    public function resendHr(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE employment_id = :id ORDER BY created_at DESC LIMIT 1", ['id' => $id]);
        if ($req) {
            EmploymentVerificationService::createHrRequest($id, [
                'hr_email' => $req['hr_email'],
                'hr_phone' => $req['hr_phone'],
                'manager_email' => $req['manager_email'],
                'company_website' => $req['company_website'],
                'cin' => $req['cin'],
                'gst' => $req['gst']
            ]);
        }
        $response->redirect('/admin/verification/' . $id . '?msg=resend_ok');
    }

    public function generateReport(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $pdf = (new EmploymentVerificationPDFService())->generate($id);
        if (!$pdf) { $response->json(['error' => 'failed'], 500); return; }
        $response->json(['success' => true, 'pdf_url' => $pdf]);
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        try {
            $db->query("START TRANSACTION");
            // Delete responses for requests
            $reqs = $db->fetchAll("SELECT id FROM verification_requests WHERE employment_id = :id", ['id' => $id]);
            if (!empty($reqs)) {
                $ids = implode(',', array_map(fn($r) => (int)$r['id'], $reqs));
                $db->query("DELETE FROM verification_responses WHERE request_id IN ({$ids})");
            }
            // Delete requests
            $db->query("DELETE FROM verification_requests WHERE employment_id = :id", ['id' => $id]);
            // Delete document texts
            $docs = $db->fetchAll("SELECT id FROM employment_documents WHERE employment_id = :id", ['id' => $id]);
            if (!empty($docs)) {
                $dids = implode(',', array_map(fn($r) => (int)$r['id'], $docs));
                $db->query("DELETE FROM employment_document_texts WHERE document_id IN ({$dids})");
            }
            // Delete documents
            $db->query("DELETE FROM employment_documents WHERE employment_id = :id", ['id' => $id]);
            // Delete scores/logs/unlocks
            $db->query("DELETE FROM verification_scores WHERE employment_id = :id", ['id' => $id]);
            $db->query("DELETE FROM verification_logs WHERE employment_id = :id", ['id' => $id]);
            $db->query("DELETE FROM employer_unlocks WHERE employment_id = :id", ['id' => $id]);
            // Finally delete the record
            $db->query("DELETE FROM employment_records WHERE id = :id", ['id' => $id]);
            $db->query("COMMIT");
        } catch (\Throwable $e) {
            $db->query("ROLLBACK");
            error_log('Delete verification error: ' . $e->getMessage());
        }
        $response->redirect('/admin/verification?msg=deleted');
    }
}
