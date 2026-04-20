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
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="mobile", type="string")
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
            'mobile' => 'required'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $user = $this->authService->registerCandidate($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email already exists', 400);
            return;
        }

        // Send welcome / verification email
        try {
            \App\Services\VerificationService::sendEmailVerification((int)$user->id, (string)$user->email);
        } catch (\Throwable $e) {
            error_log('Failed to send verification email during API registration: ' . $e->getMessage());
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $data['full_name'],
                'mobile' => $data['mobile']
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
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="company_name", type="string"),
     *             @OA\Property(property="phone", type="string")
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
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
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
        } catch (\Throwable $e) {
            error_log('Failed to send verification email during API employer registration: ' . $e->getMessage());
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 'Registration successful', 201);
    }

    public function sendPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));

        if ($phone === '') {
            $this->error($response, 'Phone number is required', 422);
            return;
        }

        $result = VerificationService::sendAuthPhoneOTP($phone, $purpose, [
            'role' => $data['role'] ?? null,
        ]);

        if (empty($result['success'])) {
            $this->error($response, $result['error'] ?? 'Failed to send OTP', 500);
            return;
        }

        $payload = [
            'phone' => $result['phone'],
            'purpose' => $result['purpose'],
            'mode' => $result['mode'] ?? 'sms',
        ];

        if (!empty($result['otp_preview'])) {
            $payload['otp_preview'] = $result['otp_preview'];
        }

        $this->success($response, $payload, 'OTP sent successfully');
    }

    public function loginWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $otp = trim((string)($data['otp'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));

        if ($phone === '' || $otp === '') {
            $this->error($response, 'phone and otp are required', 422);
            return;
        }

        $verification = VerificationService::verifyAuthPhoneOTP($phone, $otp, $purpose);
        if (empty($verification['success'])) {
            $this->error($response, $verification['error'] ?? 'Invalid OTP', 400);
            return;
        }

        $user = $this->authService->loginByPhone($phone);
        if (!$user) {
            $this->error($response, 'Account not found for this phone number', 404);
            return;
        }

        $payload = $this->buildOAuthAuthPayload($user);
        $prefs = $user->getNotificationPreferences();
        if (!empty($prefs['contact']['additional_mobile'])) {
            $payload['user']['additional_mobile'] = $prefs['contact']['additional_mobile'];
        }

        $this->success($response, $payload, 'Login successful');
    }

    public function registerCandidateWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $otp = trim((string)($data['otp'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));

        $errors = $this->validate($data, [
            'phone' => 'required',
            'otp' => 'required',
            'full_name' => 'required',
            'email' => 'sometimes|email',
            'password' => 'sometimes|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $verification = VerificationService::verifyAuthPhoneOTP($phone, $otp, $purpose);
        if (empty($verification['success'])) {
            $this->error($response, $verification['error'] ?? 'Invalid OTP', 400);
            return;
        }

        $result = $this->authService->registerCandidateWithPhone($data);
        if (empty($result['success']) || empty($result['user'])) {
            $this->error($response, $result['error'] ?? 'Registration failed', 400);
            return;
        }

        $payload = $this->buildOAuthAuthPayload($result['user']);
        if (!empty($result['additional_mobile'])) {
            $payload['user']['additional_mobile'] = $result['additional_mobile'];
        }

        $this->success($response, $payload, 'Registration successful', 201);
    }

    public function registerEmployerWithPhoneOtp(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $phone = trim((string)($data['phone'] ?? ''));
        $otp = trim((string)($data['otp'] ?? ''));
        $purpose = trim((string)($data['purpose'] ?? 'auth'));

        $errors = $this->validate($data, [
            'phone' => 'required',
            'otp' => 'required',
            'company_name' => 'required',
            'email' => 'sometimes|email',
            'password' => 'sometimes|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $verification = VerificationService::verifyAuthPhoneOTP($phone, $otp, $purpose);
        if (empty($verification['success'])) {
            $this->error($response, $verification['error'] ?? 'Invalid OTP', 400);
            return;
        }

        $result = $this->authService->registerEmployerWithPhone($data);
        if (empty($result['success']) || empty($result['user'])) {
            $this->error($response, $result['error'] ?? 'Registration failed', 400);
            return;
        }

        $payload = $this->buildOAuthAuthPayload($result['user']);
        if (!empty($result['additional_mobile'])) {
            $payload['user']['additional_mobile'] = $result['additional_mobile'];
        }

        $this->success($response, $payload, 'Registration successful', 201);
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

    private function normalizeOAuthRole($role): string
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

        $email = trim((string)($userData['email'] ?? ''));
        if ($email !== '') {
            $user = User::where('email', '=', $email)->first();
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
            $payload['phone'] = (string)$extraData['phone'];
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
