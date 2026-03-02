<?php

declare(strict_types=1);

namespace App\Controllers\Bulk;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\BulkUploadAccount;
use App\Models\ResumeBatch;
use App\Models\ResumeFile;

class BulkUploadController extends BaseController
{
    private function currentAccount(): ?BulkUploadAccount
    {
        $id = (int)($_SESSION['bulk_account_id'] ?? 0);
        if ($id <= 0) return null;
        return BulkUploadAccount::find($id);
    }

    public function login(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'GET') {
            $response->view('bulk/login', ['title' => 'Bulk Uploader Login']);
            return;
        }
        $username = trim((string)$request->post('username', ''));
        $password = (string)$request->post('password', '');
        $row = BulkUploadAccount::where('username', '=', $username)->first();
        if (!$row) {
            $response->view('bulk/login', ['error' => 'Invalid credentials']);
            return;
        }
        $acc = $row;
        if (!password_verify($password, $acc->attributes['password_hash'] ?? '')) {
            $response->view('bulk/login', ['error' => 'Invalid credentials']);
            return;
        }
        if (($acc->attributes['status'] ?? 'active') !== 'active') {
            $response->view('bulk/login', ['error' => 'Account suspended']);
            return;
        }
        $exp = $acc->attributes['expires_at'] ?? null;
        if ($exp && strtotime((string)$exp) < time()) {
            $response->view('bulk/login', ['error' => 'Account expired']);
            return;
        }
        $_SESSION['bulk_account_id'] = $acc->id;
        $response->redirect('/bulk/upload');
    }

    public function logout(Request $request, Response $response): void
    {
        unset($_SESSION['bulk_account_id']);
        $response->redirect('/bulk/login');
    }

    public function upload(Request $request, Response $response): void
    {
        $acc = $this->currentAccount();
        if (!$acc) { $response->redirect('/bulk/login'); return; }
        if ($request->getMethod() === 'GET') {
            $remaining = max(0, (int)$acc->attributes['limit_total'] - (int)($acc->attributes['limit_used'] ?? 0));
            $response->view('bulk/upload', ['title' => 'Bulk Resume Upload', 'remaining' => $remaining]);
            return;
        }
        $remaining = max(0, (int)$acc->attributes['limit_total'] - (int)($acc->attributes['limit_used'] ?? 0));
        $files = $_FILES['resumes'] ?? null;
        $count = is_array($files['name'] ?? null) ? count($files['name']) : 0;
        if ($count <= 0) {
            $response->view('bulk/upload', ['error' => 'No files selected', 'remaining' => $remaining]);
            return;
        }
        // Pre-validate files
        $validIdx = [];
        $errorsSummary = [];
        for ($i = 0; $i < $count; $i++) {
            $err = (int)($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            if ($err !== UPLOAD_ERR_OK) {
                $errorsSummary['upload_error'] = ($errorsSummary['upload_error'] ?? 0) + 1;
                continue;
            }
            $name = (string)$files['name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf','doc','docx','zip'])) {
                $errorsSummary['unsupported_type'] = ($errorsSummary['unsupported_type'] ?? 0) + 1;
                continue;
            }
            $validIdx[] = $i;
        }
        if (empty($validIdx)) {
            $msg = 'No valid files. ';
            if (!empty($errorsSummary)) {
                $parts = [];
                foreach ($errorsSummary as $k => $v) { $parts[] = "{$k}: {$v}"; }
                $msg .= implode(', ', $parts);
            }
            $response->view('bulk/upload', ['error' => $msg, 'remaining' => $remaining]);
            return;
        }
        if (count($validIdx) > $remaining) {
            $response->view('bulk/upload', ['error' => 'Upload limit exceeded', 'remaining' => $remaining]);
            return;
        }
        // Create batch only when there is at least one valid file
        $batch = new ResumeBatch();
        $batch->fill([
            'bulk_account_id' => $acc->id,
            'total_files' => count($validIdx),
            'processed_files' => 0,
            'failed_files' => 0,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $batch->save();
        error_log("BulkUpload: created batch {$batch->id} for account {$acc->id}");
        $storageRoot = rtrim((string)($_ENV['STORAGE_PATH'] ?? (dirname(__DIR__, 2) . '/storage/resumes')), '/\\');
        if (!is_dir($storageRoot)) {
            @mkdir($storageRoot, 0777, true);
        }
        $accepted = 0;
        $maxBytes = 10 * 1024 * 1024;
        $finfo = class_exists('\finfo') ? new \finfo(FILEINFO_MIME_TYPE) : null;
        foreach ($validIdx as $i) {
            $tmp = $files['tmp_name'][$i];
            $name = $files['name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext === 'zip') {
                $zip = new \ZipArchive();
                if ($zip->open($tmp) === true) {
                    for ($z = 0; $z < $zip->numFiles; $z++) {
                        $entry = $zip->getNameIndex($z);
                        $zext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                        if (!in_array($zext, ['pdf','doc','docx'])) continue;
                        $stat = $zip->statIndex($z);
                        $zsize = (int)($stat['size'] ?? 0);
                        if ($zsize <= 0 || $zsize > $maxBytes) continue;
                        $contents = $zip->getFromIndex($z);
                        if ($contents === false) continue;
                        if ($finfo) {
                            $mime = $finfo->buffer($contents) ?: '';
                            if (!$this->isAllowedMime($mime, $zext)) continue;
                        }
                        $hash = hash('sha256', $contents);
                        if ($accepted >= $remaining) break;
                        $dest = $storageRoot . '/' . date('Ymd') . '-' . $hash . '.' . $zext;
                        file_put_contents($dest, $contents);
                        $file = new ResumeFile();
                        $file->fill([
                            'batch_id' => $batch->id,
                            'filename' => basename($entry),
                            'filepath' => $dest,
                            'hash' => $hash,
                            'status' => 'pending',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        if ($file->save()) {
                            $accepted++;
                        }
                    }
                    $zip->close();
                }
            } elseif (in_array($ext, ['pdf','doc','docx'])) {
                if ($accepted >= $remaining) break;
                $size = @filesize($tmp);
                if ($size === false || $size > $maxBytes) {
                    $errorsSummary['size_exceeded'] = ($errorsSummary['size_exceeded'] ?? 0) + 1;
                    continue;
                }
                if ($finfo) {
                    $mime = $finfo->file($tmp) ?: '';
                    if (!$this->isAllowedMime($mime, $ext)) {
                        $errorsSummary['invalid_mime'] = ($errorsSummary['invalid_mime'] ?? 0) + 1;
                        continue;
                    }
                }
                $hash = hash_file('sha256', $tmp);
                $dest = $storageRoot . '/' . date('Ymd') . '-' . $hash . '.' . $ext;
                $moved = @move_uploaded_file($tmp, $dest);
                if (!$moved) {
                    $errorsSummary['move_failed'] = ($errorsSummary['move_failed'] ?? 0) + 1;
                    continue;
                }
                $file = new ResumeFile();
                $file->fill([
                    'batch_id' => $batch->id,
                    'filename' => $name,
                    'filepath' => $dest,
                    'hash' => $hash,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                if ($file->save()) {
                    $accepted++;
                }
            }
        }
        $db = Database::getInstance();
        if ($accepted > 0) {
            $db->execute("UPDATE bulk_upload_accounts SET limit_used = COALESCE(limit_used,0) + :c WHERE id = :id", ['c' => $accepted, 'id' => $acc->id]);
        } else {
            error_log("BulkUpload: no files accepted for batch {$batch->id}");
        }
        try {
            \App\Workers\ResumeParseWorker::enqueue(['limit' => 100]);
            if (!\App\Core\RedisClient::getInstance()->isAvailable()) {
                $w = new \App\Workers\ResumeParseWorker();
                $w->process(['limit' => 100]);
            }
        } catch (\Throwable $e) {
            error_log('Failed to enqueue resume parse: ' . $e->getMessage());
            try {
                $w = new \App\Workers\ResumeParseWorker();
                $w->process(['limit' => 50]);
            } catch (\Throwable $e2) {
                error_log('Fallback resume parse failed: ' . $e2->getMessage());
            }
        }
        error_log("BulkUpload: accepted {$accepted} files, remaining before accept {$remaining}");
        if ($accepted <= 0) {
            $remainingNow = max(0, (int)$acc->attributes['limit_total'] - (int)($acc->attributes['limit_used'] ?? 0));
            $msg = 'No valid files accepted';
            if (!empty($errorsSummary)) {
                $parts = [];
                foreach ($errorsSummary as $k => $v) { $parts[] = "{$k}: {$v}"; }
                $msg .= ' (' . implode(', ', $parts) . ')';
            }
            $response->view('bulk/upload', ['error' => $msg, 'remaining' => $remainingNow]);
            return;
        }
        $response->redirect('/bulk/batches/' . $batch->id);
    }

    public function batch(Request $request, Response $response): void
    {
        $acc = $this->currentAccount();
        if (!$acc) { $response->redirect('/bulk/login'); return; }
        $id = (int)$request->param('id', 0);
        $batch = ResumeBatch::find($id);
        if (!$batch || (int)$batch->attributes['bulk_account_id'] !== (int)$acc->id) {
            error_log("BulkUpload: invalid batch {$id} for account {$acc->id}");
            $response->view('bulk/upload', ['error' => 'Invalid batch', 'remaining' => 0]);
            return;
        }
        $files = ResumeFile::where('batch_id', '=', $batch->id)->get();
        $total = count($files);
        $processed = 0;
        $failed = 0;
        foreach ($files as $f) {
            $s = (string)($f->attributes['status'] ?? '');
            if ($s === 'processed') { $processed++; }
            elseif ($s === 'failed') { $failed++; }
        }
        $pending = $total - $processed - $failed;
        $remaining = max(0, (int)$acc->attributes['limit_total'] - (int)($acc->attributes['limit_used'] ?? 0));
        $response->view('bulk/batch', [
            'batch' => $batch,
            'files' => $files,
            'stats' => [
                'total' => $total,
                'processed' => $processed,
                'failed' => $failed,
                'pending' => $pending
            ],
            'remaining' => $remaining
        ]);
    }

    public function files(Request $request, Response $response): void
    {
        $acc = $this->currentAccount();
        if (!$acc) { $response->redirect('/bulk/login'); return; }
        $db = Database::getInstance();
        $files = $db->fetchAll("SELECT rf.* FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id ORDER BY rf.id DESC LIMIT 200", ['id' => (int)$acc->id]);
        $agg = $db->fetchAll("SELECT rf.status AS status, COUNT(*) c FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rb.bulk_account_id = :id GROUP BY rf.status", ['id' => (int)$acc->id]);
        $p=0;$f=0;$pn=0; foreach ($agg as $a){ if (($a['status']??'')==='processed') $p+=(int)$a['c']; elseif (($a['status']??'')==='failed') $f+=(int)$a['c']; else $pn+=(int)$a['c']; }
        $remaining = max(0, (int)$acc->attributes['limit_total'] - (int)($acc->attributes['limit_used'] ?? 0));
        $response->view('bulk/files', [
            'title' => 'My Uploads',
            'files' => $files,
            'stats' => ['processed'=>$p,'failed'=>$f,'pending'=>$pn,'total'=>$p+$f+$pn],
            'remaining' => $remaining
        ]);
    }

    public function download(Request $request, Response $response): void
    {
        $acc = $this->currentAccount();
        if (!$acc) { $response->redirect('/bulk/login'); return; }
        $fileId = (int)$request->param('id', 0);
        $db = Database::getInstance();
        $f = $db->fetchOne("SELECT rf.* FROM resume_files rf INNER JOIN resume_batches rb ON rf.batch_id = rb.id WHERE rf.id = :id AND rb.bulk_account_id = :bid", ['id' => $fileId, 'bid' => (int)$acc->id]) ?? [];
        if (!$f) { $response->setStatusCode(404); $response->setBody('File not found'); return; }
        $response->download((string)$f['filepath'], (string)($f['filename'] ?? basename((string)$f['filepath'])));
    }

    private function isAllowedMime(string $mime, string $ext): bool
    {
        $ext = strtolower($ext);
        if ($ext === 'pdf') {
            return strpos($mime, 'pdf') !== false;
        }
        if ($ext === 'docx') {
            return $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
        if ($ext === 'doc') {
            return $mime === 'application/msword' || $mime === 'application/octet-stream';
        }
        return false;
    }
}
