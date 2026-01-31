<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Application;
use App\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    private AnalyticsService $analyticsService;

    public function __construct()
    {
        parent::__construct();
        $this->analyticsService = new AnalyticsService();
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            error_log("AnalyticsController::index - Employer profile not found for user ID: " . ($this->currentUser->id ?? 'N/A'));
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found. Please complete your registration.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get analytics data
        $stats = $this->getAnalyticsStats($employer->id);
        
        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;

        // Get jobs list for filters
        $jobs = Job::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $response->view('employer/analytics', [
            'title' => 'Analytics Dashboard',
            'employer' => $employer,
            'stats' => $stats,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'jobs' => $jobs
        ], 200, 'employer/layout');
    }

    // API Endpoints for Analytics Data
    public function getHiringFunnel(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $location = $request->get('location');

            $data = $this->analyticsService->getHiringFunnel($employer->id, $jobId, $dateFrom, $dateTo, $location);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getHiringFunnel error: " . $e->getMessage());
            $response->json([
                'stages' => [
                    'applied' => ['count' => 0, 'percentage' => 0],
                    'shortlisted' => ['count' => 0, 'percentage' => 0],
                    'interviewed' => ['count' => 0, 'percentage' => 0],
                    'offered' => ['count' => 0, 'percentage' => 0],
                    'hired' => ['count' => 0, 'percentage' => 0],
                    'rejected' => ['count' => 0, 'percentage' => 0]
                ],
                'drop_off_rates' => [
                    'applied_to_shortlisted' => 0,
                    'shortlisted_to_interview' => 0,
                    'interview_to_offer' => 0,
                    'offer_to_hire' => 0
                ],
                'conversion_rate' => 0,
                'total' => 0
            ]);
        }
    }

    public function getTimeToHire(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $data = $this->analyticsService->getTimeToHire($employer->id, $jobId, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getTimeToHire error: " . $e->getMessage());
            $response->json([
                'avg_days_posted_to_application' => 0,
                'avg_days_application_to_shortlisted' => 0,
                'avg_days_shortlisted_to_interview' => 0,
                'avg_days_interview_to_offer' => 0,
                'avg_days_offer_to_hire' => 0,
                'avg_days_total_time_to_hire' => 0,
                'longest_open_job_days' => 0,
                'fastest_filled_job_days' => 0
            ]);
        }
    }

    public function getLocationAnalytics(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
            $category = $request->get('category');

            $data = $this->analyticsService->getLocationAnalytics($employer->id, $jobId, $category);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getLocationAnalytics error: " . $e->getMessage());
            $response->json([
                'by_city' => [],
                'by_state' => [],
                'by_country' => [],
                'top_cities' => []
            ]);
        }
    }

    public function getJobEngagement(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;

            $data = $this->analyticsService->getJobEngagement($employer->id, $jobId);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getJobEngagement error: " . $e->getMessage());
            $response->json([]);
        }
    }

    public function getCandidateQuality(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;

            $data = $this->analyticsService->getCandidateQuality($employer->id, $jobId);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getCandidateQuality error: " . $e->getMessage());
            $response->json([]);
        }
    }

    public function getCommunicationAnalytics(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $data = $this->analyticsService->getCommunicationAnalytics($employer->id, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getCommunicationAnalytics error: " . $e->getMessage());
            $response->json([
                'messages_sent' => 0,
                'replies_received' => 0,
                'avg_response_time_hours' => 0,
                'interview_invites_sent' => 0,
                'interview_invites_read' => 0,
                'missed_interviews' => 0
            ]);
        }
    }

    public function getNotificationPerformance(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $data = $this->analyticsService->getNotificationPerformance($employer->id, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getNotificationPerformance error: " . $e->getMessage());
            $response->json([
                'total_sent' => 0,
                'delivered' => 0,
                'opened' => 0,
                'failed' => 0,
                'delivery_rate' => 0,
                'open_rate' => 0,
                'reminders_sent' => 0,
                'reminder_success_rate' => 0
            ]);
        }
    }

    public function getCandidateSources(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }
            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $data = $this->analyticsService->getCandidateSources($employer->id, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getCandidateSources error: " . $e->getMessage());
            $response->json(['counts' => [], 'percentages' => [], 'total' => 0]);
        }
    }

    public function getInterviewOutcomes(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }
            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }
            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $data = $this->analyticsService->getInterviewOutcomes($employer->id, $jobId, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getInterviewOutcomes error: " . $e->getMessage());
            $response->json(['passed' => 0, 'failed' => 0, 'no_show' => 0, 'total' => 0]);
        }
    }

    public function getOfferAcceptanceRate(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }
            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }
            $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $data = $this->analyticsService->getOfferAcceptanceRate($employer->id, $jobId, $dateFrom, $dateTo);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getOfferAcceptanceRate error: " . $e->getMessage());
            $response->json(['offers_made' => 0, 'offers_accepted' => 0, 'acceptance_rate' => 0]);
        }
    }

    public function getEmployerActivity(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $days = (int)($request->get('days') ?? 30);

            $data = $this->analyticsService->getEmployerActivity($employer->id, $days);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getEmployerActivity error: " . $e->getMessage());
            $response->json([
                'daily_activity' => [],
                'summary' => [
                    'days_with_job_creation' => 0,
                    'total_profiles_viewed' => 0,
                    'total_resumes_downloaded' => 0,
                    'first_action' => null,
                    'last_action' => null
                ]
            ]);
        }
    }

    public function getSubscriptionROI(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $data = $this->analyticsService->getSubscriptionROI($employer->id);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getSubscriptionROI error: " . $e->getMessage());
            $response->json([
                'has_subscription' => false,
                'message' => 'No active subscription found'
            ]);
        }
    }

    public function getSecurityLogs(Request $request, Response $response): void
    {
        try {
            if (!$this->requireRole('employer', $request, $response)) {
                return;
            }

            $employer = $this->currentUser->employer();
            if (!$employer) {
                $response->json(['error' => 'Employer profile not found'], 404);
                return;
            }

            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $type = $request->get('type');
            $page = (int)($request->get('page') ?? 1);
            $perPage = (int)($request->get('per_page') ?? 50);

            $data = $this->analyticsService->getSecurityLogs($employer->id, $dateFrom, $dateTo, $type, $page, $perPage);
            $response->json($data);
        } catch (\Exception $e) {
            error_log("AnalyticsController::getSecurityLogs error: " . $e->getMessage());
            $response->json([
                'logs' => [],
                'pagination' => [
                    'page' => 1,
                    'per_page' => 50,
                    'total' => 0,
                    'total_pages' => 0
                ]
            ]);
        }
    }

    public function exportReport(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $reportType = $request->get('type') ?? 'analytics';
        $format = $request->get('format') ?? 'csv';
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;

        // Generate export file
        $filename = $this->generateExport($employer->id, $reportType, $format, $dateFrom, $dateTo, $jobId);
        
        if ($filename && file_exists($filename)) {
            header('Content-Type: ' . ($format === 'pdf' ? 'application/pdf' : 'text/csv'));
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            readfile($filename);
            unlink($filename); // Clean up
            exit;
        } else {
            $response->json(['error' => 'Failed to generate export'], 500);
        }
    }

    private function generateExport(int $employerId, string $reportType, string $format, ?string $dateFrom, ?string $dateTo, ?int $jobId): ?string
    {
        try {
            // Simple CSV export implementation
            if ($format === 'csv') {
                $data = [];
                $headers = [];

                switch ($reportType) {
                    case 'funnel':
                        $funnel = $this->analyticsService->getHiringFunnel($employerId, $jobId, $dateFrom, $dateTo);
                        $headers = ['Stage', 'Count', 'Percentage'];
                        if (isset($funnel['stages']) && is_array($funnel['stages'])) {
                            foreach ($funnel['stages'] as $stage => $info) {
                                if (is_array($info) && isset($info['count'])) {
                                    $data[] = [ucfirst($stage), $info['count'], ($info['percentage'] ?? 0) . '%'];
                                }
                            }
                        }
                        break;
                    
                    case 'time_to_hire':
                        $ttm = $this->analyticsService->getTimeToHire($employerId, $jobId, $dateFrom, $dateTo);
                        $headers = ['Metric', 'Days'];
                        $data = [
                            ['Posted to Application', $ttm['avg_days_posted_to_application'] ?? 0],
                            ['Application to Shortlisted', $ttm['avg_days_application_to_shortlisted'] ?? 0],
                            ['Shortlisted to Interview', $ttm['avg_days_shortlisted_to_interview'] ?? 0],
                            ['Interview to Offer', $ttm['avg_days_interview_to_offer'] ?? 0],
                            ['Offer to Hire', $ttm['avg_days_offer_to_hire'] ?? 0],
                            ['Total Time to Hire', $ttm['avg_days_total_time_to_hire'] ?? 0]
                        ];
                        break;
                    
                    case 'analytics':
                    case 'comprehensive':
                        // Comprehensive analytics export
                        $headers = ['Metric', 'Value'];
                        $data = [];
                        
                        // Hiring Funnel
                        $funnel = $this->analyticsService->getHiringFunnel($employerId, $jobId, $dateFrom, $dateTo);
                        $data[] = ['', ''];
                        $data[] = ['HIRING FUNNEL', ''];
                        if (isset($funnel['stages']) && is_array($funnel['stages'])) {
                            foreach ($funnel['stages'] as $stage => $info) {
                                if (is_array($info) && isset($info['count'])) {
                                    $data[] = [ucfirst($stage) . ' Count', $info['count']];
                                    $data[] = [ucfirst($stage) . ' Percentage', ($info['percentage'] ?? 0) . '%'];
                                }
                            }
                        }
                        $data[] = ['Total Applications', $funnel['total'] ?? 0];
                        $data[] = ['Conversion Rate', ($funnel['conversion_rate'] ?? 0) . '%'];
                        
                        // Time to Hire
                        $ttm = $this->analyticsService->getTimeToHire($employerId, $jobId, $dateFrom, $dateTo);
                        $data[] = ['', ''];
                        $data[] = ['TIME TO HIRE', ''];
                        $data[] = ['Posted to Application (days)', $ttm['avg_days_posted_to_application'] ?? 0];
                        $data[] = ['Application to Shortlisted (days)', $ttm['avg_days_application_to_shortlisted'] ?? 0];
                        $data[] = ['Shortlisted to Interview (days)', $ttm['avg_days_shortlisted_to_interview'] ?? 0];
                        $data[] = ['Interview to Offer (days)', $ttm['avg_days_interview_to_offer'] ?? 0];
                        $data[] = ['Offer to Hire (days)', $ttm['avg_days_offer_to_hire'] ?? 0];
                        $data[] = ['Total Time to Hire (days)', $ttm['avg_days_total_time_to_hire'] ?? 0];
                        
                        // Location Analytics
                        $location = $this->analyticsService->getLocationAnalytics($employerId, $jobId);
                        $data[] = ['', ''];
                        $data[] = ['LOCATION ANALYTICS', ''];
                        if (isset($location['by_city']) && is_array($location['by_city'])) {
                            foreach ($location['by_city'] as $city => $stats) {
                                if (is_array($stats)) {
                                    $data[] = [$city . ' - Applications', $stats['applications'] ?? 0];
                                    $data[] = [$city . ' - Hired', $stats['hired'] ?? 0];
                                }
                            }
                        }
                        
                        // Job Engagement
                        $engagement = $this->analyticsService->getJobEngagement($employerId, $jobId);
                        $data[] = ['', ''];
                        $data[] = ['JOB ENGAGEMENT', ''];
                        if (is_array($engagement)) {
                            foreach ($engagement as $job) {
                                if (is_array($job) && isset($job['job_title'])) {
                                    $data[] = [
                                        $job['job_title'] ?? 'N/A',
                                        'Views: ' . ($job['views'] ?? 0) . ', Saves: ' . ($job['saves'] ?? 0) . ', Applications: ' . ($job['applications'] ?? 0)
                                    ];
                                }
                            }
                        }
                        
                        // Communication Analytics
                        $communication = $this->analyticsService->getCommunicationAnalytics($employerId, $dateFrom, $dateTo);
                        $data[] = ['', ''];
                        $data[] = ['COMMUNICATION ANALYTICS', ''];
                        $data[] = ['Messages Sent', $communication['messages_sent'] ?? 0];
                        $data[] = ['Replies Received', $communication['replies_received'] ?? 0];
                        $data[] = ['Avg Response Time (hours)', $communication['avg_response_time_hours'] ?? 0];
                        $data[] = ['Interview Invites Sent', $communication['interview_invites_sent'] ?? 0];
                        
                        break;
                    
                    default:
                        error_log("AnalyticsController::generateExport - Unknown report type: " . $reportType);
                        return null;
                }

                // Ensure we have data to export
                if (empty($data)) {
                    error_log("AnalyticsController::generateExport - No data to export for type: " . $reportType);
                    return null;
                }

                // Create export directory if it doesn't exist
                $exportDir = sys_get_temp_dir();
                if (!is_dir($exportDir) || !is_writable($exportDir)) {
                    error_log("AnalyticsController::generateExport - Export directory not writable: " . $exportDir);
                    return null;
                }

                $filename = $exportDir . '/analytics_export_' . $employerId . '_' . time() . '.csv';
                $fp = @fopen($filename, 'w');
                
                if (!$fp) {
                    error_log("AnalyticsController::generateExport - Failed to create file: " . $filename);
                    return null;
                }

                // Write BOM for Excel compatibility
                fwrite($fp, "\xEF\xBB\xBF");
                
                // Write headers
                if (!empty($headers)) {
                    fputcsv($fp, $headers);
                }
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($fp, $row);
                }
                
                fclose($fp);
                
                // Verify file was created
                if (!file_exists($filename) || filesize($filename) === 0) {
                    error_log("AnalyticsController::generateExport - File creation failed or empty: " . $filename);
                    return null;
                }
                
                return $filename;
            }

            error_log("AnalyticsController::generateExport - Unsupported format: " . $format);
            return null;
        } catch (\Exception $e) {
            error_log("AnalyticsController::generateExport error: " . $e->getMessage());
            return null;
        }
    }

    private function getAnalyticsStats(int $employerId): array
    {
        // Get job statistics
        $totalJobs = Job::where('employer_id', '=', $employerId)->count();
        $publishedJobs = Job::where('employer_id', '=', $employerId)
            ->where('status', '=', 'published')->count();
        $draftJobs = Job::where('employer_id', '=', $employerId)
            ->where('status', '=', 'draft')->count();
        $closedJobs = Job::where('employer_id', '=', $employerId)
            ->where('status', '=', 'closed')->count();

        // Get application statistics
        $jobIds = Job::where('employer_id', '=', $employerId)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;
        
        $pendingApplications = !empty($jobIds)
            ? Application::whereIn('job_id', $jobIds)
                ->where('status', '=', 'applied')->count()
            : 0;
        
        $shortlistedApplications = !empty($jobIds)
            ? Application::whereIn('job_id', $jobIds)
                ->where('status', '=', 'shortlisted')->count()
            : 0;
        
        $rejectedApplications = !empty($jobIds)
            ? Application::whereIn('job_id', $jobIds)
                ->where('status', '=', 'rejected')->count()
            : 0;

        // Get applications by month (last 6 months)
        $applicationsByMonth = $this->getApplicationsByMonth($employerId);

        // Get top performing jobs
        $topJobs = $this->getTopPerformingJobs($employerId);

        return [
            'jobs' => [
                'total' => $totalJobs,
                'published' => $publishedJobs,
                'draft' => $draftJobs,
                'closed' => $closedJobs
            ],
            'applications' => [
                'total' => $totalApplications,
                'pending' => $pendingApplications,
                'shortlisted' => $shortlistedApplications,
                'rejected' => $rejectedApplications
            ],
            'applications_by_month' => $applicationsByMonth,
            'top_jobs' => $topJobs
        ];
    }

    private function getApplicationsByMonth(int $employerId): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(applied_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                WHERE j.employer_id = :employer_id
                    AND applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(applied_at, '%Y-%m')
                ORDER BY month ASC";
        
        $results = \App\Core\Database::getInstance()->fetchAll($sql, ['employer_id' => $employerId]);
        
        $data = [];
        foreach ($results as $row) {
            $data[$row['month']] = (int)$row['count'];
        }
        
        return $data;
    }

    private function getTopPerformingJobs(int $employerId, int $limit = 5): array
    {
        $sql = "SELECT 
                    j.id,
                    j.title,
                    COUNT(a.id) as applications_count
                FROM jobs j
                LEFT JOIN applications a ON j.id = a.job_id
                WHERE j.employer_id = :employer_id
                GROUP BY j.id, j.title
                ORDER BY applications_count DESC
                LIMIT :limit";
        
        $results = \App\Core\Database::getInstance()->fetchAll($sql, [
            'employer_id' => $employerId,
            'limit' => $limit
        ]);
        
        return $results;
    }
}

