<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Employer;
use App\Models\EmployerSetting;
use App\Models\EmployerKycDocument;
use App\Core\RedisClient;
use App\Services\GoogleOAuthService;
use App\Services\AppleOAuthService;
use App\Services\MailService;

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
            'status' => 'pending'
        ]);
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
                'email' => 'required|email',
                'password' => 'required|password_strong|min:8|max:20',
                'company_name' => 'required',
                'phone' => 'required',
                'country' => 'required',
                'company_size' => 'required',
            ]);

            if (!empty($errors)) {
                $response->json(['errors' => $errors], 422);
                return;
            }

            // Check if user exists
            $existing = User::where('email', '=', $data['email'])->first();
            if ($existing) {
                $response->json(['error' => 'Email already registered'], 409);
                return;
            }

            // Create user
            $user = new User();
            $user->fill([
                'email' => $data['email'],
                'role' => 'employer',
                'status' => 'pending',
                'phone' => ($data['country_code'] ?? '') . ($data['phone'] ?? '')
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
        $employer->fill([
            'user_id' => $user->id,
            'company_name' => $data['company_name'],
            'company_slug' => $employer->generateSlug($data['company_name']),
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'industry' => $data['industry'] ?? null,
            'size' => $data['company_size'],
            'address' => !empty($address) ? json_encode($address, JSON_UNESCAPED_UNICODE) : null,
            'country' => $data['country'],
            'state' => $address['state'] ?? null,
            'city' => $address['city'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'kyc_status' => 'pending'
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
                        // Ensure upload directory exists
                        $uploadDir = __DIR__ . '/../../storage/uploads/kyc/' . $employer->id;
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $filePath = $storage->store($file, 'kyc/' . $employer->id);
                        
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
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        error_log("✓ Session set - User ID: {$user->id}, Role: {$user->role}");

        // Always return JSON with redirect info for JavaScript to handle
        $redirectUrl = count($uploadedDocs) >= 3 ? '/employer/dashboard' : '/employer/kyc';
        
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
        $isJson = strpos($contentType, 'application/json') !== false;
        $data = [];
        
        try {
            $data = $isJson ? $request->getJsonBody() : $request->all();
            
            // Email-only registration for candidates with international standard validation
            $errors = $this->validate($data, [
                'email' => 'required|email',
                'password' => 'required|password_strong|min:8|max:20',
            ]);

            if (!empty($errors)) {
                if ($isJson) {
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

            // Check if user exists
            $existing = User::where('email', '=', $data['email'])->first();
            if ($existing) {
                if ($isJson) {
                    $response->json(['error' => 'Email already registered'], 409);
                } else {
                    $response->view('auth/register-candidate', [
                        'title' => 'Candidate Registration',
                        'error' => 'Email already registered',
                        'old' => $data
                    ]);
                }
                return;
            }

            // Create user
            $user = new User();
            $user->fill([
                'email' => $data['email'],
                'role' => 'candidate',
                'status' => 'active', // Candidates can be active immediately
            ]);
            $user->setPassword($data['password']);

            if (!$user->save()) {
                if ($isJson) {
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

            // Create candidate profile with registration data
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

            // Don't auto-login - redirect to login page with success message
            $redirectUrl = '/login?registered=1&email=' . urlencode($user->email);

            if ($isJson) {
                $response->json([
                    'success' => true,
                    'message' => 'Registration successful! Please check your email for confirmation. You will be redirected to login page.',
                    'user_id' => $user->id,
                    'redirect' => $redirectUrl
                ], 201);
            } else {
                $response->redirect($redirectUrl);
            }
        } catch (\Exception $e) {
            error_log("Candidate registration exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if ($isJson) {
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
            $response->view('auth/login', [
                'title' => 'Login',
                'redirect' => $redirect
            ]);
            return;
        }

        // Handle login POST - support both JSON and form data
        $contentType = $request->header('Content-Type') ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $data = $request->getMethod() === 'POST' 
            ? ($isJson ? $request->getJsonBody() : $request->all())
            : [];
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('email', '=', $email)->first();
        /** @var \App\Models\User|null $user */
        if (!$user || !$user->verifyPassword($password)) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') !== false || $isJson) {
                $response->json(['error' => 'Invalid credentials'], 401);
            } else {
                $response->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Invalid email or password',
                    'redirect' => $request->get('redirect')
                ]);
            }
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

        // Update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        // Set session
        $_SESSION['user_id'] = $user->id;
        // Determine primary role from RBAC (fallback to legacy role column)
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
        $_SESSION['user_role'] = $primaryRole;
        if ($user->role === 'candidate') {
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            if (!$candidate) {
                $candidate = \App\Models\Candidate::createForUser($user->id);
            }
            if ($candidate && isset($candidate->attributes['id'])) {
                $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
            }
        }
        error_log("✓ Login successful - User ID: {$user->id}, Role: {$user->role}");

        $acceptHeader = $request->header('Accept') ?? '';
        $isJsonRequest = strpos($acceptHeader, 'application/json') !== false || $isJson;
        
        // Determine redirect URL
        $redirect = $request->get('redirect');
        if (!$redirect && $user->role === 'employer') {
            $redirect = '/employer/dashboard';
        } elseif (!$redirect && $user->role === 'candidate') {
            // Check candidate profile status
            $candidate = \App\Models\Candidate::findByUserId($user->id);
            if (!$candidate) {
                // Extract name from email if available
                $nameFromEmail = $this->extractNameFromEmail($user->attributes['email'] ?? '');
                $initialData = [];
                if ($nameFromEmail) {
                    $initialData['full_name'] = $nameFromEmail;
                }
                // Create profile if doesn't exist with name from email
                $candidate = \App\Models\Candidate::createForUser($user->id, $initialData);
            } else {
                // If candidate exists but no name, try to extract from email
                if (empty($candidate->attributes['full_name'])) {
                    $nameFromEmail = $this->extractNameFromEmail($user->attributes['email'] ?? '');
                    if ($nameFromEmail) {
                        $candidate->fill(['full_name' => $nameFromEmail]);
                        $candidate->save();
                        $candidate->updateProfileStrength();
                    }
                }
            }
            
            // Recalculate profile strength to ensure it's accurate
            if ($candidate) {
                $candidate->updateProfileStrength();
            }
            
            // Check if profile has substantial data (not just empty profile)
            $hasData = false;
            if ($candidate && isset($candidate->attributes)) {
                $hasData = !empty($candidate->attributes['full_name']) || 
                          !empty($candidate->attributes['mobile']) || 
                          !empty($candidate->attributes['city']) ||
                          !empty($candidate->attributes['dob']) ||
                          !empty($candidate->attributes['gender']);
            }
            
            // Only redirect to complete page if profile is truly empty
            // If profile has data (even if not 100% complete), go to dashboard
            if (!$hasData) {
                $redirect = '/candidate/profile/complete';
            } else {
                $redirect = '/candidate/dashboard';
            }
        } elseif (!$redirect && $primaryRole === 'sales_manager') {
            $redirect = '/sales-manager/dashboard';
        } elseif (!$redirect && $primaryRole === 'sales_executive') {
            $redirect = '/sales-executive/dashboard';
        } elseif (!$redirect) {
            $redirect = '/';
        }
        
        if ($isJsonRequest) {
            $response->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user->toArray(),
                'redirect_to' => $redirect
            ]);
        } else {
            $response->redirect($redirect);
        }
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
            
            // Update last login
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            
            // Set session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            if ($user->role === 'candidate') {
                $candidate = \App\Models\Candidate::findByUserId($user->id);
                if (!$candidate) {
                    $candidate = \App\Models\Candidate::createForUser($user->id);
                }
                if ($candidate && isset($candidate->attributes['id'])) {
                    $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
                }
            }
            
            // Determine redirect URL
            $redirect = $_SESSION['oauth_redirect'] ?? null;
            unset($_SESSION['oauth_redirect']);
            
            if (!$redirect) {
                $redirect = $this->getDefaultRedirectUrl($user);
            }
            
            $response->redirect($redirect);
            
        } catch (\Exception $e) {
            error_log("Google callback error: " . $e->getMessage());
            $this->clearOAuthSession();
            $response->view('auth/login', [
                'title' => 'Login',
                'error' => 'Failed to authenticate with Google. Please try again.'
            ]);
        }
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
            if ($user->role === 'candidate') {
                $candidate = \App\Models\Candidate::findByUserId($user->id);
                if (!$candidate) {
                    $candidate = \App\Models\Candidate::createForUser($user->id);
                }
                if ($candidate && isset($candidate->attributes['id'])) {
                    $_SESSION['candidate_id'] = (int)$candidate->attributes['id'];
                }
            }
            
            // Determine redirect URL
            $redirect = $_SESSION['oauth_redirect'] ?? null;
            unset($_SESSION['oauth_redirect']);
            
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
            if ($provider === 'google' && !empty($userData['verified_email'])) {
                $linkData['is_email_verified'] = 1;
            } elseif ($provider === 'apple' && strpos($userData['email'], '@privaterelay.appleid.com') === false) {
                $linkData['is_email_verified'] = 1;
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
        if ($provider === 'google' && !empty($userData['verified_email'])) {
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
        // Show forgot password form
        if ($request->getMethod() === 'GET') {
            $response->view('auth/forgot-password', [
                'title' => 'Forgot Password'
            ]);
            return;
        }

        // Handle forgot password POST
        $data = $request->getJsonBody() ?? $request->all();
        $email = $data['email'] ?? '';

        if (empty($email)) {
            $response->json(['error' => 'Email is required'], 422);
            return;
        }

        $user = User::where('email', '=', $email)->first();

        // Restrict password reset for assigned sales roles
        if ($user) {
            try {
                $db = \App\Core\Database::getInstance();
                $roles = $db->fetchAll(
                    'SELECT r.slug FROM roles r INNER JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = :uid',
                    ['uid' => (int)$user->id]
                );
                $blocked = ['sales_manager','sales_executive'];
                foreach ($roles as $r) {
                    if (in_array((string)($r['slug'] ?? ''), $blocked, true)) {
                        $response->json(['error' => 'Password reset is disabled for assigned sales roles'], 403);
                        return;
                    }
                }
            } catch (\Throwable $t) {
                // Fall through to normal flow on query failure
            }
        }

        // Always return success message (security best practice - don't reveal if email exists)
        $resetLink = null;

        if ($user) {
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
                    'email' => $user->email,
                    'expires_at' => $expiresAt
                ];
                $stored = $redis->set("password_reset:{$token}", $tokenData, 3600); // 1 hour expiry
            }
            
            // Always store in database as reliable fallback
            $db = \App\Core\Database::getInstance();
            try {
                // Delete any existing tokens for this user
                // Use UTC_TIMESTAMP() to match timezone
                $db->query("DELETE FROM password_resets WHERE user_id = :user_id OR expires_at < UTC_TIMESTAMP()", [
                    'user_id' => $user->id
                ]);
                
                // Insert new token
                error_log("Forgot Password - Storing token in database for user_id: {$user->id}, token: " . substr($token, 0, 20) . "...");
                $db->query(
                    "INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email, :token, :user_id, :expires_at)",
                    [
                        'email' => $user->email,
                        'token' => $token,
                        'user_id' => $user->id,
                        'expires_at' => $expiresAt
                    ]
                );
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
            $isAdminContext = str_starts_with($request->getPath(), '/admin/');
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path = ($isAdminContext ? '/admin/reset-password' : '/reset-password') . "?token={$token}";
            $resetLink = $scheme . '://' . $host . $path;

            // Send password reset email
            $emailSent = \App\Services\MailService::sendPasswordReset($user->email, $resetLink);
            if ($emailSent) {
                error_log("Forgot Password - Password reset email sent successfully to: {$user->email}");
            } else {
                error_log("Forgot Password - Failed to send password reset email to: {$user->email}");
                error_log("Forgot Password - Reset link: {$resetLink}");
            }
        }

        // Always return success (don't reveal if email exists)
        $responseData = [
            'success' => true,
            'message' => 'If an account exists with that email, a password reset link has been sent.'
        ];
        
        // Only include reset_link in development (remove in production!)
        if ($resetLink) {
            $responseData['reset_link'] = $resetLink;
        }
        
        $response->json($responseData);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $token = $request->get('token') ?? '';
        
        // Show reset password form
        if ($request->getMethod() === 'GET') {
            if (empty($token)) {
                $response->view('auth/reset-password', [
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
                $db = \App\Core\Database::getInstance();
                try {
                    error_log("Reset Password - Checking database for token...");
                    // Use UTC_TIMESTAMP() to match timezone with stored expires_at
                    $result = $db->fetchOne(
                        "SELECT user_id, email, expires_at FROM password_resets WHERE token = :token AND expires_at > UTC_TIMESTAMP()",
                        ['token' => $token]
                    );
                    
                    error_log("Reset Password - Database query result: " . ($result ? 'found' : 'not found'));
                    if ($result) {
                        error_log("Reset Password - Token data: user_id={$result['user_id']}, email={$result['email']}, expires_at={$result['expires_at']}");
                        $tokenData = [
                            'user_id' => $result['user_id'],
                            'email' => $result['email'],
                            'expires_at' => $result['expires_at']
                        ];
                    } else {
                        // Check if token exists but expired
                        $expiredCheck = $db->fetchOne(
                            "SELECT token, expires_at FROM password_resets WHERE token = :token",
                            ['token' => $token]
                        );
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
                $response->view('auth/reset-password', [
                    'title' => 'Reset Password',
                    'error' => 'Invalid or expired reset token',
                    'token' => ''
                ]);
                return;
            }
            
            error_log("Reset Password - Token validated successfully for user_id: {$tokenData['user_id']}");

            $response->view('auth/reset-password', [
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
            $db = \App\Core\Database::getInstance();
            try {
                $result = $db->fetchOne(
                    "SELECT user_id, email, expires_at FROM password_resets WHERE token = :token AND expires_at > UTC_TIMESTAMP()",
                    ['token' => $token]
                );
                
                if ($result) {
                    $tokenData = [
                        'user_id' => $result['user_id'],
                        'email' => $result['email'],
                        'expires_at' => $result['expires_at']
                    ];
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
            $db = \App\Core\Database::getInstance();
            try {
                $db->query("DELETE FROM password_resets WHERE token = :token", ['token' => $token]);
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
                 $db = \App\Core\Database::getInstance();
                 $db->query(
                    "UPDATE candidates SET profile_status = :status WHERE user_id = :user_id",
                    ['status' => 'active', 'user_id' => $user->id]
                 );
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
}
