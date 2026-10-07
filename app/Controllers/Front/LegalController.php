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

    /** /data-deletion – how to get an account and its data deleted (Facebook Login "User Data Deletion" URL). */
    public function dataDeletion(Request $request, Response $response): void
    {
        $response->view('data-deletion');
    }

    public function grievances(Request $request, Response $response): void
    {
        $response->view('grievances');
    }

    public function refundCancellationPolicy(Request $request, Response $response): void
    {
        $response->view('refund-cancellation-policy');
    }
    
}
