<?php

declare(strict_types=1);

namespace App\Models;

class SubscriptionUsageLog extends Model
{
    protected string $table = 'subscription_usage_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'subscription_id', 'employer_id', 'action_type', 'candidate_id',
        'job_id', 'application_id', 'metadata'
    ];

    public function subscription()
    {
        return EmployerSubscription::find($this->attributes['subscription_id'] ?? 0);
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public static function logUsage(
        int $subscriptionId,
        int $employerId,
        string $actionType,
        ?int $candidateId = null,
        ?int $jobId = null,
        ?int $applicationId = null,
        ?array $metadata = null
    ): void {
        $log = new self();
        $log->fill([
            'subscription_id' => $subscriptionId,
            'employer_id' => $employerId,
            'action_type' => $actionType,
            'candidate_id' => $candidateId,
            'job_id' => $jobId,
            'application_id' => $applicationId,
            'metadata' => $metadata ? json_encode($metadata) : null
        ]);
        $log->save();
    }
}

