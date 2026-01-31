<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Storage;

class JobCategoriesController extends BaseController
{
    public function index(Request $request, Response $response): void
{
    if (!$this->requireAdmin($request, $response)) {
        return;
    }

    $db = Database::getInstance();

    $page = (int)($request->get('page', 1));
    $perPage = 15;
    $offset = ($page - 1) * $perPage;

    $search = trim((string)$request->get('search', ''));
    $whereSql = '';
    $params = [];

    if ($search !== '') {
        $whereSql = "WHERE name LIKE ? OR slug LIKE ? OR description LIKE ?";
        $like = '%' . $search . '%';
        $params = [$like, $like, $like];
    }

    try {
        // Total count
        $totalRow = $db->fetchOne(
            "SELECT COUNT(*) as total FROM job_categories $whereSql",
            $params
        );

        $total = (int)($totalRow['total'] ?? 0);
        $totalPages = max(1, ceil($total / $perPage));

        // Data
        $categories = $db->fetchAll(
            "SELECT * FROM job_categories
             $whereSql
             ORDER BY sort_order ASC, name ASC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        // Job counts
        foreach ($categories as &$category) {
            $count = $db->fetchOne(
                "SELECT COUNT(*) as count
                 FROM jobs
                 WHERE category = ? AND status = 'published'",
                [$category['name']]
            );
            $category['job_count'] = (int)($count['count'] ?? 0);
        }

    } catch (\Exception $e) {
        error_log("JobCategoriesController::index - " . $e->getMessage());
        $categories = [];
        $total = 0;
        $totalPages = 1;
        $page = 1;
    }

    $response->view('admin/job-categories/index', [
        'title' => 'Job Categories',
        'categories' => $categories,
        'search' => $search,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'per_page' => $perPage
        ]
    ], 200, 'admin/layout');
}

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $response->view('admin/job-categories/create', [
            'title' => 'Create Job Category'
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $name = trim((string)$request->post('name', ''));
        $description = trim((string)$request->post('description', ''));
        $sortOrder = (int)($request->post('sort_order', 0));
        $isActive = (int)($request->post('is_active', 1));

        if (empty($name)) {
            $response->view('admin/job-categories/create', [
                'title' => 'Create Job Category',
                'error' => 'Category name is required',
                'formData' => $request->post()
            ], 422, 'admin/layout');
            return;
        }

        // Generate slug from name
        $slug = $this->generateSlug($name);

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file) {
                try {
                    $storage = new Storage();
                    $filePath = $storage->store($file, 'job-categories');
                    $imageUrl = $storage->url($filePath);
                } catch (\Exception $e) {
                    error_log("JobCategoriesController::store - Image upload error: " . $e->getMessage());
                    $response->view('admin/job-categories/create', [
                        'title' => 'Create Job Category',
                        'error' => 'Failed to upload image: ' . $e->getMessage(),
                        'formData' => $request->post()
                    ], 422, 'admin/layout');
                    return;
                }
            }
        }

        $db = Database::getInstance();
        
