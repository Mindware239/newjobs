<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SocialServiceController extends BaseController
{
    /**
     * Frontend Social Services Page
     * URL: /social-services
     */
     public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $featuredJobs = [];
        try {
            $jobRows = $db->fetchAll("
                SELECT id, role_name, organization_name, job_location, location_details,
                       min_pay, max_pay, pay_type, website, created_at, publish_date
                FROM social_jobs
                WHERE is_deleted = 0 AND publish_status = 'published'
                ORDER BY COALESCE(publish_date, created_at) DESC
                LIMIT 5
            ");
            // Extract domain safely (handles 'http(s)://', bare domains, and junk like '1 vt.com')
            $extractHost = function ($url): string {
                $url = trim((string)$url);
                if ($url === '') return '';
                if (!preg_match('~^https?://~i', $url)) {
                    $url = 'http://' . $url;
                }
                $host = parse_url($url, PHP_URL_HOST) ?: '';
                $host = trim($host);
                // If host still looks bad, try to pick last token that looks like a domain
                if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                    // Extract tokens and pick one that has a dot and valid chars
                    $tokens = preg_split('/\s+/', trim((string)$url));
                    foreach (array_reverse($tokens) as $t) {
                        $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                        if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                            $host = $t;
                            break;
                        }
                    }
                }
                return $host;
            };
            foreach ($jobRows as $r) {
                $name = (string)($r['organization_name'] ?? '');
                $website = (string)($r['website'] ?? '');
                $host = $extractHost($website);
                $logo = $host ? ("https://logo.clearbit.com/" . $host) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f");
                $min = $r['min_pay'] ?? null;
                $max = $r['max_pay'] ?? null;
                $salary = 'Negotiable';
                $fmt = function($v) { return is_numeric($v) ? '$' . number_format((float)$v) : ''; };
                if (is_numeric($min) && is_numeric($max) && $min > 0 && $max > 0) {
                    $salary = $fmt($min) . ' - ' . $fmt($max);
                } elseif (is_numeric($min) && $min > 0) {
                    $salary = $fmt($min) . '+';
                } elseif (is_numeric($max) && $max > 0) {
                    $salary = 'Up to ' . $fmt($max);
                }
                $loc = (string)($r['job_location'] ?? '');
                if ($loc === '') {
                    $loc = (string)($r['location_details'] ?? '');
                }
                $featuredJobs[] = [
                    'id' => (int)$r['id'],
                    'title' => (string)($r['role_name'] ?? ''),
                    'company' => $name,
                    'loc' => $loc,
                    'salary' => $salary,
                    'img' => $logo,
                    'url' => '/job-details?id=' . (int)$r['id'],
                ];
            }
        } catch (\Throwable $t) {}
        $insights = [];
        try {
            $rows = $db->fetchAll("
                SELECT id, title, short_description, image, status, published_at, created_at
                FROM career_articles
                WHERE status = 'published'
                ORDER BY COALESCE(published_at, created_at) DESC
                LIMIT 5
            ");
            foreach ($rows as $r) {
                $insights[] = [
                    'id' => (int)$r['id'],
                    'title' => (string)$r['title'],
                    'desc' => (string)($r['short_description'] ?? ''),
                    'img' => (string)($r['image'] ?? ''),
                    'url' => '/hiringInsight/article?id=' . (int)$r['id'],
                ];
            }
        } catch (\Throwable $t) {}
        $featuredOrgs = [];
        try {
            $orgRows = $db->fetchAll("
                SELECT id, organization_name, website, created_at
                FROM social_organizations
                ORDER BY created_at DESC
                LIMIT 8
            ");
            // Same host extractor as above
            $extractHost = function ($url): string {
                $url = trim((string)$url);
                if ($url === '') return '';
                if (!preg_match('~^https?://~i', $url)) {
                    $url = 'http://' . $url;
                }
                $host = parse_url($url, PHP_URL_HOST) ?: '';
                $host = trim($host);
                if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                    $tokens = preg_split('/\s+/', trim((string)$url));
                    foreach (array_reverse($tokens) as $t) {
                        $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                        if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                            $host = $t;
                            break;
                        }
                    }
                }
                return $host;
            };
            foreach ($orgRows as $o) {
                $name = (string)($o['organization_name'] ?? '');
                $website = (string)($o['website'] ?? '');
                $host = $extractHost($website);
                $logo = $host ? ("https://logo.clearbit.com/" . $host) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f");
                $featuredOrgs[] = [
                    'id' => (int)($o['id'] ?? 0),
                    'name' => $name,
                    'logo' => $logo,
                    'url' => '/organizationDetails?id=' . (int)($o['id'] ?? 0),
                ];
            }
        } catch (\Throwable $t) {}
        $response->view('social-services/index', [
            'featuredJobs' => $featuredJobs,
            'insights' => $insights,
            'featuredOrgs' => $featuredOrgs,
            'base' => '/'
        ]);
    }

    /**
     * FIND A JOB PAGE
     */
