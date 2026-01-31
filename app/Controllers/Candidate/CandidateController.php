<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\JobBookmark;
use App\Models\JobView;
use App\Models\Review;
use App\Models\Application;
use App\Models\Job;
use App\Models\CandidatePremiumPurchase;
use App\Models\Skill;

class CandidateController extends BaseController
{
    /**
     * Extract name from email address
     * Example: "tagsindia1997@gmail.com" -> "Tags India"
     */
    private function extractNameFromEmail(string $email): ?string
    {
        if (empty($email)) {
            return null;
        }
        
        // Get the part before @
        $localPart = explode('@', $email)[0] ?? '';
        if (empty($localPart)) {
            return null;
        }
        
        // Remove numbers
        $namePart = preg_replace('/\d+/', '', $localPart);
        
        // Split by common separators (., _, -)
        $parts = preg_split('/[._-]+/', $namePart);
        
        // Filter out empty parts
        $parts = array_filter($parts, fn($p) => !empty(trim($p)));
        
        if (empty($parts)) {
            // If no separators, try to split camelCase or all lowercase
            // For "tagsindia" -> "Tags India"
            $namePart = preg_replace('/([a-z])([A-Z])/', '$1 $2', $namePart);
            $parts = [trim($namePart)];
        }
        
        // Capitalize each word
        $nameParts = array_map(function($part) {
            $part = trim($part);
            if (empty($part)) return '';
            // Capitalize first letter, lowercase rest
            return ucfirst(strtolower($part));
        }, $parts);
        
        $nameParts = array_filter($nameParts, fn($p) => !empty($p));
        
        if (empty($nameParts)) {
            return null;
        }
        
        // Join with spaces
        $fullName = implode(' ', $nameParts);
        
        // If result is too short or just numbers, return null
        if (strlen($fullName) < 2) {
            return null;
        }
        
        return $fullName;
    }

