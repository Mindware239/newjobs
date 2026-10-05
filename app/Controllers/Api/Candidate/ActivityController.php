<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ActivityController extends ApiController
{
    /**
     * GET /api/v1/candidate/activity-logs
     * Get candidate activity logs
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 20);
        $offset = ($page - 1) * $perPage;

        $db = Database::getInstance();
        
        $logs = $db->fetchAll(
            "SELECT * FROM activity_logs 
             WHERE user_id = :uid 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset",
            [
                'uid' => $user->id,
                'limit' => $perPage,
                'offset' => $offset
            ]
        );

        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = :uid",
            ['uid' => $user->id]
        )['count'] ?? 0);

        $this->success($response, [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * GET /api/v1/candidate/activity-summary
     * Get summary of recent activities
     */
    public function summary(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $db = Database::getInstance();
        
        $recentApplications = $db->fetchAll(
            "SELECT a.*, j.title as job_title, e.company_name 
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             JOIN employers e ON j.employer_id = e.id
             WHERE a.candidate_user_id = :uid
             ORDER BY a.applied_at DESC LIMIT 5",
            ['uid' => $user->id]
        );

        $recentViews = $db->fetchAll(
            "SELECT cv.*, e.company_name, u.avatar as employer_logo
             FROM candidate_views cv
             JOIN employers e ON cv.employer_id = e.id
             JOIN users u ON e.user_id = u.id
             WHERE cv.candidate_id = (SELECT id FROM candidates WHERE user_id = :uid)
             ORDER BY cv.viewed_at DESC LIMIT 5",
            ['uid' => $user->id]
        );

        $this->success($response, [
            'recent_applications' => $recentApplications,
            'profile_views' => $recentViews
        ]);
    }
}
