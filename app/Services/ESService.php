<?php

declare(strict_types=1);

namespace App\Services;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use App\Models\Job;
use App\Models\Application;

class ESService
{
    private Client $client;
    private string $indexPrefix;

    public function __construct()
    {
        $hosts = [
            [
                'host' => $_ENV['ES_HOST'] ?? 'localhost',
                'port' => (int)($_ENV['ES_PORT'] ?? 9200),
                'scheme' => 'http'
            ]
        ];

        $clientBuilder = ClientBuilder::create()->setHosts($hosts);

        if ($_ENV['ES_USERNAME'] ?? null) {
            $clientBuilder->setBasicAuthentication(
                $_ENV['ES_USERNAME'],
                $_ENV['ES_PASSWORD'] ?? ''
            );
        }

        $this->client = $clientBuilder->build();
        $this->indexPrefix = $_ENV['ES_INDEX_PREFIX'] ?? 'jobportal';
    }

    public function indexJob(int $jobId): bool
    {
        $job = Job::find($jobId);
        if (!$job) {
            return false;
        }

        $employer = $job->employer();
        $skills = $job->skills();
        $locations = $job->locations();

        $document = [
            'index' => $this->getJobsIndex(),
            'id' => $jobId,
            'body' => [
                'job_id' => $jobId,
                'employer_id' => $job->employer_id,
                'title' => $job->title,
                'description' => $job->description,
                'short_description' => $job->short_description,
                'employment_type' => $job->employment_type,
                'seniority' => $job->seniority,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'currency' => $job->currency,
                'is_remote' => (bool)$job->is_remote,
                'status' => $job->status,
                'publish_at' => $job->publish_at,
                'expires_at' => $job->expires_at,
                'vacancies' => $job->vacancies,
                'company_name' => $employer->company_name ?? '',
                'company_slug' => $employer->company_slug ?? '',
                'skills' => array_column($skills, 'name'),
                'skill_ids' => array_column($skills, 'id'),
                'locations' => array_map(fn($loc) => [
                    'city' => $loc->city,
                    'state' => $loc->state,
                    'country' => $loc->country,
                    'coordinates' => $loc->latitude && $loc->longitude ? [
                        'lat' => (float)$loc->latitude,
                        'lon' => (float)$loc->longitude
                    ] : null
                ], $locations),
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
            ]
        ];

        try {
            $this->client->index($document);
            return true;
        } catch (\Exception $e) {
            error_log("ES indexing error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteJob(int $jobId): bool
    {
        try {
            $this->client->delete([
                'index' => $this->getJobsIndex(),
                'id' => $jobId
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function searchJobs(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = ['match_all' => (object)[]];

        if (!empty($filters['q'])) {
            $query = [
                'multi_match' => [
                    'query' => $filters['q'],
                    'fields' => ['title^3', 'description', 'company_name^2', 'skills'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        $must = [$query];
        $filters_es = [];

        if (!empty($filters['employment_type'])) {
            $filters_es[] = ['term' => ['employment_type' => $filters['employment_type']]];
        }

        if (!empty($filters['is_remote'])) {
            $filters_es[] = ['term' => ['is_remote' => (bool)$filters['is_remote']]];
        }

        if (!empty($filters['location'])) {
            $filters_es[] = ['match' => ['locations.country' => $filters['location']]];
        }

        if (!empty($filters['salary_min'])) {
            $filters_es[] = ['range' => ['salary_max' => ['gte' => (int)$filters['salary_min']]]];
        }

        $body = [
            'query' => [
                'bool' => [
                    'must' => $must,
                    'filter' => $filters_es
                ]
            ],
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'sort' => [
                ['created_at' => ['order' => 'desc']]
            ]
        ];

        try {
            $response = $this->client->search([
                'index' => $this->getJobsIndex(),
                'body' => $body
            ]);

            return [
                'data' => array_map(fn($hit) => $hit['_source'], $response['hits']['hits']),
                'total' => $response['hits']['total']['value'],
                'page' => $page,
                'per_page' => $perPage
            ];
        } catch (\Exception $e) {
            error_log("ES search error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }
    }

    public function indexResume(int $applicationId): bool
    {
        $application = Application::find($applicationId);
        if (!$application) {
            return false;
        }

        $candidate = $application->candidate();
        $job = $application->job();

        $document = [
            'index' => $this->getResumesIndex(),
            'id' => $applicationId,
            'body' => [
                'application_id' => $applicationId,
                'job_id' => $application->job_id,
                'employer_id' => $job->employer_id,
                'candidate_user_id' => $application->candidate_user_id,
                'candidate_email' => $candidate->email ?? '',
                'resume_url' => $application->resume_url,
                'cover_letter' => $application->cover_letter,
                'expected_salary' => $application->expected_salary,
                'status' => $application->status,
                'applied_at' => $application->applied_at,
            ]
        ];

        // Parse resume if URL exists
        if ($application->resume_url) {
            $parser = new ResumeParserService();
            $parsed = $parser->parse($application->resume_url);
            if ($parsed) {
                $document['body']['parsed_resume'] = $parsed;
            }
        }

        try {
            $this->client->index($document);
            return true;
        } catch (\Exception $e) {
            error_log("ES resume indexing error: " . $e->getMessage());
            return false;
        }
    }

    public function searchResumes(string $query, array $filters = []): array
    {
        $must = [
            [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['cover_letter^2', 'parsed_resume.skills', 'parsed_resume.experience', 'candidate_email'],
                    'fuzziness' => 'AUTO'
                ]
            ]
        ];

        $filters_es = [];
        if (!empty($filters['employer_id'])) {
            $filters_es[] = ['term' => ['employer_id' => (int)$filters['employer_id']]];
        }

        if (!empty($filters['job_id'])) {
            $filters_es[] = ['term' => ['job_id' => (int)$filters['job_id']]];
        }

        $body = [
            'query' => [
                'bool' => [
                    'must' => $must,
                    'filter' => $filters_es
                ]
            ],
            'size' => 50
        ];

        try {
            $response = $this->client->search([
                'index' => $this->getResumesIndex(),
                'body' => $body
            ]);

            return array_map(fn($hit) => $hit['_source'], $response['hits']['hits']);
        } catch (\Exception $e) {
            error_log("ES resume search error: " . $e->getMessage());
            return [];
        }
    }

    public function createIndices(): void
    {
        $this->createJobsIndex();
        $this->createResumesIndex();
    }

    private function createJobsIndex(): void
    {
        $index = $this->getJobsIndex();
        
        if ($this->client->indices()->exists(['index' => $index])) {
            return;
        }

        $mapping = [
            'index' => $index,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'job_id' => ['type' => 'long'],
                        'employer_id' => ['type' => 'long'],
                        'title' => ['type' => 'text', 'analyzer' => 'standard'],
                        'description' => ['type' => 'text', 'analyzer' => 'standard'],
                        'short_description' => ['type' => 'text'],
                        'employment_type' => ['type' => 'keyword'],
                        'seniority' => ['type' => 'keyword'],
                        'salary_min' => ['type' => 'integer'],
                        'salary_max' => ['type' => 'integer'],
                        'currency' => ['type' => 'keyword'],
                        'is_remote' => ['type' => 'boolean'],
                        'status' => ['type' => 'keyword'],
                        'company_name' => ['type' => 'text'],
                        'company_slug' => ['type' => 'keyword'],
                        'skills' => ['type' => 'keyword'],
                        'skill_ids' => ['type' => 'long'],
                        'locations' => [
                            'type' => 'nested',
                            'properties' => [
                                'city' => ['type' => 'keyword'],
                                'state' => ['type' => 'keyword'],
                                'country' => ['type' => 'keyword'],
                                'coordinates' => ['type' => 'geo_point']
                            ]
                        ],
                        'publish_at' => ['type' => 'date'],
                        'expires_at' => ['type' => 'date'],
                        'created_at' => ['type' => 'date'],
                        'updated_at' => ['type' => 'date'],
                    ]
                ]
            ]
        ];

        $this->client->indices()->create($mapping);
    }

    private function createResumesIndex(): void
    {
        $index = $this->getResumesIndex();
        
        if ($this->client->indices()->exists(['index' => $index])) {
            return;
        }

        $mapping = [
            'index' => $index,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'application_id' => ['type' => 'long'],
                        'job_id' => ['type' => 'long'],
                        'employer_id' => ['type' => 'long'],
                        'candidate_user_id' => ['type' => 'long'],
                        'candidate_email' => ['type' => 'keyword'],
                        'resume_url' => ['type' => 'keyword'],
                        'cover_letter' => ['type' => 'text', 'analyzer' => 'standard'],
                        'expected_salary' => ['type' => 'integer'],
                        'status' => ['type' => 'keyword'],
                        'parsed_resume' => [
                            'type' => 'object',
                            'properties' => [
                                'skills' => ['type' => 'keyword'],
                                'experience' => ['type' => 'text'],
                                'education' => ['type' => 'text'],
                            ]
                        ],
                        'applied_at' => ['type' => 'date'],
                    ]
                ]
            ]
        ];

        $this->client->indices()->create($mapping);
    }

    private function getJobsIndex(): string
    {
        return $this->indexPrefix . '_jobs_v1';
    }

    private function getResumesIndex(): string
    {
        return $this->indexPrefix . '_resumes_v1';
    }
}

