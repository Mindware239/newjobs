<?php
 

namespace App\Controllers\Social;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\SocialJobApplication;

class CandidatesController
{

    public function candidate(Request $request, Response $response)
    {

        $response->view('social-candidate/candidate');
    }


    public function submitApplication(Request $request, Response $response)
{
    $db = Database::getInstance();

    $email   = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $jobId   = (int)($_POST['job_id'] ?? 0);

    $storedFile = '';
    $originalName = '';

    if (!empty($_FILES['resume']['name'])) {

        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','doc','docx'])) {
            $response->redirect('/candidatelisting');
            return;
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $response->redirect('/candidatelisting');
            return;
        }
        $uploadDir = __DIR__ . '/../../../storage/uploads/applications/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $storedFile = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($tmpPath, $uploadDir . $storedFile);
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId && $jobId > 0) {
        $candidate = $this->model->getCandidateIdByUserId($userId);
        if (!$candidate) {
            $user = \App\Models\User::find($userId);
            $fullName = (string)($user->full_name ?? '');
            $parts = array_values(array_filter(explode(' ', $fullName)));
            $first = $parts[0] ?? 'Candidate';
            $last = $parts[1] ?? '';
            $sc = new \App\Models\SocialAccountCandidate();
            $sc->saveOrUpdate([
                'user_id' => $userId,
                'email' => (string)($user->email ?? $email),
                'phone' => null,
                'first_name' => $first,
                'last_name' => $last
            ]);
            $candidate = $sc->findByUserId($userId);
        }
        if ($candidate && $this->model->jobExists($jobId)) {
            $cid = (int)$candidate['id'];
            if (!$this->model->alreadyApplied($jobId, $cid)) {
                $this->model->insert($jobId, $cid, (string)$email, (string)$storedFile, (string)$message);
            } else {
                $response->setBody('<script>alert("You have already applied for this job.");window.location.href="/candidatelisting";</script>');
            }
        }
    }

    $response->redirect('/candidatelisting');
}


    private $model;

    public function __construct()
{
    try {

        $pdo = \App\Core\Database::getInstance()->getConnection();
        $this->model = new SocialJobApplication($pdo);

    } catch (\Throwable $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

    public function apply()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if(empty($_SESSION['user_id']))
        die("Login required");

    $jobId = (int)($_POST['job_id'] ?? 0);
    if(!$jobId) die("Invalid job");

    $userId = (int)$_SESSION['user_id'];

    $candidate = $this->model->getCandidateIdByUserId($userId);
    if(!$candidate) {
        $user = \App\Models\User::find($userId);
        $fullName = (string)($user->full_name ?? '');
        $parts = array_values(array_filter(explode(' ', $fullName)));
        $first = $parts[0] ?? 'Candidate';
        $last = $parts[1] ?? '';
        $sc = new \App\Models\SocialAccountCandidate();
        $sc->saveOrUpdate([
            'user_id' => $userId,
            'email' => (string)($user->email ?? ''),
            'phone' => null,
            'first_name' => $first,
            'last_name' => $last
        ]);
        $candidate = $sc->findByUserId($userId);
    }

    $cid = $candidate['id'];

    if($this->model->alreadyApplied($jobId,$cid)) {
        echo '<script>alert("You have already applied for this job.");window.location.href="/candidatelisting";</script>';
        exit;
    }

    if(!$this->model->jobExists($jobId))
    die("Invalid job id");

$this->model->insert($jobId,$cid);

    header("Location: /candidatelisting");
    exit;
}
    public function appliedJobs()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if(empty($_SESSION['user_id']))
        die("Login required");

    $userId = (int)$_SESSION['user_id'];
    $candidate = $this->model->getCandidateIdByUserId($userId);

    if(!$candidate) {
        $user = \App\Models\User::find($userId);
        $fullName = (string)($user->full_name ?? '');
        $parts = array_values(array_filter(explode(' ', $fullName)));
        $first = $parts[0] ?? 'Candidate';
        $last = $parts[1] ?? '';
        $sc = new \App\Models\SocialAccountCandidate();
        $sc->saveOrUpdate([
            'user_id' => $userId,
            'email' => (string)($user->email ?? ''),
            'phone' => null,
            'first_name' => $first,
            'last_name' => $last
        ]);
        $candidate = $sc->findByUserId($userId);
        if (!$candidate) {
            die("Profile not found");
        }
    }

    $jobs = $this->model->candidateJobs($candidate['id']);

    require dirname(__DIR__,3) . '/resources/views/social-candidate/candidatelisting.php';

}

}
