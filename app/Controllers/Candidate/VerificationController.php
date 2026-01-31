<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Services\VerificationService;

class VerificationController extends BaseController
{

    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return null;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        return $candidate;
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $user = $candidate->user();
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        $code = VerificationService::sendEmailVerification($user->id, $user->attributes['email']);
        
        $response->json([
            'success' => true,
            'message' => 'Verification code sent to your email',
            'code' => $code // Remove in production
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody() ?? $request->all();
        $code = $data['code'] ?? '';

        $user = $candidate->user();
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        if (VerificationService::verifyEmail($user->id, $code)) {
            $response->json([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);
        } else {
            $response->json(['error' => 'Invalid verification code'], 400);
        }
    }

    /**
     * Send phone OTP
     */
    public function sendPhoneOTP(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody() ?? $request->all();
        $phone = $data['phone'] ?? $candidate->attributes['mobile'] ?? '';

        if (empty($phone)) {
            $response->json(['error' => 'Phone number is required'], 422);
            return;
        }

        $user = $candidate->user();
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        $otp = VerificationService::sendPhoneOTP($user->id, $phone);
        
        $response->json([
            'success' => true,
            'message' => 'OTP sent to your phone',
            'otp' => $otp // Remove in production
        ]);
    }

    /**
     * Verify phone
     */
    public function verifyPhone(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody() ?? $request->all();
        $otp = $data['otp'] ?? '';

        $user = $candidate->user();
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        if (VerificationService::verifyPhone($user->id, $otp)) {
            $response->json([
                'success' => true,
                'message' => 'Phone verified successfully'
            ]);
        } else {
            $response->json(['error' => 'Invalid OTP'], 400);
        }
    }
}

