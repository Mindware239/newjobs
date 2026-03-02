<?php

declare(strict_types=1);

namespace App\Controllers\Social;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Subscription;


class SocialJobsController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
$jobs = $db->fetchAll("
    SELECT * FROM social_jobs
    WHERE is_deleted = 0
    ORDER BY created_at DESC
");

        $response->view('social-services/listings', [
            'title' => 'Social Jobs',
            'jobs' => $jobs
        ]);
    }

    public function status(Request $request, Response $response): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int)($request->param('id') ?? 0);
        $status = (string)($request->post('status') ?? '');
        if ($id <= 0) {
            $response->redirect('/social-employer/listings?error=Invalid job id');
            return;
        }
        $allowed = ['draft', 'pending', 'active', 'expired'];
        if (!in_array($status, $allowed, true)) {
            $response->redirect('/social-employer/listings?error=Invalid status');
            return;
        }

        $map = [
            'draft'   => ['publish_status' => 'draft',   'is_draft' => 1],
            'pending' => ['publish_status' => 'pending', 'is_draft' => 0],
            'active'  => ['publish_status' => 'published','is_draft' => 0],
            'expired' => ['publish_status' => 'expired', 'is_draft' => 0]
        ];

        $db = Database::getInstance();
        try {
            $db->query(
                "UPDATE social_jobs SET publish_status = :ps, is_draft = :draft WHERE id = :id",
                ['ps' => $map[$status]['publish_status'], 'draft' => $map[$status]['is_draft'], 'id' => $id]
            );
            $response->redirect('/social-employer/listings?success=Status updated');
        } catch (\Exception $e) {
            error_log('Status update error: ' . $e->getMessage());
            $response->redirect('/social-employer/listings?error=Status update failed');
        }
    }
    public function create(Request $request, Response $response): void
    {
        $response->view('social-services/newlisting', [
            'title' => 'Create Social Job'
        ]);
    }


    // ✅ Has subscription → allow job post form

    // ===========================
    // STORE JOB
    // ===========================
public function store(Request $request, Response $response): void
{
    $db = Database::getInstance();

    $subscriptionModel = new Subscription();

    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

   $userId = $_SESSION['user_id'] ?? null;

if(!$userId){
    $response->redirect('/login');
    return;
}

$employer = $db->fetch(
    "SELECT id FROM social_employer_profiles WHERE user_id = :uid",
    ['uid'=>$userId]
);

if(!$employer){
    $response->redirect('/social-employer/account?error=Create profile first');
    return;
}

$employer_id = $employer['id'];

    // ===============================
    // ✅ SUBSCRIPTION CHECK
    // ===============================

    $sub = $subscriptionModel->getActive($employer_id);

    if(!$sub){
        $response->redirect('/pricing?error=Please buy a plan first');
        return;
    }

    if($sub['jobs_used'] >= $sub['job_limit']){
        $response->redirect('/pricing?error=Your job posting limit is finished');
        return;
    }

    // ===============================
    // 🚫 DUPLICATE JOB CHECK (ADDED)
    // ===============================

    $organizationName = $request->post('organization_name') ?: $request->post('organization_select');
    $roleName = $request->post('role_name');

    $existingJob = $db->fetch("
        SELECT id FROM social_jobs 
        WHERE employer_id = :employer_id
          AND organization_name = :organization_name
          AND role_name = :role_name
    ", [
        'employer_id' => $employer_id,
        'organization_name' => $organizationName,
        'role_name' => $roleName
    ]);

    if($existingJob){
        $response->redirect('/employer/social-jobs/create?error=This job already exists');
        return;
    }

    // ===============================
    // 👉 INSERT JOB
    // ===============================

    $data = [
        'employer_id' => $employer_id,

        'candidate_type'       => $request->post('candidate_type') ?: 'Employee / Staff (Paid)',
        'organization_name'    => $organizationName,
        'organization_type'    => $request->post('org_type') ?: 'Unspecified',
        'is_agency'            => $request->post('is_agency') ? 1 : 0,
        'website'              => $request->post('website'),
        'ein'                  => $request->post('ein'),
        'staff_count'          => $request->post('staff_count'),
        'org_mission_focus'    => $request->post('mission_focus'),
        'organization_mission'=> $request->post('org_mission'),
        'organization_impact' => $request->post('org_impact'),

        'role_name'            => $roleName,
        'time_commitment'      => $request->post('time_commitment'),
        'time_details'         => $request->post('time_details'),
        'work_category'        => $request->post('work_category'),
        'experience_years'     => $request->post('experience_years'),
        'education_level'      => $request->post('education_level'),

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
        'notification_emails' => json_encode($request->post('emails') ?? []),
        'screening_questions' => json_encode($request->post('questions') ?? []),

// ✅ JOB STATUS (ACTIVE)
'publish_status' => 'published',
'is_draft'       => 0,
'is_deleted'     => 0,

'created_at'     => date('Y-m-d H:i:s')
    ];

   $sql = "INSERT INTO social_jobs (
    employer_id,
    candidate_type, organization_name, organization_type, is_agency, website, ein, staff_count,
    org_mission_focus, organization_mission, organization_impact,
    role_name, time_commitment, time_details, work_category, experience_years, education_level,
    pay_type, min_pay, max_pay,
    role_mission_focused, short_description, full_description,
    workplace_option, workplace_details, job_location, location_details,
    publish_type, publish_date,
    apply_method, notification_emails, screening_questions,
    publish_status, is_draft, is_deleted, created_at
) VALUES (
    :employer_id,
    :candidate_type, :organization_name, :organization_type, :is_agency, :website, :ein, :staff_count,
    :org_mission_focus, :organization_mission, :organization_impact,
    :role_name, :time_commitment, :time_details, :work_category, :experience_years, :education_level,
    :pay_type, :min_pay, :max_pay,
    :role_mission_focused, :short_description, :full_description,
    :workplace_option, :workplace_details, :job_location, :location_details,
    :publish_type, :publish_date,
    :apply_method, :notification_emails, :screening_questions,
    :publish_status, :is_draft, :is_deleted, :created_at
)";


