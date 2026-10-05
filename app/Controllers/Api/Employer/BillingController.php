<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Services\EmployerBillingDataService;

class BillingController extends ApiController
{
    private EmployerBillingDataService $billingData;

    public function __construct()
    {
        $this->billingData = new EmployerBillingDataService();
    }

    public function overview(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->overview($employer);
        $this->success($response, $data, 'Billing overview');
    }

    public function transactions(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->transactions($employer, $request->get());
        $this->success($response, $data, 'Billing transactions');
    }

    public function invoices(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->invoices($employer, $request->get());
        $this->success($response, $data, 'Billing invoices');
    }

    public function paymentMethods(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->paymentMethods($employer);
        $this->success($response, $data, 'Payment methods');
    }

    public function settings(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->settings($employer);
        $this->success($response, $data, 'Billing settings');
    }

    public function subscriptionDashboard(Request $request, Response $response): void
    {
        $employer = $this->requireEmployerProfile($request, $response);
        if (!$employer) return;

        $data = $this->billingData->subscriptionDashboard($employer);
        $this->success($response, $data, 'Subscription dashboard');
    }

    private function requireEmployerProfile(Request $request, Response $response): ?Employer
    {
        $user = $this->user($request);
        if (!$user || (string)$user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return null;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return null;
        }

        return $employer;
    }
}
