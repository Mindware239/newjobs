<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Career;
use App\Models\CareerApplication;

class AdminCareerController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        $response->view('admin/careers/index', [
            'title' => 'Careers',
            'jobs' => Career::allForAdmin(),
            'success' => $request->get('success'),
            'error' => $request->get('error'),
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        $response->view('admin/careers/create', [
            'title' => 'Add Career Job',
            'job' => $this->defaults(),
            'errors' => [],
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        $data = $this->jobData($request);
        $errors = $this->validateJob($data);

        if (!empty($errors)) {
            $response->view('admin/careers/create', [
                'title' => 'Add Career Job',
                'job' => $data,
                'errors' => $errors,
            ], 422, 'admin/layout');
            return;
        }

        try {
            Career::createCareer($data);
            $response->redirect('/admin/careers?success=Job created successfully');
        } catch (\Throwable $e) {
            $response->view('admin/careers/create', [
                'title' => 'Add Career Job',
                'job' => $data,
                'errors' => ['general' => $e->getMessage()],
            ], 500, 'admin/layout');
        }
    }

    public function edit(Request $request, Response $response, int $id): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        $job = Career::findCareer($id, false);
        if (!$job) {
            $response->redirect('/admin/careers?error=Job not found');
            return;
        }

        $response->view('admin/careers/edit', [
            'title' => 'Edit Career Job',
            'job' => $job,
            'applications' => CareerApplication::forCareer($id),
            'errors' => [],
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, int $id): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        $job = Career::findCareer($id, false);
        if (!$job) {
            $response->redirect('/admin/careers?error=Job not found');
            return;
        }

        $data = $this->jobData($request);
        $errors = $this->validateJob($data);

        if (!empty($errors)) {
            $response->view('admin/careers/edit', [
                'title' => 'Edit Career Job',
                'job' => array_merge($job, $data),
                'applications' => CareerApplication::forCareer($id),
                'errors' => $errors,
            ], 422, 'admin/layout');
            return;
        }

        Career::updateCareer($id, $data);
        $response->redirect('/admin/careers?success=Job updated successfully');
    }

    public function delete(Request $request, Response $response, int $id): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        Career::deleteCareer($id);
        $response->redirect('/admin/careers?success=Job deleted successfully');
    }

    public function toggle(Request $request, Response $response, int $id): void
    {
        if (!$this->requireAdminUser($response)) {
            return;
        }

        Career::toggleStatus($id);
        $response->redirect('/admin/careers?success=Job status updated');
    }

    private function requireAdminUser(Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }

        return true;
    }

    private function defaults(): array
    {
        return [
            'title' => '',
            'department' => '',
            'location' => '',
            'job_type' => 'Full Time',
            'work_type' => 'On-site',
            'description' => '',
            'responsibilities' => '',
            'requirements' => '',
            'career_status' => 'active',
            'status' => 'active',
        ];
    }

    private function jobData(Request $request): array
    {
        return [
            'title' => trim((string)$request->post('title', '')),
            'department' => trim((string)$request->post('department', '')),
            'location' => trim((string)$request->post('location', '')),
            'job_type' => trim((string)$request->post('job_type', 'Full Time')),
            'work_type' => trim((string)$request->post('work_type', 'On-site')),
            'description' => trim((string)$request->post('description', '')),
            'responsibilities' => trim((string)$request->post('responsibilities', '')),
            'requirements' => trim((string)$request->post('requirements', '')),
            'status' => trim((string)$request->post('status', 'active')),
            'career_status' => trim((string)$request->post('status', 'active')),
        ];
    }

    private function validateJob(array $data): array
    {
        $errors = [];
        foreach (['title', 'department', 'location', 'description', 'responsibilities', 'requirements'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        if (!in_array($data['status'] ?? 'active', ['active', 'inactive'], true)) {
            $errors['status'] = 'Status must be active or inactive.';
        }

        return $errors;
    }
}
