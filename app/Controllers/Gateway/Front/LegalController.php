<?php

namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;

class LegalController
{
    public function terms(Request $request, Response $response): void
    {
        $response->view('terms');
    }

    public function privacy(Request $request, Response $response): void
    {
        $response->view('privacy');
    }

    public function grievances(Request $request, Response $response): void
    {
        $response->view('grievances');
    }
}
