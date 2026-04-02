<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\EmployerProfile;
use App\Models\Document;
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

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $this->success($response, [
            'id' => $profile->id,
            'employer_id' => $profile->employer_id,
            'company_name' => $profile->company_name,
            'company_description' => $profile->company_description,
            'logo' => $profile->logo,
            'banner' => $profile->banner,
            'website' => $profile->website,
            'industry' => $profile->industry,
            'company_size' => $profile->company_size,
            'location' => $profile->location,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'established_year' => $profile->established_year,
            'verification_status' => $profile->verification_status,
            'verified_at' => $profile->verified_at,
            'social_links' => json_decode($profile->social_links ?? '{}', true)
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
            'website' => 'sometimes',
            'industry' => 'sometimes',
            'company_size' => 'sometimes',
            'location' => 'sometimes',
            'phone' => 'sometimes',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if (!$profile) {
            $profile = new EmployerProfile();
            $profile->employer_id = $user->id;
        }

        $profile->fill([
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'],
            'website' => $data['website'] ?? $profile->website,
            'industry' => $data['industry'] ?? $profile->industry,
            'company_size' => $data['company_size'] ?? $profile->company_size,
            'location' => $data['location'] ?? $profile->location,
            'phone' => $data['phone'] ?? $profile->phone,
        ]);

        $profile->save();

        $this->success($response, ['profile_id' => $profile->id], 'Profile updated successfully');
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

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if ($profile) {
            $profile->logo = '/storage/uploads/company-logos/' . $filename;
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
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
            $this->error($response, 'Banner file is required', 400);
            return;
        }

        $file = $_FILES['banner'];
        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed)) {
            $this->error($response, 'Invalid file type. Allowed: JPEG, PNG', 400);
            return;
        }

        $filename = uniqid('banner_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadDir = __DIR__ . '/../../../../storage/uploads/company-banners/';
        @mkdir($uploadDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->error($response, 'Failed to upload banner', 500);
            return;
        }

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if ($profile) {
            $profile->banner = '/storage/uploads/company-banners/' . $filename;
            $profile->save();
        }

        $this->success($response, ['banner_url' => '/storage/uploads/company-banners/' . $filename], 'Banner uploaded');
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

        $document = new Document();
        $document->fill([
            'employer_id' => $user->id,
            'type' => $data['type'] ?? 'other',
            'file_path' => '/storage/uploads/company-documents/' . $filename,
            'original_name' => $file['name']
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

        $documents = Document::where('employer_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $data = [];
        foreach ($documents as $doc) {
            $data[] = [
                'id' => $doc->id,
                'type' => $doc->type,
                'file_path' => $doc->file_path,
                'original_name' => $doc->original_name,
                'uploaded_at' => $doc->created_at
            ];
        }

        $this->success($response, ['documents' => $data]);
    }

    /**
     * DELETE /api/v1/employer/profile/documents/{id}
     * Delete a document
     */
    public function deleteDocument(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $document = Document::find((int)$params['id']);
        if (!$document || $document->employer_id !== $user->id) {
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

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $this->success($response, [
            'status' => $profile->verification_status ?? 'pending',
            'verified_at' => $profile->verified_at,
            'reason' => $profile->rejection_reason ?? null
        ]);
    }

    /**
     * POST /api/v1/employer/profile/social-links
     * Update social media links
     */
    public function updateSocialLinks(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $links = [
            'linkedin' => $data['linkedin'] ?? null,
            'twitter' => $data['twitter'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'instagram' => $data['instagram'] ?? null,
        ];

        $profile = EmployerProfile::where('employer_id', '=', $user->id)->first();
        if (!$profile) {
            $profile = new EmployerProfile();
            $profile->employer_id = $user->id;
        }

        $profile->social_links = json_encode($links);
        $profile->save();

        $this->success($response, null, 'Social links updated');
    }
}
