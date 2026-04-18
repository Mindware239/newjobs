<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Employer;
use App\Models\EmployerKycDocument;
use App\Services\MailService;

class ProfileController extends ApiController
{
    private MailService $mailService;

    public function __construct()
    {
        $this->mailService = new MailService();
    }

    /**
     * GET /api/v1/employer/profile
     * Get employer company profile
     */
    public function show(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $this->success($response, [
            'id' => $profile->id,
            'employer_id' => $profile->user_id,
            'company_name' => $profile->company_name,
            'company_description' => $profile->description,
            'logo' => $profile->logo_url,
            'website' => $profile->website,
            'industry' => $profile->industry,
            'company_size' => $profile->size,
            'verification_status' => $profile->kyc_status,
            'verified' => $profile->verified,
            'social_links' => []
        ]);
    }

    /**
     * PUT /api/v1/employer/profile
     * Update employer company profile
     */
    public function update(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'company_name' => 'required',
            'company_description' => 'required',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $profile = new Employer();
            $profile->user_id = $user->id;
        }

        $profile->company_name = $data['company_name'];
        $profile->description = $data['company_description'];
        $profile->website = $data['website'] ?? ($profile->website ?? null);
        $profile->industry = $data['industry'] ?? ($profile->industry ?? null);
        $profile->size = $data['company_size'] ?? ($profile->size ?? null);
        $profile->save();

        $this->success($response, ['id' => $profile->id], 'Profile updated successfully');
    }
    /**
     * POST /api/v1/employer/profile/logo
     * Upload company logo
     */
    public function uploadLogo(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->error($response, 'Logo file is required', 400);
            return;
        }

        $file = $_FILES['logo'];
        $allowed = ['image/jpeg', 'image/png', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed)) {
            $this->error($response, 'Invalid file type. Allowed: JPEG, PNG, SVG', 400);
            return;
        }

        $filename = uniqid('logo_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadDir = __DIR__ . '/../../../../storage/uploads/company-logos/';
        @mkdir($uploadDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->error($response, 'Failed to upload logo', 500);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if ($profile) {
            $profile->logo_url = '/storage/uploads/company-logos/' . $filename;
            $profile->save();
        }

        $this->success($response, ['logo_url' => '/storage/uploads/company-logos/' . $filename], 'Logo uploaded');
    }

    /**
     * POST /api/v1/employer/profile/banner
     * Upload company banner
     */
    public function uploadBanner(Request $request, Response $response): void
    {
        $this->error($response, 'Banner upload not supported', 400);
    }

    /**
     * POST /api/v1/employer/profile/documents/upload
     * Upload company documents (certificates, registrations, etc.)
     */
    public function uploadDocument(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $this->error($response, 'Document file is required', 400);
            return;
        }

        $file = $_FILES['document'];
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed)) {
            $this->error($response, 'Invalid file type. Allowed: PDF, JPEG, PNG', 400);
            return;
        }

        $filename = uniqid('doc_') . '_' . basename($file['name']);
        $uploadDir = __DIR__ . '/../../../../storage/uploads/company-documents/';
        @mkdir($uploadDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->error($response, 'Failed to upload document', 500);
            return;
        }

        $document = new EmployerKycDocument();
        $document->fill([
            'employer_id' => $profile->id,
            'document_type' => $data['type'] ?? 'other',
            'file_path' => '/storage/uploads/company-documents/' . $filename,
            'status' => 'pending'
        ]);
        $document->save();

        $this->success($response, ['document_id' => $document->id], 'Document uploaded');
    }

    /**
     * GET /api/v1/employer/profile/documents
     * List company documents
     */
    public function listDocuments(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->success($response, ['documents' => []]);
            return;
        }

        $documents = EmployerKycDocument::where('employer_id', '=', $profile->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $data = [];
        foreach ($documents as $doc) {
            $data[] = [
                'id' => $doc->id,
                'type' => $doc->document_type,
                'file_path' => $doc->file_path,
                'status' => $doc->status,
                'uploaded_at' => $doc->created_at
            ];
        }

        $this->success($response, ['documents' => $data]);
    }

    /**
     * DELETE /api/v1/employer/profile/documents/{id}
     * Delete a document
     */
    public function deleteDocument(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $document = EmployerKycDocument::find((int)$id);
        if (!$document || $document->employer_id !== $profile->id) {
            $this->error($response, 'Document not found', 404);
            return;
        }

        $document->delete();
        $this->success($response, null, 'Document deleted');
    }

    /**
     * GET /api/v1/employer/profile/verification-status
     * Get employer verification status
     */
    public function verificationStatus(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $this->success($response, [
            'status' => $profile->kyc_status ?? 'pending',
            'verified' => $profile->verified,
        ]);
    }

    /**
     * POST /api/v1/employer/profile/social-links
     * Update social media links
     */
    public function updateSocialLinks(Request $request, Response $response): void
    {
        $this->error($response, 'Social links update not supported yet', 400);
    }
}