public function findjob(Request $request, Response $response): void
{
    $db = Database::getInstance();

    // Prefetch organizations and their uploaded logos for mapping
    $orgMap = [];
    try {
        $orgRows = $db->fetchAll("
            SELECT employer_id, organization_name, logo_url, website
            FROM social_organizations
        ");
        foreach ($orgRows as $o) {
            $eid = (int)($o['employer_id'] ?? 0);
            if ($eid <= 0) continue;
            $orgMap[$eid][] = [
                'name' => (string)($o['organization_name'] ?? ''),
                'logo' => (string)($o['logo_url'] ?? ''),
                'website' => (string)($o['website'] ?? '')
            ];
        }
    } catch (\Throwable $t) {}

    $rows = $db->fetchAll("
        SELECT *
        FROM social_jobs
        WHERE is_deleted = 0
        ORDER BY created_at DESC
    ");

    $jobs = [];

    foreach ($rows as $row) {
        $name = (string)($row['organization_name'] ?? '');
        $website = (string)($row['website'] ?? '');
        $uploadedLogo = '';
        $eid = (int)($row['employer_id'] ?? 0);
        if ($eid > 0 && isset($orgMap[$eid])) {
            $candidates = $orgMap[$eid];
            // Try match by name
            foreach ($candidates as $c) {
                if ($name !== '' && strcasecmp($c['name'], $name) === 0 && !empty($c['logo'])) {
                    $uploadedLogo = $c['logo'];
                    break;
                }
            }
            // If not found by name, pick first available logo
            if ($uploadedLogo === '') {
                foreach ($candidates as $c) {
                    if (!empty($c['logo'])) { $uploadedLogo = $c['logo']; break; }
                }
            }
            // If website empty in job row, use org website for Clearbit fallback extraction
            if ($website === '') {
                foreach ($candidates as $c) {
                    if (!empty($c['website'])) { $website = $c['website']; break; }
                }
            }
        }
        $extractHost = function ($url): string {
            $url = trim((string)$url);
            if ($url === '') return '';
            if (!preg_match('~^https?://~i', $url)) {
                $url = 'http://' . $url;
            }
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $host = trim($host);
            if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                $tokens = preg_split('/\s+/', trim((string)$url));
                foreach (array_reverse($tokens) as $t) {
                    $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                    if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                        $host = $t;
                        break;
                    }
                }
            }
            if (stripos($host, 'www.') === 0) {
                $host = substr($host, 4);
            }
            return $host;
        };
        $host = $extractHost($website);
        if (stripos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        $logo = $uploadedLogo !== '' ? $uploadedLogo
            : ($host ? ("https://logo.clearbit.com/" . $host) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f"));
        $jobs[] = [
            'id'          => (int)$row['id'],
            'title'       => $row['role_name'] ?? '',
            'company'     => $name,
            'location'    => $row['job_location'] ?: ($row['location_details'] ?? ''),
            'type'        => $row['time_commitment'] ?? '',
            'workplace'   => $row['workplace_option'] ?? '',
            'salary_min'  => $row['min_pay'] ?: 'Negotiable',
            'salary_max'  => $row['max_pay'] ?: '',
            'description' => strip_tags($row['full_description'] ?? $row['short_description'] ?? ''),
            'posted'      => date('F d, Y', strtotime($row['created_at'])),
            'expires'     => !empty($row['publish_date'])
                                ? date('F d, Y', strtotime($row['publish_date']))
                                : 'Open',
            'education'   => $row['education_level'] ?? '',
            'experience'  => $row['experience_years'] ?? 0,
            'mission_focus' => $row['org_mission_focus'] ?? '',
            'category'    => $row['work_category'] ?? '',
            'publish_type'=> $row['publish_type'] ?? 'standard',
            'logo'        => $logo,
        ];
    }

    $response->view('social-services/find-a-job', [
        'jobs' => $jobs
    ]);
}



     public function roles(Request $request, Response $response): void
{
    $response->view('social-services/roles', []);
}
  public function createjob(Request $request, Response $response): void
  {
    $response->view('social-services/createjob',[]);

  }
  public function candidate(Request $request, Response $response): void
    {
        $response->view('social-services/candidate');
    }
    
   public function listings(Request $request, Response $response): void
    {
        $db = Database::getInstance();

        $jobs = $db->fetchAll("
            SELECT *
            FROM social_jobs
            ORDER BY created_at DESC
        ");

        $response->view('social-employer/listings', [
            'jobs' => $jobs
        ]);
    }

   
 public function subscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/subscriptions');
    }

    public function newsubscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/newsubscriptions');
    }
     public function employers(Request $request, Response $response): void
    {
        $response->view('social-services/employers');
    }
       public function pricing(Request $request, Response $response): void
    {
        $response->view('social-services/pricing');
    }
     public function cart(Request $request, Response $response): void
    {
        $response->view('social-services/cart');
    }
    public function saveCart(Request $request, Response $response): void
    {
        $payload = $request->all();
        $items = $payload['items'] ?? [];
        if (!is_array($items)) {
            $response->json(['error' => 'invalid_items'], 422);
            return;
        }
        $_SESSION['employer_cart'] = $items;
        $total = 0;
        foreach ($items as $it) {
            $qty = (int)($it['qty'] ?? 0);
            $price = (float)($it['price'] ?? 0);
            $total += $qty * $price;
        }
        $employerId = (int)($_SESSION['user_id'] ?? 0);
        $rec = new SocialSubscriptionEmploy();
        $rec->fill([
            'employer_id' => $employerId,
            'items' => json_encode($items),
            'total_amount' => number_format($total, 2, '.', ''),
            'currency' => 'USD',
            'status' => 'cart',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $rec->save();
        $response->json(['success' => true]);
    }
     public function checkout(Request $request, Response $response): void
    {
        $response->view('social-employer/checkout');
    }
      public function organisation(Request $request, Response $response): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $employerId = (int)($_SESSION['employer_id'] ?? 0);
        try {
            if ($employerId <= 0 && !empty($_SESSION['user_id'])) {
                $pdo = Database::getInstance()->getConnection();
                $model = new \App\Models\SocialJobApplication($pdo);
                $derived = (int)($model->getEmployerProfileId((int)$_SESSION['user_id']) ?? 0);
                if ($derived > 0) {
                    $_SESSION['employer_id'] = $derived;
                    $employerId = $derived;
                }
            }
        } catch (\Throwable $t) {}
        $orgs = [];
        try {
            $orgModel = new \App\Models\SocialOrganization();
            if ($employerId > 0) {
                $orgs = $orgModel->getByEmployer($employerId);
            }
            if (empty($orgs) && !empty($_SESSION['user_id'])) {
                $orgs = $orgModel->getByEmployer((int)$_SESSION['user_id']);
            }
            if (empty($orgs)) {
                $db = Database::getInstance();
                $orgs = $db->fetchAll("SELECT * FROM social_organizations ORDER BY created_at DESC");
            }
        } catch (\Throwable $t) { $orgs = []; }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $response->view('social-employer/organisation', [
            'organizations' => $orgs,
            'base' => '/',
        ]);
    }
      public function aboutus(Request $request, Response $response): void
    {
        $response->view('social-services/aboutus');
    }
      public function specials(Request $request, Response $response): void
    {
        $response->view('social-services/specials');
    }

    public function terms(Request $request, Response $response): void
    {
        $response->view('social-services/terms');
    }

    public function privacy(Request $request, Response $response): void
    {
        $response->view('social-services/privacy');
    }

    public function grievances(Request $request, Response $response): void
    {
        $response->view('social-services/grievances');
    }
    
    public function supports(Request $request, Response $response): void
    {
        $response->view('social-services/supports');
    }
        public function aboutuss(Request $request, Response $response): void
    {
        $response->view('social-services/aboutuss');
    }
    public function application (Request $request, Response $response): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Ensure employer_id in session points to social_employer_profiles; if not, derive and fix
        $employerId = (int)($_SESSION['employer_id'] ?? 0);
        try {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            if ($userId > 0) {
                $pdo = Database::getInstance()->getConnection();
                $model = new \App\Models\SocialJobApplication($pdo);
                $derived = (int)($model->getEmployerProfileId($userId) ?? 0);
                if ($derived > 0 && $employerId !== $derived) {
                    $_SESSION['employer_id'] = $derived;
                    $employerId = $derived;
                }
            }
        } catch (\Throwable $t) {
            $employerId = (int)($_SESSION['employer_id'] ?? 0);
        }
        // Debug diagnostics to help verify why results may be empty
        error_log("Social Applications: employerProfileId=" . $employerId);
        // Fetch applications for logged employer profile; include legacy jobs by email
        $apps = [];
        try {
            $pdo = Database::getInstance()->getConnection();
            $model = new \App\Models\SocialJobApplication($pdo);
            $email = '';
            if (!empty($_SESSION['user_id'])) {
                $user = \App\Models\User::find((int)$_SESSION['user_id']);
                $email = (string)($user->email ?? '');
            }
            // Attempt to repair legacy jobs with missing employer_id using notification email
            if ($employerId > 0 && $email !== '') {
                // Reassign all jobs that notify this email to the current employer profile
                $stmt = $pdo->prepare("UPDATE social_jobs SET employer_id = ? WHERE notification_emails LIKE ?");
                try { $stmt->execute([$employerId, '%' . $email . '%']); } catch (\Throwable $e) {}
            }
            // Fetch by employer_id
            $appsById = $employerId > 0 ? ($model->employerApplicants($employerId) ?? []) : [];
            // Also fetch by email for jobs not yet linked to employer profile
            $appsByEmail = $email !== '' ? ($model->employerApplicantsByEmail($email) ?? []) : [];
            error_log("Social Applications: appsById=" . count($appsById) . ", appsByEmail=" . count($appsByEmail) . ", email=" . $email);
            // Merge, dedupe by application_id
            $seen = [];
            foreach (array_merge($appsById, $appsByEmail) as $row) {
                $aid = (int)($row['application_id'] ?? 0);
                if ($aid > 0 && !isset($seen[$aid])) {
                    $apps[] = $row;
                    $seen[$aid] = true;
                }
            }
        } catch (\Throwable $t) {
            $apps = [];
        }
        error_log("Social Applications: finalCount=" . count($apps));
        $base = "/";
        $response->view('social-employer/application', [
            'applications' => $apps,
            'apps' => $apps,
            'base' => $base
        ]);
    }

    public function applicationStatus(Request $request, Response $response): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $id = (int)($request->post('id') ?? 0);
        $status = (string)($request->post('status') ?? '');
        $allowed = ['applied','reviewed','shortlisted','accepted','rejected'];
        if ($id <= 0 || !in_array($status, $allowed, true)) {
            $response->redirect('/social-employer/application?error=Invalid request');
            return;
        }
        $employerProfileId = (int)($_SESSION['employer_id'] ?? 0);
        try {
            $pdo = Database::getInstance()->getConnection();
            $model = new \App\Models\SocialJobApplication($pdo);
            // verify belongs to logged employer profile
            if (!$model->applicationBelongsToEmployer($id, $employerProfileId)) {
                $response->redirect('/social-employer/application?error=Unauthorized');
                return;
            }
            if ($model->updateStatus($id, $status)) {
                $response->redirect('/social-employer/application?success=Status updated');
            } else {
                $response->redirect('/social-employer/application?error=Update failed');
            }
        } catch (\Throwable $t) {
            error_log('Application status error: ' . $t->getMessage());
            $response->redirect('/social-employer/application?error=Server error');
        }
    }

    public function apiEmployerApplications(Request $request, Response $response): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        try {
            $pdo = Database::getInstance()->getConnection();
            $model = new \App\Models\SocialJobApplication($pdo);
            $employerProfileId = (int)($model->getEmployerProfileId($userId) ?? 0);
            if ($employerProfileId <= 0) {
                $response->json(['applications' => []]);
                return;
            }
            $rows = $model->employerApplicants($employerProfileId) ?? [];
            $response->json(['applications' => $rows]);
        } catch (\Throwable $t) {
            $response->json(['applications' => [], 'error' => 'server_error'], 500);
        }
    }
    public function newlisting(Request $request, Response $response): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $response->view('social-employer/newlisting', [
            'title' => 'Create new listing',
            'action' => '/social-employer/newlisting',
            'job' => [] // empty defaults
        ]);
    }
 public function store(Request $request, Response $response): void
    {
        $db = Database::getInstance();

        $employerProfileId = null;
        try {
            $pdo = $db->getConnection();
            $model = new \App\Models\SocialJobApplication($pdo);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $employerProfileId = $model->getEmployerProfileId($userId);
            if (!$employerProfileId && $userId > 0) {
                $profileModel = new \App\Models\EmployerProfile();
                $existing = $profileModel->findByUser($userId);
                if (!$existing) {
                    $user = \App\Models\User::find($userId);
                    $fullName = (string)($user->full_name ?? '');
                    $parts = array_values(array_filter(explode(' ', $fullName)));
                    $first = $parts[0] ?? 'Employer';
                    $last = $parts[1] ?? '';
                    $newId = $profileModel->create([
                        'user_id' => $userId,
                        'full_name' => $fullName,
                        'preferred_name' => $fullName,
                        'pronouns' => '',
                        'prefix' => '',
                        'first_name' => $first,
                        'middle_name' => '',
                        'last_name' => $last,
                        'suffix' => ''
                    ]);
                    $employerProfileId = $newId;
                } else {
                    $employerProfileId = (int)($existing['id'] ?? 0);
                }
            }
        } catch (\Throwable $e) {
            $employerProfileId = null;
        }

        if (!$employerProfileId || $employerProfileId <= 0) {
            $response->redirect('/social-employer/account?error=Create profile first');
            return;
        }

        $data = [
            'employer_id'          => $employerProfileId,
            'candidate_type'       => $request->post('candidate_type') ?? 'Employee',
            'organization_name'    => $request->post('organization_name'),
            'organization_type'    => $request->post('org_type'),
            'is_agency'            => $request->post('is_agency') ? 1 : 0,
            'website'              => $request->post('website'),
            'ein'                  => $request->post('ein'),
            'staff_count'          => $request->post('staff_count'),
            'org_mission_focus'    => $request->post('mission_focus'),
            'organization_mission' => $request->post('org_mission'),
            'organization_impact'  => $request->post('org_impact'),

            'role_name'            => $request->post('role_name'),
            'time_commitment'      => $request->post('time_commitment'),
            'time_details'         => $request->post('time_details'),
            'work_category'        => $request->post('work_category'),
            'experience_years'     => $request->post('experience_years'),
            'education_level'      => $request->post('education_level'),

            'pay_type'             => $request->post('pay_type'),
            'min_pay'              => $request->post('pay_min'),
            'max_pay'              => $request->post('pay_max'),

            'role_mission_focused'   => $request->post('role_focus'),
            'short_description'    => $request->post('short_description'),
            'full_description'     => $request->post('job_overview'),

            'workplace_option'     => $request->post('workplace_type'),
            'workplace_details'    => $request->post('workplace_details'),
            'job_location'         => $request->post('location'),
            'location_details'     => $request->post('location_details'),

            'publish_type'         => $request->post('publish_type'),
            'publish_date'         => $request->post('publish_date') ?: date('Y-m-d'),
            'apply_method'         => $request->post('apply_method'),
            'notification_emails'  => json_encode($request->post('emails') ?? []),
            'screening_questions'  => json_encode($request->post('questions') ?? []),

            'publish_status'       => 'published',
            'is_draft'             => 0,
            'is_deleted'           => 0,
            'created_at'           => date('Y-m-d H:i:s'),
            'employer_id'          => $employerProfileId
        ];

        $sql = "
            INSERT INTO social_jobs (
                candidate_type, organization_name, organization_type, is_agency,
                website, ein, staff_count,
                org_mission_focus, organization_mission, organization_impact,

                role_name, time_commitment, time_details, work_category,
                experience_years, education_level,

                pay_type, min_pay, max_pay,
                role_mission_focused, short_description, full_description,

                workplace_option, workplace_details,
                job_location, location_details,

                publish_type, publish_date,
                apply_method, notification_emails, screening_questions,
                publish_status, is_draft, is_deleted, created_at, employer_id
            ) VALUES (
                :candidate_type, :organization_name, :organization_type, :is_agency,
                :website, :ein, :staff_count,
                :org_mission_focus, :organization_mission, :organization_impact,

                :role_name, :time_commitment, :time_details, :work_category,
                :experience_years, :education_level,

                :pay_type, :min_pay, :max_pay,
                :role_mission_focused, :short_description, :full_description,

                :workplace_option, :workplace_details,
                :job_location, :location_details,

                :publish_type, :publish_date,
                :apply_method, :notification_emails, :screening_questions,
                :publish_status, :is_draft, :is_deleted, :created_at, :employer_id
            )
        ";

        try {
            $db->query($sql, $data);
            $response->redirect('/social-employer/listings?success=Job published successfully');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $response->redirect('/social-employer/listings?error=Job insert failed');
        }
    }


    public function checkoutComplete(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $employerId = (int)($_SESSION['user_id'] ?? 0);
        if ($employerId <= 0) {
            $response->redirect('/login');
            return;
        }
        $amount = (float)($request->post('amount') ?? 0);
        $plan = (string)($request->post('plan') ?? 'standard');
        try {
            $db->query("
                INSERT INTO employer_subscriptions (employer_id, plan, amount, status, started_at, expires_at, created_at)
                VALUES (:eid, :plan, :amount, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
            ", ['eid' => $employerId, 'plan' => $plan, 'amount' => $amount]);
            $subscriptionId = (int)$db->lastInsertId();
            $row = $db->fetchOne("SELECT id FROM social_subscription_employ WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 1", ['eid' => $employerId]);
            if ($row) {
                $db->query("UPDATE social_subscription_employ SET status = 'active', subscription_id = :sid, updated_at = NOW() WHERE id = :id", [
                    'sid' => $subscriptionId,
                    'id' => (int)$row['id']
                ]);
            }
        } catch (\Exception $e) {
            error_log('Subscription activate error: ' . $e->getMessage());
            $response->redirect('/social-services/cart?error=Activation failed');
            return;
        }
        $pending = $_SESSION['pending_job'] ?? null;
        unset($_SESSION['pending_job']);
        if (is_array($pending)) {
            $_POST = $pending;
            $this->store($request, $response);
            return;
        }
        $response->redirect('/social-employer/newlisting?success=Subscription activated');
    }


    public function edit(Request $request, Response $response): void
    {
        $id = (int)($request->param('id') ?? 0);
        if ($id <= 0) {
            $response->redirect('/social-employer/listings?error=Invalid job id');
            return;
        }
        $db = Database::getInstance();
        $job = $db->fetchOne("SELECT * FROM social_jobs WHERE id = :id", ['id' => $id]);
        if (!$job) {
            $response->redirect('/social-employer/listings?error=Job not found');
            return;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $response->view('social-employer/newlisting', [
            'title' => 'Edit listing',
            'action' => '/social-employer/job/' . $id . '/update',
            'job' => $job
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int)($request->param('id') ?? 0);
        if ($id <= 0) {
            $response->redirect('/social-employer/listings?error=Invalid job id');
            return;
        }
        $db = Database::getInstance();
        $payload = [
            'candidate_type'       => $request->post('candidate_type'),
            'organization_name'    => $request->post('organization_name') ?: $request->post('organization_select'),
            'organization_type'    => $request->post('org_type') ?: 'Unspecified',
            'is_agency'            => $request->post('is_agency') ? 1 : 0,
            'website'              => $request->post('website'),
            'ein'                  => $request->post('ein'),
            'staff_count'          => $request->post('staff_count'),
            'org_mission_focus'    => $request->post('mission_focus'),
            'organization_mission' => $request->post('org_mission'),
            'organization_impact'  => $request->post('org_impact'),
            'role_name'            => $request->post('role_name'),
            'time_commitment'      => $request->post('time_commitment'),
            'time_details'         => $request->post('time_details'),
            'work_category'        => $request->post('work_category'),
            'experience_years'     => $request->post('experience_years'),
            'pay_type'             => $request->post('pay_type'),
            'min_pay'              => $request->post('pay_min'),
            'max_pay'              => $request->post('pay_max'),
            'role_mission_focused' => $request->post('role_focus'),
            'short_description'    => $request->post('short_description'),
            'full_description'     => $request->post('job_overview'),
            'workplace_option'     => $request->post('workplace_type'),
            'workplace_details'    => $request->post('workplace_details'),
            'job_location'         => $request->post('location'),
            'location_details'     => $request->post('location_details'),
            'publish_type'         => $request->post('publish_type'),
            'publish_date'         => $request->post('publish_date') ?: date('Y-m-d'),
            'apply_method'         => $request->post('apply_method'),
            'notification_emails'  => json_encode($request->post('emails') ?? []),
            'screening_questions'  => json_encode($request->post('questions') ?? []),
        ];
        $sql = "UPDATE social_jobs SET
            candidate_type = :candidate_type,
            organization_name = :organization_name,
            organization_type = :organization_type,
            is_agency = :is_agency,
            website = :website,
            ein = :ein,
            staff_count = :staff_count,
            org_mission_focus = :org_mission_focus,
            organization_mission = :organization_mission,
            organization_impact = :organization_impact,
            role_name = :role_name,
            time_commitment = :time_commitment,
            time_details = :time_details,
            work_category = :work_category,
            experience_years = :experience_years,
            role_mission_focused = :role_mission_focused,
            pay_type = :pay_type,
            min_pay = :min_pay,
            max_pay = :max_pay,
            short_description = :short_description,
            full_description = :full_description,
            workplace_option = :workplace_option,
            workplace_details = :workplace_details,
            job_location = :job_location,
            location_details = :location_details,
            publish_type = :publish_type,
            publish_date = :publish_date,
            apply_method = :apply_method,
            notification_emails = :notification_emails,
            screening_questions = :screening_questions
            WHERE id = :id";
        $payload['id'] = $id;
        try {
            error_log('Update Payload: ' . print_r($payload, true));
            $stmt = $db->query($sql, $payload);
            $affected = (int)$stmt->rowCount();
            if ($affected > 0) {
                $response->redirect('/social-employer/listings?success=Listing updated&id=' . $id);
            } else {
                $response->redirect('/social-employer/listings?error=No changes or update failed&id=' . $id);
            }
        } catch (\Exception $e) {
            error_log('Update error: ' . $e->getMessage());
            $response->redirect('/social-employer/listings?error=Update error');
        }
    }

    public function delete(Request $request, Response $response): void
    {
        $id = (int)($request->param('id') ?? 0);
        if ($id <= 0) {
            $response->redirect('/social-employer/listings?error=Invalid job id');
            return;
        }
        $db = Database::getInstance();
        $db->query("DELETE FROM social_jobs WHERE id = :id", ['id' => $id]);
        $response->redirect('/social-employer/listings?success=Listing deleted');
    }

public function status(Request $request, Response $response): void
    {
        $id     = (int)$request->param('id');
        $status = $request->post('status');

        $allowed = ['draft', 'pending', 'active', 'expired'];
        if (!in_array($status, $allowed, true)) {
            $response->redirect('/social-employer/listings?error=Invalid status');
            return;
        }

        $db = Database::getInstance();
        $db->query(
            "UPDATE social_jobs SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $id]
        );

        $response->redirect('/social-employer/listings?success=Status updated');
    }

    ////candidate// ===============================
// CANDIDATE JOB ALERTS (LIST + FORM)
// ===============================
public function candidatesubscriptions(Request $request, Response $response): void
{
    $db = Database::getInstance();
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        $response->view('social-candidate/candidatesubscriptions', [
            'alerts'    => [],
            'showForm'  => false,
            'editAlert' => null
        ]);
        return;
    }

    $alerts = [];
    try {
        $alerts = $db->fetchAll(
            "SELECT * FROM candidate_job_alerts
             WHERE user_id = :uid
             ORDER BY created_at DESC",
            ['uid' => $userId]
        );
    } catch (\Throwable $t) {
        error_log("Job alerts table missing or query failed: " . $t->getMessage());
        $alerts = [];
    }

    $showForm  = false;
    $editAlert = null;

    if ($request->get('new') === '1') {
        $showForm = true;
    }

    if ($request->get('edit')) {
        $id = (int)$request->get('edit');
        try {
            $editAlert = $db->fetchOne(
                "SELECT * FROM candidate_job_alerts
                 WHERE id = :id AND user_id = :uid",
                ['id' => $id, 'uid' => $userId]
            );
        } catch (\Throwable $t) {
            $editAlert = null;
        }
        if ($editAlert) {
            $showForm = true;
        }
    }

    $response->view('social-candidate/candidatesubscriptions', [
        'alerts'    => $alerts,
        'showForm'  => $showForm,
        'editAlert' => $editAlert
    ]);
}



    public function jobdetails(Request $request, Response $response): void
    {
        $id = (int)($request->get('id') ?? 0);
        if ($id <= 0) {
            $response->redirect('/find-a-job');
            return;
        }
        $db = Database::getInstance();
        $job = $db->fetchOne("SELECT * FROM social_jobs WHERE id = :id", ['id' => $id]);
        if (!$job) {
            $response->redirect('/find-a-job');
            return;
        }
        $name = (string)($job['organization_name'] ?? '');
        $website = (string)($job['website'] ?? '');
        $eid = (int)($job['employer_id'] ?? 0);
        $uploadedLogo = '';
        try {
            if ($eid > 0) {
                $org = $db->fetchOne("SELECT organization_name, logo_url, website FROM social_organizations WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 1", ['eid' => $eid]);
                if ($org) {
                    $uploadedLogo = (string)($org['logo_url'] ?? '');
                    if ($website === '' && !empty($org['website'])) {
                        $website = (string)$org['website'];
                    }
                    if ($name === '' && !empty($org['organization_name'])) {
                        $name = (string)$org['organization_name'];
                    }
                }
            }
            if ($uploadedLogo === '' && $name !== '') {
                $orgByName = $db->fetchOne("SELECT logo_url, website FROM social_organizations WHERE organization_name = :n ORDER BY created_at DESC LIMIT 1", ['n' => $name]);
                if ($orgByName) {
                    $uploadedLogo = (string)($orgByName['logo_url'] ?? '');
                    if ($website === '' && !empty($orgByName['website'])) {
                        $website = (string)$orgByName['website'];
                    }
                }
            }
        } catch (\Throwable $t) {}
        $extractHost = function ($url): string {
            $url = trim((string)$url);
            if ($url === '') return '';
            if (!preg_match('~^https?://~i', $url)) {
                $url = 'http://' . $url;
            }
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $host = trim($host);
            if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                $tokens = preg_split('/\s+/', trim((string)$url));
                foreach (array_reverse($tokens) as $t) {
                    $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                    if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                        $host = $t;
                        break;
                    }
                }
            }
            if (stripos($host, 'www.') === 0) {
                $host = substr($host, 4);
            }
            return $host;
        };
        $host = $extractHost($website);
        $logo = $uploadedLogo !== '' ? $uploadedLogo
            : ($host ? ("https://logo.clearbit.com/" . $host) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f"));
        $job['logo'] = $logo;
        $job['organization_website'] = $website;
        $response->view('social-employer/viewdetails', [
            'job' => $job
        ]);
    }

    public function viewdetails(Request $request, Response $response): void
    {
        $id = (int)($request->param('id') ?? $request->get('id') ?? 0);
        if ($id <= 0) {
            $this->jobdetails($request, $response);
            return;
        }
        $db = Database::getInstance();
        $job = $db->fetchOne("SELECT * FROM social_jobs WHERE id = :id", ['id' => $id]);
        if (!$job) {
            $response->redirect('/find-a-job');
            return;
        }
        $response->view('social-employer/viewdetails', ['job' => $job]);
    }


// ===============================


// ===============================
// SAVE (CREATE + UPDATE)
// ===============================
public function savecandidatesubscription(Request $request, Response $response): void
{
    $db = Database::getInstance();
    $userId = (int)($_SESSION['user_id'] ?? 0);

    // ❌ NO LOGIN REDIRECT
    if ($userId <= 0) {
        $response->redirect('/social-candidate/candidatesubscriptions');
        return;
    }

    $id = (int)$request->post('id');

    $data = [
        'user_id' => $userId,
        'subject_name' => $request->post('subject_name'),
        'alert_status' => $request->post('alert_status') ? 1 : 0,
        'notification_email' => $request->post('notification_email'),
        'frequency' => $request->post('frequency'),
        'role_type' => $request->post('role_type'),
        'workplace_option' => $request->post('workplace_option'),
        'time_commitment' => $request->post('time_commitment'),
        'role_category' => $request->post('role_category'),
        'minimum_education' => $request->post('minimum_education'),
        'minimum_experience' => (int)$request->post('minimum_experience'),
        'pay_term' => $request->post('pay_term'),
        'minimum_hourly_rate' => $request->post('minimum_hourly_rate'),
        'minimum_salary' => $request->post('minimum_salary'),
        'impact_area' => $request->post('impact_area'),
    ];

    if ($id > 0) {
        $data['id'] = $id;
        $db->query("
            UPDATE candidate_job_alerts SET
            subject_name = :subject_name,
            alert_status = :alert_status,
            notification_email = :notification_email,
            frequency = :frequency,
            role_type = :role_type,
            workplace_option = :workplace_option,
            time_commitment = :time_commitment,
            role_category = :role_category,
            minimum_education = :minimum_education,
            minimum_experience = :minimum_experience,
            pay_term = :pay_term,
            minimum_hourly_rate = :minimum_hourly_rate,
            minimum_salary = :minimum_salary,
            impact_area = :impact_area,
            updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ", $data);
    } else {
        $db->query("
            INSERT INTO candidate_job_alerts
            (
                user_id, subject_name, alert_status, notification_email,
                frequency, role_type, workplace_option, time_commitment,
                role_category, minimum_education, minimum_experience,
                pay_term, minimum_hourly_rate, minimum_salary, impact_area,
                created_at
            ) VALUES (
                :user_id, :subject_name, :alert_status, :notification_email,
                :frequency, :role_type, :workplace_option, :time_commitment,
                :role_category, :minimum_education, :minimum_experience,
                :pay_term, :minimum_hourly_rate, :minimum_salary, :impact_area,
                NOW()
            )
        ", $data);
    }

    $response->redirect('/social-candidate/candidatesubscriptions');
}
public function hiringInsight(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $articles = [];
        try {
            $rows = $db->fetchAll("
                SELECT id, title, short_description, image, author, status, published_at, created_at
                FROM career_articles
                WHERE status = 'published'
                ORDER BY COALESCE(published_at, created_at) DESC
                LIMIT 200
            ");
            $makeExcerpt = function ($html, $max = 180) {
                $text = html_entity_decode(strip_tags((string)$html), ENT_QUOTES, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', $text);
                $text = trim($text);
                if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                    if (mb_strlen($text, 'UTF-8') > $max) {
                        $text = mb_substr($text, 0, $max, 'UTF-8') . '…';
                    }
                } else {
                    if (strlen($text) > $max) {
                        $text = substr($text, 0, $max) . '…';
                    }
                }
                return $text;
            };
            foreach ($rows as $r) {
                $descSource = $r['short_description'] ?? $r['content'] ?? '';
                $articles[] = [
                    'id' => (int)($r['id'] ?? 0),
                    'title' => (string)($r['title'] ?? ''),
                    'date' => !empty($r['published_at'])
                        ? date('M d, Y', strtotime($r['published_at']))
                        : (!empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : ''),
                    'desc' => $makeExcerpt($descSource),
                    'img' => (string)($r['image'] ?? ''),
                ];
            }
        } catch (\Throwable $t) {}
        $response->view('social-services/hiringInsight', [
            'articles' => $articles,
            'base' => '/'
        ]);
    }


    public function hiringInsightSignUp(Request $request,Response $response): void
    {
        $response->view('social-services/hiringInsightSignUp');
    }
     public function frequentlyCandidateAskedQuestions(Request $request,Response $response):void
    {
        $response->view('social-services/frequentlyCandidateAskedQuestions');
    }
     public function frequentlyEmployerAskedQuestions(Request $request,Response $response):void
    {
        $response->view('social-services/frequentlyEmployerAskedQuestions');
    }
    public function forgotPassword(Request $request, Response $response): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $response->view('social-services/forgot-password', [
            'title' => 'Reset Password',
        ]);
    }
    public function forgotPasswordSend(Request $request, Response $response): void
    {
        $email = trim((string)$request->post('email'));
        $sent = false;
        if ($email !== '') {
            $sent = true;
        }
        $response->view('social-services/forgot-password', [
            'title' => 'Reset Password',
            'sent' => $sent,
            'email' => $email
        ]);
    }
     public function searchEmployers(Request $request,Response $response): void
    {
        $db = Database::getInstance();
        $rows = [];
        try {
            $rows = $db->fetchAll("
                SELECT id, organization_name, acronyms, organization_type, website, logo_url
                FROM social_organizations
                ORDER BY organization_name ASC
                LIMIT 500
            ");
        } catch (\Throwable $t) {
            $rows = [];
        }
        $employers = [];
        foreach ($rows as $r) {
            $name = (string)($r['organization_name'] ?? '');
            $logo = '';
            $website = (string)($r['website'] ?? '');
            $extractHost = function ($url): string {
                $url = trim((string)$url);
                if ($url === '') return '';
                if (!preg_match('~^https?://~i', $url)) {
                    $url = 'http://' . $url;
                }
                $host = parse_url($url, PHP_URL_HOST) ?: '';
                $host = trim($host);
                if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                    $tokens = preg_split('/\s+/', trim((string)$url));
                    foreach (array_reverse($tokens) as $t) {
                        $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                        if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                            $host = $t;
                            break;
                        }
                    }
                }
                return $host;
            };
            $initials = (string)($r['acronyms'] ?? '');
            if ($initials === '' && $name !== '') {
                $parts = preg_split('/\s+/', $name);
                $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
            }
            // Prefer uploaded logo if present
            if ($logo === '' && !empty($r['logo_url'])) {
                $candidate = trim((string)$r['logo_url']);
                // URL-encode filename if it contains spaces/unsafe chars
                if ($candidate !== '' && strpos($candidate, 'http') !== 0) {
                    $pos = strrpos($candidate, '/');
                    if ($pos !== false) {
                        $prefix = substr($candidate, 0, $pos + 1);
                        $fname = substr($candidate, $pos + 1);
                        $candidate = $prefix . rawurlencode($fname);
                    }
                }
                // Normalize to public URL
                try {
                    $storage = new \App\Core\Storage();
                    $logo = $storage->url(ltrim($candidate, '/'));
                } catch (\Throwable $t) {
                    $logo = $candidate;
                }
            }
            if ($logo === '' && $website !== '') {
                $host = $extractHost($website);
                if (stripos($host, 'www.') === 0) {
                    $host = substr($host, 4);
                }
                $logo = "https://logo.clearbit.com/" . $host;
            }
            if ($logo === '' && $name !== '') {
                $logo = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f";
            }
            $type = strtolower((string)($r['organization_type'] ?? ''));
            if ($type === 'agency' || $type === 'recruiter' || $type === 'staffing') {
                $type = 'recruiter';
            } elseif ($type === '') {
                $type = 'direct';
            }
            $employers[] = [
                'id' => (int)($r['id'] ?? 0),
                'name' => $name,
                'logo' => $logo,
                'initials' => $initials,
                'type' => $type,
                'website' => $website,
            ];
        }
        $response->view('social-services/searchEmployers', [
            'employers' => $employers,
            'base' => '/'
        ]);
    }
    public function organizationDetails(Request $request, Response $response): void
    {
        $id = (int)($request->get('id') ?? 0);
        if ($id <= 0) {
            $response->view('social-services/organizationDetails', [
                'org' => null,
                'base' => '/'
            ]);
            return;
        }
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM social_organizations WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->view('social-services/organizationDetails', [
                'org' => null,
                'base' => '/'
            ]);
            return;
        }
        $name = (string)($row['organization_name'] ?? '');
        $website = (string)($row['website'] ?? '');
        $extractHost = function ($url): string {
            $url = trim((string)$url);
            if ($url === '') return '';
            if (!preg_match('~^https?://~i', $url)) {
                $url = 'http://' . $url;
            }
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $host = trim($host);
            if ($host === '' || !preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $host)) {
                $tokens = preg_split('/\s+/', trim((string)$url));
                foreach (array_reverse($tokens) as $t) {
                    $t = preg_replace('/[^A-Za-z0-9\.\-]/', '', $t);
                    if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t)) {
                        $host = $t;
                        break;
                    }
                }
            }
            return $host;
        };
        $host = $extractHost($website);
        $logo = '';
        if (!empty($row['logo_url'])) {
            $candidate = trim((string)$row['logo_url']);
            if ($candidate !== '' && strpos($candidate, 'http') !== 0) {
                $pos = strrpos($candidate, '/');
                if ($pos !== false) {
                    $prefix = substr($candidate, 0, $pos + 1);
                    $fname = substr($candidate, $pos + 1);
                    $candidate = $prefix . rawurlencode($fname);
                }
            }
            try {
                $storage = new \App\Core\Storage();
                $logo = $storage->url(ltrim($candidate, '/'));
            } catch (\Throwable $t) {
                $logo = $candidate;
            }
        } else {
            $logo = $host ? ("https://logo.clearbit.com/" . $host) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=ffffff&color=54595f");
        }
        $org = [
            'id' => (int)$row['id'],
            'name' => $name,
            'acronyms' => (string)($row['acronyms'] ?? ''),
            'type' => (string)($row['organization_type'] ?? ''),
            'is_agency' => (int)($row['is_agency'] ?? 0),
            'website' => $website,
            'ein' => (string)($row['ein'] ?? ''),
            'staff_count' => (int)($row['staff_count'] ?? 0),
            'mission_focus' => (string)($row['mission_focus'] ?? ''),
            'mission' => (string)($row['mission'] ?? ''),
            'impact' => (string)($row['impact'] ?? ''),
            'logo' => $logo,
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
        $response->view('social-services/organizationDetails', [
            'org' => $org,
            'base' => '/'
        ]);
    }
       public function supportss(Request $request, Response $response): void
    {
        $response->view('social-services/supportss');
    }
