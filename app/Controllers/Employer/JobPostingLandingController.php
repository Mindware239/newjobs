<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SubscriptionPlan;
use App\Models\Testimonial;

class JobPostingLandingController extends BaseController
{
    /**
     * Employer Job Posting Landing Page
     * GET /employer/job-posting
     */
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        
        // 1. Fetch Dynamic Stats
        $stats = [];
        try {
            $stats = [
                'employers' => $db->fetchOne("SELECT COUNT(*) as count FROM employers")['count'] ?? 0,
                'jobs' => $db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE status = 'published'")['count'] ?? 0,
                'candidates' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates")['count'] ?? 0,
                'interviews' => $db->fetchOne("SELECT COUNT(*) as count FROM interviews")['count'] ?? 0,
                'verified_profiles' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates WHERE is_verified = 1 OR profile_status = 'verified'")['count'] ?? 0,
            ];
        } catch (\Throwable $t) {
            $stats = ['employers' => 500, 'jobs' => 1200, 'candidates' => 50000, 'interviews' => 2500, 'verified_profiles' => 15000];
        }

        // 2. Fetch Dynamic Pricing Plans
        $plans = SubscriptionPlan::getActivePlansFor('employer');
        if (empty($plans)) {
            $plans = SubscriptionPlan::getActivePlans();
        }

        // 3. Fetch Dynamic Testimonials (client/employer testimonials are employer-side feedback)
        $testimonials = [];
        try {
            $testimonials = $db->fetchAll(
                "SELECT * FROM testimonials
                 WHERE testimonial_type IN ('client', 'employer')
                   AND is_active = 1
                 ORDER BY created_at DESC
                 LIMIT 12"
            );
        } catch (\Throwable $t) {
            $testimonials = Testimonial::getByType('client', 12);
        }

        // 4. Fetch published blogs from the blogs table
        $blogs = [];
        try {
            $blogs = $db->fetchAll("
                SELECT b.*, bcj.category_name
                FROM blogs b
                LEFT JOIN (
                    SELECT bcm.blog_id, MIN(bc.name) AS category_name
                    FROM blog_category_map bcm
                    INNER JOIN blog_categories bc ON bc.id = bcm.category_id
                    GROUP BY bcm.blog_id
                ) bcj ON bcj.blog_id = b.id
                WHERE b.published_at IS NOT NULL
                  AND b.published_at <= NOW()
                ORDER BY b.is_featured DESC, b.published_at DESC, b.created_at DESC
                LIMIT 8
            ");
        } catch (\Throwable $t) {
            $blogs = [];
        }

        // 5. Fetch Trusted Company Logos
        $companyLogos = [];
        try {
            $companyLogos = $db->fetchAll("SELECT company_name, logo_url FROM employers WHERE logo_url IS NOT NULL AND logo_url <> '' AND verified = 1 LIMIT 18");
            if (empty($companyLogos)) {
                $companyLogos = $db->fetchAll("SELECT organization_name as company_name, logo_url FROM social_organizations WHERE logo_url IS NOT NULL AND logo_url <> '' LIMIT 18");
            }
        } catch (\Throwable $t) {}

        // 6. Fetch Recent Candidates for Floating Card
        $recentCandidates = [];
        try {
            $recentCandidates = $db->fetchAll("SELECT name, profile_image FROM candidates ORDER BY created_at DESC LIMIT 3");
        } catch (\Throwable $t) {}

        $response->view('employer/job-posting-landing', [
            'title' => 'Hire Faster with Jobsence ATS',
            'stats' => $stats,
            'plans' => $plans,
            'testimonials' => $testimonials,
            'blogs' => $blogs,
            'companyLogos' => $companyLogos,
            'recentCandidates' => $recentCandidates,
            'base' => '/'
        ], 200, 'layout'); // Using the standard layout
    }

    /**
     * Handle Plan Selection
     * POST /employer/select-plan
     */
    public function selectPlan(Request $request, Response $response): void
    {
        $planId = (int)$request->post('plan_id');
        $cycle = (string)$request->post('billing_cycle', 'monthly');

        if ($planId <= 0) {
            $response->redirect('/employer/job-posting');
            return;
        }

        // Store selection in session
        $_SESSION['selected_plan_id'] = $planId;
        $_SESSION['selected_billing_cycle'] = $cycle;
        $_SESSION['intended_action'] = 'buy_subscription';

        // Check if logged in as employer
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employer') {
            // Redirect to registration or login
            $response->redirect('/register-employer?plan=' . $planId . '&cycle=' . $cycle);
            return;
        }

        // If already logged in, go to checkout
        $response->redirect('/employer/checkout');
    }
}
