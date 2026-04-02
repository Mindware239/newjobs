<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends ApiController
{
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
        $db = Database::getInstance();
        $data = [];

        if ($user->isCandidate()) {
            $candidateId = (int)($db->fetchOne("SELECT id FROM candidates WHERE user_id = :uid", ['uid' => $user->id])['id'] ?? 0);
            
            $data = [
                'total_applications' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid", ['cid' => $candidateId])['count'] ?? 0),
                'shortlisted_applications' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid AND status = 'shortlisted'", ['cid' => $candidateId])['count'] ?? 0),
                'rejected_applications' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid AND status = 'rejected'", ['cid' => $candidateId])['count'] ?? 0),
                'saved_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM job_bookmarks WHERE user_id = :uid", ['uid' => $user->id])['count'] ?? 0),
                'recent_applications' => $db->fetchAll(
                    "SELECT a.*, j.title, e.company_name 
                     FROM applications a 
                     JOIN jobs j ON a.job_id = j.id 
                     JOIN employers e ON j.employer_id = e.id 
                     WHERE a.candidate_id = :cid 
                     ORDER BY a.applied_at DESC LIMIT 5",
                    ['cid' => $candidateId]
                )
            ];
        } elseif ($user->isEmployer()) {
            $employerId = (int)($db->fetchOne("SELECT id FROM employers WHERE user_id = :uid", ['uid' => $user->id])['id'] ?? 0);
            
            $data = [
                'total_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE employer_id = :eid", ['eid' => $employerId])['count'] ?? 0),
                'active_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE employer_id = :eid AND status = 'published'", ['eid' => $employerId])['count'] ?? 0),
                'total_applications' => (int)($db->fetchOne(
                    "SELECT COUNT(*) as count FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = :eid",
                    ['eid' => $employerId]
                )['count'] ?? 0),
                'recent_jobs' => $db->fetchAll(
                    "SELECT * FROM jobs WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 5",
                    ['eid' => $employerId]
                )
            ];
        }

        $this->success($response, $data);
    }
}
