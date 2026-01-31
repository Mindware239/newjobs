<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $stats = [
            'total_employers' => 0,
            'total_candidates' => 0,
            'total_jobs' => 0,
            'active_jobs' => 0,
            'revenue_month' => 0.0,
            'kyc_pending' => 0,
            'kyc_approved' => 0,
            'kyc_rejected' => 0,
            'kyc_escalated' => 0,
            'my_assigned' => 0,
            'docs_pending' => 0,
            'docs_approved' => 0,
            'docs_rejected' => 0,
            'cand_verif_pending' => 0,
            'cand_verif_assigned' => 0,
            'cand_verif_approved' => 0,
            'cand_verif_rejected' => 0,
            'applications_today' => 0,
            'auto_apply_today' => 0,
            'auto_apply_failed_today' => 0,
            'auto_apply_avg_score_today' => 0.0,
            'applications_month' => 0,
            'auto_apply_month' => 0
        ];

        $series = [
            'months' => [],
            'jobs' => [],
            'applications' => [],
            'auto_apply' => [],
            'revenue_months' => [],
            'revenue' => []
        ];
        $distribution = [
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0
        ];
        $topCategories = [];
        $recentApplications = [];
        $topEmployers = [];

        $error = null;
        try {
            $db = Database::getInstance();
            $stats['total_employers'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers")['c'] ?? 0);
            $stats['total_candidates'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM candidates")['c'] ?? 0);
            $stats['total_jobs'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM jobs")['c'] ?? 0);
            $stats['active_jobs'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM jobs WHERE status='published'")['c'] ?? 0);
            $stats['revenue_month'] = (float)($db->fetchOne("SELECT COALESCE(SUM(amount),0) as t FROM employer_payments WHERE status='completed' AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")['t'] ?? 0);
            $stats['kyc_pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status='pending'")['c'] ?? 0);
            $stats['kyc_approved'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status='approved'")['c'] ?? 0);
            $stats['kyc_rejected'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_status='rejected'")['c'] ?? 0);
            $stats['kyc_escalated'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_escalated=1")['c'] ?? 0);
            $uid = (int)($_SESSION['user_id'] ?? 0);
            $stats['my_assigned'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employers WHERE kyc_assigned_to = :uid AND kyc_status='pending'", ['uid' => $uid])['c'] ?? 0);
            $stats['docs_pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employer_kyc_documents WHERE review_status='pending'")['c'] ?? 0);
            $stats['docs_approved'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employer_kyc_documents WHERE review_status='approved'")['c'] ?? 0);
            $stats['docs_rejected'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM employer_kyc_documents WHERE review_status='rejected'")['c'] ?? 0);
            $stats['cand_verif_pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM verifications WHERE user_type='candidate' AND status='pending'")['c'] ?? 0);
            $stats['cand_verif_assigned'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM verifications WHERE user_type='candidate' AND status='assigned'")['c'] ?? 0);
            $stats['cand_verif_approved'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM verifications WHERE user_type='candidate' AND status='approved'")['c'] ?? 0);
            $stats['cand_verif_rejected'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM verifications WHERE user_type='candidate' AND status='rejected'")['c'] ?? 0);

            $stats['applications_today'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE DATE(applied_at)=CURDATE()")['c'] ?? 0);
            $stats['auto_apply_today'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE application_method='auto' AND DATE(applied_at)=CURDATE()")['c'] ?? 0);
            $stats['auto_apply_failed_today'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action='auto_apply_error' AND DATE(created_at)=CURDATE()")['c'] ?? 0);
            $stats['auto_apply_avg_score_today'] = (float)($db->fetchOne("SELECT COALESCE(AVG(match_score),0) as avg FROM applications WHERE application_method='auto' AND DATE(applied_at)=CURDATE()")['avg'] ?? 0.0);
            $stats['applications_month'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE YEAR(applied_at)=YEAR(CURDATE()) AND MONTH(applied_at)=MONTH(CURDATE())")['c'] ?? 0);
            $stats['auto_apply_month'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE application_method='auto' AND YEAR(applied_at)=YEAR(CURDATE()) AND MONTH(applied_at)=MONTH(CURDATE())")['c'] ?? 0);

            $rows = $db->fetchAll("
                SELECT DATE_FORMAT(applied_at,'%b') as m, COUNT(*) as apps,
                       SUM(application_method='auto') as autos
                FROM applications
                WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY DATE_FORMAT(applied_at,'%Y-%m')
                ORDER BY DATE_FORMAT(applied_at,'%Y-%m') ASC
            ");
            $series['months'] = array_map(fn($r) => (string)$r['m'], $rows);
            $series['applications'] = array_map(fn($r) => (int)$r['apps'], $rows);
            $series['auto_apply'] = array_map(fn($r) => (int)$r['autos'], $rows);
            $jrows = $db->fetchAll("
                SELECT DATE_FORMAT(updated_at,'%b') as m, COUNT(*) as jobs
                FROM jobs
                WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY DATE_FORMAT(updated_at,'%Y-%m')
                ORDER BY DATE_FORMAT(updated_at,'%Y-%m') ASC
            ");
            $series['jobs'] = array_map(fn($r) => (int)$r['jobs'], $jrows);
            $revRows = $db->fetchAll("
                SELECT DATE_FORMAT(created_at,'%Y-%m') as ym, DATE_FORMAT(created_at,'%b') as m, COALESCE(SUM(amount),0) as amt
                FROM employer_payments
                WHERE status='completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY DATE_FORMAT(created_at,'%Y-%m')
                ORDER BY DATE_FORMAT(created_at,'%Y-%m') ASC
            ");
            $map = [];
            foreach ($revRows as $r) { $map[(string)$r['ym']] = (float)($r['amt'] ?? 0); }
            $months = [];
            $values = [];
            for ($i = 5; $i >= 0; $i--) {
                $key = date('Y-m', strtotime("-{$i} months"));
                $months[] = date('M', strtotime($key . '-01'));
                $values[] = (float)($map[$key] ?? 0.0);
            }
            $series['revenue_months'] = $months;
            $series['revenue'] = $values;

            $distribution['approved'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE status IN ('shortlisted','interview','hired')")['c'] ?? 0);
            $distribution['pending'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE status IN ('applied','screening')")['c'] ?? 0);
            $distribution['rejected'] = (int)($db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE status='rejected'")['c'] ?? 0);

            $topCategories = $db->fetchAll("SELECT category as name, COUNT(*) as c FROM jobs WHERE status='published' GROUP BY category ORDER BY c DESC LIMIT 6");
            $recentApplications = $db->fetchAll("
                SELECT a.id, a.status, a.applied_at, j.title as job_title, e.company_name 
                FROM applications a 
                LEFT JOIN jobs j ON j.id = a.job_id 
                LEFT JOIN employers e ON e.id = j.employer_id 
                ORDER BY a.applied_at DESC 
                LIMIT 5
            ");
            $topEmployers = $db->fetchAll("
                SELECT e.id, e.company_name, COUNT(j.id) as jobs 
                FROM employers e 
                LEFT JOIN jobs j ON j.employer_id = e.id AND j.status='published' 
                GROUP BY e.id 
                ORDER BY jobs DESC 
                LIMIT 5
            ");
        } catch (\Throwable $t) {
            $error = 'Database connection failed';
        }

        $response->view('masteradmin/dashboard', [
            'title' => 'Master Admin Dashboard',
            'stats' => $stats,
            'error' => $error,
            'series' => $series,
            'distribution' => $distribution,
            'topCategories' => $topCategories,
            'recentApplications' => $recentApplications,
            'topEmployers' => $topEmployers
        ], 200, 'masteradmin/layout');
    }
}
