<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SubscriptionPayment;
use App\Models\Employer;
use App\Models\Job;

class PaymentsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $totalApplications = 0; // TODO: Calculate

        $response->view('employer/payments', [
            'title' => 'Payments',
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function invoice(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $id = (int)$request->param('id');
        $payment = SubscriptionPayment::find($id);
        if (!$payment || (int)($payment->attributes['employer_id'] ?? 0) !== (int)$employer->id) {
            $response->json(['error' => 'Invoice not found'], 404);
            return;
        }

        $subscription = \App\Models\EmployerSubscription::find((int)($payment->attributes['subscription_id'] ?? 0));
        $plan = $subscription ? $subscription->plan() : null;

        $response->view('employer/invoices/show', [
            'title' => 'Invoice #' . ($payment->attributes['invoice_number'] ?? $id),
            'employer' => $employer,
            'payment' => $payment->toArray(),
            'subscription' => $subscription ? $subscription->toArray() : null,
            'plan' => $plan
        ], 200, 'employer/layout');
    }

    public function updateBillingInfo(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        // 1. CSRF Protection
        $token = $request->header('X-CSRF-Token') ?? $request->input('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $data = $request->all();

        // 2. Strong Input Validation
        $errors = [];
        $street = trim((string)($data['address'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $state = trim((string)($data['state'] ?? ''));
        $country = trim((string)($data['country'] ?? ''));
        $postal = trim((string)($data['postal_code'] ?? ''));

        if (empty($street)) {
            $errors['address'] = 'Address is required';
        }
        if (empty($city)) {
            $errors['city'] = 'City is required';
        }
        if (empty($state)) {
            $errors['state'] = 'State is required';
        }
        if (empty($country)) {
            $errors['country'] = 'Country is required';
        }
        if (empty($postal)) {
            $errors['postal_code'] = 'Postal code is required';
        } elseif (!preg_match('/^[a-zA-Z0-9\s-]{3,10}$/', $postal)) {
             $errors['postal_code'] = 'Invalid postal code format';
        }

        if (!empty($errors)) {
            $response->json(['errors' => $errors], 422);
            return;
        }

        // 3. Better Error Handling with Try-Catch
        try {
            $addressJson = json_encode([
                'street' => $street,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postal
            ], JSON_UNESCAPED_UNICODE);

            $employer->attributes['address'] = $addressJson;
            $employer->attributes['city'] = $city;
            $employer->attributes['state'] = $state;
            $employer->attributes['country'] = $country;
            $employer->attributes['postal_code'] = $postal;
            
            if ($employer->save()) {
                $response->json(['success' => true]);
            } else {
                throw new \Exception('Failed to save employer data');
            }
        } catch (\Exception $e) {
            // Log error if logging system exists, otherwise just return 500
            $response->json(['error' => 'Database update failed: ' . $e->getMessage()], 500);
        }
    }
}
