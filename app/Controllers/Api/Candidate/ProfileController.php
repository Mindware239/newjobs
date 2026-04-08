<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateSkill;
use App\Models\CandidateLanguage;
use App\Models\CandidateInterest;

class ProfileController extends ApiController
{
    /**
     * GET /candidate/profile/detailed
     * Get detailed candidate profile
     */
    public function detailed(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();
        if (!$candidate) {
            $this->error($response, 'Profile not found', 404);
            return;
        }

        $education = CandidateEducation::where('candidate_id', '=', $candidate->id)->get();
        $experience = CandidateExperience::where('candidate_id', '=', $candidate->id)
            ->orderBy('end_date', 'DESC')->get();
        $skills = CandidateSkill::where('candidate_id', '=', $candidate->id)->get();
        $languages = CandidateLanguage::where('candidate_id', '=', $candidate->id)->get();
        $interests = CandidateInterest::where('candidate_id', '=', $candidate->id)->get();

        $this->success($response, [
            'personal' => [
                'full_name' => $candidate->full_name,
                'email' => $user->email,
                'phone' => $candidate->mobile ?: $user->phone,
                'additional_mobile' => ($user->getNotificationPreferences()['contact']['additional_mobile'] ?? null),
                'avatar' => $user->avatar,
                'headline' => $candidate->headline,
                'summary' => $candidate->summary,
                'location' => $candidate->location
            ],
            'education' => $education,
            'experience' => $experience,
            'skills' => $skills,
            'languages' => $languages,
            'interests' => $interests,
            'completion_percentage' => $this->calculateCompletion($candidate)
        ]);
    }

    /**
     * POST /candidate/profile/education
     * Add education
     */
    public function addEducation(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'school_name' => 'required',
            'degree' => 'required',
            'field_of_study' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'sometimes|date',
            'grade' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        $education = new CandidateEducation();
        $education->fill(array_merge(
            $request->getJsonBody(),
            ['candidate_id' => $candidate->id]
        ))->save();

        $this->success($response, ['id' => $education->id], 'Education added', 201);
    }

    /**
     * PUT /candidate/profile/education/{id}
     * Update education
     */
    public function updateEducation(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $education = CandidateEducation::find($id);
        if (!$education) {
            $this->error($response, 'Education record not found', 404);
            return;
        }

        $education->fill($request->getJsonBody())->save();

        $this->success($response, ['id' => $education->id]);
    }

    /**
     * DELETE /candidate/profile/education/{id}
     * Delete education
     */
    public function deleteEducation(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $education = CandidateEducation::find($id);
        if (!$education) {
            $this->error($response, 'Education record not found', 404);
            return;
        }

        $education->delete();

        $this->success($response, [], 'Education deleted');
    }

    /**
     * POST /candidate/profile/experience
     * Add experience
     */
    public function addExperience(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'company_name' => 'required',
            'job_title' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'sometimes|date',
            'currently_working' => 'sometimes|boolean',
            'description' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        $experience = new CandidateExperience();
        $experience->fill(array_merge(
            $request->getJsonBody(),
            ['candidate_id' => $candidate->id]
        ))->save();

        $this->success($response, ['id' => $experience->id], 'Experience added', 201);
    }

    /**
     * PUT /candidate/profile/experience/{id}
     * Update experience
     */
    public function updateExperience(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $experience = CandidateExperience::find($id);
        if (!$experience) {
            $this->error($response, 'Experience record not found', 404);
            return;
        }

        $experience->fill($request->getJsonBody())->save();

        $this->success($response, ['id' => $experience->id]);
    }

    /**
     * DELETE /candidate/profile/experience/{id}
     * Delete experience
     */
    public function deleteExperience(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $experience = CandidateExperience::find($id);
        if (!$experience) {
            $this->error($response, 'Experience record not found', 404);
            return;
        }

        $experience->delete();

        $this->success($response, [], 'Experience deleted');
    }

