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
use App\Models\CandidateView;
use App\Models\SubscriptionUsageLog;
use App\Services\JobMatchService;

class ApplicationsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = $this->currentUser;
        $employer = $user->employer();
        if (!$employer) {
            $_SESSION['profile_required_message'] = 'Please complete your employer profile before viewing applications.';
            $response->redirect('/employer/profile?setup=1&complete_required=1');
            return;
        }

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

        $employerId = (int)$employer->id;
        $db = \App\Core\Database::getInstance();
        
        // Subscription info early to set list limits
        $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer($employerId);
        $plan = $subscription ? $subscription->plan() : null;
        $subscriptionActive = $subscription ? ($subscription->isActive() || $subscription->isInGracePeriod()) : false;
        $listLimit = $subscriptionActive ? 200 : 45;
        
        // Get applications using Model method (MVC Refactor)
        $filters = [
            'job_id' => $jobId,
            'status' => $status,
            'source' => $source,
            'search' => $search,
            'sort_by' => $sortBy,
            'location' => $locationFilter,
            'location_distance' => $locationDistance,
            'interest' => $interestFilter,
            'has_resume' => $hasResume,
            'active_in' => $activeIn,
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'skills' => $skillsFilter,
            'language' => $languageFilter
        ];
        
        $applications = Application::getEmployerApplications($employerId, $filters, $listLimit);

        // Independent query for status counts
        $countSql = "FROM applications a INNER JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = :employer_id";
        $countParams = ['employer_id' => $employerId];
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
        $statusCounts = $statusCounts ?: ['total' => 0, 'new_count' => 0, 'reviewing_count' => 0, 'contacting_count' => 0, 'interviewing_count' => 0, 'rejected_count' => 0, 'hired_count' => 0, 'shortlisted_count' => 0, 'undecided_count' => 0];

        $currentJob = $jobId ? Job::find($jobId) : null;
        if ($currentJob && (int)($currentJob->attributes['employer_id'] ?? 0) !== $employerId) {
            $currentJob = null;
            $jobId = 0;
        }

        // Get activity events
        $applicationIds = array_filter(array_column($applications, 'application_id'));
        $events = [];
        if (!empty($applicationIds)) {
            $placeholders = implode(',', array_fill(0, count($applicationIds), '?'));
            $eventsSql = "SELECT application_id, to_status, created_at, comment FROM application_events WHERE application_id IN ({$placeholders}) ORDER BY created_at DESC";
            $eventsData = $db->fetchAll($eventsSql, $applicationIds);
            foreach ($eventsData as $event) {
                $events[$event['application_id']][] = $event;
            }
        }

        $matchService = new JobMatchService();
        foreach ($applications as &$app) {
            $appId = $app['application_id'] ?? null;
            foreach (['matched_skills', 'missing_skills', 'extra_relevant_skills'] as $field) {
                $decoded = !empty($app[$field]) ? json_decode((string)$app[$field], true) : [];
                $app[$field] = is_array($decoded) ? $decoded : [];
            }
            
            // Calculate Experience Years
            $expData = !empty($app['experience_data']) ? json_decode((string)$app['experience_data'], true) : [];
            $totalMonths = 0;
            if (is_array($expData)) {
                foreach ($expData as $exp) {
                     if (!empty($exp['start_date'])) {
                         try {
                             $start = new \DateTime($exp['start_date']);
                             $end = (!empty($exp['end_date']) && empty($exp['is_current'])) ? new \DateTime($exp['end_date']) : new \DateTime();
                             $diff = $start->diff($end);
                             $totalMonths += ($diff->y * 12) + $diff->m;
                         } catch (\Exception $e) {}
                     }
                }
            }
            $years = round($totalMonths / 12, 1);
            $app['experience_years'] = $years;

            if (($minExp !== null && $minExp !== '' && $years < (float)$minExp) || ($maxExp !== null && $maxExp !== '' && $years > (float)$maxExp)) {
                $app['_remove'] = true;
                continue;
            }

            if ($educationFilter) {
                $eduLevels = is_array($educationFilter) ? $educationFilter : explode(',', (string)$educationFilter);
                $hasEdu = false;
                $eduData = !empty($app['education_data']) ? json_decode((string)$app['education_data'], true) : [];
                if (is_array($eduData)) {
                    foreach ($eduData as $edu) {
                        foreach ($eduLevels as $level) {
                            if (stripos((string)($edu['degree'] ?? ''), (string)$level) !== false) {
                                $hasEdu = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$hasEdu) { $app['_remove'] = true; continue; }
            }

            if (!empty($app['certifications_data'])) {
                $certData = json_decode((string)$app['certifications_data'], true);
                $app['certifications'] = $certData['items'] ?? (is_array($certData) ? $certData : []);
            }
            
            if (empty($app['overall_match_score']) && ($app['candidate_id'] ?? null) && ($app['job_id'] ?? null)) {
                try {
                    $matchData = $matchService->calculateMatch((int)$app['candidate_id'], (int)$app['job_id'], true);
                    $app['overall_match_score'] = $matchData['overall_match_score'] ?? 0;
                    $app['match_summary'] = $matchData['summary'] ?? '';
                } catch (\Exception $e) {}
            }
            
            $app['activity'] = $appId ? ($events[$appId] ?? []) : [];
            $app['latest_activity'] = !empty($app['activity']) ? $app['activity'][0] : null;
            $app['applied_at_formatted'] = $this->formatTimeAgo((string)($app['applied_at'] ?? date('Y-m-d H:i:s')));
            $locationParts = array_filter([(string)($app['city'] ?? ''), (string)($app['state'] ?? ''), (string)($app['country'] ?? '')]);
            $app['location_display'] = !empty($locationParts) ? implode(', ', $locationParts) : 'Not specified';
        }

        $applications = array_values(array_filter($applications, function($a) { return !isset($a['_remove']); }));

        if (count($applications) < 10) {
            $fallbackJobId = $jobId ?: (int)(Job::where('employer_id', '=', $employerId)->orderBy('created_at', 'DESC')->first()?->id ?? 0);
            if ($fallbackJobId) {
                $excludeIds = array_column($applications, 'candidate_id');
                $suggested = Candidate::getSuggestedCandidates($fallbackJobId, $excludeIds, 45 - count($applications));
                foreach ($suggested as $s) { $applications[] = $s; }
            }
        }

        if ((string)$includeApplied === '0') {
            $applications = array_values(array_filter($applications, function($row) { return (($row['status'] ?? '') === 'suggested') || empty($row['application_id']); }));
        }

        if (!empty($locationDistance) && !empty($applications)) {
            $applications = array_values(array_filter($applications, function($row) use ($db, $locationDistance) {
                $jid = (int)($row['job_id'] ?? 0); if (!$jid) return true;
                $rows = $db->fetchAll("SELECT c.name as city, s.name as state FROM job_locations jl LEFT JOIN cities c ON jl.city_id = c.id LEFT JOIN states s ON jl.state_id = s.id WHERE jl.job_id = :job_id", ['job_id' => $jid]);
                $cities = array_filter(array_map(fn($r) => strtolower(trim((string)($r['city'] ?? ''))), $rows));
                $states = array_filter(array_map(fn($r) => strtolower(trim((string)($r['state'] ?? ''))), $rows));
                $candCity = strtolower(trim((string)($row['city'] ?? ''))); $candState = strtolower(trim((string)($row['state'] ?? '')));
                if ($locationDistance <= 10) return $candCity && in_array($candCity, $cities, true);
                return ($candCity && in_array($candCity, $cities, true)) || ($candState && in_array($candState, $states, true));
            }));
        }

        $jobs = Job::where('employer_id', '=', $employerId)->orderBy('title', 'ASC')->get();

        $candidateIds = array_values(array_unique(array_filter(array_map(fn($r) => (int)($r['candidate_id'] ?? 0), $applications))));
        if (!empty($candidateIds)) {
            $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
            $rows = $db->fetchAll("SELECT er.id as employment_id, er.candidate_id FROM employment_records er WHERE er.candidate_id IN ({$placeholders}) AND er.status_overall = 'verified'", $candidateIds);
            $verifiedMap = []; foreach ($rows as $r) { $verifiedMap[(int)$r['candidate_id']][] = (int)$r['employment_id']; }
            $allEmpIds = array_column($rows, 'employment_id');
            $unlockedIds = [];
            if (!empty($allEmpIds)) {
                $ph = implode(',', array_fill(0, count($allEmpIds), '?'));
                $unlockedRows = $db->fetchAll("SELECT employment_id FROM employer_unlocks WHERE employer_id = ? AND status = 'paid' AND employment_id IN ({$ph})", array_merge([$employerId], $allEmpIds));
                $unlockedIds = array_column($unlockedRows, 'employment_id');
            }
            foreach ($applications as &$app) {
                $cid = (int)($app['candidate_id'] ?? 0);
                $app['verified_badge'] = !empty($verifiedMap[$cid]) ? 1 : 0;
                $app['verification_unlocked'] = !empty(array_intersect($verifiedMap[$cid] ?? [], $unlockedIds)) ? 1 : 0;
            }
        }

        $usage = [
            'contacts_used' => (int)($subscription->attributes['contacts_used_this_month'] ?? 0),
            'contacts_limit' => $plan ? (int)$plan->getLimit('max_contacts_per_month') : 0,
            'downloads_used' => (int)($subscription->attributes['resume_downloads_used_this_month'] ?? 0),
            'downloads_limit' => $plan ? (int)$plan->getLimit('max_resume_downloads') : 0,
            'plan_name' => $plan ? (string)($plan->attributes['name'] ?? 'Free') : 'Free'
        ];
        $usage['contacts_remaining'] = max(0, $usage['contacts_limit'] - $usage['contacts_used']);
        $usage['downloads_remaining'] = max(0, $usage['downloads_limit'] - $usage['downloads_used']);

        $empUser = \App\Models\User::find((int)($employer->attributes['user_id'] ?? 0));
        $employerPhone = $empUser ? (string)($empUser->attributes['phone'] ?? '') : '';
        $companyName = (string)($employer->attributes['company_name'] ?? 'Company');

        $canMessage = $plan ? $plan->hasFeature('chat_enabled') : false;

        $response->view('employer/applications', [
            'title' => 'Applications',
            'currentJob' => $currentJob,
            'applications' => $applications,
            'employer' => $employer,
            'employerPhone' => $employerPhone,
            'companyName' => $companyName,
            'canMessage' => $canMessage,
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
            'subscription' => [
                'featureAccess' => [
                    'candidate_mobile_visible' => $plan ? $plan->hasFeature('candidate_mobile_visible') : false,
                    'resume_download_enabled' => $plan ? $plan->hasFeature('resume_download_enabled') : false,
                ],
                'usage' => $usage
            ]
        ], 200, 'employer/layout');
    }

    public function candidateProfile(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $user = $this->currentUser;
        $employer = $user->employer();
        $candidateId = (int)$request->param('id');

        // Check if there is an application for this candidate to this employer's jobs
        $sql = "SELECT a.id FROM applications a 
                INNER JOIN jobs j ON a.job_id = j.id 
                WHERE a.candidate_user_id = (SELECT user_id FROM candidates WHERE id = :cid) 
                AND j.employer_id = :eid 
                ORDER BY a.applied_at DESC LIMIT 1";
        
        $db = \App\Core\Database::getInstance();
        $app = $db->fetchOne($sql, ['cid' => $candidateId, 'eid' => $employer->id]);

        if ($app) {
            // Redirect to the application show page which has the full profile
            $response->redirect('/employer/applications/' . $app['id']);
            return;
        }

        // Fallback: If no application, but maybe employer is viewing from database search
        // Check if candidate exists and is searchable
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            $response->view('errors/404', ['title' => 'Candidate Not Found'], 404);
            return;
        }

        // Record view
        $this->recordView($request, $response);

        // For now, redirect to applications with a message or just show the same view with a dummy application object
        $response->redirect('/employer/applications?search=' . urlencode($candidate->full_name ?? ''));
    }

    private function formatTimeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        if (!$timestamp) return $datetime;
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

        /** @var \App\Models\User $user */
        $user = $this->currentUser;
        $employer = $user->employer();
        $applicationId = (int)$request->param('id');

        $sql = "SELECT a.*, 
                       j.title as job_title, 
                       j.id as job_id,
                       j.employer_id, 
                       u.email as candidate_email,
                       COALESCE(NULLIF(u.phone, ''), NULLIF(c.mobile, '')) as phone,
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

        $application['events'] = array_map(fn($e) => $e->toArray(), \App\Models\ApplicationEvent::where('application_id', '=', $applicationId)->orderBy('created_at', 'DESC')->get());
        $application['interviews'] = array_map(fn($i) => $i->toArray(), \App\Models\Interview::where('application_id', '=', $applicationId)->orderBy('created_at', 'DESC')->get());
        
        // Decode match data JSON fields
        $application['matched_skills'] = !empty($application['matched_skills']) 
            ? json_decode((string)$application['matched_skills'], true) ?? [] 
            : [];
        $application['missing_skills'] = !empty($application['missing_skills']) 
            ? json_decode((string)$application['missing_skills'], true) ?? [] 
            : [];
        $application['extra_relevant_skills'] = !empty($application['extra_relevant_skills']) 
            ? json_decode((string)$application['extra_relevant_skills'], true) ?? [] 
            : [];
        
        // Calculate match if not already calculated
        $candidateId = $application['candidate_id'] ?? null;
        $jobId = $application['job_id'] ?? null;
        if (empty($application['match_score']) && $candidateId && $jobId) {
            try {
                $matchService = new JobMatchService();
                $matchData = $matchService->calculateMatch((int)$candidateId, (int)$jobId, true);
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
            $candidate = Candidate::findByUserId((int)$candidateUserId);
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
                    
                    // Notify Candidate about profile view
                    $companySlug = $employer->attributes['company_slug'] ?? $employer->company_slug ?? null;
                    if (!$companySlug || trim((string)$companySlug) === '') {
                        $name = (string)($employer->company_name ?? 'company');
                        $companySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                        $companySlug = trim($companySlug, '-');
                    }
                    $companyLink = '/company/' . $companySlug;
                    \App\Services\NotificationService::send(
                        (int)$candidateUserId,
                        'profile_view',
                        'Profile View',
                        "{$employer->company_name} viewed your profile.",
                        [
                            'employer_id' => $employer->id,
                            'company_name' => $employer->company_name,
                            'company_slug' => $companySlug,
                            'link' => $companyLink
                        ],
                        $companyLink
                    );
                } catch (\Throwable $e) {}

                $isPremiumCandidate = $candidate->isPremium();
                $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer((int)$employer->id);
                $plan = $subscription ? $subscription->plan() : null;
                
                $alreadyUnlockedContact = SubscriptionUsageLog::hasUnlocked((int)$employer->id, (int)$candidate->id, 'contact_view');
                $canSeeContacts = $subscription && ($subscription->isActive() || $subscription->isInGracePeriod()) && $plan && $plan->hasFeature('candidate_mobile_visible') && ($alreadyUnlockedContact || $subscription->canUseFeature('max_contacts_per_month'));
                $canDownloadResume = $subscription && ($subscription->isActive() || $subscription->isInGracePeriod()) && $plan && $plan->hasFeature('resume_download_enabled') && (SubscriptionUsageLog::hasUnlocked((int)$employer->id, (int)$candidate->id, 'resume_download') || $subscription->canUseFeature('max_resume_downloads'));

                if (!$alreadyUnlockedContact && $canSeeContacts && $isPremiumCandidate) {
                     $subscription->incrementUsage('max_contacts_per_month');
                     SubscriptionUsageLog::logUsage((int)$subscription->id, (int)$employer->id, 'contact_view', (int)$candidate->id);
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
                    'education' => !empty($candidate->attributes['education_data']) 
                        ? array_map(fn($edu) => [
                                'id' => $edu['id'] ?? null,
                                'degree' => $edu['degree'] ?? null,
                                'field' => $edu['field_of_study'] ?? null,
                                'institution' => $edu['institution'] ?? null,
                                'start_date' => $edu['start_date'] ?? null,
                                'end_date' => $edu['end_date'] ?? null,
                                'is_current' => $edu['is_current'] ?? 0,
                                'grade' => $edu['grade'] ?? null,
                                'description' => $edu['description'] ?? null
                            ], json_decode((string)$candidate->attributes['education_data'], true) ?? [])
                        : [],
                    'experience' => !empty($candidate->attributes['experience_data'])
                        ? array_map(fn($exp) => [
                                'id' => $exp['id'] ?? null,
                                'title' => $exp['job_title'] ?? null,
                                'company' => $exp['company_name'] ?? null,
                                'location' => $exp['location'] ?? null,
                                'start_date' => $exp['start_date'] ?? null,
                                'end_date' => $exp['end_date'] ?? null,
                                'is_current' => $exp['is_current'] ?? 0,
                                'description' => $exp['description'] ?? null
                            ], json_decode((string)$candidate->attributes['experience_data'], true) ?? [])
                        : [],
                    'skills' => !empty($candidate->attributes['skills_data'])
                        ? array_map(fn($skill) => [
                                'id' => $skill['skill_id'] ?? null,
                                'name' => $skill['name'] ?? null,
                                'level' => $skill['proficiency_level'] ?? null,
                                'years_experience' => $skill['years_of_experience'] ?? null
                            ], json_decode((string)$candidate->attributes['skills_data'], true) ?? [])
                        : [],
                    'languages' => !empty($candidate->attributes['languages_data'])
                        ? array_map(fn($lang) => [
                                'id' => $lang['id'] ?? null,
                                'language' => $lang['language'] ?? null,
                                'proficiency' => $lang['proficiency'] ?? null
                            ], json_decode((string)$candidate->attributes['languages_data'], true) ?? [])
                        : [],
                    'preferences' => !empty($candidate->attributes['preferences_data'])
                        ? json_decode((string)$candidate->attributes['preferences_data'], true) ?? []
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

        $user = $this->currentUser;
        $employer = $user->employer();
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
            $companySlug = $employer->attributes['company_slug'] ?? $employer->company_slug ?? null;
            if (!$companySlug || trim((string)$companySlug) === '') {
                $name = (string)($employer->company_name ?? 'company');
                $companySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                $companySlug = trim($companySlug, '-');
            }
            $companyLink = '/company/' . $companySlug;
             \App\Services\NotificationService::send(
                (int)$candidate->user_id,
                'profile_view',
                'Profile View',
                "{$employer->company_name} viewed your profile.",
                [
                    'employer_id' => $employer->id,
                    'company_name' => $employer->company_name,
                    'company_slug' => $companySlug,
                    'link' => $companyLink
                ],
                $companyLink
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

        $user = $this->currentUser;
        $employer = $user->employer();
        $candidateId = (int)$request->param('id');
        $candidate = Candidate::find($candidateId);

        if (!$candidate || empty($candidate->resume_url)) {
            $response->redirect('/employer/applications?error=resume_not_found');
            return;
        }

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

        if (!$subscription->canUseFeature('max_resume_downloads')) {
             $response->redirect('/employer/subscription/plans?upgrade=1&reason=resume_download_limit_reached');
             return;
        }

        $alreadyUnlocked = SubscriptionUsageLog::hasUnlocked((int)$employer->id, $candidateId, 'resume_download');
        if (!$alreadyUnlocked) {
            $subscription->incrementUsage('max_resume_downloads');
            SubscriptionUsageLog::logUsage((int)$subscription->id, (int)$employer->id, 'resume_download', $candidateId);
        }

        try {
            $companySlug = $employer->attributes['company_slug'] ?? $employer->company_slug ?? null;
            if (!$companySlug || trim((string)$companySlug) === '') {
                $name = (string)($employer->company_name ?? 'company');
                $companySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                $companySlug = trim($companySlug, '-');
            }
            $companyLink = '/company/' . $companySlug;
             \App\Services\NotificationService::send(
                (int)$candidate->user_id,
                'resume_downloaded',
                'Resume Downloaded',
                "{$employer->company_name} downloaded your resume.",
                [
                    'employer_id' => $employer->id,
                    'company_name' => $employer->company_name,
                    'company_slug' => $companySlug,
                    'link' => $companyLink
                ],
                $companyLink
            );
        } catch (\Throwable $e) {}

        $response->redirect($candidate->resume_url);
    }

    public function updateStatus(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $user = $this->currentUser;
        $employer = $user->employer();
        $applicationId = (int)$request->param('id');
        $data = $request->getJsonBody();

        $application = Application::find($applicationId);
        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }

        $job = Job::find((int)($application->attributes['job_id'] ?? 0));
        if (!$job || (int)($job->employer_id ?? $job->attributes['employer_id'] ?? 0) !== (int)$employer->id) {
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
            if ($newStatus === 'hired') {
                $db = \App\Core\Database::getInstance();
                $db->query("UPDATE interviews SET status = 'completed', updated_at = NOW() WHERE application_id = :application_id AND status IN ('scheduled', 'rescheduled')", ['application_id' => $applicationId]);
            }
            
            try {
                if (class_exists(\App\Workers\WebhookWorker::class)) {
                    \App\Workers\WebhookWorker::enqueue([
                        'employer_id' => $employer->id,
                        'event' => 'application.status_changed',
                        'data' => ['application_id' => $applicationId, 'status' => $newStatus]
                    ]);
                }
            } catch (\Throwable $t) {}
            
            $candidateUserId = (int)($application->attributes['candidate_user_id'] ?? 0);
            $candidateUser = $candidateUserId > 0 ? \App\Models\User::find($candidateUserId) : null;
            $jobTitle = $job->title ?? ($job->attributes['title'] ?? 'Job');
            
            if ($candidateUser && $candidateUser->email) {
                \App\Services\NotificationService::queueEmail(
                    $candidateUser->email,
                    'application_status',
                    [
                        'job_title' => $jobTitle,
                        'status' => $newStatus,
                        'employer_id' => (int)$employer->id,
                        'candidate_user_id' => (int)$candidateUserId,
                        'company_name' => (string)($employer->attributes['company_name'] ?? 'MindInfotech'),
                        'company_logo' => '',
                        'company_website' => (string)($employer->attributes['website'] ?? ''),
                        'candidate_name' => (string)($candidateUser->full_name ?? 'Candidate')
                    ]
                );

                \App\Services\NotificationService::queueChatNotification(
                    (int)$employer->id,
                    (int)$candidateUserId,
                    "Hi " . ($candidateUser->full_name ?? 'Candidate') . ",\n\nYour application status for the " . $jobTitle . " position at " . ($employer->attributes['company_name'] ?? 'MindInfotech') . " has been updated.\n\nNew Status: " . ucfirst((string)$newStatus) . "\n\nView your application dashboard for more details."
                );

                \App\Services\NotificationService::notifyApplicationUpdate((int)$candidateUser->id, (string)$jobTitle, (string)$newStatus);
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
        $user = $this->currentUser;
        $employer = $user->employer();
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
        $job = Job::find((int)($application->attributes['job_id'] ?? 0));
        if (!$job || (int)($job->employer_id ?? $job->attributes['employer_id'] ?? 0) !== (int)$employer->id) {
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

        $user = $this->currentUser;
        $employer = $user->employer();
        $jobId = (int)($request->get('job_id') ?? 0);

        $sql = "SELECT a.id, a.applied_at, a.status, a.expected_salary, a.candidate_user_id, j.title as job_title, u.email as candidate_email, u.phone FROM applications a INNER JOIN jobs j ON a.job_id = j.id INNER JOIN users u ON a.candidate_user_id = u.id WHERE j.employer_id = :employer_id";
        $params = ['employer_id' => $employer->id];
        if ($jobId) {
            $sql .= " AND a.job_id = :job_id";
            $params['job_id'] = $jobId;
        }

        $applications = \App\Core\Database::getInstance()->fetchAll($sql, $params);
        $filename = 'applications_' . date('Y-m-d') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['ID', 'Applied At', 'Status', 'Expected Salary', 'Job Title', 'Email', 'Phone']);

        foreach ($applications as $app) {
            $candidateUserId = (int)($app['candidate_user_id'] ?? 0);
            $email = ''; $phone = '';
            if ($candidateUserId > 0) {
                $candidate = \App\Models\Candidate::findByUserId($candidateUserId);
                if ($candidate && $candidate->isPremium()) {
                    $email = $app['candidate_email'] ?? '';
                    $phone = $app['phone'] ?? '';
                }
            }
            fputcsv($handle, [$app['id'], $app['applied_at'], $app['status'], $app['expected_salary'], $app['job_title'], $email, $phone]);
        }
        fclose($handle);
        $response->download($filepath, $filename);
    }

    public function generateScore(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $applicationId = (int)$request->param('id');
        $user = $this->currentUser;
        $employer = $user->employer();

        $application = Application::find($applicationId);
        if (!$application) {
            $response->json(['error' => 'Application not found'], 404);
            return;
        }

        $job = Job::find((int)($application->attributes['job_id'] ?? 0));
        if (!$job || (int)($job->attributes['employer_id'] ?? $job->employer_id ?? 0) !== (int)$employer->id) {
            $response->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $candidate = Candidate::where('user_id', '=', $application->attributes['candidate_user_id'] ?? 0)->first();
        if (!$candidate) {
            $response->json(['error' => 'Candidate not found'], 404);
            return;
        }

        try {
            $matchService = new JobMatchService();
            $matchData = $matchService->calculateMatch((int)$candidate->attributes['id'], (int)$job->attributes['id'], true);
            $response->json(['success' => true, 'message' => 'Match score calculated successfully', 'match_data' => $matchData]);
        } catch (\Exception $e) {
            $response->json(['error' => 'Failed to calculate match score', 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkStatus(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $user = $this->currentUser;
        $employer = $user->employer();
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
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE applications a INNER JOIN jobs j ON a.job_id = j.id SET a.status = ? WHERE a.id IN ($placeholders) AND j.employer_id = ?";
        $params = array_merge([$newStatus], $ids, [$employer->id]);
        
        try {
            $stmt = $db->query($sql, $params);
            $response->json(['success' => true, 'updated_count' => $stmt->rowCount()]);
        } catch (\Exception $e) {
            $response->json(['error' => 'Database error'], 500);
        }
    }
}
