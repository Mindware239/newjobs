<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Storage;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Middlewares\AdminMiddleware;

class BlogController
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
        $blogs = $db->fetchAll("SELECT * FROM blogs ORDER BY created_at DESC LIMIT 100");
        $response->view('admin/blog/index', [
            'title' => 'Blogs',
            'blogs' => $blogs
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name ASC");
        $tags = $db->fetchAll("SELECT * FROM blog_tags ORDER BY name ASC");
        $response->view('admin/blog/create', [
            'title' => 'Create Blog',
            'categories' => $categories,
            'tags' => $tags
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $db = Database::getInstance();

        $title = trim((string)$request->post('title'));
        $excerpt = trim((string)$request->post('excerpt'));
        $content = (string)$request->post('content');
        $statusId = (int)($request->post('status_id') ?? 0);
        $publishedAt = $request->post('published_at') ?: null;
        $metaTitle = trim((string)$request->post('meta_title'));
        $metaDescription = trim((string)$request->post('meta_description'));
        $metaKeywords = trim((string)$request->post('meta_keywords'));
        $canonicalUrl = trim((string)$request->post('canonical_url'));
        $authorId = (int)($_SESSION['user_id'] ?? 0);

        if ($title === '' || $content === '') {
            $response->view('admin/blog/create', [
                'title' => 'Create Blog',
                'error' => 'Title and content are required',
                'formData' => $request->post()
            ], 422, 'admin/layout');
            return;
        }

        $blog = new Blog();
        $slug = $blog->generateSlug($title);

        $featuredImageUrl = null;
        if (!empty($_FILES['featured_image']) && is_uploaded_file($_FILES['featured_image']['tmp_name'])) {
            try {
                $storage = new Storage();
                $path = $storage->store($_FILES['featured_image'], 'blog');
                $featuredImageUrl = $storage->url($path);
            } catch (\Exception $e) {
                $response->view('admin/blog/create', [
                    'title' => 'Create Blog',
                    'error' => 'Failed to upload featured image: ' . $e->getMessage(),
                    'formData' => $request->post()
                ], 422, 'admin/layout');
                return;
            }
        }

        $blog->fill([
            'author_id' => $authorId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImageUrl,
            'status_id' => $statusId,
            'published_at' => $publishedAt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $metaKeywords,
            'canonical_url' => $canonicalUrl
        ]);

        if (!$blog->save()) {
            $response->view('admin/blog/create', [
                'title' => 'Create Blog',
                'error' => 'Failed to save blog',
                'formData' => $request->post()
            ], 500, 'admin/layout');
            return;
        }

        $categoryIds = (array)($request->post('category_ids') ?? []);
        $tagIds = (array)($request->post('tag_ids') ?? []);
        $blog->attachCategoriesByIds(array_map('intval', $categoryIds));
        $blog->attachTagsByIds(array_map('intval', $tagIds));

        $response->redirect('/admin/blog?success=Blog created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name ASC");
        $tags = $db->fetchAll("SELECT * FROM blog_tags ORDER BY name ASC");
        $currentCats = $db->fetchAll("SELECT category_id FROM blog_category_map WHERE blog_id = :id", ['id' => $id]);
        $currentTags = $db->fetchAll("SELECT tag_id FROM blog_tag_map WHERE blog_id = :id", ['id' => $id]);
        $response->view('admin/blog/edit', [
            'title' => 'Edit Blog',
            'blog' => $blog->toArray(),
            'categories' => $categories,
            'tags' => $tags,
            'selected_category_ids' => array_column($currentCats, 'category_id'),
            'selected_tag_ids' => array_column($currentTags, 'tag_id'),
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }

        $title = trim((string)$request->post('title'));
        $excerpt = trim((string)$request->post('excerpt'));
        $content = (string)$request->post('content');
        $statusId = (int)($request->post('status_id') ?? 0);
        $publishedAt = $request->post('published_at') ?: null;
        $metaTitle = trim((string)$request->post('meta_title'));
        $metaDescription = trim((string)$request->post('meta_description'));
        $metaKeywords = trim((string)$request->post('meta_keywords'));
        $canonicalUrl = trim((string)$request->post('canonical_url'));

        if ($title === '' || $content === '') {
            $response->view('admin/blog/edit', [
                'title' => 'Edit Blog',
                'error' => 'Title and content are required',
                'blog' => $blog->toArray()
            ], 422, 'admin/layout');
            return;
        }

        $featuredImageUrl = $blog->featured_image ?? null;
        if (!empty($_FILES['featured_image']) && is_uploaded_file($_FILES['featured_image']['tmp_name'])) {
            try {
                $storage = new Storage();
                $path = $storage->store($_FILES['featured_image'], 'blog');
                $featuredImageUrl = $storage->url($path);
            } catch (\Exception $e) {
                $response->view('admin/blog/edit', [
                    'title' => 'Edit Blog',
                    'error' => 'Failed to upload featured image: ' . $e->getMessage(),
                    'blog' => $blog->toArray()
                ], 422, 'admin/layout');
                return;
            }
        }

        $blog->fill([
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImageUrl,
            'status_id' => $statusId,
            'published_at' => $publishedAt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $metaKeywords,
            'canonical_url' => $canonicalUrl
        ]);

        if (!$blog->save()) {
            $response->view('admin/blog/edit', [
                'title' => 'Edit Blog',
                'error' => 'Failed to update blog',
                'blog' => $blog->toArray()
            ], 500, 'admin/layout');
            return;
        }

        $blog->detachCategories();
        $blog->detachTags();
        $categoryIds = (array)($request->post('category_ids') ?? []);
        $tagIds = (array)($request->post('tag_ids') ?? []);
        $blog->attachCategoriesByIds(array_map('intval', $categoryIds));
        $blog->attachTagsByIds(array_map('intval', $tagIds));

        $response->redirect('/admin/blog?success=Blog updated');
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $blog->detachCategories();
        $blog->detachTags();
        $blog->delete();
        $response->redirect('/admin/blog?success=Blog deleted');
    }

    public function preview(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $response->view('admin/blog/preview', [
            'title' => 'Preview: ' . ($blog->title ?? ''),
            'blog' => $blog->toArray()
        ], 200, 'admin/layout');
    }

    public function publish(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $blog->fill([
            'status_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        $blog->save();
        $response->redirect('/admin/blog?success=Published');
    }

    public function draft(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $blog->fill([
            'status_id' => 0,
            'published_at' => null
        ]);
        $blog->save();
        $response->redirect('/admin/blog?success=Moved to draft');
    }

    public function schedule(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $blog = Blog::find($id);
        if (!$blog) {
            $response->redirect('/admin/blog?error=Blog not found');
            return;
        }
        $when = (string)$request->post('published_at');
        if ($when === '') {
            $response->redirect('/admin/blog?error=Provide publish datetime');
            return;
        }
        $blog->fill([
            'status_id' => 2,
            'published_at' => $when
        ]);
        $blog->save();
        $response->redirect('/admin/blog?success=Scheduled');
    }

    public function feature(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance();
        $db->query("UPDATE blogs SET is_featured = 1 WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/blog?success=Featured');
    }

    public function unfeature(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance();
        $db->query("UPDATE blogs SET is_featured = 0 WHERE id = :id", ['id' => $id]);
        $response->redirect('/admin/blog?success=Unfeatured');
    }

    public function reorder(Request $request, Response $response, array $params): void
    {
        $this->middleware->handle($request, $response);
        $id = (int)($params['id'] ?? 0);
        $order = (int)($request->post('sort_order') ?? 0);
        $db = Database::getInstance();
        $db->query("UPDATE blogs SET sort_order = :o WHERE id = :id", ['o' => $order, 'id' => $id]);
        $response->redirect('/admin/blog?success=Order updated');
    }

    public function reorderBulk(Request $request, Response $response): void
    {
        $this->middleware->handle($request, $response);
        $orderStr = (string)$request->post('order');
        if ($orderStr === '') {
            $response->redirect('/admin/blog?error=No order provided');
            return;
        }
        $ids = array_values(array_filter(array_map('intval', explode(',', $orderStr))));
        if (empty($ids)) {
            $response->redirect('/admin/blog?error=Invalid order payload');
            return;
        }
        $db = Database::getInstance();
        $count = count($ids);
        // Highest sort_order for first item
        foreach ($ids as $pos => $id) {
            $sortOrder = $count - $pos;
            $db->query("UPDATE blogs SET sort_order = :o WHERE id = :id", ['o' => $sortOrder, 'id' => $id]);
        }
        $response->redirect('/admin/blog?success=Order saved');
    }
}
