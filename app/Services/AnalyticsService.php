<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get hiring funnel data
     */
    public function getHiringFunnel(int $employerId, ?int $jobId = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $location = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];

            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }

            if ($dateFrom) {
                $where[] = "a.applied_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = "a.applied_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }

            if ($location) {
                $where[] = "(jl.city LIKE :location OR jl.state LIKE :location OR jl.country LIKE :location)";
                $params['location'] = "%{$location}%";
            }

            $sql = "SELECT 
                        COUNT(CASE WHEN a.status = 'applied' THEN 1 END) as applied,
                        COUNT(CASE WHEN a.status = 'shortlisted' THEN 1 END) as shortlisted,
                        COUNT(CASE WHEN a.status = 'interview' THEN 1 END) as interviewed,
                        COUNT(CASE WHEN a.status = 'offer' THEN 1 END) as offered,
                        COUNT(CASE WHEN a.status = 'hired' THEN 1 END) as hired,
                        COUNT(CASE WHEN a.status = 'rejected' THEN 1 END) as rejected,
                        COUNT(*) as total
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN job_locations jl ON j.id = jl.job_id
                    WHERE " . implode(' AND ', $where);

            $result = $this->db->fetchOne($sql, $params);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getHiringFunnel error: " . $e->getMessage());
            $result = null;
        }

        if (!$result) {
            $result = [
                'applied' => 0,
                'shortlisted' => 0,
                'interviewed' => 0,
                'offered' => 0,
                'hired' => 0,
                'rejected' => 0,
                'total' => 0
            ];
        }

        $total = (int)($result['total'] ?? 0);
        $applied = (int)($result['applied'] ?? 0);
        $shortlisted = (int)($result['shortlisted'] ?? 0);
        $interviewed = (int)($result['interviewed'] ?? 0);
        $offered = (int)($result['offered'] ?? 0);
        $hired = (int)($result['hired'] ?? 0);
        $rejected = (int)($result['rejected'] ?? 0);

        // Calculate percentages
        $appliedPct = $total > 0 ? ($applied / $total) * 100 : 0;
        $shortlistedPct = $applied > 0 ? ($shortlisted / $applied) * 100 : 0;
        $interviewedPct = $shortlisted > 0 ? ($interviewed / $shortlisted) * 100 : 0;
        $offeredPct = $interviewed > 0 ? ($offered / $interviewed) * 100 : 0;
        $hiredPct = $offered > 0 ? ($hired / $offered) * 100 : 0;
        $conversionRate = $total > 0 ? ($hired / $total) * 100 : 0;

        // Calculate drop-off rates
        $dropOffAppliedToShortlisted = $applied > 0 ? (($applied - $shortlisted) / $applied) * 100 : 0;
        $dropOffShortlistedToInterview = $shortlisted > 0 ? (($shortlisted - $interviewed) / $shortlisted) * 100 : 0;
        $dropOffInterviewToOffer = $interviewed > 0 ? (($interviewed - $offered) / $interviewed) * 100 : 0;
        $dropOffOfferToHire = $offered > 0 ? (($offered - $hired) / $offered) * 100 : 0;

        return [
            'stages' => [
                'applied' => ['count' => $applied, 'percentage' => round($appliedPct, 2)],
                'shortlisted' => ['count' => $shortlisted, 'percentage' => round($shortlistedPct, 2)],
                'interviewed' => ['count' => $interviewed, 'percentage' => round($interviewedPct, 2)],
                'offered' => ['count' => $offered, 'percentage' => round($offeredPct, 2)],
                'hired' => ['count' => $hired, 'percentage' => round($hiredPct, 2)],
                'rejected' => ['count' => $rejected, 'percentage' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0]
            ],
            'drop_off_rates' => [
                'applied_to_shortlisted' => round($dropOffAppliedToShortlisted, 2),
                'shortlisted_to_interview' => round($dropOffShortlistedToInterview, 2),
                'interview_to_offer' => round($dropOffInterviewToOffer, 2),
                'offer_to_hire' => round($dropOffOfferToHire, 2)
            ],
            'conversion_rate' => round($conversionRate, 2),
            'total' => $total
        ];
    }

    /**
     * Get time-to-hire analytics
     */
    public function getTimeToHire(int $employerId, ?int $jobId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            // Check if application_events table exists
            $eventsTableExists = false;
            try {
                $this->db->query("SELECT 1 FROM application_events LIMIT 1");
                $eventsTableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id", "a.status = 'hired'"];

            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }

            if ($dateFrom) {
                $where[] = "a.applied_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = "a.applied_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }

            // Simplified query that works even without application_events table
            if ($eventsTableExists) {
                $sql = "SELECT 
                            AVG(DATEDIFF(a.applied_at, j.created_at)) as avg_days_posted_to_application,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'shortlisted'), a.applied_at),
                                a.applied_at
                            )) as avg_days_application_to_shortlisted,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(i.scheduled_start) FROM interviews i WHERE i.application_id = a.id), NOW()),
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'shortlisted'), a.applied_at)
                            )) as avg_days_shortlisted_to_interview,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'offer'), NOW()),
                                COALESCE((SELECT MIN(i.scheduled_start) FROM interviews i WHERE i.application_id = a.id), NOW())
                            )) as avg_days_interview_to_offer,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'hired'), NOW()),
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'offer'), NOW())
                            )) as avg_days_offer_to_hire,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'hired'), NOW()),
                                a.applied_at
                            )) as avg_days_total_time_to_hire,
                            MAX(DATEDIFF(NOW(), j.created_at)) as longest_open_job_days,
                            MIN(DATEDIFF(
                                COALESCE((SELECT MIN(ae.created_at) FROM application_events ae WHERE ae.application_id = a.id AND ae.to_status = 'hired'), NOW()),
                                j.created_at
                            )) as fastest_filled_job_days
                        FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        WHERE " . implode(' AND ', $where);
            } else {
                // Simplified query without application_events
                $sql = "SELECT 
                            AVG(DATEDIFF(a.applied_at, j.created_at)) as avg_days_posted_to_application,
                            AVG(DATEDIFF(
                                COALESCE((SELECT MIN(i.scheduled_start) FROM interviews i WHERE i.application_id = a.id), NOW()),
                                a.applied_at
                            )) as avg_days_application_to_shortlisted,
                            0 as avg_days_shortlisted_to_interview,
                            0 as avg_days_interview_to_offer,
                            0 as avg_days_offer_to_hire,
                            AVG(DATEDIFF(NOW(), a.applied_at)) as avg_days_total_time_to_hire,
                            MAX(DATEDIFF(NOW(), j.created_at)) as longest_open_job_days,
                            MIN(DATEDIFF(NOW(), j.created_at)) as fastest_filled_job_days
                        FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        WHERE " . implode(' AND ', $where);
            }

            $result = $this->db->fetchOne($sql, $params);
            
            // If no results or all values are NULL, return defaults
            if (!$result || (empty($result['avg_days_posted_to_application']) && empty($result['avg_days_total_time_to_hire']))) {
                $result = [
                    'avg_days_posted_to_application' => 0,
                    'avg_days_application_to_shortlisted' => 0,
                    'avg_days_shortlisted_to_interview' => 0,
                    'avg_days_interview_to_offer' => 0,
                    'avg_days_offer_to_hire' => 0,
                    'avg_days_total_time_to_hire' => 0,
                    'longest_open_job_days' => 0,
                    'fastest_filled_job_days' => 0
                ];
            }
        } catch (\Exception $e) {
            error_log("AnalyticsService::getTimeToHire error: " . $e->getMessage());
            $result = [
                'avg_days_posted_to_application' => 0,
                'avg_days_application_to_shortlisted' => 0,
                'avg_days_shortlisted_to_interview' => 0,
                'avg_days_interview_to_offer' => 0,
                'avg_days_offer_to_hire' => 0,
                'avg_days_total_time_to_hire' => 0,
                'longest_open_job_days' => 0,
                'fastest_filled_job_days' => 0
            ];
        }

        return [
            'avg_days_posted_to_application' => round((float)($result['avg_days_posted_to_application'] ?? 0), 1),
            'avg_days_application_to_shortlisted' => round((float)($result['avg_days_application_to_shortlisted'] ?? 0), 1),
            'avg_days_shortlisted_to_interview' => round((float)($result['avg_days_shortlisted_to_interview'] ?? 0), 1),
            'avg_days_interview_to_offer' => round((float)($result['avg_days_interview_to_offer'] ?? 0), 1),
            'avg_days_offer_to_hire' => round((float)($result['avg_days_offer_to_hire'] ?? 0), 1),
            'avg_days_total_time_to_hire' => round((float)($result['avg_days_total_time_to_hire'] ?? 0), 1),
            'longest_open_job_days' => (int)($result['longest_open_job_days'] ?? 0),
            'fastest_filled_job_days' => (int)($result['fastest_filled_job_days'] ?? 0)
        ];
    }

    /**
     * Get location-based analytics
     */
    public function getLocationAnalytics(int $employerId, ?int $jobId = null, ?string $category = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];

            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }

            if ($category) {
                $where[] = "j.category = :category";
                $params['category'] = $category;
            }

            $sql = "SELECT 
                        COALESCE(c.city, 'Unknown') as city,
                        COALESCE(c.state, 'Unknown') as state,
                        COALESCE(c.country, 'Unknown') as country,
                        COUNT(DISTINCT a.id) as applications_count,
                        COUNT(DISTINCT CASE WHEN a.status = 'hired' THEN a.id END) as hired_count
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN candidates c ON a.candidate_user_id = c.user_id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY c.city, c.state, c.country
                    ORDER BY applications_count DESC
                    LIMIT 50";

            $results = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getLocationAnalytics error: " . $e->getMessage());
            $results = [];
        }

        $byCity = [];
        $byState = [];
        $byCountry = [];
        $topCities = [];

        foreach ($results as $row) {
            $city = $row['city'];
            $state = $row['state'];
            $country = $row['country'];
            $apps = (int)$row['applications_count'];
            $hired = (int)$row['hired_count'];

            // By city
            if (!isset($byCity[$city])) {
                $byCity[$city] = ['applications' => 0, 'hired' => 0];
            }
            $byCity[$city]['applications'] += $apps;
            $byCity[$city]['hired'] += $hired;

            // By state
            if (!isset($byState[$state])) {
                $byState[$state] = ['applications' => 0, 'hired' => 0];
            }
            $byState[$state]['applications'] += $apps;
            $byState[$state]['hired'] += $hired;

            // By country
            if (!isset($byCountry[$country])) {
                $byCountry[$country] = ['applications' => 0, 'hired' => 0];
            }
            $byCountry[$country]['applications'] += $apps;
            $byCountry[$country]['hired'] += $hired;

            // Top cities with successful hiring
            if ($hired > 0) {
                $topCities[] = [
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'applications' => $apps,
                    'hired' => $hired,
                    'success_rate' => round(($hired / $apps) * 100, 2)
                ];
            }
        }

        // Sort top cities by success rate
        usort($topCities, fn($a, $b) => $b['success_rate'] <=> $a['success_rate']);

        return [
            'by_city' => $byCity,
            'by_state' => $byState,
            'by_country' => $byCountry,
            'top_cities' => array_slice($topCities, 0, 10)
        ];
    }

    public function getCandidateSources(int $employerId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];
            if ($dateFrom) {
                $where[] = "a.applied_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }
            if ($dateTo) {
                $where[] = "a.applied_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }
            $sql = "SELECT LOWER(COALESCE(a.source, 'unknown')) AS src, COUNT(*) AS cnt
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY src";
            $rows = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log('AnalyticsService::getCandidateSources error: ' . $e->getMessage());
            $rows = [];
        }
        $categories = [
            'paid' => 0,
            'organic' => 0,
            'referral' => 0,
            'social' => 0,
            'other' => 0
        ];
        foreach ($rows as $r) {
            $src = (string)($r['src'] ?? 'unknown');
            $cnt = (int)($r['cnt'] ?? 0);
            if (in_array($src, ['paid', 'adwords', 'campaign'])) {
                $categories['paid'] += $cnt;
            } elseif (in_array($src, ['portal', 'website', 'registration', 'organic'])) {
                $categories['organic'] += $cnt;
            } elseif (in_array($src, ['referral', 'employee_referral'])) {
                $categories['referral'] += $cnt;
            } elseif (in_array($src, ['social', 'social_media', 'facebook', 'linkedin', 'twitter'])) {
                $categories['social'] += $cnt;
            } else {
                $categories['other'] += $cnt;
            }
        }
        $total = array_sum($categories);
        $percentages = [];
        foreach ($categories as $k => $v) {
            $percentages[$k] = $total > 0 ? round(($v / $total) * 100, 2) : 0;
        }
        return [
            'counts' => $categories,
            'percentages' => $percentages,
            'total' => $total
        ];
    }

    public function getInterviewOutcomes(int $employerId, ?int $jobId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["i.employer_id = :employer_id"];
            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }
            if ($dateFrom) {
                $where[] = "i.scheduled_start >= :date_from";
                $params['date_from'] = $dateFrom;
            }
            if ($dateTo) {
                $where[] = "i.scheduled_start <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }
            $sql = "SELECT 
                        SUM(CASE WHEN i.status = 'completed' AND a.status IN ('offer','hired') THEN 1 ELSE 0 END) AS passed,
                        SUM(CASE WHEN i.status = 'completed' AND a.status = 'rejected' THEN 1 ELSE 0 END) AS failed,
                        SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) AS no_show,
                        COUNT(*) AS total
                    FROM interviews i
                    INNER JOIN applications a ON a.id = i.application_id
                    WHERE " . implode(' AND ', $where);
            $row = $this->db->fetchOne($sql, $params);
        } catch (\Exception $e) {
            error_log('AnalyticsService::getInterviewOutcomes error: ' . $e->getMessage());
            $row = null;
        }
        $passed = (int)($row['passed'] ?? 0);
        $failed = (int)($row['failed'] ?? 0);
        $noShow = (int)($row['no_show'] ?? 0);
        $total = (int)($row['total'] ?? 0);
        return [
            'passed' => $passed,
            'failed' => $failed,
            'no_show' => $noShow,
            'total' => $total
        ];
    }

    public function getOfferAcceptanceRate(int $employerId, ?int $jobId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];
            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }
            if ($dateFrom) {
                $where[] = "a.applied_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }
            if ($dateTo) {
                $where[] = "a.applied_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }
            $sql = "SELECT 
                        SUM(CASE WHEN a.status = 'offer' THEN 1 ELSE 0 END) AS offers_made,
                        SUM(CASE WHEN a.status = 'hired' THEN 1 ELSE 0 END) AS offers_accepted
                    FROM applications a
                    INNER JOIN jobs j ON a.job_id = j.id
                    WHERE " . implode(' AND ', $where);
            $row = $this->db->fetchOne($sql, $params);
        } catch (\Exception $e) {
            error_log('AnalyticsService::getOfferAcceptanceRate error: ' . $e->getMessage());
            $row = null;
        }
        $made = (int)($row['offers_made'] ?? 0);
        $accepted = (int)($row['offers_accepted'] ?? 0);
        $rate = $made > 0 ? round(($accepted / $made) * 100, 2) : 0;
        return [
            'offers_made' => $made,
            'offers_accepted' => $accepted,
            'acceptance_rate' => $rate
        ];
    }

    /**
     * Get job engagement metrics
     */
    public function getJobEngagement(int $employerId, ?int $jobId = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];

            if ($jobId) {
                $where[] = "j.id = :job_id";
                $params['job_id'] = $jobId;
            }

            // Check if job_engagement table exists, if not use basic query
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM job_engagement LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist, use basic query
            }

            if ($tableExists) {
                $sql = "SELECT 
                            j.id,
                            j.title,
                            j.slug,
                            COALESCE(je.views_count, 0) as views,
                            COALESCE(je.saves_count, 0) as saves,
                            COALESCE(je.shares_count, 0) as shares,
                            COALESCE(je.applications_count, 0) as applications,
                            COALESCE(je.engagement_score, 0) as engagement_score,
                            CASE 
                                WHEN COALESCE(je.views_count, 0) > 0 
                                THEN (COALESCE(je.applications_count, 0) / je.views_count) * 100
                                ELSE 0
                            END as application_rate
                        FROM jobs j
                        LEFT JOIN job_engagement je ON j.id = je.job_id
                        WHERE " . implode(' AND ', $where) . "
                        ORDER BY je.engagement_score DESC, j.created_at DESC";
            } else {
                // Fallback query without job_engagement table
                $sql = "SELECT 
                            j.id,
                            j.title,
                            j.slug,
                            COALESCE(j.views, 0) as views,
                            0 as saves,
                            0 as shares,
                            COALESCE((SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id), 0) as applications,
                            0 as engagement_score,
                            0 as application_rate
                        FROM jobs j
                        WHERE " . implode(' AND ', $where) . "
                        ORDER BY j.created_at DESC";
            }

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getJobEngagement error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate and update engagement score for a job
     */
    public function calculateEngagementScore(int $jobId, float $viewsWeight = 0.3, float $savesWeight = 0.2, float $sharesWeight = 0.2, float $applicationsWeight = 0.3): float
    {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN jvl.id IS NOT NULL THEN 1 ELSE 0 END), 0) as views,
                    COALESCE(SUM(CASE WHEN jsl.id IS NOT NULL THEN 1 ELSE 0 END), 0) as saves,
                    COALESCE(SUM(CASE WHEN jshl.id IS NOT NULL THEN 1 ELSE 0 END), 0) as shares,
                    COALESCE(COUNT(DISTINCT a.id), 0) as applications
                FROM jobs j
                LEFT JOIN job_views_log jvl ON j.id = jvl.job_id
                LEFT JOIN job_saves_log jsl ON j.id = jsl.job_id
                LEFT JOIN job_shares_log jshl ON j.id = jshl.job_id
                LEFT JOIN applications a ON j.id = a.job_id
                WHERE j.id = :job_id
                GROUP BY j.id";

        $result = $this->db->fetchOne($sql, ['job_id' => $jobId]);
        
        if (!$result) {
            $result = [
                'views' => 0,
                'saves' => 0,
                'shares' => 0,
                'applications' => 0
            ];
        }

        $views = (int)($result['views'] ?? 0);
        $saves = (int)($result['saves'] ?? 0);
        $shares = (int)($result['shares'] ?? 0);
        $applications = (int)($result['applications'] ?? 0);

        // Normalize scores (simple normalization - can be improved)
        $maxViewsResult = $this->db->fetchOne("SELECT MAX(view_count) as max FROM (SELECT COUNT(*) as view_count FROM job_views_log GROUP BY job_id) as sub");
        $maxViews = $maxViewsResult ? ($maxViewsResult['max'] ?? 1) : 1;
        
        $maxSavesResult = $this->db->fetchOne("SELECT MAX(save_count) as max FROM (SELECT COUNT(*) as save_count FROM job_saves_log GROUP BY job_id) as sub");
        $maxSaves = $maxSavesResult ? ($maxSavesResult['max'] ?? 1) : 1;
        
        $maxSharesResult = $this->db->fetchOne("SELECT MAX(share_count) as max FROM (SELECT COUNT(*) as share_count FROM job_shares_log GROUP BY job_id) as sub");
        $maxShares = $maxSharesResult ? ($maxSharesResult['max'] ?? 1) : 1;
        
        $maxApplicationsResult = $this->db->fetchOne("SELECT MAX(app_count) as max FROM (SELECT COUNT(*) as app_count FROM applications GROUP BY job_id) as sub");
        $maxApplications = $maxApplicationsResult ? ($maxApplicationsResult['max'] ?? 1) : 1;

        $normalizedViews = $maxViews > 0 ? ($views / $maxViews) * 100 : 0;
        $normalizedSaves = $maxSaves > 0 ? ($saves / $maxSaves) * 100 : 0;
        $normalizedShares = $maxShares > 0 ? ($shares / $maxShares) * 100 : 0;
        $normalizedApplications = $maxApplications > 0 ? ($applications / $maxApplications) * 100 : 0;

        $score = ($normalizedViews * $viewsWeight) + 
                 ($normalizedSaves * $savesWeight) + 
                 ($normalizedShares * $sharesWeight) + 
                 ($normalizedApplications * $applicationsWeight);

        // Update job_engagement table
        $this->db->query("INSERT INTO job_engagement (job_id, views_count, saves_count, shares_count, applications_count, engagement_score, updated_at)
                          VALUES (:job_id, :views, :saves, :shares, :applications, :score, NOW())
                          ON DUPLICATE KEY UPDATE
                          views_count = VALUES(views_count),
                          saves_count = VALUES(saves_count),
                          shares_count = VALUES(shares_count),
                          applications_count = VALUES(applications_count),
                          engagement_score = VALUES(engagement_score),
                          updated_at = NOW()",
            [
                'job_id' => $jobId,
                'views' => $views,
                'saves' => $saves,
                'shares' => $shares,
                'applications' => $applications,
                'score' => round($score, 2)
            ]
        );

        return round($score, 2);
    }

    /**
     * Get candidate quality analytics
     */
    public function getCandidateQuality(int $employerId, ?int $jobId = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            $where = ["j.employer_id = :employer_id"];

            if ($jobId) {
                $where[] = "a.job_id = :job_id";
                $params['job_id'] = $jobId;
            }

            // Check if candidate_quality_scores table exists
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM candidate_quality_scores LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if ($tableExists) {
                $sql = "SELECT 
                            a.job_id,
                            j.title as job_title,
                            COUNT(DISTINCT a.id) as total_applications,
                            AVG(COALESCE(cqs.resume_completeness_score, 0)) as avg_resume_completeness,
                            AVG(COALESCE(cqs.skill_match_percentage, 0)) as avg_skill_match,
                            AVG(COALESCE(cqs.interview_score, 0)) as avg_interview_score,
                            AVG(COALESCE(cqs.overall_score, 0)) as avg_overall_score
                        FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        LEFT JOIN candidate_quality_scores cqs ON a.id = cqs.application_id
                        WHERE " . implode(' AND ', $where) . "
                        GROUP BY a.job_id, j.title";
            } else {
                // Fallback without quality scores table
                $sql = "SELECT 
                            a.job_id,
                            j.title as job_title,
                            COUNT(DISTINCT a.id) as total_applications,
                            0 as avg_resume_completeness,
                            0 as avg_skill_match,
                            0 as avg_interview_score,
                            0 as avg_overall_score
                        FROM applications a
                        INNER JOIN jobs j ON a.job_id = j.id
                        WHERE " . implode(' AND ', $where) . "
                        GROUP BY a.job_id, j.title";
            }

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getCandidateQuality error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get communication analytics
     */
    public function getCommunicationAnalytics(int $employerId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $params = ['employer_id' => $employerId];
            
            // Check if communication_logs table exists
            $commTableExists = false;
            try {
                $this->db->query("SELECT 1 FROM communication_logs LIMIT 1");
                $commTableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if ($commTableExists) {
                $where = ["employer_id = :employer_id"];

                if ($dateFrom) {
                    $where[] = "created_at >= :date_from";
                    $params['date_from'] = $dateFrom;
                }

                if ($dateTo) {
                    $where[] = "created_at <= :date_to";
                    $params['date_to'] = $dateTo . ' 23:59:59';
                }

                $sql = "SELECT 
                            COUNT(CASE WHEN direction = 'sent' THEN 1 END) as messages_sent,
                            COUNT(CASE WHEN direction = 'received' THEN 1 END) as replies_received,
                            AVG(CASE WHEN response_time_seconds IS NOT NULL THEN response_time_seconds END) as avg_response_time_seconds,
                            COUNT(CASE WHEN communication_type = 'interview_invite' THEN 1 END) as interview_invites_sent,
                            COUNT(CASE WHEN communication_type = 'interview_invite' AND status = 'read' THEN 1 END) as interview_invites_read
                        FROM communication_logs
                        WHERE " . implode(' AND ', $where);

                $result = $this->db->fetchOne($sql, $params);
                if (!$result) {
                    $result = [
                        'messages_sent' => 0,
                        'replies_received' => 0,
                        'avg_response_time_seconds' => null,
                        'interview_invites_sent' => 0,
                        'interview_invites_read' => 0
                    ];
                }
            } else {
                $result = [
                    'messages_sent' => 0,
                    'replies_received' => 0,
                    'avg_response_time_seconds' => null,
                    'interview_invites_sent' => 0,
                    'interview_invites_read' => 0
                ];
            }

            // Get missed interviews (scheduled but not attended)
            $missedResult = ['missed_interviews' => 0];
            try {
                $missedSql = "SELECT COUNT(*) as missed_interviews
                              FROM interviews i
                              INNER JOIN applications a ON i.application_id = a.id
                              INNER JOIN jobs j ON a.job_id = j.id
                              WHERE j.employer_id = :employer_id
                              AND i.status = 'no_show'";
                if ($dateFrom) {
                    $missedSql .= " AND i.scheduled_start >= :date_from";
                }
                if ($dateTo) {
                    $missedSql .= " AND i.scheduled_start <= :date_to";
                }

                $missedResult = $this->db->fetchOne($missedSql, $params);
                if (!$missedResult) {
                    $missedResult = ['missed_interviews' => 0];
                }
            } catch (\Exception $e) {
                error_log("AnalyticsService::getCommunicationAnalytics - missed interviews error: " . $e->getMessage());
                $missedResult = ['missed_interviews' => 0];
            }

            return [
                'messages_sent' => (int)($result['messages_sent'] ?? 0),
                'replies_received' => (int)($result['replies_received'] ?? 0),
                'avg_response_time_hours' => round(((float)($result['avg_response_time_seconds'] ?? 0)) / 3600, 2),
                'interview_invites_sent' => (int)($result['interview_invites_sent'] ?? 0),
                'interview_invites_read' => (int)($result['interview_invites_read'] ?? 0),
                'missed_interviews' => (int)($missedResult['missed_interviews'] ?? 0)
            ];
        } catch (\Exception $e) {
            error_log("AnalyticsService::getCommunicationAnalytics error: " . $e->getMessage());
            return [
                'messages_sent' => 0,
                'replies_received' => 0,
                'avg_response_time_hours' => 0,
                'interview_invites_sent' => 0,
                'interview_invites_read' => 0,
                'missed_interviews' => 0
            ];
        }
    }

    /**
     * Get notification performance analytics
     */
    public function getNotificationPerformance(int $employerId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            // Check if notification_logs table exists
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM notification_logs LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if (!$tableExists) {
                return [
                    'total_sent' => 0,
                    'delivered' => 0,
                    'opened' => 0,
                    'failed' => 0,
                    'delivery_rate' => 0,
                    'open_rate' => 0,
                    'reminders_sent' => 0,
                    'reminder_success_rate' => 0
                ];
            }

            $params = ['employer_id' => $employerId];
            $where = ["employer_id = :employer_id"];

            if ($dateFrom) {
                $where[] = "created_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = "created_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }

            $sql = "SELECT 
                        COUNT(*) as total_sent,
                        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                        COUNT(CASE WHEN status = 'opened' THEN 1 END) as opened,
                        COUNT(CASE WHEN status = 'failed' OR status = 'bounced' THEN 1 END) as failed,
                        COUNT(CASE WHEN template_key LIKE '%reminder%' THEN 1 END) as reminders_sent,
                        COUNT(CASE WHEN template_key LIKE '%reminder%' AND status = 'opened' THEN 1 END) as reminders_opened
                    FROM notification_logs
                    WHERE " . implode(' AND ', $where);

            $result = $this->db->fetchOne($sql, $params);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getNotificationPerformance error: " . $e->getMessage());
            $result = null;
        }
        
        if (!$result) {
            $result = [
                'total_sent' => 0,
                'delivered' => 0,
                'opened' => 0,
                'failed' => 0,
                'reminders_sent' => 0,
                'reminders_opened' => 0
            ];
        }

        $totalSent = (int)($result['total_sent'] ?? 0);
        $delivered = (int)($result['delivered'] ?? 0);
        $opened = (int)($result['opened'] ?? 0);
        $failed = (int)($result['failed'] ?? 0);
        $remindersSent = (int)($result['reminders_sent'] ?? 0);
        $remindersOpened = (int)($result['reminders_opened'] ?? 0);

        return [
            'total_sent' => $totalSent,
            'delivered' => $delivered,
            'opened' => $opened,
            'failed' => $failed,
            'delivery_rate' => $totalSent > 0 ? round(($delivered / $totalSent) * 100, 2) : 0,
            'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0,
            'reminders_sent' => $remindersSent,
            'reminder_success_rate' => $remindersSent > 0 ? round(($remindersOpened / $remindersSent) * 100, 2) : 0
        ];
    }

    /**
     * Get employer activity tracking
     */
    public function getEmployerActivity(int $employerId, int $days = 30): array
    {
        try {
            // Check if activity_logs table exists
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM activity_logs LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if (!$tableExists) {
                return [
                    'daily_activity' => [],
                    'summary' => [
                        'days_with_job_creation' => 0,
                        'total_profiles_viewed' => 0,
                        'total_resumes_downloaded' => 0,
                        'first_action' => null,
                        'last_action' => null
                    ]
                ];
            }

            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(DISTINCT CASE WHEN action = 'job_created' THEN id END) as jobs_created,
                        COUNT(DISTINCT CASE WHEN action = 'application_viewed' THEN id END) as profiles_viewed,
                        COUNT(DISTINCT CASE WHEN action = 'resume_downloaded' THEN id END) as resumes_downloaded,
                        COUNT(*) as total_actions
                    FROM activity_logs
                    WHERE actor_type = 'employer'
                    AND actor_id = :employer_id
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";

            $results = $this->db->fetchAll($sql, ['employer_id' => $employerId, 'days' => $days]);

            // Get summary stats
            $summarySql = "SELECT 
                            COUNT(DISTINCT CASE WHEN action = 'job_created' THEN DATE(created_at) END) as days_with_job_creation,
                            COUNT(DISTINCT CASE WHEN action = 'application_viewed' THEN id END) as total_profiles_viewed,
                            COUNT(DISTINCT CASE WHEN action = 'resume_downloaded' THEN id END) as total_resumes_downloaded,
                            MIN(created_at) as first_action,
                            MAX(created_at) as last_action
                          FROM activity_logs
                          WHERE actor_type = 'employer'
                          AND actor_id = :employer_id
                          AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";

            $summary = $this->db->fetchOne($summarySql, ['employer_id' => $employerId, 'days' => $days]);
            if (!$summary) {
                $summary = [
                    'days_with_job_creation' => 0,
                    'total_profiles_viewed' => 0,
                    'total_resumes_downloaded' => 0,
                    'first_action' => null,
                    'last_action' => null
                ];
            }
        } catch (\Exception $e) {
            error_log("AnalyticsService::getEmployerActivity error: " . $e->getMessage());
            $results = [];
            $summary = [
                'days_with_job_creation' => 0,
                'total_profiles_viewed' => 0,
                'total_resumes_downloaded' => 0,
                'first_action' => null,
                'last_action' => null
            ];
        }

        return [
            'daily_activity' => $results,
            'summary' => [
                'days_with_job_creation' => (int)($summary['days_with_job_creation'] ?? 0),
                'total_profiles_viewed' => (int)($summary['total_profiles_viewed'] ?? 0),
                'total_resumes_downloaded' => (int)($summary['total_resumes_downloaded'] ?? 0),
                'first_action' => $summary['first_action'] ?? null,
                'last_action' => $summary['last_action'] ?? null
            ]
        ];
    }

    /**
     * Get subscription & ROI analytics
     */
    public function getSubscriptionROI(int $employerId): array
    {
        try {
            // Check if subscription tables exist
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM employer_subscriptions LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if (!$tableExists) {
                return [
                    'has_subscription' => false,
                    'message' => 'No active subscription found'
                ];
            }

            // Get subscription info - use price based on billing_cycle
            // Note: subscription_plans has price_monthly, price_quarterly, price_annual (not 'price')
            // employer_subscriptions has job_posts_used (not job_slots_used), and max_job_posts is in subscription_plans
            $subscriptionSql = "SELECT 
                                  sp.name as plan_name,
                                  CASE 
                                      WHEN es.billing_cycle = 'monthly' THEN sp.price_monthly
                                      WHEN es.billing_cycle = 'quarterly' THEN sp.price_quarterly
                                      WHEN es.billing_cycle = 'annual' THEN sp.price_annual
                                      ELSE COALESCE(sp.price_monthly, 0)
                                  END as plan_price,
                                  es.started_at,
                                  es.expires_at,
                                  es.billing_cycle,
                                  COALESCE(sp.max_job_posts, 0) as job_slots_total,
                                  COALESCE(es.job_posts_used, 0) as job_slots_used
                                FROM employer_subscriptions es
                                INNER JOIN subscription_plans sp ON es.plan_id = sp.id
                                WHERE es.employer_id = :employer_id
                                AND es.status = 'active'
                                ORDER BY es.started_at DESC
                                LIMIT 1";

            $subscription = $this->db->fetchOne($subscriptionSql, ['employer_id' => $employerId]);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getSubscriptionROI error: " . $e->getMessage());
            return [
                'has_subscription' => false,
                'message' => 'No active subscription found'
            ];
        }

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'message' => 'No active subscription found'
            ];
        }

        // Get job and hire stats for ROI calculation
        $roiSql = "SELECT 
                     COUNT(DISTINCT j.id) as jobs_posted,
                     COUNT(DISTINCT a.id) as total_applications,
                     COUNT(DISTINCT CASE WHEN a.status = 'hired' THEN a.id END) as hires
                   FROM jobs j
                   LEFT JOIN applications a ON j.id = a.job_id
                   WHERE j.employer_id = :employer_id
                   AND j.created_at >= :started_at";

        $roi = $this->db->fetchOne($roiSql, [
            'employer_id' => $employerId,
            'started_at' => $subscription['started_at']
        ]);
        
        if (!$roi) {
            $roi = [
                'jobs_posted' => 0,
                'total_applications' => 0,
                'hires' => 0
            ];
        }

        $planPrice = (float)($subscription['plan_price'] ?? 0);
        $jobsPosted = (int)($roi['jobs_posted'] ?? 0);
        $hires = (int)($roi['hires'] ?? 0);

        return [
            'has_subscription' => true,
            'plan_name' => $subscription['plan_name'] ?? 'N/A',
            'plan_price' => $planPrice,
            'started_at' => $subscription['started_at'],
            'expires_at' => $subscription['expires_at'],
            'job_slots_total' => (int)($subscription['job_slots_total'] ?? 0),
            'job_slots_used' => (int)($subscription['job_slots_used'] ?? 0),
            'job_slots_remaining' => (int)($subscription['job_slots_total'] ?? 0) - (int)($subscription['job_slots_used'] ?? 0),
            'jobs_posted' => $jobsPosted,
            'hires' => $hires,
            'cost_per_job' => $jobsPosted > 0 ? round($planPrice / $jobsPosted, 2) : $planPrice,
            'cost_per_hire' => $hires > 0 ? round($planPrice / $hires, 2) : ($planPrice > 0 ? $planPrice : 0),
            'hiring_roi' => $planPrice > 0 ? round(($hires / $planPrice) * 100, 2) : 0
        ];
    }

    /**
     * Get security & audit logs
     */
    public function getSecurityLogs(int $employerId, ?string $dateFrom = null, ?string $dateTo = null, ?string $type = null, int $page = 1, int $perPage = 50): array
    {
        try {
            // Check if login_history table exists
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM login_history LIMIT 1");
                $tableExists = true;
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            if (!$tableExists) {
                return [
                    'logs' => [],
                    'pagination' => [
                        'page' => $page,
                        'per_page' => $perPage,
                        'total' => 0,
                        'total_pages' => 0
                    ]
                ];
            }

            $params = ['employer_id' => $employerId];
            $where = ["user_id = :employer_id", "user_type = 'employer'"];

            if ($dateFrom) {
                $where[] = "logged_in_at >= :date_from";
                $params['date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = "logged_in_at <= :date_to";
                $params['date_to'] = $dateTo . ' 23:59:59';
            }

            if ($type === 'successful') {
                $where[] = "login_successful = 1";
            } elseif ($type === 'failed') {
                $where[] = "login_successful = 0";
            }

            $offset = ($page - 1) * $perPage;

            // PDO doesn't support named parameters for LIMIT/OFFSET, use integers directly
            $sql = "SELECT 
                        id,
                        ip_address,
                        user_agent,
                        login_successful,
                        failure_reason,
                        logged_in_at,
                        logged_out_at,
                        session_duration_seconds
                    FROM login_history
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY logged_in_at DESC
                    LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

            $logs = $this->db->fetchAll($sql, $params);

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM login_history WHERE " . implode(' AND ', $where);
            $countResult = $this->db->fetchOne($countSql, $params);
            $total = (int)(($countResult ? $countResult['total'] : 0) ?? 0);
        } catch (\Exception $e) {
            error_log("AnalyticsService::getSecurityLogs error: " . $e->getMessage());
            return [
                'logs' => [],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0
                ]
            ];
        }

        return [
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
}

