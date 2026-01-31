<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Category;
use App\Middlewares\AdminMiddleware;

class CategoryController
{
    private AdminMiddleware $middleware;

    public function __construct()
    {
        $this->middleware = new AdminMiddleware();
    }

    public function index(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM blog_categories ORDER BY name ASC");
        $response->view('admin/category/index', [
            'title' => 'Blog Categories',
            'categories' => $categories
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $response->view('admin/category/create', [
            'title' => 'Create Category'
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $name = trim((string)$request->post('name'));
        $description = trim((string)$request->post('description'));
        $isActive = (int)($request->post('is_active') ?? 1);
        if ($name === '') {
            $response->view('admin/category/create', [
                'title' => 'Create Category',
                'error' => 'Name is required',
                'formData' => $request->post(null)
            ], 422, 'admin/layout');
            return;
        }
        $category = new Category();
        $slug = $category->generateSlug($name);
        $category->fill([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_active' => $isActive
        ]);
        if (!$category->save()) {
            $response->view('admin/category/create', [
                'title' => 'Create Category',
                'error' => 'Failed to create category',
                'formData' => $request->post(null)
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/blog-categories?success=Category created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $category = Category::find($id);
        if (!$category) {
            $response->redirect('/admin/blog-categories?error=Category not found');
            return;
        }
        $response->view('admin/category/edit', [
            'title' => 'Edit Category',
            'category' => $category->toArray()
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $category = Category::find($id);
        if (!$category) {
            $response->redirect('/admin/blog-categories?error=Category not found');
            return;
        }
        $name = trim((string)$request->post('name'));
        $description = trim((string)$request->post('description'));
        $isActive = (int)($request->post('is_active') ?? 1);
        if ($name === '') {
            $response->view('admin/category/edit', [
                'title' => 'Edit Category',
                'error' => 'Name is required',
                'category' => $category->toArray()
            ], 422, 'admin/layout');
            return;
        }
        $category->fill([
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);
        if (!$category->save()) {
            $response->view('admin/category/edit', [
                'title' => 'Edit Category',
                'error' => 'Failed to update category',
                'category' => $category->toArray()
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/blog-categories?success=Category updated');
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $category = Category::find($id);
        if (!$category) {
            $response->redirect('/admin/blog-categories?error=Category not found');
            return;
        }
        // Optional: ensure not used
        Database::getInstance()->query("DELETE FROM blog_category_map WHERE category_id = :id", ['id' => $id]);
        $category->delete();
        $response->redirect('/admin/blog-categories?success=Category deleted');
    }
}

