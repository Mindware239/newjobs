<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Middlewares\AdminMiddleware;

class ArticleCategoriesController
{
    private AdminMiddleware $middleware;

    public function __construct()
    {
        $this->middleware = new AdminMiddleware();
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text ?? '', '-');
        return $text ?: uniqid('category-');
    }

    public function index(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT * FROM article_categories ORDER BY created_at DESC");
        $response->view('admin/article_categories/index', [
            'title' => 'Article Categories',
            'categories' => $rows
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $response->view('admin/article_categories/create', [
            'title' => 'Add Category'
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $name = trim((string)$request->post('name', ''));
        $slug = trim((string)$request->post('slug', ''));
        if ($name === '') {
            $response->redirect('/admin/article-categories/create?error=Name required');
            return;
        }
        if ($slug === '') {
            $slug = $this->slugify($name);
        }
        $exists = $db->fetchOne("SELECT id FROM article_categories WHERE slug = :s", ['s' => $slug]);
        if ($exists) {
            $slug .= '-' . substr(uniqid('', true), -6);
        }
        $db->query("INSERT INTO article_categories (name, slug, created_at) VALUES (:n, :s, NOW())", ['n' => $name, 's' => $slug]);
        $response->redirect('/admin/article-categories?success=Category created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $row = $db->fetchOne("SELECT * FROM article_categories WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/article-categories?error=Not found');
            return;
        }
        $response->view('admin/article_categories/edit', [
            'title' => 'Edit Category',
            'category' => $row
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $row = $db->fetchOne("SELECT * FROM article_categories WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/article-categories?error=Not found');
            return;
        }
        $name = trim((string)$request->post('name', ''));
        $slug = trim((string)$request->post('slug', ''));
        if ($name === '') {
            $response->redirect('/admin/article-categories/'.$id.'/edit?error=Name required');
            return;
        }
        if ($slug === '') {
            $slug = $this->slugify($name);
        }
        $exists = $db->fetchOne("SELECT id FROM article_categories WHERE slug = :s AND id != :id", ['s' => $slug, 'id' => $id]);
        if ($exists) {
            $slug .= '-' . substr(uniqid('', true), -6);
        }
        $db->query("UPDATE article_categories SET name = :n, slug = :s WHERE id = :id", ['n' => $name, 's' => $slug, 'id' => $id]);
        $response->redirect('/admin/article-categories/'.$id.'/edit?success=Saved');
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $db->query("DELETE FROM article_categories WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/article-categories?success=Deleted');
    }
}

