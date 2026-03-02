<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\EmploymentVerificationService;

class EmploymentVerificationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $candidateId = (int)$request->get('candidate_id', 0);
        $db = Database::getInstance();
        $rows = [];
        if ($candidateId > 0) {
            $rows = $db->fetchAll("SELECT er.*, 
                CASE WHEN er.status_overall = 'verified' THEN 1 ELSE 0 END AS badge
                FROM employment_records er 
                WHERE er.candidate_id = :cid
                ORDER BY er.created_at DESC", ['cid' => $candidateId]);
        }
        $price = \App\Models\SystemSetting::get('employment_verification_price', '999');
        $response->view('employer/verification/index', [
            'title' => 'Candidate Verification',
            'records' => $rows,
            'price' => $price,
            'user' => $this->currentUser,
            'unlocked' => (int)$request->get('unlocked', 0)
        ], 200, 'employer/layout');
    }

    public function unlock(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $employmentId = (int)$request->param('id');
        $unlockId = EmploymentVerificationService::createUnlock($employmentId, (int)$employer->id);
        $response->redirect('/employer/verification/checkout/' . $unlockId);
    }

    public function checkout(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->param('unlockId');
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT * FROM employer_unlocks WHERE id = :id", ['id' => $unlockId]);
        if (!$unlock) { $response->redirect('/employer/verification'); return; }
        $response->view('employer/verification/checkout', [
            'title' => 'Unlock Verification Report',
            'unlock' => $unlock,
            'user' => $this->currentUser
        ], 200, 'employer/layout');
    }

    public function markPaid(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->param('unlockId');
        $invoiceNumber = 'VRF-' . date('Ym') . '-' . strtoupper(substr(uniqid('', true), -6));
        $invoiceUrl = '/employer/verification/invoice/' . $unlockId;
        EmploymentVerificationService::markUnlockPaid($unlockId, $invoiceNumber, $invoiceUrl);
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT employment_id FROM employer_unlocks WHERE id = :id", ['id' => $unlockId]);
        $emp = null;
        if ($unlock) {
            $emp = $db->fetchOne("SELECT candidate_id FROM employment_records WHERE id = :id", ['id' => (int)$unlock['employment_id']]);
        }
        if ($emp && (int)($emp['candidate_id'] ?? 0) > 0) {
            $response->redirect('/employer/verification?candidate_id=' . (int)$emp['candidate_id'] . '&unlocked=' . $unlockId);
            return;
        }
        $response->redirect('/employer/verification/success?unlock=' . $unlockId);
    }

    public function success(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->get('unlock', 0);
        if ($unlockId <= 0) { $response->redirect('/employer/verification'); return; }
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT * FROM employer_unlocks WHERE id = :id AND employer_id = :eid", [
            'id' => $unlockId,
            'eid' => (int)$this->currentUser->employer()->id
        ]);
        if (!$unlock || ($unlock['status'] ?? '') !== 'paid') { $response->redirect('/employer/verification'); return; }
        $employment = $db->fetchOne("SELECT er.*, c.first_name, c.last_name FROM employment_records er INNER JOIN candidates c ON c.id = er.candidate_id WHERE er.id = :id", [
            'id' => (int)$unlock['employment_id']
        ]);
        $response->view('employer/verification/success', [
            'title' => 'Payment Successful',
            'unlock' => $unlock,
            'employment' => $employment,
            'user' => $this->currentUser
        ], 200, 'employer/layout');
    }

    public function report(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->param('unlockId');
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT * FROM employer_unlocks WHERE id = :id AND employer_id = :eid", [
            'id' => $unlockId,
            'eid' => (int)$this->currentUser->employer()->id
        ]);
        if (!$unlock || ($unlock['status'] ?? '') !== 'paid') { $response->redirect('/employer/verification'); return; }
        $pdfUrl = (new \App\Services\EmploymentVerificationPDFService())->generate((int)$unlock['employment_id']);
        if (!$pdfUrl) { $response->redirect('/employer/verification'); return; }
        $response->redirect($pdfUrl);
    }

    public function invoice(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->param('unlockId');
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT * FROM employer_unlocks WHERE id = :id AND employer_id = :eid", [
            'id' => $unlockId,
            'eid' => (int)$this->currentUser->employer()->id
        ]);
        if (!$unlock) { $response->redirect('/employer/verification'); return; }
        $response->view('employer/verification/invoice', [
            'title' => 'Invoice',
            'unlock' => $unlock,
            'user' => $this->currentUser
        ], 200, 'employer/layout');
    }

    public function details(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $unlockId = (int)$request->param('unlockId');
        $db = Database::getInstance();
        $unlock = $db->fetchOne("SELECT * FROM employer_unlocks WHERE id = :id AND employer_id = :eid", [
            'id' => $unlockId,
            'eid' => (int)$this->currentUser->employer()->id
        ]);
        if (!$unlock || ($unlock['status'] ?? '') !== 'paid') { $response->redirect('/employer/verification'); return; }
        $employment = $db->fetchOne("SELECT er.*, c.first_name, c.last_name FROM employment_records er INNER JOIN candidates c ON c.id = er.candidate_id WHERE er.id = :id", [
            'id' => (int)$unlock['employment_id']
        ]);
        if (!$employment || (string)($employment['status_overall'] ?? '') !== 'verified') {
            $response->view('employer/verification/success', [
                'title' => 'Payment Successful',
                'unlock' => $unlock,
                'employment' => $employment,
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }
        $documents = $db->fetchAll("SELECT doc_type, file_path FROM employment_documents WHERE employment_id = :id ORDER BY doc_type", [
            'id' => (int)$unlock['employment_id']
        ]);
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE employment_id = :id ORDER BY created_at DESC LIMIT 1", [
            'id' => (int)$unlock['employment_id']
        ]);
        $resp = $db->fetchOne("SELECT * FROM verification_responses WHERE request_id IN (SELECT id FROM verification_requests WHERE employment_id = :eid) ORDER BY responded_at DESC LIMIT 1", [
            'eid' => (int)$unlock['employment_id']
        ]);
        $maskedHrEmail = '';
        if (!empty($req['hr_email'])) {
            $parts = explode('@', (string)$req['hr_email']);
            if (count($parts) === 2) {
                $maskedHrEmail = substr($parts[0], 0, 2) . '••••@' . $parts[1];
            }
        }
        $response->view('employer/verification/details', [
            'title' => 'Verification Details',
            'unlock' => $unlock,
            'employment' => $employment,
            'documents' => $documents,
            'hr_email_masked' => $maskedHrEmail,
            'hr_response' => $resp,
            'user' => $this->currentUser
        ], 200, 'employer/layout');
    }
}
