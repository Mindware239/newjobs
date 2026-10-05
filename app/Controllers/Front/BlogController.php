<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Helpers\TocHelper;
use App\Helpers\Sanitizer;
use App\Helpers\SeoHelper;
use App\Repositories\BlogRepository;
use App\Services\BlogPresentationService;

class BlogController
{
    private BlogRepository $blogRepository;
    private BlogPresentationService $blogPresentationService;

    public function __construct()
    {
        $this->blogRepository = new BlogRepository();
        $this->blogPresentationService = new BlogPresentationService();
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int)($request->get('page') ?? 1));
        $search = trim((string)($request->get('search') ?? ''));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:index:page:$page:search:" . md5($search);
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blogs'])) {
            $response->view('blog/index', $cached, 200, 'layout');
            return;
        }

        $featured = [];
        if ($search === '') {
            $featured = $this->blogRepository->getFeaturedBlogs(6);
        }

        if ($search !== '') {
            $blogs = $this->blogRepository->searchPublishedBlogs($search, $perPage, $offset);
            $total = $this->blogRepository->countSearchPublishedBlogs($search);
        } else {
            $blogs = $this->blogRepository->getPublishedBlogs($perPage, $offset);
            $total = $this->blogRepository->countPublishedBlogs();
        }

        $categories = $this->blogRepository->getBlogCategoriesWithCounts();
        $tags = $this->blogRepository->getBlogTagsWithCounts();
        $latestArticles = $this->blogRepository->getLatestPublishedBlogs(3);

        $byCategory = [];
        if ($search === '') {
            foreach ($categories as $c) {
                $posts = $this->blogRepository->getPublishedBlogsByCategoryId((int)($c['id'] ?? 0), 6, 0);
                $byCategory[$c['slug'] ?? (string)$c['id']] = [
                    'category' => $c,
                    'posts' => $posts
                ];
            }
        }

        $payload = $this->blogPresentationService->buildIndexPayload(
            $blogs,
            $featured,
            $categories,
            $tags,
            $latestArticles,
            $byCategory,
            $search,
            $page,
            $perPage,
            $total
        );
        $cache->set($cacheKey, $payload, 300);
        $response->view('blog/index', $payload, 200, 'layout');
    }

    public function detail(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:detail:$slug";
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blog'])) {
            $response->view('blog/detail', $cached, 200, 'layout');
            return;
        }
        $blog = Blog::findBySlug($slug);
        if (!$blog) {
            $response->setStatusCode(404);
            $response->view('blog/detail', [
                'title' => 'Not Found',
                'blog' => null
            ]);
            return;
        }

        $contentHtml = (string)($blog->content ?? '');
        $contentHtml = Sanitizer::cleanBlogHtml($contentHtml);
        [$contentWithAnchors, $toc] = TocHelper::generateWithAnchors($contentHtml);

        $categories = $blog->getCategories();
        $tags = $blog->getTags();

        $meta = SeoHelper::forBlogDetail($blog->toArray());
        $baseUrl = $_ENV['APP_URL'] ?? '';
        $articleSchema = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $blog->title ?? '',
            "datePublished" => $blog->published_at ?? null,
            "dateModified" => $blog->updated_at ?? null,
            "image" => $blog->featured_image ?? null,
            "author" => [
                "@type" => "Person",
                "name" => "Author"
            ],
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $meta['canonical'] ?? ''
            ]
        ];
        $primaryCategory = $categories[0] ?? null;
        $breadcrumbItems = [
            [
                "@type" => "ListItem",
                "position" => 1,
                "item" => [
                    "@id" => $baseUrl . '/',
                    "name" => "Home"
                ]
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "item" => [
                    "@id" => $baseUrl . '/blog',
                    "name" => "Blog"
                ]
            ]
        ];
        if ($primaryCategory) {
            $breadcrumbItems[] = [
                "@type" => "ListItem",
                "position" => 3,
                "item" => [
                    "@id" => $baseUrl . '/blog/category/' . ($primaryCategory->slug ?? ''),
                    "name" => $primaryCategory->name ?? 'Category'
                ]
            ];
            $postPosition = 4;
        } else {
            $postPosition = 3;
        }
        $breadcrumbItems[] = [
            "@type" => "ListItem",
            "position" => $postPosition,
            "item" => [
                "@id" => $meta['canonical'] ?? ($baseUrl . '/blog/' . ($blog->slug ?? '')),
                "name" => $blog->title ?? ''
            ]
        ];
        $breadcrumbsSchema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $breadcrumbItems
        ];
        $schemaJsonLd = [$articleSchema, $breadcrumbsSchema];

        $related = [];
        $catIds = array_column(array_map(fn($c) => $c->toArray(), $categories), 'id');
        $tagIds = array_column(array_map(fn($t) => $t->toArray(), $tags), 'id');
        if (!empty($catIds)) {
            $relByCat = $this->blogRepository->getRelatedBlogsByCategoryIds($catIds, (int)$blog->id, 6);
            $related = array_merge($related, $relByCat);
        }
        if (!empty($tagIds)) {
            $relByTag = $this->blogRepository->getRelatedBlogsByTagIds($tagIds, (int)$blog->id, 6);
            $related = array_merge($related, $relByTag);
        }
        $related = $this->blogPresentationService->uniqueRelatedBlogs($related, 6);

        $latestArticles = $this->blogRepository->getLatestPublishedBlogs(3);

        $payload = [
            'title' => $blog->title ?? '',
            'blog' => $blog->toArray(),
            'content' => $contentWithAnchors,
            'toc' => $toc,
            'categories' => array_map(fn($c) => $c->toArray(), $categories),
            'tags' => array_map(fn($t) => $t->toArray(), $tags),
            'meta' => $meta,
            'schemaJsonLd' => $schemaJsonLd,
            'related' => $related,
            'latestArticles' => $latestArticles
        ];
        $cache->set($cacheKey, $payload, 300);
        $response->view('blog/detail', $payload, 200, 'layout');
    }

    public function category(Request $request, Response $response, array $params): void
    {
        $slug = trim((string)($params['slug'] ?? ''));
        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:category:$slug:" . ($request->get('page') ?? 1);
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blogs'])) {
            $response->view('blog/category', $cached, 200, 'layout');
            return;
        }
        $category = Category::findBySlug($slug);
        if (!$category && $slug !== '') {
            $altSlug = str_replace('_', '-', $slug);
            if ($altSlug !== $slug) {
                $category = Category::findBySlug($altSlug);
            }
        }
        if (!$category) {
            $response->setStatusCode(404);
            $response->view('blog/category', [
                'title' => 'Category',
                'category' => null,
                'blogs' => [],
                'pagination' => [
                    'page' => 1,
                    'per_page' => 12,
                    'total' => 0
                ]
            ], 404, 'layout');
            return;
        }

        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $blogs = $this->blogRepository->getPublishedBlogsByCategoryId((int)$category->id, $perPage, $offset);
        $total = $this->blogRepository->countPublishedBlogsByCategoryId((int)$category->id);
        $latestArticles = $this->blogRepository->getLatestPublishedBlogs(3);

        $payload = $this->blogPresentationService->buildPagedPayload(
            $category->name ?? 'Category',
            $category->toArray(),
            'category',
            $blogs,
            $latestArticles,
            $page,
            $perPage,
            $total
        );
        $cache->set($cacheKey, $payload, 300);
        $response->view('blog/category', $payload, 200, 'layout');
    }

    public function tag(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:tag:$slug:" . ($request->get('page') ?? 1);
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blogs'])) {
            $response->view('blog/tag', $cached, 200, 'layout');
            return;
        }
        $tag = Tag::findBySlug($slug);
        if (!$tag) {
            $response->setStatusCode(404);
            $response->view('blog/tag', [
                'title' => 'Tag',
                'tag' => null,
                'blogs' => []
            ]);
            return;
        }

        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $blogs = $this->blogRepository->getPublishedBlogsByTagId((int)$tag->id, $perPage, $offset);
        $total = $this->blogRepository->countPublishedBlogsByTagId((int)$tag->id);
        $latestArticles = $this->blogRepository->getLatestPublishedBlogs(3);

        $payload = $this->blogPresentationService->buildPagedPayload(
            $tag->name ?? 'Tag',
            $tag->toArray(),
            'tag',
            $blogs,
            $latestArticles,
            $page,
            $perPage,
            $total
        );
        $cache->set($cacheKey, $payload, 300);
        $response->view('blog/tag', $payload, 200, 'layout');
    }
}
