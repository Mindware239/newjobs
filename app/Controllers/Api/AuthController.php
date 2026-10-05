<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\EmployerSetting;
use App\Services\AuthService;
use App\Services\AppleOAuthService;
use App\Services\CandidateCreationService;
use App\Services\GoogleOAuthService;
use App\Services\VerificationService;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Candidate;
use App\Models\ResumeFile;
use App\Core\Storage;
use App\Services\ResumeTextExtractor;

class AuthController extends ApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful")
     * )
     */
    public function login(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required',
            // 'email_otp' => 'required', // Removed mandatory OTP for login
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $user = $this->authService->login($data['email'], $data['password']);

        if (!$user) {
            $this->error($response, 'Invalid credentials', 401);
            return;
        }

        $token = $this->authService->generateToken($user);

        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];

        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::where('user_id', '=', $user->id)->first();
            if ($candidate) {
                $userData['name'] = $candidate->full_name;
                $userData['mobile'] = $candidate->mobile ?? $user->phone;
            }
        } elseif ($user->role === 'employer') {
            $employer = Employer::where('user_id', '=', $user->id)->first();
            if ($employer) {
                $userData['company_name'] = $employer->company_name;
            }
        }

        $this->success($response, [
            'token' => $token,
            'user' => $userData
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register-candidate",
     *     summary="Candidate registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="password", type="string"),
     *                 @OA\Property(property="full_name", type="string"),
     *                 @OA\Property(property="mobile", type="string"),
     *                 @OA\Property(property="email_otp", type="string"),
     *                 @OA\Property(property="resume", type="string", format="binary", description="Resume file (PDF, DOC, DOCX)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration successful")
     * )
     */
    public function registerCandidate(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
            'full_name' => 'required',
            'mobile' => 'required',
            'email_otp' => 'required',
        ]);

        $resumeErrors = $this->validateCandidateResume($request);
        if (!empty($resumeErrors)) {
            $errors['resume'] = $resumeErrors;
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $data['email'] = strtolower(trim((string)$data['email']));
        $normalizedMobile = AuthService::normalizePhoneNumber((string)($data['mobile'] ?? ''));
        if ($normalizedMobile === '') {
            $this->error($response, 'Mobile Number must be 10 digits', 422);
            return;
        }
        $data['mobile'] = $normalizedMobile;

        if ($this->authService->findUserByAnyEmail((string)$data['email'])) {
            $this->error($response, 'Email already registered', 409);
            return;
        }
        if ($this->authService->findUserByPhone($normalizedMobile)) {
            $this->error($response, 'Mobile number already registered', 409);
            return;
        }

        $emailVerification = VerificationService::verifyEmailAuthOTP(
            (string)$data['email'],
            (string)$data['email_otp'],
            'register_candidate'
        );
        if (empty($emailVerification['success'])) {
            $this->error($response, $emailVerification['error'] ?? 'Invalid or expired email OTP', 422);
            return;
        }

        $user = $this->authService->registerCandidate($data);

        if (!$user) {
            $this->error($response, 'Registration failed', 400);
            return;
        }

        // Handle Resume Upload (API support)
        $resumeUrl = null;
        if ($request->hasFile('resume')) {
            try {
                $resumeFile = $request->file('resume');
                $storage = Storage::disk('local');
                $resumePath = $storage->store($resumeFile, 'uploads/resumes');
                $resumeUrl = $storage->url($resumePath);

                // Update Candidate Profile with resume URL
                $candidate = Candidate::findByUserId((int)$user->id);
                if ($candidate) {
                    $candidate->setAttribute('resume_url', $resumeUrl);
                    $candidate->setAttribute('is_profile_complete', 1);
                    $candidate->save();

                    // Save Resume File record
                    $resumeFileModel = new ResumeFile();
                    $resumeFileModel->fill([
                        'candidate_id' => $candidate->id,
                        'filename' => $resumeFile['name'],
                        'filepath' => $resumePath,
                        'hash' => sha1_file($storage->path($resumePath)),
                        'status' => 'uploaded',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $resumeFileModel->save();
                }
            } catch (\Throwable $e) {
                error_log('API Resume upload failed: ' . $e->getMessage());
            }
        }

        // Send welcome / verification email
        try {
            // Send role-based welcome email
            \App\Services\NotificationService::send(
                (int)$user->id,
                'candidate_welcome',
                'Welcome to Jobsence',
                'Welcome, ' . $data['full_name'] . '! Thanks for joining Jobsence. We\'re excited to help you find your next career opportunity.',
                ['candidate_name' => $data['full_name']],
                null,
                ['email']
            );

            // Notify Admin about new candidate registration
            $adminMail = getenv('ADMIN_MAIL') ?: 'admin@example.com';
            \App\Services\MailService::sendEmail(
                $adminMail,
                'New Candidate Registered: ' . $data['full_name'],
                "<p>A new candidate has registered on the platform" . ($resumeUrl ? " with a resume" : "") . ":</p>
                 <ul>
                    <li><strong>Name:</strong> {$data['full_name']}</li>
                    <li><strong>Email:</strong> {$user->email}</li>
                    <li><strong>Mobile:</strong> {$data['mobile']}</li>
                    " . ($resumeUrl ? "<li><strong>Resume:</strong> <a href=\"{$resumeUrl}\">View Resume</a></li>" : "") . "
                 </ul>"
            );
        } catch (\Throwable $e) {
            error_log('Failed to send notifications during API registration: ' . $e->getMessage());
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $data['full_name'],
                'mobile' => $data['mobile'],
                'resume_url' => $resumeUrl
            ]
        ], 'Registration successful', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register-employer",
     *     summary="Employer registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","company_name","full_name","phone","pincode","email_otp"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="password_confirm", type="string", minLength=8),
     *             @OA\Property(property="company_name", type="string"),
     *             @OA\Property(property="full_name", type="string", description="Contact person name"),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="register_as", type="string", enum={"company","individual"}, example="company"),
     *             @OA\Property(property="industry", type="string", example="IT / Software"),
     *             @OA\Property(property="company_type", type="string", example="private_limited"),
     *             @OA\Property(property="company_size", type="string", example="11-50"),
     *             @OA\Property(property="profession_type", type="string", example="Consultant"),
     *             @OA\Property(property="service_category", type="string", example="HR Services"),
     *             @OA\Property(property="gstin", type="string", nullable=true, description="Must be unique across companies/mentors"),
     *             @OA\Property(property="no_gst", type="boolean", nullable=true, description="Send true when the company has no GST. If this field is sent, gstin becomes mandatory unless no_gst is true."),
     *             @OA\Property(property="website", type="string", nullable=true),
     *             @OA\Property(property="pincode", type="string", example="110001"),
     *             @OA\Property(property="address", type="object", nullable=true),
     *             @OA\Property(property="email_otp", type="string", description="6-digit OTP sent with purpose register_employer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration successful")
     * )
     */
    public function registerEmployer(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
            'company_name' => 'required',
            'full_name' => 'required',
            'phone' => 'required',
            'email_otp' => 'required',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        if (($data['password_confirm'] ?? $data['password']) !== $data['password']) {
            $this->error($response, 'Passwords do not match', 422);
            return;
        }

        $phoneDigits = preg_replace('/\D+/', '', (string)($data['phone'] ?? ''));
        if (!preg_match('/^[0-9]{10}$/', $phoneDigits)) {
            $this->error($response, 'Mobile Number must be 10 digits', 422);
            return;
        }
        $data['phone'] = AuthService::normalizePhoneNumber($phoneDigits);

        $data['email'] = strtolower(trim((string)$data['email']));
        if ($this->authService->findUserByAnyEmail((string)$data['email'])) {
            $this->error($response, 'Email already registered', 409);
            return;
        }
        if ($this->authService->findUserByPhone((string)$data['phone'])) {
            $this->error($response, 'Mobile number already registered', 409);
            return;
        }

        $registerAs = strtolower(trim((string)($data['register_as'] ?? 'company')));
        if (!in_array($registerAs, ['company', 'individual'], true)) {
            $this->error($response, 'register_as must be company or individual', 422);
            return;
        }
        $data['register_as'] = $registerAs;

        if ($registerAs === 'company') {
            $requiredCompanyFields = [
                'industry' => 'Industry Type is required',
                'company_type' => 'Company Type is required',
                'company_size' => 'Company Size is required',
            ];
            foreach ($requiredCompanyFields as $field => $message) {
                if (trim((string)($data[$field] ?? '')) === '') {
                    $this->error($response, $message, 422);
                    return;
                }
            }
        } else {
            $requiredIndividualFields = [
                'profession_type' => 'Profession Type is required',
                'service_category' => 'Service Category is required',
            ];
            foreach ($requiredIndividualFields as $field => $message) {
                if (trim((string)($data[$field] ?? '')) === '') {
                    $this->error($response, $message, 422);
                    return;
                }
            }
        }

        $address = $data['address'] ?? [];
        if (is_string($address)) {
            $address = json_decode($address, true) ?: [];
        }
        if (!is_array($address)) {
            $address = [];
        }

        $postalCode = trim((string)($data['pincode'] ?? $data['postal_code'] ?? ($address['postal_code'] ?? '')));
        if ($postalCode === '') {
            $this->error($response, 'Pin Code is required', 422);
            return;
        }
        if (!preg_match('/^[0-9]{6}$/', $postalCode)) {
            $this->error($response, 'Pin Code must be exactly 6 digits', 422);
            return;
        }

        $gstin = strtoupper(trim((string)($data['gstin'] ?? $data['tax_id'] ?? '')));
        if ($gstin !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/', $gstin)) {
            $this->error($response, 'Please enter valid GSTIN number.', 422);
            return;
        }
        $providerError = $this->providerIdentityError($data, (string)$data['email'], $phoneDigits, $gstin);
        if ($providerError !== null) {
            $this->error($response, $providerError, 409);
            return;
        }

        if ($postalCode !== '') {
            $data['postal_code'] = $postalCode;
            $address['postal_code'] = $postalCode;
            $data['address'] = $address;
        }
        if ($gstin !== '') {
            $data['tax_id'] = $gstin;
        }

        $emailVerification = VerificationService::verifyEmailAuthOTP(
            (string)$data['email'],
            (string)$data['email_otp'],
            'register_employer'
        );
        if (empty($emailVerification['success'])) {
            $this->error($response, $emailVerification['error'] ?? 'Invalid or expired email OTP', 422);
            return;
        }

        $user = $this->authService->registerEmployer($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email already exists', 400);
            return;
        }

        // Send welcome / verification email for Employer
        try {
            \App\Services\VerificationService::sendEmailVerification((int)$user->id, (string)$user->email);

            // Send role-based welcome email for Employer
            \App\Services\NotificationService::send(
                (int)$user->id,
                'employer_welcome',
                'Welcome to Jobsence',
                'Thank you for registering as an employer. We are here to help you hire the best talent.',
                [],
                null,
                ['email']
            );

            // Notify Admin about new employer registration
            $adminMail = getenv('ADMIN_MAIL') ?: 'admin@example.com';
            \App\Services\MailService::sendEmail(
                $adminMail,
                'New Employer Registered: ' . ($data['company_name'] ?? 'Unknown'),
                "<p>A new employer has registered on the platform:</p>
                 <ul>
                    <li><strong>Company:</strong> " . ($data['company_name'] ?? 'N/A') . "</li>
                    <li><strong>Email:</strong> {$user->email}</li>
                    <li><strong>Phone:</strong> " . ($data['phone'] ?? 'N/A') . "</li>
                 </ul>"
            );
        } catch (\Throwable $e) {
            error_log('Failed to send notifications during API employer registration: ' . $e->getMessage());
        }

        $token = $this->authService->generateToken($user);
        $employer = Employer::where('user_id', '=', (int)$user->id)->first();

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'name' => $data['full_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
            ],
            'employer' => $employer ? [
                'id' => (int)$employer->id,
                'company_name' => $employer->attributes['company_name'] ?? null,
                'register_as' => $employer->attributes['register_as'] ?? null,
                'industry' => $employer->attributes['industry'] ?? null,
                'company_type' => $employer->attributes['company_type'] ?? null,
                'profession_type' => $employer->attributes['profession_type'] ?? null,
                'service_category' => $employer->attributes['service_category'] ?? null,
                'company_size' => $employer->attributes['size'] ?? null,
                'postal_code' => $employer->attributes['postal_code'] ?? null,
                'kyc_status' => $employer->attributes['kyc_status'] ?? null,
            ] : null,
            'redirect' => '/employer/company-profile'
        ], 'Registration successful', 201);
    }

    /**
     * One company = one Email + Mobile + GST (shared with the website and mentor registrations).
     * GST is mandatory only for clients that send the `no_gst` flag (newer app versions), so older
     * app builds keep working; an already-registered GST is always refused.
     */
    private function providerIdentityError(array $data, string $email, string $phone, string $gstin): ?string
    {
        if (array_key_exists('no_gst', $data)) {
            $noGst = in_array($data['no_gst'], [true, 1, '1', 'true', 'on'], true);
            if ($gstin === '' && !$noGst) {
                return 'GST नंबर भरें या “मेरे पास GST नहीं है” चुनें / Enter GSTIN or set no_gst';
            }
        }

        $conflicts = \App\Services\Registration\ProviderIdentity::conflicts($email, $phone, $gstin);
        if (!$conflicts) {
            return null;
        }
        return implode(' • ', array_map(static fn($m) => $m[0] . ' / ' . $m[1], $conflicts));
    }

    public function sendPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));
        $role = trim((string)($data['role'] ?? ''));

        if ($phone === '') {
            $this->error($response, 'Phone number is required', 422);
            return;
        }

        if (in_array(strtolower($purpose), ['register_candidate', 'register_employer'], true)) {
            $normalizedPhone = AuthService::normalizePhoneNumber($phone);
            if ($normalizedPhone === '') {
                $this->error($response, 'Mobile Number must be 10 digits', 422);
                return;
            }
            if ($this->authService->findUserByPhone($normalizedPhone)) {
                $this->error($response, 'Mobile number already registered', 409);
                return;
            }
        }

        $result = VerificationService::sendAuthPhoneOTP($phone, $purpose, ['role' => $role]);
        if (empty($result['success'])) {
            $this->error($response, $result['error'] ?? 'Failed to send OTP', 500);
            return;
        }

        $this->success($response, [
            'phone' => $result['phone'],
            'purpose' => $result['purpose'],
            'mode' => $result['mode'] ?? 'sms',
            'otp_preview' => $result['otp_preview'] ?? null,
        ], 'OTP sent to your phone');
    }

    public function sendEmailOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $email = trim((string)($data['email'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));
        $role = trim((string)($data['role'] ?? ''));

        if ($email === '') {
            $this->error($response, 'Email is required', 422);
            return;
        }

        if (in_array(strtolower($purpose), ['register_candidate', 'register_employer'], true)) {
            if ($this->authService->findUserByAnyEmail($email)) {
                $this->error($response, 'Email already registered', 409);
                return;
            }

            $phone = AuthService::normalizePhoneNumber((string)($data['phone'] ?? ($data['mobile'] ?? '')));
            if ($phone !== '' && $this->authService->findUserByPhone($phone)) {
                $this->error($response, 'Mobile number already registered', 409);
                return;
            }

            if (strtolower($purpose) === 'register_employer') {
                $gstin = strtoupper(trim((string)($data['gstin'] ?? '')));
                $providerError = $this->providerIdentityError($data, $email, $phone, $gstin);
                if ($providerError !== null) {
                    $this->error($response, $providerError, 409);
                    return;
                }
            }
        }

        $result = VerificationService::sendEmailAuthOTP($email, $purpose, ['role' => $role]);
        if (empty($result['success'])) {
            $this->error($response, $result['error'] ?? 'Failed to send OTP', 500);
            return;
        }

        $payload = [
            'email' => $result['email'] ?? $email,
            'purpose' => $result['purpose'] ?? $purpose,
        ];

        $this->success($response, $payload, 'OTP sent to your email');
    }

    public function loginWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $otp = trim((string)($data['otp'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));

        if ($phone === '' || $otp === '') {
            $this->error($response, 'Phone and OTP are required', 422);
            return;
        }

        // Try 'login' purpose first, then fallback to 'auth'
        $verify = VerificationService::verifyAuthPhoneOTP($phone, $otp, 'login');
        if (empty($verify['success'])) {
            $verify = VerificationService::verifyAuthPhoneOTP($phone, $otp, 'auth');
        }

        if (empty($verify['success'])) {
            $this->error($response, $verify['error'] ?? 'Invalid or expired OTP', 422);
            return;
        }

        // Find user by normalized phone, accepting 10-digit and +91 formats.
        $user = $this->authService->findUserByPhone($phone);
        if (!$user) {
            $this->error($response, 'No account found with this phone number', 404);
            return;
        }

        $token = $this->authService->generateToken($user);

        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];

        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::where('user_id', '=', $user->id)->first();
            if ($candidate) {
                $userData['name'] = $candidate->full_name;
                $userData['mobile'] = $candidate->mobile ?? $user->phone;
            }
        } elseif ($user->role === 'employer') {
            $employer = Employer::where('user_id', '=', $user->id)->first();
            if ($employer) {
                $userData['company_name'] = $employer->company_name;
            }
        }

        $this->success($response, [
            'token' => $token,
            'user' => $userData
        ], 'Login successful');
    }

    public function registerCandidateWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'phone' => 'required',
            'otp' => 'required',
            'full_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
        ]);

        $resumeErrors = $this->validateCandidateResume($request);
        if (!empty($resumeErrors)) {
            $errors['resume'] = $resumeErrors;
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $data['email'] = strtolower(trim((string)$data['email']));
        $data['phone'] = AuthService::normalizePhoneNumber((string)$data['phone']);
        if ($data['phone'] === '') {
            $this->error($response, 'Mobile Number must be 10 digits', 422);
            return;
        }
        if ($this->authService->findUserByAnyEmail((string)$data['email'])) {
            $this->error($response, 'Email already registered', 409);
            return;
        }
        if ($this->authService->findUserByPhone((string)$data['phone'])) {
            $this->error($response, 'Mobile number already registered', 409);
            return;
        }

        $verify = VerificationService::verifyAuthPhoneOTP($data['phone'], $data['otp'], 'register_candidate');
        if (empty($verify['success'])) {
            $this->error($response, $verify['error'] ?? 'Invalid or expired OTP', 422);
            return;
        }

        // Add phone to data for registration
        $data['mobile'] = $data['phone'];
        $user = $this->authService->registerCandidate($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email/phone already exists', 400);
            return;
        }

        // Handle Resume Upload (API support)
        $resumeUrl = null;
        if ($request->hasFile('resume')) {
            try {
                $resumeFile = $request->file('resume');
                $storage = Storage::disk('local');
                $resumePath = $storage->store($resumeFile, 'uploads/resumes');
                $resumeUrl = $storage->url($resumePath);

                // Update Candidate Profile with resume URL
                $candidate = Candidate::findByUserId((int)$user->id);
                if ($candidate) {
                    $candidate->setAttribute('resume_url', $resumeUrl);
                    $candidate->setAttribute('is_profile_complete', 1);
                    $candidate->save();

                    // Save Resume File record
                    $resumeFileModel = new ResumeFile();
                    $resumeFileModel->fill([
                        'candidate_id' => $candidate->id,
                        'filename' => $resumeFile['name'],
                        'filepath' => $resumePath,
                        'hash' => sha1_file($storage->path($resumePath)),
                        'status' => 'uploaded',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $resumeFileModel->save();
                }
            } catch (\Throwable $e) {
                error_log('API Phone Register Resume upload failed: ' . $e->getMessage());
            }
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $data['full_name'],
                'mobile' => $data['phone'],
                'resume_url' => $resumeUrl
            ]
        ], 'Registration successful', 201);
    }

    public function registerEmployerWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'phone' => 'required',
            'otp' => 'required',
            'company_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $data['email'] = strtolower(trim((string)$data['email']));
        $data['phone'] = AuthService::normalizePhoneNumber((string)$data['phone']);
        if ($data['phone'] === '') {
            $this->error($response, 'Mobile Number must be 10 digits', 422);
            return;
        }
        if ($this->authService->findUserByAnyEmail((string)$data['email'])) {
            $this->error($response, 'Email already registered', 409);
            return;
        }
        if ($this->authService->findUserByPhone((string)$data['phone'])) {
            $this->error($response, 'Mobile number already registered', 409);
            return;
        }
        $gstin = strtoupper(trim((string)($data['gstin'] ?? $data['tax_id'] ?? '')));
        if ($gstin !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/', $gstin)) {
            $this->error($response, 'Please enter valid GSTIN number.', 422);
            return;
        }
        $providerError = $this->providerIdentityError($data, (string)$data['email'], (string)$data['phone'], $gstin);
        if ($providerError !== null) {
            $this->error($response, $providerError, 409);
            return;
        }
        if ($gstin !== '') {
            $data['tax_id'] = $gstin;
        }

        $verify = VerificationService::verifyAuthPhoneOTP($data['phone'], $data['otp'], 'register_employer');
        if (empty($verify['success'])) {
            $this->error($response, $verify['error'] ?? 'Invalid or expired OTP', 422);
            return;
        }

        $user = $this->authService->registerEmployer($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email/phone already exists', 400);
            return;
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'company_name' => $data['company_name'] ?? null,
            ]
        ], 'Registration successful', 201);
    }

    public function me(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $this->success($response, [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ]);
    }

    public function logout(Request $request, Response $response): void
    {
        $this->authService->logout();
        $this->success($response, [], 'Logged out successfully');
    }

    public function refreshToken(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $token = $this->authService->generateToken($user);
        $this->success($response, ['token' => $token], 'Token refreshed successfully');
    }

    public function changePassword(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|password_strong|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        if (!$user->verifyPassword((string)$data['current_password'])) {
            $this->error($response, 'Current password is incorrect', 422);
            return;
        }

        $user->setPassword((string)$data['new_password']);
        try {
            $user->save();
            $this->success($response, [], 'Password updated successfully');
        } catch (\Throwable $e) {
            error_log("API ChangePassword Error: " . $e->getMessage());
            $this->error($response, 'Failed to update password. Please try again.', 500);
        }
    }

    public function forgotPassword(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $email = trim((string)($data['email'] ?? ''));

        if ($email === '') {
            $this->error($response, 'Email is required', 422);
            return;
        }

        $user = User::where('email', '=', $email)->first();
        if ($user) {
            try {
                $token = \App\Services\VerificationService::generateResetToken((int)$user->id);

                $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost') . '/reset-password?token=' . urlencode($token);
                \App\Services\NotificationService::send(
                    (int)$user->id,
                    'password_reset',
                    'Password Reset Request',
                    'Please click the link to reset your password: ' . $resetLink,
                    ['link' => $resetLink],
                    $resetLink,
                    ['email']
                );
            } catch (\Throwable $e) {
                error_log('API forgotPassword processing failed: ' . $e->getMessage());
            }
        }

        $this->success($response, [], 'If the email exists, a reset link has been sent');
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'token' => 'required',
            'password' => 'required|password_strong|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $userId = VerificationService::verifyResetToken((string)$data['token']);
        if (!$userId) {
            $this->error($response, 'Invalid or expired reset token', 400);
            return;
        }

        $user = User::find((int)$userId);
        if (!$user) {
            $this->error($response, 'User not found', 404);
            return;
        }

        $user->setPassword((string)$data['password']);
        try {
            $user->save();
            $this->success($response, [], 'Password reset successfully');
        } catch (\Throwable $e) {
            error_log("API ResetPassword Save Error: " . $e->getMessage());
            $this->error($response, 'Failed to reset password. Please try again.', 500);
        }
    }

    public function verifyEmail(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $userId = (int)($data['user_id'] ?? 0);
        $code = trim((string)($data['code'] ?? ''));

        if ($userId <= 0 || $code === '') {
            $this->error($response, 'user_id and code are required', 422);
            return;
        }

        if (!VerificationService::verifyEmail($userId, $code)) {
            $this->error($response, 'Invalid verification code', 400);
            return;
        }

        $this->success($response, [], 'Email verified successfully');
    }

    public function verifyOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $userId = (int)($data['user_id'] ?? 0);
        $otp = trim((string)($data['otp'] ?? ''));

        if ($userId <= 0 || $otp === '') {
            $this->error($response, 'user_id and otp are required', 422);
            return;
        }

        if (!VerificationService::verifyPhone($userId, $otp)) {
            $this->error($response, 'Invalid OTP', 400);
            return;
        }

        $this->success($response, [], 'Phone verified successfully');
    }

    public function resendOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $userId = (int)($data['user_id'] ?? 0);
        $type = trim((string)($data['type'] ?? 'email'));

        $user = User::find($userId);
        if (!$user) {
            $this->error($response, 'User not found', 404);
            return;
        }

        if ($type === 'phone') {
            $phone = (string)($data['phone'] ?? ($user->attributes['phone'] ?? ''));
            if ($phone === '') {
                $this->error($response, 'Phone number is required', 422);
                return;
            }

            $result = VerificationService::sendPhoneOTP((int)$user->id, $phone);
            if (empty($result['success'])) {
                $this->error($response, $result['error'] ?? 'Failed to send OTP', 500);
                return;
            }
            $this->success($response, [], 'OTP sent successfully');
            return;
        }

        VerificationService::sendEmailVerification((int)$user->id, (string)$user->email);
        $this->success($response, [], 'Verification code sent successfully');
    }

    public function googleCallback(Request $request, Response $response): void
    {
        try {
            $config = require __DIR__ . '/../../../config/google.php';
            $googleService = new GoogleOAuthService($config);
            $data = $request->getJsonBody();

            if (!empty($data['id_token'])) {
                $googleUser = $googleService->verifyIdToken((string)$data['id_token']);
            } elseif (!empty($data['access_token'])) {
                $googleUser = $googleService->getUserInfo(['access_token' => (string)$data['access_token']]);
            } else {
                $this->error($response, 'id_token or access_token is required', 422);
                return;
            }

            $role = $this->normalizeOAuthRole($data['role'] ?? null);
            $user = $this->findOrCreateOAuthUser('google', $googleUser, $role, $data);
            if (!$user) {
                $this->error($response, 'Failed to authenticate with Google', 500);
                return;
            }

            $this->success($response, $this->buildOAuthAuthPayload($user), 'Google authentication successful');
        } catch (\Throwable $e) {
            error_log('API Google auth error: ' . $e->getMessage());
            $this->error($response, 'Failed to authenticate with Google', 400);
        }
    }

    public function appleCallback(Request $request, Response $response): void
    {
        try {
            $config = require __DIR__ . '/../../../config/apple.php';
            $appleService = new AppleOAuthService($config);
            $data = $request->getJsonBody();

            if (!empty($data['id_token'])) {
                $appleUser = $appleService->decodeIdToken((string)$data['id_token']);
            } elseif (!empty($data['code'])) {
                $tokenData = $appleService->exchangeCodeForToken((string)$data['code']);
                $idToken = (string)($tokenData['id_token'] ?? '');
                if ($idToken === '') {
                    $this->error($response, 'Apple id_token not returned', 400);
                    return;
                }
                $appleUser = $appleService->decodeIdToken($idToken);
            } else {
                $this->error($response, 'id_token or code is required', 422);
                return;
            }

            $profile = [
                'id' => $appleUser['sub'] ?? '',
                'email' => $appleUser['email'] ?? ($data['email'] ?? ''),
                'name' => trim((string)($data['name'] ?? '')),
            ];

            if ($profile['id'] === '') {
                $this->error($response, 'Invalid Apple user data', 400);
                return;
            }

            $role = $this->normalizeOAuthRole($data['role'] ?? null);
            $user = $this->findOrCreateOAuthUser('apple', $profile, $role, $data);
            if (!$user) {
                $this->error($response, 'Failed to authenticate with Apple', 500);
                return;
            }

            $this->success($response, $this->buildOAuthAuthPayload($user), 'Apple authentication successful');
        } catch (\Throwable $e) {
            error_log('API Apple auth error: ' . $e->getMessage());
            $this->error($response, 'Failed to authenticate with Apple', 400);
        }
    }

    private function normalizeOAuthRole(mixed $role): string
    {
        $value = strtolower(trim((string)$role));
        return in_array($value, ['candidate', 'employer'], true) ? $value : 'candidate';
    }

    private function buildOAuthAuthPayload(User $user): array
    {
        $token = $this->authService->generateToken($user);

        return [
            'token' => $token,
            'user' => [
                'id' => (int)$user->id,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ];
    }

    private function findOrCreateOAuthUser(string $provider, array $userData, string $requestedRole, array $extraData = []): ?User
    {
        if (empty($userData['id'])) {
            return null;
        }

        $providerIdField = $provider . '_id';
        $providerEmailField = $provider . '_email';
        $providerNameField = $provider . '_name';

        $user = User::where($providerIdField, '=', $userData['id'])->first();
        if ($user) {
            $this->syncOAuthUserFields($user, $provider, $userData);
            $this->ensureOAuthProfileForRole($user, $extraData);
            return $user;
        }

        $email = strtolower(trim((string)($userData['email'] ?? '')));
        if ($email !== '') {
            $user = $this->authService->findUserByAnyEmail($email);
            if ($user) {
                if ($user->role !== $requestedRole) {
                    throw new \RuntimeException('An account with this email already exists under a different role.');
                }

                $this->syncOAuthUserFields($user, $provider, $userData);
                $this->ensureOAuthProfileForRole($user, $extraData);
                return $user;
            }
        }

        if ($email === '') {
            throw new \RuntimeException('Email is required the first time you sign in with this provider.');
        }

        $user = new User();
        $payload = [
            'email' => $email,
            'role' => $requestedRole,
            'status' => 'active',
            $providerIdField => $userData['id'],
            $providerEmailField => $email,
            'is_email_verified' => 1,
        ];

        if (!empty($userData['name'])) {
            $payload[$providerNameField] = $userData['name'];
        }
        if ($provider === 'google' && !empty($userData['picture'])) {
            $payload['google_picture'] = $userData['picture'];
        }
        if (!empty($extraData['phone'])) {
            $phone = AuthService::normalizePhoneNumber((string)$extraData['phone']);
            if ($phone === '') {
                throw new \RuntimeException('A valid phone number is required.');
            }
            if ($this->authService->findUserByPhone($phone)) {
                throw new \RuntimeException('Mobile number already registered.');
            }
            $payload['phone'] = $phone;
        }

        $user->fill($payload);
        $user->setPassword(bin2hex(random_bytes(32)));

        if (!$user->save()) {
            return null;
        }

        $this->ensureOAuthProfileForRole($user, $extraData + $userData);
        return $user;
    }

    private function syncOAuthUserFields(User $user, string $provider, array $userData): void
    {
        $providerIdField = $provider . '_id';
        $providerEmailField = $provider . '_email';
        $providerNameField = $provider . '_name';

        $payload = [
            $providerIdField => $userData['id'],
            'is_email_verified' => 1,
        ];

        if (!empty($userData['email'])) {
            $payload[$providerEmailField] = $userData['email'];
        }
        if (!empty($userData['name'])) {
            $payload[$providerNameField] = $userData['name'];
        }
        if ($provider === 'google' && !empty($userData['picture'])) {
            $payload['google_picture'] = $userData['picture'];
        }

        $user->fill($payload);
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    private function validateCandidateResume(Request $request): array
    {
        if (!$request->hasFile('resume')) {
            return []; // Optional for API unless strictly required
        }

        $resumeFile = $request->file('resume');
        $ext = strtolower(pathinfo((string)($resumeFile['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            return ['Invalid resume format. Only PDF, DOC, and DOCX are allowed'];
        }

        if ((int)($resumeFile['size'] ?? 0) > 5 * 1024 * 1024) {
            return ['Resume size exceeds 5MB limit'];
        }

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string)finfo_file($finfo, (string)$resumeFile['tmp_name']);
                finfo_close($finfo);
            }
        }

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ];

        if ($mime !== '' && !in_array($mime, $allowedMimes, true)) {
            return ['Invalid file content. Please upload a real PDF or Word document.'];
        }

        try {
            $extractor = new ResumeTextExtractor();
            $text = $extractor->extractResumeText((string)$resumeFile['tmp_name'], $ext);
            $text = trim($text);
            if ($text !== '' && strlen($text) < 80) {
                return ['The uploaded resume contains too little readable text. Please upload a text-based PDF or Word document.'];
            }
        } catch (\Throwable $e) {
            error_log("Resume text verification skipped during registration: " . $e->getMessage());
        }

        return [];
    }

    private function ensureOAuthProfileForRole(User $user, array $data): void
    {
        if ($user->role === 'candidate') {
            $service = new CandidateCreationService();
            $service->ensureCandidateForUser((int)$user->id, [
                'full_name' => $data['name'] ?? null,
                'mobile' => $data['phone'] ?? null,
                'profile_picture' => $data['picture'] ?? null,
                'created_by' => 'oauth',
                'source' => 'mobile_oauth'
            ]);
            return;
        }

        $employer = Employer::findByUserId((int)$user->id);
        if (!$employer) {
            $employer = new Employer();
            $companyName = trim((string)($data['company_name'] ?? ''));
            if ($companyName === '') {
                $companyName = trim((string)($data['name'] ?? ''));
            }
            if ($companyName === '') {
                $companyName = 'Company ' . (int)$user->id;
            }

            $employer->fill([
                'user_id' => (int)$user->id,
                'company_name' => $companyName,
                'company_slug' => $employer->generateSlug($companyName),
                'website' => $data['website'] ?? null,
                'description' => $data['description'] ?? null,
                'industry' => $data['industry'] ?? null,
                'size' => $data['company_size'] ?? null,
                'country' => $data['country'] ?? null,
                'kyc_status' => 'pending',
            ]);
            $employer->save();
        }

        $settings = new EmployerSetting();
        $settings->fill([
            'employer_id' => (int)$employer->id,
            'billing_plan' => 'free',
            'credits' => 0,
            'timezone' => (($data['country'] ?? '') === 'India') ? 'Asia/Kolkata' : 'UTC'
        ]);
        $settings->save();
    }
}
