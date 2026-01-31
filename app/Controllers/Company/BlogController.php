<?php

declare(strict_types=1);

namespace App\Controllers\Company;

use App\Core\Request;
use App\Core\Response;
use App\Models\CompanyBlog;

class BlogController
{
    public function show(Request $request, Response $response): void
    {
        $slug = $request->param('slug');

        if (!$slug) {
            $response->view('errors/404');
            return;
        }

        $blog = (new CompanyBlog())->getBySlug($slug);

        if (!$blog) {
            $response->view('errors/404');
            return;
        }

        $response->view('blog/show', [
            'blog' => $blog
        ]);
    }
}
