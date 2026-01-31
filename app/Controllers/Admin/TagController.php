<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Tag;
use App\Middlewares\AdminMiddleware;

class TagController
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
        $tags = $db->fetchAll("SELECT * FROM blog_tags ORDER BY name ASC");
        $response->view('admin/tag/index', [
            'title' => 'Blog Tags',
            'tags' => $tags
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $response->view('admin/tag/create', [
            'title' => 'Create Tag'
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $name = trim((string)$request->post('name'));
        if ($name === '') {
            $response->view('admin/tag/create', [
                'title' => 'Create Tag',
                'error' => 'Name is required',
                'formData' => $request->post()
            ], 422, 'admin/layout');
            return;
        }
        $tag = new Tag();
        $slug = $tag->generateSlug($name);
        $tag->fill([
            'name' => $name,
            'slug' => $slug
        ]);
        if (!$tag->save()) {
            $response->view('admin/tag/create', [
                'title' => 'Create Tag',
                'error' => 'Failed to create tag',
                'formData' => $request->post()
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/blog-tags?success=Tag created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $tag = Tag::find($id);
        if (!$tag) {
            $response->redirect('/admin/blog-tags?error=Tag not found');
            return;
        }
        $response->view('admin/tag/edit', [
            'title' => 'Edit Tag',
            'tag' => $tag->toArray()
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $tag = Tag::find($id);
        if (!$tag) {
            $response->redirect('/admin/blog-tags?error=Tag not found');
            return;
        }
        $name = trim((string)$request->post('name'));
        if ($name === '') {
            $response->view('admin/tag/edit', [
                'title' => 'Edit Tag',
                'error' => 'Name is required',
                'tag' => $tag->toArray()
            ], 422, 'admin/layout');
            return;
        }
        $tag->fill(['name' => $name]);
        if (!$tag->save()) {
            $response->view('admin/tag/edit', [
                'title' => 'Edit Tag',
                'error' => 'Failed to update tag',
                'tag' => $tag->toArray()
            ], 500, 'admin/layout');
            return;
        }
        $response->redirect('/admin/blog-tags?success=Tag updated');
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $tag = Tag::find($id);
        if (!$tag) {
            $response->redirect('/admin/blog-tags?error=Tag not found');
            return;
        }
        Database::getInstance()->query("DELETE FROM blog_tag_map WHERE tag_id = :id", ['id' => $id]);
        $tag->delete();
        $response->redirect('/admin/blog-tags?success=Tag deleted');
    }
}

