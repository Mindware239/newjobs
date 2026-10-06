<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Services\NotificationService;

class CareerController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $department = trim((string)$request->get('department', ''));
        $location = trim((string)$request->get('location', ''));

        $response->view('careers/index', [
            'title' => 'Open Positions',
            'jobs' => Career::allActive($department, $location),
            'departments' => Career::departments(),
            'locations' => Career::locations(),
            'selectedDepartment' => $department,
            'selectedLocation' => $location,
        ], 200, 'layout');
    }

    public function details(Request $request, Response $response, int $id): void
    {
        $job = Career::findCareer($id);
        if (!$job) {
            $response->redirect('/careers?error=Job not found');
            return;
        }

        $response->view('careers/details', [
            'title' => $job['title'],
            'job' => $job,
            'errors' => [],
            'success' => $request->get('success'),
            'formData' => [],
            'activeTab' => $request->get('tab', 'details') === 'apply' ? 'apply' : 'details',
        ], 200, 'layout');
    }

    public function apply(Request $request, Response $response, int $id): void
    {
        $job = Career::findCareer($id);
        if (!$job) {
            $response->redirect('/careers?error=Job not found');
            return;
        }

        $response->redirect('/careers/' . $id . '?tab=apply');
    }

    public function submitApplication(Request $request, Response $response, int $id): void
    {
        $job = Career::findCareer($id);
        if (!$job) {
            $response->redirect('/careers?error=Job not found');
            return;
        }

        $data = [
            'first_name' => trim((string)$request->post('first_name', '')),
            'last_name' => trim((string)$request->post('last_name', '')),
            'email' => trim((string)$request->post('email', '')),
            'phone' => trim((string)$request->post('phone', '')),
        ];

        $errors = $this->validateApplication($data, $request);
        if (!empty($errors)) {
            $response->view('careers/details', [
                'title' => $job['title'],
                'job' => $job,
                'errors' => $errors,
                'formData' => $data,
                'activeTab' => 'apply',
            ], 422, 'layout');
            return;
        }

        try {
            $resumeName = $this->storeUploadedFile($request->file('resume'), 'resumes');
            $coverName = null;
            if ($request->hasFile('cover_letter')) {
                $coverName = $this->storeUploadedFile($request->file('cover_letter'), 'cover_letters');
            }

            $applicationId = CareerApplication::createApplication([
                'career_id' => $id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'resume' => $resumeName,
                'cover_letter' => $coverName,
            ]);

            $this->sendApplicationNotifications($job, $data, $applicationId, $resumeName, $coverName);

            $response->redirect('/careers/' . $id . '?tab=apply&success=Application submitted successfully');
        } catch (\Throwable $e) {
            error_log('Career application upload failed: ' . $e->getMessage());
            $response->view('careers/details', [
                'title' => $job['title'],
                'job' => $job,
                'errors' => ['upload' => 'Unable to submit application. Please try again.'],
                'formData' => $data,
                'activeTab' => 'apply',
            ], 500, 'layout');
        }
    }

    private function validateApplication(array $data, Request $request): array
    {
        $errors = [];

        foreach (['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'phone' => 'Phone'] as $field => $label) {
            if ($data[$field] === '') {
                $errors[$field] = $label . ' is required.';
            }
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($data['phone'] !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $data['phone'])) {
            $errors['phone'] = 'Enter a valid phone number.';
        }

        if (!$request->hasFile('resume')) {
            $errors['resume'] = 'Resume upload is required.';
        } elseif (!$this->isAllowedDocument($request->file('resume'))) {
            $errors['resume'] = 'Resume must be a PDF, DOC, or DOCX file.';
        }

        if ($request->hasFile('cover_letter') && !$this->isAllowedDocument($request->file('cover_letter'))) {
            $errors['cover_letter'] = 'Cover letter must be a PDF, DOC, or DOCX file.';
        }

        return $errors;
    }

    private function isAllowedDocument(?array $file): bool
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            return false;
        }

        $allowedMime = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/octet-stream',
        ];
        $mime = '';
        if (is_uploaded_file((string)$file['tmp_name'])) {
            $mime = (string)(mime_content_type((string)$file['tmp_name']) ?: '');
        }

        return $mime === '' || in_array($mime, $allowedMime, true);
    }

    private function storeUploadedFile(array $file, string $folder): string
    {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/careers/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $filename = $folder . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $target = $uploadDir . '/' . $filename;

        if (!move_uploaded_file((string)$file['tmp_name'], $target)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return $filename;
    }

    private function sendApplicationNotifications(array $job, array $data, int $applicationId, string $resumeName, ?string $coverName): void
    {
        $appUrl = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
        $jobTitle = (string)($job['title'] ?? 'Career Opening');
        $resumePath = $this->uploadedFilePath('resumes', $resumeName);
        $coverPath = $coverName ? $this->uploadedFilePath('cover_letters', $coverName) : null;

        $attachments = [];
        if (is_file($resumePath)) {
            $attachments[] = ['path' => $resumePath, 'name' => $this->safeAttachmentName($fullName, 'resume', $resumeName)];
        }
        if ($coverPath && is_file($coverPath)) {
            $attachments[] = ['path' => $coverPath, 'name' => $this->safeAttachmentName($fullName, 'cover-letter', $coverName)];
        }

        $payload = [
            'subject' => 'New Career Application: ' . $jobTitle . ' - ' . $fullName,
            'candidate_name' => $fullName,
            'candidate_email' => $data['email'],
            'candidate_phone' => $data['phone'],
            'job_title' => $jobTitle,
            'department' => (string)($job['department'] ?? ''),
            'location' => (string)($job['location'] ?? ''),
            'application_id' => $applicationId,
            'resume_file' => $resumeName,
            'cover_letter_file' => $coverName ?: 'Not provided',
            'attachments' => $attachments,
        ];

        foreach (\App\Helpers\AdminMail::list() as $recipient) {
            try {
                NotificationService::sendEmail($recipient, $payload['subject'], 'career_application_hr', $payload);
            } catch (\Throwable $e) {
                error_log('Career HR notification failed for ' . $recipient . ': ' . $e->getMessage());
            }
        }

        try {
            NotificationService::sendEmail(
                (string)$data['email'],
                'Application Received: ' . $jobTitle,
                'career_application_candidate',
                [
                    'subject' => 'Application Received: ' . $jobTitle,
                    'candidate_name' => $fullName,
                    'job_title' => $jobTitle,
                    'department' => (string)($job['department'] ?? ''),
                    'location' => (string)($job['location'] ?? ''),
                    'application_id' => $applicationId,
                    'link' => $appUrl !== '' ? $appUrl . '/careers/' . (int)($job['id'] ?? 0) : '/careers/' . (int)($job['id'] ?? 0),
                ]
            );
        } catch (\Throwable $e) {
            error_log('Career candidate confirmation failed: ' . $e->getMessage());
        }
    }

    private function uploadedFilePath(string $folder, string $filename): string
    {
        return dirname(__DIR__, 2) . '/uploads/careers/' . $folder . '/' . $filename;
    }

    private function safeAttachmentName(string $candidateName, string $type, string $storedFilename): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $candidateName), '-')) ?: 'candidate';
        $ext = strtolower(pathinfo($storedFilename, PATHINFO_EXTENSION));
        return $base . '-' . $type . ($ext !== '' ? '.' . $ext : '');
    }
}
