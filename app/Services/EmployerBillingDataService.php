<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employer;
use App\Models\EmployerPayment;
use App\Models\EmployerSubscription;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPayment;

class EmployerBillingDataService
{
    public function overview(Employer $employer): array
    {
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        $plan = $subscription ? $subscription->plan() : null;

        $unpaidRows = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->where('status', '!=', 'completed')
            ->get();

        $balanceDue = 0.0;
        foreach ($unpaidRows as $row) {
            $balanceDue += (float)($row->attributes['amount'] ?? 0);
        }

        $lastPayment = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        $upcomingDate = null;
        $upcomingAmount = null;
        if ($subscription) {
            $upcomingDate = $subscription->attributes['next_billing_date'] ?? null;
            $upcomingAmount = $plan ? ($plan->attributes['price_monthly'] ?? $plan->attributes['price'] ?? null) : null;
        }

        $recentTransactions = $this->recentTransactions((int)$employer->id, 5);
        $recentInvoices = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();
        $paymentMethods = $this->paymentMethods($employer);

        return [
            'employer' => $this->employerPayload($employer),
            'current_plan' => $plan ? $this->planPayload($plan->attributes) : null,
            'subscription' => $subscription ? $subscription->attributes : null,
            'balance_due' => $balanceDue,
            'upcoming_date' => $upcomingDate,
            'upcoming_amount' => $upcomingAmount !== null ? (float)$upcomingAmount : null,
            'last_payment' => $lastPayment ? $this->paymentPayload($lastPayment->toArray(), 'subscription') : null,
            'recent_transactions' => $recentTransactions,
            'invoices' => array_map(fn($p): array => $this->invoicePayload($p->toArray()), $recentInvoices),
            'payment_methods' => $paymentMethods['methods'] ?? [],
            'default_payment_method' => $paymentMethods['default_method'] ?? null,
            'alerts' => $this->alerts($employer, $subscription, $plan, $balanceDue, $lastPayment ? $lastPayment->toArray() : null),
        ];
    }

