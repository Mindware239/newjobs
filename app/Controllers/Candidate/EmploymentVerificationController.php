<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\EmploymentVerificationService;
use App\Services\ResumeTextExtractor;
use App\Services\NotificationService;

class EmploymentVerificationController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT * FROM employment_records WHERE candidate_id = :cid ORDER BY created_at DESC", ['cid' => (int)$candidate->id]);
        $response->view('candidate/verification/index', [
            'title' => 'Employment Verification',
            'records' => $rows,
            'user' => $this->currentUser
        ], 200, 'candidate/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $data = [
            'company_name' => (string)$request->post('company_name'),
            'designation' => (string)$request->post('designation'),
            'employee_id' => (string)$request->post('employee_id'),
            'start_date' => (string)$request->post('start_date'),
            'end_date' => (string)$request->post('end_date'),
            'consent' => (bool)$request->post('consent')
        ];
        $id = EmploymentVerificationService::createRecord((int)$candidate->id, $data);
        // Treat JSON/AJAX or explicit Accept header as API call
        $accept = strtolower($request->header('Accept', ''));
        if ($request->isAjax() || strpos($accept, 'application/json') !== false) {
            $response->json(['success' => true, 'employment_id' => $id]);
        } else {
            $response->redirect('/candidate/profile/complete');
        }
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM employment_records WHERE id = :id AND candidate_id = :cid", ['id' => $id, 'cid' => (int)$candidate->id]);
        if (!$row) { $response->redirect('/candidate/verification'); return; }
        $docs = $db->fetchAll("SELECT * FROM employment_documents WHERE employment_id = :id ORDER BY uploaded_at DESC", ['id' => $id]);
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE employment_id = :id ORDER BY created_at DESC LIMIT 1", ['id' => $id]);
        $response->view('candidate/verification/show', [
            'title' => 'Verification Details',
            'record' => $row,
            'documents' => $docs,
            'request' => $req,
            'user' => $this->currentUser
        ], 200, 'candidate/layout');
    }

    public function uploadDocument(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $employmentId = (int)$request->param('id');
        $db = Database::getInstance();
        $own = $db->fetchOne("SELECT id FROM employment_records WHERE id = :id AND candidate_id = :cid", ['id' => $employmentId, 'cid' => (int)$candidate->id]);
        if (!$own) { $response->json(['error' => 'unauthorized'], 403); return; }
        $docType = (string)$request->post('doc_type');
        $allowedTypes = ['offer_letter','relieving_letter','experience_letter','salary_slip','bank_statement','form16'];
        if (!in_array($docType, $allowedTypes, true)) { $response->json(['error' => 'invalid_doc_type'], 422); return; }
        try {
            $file = $request->file('file');
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { $response->json(['error' => 'invalid_file'], 422); return; }
            $size = (int)($file['size'] ?? 0);
            $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            $mime = (string)($file['type'] ?? '');
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/jpg',
                'application/octet-stream'
            ];
            $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg'];
            if ($size <= 0 || $size > 5 * 1024 * 1024 || !in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                $response->json(['error' => 'invalid_file'], 422);
                return;
            }
            $result = EmploymentVerificationService::uploadDocument($employmentId, $docType, $file);
            if (!($result['success'] ?? false)) {
                $code = ($result['error'] ?? '') === 'invalid_file' ? 422 : 500;
                error_log("Employment upload error: " . ($result['error'] ?? 'failed'));
                $response->json(['error' => $result['error'] ?? 'failed'], $code);
                return;
            }
            $response->json(['success' => true, 'document' => $result], 200);
        } catch (\Throwable $t) {
            error_log("Employment upload exception: " . $t->getMessage());
            $response->json(['error' => 'server_error'], 500);
        }
    }

    public function submitHr(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $employmentId = (int)$request->param('id');
        $db = Database::getInstance();
        $own = $db->fetchOne("SELECT id FROM employment_records WHERE id = :id AND candidate_id = :cid", ['id' => $employmentId, 'cid' => (int)$candidate->id]);
        if (!$own) { $response->json(['error' => 'unauthorized'], 403); return; }
        // Block if already verified
        $rec = $db->fetchOne("SELECT status_overall FROM employment_records WHERE id = :id", ['id' => $employmentId]);
        if ((string)($rec['status_overall'] ?? '') === 'verified') {
            $response->json(['error' => 'already_verified'], 422);
            return;
        }
        $consent = (bool)$request->post('consent');
        if ($consent) { EmploymentVerificationService::setConsent($employmentId, true); }
        $payload = [
            'hr_email' => (string)$request->post('hr_email'),
            'hr_phone' => (string)$request->post('hr_phone'),
            'manager_email' => (string)$request->post('manager_email'),
            'company_website' => (string)$request->post('company_website'),
            'cin' => (string)$request->post('cin'),
            'gst' => (string)$request->post('gst')
        ];
        if (empty($payload['hr_email'])) {
            $json = $request->getJsonBody();
            if (!empty($json['hr_email'])) {
                $payload['hr_email'] = (string)$json['hr_email'];
                $payload['hr_phone'] = (string)($json['hr_phone'] ?? $payload['hr_phone']);
                $payload['manager_email'] = (string)($json['manager_email'] ?? $payload['manager_email']);
                $payload['company_website'] = (string)($json['company_website'] ?? $payload['company_website']);
                $payload['cin'] = (string)($json['cin'] ?? $payload['cin']);
                $payload['gst'] = (string)($json['gst'] ?? $payload['gst']);
            }
        }
        error_log('SubmitHR payload email=' . ($payload['hr_email'] ?? '') . ' employment_id=' . $employmentId);
        $res = EmploymentVerificationService::createHrRequest($employmentId, $payload);
        if (!($res['success'] ?? false)) {
            $error = (string)($res['error'] ?? 'failed');
            $code = $error === 'invalid_email_domain' ? 422 : 429;
            $response->json(['error' => $error], $code);
            return;
        }
        $response->json(['success' => true, 'token' => (string)$res['token']], 200);
    }

    public function status(Request $request, Response $response): void
    {
        if (!$this->requireRole('candidate', $request, $response)) { return; }
        $candidate = $this->currentUser->candidate();
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM employment_records WHERE id = :id AND candidate_id = :cid", ['id' => $id, 'cid' => (int)$candidate->id]);
        if (!$row) { $response->json(['error' => 'not_found'], 404); return; }
        $docs = $db->fetchAll("SELECT id, doc_type, file_path, metadata FROM employment_documents WHERE employment_id = :id ORDER BY uploaded_at DESC", ['id' => $id]);
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE employment_id = :id ORDER BY created_at DESC LIMIT 1", ['id' => $id]);
        $documents = array_map(function($d) {
            $meta = [];
            if (!empty($d['metadata'])) {
                try { $meta = json_decode((string)$d['metadata'], true) ?: []; } catch (\Throwable $ignore) {}
            }
            $url = (string)$d['file_path'];
            if (stripos($url, '/storage/uploads/') === 0) {
                $url = '/uploads/' . ltrim(substr($url, strlen('/storage/uploads/')), '/');
            }
            return [
                'doc_type' => (string)$d['doc_type'],
                'url' => $url,
                'file_name' => (string)($meta['original_name'] ?? basename((string)$d['file_path']))
            ];
        }, $docs);
        $resp = null;
        if ($req) {
            $resp = $db->fetchOne("SELECT * FROM verification_responses WHERE request_id = :rid ORDER BY responded_at DESC LIMIT 1", ['rid' => (int)$req['id']]);
        }
        $response->json([
            'record' => $row,
            'documents_count' => count($docs),
            'documents' => $documents,
            'request' => $req,
            'response' => $resp
        ]);
    }
}
