<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Storage;
use App\Middlewares\AdminMiddleware;

class CareerArticlesController
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
        return $text ?: uniqid('article-');
    }

    public function index(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $search = (string)$request->get('q', '');
        $params = [];
        $where = '1=1';
        if ($search !== '') {
            $where .= ' AND (title LIKE :q OR author LIKE :q)';
            $params['q'] = '%' . $search . '%';
        }
        $rows = $db->fetchAll("SELECT ca.*, ac.name AS category_name FROM career_articles ca LEFT JOIN article_categories ac ON ac.id = ca.category_id WHERE {$where} ORDER BY ca.created_at DESC LIMIT 100", $params);
        $response->view('admin/career_articles/index', [
            'title' => 'Career Articles',
            'articles' => $rows
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT id, name FROM article_categories ORDER BY name");
        $response->view('admin/career_articles/create', [
            'title' => 'Add Article',
            'categories' => $categories
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $categoryId = (int)$request->post('category_id', 0);
        $title = trim((string)$request->post('title', ''));
        $slug = trim((string)$request->post('slug', ''));
        $short = trim((string)$request->post('short_description', ''));
        $content = (string)$request->post('content', '');
        $image = trim((string)$request->post('image', ''));
        $author = trim((string)$request->post('author', ''));
        $status = in_array($request->post('status', 'published'), ['draft','published'], true) ? (string)$request->post('status', 'published') : 'published';
        $publishedAt = trim((string)$request->post('published_at', ''));

        // Handle image file upload (optional)
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                try {
                    $storage = new Storage();
                    $path = $storage->store($file, 'uploads/career-articles');
                    $image = $storage->url($path);
                } catch (\Throwable $t) {
                    error_log('CareerArticlesController::store image upload failed: ' . $t->getMessage());
                }
            }
        }

        if ($title === '') {
            $response->redirect('/admin/career-articles/create?error=Title required');
            return;
        }
        if ($slug === '') {
            $slug = $this->slugify($title);
        }
        $exists = $db->fetchOne("SELECT id FROM career_articles WHERE slug = :s", ['s' => $slug]);
        if ($exists) {
            $slug .= '-' . substr(uniqid('', true), -6);
        }
        $params = [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'title' => $title,
            'slug' => $slug,
            'short_description' => $short,
            'content' => $content,
            'image' => $image,
            'author' => $author,
            'status' => $status,
            'published_at' => $publishedAt !== '' ? $publishedAt : null
        ];
        $db->query("INSERT INTO career_articles (category_id,title,slug,short_description,content,image,author,status,published_at,created_at,updated_at) VALUES (:category_id,:title,:slug,:short_description,:content,:image,:author,:status,:published_at,NOW(),NOW())", $params);
        $response->redirect('/admin/career-articles?success=Article created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $row = $db->fetchOne("SELECT * FROM career_articles WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/career-articles?error=Not found');
            return;
        }
        $categories = $db->fetchAll("SELECT id, name FROM article_categories ORDER BY name");
        $response->view('admin/career_articles/edit', [
            'title' => 'Edit Article',
            'article' => $row,
            'categories' => $categories
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $row = $db->fetchOne("SELECT * FROM career_articles WHERE id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/career-articles?error=Not found');
            return;
        }
        $categoryId = (int)$request->post('category_id', 0);
        $title = trim((string)$request->post('title', ''));
        $slug = trim((string)$request->post('slug', ''));
        $short = trim((string)$request->post('short_description', ''));
        $content = (string)$request->post('content', '');
        $image = trim((string)$request->post('image', ''));
        $author = trim((string)$request->post('author', ''));
        $status = in_array($request->post('status', 'published'), ['draft','published'], true) ? (string)$request->post('status', 'published') : 'published';
        $publishedAt = trim((string)$request->post('published_at', ''));

        // Handle image file upload (optional)
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                try {
                    $storage = new Storage();
                    $path = $storage->store($file, 'uploads/career-articles');
                    $image = $storage->url($path);
                } catch (\Throwable $t) {
                    error_log('CareerArticlesController::update image upload failed: ' . $t->getMessage());
                }
            }
        }

        if ($title === '') {
            $response->redirect('/admin/career-articles/'.$id.'/edit?error=Title required');
            return;
        }
        if ($slug === '') {
            $slug = $this->slugify($title);
        }
        $exists = $db->fetchOne("SELECT id FROM career_articles WHERE slug = :s AND id != :id", ['s' => $slug, 'id' => $id]);
        if ($exists) {
            $slug .= '-' . substr(uniqid('', true), -6);
        }
        $paramsDb = [
            'id' => $id,
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'title' => $title,
            'slug' => $slug,
            'short_description' => $short,
            'content' => $content,
            'image' => $image,
            'author' => $author,
            'status' => $status,
            'published_at' => $publishedAt !== '' ? $publishedAt : null
        ];
        $db->query("UPDATE career_articles SET category_id=:category_id,title=:title,slug=:slug,short_description=:short_description,content=:content,image=:image,author=:author,status=:status,published_at=:published_at,updated_at=NOW() WHERE id=:id", $paramsDb);
        $response->redirect('/admin/career-articles/'.$id.'/edit?success=Saved');
    }

    public function uploadImage(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        if (!$request->hasFile('file')) {
            $response->json(['error' => 'No file'], 400);
            return;
        }
        $file = $request->file('file');
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'Upload failed'], 400);
            return;
        }
        try {
            $storage = new Storage();
            $path = $storage->store($file, 'uploads/career-articles');
            $url = $storage->url($path);
            $response->json(['url' => $url]);
        } catch (\Throwable $t) {
            error_log('CareerArticlesController::uploadImage error: ' . $t->getMessage());
            $response->json(['error' => 'Server error'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $db->query("DELETE FROM career_articles WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/career-articles?success=Deleted');
    }

    public function preview(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $id = (int)($params['id'] ?? 0);
        $row = $db->fetchOne("SELECT ca.*, ac.name AS category_name FROM career_articles ca LEFT JOIN article_categories ac ON ac.id = ca.category_id WHERE ca.id = :id", ['id' => $id]);
        if (!$row) {
            $response->redirect('/admin/career-articles?error=Not found');
            return;
        }
        $response->view('admin/career_articles/preview', [
            'title' => 'Preview: ' . ($row['title'] ?? ''),
            'article' => $row
        ], 200, 'admin/layout');
    }
}