////anupam
 
/*public function findjob(Request $request, Response $response): void
{
    $response->view('social-services/find-a-job');
}


     public function roles(Request $request, Response $response): void
{
    $response->view('social-services/roles', []);
}
  public function createjob(Request $request, Response $response): void
  {
    $response->view('social-services/createjob',[]);

  }
  public function candidate(Request $request, Response $response): void
    {
        $response->view('social-services/candidate');
    }
 public function listings(Request $request, Response $response): void
    {
        $response->view('social-services/listings');
    }
 public function subscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/subscriptions');
    }

    public function newsubscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/newsubscriptions');
    }
     public function employers(Request $request, Response $response): void
    {
        $response->view('social-services/employers');
    }
       public function pricing(Request $request, Response $response): void
    {
        $response->view('social-services/pricing');
    }
      public function aboutus(Request $request, Response $response): void
    {
        $response->view('social-services/aboutus');
    }
      public function specials(Request $request, Response $response): void
    {
        $response->view('social-services/specials');
    }

    public function terms(Request $request, Response $response): void
    {
        $response->view('social-services/terms');
    }

    public function privacy(Request $request, Response $response): void
    {
        $response->view('social-services/privacy');
    }

    public function grievances(Request $request, Response $response): void
    {
        $response->view('social-services/grievances');
    }
    
 
    }
       public function cart(Request $request, Response $response): void
    {
        $response->view('social-services/cart');
    }
    public function index2(Request $request, Response $response): void
    {
        $response->view('social-services/index');
    }
   
    public function organizationDetails(Request $request, Response $response): void
    {
        $response->view('social-services/organizationDetails');
    }
    
    public function contactus(Request $request,Response $response):void
    {
        $response->view('social-services/contactus');
    }
   
   

    
}*/

 public function login(Request $request, Response $response): void
    {
        $response->view('social-services/login');
    }

    public function hiringInsightArticle(Request $request, Response $response): void
    {
        $id = (int)($request->get('id') ?? 0);
        if ($id <= 0) {
            $response->redirect('/hiringInsight');
            return;
        }
        $db = \App\Core\Database::getInstance();
        $row = null;
        try {
            $row = $db->fetchOne("
                SELECT ca.*, ac.name AS category_name
                FROM career_articles ca
                LEFT JOIN article_categories ac ON ac.id = ca.category_id
                WHERE ca.id = :id AND ca.status = 'published'
            ", ['id' => $id]);
        } catch (\Throwable $t) {}
        if (!$row) {
            $response->redirect('/hiringInsight');
            return;
        }
        $article = [
            'id' => (int)($row['id'] ?? 0),
            'title' => (string)($row['title'] ?? ''),
            'date' => !empty($row['published_at'])
                ? date('M d, Y', strtotime($row['published_at']))
                : (!empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : ''),
            'img' => (string)($row['image'] ?? ''),
            'content' => (string)($row['content'] ?? ''),
            'short_description' => (string)($row['short_description'] ?? ''),
            'author' => (string)($row['author'] ?? ''),
            'category' => (string)($row['category_name'] ?? ''),
            'url' => '/hiringInsight/article?id=' . $id,
        ];
        $related = [];
        $latest = [];
        $prev = null;
        $next = null;
        try {
            $currentTs = !empty($row['published_at']) ? $row['published_at'] : $row['created_at'];
            if ($currentTs) {
                $p = $db->fetchOne("
                    SELECT id, title
                    FROM career_articles
                    WHERE status = 'published'
                      AND COALESCE(published_at, created_at) < :ts
                    ORDER BY COALESCE(published_at, created_at) DESC
                    LIMIT 1
                ", ['ts' => $currentTs]);
                if ($p) {
                    $prev = ['id' => (int)$p['id'], 'title' => (string)$p['title']];
                }
                $n = $db->fetchOne("
                    SELECT id, title
                    FROM career_articles
                    WHERE status = 'published'
                      AND COALESCE(published_at, created_at) > :ts
                    ORDER BY COALESCE(published_at, created_at) ASC
                    LIMIT 1
                ", ['ts' => $currentTs]);
                if ($n) {
                    $next = ['id' => (int)$n['id'], 'title' => (string)$n['title']];
                }
            }
            $latestRows = $db->fetchAll("
                SELECT id, title
                FROM career_articles
                WHERE status = 'published' AND id <> :id
                ORDER BY COALESCE(published_at, created_at) DESC
                LIMIT 5
            ", ['id' => $id]);
            foreach ($latestRows as $lr) {
                $latest[] = [
                    'id' => (int)($lr['id'] ?? 0),
                    'title' => (string)($lr['title'] ?? ''),
                ];
            }
            if (!empty($row['category_id'])) {
                $relRows = $db->fetchAll("
                    SELECT ca.id, ca.title, ca.image, ca.published_at, ca.created_at, ac.name AS category
                    FROM career_articles ca
                    LEFT JOIN article_categories ac ON ac.id = ca.category_id
                    WHERE ca.status = 'published' AND ca.id <> :id AND ca.category_id = :cat
                    ORDER BY COALESCE(ca.published_at, ca.created_at) DESC
                    LIMIT 3
                ", ['id' => $id, 'cat' => $row['category_id']]);
            } else {
                $relRows = $db->fetchAll("
                    SELECT ca.id, ca.title, ca.image, ca.published_at, ca.created_at, ac.name AS category
                    FROM career_articles ca
                    LEFT JOIN article_categories ac ON ac.id = ca.category_id
                    WHERE ca.status = 'published' AND ca.id <> :id
                    ORDER BY COALESCE(ca.published_at, ca.created_at) DESC
                    LIMIT 3
                ", ['id' => $id]);
            }
            foreach ($relRows as $r) {
                $related[] = [
                    'id' => (int)($r['id'] ?? 0),
                    'title' => (string)($r['title'] ?? ''),
                    'img' => (string)($r['image'] ?? ''),
                    'category' => (string)($r['category'] ?? ''),
                    'date' => !empty($r['published_at'])
                        ? date('M d, Y', strtotime($r['published_at']))
                        : (!empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : ''),
                ];
            }
        } catch (\Throwable $t) {}
        $response->view('social-services/hiringInsightArticle', [
            'article' => $article,
            'related' => $related,
            'latest' => $latest,
            'prev' => $prev,
            'next' => $next,
        ]);
    }


}
