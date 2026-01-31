<?php
// Test job matching and notifications (email + push) for one candidate and one job
// Usage: php scripts/test_job_match_notifications.php [job_id]

declare(strict_types=1);

// Bootstrap
$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once $root . '/app/Core/Database.php';
    require_once $root . '/app/Core/Env.php';
    require_once $root . '/app/Models/User.php';
    require_once $root . '/app/Models/Candidate.php';
    require_once $root . '/app/Models/Job.php';
    require_once $root . '/app/Services/JobMatchService.php';
    require_once $root . '/app/Services/NotificationService.php';
}

echo "== Job Match + Notifications Test ==\n";

// Load env if available
try {
    if (class_exists('\\Dotenv\\Dotenv')) {
        \Dotenv\Dotenv::createImmutable($root)->safeLoad();
    }
} catch (\Throwable $t) {}

$db = \App\Core\Database::getInstance();

// Pick job
$jobIdArg = (int)($argv[1] ?? 0);
if ($jobIdArg > 0) {
    $job = \App\Models\Job::find($jobIdArg);
} else {
    $row = $db->fetchOne("SELECT * FROM jobs WHERE status = 'published' ORDER BY created_at DESC LIMIT 1");
    $job = $row ? \App\Models\Job::find((int)$row['id']) : null;
}

if (!$job) {
    echo "✗ No job found. Create a published job first.\n";
    exit(1);
}
echo "✓ Using Job: ID={$job->id}, Title={$job->title}\n";

// Pick candidate user (prefer with FCM token for push test)
$candRow = $db->fetchOne("
    SELECT u.id as user_id, c.id as candidate_id, u.email, u.fcm_token
    FROM users u 
    JOIN candidates c ON c.user_id = u.id
    WHERE COALESCE(u.fcm_token,'') <> '' 
    LIMIT 1
");
if (!$candRow) {
    $candRow = $db->fetchOne("
        SELECT u.id as user_id, c.id as candidate_id, u.email, u.fcm_token
        FROM users u 
        JOIN candidates c ON c.user_id = u.id
        WHERE COALESCE(u.email,'') <> '' 
        ORDER BY u.created_at DESC
        LIMIT 1
    ");
}
if (!$candRow) {
    echo "✗ No candidate with email or FCM token found.\n";
    exit(1);
}
$candidateId = (int)$candRow['candidate_id'];
$candidateUserId = (int)$candRow['user_id'];
$candidateEmail = (string)($candRow['email'] ?? '');
$candidateFcm = (string)($candRow['fcm_token'] ?? '');
echo "✓ Using Candidate: candidate_id={$candidateId}, user_id={$candidateUserId}\n";

// Calculate match
$matchService = new \App\Services\JobMatchService();
$match = $matchService->calculateMatch($candidateId, (int)$job->id, true);
echo "✓ Match Score: overall={$match['overall_match_score']} skills={$match['skill_match_score']} exp={$match['experience_match_score']}\n";

$candidateLink = "/candidate/jobs/" . ($job->slug ?? $job->id);

// 1) Push test (FCM)
if ($candidateFcm !== '') {
    $pushOk = \App\Services\NotificationService::sendPush(
        $candidateUserId,
        'New Job Match',
        "Job '{$job->title}' matches your profile ({$match['overall_match_score']}%).",
        $candidateLink
    );
    echo $pushOk ? "✓ Push sent via FCM\n" : "✗ Push failed (check storage/firebase.json and fcm_token)\n";
} else {
    echo "ℹ Candidate has no FCM token; skipping push test.\n";
}

// 2) Email test to candidate
$emailCandidate = false;
if ($candidateEmail !== '') {
    \App\Services\NotificationService::queueEmail(
        $candidateEmail,
        'job_match',
        [
            'job_title' => $job->title,
            'match_score' => $match['overall_match_score'],
            'candidate_user_id' => $candidateUserId,
            'employer_id' => (int)($job->attributes['employer_id'] ?? 0)
        ],
        "New Job Match: {$job->title}"
    );
    $emailCandidate = true;
    echo "✓ Candidate email queued/sent (driver=" . ($_ENV['QUEUE_DRIVER'] ?? 'sync') . ")\n";
} else {
    echo "ℹ Candidate has no email; skipping email test.\n";
}

// 3) Notify employer (email)
$employer = $job->employer();
if ($employer) {
    $employerUser = $employer->user();
    if ($employerUser && !empty($employerUser->attributes['email'])) {
        \App\Services\NotificationService::queueEmail(
            (string)$employerUser->attributes['email'],
            'candidate_match_employer',
            [
                'job_title' => $job->title,
                'match_count' => 1,
                'dashboard_link' => "/employer/jobs/{$job->slug}/candidates",
                'employer_id' => (int)$employer->id
            ],
            "Candidates Found for {$job->title}"
        );
        echo "✓ Employer email queued/sent\n";
    } else {
        echo "ℹ Employer user email not found; skipping employer email.\n";
    }
} else {
    echo "ℹ Employer not found for job; skipping employer notification.\n";
}

echo "== Done ==\n";
