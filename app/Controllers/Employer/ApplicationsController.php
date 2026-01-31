<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateSkill;
use App\Models\CandidateLanguage;
use App\Models\CandidateView;
use App\Services\ESService;
use App\Services\JobMatchService;

class ApplicationsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $jobId = (int)($request->get('job_id') ?? 0);
        $status = $request->get('status');
        $source = $request->get('source'); // 'database' or null (default: applications)
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'date'); // date, location, interest
        $locationFilter = $request->get('location');
        $interestFilter = $request->get('interest'); // shortlisted, rejected, undecided
        $includeApplied = $request->get('include_applied', '1'); // '1' or '0'
        $hasResume = $request->get('has_resume'); // '1' means only candidates with resume_url
        $activeIn = (int)($request->get('active_in') ?? 0); // days
        $locationDistance = (int)($request->get('location_distance') ?? 0); // 0, 5, 10, 25, 50
        $minExp = $request->get('min_experience');
        $maxExp = $request->get('max_experience');
        $salaryMin = $request->get('salary_min');
        $salaryMax = $request->get('salary_max');
        $educationFilter = $request->get('education'); // array or comma-separated
        $skillsFilter = $request->get('skills'); // array or comma-separated
        $languageFilter = $request->get('language');

        $db = \App\Core\Database::getInstance();
        
        // Subscription info early to set list limits
        $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        $plan = $subscription ? $subscription->plan() : null;
        $subscriptionActive = $subscription ? ($subscription->isActive() || $subscription->isInGracePeriod()) : false;
        $listLimit = $subscriptionActive ? 200 : 45;
        
        // Get database count (matched candidates who haven't applied)
        $databaseCount = 0;
        if ($jobId) {
            $dbCountSql = "SELECT COUNT(*) as c
                          FROM candidate_job_scores cjs 
                          LEFT JOIN applications a ON a.job_id = cjs.job_id AND a.candidate_user_id = (SELECT user_id FROM candidates WHERE id = cjs.candidate_id)
                          WHERE cjs.job_id = :job_id AND a.id IS NULL";
            $databaseCountRow = $db->fetchOne($dbCountSql, ['job_id' => $jobId]);
            $databaseCount = $databaseCountRow ? ($databaseCountRow['c'] ?? 0) : 0;
        }

        // Base query for applications
        if ($source === 'database' && $jobId) {
             // Database/Matched Candidates Query
             $baseSql = "FROM candidate_job_scores cjs
                JOIN candidates c ON cjs.candidate_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN jobs j ON cjs.job_id = j.id
                LEFT JOIN applications a ON a.job_id = cjs.job_id AND a.candidate_user_id = u.id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                WHERE cjs.job_id = :job_id AND a.id IS NULL";
             
             $baseParams = ['job_id' => $jobId];
        } else {
            // Standard Applications Query
            $baseSql = "FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.candidate_user_id = u.id
                LEFT JOIN candidates c ON c.user_id = u.id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = j.id
                WHERE j.employer_id = :employer_id";
            
            $baseParams = ['employer_id' => $employer->id];
        }
        // Independent query for status counts (always based on actual applications)
        $countSql = "FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            WHERE j.employer_id = :employer_id";
        $countParams = ['employer_id' => $employer->id];
        
        if ($jobId) {
            $countSql .= " AND a.job_id = :job_id";
            $countParams['job_id'] = $jobId;
        }

        $statusCountsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN a.status = 'applied' THEN 1 ELSE 0 END) as new_count,
                    SUM(CASE WHEN a.status IN ('screening', 'applied') THEN 1 ELSE 0 END) as reviewing_count,
                    SUM(CASE WHEN a.status = 'offer' THEN 1 ELSE 0 END) as contacting_count,
                    SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END) as interviewing_count,
                    SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(CASE WHEN a.status = 'hired' THEN 1 ELSE 0 END) as hired_count,
                    SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted_count,
                    SUM(CASE WHEN a.status NOT IN ('shortlisted', 'rejected', 'hired') THEN 1 ELSE 0 END) as undecided_count
                " . $countSql;
        
        $statusCounts = $db->fetchOne($statusCountsSql, $countParams);
        $statusCounts = $statusCounts ?: [
            'total' => 0,
            'new_count' => 0,
            'reviewing_count' => 0,
            'contacting_count' => 0,
            'interviewing_count' => 0,
            'rejected_count' => 0,
            'hired_count' => 0,
            'shortlisted_count' => 0,
            'undecided_count' => 0
        ];

        // Build WHERE conditions
        $whereConditions = [];
        $params = $baseParams;

        $currentJob = null;
        if ($jobId) {
            $currentJob = Job::find($jobId);
            // Verify job belongs to employer
            if ($currentJob && $currentJob->employer_id != $employer->id) {
                $currentJob = null;
                $jobId = 0;
            } else {
                // Only filter by application job_id if NOT in database mode
                if ($source !== 'database') {
                    $whereConditions[] = "a.job_id = :job_id";
                }
                $params['job_id'] = $jobId;
                
                // Auto-fill location filter from job if not set
                if (empty($locationFilter) && !empty($currentJob->city)) {
                    $locationFilter = $currentJob->city;
                }
            }
        }

        // Map UI status filters to database statuses
        if ($status && $source !== 'database') {
            $statusMap = [
                'new' => 'applied',
                'reviewing' => ['screening', 'applied'],
                'contacting' => 'offer',
                'interviewing' => 'interview',
                'rejected' => 'rejected',
                'hired' => 'hired',
                'shortlist' => 'shortlisted',
                'undecided' => ['applied', 'screening', 'interview', 'offer']
            ];
            
            if (isset($statusMap[$status])) {
                if (is_array($statusMap[$status])) {
                    $placeholders = [];
                    foreach ($statusMap[$status] as $idx => $s) {
                        $key = 'status_' . $idx;
                        $placeholders[] = ":{$key}";
                        $params[$key] = $s;
                    }
                    $whereConditions[] = "a.status IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $whereConditions[] = "a.status = :status";
                    $params['status'] = $statusMap[$status];
                }
            }
        }

        if ($locationFilter) {
            if ($locationDistance > 0) {
                if ($locationDistance <= 10) {
                    // Strict City Match
                    $whereConditions[] = "c.city LIKE :location";
                } elseif ($locationDistance <= 50) {
                    // City or State
                    $whereConditions[] = "(c.city LIKE :location OR c.state LIKE :location)";
                } else {
                    // City, State or Country
                    $whereConditions[] = "(c.city LIKE :location OR c.state LIKE :location OR c.country LIKE :location)";
                }
            } else {
                // Default lenient match
                $whereConditions[] = "(c.city LIKE :location OR c.state LIKE :location OR c.country LIKE :location)";
            }
            $params['location'] = "%{$locationFilter}%";
        }

        if ($skillsFilter) {
            if (is_array($skillsFilter)) {
                $skillConds = [];
                foreach ($skillsFilter as $idx => $skill) {
                    $key = "skill_filter_{$idx}";
                    $skillConds[] = "c.skills_data LIKE :{$key}";
                    $params[$key] = "%{$skill}%";
                }
                if (!empty($skillConds)) {
                    $whereConditions[] = "(" . implode(' OR ', $skillConds) . ")";
                }
            } else {
                $whereConditions[] = "c.skills_data LIKE :skill_filter";
                $params['skill_filter'] = "%{$skillsFilter}%";
            }
        }

        if ($languageFilter) {
            $whereConditions[] = "c.languages_data LIKE :language_filter";
            $params['language_filter'] = "%{$languageFilter}%";
        }

        if ($interestFilter) {
            if ($interestFilter === 'shortlisted') {
                $whereConditions[] = "a.status = 'shortlisted'";
            } elseif ($interestFilter === 'rejected') {
                $whereConditions[] = "a.status = 'rejected'";
            } elseif ($interestFilter === 'undecided') {
                $whereConditions[] = "a.status NOT IN ('shortlisted', 'rejected', 'hired')";
            }
        }

        if ($search) {
            $whereConditions[] = "(c.full_name LIKE :search OR u.email LIKE :search OR j.title LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($hasResume === '1') {
            $whereConditions[] = "(c.resume_url IS NOT NULL AND c.resume_url <> '')";
        }

        if ($activeIn > 0) {
            $since = date('Y-m-d H:i:s', time() - ($activeIn * 86400));
            $whereConditions[] = "u.last_login >= :active_since";
            $params['active_since'] = $since;
        }
        
        if ($salaryMin !== null && $salaryMin !== '') {
            $whereConditions[] = "c.expected_salary_min >= :salary_min";
            $params['salary_min'] = $salaryMin;
        }
        
        if ($salaryMax !== null && $salaryMax !== '') {
            $whereConditions[] = "c.expected_salary_max <= :salary_max";
            $params['salary_max'] = $salaryMax;
        }
        
        $whereClause = !empty($whereConditions) ? " AND " . implode(" AND ", $whereConditions) : "";

        // Build ORDER BY clause
        // Premium-first ranking with sensible fallbacks
        $premiumOrder = "CASE WHEN c.is_premium = 1 AND c.premium_expires_at > NOW() THEN 1 ELSE 0 END DESC, c.profile_strength DESC, u.last_login DESC";
        
        if ($source === 'database') {
             // Database sorting (no application data)
             switch ($sortBy) {
                case 'date':
                    $orderBy = $premiumOrder . ", c.id DESC"; // Use ID as proxy for new candidates
                    break;
                case 'location':
                    $orderBy = $premiumOrder . ", c.city ASC, c.state ASC, c.country ASC";
                    break;
                case 'match_score':
                default:
                    $orderBy = $premiumOrder . ", cjs.overall_match_score DESC";
                    break;
            }
        } else {
            // Application sorting
            $orderBy = $premiumOrder . ", a.applied_at DESC"; // Default
            switch ($sortBy) {
                case 'date':
                    $orderBy = $premiumOrder . ", a.applied_at DESC";
                    break;
                case 'location':
                    $orderBy = $premiumOrder . ", c.city ASC, c.state ASC, c.country ASC";
                    break;
                case 'interest':
                    $orderBy = $premiumOrder . ", CASE 
                        WHEN a.status = 'shortlisted' THEN 1
                        WHEN a.status = 'rejected' THEN 3
                        ELSE 2
                    END, a.applied_at DESC";
                    break;
                case 'match_score':
                    $orderBy = $premiumOrder . ", CASE WHEN cjs.overall_match_score IS NOT NULL THEN 0 ELSE 1 END,
                               cjs.overall_match_score DESC, a.applied_at DESC";
                    break;
            }
        }

        // Main query
        $sql = "SELECT 
                    a.*, 
                    a.id as application_id,
                    j.id as job_id,
                    j.title as job_title,
                    j.slug as job_slug,
                    j.currency as job_currency,
                    j.salary_min as job_salary_min,
                    j.salary_max as job_salary_max,
                    u.email as candidate_email, 
                    u.phone,
                    c.mobile as candidate_mobile,
                    c.id as candidate_id,
                    c.full_name,
                    c.city,
                    c.state,
                    c.country,
                    c.profile_picture,
                    c.resume_url,
                    c.current_salary,
                    c.expected_salary_min,
                    c.expected_salary_max,
                    c.education_data,
                    c.experience_data,
                    c.skills_data,
                    c.languages_data,
                    rs_cert.section_data as certifications_data,
                    cjs.overall_match_score,
                    cjs.skill_score,
                    cjs.experience_score,
                    cjs.education_score,
                    cjs.recommendation,
                    cjs.matched_skills,
                    cjs.missing_skills,
                    cjs.extra_relevant_skills,
                    cjs.summary as match_summary
                " . $baseSql . $whereClause . " ORDER BY {$orderBy} LIMIT {$listLimit}";

        $applications = $db->fetchAll($sql, $params);

        // Get activity events for each application
        $applicationIds = array_column($applications, 'application_id');
        $events = [];
        if (!empty($applicationIds)) {
            $placeholders = implode(',', array_fill(0, count($applicationIds), '?'));
            $eventsSql = "SELECT application_id, to_status, created_at, comment
                         FROM application_events
                         WHERE application_id IN ({$placeholders})
                         ORDER BY created_at DESC";
            $eventsData = $db->fetchAll($eventsSql, $applicationIds);
            
            foreach ($eventsData as $event) {
                $appId = $event['application_id'];
                if (!isset($events[$appId])) {
                    $events[$appId] = [];
                }
                $events[$appId][] = $event;
            }
        }

        // Initialize match service
        $matchService = new JobMatchService();

        // Decode JSON fields and add activity for each application
        foreach ($applications as &$app) {
            $appId = $app['application_id'];
            $candidateId = $app['candidate_id'] ?? null;
            $jobId = $app['job_id'] ?? null;
            
            // Decode JSON fields and add activity for each application
            $matchedSkills = !empty($app['matched_skills']) ? json_decode($app['matched_skills'], true) : [];
            $app['matched_skills'] = is_array($matchedSkills) ? $matchedSkills : [];

            $missingSkills = !empty($app['missing_skills']) ? json_decode($app['missing_skills'], true) : [];
            $app['missing_skills'] = is_array($missingSkills) ? $missingSkills : [];

            $extraSkills = !empty($app['extra_relevant_skills']) ? json_decode($app['extra_relevant_skills'], true) : [];
            $app['extra_relevant_skills'] = is_array($extraSkills) ? $extraSkills : [];
            
            // Calculate Experience Years
            $expData = !empty($app['experience_data']) ? json_decode($app['experience_data'], true) : [];
            $totalMonths = 0;
            if (is_array($expData)) {
                foreach ($expData as $exp) {
                     if (!empty($exp['start_date'])) {
                         try {
                             $start = new \DateTime($exp['start_date']);
                             $end = (!empty($exp['end_date']) && !$exp['is_current']) ? new \DateTime($exp['end_date']) : new \DateTime();
                             $diff = $start->diff($end);
                             $totalMonths += ($diff->y * 12) + $diff->m;
                         } catch (\Exception $e) {}
                     }
                }
            }
            $years = round($totalMonths / 12, 1);
            $app['experience_years'] = $years;

            // Filter Experience
            if (($minExp !== null && $minExp !== '' && $years < (float)$minExp) || 
                ($maxExp !== null && $maxExp !== '' && $years > (float)$maxExp)) {
                $app['_remove'] = true;
                continue;
            }

            // Filter Education
            if ($educationFilter) {
                $eduLevels = is_array($educationFilter) ? $educationFilter : explode(',', $educationFilter);
                $hasEdu = false;
                $eduData = !empty($app['education_data']) ? json_decode($app['education_data'], true) : [];
                
                if (is_array($eduData)) {
                    foreach ($eduData as $edu) {
                        foreach ($eduLevels as $level) {
                            if (stripos($edu['degree'] ?? '', $level) !== false) {
                                $hasEdu = true;
                                break 2;
                            }
                        }
                    }
                }
                
                if (!$hasEdu) {
                    $app['_remove'] = true;
                    continue;
                }
            }

            // Decode Certifications
            if (!empty($app['certifications_data'])) {
                $certData = json_decode($app['certifications_data'], true);
                // section_data might be wrapped or just the array. Usually resume_sections.section_data is the content.
                // Assuming it's an array or object.
                if (is_array($certData)) {
                    $app['certifications'] = $certData['items'] ?? $certData;
                } else {
                    $app['certifications'] = [];
                }
            } else {
                $app['certifications'] = [];
            }
            
            // Calculate match if not already calculated or if using database method
            if (empty($app['overall_match_score']) && $candidateId && $jobId) {
                try {
                    $matchData = $matchService->calculateMatch($candidateId, $jobId, true);
                    $app['overall_match_score'] = $matchData['overall_match_score'];
                    $app['skill_match_score'] = $matchData['skill_match_score'];
                    $app['experience_match_score'] = $matchData['experience_match_score'];
                    $app['education_match_score'] = $matchData['education_match_score'];
                    $app['matched_skills'] = $matchData['matched_skills'];
                    $app['missing_skills'] = $matchData['missing_skills'];
                    $app['extra_relevant_skills'] = $matchData['extra_relevant_skills'];
                    $app['recommendation'] = $matchData['recommendation'];
                    $app['match_summary'] = $matchData['summary'];
                } catch (\Exception $e) {
                    error_log("Error calculating match for application {$appId}: " . $e->getMessage());
                }
            }
            
            // Add activity/events
            $app['activity'] = $events[$appId] ?? [];
            $app['latest_activity'] = !empty($app['activity']) ? $app['activity'][0] : null;
            
            // Format applied date
            $app['applied_at_formatted'] = $this->formatTimeAgo($app['applied_at'] ?? date('Y-m-d H:i:s'));
            
            // Get location display
            $locationParts = array_filter([$app['city'] ?? '', $app['state'] ?? '', $app['country'] ?? '']);
            $app['location_display'] = !empty($locationParts) ? implode(', ', $locationParts) : 'Not specified';
        }

        // Remove filtered items
        $applications = array_filter($applications, function($a) {
            return !isset($a['_remove']);
        });

        // Fallback: if few applications, suggest candidates
        if (count($applications) < 10) {
            $fallbackJobId = $jobId ?: (int)(Job::where('employer_id', '=', $employer->id)->orderBy('created_at', 'DESC')->first()->attributes['id'] ?? 0);
            
            if ($fallbackJobId) {
                $fallbackJob = Job::find((int)$fallbackJobId);
                $jobCategory = $fallbackJob->attributes['category'] ?? '';
                
                // Exclude existing applicants
                $excludeIds = !empty($applications) ? implode(',', array_column($applications, 'candidate_id')) : '0';
                
                // Build suggested candidates query
                // Matches by category (via primary resume) or generic fallback
                $suggestSql = "SELECT 
                                c.id as candidate_id,
                                u.id as candidate_user_id,
                                c.full_name,
                                c.city, c.state, c.country,
                                c.profile_picture,
                                c.resume_url,
                                u.email as candidate_email, 
                                u.phone,
                                c.current_salary,
                                c.expected_salary_min,
                                c.expected_salary_max,
                                c.education_data,
                                c.experience_data,
                                c.skills_data,
                                c.languages_data,
                                rs_cert.section_data as certifications_data,
                                cjs.overall_match_score,
                                cjs.skill_score,
                                cjs.experience_score,
                                cjs.education_score,
                                cjs.recommendation,
                                cjs.summary as match_summary,
                                cjs.matched_skills,
                                cjs.missing_skills,
                                cjs.extra_relevant_skills,
                                'suggested' as status,
                                :job_id as job_id,
                                :job_title as job_title,
                                :job_currency as job_currency,
                                :job_slug as job_slug
                               FROM candidates c
                               INNER JOIN users u ON u.id = c.user_id
                               LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                               LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                               LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = :job_id_join
                               WHERE c.id NOT IN ({$excludeIds})
                               AND (
                                   r.job_category = :category 
                                   OR c.skills_data LIKE :category_like
                                   OR :category_empty = 1
                               )
                               ORDER BY cjs.overall_match_score DESC, c.profile_strength DESC, c.created_at DESC
                               LIMIT " . (45 - count($applications)); // Fill up to 45
                
                $suggestParams = [
                    'job_id' => $fallbackJob->attributes['id'],
                    'job_title' => $fallbackJob->attributes['title'],
                    'job_currency' => $fallbackJob->attributes['currency'],
                    'job_slug' => $fallbackJob->attributes['slug'],
                    'job_id_join' => $fallbackJob->attributes['id'],
                    'category' => $jobCategory,
                    'category_like' => "%{$jobCategory}%",
                    'category_empty' => empty($jobCategory) ? 1 : 0
                ];
                
                $suggested = $db->fetchAll($suggestSql, $suggestParams);
                
                // Merge suggested candidates
                foreach ($suggested as $s) {
                    $applications[] = $s;
                }
            }
        }

        // Apply include_applied filter post-merge (suggested have no application_id)
        if ((string)$includeApplied === '0') {
            $applications = array_values(array_filter($applications, function($row) {
                return (($row['status'] ?? '') === 'suggested') || empty($row['application_id']);
            }));
        }

        // Location distance filtering (approximate city/state matching)
        if (!empty($locationDistance) && ($jobId || !empty($applications))) {
            $jobLocationsCache = [];
            $getJobLocations = function(int $jid) use ($db, &$jobLocationsCache): array {
                if (isset($jobLocationsCache[$jid])) return $jobLocationsCache[$jid];
                $rows = $db->fetchAll(
                    "SELECT c.name as city, s.name as state, co.name as country
                     FROM job_locations jl
                     LEFT JOIN cities c ON jl.city_id = c.id
                     LEFT JOIN states s ON jl.state_id = s.id
                     LEFT JOIN countries co ON jl.country_id = co.id
                     WHERE jl.job_id = :job_id",
                    ['job_id' => $jid]
                );
                $cities = array_filter(array_map(fn($r) => strtolower(trim($r['city'] ?? '')), $rows));
                $states = array_filter(array_map(fn($r) => strtolower(trim($r['state'] ?? '')), $rows));
                $countries = array_filter(array_map(fn($r) => strtolower(trim($r['country'] ?? '')), $rows));
                return $jobLocationsCache[$jid] = [
                    'cities' => array_unique($cities),
                    'states' => array_unique($states),
                    'countries' => array_unique($countries),
                ];
            };
            $strictCity = ($locationDistance === 'city') || ((int)$locationDistance <= 10);
            $applications = array_values(array_filter($applications, function($row) use ($getJobLocations, $strictCity) {
                $jid = (int)($row['job_id'] ?? 0);
                if (!$jid) return true;
                $locs = $getJobLocations($jid);
                $candCity = strtolower(trim($row['city'] ?? ''));
                $candState = strtolower(trim($row['state'] ?? ''));
                if ($strictCity) {
                    return $candCity && in_array($candCity, $locs['cities'], true);
                }
                return ($candCity && in_array($candCity, $locs['cities'], true)) || ($candState && in_array($candState, $locs['states'], true));
            }));
        }

        // Get all jobs for filter dropdown
        $jobs = Job::where('employer_id', '=', $employer->id)
            ->orderBy('title', 'ASC')
            ->get();

        // Subscription gating info
        $featureAccess = [
            'has_subscription' => (bool)$subscription,
            'subscription_active' => $subscriptionActive,
            'candidate_mobile_visible' => $plan ? $plan->hasFeature('candidate_mobile_visible') : false,
            'resume_download_enabled' => $plan ? $plan->hasFeature('resume_download_enabled') : false,
            'advanced_filters' => $plan ? $plan->hasFeature('advanced_filters') : false,
            'chat_enabled' => $plan ? $plan->hasFeature('chat_enabled') : false
        ];
        $usage = [
            'contacts_used' => (int)($subscription->attributes['contacts_used_this_month'] ?? 0),
            'contacts_limit' => $plan ? (int)$plan->getLimit('max_contacts_per_month') : 0,
            'contacts_remaining' => $plan ? max(0, (int)$plan->getLimit('max_contacts_per_month') - (int)($subscription->attributes['contacts_used_this_month'] ?? 0)) : 0,
            'downloads_used' => (int)($subscription->attributes['resume_downloads_used_this_month'] ?? 0),
            'downloads_limit' => $plan ? (int)$plan->getLimit('max_resume_downloads') : 0,
            'downloads_remaining' => $plan ? max(0, (int)$plan->getLimit('max_resume_downloads') - (int)($subscription->attributes['resume_downloads_used_this_month'] ?? 0)) : 0,
            'chat_used' => (int)($subscription->attributes['chat_messages_used_this_month'] ?? 0),
            'chat_limit' => $plan ? (int)$plan->getLimit('max_chat_messages') : 0,
            'chat_remaining' => $plan ? max(0, (int)$plan->getLimit('max_chat_messages') - (int)($subscription->attributes['chat_messages_used_this_month'] ?? 0)) : 0,
            'plan_name' => $plan ? ($plan->attributes['name'] ?? '') : 'Free'
        ];

        $databaseCount = $db->fetchOne("SELECT COUNT(*) as c FROM candidates")['c'] ?? 0;
        if ($jobId) {
             $dbCountSql = "SELECT COUNT(*) 
                          FROM candidate_job_scores cjs 
                          LEFT JOIN applications a ON a.job_id = cjs.job_id AND a.candidate_user_id = (SELECT user_id FROM candidates WHERE id = cjs.candidate_id)
                          WHERE cjs.job_id = :job_id AND a.id IS NULL";
             $databaseCount = $db->fetchOne($dbCountSql, ['job_id' => $jobId])['c'] ?? 0;
        }

        // Employer contact phone for WhatsApp templates
        $empUser = \App\Models\User::find((int)($employer->attributes['user_id'] ?? $employer->id));
        $employerPhone = $empUser ? (string)($empUser->attributes['phone'] ?? '') : '';
        $companyName = (string)($employer->attributes['company_name'] ?? $employer->company_name ?? 'Company');


        $response->view('employer/applications', [
            'title' => 'Applications',
            'currentJob' => $currentJob,
            'databaseCount' => $databaseCount,
            'applications' => $applications,
            'employer' => $employer,
            'employerPhone' => $employerPhone,
            'companyName' => $companyName,
            'jobs' => $jobs,
            'statusCounts' => $statusCounts,
            'filters' => [
                'job_id' => $jobId,
                'status' => $status,
                'search' => $search,
                'source' => $source,
                'sort_by' => $sortBy,
                'location' => $locationFilter,
                'interest' => $interestFilter,
                'include_applied' => $includeApplied,
                'has_resume' => $hasResume,
                'active_in' => $activeIn,
                'location_distance' => $locationDistance,
                'min_experience' => $minExp,
                'max_experience' => $maxExp,
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'education' => $educationFilter,
                'skills' => $skillsFilter,
                'language' => $languageFilter
            ],
            'jobCount' => $statusCounts['total'] ?? 0,
            'applicationCount' => $statusCounts['total'] ?? 0,
            'subscription' => [
                'featureAccess' => $featureAccess,
                'usage' => $usage
            ],
            'suggestedMode' => empty($applicationIds) && !empty($applications)
        ], 200, 'employer/layout');
    }

    private function formatTimeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $applicationId = (int)$request->param('id');

        $sql = "SELECT a.*, 
                       j.title as job_title, 
                       j.id as job_id,
                       j.employer_id, 
                       u.email as candidate_email, 
                       u.phone,
                       c.id as candidate_id,
                       c.resume_url,
                       COALESCE(cjs.overall_match_score, a.score, 0) as score,
                       cjs.overall_match_score as match_score,
                       cjs.skill_score,
                       cjs.experience_score,
                       cjs.education_score,
                       cjs.matched_skills,
                       cjs.missing_skills,
                       cjs.extra_relevant_skills,
                       cjs.recommendation,
                       cjs.summary as match_summary
                FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.candidate_user_id = u.id
                LEFT JOIN candidates c ON c.user_id = u.id
                LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = j.id
                WHERE a.id = :id AND j.employer_id = :employer_id";

        $application = \App\Core\Database::getInstance()->fetchOne($sql, [
            'id' => $applicationId,
            'employer_id' => $employer->id
        ]);

        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }

        $app = Application::find($applicationId);
        $application['events'] = array_map(fn($e) => $e->toArray(), \App\Models\ApplicationEvent::where('application_id', '=', $applicationId)->orderBy('created_at', 'DESC')->get());
        $application['interviews'] = array_map(fn($i) => $i->toArray(), \App\Models\Interview::where('application_id', '=', $applicationId)->orderBy('created_at', 'DESC')->get());
        
        // Decode match data JSON fields
        $application['matched_skills'] = !empty($application['matched_skills']) 
            ? json_decode($application['matched_skills'], true) ?? [] 
            : [];
        $application['missing_skills'] = !empty($application['missing_skills']) 
            ? json_decode($application['missing_skills'], true) ?? [] 
            : [];
        $application['extra_relevant_skills'] = !empty($application['extra_relevant_skills']) 
            ? json_decode($application['extra_relevant_skills'], true) ?? [] 
            : [];
        
        // Calculate match if not already calculated
        $candidateId = $application['candidate_id'] ?? null;
        $jobId = $application['job_id'] ?? null;
        if (empty($application['match_score']) && $candidateId && $jobId) {
            try {
                $matchService = new JobMatchService();
                $matchData = $matchService->calculateMatch($candidateId, $jobId, true);
                $application['match_score'] = $matchData['overall_match_score'];
                $application['skill_score'] = $matchData['skill_match_score'];
                $application['experience_score'] = $matchData['experience_match_score'];
                $application['education_score'] = $matchData['education_match_score'];
                $application['matched_skills'] = $matchData['matched_skills'];
                $application['missing_skills'] = $matchData['missing_skills'];
                $application['extra_relevant_skills'] = $matchData['extra_relevant_skills'];
                $application['recommendation'] = $matchData['recommendation'];
                $application['match_summary'] = $matchData['summary'];
                $application['match_method'] = $matchData['match_method'] ?? 'database';
            } catch (\Exception $e) {
                error_log("Error calculating match in show: " . $e->getMessage());
            }
        }

        // Get full candidate profile data
        $candidateUserId = $application['candidate_user_id'] ?? null;
        $candidateData = null;
        if ($candidateUserId) {
            $candidate = Candidate::findByUserId($candidateUserId);
            if ($candidate) {
                // Record Candidate View
                try {
                    $cv = new CandidateView();
                    $cv->fill([
                        'employer_id' => $employer->id,
                        'candidate_id' => $candidate->id,
                        'viewed_at' => date('Y-m-d H:i:s')
                    ]);
                    $cv->save();
                    
                    // Notify Candidate about profile view (Real-time)
                    \App\Services\NotificationService::send(
                        (int)$candidateUserId,
                        'profile_view',
                        'Profile View',
                        "{$employer->company_name} viewed your profile.",
                        [
                            'employer_id' => $employer->id,
                            'company_name' => $employer->company_name
                        ],
                        '/candidate/profile/views' // Assuming this route exists or will exist
                    );
                } catch (\Throwable $e) {
                    // Ignore duplicate entry or other errors to not break the flow
                }

                $isPremiumCandidate = $candidate->isPremium();
                // Employer subscription gating
                $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer((int)$employer->id);
                $plan = $subscription ? $subscription->plan() : null;
                $canSeeContacts = $subscription && ($subscription->isActive() || $subscription->isInGracePeriod()) && $plan && $plan->hasFeature('candidate_mobile_visible') && $subscription->canUseFeature('max_contacts_per_month');
                $canDownloadResume = $subscription && ($subscription->isActive() || $subscription->isInGracePeriod()) && $plan && $plan->hasFeature('resume_download_enabled') && $subscription->canUseFeature('max_resume_downloads');
                if (!$isPremiumCandidate || !$canSeeContacts) {
                    $application['candidate_email'] = null;
                    $application['phone'] = null;
                }
                $candidateData = [
                    'id' => $candidate->attributes['id'] ?? null,
                    'full_name' => $candidate->attributes['full_name'] ?? null,
                    'dob' => $candidate->attributes['dob'] ?? null,
                    'gender' => $candidate->attributes['gender'] ?? null,
                    'mobile' => ($isPremiumCandidate && $canSeeContacts) ? ($candidate->attributes['mobile'] ?? null) : null,
                    'city' => $candidate->attributes['city'] ?? null,
                    'state' => $candidate->attributes['state'] ?? null,
                    'country' => $candidate->attributes['country'] ?? null,
                    'profile_picture' => $candidate->attributes['profile_picture'] ?? null,
                    'resume_url' => $canDownloadResume ? ($candidate->attributes['resume_url'] ?? $application['resume_url'] ?? null) : null,
                    'video_intro_url' => $candidate->attributes['video_intro_url'] ?? null,
                    'self_introduction' => $candidate->attributes['self_introduction'] ?? null,
                    'expected_salary_min' => $candidate->attributes['expected_salary_min'] ?? null,
                    'expected_salary_max' => $candidate->attributes['expected_salary_max'] ?? null,
                    'current_salary' => $candidate->attributes['current_salary'] ?? null,
                    'notice_period' => $candidate->attributes['notice_period'] ?? null,
                    'preferred_job_location' => $candidate->attributes['preferred_job_location'] ?? null,
                    'portfolio_url' => $candidate->attributes['portfolio_url'] ?? null,
                    'linkedin_url' => $candidate->attributes['linkedin_url'] ?? null,
                    'github_url' => $candidate->attributes['github_url'] ?? null,
                    'website_url' => $candidate->attributes['website_url'] ?? null,
                    'profile_strength' => $candidate->attributes['profile_strength'] ?? 0,
                    'is_profile_complete' => $candidate->attributes['is_profile_complete'] ?? 0,
                    'is_verified' => $candidate->attributes['is_verified'] ?? 0,
                    'is_premium' => ($isPremiumCandidate ? 1 : 0),
                    // Parse JSON data from candidates table
                    'education' => !empty($candidate->attributes['education_data']) 
                        ? array_map(function($edu) {
                            return [
                                'id' => $edu['id'] ?? null,
                                'degree' => $edu['degree'] ?? null,
                                'field' => $edu['field_of_study'] ?? null, // Note: field_of_study in JSON
                                'institution' => $edu['institution'] ?? null,
                                'start_date' => $edu['start_date'] ?? null,
                                'end_date' => $edu['end_date'] ?? null,
                                'is_current' => $edu['is_current'] ?? 0,
                                'grade' => $edu['grade'] ?? null,
                                'description' => $edu['description'] ?? null
                            ];
                        }, json_decode($candidate->attributes['education_data'], true) ?? [])
                        : [],
                    'experience' => !empty($candidate->attributes['experience_data'])
                        ? array_map(function($exp) {
                            return [
                                'id' => $exp['id'] ?? null,
                                'title' => $exp['job_title'] ?? null, // Note: job_title in JSON
                                'company' => $exp['company_name'] ?? null, // Note: company_name in JSON
                                'location' => $exp['location'] ?? null,
                                'start_date' => $exp['start_date'] ?? null,
                                'end_date' => $exp['end_date'] ?? null,
                                'is_current' => $exp['is_current'] ?? 0,
                                'description' => $exp['description'] ?? null
                            ];
                        }, json_decode($candidate->attributes['experience_data'], true) ?? [])
                        : [],
                    'skills' => !empty($candidate->attributes['skills_data'])
                        ? array_map(function($skill) {
                            return [
                                'id' => $skill['skill_id'] ?? null,
                                'name' => $skill['name'] ?? null,
                                'level' => $skill['proficiency_level'] ?? null, // Note: proficiency_level in JSON
                                'years_experience' => $skill['years_of_experience'] ?? null // Note: years_of_experience in JSON
                            ];
                        }, json_decode($candidate->attributes['skills_data'], true) ?? [])
                        : [],
                    'languages' => !empty($candidate->attributes['languages_data'])
                        ? array_map(function($lang) {
                            return [
                                'id' => $lang['id'] ?? null,
                                'language' => $lang['language'] ?? null,
                                'proficiency' => $lang['proficiency'] ?? null
                            ];
                        }, json_decode($candidate->attributes['languages_data'], true) ?? [])
                        : []
                ];
            }
        }

        $response->view('employer/applications/show', [
            'title' => 'Application Details',
            'application' => $application,
            'candidate' => $candidateData,
            'employer' => $employer
        ], 200, 'employer/layout');
    }

    public function recordView(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $candidateId = (int)$request->param('id');
        $candidate = Candidate::find($candidateId);

        if (!$candidate) {
            $response->json(['error' => 'Candidate not found'], 404);
            return;
        }

        try {
            $cv = new CandidateView();
            $cv->fill([
                'employer_id' => $employer->id,
                'candidate_id' => $candidate->id,
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
            $cv->save();
            
            // Notify Candidate
             \App\Services\NotificationService::send(
                (int)$candidate->user_id,
                'profile_view',
                'Profile View',
                "{$employer->company_name} viewed your profile.",
                [
                    'employer_id' => $employer->id,
                    'company_name' => $employer->company_name
                ],
                '/candidate/profile/views'
            );

            $response->json(['success' => true]);
        } catch (\Throwable $e) {
            $response->json(['error' => 'Failed to record view'], 500);
        }
    }

    public function downloadResume(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $candidateId = (int)$request->param('id');
        $candidate = Candidate::find($candidateId);

        if (!$candidate || empty($candidate->resume_url)) {
            $response->redirect('/employer/applications?error=resume_not_found');
            return;
        }

        // Check Subscription
        $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        
        if (!$subscription || (!$subscription->isActive() && !$subscription->isInGracePeriod())) {
             $response->redirect('/employer/subscription/plans?upgrade=1&reason=resume_download_subscription_required');
             return;
        }
        
        $plan = $subscription->plan();
        if (!$plan || !$plan->hasFeature('resume_download_enabled')) {
             $response->redirect('/employer/subscription/plans?upgrade=1&reason=resume_download_feature_locked');
             return;
        }

        // Check Limit
        if (!$subscription->canUseFeature('max_resume_downloads')) {
             $response->redirect('/employer/subscription/plans?upgrade=1&reason=resume_download_limit_reached');
             return;
        }

        // Increment Usage
        $subscription->incrementUsage('max_resume_downloads');

        // Notify Candidate
        try {
             \App\Services\NotificationService::send(
                (int)$candidate->user_id,
                'resume_downloaded',
                'Resume Downloaded',
                "{$employer->company_name} downloaded your resume.",
                [
                    'employer_id' => $employer->id,
                    'company_name' => $employer->company_name
                ],
                '/candidate/profile/views'
            );
        } catch (\Throwable $e) {}

        // Redirect to Resume URL
        $response->redirect($candidate->resume_url);
    }

    public function updateStatus(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $applicationId = (int)$request->param('id');
        $data = $request->getJsonBody();

        $application = Application::find($applicationId);
        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }

        // Verify employer owns the job
        $job = Job::find($application->attributes['job_id'] ?? 0);
        if ($job->employer_id !== $employer->id) {
            $response->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $newStatus = $data['status'] ?? null;
        $comment = $data['comment'] ?? '';

        if (!in_array($newStatus, ['applied', 'screening', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'])) {
            $response->json(['error' => 'Invalid status'], 422);
            return;
        }

        $oldStatus = $application->status ?? $application->attributes['status'] ?? 'applied';
        $application->fill(['status' => $newStatus]);
        if ($application->save()) {
            $event = new \App\Models\ApplicationEvent();
            $event->fill([
                'application_id' => $applicationId,
                'actor_user_id' => $this->currentUser->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => $comment
            ]);
            $event->save();
            // If status is changed to 'hired', update all related interviews to 'completed'
            if ($newStatus === 'hired') {
                $db = \App\Core\Database::getInstance();
                $updateInterviewSql = "UPDATE interviews 
                                      SET status = 'completed', updated_at = NOW()
                                      WHERE application_id = :application_id 
                                      AND status IN ('scheduled', 'rescheduled')";
                $db->query($updateInterviewSql, ['application_id' => $applicationId]);
            }
            
            // Trigger webhook
            try {
                if (class_exists(\App\Workers\WebhookWorker::class) && method_exists(\App\Workers\WebhookWorker::class, 'enqueue')) {
                    \App\Workers\WebhookWorker::enqueue([
                        'employer_id' => $employer->id,
                        'event' => 'application.status_changed',
                        'data' => [
                            'application_id' => $applicationId,
                            'status' => $newStatus
                        ]
                    ]);
                }
            } catch (\Throwable $t) {
                error_log('Webhook enqueue skipped: ' . $t->getMessage());
            }
            
            $candidateUserId = (int)($application->attributes['candidate_user_id'] ?? 0);
            $candidateUser = $candidateUserId > 0 ? \App\Models\User::find($candidateUserId) : null;
            $jobTitle = $job->title ?? ($job->attributes['title'] ?? 'Job');
            
            // Fetch company details
            $db = \App\Core\Database::getInstance();
            $companySql = "SELECT company_name, website FROM employers WHERE id = :id";
            $companyInfo = $db->fetchOne($companySql, ['id' => $employer->id]);
            
            if ($candidateUser && $candidateUser->email) {
                \App\Services\NotificationService::queueEmail(
                    $candidateUser->email,
                    'application_status',
                    [
                        'job_title' => $jobTitle,
                        'status' => $newStatus,
                        'employer_id' => (int)$employer->id,
                        'candidate_user_id' => (int)$candidateUserId,
                        'company_name' => (string)($companyInfo['company_name'] ?? 'MindInfotech'),
                        'company_logo' => '',
                        'company_website' => (string)($companyInfo['website'] ?? ''),
                        'candidate_name' => (string)($candidateUser->full_name ?? 'Candidate')
                ]
            );

            // Send chat notification
            $chatMessage = "Hi " . ($candidateUser->full_name ?? 'Candidate') . ",\n\n" .
                           "Your application status for the " . $jobTitle . " position at " . ($companyInfo['company_name'] ?? 'MindInfotech') . " has been updated.\n\n" .
                           "New Status: " . ucfirst($newStatus) . "\n\n" .
                           "View your application dashboard for more details.";
            \App\Services\NotificationService::queueChatNotification(
                (int)$employer->id,
                (int)$candidateUserId,
                $chatMessage
            );

            \App\Services\NotificationService::notifyApplicationUpdate(
                (int)$candidateUser->id,
                (string)$jobTitle,
                (string)$newStatus
            );
        }

        $response->json(['message' => 'Status updated', 'application' => $application->toArray()]);
    } else {
            $response->json(['error' => 'Failed to update status'], 500);
        }
    }

    public function addNote(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }
        $employer = $this->currentUser->employer();
        $applicationId = (int)$request->param('id');
        $data = $request->getJsonBody();
        $note = trim((string)($data['note'] ?? ''));
        if ($note === '') {
            $response->json(['error' => 'Note cannot be empty'], 422);
            return;
        }
        $application = Application::find($applicationId);
        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }
        $job = Job::find($application->attributes['job_id'] ?? 0);
        if (!$job || $job->employer_id !== $employer->id) {
            $response->json(['error' => 'Unauthorized'], 403);
            return;
        }
        $event = new \App\Models\ApplicationEvent();
        $event->fill([
            'application_id' => $applicationId,
            'actor_user_id' => $this->currentUser->id,
            'from_status' => null,
            'to_status' => null,
            'comment' => $note
        ]);
        $event->save();
        $response->json(['success' => true]);
    }
    public function export(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $jobId = (int)($request->get('job_id') ?? 0);

        $sql = "SELECT a.id, a.applied_at, a.status, a.expected_salary,
                       a.candidate_user_id,
                       j.title as job_title,
                       u.email as candidate_email, u.phone
                FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.candidate_user_id = u.id
                WHERE j.employer_id = :employer_id";

        $params = ['employer_id' => $employer->id];

        if ($jobId) {
            $sql .= " AND a.job_id = :job_id";
            $params['job_id'] = $jobId;
        }

        $applications = \App\Core\Database::getInstance()->fetchAll($sql, $params);

        // Generate CSV
        $filename = 'applications_' . date('Y-m-d') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        $handle = fopen($filepath, 'w');

        fputcsv($handle, ['ID', 'Applied At', 'Status', 'Expected Salary', 'Job Title', 'Email', 'Phone']);

        foreach ($applications as $app) {
            $candidateUserId = (int)($app['candidate_user_id'] ?? 0);
            $email = '';
            $phone = '';

            if ($candidateUserId > 0) {
                $candidate = \App\Models\Candidate::findByUserId($candidateUserId);
                if ($candidate && $candidate->isPremium()) {
                    $email = $app['candidate_email'] ?? '';
                    $phone = $app['phone'] ?? '';
                }
            }

            fputcsv($handle, [
                $app['id'],
                $app['applied_at'],
                $app['status'],
                $app['expected_salary'],
                $app['job_title'],
                $email,
                $phone
            ]);
        }

        fclose($handle);
        $response->download($filepath, $filename);
    }

    /**
     * Generate match score for a specific application
     * POST /employer/applications/{id}/generate-score
     */
    public function generateScore(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $applicationId = (int)$request->param('id');
        $employer = $this->currentUser->employer();

        // Get application
        $application = Application::find($applicationId);
        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }

        // Verify job belongs to employer
        $job = Job::find($application->attributes['job_id'] ?? 0);
        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Unauthorized'], 403);
            return;
        }

        // Get candidate
        $candidate = Candidate::where('user_id', '=', $application->attributes['candidate_user_id'] ?? 0)->first();
        if (!$candidate) {
            $response->json(['error' => 'Candidate not found'], 404);
            return;
        }

        try {
            $matchService = new JobMatchService();
            $matchData = $matchService->calculateMatch(
                $candidate->attributes['id'],
                $job->attributes['id'],
                true
            );

            $response->json([
                'success' => true,
                'message' => 'Match score calculated successfully',
                'match_data' => [
                    'overall_match_score' => $matchData['overall_match_score'],
                    'skill_match_score' => $matchData['skill_match_score'],
                    'experience_match_score' => $matchData['experience_match_score'],
                    'education_match_score' => $matchData['education_match_score'],
                    'matched_skills' => $matchData['matched_skills'],
                    'missing_skills' => $matchData['missing_skills'],
                    'extra_relevant_skills' => $matchData['extra_relevant_skills'],
                    'recommendation' => $matchData['recommendation'],
                    'summary' => $matchData['summary']
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Generate score error: " . $e->getMessage());
            $response->json([
                'error' => 'Failed to calculate match score',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkStatus(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $data = $request->getJsonBody();
        $ids = $data['ids'] ?? [];
        $newStatus = $data['status'] ?? '';

        if ($newStatus === 'shortlist') $newStatus = 'shortlisted';

        if (empty($ids) || !is_array($ids)) {
            $response->json(['error' => 'No applications selected'], 400);
            return;
        }

        if (!in_array($newStatus, ['shortlisted', 'rejected', 'interview', 'hired', 'screening', 'applied'])) {
            $response->json(['error' => 'Invalid status'], 400);
            return;
        }

        $db = \App\Core\Database::getInstance();
        
        // Verify ownership and update
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "UPDATE applications a
                INNER JOIN jobs j ON a.job_id = j.id
                SET a.status = ?
                WHERE a.id IN ($placeholders)
                AND j.employer_id = ?";
        
        $params = array_merge([$newStatus], $ids, [$employer->id]);
        
        try {
            $stmt = $db->query($sql, $params);
            $count = $stmt->rowCount();
            $response->json(['success' => true, 'updated_count' => $count]);
        } catch (\Exception $e) {
            $response->json(['error' => 'Database error'], 500);
        }
    }
}
