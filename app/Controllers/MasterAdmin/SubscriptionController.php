<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class SubscriptionController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $response->redirect('/admin/subscriptions');
    }
}

