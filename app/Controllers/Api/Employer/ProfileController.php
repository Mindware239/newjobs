<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Employer;
use App\Models\EmployerKycDocument;
use App\Models\EmployerSubscription;
use App\Models\Job;
use App\Models\Application;
use App\Models\SubscriptionPayment;
use App\Core\Storage;
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

        $payload = $this->buildProfilePayload($request, $user, $profile);

        $this->success($response, array_merge([
            'id' => $profile->id,
            'employer_id' => $profile->id,
            'user_id' => $profile->user_id,
            'register_as' => $payload['profile']['company']['register_as'],
            'company_name' => $payload['profile']['company']['name'],
            'company_description' => $payload['profile']['company']['description'],
            'company_type' => $payload['profile']['company']['company_type'],
            'company_size' => $payload['profile']['company']['company_size'],
            'profession_type' => $payload['profile']['company']['profession_type'],
            'service_category' => $payload['profile']['company']['service_category'],
            'logo' => $payload['profile']['company']['logo_url'],
            'logo_url' => $payload['profile']['company']['logo_url'],
            'banner_url' => $payload['profile']['company']['banner_url'],
            'website' => $payload['profile']['company']['website'],
            'industry' => $payload['profile']['company']['industry'],
            'tax_id' => $payload['profile']['tax']['tax_id'],
            'gstin' => $payload['profile']['tax']['gstin'],
            'country' => $payload['profile']['address']['country'],
            'state' => $payload['profile']['address']['state'],
            'city' => $payload['profile']['address']['city'],
            'postal_code' => $payload['profile']['address']['postal_code'],
            'address' => $payload['profile']['address'],
            'verification_status' => $payload['profile']['kyc']['status'],
            'verified' => $payload['profile']['kyc']['verified'],
            'kyc' => $payload['profile']['kyc'],
            'documents' => $payload['profile']['kyc']['documents'],
            'completion' => $payload['profile']['completion'],
            'stats' => $payload['profile']['stats'],
            'subscription' => $payload['profile']['subscription'],
            'last_payment' => $payload['profile']['last_payment'],
            'social_links' => $payload['profile']['social_links']
        ], $payload));
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

        $taxId = strtoupper(trim((string)($data['tax_id'] ?? '')));
        if ($taxId !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/', $taxId)) {
            $this->error($response, 'Please enter valid GSTIN number.', 422);
            return;
        }

        $postalCode = trim((string)($data['postal_code'] ?? ($data['address']['postal_code'] ?? '')));
        if ($postalCode !== '' && !preg_match('/^[0-9]{6}$/', $postalCode)) {
            $this->error($response, 'Pin Code must be exactly 6 digits', 422);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $profile = new Employer();
            $profile->user_id = $user->id;
            $profile->company_slug = $profile->generateSlug((string)$data['company_name']);
            $profile->kyc_status = 'pending';
        }

        $address = $data['address'] ?? [];
        if (is_string($address)) {
            $address = json_decode($address, true) ?: [];
        }
        if (!is_array($address)) {
            $address = [];
        }

        foreach (['company_type', 'tax_id'] as $key) {
            if (array_key_exists($key, $data)) {
                $address[$key] = is_string($data[$key]) ? trim($data[$key]) : $data[$key];
            }
        }

        if ($postalCode !== '') {
            $address['postal_code'] = $postalCode;
        }

        $profile->company_name = $data['company_name'];
        $profile->description = $data['company_description'];
        $profile->website = $data['website'] ?? ($profile->website ?? null);
        $profile->industry = $data['industry'] ?? ($profile->industry ?? null);
        $profile->company_type = $data['company_type'] ?? ($profile->company_type ?? null);
        $profile->register_as = $data['register_as'] ?? ($profile->register_as ?? 'company');
        $profile->profession_type = $data['profession_type'] ?? ($profile->profession_type ?? null);
        $profile->service_category = $data['service_category'] ?? ($profile->service_category ?? null);
        $profile->size = $data['company_size'] ?? ($profile->size ?? null);
        $profile->tax_id = $taxId !== '' ? $taxId : ($profile->tax_id ?? null);
        $profile->country = $data['country'] ?? ($address['country'] ?? ($profile->country ?? null));
        $profile->state = $data['state'] ?? ($address['state'] ?? ($profile->state ?? null));
        $profile->city = $data['city'] ?? ($address['city'] ?? ($profile->city ?? null));
        $profile->postal_code = $postalCode ?: ($address['postal_code'] ?? ($profile->postal_code ?? null));
        foreach (['country', 'state', 'city', 'postal_code'] as $key) {
            if (!isset($address[$key]) && isset($profile->$key)) {
                $address[$key] = $profile->$key;
            }
        }
        if (!empty($address)) {
            $profile->address = json_encode($address, JSON_UNESCAPED_UNICODE);
        }
        $profile->save();

        $this->success($response, ['profile' => $profile->toArray()], 'Profile updated successfully');
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
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
            $this->error($response, 'Banner file is required', 400);
            return;
        }

        $profile = Employer::findByUserId((int)$user->id);
        if (!$profile) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $file = $_FILES['banner'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed, true)) {
            $this->error($response, 'Invalid file type. Allowed: JPEG, PNG, WEBP', 400);
            return;
        }

        $storage = new Storage();
        $filePath = $storage->store($file, 'uploads/employers/' . (int)$profile->id);
        $bannerUrl = $storage->url($filePath);

        $address = $this->decodeAddress($profile->address);
        $address['banner_url'] = $bannerUrl;
        $profile->address = json_encode($address, JSON_UNESCAPED_UNICODE);
        $profile->save();

        $this->success($response, ['banner_url' => $this->assetUrl($request, $bannerUrl)], 'Banner uploaded');
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

        $storage = new Storage();
        $filePath = $storage->store($file, 'uploads/kyc/' . (int)$profile->id);
        $docType = (string)($request->post('type') ?? $request->post('doc_type') ?? 'other');

        $document = new EmployerKycDocument();
        $document->fill([
            'employer_id' => $profile->id,
            'doc_type' => $docType,
            'file_url' => $storage->url($filePath),
            'file_name' => $file['name'],
            'uploaded_by' => $user->id,
            'review_status' => 'pending'
        ]);
        $document->save();

        $this->success($response, [
            'document_id' => $document->id,
            'document' => $this->formatKycDocument($request, $document)
        ], 'Document uploaded');
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
            ->orderBy('uploaded_at', 'DESC')
            ->get();

        $data = [];
        foreach ($documents as $doc) {
            $data[] = $this->formatKycDocument($request, $doc);
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

    private function buildProfilePayload(Request $request, User $user, Employer $profile): array
    {
        $address = $this->decodeAddress($profile->address);
        $companyRow = $this->companyRow((int)$profile->id);
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$profile->id);
        $plan = $subscription ? $subscription->plan() : null;
        $lastPayment = SubscriptionPayment::where('employer_id', '=', (int)$profile->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        $jobCount = Job::where('employer_id', '=', (int)$profile->id)->count();
        $activeJobCount = Job::where('employer_id', '=', (int)$profile->id)
            ->where('status', '=', 'published')
            ->count();
        $jobIds = Job::where('employer_id', '=', (int)$profile->id)->pluck('id');
        $applicationCount = !empty($jobIds) ? Application::whereIn('job_id', $jobIds)->count() : 0;

        $documents = EmployerKycDocument::where('employer_id', '=', (int)$profile->id)
            ->orderBy('id', 'DESC')
            ->get();
        $documentPayload = array_map(fn($doc) => $this->formatKycDocument($request, $doc), $documents);

        $companyType = $profile->company_type ?? ($address['company_type'] ?? null);
        $taxId = $profile->tax_id ?? ($address['tax_id'] ?? $address['gstin'] ?? null);
        $logoUrl = $profile->logo_url ?: ($companyRow['logo_url'] ?? null);
        $bannerUrl = $address['banner_url'] ?? ($companyRow['banner_url'] ?? null);

        $basicInfoComplete = method_exists($profile, 'isBasicInfoComplete') ? $profile->isBasicInfoComplete() : false;
        $addressComplete = method_exists($profile, 'isAddressComplete') ? $profile->isAddressComplete() : false;

        return [
            'profile' => [
                'ids' => [
                    'user_id' => (int)$user->id,
                    'employer_id' => (int)$profile->id,
                    'company_id' => isset($companyRow['id']) ? (int)$companyRow['id'] : null,
                ],
                'account' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified' => (bool)($user->is_email_verified ?? false),
                    'phone_verified' => (bool)($user->is_phone_verified ?? false),
                    'last_login' => $user->last_login,
                ],
                'company' => [
                    'name' => $profile->company_name,
                    'slug' => $profile->company_slug,
                    'register_as' => $profile->register_as ?? 'company',
                    'company_type' => $companyType,
                    'industry' => $profile->industry,
                    'company_size' => $profile->size ?? ($companyRow['company_size'] ?? null),
                    'website' => $profile->website ?? ($companyRow['website'] ?? null),
                    'description' => $profile->description ?? ($companyRow['description'] ?? null),
                    'logo_url' => $this->assetUrl($request, $logoUrl),
                    'banner_url' => $this->assetUrl($request, $bannerUrl),
                    'profession_type' => $profile->profession_type,
                    'service_category' => $profile->service_category,
                    'founded_year' => $companyRow['founded_year'] ?? null,
                    'headquarters' => $companyRow['headquarters'] ?? null,
                    'revenue' => $companyRow['revenue'] ?? null,
                    'ceo_name' => $companyRow['ceo_name'] ?? null,
                    'ceo_photo' => $this->assetUrl($request, $companyRow['ceo_photo'] ?? null),
                    'is_featured' => isset($companyRow['is_featured']) ? (bool)$companyRow['is_featured'] : false,
                ],
                'tax' => [
                    'tax_id' => $taxId,
                    'gstin' => $taxId,
                    'gst_number' => $taxId,
                    'is_gstin_available' => !empty($taxId),
                ],
                'address' => [
                    'street' => $address['street'] ?? null,
                    'address_line' => $address['address_line'] ?? $address['street'] ?? null,
                    'city' => $profile->city ?? ($address['city'] ?? null),
                    'state' => $profile->state ?? ($address['state'] ?? null),
                    'country' => $profile->country ?? ($address['country'] ?? null),
                    'postal_code' => $profile->postal_code ?? ($address['postal_code'] ?? null),
                    'latitude' => $address['latitude'] ?? null,
                    'longitude' => $address['longitude'] ?? null,
                    'raw' => $address,
                ],
                'kyc' => [
                    'status' => $profile->kyc_status ?? 'not_submitted',
                    'verified' => (bool)($profile->verified ?? false),
                    'rejection_reason' => $profile->kyc_rejection_reason ?? null,
                    'documents_count' => count($documentPayload),
                    'documents' => $documentPayload,
                ],
                'completion' => [
                    'basic_info_complete' => $basicInfoComplete,
                    'address_complete' => $addressComplete,
                    'profile_complete' => $basicInfoComplete && $addressComplete,
                    'next_step' => method_exists($profile, 'nextProfileStep') ? $profile->nextProfileStep() : null,
                ],
                'stats' => [
                    'jobs_total' => (int)$jobCount,
                    'jobs_published' => (int)$activeJobCount,
                    'applications_total' => (int)$applicationCount,
                ],
                'subscription' => $subscription ? [
                    'id' => (int)$subscription->id,
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'auto_renew' => (bool)$subscription->auto_renew,
                    'plan' => $plan ? [
                        'id' => (int)$plan->id,
                        'name' => $plan->name,
                        'slug' => $plan->slug,
                        'price_monthly' => (float)$plan->price_monthly,
                        'price_quarterly' => (float)$plan->price_quarterly,
                        'price_annual' => (float)$plan->price_annual,
                    ] : null,
                ] : null,
                'last_payment' => $lastPayment ? [
                    'id' => (int)$lastPayment->id,
                    'amount' => (float)$lastPayment->amount,
                    'currency' => $lastPayment->currency,
                    'gateway' => $lastPayment->gateway,
                    'status' => $lastPayment->status,
                    'gateway_order_id' => $lastPayment->gateway_order_id,
                    'gateway_payment_id' => $lastPayment->gateway_payment_id,
                    'paid_at' => $lastPayment->paid_at,
                    'created_at' => $lastPayment->created_at,
                ] : null,
                'social_links' => $this->socialLinks($address),
                'raw' => [
                    'employer' => $profile->toArray(),
                    'company' => $companyRow,
                ],
            ],
        ];
    }

    private function decodeAddress($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function companyRow(int $employerId): ?array
    {
        try {
            return (new Employer())->getDb()->fetchOne(
                'SELECT * FROM companies WHERE employer_id = :eid ORDER BY id DESC LIMIT 1',
                ['eid' => $employerId]
            ) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function socialLinks(array $address): array
    {
        $links = $address['social_links'] ?? [];
        return is_array($links) ? $links : [];
    }

    private function formatKycDocument(Request $request, EmployerKycDocument $doc): array
    {
        return [
            'id' => (int)$doc->id,
            'type' => $doc->doc_type,
            'doc_type' => $doc->doc_type,
            'file_name' => $doc->file_name,
            'file_url' => $this->assetUrl($request, $doc->file_url),
            'status' => $doc->review_status,
            'review_status' => $doc->review_status,
            'review_notes' => $doc->review_notes,
            'reviewed_by' => $doc->reviewed_by,
            'reviewed_at' => $doc->reviewed_at,
            'uploaded_by' => $doc->uploaded_by,
            'uploaded_at' => $doc->uploaded_at ?? $doc->created_at,
        ];
    }

    private function assetUrl(Request $request, ?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $url = '/' . ltrim(str_replace('\\', '/', $url), '/');
        if (function_exists('fix_url')) {
            $url = fix_url($url);
        }

        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . ($request->header('Host') ?? ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }

        return rtrim($base, '/') . $url;
    }
}