        try {
            // Check if name or slug already exists
            $existing = $db->fetchOne(
                "SELECT id FROM job_categories WHERE name = ? OR slug = ?",
                [$name, $slug]
            );

            if ($existing) {
                $response->view('admin/job-categories/create', [
                    'title' => 'Create Job Category',
                    'error' => 'A category with this name or slug already exists',
                    'formData' => $request->post()
                ], 422, 'admin/layout');
                return;
            }

            // Insert new category
            $db->execute(
                "INSERT INTO job_categories (name, slug, description, image, sort_order, is_active) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$name, $slug, $description, $imageUrl, $sortOrder, $isActive]
            );

            $response->redirect('/admin/job-categories?success=Category created successfully');
        } catch (\Exception $e) {
            error_log("JobCategoriesController::store - Error: " . $e->getMessage());
            $response->view('admin/job-categories/create', [
                'title' => 'Create Job Category',
                'error' => 'Failed to create category: ' . $e->getMessage(),
                'formData' => $request->post()
            ], 500, 'admin/layout');
        }
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $response->redirect('/admin/job-categories');
            return;
        }

        $db = Database::getInstance();
        $category = $db->fetchOne("SELECT * FROM job_categories WHERE id = ?", [$id]);

        if (!$category) {
            $response->redirect('/admin/job-categories?error=Category not found');
            return;
        }

        $response->view('admin/job-categories/edit', [
            'title' => 'Edit Job Category',
            'category' => $category
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $response->redirect('/admin/job-categories');
            return;
        }

        $name = trim((string)$request->post('name', ''));
        $description = trim((string)$request->post('description', ''));
        $sortOrder = (int)($request->post('sort_order', 0));
        $isActive = (int)($request->post('is_active', 1));

        if (empty($name)) {
            $db = Database::getInstance();
            $category = $db->fetchOne("SELECT * FROM job_categories WHERE id = ?", [$id]);
            $response->view('admin/job-categories/edit', [
                'title' => 'Edit Job Category',
                'error' => 'Category name is required',
                'category' => $category
            ], 422, 'admin/layout');
            return;
        }

        $db = Database::getInstance();
        
        try {
            // Get current category to preserve existing image
            $category = $db->fetchOne("SELECT * FROM job_categories WHERE id = ?", [$id]);
            if (!$category) {
                $response->redirect('/admin/job-categories?error=Category not found');
                return;
            }

            // Check if name already exists for another category
            $existing = $db->fetchOne(
                "SELECT id FROM job_categories WHERE (name = ? OR slug = ?) AND id != ?",
                [$name, $this->generateSlug($name), $id]
            );

            if ($existing) {
                $response->view('admin/job-categories/edit', [
                    'title' => 'Edit Job Category',
                    'error' => 'A category with this name or slug already exists',
                    'category' => $category
                ], 422, 'admin/layout');
                return;
            }

            // Handle image upload
            $imageUrl = $category['image'] ?? null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                if ($file) {
                    try {
                        $storage = new Storage();
                        
                        // Delete old image if exists
                        if (!empty($category['image'])) {
                            $oldPath = str_replace($_ENV['APP_URL'] . '/storage/uploads/', '', $category['image']);
                            $storage->delete($oldPath);
                        }
                        
                        $filePath = $storage->store($file, 'job-categories');
                        $imageUrl = $storage->url($filePath);
                    } catch (\Exception $e) {
                        error_log("JobCategoriesController::update - Image upload error: " . $e->getMessage());
                        $response->view('admin/job-categories/edit', [
                            'title' => 'Edit Job Category',
                            'error' => 'Failed to upload image: ' . $e->getMessage(),
                            'category' => $category
                        ], 422, 'admin/layout');
                        return;
                    }
                }
            }

            // Update category
            $db->execute(
                "UPDATE job_categories 
                 SET name = ?, slug = ?, description = ?, image = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                 WHERE id = ?",
                [$name, $this->generateSlug($name), $description, $imageUrl, $sortOrder, $isActive, $id]
            );

            $response->redirect('/admin/job-categories?success=Category updated successfully');
        } catch (\Exception $e) {
            error_log("JobCategoriesController::update - Error: " . $e->getMessage());
            $category = $db->fetchOne("SELECT * FROM job_categories WHERE id = ?", [$id]);
            $response->view('admin/job-categories/edit', [
                'title' => 'Edit Job Category',
                'error' => 'Failed to update category: ' . $e->getMessage(),
                'category' => $category
            ], 500, 'admin/layout');
        }
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            $response->json(['error' => 'Invalid category ID'], 400);
            return;
        }

        $db = Database::getInstance();
        
        try {
            // Check if category is used by any jobs
            $jobCount = $db->fetchOne(
                "SELECT COUNT(*) as count FROM jobs WHERE category = (SELECT name FROM job_categories WHERE id = ?)",
                [$id]
            );
            
            $count = (int)($jobCount['count'] ?? 0);
            if ($count > 0) {
                $response->json(['error' => "Cannot delete category. It is used by {$count} job(s)."], 400);
                return;
            }

            // Delete category
            $db->execute("DELETE FROM job_categories WHERE id = ?", [$id]);
            $response->json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            error_log("JobCategoriesController::delete - Error: " . $e->getMessage());
            $response->json(['error' => 'Failed to delete category'], 500);
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
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


