<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Application;
use App\Core\RedisClient;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            error_log("Employer profile not found for user ID: " . ($this->currentUser->id ?? 'N/A'));
            error_log("User role: " . ($this->currentUser->role ?? 'N/A'));
            error_log("User email: " . ($this->currentUser->email ?? 'N/A'));
            
            // Check if employer exists in database directly
            $employerCheck = Employer::where('user_id', '=', $this->currentUser->id)->first();
            if ($employerCheck) {
                error_log("Employer found directly but employer() method returned null. Using direct query result.");
                $employer = $employerCheck;
            } else {
                error_log("No employer record found in database for user_id: " . $this->currentUser->id);
                
                // Show helpful error page instead of JSON error
                $response->view('employer/profile-missing', [
                    'title' => 'Complete Your Profile',
                    'message' => 'Your employer profile was not found. Please complete your registration.',
                    'user' => $this->currentUser
                ], 200, 'employer/layout');
                return;
            }
        }

        $redis = RedisClient::getInstance();
        $cacheKey = "employer:dashboard:{$employer->id}";
        
        // Try cache first (if Redis is available)
        if ($redis->isAvailable()) {
            $cached = $redis->get($cacheKey);
            if ($cached) {
                $response->json($cached);
                return;
            }
        }

        try {
            $totalJobs = count($employer->jobs());
        } catch (\Exception $e) {
            error_log('Dashboard jobs() error: ' . $e->getMessage());
            $totalJobs = 0;
        }
        try {
            $activeJobs = count(Job::where('employer_id', '=', $employer->id)
                ->where('status', '=', 'published')->get());
        } catch (\Exception $e) {
            error_log('Dashboard active jobs error: ' . $e->getMessage());
            $activeJobs = 0;
        }
        $stats = [
            'total_jobs' => $totalJobs,
            'active_jobs' => $activeJobs,
            'total_applications' => $this->safeGetTotalApplications($employer->id),
            'pending_applications' => $this->safeGetPendingApplications($employer->id),
            'recent_applications' => $this->safeGetRecentApplications($employer->id, 5),
            'kyc_status' => $employer->kyc_status,
        ];

        // Cache for 5 minutes (if Redis is available)
        if ($redis->isAvailable()) {
            $redis->set($cacheKey, $stats, 300);
        }
        // Get recent jobs
        try {
            $recentJobs = Job::where('employer_id', '=', $employer->id)
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            error_log('Dashboard recent jobs error: ' . $e->getMessage());
            $recentJobs = [];
        }

        $recentJobsWithStats = [];
        foreach ($recentJobs as $job) {
            $jobArray = $job->toArray();
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            
            // Get application counts
            $jobArray['applications_count'] = \App\Models\Application::where('job_id', '=', $jobId)->count();
            $jobArray['new_applications_count'] = \App\Models\Application::where('job_id', '=', $jobId)
                ->where('status', '=', 'pending')->count();
            
            // Get job locations directly from database
            $jobLocations = [];
            if ($jobId) {
                try {
                    $db = \App\Core\Database::getInstance();
                    $locationRows = $db->fetchAll(
                        "SELECT c.name as city, s.name as state, co.name as country 
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
                    } else {
                        $jobArray['location'] = $jobArray['locations'];
                    }
                } else {
                    $jobArray['location'] = 'Location not specified';
                }
            }
            
            // Format date
            if (!empty($jobArray['created_at'])) {
                $jobArray['created_at_formatted'] = date('M d, Y', strtotime($jobArray['created_at']));
            } else {
                $jobArray['created_at_formatted'] = 'N/A';
            }
            
            $recentJobsWithStats[] = $jobArray;
        }
        
        // Get recent applications with candidate info
        $recentApplications = $this->safeGetRecentApplications($employer->id, 5);

        // Get counts for sidebar
        $activeJobsCount = $stats['active_jobs'];
        $totalApplications = $stats['total_applications'];

        // Get interview count
        $interviewCount = $this->safeGetInterviewCount($employer->id);
        
        // Get additional dashboard data
        $recentActivities = $this->getRecentActivities($employer->id);
        $upcomingInterviews = $this->getUpcomingInterviews($employer->id);
        $shortlistedCandidates = $this->getShortlistedCandidates($employer->id);
        $feedbacks = $this->getCandidateFeedbacks($employer->id);
        $hiringTeam = $this->getHiringTeam($employer->id);
        $documents = $this->getRecentDocuments($employer->id);
        $performanceMetrics = $this->getPerformanceMetrics($employer->id);
        $notifications = $this->getNotifications($employer->id);
        
        $response->view('employer/dashboard', [
            'title' => 'Dashboard',
            'employer' => $employer,
            'stats' => [
                'active_jobs' => $stats['active_jobs'],
                'total_applications' => $stats['total_applications'],
                'new_applications' => $stats['pending_applications'],
                'interviews' => $interviewCount
            ],
            'recentJobs' => $recentJobsWithStats,
            'recentApplications' => $recentApplications,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'recentActivities' => $recentActivities,
            'upcomingInterviews' => $upcomingInterviews,
            'shortlistedCandidates' => $shortlistedCandidates,
            'feedbacks' => $feedbacks,
            'hiringTeam' => $hiringTeam,
            'documents' => $documents,
            'performanceMetrics' => $performanceMetrics,
            'notifications' => $notifications
        ], 200, 'employer/layout');
    }

    private function safeGetTotalApplications(int $employerId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    WHERE j.employer_id = :employer_id";
            $result = \App\Core\Database::getInstance()->fetchOne($sql, ['employer_id' => $employerId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('Dashboard total applications error: ' . $e->getMessage());
            return 0;
        }
    }

    private function safeGetPendingApplications(int $employerId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    WHERE j.employer_id = :employer_id AND a.status = 'applied'";
            $result = \App\Core\Database::getInstance()->fetchOne($sql, ['employer_id' => $employerId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('Dashboard pending applications error: ' . $e->getMessage());
            return 0;
        }
    }

    private function safeGetRecentApplications(int $employerId, int $limit): array
    {
        try {
            $limit = (int)$limit;
            $sql = "SELECT a.*, j.title as job_title, j.slug as job_slug, u.email as candidate_email, u.phone as candidate_phone,
                    c.full_name as candidate_name, c.profile_picture
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    INNER JOIN users u ON a.candidate_user_id = u.id
                    LEFT JOIN candidates c ON c.user_id = u.id
                    WHERE j.employer_id = :employer_id
                    ORDER BY a.applied_at DESC
                    LIMIT $limit";
            return \App\Core\Database::getInstance()->fetchAll($sql, [
                'employer_id' => $employerId
            ]);
        } catch (\Exception $e) {
            error_log('Dashboard recent applications error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function safeGetInterviewCount(int $employerId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM interviews i
                    WHERE i.employer_id = :employer_id 
                    AND i.status IN ('scheduled', 'rescheduled')
                    AND i.scheduled_start >= CURDATE()";
            $result = \App\Core\Database::getInstance()->fetchOne($sql, ['employer_id' => $employerId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('Dashboard interview count error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getRecentActivities(int $employerId, int $limit = 10): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $activities = [];
            
            // Get recent job postings
            $jobsSql = "SELECT j.title, j.created_at, 'job_posted' as type
                        FROM jobs j
                        WHERE j.employer_id = :employer_id
                        ORDER BY j.created_at DESC
                        LIMIT 3";
            $jobs = $db->fetchAll($jobsSql, ['employer_id' => $employerId]);
            foreach ($jobs as $job) {
                $activities[] = [
                    'message' => "Posted job: " . $job['title'],
                    'created_at' => $job['created_at'],
                    'type' => $job['type']
                ];
            }
            
            // Get recent applications
            $appsSql = "SELECT a.*, j.title as job_title, c.full_name as candidate_name, a.applied_at as created_at
                        FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        LEFT JOIN candidates c ON c.user_id = a.candidate_user_id
                        WHERE j.employer_id = :employer_id
                        ORDER BY a.applied_at DESC
                        LIMIT 3";
            $apps = $db->fetchAll($appsSql, ['employer_id' => $employerId]);
            foreach ($apps as $app) {
                $candidateName = $app['candidate_name'] ?? 'A candidate';
                $activities[] = [
                    'message' => $candidateName . " applied for " . $app['job_title'],
                    'created_at' => $app['created_at'],
                    'type' => 'application'
                ];
            }

            // Get recent interviews
            try {
                $interviewsSql = "SELECT i.*, j.title as job_title, c.full_name as candidate_name
                            FROM interviews i
                            INNER JOIN applications a ON i.application_id = a.id
                            INNER JOIN jobs j ON a.job_id = j.id
                            LEFT JOIN candidates c ON c.user_id = a.candidate_user_id
                            WHERE i.employer_id = :employer_id
                            ORDER BY i.created_at DESC
                            LIMIT 3";
                $interviews = $db->fetchAll($interviewsSql, ['employer_id' => $employerId]);
                foreach ($interviews as $interview) {
                    $candidateName = $interview['candidate_name'] ?? 'A candidate';
                    $activities[] = [
                        'message' => "Interview scheduled with " . $candidateName . " for " . $interview['job_title'],
                        'created_at' => $interview['created_at'],
                        'type' => 'interview'
                    ];
                }
            } catch (\Exception $e) {
                // Ignore interview errors if table/column missing
            }
            
            // Sort by created_at descending and limit
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (\Exception $e) {
            error_log('Dashboard recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    private function getUpcomingInterviews(int $employerId, int $limit = 5): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $limit = (int)$limit;
            $sql = "SELECT i.*, j.title as job_title, c.full_name as candidate_name,
                    a.id as application_id
                    FROM interviews i
                    INNER JOIN applications a ON i.application_id = a.id
                    INNER JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN candidates c ON c.user_id = a.candidate_user_id
                    WHERE i.employer_id = :employer_id
                    AND i.status IN ('scheduled', 'rescheduled')
                    AND i.scheduled_start >= NOW()
                    ORDER BY i.scheduled_start ASC
                    LIMIT $limit";
            $results = $db->fetchAll($sql, [
                'employer_id' => $employerId
            ]);
            
            $interviews = [];
            foreach ($results as $row) {
                $interviews[] = [
                    'id' => $row['id'],
                    'candidate_name' => $row['candidate_name'] ?? 'Unknown Candidate',
                    'job_title' => $row['job_title'],
                    'scheduled_at' => $row['scheduled_start'],
                    'interview_type' => $row['interview_type'] ?? 'phone',
                    'meeting_link' => $row['meeting_link'] ?? ''
                ];
            }
            
            return $interviews;
        } catch (\Exception $e) {
            error_log('Dashboard upcoming interviews error: ' . $e->getMessage());
            return [];
        }
    }

    private function getShortlistedCandidates(int $employerId, int $limit = 5): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $limit = (int)$limit;
            $sql = "SELECT a.*, j.title as job_title, c.full_name as name,
                    c.profile_picture, c.id as candidate_id, a.id as application_id
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN candidates c ON c.user_id = a.candidate_user_id
                    WHERE j.employer_id = :employer_id
                    AND a.status = 'shortlisted'
                    ORDER BY a.updated_at DESC
                    LIMIT $limit";
            $results = $db->fetchAll($sql, [
                'employer_id' => $employerId
            ]);
            
            $candidates = [];
            foreach ($results as $row) {
                $candidates[] = [
                    'id' => $row['candidate_id'] ?? $row['application_id'],
                    'name' => $row['name'] ?? 'Unknown Candidate',
                    'job_title' => $row['job_title'],
                    'profile_picture' => $row['profile_picture'] ?? '/uploads/default-avatar.png',
                    'application_id' => $row['application_id']
                ];
            }
            
            return $candidates;
        } catch (\Exception $e) {
            error_log('Dashboard shortlisted candidates error: ' . $e->getMessage());
            return [];
        }
    }

    private function getCandidateFeedbacks(int $employerId, int $limit = 5): array
    {
        // Placeholder - can be implemented when feedback system is ready
        return [];
    }

    private function getHiringTeam(int $employerId): array
    {
        // Placeholder - can be implemented when team management is ready
        // For now, return empty array
        return [];
    }

    private function getRecentDocuments(int $employerId, int $limit = 5): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $limit = (int)$limit;
            $sql = "SELECT id, doc_type as type, file_name as name, 
                    COALESCE(reviewed_at, NOW()) as uploaded_at, 
                    file_url
                    FROM employer_kyc_documents
                    WHERE employer_id = :employer_id
                    ORDER BY id DESC
                    LIMIT $limit";
            $results = $db->fetchAll($sql, [
                'employer_id' => $employerId
            ]);
            
            $documents = [];
            foreach ($results as $row) {
                $documents[] = [
                    'id' => $row['id'],
                    'name' => $row['name'] ?? ucfirst(str_replace('_', ' ', $row['type'] ?? 'document')),
                    'type' => ucfirst(str_replace('_', ' ', $row['type'] ?? 'document')),
                    'uploaded_at' => $row['uploaded_at'] ?? date('Y-m-d H:i:s'),
                    'file_url' => $row['file_url'] ?? ''
                ];
            }
            
            return $documents;
        } catch (\Exception $e) {
            error_log('Dashboard documents error: ' . $e->getMessage());
            return [];
        }
    }

    private function getPerformanceMetrics(int $employerId): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            
            // Get total applications
            $totalAppsSql = "SELECT COUNT(*) as count FROM applications a
                            INNER JOIN jobs j ON a.job_id = j.id
                            WHERE j.employer_id = :employer_id";
            $totalApps = $db->fetchOne($totalAppsSql, ['employer_id' => $employerId]);
            $totalApplications = (int)($totalApps['count'] ?? 0);
            
            // Get total interviews
            $totalInterviewsSql = "SELECT COUNT(*) as count FROM interviews i
                                  WHERE i.employer_id = :employer_id";
            $totalInterviews = $db->fetchOne($totalInterviewsSql, ['employer_id' => $employerId]);
            $totalInterviewCount = (int)($totalInterviews['count'] ?? 0);
            
            // Get hired candidates (applications with status 'hired' or 'accepted')
            $hiredSql = "SELECT COUNT(*) as count FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        WHERE j.employer_id = :employer_id
                        AND a.status IN ('hired', 'accepted')";
            $hired = $db->fetchOne($hiredSql, ['employer_id' => $employerId]);
            $hiredCount = (int)($hired['count'] ?? 0);
            
            // Calculate hiring speed (average days from application to hire)
            $hiringSpeed = 'N/A';
            if ($hiredCount > 0) {
                $speedSql = "SELECT AVG(DATEDIFF(a.updated_at, a.applied_at)) as avg_days
                            FROM applications a
                            INNER JOIN jobs j ON a.job_id = j.id
                            WHERE j.employer_id = :employer_id
                            AND a.status IN ('hired', 'accepted')
                            AND a.updated_at IS NOT NULL";
                $speedResult = $db->fetchOne($speedSql, ['employer_id' => $employerId]);
                if ($speedResult && $speedResult['avg_days'] !== null) {
                    $hiringSpeed = round((float)$speedResult['avg_days'], 1);
                }
            }
            
            // Calculate ratios
            $applicationToHireRatio = $hiredCount > 0 ? round($totalApplications / $hiredCount, 1) : 'N/A';
            $interviewToHireRatio = $hiredCount > 0 ? round($totalInterviewCount / $hiredCount, 1) : 'N/A';
            
            return [
                'hiring_speed' => $hiringSpeed,
                'application_to_hire_ratio' => $applicationToHireRatio,
                'interview_to_hire_ratio' => $interviewToHireRatio
            ];
        } catch (\Exception $e) {
            error_log('Dashboard performance metrics error: ' . $e->getMessage());
            return [
                'hiring_speed' => 'N/A',
                'application_to_hire_ratio' => 'N/A',
                'interview_to_hire_ratio' => 'N/A'
            ];
        }
    }

    private function getNotifications(int $employerId, int $limit = 10): array
    {
        // Placeholder - can be implemented when notification system is ready
        return [];
    }
}
