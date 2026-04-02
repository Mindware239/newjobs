<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Storage;
use App\Services\NotificationService;
use App\Models\AuditLog;
use App\Models\SystemSetting;

class EmploymentVerificationService
{
    private static function ensureTables(): void
    {
        try {
            $db = Database::getInstance();
            // employment_records
            $db->execute("
                CREATE TABLE IF NOT EXISTS employment_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    candidate_id INT NOT NULL,
                    company_name VARCHAR(255) NOT NULL,
                    designation VARCHAR(255) NULL,
                    employee_id VARCHAR(100) NULL,
                    start_date DATE NULL,
                    end_date DATE NULL,
                    consent_given TINYINT(1) DEFAULT 0,
                    consent_at DATETIME NULL,
                    status_overall VARCHAR(50) DEFAULT 'under_review',
                    status_level1 VARCHAR(50) DEFAULT 'pending',
                    status_level2 VARCHAR(50) DEFAULT 'pending',
                    status_level3 VARCHAR(50) DEFAULT 'pending',
                    risk_score INT DEFAULT 0,
                    risk_category VARCHAR(50) DEFAULT 'pending',
                    verification_date DATETIME NULL,
                    verified_badge TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NULL,
                    INDEX idx_candidate (candidate_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            // employment_documents
            $db->execute("
                CREATE TABLE IF NOT EXISTS employment_documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employment_id INT NOT NULL,
                    doc_type VARCHAR(50) NOT NULL,
                    file_path VARCHAR(512) NOT NULL,
                    file_hash VARCHAR(64) NULL,
                    mime_type VARCHAR(100) NULL,
                    size_bytes INT NULL,
                    metadata JSON NULL,
                    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_emp (employment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            try {
                $db->query("SELECT employment_id FROM employment_documents LIMIT 1");
            } catch (\Throwable $e) {
                try {
                    $db->execute("ALTER TABLE employment_documents ADD COLUMN employment_id INT NOT NULL");
                    $db->execute("CREATE INDEX IF NOT EXISTS idx_emp ON employment_documents (employment_id)");
                } catch (\Throwable $e2) {}
            }
            // employment_document_texts
            $db->execute("
                CREATE TABLE IF NOT EXISTS employment_document_texts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    document_id INT NOT NULL,
                    extracted_text LONGTEXT NULL,
                    language VARCHAR(10) DEFAULT 'en',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_doc (document_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            // verification_requests
            $db->execute("
                CREATE TABLE IF NOT EXISTS verification_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employment_id INT NOT NULL,
                    hr_email VARCHAR(255) NOT NULL,
                    hr_phone VARCHAR(50) NULL,
                    manager_email VARCHAR(255) NULL,
                    company_website VARCHAR(255) NULL,
                    cin VARCHAR(50) NULL,
                    gst VARCHAR(50) NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    status VARCHAR(50) DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_emp_req (employment_id),
                    UNIQUE KEY uniq_token (token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            // verification_responses
            $db->execute("
                CREATE TABLE IF NOT EXISTS verification_responses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    request_id INT NOT NULL,
                    status VARCHAR(50) DEFAULT 'verified',
                    confirmed_working TINYINT(1) DEFAULT 0,
                    duration_text VARCHAR(255) NULL,
                    designation VARCHAR(255) NULL,
                    rehire_eligibility VARCHAR(50) DEFAULT 'unknown',
                    misconduct TINYINT(1) DEFAULT 0,
                    remarks TEXT NULL,
                    responder_ip VARCHAR(64) NULL,
                    responded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_req (request_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            // verification_scores
            $db->execute("
                CREATE TABLE IF NOT EXISTS verification_scores (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employment_id INT NOT NULL,
                    score INT NOT NULL,
                    category VARCHAR(50) NOT NULL,
                    breakdown JSON NULL,
                    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_emp_score (employment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            // verification_logs
            $db->execute("
                CREATE TABLE IF NOT EXISTS verification_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employment_id INT NOT NULL,
                    event VARCHAR(50) NOT NULL,
                    metadata JSON NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_emp_log (employment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            try {
                $db->query("SELECT employment_id FROM verification_logs LIMIT 1");
            } catch (\Throwable $e) {
                try {
                    $db->execute("ALTER TABLE verification_logs ADD COLUMN employment_id INT NOT NULL");
                    $db->execute("CREATE INDEX IF NOT EXISTS idx_emp_log ON verification_logs (employment_id)");
                } catch (\Throwable $e2) {}
            }
            // employer_unlocks
            $db->execute("
                CREATE TABLE IF NOT EXISTS employer_unlocks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employment_id INT NOT NULL,
                    employer_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    currency VARCHAR(10) DEFAULT 'INR',
                    status VARCHAR(50) DEFAULT 'pending',
                    unlocked_at DATETIME NULL,
                    invoice_number VARCHAR(50) NULL,
                    invoice_url VARCHAR(512) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_emp_unlock (employment_id),
                    INDEX idx_employer (employer_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (\Throwable $t) {
            error_log("Employment verification ensureTables error: " . $t->getMessage());
        }
    }

    public static function ensureSchema(): void
    {
        self::ensureTables();
    }

    public static function createRecord(int $candidateId, array $data): int
    {
        self::ensureTables();
        $db = Database::getInstance();
        $startInput = trim((string)($data['start_date'] ?? ''));
        $endInput = trim((string)($data['end_date'] ?? ''));
        $params = [
            'cid' => $candidateId,
            'company' => trim((string)($data['company_name'] ?? '')),
            'designation' => trim((string)($data['designation'] ?? '')),
            'empid' => trim((string)($data['employee_id'] ?? '')),
            'start' => ($startInput !== '' ? $startInput : null),
            'end' => ($endInput !== '' ? $endInput : null),
            'consent' => !empty($data['consent']) ? 1 : 0,
            'consent_at' => !empty($data['consent']) ? date('Y-m-d H:i:s') : null
        ];
        // Soft-dedupe: if a recent record for same candidate+company exists (regardless of dates), reuse it and optionally update missing fields
        try {
            $recent = $db->fetchOne("
                SELECT id, designation, employee_id, start_date, end_date 
                FROM employment_records 
                WHERE candidate_id = :cid 
                  AND company_name = :company 
                ORDER BY created_at DESC 
                LIMIT 1
            ", ['cid' => $params['cid'], 'company' => $params['company']]);
            if ($recent) {
                $rid = (int)$recent['id'];
                $createdWithin = $db->fetchOne("
                    SELECT (created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)) AS recent 
                    FROM employment_records WHERE id = :id
                ", ['id' => $rid]);
                $isRecent = ((int)($createdWithin['recent'] ?? 0)) === 1;
                if ($isRecent) {
                    $upd = [];
                    $uparams = ['id' => $rid];
                    if (!empty($params['designation']) && empty($recent['designation'])) { $upd[] = "designation = :designation"; $uparams['designation'] = $params['designation']; }
                    if (!empty($params['empid']) && empty($recent['employee_id'])) { $upd[] = "employee_id = :empid"; $uparams['empid'] = $params['empid']; }
                    if (!empty($params['start']) && empty($recent['start_date'])) { $upd[] = "start_date = :start"; $uparams['start'] = $params['start']; }
                    if (!empty($params['end']) && empty($recent['end_date'])) { $upd[] = "end_date = :end"; $uparams['end'] = $params['end']; }
                    if (!empty($params['consent']) && (int)$params['consent'] === 1) { $upd[] = "consent_given = 1, consent_at = :consent_at"; $uparams['consent_at'] = $params['consent_at']; }
                    if (!empty($upd)) {
                        $db->query("UPDATE employment_records SET " . implode(', ', $upd) . " WHERE id = :id", $uparams);
                    }
                    return $rid;
                }
            }
        } catch (\Throwable $e) {}
        try {
            $existing = $db->fetchOne("
                SELECT id 
                FROM employment_records 
                WHERE candidate_id = :cid 
                  AND company_name = :company 
                  AND ((:start IS NULL AND start_date IS NULL) OR start_date = :start)
                  AND ((:end IS NULL AND end_date IS NULL) OR end_date = :end)
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                ORDER BY id DESC
                LIMIT 1
            ", $params);
            if ($existing && (int)$existing['id'] > 0) {
                return (int)$existing['id'];
            }
        } catch (\Throwable $e) {}
        $sql = "INSERT INTO employment_records (candidate_id, company_name, designation, employee_id, start_date, end_date, consent_given, consent_at, created_at) VALUES (:cid, :company, :designation, :empid, :start, :end, :consent, :consent_at, NOW())";
        $db->query($sql, $params);
        $id = (int)$db->lastInsertId();
        AuditLog::log('employment_record', $id, 'created', $candidateId, $data);
        return $id;
    }

    public static function uploadDocument(int $employmentId, string $docType, array $file): array
    {
        self::ensureTables();
        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'application/octet-stream'
        ];
        $mime = (string)($file['type'] ?? '');
        $size = (int)($file['size'] ?? 0);
        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg'];
        if ((!in_array($mime, $allowed) || !in_array($ext, $allowedExts, true)) || $size <= 0 || $size > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'invalid_file'];
        }
        $tmp = (string)$file['tmp_name'];
        if (!is_file($tmp)) {
            return ['success' => false, 'error' => 'upload_failed'];
        }
        $hash = hash_file('sha256', $tmp);
        $storage = new Storage();
        $path = 'uploads/employment_docs/' . $employmentId;
        $stored = $storage->store($file, $path);
        if (!$stored) {
            return ['success' => false, 'error' => 'store_failed'];
        }
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO employment_documents (employment_id, doc_type, file_path, file_hash, mime_type, size_bytes, metadata, uploaded_at) VALUES (:eid, :type, :path, :hash, :mime, :size, :meta, NOW())",
            [
                'eid' => $employmentId,
                'type' => $docType,
                'path' => '/' . ltrim($stored, '/'),
                'hash' => $hash,
                'mime' => $mime,
                'size' => $size,
                'meta' => json_encode(['original_name' => (string)($file['name'] ?? ''), 'uploaded_ip' => $_SERVER['REMOTE_ADDR'] ?? null])
            ]
        );
        $docId = (int)$db->lastInsertId();
        AuditLog::log('employment_document', $docId, 'uploaded', null, ['employment_id' => $employmentId, 'doc_type' => $docType]);
        return [
            'success' => true,
            'document_id' => $docId,
            'file_path' => $stored,
            'file_url' => '/' . ltrim($stored, '/'),
            'file_name' => (string)($file['name'] ?? basename($stored))
        ];
    }

    public static function storeExtractedText(int $documentId, string $text, string $language = 'en'): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO employment_document_texts (document_id, extracted_text, language, created_at) VALUES (:doc, :txt, :lang, NOW())",
            ['doc' => $documentId, 'txt' => $text, 'lang' => $language]
        );
    }

    public static function createHrRequest(int $employmentId, array $data): array
    {
        self::ensureTables();
        $email = strtolower(trim((string)($data['hr_email'] ?? '')));
        if (!self::isCorporateEmail($email)) {
            return ['success' => false, 'error' => 'invalid_email_domain'];
        }
        $db = Database::getInstance();
        // Prevent rapid duplicates: check pending/email_sent within last 24h or not expired
        $existing = $db->fetchOne("
            SELECT id, status, expires_at, created_at 
            FROM verification_requests 
            WHERE employment_id = :eid 
              AND status IN ('pending','email_sent')
            ORDER BY created_at DESC
            LIMIT 1
        ", ['eid' => $employmentId]);
        if ($existing) {
            $expiresAt = strtotime((string)($existing['expires_at'] ?? ''));
            $createdAt = strtotime((string)($existing['created_at'] ?? ''));
            $recent = $createdAt && ($createdAt > strtotime('-24 hours'));
            $notExpired = $expiresAt && $expiresAt > time();
            if ($recent || $notExpired) {
                return ['success' => false, 'error' => 'already_sent'];
            }
        }
        $token = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', strtotime('+72 hours'));
        $db->query(
            "INSERT INTO verification_requests (employment_id, hr_email, hr_phone, manager_email, company_website, cin, gst, token, expires_at, status, created_at) VALUES (:eid, :email, :phone, :manager, :web, :cin, :gst, :tok, :exp, 'pending', NOW())",
            [
                'eid' => $employmentId,
                'email' => $email,
                'phone' => trim((string)($data['hr_phone'] ?? '')),
                'manager' => trim((string)($data['manager_email'] ?? '')),
                'web' => trim((string)($data['company_website'] ?? '')),
                'cin' => trim((string)($data['cin'] ?? '')),
                'gst' => trim((string)($data['gst'] ?? '')),
                'tok' => $token,
                'exp' => $expires
            ]
        );
        $reqId = (int)$db->lastInsertId();
        try {
            $ok = self::sendHrEmail($employmentId, $email, $token);
        } catch (\Throwable $e) {
            $ok = false;
            $db->query("UPDATE verification_requests SET status = 'failed' WHERE employment_id = :eid AND token = :tok", [
                'eid' => $employmentId,
                'tok' => $token
            ]);
            error_log("Employment HR email send error: " . $e->getMessage());
        }
        AuditLog::log('verification_request', $reqId, $ok ? 'email_sent' : 'email_failed', null, ['hr_email' => $email]);
        if (!$ok) {
            return ['success' => false, 'error' => 'send_failed', 'token' => $token];
        }
        return ['success' => true, 'token' => $token];
    }

    private static function sendHrEmail(int $employmentId, string $to, string $token): bool
    {
        $db = Database::getInstance();
        $row = $db->fetchOne("
            SELECT er.*, c.full_name, c.user_id
            FROM employment_records er 
            INNER JOIN candidates c ON er.candidate_id = c.id 
            WHERE er.id = :id
        ", ['id' => $employmentId]);
        $candidateName = trim((string)($row['full_name'] ?? ''));
        $base = (string)($_ENV['MAIL_LINK_BASE'] ?? ($_ENV['APP_URL'] ?? ''));
        if (empty($base)) {
            $fromEmail = (string)($_ENV['MAIL_FROM_ADDRESS'] ?? '');
            $domain = '';
            if (strpos($fromEmail, '@') !== false) {
                $domain = substr($fromEmail, strrpos($fromEmail, '@') + 1);
            }
            $base = $domain ? ('https://' . $domain) : 'http://localhost:8000';
        }
        if (stripos($base, 'http://') !== 0 && stripos($base, 'https://') !== 0) {
            $base = 'https://' . ltrim($base, '/');
        }
        $appUrl = rtrim($base, '/');
        $link = $appUrl . '/hr/verify?token=' . urlencode($token);
        $docs = $db->fetchAll("SELECT doc_type, file_path FROM employment_documents WHERE employment_id = :id ORDER BY uploaded_at ASC", ['id' => $employmentId]);
        $docLinks = [];
        $attachments = [];
        foreach ($docs as $d) {
            $docLinks[] = [
                'type' => (string)$d['doc_type'],
                'url'  => (string)$d['file_path'],
            ];
            $url = (string)$d['file_path'];
            $fname = basename(parse_url($url, PHP_URL_PATH) ?: ($url ?: 'document'));
            $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            $mime = 'application/octet-stream';
            if ($ext === 'pdf') $mime = 'application/pdf';
            if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
            if ($ext === 'png') $mime = 'image/png';
            $path = null;
            if (!empty($url) && str_starts_with($url, '/')) {
                $pathCandidate = __DIR__ . '/../../public' . $url;
                if (file_exists($pathCandidate)) { $path = $pathCandidate; }
            }
            $att = ['name' => $fname, 'mime' => $mime];
            if ($path) { 
                $att['path'] = $path; 
            } else { 
                $att['url'] = (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) ? $url : $appUrl . $url; 
            }
            $attachments[] = $att;
        }
        try {
            $candRow = $db->fetchOne("SELECT verification_data FROM candidates WHERE id = :cid", ['cid' => (int)($row['candidate_id'] ?? 0)]);
            if (!empty($candRow['verification_data'])) {
                $vdata = json_decode((string)$candRow['verification_data'], true) ?: [];
                $emps = is_array($vdata['employments'] ?? null) ? $vdata['employments'] : [];
                foreach ($emps as $e) {
                    $match = ((int)($e['employment_id'] ?? 0) === (int)$employmentId);
                    if (!$match) {
                        $cmpA = strtolower(trim((string)($e['company'] ?? '')));
                        $cmpB = strtolower(trim((string)($row['company_name'] ?? '')));
                        $match = ($cmpA && $cmpB && $cmpA === $cmpB);
                    }
                    if ($match && !empty($e['documents']) && is_array($e['documents'])) {
                        foreach ($e['documents'] as $t => $u) {
                            if (!$u) continue;
                            $docLinks[] = ['type' => (string)$t, 'url' => (string)$u];
                            $fname = basename(parse_url((string)$u, PHP_URL_PATH) ?: ((string)$u ?: 'document'));
                            $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                            $mime = 'application/octet-stream';
                            if ($ext === 'pdf') $mime = 'application/pdf';
                            if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
                            if ($ext === 'png') $mime = 'image/png';
                            $path = null;
                            if (!empty($u) && str_starts_with((string)$u, '/')) {
                                $pathCandidate = __DIR__ . '/../../public' . (string)$u;
                                if (file_exists($pathCandidate)) { $path = $pathCandidate; }
                            }
                            $att = ['name' => $fname, 'mime' => $mime];
                            if ($path) { 
                                $att['path'] = $path; 
                            } else { 
                                $att['url'] = (stripos((string)$u, 'http://') === 0 || stripos((string)$u, 'https://') === 0) ? (string)$u : $appUrl . (string)$u; 
                            }
                            $attachments[] = $att;
                        }
                    }
                }
            }
        } catch (\Throwable $ignore) {}
        $subject = 'Employment Verification – ' . ($candidateName ?: 'Candidate');
        $payload = [
            'subject' => $subject,
            'candidate_name' => $candidateName,
            'company_name' => (string)($row['company_name'] ?? ''),
            'employee_id' => (string)($row['employee_id'] ?? ''),
            'designation' => (string)($row['designation'] ?? ''),
            'period' => trim((string)($row['start_date'] ?? '')) . ' to ' . trim((string)($row['end_date'] ?? '')),
            'secure_link' => $link,
            'documents' => array_map(function($d) use ($appUrl){
                $u = (string)($d['url'] ?? '');
                if (stripos($u, '/storage/uploads/') === 0) {
                    $u = '/uploads/' . ltrim(substr($u, strlen('/storage/uploads/')), '/');
                }
                if (!empty($u) && str_starts_with($u, '/')) {
                    $u = $appUrl . $u;
                }
                return ['type' => (string)($d['type'] ?? ''), 'url' => $u];
            }, $docLinks),
            'attachments' => $attachments,
            'employment_id' => (int)($row['id'] ?? $employmentId),
            'candidate_user_id' => (int)($row['user_id'] ?? 0),
        ];
        $ok = NotificationService::sendEmail($to, $subject, 'hr_verification_request', $payload);
        $db->query("UPDATE verification_requests SET status = :st WHERE employment_id = :eid AND token = :tok", [
            'eid' => $employmentId,
            'tok' => $token,
            'st'  => $ok ? 'email_sent' : 'failed'
        ]);
        return $ok;
    }

    public static function recordHrResponse(string $token, array $data, string $ip): bool
    {
        $db = Database::getInstance();
        $req = $db->fetchOne("SELECT * FROM verification_requests WHERE token = :tok LIMIT 1", ['tok' => $token]);
        if (!$req) return false;
        $expired = strtotime((string)($req['expires_at'] ?? '')) < time();
        if ($expired) return false;
        $status = (string)($data['status'] ?? 'verified');
        $db->query(
            "INSERT INTO verification_responses (request_id, status, confirmed_working, duration_text, designation, rehire_eligibility, misconduct, remarks, responder_ip, responded_at) VALUES (:rid, :status, :working, :dur, :desig, :rehire, :mis, :remarks, :ip, NOW())",
            [
                'rid' => (int)$req['id'],
                'status' => $status,
                'working' => !empty($data['confirmed_working']) ? 1 : 0,
                'dur' => trim((string)($data['duration_text'] ?? '')),
                'desig' => trim((string)($data['designation'] ?? '')),
                'rehire' => (string)($data['rehire_eligibility'] ?? 'unknown'),
                'mis' => !empty($data['misconduct']) ? 1 : 0,
                'remarks' => trim((string)($data['remarks'] ?? '')),
                'ip' => $ip
            ]
        );
        self::applyLevel2Status((int)$req['employment_id'], $status);
        AuditLog::log('verification_request', (int)$req['id'], 'hr_responded', null, ['status' => $status]);
        return true;
    }

    private static function applyLevel2Status(int $employmentId, string $status): void
    {
        $db = Database::getInstance();
        $db->query("UPDATE employment_records SET status_level2 = :s WHERE id = :id", ['s' => $status, 'id' => $employmentId]);
        self::recalculateRisk($employmentId);
    }

    public static function autoFlagLevel1(int $employmentId, array $signals): void
    {
        $db = Database::getInstance();
        $db->query("UPDATE employment_records SET status_level1 = 'auto_flagged' WHERE id = :id", ['id' => $employmentId]);
        $db->query("INSERT INTO verification_logs (employment_id, event, metadata, created_at) VALUES (:id, 'auto_flag', :meta, NOW())", ['id' => $employmentId, 'meta' => json_encode($signals)]);
    }

    public static function approveLevel1(int $employmentId): void
    {
        Database::getInstance()->query("UPDATE employment_records SET status_level1 = 'approved' WHERE id = :id", ['id' => $employmentId]);
    }

    public static function rejectLevel1(int $employmentId): void
    {
        Database::getInstance()->query("UPDATE employment_records SET status_level1 = 'rejected' WHERE id = :id", ['id' => $employmentId]);
        self::setOverallStatus($employmentId, 'not_verified');
    }

    public static function advancedChecks(int $employmentId, array $data): void
    {
        $db = Database::getInstance();
        $pass = (bool)($data['pass'] ?? false);
        $db->query("UPDATE employment_records SET status_level3 = :s WHERE id = :id", ['s' => $pass ? 'checks_passed' : 'checks_failed', 'id' => $employmentId]);
        self::recalculateRisk($employmentId, $data);
    }

    public static function recalculateRisk(int $employmentId, array $extra = []): void
    {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM employment_records WHERE id = :id", ['id' => $employmentId]);
        $score = 0;
        if ((string)($row['status_level2'] ?? '') === 'verified') $score += 50;
        $ocrMatch = (bool)($extra['ocr_match'] ?? false);
        if ($ocrMatch) $score += 20;
        $domainMatched = (bool)($extra['domain_matched'] ?? false);
        if ($domainMatched) $score += 10;
        if ((string)($row['status_level2'] ?? '') === 'no_response') $score -= 20;
        $metaSuspicious = (bool)($extra['metadata_suspicious'] ?? false);
        if ($metaSuspicious) $score -= 30;
        $dateOverlap = (bool)($extra['date_overlap'] ?? false);
        if ($dateOverlap) $score -= 25;
        $category = $score >= 70 ? 'fully_verified' : ($score >= 40 ? 'partially_verified' : 'unverified');
        $db->query("INSERT INTO verification_scores (employment_id, score, category, breakdown, calculated_at) VALUES (:id, :score, :cat, :bd, NOW())", [
            'id' => $employmentId,
            'score' => $score,
            'cat' => $category,
            'bd' => json_encode($extra)
        ]);
        $db->query("UPDATE employment_records SET risk_score = :score, risk_category = :cat WHERE id = :id", [
            'score' => $score, 'cat' => $category, 'id' => $employmentId
        ]);
    }

    public static function setOverallStatus(int $employmentId, string $status): void
    {
        $isVerified = ($status === 'verified') ? 1 : 0;
        Database::getInstance()->query(
            "UPDATE employment_records 
             SET status_overall = :s, 
                 verification_date = NOW(), 
                 verified_badge = CASE WHEN :isv = 1 THEN 1 ELSE verified_badge END 
             WHERE id = :id",
            ['s' => $status, 'isv' => $isVerified, 'id' => $employmentId]
        );
    }

    public static function createUnlock(int $employmentId, int $employerId): int
    {
        $price = (float)(SystemSetting::get('employment_verification_price', '999'));
        $db = Database::getInstance();
        $db->query("INSERT INTO employer_unlocks (employment_id, employer_id, amount, currency, status, created_at) VALUES (:eid, :employer, :amt, 'INR', 'pending', NOW())", [
            'eid' => $employmentId,
            'employer' => $employerId,
            'amt' => $price
        ]);
        return (int)$db->lastInsertId();
    }

    public static function markUnlockPaid(int $unlockId, string $invoiceNumber, string $invoiceUrl): void
    {
        Database::getInstance()->query("UPDATE employer_unlocks SET status = 'paid', unlocked_at = NOW(), invoice_number = :num, invoice_url = :url WHERE id = :id", [
            'num' => $invoiceNumber, 'url' => $invoiceUrl, 'id' => $unlockId
        ]);
    }

    public static function setConsent(int $employmentId, bool $consent): void
    {
        Database::getInstance()->query(
            "UPDATE employment_records SET consent_given = :c, consent_at = :at WHERE id = :id",
            [
                'c' => $consent ? 1 : 0,
                'at' => $consent ? date('Y-m-d H:i:s') : null,
                'id' => $employmentId
            ]
        );
    }

    private static function isCorporateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        $domain = substr($email, strrpos($email, '@') + 1);
        $free = ['gmail.com','yahoo.com','rediffmail.com','outlook.com','hotmail.com','proton.me','icloud.com','zoho.com'];
        return !in_array($domain, $free);
    }
}
