<?php

namespace App\Controllers\Company;

use App\Models\CompanyFollower;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\BaseController;

ini_set('display_errors', 1);
error_reporting(E_ALL);

class CompanyFollowController extends BaseController
{
    public function toggle(Request $req, Response $res)
    {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        $candidateId = $_SESSION['candidate_id'] ?? null;
        if (!$candidateId) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $candidate = \App\Models\Candidate::findByUserId((int)$userId);
                if ($candidate && isset($candidate->attributes['id'])) {
                    $candidateId = (int)$candidate->attributes['id'];
                    $_SESSION['candidate_id'] = $candidateId;
                }
            }
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $companyId = (int)($data['company_id'] ?? 0);

        if (!$candidateId) {
            return $res->json(['error' => 'Not logged in'], 401);
        }

        if (!$companyId) {
            return $res->json(['error' => 'Invalid company'], 422);
        }

        if (CompanyFollower::isFollowing($candidateId, $companyId)) {
            CompanyFollower::unfollow($candidateId, $companyId);
            $status = 'unfollowed';
        } else {
            CompanyFollower::follow($candidateId, $companyId);
            $status = 'followed';
        }

        $followers = CompanyFollower::countFollowers($companyId);

        return $res->json([
            'status'    => $status,
            'followers' => $followers
        ]);
    }
}
