<?php

declare(strict_types=1);

namespace App\Services\Employer;

use App\Core\Database;
use App\Models\Employer;
use App\Models\Job;
use App\Models\JobLocation;

class JobService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getJobsByEmployer(int $employerId): array
    {
        $jobs = Job::where('employer_id', '=', $employerId)
            ->orderBy('created_at', 'DESC')
            ->get();

        return array_map(fn(Job $job) => $this->formatJob($job), $jobs);
    }

    public function getJob(int $employerId, int $jobId): array
    {
        $job = Job::find($jobId);
        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found', 'code' => 404];
        }

        return ['success' => true, 'job' => $this->formatJob($job)];
    }

    public function createJob(int $employerId, array $data): array
    {
        $errors = $this->validateJobData($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $errors];
        }

        $employer = Employer::find($employerId);
        $job = new Job();
        $title = trim((string)$data['title']);
        $locations = $this->normalizeLocations($data);

        $job->fill($this->buildJobAttributes($employerId, $data, $employer, [
            'title' => $title,
            'slug' => $job->generateSlug($title),
            'status' => $this->normalizeStatus($data['status'] ?? 'published'),
            'locations' => !empty($locations) ? json_encode($locations, JSON_UNESCAPED_UNICODE) : null,
        ]));

        try {
            if (!$job->save()) {
                return ['success' => false, 'message' => 'Failed to create job'];
            }

            $this->replaceLocations((int)$job->id, $locations);
            $saved = Job::find((int)$job->id) ?: $job;

            return ['success' => true, 'job' => $this->formatJob($saved)];
        } catch (\Throwable $e) {
            error_log('API Employer Job create failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create job'];
        }
    }

    public function updateJob(int $employerId, int $jobId, array $data): array
    {
        $job = Job::find($jobId);

        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found or you do not have permission to edit it', 'code' => 404];
        }

        $errors = $this->validateJobData($data, false);
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed', 'code' => 422, 'errors' => $errors];
        }

        $employer = Employer::find($employerId);
        $locations = array_key_exists('location', $data) || array_key_exists('locations', $data)
            ? $this->normalizeLocations($data)
            : null;

        $overrides = [];
        if (!empty($data['title']) && trim((string)$data['title']) !== (string)$job->title) {
            $overrides['slug'] = $job->generateSlug((string)$data['title']);
        }
        if ($locations !== null) {
            $overrides['locations'] = !empty($locations) ? json_encode($locations, JSON_UNESCAPED_UNICODE) : null;
        }

        $job->fill($this->buildJobAttributes($employerId, array_merge($job->attributes, $data), $employer, $overrides));

        try {
            if (!$job->save()) {
                return ['success' => false, 'message' => 'Failed to update job', 'code' => 500];
            }

            if ($locations !== null) {
                $this->replaceLocations($jobId, $locations);
            }

            return ['success' => true, 'job' => $this->formatJob(Job::find($jobId) ?: $job)];
        } catch (\Throwable $e) {
            error_log('API Employer Job update failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update job', 'code' => 500];
        }
    }

    public function deleteJob(int $employerId, int $jobId): array
    {
        $job = Job::find($jobId);

        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found or you do not have permission to delete it', 'code' => 404];
        }

        if ($job->delete()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete job', 'code' => 500];
        }
    }

    public function duplicateJob(int $employerId, int $jobId): array
    {
        $job = Job::find($jobId);
        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found', 'code' => 404];
        }

        $data = $job->attributes;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['views']);
        $data['title'] = trim((string)$job->title) . ' Copy';
        $data['status'] = 'draft';
        $data['locations'] = $this->formatLocations($job);

        return $this->createJob($employerId, $data);
    }

    public function setStatus(int $employerId, int $jobId, string $status): array
    {
        $job = Job::find($jobId);
        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found', 'code' => 404];
        }

        $job->status = $this->normalizeStatus($status);
        $job->publish_at = $job->status === 'published' ? date('Y-m-d H:i:s') : ($job->publish_at ?? null);

        try {
            $job->save();
            return ['success' => true, 'job' => $this->formatJob(Job::find($jobId) ?: $job)];
        } catch (\Throwable $e) {
            error_log('API Employer Job status update failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update job status', 'code' => 500];
        }
    }

    private function validateJobData(array $data, bool $creating = true): array
    {
        $errors = [];
        if ($creating || array_key_exists('title', $data)) {
            if (trim((string)($data['title'] ?? '')) === '') {
                $errors['title'] = 'Title is required';
            }
        }
        if ($creating || array_key_exists('description', $data)) {
            if (trim(strip_tags((string)($data['description'] ?? ''))) === '') {
                $errors['description'] = 'Description is required';
            }
        }
        return $errors;
    }

    private function buildJobAttributes(int $employerId, array $data, ?Employer $employer, array $overrides = []): array
    {
        $description = (string)($data['description'] ?? '');
        $shortDescription = (string)($data['short_description'] ?? strip_tags($description));

        $attrs = [
            'employer_id' => $employerId,
            'title' => $data['title'] ?? '',
            'description' => $description,
            'short_description' => substr($shortDescription, 0, 1000),
            'employment_type' => $data['employment_type'] ?? $data['job_type'] ?? 'full_time',
            'seniority' => $data['seniority'] ?? 'mid',
            'salary_min' => $this->nullableInt($data['salary_min'] ?? null),
            'salary_max' => $this->nullableInt($data['salary_max'] ?? null),
            'currency' => $data['currency'] ?? $data['salary_currency'] ?? 'INR',
            'pay_type' => $data['pay_type'] ?? 'range',
            'pay_frequency' => $data['pay_frequency'] ?? $data['salary_frequency'] ?? 'monthly',
            'pay_fixed_amount' => $this->nullableInt($data['pay_fixed_amount'] ?? null),
            'hours_per_week' => $this->nullableInt($data['hours_per_week'] ?? null),
            'shift' => $data['shift'] ?? null,
            'contract_length' => $this->nullableInt($data['contract_length'] ?? null),
            'contract_period' => $data['contract_period'] ?? null,
            'commission_percent' => $data['commission_percent'] ?? null,
            'incentive_rules' => $data['incentive_rules'] ?? null,
            'stipend' => $this->nullableInt($data['stipend'] ?? null),
            'internship_length' => $this->nullableInt($data['internship_length'] ?? null),
            'season_duration' => $data['season_duration'] ?? null,
            'flexible_hours' => !empty($data['flexible_hours']) ? 1 : 0,
            'remote_policy' => $data['remote_policy'] ?? null,
            'remote_tools' => $data['remote_tools'] ?? null,
            'is_remote' => !empty($data['is_remote']) ? 1 : 0,
            'qualifications' => isset($data['qualifications']) ? (is_string($data['qualifications']) ? $data['qualifications'] : json_encode($data['qualifications'], JSON_UNESCAPED_UNICODE)) : null,
            'status' => $this->normalizeStatus($data['status'] ?? 'published'),
            'visibility' => $data['visibility'] ?? 'public',
            'publish_at' => $data['publish_at'] ?? date('Y-m-d H:i:s'),
            'expires_at' => $data['expires_at'] ?? null,
            'vacancies' => $this->nullableInt($data['vacancies'] ?? ($data['openings'] ?? 1)) ?: 1,
            'job_timings' => $data['job_timings'] ?? null,
            'interview_timings' => $data['interview_timings'] ?? null,
            'job_address' => $data['job_address'] ?? $data['address'] ?? null,
            'experience_type' => $data['experience_type'] ?? 'any',
            'min_experience' => $this->nullableInt($data['min_experience'] ?? null),
            'max_experience' => $this->nullableInt($data['max_experience'] ?? null),
            'offers_bonus' => $data['offers_bonus'] ?? 'no',
            'call_availability' => $data['call_availability'] ?? 'everyday',
            'company_name' => $data['company_name'] ?? ($employer->company_name ?? null),
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'contact_profile' => $data['contact_profile'] ?? null,
            'company_size' => $data['company_size'] ?? ($employer->size ?? null),
            'hiring_urgency' => $data['hiring_urgency'] ?? 'immediate',
            'language' => $data['language'] ?? 'English',
            'category' => $data['category'] ?? ($employer->industry ?? null),
        ];

        return array_merge($attrs, $overrides);
    }

    private function normalizeLocations(array $data): array
    {
        $raw = $data['locations'] ?? ($data['location'] ?? []);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [['city' => $raw]];
        }
        if (!is_array($raw)) {
            return [];
        }
        if (isset($raw['city']) || isset($raw['state']) || isset($raw['country'])) {
            $raw = [$raw];
        }

        $locations = [];
        foreach ($raw as $loc) {
            if (!is_array($loc)) {
                continue;
            }
            $city = trim((string)($loc['city'] ?? ''));
            $state = trim((string)($loc['state'] ?? ''));
            $country = trim((string)($loc['country'] ?? 'India'));
            if ($city === '' && $state === '' && $country === '') {
                continue;
            }
            $locations[] = [
                'city' => $city,
                'state' => $state,
                'country' => $country !== '' ? $country : 'India',
                'latitude' => $loc['latitude'] ?? null,
                'longitude' => $loc['longitude'] ?? null,
            ];
        }

        return $locations;
    }

    private function replaceLocations(int $jobId, array $locations): void
    {
        $this->db->execute('DELETE FROM job_locations WHERE job_id = :job_id', ['job_id' => $jobId]);

        foreach ($locations as $loc) {
            $location = new JobLocation();
            $location->fill([
                'job_id' => $jobId,
                'city' => $loc['city'] ?: null,
                'state' => $loc['state'] ?: null,
                'country' => $loc['country'] ?: 'India',
                'latitude' => $loc['latitude'] ?? null,
                'longitude' => $loc['longitude'] ?? null,
            ]);
            $location->save();
        }
    }

    private function formatJob(Job $job): array
    {
        $data = $job->toArray();
        $data['locations'] = $this->formatLocations($job);
        $data['location_display'] = $this->locationDisplay($data['locations'], (bool)($data['is_remote'] ?? false));
        $data['is_published'] = ($data['status'] ?? '') === 'published';
        return $data;
    }

    private function formatLocations(Job $job): array
    {
        $locations = [];
        foreach ($job->locations() as $loc) {
            $row = is_object($loc) ? $loc->toArray() : (array)$loc;
            $locations[] = [
                'city' => $row['city'] ?? '',
                'state' => $row['state'] ?? '',
                'country' => $row['country'] ?? '',
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
            ];
        }
        return $locations;
    }

    private function locationDisplay(array $locations, bool $remote): string
    {
        if (empty($locations)) {
            return $remote ? 'Remote' : 'Location not specified';
        }

        $parts = array_map(function (array $loc): string {
            return implode(', ', array_filter([$loc['city'] ?? '', $loc['state'] ?? '', $loc['country'] ?? '']));
        }, $locations);

        $parts = array_values(array_filter(array_unique($parts)));
        return !empty($parts) ? implode(' | ', $parts) : ($remote ? 'Remote' : 'Location not specified');
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['draft', 'published', 'paused', 'closed', 'archived'], true) ? $status : 'published';
    }

    private function nullableInt($value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }
}