    /**
     * Check if user is candidate, redirect if not
     */
    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login?redirect=' . urlencode($request->getUri()));
            return null;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            // Create candidate profile if doesn't exist
            $candidate = Candidate::createForUser($userId);
        }

        return $candidate;
    }

    /**
     * Dashboard
     */
    public function dashboard(Request $request, Response $response): void
    {
        // Get user ID from session
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        // Directly fetch candidate from database to get fresh data
        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            // Create candidate profile if doesn't exist
            $candidate = Candidate::createForUser((int)$userId);
        }

        // Always recalculate profile strength to ensure it's up to date
        if ($candidate && isset($candidate->attributes['id'])) {
            // Update profile strength using current candidate data
            $candidate->updateProfileStrength();
            
            // Reload candidate using findByUserId to ensure we get ALL columns including JSON
            $candidate = Candidate::findByUserId((int)$userId);
        }

        // Get recommended jobs (will implement matching later)
        $jobs = $this->getRecommendedJobs($candidate);
        $bookmarkedJobs = $this->getBookmarkedJobs($candidate);
        $recentViews = $this->getRecentViews($candidate);
        $applications = $this->getApplications($candidate);
        
        // Calculate stats from applications
        $stats = [
            'applications' => count($applications),
            'shortlisted' => count(array_filter($applications, fn($a) => strtolower($a['status'] ?? '') === 'shortlisted')),
            'interviews' => count(array_filter($applications, fn($a) => strtolower($a['status'] ?? '') === 'interview')),
            'hired' => count(array_filter($applications, fn($a) => strtolower($a['status'] ?? '') === 'hired'))
        ];

        // Get unread counts for navigation
        $unreadCounts = $this->getUnreadCounts($candidate);
        
        // Premium purchases history (latest 5)
        $premiumPurchasesModels = CandidatePremiumPurchase::where('candidate_id', '=', (int)($candidate->attributes['id'] ?? 0))
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();
        $premiumPurchases = array_map(fn($p) => $p->attributes, $premiumPurchasesModels);
        $activePurchase = null;
        foreach ($premiumPurchases as $p) {
            if (($p['status'] ?? '') === 'completed') { $activePurchase = $p; break; }
        }

        $response->view('candidate/dashboard', [
            'title' => 'Candidate Dashboard',
            'candidate' => $candidate,
            'jobs' => $jobs,
            'bookmarkedJobs' => $bookmarkedJobs,
            'recentViews' => $recentViews,
            'applications' => $applications,
            'stats' => $stats,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications'],
            'premiumPurchases' => $premiumPurchases,
            'activePurchase' => $activePurchase
        ]);
    }

    /**
     * View all applications
     */
    public function applications(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Use session user_id for reliability
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $userId = $candidate->attributes['user_id'] ?? null;
        }
        
        if (!$userId) {
            $response->redirect('/candidate/dashboard');
            return;
        }
        
        $applications = \App\Models\Application::where('candidate_user_id', '=', $userId)
            ->orderBy('applied_at', 'DESC')
            ->get();
        
        $result = [];
        $statusLabels = [
            'applied' => 'Applied',
            'screening' => 'Under Review',
            'shortlisted' => 'Shortlisted',
            'interview' => 'Interview Scheduled',
            'offer' => 'Offer Received',
            'hired' => 'Hired',
            'rejected' => 'Rejected'
        ];
        
        $db = \App\Core\Database::getInstance();
        
        foreach ($applications as $app) {
            try {
                $job = $app->job();
                $appData = $app->attributes;
                $status = strtolower($app->attributes['status'] ?? 'applied');
                $appData['status'] = $status;
                $appData['job_title'] = $job ? ($job->attributes['title'] ?? 'Unknown') : 'Unknown';
                $appData['job_id'] = $app->attributes['job_id'] ?? null;
                $appData['job_slug'] = $job ? ($job->attributes['slug'] ?? null) : null;
                $employer = $job ? $job->employer() : null;
                $appData['company_name'] = $employer ? ($employer->attributes['company_name'] ?? '') : '';
                $appData['employer_id'] = $employer ? ($employer->attributes['id'] ?? null) : null;
                $appData['status_label'] = $statusLabels[$status] ?? ucfirst($app->attributes['status'] ?? 'applied');
                $appliedAt = $app->attributes['applied_at'] ?? $app->attributes['created_at'] ?? date('Y-m-d H:i:s');
                $appData['applied_at'] = date('M d, Y', strtotime($appliedAt));
                $appData['applied_at_full'] = date('M d, Y h:i A', strtotime($appliedAt));
                
                if ($status === 'interview') {
                    try {
                        $interviewSql = "SELECT * FROM interviews 
                                        WHERE application_id = :application_id 
                                        AND status IN ('scheduled', 'rescheduled')
                                        ORDER BY scheduled_start DESC 
                                        LIMIT 1";
                        $interview = $db->fetchOne($interviewSql, ['application_id' => $app->attributes['id']]);
                        if ($interview) {
                            $appData['interview'] = [
                                'id' => $interview['id'],
                                'type' => $interview['interview_type'],
                                'scheduled_start' => $interview['scheduled_start'],
                                'scheduled_end' => $interview['scheduled_end'],
                                'date' => date('M d, Y', strtotime($interview['scheduled_start'])),
                                'start_time' => date('h:i A', strtotime($interview['scheduled_start'])),
                                'end_time' => date('h:i A', strtotime($interview['scheduled_end'])),
                                'location' => $interview['location'],
                                'meeting_link' => $interview['meeting_link'],
                                'timezone' => $interview['timezone'] ?? 'Asia/Kolkata',
                                'status' => $interview['status']
                            ];
                        }
                    } catch (\Throwable $e) {
                    }
                }
                
                $result[] = $appData;
            } catch (\Throwable $e) {
            }
        }
        
        // Calculate stats
        $stats = [
            'total' => count($result),
            'applied' => count(array_filter($result, function ($a) { return strtolower($a['status'] ?? '') === 'applied'; })),
            'shortlisted' => count(array_filter($result, function ($a) { return strtolower($a['status'] ?? '') === 'shortlisted'; })),
            'interview' => count(array_filter($result, function ($a) { return strtolower($a['status'] ?? '') === 'interview'; })),
            'rejected' => count(array_filter($result, function ($a) { return strtolower($a['status'] ?? '') === 'rejected'; }))
        ];

        // Get unread counts for navigation
        $unreadCounts = $this->getUnreadCounts($candidate);

        $response->view('candidate/applications', [
            'title' => 'My Applications',
            'candidate' => $candidate,
            'applications' => $result,
            'stats' => $stats,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications']
        ]);
    }

    /**
     * View all interviews
     */
    public function interviews(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $userId = $candidate->attributes['user_id'] ?? $_SESSION['user_id'];
        $db = \App\Core\Database::getInstance();
        
        // Fetch all interviews linked to candidate's applications
        $sql = "SELECT i.*, a.status as application_status, j.title as job_title, j.slug as job_slug, j.id as job_id,
                       e.company_name, e.logo_url as company_logo
                FROM interviews i
                JOIN applications a ON i.application_id = a.id
                JOIN jobs j ON a.job_id = j.id
                JOIN employers e ON j.employer_id = e.id
                WHERE a.candidate_user_id = :user_id
                ORDER BY i.scheduled_start ASC";
                
        $interviews = $db->fetchAll($sql, ['user_id' => $userId]);
        
        $deduped = [];
        foreach ($interviews as $interview) {
            $appId = (int)($interview['application_id'] ?? 0);
            if ($appId <= 0) continue;
            $curr = $deduped[$appId] ?? null;
            if (!$curr) {
                $deduped[$appId] = $interview;
                continue;
            }
            $currStart = (string)($curr['scheduled_start'] ?? '');
            $newStart = (string)($interview['scheduled_start'] ?? '');
            if ($newStart > $currStart) {
                $deduped[$appId] = $interview;
            }
        }

        // Group into upcoming and past
        $upcoming = [];
        $past = [];
        $now = date('Y-m-d H:i:s');
        
        foreach (array_values($deduped) as $interview) {
             // Format dates
            $start = strtotime($interview['scheduled_start']);
            $end = strtotime($interview['scheduled_end']);
            
            $interview['date_formatted'] = date('l, M d, Y', $start);
            $interview['time_range'] = date('h:i A', $start) . ' - ' . date('h:i A', $end);
            $interview['is_video'] = ($interview['interview_type'] === 'video');
            $interview['is_phone'] = ($interview['interview_type'] === 'phone');
            $interview['is_onsite'] = ($interview['interview_type'] === 'onsite');
            $interview['is_telephonic'] = ($interview['interview_type'] === 'telephonic');
            // Platform label
            $type = (string)($interview['interview_type'] ?? '');
            $link = (string)($interview['meeting_link'] ?? '');
            $platformLabel = 'Video';
            if ($type === 'phone') {
                $platformLabel = 'Phone';
            } elseif ($type === 'onsite') {
                $platformLabel = 'On-site';
            } elseif ($type === 'telephonic') {
                $platformLabel = 'Telephonic';
            } elseif ($type === 'video') {
                if ($link === '') {
                    $platformLabel = 'Jitsi (auto)';
                } else {
                    $host = strtolower((string)(parse_url($link, PHP_URL_HOST) ?: ''));
                    if ($host === '' || strpos($host, 'jit.si') !== false) $platformLabel = 'Jitsi';
                    elseif (strpos($host, 'zoom.us') !== false) $platformLabel = 'Zoom';
                    elseif (strpos($host, 'meet.google.com') !== false) $platformLabel = 'Google Meet';
                    elseif (strpos($host, 'teams.microsoft.com') !== false || strpos($host, 'office.com') !== false) $platformLabel = 'Microsoft Teams';
                }
            }
            $interview['platform_label'] = $platformLabel;
            
            if ($interview['scheduled_start'] > $now) {
                $upcoming[] = $interview;
            } else {
                $past[] = $interview;
            }
        }
        
        // Sort past interviews descending (most recent first)
        usort($past, function($a, $b) {
            return strtotime($b['scheduled_start']) - strtotime($a['scheduled_start']);
        });

        // Get unread counts for navigation
        $unreadCounts = $this->getUnreadCounts($candidate);

        $response->view('candidate/interviews/index', [
            'title' => 'My Interviews',
            'candidate' => $candidate,
            'upcoming' => $upcoming,
            'past' => $past,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications']
        ]);
    }

    /**
     * View profile
     */
    public function viewProfile(Request $request, Response $response): void
    {
        // Get user ID from session
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        // Directly fetch candidate by user_id to ensure fresh data
        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            // Create candidate profile if doesn't exist
            $candidate = Candidate::createForUser((int)$userId);
            $response->redirect('/candidate/profile/complete');
            return;
        }

        // Get candidate ID
        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            error_log("ERROR: Candidate found but no ID - User ID: {$userId}");
            $response->redirect('/candidate/profile/complete');
            return;
        }
        
        // Log for debugging - check what data we have
        error_log("View Profile - User ID: {$userId}, Candidate ID: {$candidateId}");
        error_log("View Profile - Full Name: " . ($candidate->attributes['full_name'] ?? 'NULL'));
        error_log("View Profile - City: " . ($candidate->attributes['city'] ?? 'NULL'));
        error_log("View Profile - Mobile: " . ($candidate->attributes['mobile'] ?? 'NULL'));
        error_log("View Profile - All attributes: " . json_encode($candidate->attributes));
        
        // Get all related data from JSON columns in candidates table
        $education = [];
        $experience = [];
        $skills = [];
        $languages = [];
        
        // Parse JSON data from candidates table
        if (!empty($candidate->attributes['education_data'])) {
            $education = json_decode($candidate->attributes['education_data'], true) ?? [];
            error_log("View Profile - Education decoded: " . count($education) . " records");
        } else {
            error_log("View Profile - Education data is empty or NULL");
        }
        
        if (!empty($candidate->attributes['experience_data'])) {
            $experience = json_decode($candidate->attributes['experience_data'], true) ?? [];
            error_log("View Profile - Experience decoded: " . count($experience) . " records");
        } else {
            error_log("View Profile - Experience data is empty or NULL");
        }
        
        if (!empty($candidate->attributes['skills_data'])) {
            $skills = json_decode($candidate->attributes['skills_data'], true) ?? [];
            error_log("View Profile - Skills decoded: " . count($skills) . " records");
        } else {
            error_log("View Profile - Skills data is empty or NULL");
        }
        
        if (!empty($candidate->attributes['languages_data'])) {
            $languages = json_decode($candidate->attributes['languages_data'], true) ?? [];
            error_log("View Profile - Languages decoded: " . count($languages) . " records");
        } else {
            error_log("View Profile - Languages data is empty or NULL");
        }

        $response->view('candidate/profile/view', [
            'title' => 'My Profile',
            'candidate' => $candidate,
            'education' => $education,
            'experience' => $experience,
            'skills' => $skills,
            'languages' => $languages
        ]);
    }

    /**
     * Show change password form
     */
    public function changePassword(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $response->view('candidate/change-password', [
            'title' => 'Change Password',
            'candidate' => $candidate
        ]);
    }

    public function viewReviews(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $userId = $candidate->attributes['user_id'] ?? $_SESSION['user_id'];
        $db = \App\Core\Database::getInstance();
        $userReviews = [];

        try {
            $userReviews = $db->fetchAll(
                "SELECT r.*, c.company_name, c.logo_url as company_logo 
                 FROM reviews r
                 LEFT JOIN companies c ON r.company_id = c.id
                 WHERE r.user_id = :user_id 
                 ORDER BY r.created_at DESC",
                ['user_id' => $userId]
            );
        } catch (\Throwable $e) {
            error_log('Failed to fetch user reviews: ' . $e->getMessage());
        }

        $unreadCounts = $this->getUnreadCounts($candidate);

        $response->view('candidate/reviews', [
            'title' => 'My Reviews',
            'candidate' => $candidate,
            'userReviews' => $userReviews,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications']
        ]);
    }

    public function createReview(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $unreadCounts = $this->getUnreadCounts($candidate);

        $response->view('candidate/reviews/create', [
            'title' => 'Write a Review',
            'candidate' => $candidate,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications']
        ]);
    }

    public function reviewSuccess(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $unreadCounts = $this->getUnreadCounts($candidate);

        $response->view('candidate/reviews/success', [
            'title' => 'Review Submitted',
            'candidate' => $candidate,
            'unreadMessages' => $unreadCounts['messages'],
            'unreadNotifications' => $unreadCounts['notifications']
        ]);
    }

    public function submitReview(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody();
        if (empty($data)) {
            $data = $request->all();
        }

        $db = \App\Core\Database::getInstance();
        
        try {
            // Logic to calculate rating if it's coming from the multi-step form
            $rating = $data['rating'] ?? 0;
            if (empty($rating) && isset($data['answers'])) {
                // Simple logic: Yes = 1 point. Scale to 5.
                $positiveCount = 0;
                $totalQuestions = 0;
                foreach ($data['answers'] as $ans) {
                    if (is_array($ans) && isset($ans['answer'])) {
                        $totalQuestions++;
                        if (strtolower($ans['answer']) === 'yes') {
                            $positiveCount++;
                        }
                    }
                }
                // Rough estimation for demo purposes
                $rating = max(1, min(5, $positiveCount)); 
            }

            // Construct review text from answers if needed
            $reviewText = $data['review_text'] ?? '';
            if (empty($reviewText) && isset($data['answers'])) {
                $parts = [];
                foreach ($data['answers'] as $ans) {
                    $q = $ans['question'] ?? '';
                    $a = $ans['answer'] ?? '';
                    if (is_array($a)) $a = implode(', ', $a);
                    $parts[] = "$q: $a";
                }
                $reviewText = implode("\n\n", $parts);
            }
            
            $title = $data['title'] ?? 'Review for ' . ($data['company_name'] ?? 'Company');

            // Insert directly using DB if Model doesn't support easy insert or specific fields
            $db->execute(
                "INSERT INTO reviews (user_id, candidate_id, company_id, reviewer_name, rating, title, review_text, created_at) 
                 VALUES (:uid, :cid, :coid, :name, :rating, :title, :text, NOW())",
                [
                    'uid' => $candidate->attributes['user_id'] ?? $_SESSION['user_id'],
                    'cid' => $candidate->attributes['id'],
                    'coid' => $data['company_id'] ?? 0, // 0 if not linked to a specific company ID yet
                    'name' => $candidate->attributes['full_name'] ?? 'Anonymous',
                    'rating' => (int)$rating,
                    'title' => $title,
                    'text' => $reviewText
                ]
            );

            $response->json(['success' => true, 'redirect' => '/candidate/reviews']);
        } catch (\Throwable $e) {
            error_log('Review submission error: ' . $e->getMessage());
            $response->json(['error' => 'Failed to submit review: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody();
        $user = $candidate->user();

        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }

        // Verify current password
        if (empty($data['current_password']) || !$user->verifyPassword($data['current_password'])) {
            $response->json(['error' => 'Current password is incorrect'], 422);
            return;
        }

        // Validate new password
        if (empty($data['new_password']) || strlen($data['new_password']) < 8) {
            $response->json(['error' => 'New password must be at least 8 characters'], 422);
            return;
        }

        if ($data['new_password'] !== ($data['confirm_password'] ?? '')) {
            $response->json(['error' => 'New passwords do not match'], 422);
            return;
        }

        // Update password
        $user->setPassword($data['new_password']);
        $user->save();

        $response->json(['success' => true, 'message' => 'Password updated successfully']);
    }

    /**
     * Profile completion page
     */
    public function profileComplete(Request $request, Response $response): void
    {
        // Get user ID from session
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        // Directly fetch candidate from database to get fresh data
        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            // Create candidate profile if doesn't exist
            $candidate = Candidate::createForUser((int)$userId);
        }

        // Ensure candidate exists and has attributes
        if (!$candidate) {
            error_log("CRITICAL: Could not create or find candidate for User ID: {$userId}");
            $response->view('candidate/profile-complete', [
                'title' => 'Complete Your Profile',
                'candidate' => new Candidate(),
                'user' => null,
                'allSkills' => [],
                'existingEducation' => [],
                'existingExperience' => [],
                'existingSkills' => [],
                'existingLanguages' => []
            ]);
            return;
        }

        // Get candidate ID - should always exist after findByUserId
        $candidateId = $candidate->attributes['id'] ?? null;
        
        // Log candidate data immediately after fetch to verify it's correct
        error_log("Profile Complete - After findByUserId:");
        error_log("  - Candidate object exists: " . ($candidate ? 'YES' : 'NO'));
        error_log("  - Candidate ID: " . ($candidateId ?? 'NULL'));
        error_log("  - Attributes exists: " . (isset($candidate->attributes) ? 'YES' : 'NO'));
        error_log("  - Attributes is array: " . (is_array($candidate->attributes ?? null) ? 'YES' : 'NO'));
        error_log("  - Attributes count: " . (is_array($candidate->attributes ?? null) ? count($candidate->attributes) : 'NOT ARRAY'));
        error_log("  - Full Name: " . ($candidate->attributes['full_name'] ?? 'NULL'));
        
        // CRITICAL: If attributes are not set, reload immediately
        if (!is_array($candidate->attributes ?? null) || empty($candidate->attributes['id'] ?? null)) {
            error_log("CRITICAL: Attributes invalid, reloading candidate immediately");
            $candidate = Candidate::findByUserId((int)$userId);
            if ($candidate) {
                $candidateId = $candidate->attributes['id'] ?? null;
                error_log("After critical reload - Candidate ID: " . ($candidateId ?? 'NULL'));
                error_log("After critical reload - Attributes is array: " . (is_array($candidate->attributes ?? null) ? 'YES' : 'NO'));
                if (!is_array($candidate->attributes ?? null)) {
                    error_log("FATAL: Even after reload, attributes are not an array!");
                    // Last resort: create new candidate object directly from database
                    $db = \App\Core\Database::getInstance();
                    $directResult = $db->fetchOne("SELECT * FROM candidates WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
                    if ($directResult && is_array($directResult)) {
                        // CRITICAL: Create candidate WITHOUT passing attributes to constructor to avoid nesting
                        $candidate = new Candidate();
                        // Directly assign to attributes property
                        $candidate->attributes = $directResult;
                        $candidateId = $candidate->attributes['id'] ?? null;
                        error_log("Last resort fix - Candidate ID: " . ($candidateId ?? 'NULL'));
                        error_log("Last resort fix - Attributes count: " . (is_array($candidate->attributes) ? count($candidate->attributes) : 'NOT ARRAY'));
                    }
                }
            }
        }

        // Recalculate profile strength to ensure it's up to date
        if ($candidate && $candidateId) {
            $candidate->updateProfileStrength();
            // Reload using findByUserId to get ALL columns including JSON and updated strength
            $candidate = Candidate::findByUserId((int)$userId);
            if ($candidate) {
                error_log("Profile Complete - After updateProfileStrength reload:");
                error_log("  - Full Name: " . ($candidate->attributes['full_name'] ?? 'NULL'));
                error_log("  - Profile Strength: " . ($candidate->attributes['profile_strength'] ?? 'NULL'));
            }
        }

        // CRITICAL: Verify candidate still has attributes before calling user()
        if (!is_array($candidate->attributes ?? null) || empty($candidate->attributes['id'] ?? null)) {
            error_log("CRITICAL: Candidate lost attributes before user() call - reloading");
            $candidate = Candidate::findByUserId((int)$userId);
            if (!$candidate || !is_array($candidate->attributes ?? null)) {
                error_log("FATAL: Cannot reload candidate with valid attributes");
                $response->view('candidate/profile-complete', [
                    'title' => 'Complete Your Profile',
                    'candidate' => new Candidate(),
                    'user' => null,
                    'allSkills' => [],
                    'existingEducation' => [],
                    'existingExperience' => [],
                    'existingSkills' => [],
                    'existingLanguages' => []
                ]);
                return;
            }
        }
        
        // Get user data for pre-filling
        $user = $candidate->user();
        
        // CRITICAL: Verify candidate still has attributes after user() call
        if (!is_array($candidate->attributes ?? null) || empty($candidate->attributes['id'] ?? null)) {
            error_log("CRITICAL: Candidate lost attributes after user() call - reloading");
            $candidate = Candidate::findByUserId((int)$userId);
        }
        
        // Pre-fill candidate profile with user data if not already set
        $updateData = [];
        if ($user) {
            if (empty($candidate->attributes['full_name'])) {
                $name = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? null;
                if (!$name) {
                    $name = $this->extractNameFromEmail($user->attributes['email'] ?? '');
                }
                if ($name) {
                    $updateData['full_name'] = $name;
                }
            }
            if (empty($candidate->attributes['profile_picture']) && !empty($user->attributes['google_picture'])) {
                $updateData['profile_picture'] = $user->attributes['google_picture'];
            }
            if (empty($candidate->attributes['mobile']) && !empty($user->attributes['phone'] ?? null)) {
                $updateData['mobile'] = $user->attributes['phone'];
            }
            if (!empty($updateData)) {
                $candidate->fill($updateData);
                $candidate->save();
                $candidate->updateProfileStrength();
            }
        }

        // Load existing education, experience, skills, languages from JSON columns
        $education = [];
        $experience = [];
        $skills = [];
        $languages = [];
        
        // Parse JSON data from candidates table
        if (!empty($candidate->attributes['education_data'])) {
            $education = json_decode($candidate->attributes['education_data'], true) ?? [];
        }
        
        if (!empty($candidate->attributes['experience_data'])) {
            $experience = json_decode($candidate->attributes['experience_data'], true) ?? [];
        }
        
        if (!empty($candidate->attributes['skills_data'])) {
            $skills = json_decode($candidate->attributes['skills_data'], true) ?? [];
        }
        
        if (!empty($candidate->attributes['languages_data'])) {
            $languages = json_decode($candidate->attributes['languages_data'], true) ?? [];
        }

        // Get all skills for autocomplete
        try {
            $allSkills = Skill::all();
            $allSkills = array_map(fn($s) => $s->attributes, $allSkills);
        } catch (\Exception $e) {
            error_log("Error loading skills: " . $e->getMessage());
            $allSkills = [];
        }

        // Prepare user data for view (including OAuth data)
        $userData = null;
        if ($user) {
            $userData = $user->attributes;
        }
        
        // Log what we're passing to the view - verify candidate object is correct
        error_log("Profile Complete - Passing to view:");
        error_log("  - Candidate object exists: " . ($candidate ? 'YES' : 'NO'));
        error_log("  - Candidate attributes is array: " . (is_array($candidate->attributes ?? null) ? 'YES' : 'NO'));
        error_log("  - Candidate attributes count: " . (is_array($candidate->attributes ?? null) ? count($candidate->attributes) : '0'));
        error_log("  - Candidate ID: " . ($candidate->attributes['id'] ?? 'NULL'));
        error_log("  - Candidate full_name: " . ($candidate->attributes['full_name'] ?? 'NULL'));
        error_log("  - Candidate dob: " . ($candidate->attributes['dob'] ?? 'NULL'));
        error_log("  - Candidate gender: " . ($candidate->attributes['gender'] ?? 'NULL'));
        error_log("  - Candidate mobile: " . ($candidate->attributes['mobile'] ?? 'NULL'));
        error_log("  - Candidate city: " . ($candidate->attributes['city'] ?? 'NULL'));
        error_log("  - Education data exists: " . (!empty($candidate->attributes['education_data']) ? 'YES (' . strlen($candidate->attributes['education_data']) . ' chars)' : 'NO'));
        error_log("  - Experience data exists: " . (!empty($candidate->attributes['experience_data']) ? 'YES (' . strlen($candidate->attributes['experience_data']) . ' chars)' : 'NO'));
        error_log("  - Skills data exists: " . (!empty($candidate->attributes['skills_data']) ? 'YES (' . strlen($candidate->attributes['skills_data']) . ' chars)' : 'NO'));
        
        $response->view('candidate/profile-complete', [
            'title' => 'Complete Your Profile',
            'candidate' => $candidate,
            'user' => $userData, // Use userData (array) for view
            'allSkills' => $allSkills,
            'existingEducation' => $education,
            'existingExperience' => $experience,
            'existingSkills' => $skills,
            'existingLanguages' => $languages
        ]);
    }

    /**
     * Save profile section
     */
    public function saveProfile(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody() ?? $request->all();
        $section = $data['section'] ?? 'basic';

        try {
            switch ($section) {
                case 'basic':
                    $this->saveBasicDetails($candidate, $data);
                    break;
                case 'education':
                    $this->saveEducation($candidate, $data);
                    break;
                case 'experience':
                    $this->saveExperience($candidate, $data);
                    break;
                case 'skills':
                    $this->saveSkills($candidate, $data);
                    break;
                case 'languages':
                    $this->saveLanguages($candidate, $data);
                    break;
                case 'additional':
                    $this->saveAdditional($candidate, $data);
                    break;
                case 'preferences':
                    $this->savePreferences($candidate, $data);
                    break;
                case 'auto_apply':
                    $this->saveAutoApply($candidate, $data);
                    break;
            }

            // Get user ID to reload using findByUserId (ensures ALL columns including JSON)
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                // Reload candidate from database using findByUserId to get ALL columns including JSON
                $candidate = Candidate::findByUserId((int)$userId);
                if ($candidate) {
                    // Log what we saved for debugging
                    $candidateId = $candidate->attributes['id'] ?? null;
                    error_log("Profile Save - Section: {$section}, Candidate ID: {$candidateId}, User ID: {$userId}");
                    if ($section === 'education' && !empty($candidate->attributes['education_data'])) {
                        error_log("Education data saved: " . substr($candidate->attributes['education_data'], 0, 200));
                    }
                    if ($section === 'experience' && !empty($candidate->attributes['experience_data'])) {
                        error_log("Experience data saved: " . substr($candidate->attributes['experience_data'], 0, 200));
                    }
                    if ($section === 'skills' && !empty($candidate->attributes['skills_data'])) {
                        error_log("Skills data saved: " . substr($candidate->attributes['skills_data'], 0, 200));
                    }
                    if ($section === 'languages' && !empty($candidate->attributes['languages_data'])) {
                        error_log("Languages data saved: " . substr($candidate->attributes['languages_data'], 0, 200));
                    }
                }
            }
            
            // Update profile strength after reload
            if ($candidate && $userId) {
                $candidate->updateProfileStrength();
                // Reload again using findByUserId to get updated profile_strength and ALL columns
                $candidate = Candidate::findByUserId((int)$userId);
                try {
                    $matchService = new \App\Services\JobMatchService();
                    $matchService->findMatchingJobsForCandidateAndNotifyEmployers($candidate);
                    $matchService->findMatchingJobsForCandidateAndNotifyCandidate($candidate);
                } catch (\Throwable $t) {}
            }

            $profileStrength = $candidate ? ($candidate->attributes['profile_strength'] ?? 0) : 0;
            $isComplete = $candidate ? $candidate->isProfileComplete() : false;

            $response->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'profile_strength' => $profileStrength,
                'is_complete' => $isComplete
            ]);
        } catch (\Exception $e) {
            error_log("Profile save error: " . $e->getMessage());
            $response->json(['error' => 'Failed to save profile: ' . $e->getMessage()], 500);
        }
    }

    private function saveBasicDetails(Candidate $candidate, array $data): void
    {
        // Only update fields that are provided (incremental save)
        $updateData = [];
        if (isset($data['full_name'])) $updateData['full_name'] = $data['full_name'];
        if (isset($data['dob'])) $updateData['dob'] = $data['dob'] ?: null;
        if (isset($data['gender'])) $updateData['gender'] = $data['gender'] ?: null;
        if (isset($data['mobile'])) $updateData['mobile'] = $data['mobile'] ?: null;
        if (isset($data['city'])) $updateData['city'] = $data['city'] ?: null;
        if (isset($data['state'])) $updateData['state'] = $data['state'] ?: null;
        if (isset($data['country'])) $updateData['country'] = $data['country'] ?: null;
        if (isset($data['self_introduction'])) $updateData['self_introduction'] = $data['self_introduction'] ?: null;
        if (isset($data['profile_picture'])) $updateData['profile_picture'] = $data['profile_picture'] ?: null;
        if (isset($data['resume_url'])) $updateData['resume_url'] = $data['resume_url'] ?: null;
        if (isset($data['video_intro_url'])) $updateData['video_intro_url'] = $data['video_intro_url'] ?: null;
        if (isset($data['video_intro_type'])) $updateData['video_intro_type'] = $data['video_intro_type'] ?: null;
        
        if (!empty($updateData)) {
            $candidate->fill($updateData);
            $candidate->save();
        }
    }

    private function saveEducation(Candidate $candidate, array $data): void
    {
        // Store education as JSON in candidates table
        if (isset($data['education']) && is_array($data['education'])) {
            // Clean and validate education data
            $educationData = [];
            foreach ($data['education'] as $edu) {
                if (!empty($edu['degree']) || !empty($edu['institution'])) {
                    $educationData[] = [
                        'degree' => $edu['degree'] ?? '',
                        'field_of_study' => $edu['field_of_study'] ?? null,
                        'institution' => $edu['institution'] ?? '',
                        'start_date' => $edu['start_date'] ?? null,
                        'end_date' => $edu['end_date'] ?? null,
                        'is_current' => $edu['is_current'] ?? 0,
                        'grade' => $edu['grade'] ?? null,
                        'description' => $edu['description'] ?? null,
                    ];
                }
            }
            // Use fill() to ensure the field is properly saved
            $candidate->fill(['education_data' => json_encode($educationData)]);
            $candidate->save();
        }
    }

    private function saveExperience(Candidate $candidate, array $data): void
    {
        // Store experience as JSON in candidates table
        if (isset($data['experience']) && is_array($data['experience'])) {
            // Clean and validate experience data
            $experienceData = [];
            foreach ($data['experience'] as $exp) {
                if (!empty($exp['job_title']) || !empty($exp['company_name'])) {
                    $experienceData[] = [
                        'job_title' => $exp['job_title'] ?? '',
                        'company_name' => $exp['company_name'] ?? '',
                        'start_date' => $exp['start_date'] ?? null,
                        'end_date' => $exp['end_date'] ?? null,
                        'is_current' => $exp['is_current'] ?? 0,
                        'description' => $exp['description'] ?? null,
                        'location' => $exp['location'] ?? null,
                    ];
                }
            }
            // Use fill() to ensure the field is properly saved
            $candidate->fill(['experience_data' => json_encode($experienceData)]);
            $candidate->save();
        }
    }

    private function saveSkills(Candidate $candidate, array $data): void
    {
        // Store skills as JSON in candidates table
        if (isset($data['skills']) && is_array($data['skills'])) {
            // Clean and validate skills data
            $skillsData = [];
            foreach ($data['skills'] as $skillData) {
                $skillName = $skillData['name'] ?? $skillData['skill_name'] ?? '';
                if (!empty($skillName)) {
                    // Get or create skill in skills table for reference
                    $skill = Skill::where('name', '=', $skillName)->first();
                    if (!$skill) {
                        $skill = new Skill();
                        $skill->fill(['name' => $skillName]);
                        $skill->save();
                    }
                    
                    $skillsData[] = [
                        'skill_id' => $skill->attributes['id'] ?? null,
                        'name' => $skillName,
                        'proficiency_level' => $skillData['proficiency_level'] ?? 'intermediate',
                        'years_of_experience' => $skillData['years_of_experience'] ?? null,
                    ];
                }
            }
            // Use fill() to ensure the field is properly saved
            $candidate->fill(['skills_data' => json_encode($skillsData)]);
            $candidate->save();
        }
    }

    private function saveLanguages(Candidate $candidate, array $data): void
    {
        // Store languages as JSON in candidates table
        if (isset($data['languages']) && is_array($data['languages'])) {
            // Clean and validate languages data
            $languagesData = [];
            foreach ($data['languages'] as $lang) {
                if (!empty($lang['language'])) {
                    $languagesData[] = [
                        'language' => $lang['language'] ?? '',
                        'proficiency' => $lang['proficiency'] ?? 'conversational',
                    ];
                }
            }
            // Use fill() to ensure the field is properly saved
            $candidate->fill(['languages_data' => json_encode($languagesData)]);
            $candidate->save();
        }
    }

    private function saveAdditional(Candidate $candidate, array $data): void
    {
        $candidate->fill([
            'expected_salary_min' => $data['expected_salary_min'] ?? null,
            'expected_salary_max' => $data['expected_salary_max'] ?? null,
            'current_salary' => $data['current_salary'] ?? null,
            'notice_period' => $data['notice_period'] ?? null,
            'preferred_job_location' => $data['preferred_job_location'] ?? null,
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'github_url' => $data['github_url'] ?? null,
            'website_url' => $data['website_url'] ?? null,
        ]);
        $candidate->save();
    }

    private function savePreferences(Candidate $candidate, array $data): void
    {
        $titles = $data['preferred_job_titles'] ?? [];
        $types = $data['preferred_job_types'] ?? [];
        $workMode = $data['preferred_work_mode'] ?? null;
        $locations = $data['preferred_locations'] ?? [];
        $minSalary = $data['minimum_acceptable_salary'] ?? null;
        $relocate = $data['open_to_relocation'] ?? null;
        if (!is_array($titles)) $titles = [];
        if (!is_array($types)) $types = [];
        if (!is_array($locations)) $locations = [];
        $titles = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : ''; }, $titles)));
        $types = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : ''; }, $types)));
        $locations = array_values(array_filter(array_map(function($v){ return is_string($v) ? trim($v) : ''; }, $locations)));
        $workMode = is_string($workMode) ? trim($workMode) : null;
        $minSalary = is_numeric($minSalary) ? (int)$minSalary : null;
        $relocate = is_numeric($relocate) ? (int)$relocate : (in_array(strtolower((string)$relocate), ['yes','true','1']) ? 1 : 0);
        $payload = [
            'preferred_job_titles' => $titles,
            'preferred_job_types' => $types,
            'preferred_work_mode' => $workMode,
            'preferred_locations' => $locations,
            'minimum_acceptable_salary' => $minSalary,
            'open_to_relocation' => $relocate
        ];
        $candidate->fill(['preferences_data' => json_encode($payload)]);
        $candidate->save();
    }

    private function saveAutoApply(Candidate $candidate, array $data): void
    {
        if (!$candidate->isPremium()) {
            return;
        }
        $enabled = $data['auto_apply_enabled'] ?? 0;
        $candidate->fill([
            'auto_apply_enabled' => (in_array(strtolower((string)$enabled), ['1','true','yes','on']) || (int)$enabled === 1) ? 1 : 0,
        ]);
        $candidate->save();
    }

    /**
     * Upload file (profile picture, resume, video)
     */
    public function uploadFile(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Check premium status for video uploads
        $fileType = $request->get('type') ?? 'profile_picture';
        if ($fileType === 'video' && !$candidate->isPremium()) {
            $response->json(['error' => 'Video profile is a premium feature. Please upgrade to premium.'], 403);
            return;
        }

        $file = $request->file('file');

        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'File upload failed'], 400);
            return;
        }

        // Validate file size for videos (50MB max)
        if ($fileType === 'video' && isset($file['size']) && $file['size'] > 50 * 1024 * 1024) {
            $response->json(['error' => 'Video file size must be less than 50MB'], 400);
            return;
        }

        // Validate video file types
        if ($fileType === 'video') {
            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
            $fileMimeType = $file['type'] ?? '';
            if (!in_array($fileMimeType, $allowedTypes) && !preg_match('/^video\//', $fileMimeType)) {
                $response->json(['error' => 'Invalid video file type. Supported formats: MP4, WebM, OGG'], 400);
                return;
            }
        }

        try {
            $storage = new \App\Core\Storage();
            $uploadDir = 'candidates/' . $candidate->id;
            
            // Delete old video if replacing
            if ($fileType === 'video' && !empty($candidate->attributes['video_intro_url'])) {
                try {
                    $oldPath = parse_url($candidate->attributes['video_intro_url'], PHP_URL_PATH);
                    if ($oldPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPath)) {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . $oldPath);
                    }
                } catch (\Exception $e) {
                    // Ignore errors when deleting old file
                    error_log("Error deleting old video: " . $e->getMessage());
                }
            }
            
            $filePath = $storage->store($file, $uploadDir);
            $fileUrl = $storage->url($filePath);

            // Update candidate record
            switch ($fileType) {
                case 'profile_picture':
                    $candidate->fill(['profile_picture' => $fileUrl]);
                    break;
                case 'resume':
                    $candidate->fill(['resume_url' => $fileUrl]);
                    break;
                case 'video':
                    $candidate->fill([
                        'video_intro_url' => $fileUrl,
                        'video_intro_type' => 'upload'
                    ]);
                    break;
            }
            $candidate->save();
            $candidate->updateProfileStrength();

            $response->json([
                'success' => true,
                'url' => $fileUrl,
                'message' => 'File uploaded successfully',
                'profile_strength' => $candidate->attributes['profile_strength'] ?? 0
            ]);
        } catch (\Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            $response->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete video profile
     */
    public function help(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        $response->view('candidate/help', [
            'title' => 'Help & Support',
            'candidate' => $candidate
        ]);
    }

    public function privacy(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        $response->view('candidate/privacy', [
            'title' => 'Privacy Policy',
            'candidate' => $candidate
        ]);
    }

    public function terms(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        $response->view('candidate/terms', [
            'title' => 'Terms of Service',
            'candidate' => $candidate
        ]);
    }

    public function deleteVideo(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Check premium status
        if (!$candidate->isPremium()) {
            $response->json(['error' => 'Video profile is a premium feature'], 403);
            return;
        }

        try {
            // Delete video file from storage
            if (!empty($candidate->attributes['video_intro_url'])) {
                try {
                    $videoPath = parse_url($candidate->attributes['video_intro_url'], PHP_URL_PATH);
                    if ($videoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $videoPath)) {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . $videoPath);
                    }
                } catch (\Exception $e) {
                    error_log("Error deleting video file: " . $e->getMessage());
                }
            }

            // Update candidate record
            $candidate->fill([
                'video_intro_url' => null,
                'video_intro_type' => null
            ]);
            $candidate->save();
            $candidate->updateProfileStrength();

            $response->json([
                'success' => true,
                'message' => 'Video deleted successfully',
                'profile_strength' => $candidate->attributes['profile_strength'] ?? 0
            ]);
        } catch (\Exception $e) {
            error_log("Video delete error: " . $e->getMessage());
            $response->json(['error' => 'Failed to delete video: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods for dashboard
    private function getRecommendedJobs(Candidate $candidate): array
    {
        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            $candidateId = 0; // Use 0 for queries if no candidate ID
        }

        $candidateSkills = [];
        if (!empty($candidate->attributes['skills_data'])) {
            $candidateSkills = json_decode($candidate->attributes['skills_data'], true) ?? [];
        }
        $skillIds = array_map(fn($s) => $s['skill_id'] ?? null, $candidateSkills);
        $skillIds = array_filter($skillIds); // Remove null values
        $skillNames = array_map(fn($s) => strtolower(trim($s['name'] ?? '')), $candidateSkills);
        $skillNames = array_filter($skillNames);
        $hasItProfile = false;
        foreach ($skillNames as $n) {
            if (preg_match('/(developer|software|web|html|css|javascript|node|react|php|python|java|\.net|c\#)/i', $n)) {
                $hasItProfile = true;
                break;
            }
        }
        if (!$hasItProfile && !empty($candidate->attributes['experience_data'])) {
            $exp = json_decode($candidate->attributes['experience_data'], true) ?? [];
            foreach ($exp as $e) {
                $jt = strtolower($e['job_title'] ?? '');
                if ($jt && preg_match('/(developer|software|web|engineer)/i', $jt)) {
                    $hasItProfile = true;
                    break;
                }
            }
        }
        
        $jobs = [];
        $db = \App\Core\Database::getInstance();

        if (!empty($skillIds)) {
            $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
            $sql = "SELECT DISTINCT j.*, e.company_name 
                    FROM jobs j
                    INNER JOIN employers e ON j.employer_id = e.id
                    INNER JOIN job_skills js ON j.id = js.job_id
                    WHERE j.status = 'published' 
                    AND j.slug IS NOT NULL AND j.slug != ''
                    AND js.skill_id IN ($placeholders)
                    AND LOWER(j.title) NOT LIKE '%driver%'
                    AND LOWER(j.title) NOT LIKE '%delivery%'
                    AND LOWER(j.title) NOT LIKE '%driving%'
                    AND LOWER(j.title) NOT LIKE '%3 wheeler%'
                    AND LOWER(j.title) NOT LIKE '%truck%'
                    ORDER BY j.created_at DESC
                    LIMIT 20";
            $results = $db->fetchAll($sql, $skillIds);
            foreach ($results as $jobData) {
                $matchScore = $this->calculateJobMatch($candidate, $jobData);
                if ($matchScore < 25) {
                    continue;
                }
                $jobData['match_score'] = $matchScore;
                $jobData['is_bookmarked'] = $this->isBookmarked($candidateId, $jobData['id'] ?? 0);
                $jobs[] = $jobData;
            }
        } else {
            $keywords = [];
            if (!empty($candidate->attributes['experience_data'])) {
                $exp = json_decode($candidate->attributes['experience_data'], true) ?? [];
                foreach ($exp as $e) {
                    $jt = strtolower($e['job_title'] ?? '');
                    if ($jt) {
                        foreach (['developer','software','web','engineer','frontend','backend','full stack'] as $kw) {
                            if (str_contains($jt, $kw)) $keywords[$kw] = true;
                        }
                    }
                }
            }
            foreach ($skillNames as $n) {
                foreach (['html','css','javascript','node','react','php','python','java','.net','c#'] as $kw) {
                    if (str_contains($n, $kw)) $keywords[$kw] = true;
                }
            }
            $kwList = array_keys($keywords);
            if (!empty($kwList)) {
                $likeParts = [];
                $params = [];
                foreach ($kwList as $kw) {
                    $likeParts[] = "LOWER(j.title) LIKE ?";
                    $params[] = '%' . $kw . '%';
                }
                $whereKw = implode(' OR ', $likeParts);
                $sql = "SELECT j.*, e.company_name 
                        FROM jobs j
                        LEFT JOIN employers e ON j.employer_id = e.id
                        WHERE j.status = 'published'
                        AND j.slug IS NOT NULL AND j.slug != ''
                        AND ($whereKw)
                        AND LOWER(j.title) NOT LIKE '%driver%'
                        AND LOWER(j.title) NOT LIKE '%delivery%'
                        AND LOWER(j.title) NOT LIKE '%driving%'
                        AND LOWER(j.title) NOT LIKE '%3 wheeler%'
                        AND LOWER(j.title) NOT LIKE '%truck%'
                        ORDER BY j.created_at DESC
                        LIMIT 20";
                $results = $db->fetchAll($sql, $params);
                foreach ($results as $jobData) {
                    $matchScore = $this->calculateJobMatch($candidate, $jobData);
                    $jobData['match_score'] = $matchScore;
                    if ($hasItProfile && preg_match('/driver|delivery|driving|3 wheeler|truck/i', $jobData['title'] ?? '')) {
                        continue;
                    }
                    $jobData['is_bookmarked'] = $this->isBookmarked($candidateId, $jobData['id'] ?? 0);
                    $jobs[] = $jobData;
                }
            }
        }

        return $jobs;
    }

    private function calculateJobMatch(Candidate $candidate, array $jobData): int
    {
        $score = 0;
        $maxScore = 100;
        
        // Skills match (40 points) - from JSON column
        $candidateSkills = [];
        if (!empty($candidate->attributes['skills_data'])) {
            $candidateSkills = json_decode($candidate->attributes['skills_data'], true) ?? [];
        }
        $candidateSkillIds = array_map(fn($s) => $s['skill_id'] ?? null, $candidateSkills);
        $candidateSkillIds = array_filter($candidateSkillIds); // Remove null values
        
        $jobSkills = \App\Models\Job::find($jobData['id'])->skills();
        $jobSkillIds = array_map(fn($s) => $s['id'], $jobSkills);
        
        if (!empty($jobSkillIds)) {
            $matchedSkills = count(array_intersect($candidateSkillIds, $jobSkillIds));
            $score += ($matchedSkills / count($jobSkillIds)) * 40;
        }
        
        // Salary match (30 points)
        $expectedMin = $candidate->attributes['expected_salary_min'] ?? 0;
        $expectedMax = $candidate->attributes['expected_salary_max'] ?? 0;
        $pref = [];
        if (!empty($candidate->attributes['preferences_data'])) {
            $pref = json_decode($candidate->attributes['preferences_data'], true) ?? [];
        }
        $minAcceptable = (int)($pref['minimum_acceptable_salary'] ?? 0);
        $jobMin = $jobData['salary_min'] ?? 0;
        $jobMax = $jobData['salary_max'] ?? 0;
        
        $salaryFloor = $minAcceptable > 0 ? $minAcceptable : $expectedMin;
        if ($salaryFloor > 0 && $jobMax > 0) {
            if ($salaryFloor <= $jobMax && $expectedMax >= $jobMin) {
                $score += 30;
            } elseif ($salaryFloor <= $jobMax * 1.2) {
                $score += 15;
            }
        }
        
        // Location match (20 points)
        $preferredLocation = strtolower($candidate->attributes['preferred_job_location'] ?? '');
        $jobLocation = strtolower($jobData['locations'] ?? ($jobData['location'] ?? ''));
        $preferredLocations = [];
        if (!empty($pref['preferred_locations']) && is_array($pref['preferred_locations'])) {
            $preferredLocations = array_map(function($v){ return strtolower(trim((string)$v)); }, $pref['preferred_locations']);
            $preferredLocations = array_filter($preferredLocations);
        }
        $locationMatched = false;
        if (!empty($preferredLocations)) {
            foreach ($preferredLocations as $loc) {
                if ($loc !== '' && $jobLocation !== '' && (strpos($jobLocation, $loc) !== false || strpos($loc, $jobLocation) !== false)) {
                    $locationMatched = true;
                    break;
                }
            }
        } elseif (!empty($preferredLocation) && !empty($jobLocation)) {
            if (strpos($jobLocation, $preferredLocation) !== false || strpos($preferredLocation, $jobLocation) !== false) {
                $locationMatched = true;
            }
        }
        if ($locationMatched) {
            $score += 20;
        }

        // Work mode and job type match (adjust score by up to 10 points)
        $workMode = strtolower((string)($pref['preferred_work_mode'] ?? ''));
        $jobRemote = (int)($jobData['is_remote'] ?? 0) === 1;
        $jobType = strtolower((string)($jobData['employment_type'] ?? ''));
        $preferredTypes = [];
        if (!empty($pref['preferred_job_types']) && is_array($pref['preferred_job_types'])) {
            $preferredTypes = array_map(function($v){ return strtolower(trim((string)$v)); }, $pref['preferred_job_types']);
            $preferredTypes = array_filter($preferredTypes);
        }
        $workModeMatch = false;
        if ($workMode !== '') {
            if ($workMode === 'remote' && $jobRemote) $workModeMatch = true;
            if ($workMode === 'office' && !$jobRemote) $workModeMatch = true;
            if ($workMode === 'hybrid') $workModeMatch = true;
        }
        $typeMatch = false;
        if (!empty($preferredTypes)) {
            foreach ($preferredTypes as $t) {
                if ($t !== '' && $jobType !== '' && strpos($jobType, $t) !== false) {
                    $typeMatch = true;
                    break;
                }
            }
        }
        if ($workModeMatch) $score += 5;
        if ($typeMatch) $score += 5;
        
        // Experience match (10 points) - simplified
        $score += 10; // Default points
        
        return min((int)round($score), 100);
    }

    private function getBookmarkedJobs(Candidate $candidate): array
    {
        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            return [];
        }
        
        $bookmarks = JobBookmark::where('candidate_id', '=', $candidateId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();
        
        $jobs = [];
        foreach ($bookmarks as $bookmark) {
            $job = \App\Models\Job::find($bookmark->attributes['job_id']);
            if ($job) {
                $jobData = $job->attributes;
                $employer = $job->employer();
                $jobData['company_name'] = $employer ? $employer->attributes['company_name'] : '';
                $jobs[] = $jobData;
            }
        }
        
        return $jobs;
    }

    private function getRecentViews(Candidate $candidate): array
    {
        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            return [];
        }
        
        $views = JobView::where('candidate_id', '=', $candidateId)
            ->orderBy('viewed_at', 'DESC')
            ->limit(5)
            ->get();
        
        $jobs = [];
        foreach ($views as $view) {
            $job = \App\Models\Job::find($view->attributes['job_id']);
            if ($job) {
                $jobData = $job->attributes;
                $employer = $job->employer();
                $jobData['company_name'] = $employer ? $employer->attributes['company_name'] : '';
                $jobs[] = $jobData;
            }
        }
        
        return $jobs;
    }

    private function getApplications(Candidate $candidate): array
    {
        // Use session user_id for reliability (same as JobController::apply)
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            // Fallback to candidate user_id
            $userId = $candidate->attributes['user_id'] ?? null;
        }
        
        if (!$userId) {
            return [];
        }
        
        $applications = \App\Models\Application::where('candidate_user_id', '=', $userId)
            ->orderBy('applied_at', 'DESC')
            ->limit(10)
            ->get();
        
        $result = [];
        $statusLabels = [
            'applied' => 'Applied',
            'screening' => 'Under Review',
            'shortlisted' => 'Shortlisted',
            'interview' => 'Interview Scheduled',
            'offer' => 'Offer Received',
            'hired' => 'Hired',
            'rejected' => 'Rejected'
        ];
        
        $db = \App\Core\Database::getInstance();
        
        foreach ($applications as $app) {
            $job = $app->job();
            $appData = $app->attributes;
            $status = strtolower($app->attributes['status'] ?? 'applied');
            $appData['status'] = $status; // Ensure status is lowercase for consistent filtering
            $appData['job_title'] = $job ? ($job->attributes['title'] ?? 'Unknown') : 'Unknown';
            $appData['job_id'] = $app->attributes['job_id'] ?? null;
            $appData['job_slug'] = $job ? ($job->attributes['slug'] ?? $job->slug ?? null) : null;
            $employer = $job ? $job->employer() : null;
            $appData['company_name'] = $employer ? ($employer->attributes['company_name'] ?? '') : '';
            $appData['status_label'] = $statusLabels[$status] ?? ucfirst($status);
            $appliedAt = $app->attributes['applied_at'] ?? $app->attributes['created_at'] ?? date('Y-m-d H:i:s');
            $appData['applied_at'] = date('M d, Y', strtotime($appliedAt));
            
            // Fetch interview details if status is 'interview'
            if ($status === 'interview') {
                $interviewSql = "SELECT * FROM interviews 
                                WHERE application_id = :application_id 
                                AND status IN ('scheduled', 'rescheduled')
                                ORDER BY scheduled_start DESC 
                                LIMIT 1";
                $interview = $db->fetchOne($interviewSql, ['application_id' => $app->attributes['id']]);
                
                if ($interview) {
                    $appData['interview'] = [
                        'id' => $interview['id'],
                        'type' => $interview['interview_type'],
                        'scheduled_start' => $interview['scheduled_start'],
                        'scheduled_end' => $interview['scheduled_end'],
                        'date' => date('M d, Y', strtotime($interview['scheduled_start'])),
                        'start_time' => date('h:i A', strtotime($interview['scheduled_start'])),
                        'end_time' => date('h:i A', strtotime($interview['scheduled_end'])),
                        'location' => $interview['location'],
                        'meeting_link' => $interview['meeting_link'],
                        'timezone' => $interview['timezone'] ?? 'Asia/Kolkata',
                        'status' => $interview['status']
                    ];
                }
            }
            
            $result[] = $appData;
        }
        
        return $result;
    }

    private function isBookmarked(int $candidateId, int $jobId): bool
    {
        $bookmark = JobBookmark::where('candidate_id', '=', $candidateId)
            ->where('job_id', '=', $jobId)
            ->first();
        return $bookmark !== null;
    }

    /**
     * Get unread counts for messages and notifications
     */
    private function getUnreadCounts(Candidate $candidate): array
    {
        $userId = $candidate->attributes['user_id'] ?? null;
        if (!$userId) {
            return ['messages' => 0, 'notifications' => 0];
        }

        $db = \App\Core\Database::getInstance();
        
        // Get unread messages count
        $messagesSql = "SELECT SUM(unread_candidate) as total FROM conversations WHERE candidate_user_id = :user_id";
        $messagesResult = $db->fetchOne($messagesSql, ['user_id' => $userId]);
        $unreadMessages = (int)($messagesResult['total'] ?? 0);

        // Get unread notifications count
        $notificationsSql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $notificationsResult = $db->fetchOne($notificationsSql, ['user_id' => $userId]);
        $unreadNotifications = (int)($notificationsResult['total'] ?? 0);

        return [
            'messages' => $unreadMessages,
            'notifications' => $unreadNotifications
        ];
    }
}
