<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Candidate;
use App\Models\Job;

class NotificationQueueService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function queueCandidateNotification(Candidate $candidate, Job $job, int $score): void
    {
        $user = $candidate->user();
        if (!$user) return;

        $isHighSalary = false;
        $jobMinSalary = (int)($job->attributes['salary_min'] ?? 0);
        $candExpMax = (int)($candidate->attributes['expected_salary_max'] ?? 0);
        if (($candExpMax > 0 && $jobMinSalary > ($candExpMax * 1.5)) || $jobMinSalary >= 100000) {
            $isHighSalary = true;
        }

        $companyName = $this->getCompanyName($job);
        $link = '/candidate/jobs/' . ($job->attributes['slug'] ?? $job->id);
        $title = $isHighSalary ? 'High salary job match' : 'New job match found';
        $message = "{$job->attributes['title']} at {$companyName} matches your profile ({$score}%).";

        $this->insertIntoQueue((int)$user->id, 'job_match', $title, $message, array_merge(
            $this->buildJobPayload($job),
            [
                'match_score' => $score,
                'link' => $link,
                'details_url' => $this->absoluteUrl($link),
                'is_high_salary' => $isHighSalary,
                'reference_id' => "cand_{$candidate->id}_job_{$job->id}"
            ]
        ), $link);
    }

    public function queueEmployerNotification(Job $job, int $count): void
    {
        $employer = $job->employer();
        if (!$employer) return;
        $user = $employer->user();
        if (!$user) return;

        $link = '/employer/jobs/' . ($job->attributes['slug'] ?? $job->id) . '/candidates';
        $message = "We found {$count} relevant candidate" . ($count === 1 ? '' : 's') . " for " . ($job->attributes['title'] ?? 'your job') . ".";

        $this->insertIntoQueue((int)$user->id, 'job_match', 'Relevant candidates found', $message, [
            'email_template' => 'employer_job_match',
            'audience' => 'employer',
            'job_title' => $job->attributes['title'] ?? '',
            'company_name' => $this->getCompanyName($job),
            'count' => $count,
            'link' => $link,
            'details_url' => $this->absoluteUrl($link),
            'reference_id' => "job_{$job->id}_candidate_batch"
        ], $link);
    }

    public function queueEmployerMatchNotification(Job $job, Candidate $candidate): void
    {
        $employer = $job->employer();
        if (!$employer) return;
        $user = $employer->user();
        if (!$user) return;

        $link = '/employer/jobs/' . ($job->attributes['slug'] ?? $job->id) . '/candidates';
        $candidateName = $candidate->attributes['full_name'] ?? 'A candidate';

        $this->insertIntoQueue((int)$user->id, 'job_match', 'New candidate match', "{$candidateName} matches your job.", array_merge(
            $this->buildCandidatePayload($candidate),
            $this->buildJobPayload($job),
            [
                'email_template' => 'candidate_match_employer',
                'audience' => 'employer',
                'candidate_name' => $candidateName,
                'link' => $link,
                'details_url' => $this->absoluteUrl($link),
                'dashboard_link' => $link,
                'reference_id' => "cand_{$candidate->id}_job_{$job->id}"
            ]
        ), $link);
    }

    public function queueJobMatchNotification(\App\Models\User $user, Job $job, Candidate $candidate, int $score): void
    {
        $link = '/candidate/jobs/' . ($job->attributes['slug'] ?? $job->id);

        $this->insertIntoQueue((int)$user->id, 'job_match', 'New job match found', "{$job->attributes['title']} matches your profile ({$score}%).", array_merge(
            $this->buildJobPayload($job),
            $this->buildCandidatePayload($candidate),
            [
                'match_score' => $score,
                'link' => $link,
                'details_url' => $this->absoluteUrl($link),
                'reference_id' => "user_{$user->id}_job_{$job->id}"
            ]
        ), $link);
    }

    private function insertIntoQueue(int $userId, string $type, string $title, string $message, array $data, ?string $link): void
    {
        try {
            $hasQueueTable = $this->db->fetchOne("SHOW TABLES LIKE 'notification_queue'");

            if ($hasQueueTable) {
                $ref = $data['reference_id'] ?? null;
                if ($ref) {
                    $exists = $this->db->fetchOne(
                        "SELECT id FROM notification_queue
                         WHERE user_id = :uid AND JSON_EXTRACT(data, '$.reference_id') = :ref
                         AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                        ['uid' => $userId, 'ref' => $ref]
                    );
                    if ($exists) return;
                }

                $this->db->query(
                    "INSERT INTO notification_queue (user_id, type, title, message, data, link, status, retries, created_at)
                     VALUES (:uid, :type, :title, :msg, :data, :link, 'pending', 0, NOW())",
                    [
                        'uid' => $userId,
                        'type' => $type,
                        'title' => $title,
                        'msg' => $message,
                        'data' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'link' => $link
                    ]
                );
            } else {
                NotificationService::send($userId, $type, $title, $message, $data, $link);
            }
        } catch (\Throwable $t) {
            error_log("Failed to queue notification: " . $t->getMessage());
        }
    }

    private function buildCandidatePayload(Candidate $candidate): array
    {
        $skills = array_values(array_filter(array_map(
            fn($skill) => trim((string)($skill['name'] ?? '')),
            $candidate->skills()
        )));
        $experience = $candidate->experience();
        $latestExperience = $experience[0] ?? [];
        $location = implode(', ', array_filter([
            $candidate->attributes['city'] ?? '',
            $candidate->attributes['state'] ?? '',
            $candidate->attributes['country'] ?? ''
        ]));
        $resumePath = !empty($candidate->attributes['resume_url']) ? (string)$candidate->attributes['resume_url'] : null;

        return [
            'candidate_id' => (int)$candidate->id,
            'candidate_name' => $candidate->attributes['full_name'] ?? 'Candidate',
            'candidate_title' => $candidate->attributes['professional_title'] ?? ($latestExperience['job_title'] ?? ''),
            'candidate_location' => $location,
            'candidate_profile_strength' => (int)($candidate->attributes['profile_strength'] ?? 0),
            'candidate_skills' => array_slice($skills, 0, 12),
            'candidate_skills_text' => implode(', ', array_slice($skills, 0, 8)),
            'candidate_current_company' => $latestExperience['company_name'] ?? '',
            'candidate_experience_title' => $latestExperience['job_title'] ?? '',
            'candidate_summary' => $this->plainText((string)($candidate->attributes['self_introduction'] ?? '')),
            'candidate_photo_url' => $this->absoluteUrl($candidate->attributes['profile_picture'] ?? null),
            'candidate_resume_url' => $resumePath ? $this->absoluteUrl($resumePath) : null,
            'candidate_profile_url' => $this->absoluteUrl('/api/v1/employer/candidates/' . (int)$candidate->id)
        ];
    }

    private function buildJobPayload(Job $job): array
    {
        $skills = array_values(array_filter(array_map(
            fn($skill) => trim((string)($skill['name'] ?? '')),
            $job->skills()
        )));
        $locations = $this->formatJobLocations($job);
        $slug = (string)($job->attributes['slug'] ?? $job->id);
        $path = '/candidate/jobs/' . $slug;

        return [
            'job_id' => (int)($job->attributes['id'] ?? $job->id ?? 0),
            'job_slug' => $slug,
            'job_title' => $job->attributes['title'] ?? 'Job',
            'job_description' => $this->plainText((string)($job->attributes['short_description'] ?? $job->attributes['description'] ?? '')),
            'job_location' => implode(' | ', $locations),
            'job_locations' => $locations,
            'job_skills' => array_slice($skills, 0, 12),
            'job_skills_text' => implode(', ', array_slice($skills, 0, 8)),
            'salary_display' => $this->formatSalary($job),
            'experience_display' => $this->formatExperience($job),
            'employment_type' => ucfirst(str_replace('_', ' ', (string)($job->attributes['employment_type'] ?? 'full_time'))),
            'company_name' => $this->getCompanyName($job),
            'company_logo_url' => $this->absoluteUrl($this->getCompanyLogo($job)),
            'job_url' => $this->absoluteUrl($path)
        ];
    }

    private function formatJobLocations(Job $job): array
    {
        $locations = [];
        foreach ($job->locations() as $loc) {
            if (is_object($loc) && isset($loc->attributes)) {
                $parts = array_filter([$loc->attributes['city'] ?? '', $loc->attributes['state'] ?? '', $loc->attributes['country'] ?? '']);
            } elseif (is_array($loc)) {
                $parts = array_filter([$loc['city'] ?? '', $loc['state'] ?? '', $loc['country'] ?? '', $loc['display'] ?? '']);
            } else {
                $parts = [];
            }
            if (!empty($parts)) {
                $locations[] = implode(', ', $parts);
            }
        }

        return array_values(array_unique($locations));
    }

    private function getCompanyName(Job $job): string
    {
        if (!empty($job->attributes['company_name'])) {
            return (string)$job->attributes['company_name'];
        }
        $employer = $job->employer();
        return $employer ? (string)($employer->attributes['company_name'] ?? 'Company') : 'Company';
    }

    private function getCompanyLogo(Job $job): ?string
    {
        $employer = $job->employer();
        return $employer ? ($employer->attributes['logo_url'] ?? null) : null;
    }

    private function formatSalary(Job $job): string
    {
        $currency = (string)($job->attributes['currency'] ?? 'INR');
        $min = $job->attributes['salary_min'] ?? null;
        $max = $job->attributes['salary_max'] ?? null;
        if ($min && $max) return "{$currency} {$min} - {$max}";
        if ($min) return "{$currency} {$min}+";
        if ($max) return "Up to {$currency} {$max}";
        return 'Not disclosed';
    }

    private function formatExperience(Job $job): string
    {
        $min = $job->attributes['min_experience'] ?? null;
        $max = $job->attributes['max_experience'] ?? null;
        if ($min !== null && $max !== null) return "{$min}-{$max} years";
        if ($min !== null) return "{$min}+ years";
        return 'Not specified';
    }

    private function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (preg_match('#^https?://#i', $path)) return $path;
        $base = rtrim((string)(getenv('APP_URL') ?: 'http://localhost:8000'), '/');
        return $base . (str_starts_with($path, '/') ? $path : '/' . $path);
    }
}