try {
    $db->query($sql, $data);
} catch (\Throwable $e) {
    die("SQL ERROR: " . $e->getMessage());
}

    // ===============================
    // ✅ REDUCE CREDIT
    // ===============================

    $subscriptionModel->increaseJobUsed($sub['id']);

    $response->redirect('/employer/social-jobs?success=Social Job created');
}



    // ===========================
    // EDIT
    // ===========================
  public function edit(Request $request, Response $response): void
{
    $id = (int)($request->param('id') ?? 0);

    if ($id <= 0) {
        $response->redirect('/social-employer/listings?error=Invalid job id');
        return;
    }

    $db = Database::getInstance();

    $job = $db->fetchOne(
        "SELECT * FROM social_jobs WHERE id = :id",
        ['id' => $id]
    );

    if (!$job) {
        $response->redirect('/social-employer/listings?error=Job not found');
        return;
    }

    $response->view('social-employer/newlisting', [
        'title'  => 'Edit Job',
        'action' => '/social-employer/job/' . $id . '/update',
        'job'    => $job
    ]);
}


    // ===========================
    // UPDATE
    // ===========================
public function update(Request $request, Response $response, array $params): void
{
    $id = (int)($params['id'] ?? 0);

    if ($id <= 0) {
        $response->redirect('/employer/social-jobs?error=Invalid job id');
        return;
    }

    // 🔹 Decide publish status
    $publishType = $request->post('publish_type');

    $publishStatus = ($publishType === 'draft') ? 'draft' : 'published';
    $isDraft       = ($publishType === 'draft') ? 1 : 0;

    $db = Database::getInstance();

    $db->query("
        UPDATE social_jobs SET
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
            education_level = :education_level,
            pay_type = :pay_type,
            min_pay = :min_pay,
            max_pay = :max_pay,
            role_mission_focused = :role_mission_focused,
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

            -- ✅ STATUS FIX
            publish_status = :publish_status,
            is_draft = :is_draft

        WHERE id = :id
    ", [
        'candidate_type'       => $request->post('candidate_type'),
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
        'role_mission_focused' => $request->post('role_focus'),
        'short_description'    => $request->post('short_description'),
        'full_description'     => $request->post('job_overview'),
        'workplace_option'     => $request->post('workplace_type'),
        'workplace_details'    => $request->post('workplace_details'),
        'job_location'         => $request->post('location'),
        'location_details'     => $request->post('location_details'),
        'publish_type'         => $publishType,
        'publish_date'         => $request->post('publish_date'),
        'apply_method'         => $request->post('apply_method'),
        'notification_emails'  => json_encode($request->post('emails') ?? []),

        // ✅ STATUS VALUES
        'publish_status' => $publishStatus,
        'is_draft'       => $isDraft,

        'id' => $id
    ]);

    $response->redirect('/employer/social-jobs?success=Job updated');
}



    // ===========================
    // DELETE
    // ===========================
    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $db = Database::getInstance();

        $db->query("DELETE FROM social_jobs WHERE id = :id", [
            'id' => $id
        ]);

        $response->redirect('/employer/social-jobs?success=Deleted');
    }
}