    public function transactions(Employer $employer, array $filters = []): array
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $status = $filters['status'] ?? null;
        $method = $filters['method'] ?? null;
        $product = $filters['product'] ?? null;
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 20)));

        $subQ = SubscriptionPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') {
            $subQ = $subQ->where('status', '=', $status);
        }
        if ($from) {
            $subQ = $subQ->where('created_at', '>=', $from);
        }
        if ($to) {
            $subQ = $subQ->where('created_at', '<=', $to);
        }
        if ($method && $method !== 'all') {
            $subQ = $subQ->where('gateway', '=', $method);
        }
        $subscriptionPayments = $subQ->orderBy('created_at', 'DESC')->limit(300)->get();

        $addQ = EmployerPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') {
            $addQ = $addQ->where('status', '=', $status);
        }
        if ($from) {
            $addQ = $addQ->where('created_at', '>=', $from);
        }
        if ($to) {
            $addQ = $addQ->where('created_at', '<=', $to);
        }
        $employerPayments = $addQ->orderBy('created_at', 'DESC')->limit(300)->get();

        $rows = array_merge(
            array_map(fn($p): array => $this->paymentPayload($p->toArray(), 'subscription'), $subscriptionPayments),
            array_map(fn($p): array => $this->paymentPayload($p->toArray(), 'addon'), $employerPayments)
        );

        if ($product && $product !== 'all') {
            $rows = array_values(array_filter($rows, static fn(array $row): bool => ($row['kind'] ?? '') === $product));
        }

        usort($rows, static function (array $a, array $b): int {
            $ta = strtotime((string)($a['created_at'] ?? 'now'));
            $tb = strtotime((string)($b['created_at'] ?? 'now'));
            return $tb <=> $ta;
        });

        $summary = [
            'total' => count($rows),
            'paid' => 0.0,
            'pending' => 0.0,
            'failed' => 0,
        ];

        foreach ($rows as $row) {
            $amount = (float)($row['amount'] ?? 0);
            $rowStatus = strtolower((string)($row['status'] ?? ''));
            if (in_array($rowStatus, ['completed', 'success'], true)) {
                $summary['paid'] += $amount;
            } elseif ($rowStatus === 'pending') {
                $summary['pending'] += $amount;
            } elseif (in_array($rowStatus, ['failed', 'refunded'], true)) {
                $summary['failed']++;
            }
        }

        $total = count($rows);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'method' => $method,
                'product' => $product,
            ],
            'summary' => $summary,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }

    public function invoices(Employer $employer, array $filters = []): array
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $status = $filters['status'] ?? null;

        $query = SubscriptionPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') {
            $query = $query->where('status', '=', $status);
        }
        if ($from) {
            $query = $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query = $query->where('created_at', '<=', $to);
        }
        $payments = $query->orderBy('created_at', 'DESC')->limit(300)->get();

        return [
            'invoices' => array_map(fn($p): array => $this->invoicePayload($p->toArray()), $payments),
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
            ],
        ];
    }

    public function paymentMethods(Employer $employer): array
    {
        $savedMethods = PaymentMethod::getForEmployer((int)$employer->id);
        $methods = array_map(function ($method): array {
            $attr = $method->attributes;
            $token = (string)($attr['token'] ?? '');
            unset($attr['token']);
            if (($attr['method_type'] ?? '') === 'card') {
                $attr['label'] = (($attr['brand'] ?? '') ?: 'Card') . ' •••• ' . ($attr['last4'] ?? '');
                $attr['details'] = 'Expires ' . ($attr['exp_month'] ?? '') . '/' . ($attr['exp_year'] ?? '');
            } elseif (($attr['method_type'] ?? '') === 'upi') {
                $attr['label'] = 'UPI';
                $attr['details'] = $this->maskUpi($token);
            }
            return $attr;
        }, $savedMethods);

        return [
            'methods' => $methods,
            'default_method' => array_values(array_filter($methods, static fn(array $method): bool => (int)($method['is_default'] ?? 0) === 1))[0] ?? null,
        ];
    }

    public function settings(Employer $employer): array
    {
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        $plan = $subscription ? $subscription->plan() : null;

        return [
            'employer' => $this->employerPayload($employer),
            'billing_profile' => [
                'company_name' => $employer->attributes['company_name'] ?? null,
                'tax_id' => $employer->attributes['tax_id'] ?? null,
                'address' => $this->decodeJson($employer->attributes['address'] ?? null),
                'country' => $employer->attributes['country'] ?? null,
                'state' => $employer->attributes['state'] ?? null,
                'city' => $employer->attributes['city'] ?? null,
                'postal_code' => $employer->attributes['postal_code'] ?? null,
            ],
            'subscription' => $subscription ? $subscription->attributes : null,
            'current_plan' => $plan ? $this->planPayload($plan->attributes) : null,
            'payment_methods' => $this->paymentMethods($employer)['methods'],
        ];
    }

    public function subscriptionDashboard(Employer $employer): array
    {
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        $plan = $subscription ? $subscription->plan() : null;
        $payments = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $usage = [
            'job_posts' => [
                'used' => $subscription ? ($subscription->job_posts_used ?? 0) : 0,
                'limit' => $plan ? $plan->max_job_posts : 0,
            ],
            'resume_views' => [
                'used' => $subscription ? ($subscription->resume_downloads_used_this_month ?? 0) : 0,
                'limit' => $plan ? $plan->max_resume_downloads : 0,
            ],
            'contacts_views' => [
                'used' => $subscription ? ($subscription->contacts_used_this_month ?? 0) : 0,
                'limit' => $plan ? $plan->max_contacts_per_month : 0,
            ],
        ];

        return [
            'employer' => $this->employerPayload($employer),
            'subscription' => $subscription ? $subscription->attributes : null,
            'current_plan' => $plan ? $this->planPayload($plan->attributes) : null,
            'usage' => $usage,
            'payments' => array_map(fn($p): array => $this->paymentPayload($p->attributes, 'subscription'), $payments),
            'alerts' => $this->alerts($employer, $subscription, $plan, 0.0, $payments[0]->attributes ?? null),
        ];
    }

    public function recentTransactions(int $employerId, int $limit = 5): array
    {
        try {
            $subPayments = SubscriptionPayment::where('employer_id', '=', $employerId)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get();
            $addonPayments = EmployerPayment::where('employer_id', '=', $employerId)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get();

            $rows = array_merge(
                array_map(fn($p): array => $this->paymentPayload($p->toArray(), 'subscription'), $subPayments),
                array_map(fn($p): array => $this->paymentPayload($p->toArray(), 'addon'), $addonPayments)
            );
            usort($rows, static function (array $a, array $b): int {
                $ta = strtotime((string)($a['created_at'] ?? '1970-01-01'));
                $tb = strtotime((string)($b['created_at'] ?? '1970-01-01'));
                return $tb <=> $ta;
            });
            return array_slice($rows, 0, $limit);
        } catch (\Throwable) {
            return [];
        }
    }

    private function alerts(Employer $employer, ?EmployerSubscription $subscription, $plan, float $balanceDue, ?array $lastPayment): array
    {
        $alerts = [];
        if ($balanceDue > 0) {
            $alerts[] = [
                'type' => 'payment_due',
                'severity' => 'warning',
                'message' => 'You have a pending billing balance.',
                'amount' => $balanceDue,
                'action_url' => '/employer/billing/transactions?pending=1',
            ];
        }

        if (!$subscription) {
            $alerts[] = [
                'type' => 'no_subscription',
                'severity' => 'info',
                'message' => 'No active subscription found.',
                'action_url' => '/employer/subscription/plans',
            ];
        } elseif (($subscription->attributes['expires_at'] ?? null) && strtotime((string)$subscription->attributes['expires_at']) <= strtotime('+7 days')) {
            $alerts[] = [
                'type' => 'subscription_expiring',
                'severity' => 'warning',
                'message' => 'Your subscription is expiring soon.',
                'expires_at' => $subscription->attributes['expires_at'],
                'action_url' => '/employer/subscription/dashboard',
            ];
        }

        if (!$lastPayment) {
            $alerts[] = [
                'type' => 'no_payment_history',
                'severity' => 'info',
                'message' => 'No recent payment found.',
            ];
        }

        return $alerts;
    }

    private function paymentPayload(array $payment, string $kind): array
    {
        $payment['kind'] = $kind;
        if (isset($payment['amount'])) {
            $payment['amount'] = (float)$payment['amount'];
        }
        $payment['invoice_url'] = $kind === 'subscription' && !empty($payment['id'])
            ? '/employer/invoices/' . (int)$payment['id']
            : null;
        return $payment;
    }

    private function invoicePayload(array $payment): array
    {
        $payment = $this->paymentPayload($payment, 'subscription');
        if (empty($payment['invoice_number'])) {
            $payment['invoice_number'] = 'INV-' . (int)($payment['id'] ?? 0);
        }
        return $payment;
    }

    private function maskUpi(string $upi): string
    {
        if ($upi === '' || !str_contains($upi, '@')) {
            return '';
        }

        [$name, $handle] = explode('@', $upi, 2);
        $visible = substr($name, 0, 2);
        return $visible . str_repeat('*', max(2, strlen($name) - 2)) . '@' . $handle;
    }

    private function planPayload(array $plan): array
    {
        foreach (['price', 'price_monthly', 'price_quarterly', 'price_annual'] as $field) {
            if (isset($plan[$field])) {
                $plan[$field] = (float)$plan[$field];
            }
        }
        if (isset($plan['features']) && is_string($plan['features'])) {
            $plan['features_list'] = $this->decodeJson($plan['features']) ?: [];
        }
        return $plan;
    }

    private function employerPayload(Employer $employer): array
    {
        return [
            'id' => (int)$employer->id,
            'company_name' => $employer->attributes['company_name'] ?? null,
            'register_as' => $employer->attributes['register_as'] ?? null,
            'kyc_status' => $employer->attributes['kyc_status'] ?? null,
        ];
    }

    private function decodeJson(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