    /**
     * POST /candidate/profile/skills
     * Add skill
     */
    public function addSkill(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'skill_name' => 'required',
            'proficiency_level' => 'sometimes|in:beginner,intermediate,expert'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        $skill = new CandidateSkill();
        $skill->fill(array_merge(
            $request->getJsonBody(),
            ['candidate_id' => $candidate->id]
        ))->save();

        $this->success($response, ['id' => $skill->id], 'Skill added', 201);
    }

    /**
     * DELETE /candidate/profile/skills/{id}
     * Remove skill
     */
    public function removeSkill(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $skill = CandidateSkill::find($id);
        if (!$skill) {
            $this->error($response, 'Skill not found', 404);
            return;
        }

        $skill->delete();

        $this->success($response, [], 'Skill removed');
    }

    /**
     * POST /candidate/profile/languages
     * Add language
     */
    public function addLanguage(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'language_name' => 'required',
            'proficiency_level' => 'required|in:basic,fluent,native'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        $language = new CandidateLanguage();
        $language->fill(array_merge(
            $request->getJsonBody(),
            ['candidate_id' => $candidate->id]
        ))->save();

        $this->success($response, ['id' => $language->id], 'Language added', 201);
    }

    /**
     * DELETE /candidate/profile/languages/{id}
     * Remove language
     */
    public function removeLanguage(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $language = CandidateLanguage::find($id);
        if (!$language) {
            $this->error($response, 'Language not found', 404);
            return;
        }

        $language->delete();

        $this->success($response, [], 'Language removed');
    }

    /**
     * POST /candidate/profile/interests
     * Set job interests
     */
    public function setInterests(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'job_titles' => 'required|array',
            'industries' => 'required|array',
            'locations' => 'required|array',
            'experience_level' => 'required|in:entry,mid,senior'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        // Delete existing interests
        CandidateInterest::where('candidate_id', '=', $candidate->id)->delete();

        // Add new interests
        foreach ($request->input('job_titles', []) as $title) {
            $interest = new CandidateInterest();
            $interest->fill([
                'candidate_id' => $candidate->id,
                'interest_type' => 'job_title',
                'interest_value' => $title
            ])->save();
        }

        $this->success($response, [], 'Interests updated', 200);
    }

    /**
     * GET /candidate/profile/completion-status
     * Get profile completion percentage
     */
    public function completionStatus(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $user->id)->first();

        $completion = $this->calculateCompletion($candidate);
        $suggestions = $this->getCompletionSuggestions($candidate);

        $this->success($response, [
            'completion_percentage' => $completion,
            'suggestions' => $suggestions
        ]);
    }

    private function calculateCompletion($candidate): int
    {
        $fields = 0;
        $completed = 0;

        // Check personal info
        $fields++;
        if ($candidate->headline && $candidate->summary) $completed++;

        // Check education (at least 1)
        $fields++;
        if (CandidateEducation::where('candidate_id', '=', $candidate->id)->count() > 0) $completed++;

        // Check experience (at least 1)
        $fields++;
        if (CandidateExperience::where('candidate_id', '=', $candidate->id)->count() > 0) $completed++;

        // Check skills (at least 3)
        $fields++;
        if (CandidateSkill::where('candidate_id', '=', $candidate->id)->count() >= 3) $completed++;

        return (int)(($completed / $fields) * 100);
    }

    private function getCompletionSuggestions($candidate): array
    {
        $suggestions = [];

        if (!$candidate->headline) {
            $suggestions[] = 'Add a professional headline';
        }

        if (!$candidate->summary) {
            $suggestions[] = 'Write a profile summary';
        }

        if (CandidateEducation::where('candidate_id', '=', $candidate->id)->count() === 0) {
            $suggestions[] = 'Add your education details';
        }

        if (CandidateExperience::where('candidate_id', '=', $candidate->id)->count() === 0) {
            $suggestions[] = 'Add your work experience';
        }

        if (CandidateSkill::where('candidate_id', '=', $candidate->id)->count() < 3) {
            $suggestions[] = 'Add at least 3 skills';
        }

        return $suggestions;
    }
}
