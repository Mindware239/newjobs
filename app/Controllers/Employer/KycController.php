<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\EmployerKycDocument;
use App\Models\Job;
use App\Models\Application;
use App\Core\Storage;

class KycController extends BaseController
{
    public function show(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get KYC documents
        $documents = $employer->kycDocuments();
        $documentsArray = array_map(fn($doc) => $doc->toArray(), $documents);

        // Get counts for sidebar
        $activeJobsCount = \App\Models\Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = \App\Models\Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $response->view('employer/kyc', [
            'title' => 'KYC Verification',
            'employer' => $employer,
            'kyc_status' => $employer->kyc_status,
            'documents' => $documentsArray,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function uploadDocument(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $docType = $request->post('doc_type');
        $file = $request->file('file');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'Invalid file'], 400);
            return;
        }

        if (!in_array($docType, ['business_license', 'tax_id', 'address_proof', 'director_id', 'other'])) {
            $response->json(['error' => 'Invalid document type'], 422);
            return;
        }

        $storage = new Storage();
        $filePath = $storage->store($file, 'kyc/' . $employer->id);

        $document = new EmployerKycDocument();
        $document->fill([
            'employer_id' => $employer->id,
            'doc_type' => $docType,
            'file_url' => $storage->url($filePath),
            'file_name' => $file['name'],
            'uploaded_by' => $this->currentUser->id,
            'review_status' => 'pending'
        ]);

        if ($document->save()) {
            // Run OCR + store structured fields
            try {
                $ocrService = new \App\Services\OcrService();
                $ocrResult = $ocrService->extract([
                    'id' => $document->attributes['id'] ?? $document->id,
                    'employer_id' => $employer->id,
                    'path' => $filePath,
                    'doc_type' => $docType,
                ]);
            } catch (\Throwable $e) {
                error_log('KycController::uploadDocument OCR failed: ' . $e->getMessage());
                $ocrResult = [];
            }

            // Update employer KYC status
            if ($employer->kyc_status === 'not_submitted') {
                $employer->kyc_status = 'pending';
                $employer->save();
            }

            $payload = $document->toArray();
            if (!empty($ocrResult)) {
                $payload['ocr'] = $ocrResult;
            }

            $response->json($payload, 201);
        } else {
            $response->json(['error' => 'Failed to upload document'], 500);
        }
    }

    public function submit(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $documents = $employer->kycDocuments();
        if (empty($documents)) {
            $response->json(['error' => 'No documents uploaded'], 400);
            return;
        }

        // Evaluate employer trust score based on OCR and other rules
        try {
            $verificationService = new \App\Services\EmployerVerificationService();
            $context = [
                'user_email' => $this->currentUser->attributes['email'] ?? '',
                'documents' => [
                    // Additional structured fields can be passed here as needed
                ]
            ];
            $result = $verificationService->evaluate($employer, $context);
        } catch (\Throwable $e) {
            error_log('KycController::submit verification failed: ' . $e->getMessage());
            $result = ['score' => 0, 'risk_level' => 'high'];
        }

        $score = (int)($result['score'] ?? 0);
        $riskLevel = $result['risk_level'] ?? 'high';

        if ($score >= 80) {
            $employer->kyc_status = 'approved';
            $employer->save();
        } elseif ($score >= 50) {
            $employer->kyc_status = 'pending';
            $employer->save();
        } else {
            $employer->kyc_status = 'rejected';
            $employer->save();
        }

        $response->json([
            'message' => 'KYC submitted',
            'trust_score' => $score,
            'risk_level' => $riskLevel
        ]);
    }
}

