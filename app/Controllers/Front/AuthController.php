<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Employer;
use App\Models\EmployerSetting;
use App\Models\EmployerKycDocument;
use App\Core\RedisClient;
use App\Repositories\AuthRepository;
use App\Services\AuthService;
use App\Services\TrustedDeviceService;
use App\Services\LoginPinService;
use App\Services\AuthFlowService;
use App\Services\VerificationService;
use App\Services\GoogleOAuthService;
use App\Services\AppleOAuthService;
use App\Services\MailService;
use App\Services\CookieService;
use App\Core\Storage;
use App\Services\ResumeTextExtractor;
use App\Models\Candidate;
use App\Models\ResumeFile;

class AuthController extends BaseController
{
    public function register(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8|max:20',
            'role' => 'required',
        ]);

        if (!empty($errors)) {
            $response->json(['errors' => $errors], 422);
            return;
        }
        if (!in_array((string)$data['role'], ['candidate', 'employer'], true)) {
            $response->json(['errors' => ['role' => 'Role must be candidate or employer']], 422);
            return;
        }
        // Mobile number is mandatory for every account.
        $mobileDigits = preg_replace('/\D/', '', (string)($data['mobile'] ?? $data['phone'] ?? ''));
        $mobileDigits = strlen($mobileDigits) === 12 && str_starts_with($mobileDigits, '91') ? substr($mobileDigits, 2) : $mobileDigits;
        if (!preg_match('/^[6-9]\d{9}$/', (string)$mobileDigits)) {
            $response->json(['errors' => ['mobile' => 'A valid 10-digit mobile number is required']], 422);
            return;
        }
        if ((new AuthService())->findUserByPhone($mobileDigits)) {
            $response->json(['error' => 'Mobile number already registered'], 409);
            return;
        }

        // Check if user exists
        $existing = User::where('email', '=', $data['email'])->first();
        if ($existing) {
            $response->json(['error' => 'Email already registered'], 409);
            return;
        }

        $user = new User();
        $user->fill([
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => AuthService::normalizePhoneNumber($mobileDigits),
            'status' => 'pending'
        ]);
        /** @var \App\Models\User|null $user */
        $user->setPassword($data['password']);

        if ($user->save()) {
            // Create employer profile if role is employer
            if ($data['role'] === 'employer') {
                $employer = new Employer();
                $employer->fill([
                    'user_id' => $user->id,
                    'company_name' => $data['company_name'] ?? '',
                    'company_slug' => $employer->generateSlug($data['company_name'] ?? 'company-' . $user->id),
                    'kyc_status' => 'not_submitted'
                ]);
                $employer->save();

                // Create default settings
                $settings = new EmployerSetting();
                $settings->fill([
                    'employer_id' => $employer->id,
                    'billing_plan' => 'free',
                    'credits' => 0
                ]);
                $settings->save();
            } elseif ($data['role'] === 'candidate') {
                $candidateData = [];
                if (!empty($data['full_name'])) {
                    $candidateData['full_name'] = $data['full_name'];
                }
                if (!empty($data['mobile'])) {
                    $candidateData['mobile'] = $data['mobile'];
                }
                $candidate = \App\Models\Candidate::createForUser((int)$user->id, $candidateData);
                try {
                    \App\Services\NotificationService::queueEmail(
                        $user->email,
                        'candidate_welcome',
                        ['candidate_user_id' => (int)$user->id]
                    );
                } catch (\Exception $e) {}
                try {
                    $matchService = new \App\Services\JobMatchService();
                    $matchService->findMatchingJobsForCandidateAndNotifyEmployers($candidate);
                    $matchService->findMatchingJobsForCandidateAndNotifyCandidate($candidate);
                } catch (\Throwable $t) {}
            }

            $response->json(['message' => 'Registration successful', 'user_id' => $user->id], 201);
        } else {
            $response->json(['error' => 'Registration failed'], 500);
        }
    }

    public function registerEmployer(Request $request, Response $response): void
    {
        // Show registration form
        if ($request->getMethod() === 'GET') {
            // Ensure CSRF token exists
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $response->view('auth/register-employer', [
                'title' => 'Employer Registration'
            ]);
            return;
        }

        // Handle registration POST - support both JSON and form data
        try {
            $contentType = $request->header('Content-Type') ?? '';
            $isJson = strpos($contentType, 'application/json') !== false;
            $isFormData = strpos($contentType, 'multipart/form-data') !== false || !empty($_POST);
            
            if ($isJson) {
                $data = $request->getJsonBody();
            } else {
                // For FormData, use $_POST directly
                $data = $_POST;
                // Parse JSON fields if present
                if (isset($data['address']) && is_string($data['address'])) {
                    $data['address'] = json_decode($data['address'], true) ?? [];
                }
            }
            
            error_log("Registration data received: " . json_encode(array_keys($data)));
            
            $errors = $this->validate($data, [
                'full_name' => 'required',
                'company_name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'password' => 'required|password_strong|min:8|max:20',
            ]);

            if (!empty($errors)) {
                $response->json(['errors' => $errors], 422);
                return;
            }

            $phoneDigits = preg_replace('/\D+/', '', (string)($data['phone'] ?? ''));
            if (!preg_match('/^[0-9]{10}$/', $phoneDigits)) {
                $response->json(['error' => 'Mobile Number must be 10 digits'], 422);
                return;
            }
            $normalizedPhone = AuthService::normalizePhoneNumber($phoneDigits);
            if ($normalizedPhone === '' || $this->findExistingRegistrationPhone($normalizedPhone)) {
                $response->json(['error' => 'यह Mobile नंबर पहले से रजिस्टर्ड है / Mobile number already registered'], 409);
                return;
            }
            $data['phone'] = $normalizedPhone;

            $postalCode = trim((string)($data['pincode'] ?? $data['postal_code'] ?? ($data['address']['postal_code'] ?? '')));
            if ($postalCode !== '' && !preg_match('/^[0-9]{6}$/', $postalCode)) {
                $response->json(['error' => 'Pin Code must be exactly 6 digits'], 422);
                return;
            }

            $gstin = strtoupper(trim((string)($data['gstin'] ?? $data['tax_id'] ?? '')));
            if ($gstin !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/', $gstin)) {
                $response->json(['error' => 'Please enter valid GSTIN number.'], 422);
                return;
            }
            $providerError = $this->providerIdentityError((string)($data['email'] ?? ''), $phoneDigits, $gstin, $data['no_gst'] ?? null);
            if ($providerError !== null) {
                $response->json(['error' => $providerError], 409);
                return;
            }

            $emailVerification = VerificationService::verifyEmailAuthOTP(
                (string)($data['email'] ?? ''),
                (string)($data['email_otp'] ?? ''),
                'register_employer'
            );
            if (empty($emailVerification['success'])) {
                $response->json(['error' => $emailVerification['error'] ?? 'Invalid or expired email OTP'], 422);
                return;
            }

            // Check if user exists
            $existing = $this->findExistingRegistrationEmail((string)$data['email']);
            if ($existing) {
                $response->json(['error' => 'Email already registered'], 409);
                return;
            }

            // Create user
            $user = new User();
            $user->fill([
                'name' => $data['full_name'] ?? null,
                'email' => $data['email'],
                'role' => 'employer',
                'status' => 'pending',
                'phone' => $data['phone'] ?? null
            ]);
            $user->setPassword($data['password']);

            if (!$user->save()) {
                error_log("Failed to save user. Email: " . ($data['email'] ?? 'N/A'));
                error_log("User attributes: " . json_encode($user->attributes ?? []));
                $response->json(['error' => 'Registration failed. Please check server logs for details.'], 500);
                return;
            }
            error_log("✓ User saved successfully. ID: " . $user->id);
        } catch (\Exception $e) {
            error_log("Registration exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $response->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
            return;
        }

        // Create employer profile
        $address = is_string($data['address'] ?? null) 
            ? json_decode($data['address'], true) 
            : ($data['address'] ?? []);
        
        if (!is_array($address)) {
            $address = [];
        }
        
        $employer = new Employer();
        $companyName = trim((string)($data['company_name'] ?? ''));
        if ($companyName === '') {
            $emailLocal = '';
            try {
                $emailParts = explode('@', (string)($data['email'] ?? ''));
                $emailLocal = trim((string)($emailParts[0] ?? ''));
            } catch (\Throwable $t) {}
            $companyName = $emailLocal !== '' ? ucfirst($emailLocal) : ('Company ' . (string)$user->id);
        }
        $companySlug = $companyName !== '' ? (new Employer())->generateSlug($companyName) : ('company-' . (string)$user->id);
        
        $employer->fill([
            'user_id' => $user->id,
            'register_as' => $data['register_as'] ?? 'company',
            'company_name' => $companyName,
            'company_slug' => $companySlug,
            'website' => $data['website'] ?? null,
            'industry' => $data['industry'] ?? null,
            'company_type' => $data['company_type'] ?? null,
            'profession_type' => $data['profession_type'] ?? null,
            'service_category' => $data['service_category'] ?? null,
            'size' => isset($data['company_size']) ? $data['company_size'] : (isset($data['size']) ? $data['size'] : null),
            'address' => !empty($address) ? json_encode($address, JSON_UNESCAPED_UNICODE) : null,
            'country' => $data['country'] ?? 'India',
            'state' => $address['state'] ?? null,
            'city' => $address['city'] ?? null,
            'postal_code' => $postalCode ?: null,
            'tax_id' => $gstin ?: null,
            'kyc_status' => 'not_submitted'
        ]);
        
        error_log("Attempting to save employer for user ID: " . $user->id);
        error_log("Employer data: " . json_encode($employer->attributes));
        
        try {
            if (!$employer->save()) {
                error_log("Failed to save employer. User ID: " . ($user->id ?? 'N/A'));
                error_log("Employer attributes: " . json_encode($employer->attributes ?? []));
                $response->json(['error' => 'Failed to create employer profile. Please check server logs.'], 500);
                return;
            }
            error_log("✓ Employer saved successfully. ID: " . $employer->id);
        } catch (\Exception $e) {
            error_log("Exception saving employer: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $response->json(['error' => 'Failed to create employer profile: ' . $e->getMessage()], 500);
            return;
        }

        // Create default settings
        try {
            $settings = new EmployerSetting();
            $settings->fill([
                'employer_id' => $employer->id,
                'billing_plan' => 'free',
                'credits' => 0,
                'timezone' => $data['country'] === 'India' ? 'Asia/Kolkata' : 'UTC'
            ]);
            if (!$settings->save()) {
                error_log("Warning: Failed to save employer settings for employer ID: " . $employer->id);
                // Continue anyway, settings are not critical
            } else {
                error_log("✓ Employer settings saved for employer ID: " . $employer->id);
            }
        } catch (\Exception $e) {
            error_log("Exception creating employer settings: " . $e->getMessage());
            // Continue anyway, settings are not critical
        }

        // Upload KYC documents
        $storage = new \App\Core\Storage();
        $uploadedDocs = [];

        $docTypes = ['business_license', 'tax_id', 'address_proof', 'director_id', 'other'];
        foreach ($docTypes as $docType) {
            $fileKey = 'doc_' . $docType;
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                    try {
                        $allowedExt = ['pdf','jpg','jpeg','png'];
                        $name = (string)($file['name'] ?? '');
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExt, true)) {
                            throw new \Exception('Invalid file type');
                        }
                        $sizeBytes = (int)($file['size'] ?? 0);
                        if ($sizeBytes <= 0 || $sizeBytes > 10 * 1024 * 1024) {
                            throw new \Exception('File too large');
                        }
                        $mime = (string)($file['type'] ?? '');
                        $allowedMime = ['application/pdf','image/jpeg','image/png'];
                        if ($mime !== '' && !in_array($mime, $allowedMime, true)) {
                            throw new \Exception('Invalid MIME type');
                        }
                        // Ensure upload directory exists
                        $uploadDir = __DIR__ . '/../../storage/uploads/kyc/' . $employer->id;
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        // Store under /uploads/kyc/{employer_id}
                        $filePath = $storage->store($file, 'uploads/kyc/' . $employer->id);
                        
                        $kycDoc = new EmployerKycDocument();
                        $kycDoc->fill([
                            'employer_id' => $employer->id,
                            'doc_type' => $docType,
                            'file_url' => $storage->url($filePath),
                            'file_name' => $file['name'] ?? 'document.pdf',
                            'uploaded_by' => $user->id,
                            'review_status' => 'pending'
                        ]);
                        
                        if ($kycDoc->save()) {
                            $uploadedDocs[] = $docType;
                            error_log("KYC document uploaded: $docType for employer {$employer->id}");
                        } else {
                            error_log("Failed to save KYC document: $docType");
                        }
                    } catch (\Exception $e) {
                        error_log("File upload error for $docType: " . $e->getMessage());
                        error_log("Stack trace: " . $e->getTraceAsString());
                    }
                } else {
                    error_log("File upload error for $fileKey: " . ($file['error'] ?? 'Unknown error'));
                }
            }
        }

        // Keep user as active (can be changed to pending if KYC approval is required)
        $user->status = 'active';
        if (!$user->save()) {
            error_log("Warning: Failed to update user status to active for user ID: " . $user->id);
        } else {
            error_log("✓ User status set to active for user ID: " . $user->id);
        }

        // Auto-login after registration
        $this->signInUser($user);
        TrustedDeviceService::trust((int)$user->id); // email proven on this device (sign-up OTP / Google / Apple)
        error_log("✓ Session set - User ID: {$user->id}, Role: {$user->role}");

        // Send welcome / verification email and notify admin
        try {
            // Send email verification OTP
            \App\Services\VerificationService::sendEmailVerification((int)$user->id, (string)$user->email);

            // Send role-based welcome email
            \App\Services\NotificationService::send(
                (int)$user->id,
                'employer_welcome',
                'Welcome to ' . (getenv('PORTAL_NAME') ?: 'Jobsence'),
                'Welcome, ' . $data['full_name'] . '! Your employer account has been created. Please complete your KYC verification to start posting jobs.',
                ['employer_name' => $data['full_name'], 'company_name' => $companyName],
                null,
                ['email']
            );

            // Notify Admin about new employer registration
            $adminMail = \App\Helpers\AdminMail::to();
            \App\Services\MailService::sendEmail(
                $adminMail,
                'New Employer Registered: ' . $companyName,
                "<p>A new employer has registered on the platform:</p>
                 <ul>
                    <li><strong>Company:</strong> {$companyName}</li>
                    <li><strong>Contact Name:</strong> {$data['full_name']}</li>
                    <li><strong>Email:</strong> {$user->email}</li>
                    <li><strong>Mobile:</strong> {$data['phone']}</li>
                 </ul>"
            );
        } catch (\Throwable $e) {
            error_log('Failed to send notifications during employer registration: ' . $e->getMessage());
        }

        // Always return JSON with redirect info for JavaScript to handle
        $redirectUrl = '/employer/company-profile';
        
        error_log("✓ Registration complete - User ID: {$user->id}, Employer ID: {$employer->id}, Redirect: {$redirectUrl}");
        
        $response->json([
            'success' => true,
            'message' => 'Registration successful! Redirecting to dashboard...',
            'user_id' => $user->id,
            'employer_id' => $employer->id,
            'uploaded_documents' => $uploadedDocs,
            'redirect' => $redirectUrl
        ], 201);
    }

    public function registerCandidate(Request $request, Response $response): void
    {
        // Show registration form
        if ($request->getMethod() === 'GET') {
            // Ensure CSRF token exists
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $response->view('auth/register-candidate', [
                'title' => 'Candidate Registration'
            ]);
            return;
        }

        // Handle registration POST - support both JSON and form data
        $contentType = $request->header('Content-Type') ?? '';
        $isJsonBody = strpos($contentType, 'application/json') !== false;
        $isAjax = $request->isAjax() || $isJsonBody;
        $data = [];
        
        try {
            $data = $isJsonBody ? $request->getJsonBody() : $request->all();
            $data['email'] = strtolower(trim((string)($data['email'] ?? '')));
            $phoneDigits = preg_replace('/\D+/', '', (string)($data['mobile'] ?? ''));
            $normalizedPhone = AuthService::normalizePhoneNumber($phoneDigits);
            $prevalidateOnly = !empty($data['prevalidate_only']);
            
            // Validation
            $errors = $this->validate($data, [
                'full_name' => 'required',
                'mobile' => 'required',
                'email' => 'required|email',
                'password' => 'required|password_strong|min:8|max:20',
            ]);

            $confirmPassword = (string)($data['password_confirm'] ?? $data['confirm_password'] ?? '');
            if ($confirmPassword === '' || (string)($data['password'] ?? '') !== $confirmPassword) {
                $errors['confirm_password'] = ['Passwords do not match'];
            }

            if (!preg_match('/^[0-9]{10}$/', (string)$phoneDigits) || $normalizedPhone === '') {
                $errors['mobile'] = ['Mobile Number must be exactly 10 digits'];
            }

            if (empty($errors['email']) && $this->findExistingRegistrationEmail($data['email'])) {
                $errors['email'] = ['Email already registered'];
            }

            if (empty($errors['mobile']) && $this->findExistingRegistrationPhone($normalizedPhone)) {
                $errors['mobile'] = ['Mobile number already registered'];
            }

            $resumeErrors = $this->validateCandidateResume($request);
            if (!empty($resumeErrors)) {
                $errors['resume'] = $resumeErrors;
            }

            if (!empty($errors)) {
                if ($isAjax) {
                    $response->json(['errors' => $errors], 422);
                } else {
                    $response->view('auth/register-candidate', [
                        'title' => 'Candidate Registration',
                        'errors' => $errors,
                        'old' => $data
                    ]);
                }
                return;
            }

            if ($prevalidateOnly) {
                $response->json(['success' => true, 'message' => 'Candidate details are valid']);
                return;
            }

            $resumeFile = $request->file('resume');

            $emailVerification = VerificationService::verifyEmailAuthOTP(
                (string)($data['email'] ?? ''),
                (string)($data['email_otp'] ?? ''),
                'register_candidate'
            );
            if (empty($emailVerification['success'])) {
                if ($isAjax) {
                    $response->json(['error' => $emailVerification['error'] ?? 'Invalid or expired email OTP'], 422);
                } else {
                    $response->view('auth/register-candidate', [
                        'title' => 'Candidate Registration',
                        'error' => $emailVerification['error'] ?? 'Invalid or expired email OTP',
                        'old' => $data
                    ]);
                }
                return;
            }

            // Create user
            $user = new User();
            $user->fill([
                'name' => $data['full_name'] ?? null,
                'email' => $data['email'],
                'role' => 'candidate',
                'status' => 'active', // Candidates can be active immediately
                'phone' => $normalizedPhone,
                'is_email_verified' => 1
            ]);
            $user->setPassword($data['password']);

            if (!$user->save()) {
                if ($isAjax) {
                    $response->json(['error' => 'Registration failed'], 500);
                } else {
                    $response->view('auth/register-candidate', [
                        'title' => 'Candidate Registration',
                        'error' => 'Registration failed. Please try again.',
                        'old' => $data
                    ]);
                }
                return;
            }

            // Handle Resume Upload
            $storage = Storage::disk('local');
            $resumePath = $storage->store($resumeFile, 'uploads/resumes');
            $resumeUrl = $storage->url($resumePath);

            // Create candidate profile
            $candidate = new Candidate();
            $candidate->fill([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->phone,
                'resume_url' => $resumeUrl,
                'profile_status' => 'active',
                'visibility' => 'public',
                'is_profile_complete' => 1
            ]);
            $candidate->save();

            // Save Resume File record
            $resumeFileModel = new ResumeFile();
            $resumeFileModel->fill([
                'candidate_id' => $candidate->id,
                'filename' => $resumeFile['name'],
                'filepath' => $resumePath,
                'hash' => sha1_file(Storage::disk('local')->path($resumePath)),
                'status' => 'uploaded',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $resumeFileModel->save();

            // Send welcome / verification email and notify admin
            try {
                // Send role-based welcome email
                \App\Services\NotificationService::send(
                    (int)$user->id,
                    'candidate_welcome',
                    'Welcome to ' . (getenv('PORTAL_NAME') ?: 'Jobsence'),
                    'Welcome, ' . $data['full_name'] . '! Thanks for joining Jobsence. We\'ve received your resume and we\'re excited to help you find your next career opportunity.',
                    ['candidate_name' => $data['full_name']],
                    null,
                    ['email']
                );

                // Notify Admin about new candidate registration
                $adminMail = \App\Helpers\AdminMail::to();
                \App\Services\MailService::sendEmail(
                    $adminMail,
                    'New Candidate Registered: ' . $data['full_name'],
                    "<p>A new candidate has registered on the platform with a resume:</p>
                     <ul>
                        <li><strong>Name:</strong> {$data['full_name']}</li>
                        <li><strong>Email:</strong> {$user->email}</li>
                        <li><strong>Mobile:</strong> {$data['mobile']}</li>
                        <li><strong>Resume:</strong> <a href=\"{$resumeUrl}\">View Resume</a></li>
                     </ul>"
                );
            } catch (\Throwable $e) {
                error_log('Failed to send notifications during candidate registration: ' . $e->getMessage());
            }
            
            try {
                $matchService = new \App\Services\JobMatchService();
                $matchService->findMatchingJobsForCandidateAndNotifyEmployers($candidate);
                $matchService->findMatchingJobsForCandidateAndNotifyCandidate($candidate);
            } catch (\Throwable $t) {}

            // Auto-login the newly registered candidate
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['candidate_id'] = (int)$candidate->id;
            TrustedDeviceService::trust((int)$user->id); // email proven on this device (sign-up OTP / Google / Apple)
            
            $authService = new \App\Services\AuthService();
            $jwtToken = $authService->generateToken($user);
            $jwtCookieEnabled = ($_ENV['WEB_JWT_COOKIE'] ?? '1') === '1';
            if ($jwtCookieEnabled) {
                $authService->setTokenCookie($jwtToken);
            }

            // Redirect to dashboard (since profile is now "complete" enough with resume)
            $redirectUrl = '/candidate/dashboard';

            if ($isAjax) {
                $response->json([
                    'success' => true,
                    'message' => 'Registration successful! Your resume has been uploaded.',
                    'user_id' => $user->id,
                    'redirect' => $redirectUrl,
                    'token' => $jwtToken
                ], 201);
            } else {
                $response->redirect($redirectUrl);
            }
        } catch (\Exception $e) {
            error_log("Candidate registration exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if ($isAjax) {
                $response->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
            } else {
                $response->view('auth/register-candidate', [
                    'title' => 'Candidate Registration',
                    'error' => 'Registration failed. Please try again.',
                    'old' => $data ?? []
                ]);
            }
        }
    }

    /**
     * One company = one Email + Mobile + GST: returns a bilingual error if GST is missing without
     * "I don't have GST", or if any identifier already belongs to an employer / registered mentor.
     */
    private function providerIdentityError(string $email, string $phone, string $gstin, $noGst): ?string
    {
        $noGst = in_array($noGst, [true, 1, '1', 'true', 'on'], true);
        if ($gstin === '' && !$noGst) {
            return 'GST नंबर भरें या “मेरे पास GST नहीं है” चुनें / Enter GSTIN or tick “I don’t have GST”';
        }

        $conflicts = \App\Services\Registration\ProviderIdentity::conflicts($email, $phone, $noGst ? '' : $gstin);
        if (!$conflicts) {
            return null;
        }
        return implode(' • ', array_map(static fn($m) => $m[0] . ' / ' . $m[1], $conflicts));
    }

    private function findExistingRegistrationEmail(string $email): ?User
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        $authRepository = new AuthRepository();
        $row = $authRepository->findUserByAnyEmail($email);
        return $row ? new User($row) : null;
    }

    private function findExistingRegistrationPhone(string $normalizedPhone): ?User
    {
        $authService = new AuthService();
        $user = $authService->findUserByPhone($normalizedPhone);
        if ($user) {
            return $user;
        }

        $digits = preg_replace('/\D+/', '', $normalizedPhone) ?: '';
        $lastTen = strlen($digits) >= 10 ? substr($digits, -10) : $digits;
        if (!preg_match('/^[0-9]{10}$/', $lastTen)) {
            return null;
        }

        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT * FROM users
             WHERE RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), 10) = :phone
             LIMIT 1",
            ['phone' => $lastTen]
        );

        return $row ? new User($row) : null;
    }

    private function validateCandidateResume(Request $request): array
    {
        if (!$request->hasFile('resume')) {
            return ['Please upload your resume'];
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

    public function login(Request $request, Response $response): void
    {
        // Check if already logged in
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
             if ($_SESSION['user_role'] === 'candidate') {
                 $response->redirect('/candidate/dashboard');
                 return;
             } elseif ($_SESSION['user_role'] === 'employer') {
                 $response->redirect('/employer/dashboard');
                 return;
             } elseif ($_SESSION['user_role'] === 'admin') {
                 $response->redirect('/admin/dashboard');
                 return;
             } elseif ($_SESSION['user_role'] === 'sales_manager') {
                 $response->redirect('/sales/manager/dashboard');
                 return;
             } elseif ($_SESSION['user_role'] === 'sales_executive') {
                 $response->redirect('/sales/executive/dashboard');
                 return;
             }
        }

        // Show login form
        if ($request->getMethod() === 'GET') {
            $redirect = $request->get('redirect');
            \App\Middlewares\CsrfMiddleware::generateToken();
            // Separate logins on one page: /login/job-seeker, /login/employer, /login/mentor, /login/senior (or ?role= / ?as=).
            $path = rtrim((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
            $as = (string)($request->get('as') ?? $request->get('role') ?? '');
            $tab = match (true) {
                str_ends_with($path, '/login/employer'), $as === 'employer' => 'employer',
                str_ends_with($path, '/login/mentor'), in_array($as, ['mentor', 'provider'], true) => 'mentor',
                str_ends_with($path, '/login/senior'), $as === 'senior' => 'senior',
                default => 'candidate',
            };
            $response->view('auth/login', [
                'title' => 'Login',
                'redirect' => $redirect,
                'tab' => $tab,
            ]);
            return;
        }

        // Handle login POST - support both JSON and form data
        $contentType = $request->header('Content-Type') ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $data = $request->getMethod() === 'POST' 
            ? ($isJson ? $request->getJsonBody() : $request->all())
            : [];
        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $emailOtp = trim((string)($data['email_otp'] ?? ''));

        $redis = RedisClient::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $rateKey = 'login_attempt:' . strtolower($email) . ':' . $ip;
        $attempts = 0;
        try {
            $val = $redis->isAvailable() ? $redis->get($rateKey) : null;
            $attempts = is_array($val) ? (int)($val['count'] ?? 0) : (int)($val ?? 0);
        } catch (\Throwable $t) {}
        if ($attempts >= 10) { // Increased from 5 since we might have multi-step check
            $response->json(['error' => 'Too many attempts. Please try again later.'], 429);
            return;
        }

        /** @var \App\Models\User|null $existingUser */
        $authRepository = new AuthRepository();
        $userRow = $authRepository->findUserByAnyEmail($email);

        if (!$userRow) {
            $this->handleLoginFailure($request, $response, $redis, $rateKey, $attempts, 'User not registered. Please create an account first.', 404);
            return;
        }

        /** @var \App\Models\User|null $user */
        $user = new User($userRow);

        // Handle login failure if user not authenticated
        if (!$user->verifyPassword($password)) {
            $this->handleLoginFailure($request, $response, $redis, $rateKey, $attempts, 'Invalid email or password', 401);
            return;
        }

        try {
            if ($redis->isAvailable()) {
                $redis->set($rateKey, 0, 300);
            }
        } catch (\Throwable $t) {}

        if ($wrong = $this->wrongLoginTab($user, (string)($data['as'] ?? ''))) {
            $response->json(['success' => false, 'status' => 'wrong_tab', 'tab' => $wrong[1], 'error' => $wrong[0]], 409);
            return;
        }

        if ($user->status !== 'active') {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') !== false || $isJson) {
                $response->json(['error' => 'Account not active. Please wait for approval.'], 403);
            } else {
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Account not active. Please wait for approval.',
                    'redirect' => $request->get('redirect')
                ]);
            }
            return;
        }

        // AuthService already updates last login via API path; this is safe redundancy for web.
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }

        // Normalize role to canonical to satisfy middleware checks
        $rawRole = (string)($user->role ?? '');
        if (str_starts_with($rawRole, 'social_') || str_starts_with($rawRole, 'social-')) {
            $canon = $rawRole;
            if ($rawRole === 'social_candidate' || $rawRole === 'social-candidate') $canon = 'candidate';
            if ($rawRole === 'social_employer' || $rawRole === 'social-employer') $canon = 'employer';
            
            if ($canon !== $rawRole) {
                $user->role = $canon;
                try { $user->save(); } catch (\Throwable $t) {}
            }
        }

        $primaryRole = $user->role;
        try {
            $roles = $user->roles();
            $slugs = array_map(fn($r) => strtolower((string)($r['slug'] ?? '')), $roles);
            if (in_array('super_admin', $slugs, true)) {
                $primaryRole = 'super_admin';
            } elseif (in_array('admin', $slugs, true)) {
                $primaryRole = 'admin';
            } elseif (in_array('sales_manager', $slugs, true)) {
                $primaryRole = 'sales_manager';
            } elseif (in_array('sales_executive', $slugs, true)) {
                $primaryRole = 'sales_executive';
            }
        } catch (\Throwable $t) {}

        // Session for web
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $primaryRole;

        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            if (!$candidate) {
                $candidate = \App\Models\Candidate::createForUser($user->id, ['full_name' => $this->extractNameFromEmail($user->attributes['email'] ?? '')]);
            }
            if ($candidate && isset($candidate->attributes['id'])) {
                $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
            }
        } elseif ($user->role === 'employer' || $user->role === 'social_employer') {
            $employer = \App\Models\Employer::findByUserId($user->id);
            if ($employer && isset($employer->id)) {
                $_SESSION['employer_id'] = (int)$employer->id;
            }
        }

        // Generate optional JWT for web session clients (hydrate cookie for Ajax + mobile-web hybrid)
        $authService = new AuthService();
        $jwtToken = $authService->generateToken($user);
        $jwtCookieEnabled = ($_ENV['WEB_JWT_COOKIE'] ?? '1') === '1';
        if ($jwtCookieEnabled) {
            $authService->setTokenCookie($jwtToken);
        }

        // Attach token to JSON response in API mode
        if ($isJson) {
            if (filter_var($request->post('remember') ?? false, FILTER_VALIDATE_BOOLEAN) && !$this->isStaffUser($user)) {
                TrustedDeviceService::trust((int)$user->id);
            }
            $_SESSION['has_mobile'] = trim((string)($user->phone ?? '')) !== '';
            $to = $this->afterLoginUrl($user, (string)($request->get('redirect') ?? ($request->post('redirect') ?? '')));
            $response->json([ 'success' => true, 'message' => 'Login successful', 'token' => $jwtToken, 'user' => $user->toArray(), 'redirect' => $to, 'redirect_to' => $to ]);
            return;
        }
        try { CookieService::linkAnonymousConsent((int)$user->id, (string)($user->email ?? ''), session_id(), $_COOKIE['anon_id'] ?? null); } catch (\Throwable $e) {}
        error_log("✓ Login successful - User ID: {$user->id}, Role: {$user->role}");

        // Determine redirect URL
        $redirect = $this->afterLoginUrl($user, (string)($request->get('redirect') ?? ''));

        $acceptHeader = $request->header('Accept') ?? '';
        $isJsonRequest = strpos($acceptHeader, 'application/json') !== false || $isJson;

        if ($isJsonRequest) {
            $response->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $jwtToken,
                'user' => $user->toArray(),
                'redirect_to' => $redirect
            ]);
        } else {
            $response->redirect($redirect);
        }
    }

    /**
     * Handle login failure with rate limiting and response formatting
     */
    private function handleLoginFailure(Request $request, Response $response, $redis, string $rateKey, int $attempts, string $message, int $statusCode): void
    {
        $contentType = $request->header('Content-Type') ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $acceptHeader = $request->header('Accept') ?? '';
        $isJsonRequest = strpos($acceptHeader, 'application/json') !== false || $isJson;

        if ($isJsonRequest) {
            $response->json(['error' => $message], $statusCode);
        } else {
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => $message,
                'redirect' => $request->get('redirect')
            ]);
        }

        try {
            if ($redis && $redis->isAvailable()) {
                $redis->set($rateKey, ['count' => $attempts + 1], 300);
            }
        } catch (\Throwable $t) {}
    }

    public function googleLogin(Request $request, Response $response): void
    {
        try {
            // Load config
            $configPath = __DIR__ . '/../../../config/google.php';
            $configPath = realpath($configPath);
            if (!$configPath || !file_exists($configPath)) {
                throw new \Exception('Google OAuth configuration file not found');
            }
            
            $config = require $configPath;
            
            if (empty($config['client_id']) || empty($config['client_secret'])) {
                throw new \Exception('Google OAuth is not configured');
            }
            
            // Store redirect URL in session for after login
            $redirect = $request->get('redirect');
            if ($redirect && $this->isValidRedirectUrl($redirect)) {
                $_SESSION['oauth_redirect'] = $redirect;
            }
            
            // Generate state token for CSRF protection
            $state = bin2hex(random_bytes(32));
            $_SESSION['oauth_state'] = $state;
            $_SESSION['oauth_provider'] = 'google';
            $_SESSION['oauth_state_time'] = time(); // Prevent replay attacks
            
            // Initialize Google OAuth service
            $googleService = new GoogleOAuthService($config);
            
            // Get authorization URL
            $authUrl = $googleService->getAuthUrl($state);
            
            // Redirect to Google
            $response->redirect($authUrl);
            
        } catch (\Exception $e) {
            error_log("Google login error: " . $e->getMessage());
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => 'Google OAuth is not available. Please contact administrator.'
            ]);
        }
    }
    
    public function googleCallback(Request $request, Response $response): void
    {
        try {
            // Load config
            $configPath = __DIR__ . '/../../../config/google.php';
            $configPath = realpath($configPath);
            if (!$configPath || !file_exists($configPath)) {
                throw new \Exception('Google OAuth configuration file not found');
            }
            
            $config = require $configPath;
            $code = $request->get('code');
            $state = $request->get('state');
            $error = $request->get('error');
            
            // Check for errors from Google
            if (!empty($error)) {
                error_log("Google OAuth error: " . $error);
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Google login was cancelled or failed. Please try again.'
                ]);
                return;
            }
            
            // Verify state token (CSRF protection)
            if (empty($state) || !isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
                error_log("Invalid OAuth state token");
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Security verification failed. Please try again.'
                ]);
                return;
            }
            
            // Check state token expiry (prevent replay attacks)
            if (isset($_SESSION['oauth_state_time']) && (time() - $_SESSION['oauth_state_time']) > 600) {
                error_log("OAuth state token expired");
                unset($_SESSION['oauth_state'], $_SESSION['oauth_state_time'], $_SESSION['oauth_provider']);
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Login session expired. Please try again.'
                ]);
                return;
            }
            
            // Verify provider
            if (($_SESSION['oauth_provider'] ?? '') !== 'google') {
                error_log("OAuth provider mismatch");
                $this->clearOAuthSession();
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Invalid login session. Please try again.'
                ]);
                return;
            }
            
            // Clean up state
            $this->clearOAuthSession();
            
            if (empty($code)) {
                throw new \Exception('Authorization code not provided');
            }
            
            // Initialize Google OAuth service
            $googleService = new GoogleOAuthService($config);
            
            // Exchange code for access token
            $token = $googleService->fetchAccessTokenWithCode($code);
            
            // Get user info from Google
            $googleUser = $googleService->getUserInfo($token);
            
            // Validate user data
            if (empty($googleUser['id']) || empty($googleUser['email'])) {
                throw new \Exception('Invalid user information from Google');
            }
            
            // Validate email format
            if (!$googleService->validateEmail($googleUser['email'])) {
                throw new \Exception('Invalid email address from Google');
            }
            
            // Find or create user
            $user = $this->findOrCreateOAuthUser('google', $googleUser);
            
            if (!$user) {
                throw new \Exception('Failed to create or find user account');
            }
            
            $this->finishOAuthLogin($user, $response);
            
        } catch (\Exception $e) {
            error_log("Google callback error: " . $e->getMessage());
            $this->clearOAuthSession();
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => 'Failed to authenticate with Google. Please try again.'
            ]);
        }
    }
    
    /** Session, trusted device and role-aware redirect after any social login (Google, Facebook, LinkedIn). */
    private function finishOAuthLogin(User $user, Response $response): void
    {
        // Update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
        
        // Set session (new session id at login – no fixation)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        TrustedDeviceService::trust((int)$user->id); // email proven on this device (sign-up OTP / Google / Facebook / LinkedIn)
        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            if (!$candidate) {
                $candidate = \App\Models\Candidate::createForUser($user->id);
            }
            if ($candidate && isset($candidate->attributes['id'])) {
                $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
            }
        }
        try { CookieService::linkAnonymousConsent((int)$user->id, (string)($user->email ?? ''), session_id(), $_COOKIE['anon_id'] ?? null); } catch (\Throwable $e) {}
        
        // Determine redirect URL
        $redirect = $_SESSION['oauth_redirect'] ?? null;
        unset($_SESSION['oauth_redirect']);
        
        // Validate redirect against user role
        if ($redirect) {
            if ($user->role === 'employer' && strpos($redirect, '/candidate/') === 0) {
                $redirect = '/employer/dashboard';
            } elseif ($user->role === 'candidate' && strpos($redirect, '/employer/') === 0) {
                $redirect = '/candidate/dashboard';
            }
        }
        
        if (!$redirect) {
            $redirect = $this->getDefaultRedirectUrl($user);
        }
        
        // Mobile number is mandatory for everyone: social accounts without one add it straight after login.
        if (trim((string)($user->phone ?? '')) === '') {
            $redirect = '/account/mobile?next=' . rawurlencode($redirect);
        }
        $response->redirect($redirect);
    }

    /** GET /auth/{facebook|linkedin} – start a social login (only when the provider is configured). */
    public function socialLogin(Request $request, Response $response): void
    {
        $provider = self::socialProvider();
        if (!$provider || !\App\Services\SocialOAuthService::enabled($provider)) {
            $response->redirect('/login?message=' . rawurlencode('This login option is not available yet – use Google or your mobile / email.'));
            return;
        }
        $redirect = $request->get('redirect');
        if ($redirect && $this->isValidRedirectUrl($redirect)) {
            $_SESSION['oauth_redirect'] = $redirect;
        }
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;
        $_SESSION['oauth_state_time'] = time();
        $response->redirect((new \App\Services\SocialOAuthService($provider))->authUrl($state));
    }

    /** GET /auth/{facebook|linkedin}/callback */
    public function socialCallback(Request $request, Response $response): void
    {
        $provider = self::socialProvider();
        $label = ['facebook' => 'Facebook', 'linkedin' => 'LinkedIn'][$provider] ?? 'Social';
        $fail = function (string $msg) use ($response): void {
            $this->clearOAuthSession();
            $response->view('auth/login', ['title' => 'Login', 'error' => $msg]);
        };
        if (!$provider || !\App\Services\SocialOAuthService::enabled($provider)) {
            $fail('This login option is not available.');
            return;
        }
        if ($request->get('error')) {
            $fail($label . ' login was cancelled. Please try again.');
            return;
        }
        $state = (string)$request->get('state', '');
        if ($state === '' || !hash_equals((string)($_SESSION['oauth_state'] ?? ''), $state)
            || ($_SESSION['oauth_provider'] ?? '') !== $provider || time() - (int)($_SESSION['oauth_state_time'] ?? 0) > 600) {
            $fail('Security verification failed or the login took too long. Please try again.');
            return;
        }
        $this->clearOAuthSession();
        try {
            $info = (new \App\Services\SocialOAuthService($provider))->userFromCode((string)$request->get('code', ''));
        } catch (\Throwable $e) {
            error_log($label . ' callback error: ' . $e->getMessage());
            $fail('Failed to sign in with ' . $label . '. Please try again.');
            return;
        }
        if ($info['id'] === '' || !filter_var($info['email'], FILTER_VALIDATE_EMAIL)) {
            $fail('Your ' . $label . ' account did not share an email address. Please log in with Google or your mobile number / email instead.');
            return;
        }
        if (empty($info['verified_email'])) {
            $fail('Your ' . $label . ' email address is not verified. Please verify it with ' . $label . ', or log in with your mobile number / email.');
            return;
        }
        \App\Services\SocialOAuthService::ensureSchema();
        $user = $this->findOrCreateOAuthUser($provider, $info);
        if (!$user) {
            $fail('Failed to create or find your account. Please try again.');
            return;
        }
        $this->finishOAuthLogin($user, $response);
    }

    private static function socialProvider(): ?string
    {
        $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        return preg_match('#^/auth/(facebook|linkedin)(/callback)?/?$#', $path, $m) ? $m[1] : null;
    }

    public function appleLogin(Request $request, Response $response): void
    {
        try {
            // Load config
            $configPath = __DIR__ . '/../../../config/apple.php';
            $configPath = realpath($configPath);
            if (!$configPath || !file_exists($configPath)) {
                throw new \Exception('Apple OAuth configuration file not found');
            }
            
            $config = require $configPath;
            
            if (empty($config['client_id'])) {
                throw new \Exception('Apple OAuth is not configured');
            }
            
            // Store redirect URL in session for after login
            $redirect = $request->get('redirect');
            if ($redirect && $this->isValidRedirectUrl($redirect)) {
                $_SESSION['oauth_redirect'] = $redirect;
            }
            
            // Generate state token for CSRF protection
            $state = bin2hex(random_bytes(32));
            $_SESSION['oauth_state'] = $state;
            $_SESSION['oauth_provider'] = 'apple';
            $_SESSION['oauth_state_time'] = time(); // Prevent replay attacks
            
            // Initialize Apple OAuth service
            $appleService = new AppleOAuthService($config);
            
            // Get authorization URL
            $authUrl = $appleService->getAuthUrl($state);
            
            // Redirect to Apple
            $response->redirect($authUrl);
            
        } catch (\Exception $e) {
            error_log("Apple login error: " . $e->getMessage());
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => 'Apple OAuth is not available. Please contact administrator.'
            ]);
        }
    }
    
    public function appleCallback(Request $request, Response $response): void
    {
        try {
            // Load config
            $configPath = __DIR__ . '/../../../config/apple.php';
            $configPath = realpath($configPath);
            if (!$configPath || !file_exists($configPath)) {
                throw new \Exception('Apple OAuth configuration file not found');
            }
            
            $config = require $configPath;
            
            // Apple sends data via POST (form_post response_mode)
            $code = $_POST['code'] ?? $request->get('code');
            $state = $_POST['state'] ?? $request->get('state');
            $userDataJson = $_POST['user'] ?? null;
            $error = $_POST['error'] ?? $request->get('error');
            
            // Check for errors from Apple
            if (!empty($error)) {
                error_log("Apple OAuth error: " . $error);
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Apple login was cancelled or failed. Please try again.'
                ]);
                return;
            }
            
            // Verify state token (CSRF protection)
            if (empty($state) || !isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
                error_log("Invalid OAuth state token");
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Security verification failed. Please try again.'
                ]);
                return;
            }
            
            // Check state token expiry (prevent replay attacks)
            if (isset($_SESSION['oauth_state_time']) && (time() - $_SESSION['oauth_state_time']) > 600) {
                error_log("OAuth state token expired");
                $this->clearOAuthSession();
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Login session expired. Please try again.'
                ]);
                return;
            }
            
            // Verify provider
            if (($_SESSION['oauth_provider'] ?? '') !== 'apple') {
                error_log("OAuth provider mismatch");
                $this->clearOAuthSession();
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Invalid login session. Please try again.'
                ]);
                return;
            }
            
            // Clean up state
            $this->clearOAuthSession();
            
            if (empty($code)) {
                throw new \Exception('Authorization code not provided');
            }
            
            // Initialize Apple OAuth service
            $appleService = new AppleOAuthService($config);
            
            // Exchange code for tokens
            $tokenData = $appleService->exchangeCodeForToken($code);
            
            $idToken = $tokenData['id_token'] ?? null;
            if (!$idToken) {
                throw new \Exception('ID token not provided by Apple');
            }
            
            // Decode ID token to get user info
            $appleUser = $appleService->decodeIdToken($idToken);
            
            if (empty($appleUser['sub'])) {
                throw new \Exception('Invalid user information from Apple');
            }
            
            // Parse user data from POST (first time login only)
            $userData = $appleService->parseUserData($userDataJson);
            
            $appleEmail = $appleUser['email'] ?? $userData['email'] ?? null;
            $appleName = $userData['name'] ?? null;
            
            // Handle Apple email privacy (may be null after first login)
            if (!$appleEmail) {
                // Use Apple ID as email identifier
                $appleEmail = $appleUser['sub'] . '@privaterelay.appleid.com';
            }
            
            // Validate email format
            if (!$appleService->validateEmail($appleEmail) && strpos($appleEmail, '@privaterelay.appleid.com') === false) {
                throw new \Exception('Invalid email address from Apple');
            }
            
            // Find or create user
            $appleUserData = [
                'id' => $appleUser['sub'],
                'email' => $appleEmail,
                'name' => $appleName
            ];
            
            $user = $this->findOrCreateOAuthUser('apple', $appleUserData);
            
            if (!$user) {
                throw new \Exception('Failed to create or find user account');
            }
            
            // Update last login
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            
            // Set session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            TrustedDeviceService::trust((int)$user->id); // email proven on this device (sign-up OTP / Google / Apple)
            if ($user->role === 'candidate') {
                $candidate = \App\Models\Candidate::findByUserId($user->id);
                if (!$candidate) {
                    $candidate = \App\Models\Candidate::createForUser($user->id);
                }
                if ($candidate && isset($candidate->attributes['id'])) {
                    $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
                }
            }
            try { CookieService::linkAnonymousConsent((int)$user->id, (string)($user->email ?? ''), session_id(), $_COOKIE['anon_id'] ?? null); } catch (\Throwable $e) {}
            
            // Determine redirect URL
            $redirect = $_SESSION['oauth_redirect'] ?? null;
            unset($_SESSION['oauth_redirect']);
            
            // Validate redirect against user role
            if ($redirect) {
                if ($user->role === 'employer' && strpos($redirect, '/candidate/') === 0) {
                    $redirect = '/employer/dashboard';
                } elseif ($user->role === 'candidate' && strpos($redirect, '/employer/') === 0) {
                    $redirect = '/candidate/dashboard';
                }
            }
            
            if (!$redirect) {
                $redirect = $this->getDefaultRedirectUrl($user);
            }
            
            $response->redirect($redirect);
            
        } catch (\Exception $e) {
            error_log("Apple callback error: " . $e->getMessage());
            $this->clearOAuthSession();
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => 'Failed to authenticate with Apple. Please try again.'
            ]);
        }
    }
    
    /**
     * Clear OAuth session data
     */
    private function clearOAuthSession(): void
    {
        unset($_SESSION['oauth_state'], $_SESSION['oauth_state_time'], $_SESSION['oauth_provider']);
    }
    
    /**
     * Validate redirect URL to prevent open redirect attacks
     */
    private function isValidRedirectUrl(string $url): bool
    {
        // Only allow relative URLs
        if (empty($url) || $url[0] !== '/') {
            return false;
        }
        
        // Prevent redirect to login/logout loops
        if (strpos($url, '/logout') !== false) {
            return false;
        }
        
        // Allow common safe paths
        $allowedPaths = ['/employer/', '/candidate/', '/admin/', '/master/', '/sales-manager/', '/sales-executive/', '/dashboard', '/profile', '/'];
        foreach ($allowedPaths as $path) {
            if (strpos($url, $path) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get default redirect URL based on user role
     */
    private function getDefaultRedirectUrl(User $user): string
    {
        if ($user->role === 'employer') {
            return '/employer/dashboard';
        } elseif ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            if (!$candidate) {
                $candidate = \App\Models\Candidate::createForUser($user->id);
            }
            if (!$candidate->isProfileComplete()) {
                return '/candidate/profile/complete';
            } else {
                return '/candidate/dashboard';
            }
        } else {
            return '/';
        }
    }
    
    /**
     * Find or create user from OAuth provider data
     */
    private function findOrCreateOAuthUser(string $provider, array $userData): ?User
    {
        if (empty($userData['id']) || empty($userData['email'])) {
            return null;
        }
        
        $providerIdField = $provider . '_id';
        $providerEmailField = $provider . '_email';
        $providerNameField = $provider . '_name';
        
        // First, try to find user by provider ID
        $user = User::where($providerIdField, '=', $userData['id'])->first();
        
        if ($user) {
            // Update provider info
            $updateData = [
                $providerEmailField => $userData['email'],
            ];
            if (!empty($userData['name'])) {
                $updateData[$providerNameField] = $userData['name'];
            }
            if ($provider === 'google' && !empty($userData['picture'])) {
                $updateData['google_picture'] = $userData['picture'];
            }
            
            $user->fill($updateData);
            $user->save();
            
            // Update candidate profile with OAuth data if candidate
            if ($user->role === 'candidate') {
                $this->populateCandidateFromOAuth($user, $userData, $provider);
            }
            
            return $user;
        }
        
        // Try to find by email (account linking)
        $user = User::where('email', '=', $userData['email'])->first();
        
        if ($user) {
            // Link provider account to existing user
            $linkData = [
                $providerIdField => $userData['id'],
                $providerEmailField => $userData['email'],
            ];
            if (!empty($userData['name'])) {
                $linkData[$providerNameField] = $userData['name'];
            }
            if ($provider === 'google' && !empty($userData['picture'])) {
                $linkData['google_picture'] = $userData['picture'];
            }
            if (in_array($provider, ['google', 'facebook', 'linkedin'], true) && !empty($userData['verified_email'])) {
                $linkData['is_email_verified'] = 1;
            } elseif ($provider === 'apple' && strpos($userData['email'], '@privaterelay.appleid.com') === false) {
                $linkData['is_email_verified'] = 1;
            }
            
            // CRITICAL FIX: Ensure user has a role if missing
            if (empty($user->role)) {
                $linkData['role'] = 'candidate';
            }
            
            $user->fill($linkData);
            $user->save();
            
            // Update candidate profile with OAuth data if candidate
            if ($user->role === 'candidate') {
                $this->populateCandidateFromOAuth($user, $userData, $provider);
            }
            
            return $user;
        }
        
        // Create new user
        $user = new User();
        $newUserData = [
            'email' => $userData['email'],
            $providerIdField => $userData['id'],
            $providerEmailField => $userData['email'],
            'role' => 'candidate',
            'status' => 'active',
        ];
        
        if (!empty($userData['name'])) {
            $newUserData[$providerNameField] = $userData['name'];
        }
        
        if ($provider === 'google' && !empty($userData['picture'])) {
            $newUserData['google_picture'] = $userData['picture'];
        }
        
        // Set email verification status
        if (in_array($provider, ['google', 'facebook', 'linkedin'], true) && !empty($userData['verified_email'])) {
            $newUserData['is_email_verified'] = 1;
        } elseif ($provider === 'apple' && strpos($userData['email'], '@privaterelay.appleid.com') === false) {
            $newUserData['is_email_verified'] = 1;
        } else {
            $newUserData['is_email_verified'] = 0;
        }
        
        $user->fill($newUserData);
        
        // Set a random password (user won't need it for OAuth login)
        $user->setPassword(bin2hex(random_bytes(32)));
        
        if ($user->save()) {
            // Auto-populate candidate profile with OAuth data
            if ($user->role === 'candidate') {
                $this->populateCandidateFromOAuth($user, $userData, $provider);
            }
            return $user;
        }
        
        return null;
    }
    
    /**
     * Extract name from email address
     * Example: "tagsindia1997@gmail.com" -> "Tags India"
     */
    private function extractNameFromEmail(string $email): ?string
    {
        if (empty($email)) {
            return null;
        }
        
        // Get the part before @
        $localPart = explode('@', $email)[0] ?? '';
        if (empty($localPart)) {
            return null;
        }
        
        // Remove numbers
        $namePart = preg_replace('/\d+/', '', $localPart);
        
        // Split by common separators (., _, -)
        $parts = preg_split('/[._-]+/', $namePart);
        
        // Filter out empty parts
        $parts = array_filter($parts, fn($p) => !empty(trim($p)));
        
        if (empty($parts)) {
            // If no separators, try to split camelCase or all lowercase
            // For "tagsindia" -> "Tags India"
            $namePart = preg_replace('/([a-z])([A-Z])/', '$1 $2', $namePart);
            $parts = [trim($namePart)];
        }
        
        // Capitalize each word
        $nameParts = array_map(function($part) {
            $part = trim($part);
            if (empty($part)) return '';
            // Capitalize first letter, lowercase rest
            return ucfirst(strtolower($part));
        }, $parts);
        
        $nameParts = array_filter($nameParts, fn($p) => !empty($p));
        
        if (empty($nameParts)) {
            return null;
        }
        
        // Join with spaces
        $fullName = implode(' ', $nameParts);
        
        // If result is too short or just numbers, return null
        if (strlen($fullName) < 2) {
            return null;
        }
        
        return $fullName;
    }
    
    /**
     * Populate candidate profile with OAuth data (name, email, picture)
     */
    private function populateCandidateFromOAuth(User $user, array $userData, string $provider): void
    {
        try {
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            
            if (!$candidate) {
                $candidate = \App\Models\Candidate::createForUser($user->id);
            }
            
            $updateData = [];
            
            // Set full name from OAuth
            if (empty($candidate->attributes['full_name']) && !empty($userData['name'])) {
                $updateData['full_name'] = $userData['name'];
            }
            
            // Set profile picture from Google
            if ($provider === 'google' && empty($candidate->attributes['profile_picture']) && !empty($userData['picture'])) {
                $updateData['profile_picture'] = $userData['picture'];
            }
            
            // Update candidate if we have data
            if (!empty($updateData)) {
                $candidate->fill($updateData);
                $candidate->save();
                $candidate->updateProfileStrength();
            }
        } catch (\Exception $e) {
            error_log("Error populating candidate from OAuth: " . $e->getMessage());
        }
    }

    public function logout(Request $request, Response $response): void
    {
        // Clear session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Check if it's an AJAX/JSON request
        $acceptHeader = $request->header('Accept') ?? '';
        if (strpos($acceptHeader, 'application/json') !== false) {
            $response->json(['message' => 'Logged out successfully']);
        } else {
            // Redirect to login page for browser requests
            $response->redirect('/login?message=' . urlencode('You have been logged out successfully'));
        }
    }

    public function forgotPassword(Request $request, Response $response): void
    {
        $authFlowService = new AuthFlowService();
        // Show forgot password form
        if ($request->getMethod() === 'GET') {
            $isAdminPath = (strpos($request->getUri(), '/admin/') === 0);
            $view = $isAdminPath ? 'admin/auth/forgot-password' : 'auth/forgot-password';
            $response->view($view, [
                'title' => 'Forgot Password'
            ]);
            return;
        }

        // Handle forgot password POST
        $data = $request->getJsonBody() ?? $request->all();
        $email = trim((string)($data['email'] ?? ''));

        if (empty($email)) {
            $response->json(['error' => 'Email is required'], 422);
            return;
        }

        $authRepository = new AuthRepository();
        $userRow = $authRepository->findUserByAnyEmail($email);
        $user = $userRow ? new User($userRow) : null;
        $userEmail = $email;
        
        error_log("Forgot Password - Request for email: {$email}");
        if (!$user) {
            error_log("Forgot Password - User not found for email: {$email}");
        } else {
            $userEmail = $authFlowService->resolveDeliverableEmail($user, $email);
            error_log("Forgot Password - User found. ID: {$user->id}, Role: {$user->role}, Email in DB: {$userEmail}");
        }

        // Restrict password reset for assigned sales roles
        if ($user) {
            try {
                $roleSlugs = $authRepository->getRoleSlugsByUserId((int)$user->id);
                if ($authFlowService->hasBlockedSalesRole($roleSlugs)) {
                    $response->json(['error' => 'Password reset is disabled for assigned sales roles'], 403);
                    return;
                }
            } catch (\Throwable $t) {
                // Fall through to normal flow on query failure
            }
        }

        // Always return success message (security best practice - don't reveal if email exists)
        $resetLink = null;

        if ($user) {
            if ($userEmail === '') {
                error_log("Forgot Password - No deliverable email found for user ID: {$user->id}");
                $response->json([
                    'success' => true,
                    'message' => 'If an account exists with that email, a password reset link has been sent.'
                ]);
                return;
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            // Use UTC timezone consistently
            $expiresAt = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token - try Redis first, then database fallback
            $redis = RedisClient::getInstance();
            $stored = false;
            
            if ($redis->isAvailable()) {
                // Store in Redis
                $tokenData = [
                    'user_id' => $user->id,
                    'email' => $userEmail,
                    'expires_at' => $expiresAt
                ];
                $stored = $redis->set("password_reset:{$token}", $tokenData, 3600); // 1 hour expiry
            }
            
            // Always store in database as reliable fallback
            try {
                error_log("Forgot Password - Storing token in database for user_id: {$user->id}, token: " . substr($token, 0, 20) . "...");
                $authRepository->upsertPasswordResetToken((int)$user->id, $userEmail, $token, $expiresAt);
                $stored = true;
                error_log("Forgot Password - Token stored successfully in database. Expires at: {$expiresAt}");
            } catch (\Exception $e) {
                error_log("Failed to store password reset token in database: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());
            }
            
            if (!$stored) {
                error_log("Password reset token for user {$user->id}: {$token} (not stored - Redis and DB both failed)");
            } else {
                error_log("Forgot Password - Token storage successful. Redis: " . ($redis->isAvailable() ? 'yes' : 'no') . ", Database: yes");
            }

            // Build absolute reset link and send email
            $resetLink = $authFlowService->buildResetLink($request, $token);

            // Send password reset email
            $emailSent = \App\Services\MailService::sendPasswordReset($userEmail, $resetLink);
            if ($emailSent) {
                error_log("Forgot Password - Password reset email sent successfully to: {$userEmail}");
            } else {
                error_log("Forgot Password - Failed to send password reset email to: {$userEmail}");
                error_log("Forgot Password - Reset link: {$resetLink}");
            }
        }

        // Always return success (don't reveal if email exists)
        $responseData = [
            'success' => true,
            'message' => 'If an account exists with that email, a password reset link has been sent.'
        ];
        $isLocal = $authFlowService->shouldExposeResetLinkForLocal();
        if ($isLocal && $resetLink) {
            $responseData['reset_link'] = $resetLink;
        }
        
        $response->json($responseData);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $authRepository = new AuthRepository();
        $authFlowService = new AuthFlowService();
        $token = $request->get('token') ?? '';
        
        // Show reset password form
        if ($request->getMethod() === 'GET') {
            if (empty($token)) {
                $isAdminPath = (strpos($request->getUri(), '/admin/') === 0);
                $view = $isAdminPath ? 'admin/auth/reset-password' : 'auth/reset-password';
                $response->view($view, [
                    'title' => 'Reset Password',
                    'error' => 'Invalid or missing reset token',
                    'token' => ''
                ]);
                return;
            }

            // Verify token - check Redis first, then database
            $tokenData = null;
            $redis = RedisClient::getInstance();
            
            error_log("Reset Password - Checking token: " . substr($token, 0, 20) . "...");
            error_log("Reset Password - Redis available: " . ($redis->isAvailable() ? 'yes' : 'no'));
            
            if ($redis->isAvailable()) {
                $tokenData = $redis->get("password_reset:{$token}");
                error_log("Reset Password - Redis lookup result: " . ($tokenData ? 'found' : 'not found'));
            }
            
            // If not in Redis, check database
            if (!$tokenData) {
                try {
                    error_log("Reset Password - Checking database for token...");
                    $result = $authRepository->getValidPasswordResetTokenData($token);
                    
                    error_log("Reset Password - Database query result: " . ($result ? 'found' : 'not found'));
                    if ($result) {
                        error_log("Reset Password - Token data: user_id={$result['user_id']}, email={$result['email']}, expires_at={$result['expires_at']}");
                        $tokenData = $authFlowService->normalizeTokenData($result);
                    } else {
                        // Check if token exists but expired
                        $expiredCheck = $authRepository->getPasswordResetTokenData($token);
                        if ($expiredCheck) {
                            $currentUtc = gmdate('Y-m-d H:i:s');
                            error_log("Reset Password - Token found but expired. Expires at: {$expiredCheck['expires_at']}, Current UTC time: {$currentUtc}");
                        } else {
                            error_log("Reset Password - Token not found in database at all");
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Error checking password reset token in database: " . $e->getMessage());
                    error_log("Error trace: " . $e->getTraceAsString());
                }
            }

            if (!$tokenData) {
                error_log("Reset Password - Token validation failed. Token: " . substr($token, 0, 20) . "...");
                $isAdminPath = (strpos($request->getUri(), '/admin/') === 0);
                $view = $isAdminPath ? 'admin/auth/reset-password' : 'auth/reset-password';
                $response->view($view, [
                    'title' => 'Reset Password',
                    'error' => 'Invalid or expired reset token',
                    'token' => ''
                ]);
                return;
            }
            
            error_log("Reset Password - Token validated successfully for user_id: {$tokenData['user_id']}");

            $isAdminPath = (strpos($request->getUri(), '/admin/') === 0);
            $view = $isAdminPath ? 'admin/auth/reset-password' : 'auth/reset-password';
            $response->view($view, [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => ''
            ]);
            return;
        }

        // Handle reset password POST
        $data = $request->getJsonBody() ?? $request->all();
        $token = $data['token'] ?? $request->get('token') ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';

        if (empty($token)) {
            $response->json(['error' => 'Reset token is required'], 422);
            return;
        }

        if (empty($password) || strlen($password) < 8) {
            $response->json(['error' => 'Password must be at least 8 characters'], 422);
            return;
        }

        if ($password !== $passwordConfirm) {
            $response->json(['error' => 'Passwords do not match'], 422);
            return;
        }

        // Verify token - check Redis first, then database
        $tokenData = null;
        $redis = RedisClient::getInstance();
        
        if ($redis->isAvailable()) {
            $tokenData = $redis->get("password_reset:{$token}");
        }
        
        // If not in Redis, check database
        if (!$tokenData) {
            try {
                $result = $authRepository->getValidPasswordResetTokenData($token);
                
                if ($result) {
                    $tokenData = $authFlowService->normalizeTokenData($result);
                }
            } catch (\Exception $e) {
                error_log("Error checking password reset token in database: " . $e->getMessage());
            }
        }

        if (!$tokenData) {
            $response->json(['error' => 'Invalid or expired reset token'], 400);
            return;
        }

        // Update user password
        /** @var \App\Models\User|null $user */
        $user = User::find($tokenData['user_id']);
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        /** @var \App\Models\User $user */
        $user->setPassword($password);
        if ($user->save()) {
            // Delete token from both Redis and database
            if ($redis->isAvailable()) {
                $redis->delete("password_reset:{$token}");
            }
            
            // Delete from database
            try {
                $authRepository->deletePasswordResetToken($token);
            } catch (\Exception $e) {
                error_log("Error deleting password reset token: " . $e->getMessage());
            }

            $response->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ]);
        } else {
            $response->json(['error' => 'Failed to update password'], 500);
        }
    }

    public function verifyAccount(Request $request, Response $response): void
    {
        $token = $request->get('token');
        if (!$token) {
            $response->redirect('/login?error=missing_token');
            return;
        }

        $user = User::where('verification_token', '=', $token)->first();
        if (!$user) {
            $response->redirect('/login?error=invalid_token');
            return;
        }

        if (strtotime($user->verification_expires_at) < time()) {
            $response->redirect('/login?error=expired_token');
            return;
        }

        // Pass user details for welcome message
        $response->view('auth/verify-account', [
            'token' => $token,
            'email' => $user->email,
            'title' => 'Complete Your Account'
        ]);
    }

    public function processVerification(Request $request, Response $response): void
    {
        $authRepository = new AuthRepository();
        $data = $request->all();
        
        // Basic validation
        if (empty($data['token']) || empty($data['password']) || empty($data['password_confirmation'])) {
             $response->view('auth/verify-account', [
                'token' => $data['token'] ?? '',
                'email' => '',
                'error' => 'All fields are required',
                'title' => 'Complete Your Account'
            ]);
            return;
        }

        if (empty($data['terms'])) {
            $response->view('auth/verify-account', [
                'token' => $data['token'],
                'email' => '',
                'error' => 'You must accept the Terms of Service and Privacy Policy',
                'title' => 'Complete Your Account'
            ]);
            return;
        }

        if ($data['password'] !== $data['password_confirmation']) {
            $response->view('auth/verify-account', [
                'token' => $data['token'],
                'email' => '',
                'error' => 'Passwords do not match',
                'title' => 'Complete Your Account'
            ]);
            return;
        }
        
        if (strlen($data['password']) < 8) {
            $response->view('auth/verify-account', [
                'token' => $data['token'],
                'email' => '',
                'error' => 'Password must be at least 8 characters',
                'title' => 'Complete Your Account'
            ]);
            return;
        }

        /** @var \App\Models\User|null $user */
        $user = User::where('verification_token', '=', $data['token'])->first();
        if (!$user) {
            $response->redirect('/login?error=invalid_token');
            return;
        }
        
        if (strtotime($user->verification_expires_at) < time()) {
            $response->redirect('/login?error=expired_token');
            return;
        }

        try {
            // Update user
            $user->setPassword($data['password']);
            $user->is_email_verified = 1;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->verification_token = null; // Clear token
            $user->verification_expires_at = null;
            $user->save();
            
            // Also update candidate profile status if applicable
            if ($user->role === 'candidate') {
                 $authRepository->activateCandidateProfileByUserId((int)$user->id);
            }

            $response->redirect('/login?success=account_verified');
        } catch (\Exception $e) {
             $response->view('auth/verify-account', [
                'token' => $data['token'],
                'email' => $user->email,
                'error' => 'System error: ' . $e->getMessage(),
                'title' => 'Complete Your Account'
            ]);
        }
    }

    public function sendPhoneOtp(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'error' => 'Mobile OTP login/registration is coming soon. Please use Email OTP.'
        ], 503);
    }

    public function sendEmailOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody() ?? $request->all();
        $email = trim((string)($data['email'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));
        $role = trim((string)($data['role'] ?? ''));

        if ($email === '') {
            $response->json(['error' => 'Email is required'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json(['error' => 'Please enter a valid email address'], 422);
            return;
        }

        if (strtolower($purpose) === 'register_candidate') {
            if ($this->findExistingRegistrationEmail($email)) {
                $response->json(['error' => 'Email already registered'], 409);
                return;
            }

            $phoneDigits = preg_replace('/\D+/', '', (string)($data['mobile'] ?? $data['phone'] ?? ''));
            if ($phoneDigits !== '') {
                $normalizedPhone = AuthService::normalizePhoneNumber($phoneDigits);
                if (!preg_match('/^[0-9]{10}$/', (string)$phoneDigits) || $normalizedPhone === '') {
                    $response->json(['error' => 'Mobile Number must be exactly 10 digits'], 422);
                    return;
                }
                if ($this->findExistingRegistrationPhone($normalizedPhone)) {
                    $response->json(['error' => 'Mobile number already registered'], 409);
                    return;
                }
            }
        } elseif (strtolower($purpose) === 'register_employer') {
            if ($this->findExistingRegistrationEmail($email)) {
                $response->json(['error' => 'यह Email पहले से रजिस्टर्ड है / Email already registered'], 409);
                return;
            }
            if (array_key_exists('gstin', $data) || array_key_exists('no_gst', $data)) {
                $providerError = $this->providerIdentityError($email, (string)($data['phone'] ?? $data['mobile'] ?? ''), strtoupper(trim((string)($data['gstin'] ?? ''))), $data['no_gst'] ?? null);
                if ($providerError !== null) {
                    $response->json(['error' => $providerError], 409);
                    return;
                }
            }

            $phoneDigits = preg_replace('/\D+/', '', (string)($data['phone'] ?? $data['mobile'] ?? ''));
            if ($phoneDigits !== '') {
                $normalizedPhone = AuthService::normalizePhoneNumber($phoneDigits);
                if (!preg_match('/^[0-9]{10}$/', (string)$phoneDigits) || $normalizedPhone === '') {
                    $response->json(['error' => 'Mobile Number must be exactly 10 digits'], 422);
                    return;
                }
                if ($this->findExistingRegistrationPhone($normalizedPhone)) {
                    $response->json(['error' => 'Mobile number already registered'], 409);
                    return;
                }
            }
        }

        $result = VerificationService::sendEmailAuthOTP($email, $purpose, ['role' => $role]);
        if (empty($result['success'])) {
            $response->json(['error' => $result['error'] ?? 'Failed to send OTP'], 500);
            return;
        }

        $payload = [
            'success' => true,
            'message' => 'OTP sent to your email',
            'email' => $result['email'] ?? $email,
            'purpose' => $result['purpose'] ?? $purpose,
        ];
        $response->json($payload);
    }

    public function loginWithPhoneOtp(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'error' => 'Mobile OTP login is coming soon. Please use Email OTP login.'
        ], 503);
    }

    public function registerCandidateWithPhoneOtp(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'error' => 'Mobile OTP candidate registration is coming soon. Please use Email OTP registration.'
        ], 503);
    }

    public function registerEmployerWithPhoneOtp(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'error' => 'Mobile OTP employer registration is coming soon. Please use Email OTP registration.'
        ], 503);
    }

    private function signInUser(User $user): void
    {
        if (function_exists('session_regenerate_id') && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user->id;
        $primaryRole = $user->role;

        try {
            $roles = $user->roles();
            $slugs = array_map(fn($r) => strtolower((string)($r['slug'] ?? '')), $roles);
            if (in_array('super_admin', $slugs, true)) {
                $primaryRole = 'super_admin';
            } elseif (in_array('admin', $slugs, true)) {
                $primaryRole = 'admin';
            } elseif (in_array('sales_manager', $slugs, true)) {
                $primaryRole = 'sales_manager';
            } elseif (in_array('sales_executive', $slugs, true)) {
                $primaryRole = 'sales_executive';
            }
        } catch (\Throwable $t) {
        }

        $_SESSION['user_role'] = $primaryRole;

        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId((int)$user->id);
            if (!$candidate) {
                $service = new \App\Services\CandidateCreationService();
                $candidate = $service->ensureCandidateForUser((int)$user->id, [
                    'profile_status' => 'unverified',
                    'visibility' => 'limited',
                    'is_profile_complete' => 0,
                    'created_by' => 'self',
                    'source' => 'website'
                ]);
            }
            if ($candidate && isset($candidate->attributes['id'])) {
                $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
            }
        } elseif ($user->role === 'employer' || $user->role === 'social_employer') {
            $employer = \App\Models\Employer::findByUserId((int)$user->id);
            if ($employer && isset($employer->id)) {
                $_SESSION['employer_id'] = (int)$employer->id;
            }
        }

        try {
            CookieService::linkAnonymousConsent((int)$user->id, (string)($user->email ?? ''), session_id(), $_COOKIE['anon_id'] ?? null);
        } catch (\Throwable $e) {
        }
        if (($_ENV['WEB_JWT_COOKIE'] ?? '1') === '1') {
            try {
                $auth = new AuthService();
                $auth->setTokenCookie($auth->generateToken($user));
            } catch (\Throwable $e) {
            }
        }
        $_SESSION['has_mobile'] = trim((string)($user->phone ?? '')) !== '';
    }

    // ------------------------------------------------------------------
    // Smooth login: mobile number or email → (trusted device ? in : email OTP)
    // ------------------------------------------------------------------

    private const LOGIN_OTP_PURPOSE = 'login_device';

    /** Account for "mobile number or email". */
    private function findLoginUser(string $identifier): ?User
    {
        $identifier = trim($identifier);
        $auth = new AuthService();
        if (str_contains($identifier, '@')) {
            return filter_var($identifier, FILTER_VALIDATE_EMAIL) ? $auth->findUserByAnyEmail($identifier) : null;
        }
        $digits = preg_replace('/\D/', '', $identifier);
        return strlen((string)$digits) >= 10 ? $auth->findUserByPhone($identifier) : null;
    }

    /**
     * Separate Job Seeker / Employer logins: an account of the other kind gets a clear message and the
     * tab to use instead. Staff accounts and requests without 'as' (mobile app, old links) pass.
     * @return array{0:string,1:string}|null [message, tab]
     */
    private function wrongLoginTab(User $user, string $as): ?array
    {
        if (!in_array($as, ['candidate', 'employer'], true) || $this->isStaffUser($user)) {
            return null;
        }
        $role = (string)$user->role;
        if ($role === $as || !in_array($role, ['candidate', 'employer'], true)) {
            return null;
        }
        return $role === 'employer'
            ? ['यह एम्प्लॉयर खाता है – “एम्प्लॉयर लॉगिन” से लॉगिन करें / This is an employer account – please use Employer Login', 'employer']
            : ['यह जॉब सीकर खाता है – “जॉब सीकर लॉगिन” से लॉगिन करें / This is a job seeker account – please use Job Seeker Login', 'candidate'];
    }

    /** Admin / sales staff always confirm with an email OTP – never a remembered device. */
    private function isStaffUser(User $user): bool
    {
        if (in_array((string)$user->role, ['admin', 'super_admin', 'sales_manager', 'sales_executive'], true)) {
            return true;
        }
        try {
            foreach ($user->roles() as $r) {
                if (in_array(strtolower((string)($r['slug'] ?? '')), ['admin', 'super_admin', 'sales_manager', 'sales_executive'], true)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
        }
        return false;
    }

    private function safeRedirect(?string $url): ?string
    {
        $url = (string)$url;
        return ($url !== '' && $url[0] === '/' && !str_starts_with($url, '//') && !str_starts_with($url, '/\\') && $this->isValidRedirectUrl($url)) ? $url : null;
    }

    /** Where to go after login: add the mobile number first if the account has none. */
    private function afterLoginUrl(User $user, ?string $redirect): string
    {
        $to = $this->safeRedirect($redirect) ?? $this->resolveRedirectForUser($user);
        return trim((string)($user->phone ?? '')) === '' ? '/account/mobile?next=' . rawurlencode($to) : $to;
    }

    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($local, 0, 2) . str_repeat('•', max(1, min(6, mb_strlen($local) - 2))) . '@' . $domain;
    }

    /**
     * POST /login/identify {identifier, redirect}
     * → {status: 'logged_in', redirect} on a remembered device, {status: 'pin_required'} when the account has a
     * login PIN (unless method=otp), {status: 'otp_sent', email} or {status: 'not_found'}.
     */
    public function loginIdentify(Request $request, Response $response): void
    {
        $identifier = trim((string)($request->post('identifier') ?? ''));
        if ($identifier === '') {
            $response->json(['success' => false, 'error' => 'मोबाइल नंबर या ईमेल भरें / Enter your mobile number or email'], 422);
            return;
        }
        $user = $this->findLoginUser($identifier);
        if (!$user) {
            $response->json(['success' => false, 'status' => 'not_found', 'error' => 'इस मोबाइल / ईमेल से कोई खाता नहीं मिला / No account found with this mobile number or email'], 404);
            return;
        }
        if (($user->status ?? '') !== 'active') {
            $response->json(['success' => false, 'error' => 'यह खाता सक्रिय नहीं है – gm@jobsence.com पर लिखें / This account is not active – write to gm@jobsence.com'], 403);
            return;
        }
        $redirect = (string)($request->post('redirect') ?? '');
        if ($wrong = $this->wrongLoginTab($user, (string)($request->post('as') ?? ''))) {
            $response->json(['success' => false, 'status' => 'wrong_tab', 'tab' => $wrong[1], 'error' => $wrong[0]], 409);
            return;
        }

        if (!$this->isStaffUser($user) && TrustedDeviceService::isTrusted((int)$user->id)) {
            $this->signInUser($user);
            TrustedDeviceService::trust((int)$user->id);
            $response->json(['success' => true, 'status' => 'logged_in', 'redirect' => $this->afterLoginUrl($user, $redirect)]);
            return;
        }

        // Optional login PIN: ask for it instead of an email OTP (the OTP stays one click away).
        if ($request->post('method') !== 'otp' && !$this->isStaffUser($user) && LoginPinService::has((int)$user->id) && !LoginPinService::isLocked((int)$user->id)) {
            $_SESSION['login_pin_user'] = (int)$user->id;
            $response->json(['success' => true, 'status' => 'pin_required']);
            return;
        }

        $email = strtolower(trim((string)($user->email ?? '')));
        if ($email === '' || str_ends_with($email, '@mobile.local')) {
            $response->json(['success' => false, 'error' => 'इस खाते में ईमेल नहीं है – पासवर्ड से लॉगिन करें या gm@jobsence.com पर लिखें / This account has no email – use your password or write to gm@jobsence.com'], 422);
            return;
        }
        $last = (int)($_SESSION['login_otp_sent'][(int)$user->id] ?? 0);
        if ($last > time() - 30) {
            $response->json(['success' => true, 'status' => 'otp_sent', 'email' => self::maskEmail($email), 'wait' => 30 - (time() - $last)]);
            return;
        }
        $sent = VerificationService::sendEmailAuthOTP($email, self::LOGIN_OTP_PURPOSE);
        if (empty($sent['success'])) {
            $response->json(['success' => false, 'error' => (string)($sent['error'] ?? 'OTP नहीं भेजा जा सका / Could not send the OTP')], !empty($sent['blocked']) ? 429 : 500);
            return;
        }
        $_SESSION['login_otp_sent'][(int)$user->id] = time();
        $_SESSION['login_otp_user'] = (int)$user->id;
        $response->json(['success' => true, 'status' => 'otp_sent', 'email' => self::maskEmail($email), 'wait' => 30]);
    }

    /** POST /login/verify {identifier, otp, remember, redirect} – email OTP → signed in (+ remember this device). */
    public function loginVerify(Request $request, Response $response): void
    {
        $user = $this->findLoginUser((string)($request->post('identifier') ?? ''));
        if (!$user || (int)($_SESSION['login_otp_user'] ?? 0) !== (int)$user->id) {
            $response->json(['success' => false, 'error' => 'दोबारा शुरू करें / Please start again'], 422);
            return;
        }
        if (($user->status ?? '') !== 'active') {
            $response->json(['success' => false, 'error' => 'यह खाता सक्रिय नहीं है / This account is not active'], 403);
            return;
        }
        $check = VerificationService::verifyEmailAuthOTP(strtolower(trim((string)$user->email)), trim((string)($request->post('otp') ?? '')), self::LOGIN_OTP_PURPOSE);
        if (empty($check['success'])) {
            $response->json(['success' => false, 'error' => (string)($check['error'] ?? 'OTP सही नहीं है / Incorrect OTP')], !empty($check['blocked']) ? 429 : 422);
            return;
        }
        unset($_SESSION['login_otp_user'], $_SESSION['login_otp_sent'][(int)$user->id]);
        $this->signInUser($user);
        $remember = filter_var($request->post('remember') ?? true, FILTER_VALIDATE_BOOLEAN);
        if ($remember && !$this->isStaffUser($user)) {
            TrustedDeviceService::trust((int)$user->id);
        }
        try {
            \App\Core\Database::getInstance()->execute('UPDATE users SET is_email_verified = 1 WHERE id = ?', [(int)$user->id]);
        } catch (\Throwable $e) {
        }
        $response->json(['success' => true, 'status' => 'logged_in', 'redirect' => $this->afterLoginUrl($user, (string)($request->post('redirect') ?? ''))]);
    }

    /** POST /login/pin {identifier, pin, remember, redirect} – login PIN → signed in (+ remember this device). */
    public function loginPin(Request $request, Response $response): void
    {
        $user = $this->findLoginUser((string)($request->post('identifier') ?? ''));
        if (!$user || (int)($_SESSION['login_pin_user'] ?? 0) !== (int)$user->id || $this->isStaffUser($user)) {
            $response->json(['success' => false, 'error' => 'दोबारा शुरू करें / Please start again'], 422);
            return;
        }
        if (($user->status ?? '') !== 'active') {
            $response->json(['success' => false, 'error' => 'यह खाता सक्रिय नहीं है / This account is not active'], 403);
            return;
        }
        $check = LoginPinService::verify((int)$user->id, trim((string)($request->post('pin') ?? '')));
        if (empty($check['ok'])) {
            if (!empty($check['locked'])) {
                unset($_SESSION['login_pin_user']);
                $response->json(['success' => false, 'status' => 'pin_locked',
                    'error' => 'बहुत बार गलत PIN – PIN ' . LoginPinService::LOCK_MINUTES . ' मिनट के लिए बंद है। ईमेल OTP से लॉगिन करें। / Too many wrong PINs – PIN locked for ' . LoginPinService::LOCK_MINUTES . ' minutes. Log in with the email OTP.'], 429);
                return;
            }
            $response->json(['success' => false, 'error' => 'PIN सही नहीं है – ' . $check['left'] . ' प्रयास बाकी / Incorrect PIN – ' . $check['left'] . ' tries left'], 422);
            return;
        }
        unset($_SESSION['login_pin_user']);
        $this->signInUser($user);
        if (filter_var($request->post('remember') ?? true, FILTER_VALIDATE_BOOLEAN)) {
            TrustedDeviceService::trust((int)$user->id);
        }
        $response->json(['success' => true, 'status' => 'logged_in', 'redirect' => $this->afterLoginUrl($user, (string)($request->post('redirect') ?? ''))]);
    }

    // ------------------------------------------------------------------
    // Mobile number is mandatory for every account
    // ------------------------------------------------------------------

    private function currentUser(): ?User
    {
        $id = (int)($_SESSION['user_id'] ?? 0);
        return $id > 0 ? User::find($id) : null;
    }

    /** GET /account/mobile – one-time "add your mobile number" (no SMS; OTPs go by email). */
    public function mobileForm(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login?redirect=' . rawurlencode('/account/mobile'));
            return;
        }
        $next = $this->safeRedirect((string)$request->get('next', '')) ?? $this->resolveRedirectForUser($user);
        if (trim((string)($user->phone ?? '')) !== '' && !$request->get('change')) {
            $response->redirect($next);
            return;
        }
        $response->view('auth/mobile', ['next' => $next, 'error' => null, 'phone' => '', 'title' => 'Add your mobile number – Jobsence'], 200, 'layout');
    }

    /** POST /account/mobile */
    public function mobileSave(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login');
            return;
        }
        $next = $this->safeRedirect((string)$request->post('next', '')) ?? $this->resolveRedirectForUser($user);
        $digits = preg_replace('/\D/', '', (string)$request->post('mobile', ''));
        $digits = strlen($digits) === 12 && str_starts_with($digits, '91') ? substr($digits, 2) : $digits;
        $error = null;
        if (!preg_match('/^[6-9]\d{9}$/', (string)$digits)) {
            $error = 'सही 10 अंकों का मोबाइल नंबर भरें / Enter a valid 10-digit mobile number';
        } elseif ((new AuthService())->findUserByPhone($digits, (int)$user->id)) {
            $error = 'यह मोबाइल नंबर किसी दूसरे खाते में है / This mobile number belongs to another account';
        }
        if ($error) {
            $response->view('auth/mobile', ['next' => $next, 'error' => $error, 'phone' => $digits, 'title' => 'Add your mobile number – Jobsence'], 422, 'layout');
            return;
        }
        $phone = AuthService::normalizePhoneNumber($digits);
        $db = \App\Core\Database::getInstance();
        $db->execute('UPDATE users SET phone = ?, is_phone_verified = 0 WHERE id = ?', [$phone, (int)$user->id]);
        if ($user->role === 'candidate') {
            $db->execute("UPDATE candidates SET mobile = ? WHERE user_id = ? AND (mobile IS NULL OR mobile = '')", [$phone, (int)$user->id]);
        }
        $_SESSION['has_mobile'] = true;
        $response->redirect($next);
    }

    /** GET /account/security – remembered devices + forget all. */
    public function security(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login?redirect=' . rawurlencode('/account/security'));
            return;
        }
        $response->view('auth/security', [
            'user' => $user,
            'devices' => TrustedDeviceService::listFor((int)$user->id),
            'hasPin' => LoginPinService::has((int)$user->id),
            'pinAllowed' => !$this->isStaffUser($user),
            'pinError' => $this->takePinError(),
            'flash' => $this->takeSecurityFlash(),
            'title' => 'Login & devices – Jobsence',
        ], 200, 'layout');
    }

    /** POST /account/devices/forget {id?} – forget one device, or all when no id. */
    public function forgetDevices(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login');
            return;
        }
        $id = (int)$request->post('id', 0);
        if ($id > 0) {
            TrustedDeviceService::revoke((int)$user->id, $id);
            $_SESSION['security_flash'] = 'डिवाइस हटाया गया / Device removed';
        } else {
            TrustedDeviceService::revokeAll((int)$user->id);
            $_SESSION['security_flash'] = 'सभी डिवाइस भूले गए – अगली बार हर डिवाइस पर ईमेल OTP लगेगा / All devices forgotten – every device needs an email OTP next time';
        }
        $response->redirect('/account/security');
    }

    /** POST /account/pin {pin, pin_confirm} – set or change the optional login PIN. */
    public function savePin(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login?redirect=' . rawurlencode('/account/security'));
            return;
        }
        if ($this->isStaffUser($user)) {
            $_SESSION['pin_error'] = 'स्टाफ़ खाते हमेशा ईमेल OTP से लॉगिन करते हैं / Staff accounts always log in with an email OTP';
            $response->redirect('/account/security');
            return;
        }
        $pin = trim((string)$request->post('pin', ''));
        $problem = LoginPinService::problem($pin);
        if ($problem === null && $pin !== trim((string)$request->post('pin_confirm', ''))) {
            $problem = ['दोनों PIN एक जैसे नहीं हैं', 'The two PINs do not match'];
        }
        if ($problem !== null) {
            $_SESSION['pin_error'] = $problem[0] . ' / ' . $problem[1];
            $response->redirect('/account/security#pin');
            return;
        }
        $had = LoginPinService::has((int)$user->id);
        LoginPinService::set((int)$user->id, $pin);
        $this->notifyPinChange($user, $had ? 'changed' : 'set');
        $_SESSION['security_flash'] = $had ? 'आपका लॉगिन PIN बदल दिया गया / Your login PIN was changed' : 'लॉगिन PIN सेट हो गया – अब मोबाइल / ईमेल + PIN से लॉगिन करें / Login PIN set – you can now log in with mobile / email + PIN';
        $response->redirect('/account/security');
    }

    /** POST /account/pin/remove – back to email OTP only. */
    public function removePin(Request $request, Response $response): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $response->redirect('/login');
            return;
        }
        if (LoginPinService::has((int)$user->id)) {
            LoginPinService::remove((int)$user->id);
            $this->notifyPinChange($user, 'removed');
        }
        $_SESSION['security_flash'] = 'लॉगिन PIN हटा दिया गया – अब ईमेल OTP से लॉगिन होगा / Login PIN removed – you will log in with the email OTP';
        $response->redirect('/account/security');
    }

    /** Security notice to the account email whenever the PIN is set, changed or removed. */
    private function notifyPinChange(User $user, string $what): void
    {
        $email = strtolower(trim((string)($user->email ?? '')));
        if ($email === '' || str_ends_with($email, '@mobile.local')) {
            return;
        }
        $hi = ['set' => 'सेट किया गया', 'changed' => 'बदला गया', 'removed' => 'हटाया गया'][$what] ?? $what;
        try {
            MailService::sendEmail(
                $email,
                'Jobsence: login PIN ' . $what,
                '<p>आपके Jobsence खाते का लॉगिन PIN ' . $hi . ' – ' . date('d M Y, h:i A') . '.</p>'
                . '<p>Your Jobsence login PIN was ' . $what . ' on ' . date('d M Y, h:i A') . '.</p>'
                . '<p>अगर यह आपने नहीं किया, तो तुरंत ईमेल OTP से लॉगिन करके <a href="https://jobsence.com/account/security">Login &amp; devices</a> में PIN हटाएँ और सभी डिवाइस भूल जाएँ, और gm@jobsence.com पर लिखें।<br>'
                . 'If this was not you, log in with the email OTP, remove the PIN and forget all devices at <a href="https://jobsence.com/account/security">Login &amp; devices</a>, and write to gm@jobsence.com.</p>'
            );
        } catch (\Throwable $e) {
            error_log('PIN notice mail: ' . $e->getMessage());
        }
    }

    private function takePinError(): ?string
    {
        $e = $_SESSION['pin_error'] ?? null;
        unset($_SESSION['pin_error']);
        return $e;
    }

    private function takeSecurityFlash(): ?string
    {
        $f = $_SESSION['security_flash'] ?? null;
        unset($_SESSION['security_flash']);
        return $f;
    }

    private function resolveRedirectForUser(User $user): string
    {
        if ($user->role === 'employer') {
            return '/employer/dashboard';
        }

        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId((int)$user->id);
            if (!$candidate) {
                return '/candidate/profile/complete';
            }

            $hasData = !empty($candidate->attributes['full_name']) ||
                !empty($candidate->attributes['mobile']) ||
                !empty($candidate->attributes['city']) ||
                !empty($candidate->attributes['dob']) ||
                !empty($candidate->attributes['gender']);

            return $hasData ? '/candidate/dashboard' : '/candidate/profile/complete';
        }

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return '/admin/dashboard';
        }

        return '/';
    }
}
