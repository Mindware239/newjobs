<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ApiDashboardRepository;

class DashboardController extends ApiController
{
    private ApiDashboardRepository $dashboardRepository;

    public function __construct()
    {
        $this->dashboardRepository = new ApiDashboardRepository();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard",
     *     summary="Get dashboard statistics",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Dashboard statistics")
     * )
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = [];

        if ($user->isCandidate()) {
            $candidateId = $this->dashboardRepository->getCandidateIdByUserId((int)$user->id);
            $data = $this->dashboardRepository->getCandidateDashboardData($candidateId, (int)$user->id);
        } elseif ($user->isEmployer()) {
            $employerId = $this->dashboardRepository->getEmployerIdByUserId((int)$user->id);
            $data = $this->dashboardRepository->getEmployerDashboardData($employerId);
        }

        $this->success($response, $data);
    }

    /**
     * GET /api/v1/candidate/dashboard
     * Explicit candidate dashboard for mobile app
     */
    public function candidateDashboard(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidateId = $this->dashboardRepository->getCandidateIdByUserId((int)$user->id);
        $data = $this->dashboardRepository->getCandidateDashboardData($candidateId, (int)$user->id);
        
        // Add more mobile-specific dashboard data if needed
        $data['user'] = [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'is_verified' => (bool)$user->is_email_verified
        ];

        $this->success($response, $data);
    }
}
