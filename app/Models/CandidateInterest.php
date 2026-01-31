<?php

declare(strict_types=1);

namespace App\Models;

class CandidateInterest extends Model
{
    protected string $table = 'candidate_interest';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'employer_id', 'job_id', 'interest_level', 'metadata'
    ];

    public function candidate()
    {
        return Candidate::find($this->attributes['candidate_id'] ?? 0);
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function job()
    {
        return Job::find($this->attributes['job_id'] ?? 0);
    }

    public static function recordInterest(
        int $candidateId,
        int $employerId,
        string $interestLevel,
        ?int $jobId = null
    ): void {
        $existing = self::where('candidate_id', '=', $candidateId)
            ->where('employer_id', '=', $employerId)
            ->where('job_id', '=', $jobId)
            ->first();
        
        if ($existing) {
            $existing->attributes['interest_level'] = $interestLevel;
            $existing->save();
        } else {
            $interest = new self();
            $interest->fill([
                'candidate_id' => $candidateId,
                'employer_id' => $employerId,
                'job_id' => $jobId,
                'interest_level' => $interestLevel
            ]);
            $interest->save();
        }
    }

    public static function getInterestedCandidates(int $employerId, ?int $jobId = null): array
    {
        $query = self::where('employer_id', '=', $employerId);
        
        if ($jobId) {
            $query = $query->where('job_id', '=', $jobId);
        }
        
        return $query->whereIn('interest_level', ['applied', 'shortlisted', 'high_interest'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}

