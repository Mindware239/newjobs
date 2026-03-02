<?php

declare(strict_types=1);

namespace App\Controllers\Front;

class AboutController
{
    /**
     * Render About Us page
     */
    public static function index(): void
    {
        $viewPath = dirname(dirname(dirname(__DIR__))) . '/resources/views/about.php';
        require_once $viewPath;
    }
}

