<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Job;
use App\Models\Employer;
use App\Models\Candidate;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();

        // Platform Statistics
        $stats = [
            'total_employers' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM employers")['count'] ?? 0),
            'total_candidates' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM candidates")['count'] ?? 0),
            'total_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs")['count'] ?? 0),
            'active_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'published'")['count'] ?? 0),
            'expired_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'expired'")['count'] ?? 0),
            'draft_jobs' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'draft'")['count'] ?? 0),
            'total_applications' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM applications")['count'] ?? 0),
            'total_job_views' => (int)($db->fetchOne("SELECT SUM(views) as total FROM jobs")['total'] ?? 0),
            'total_messages' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM messages")['count'] ?? 0),
            'verified_companies' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM employers WHERE kyc_status = 'approved'")['count'] ?? 0),
            'total_subscriptions' => (int)($db->fetchOne("SELECT COUNT(*) as count FROM employer_subscriptions WHERE status = 'active'")['count'] ?? 0),
        ];

        // Revenue Statistics
        $revenue = [
            'today' => $this->getRevenue('today'),
            'week' => $this->getRevenue('week'),
            'month' => $this->getRevenue('month'),
            'ytd' => $this->getRevenue('ytd'),
        ];

        // Daily Signups (Last 30 days)
        $dailySignups = $this->getDailySignups(30);

        // Job Posting Trends (Last 30 days)
        $jobTrends = $this->getJobTrends(30);

        // Application Trends (Last 30 days)
        $applicationTrends = $this->getApplicationTrends(30);

        // Alerts
        $alerts = $this->getAlerts();

        // Recent Activity
        $recentActivity = $this->getRecentActivity(10);

        // Candidate Analytics
        $candidateByCategory = [];
        $candidateLocations = [];
        try {
            $candidateByCategory = $db->fetchAll(
                "SELECT LOWER(j.category) as category,
                        COUNT(DISTINCT a.candidate_user_id) as candidates,
                        COUNT(*) as applications
                 FROM applications a
                 INNER JOIN jobs j ON j.id = a.job_id
                 WHERE j.category IS NOT NULL AND j.category != ''
                 GROUP BY LOWER(j.category)
                 ORDER BY candidates DESC
                 LIMIT 10"
            );
        } catch (\Exception $e) {}
        try {
            $candidateLocations = $db->fetchAll(
                "SELECT COALESCE(c.country,'') as country,
                        COALESCE(c.state,'') as state,
                        COALESCE(c.city,'') as city,
                        COUNT(*) as candidates
                 FROM candidates c
                 GROUP BY COALESCE(c.country,''), COALESCE(c.state,''), COALESCE(c.city,'')
                 ORDER BY candidates DESC
                 LIMIT 10"
            );
        } catch (\Exception $e) {}

        $response->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'revenue' => $revenue,
            'dailySignups' => $dailySignups,
            'jobTrends' => $jobTrends,
            'applicationTrends' => $applicationTrends,
            'alerts' => $alerts,
            'recentActivity' => $recentActivity,
            'candidateByCategory' => $candidateByCategory,
            'candidateLocations' => $candidateLocations,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    private function getRevenue(string $period): float
    {
        $db = Database::getInstance();
        $dateCondition = match($period) {
            'today' => "DATE(created_at) = CURDATE()",
            'week' => "YEARWEEK(created_at) = YEARWEEK(CURDATE())",
            'month' => "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())",
            'ytd' => "YEAR(created_at) = YEAR(CURDATE())",
            default => "1=1"
        };

        try {
            $result = $db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total 
                 FROM employer_payments 
                 WHERE status = 'completed' AND {$dateCondition}"
            );
            return (float)($result['total'] ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function getDailySignups(int $days): array
    {
        $db = Database::getInstance();
        try {
            return $db->fetchAll(
                "SELECT DATE(created_at) as date, 
                        COUNT(*) as count,
                        SUM(CASE WHEN role = 'employer' THEN 1 ELSE 0 END) as employers,
                        SUM(CASE WHEN role = 'candidate' THEN 1 ELSE 0 END) as candidates
                 FROM users 
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$days]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getJobTrends(int $days): array
    {
        $db = Database::getInstance();
        try {
            return $db->fetchAll(
                "SELECT DATE(created_at) as date, COUNT(*) as count
                 FROM jobs 
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$days]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getApplicationTrends(int $days): array
    {
        $db = Database::getInstance();
        try {
            return $db->fetchAll(
                "SELECT DATE(created_at) as date, COUNT(*) as count
                 FROM applications 
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$days]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAlerts(): array
    {
        $db = Database::getInstance();
        $alerts = [];

        try {
            // KYC Pending
            $kycPending = (int)($db->fetchOne("SELECT COUNT(*) as count FROM employer_kyc_documents WHERE status = 'pending'")['count'] ?? 0);
            if ($kycPending > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'KYC Pending',
                    'message' => "{$kycPending} employer KYC documents pending review",
                    'count' => $kycPending,
                    'link' => '/admin/employers/kyc'
                ];
            }

            // Payment Failures
            $paymentFailures = (int)($db->fetchOne("SELECT COUNT(*) as count FROM employer_payments WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0);
            if ($paymentFailures > 0) {
                $alerts[] = [
                    'type' => 'error',
                    'title' => 'Payment Failures',
                    'message' => "{$paymentFailures} failed payments in last 7 days",
                    'count' => $paymentFailures,
                    'link' => '/admin/payments'
                ];
            }

            // Suspicious Candidates - low quality overall_score based on candidate_quality_scores
            $suspiciousCandidates = (int)($db->fetchOne(
                "SELECT COUNT(*) as count
                 FROM candidate_quality_scores
                 WHERE overall_score < 40"
            )['count'] ?? 0);
            if ($suspiciousCandidates > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Suspicious Candidates',
                    'message' => "{$suspiciousCandidates} candidates flagged for review",
                    'count' => $suspiciousCandidates,
                    'link' => '/admin/candidates?filter=suspicious'
                ];
            }

            // High Report Count Jobs
            $reportedJobs = (int)($db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'published' AND views > 1000")['count'] ?? 0);
            if ($reportedJobs > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'High Engagement Jobs',
                    'message' => "{$reportedJobs} jobs with high view counts",
                    'count' => $reportedJobs,
                    'link' => '/admin/jobs?sort=views'
                ];
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return $alerts;
    }

    private function getRecentActivity(int $limit): array
    {
        $db = Database::getInstance();
        try {
            return $db->fetchAll(
                "SELECT al.*, u.email, u.role
                 FROM activity_logs al
                 LEFT JOIN users u ON u.id = al.user_id
                 WHERE al.action LIKE 'admin_%'
                 ORDER BY al.created_at DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}

