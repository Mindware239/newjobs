<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;

class EmploymentVerificationController extends ApiController
{
    public function unlock(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['unlock_status' => 'initiated', 'price' => 500], 'Unlock initiated');
    }

    public function checkout(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['checkout_url' => '/payment/xyz'], 'Checkout created');
    }

    public function markPaid(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['status' => 'paid'], 'Marked as paid');
    }

    public function report(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['report_url' => '/reports/' . $id . '.pdf'], 'Report generated');
    }

    public function details(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['verification_details' => []], 'Details retrieved');
    }

    public function invoice(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['invoice_url' => '/invoices/' . $id . '.pdf'], 'Invoice retrieved');
    }
}
