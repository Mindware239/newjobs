<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Job;
use App\Models\JobLocation;
use App\Models\Skill;
use App\Models\EmployerSubscription;
use App\Services\NotificationService;
use App\Services\ESService;
use App\Services\JobApprovalService;
use App\Services\AIJobDescriptionService;
use App\Core\Storage;
use App\Core\RedisClient;
use App\Services\JobMatchService;

class JobsController extends BaseController
{
    private array $langCodes = [
        'Arabic' => 'ar','Chinese' => 'zh','Czech' => 'cs','Danish' => 'da','Dutch' => 'nl','English' => 'en','Finnish' => 'fi',
        'French' => 'fr','German' => 'de','Greek' => 'el','Hebrew' => 'he','Hindi' => 'hi','Hungarian' => 'hu','Indonesian' => 'id',
        'Italian' => 'it','Japanese' => 'ja','Korean' => 'ko','Norwegian' => 'no','Polish' => 'pl','Portuguese' => 'pt','Romanian' => 'ro',
        'Russian' => 'ru','Spanish' => 'es','Swedish' => 'sv','Thai' => 'th','Turkish' => 'tr','Ukrainian' => 'uk','Vietnamese' => 'vi'
    ];

    private function translateText(string $text, string $targetCode): string
    {
        if ($text === '' || $targetCode === 'en') return $text;
        $payload = json_encode(['q' => $text, 'source' => 'auto', 'target' => $targetCode, 'format' => 'text']);
        $endpoints = [
            'http://127.0.0.1:5000/translate',
            'http://localhost:5000/translate',
            'https://libretranslate.com/translate',
            'https://libretranslate.de/translate',
            'https://translate.mentality.rip/translate'
        ];
        foreach ($endpoints as $ep) {
            try {
                if (function_exists('curl_init')) {
                    $ch = \curl_init($ep);
                    $isHttps = strpos($ep, 'https://') === 0;
                    \curl_setopt_array($ch, [
                        CURLOPT_POST => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_SSL_VERIFYPEER => $isHttps ? false : true,
                        CURLOPT_SSL_VERIFYHOST => $isHttps ? 0 : 2,
                    ]);
                    $res = \curl_exec($ch);
                    $code = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $err = \curl_error($ch);
                    // curl_close is not needed in PHP 8.0+ as CurlHandle is automatically closed
                    if (PHP_VERSION_ID < 80000) {
                        @\curl_close($ch);
                    }
                    if ($res && $code === 200) {
                        $json = json_decode($res, true);
                        $t = $json['translatedText'] ?? '';
                        if (is_string($t) && $t !== '') { return $t; }
                    } else {
                        error_log("Translation endpoint failed ({$ep}): code={$code} err={$err}");
                    }
                } else {
                    $opts = [
                        'http' => [
                            'method' => 'POST',
                            'header' => "Content-Type: application/json\r\n",
                            'content' => $payload,
                            'timeout' => 10,
                        ]
                    ];
                    $context = stream_context_create($opts);
                    $res = @file_get_contents($ep, false, $context);
                    if ($res !== false) {
                        $json = json_decode($res, true);
                        $t = $json['translatedText'] ?? '';
                        if (is_string($t) && $t !== '') { return $t; }
                    } else {
                        error_log("Translation endpoint failed ({$ep}) using file_get_contents");
                    }
                }
            } catch (\Throwable $t) {
                error_log('Translation error: ' . $t->getMessage());
            }
        }
        return $text;
    }
    public function create(Request $request, Response $response): void
    {
        // Check if user is logged in
        if (!$this->currentUser) {
            $response->redirect('/login?redirect=/employer/jobs/create');
            return;
        }

        // Check if user is employer
        if (!$this->currentUser->isEmployer()) {
            $response->view('auth/register-employer', [
                'title' => 'Register as Employer',
                'message' => 'Please register as an employer to post jobs'
            ]);
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('auth/register-employer', [
                'title' => 'Complete Registration',
                'message' => 'Please complete your employer profile'
            ]);
            return;
        }

        // Check KYC status
        if (!$employer->isKycApproved()) {
            $response->view('employer/kyc-pending', [
                'title' => 'KYC Pending',
                'employer' => $employer,
                'message' => 'Your KYC documents are under review. You can post jobs once approved.'
            ]);
            return;
        }

        // Check if employer has posted any jobs before
        $postedJobsCount = Job::where('employer_id', '=', $employer->id)->count();
        // Check if free job was already consumed (persistent, even if job deleted)
        $hasConsumedFree = false;
        try {
            $row = \App\Core\Database::getInstance()->fetchOne(
                "SELECT id FROM subscription_usage_logs WHERE employer_id = :eid AND action_type = 'free_job_used' LIMIT 1",
                ['eid' => (int)$employer->id]
            );
            $hasConsumedFree = $row !== null;
        } catch (\Throwable $t) {}
        
        // After first job OR once free job consumed, subscription is required
        if ($postedJobsCount > 0 || $hasConsumedFree) {
            $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
            
            if (!$subscription) {
                // No subscription - redirect to plans (hide free plan)
                $_SESSION['upgrade_message'] = 'You have used your free job posting. Please subscribe to a plan to post more jobs.';
                $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                return;
            }

            // Verify subscription is actually active (not expired)
            if (!$subscription->isActive() && !$subscription->isInGracePeriod()) {
                $_SESSION['upgrade_message'] = 'Your subscription has expired. Please renew your subscription to post more jobs.';
                $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                return;
            }

            // Verify subscription is actually active (not expired)
            if (!$subscription->isActive() && !$subscription->isInGracePeriod()) {
                $_SESSION['upgrade_message'] = 'Your subscription has expired. Please renew your subscription to post more jobs.';
                $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                return;
            }

            // Check if can post more jobs
            if (!$subscription->canUseFeature('max_job_posts')) {
                $plan = $subscription->plan();
                $used = (int)($subscription->attributes['job_posts_used'] ?? 0);
                $limit = $plan ? $plan->getLimit('max_job_posts') : 1;
                $_SESSION['upgrade_message'] = "You have reached your job posting limit ({$used}/{$limit}). Please upgrade your plan to post more jobs.";
                $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                return;
            }
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        // Get all available benefits for job perks section
        try {
            $db = \App\Core\Database::getInstance();
            $benefits = $db->fetchAll("SELECT * FROM benefits ORDER BY name ASC");
        } catch (\Exception $e) {
            error_log("JobsController::create - Failed to load benefits: " . $e->getMessage());
            $benefits = [];
        }

        // Check subscription and job posting limits for display
        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        $subscriptionInfo = null;
        if ($subscription) {
            $plan = $subscription->plan();
            $used = (int)($subscription->attributes['job_posts_used'] ?? 0);
            $limit = $plan ? $plan->getLimit('max_job_posts') : 0;
            $subscriptionInfo = [
                'plan_name' => $plan ? $plan->attributes['name'] : 'Free',
                'used' => $used,
                'limit' => $limit,
                'can_post' => $subscription->canUseFeature('max_job_posts'),
                'remaining' => max(0, $limit - $used)
            ];
        } else {
            // No subscription - show free plan info (first job only)
            $subscriptionInfo = [
                'plan_name' => 'Free',
                'used' => $hasConsumedFree ? 1 : $postedJobsCount,
                'limit' => 1,
                'can_post' => !$hasConsumedFree && $postedJobsCount === 0,
                'remaining' => max(0, 1 - ($hasConsumedFree ? 1 : $postedJobsCount))
            ];
        }

        $response->view('employer/post-job', [
            'title' => 'Post a Job',
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'subscription' => $subscriptionInfo,
            'benefits' => $benefits,
            'jobBenefits' => []
        ], 200, 'employer/layout');
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            error_log("JobsController::index - Employer profile not found for user ID: " . ($this->currentUser->id ?? 'N/A'));
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found. Please complete your registration.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get filters
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $location = $request->get('location', '');

        // Build query
        $query = Job::where('employer_id', '=', $employer->id);
        
        if ($status !== 'all') {
            $query = $query->where('status', '=', $status);
        }
        
        if ($search) {
            $query = $query->where('title', 'LIKE', "%$search%");
        }

        $jobs = $query->orderBy('created_at', 'DESC')->get();

        // Get application counts and locations for each job
        $jobsWithStats = [];
        foreach ($jobs as $job) {
            $jobArray = $job->toArray();
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            
            // Get application counts
            $jobArray['applications_count'] = \App\Models\Application::where('job_id', '=', $jobId)->count();
            $jobArray['new_applications_count'] = \App\Models\Application::where('job_id', '=', $jobId)
                ->where('status', '=', 'applied')->count();
            $jobArray['shortlisted_count'] = \App\Models\Application::where('job_id', '=', $jobId)
                ->where('status', '=', 'shortlisted')->count();
            
            // Get job locations directly from database
            $jobLocations = [];
            if ($jobId) {
                try {
                    $db = \App\Core\Database::getInstance();
                    $locationRows = $db->fetchAll(
                        "SELECT 
                            COALESCE(c.name, jl.city) as city, 
                            COALESCE(s.name, jl.state) as state, 
                            COALESCE(co.name, jl.country) as country
                         FROM job_locations jl
                         LEFT JOIN cities c ON jl.city_id = c.id
                         LEFT JOIN states s ON jl.state_id = s.id
                         LEFT JOIN countries co ON jl.country_id = co.id
                         WHERE jl.job_id = :job_id",
                        ['job_id' => $jobId]
                    );
                    
                    foreach ($locationRows as $locRow) {
                        $locParts = array_filter([
                            trim($locRow['city'] ?? ''),
                            trim($locRow['state'] ?? ''),
                            trim($locRow['country'] ?? '')
                        ]);
                        if (!empty($locParts)) {
                            $jobLocations[] = implode(', ', $locParts);
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Error getting job locations for job ID {$jobId}: " . $e->getMessage());
                }
            }
            
            // Format location display
            if (!empty($jobLocations)) {
                $jobArray['location'] = implode(' | ', $jobLocations);
                $jobArray['location_display'] = implode(' | ', $jobLocations);
            } else {
                // Fallback to jobs.locations if JSON
                if (!empty($jobArray['locations'])) {
                    $locationsJson = json_decode($jobArray['locations'], true);
                    if (is_array($locationsJson)) {
                        $locStrings = [];
                        foreach ($locationsJson as $loc) {
                            if (is_string($loc)) {
                                $locStrings[] = $loc;
                            } elseif (is_array($loc)) {
                                $locParts = array_filter([
                                    $loc['city'] ?? '',
                                    $loc['state'] ?? '',
                                    $loc['country'] ?? ''
                                ]);
                                if (!empty($locParts)) {
                                    $locStrings[] = implode(', ', $locParts);
                                }
                            }
                        }
                        $jobArray['location'] = !empty($locStrings) ? implode(' | ', $locStrings) : 'Location not specified';
                        $jobArray['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : 'Location not specified';
                    } else {
                        $rawLoc = trim((string)$jobArray['locations']);
                        if ($rawLoc !== '') {
                            $jobArray['location'] = $rawLoc;
                            $jobArray['location_display'] = $rawLoc;
                        } else {
                            // If locations is empty string, try job_address before employer fallback
                            $addr = trim((string)($jobArray['job_address'] ?? ''));
                            if ($addr !== '') {
                                $jobArray['location'] = $addr;
                                $jobArray['location_display'] = $addr;
                            } else {
                                // Do not fallback to employer profile location
                                $jobArray['location'] = 'Location not specified';
                                $jobArray['location_display'] = 'Location not specified';
                            }
                        }
                    }
                } else {
                    // locations JSON missing entirely: try job_address
                    $addr = trim((string)($jobArray['job_address'] ?? ''));
                    if ($addr !== '') {
                        $jobArray['location'] = $addr;
                        $jobArray['location_display'] = $addr;
                    } else {
                        $jobArray['location'] = 'Location not specified';
                        $jobArray['location_display'] = 'Location not specified';
                    }
                }
            }
            
            // Ensure slug is included (fallback to ID if slug is missing)
            if (empty($jobArray['slug']) && !empty($jobArray['title'])) {
                $jobArray['slug'] = $job->generateSlug($jobArray['title']);
            }
            
            $jobsWithStats[] = $jobArray;
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        // Get subscription info for display
        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        $subscriptionInfo = [
            'name' => 'Free Plan',
            'status' => 'active',
            'isActive' => true,
            'expiry' => null
        ];

        if ($subscription) {
            $plan = $subscription->plan();
            $subscriptionInfo = [
                'name' => $plan ? $plan->name : 'Unknown Plan',
                'status' => $subscription->attributes['status'] ?? 'unknown',
                'isActive' => $subscription->isActive(),
                'expiry' => $subscription->attributes['expires_at'] ?? null
            ];
        }

        $response->view('employer/jobs/index', [
            'title' => 'Jobs',
            'jobs' => $jobsWithStats,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'location' => $location
            ],
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'subscription' => $subscriptionInfo
        ], 200, 'employer/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::findBySlug($slug);
        if (!$job && ctype_digit($slug)) {
            $job = Job::find((int)$slug);
        }
        $employer = $this->currentUser->employer();

        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/employer/jobs');
            } else {
                $response->json(['error' => 'Job not found'], 404);
            }
            return;
        }

        // Get applications for this job
        $applications = \App\Models\Application::where('job_id', '=', $job->id)->get();
        $applicationsCount = count($applications);
        $newApplicationsCount = \App\Models\Application::where('job_id', '=', $job->id)
            ->where('status', '=', 'pending')->count();

        // Get job locations and skills
        $locations = $job->locations();
        /** @var \App\Models\Job $job */
        $skills = $job->skills(); // Returns array of arrays from raw SQL

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $response->view('employer/jobs/show', [
            'title' => $job->title,
            'job' => $job->toArray(),
            'applications' => array_map(fn($a) => $a->toArray(), $applications),
            'applicationsCount' => $applicationsCount,
            'newApplicationsCount' => $newApplicationsCount,
            'locations' => array_map(fn($l) => is_object($l) ? $l->toArray() : (is_array($l) ? $l : []), $locations),
            'skills' => $skills, // Already an array of arrays
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function previewPublic(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::findBySlug($slug);
        if (!$job && ctype_digit($slug)) {
            $job = Job::find((int)$slug);
        }
        $employer = $this->currentUser->employer();

        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->redirect('/employer/jobs');
            return;
        }

        // Get job details
        $locations = $job->locations();
        $skills = $job->skills();
        $jobEmployer = $job->employer();
        
        // Fallback to current employer if job employer is null
        if (!$jobEmployer) {
            $jobEmployer = $employer;
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $response->view('employer/jobs/public-preview', [
            'title' => 'Preview: ' . $job->title,
            'job' => $job->toArray(),
            'employer' => $employer,
            'jobEmployer' => $jobEmployer,
            'locations' => array_map(fn($l) => $l->toArray(), $locations),
            'skills' => $skills,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function edit(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::findBySlug($slug);
        if (!$job && ctype_digit($slug)) {
            $job = Job::find((int)$slug);
        }
        $employer = $this->currentUser->employer();
        $user = $this->currentUser;
        
        $id = $job->attributes['id'] ?? 0;

        if (!$job || $job->employer_id !== $employer->id) {
            error_log("Edit Job - Job not found or unauthorized. Job ID: {$id}, Employer ID: " . ($employer->id ?? 'N/A'));
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/employer/jobs');
            } else {
                $response->json(['error' => 'Job not found'], 404);
            }
            return;
        }

        // Get job locations and skills
        $jobId = $job->attributes['id'] ?? $job->id ?? null;
        error_log("Edit Job - Job found. ID from attributes: " . ($job->attributes['id'] ?? 'NOT SET') . ", ID from property: " . ($job->id ?? 'NOT SET'));
        
        // Load human-readable location names for the edit wizard
        $locations = [];
        if ($jobId) {
            try {
                $db = \App\Core\Database::getInstance();
                $locationRows = $db->fetchAll(
                    "SELECT c.name AS city, s.name AS state, co.name AS country
                     FROM job_locations jl
                     LEFT JOIN cities c ON jl.city_id = c.id
                     LEFT JOIN states s ON jl.state_id = s.id
                     LEFT JOIN countries co ON jl.country_id = co.id
                     WHERE jl.job_id = :job_id",
                    ['job_id' => $jobId]
                );
                foreach ($locationRows as $row) {
                    $locations[] = [
                        'city' => $row['city'] ?? '',
                        'state' => $row['state'] ?? '',
                        'country' => $row['country'] ?? ''
                    ];
                }
            } catch (\Exception $e) {
                error_log("JobsController::edit - Failed to load location names: " . $e->getMessage());
            }
        }
        if (empty($locations)) {
            $rawLocations = $job->locations();
            $locations = array_map(fn($l) => is_object($l) ? $l->toArray() : (is_array($l) ? $l : []), $rawLocations);
        }
        $skillsRaw = $job->skills(); // Returns array of arrays from raw SQL
        
        // Extract skill names properly - skills() returns array of arrays with 'name' key
        $skills = [];
        foreach ($skillsRaw as $skill) {
            if (is_array($skill) && isset($skill['name']) && !empty($skill['name'])) {
                $skills[] = ['name' => $skill['name']];
            } elseif (is_string($skill) && !empty($skill)) {
                $skills[] = ['name' => $skill];
            }
        }
        
        // Debug: Log skills for troubleshooting
        error_log("Edit Job - Job ID: " . $id);
        error_log("Edit Job - Skills raw count: " . count($skillsRaw));
        error_log("Edit Job - Skills processed count: " . count($skills));
        if (!empty($skills)) {
            error_log("Edit Job - First skill: " . json_encode($skills[0]));
        }

        // Get job benefits and all available benefits
        try {
            $db = \App\Core\Database::getInstance();
            $allBenefits = $db->fetchAll("SELECT * FROM benefits ORDER BY name ASC");
        } catch (\Exception $e) {
            error_log("JobsController::edit - Failed to load benefits: " . $e->getMessage());
            $allBenefits = [];
        }

        $jobBenefits = [];
        try {
            $jobBenefits = $job->benefits();
        } catch (\Exception $e) {
            error_log("JobsController::edit - Failed to load job benefits: " . $e->getMessage());
            $jobBenefits = [];
        }

        // Build enhanced job array with all fields, merging with employer/user data for defaults
        $jobArray = $job->toArray();
        
        // Build formatted description HTML for editor when description is plain text
        $rawDescription = (string)($jobArray['description'] ?? '');
        $jobArray['description_html'] = '';
        if ($rawDescription !== '') {
            if (strip_tags($rawDescription) !== $rawDescription) {
                $jobArray['description_html'] = $rawDescription;
            } else {
                $plain = preg_replace('/[ \t]+/', ' ', $rawDescription);
                $plain = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $plain);
                $plain = trim($plain);

                $desc = preg_replace('/([a-z\)\]\d])(?=[A-Z])/', "$1\n", $plain);
                $lines = preg_split('/\r\n|\r|\n/', $desc);

                $pendingList = [];
                $html = '';

                foreach ($lines as $line) {
                    $t = trim($line);
                    if ($t === '') {
                        continue;
                    }

                    if (preg_match('/^[\-\*•]\s+(.*)$/u', $t, $m)) {
                        $pendingList[] = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                        continue;
                    }

                    if (preg_match('/^\d+[\.)]\s+(.*)$/', $t, $m)) {
                        $pendingList[] = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                        continue;
                    }

                    if (mb_strlen($t) <= 120 && !preg_match('/[\.?!]$/', $t)) {
                        $pendingList[] = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
                        continue;
                    }

                    if (!empty($pendingList)) {
                        $html .= '<ul class="list-disc list-inside space-y-1">';
                        foreach ($pendingList as $li) {
                            $html .= '<li>' . $li . '</li>';
                        }
                        $html .= '</ul>';
                        $pendingList = [];
                    }

                    $html .= '<p class="mb-2">' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</p>';
                }

                if (!empty($pendingList)) {
                    $html .= '<ul class="list-disc list-inside space-y-1">';
                    foreach ($pendingList as $li) {
                        $html .= '<li>' . $li . '</li>';
                    }
                    $html .= '</ul>';
                }

                $jobArray['description_html'] = $html;
            }
        }
        
        // Ensure slug is included (critical for edit form submission)
        if (empty($jobArray['slug']) && !empty($jobArray['title'])) {
            $jobArray['slug'] = $job->generateSlug($jobArray['title']);
        }
        
        // Auto-fill from employer profile if job field is empty
        if (empty($jobArray['company_name']) && !empty($employer->attributes['company_name'])) {
            $jobArray['company_name'] = $employer->attributes['company_name'];
        }
        if (empty($jobArray['company_size']) && !empty($employer->attributes['size'])) {
            $jobArray['company_size'] = $employer->attributes['size'];
        }
        if (empty($jobArray['phone']) && !empty($user->attributes['phone'])) {
            $jobArray['phone'] = $user->attributes['phone'];
        }
        if (empty($jobArray['email']) && !empty($user->attributes['email'])) {
            $jobArray['email'] = $user->attributes['email'];
        }
        
        // Ensure all fields have default values - preserve existing values, only set defaults if missing
        $jobArray['experience_type'] = $jobArray['experience_type'] ?? 'any';
        $jobArray['min_experience'] = $jobArray['min_experience'] ?? null;
        $jobArray['max_experience'] = $jobArray['max_experience'] ?? null;
        $jobArray['offers_bonus'] = $jobArray['offers_bonus'] ?? 'no';
        $jobArray['call_availability'] = $jobArray['call_availability'] ?? 'everyday';
        $jobArray['contact_person'] = $jobArray['contact_person'] ?? '';
        $jobArray['contact_profile'] = $jobArray['contact_profile'] ?? '';
        $jobArray['hiring_urgency'] = $jobArray['hiring_urgency'] ?? 'immediate';
        $jobArray['job_timings'] = $jobArray['job_timings'] ?? '';
        $jobArray['interview_timings'] = $jobArray['interview_timings'] ?? '';
        $jobArray['job_address'] = $jobArray['job_address'] ?? '';
        $jobArray['seniority'] = $jobArray['seniority'] ?? 'mid';
        $jobArray['employment_type'] = $jobArray['employment_type'] ?? 'full_time';
        $jobArray['vacancies'] = $jobArray['vacancies'] ?? 1;
        
        // Ensure pay-related fields are set
        $jobArray['pay_type'] = $jobArray['pay_type'] ?? 'range';
        $jobArray['pay_frequency'] = $jobArray['pay_frequency'] ?? 'monthly';
        $jobArray['pay_fixed_amount'] = $jobArray['pay_fixed_amount'] ?? null;
        $jobArray['hours_per_week'] = $jobArray['hours_per_week'] ?? null;
        $jobArray['shift'] = $jobArray['shift'] ?? null;
        $jobArray['contract_length'] = $jobArray['contract_length'] ?? null;
        $jobArray['contract_period'] = $jobArray['contract_period'] ?? null;
        $jobArray['commission_percent'] = $jobArray['commission_percent'] ?? null;
        $jobArray['incentive_rules'] = $jobArray['incentive_rules'] ?? null;
        $jobArray['stipend'] = $jobArray['stipend'] ?? null;
        $jobArray['internship_length'] = $jobArray['internship_length'] ?? null;
        $jobArray['season_duration'] = $jobArray['season_duration'] ?? null;
        $jobArray['flexible_hours'] = $jobArray['flexible_hours'] ?? 0;
        $jobArray['remote_policy'] = $jobArray['remote_policy'] ?? null;
        $jobArray['remote_tools'] = $jobArray['remote_tools'] ?? null;
        $jobArray['is_remote'] = $jobArray['is_remote'] ?? 0;
        $jobArray['language'] = $jobArray['language'] ?? 'English';
        $jobArray['currency'] = $jobArray['currency'] ?? 'INR';
        $jobArray['salary_min'] = $jobArray['salary_min'] ?? null;
        $jobArray['salary_max'] = $jobArray['salary_max'] ?? null;
        
        // Set category from job or employer industry - ensure it matches job_categories.name exactly
        if (empty($jobArray['category']) && !empty($employer->attributes['industry'])) {
            $jobArray['category'] = $employer->attributes['industry'];
        }
        $jobArray['category'] = $jobArray['category'] ?? '';
        
        // Log for debugging
        error_log("Edit Job - Category: " . ($jobArray['category'] ?? 'NOT SET'));
        error_log("Edit Job - Pay Type: " . ($jobArray['pay_type'] ?? 'NOT SET'));
        error_log("Edit Job - Stipend: " . ($jobArray['stipend'] ?? 'NOT SET'));

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? \App\Models\Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $response->view('employer/post-job', [
            'title' => 'Edit Job - ' . $job->title,
            'job' => $jobArray, // Enhanced job array with all fields and defaults
            'locations' => $locations,
            'skills' => $skills, // Already an array of arrays
            'employer' => $employer,
            'user' => $user,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'isEdit' => true,
            'benefits' => $allBenefits,
            'jobBenefits' => $jobBenefits
        ], 200, 'employer/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        // Check if employer has posted any jobs before
        $postedJobsCount = Job::where('employer_id', '=', $employer->id)->count();
        // Persistent free job consumption flag
        $hasConsumedFree = false;
        try {
            $row = \App\Core\Database::getInstance()->fetchOne(
                "SELECT id FROM subscription_usage_logs WHERE employer_id = :eid AND action_type = 'free_job_used' LIMIT 1",
                ['eid' => (int)$employer->id]
            );
            $hasConsumedFree = $row !== null;
        } catch (\Throwable $t) {}
        
        // First job is free only if not consumed and count is zero
        if (!$hasConsumedFree && $postedJobsCount === 0) {
            // Allow first job posting without subscription
            // Continue to job creation below
        } else {
            // After first job, subscription is required
            $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
            
            if (!$subscription) {
                // No subscription - redirect to plans (hide free plan)
                $isAjax = $request->header('Content-Type') === 'application/json' || 
                         strpos($request->header('Accept') ?? '', 'application/json') !== false;
                
                if ($isAjax) {
                    $response->json([
                        'error' => 'Subscription required',
                        'message' => 'You have used your free job posting. Please subscribe to a plan to post more jobs.',
                        'redirect' => '/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1',
                        'subscription_required' => true
                    ], 402);
                } else {
                    $_SESSION['upgrade_message'] = 'You have used your free job posting. Please subscribe to a plan to post more jobs.';
                    $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                }
                return;
            }

            // Verify subscription is actually active (not expired)
            if (!$subscription->isActive() && !$subscription->isInGracePeriod()) {
                $isAjax = $request->header('Content-Type') === 'application/json' || 
                         strpos($request->header('Accept') ?? '', 'application/json') !== false;
                
                if ($isAjax) {
                    $response->json([
                        'error' => 'Subscription expired',
                        'message' => 'Your subscription has expired. Please renew your subscription to post more jobs.',
                        'redirect' => '/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1',
                        'subscription_expired' => true
                    ], 402);
                } else {
                    $_SESSION['upgrade_message'] = 'Your subscription has expired. Please renew your subscription to post more jobs.';
                    $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                }
                return;
            }

            // Check if can post more jobs
            if (!$subscription->canUseFeature('max_job_posts')) {
                $plan = $subscription->plan();
                $used = (int)($subscription->attributes['job_posts_used'] ?? 0);
                $limit = $plan ? $plan->getLimit('max_job_posts') : 1;
                
                $isAjax = $request->header('Content-Type') === 'application/json' || 
                         strpos($request->header('Accept') ?? '', 'application/json') !== false;
                
                if ($isAjax) {
                    $response->json([
                        'error' => 'Job posting limit reached',
                        'message' => "You have reached your job posting limit ({$used}/{$limit}). Please upgrade your plan to post more jobs.",
                        'redirect' => '/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1',
                        'limit_reached' => true,
                        'used' => $used,
                        'limit' => $limit
                    ], 402);
                } else {
                    $_SESSION['upgrade_message'] = "You have reached your job posting limit ({$used}/{$limit}). Please upgrade your plan to post more jobs.";
                    $response->redirect('/employer/subscription/plans?upgrade=1&feature=job_posting&hide_free=1');
                }
                return;
            }
        }

        // Handle both JSON and form data
        $data = $request->getMethod() === 'POST' && $request->header('Content-Type') === 'application/json' 
            ? $request->getJsonBody() 
            : array_merge($request->all(), $request->getJsonBody());

        $job = new Job();
        $title = $data['title'] ?? '';
        
        // Generate slug from title
        $slug = $job->generateSlug($title);
        
        // Auto-fill company info from employer profile if not provided
        $companyName = $data['company_name'] ?? $employer->attributes['company_name'] ?? '';
        $companySize = $data['company_size'] ?? $employer->attributes['size'] ?? '';
        $user = $this->currentUser;
        $phone = $data['phone'] ?? $user->attributes['phone'] ?? '';
        $email = $data['email'] ?? $user->attributes['email'] ?? '';

        // Server-side translation when non-English selected
        $selectedLang = (string)($data['language'] ?? 'English');
        $targetCode = $this->langCodes[$selectedLang] ?? 'en';
        $autoTranslate = (bool)($data['auto_translate'] ?? false);
        if ($targetCode !== 'en' && ($autoTranslate || $selectedLang !== 'English')) {
            if (!empty($title)) { $title = $this->translateText($title, $targetCode); }
            if (!empty($data['short_description'] ?? '')) { $data['short_description'] = $this->translateText((string)$data['short_description'], $targetCode); }
            if (!empty($data['description'] ?? '')) { $data['description'] = $this->translateText(strip_tags((string)$data['description']), $targetCode); }
        }

        // Normalize incoming location for JSON fallback on jobs.locations
        $normalizedLocations = [];
        if (isset($data['location'])) {
            if (is_array($data['location'])) {
                if (isset($data['location'][0]) && is_array($data['location'][0])) {
                    foreach ($data['location'] as $loc) {
                        $normalizedLocations[] = [
                            'city' => $loc['city'] ?? '',
                            'state' => $loc['state'] ?? '',
                            'country' => $loc['country'] ?? ''
                        ];
                    }
                } elseif (isset($data['location']['city']) || isset($data['location']['state']) || isset($data['location']['country'])) {
                    $normalizedLocations[] = [
                        'city' => $data['location']['city'] ?? '',
                        'state' => $data['location']['state'] ?? '',
                        'country' => $data['location']['country'] ?? ''
                    ];
                }
            }
        }
        $locationsJson = !empty($normalizedLocations) ? json_encode($normalizedLocations) : null;

        $job->fill([
            'employer_id' => $employer->id,
            'title' => $title,
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? substr($data['description'] ?? '', 0, 1000),
            'employment_type' => $data['employment_type'] ?? $data['job_type'] ?? 'full_time',
            'seniority' => $data['seniority'] ?? 'mid',
            'salary_min' => !empty($data['salary_min']) ? (int)$data['salary_min'] : null,
            'salary_max' => !empty($data['salary_max']) ? (int)$data['salary_max'] : null,
            'currency' => $data['currency'] ?? $data['salary_currency'] ?? 'INR',
            'pay_type' => $data['pay_type'] ?? 'range',
            'pay_frequency' => $data['pay_frequency'] ?? ($data['salary_frequency'] ?? 'monthly'),
            'pay_fixed_amount' => isset($data['pay_fixed_amount']) && $data['pay_fixed_amount'] !== '' ? (int)$data['pay_fixed_amount'] : null,
            'hours_per_week' => isset($data['hours_per_week']) && $data['hours_per_week'] !== '' ? (int)$data['hours_per_week'] : null,
            'shift' => $data['shift'] ?? null,
            'contract_length' => isset($data['contract_length']) && $data['contract_length'] !== '' ? (int)$data['contract_length'] : null,
            'contract_period' => $data['contract_period'] ?? null,
            'commission_percent' => isset($data['commission_percent']) && $data['commission_percent'] !== '' ? (float)$data['commission_percent'] : null,
            'incentive_rules' => $data['incentive_rules'] ?? null,
            'stipend' => isset($data['stipend']) && $data['stipend'] !== '' ? (int)$data['stipend'] : null,
            'internship_length' => isset($data['internship_length']) && $data['internship_length'] !== '' ? (int)$data['internship_length'] : null,
            'season_duration' => $data['season_duration'] ?? null,
            'flexible_hours' => isset($data['flexible_hours']) ? (int)$data['flexible_hours'] : 0,
            'remote_policy' => $data['remote_policy'] ?? null,
            'remote_tools' => $data['remote_tools'] ?? null,
            'is_remote' => isset($data['is_remote']) ? (int)$data['is_remote'] : 0,
            'status' => $data['status'] ?? 'draft',
            'vacancies' => !empty($data['vacancies']) ? (int)$data['vacancies'] : (!empty($data['openings']) ? (int)$data['openings'] : 1),
            'visibility' => $data['visibility'] ?? 'public',
            'job_timings' => $data['job_timings'] ?? '',
            'interview_timings' => $data['interview_timings'] ?? '',
            'job_address' => $data['job_address'] ?? '',
            'experience_type' => $data['experience_type'] ?? 'any',
            'min_experience' => !empty($data['min_experience']) ? (int)$data['min_experience'] : null,
            'max_experience' => !empty($data['max_experience']) ? (int)$data['max_experience'] : null,
            'offers_bonus' => $data['offers_bonus'] ?? 'no',
            'call_availability' => $data['call_availability'] ?? 'everyday',
            'company_name' => $companyName,
            'contact_person' => $data['contact_person'] ?? '',
            'phone' => $phone,
            'email' => $email,
            'contact_profile' => $data['contact_profile'] ?? '',
            'company_size' => $companySize,
            'hiring_urgency' => $data['hiring_urgency'] ?? 'immediate',
            'language' => $data['language'] ?? 'English',
            'category' => $data['category'] ?? $employer->attributes['industry'] ?? null,
            'locations' => $locationsJson
        ]);

        if ($job->save()) {
            // Increment job post usage
            if (($subscription ?? null)) {
                try { $subscription->incrementUsage('max_job_posts'); } catch (\Throwable $t) {}
            }
            // If free job path was used, mark as consumed for this employer (persist even after deletion)
            if (!$hasConsumedFree) {
                try {
                    \App\Models\SubscriptionUsageLog::logUsage(
                        0,
                        (int)$employer->id,
                        'free_job_used',
                        null,
                        (int)($job->attributes['id'] ?? $job->id ?? 0),
                        null,
                        ['source' => 'first_free']
                    );
                } catch (\Throwable $t) {
                    error_log('JobsController::store - Failed to log free job usage: ' . $t->getMessage());
                }
            }
            // Auto-approval based on employer trust score
            $approvalService = new JobApprovalService();
            $approvalService->handle($job, $employer);
            // Re-fetch job to ensure we have a valid ID before saving children
            $jobRecord = Job::findBySlug($slug) ?: $job;
            $jobId = (int)($jobRecord->attributes['id'] ?? $jobRecord->id ?? 0);
            error_log("Job Store - Verified Job ID after insert: " . ($jobId ?: 'NULL'));

            // Save locations - handle both array format and object format
            if (isset($data['location'])) {
                $locations = [];
                if (is_array($data['location'])) {
                    // Check if it's an array of location objects or a single object
                    if (isset($data['location'][0]) && is_array($data['location'][0])) {
                        // Array of location objects
                        $locations = $data['location'];
                    } elseif (isset($data['location']['city']) || isset($data['location']['state'])) {
                        // Single location object
                        $locations = [$data['location']];
                    }
                }
                
                // Save locations
                if ($jobId > 0) {
                    foreach ($locations as $locData) {
                        if (!empty($locData['city']) || !empty($locData['state']) || !empty($locData['country'])) {
                            try {
                                $location = new JobLocation();
                                $location->fill([
                                    'job_id' => $jobId,
                                    'city' => $locData['city'] ?? null,
                                    'state' => $locData['state'] ?? null,
                                    'country' => $locData['country'] ?? 'India',
                                    'city_id' => null,
                                    'state_id' => null,
                                    'country_id' => null,
                                    'latitude' => $locData['latitude'] ?? null,
                                    'longitude' => $locData['longitude'] ?? null,
                                ]);
                                $location->save();
                            } catch (\Exception $e) {
                                error_log("JobsController::store - Failed to save location for job {$jobId}: " . $e->getMessage());
                            }
                        }
                    }
                } else {
                    error_log("JobsController::store - Skipping location save, invalid job ID");
                }
            }

            // Save skills - delete existing first, then add new ones
            if (isset($data['skills']) && is_array($data['skills'])) {
                if ($jobId) {
                    // Delete existing skills for this job
                    \App\Core\Database::getInstance()->query(
                        "DELETE FROM job_skills WHERE job_id = :job_id",
                        ['job_id' => $jobId]
                    );
                    
                    // Add new skills
                    foreach ($data['skills'] as $skillName) {
                        if (empty(trim($skillName))) continue;
                        
                        $skillName = trim($skillName);
                        $skill = Skill::where('name', '=', $skillName)->first();
                        if (!$skill) {
                            $skill = new Skill();
                            $slug = $skill->generateSlug($skillName);
                            $skill->fill([
                                'name' => $skillName,
                                'slug' => $slug
                            ]);
                            if (!$skill->save()) {
                                error_log("Job Store - Failed to create skill: " . $skillName);
                                continue;
                            }
                        }
                        \App\Core\Database::getInstance()->query(
                            "INSERT INTO job_skills (job_id, skill_id, importance) VALUES (:job_id, :skill_id, :importance) 
                             ON DUPLICATE KEY UPDATE importance = VALUES(importance)",
                            ['job_id' => $jobId, 'skill_id' => $skill->id, 'importance' => 5]
                        );
                    }
                }
            }

            // Save benefits / perks
            if (isset($data['benefit_ids']) && is_array($data['benefit_ids'])) {
                $benefitIds = array_values(array_unique(array_filter(array_map('intval', $data['benefit_ids']))));
                $jobId = $job->attributes['id'] ?? $job->id ?? null;
                
                if ($jobId) {
                    try {
                        // Delete existing benefits for this job
                        \App\Core\Database::getInstance()->query(
                            "DELETE FROM job_benefits WHERE job_id = :job_id",
                            ['job_id' => $jobId]
                        );
                        
                        // Insert new benefits
                        if (!empty($benefitIds)) {
                            foreach ($benefitIds as $benefitId) {
                                if ($benefitId > 0) {
                                    \App\Core\Database::getInstance()->query(
                                        "INSERT INTO job_benefits (job_id, benefit_id) VALUES (:job_id, :benefit_id)
                                         ON DUPLICATE KEY UPDATE job_id = VALUES(job_id), benefit_id = VALUES(benefit_id)",
                                        ['job_id' => $jobId, 'benefit_id' => $benefitId]
                                    );
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        error_log("JobsController::store - Failed to assign benefits: " . $e->getMessage());
                    }
                }
            }

            // Queue for Elasticsearch indexing (if worker exists)
            try {
                if (class_exists('\App\Workers\IndexJobWorker')) {
                    \App\Workers\IndexJobWorker::enqueue(['job_id' => $job->id]);
                }
            } catch (\Exception $e) {
                // Worker not available, continue without indexing
                error_log("IndexJobWorker not available: " . $e->getMessage());
            }

            // Trigger Job Matching Notifications
            try {
                if ($job->status === 'published') {
                    $matchService = new JobMatchService();
                    $matchService->findAndNotifyCandidates($job);
                }
            } catch (\Exception $e) {
                error_log("Job Matching failed: " . $e->getMessage());
            }

            // Return JSON for API calls, redirect for form submissions
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') !== false || $request->header('Content-Type') === 'application/json') {
                $response->json(['success' => true, 'job' => $job->toArray(), 'message' => 'Job created successfully'], 201);
            } else {
                $response->redirect('/employer/jobs');
            }
        } else {
            $response->json(['error' => 'Failed to create job'], 500);
        }
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = $request->param('slug');
        $job = Job::findBySlug($slug);
        $employer = $this->currentUser->employer();
        $id = $job ? ($job->attributes['id'] ?? $job->id ?? null) : null;

        if (!$job) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        $jobEmployerId = $job->attributes['employer_id'] ?? $job->employer_id ?? null;
        if ($jobEmployerId !== $employer->id) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        // Handle both JSON and form data
        $data = $request->getMethod() === 'PUT' && $request->header('Content-Type') === 'application/json' 
            ? $request->getJsonBody() 
            : array_merge($request->all(), $request->getJsonBody());
        
        // Debug: Log received data
        error_log("Job Update - Received data keys: " . implode(', ', array_keys($data)));
        error_log("Job Update - Skills data: " . (isset($data['skills']) ? json_encode($data['skills']) : 'NOT SET'));

        // Update job
        $title = $data['title'] ?? $job->title;
        $slug = $job->generateSlug($title);
        
        // Server-side translation when non-English selected
        $selectedLangUpd = (string)($data['language'] ?? ($job->language ?? 'English'));
        $targetCodeUpd = $this->langCodes[$selectedLangUpd] ?? 'en';
        $autoTranslateUpd = (bool)($data['auto_translate'] ?? false);
        if ($targetCodeUpd !== 'en' && $autoTranslateUpd) {
            if (!empty($title)) { $title = $this->translateText($title, $targetCodeUpd); }
            if (!empty($data['short_description'] ?? $job->short_description)) {
                $data['short_description'] = $this->translateText((string)($data['short_description'] ?? $job->short_description), $targetCodeUpd);
            }
            if (!empty($data['description'] ?? $job->description)) {
                $data['description'] = $this->translateText(strip_tags((string)($data['description'] ?? $job->description)), $targetCodeUpd);
            }
        }

        // Also persist locations JSON on job for fallback rendering
        if (!empty($data['location'])) {
            $normLocsUpd = [];
            if (is_array($data['location'])) {
                if (isset($data['location'][0]) && is_array($data['location'][0])) {
                    foreach ($data['location'] as $loc) {
                        $normLocsUpd[] = [
                            'city' => $loc['city'] ?? '',
                            'state' => $loc['state'] ?? '',
                            'country' => $loc['country'] ?? ''
                        ];
                    }
                } elseif (isset($data['location']['city']) || isset($data['location']['state']) || isset($data['location']['country'])) {
                    $normLocsUpd[] = [
                        'city' => $data['location']['city'] ?? '',
                        'state' => $data['location']['state'] ?? '',
                        'country' => $data['location']['country'] ?? ''
                    ];
                }
            }
            if (!empty($normLocsUpd)) {
                $job->attributes['locations'] = json_encode($normLocsUpd);
            }
        }

        $job->fill([
            'title' => $title,
            'slug' => $slug,
            'description' => $data['description'] ?? $job->description,
            'short_description' => $data['short_description'] ?? substr($data['description'] ?? $job->description, 0, 1000),
            'employment_type' => $data['employment_type'] ?? $data['job_type'] ?? $job->employment_type,
            'seniority' => $data['seniority'] ?? $job->seniority,
            'salary_min' => isset($data['salary_min']) && $data['salary_min'] !== '' ? (int)$data['salary_min'] : $job->salary_min,
            'salary_max' => isset($data['salary_max']) && $data['salary_max'] !== '' ? (int)$data['salary_max'] : $job->salary_max,
            'currency' => $data['currency'] ?? $data['salary_currency'] ?? $job->currency,
            'pay_type' => $data['pay_type'] ?? $job->pay_type ?? 'range',
            'pay_frequency' => $data['pay_frequency'] ?? $job->pay_frequency ?? 'monthly',
            'pay_fixed_amount' => isset($data['pay_fixed_amount']) && $data['pay_fixed_amount'] !== '' ? (int)$data['pay_fixed_amount'] : ($job->pay_fixed_amount ?? null),
            'hours_per_week' => isset($data['hours_per_week']) && $data['hours_per_week'] !== '' ? (int)$data['hours_per_week'] : ($job->hours_per_week ?? null),
            'shift' => $data['shift'] ?? $job->shift ?? null,
            'contract_length' => isset($data['contract_length']) && $data['contract_length'] !== '' ? (int)$data['contract_length'] : ($job->contract_length ?? null),
            'contract_period' => $data['contract_period'] ?? $job->contract_period ?? null,
            'commission_percent' => isset($data['commission_percent']) && $data['commission_percent'] !== '' ? (float)$data['commission_percent'] : ($job->commission_percent ?? null),
            'incentive_rules' => $data['incentive_rules'] ?? $job->incentive_rules ?? null,
            'stipend' => isset($data['stipend']) && $data['stipend'] !== '' ? (int)$data['stipend'] : ($job->stipend ?? null),
            'internship_length' => isset($data['internship_length']) && $data['internship_length'] !== '' ? (int)$data['internship_length'] : ($job->internship_length ?? null),
            'season_duration' => $data['season_duration'] ?? $job->season_duration ?? null,
            'flexible_hours' => isset($data['flexible_hours']) ? (int)$data['flexible_hours'] : ($job->flexible_hours ?? 0),
            'remote_policy' => $data['remote_policy'] ?? $job->remote_policy ?? null,
            'remote_tools' => $data['remote_tools'] ?? $job->remote_tools ?? null,
            'is_remote' => isset($data['is_remote']) ? (int)$data['is_remote'] : $job->is_remote,
            'status' => $data['status'] ?? $job->status,
            'vacancies' => isset($data['vacancies']) ? (int)$data['vacancies'] : (isset($data['openings']) ? (int)$data['openings'] : $job->vacancies),
            'visibility' => $data['visibility'] ?? $job->visibility,
            'job_timings' => $data['job_timings'] ?? $job->job_timings ?? '',
            'interview_timings' => $data['interview_timings'] ?? $job->interview_timings ?? '',
            'job_address' => $data['job_address'] ?? $job->job_address ?? '',
            'experience_type' => $data['experience_type'] ?? $job->experience_type ?? 'any',
            'min_experience' => isset($data['min_experience']) && $data['min_experience'] !== '' ? (int)$data['min_experience'] : ($job->min_experience ?? null),
            'max_experience' => isset($data['max_experience']) && $data['max_experience'] !== '' ? (int)$data['max_experience'] : ($job->max_experience ?? null),
            'offers_bonus' => $data['offers_bonus'] ?? $job->offers_bonus ?? 'no',
            'call_availability' => $data['call_availability'] ?? $job->call_availability ?? 'everyday',
            'company_name' => $data['company_name'] ?? $job->company_name ?? '',
            'contact_person' => $data['contact_person'] ?? $job->contact_person ?? '',
            'phone' => $data['phone'] ?? $job->phone ?? '',
            'email' => $data['email'] ?? $job->email ?? '',
            'contact_profile' => $data['contact_profile'] ?? $job->contact_profile ?? '',
            'company_size' => $data['company_size'] ?? $job->company_size ?? '',
            'hiring_urgency' => $data['hiring_urgency'] ?? $job->hiring_urgency ?? 'immediate',
            'language' => $data['language'] ?? $job->language ?? 'English',
            'category' => $data['category'] ?? $job->category ?? '',
        ]);
        
        // Update status if provided
        if (isset($data['status'])) {
            $job->status = $data['status'];
        }
        
        if (!$job->save()) {
            error_log("Job Update - Failed to save job. ID: " . ($id ?? 'UNKNOWN'));
            error_log("Job Update - Attributes: " . json_encode($job->attributes));
            $response->json(['error' => 'Failed to update job. Please check the logs.'], 500);
            return;
        }
        
        error_log("Job Update - Job saved successfully. ID: " . ($job->attributes['id'] ?? $job->id));

        // Trigger Job Matching if published
        try {
            if ($job->status === 'published') {
                 $matchService = new \App\Services\JobMatchService();
                 $matchService->findAndNotifyCandidates($job);
            }
        } catch (\Exception $e) {
            error_log("Job Matching failed in update: " . $e->getMessage());
        }

        // Update locations - handle both array format and object format
        if (isset($data['location'])) {
            // Delete existing locations first
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            if ($jobId) {
                try {
                    \App\Core\Database::getInstance()->query(
                        "DELETE FROM job_locations WHERE job_id = :job_id",
                        ['job_id' => $jobId]
                    );
                } catch (\Exception $e) {
                    error_log("Job Update - Failed to delete existing locations for job {$jobId}: " . $e->getMessage());
                }
            }
            
            $locations = [];
            if (is_array($data['location'])) {
                // Check if it's an array of location objects or a single object
                if (isset($data['location'][0]) && is_array($data['location'][0])) {
                    // Array of location objects
                    $locations = $data['location'];
                } elseif (isset($data['location']['city']) || isset($data['location']['state'])) {
                    // Single location object
                    $locations = [$data['location']];
                }
            }
            
            // Save locations
                foreach ($locations as $locData) {
                    if (!empty($locData['city']) || !empty($locData['state'])) {
                        $location = new JobLocation();
                        $location->fill([
                            'job_id' => $jobId,
                            'city' => $locData['city'] ?? null,
                            'state' => $locData['state'] ?? null,
                            'country' => $locData['country'] ?? 'India',
                            'city_id' => null, // We are using text fields now
                            'state_id' => null,
                            'country_id' => null,
                            'latitude' => $locData['latitude'] ?? null,
                            'longitude' => $locData['longitude'] ?? null,
                        ]);
                        $location->save();
                    }
                }
        }

        // Update skills - delete existing first, then add new ones
        if (isset($data['skills']) && is_array($data['skills'])) {
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            if ($jobId) {
                // Delete existing skills for this job
                \App\Core\Database::getInstance()->query(
                    "DELETE FROM job_skills WHERE job_id = :job_id",
                    ['job_id' => $jobId]
                );
                
                // Add new skills (filter out empty values)
                $skills = array_filter(array_map('trim', $data['skills']), function($s) {
                    return !empty($s);
                });
                
                error_log("Job Update - Saving skills: " . json_encode($skills));
                
                foreach ($skills as $skillName) {
                    if (empty($skillName)) continue;
                    
                    $skillName = trim($skillName);
                    $skill = Skill::where('name', '=', $skillName)->first();
                    if (!$skill) {
                        $skill = new Skill();
                        $slug = $skill->generateSlug($skillName);
                        $skill->fill([
                            'name' => $skillName,
                            'slug' => $slug
                        ]);
                        if (!$skill->save()) {
                            error_log("Job Update - Failed to create skill: " . $skillName);
                            error_log("Job Update - Skill attributes: " . json_encode($skill->attributes));
                            continue;
                        }
                        error_log("Job Update - Created new skill: {$skillName} (ID: {$skill->id}, Slug: {$slug})");
                    }
                    
                    try {
                        \App\Core\Database::getInstance()->query(
                            "INSERT INTO job_skills (job_id, skill_id, importance) VALUES (:job_id, :skill_id, :importance) 
                             ON DUPLICATE KEY UPDATE importance = VALUES(importance)",
                            ['job_id' => $jobId, 'skill_id' => $skill->id, 'importance' => 5]
                        );
                        error_log("Job Update - Skill linked: {$skillName} (Skill ID: {$skill->id})");
                    } catch (\Exception $e) {
                        error_log("Job Update - Error linking skill {$skillName}: " . $e->getMessage());
                    }
                }
            } else {
                error_log("Job Update - No job ID available for saving skills");
            }
        } else {
            error_log("Job Update - No skills data provided or not an array");
        }

        // Update benefits / perks
        if (isset($data['benefit_ids']) && is_array($data['benefit_ids'])) {
            $benefitIds = array_values(array_unique(array_filter(array_map('intval', $data['benefit_ids']))));
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            
            if ($jobId) {
                try {
                    // Delete existing benefits for this job
                    \App\Core\Database::getInstance()->query(
                        "DELETE FROM job_benefits WHERE job_id = :job_id",
                        ['job_id' => $jobId]
                    );
                    
                    // Insert new benefits
                    if (!empty($benefitIds)) {
                        foreach ($benefitIds as $benefitId) {
                            if ($benefitId > 0) {
                                \App\Core\Database::getInstance()->query(
                                    "INSERT INTO job_benefits (job_id, benefit_id) VALUES (:job_id, :benefit_id)
                                     ON DUPLICATE KEY UPDATE job_id = VALUES(job_id), benefit_id = VALUES(benefit_id)",
                                    ['job_id' => $jobId, 'benefit_id' => $benefitId]
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    error_log("JobsController::update - Failed to assign benefits: " . $e->getMessage());
                }
            }
        }

        $acceptHeader = $request->header('Accept') ?? '';
        if (strpos($acceptHeader, 'application/json') !== false || $request->header('Content-Type') === 'application/json') {
            $response->json(['success' => true, 'job' => $job->toArray()]);
        } else {
            // Use slug for redirect, fallback to ID if slug is missing
            $redirectSlug = $job->attributes['slug'] ?? $job->slug ?? $job->id;
            $response->redirect('/employer/jobs/' . $redirectSlug);
        }
    }

    public function destroy(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = $request->param('slug');
        $job = Job::findBySlug($slug);
        $employer = $this->currentUser->employer();

        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        if ($job->delete()) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') !== false) {
                $response->json(['success' => true, 'message' => 'Job deleted']);
            } else {
                $response->redirect('/employer/jobs');
            }
        } else {
            $response->json(['error' => 'Failed to delete job'], 500);
        }
    }

    public function publish(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $slug = $request->param('slug');
        $job = Job::findBySlug($slug);

        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Job not found or unauthorized'], 404);
            return;
        }

        $job->status = 'published';
        if ($job->save()) {
            // Trigger Job Matching
            try {
                 $matchService = new \App\Services\JobMatchService();
                 $matchService->findAndNotifyCandidates($job);
            } catch (\Exception $e) {
                error_log("Job Matching failed in publish: " . $e->getMessage());
            }

            // Re-index in Elasticsearch
            // IndexJobWorker::push(['job_id' => $job->id, 'action' => 'index']);
            $response->json(['success' => true, 'message' => 'Job published successfully']);
        } else {
            $response->json(['error' => 'Failed to publish job'], 500);
        }
    }

    public function bulkImport(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        // Placeholder for bulk import logic
        $response->json(['message' => 'Bulk import feature is not yet implemented.'], 501);
    }

    /**
     * Generate AI job description
     * POST /employer/jobs/generate-description
     */
    public function generateDescription(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        
        try {
            $aiService = new AIJobDescriptionService();
            $description = $aiService->generateJobDescription($data);
            
            $response->json([
                'success' => true,
                'description' => $description
            ]);
        } catch (\Exception $e) {
            error_log("AI Job Description generation error: " . $e->getMessage());
            $response->json([
                'error' => 'Failed to generate job description',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
