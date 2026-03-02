<?php
namespace App\Controllers;

use App\Models\SocialJobApplication;

class EmployerApplicationController
{
    private $model;

    public function __construct()
    {
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $this->model = new SocialJobApplication($pdo);
        } catch (\Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // ===============================
    // SHOW APPLICATION LIST
    // ===============================
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        // Ensure employer_id in session
        if (empty($_SESSION['employer_id'])) {
            try {
                $userId = (int)$_SESSION['user_id'];
                $pdo = \App\Core\Database::getInstance()->getConnection();
                $model = new \App\Models\SocialJobApplication($pdo);
                $derived = (int)($model->getEmployerProfileId($userId) ?? 0);
                if ($derived > 0) {
                    $_SESSION['employer_id'] = $derived;
                }
            } catch (\Throwable $t) {}
        }
        $employerId = (int)($_SESSION['employer_id'] ?? 0);
        if ($employerId <= 0) {
            header("Location: /social-employer/account");
            exit;
        }

        // fetch applications
        $apps = $this->model->employerApplicants($employerId) ?? [];
        // base path for links
        $base = "/";
        // load view
        require dirname(__DIR__, 2) . '/resources/views/social-employer/application.php';
    }


    // ===============================
    // UPDATE APPLICATION STATUS
    // ===============================
    public function update()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $employerId = $_SESSION['employer_id'] ?? 0;

        if (!$employerId) {
            header("Location: /login");
            exit;
        }

        $appId  = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if (!$appId || !$status) {
            die("Missing data");
        }

        // Security check
        if (!$this->model->applicationBelongsToEmployer($appId, $employerId)) {
            die("Unauthorized access");
        }

        // Update status
        $this->model->updateStatus($appId, $status);

        header("Location: /social-employer/application");
        exit;
    }
}
