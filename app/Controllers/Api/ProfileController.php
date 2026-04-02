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
        
        $data = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
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
            
            if ($candidate->save()) {
                $this->success($response, $candidate->attributes, 'Profile updated successfully');
            } else {
                $this->error($response, 'Failed to update profile', 500);
            }
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

            if ($employer->save()) {
                $this->success($response, $employer->attributes, 'Profile updated successfully');
            } else {
                $this->error($response, 'Failed to update profile', 500);
            }
        } else {
            $this->error($response, 'Unsupported user role', 400);
        }
    }
}
