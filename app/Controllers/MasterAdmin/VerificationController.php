<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Employer;
use App\Models\EmployerKycDocument;
use App\Models\Candidate;
use App\Core\Storage;
use App\Models\User;

class VerificationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();

        $stats = [
            'pending' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status = 'pending'")['c'] ?? 0),
            'approved' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status = 'approved'")['c'] ?? 0),
            'rejected' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status = 'rejected'")['c'] ?? 0),
        ];

        $search = trim((string)$request->get('search', ''));
        $status = (string)$request->get('status', 'pending');

        $where = ["e.kyc_status = :status"];
        $params = ['status' => $status];
        if ($search !== '') {
            $where[] = "(e.company_name LIKE :q OR u.email LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        $sql = "SELECT e.*, u.email AS employer_email, e.kyc_assigned_to, e.kyc_level
                FROM employers e
                INNER JOIN users u ON u.id = e.user_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY e.created_at DESC
                LIMIT 200";
        $employers = $db->fetchAll($sql, $params);

        $executives = $db->fetchAll(
            "SELECT id, email, role FROM users WHERE role IN ('admin','verification_executive') ORDER BY role, email"
        );

        $response->view('masteradmin/verifications/index', [
            'title' => 'Verification Management',
            'stats' => $stats,
            'employers' => $employers,
            'executives' => $executives,
            'filters' => [
                'search' => $search,
                'status' => $status
            ]
        ], 200, 'masteradmin/layout');
    }

    public function queue(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $employers = $db->fetchAll(
            "SELECT e.*, u.email AS employer_email
             FROM employers e
             INNER JOIN users u ON u.id = e.user_id
             WHERE e.kyc_assigned_to = :uid AND e.kyc_status = 'pending'
             ORDER BY e.updated_at DESC",
            ['uid' => $userId]
        );

        $response->view('masteradmin/verifications/queue', [
            'title' => 'My Verification Queue',
            'employers' => $employers
        ], 200, 'masteradmin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->param('id');

        $employer = $db->fetchOne(
            "SELECT e.*, u.email AS employer_email
             FROM employers e
             INNER JOIN users u ON u.id = e.user_id
             WHERE e.id = :id",
            ['id' => $id]
        );

        if (!$employer) {
            $response->redirect('/master/verifications');
            return;
        }

        $documents = $db->fetchAll(
            "SELECT * FROM employer_kyc_documents WHERE employer_id = :eid ORDER BY uploaded_at DESC",
            ['eid' => $id]
        );

        $response->view('masteradmin/verifications/show', [
            'title' => 'Verify: ' . ($employer['company_name'] ?? 'Employer'),
            'employer' => $employer,
            'documents' => $documents
        ], 200, 'masteradmin/layout');
    }

    public function assign(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $employerId = (int)$request->post('employer_id');
        $executiveId = (int)$request->post('executive_id');

        if (!$employerId || !$executiveId) {
            $response->json(['error' => 'Missing parameters'], 400);
            return;
        }

        $db->query(
            "UPDATE employers SET kyc_assigned_to = :exec, updated_at = NOW() WHERE id = :id",
            ['exec' => $executiveId, 'id' => $employerId]
        );

        $response->redirect('/master/verifications');
    }

    public function setLevel(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $employerId = (int)$request->post('employer_id');
        $level = (string)$request->post('level', 'basic');
        if (!in_array($level, ['basic','full'])) {
            $level = 'basic';
        }
        $db->query(
            "UPDATE employers SET kyc_level = :level, updated_at = NOW() WHERE id = :id",
            ['level' => $level, 'id' => $employerId]
        );
        $response->redirect('/master/verifications/' . $employerId);
    }

    public function approve(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('employer_id');
        if (!$id) { $response->json(['error' => 'Invalid employer'], 400); return; }

        $db->query("UPDATE employers SET kyc_status = 'approved', kyc_rejection_reason = NULL, updated_at = NOW() WHERE id = :id", ['id' => $id]);

        $docs = $db->fetchAll("SELECT id FROM employer_kyc_documents WHERE employer_id = :id AND review_status = 'pending'", ['id' => $id]);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        foreach ($docs as $doc) {
            $db->query(
                "UPDATE employer_kyc_documents SET review_status = 'approved', reviewed_by = :uid, reviewed_at = NOW() WHERE id = :doc_id",
                ['uid' => $userId, 'doc_id' => $doc['id']]
            );
        }

        $response->redirect('/master/verifications/' . $id);
    }

    public function reject(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('employer_id');
        $reason = trim((string)$request->post('reason', ''));
        if (!$id || $reason === '') { $response->json(['error' => 'Reason required'], 422); return; }

        $db->query("UPDATE employers SET kyc_status = 'rejected', kyc_rejection_reason = :reason, updated_at = NOW() WHERE id = :id", ['id' => $id, 'reason' => $reason]);
        $response->redirect('/master/verifications/' . $id);
    }

    public function escalate(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('employer_id');
        $reason = trim((string)$request->post('reason', ''));
        if (!$id || $reason === '') { $response->json(['error' => 'Reason required'], 422); return; }

        $db->query("UPDATE employers SET kyc_escalated = 1, kyc_escalation_reason = :reason, updated_at = NOW() WHERE id = :id", ['id' => $id, 'reason' => $reason]);
        $response->redirect('/master/verifications/' . $id);
    }

    public function approveDocument(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $docId = (int)$request->post('document_id');
        $notes = trim((string)$request->post('notes', ''));
        if (!$docId) { $response->json(['error' => 'Invalid document'], 400); return; }
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $doc = $db->fetchOne("SELECT employer_id FROM employer_kyc_documents WHERE id = :id", ['id' => $docId]);
        if (!$doc) { $response->json(['error' => 'Document not found'], 404); return; }
        $db->query(
            "UPDATE employer_kyc_documents SET review_status = 'approved', review_notes = :notes, reviewed_by = :uid, reviewed_at = NOW() WHERE id = :id",
            ['notes' => $notes, 'uid' => $userId, 'id' => $docId]
        );
        $response->redirect('/master/verifications/' . (int)$doc['employer_id']);
    }

    public function rejectDocument(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $docId = (int)$request->post('document_id');
        $notes = trim((string)$request->post('notes', ''));
        if (!$docId || $notes === '') { $response->json(['error' => 'Reason required'], 422); return; }
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $doc = $db->fetchOne("SELECT employer_id FROM employer_kyc_documents WHERE id = :id", ['id' => $docId]);
        if (!$doc) { $response->json(['error' => 'Document not found'], 404); return; }
        $db->query(
            "UPDATE employer_kyc_documents SET review_status = 'rejected', review_notes = :notes, reviewed_by = :uid, reviewed_at = NOW() WHERE id = :id",
            ['notes' => $notes, 'uid' => $userId, 'id' => $docId]
        );
        $response->redirect('/master/verifications/' . (int)$doc['employer_id']);
    }

    public function uploadEvidence(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $docId = (int)$request->post('document_id');
        if (!$docId) { $response->json(['error' => 'Invalid document'], 400); return; }
        $doc = $db->fetchOne("SELECT employer_id FROM employer_kyc_documents WHERE id = :id", ['id' => $docId]);
        if (!$doc) { $response->json(['error' => 'Document not found'], 404); return; }
        if (!isset($_FILES['evidence']) || !is_array($_FILES['evidence'])) {
            $response->json(['error' => 'No evidence file'], 400); return;
        }
        $storage = new Storage();
        try {
            $storedPath = $storage->store($_FILES['evidence'], 'verification_evidence/' . $docId);
            $url = $storage->url($storedPath);
            $existing = $db->fetchOne("SELECT review_notes FROM employer_kyc_documents WHERE id = :id", ['id' => $docId]);
            $notes = trim((string)($existing['review_notes'] ?? ''));
            $append = ($notes !== '' ? ($notes . "\n") : '') . 'Evidence: ' . $url;
            $db->query("UPDATE employer_kyc_documents SET review_notes = :notes WHERE id = :id", ['notes' => $append, 'id' => $docId]);
        } catch (\RuntimeException $e) {
            $response->json(['error' => $e->getMessage()], 500); return;
        }
        $response->redirect('/master/verifications/' . (int)$doc['employer_id']);
    }

    public function reverifyDocument(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $docId = (int)$request->post('document_id');
        if (!$docId) { $response->json(['error' => 'Invalid document'], 400); return; }
        $doc = $db->fetchOne("SELECT employer_id FROM employer_kyc_documents WHERE id = :id", ['id' => $docId]);
        if (!$doc) { $response->json(['error' => 'Document not found'], 404); return; }
        $db->query("UPDATE employer_kyc_documents SET review_status = 'pending', reviewed_by = NULL, reviewed_at = NULL WHERE id = :id", ['id' => $docId]);
        $db->query("UPDATE employers SET kyc_status = 'pending' WHERE id = :eid", ['eid' => (int)$doc['employer_id']]);
        $response->redirect('/master/verifications/' . (int)$doc['employer_id']);
    }

    public function candidates(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $status = (string)$request->get('status', 'pending');
        $rows = $db->fetchAll(
            "SELECT v.*, u.email, c.full_name
             FROM verifications v
             INNER JOIN users u ON u.id = v.user_id
             LEFT JOIN candidates c ON c.user_id = v.user_id
             WHERE v.user_type = 'candidate' AND v.status = :status
             ORDER BY v.created_at DESC
             LIMIT 200",
            ['status' => $status]
        );
        $executives = $db->fetchAll(
            "SELECT id, email, role FROM users WHERE role IN ('admin','verification_executive') ORDER BY role, email"
        );
        $response->view('masteradmin/verifications/candidates', [
            'title' => 'Candidate Verifications',
            'items' => $rows,
            'executives' => $executives,
            'filters' => ['status' => $status]
        ], 200, 'masteradmin/layout');
    }

    public function showCandidate(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)$request->param('id');
        $cand = $db->fetchOne(
            "SELECT c.*, u.email FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE c.user_id = :uid",
            ['uid' => $userId]
        );
        if (!$cand) { $response->redirect('/master/verifications/candidates'); return; }
        $verifs = $db->fetchAll("SELECT * FROM verifications WHERE user_type = 'candidate' AND user_id = :uid ORDER BY created_at DESC", ['uid' => $userId]);
        $response->view('masteradmin/verifications/show-candidate', [
            'title' => 'Verify Candidate',
            'candidate' => $cand,
            'verifications' => $verifs
        ], 200, 'masteradmin/layout');
    }

    public function candidateAssign(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $verificationId = (int)$request->post('verification_id');
        $executiveId = (int)$request->post('executive_id');
        if (!$verificationId || !$executiveId) { $response->json(['error' => 'Missing parameters'], 400); return; }
        $db->query("UPDATE verifications SET assigned_to = :exec, status = 'assigned' WHERE id = :id", ['exec' => $executiveId, 'id' => $verificationId]);
        $row = $db->fetchOne("SELECT user_id FROM verifications WHERE id = :id", ['id' => $verificationId]);
        $response->redirect('/master/verifications/candidates/' . (int)($row['user_id'] ?? 0));
    }

    public function candidateApprove(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $verificationId = (int)$request->post('verification_id');
        if (!$verificationId) { $response->json(['error' => 'Invalid verification'], 400); return; }
        $db->query("UPDATE verifications SET status = 'approved' WHERE id = :id", ['id' => $verificationId]);
        $row = $db->fetchOne("SELECT user_id FROM verifications WHERE id = :id", ['id' => $verificationId]);
        $uid = (int)($row['user_id'] ?? 0);
        if ($uid) {
            $remaining = $db->fetchOne("SELECT COUNT(*) as c FROM verifications WHERE user_type = 'candidate' AND user_id = :uid AND status <> 'approved'", ['uid' => $uid]);
            if ((int)($remaining['c'] ?? 0) === 0) {
                $db->query("UPDATE candidates SET is_verified = 1 WHERE user_id = :uid", ['uid' => $uid]);
            }
        }
        $response->redirect('/master/verifications/candidates/' . $uid);
    }

    public function candidateReject(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $verificationId = (int)$request->post('verification_id');
        $notes = trim((string)$request->post('notes', ''));
        if (!$verificationId || $notes === '') { $response->json(['error' => 'Reason required'], 422); return; }
        $db->query("UPDATE verifications SET status = 'rejected', remarks = :notes WHERE id = :id", ['notes' => $notes, 'id' => $verificationId]);
        $row = $db->fetchOne("SELECT user_id FROM verifications WHERE id = :id", ['id' => $verificationId]);
        $response->redirect('/master/verifications/candidates/' . (int)($row['user_id'] ?? 0));
    }

    public function candidateEvidence(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $verificationId = (int)$request->post('verification_id');
        if (!$verificationId) { $response->json(['error' => 'Invalid verification'], 400); return; }
        if (!isset($_FILES['evidence']) || !is_array($_FILES['evidence'])) { $response->json(['error' => 'No evidence file'], 400); return; }
        $storage = new Storage();
        try {
            $storedPath = $storage->store($_FILES['evidence'], 'verification_evidence/candidate/' . $verificationId);
            $url = $storage->url($storedPath);
            $existing = $db->fetchOne("SELECT remarks FROM verifications WHERE id = :id", ['id' => $verificationId]);
            $notes = trim((string)($existing['remarks'] ?? ''));
            $append = ($notes !== '' ? ($notes . "\n") : '') . 'Evidence: ' . $url;
            $db->query("UPDATE verifications SET remarks = :notes WHERE id = :id", ['notes' => $append, 'id' => $verificationId]);
        } catch (\RuntimeException $e) {
            $response->json(['error' => $e->getMessage()], 500); return;
        }
        $row = $db->fetchOne("SELECT user_id FROM verifications WHERE id = :id", ['id' => $verificationId]);
        $response->redirect('/master/verifications/candidates/' . (int)($row['user_id'] ?? 0));
    }

    public function candidateReverify(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $verificationId = (int)$request->post('verification_id');
        if (!$verificationId) { $response->json(['error' => 'Invalid verification'], 400); return; }
        $db->query("UPDATE verifications SET status = 'pending', assigned_to = NULL WHERE id = :id", ['id' => $verificationId]);
        $row = $db->fetchOne("SELECT user_id FROM verifications WHERE id = :id", ['id' => $verificationId]);
        $db->query("UPDATE candidates SET is_verified = 0 WHERE user_id = :uid", ['uid' => (int)($row['user_id'] ?? 0)]);
        $response->redirect('/master/verifications/candidates/' . (int)($row['user_id'] ?? 0));
    }
}

