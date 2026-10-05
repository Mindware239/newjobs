<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Front\SkillDevelopmentController;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\DataCipher;
use App\Models\PortalRegistration;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\MentorMailer;
use App\Services\Registration\MentorMatching;

/**
 * Admin view of all Jobsence ₹155 registrations (skill, internship, jobs, mentors).
 */
class RegistrationsController extends BaseController
{
    private const PER_PAGE = 25;

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }

        $filters = $this->filters($request);
        $page = max(1, (int)$request->get('page', 1));
        $result = PortalRegistration::search($filters, $page, self::PER_PAGE);

        $response->view('admin/registrations/index', [
            'title' => '₹155 Registrations',
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'filters' => $filters,
            'stats' => PortalRegistration::stats(),
            'typeLabels' => $this->typeLabels(),
            'states' => array_column(SkillDevelopmentController::STATES, 0),
            'statuses' => PortalRegistration::STATUSES,
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }

        $reg = PortalRegistration::find((int)$request->param('id'));
        $form = $reg ? FormRegistry::byType((string)$reg['type']) : null;
        if (!$reg || !$form) {
            $response->redirect('/admin/registrations');
            return;
        }

        $response->view('admin/registrations/show', [
            'title' => 'Registration ' . $reg['reg_no'],
            'reg' => $reg,
            'form' => $form,
            'sections' => $this->describe($reg, $form),
            'mentorAssignments' => $reg['type'] === 'skill' ? MentorMatching::assignmentsForCandidate((int)$reg['id'])
                : ($reg['type'] === 'provider' ? MentorMatching::assignmentsForMentor((int)$reg['id']) : []),
            'mentorSuggestions' => ($reg['type'] === 'skill' && $reg['payment_status'] === 'paid') ? MentorMatching::availableMentors($reg, null, 10) : [],
            'statuses' => PortalRegistration::STATUSES,
            'success' => $request->get('success'),
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }

        $id = (int)$request->param('id');
        $before = PortalRegistration::find($id);
        $newStatus = (string)$request->post('status', '');
        PortalRegistration::updateAdmin($id, $newStatus, mb_substr(trim((string)$request->post('admin_notes', '')), 0, 5000));
        $message = 'Updated';

        $after = PortalRegistration::find($id);
        if ($before && $after && $before['status'] !== $after['status'] && $after['payment_status'] === 'paid') {
            MentorMailer::statusChanged($after, (string)$after['status']);
            // Selecting a skill candidate automatically sends a request to the best-matching mentor
            if ($after['type'] === 'skill' && $after['status'] === 'selected') {
                $message .= ' – ' . MentorMatching::request($after, null, 'auto')['message'];
            }
        }
        $response->redirect('/admin/registrations/' . $id . '?success=' . rawurlencode($message));
    }

    /** POST: send a mentor request for a skill candidate (specific mentor or best match). */
    public function mentorRequest(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id');
        $reg = PortalRegistration::find($id);
        if (!$reg) {
            $response->redirect('/admin/registrations');
            return;
        }
        $mentorId = (int)$request->post('mentor_id', 0);
        $result = MentorMatching::request($reg, $mentorId > 0 ? $mentorId : null, 'admin');
        $response->redirect('/admin/registrations/' . $id . '?success=' . rawurlencode($result['message']));
    }

    /** Streams the uploaded resume or demo video. */
    public function file(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }

        $reg = PortalRegistration::find((int)$request->param('id'));
        $kind = (string)$request->param('kind');
        $column = ['resume' => 'resume_path', 'video' => 'video_path'][$kind] ?? null;
        $relative = $reg && $column ? (string)($reg[$column] ?? '') : '';
        if ($reg && in_array($kind, ['photo', 'selfie'], true)) {
            $relative = (string)($reg['details'][$kind . '_path'] ?? '');
        }

        $root = realpath(dirname(__DIR__, 3) . '/storage/uploads/registrations');
        $path = $relative !== '' ? realpath(dirname(__DIR__, 3) . '/' . $relative) : false;
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        $mime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . (str_starts_with($mime, 'video/') || str_starts_with($mime, 'image/') || $mime === 'application/pdf' ? 'inline' : 'attachment')
            . '; filename="' . $reg['reg_no'] . '-' . $request->param('kind') . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function export(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }

        $rows = PortalRegistration::allForExport($this->filters($request));
        $base = ['reg_no', 'type', 'full_name', 'dob', 'gender', 'mobile', 'whatsapp', 'email', 'aadhaar_last4', 'gstin',
            'district', 'city', 'state', 'pincode', 'qualification', 'categories', 'preferred_location',
            'total_amount', 'payment_status', 'razorpay_payment_id', 'paid_at', 'status', 'admin_notes', 'reminder_count', 'created_at'];

        $detailKeys = [];
        foreach ($rows as $r) {
            foreach (array_keys($r['details']) as $k) {
                if ($k !== 'bank_account_enc') {
                    $detailKeys[$k] = true;
                }
            }
        }
        $detailKeys = array_keys($detailKeys);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="registrations-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel shows Hindi correctly
        fputcsv($out, array_merge($base, $detailKeys));
        foreach ($rows as $row) {
            $line = [];
            foreach ($base as $c) {
                $line[] = self::csvSafe((string)($row[$c] ?? ''));
            }
            foreach ($detailKeys as $k) {
                $v = $row['details'][$k] ?? '';
                $line[] = self::csvSafe(is_array($v) ? implode(', ', $v) : (string)$v);
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    /** Builds [section title => [label => value]] for the detail page from the form definition. */
    private function describe(array $reg, array $form): array
    {
        $out = [];
        foreach ($form['sections'] as $section) {
            $rows = [];
            foreach ($section['fields'] as $f) {
                $k = $f['key'];
                $label = $f['label'] ? $f['label'][1] : $form['categories_label'][1];
                $value = match ($f['type']) {
                    'aadhaar' => $reg['aadhaar_last4'] ? 'XXXX XXXX ' . $reg['aadhaar_last4'] : '',
                    'gst' => $reg['gstin'] ?: (!empty($reg['details']['no_gst']) ? 'No GST (declared)' : ''),
                    'file' => $reg['resume_path'] ? '__file:resume' : '',
                    'image', 'selfie' => !empty($reg['details'][$k . '_path']) ? '__file:' . $k : '',
                    'video' => trim(($reg['video_path'] ? '__file:video' : '') . (!empty($reg['details']['video_link']) ? ' ' . $reg['details']['video_link'] : '')),
                    'bank_account' => self::bankAccount($reg),
                    default => in_array($k, FormRegistry::COLUMN_FIELDS, true) ? ($reg[$k] ?? '') : ($reg['details'][$k] ?? ''),
                };
                if (isset($f['options'])) {
                    $value = implode(', ', array_map(static fn($v) => $f['options'][$v][1] ?? $v, (array)$value));
                }
                if (!empty($f['other']) && !empty($reg['details'][$f['other']])) {
                    $value .= ' – ' . $reg['details'][$f['other']];
                }
                if ($k === 'video_link') {
                    continue;
                }
                $rows[$label] = is_array($value) ? implode(', ', $value) : (string)$value;
            }
            $out[$section['title'][1]] = $rows;
        }

        $out['Payment'] = [
            'Amount' => ($reg['currency'] ?? 'INR') === 'USD'
                ? \App\Models\PortalRegistration::money($reg) . ' (outside India, no GST)'
                : '₹' . number_format((float)$reg['total_amount'], 2) . ' (base ₹' . $reg['fee_base'] . ' + GST ₹' . $reg['gst_amount'] . ')',
            'Payment status' => ucfirst((string)$reg['payment_status']),
            'Razorpay order' => (string)$reg['razorpay_order_id'],
            'Razorpay payment' => (string)$reg['razorpay_payment_id'],
            'Paid at' => (string)$reg['paid_at'],
            'Valid until' => (string)($reg['valid_until'] ?? '') !== '' ? $reg['valid_until'] . ($reg['type'] === 'skill' ? ' (3-month registration)' : '') : '',
            'Email OTP verified' => (string)($reg['email_verified_at'] ?? ''),
            'Reminders sent' => (int)$reg['reminder_count'] . ($reg['last_reminder_at'] ? ' (last ' . $reg['last_reminder_at'] . ')' : ''),
            'Form language' => (string)$reg['ui_language'],
            'Submitted' => $reg['created_at'] . ' from ' . $reg['ip_address'],
        ];

        return $out;
    }

    private static function bankAccount(array $reg): string
    {
        $enc = $reg['details']['bank_account_enc'] ?? null;
        if (!$enc) {
            return '';
        }
        try {
            return DataCipher::decrypt((string)$enc) ?? ('XXXX' . ($reg['details']['bank_account_last4'] ?? '') . ' (cannot decrypt – check PORTAL_DATA_KEY)');
        } catch (\Throwable $e) {
            return 'XXXX' . ($reg['details']['bank_account_last4'] ?? '');
        }
    }

    private static function csvSafe(string $v): string
    {
        // Prevent spreadsheet formula injection from user-entered text.
        return preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;
    }

    private function typeLabels(): array
    {
        $out = [];
        foreach (FormRegistry::all() as $form) {
            $out[$form['type']] = $form['title'][1];
        }
        return $out;
    }

    private function filters(Request $request): array
    {
        return [
            'type' => (string)$request->get('type', ''),
            'payment_status' => (string)$request->get('payment_status', ''),
            'status' => (string)$request->get('status', ''),
            'state' => (string)$request->get('state', ''),
            'q' => mb_substr(trim((string)$request->get('q', '')), 0, 100),
        ];
    }

    private function requireAdmin(Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
