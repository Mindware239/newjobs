<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Candidate;
use App\Models\User;
use App\Models\ImportLog;
use App\Workers\ImportCandidateWorker;
use App\Services\NotificationService;
use App\Core\RedisClient;

class CandidatesController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $filter = $request->get('filter', ''); // suspicious, blocked, etc.
        $location = $request->get('location', '');
        $skill = strtolower(trim((string)$request->get('skill', '')));
        $category = strtolower(trim((string)$request->get('category', '')));
        $source = $request->get('source', '');

        // Filter by user status; candidates table does not have a status column
        $where = ["u.status != 'deleted'"];
        $params = [];
        $hasQualityScores = false;
        try {
            $db->query("SELECT 1 FROM candidate_quality_scores LIMIT 1");
            $hasQualityScores = true;
        } catch (\Exception $e) {
            $hasQualityScores = false;
        }

        if ($search) {
            $where[] = "(c.full_name LIKE :search1 OR u.email LIKE :search2)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }

        if ($status !== 'all') {
            if ($status === 'not_verified') {
                $where[] = "COALESCE(u.is_email_verified, 0) = 0";
            } else {
                $where[] = "u.status = :status";
                $params['status'] = $status;
            }
        }

        if ($filter === 'suspicious' && $hasQualityScores) {
            $where[] = "EXISTS (
                SELECT 1 
                FROM candidate_quality_scores cqs 
                INNER JOIN applications a ON a.id = cqs.application_id 
                WHERE a.candidate_user_id = c.user_id 
                AND cqs.fraud_score > 70
            )";
        } elseif ($filter === 'blocked') {
            $where[] = "u.status = 'blocked'";
        }

        if (!empty($location)) {
            $where[] = "(c.city LIKE :loc1 OR c.state LIKE :loc2 OR c.country LIKE :loc3 OR c.preferred_job_location LIKE :loc4)";
            $params['loc1'] = "%{$location}%";
            $params['loc2'] = "%{$location}%";
            $params['loc3'] = "%{$location}%";
            $params['loc4'] = "%{$location}%";
        }

        if (!empty($skill)) {
            $where[] = "EXISTS (
                SELECT 1 
                FROM candidate_skills cs 
                INNER JOIN skills s ON s.id = cs.skill_id 
                WHERE cs.candidate_id = c.id 
                  AND LOWER(s.name) LIKE :skill_name
            )";
            $params['skill_name'] = "%{$skill}%";
        }

        if (!empty($category)) {
            $where[] = "EXISTS (
                SELECT 1 
                FROM applications a 
                INNER JOIN jobs j ON j.id = a.job_id 
                WHERE a.candidate_user_id = c.user_id 
                AND LOWER(j.category) = :cat
            )";
            $params['cat'] = $category;
        }

        if (!empty($source)) {
            $where[] = "c.source = :source";
            $params['source'] = $source;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM candidates c
             INNER JOIN users u ON u.id = c.user_id
             {$whereClause}",
            $params
        )['count'] ?? 0);

        // Get candidates; use overall_score from candidate_quality_scores as quality metric
        $qualitySelect = $hasQualityScores
            ? "(SELECT MAX(cqs.overall_score) FROM candidate_quality_scores cqs
                        INNER JOIN applications a2 ON a2.id = cqs.application_id
                        WHERE a2.candidate_user_id = c.user_id) as max_quality_score"
            : "NULL as max_quality_score";
        $candidates = $db->fetchAll(
            "SELECT c.*, u.email, u.status as user_status, u.last_login, COALESCE(u.is_email_verified, 0) as email_verified,
                    (SELECT COUNT(*) FROM applications a WHERE a.candidate_user_id = c.user_id) as applications_count,
                    (SELECT COUNT(*) FROM job_bookmarks jb WHERE jb.candidate_id = c.id) as saved_jobs_count,
                    {$qualitySelect}
             FROM candidates c
             INNER JOIN users u ON u.id = c.user_id
             {$whereClause}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        $categoriesRows = $db->fetchAll("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = array_values(array_filter(array_map(fn($r) => $r['category'] ?? '', $categoriesRows)));

        // Get Statistics
        $stats = [
            'total' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE u.status != 'deleted'")['count'] ?? 0,
            'admin' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE u.status != 'deleted' AND c.source = 'admin_manual'")['count'] ?? 0,
            'website' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE u.status != 'deleted' AND (c.source IS NULL OR c.source IN ('website', 'registration'))")['count'] ?? 0,
            'excel' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE u.status != 'deleted' AND c.source = 'excel'")['count'] ?? 0
        ];

        $response->view('admin/candidates/index', [
            'title' => 'Manage Candidates',
            'candidates' => $candidates,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'filter' => $filter,
                'location' => $location,
                'skill' => $skill,
                'category' => $category,
                'source' => $source
            ],
            'categories' => $categories,
            'stats' => $stats,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $candidate = $db->fetchOne(
            "SELECT c.*, u.email, u.phone, u.status as user_status, u.last_login, u.created_at as user_created_at
             FROM candidates c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.id = :id",
            ['id' => $id]
        );

        if (!$candidate) {
            $response->redirect('/admin/candidates');
            return;
        }

        // Get applications
        $applications = $db->fetchAll(
            "SELECT a.*, j.title as job_title, j.slug as job_slug
             FROM applications a
             INNER JOIN jobs j ON j.id = a.job_id
             WHERE a.candidate_user_id = :user_id
             ORDER BY a.applied_at DESC",
            ['user_id' => $candidate['user_id']]
        );

        // Get saved jobs
        $savedJobs = $db->fetchAll(
            "SELECT j.*, jb.created_at as saved_at
             FROM job_bookmarks jb
             INNER JOIN jobs j ON j.id = jb.job_id
             WHERE jb.candidate_id = :candidate_id
             ORDER BY jb.created_at DESC",
            ['candidate_id' => $id]
        );

        $skills = [];
        if (!empty($candidate['skills_data'])) {
            $skills = json_decode((string)$candidate['skills_data'], true) ?? [];
        }

        $education = [];
        if (!empty($candidate['education_data'])) {
            $education = json_decode((string)$candidate['education_data'], true) ?? [];
        }

        $experience = [];
        if (!empty($candidate['experience_data'])) {
            $experience = json_decode((string)$candidate['experience_data'], true) ?? [];
        }

        $qualityScores = $db->fetchAll(
            "SELECT cqs.* 
             FROM candidate_quality_scores cqs
             INNER JOIN applications a ON a.id = cqs.application_id
             WHERE a.candidate_user_id = :user_id
             ORDER BY cqs.calculated_at DESC, cqs.updated_at DESC
             LIMIT 10",
            ['user_id' => $candidate['user_id']]
        );

        $loginHistory = $db->fetchAll(
            "SELECT * FROM login_history WHERE user_id = :user_id ORDER BY logged_in_at DESC LIMIT 20",
            ['user_id' => $candidate['user_id']]
        );

        $nearbyJobs = [];
        $location = trim(implode(' ', array_filter([
            (string)($candidate['city'] ?? ''),
            (string)($candidate['state'] ?? ''),
            (string)($candidate['country'] ?? '')
        ])));
        if ($location !== '') {
            $nearbyJobs = $db->fetchAll(
                "SELECT DISTINCT j.*, e.company_name
                 FROM jobs j
                 LEFT JOIN employers e ON e.id = j.employer_id
                 LEFT JOIN job_locations jl ON jl.job_id = j.id
                 LEFT JOIN cities c ON jl.city_id = c.id
                 LEFT JOIN states s ON jl.state_id = s.id
                 LEFT JOIN countries cnt ON jl.country_id = cnt.id
                 WHERE j.status = 'published'
                   AND (
                       j.locations LIKE ?
                       OR c.name LIKE ?
                       OR s.name LIKE ?
                       OR cnt.name LIKE ?
                   )
                 ORDER BY j.created_at DESC
                 LIMIT 10",
                ['%' . $location . '%', '%' . $location . '%', '%' . $location . '%', '%' . $location . '%']
            );
        }

        $activeEmployers = $db->fetchAll(
            "SELECT DISTINCT e.id, e.company_name
             FROM employers e
             INNER JOIN employer_subscriptions es ON es.employer_id = e.id
             WHERE es.status IN ('active','trial')
             ORDER BY e.company_name ASC
             LIMIT 100"
        );

        if (empty($candidate['phone']) && !empty($candidate['mobile'])) {
            $candidate['phone'] = $candidate['mobile'];
        }

        $response->view('admin/candidates/show', [
            'title' => 'Candidate Details - ' . ($candidate['full_name'] ?? 'Unknown'),
            'candidate' => $candidate,
            'applications' => $applications,
            'savedJobs' => $savedJobs,
            'skills' => $skills,
            'education' => $education,
            'experience' => $experience,
            'qualityScores' => $qualityScores,
            'loginHistory' => $loginHistory,
            'nearbyJobs' => $nearbyJobs,
            'activeEmployers' => $activeEmployers,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function block(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $candidate = Candidate::find($id);

        if ($candidate) {
            $user = User::find($candidate->user_id);
            if ($user) {
                $user->status = 'blocked';
                $user->save();

                $this->logAction('block_candidate', ['candidate_id' => $id]);
            }
        }

        $response->redirect('/admin/candidates/' . $id);
    }

    public function unblock(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $candidate = Candidate::find($id);

        if ($candidate) {
            $user = User::find($candidate->user_id);
            if ($user) {
                $user->status = 'active';
                $user->save();

                $this->logAction('unblock_candidate', ['candidate_id' => $id]);
            }
        }

        $response->redirect('/admin/candidates/' . $id);
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $candidate = Candidate::find($id);

        if ($candidate) {
            $user = User::find($candidate->user_id);
            if ($user) {
                $user->status = 'deleted';
                $user->save();

                $this->logAction('delete_candidate', ['candidate_id' => $id]);
            }
        }

        $response->redirect('/admin/candidates');
    }

    public function enablePremium(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $days = (int)$request->post('days', 30);
        $candidate = Candidate::find($id);
        if ($candidate) {
            $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
            $candidate->fill([
                'is_premium' => 1,
                'premium_expires_at' => $expires
            ]);
            $candidate->save();
            $this->logAction('enable_premium', ['candidate_id' => $id, 'days' => $days]);
        }
        $response->redirect('/admin/candidates/' . $id);
    }

    public function disablePremium(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $candidate = Candidate::find($id);
        if ($candidate) {
            $candidate->fill([
                'is_premium' => 0,
                'premium_expires_at' => null
            ]);
            $candidate->save();
            $this->logAction('disable_premium', ['candidate_id' => $id]);
        }
        $response->redirect('/admin/candidates/' . $id);
    }

    public function extendPremium(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $days = max(1, (int)$request->post('days', 7));
        $candidate = Candidate::find($id);
        if ($candidate) {
            $current = $candidate->attributes['premium_expires_at'] ?? null;
            $base = $current && strtotime((string)$current) > 0 ? (string)$current : date('Y-m-d H:i:s');
            $newExpiry = date('Y-m-d H:i:s', strtotime($base . " +{$days} days"));
            $candidate->fill([
                'is_premium' => 1,
                'premium_expires_at' => $newExpiry
            ]);
            $candidate->save();
            $this->logAction('extend_premium', ['candidate_id' => $id, 'days' => $days]);
        }
        $response->redirect('/admin/candidates/' . $id);
    }

    public function reducePremium(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $days = max(1, (int)$request->post('days', 7));
        $candidate = Candidate::find($id);
        if ($candidate) {
            $current = $candidate->attributes['premium_expires_at'] ?? null;
            $base = $current && strtotime((string)$current) > 0 ? (string)$current : date('Y-m-d H:i:s');
            $newExpiry = date('Y-m-d H:i:s', strtotime($base . " -{$days} days"));
            $isPremium = strtotime($newExpiry) > time() ? 1 : 0;
            $candidate->fill([
                'is_premium' => $isPremium,
                'premium_expires_at' => $isPremium ? $newExpiry : null
            ]);
            $candidate->save();
            $this->logAction('reduce_premium', ['candidate_id' => $id, 'days' => $days]);
        }
        $response->redirect('/admin/candidates/' . $id);
    }

    public function suggestToEmployer(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $candidateId = (int)$request->param('id');
        $employerId = (int)$request->post('employer_id');
        $jobIdPost = $request->post('job_id');
        $jobId = is_null($jobIdPost) || $jobIdPost === '' ? null : (int)$jobIdPost;

        if ($candidateId > 0 && $employerId > 0) {
            \App\Models\CandidateInterest::recordInterest($candidateId, $employerId, 'high_interest', $jobId);
            $this->logAction('suggest_candidate', [
                'candidate_id' => $candidateId,
                'employer_id' => $employerId,
                'job_id' => $jobId
            ]);
        }
        $response->redirect('/admin/candidates/' . $candidateId);
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    private function logAction(string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :old_value, :new_value, :ip_address, NOW())",
                [
                    'user_id' => $this->currentUser->id,
                    'action' => $action,
                    'entity_type' => 'candidate',
                    'entity_id' => $data['candidate_id'] ?? null,
                    'old_value' => json_encode($data),
                    'new_value' => json_encode(['status' => 'changed']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function add(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        if ($request->isMethod('POST')) {
            $data = $request->all();
            
            // Validation
            if (empty($data['email']) || empty($data['name'])) {
                $response->json(['error' => 'Name and Email are required'], 400);
                return;
            }

            $db = Database::getInstance();
            
            // Check if email exists
            $existing = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $data['email']]);
            if ($existing) {
                $response->json(['error' => 'Email already exists'], 400);
                return;
            }

            try {
                $db->beginTransaction();

                // Create User
                $user = new User();
                $user->fill([
                    'email' => $data['email'],
                    'role' => 'candidate',
                    'status' => (!empty($data['status']) && in_array($data['status'], ['active','blocked','pending'])) ? $data['status'] : 'pending',
                    'is_email_verified' => 0,
                    'phone' => $data['phone'] ?? null,
                    'verification_token' => bin2hex(random_bytes(32)),
                    'verification_expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
                ]);
                $user->save();
                
                // Get ID from object or DB
                $userId = $user->id;
                if (!$userId) {
                    $u = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $data['email']]);
                    $userId = (int)($u['id'] ?? 0);
                }

                if ($userId <= 0) {
                    throw new \Exception('User creation failed - could not retrieve ID');
                }

                // Create Candidate
                $candidate = new Candidate();
                $candidate->fill([
                    'user_id' => $userId,
                    'full_name' => $data['name'],
                    'mobile' => $data['phone'] ?? null,
                    'city' => $data['location'] ?? null,
                    'created_by' => 'admin',
                    'source' => $data['source'] ?? 'walk_in',
                    'profile_status' => 'inactive',
                    'visibility' => 'private',
                    'profile_strength' => 0,
                    'is_profile_complete' => 0
                ]);
                
                if (!empty($data['skills'])) {
                    $skills = array_map('trim', explode(',', $data['skills']));
                    $candidate->fill(['skills_data' => json_encode($skills)]);
                }
                
                $candidate->save();
                $candidateId = $candidate->id ?? null;
                if (!$candidateId) {
                    $cRow = $db->fetchOne("SELECT id FROM candidates WHERE user_id = :uid", ['uid' => $userId]);
                    $candidateId = (int)($cRow['id'] ?? 0);
                }

                $db->commit();

                $sendEmail = isset($data['send_email']) ? filter_var((string)$data['send_email'], FILTER_VALIDATE_BOOLEAN) : true;
                if ($sendEmail) {
                    $appUrl = $_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'http://localhost:8000');
                    $tokenVal = $user->attributes['verification_token'] ?? ''; 
                    
                    // If token missing in attributes, fetch from DB
                    if (empty($tokenVal)) {
                         $uData = $db->fetchOne("SELECT verification_token FROM users WHERE id = :id", ['id' => $userId]);
                         $tokenVal = $uData['verification_token'] ?? '';
                    }

                    $verifyLink = rtrim($appUrl, '/') . '/verify-account?token=' . $tokenVal;
                    $resetLink = rtrim($appUrl, '/') . '/reset-password';
                    try {
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
                        $redis = \App\Core\RedisClient::getInstance();
                        if ($redis->isAvailable()) {
                            $redis->set("password_reset:{$token}", [
                                'user_id' => $userId,
                                'email' => $data['email'],
                                'expires_at' => $expiresAt
                            ], 3600);
                        }
                        $db->query(
                            "DELETE FROM password_resets WHERE user_id = :user_id OR expires_at < UTC_TIMESTAMP()",
                            ['user_id' => $userId]
                        );
                        $db->query(
                            "INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email, :token, :user_id, :expires_at)",
                            [
                                'email' => $data['email'],
                                'token' => $token,
                                'user_id' => $userId,
                                'expires_at' => $expiresAt
                            ]
                        );
                        
                        $resetLink .= "?token=" . $token . "&email=" . urlencode($data['email']);

                        NotificationService::sendEmail(
                            $data['email'], 
                            'Welcome to ' . ($_ENV['APP_NAME'] ?? 'Job Portal'), 
                            'candidate_invite', 
                            [
                                'candidate_name' => $data['name'], 
                                'verify_link' => $verifyLink,
                                'reset_link' => $resetLink,
                                'password' => 'Set via link'
                            ]
                        );
                    } catch (\Exception $e) {
                        error_log("Failed to send welcome email to {$data['email']}: " . $e->getMessage());
                    }
                }

                $response->json([
                    'message' => $sendEmail ? 'Candidate added. Verification email sent.' : 'Candidate added. Email not sent.',
                    'candidate_id' => (int)$candidateId,
                    'user_id' => (int)$userId
                ], 200);

            } catch (\Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $response->json(['error' => 'Failed to add candidate: ' . $e->getMessage()], 500);
            }
        } else {
            // GET request - Render View
            $db = Database::getInstance();
            $categoriesRows = $db->fetchAll("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL AND category != '' ORDER BY category");
            $categories = array_values(array_filter(array_map(fn($r) => $r['category'] ?? '', $categoriesRows)));
            
            $response->view('admin/candidates/create', [
                'title' => 'Add Candidate',
                'categories' => $categories,
                'user' => $this->currentUser
            ], 200, 'admin/layout');
        }
    }

    public function uploadResume(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = $request->get('id');
        if (empty($id)) {
            $response->json(['error' => 'Candidate ID is required'], 400);
            return;
        }

        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'No file uploaded or upload error'], 400);
            return;
        }

        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            $response->json(['error' => 'Only PDF, DOC, and DOCX files are allowed'], 400);
            return;
        }

        // Limit file size (e.g., 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $response->json(['error' => 'File size exceeds 5MB limit'], 400);
            return;
        }

        try {
            $candidate = Candidate::find((int)$id);
            if (!$candidate) {
                $response->json(['error' => 'Candidate not found'], 404);
                return;
            }

            $uploadDir = __DIR__ . '/../../../../public/storage/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = 'resume_' . $id . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $resumeUrl = '/storage/uploads/resumes/' . $filename;
                
                $db = Database::getInstance();
                $db->query(
                    "UPDATE candidates SET resume_url = :url, updated_at = NOW() WHERE id = :id",
                    ['url' => $resumeUrl, 'id' => $id]
                );

                $response->json([
                    'success' => true, 
                    'message' => 'Resume uploaded successfully',
                    'resume_url' => $resumeUrl
                ]);
            } else {
                throw new \Exception('Failed to move uploaded file');
            }

        } catch (\Exception $e) {
            error_log("Resume upload error: " . $e->getMessage());
            $response->json(['error' => 'Failed to upload resume'], 500);
        }
    }

    public function importHistory(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $db = Database::getInstance();
        
        $logs = $db->fetchAll(
            "SELECT il.*, u.email as admin_email 
             FROM import_logs il 
             LEFT JOIN users u ON il.admin_id = u.id 
             ORDER BY il.created_at DESC 
             LIMIT {$perPage} OFFSET {$offset}"
        );

        $total = $db->fetchOne("SELECT COUNT(*) as count FROM import_logs");
        $totalLogs = $total['count'] ?? 0;
        $totalPages = ceil($totalLogs / $perPage);

        $response->view('admin/candidates/import-history', [
            'title' => 'Import History',
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function import(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }
        $response->view('admin/candidates/import', ['title' => 'Import Candidates', 'user' => $this->currentUser], 200, 'admin/layout');
    }

    public function uploadImport(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'File upload failed'], 400);
            return;
        }

        $file = $_FILES['file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['csv', 'txt'])) {
            $response->json(['error' => 'Only CSV files are allowed'], 400);
            return;
        }

        $uploadDir = __DIR__ . '/../../../../public/storage/uploads/imports/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid('import_') . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Read first 20 rows for preview
            $rows = [];
            $handle = fopen($filepath, 'r');
            $header = fgetcsv($handle);
            $count = 0;
            while (($row = fgetcsv($handle)) !== false && $count < 20) {
                $rows[] = $row;
                $count++;
            }
            fclose($handle);

            $response->json([
                'success' => true,
                'filepath' => $filepath,
                'header' => $header,
                'preview' => $rows
            ]);
        } else {
            $response->json(['error' => 'Failed to save file'], 500);
        }
    }

    public function confirmImport(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $data = $request->all();
        $filepath = $data['filepath'] ?? '';
        $mapping = $data['mapping'] ?? [];
        $sendEmail = $data['send_email'] ?? false;

        if (!file_exists($filepath)) {
            $response->json(['error' => 'File not found'], 400);
            return;
        }

        $batchId = uniqid('batch_');
        
        // Create Import Log
        ImportLog::log($batchId, (int)$this->currentUser->id, basename($filepath), 0, 0, 0, 'pending');

        // Queue Job with graceful fallback to synchronous processing if queue unavailable
        $payload = [
            'batch_id' => $batchId,
            'file_path' => $filepath,
            'admin_id' => (int)$this->currentUser->id,
            'mapping' => $mapping,
            'send_email' => $sendEmail,
            'source' => 'excel'
        ];
        $redisAvailable = RedisClient::getInstance()->isAvailable();
        if ($redisAvailable) {
            ImportCandidateWorker::enqueue($payload);
            $response->json(['success' => true, 'message' => 'Import started. You can leave this page.', 'batch_id' => $batchId]);
        } else {
            $worker = new ImportCandidateWorker();
            $worker->process($payload);
            $response->json(['success' => true, 'message' => 'Import processed synchronously. Queue not available.', 'batch_id' => $batchId]);
        }

    }
}
