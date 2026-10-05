<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Repositories\CandidateVerificationRepository;
use App\Services\EmploymentVerificationService;

class VerificationController extends ApiController
{
    private CandidateVerificationRepository $verificationRepository;

    public function __construct()
    {
        $this->verificationRepository = new CandidateVerificationRepository();
    }

    public function create(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $data = [
            'company_name' => (string)$request->post('company_name'),
            'designation' => (string)$request->post('designation'),
            'employee_id' => (string)$request->post('employee_id'),
            'start_date' => (string)$request->post('start_date'),
            'end_date' => (string)$request->post('end_date'),
            'consent' => (bool)$request->post('consent')
        ];

        try {
            $id = EmploymentVerificationService::createRecord((int)$candidate->id, $data);
            $this->success($response, ['employment_id' => $id], 'Employment record created', 201);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function uploadDocument(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        $employmentId = (int)$id;
        
        if (!$this->verificationRepository->candidateOwnsEmploymentRecord($employmentId, (int)$candidate->id)) {
            $this->error($response, 'Unauthorized or record not found', 403);
            return;
        }

        $docType = (string)$request->post('doc_type');
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->error($response, 'Invalid file', 422);
            return;
        }

        try {
            $fileId = EmploymentVerificationService::uploadDocument($employmentId, $docType, $_FILES['file']);
            $doc = $this->verificationRepository->getEmploymentDocumentById((int)$fileId);
            $this->success($response, ['document' => $doc], 'Document uploaded successfully');
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function submitHr(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        $employmentId = (int)$id;

        if (!$this->verificationRepository->candidateOwnsEmploymentRecord($employmentId, (int)$candidate->id)) {
            $this->error($response, 'Unauthorized or record not found', 403);
            return;
        }

        $data = [
            'hr_email' => (string)$request->post('hr_email'),
            'hr_phone' => (string)$request->post('hr_phone'),
            'manager_email' => (string)$request->post('manager_email'),
            'company_website' => (string)$request->post('company_website'),
            'cin' => (string)$request->post('cin'),
            'gst' => (string)$request->post('gst')
        ];

        try {
            $reqId = EmploymentVerificationService::createHrRequest($employmentId, $data);
            if (isset($reqId['success']) && $reqId['success']) {
                $this->success($response, ['request_id' => $reqId['token'] ?? true], 'HR verification request submitted');
            } else {
                $this->error($response, $reqId['error'] ?? 'Failed to submit HR request', 400);
            }
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function status(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        $employmentId = (int)$id;

        $status = $this->verificationRepository->getEmploymentStatus($employmentId, (int)$candidate->id);
        
        if ($status === null) {
            $this->error($response, 'Record not found', 404);
            return;
        }

        $docs = $this->verificationRepository->getEmploymentDocuments($employmentId);

        $this->success($response, [
            'status' => $status,
            'documents' => $docs
        ], 'Status retrieved');
    }
}
