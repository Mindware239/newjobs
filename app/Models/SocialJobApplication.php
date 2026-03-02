<?php
namespace App\Models;

use PDO;

class SocialJobApplication
{
    private $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS social_job_applications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_id INT NOT NULL,
                candidate_id INT NOT NULL,
                application_status VARCHAR(32) NOT NULL DEFAULT 'applied',
                submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        try { $this->db->exec("ALTER TABLE social_job_applications ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE social_job_applications ADD COLUMN IF NOT EXISTS resume_file VARCHAR(255) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE social_job_applications ADD COLUMN IF NOT EXISTS cover_letter TEXT NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE social_job_applications ADD UNIQUE KEY uniq_job_candidate (job_id, candidate_id)"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE social_job_applications ADD INDEX idx_job_id (job_id)"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE social_job_applications ADD INDEX idx_candidate_id (candidate_id)"); } catch (\Throwable $e) {}
    }

    /* CHECK ALREADY APPLIED */
    public function alreadyApplied($jobId, $candidateId)
    {
        $stmt = $this->db->prepare("SELECT id FROM social_job_applications WHERE job_id = ? AND candidate_id = ? LIMIT 1");
        $stmt->execute([$jobId, $candidateId]);
        return $stmt->fetch();
    }

    /* CHECK IF JOB EXISTS */
    public function jobExists($jobId)
    {
        $stmt = $this->db->prepare("SELECT id FROM social_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        return $stmt->fetch();
    }

    /* INSERT APPLICATION */
  public function insert($jobId, $candidateId, $email = null, $resume = null, $message = null)

{
    $this->ensureTable();

    $stmt = $this->db->prepare("
        INSERT INTO social_job_applications
        (job_id, candidate_id, email, resume_file, cover_letter, application_status, submitted_at)
        VALUES
        (?, ?, ?, ?, ?, 'applied', NOW())
    ");

    return $stmt->execute([
        $jobId,
        $candidateId,
        $email,
        $resume,
        $message
    ]);
}


    /* CANDIDATE DASHBOARD */
    public function candidateJobs($candidateId)
    {
        $stmt = $this->db->prepare("
            SELECT j.*, a.application_status 
            FROM social_job_applications a
            JOIN social_jobs j ON j.id = a.job_id
            WHERE a.candidate_id = ?
            ORDER BY a.id DESC
        ");
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll();
    }

/* EMPLOYER DASHBOARD - The Fix is here */
public function employerApplicants($employerId)
{
    $stmt = $this->db->prepare("
        SELECT 
            a.id AS application_id,
            a.application_status,
            a.submitted_at,
            a.resume_file,
            a.cover_letter,
            j.id AS job_id,
            j.role_name,
            j.organization_name,
            COALESCE(c.first_name, '') AS first_name,
            COALESCE(c.last_name, '') AS last_name,
            COALESCE(c.email, a.email) AS email
        FROM social_job_applications a
        JOIN social_jobs j ON j.id = a.job_id
        LEFT JOIN social_account_candidate c ON c.id = a.candidate_id
        WHERE j.employer_id = ?
        ORDER BY a.submitted_at DESC
    ");

    $stmt->execute([$employerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    /* UPDATE STATUS */
    public function updateStatus($id, $status)
    {
        $this->ensureTable();
        $stmt = $this->db->prepare("
            UPDATE social_job_applications 
            SET application_status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $id]);
    }

    public function getCandidateIdByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT id FROM social_account_candidate WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getEmployerProfileId($userId)
    {
        $stmt = $this->db->prepare("SELECT id FROM social_employer_profiles WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? $row['id'] : null;
    }

    public function applicationBelongsToEmployer($appId, $employerProfileId)
    {
        $stmt = $this->db->prepare("
            SELECT a.id FROM social_job_applications a
            JOIN social_jobs j ON j.id = a.job_id
            JOIN social_employer_profiles e ON e.id = j.employer_id
            WHERE a.id = ? AND e.id = ? LIMIT 1
        ");
        $stmt->execute([$appId, $employerProfileId]);
        return $stmt->fetch();
    }

    public function employerApplicantsByEmail(string $email)
    {
        $stmt = $this->db->prepare("
            SELECT 
                a.id AS application_id,
                a.application_status,
                a.submitted_at,
                a.resume_file,
                j.id AS job_id,
                j.role_name,
                j.organization_name,
                COALESCE(c.first_name, '') AS first_name,
                COALESCE(c.last_name, '') AS last_name,
                COALESCE(c.email, a.email) AS email
            FROM social_job_applications a
            JOIN social_jobs j ON j.id = a.job_id
            LEFT JOIN social_account_candidate c ON c.id = a.candidate_id
            WHERE j.notification_emails LIKE ?
            ORDER BY a.submitted_at DESC
        ");
        $stmt->execute(['%' . $email . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
