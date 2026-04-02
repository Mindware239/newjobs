<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Interview;
use App\Models\Application;
use App\Services\JitsiService;
use App\Services\NotificationService;

class InterviewController extends ApiController
{
    private JitsiService $jitsiService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->jitsiService = new JitsiService();
        $this->notificationService = new NotificationService();
    }

    /**
     * POST /interviews/schedule
     * Schedule interview
     */
    public function schedule(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'application_id' => 'required|numeric',
            'interview_type' => 'required|in:phone,video,in-person,online',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s',
            'duration_minutes' => 'required|numeric|min:15|max:480',
            'interviewer_notes' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $application = Application::find((int)$request->input('application_id'));
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        // Verify authorization: only employer who posted job can schedule
        if ($user->role === 'employer') {
            // Check if user is employer of this job
            // Implementation depends on your job model
        }

        $interview = new Interview();
        $interview->fill([
            'application_id' => $application->id,
            'scheduled_at' => $request->input('scheduled_at'),
            'interview_type' => $request->input('interview_type'),
            'duration_minutes' => $request->input('duration_minutes'),
            'interviewer_notes' => $request->input('interviewer_notes'),
            'status' => 'scheduled'
        ])->save();

        // Notify candidate
        $this->notificationService->send(
            $application->candidate_id,
            'interview_scheduled',
            'Interview scheduled for ' . $application->job?->title,
            ['interview_id' => $interview->id]
        );

        $this->success($response, [
            'id' => $interview->id,
            'scheduled_at' => $interview->scheduled_at,
            'interview_type' => $interview->interview_type
        ], 'Interview scheduled', 201);
    }

    /**
     * GET /interviews
     * List interviews
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $status = $request->query('status');

        $query = Interview::query();

        if ($user->role === 'candidate') {
            $query->whereIn('application_id', function($q) use ($user) {
                $q->select('id')->from('applications')->where('candidate_id', '=', $user->id);
            });
        }

        if ($status) {
            $query->where('status', '=', $status);
        }

        $interviews = $query->orderBy('scheduled_at', 'ASC')->paginate($perPage, $page);

        $this->success($response, [
            'interviews' => $interviews['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $interviews['total'],
                'last_page' => ceil($interviews['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /interviews/{id}
     * Get interview details
     */
    public function show(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        // Verify authorization
        $application = $interview->application;
        if ($user->role === 'candidate' && $application->candidate_id !== $user->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        $this->success($response, [
            'id' => $interview->id,
            'application_id' => $interview->application_id,
            'scheduled_at' => $interview->scheduled_at,
            'interview_type' => $interview->interview_type,
            'duration_minutes' => $interview->duration_minutes,
            'status' => $interview->status,
            'interviewer_notes' => $interview->interviewer_notes,
            'feedback' => $interview->feedback,
            'meeting_link' => $interview->meeting_link
        ]);
    }

    /**
     * PUT /interviews/{id}
     * Update interview
     */
    public function update(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        if ($interview->status !== 'scheduled') {
            $this->error($response, 'Cannot update interview in current status', 400);
            return;
        }

        if ($request->input('scheduled_at')) {
            $interview->scheduled_at = $request->input('scheduled_at');
        }

        if ($request->input('duration_minutes')) {
            $interview->duration_minutes = $request->input('duration_minutes');
        }

        $interview->save();

        $this->success($response, ['id' => $interview->id]);
    }

    /**
     * DELETE /interviews/{id}
     * Cancel interview
     */
    public function cancel(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        if (!in_array($interview->status, ['scheduled', 'confirmed'])) {
            $this->error($response, 'Cannot cancel interview', 400);
            return;
        }

        $interview->status = 'cancelled';
        $interview->cancellation_reason = $request->input('reason');
        $interview->save();

        // Notify other party
        $application = $interview->application;
        $this->notificationService->send(
            $application->candidate_id,
            'interview_cancelled',
            'Interview cancelled',
            ['interview_id' => $interview->id]
        );

        $this->success($response, [], 'Interview cancelled');
    }

    /**
     * POST /interviews/{id}/reschedule
     * Reschedule interview
     */
    public function reschedule(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $interview->scheduled_at = $request->input('scheduled_at');
        $interview->rescheduled_from = $interview->getOriginal('scheduled_at');
        $interview->save();

        $this->success($response, [], 'Interview rescheduled');
    }

    /**
     * POST /interviews/{id}/complete
     * Mark interview as complete
     */
    public function complete(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        $interview->status = 'completed';
        $interview->completed_at = date('Y-m-d H:i:s');
        $interview->save();

        $this->success($response, [], 'Interview marked as complete');
    }

    /**
     * POST /interviews/{id}/feedback
     * Add feedback to interview
     */
    public function addFeedback(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'feedback' => 'required|string',
            'rating' => 'sometimes|numeric|min:1|max:5'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $interview->feedback = $request->input('feedback');
        $interview->feedback_rating = $request->input('rating');
        $interview->save();

        $this->success($response, [], 'Feedback added successfully');
    }

    /**
     * GET /interviews/{id}/jitsi-token
     * Get Jitsi video call token
     */
    public function getJitsiToken(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        if ($interview->interview_type !== 'video') {
            $this->error($response, 'This interview is not a video interview', 400);
            return;
        }

        $token = $this->jitsiService->generateToken('interview_' . $interview->id, $user->id);

        $this->success($response, [
            'token' => $token,
            'server' => 'https://meet.jit.si',
            'room' => 'interview_' . $interview->id
        ]);
    }

    /**
     * POST /interviews/{id}/attendance
     * Mark attendance
     */
    public function markAttendance(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $interview = Interview::find($id);
        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'attended' => 'required|boolean',
            'notes' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $interview->attended = $request->input('attended');
        $interview->attendance_notes = $request->input('notes');
        $interview->save();

        $this->success($response, [], 'Attendance marked');
    }
}
