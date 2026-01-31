<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Job;
use App\Models\Employer;

class JobApprovalService
{
    public function handle(Job $job, Employer $employer): void
    {
        $db = Database::getInstance();
        $risk = $db->fetchOne(
            "SELECT score FROM employer_risk_scores WHERE employer_id = :id",
            ['id' => $employer->id]
        );

        $score = (int)($risk['score'] ?? 0);

        if ($score >= 80) {
            $job->status = 'published';
            $job->approved_at = date('Y-m-d H:i:s');
            $job->save();
        } else {
            $job->status = 'pending_review';
            $job->save();

            $db->query(
                "INSERT INTO job_review_queue (job_id, employer_id, review_reason)
                 VALUES (:job_id, :employer_id, :reason)",
                [
                    'job_id'      => $job->id,
                    'employer_id' => $employer->id,
                    'reason'      => $score > 0 ? 'Trust score below 80' : 'No trust score available',
                ]
            );
        }
    }
}


