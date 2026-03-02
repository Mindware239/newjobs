<?php

declare(strict_types=1);

namespace App\Controllers\Social;

use App\Core\Request;
use App\Core\Response;
use App\Models\SocialOrganization;

class SocialOrganizationsController
{
    protected SocialOrganization $orgModel;

    public function __construct()
    {
        $this->orgModel = new SocialOrganization();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ==========================
    // ORGANIZATION PAGE
    // ==========================
    public function index(Request $request, Response $response): void
    {
        $employer_id = $_SESSION['employer_id'] ?? $_SESSION['user_id'] ?? null;

        $organizations = [];

        if ($employer_id) {
            $organizations = $this->orgModel->getByEmployer((int)$employer_id);
        }

        $response->view('social-employer/organisation', [
            'title' => 'Organizations',
            'organizations' => $organizations
        ]);
    }

    // ==========================
    // STORE ORGANIZATION
    // ==========================
    public function store(Request $request, Response $response): void
    {
        $employer_id = (int)($_SESSION['employer_id'] ?? 0);
        if ($employer_id <= 0 && !empty($_SESSION['user_id'])) {
            try {
                $pdo = \App\Core\Database::getInstance()->getConnection();
                $model = new \App\Models\SocialJobApplication($pdo);
                $derived = (int)($model->getEmployerProfileId((int)$_SESSION['user_id']) ?? 0);
                if ($derived > 0) {
                    $_SESSION['employer_id'] = $derived;
                    $employer_id = $derived;
                }
            } catch (\Throwable $t) {}
        }
        if ($employer_id <= 0 && !empty($_SESSION['user_id'])) {
            $employer_id = (int)$_SESSION['user_id'];
        }

        $orgName = trim((string)$request->post('organization_name'));

        if ($orgName === '') {
            $response->redirect('/social-employer/organisation?error=missing_name');
            return;
        }

        // ✅ Prepare data
        $data = [
            'employer_id'        => (int)$employer_id,
            'organization_name' => $orgName,
            'acronyms'           => $request->post('acronyms'),
            'organization_type' => $request->post('organization_type'),
            'is_agency'         => $request->post('is_agency') ? 1 : 0,
            'website'           => $request->post('website'),
            'ein'               => $request->post('ein'),
            'staff_count'       => (int)($request->post('staff_count') ?? 0),
            'mission_focus'     => $request->post('mission_focus'),
            'mission'           => $request->post('mission'),
            'impact'            => $request->post('impact'),
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $newId = $this->orgModel->create($data);
        $file = $request->file('logo');
        if ($newId > 0 && $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            $mime = (string)($file['type'] ?? '');
            $size = (int)($file['size'] ?? 0);
            $allowedExt = ['png','jpg','jpeg'];
            $allowedMime = ['image/png','image/jpeg','image/jpg','application/octet-stream'];
            if ($size > 0 && $size <= 5 * 1024 * 1024 && in_array($ext, $allowedExt, true) && in_array($mime, $allowedMime, true)) {
                try {
                    $storage = new \App\Core\Storage();
                    $stored = $storage->store($file, 'uploads/organizations/' . $newId);
                    if ($stored) {
                        $this->orgModel->updateById($newId, ['logo_url' => '/' . ltrim($stored, '/')]);
                    }
                } catch (\Throwable $t) {}
            }
        }

        // ✅ Success redirect
        $response->redirect('/social-employer/organisation?success=created');
    }

    // ==========================
    // AJAX SEARCH
    // ==========================
    public function search(Request $request, Response $response): void
    {
        $keyword = trim((string)$request->get('q'));

        if ($keyword === '') {
            $response->json([]);
            return;
        }

        $results = $this->orgModel->search($keyword);

        $response->json($results);
    }

    public function edit(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        if ($id <= 0) { $response->redirect('/social-employer/organisation'); return; }
        $org = $this->orgModel->find($id);
        if (!$org) { $response->redirect('/social-employer/organisation?error=not_found'); return; }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $response->view('social-employer/organization-edit', [
            'title' => 'Edit Organization',
            'org' => $org,
            'base' => '/'
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        if ($id <= 0) { $response->redirect('/social-employer/organisation'); return; }
        $payload = [
            'organization_name' => trim((string)$request->post('organization_name')),
            'acronyms' => trim((string)$request->post('acronyms')),
            'organization_type' => trim((string)$request->post('organization_type')),
            'is_agency' => $request->post('is_agency') ? 1 : 0,
            'website' => trim((string)$request->post('website')),
            'ein' => trim((string)$request->post('ein')),
            'staff_count' => (int)($request->post('staff_count') ?? 0),
            'mission_focus' => trim((string)$request->post('mission_focus')),
            'mission' => trim((string)$request->post('mission')),
            'impact' => trim((string)$request->post('impact')),
        ];
        $file = $request->file('logo');
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            $mime = (string)($file['type'] ?? '');
            $size = (int)($file['size'] ?? 0);
            $allowedExt = ['png','jpg','jpeg'];
            $allowedMime = ['image/png','image/jpeg','image/jpg','application/octet-stream'];
            if ($size > 0 && $size <= 5 * 1024 * 1024 && in_array($ext, $allowedExt, true) && in_array($mime, $allowedMime, true)) {
                try {
                    $storage = new \App\Core\Storage();
                    $stored = $storage->store($file, 'uploads/organizations/' . $id);
                    if ($stored) {
                        $payload['logo_url'] = '/' . ltrim($stored, '/');
                    }
                } catch (\Throwable $t) {}
            }
        }
        $this->orgModel->updateById($id, $payload);
        $response->redirect('/social-employer/organisation?success=updated');
    }
}
