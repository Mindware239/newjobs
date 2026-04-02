<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\BulkUploadAccount;

class BulkUploadersController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $rows = BulkUploadAccount::all();
        $response->view('admin/bulk_uploaders/index', ['items' => $rows, 'title' => 'Bulk Uploaders'], 200, 'admin/layout');
    }
    
    public function batches(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $acc = BulkUploadAccount::find($id);
        if (!$acc) { $response->redirect('/admin/bulk-uploaders'); return; }
        $batches = $db->fetchAll("SELECT id, total_files, processed_files, failed_files, status, created_at, completed_at FROM resume_batches WHERE bulk_account_id = :id ORDER BY id DESC", ['id' => $id]);
        foreach ($batches as &$b) {
            $stats = $db->fetchAll("SELECT status, COUNT(*) c FROM resume_files WHERE batch_id = :bid GROUP BY status", ['bid' => (int)$b['id']]);
            $processed = 0; $failed = 0; $pending = 0;
            foreach ($stats as $s) {
                if (($s['status'] ?? '') === 'processed') $processed += (int)$s['c'];
                elseif (($s['status'] ?? '') === 'failed') $failed += (int)$s['c'];
                else $pending += (int)$s['c'];
            }
            $b['processed_files'] = $processed;
            $b['failed_files'] = $failed;
            $b['pending_files'] = $pending;
            if ($pending === 0) {
                $doneAt = $db->fetchOne("SELECT MAX(processed_at) AS ts FROM resume_files WHERE batch_id = :bid AND processed_at IS NOT NULL", ['bid' => (int)$b['id']]);
                if (!empty($doneAt['ts'])) {
                    $b['completed_at'] = $doneAt['ts'];
                }
            }
        }
        $files = $db->fetchAll("SELECT rf.* FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id ORDER BY rf.id DESC LIMIT 200", ['id' => $id]);
        $agg = $db->fetchAll("SELECT rf.status AS status, COUNT(*) c FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id GROUP BY rf.status", ['id' => $id]);
        $aggProcessed = 0; $aggFailed = 0; $aggPending = 0;
        foreach ($agg as $row) {
            if (($row['status'] ?? '') === 'processed') $aggProcessed += (int)$row['c'];
            elseif (($row['status'] ?? '') === 'failed') $aggFailed += (int)$row['c'];
            else $aggPending += (int)$row['c'];
        }
        $aggTotal = $aggProcessed + $aggFailed + $aggPending;
        $remaining = max(0, (int)($acc->attributes['limit_total'] ?? 0) - (int)($acc->attributes['limit_used'] ?? 0));
        $q = trim((string)$request->get('q', ''));
        $params = ['id' => $id];
        $where = "c.profile_status = 'published' AND c.id IN (SELECT DISTINCT rf.candidate_id FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id AND rf.candidate_id IS NOT NULL)";
        if ($q !== '') {
            $where .= " AND (LOWER(c.full_name) LIKE :q OR LOWER(u.email) LIKE :q OR u.phone LIKE :q)";
            $params['q'] = '%' . strtolower($q) . '%';
        }
        $candidates = $db->fetchAll("SELECT c.id, c.full_name, c.city, c.created_at, u.email, u.phone, u.id AS user_id FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE {$where} ORDER BY c.id DESC LIMIT 200", $params);
        // Attach last email notification status for published candidates
        if (!empty($candidates)) {
            $userIds = array_column($candidates, 'user_id');
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            try {
                $rows = $db->fetchAll(
                    "SELECT user_id, status, template_key, event_type, created_at, error_message 
                     FROM notification_logs 
                     WHERE channel = 'email' 
                     AND user_id IN ($placeholders)
                     ORDER BY created_at DESC",
                     $userIds
                );
                $latest = [];
                foreach ($rows as $r) {
                    $uid = (int)($r['user_id'] ?? 0);
                    if ($uid > 0 && !isset($latest[$uid])) {
                        $latest[$uid] = $r;
                    }
                }
                foreach ($candidates as &$c) {
                    $uid = (int)($c['user_id'] ?? 0);
                    $log = $latest[$uid] ?? null;
                    $c['email_status'] = $log['status'] ?? null;
                    $c['email_last_at'] = $log['created_at'] ?? null;
                    $c['email_error'] = $log['error_message'] ?? null;
                }
                unset($c);
            } catch (\Throwable $e) {
                // ignore log enrichment failures
            }
        }
        if ($aggPending > 0 && !\App\Core\RedisClient::getInstance()->isAvailable()) {
            try {
                $w = new \App\Workers\ResumeParseWorker();
                $w->process(['limit' => min($aggPending, 100)]);
                // Refresh latest stats and files after processing
                $files = $db->fetchAll("SELECT rf.* FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id ORDER BY rf.id DESC LIMIT 200", ['id' => $id]);
                $agg = $db->fetchAll("SELECT rf.status AS status, COUNT(*) c FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id GROUP BY rf.status", ['id' => $id]);
                $aggProcessed = 0; $aggFailed = 0; $aggPending = 0;
                foreach ($agg as $row) {
                    if (($row['status'] ?? '') === 'processed') $aggProcessed += (int)$row['c'];
                    elseif (($row['status'] ?? '') === 'failed') $aggFailed += (int)$row['c'];
                    else $aggPending += (int)$row['c'];
                }
                $aggTotal = $aggProcessed + $aggFailed + $aggPending;
            } catch (\Throwable $e) {
                error_log('Admin batches parse fallback failed: ' . $e->getMessage());
            }
        }
        $viewTab = trim((string)$request->get('view', ''));
        $response->view('admin/bulk_uploaders/batches', [
            'title' => 'Bulk Uploads',
            'account' => $acc,
            'batches' => $batches,
            'files' => $files,
            'summary' => [
                'total' => $aggTotal,
                'processed' => $aggProcessed,
                'failed' => $aggFailed,
                'pending' => $aggPending,
                'remaining' => $remaining
            ],
            'candidates' => $candidates,
            'search' => $q,
            'view' => $viewTab
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'GET') {
            $response->view('admin/bulk_uploaders/create', ['title' => 'Create Bulk Uploader'], 200, 'admin/layout');
            return;
        }
        // Read POST body, not query string
        $name = trim((string)$request->post('name', ''));
        $username = trim((string)$request->post('username', ''));
        $password = (string)$request->post('password', '');
        $type = trim((string)$request->post('type', ''));
        $limitTotal = (int)$request->post('limit_total', 0);
        $expiresAt = trim((string)$request->post('expires_at', ''));
        if ($expiresAt !== '') {
            $dt = null;
            if (preg_match('/^(\d{2})[-\/](\d{2})[-\/](\d{4})$/', $expiresAt, $m)) {
                $dt = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
            } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $expiresAt, $m)) {
                $dt = $expiresAt;
            }
            $expiresAt = $dt ?: '';
        }
        $status = trim((string)$request->post('status', 'active'));
        if ($name === '' || $username === '' || $password === '' || $limitTotal <= 0) {
            $response->view('admin/bulk_uploaders/create', [
                'error' => 'Invalid input: name, username, password required; upload limit must be > 0',
                'title' => 'Create Bulk Uploader'
            ], 200, 'admin/layout');
            return;
        }
        $exists = BulkUploadAccount::where('username', '=', $username)->first();
        if ($exists) {
            $response->view('admin/bulk_uploaders/create', ['error' => 'Username already exists', 'title' => 'Create Bulk Uploader'], 200, 'admin/layout');
            return;
        }
        $acc = new BulkUploadAccount();
        $acc->fill([
            'name' => $name,
            'username' => $username,
            'type' => $type,
            'limit_total' => $limitTotal,
            'limit_used' => 0,
            'expires_at' => $expiresAt !== '' ? ($expiresAt . ' 00:00:00') : null,
            'status' => $status
        ]);
        $acc->setPassword($password);
        $ok = $acc->save();

        // Send welcome email
        if ($ok) {
            \App\Services\NotificationService::queueEmail(
                $username, // The 'to' address
                'bulk_uploader_welcome', // The template key
                [ // The data for the template
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                ],
                'Your Bulk Uploader Account is Ready' // The subject
            );
        }

        if (!$ok) {
            error_log("BulkUploadersController::create save failed");
            $response->view('admin/bulk_uploaders/create', [
                'error' => 'Failed to create account. Please try again.',
                'title' => 'Create Bulk Uploader'
            ], 200, 'admin/layout');
            return;
        }
        $response->redirect('/admin/bulk-uploaders');
    }

    public function toggleStatus(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $row = BulkUploadAccount::find($id);
        if (!$row) { $response->redirect('/admin/bulk-uploaders'); return; }
        $newStatus = ($row->attributes['status'] ?? 'active') === 'active' ? 'suspended' : 'active';
        $row->fill(['status' => $newStatus]);
        $row->save();
        $response->redirect('/admin/bulk-uploaders');
    }

    public function resetLimit(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $db->execute("UPDATE bulk_upload_accounts SET limit_used = 0 WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/bulk-uploaders');
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $newPassword = (string)$request->post('password', '');
        if (strlen($newPassword) < 6) {
            $rows = BulkUploadAccount::all();
            $response->view('admin/bulk_uploaders/index', [
                'items' => $rows,
                'title' => 'Bulk Uploaders',
                'error' => 'Password must be at least 6 characters'
            ], 200, 'admin/layout');
            return;
        }
        $row = BulkUploadAccount::find($id);
        if (!$row) { $response->redirect('/admin/bulk-uploaders'); return; }
        $row->setPassword($newPassword);
        $row->save();
        $response->redirect('/admin/bulk-uploaders');
    }

    public function addCredits(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $add = (int)$request->post('add', 0);
        if ($add <= 0) { $response->redirect('/admin/bulk-uploaders'); return; }
        $row = BulkUploadAccount::find($id);
        if (!$row) { $response->redirect('/admin/bulk-uploaders'); return; }
        $currentTotal = (int)($row->attributes['limit_total'] ?? 0);
        $row->fill(['limit_total' => $currentTotal + $add]);
        $row->save();
        $response->redirect('/admin/bulk-uploaders');
    }

    public function deleteAccount(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $acc = BulkUploadAccount::find($id);
        if (!$acc) { $response->redirect('/admin/bulk-uploaders'); return; }
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT rf.* FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id", ['id' => $id]);
        foreach ($rows as $rf) {
            $path = (string)($rf['filepath'] ?? '');
            if ($path !== '' && file_exists($path)) {
                @unlink($path);
            }
        }
        $db->execute("DELETE rf FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id", ['id' => $id]);
        $db->execute("DELETE FROM resume_batches WHERE bulk_account_id = :id", ['id' => $id]);
        $db->execute("DELETE FROM bulk_upload_accounts WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/bulk-uploaders');
    }

    public function downloadFile(Request $request, Response $response): void
    {
        $fileId = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $f = $db->fetchOne("SELECT * FROM resume_files WHERE id = :id", ['id' => $fileId]) ?? [];
        if (!$f) { $response->setStatusCode(404); $response->setBody('File not found'); return; }
        $path = (string)($f['filepath'] ?? '');
        $name = (string)($f['filename'] ?? basename($path));
        $response->download($path, $name);
    }

    public function showFile(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $f = $db->fetchOne("SELECT rf.*, rb.bulk_account_id FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rf.id = :id", ['id' => $id]) ?? [];
        if (!$f) { $response->redirect('/admin/bulk-uploaders'); return; }
        $parsed = [];
        if (!empty($f['parsed_data'])) {
            $json = json_decode((string)$f['parsed_data'], true);
            if (is_array($json)) $parsed = $json;
        }
        $candidate = null;
        if (!empty($f['candidate_id'])) {
            $candidate = \App\Models\Candidate::find((int)$f['candidate_id']);
        }
        $response->view('admin/bulk_uploaders/file', [
            'title' => 'Resume Details',
            'file' => $f,
            'parsed' => $parsed,
            'candidate' => $candidate
        ], 200, 'admin/layout');
    }

    public function approveFile(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $f = $db->fetchOne("SELECT * FROM resume_files WHERE id = :id", ['id' => $id]) ?? [];
        if (!$f) { $response->redirect('/admin/bulk-uploaders'); return; }
        $candidateId = (int)($f['candidate_id'] ?? 0);
        if ($candidateId <= 0) {
            try {
                $fileModel = new \App\Models\ResumeFile($f);
                $path = (string)($f['filepath'] ?? '');
                $parsed = [];
                if (!empty($f['parsed_data'])) {
                    $json = json_decode((string)$f['parsed_data'], true);
                    if (is_array($json)) $parsed = $json;
                }
                if (empty($parsed) && file_exists($path)) {
                    $parser = new \App\Services\ResumeParserService();
                    $parsed = $parser->parse($path);
                }
                if (!empty($parsed)) {
                    $creator = new \App\Services\CandidateCreationService();
                    $res = $creator->createOrLinkFromParsedResume($parsed, $fileModel, ['created_by' => 'bulk', 'source' => 'bulk_upload']);
                    $newCandidate = $res['candidate'] ?? null;
                    if ($newCandidate) {
                        $candidateId = (int)$newCandidate->id;
                        $db->execute("UPDATE resume_files SET candidate_id = :cid, parsed_data = :pd, processed_at = :ts WHERE id = :id", [
                            'cid' => $candidateId,
                            'pd' => json_encode($parsed),
                            'ts' => date('Y-m-d H:i:s'),
                            'id' => $id
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // proceed to mark success even if parsing failed
            }
        }
        // Publish candidate if now available
        if ($candidateId > 0) {
            $cand = \App\Models\Candidate::find($candidateId);
            if ($cand) {
                $cand->fill(['profile_status' => 'published', 'visibility' => 'public']);
                $cand->save();
                $user = $cand->user();
                if ($user) {
                    $user->fill(['status' => 'active']);
                    $user->save();
                    try {
                        $appUrl = $_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'http://localhost:8000');
                        $db = Database::getInstance();
                        // Generate password reset token (valid 1h), store in DB and Redis
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
                        $redis = \App\Core\RedisClient::getInstance();
                        if ($redis->isAvailable()) {
                            $redis->set("password_reset:{$token}", [
                                'user_id' => (int)$user->id,
                                'email' => (string)($user->attributes['email'] ?? ''),
                                'expires_at' => $expiresAt
                            ], 3600);
                        }
                        // Remove old tokens and insert new
                        try {
                            $db->query("DELETE FROM password_resets WHERE user_id = :uid OR expires_at < UTC_TIMESTAMP()", ['uid' => (int)$user->id]);
                            $db->query("INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email,:token,:uid,:exp)", [
                                'email' => (string)($user->attributes['email'] ?? ''),
                                'token' => $token,
                                'uid' => (int)$user->id,
                                'exp' => $expiresAt
                            ]);
                        } catch (\Throwable $t) {}

                        // Build absolute URLs
                        $setPasswordUrl = rtrim($appUrl, '/') . '/reset-password?token=' . $token;
                        $profileUrl = rtrim($appUrl, '/') . '/candidate/profile';

                        // Summaries
                        $skills = $cand->skills();
                        $skillsSummary = '';
                        if (is_array($skills) && !empty($skills)) {
                            $names = [];
                            foreach ($skills as $s) {
                                $names[] = is_array($s) ? (string)($s['name'] ?? '') : (string)($s);
                            }
                            $skillsSummary = implode(', ', array_filter($names));
                        }
                        // Experience years (best-effort sum)
                        $exp = $cand->experience();
                        $years = 0.0;
                        if (is_array($exp)) {
                            foreach ($exp as $e) {
                                $start = strtotime((string)($e['start_date'] ?? ''));
                                $endStr = (string)($e['end_date'] ?? '');
                                $end = $endStr ? strtotime($endStr) : time();
                                if ($start && $end && $end > $start) {
                                    $years += ($end - $start) / (365 * 24 * 3600);
                                }
                            }
                        }
                        $experienceYears = $years > 0 ? number_format($years, 1) : '';
                        // Education summary
                        $edu = $cand->education();
                        $eduParts = [];
                        foreach ($edu as $e) {
                            $deg = trim((string)($e['degree'] ?? ''));
                            $inst = trim((string)($e['institution'] ?? ''));
                            $label = $deg !== '' ? $deg : '';
                            if ($inst !== '') {
                                $label = $label !== '' ? ($label . ' - ' . $inst) : $inst;
                            }
                            if ($label !== '') $eduParts[] = $label;
                        }
                        $educationSummary = implode('; ', $eduParts);
                        // Salary range
                        $min = (string)($cand->attributes['expected_salary_min'] ?? '');
                        $max = (string)($cand->attributes['expected_salary_max'] ?? '');
                        $salaryRange = ($min !== '' || $max !== '') ? trim(($min !== '' ? $min : '') . (($min !== '' && $max !== '') ? ' - ' : '') . ($max !== '' ? $max : '')) : '';

                        \App\Services\NotificationService::send(
                            (int)$user->id,
                            'candidate_published',
                            'Profile Published',
                            'Your candidate profile has been published.',
                            [
                                'email_template' => 'candidate_published',
                                'candidate_name' => (string)($cand->attributes['full_name'] ?? ($user->attributes['name'] ?? '')),
                                'candidate_email' => (string)($user->attributes['email'] ?? ''),
                                'candidate_mobile' => (string)($cand->attributes['mobile'] ?? ($user->attributes['phone'] ?? '')),
                                'candidate_city' => (string)($cand->attributes['city'] ?? ''),
                                'candidate_state' => (string)($cand->attributes['state'] ?? ''),
                                'candidate_country' => (string)($cand->attributes['country'] ?? ''),
                                'skills_summary' => $skillsSummary,
                                'experience_years' => $experienceYears,
                                'education_summary' => $educationSummary,
                                'preferred_job_location' => (string)($cand->attributes['preferred_job_location'] ?? ''),
                                'expected_salary_range' => $salaryRange,
                                'notice_period' => (string)($cand->attributes['notice_period'] ?? ''),
                                'set_password_url' => $setPasswordUrl,
                                'profile_url' => $profileUrl
                            ],
                            $profileUrl,
                            ['in_app','email']
                        );
                    } catch (\Throwable $e) {}
                }
            }
            $db->execute("UPDATE resume_files SET status = 'success', failure_reason = NULL WHERE id = :id", ['id' => $id]);
        } else {
            $db->execute("UPDATE resume_files SET status = 'failed', failure_reason = COALESCE(failure_reason,'linking_failed') WHERE id = :id", ['id' => $id]);
        }
        $response->redirect('/admin/resumes/' . $id);
    }

    public function rejectFile(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $reason = trim((string)$request->post('reason', 'rejected_by_admin'));
        $db = Database::getInstance();
        $f = $db->fetchOne("SELECT * FROM resume_files WHERE id = :id", ['id' => $id]) ?? [];
        if (!$f) { $response->redirect('/admin/bulk-uploaders'); return; }
        $db->execute("UPDATE resume_files SET status = 'failed', failure_reason = :r WHERE id = :id", ['id' => $id, 'r' => $reason]);
        $response->redirect('/admin/resumes/' . $id);
    }
    
    public function exportCandidates(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $q = trim((string)$request->get('q', ''));
        $db = Database::getInstance();
        $params = ['id' => $id];
        $where = "c.source = 'bulk_upload' AND c.profile_status = 'published' AND c.id IN (SELECT DISTINCT rf.candidate_id FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id AND rf.candidate_id IS NOT NULL)";
        if ($q !== '') {
            $where .= " AND (LOWER(c.full_name) LIKE :q OR LOWER(u.email) LIKE :q OR u.phone LIKE :q)";
            $params['q'] = '%' . strtolower($q) . '%';
        }
        $rows = $db->fetchAll("SELECT c.id, c.full_name, c.city, c.created_at, u.email, u.phone FROM candidates c INNER JOIN users u ON u.id = c.user_id WHERE {$where} ORDER BY c.id DESC", $params);
        $filename = 'published_candidates_' . $id . '_' . date('Y-m-d') . '.csv';
        $path = sys_get_temp_dir() . '/' . $filename;
        $fp = fopen($path, 'w');
        fputcsv($fp, ['ID', 'Name', 'Email', 'Phone', 'City', 'Created At']);
        foreach ($rows as $r) {
            fputcsv($fp, [$r['id'], $r['full_name'], $r['email'], $r['phone'], $r['city'], $r['created_at']]);
        }
        fclose($fp);
        $response->download($path, $filename);
    }

    public function deleteFile(Request $request, Response $response): void
    {
        $id = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT rf.*, rb.bulk_account_id FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rf.id = :id", ['id' => $id]) ?? [];
        if (!$row) { $response->redirect('/admin/bulk-uploaders'); return; }
        $path = (string)($row['filepath'] ?? '');
        if ($path !== '' && file_exists($path)) {
            @unlink($path);
        }
        $db->execute("DELETE FROM resume_files WHERE id = :id", ['id' => $id]);
        $accId = (int)($row['bulk_account_id'] ?? 0);
        if ($accId > 0) {
            $response->redirect('/admin/bulk-uploaders/' . $accId . '/batches');
            return;
        }
        $response->redirect('/admin/bulk-uploaders');
    }
}
