<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Employer;

class ProfileController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     summary="Get current user profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Profile data")
     * )
     */
    public function show(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $prefs = $user->getNotificationPreferences();
        
        $data = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'additional_mobile' => $prefs['contact']['additional_mobile'] ?? null,
            'status' => $user->status
        ];

        if ($user->isCandidate()) {
            $candidate = Candidate::findByUserId($user->id);
            if ($candidate) {
                $data['candidate_profile'] = $candidate->attributes;
            }
        } elseif ($user->isEmployer()) {
            $employer = Employer::findByUserId($user->id);
            if ($employer) {
                $data['employer_profile'] = $employer->attributes;
            }
        }

        $this->success($response, $data);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile",
     *     summary="Update current user profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated")
     * )
     */
    public function update(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->getJsonBody();
        $prefs = $user->getNotificationPreferences();
        if (!is_array($prefs)) {
            $prefs = [];
        }
        $prefs['contact'] = is_array($prefs['contact'] ?? null) ? $prefs['contact'] : [];

        if (array_key_exists('additional_mobile', $data)) {
            $prefs['contact']['additional_mobile'] = $data['additional_mobile'] ?: null;
            $user->setNotificationPreferences($prefs);
        }
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'] ?: null;
            if (empty($data['phone'])) {
                $user->is_phone_verified = 0;
            }
        }
        
        try {
            $user->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        if ($user->isCandidate()) {
            $candidate = Candidate::findByUserId($user->id);
            if (!$candidate) {
                $candidate = Candidate::createForUser($user->id);
            }
            
            $candidate->fill([
                'full_name' => $data['full_name'] ?? $candidate->full_name,
                'mobile' => $data['mobile'] ?? $candidate->mobile,
                'headline' => $data['headline'] ?? $candidate->headline,
                'summary' => $data['summary'] ?? $candidate->summary,
                'location' => $data['location'] ?? $candidate->location,
            ]);
            
            try {
                $candidate->save();
            } catch (\Throwable $e) {
                error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
                $this->error($response, 'Database error occurred. Please try again.', 500);
                return;
            }

            $payload = $candidate->attributes;
            $payload['additional_mobile'] = $prefs['contact']['additional_mobile'] ?? null;
            $this->success($response, $payload, 'Profile updated successfully');
        } elseif ($user->isEmployer()) {
            $employer = Employer::findByUserId($user->id);
            if (!$employer) {
                $this->error($response, 'Employer profile not found', 404);
                return;
            }

            $employer->fill([
                'company_name' => $data['company_name'] ?? $employer->company_name,
                'website' => $data['website'] ?? $employer->website,
                'description' => $data['description'] ?? $employer->description,
                'industry' => $data['industry'] ?? $employer->industry,
                'size' => $data['company_size'] ?? $employer->size,
            ]);

            try {
                $employer->save();
            } catch (\Throwable $e) {
                error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
                $this->error($response, 'Database error occurred. Please try again.', 500);
                return;
            }

            $payload = $employer->attributes;
            $payload['additional_mobile'] = $prefs['contact']['additional_mobile'] ?? null;
            $this->success($response, $payload, 'Profile updated successfully');
        } else {
            $this->error($response, 'Unsupported user role', 400);
        }
    }
}
