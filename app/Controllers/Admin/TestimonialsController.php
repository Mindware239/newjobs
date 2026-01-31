<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Storage;
use App\Models\Testimonial;

class TestimonialsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $db = Database::getInstance();
        $type = (string)$request->get('type', 'all');
        $status = (string)$request->get('status', 'all');
        $where = [];
        $params = [];
        if ($type !== 'all') { $where[] = 'testimonial_type = :type'; $params['type'] = $type; }
        if ($status !== 'all') { $where[] = 'is_active = :active'; $params['active'] = ($status === 'active' ? 1 : 0); }
        $sql = 'SELECT * FROM testimonials';
        if (!empty($where)) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        try { $items = $db->fetchAll($sql, $params); } catch (\Throwable $t) { $items = []; }
        $response->view('admin/testimonials/index', [
            'title' => 'Testimonials',
            'items' => $items,
            'filters' => ['type' => $type, 'status' => $status],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $response->view('admin/testimonials/create', [
            'title' => 'Add Testimonial',
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $type = trim((string)$request->post('testimonial_type', 'client'));
        $title = trim((string)$request->post('title', ''));
        $name = trim((string)$request->post('name', ''));
        $designation = trim((string)$request->post('designation', ''));
        $company = trim((string)$request->post('company', ''));
        $message = trim((string)$request->post('message', ''));
        $videoUrl = trim((string)$request->post('video_url', ''));
        $isActive = (int)($request->post('is_active', 1));
        $hasVideoUpload = $request->hasFile('video_file');

        if ($name === '' || !in_array($type, ['client','candidate'], true)) {
            $response->view('admin/testimonials/create', [
                'title' => 'Add Testimonial',
                'error' => 'Name and valid type are required',
                'user' => $this->currentUser
            ], 422, 'admin/layout');
            return;
        }
        if ($message === '' && $videoUrl === '' && !$hasVideoUpload) {
            $response->view('admin/testimonials/create', [
                'title' => 'Add Testimonial',
                'error' => 'Provide message or video URL',
                'user' => $this->currentUser
            ], 422, 'admin/layout');
            return;
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $allowed = ['image/jpeg','image/png','image/webp'];
            $maxSize = 2 * 1024 * 1024;
            if (!in_array($file['type'] ?? '', $allowed, true) || ($file['size'] ?? 0) > $maxSize) {
                $response->view('admin/testimonials/create', [
                    'title' => 'Add Testimonial',
                    'error' => 'Invalid image type or size',
                    'user' => $this->currentUser
                ], 422, 'admin/layout');
                return;
            }
            $storage = new Storage();
            $path = $storage->store($file, 'testimonials');
            $imageUrl = $storage->url($path);
        }
        $finalVideoUrl = $videoUrl ?: null;
        if ($hasVideoUpload) {
            $file = $request->file('video_file');
            $allowedVideo = ['video/mp4','video/webm','video/ogg'];
            $maxVideoSize = 30 * 1024 * 1024;
            if (!in_array($file['type'] ?? '', $allowedVideo, true) || ($file['size'] ?? 0) > $maxVideoSize) {
                $response->view('admin/testimonials/create', [
                    'title' => 'Add Testimonial',
                    'error' => 'Invalid video type or size',
                    'user' => $this->currentUser
                ], 422, 'admin/layout');
                return;
            }
            $storage = new Storage();
            $path = $storage->store($file, 'testimonials/videos');
            $finalVideoUrl = $storage->url($path);
        }

        $data = [
            'testimonial_type' => $type,
            'title' => ($title !== '' ? $title : null),
            'name' => $name,
            'designation' => $designation,
            'company' => $company,
            'message' => $message ?: null,
            'video_url' => $finalVideoUrl,
            'image' => $imageUrl,
            'is_active' => $isActive
        ];
        $created = Testimonial::create($data);
        if (!$created) {
            $response->view('admin/testimonials/create', [
                'title' => 'Add Testimonial',
                'error' => 'Failed to save testimonial',
                'user' => $this->currentUser
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/testimonials?success=Testimonial%20created');
    }

    public function edit(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $row = Testimonial::findById($id);
        if (!$row) {
            $response->redirect('/admin/testimonials');
            return;
        }
        $response->view('admin/testimonials/edit', [
            'title' => 'Edit Testimonial',
            'item' => $row->getAttributes(),
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $row = Testimonial::findById($id);
        if (!$row) { $response->redirect('/admin/testimonials'); return; }
        $type = trim((string)$request->post('testimonial_type', $row->testimonial_type ?? 'client'));
        $title = trim((string)$request->post('title', (string)($row->title ?? '')));
        $name = trim((string)$request->post('name', $row->name ?? ''));
        $designation = trim((string)$request->post('designation', $row->designation ?? ''));
        $company = trim((string)$request->post('company', $row->company ?? ''));
        $message = trim((string)$request->post('message', (string)($row->message ?? '')));
        $videoUrl = trim((string)$request->post('video_url', (string)($row->video_url ?? '')));
        $isActive = (int)($request->post('is_active', (int)($row->is_active ?? 1)));
        $hasVideoUpload = $request->hasFile('video_file');

        if ($name === '' || !in_array($type, ['client','candidate'], true)) {
            $response->view('admin/testimonials/edit', [
                'title' => 'Edit Testimonial',
                'error' => 'Name and valid type are required',
                'item' => $row->getAttributes(),
                'user' => $this->currentUser
            ], 422, 'admin/layout');
            return;
        }
        if ($message === '' && $videoUrl === '' && !$hasVideoUpload) {
            $response->view('admin/testimonials/edit', [
                'title' => 'Edit Testimonial',
                'error' => 'Provide message or video URL',
                'item' => $row->getAttributes(),
                'user' => $this->currentUser
            ], 422, 'admin/layout');
            return;
        }

        $imageUrl = $row->image ?? null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $allowed = ['image/jpeg','image/png','image/webp'];
            $maxSize = 2 * 1024 * 1024;
            if (!in_array($file['type'] ?? '', $allowed, true) || ($file['size'] ?? 0) > $maxSize) {
                $response->view('admin/testimonials/edit', [
                    'title' => 'Edit Testimonial',
                    'error' => 'Invalid image type or size',
                    'item' => $row->getAttributes(),
                    'user' => $this->currentUser
                ], 422, 'admin/layout');
                return;
            }
            $storage = new Storage();
            $path = $storage->store($file, 'testimonials');
            $imageUrl = $storage->url($path);
        }
        $finalVideoUrl = $videoUrl ?: ($row->video_url ?? null);
        if ($hasVideoUpload) {
            $file = $request->file('video_file');
            $allowedVideo = ['video/mp4','video/webm','video/ogg'];
            $maxVideoSize = 30 * 1024 * 1024;
            if (!in_array($file['type'] ?? '', $allowedVideo, true) || ($file['size'] ?? 0) > $maxVideoSize) {
                $response->view('admin/testimonials/edit', [
                    'title' => 'Edit Testimonial',
                    'error' => 'Invalid video type or size',
                    'item' => $row->getAttributes(),
                    'user' => $this->currentUser
                ], 422, 'admin/layout');
                return;
            }
            $storage = new Storage();
            $path = $storage->store($file, 'testimonials/videos');
            $finalVideoUrl = $storage->url($path);
        }

        $ok = Testimonial::updateOne($id, [
            'testimonial_type' => $type,
            'title' => ($title !== '' ? $title : null),
            'name' => $name,
            'designation' => $designation,
            'company' => $company,
            'message' => $message ?: null,
            'video_url' => $finalVideoUrl,
            'image' => $imageUrl,
            'is_active' => $isActive
        ]);
        if (!$ok) {
            $response->view('admin/testimonials/edit', [
                'title' => 'Edit Testimonial',
                'error' => 'Failed to update testimonial',
                'item' => $row->getAttributes(),
                'user' => $this->currentUser
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/testimonials?success=Testimonial%20updated');
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        Testimonial::deleteOne($id);
        $response->redirect('/admin/testimonials?success=Testimonial%20deleted');
    }

    public function toggleStatus(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->param('id');
        $row = Testimonial::findById($id);
        if ($row) {
            $new = (int)($row->is_active ? 0 : 1);
            Testimonial::updateOne($id, ['is_active' => $new]);
        }
        $response->redirect('/admin/testimonials?success=Status%20updated');
    }
    
    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
