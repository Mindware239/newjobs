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

class BlogController
{
    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int)($request->get('page') ?? 1));
        $search = trim((string)($request->get('search') ?? ''));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $db = \App\Core\Database::getInstance();
        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:index:page:$page:search:" . md5($search);
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blogs'])) {
            $response->view('blog/index', $cached, 200, 'layout');
            return;
        }

        $featured = [];
        if ($search === '') {
            $featured = $db->fetchAll(
                "SELECT * FROM blogs 
                 WHERE published_at IS NOT NULL AND is_featured = 1
                 ORDER BY sort_order DESC, published_at DESC 
                 LIMIT 6"
            );
        }

        if ($search !== '') {
            $blogs = $db->fetchAll(
                "SELECT * FROM blogs 
                 WHERE published_at IS NOT NULL 
                   AND (title LIKE :q OR excerpt LIKE :q OR content LIKE :q)
                 ORDER BY published_at DESC 
                 LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
                ['q' => '%' . $search . '%']
            );
            $countRow = $db->fetchOne("SELECT COUNT(*) as c FROM blogs WHERE published_at IS NOT NULL AND (title LIKE :q OR excerpt LIKE :q OR content LIKE :q)", ['q' => '%' . $search . '%']);
        } else {
            $blogs = $db->fetchAll(
                "SELECT * FROM blogs 
                 WHERE published_at IS NOT NULL 
                 ORDER BY is_featured DESC, sort_order DESC, published_at DESC 
                 LIMIT " . (int)$perPage . " OFFSET " . (int)$offset
            );
            $countRow = $db->fetchOne("SELECT COUNT(*) as c FROM blogs WHERE published_at IS NOT NULL");
        }

        $total = (int)($countRow['c'] ?? 0);

        $categories = $db->fetchAll(
            "SELECT bc.*, COUNT(bcm.blog_id) as blog_count
             FROM blog_categories bc
             LEFT JOIN blog_category_map bcm ON bcm.category_id = bc.id
             GROUP BY bc.id
             ORDER BY bc.name ASC"
        );

        $tags = $db->fetchAll(
            "SELECT bt.*, COUNT(btm.blog_id) as blog_count
             FROM blog_tags bt
             LEFT JOIN blog_tag_map btm ON btm.tag_id = bt.id
             GROUP BY bt.id
             ORDER BY bt.name ASC"
        );

        $latestArticles = $db->fetchAll(
            "SELECT * FROM blogs 
             WHERE published_at IS NOT NULL 
             ORDER BY published_at DESC 
             LIMIT 3"
        );

        $byCategory = [];
        if ($search === '') {
            foreach ($categories as $c) {
                $posts = $db->fetchAll(
                    "SELECT b.* FROM blogs b
                     INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
                     WHERE bcm.category_id = :cid AND b.published_at IS NOT NULL
                     ORDER BY b.published_at DESC
                     LIMIT 6",
                    ['cid' => $c['id']]
                );
                $byCategory[$c['slug'] ?? (string)$c['id']] = [
                    'category' => $c,
                    'posts' => $posts
                ];
            }
        }

        $payload = [
            'title' => 'Blog',
            'blogs' => $blogs,
            'featured' => $featured,
            'categories' => $categories,
            'tags' => $tags,
            'latestArticles' => $latestArticles,
            'byCategory' => $byCategory,
            'search' => $search,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ];
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
        $db = \App\Core\Database::getInstance();
        $catIds = array_column(array_map(fn($c) => $c->toArray(), $categories), 'id');
        $tagIds = array_column(array_map(fn($t) => $t->toArray(), $tags), 'id');
        if (!empty($catIds)) {
            $relByCat = $db->fetchAll(
                "SELECT DISTINCT b.* FROM blogs b
                 INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
                 WHERE bcm.category_id IN (" . implode(',', array_map('intval', $catIds)) . ")
                   AND b.id != :id
                   AND b.published_at IS NOT NULL
                 ORDER BY b.published_at DESC
                 LIMIT 6",
                ['id' => $blog->id]
            );
            $related = array_merge($related, $relByCat);
        }
        if (!empty($tagIds)) {
            $relByTag = $db->fetchAll(
                "SELECT DISTINCT b.* FROM blogs b
                 INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
                 WHERE btm.tag_id IN (" . implode(',', array_map('intval', $tagIds)) . ")
                   AND b.id != :id
                   AND b.published_at IS NOT NULL
                 ORDER BY b.published_at DESC
                 LIMIT 6",
                ['id' => $blog->id]
            );
            $related = array_merge($related, $relByTag);
        }
        $seen = [];
        $related = array_values(array_filter($related, function($r) use (&$seen) {
            if (isset($seen[$r['id']])) return false;
            $seen[$r['id']] = true;
            return true;
        }));
        $related = array_slice($related, 0, 6);

        $latestArticles = $db->fetchAll(
            "SELECT * FROM blogs 
             WHERE published_at IS NOT NULL 
             ORDER BY published_at DESC 
             LIMIT 3"
        );

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
        $slug = $params['slug'] ?? '';
        $cache = \App\Core\RedisClient::getInstance();
        $cacheKey = "blog:category:$slug:" . ($request->get('page') ?? 1);
        $cached = $cache->get($cacheKey);
        if (is_array($cached) && isset($cached['blogs'])) {
            $response->view('blog/category', $cached, 200, 'layout');
            return;
        }
        $category = Category::findBySlug($slug);
        if (!$category) {
            $response->setStatusCode(404);
            $response->view('blog/category', [
                'title' => 'Category',
                'category' => null,
                'blogs' => []
            ]);
            return;
        }

        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $db = \App\Core\Database::getInstance();
        $blogs = $db->fetchAll(
            "SELECT b.* FROM blogs b
             INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
             WHERE bcm.category_id = :cid AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            ['cid' => $category->id]
        );

        $countRow = $db->fetchOne(
            "SELECT COUNT(*) as c FROM blogs b
             INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
             WHERE bcm.category_id = :cid AND b.published_at IS NOT NULL",
            ['cid' => $category->id]
        );
        $total = (int)($countRow['c'] ?? 0);

        $latestArticles = $db->fetchAll(
            "SELECT * FROM blogs 
             WHERE published_at IS NOT NULL 
             ORDER BY published_at DESC 
             LIMIT 3"
        );

        $payload = [
            'title' => $category->name ?? 'Category',
            'category' => $category->toArray(),
            'blogs' => $blogs,
            'latestArticles' => $latestArticles,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ];
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

        $db = \App\Core\Database::getInstance();
        $blogs = $db->fetchAll(
            "SELECT b.* FROM blogs b
             INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
             WHERE btm.tag_id = :tid AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            ['tid' => $tag->id]
        );

        $countRow = $db->fetchOne(
            "SELECT COUNT(*) as c FROM blogs b
             INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
             WHERE btm.tag_id = :tid AND b.published_at IS NOT NULL",
            ['tid' => $tag->id]
        );
        $total = (int)($countRow['c'] ?? 0);

        $latestArticles = $db->fetchAll(
            "SELECT * FROM blogs 
             WHERE published_at IS NOT NULL 
             ORDER BY published_at DESC 
             LIMIT 3"
        );

        $payload = [
            'title' => $tag->name ?? 'Tag',
            'tag' => $tag->toArray(),
            'blogs' => $blogs,
            'latestArticles' => $latestArticles,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ];
        $cache->set($cacheKey, $payload, 300);
        $response->view('blog/tag', $payload, 200, 'layout');
    }
}
