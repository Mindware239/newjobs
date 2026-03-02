<?php

declare(strict_types=1);

namespace App\Controllers\Social;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SocialSubscriptionEmploy;
use App\Models\Plan;

class SocialEmployerPaymentsController
{
    // ==========================
    // PAYMENT LIST (HISTORY)
    // ==========================
    public function index(Request $request, Response $response): void
    {
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $employer_id = $_SESSION['employer_id'] ?? null;

        if(!$employer_id){
            $response->redirect('/employers');
            return;
        }

        $payments = SocialSubscriptionEmploy::where('employer_id', '=', $employer_id)
                        ->orderBy('created_at', 'DESC')
                        ->get();

        $response->view('social/payments/index', [
            'title' => 'My Payments',
            'payments' => $payments
        ]);
    }

    // ==========================
    // STORE PAYMENT (after gateway success)
    // ==========================
    public function store(Request $request, Response $response): void
    {
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $employer_id = $_SESSION['employer_id'] ?? null;

        if(!$employer_id){
            $response->json(['error' => 'Login required'], 401);
            return;
        }

        $plan_id = (int)$request->post('plan_id');
        $amount  = (float)$request->post('amount');
        $gateway = $request->post('gateway');
        $method  = $request->post('payment_method');
        $txn_id  = $request->post('txn_id');

        // ==========================
        // SAVE PAYMENT (in social_subscription_employ)
        // ==========================

        $cartRow = SocialSubscriptionEmploy::where('employer_id', '=', $employer_id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
        if (!$cartRow) {
            $cartRow = new SocialSubscriptionEmploy();
            $cartRow->fill([
                'employer_id' => $employer_id,
                'items' => json_encode([['plan_id' => $plan_id, 'qty' => 1, 'price' => $amount]]),
                'total_amount' => number_format($amount, 2, '.', ''),
                'currency' => 'INR',
                'status' => 'cart',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $cartRow->save();
        }
        $cartRow->setAttribute('total_amount', number_format($amount, 2, '.', ''));
        $cartRow->setAttribute('currency', 'INR');
        $cartRow->setAttribute('gateway', $gateway);
        $cartRow->setAttribute('payment_method', $method);
        $cartRow->setAttribute('status', 'completed');
        $cartRow->setAttribute('gateway_payment_id', $txn_id);
        $cartRow->setAttribute('paid_at', date('Y-m-d H:i:s'));
        $cartRow->save();

        // ==========================
        // CREATE SUBSCRIPTION (employer_subscriptions)
        // ==========================

        $plan = Plan::find($plan_id);
        $db = Database::getInstance();
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $db->query("
            INSERT INTO employer_subscriptions (employer_id, plan, amount, status, started_at, expires_at, created_at)
            VALUES (:eid, :plan, :amount, 'active', NOW(), :expires, NOW())
        ", [
            'eid' => $employer_id,
            'plan' => $plan ? ($plan->attributes['slug'] ?? 'standard') : 'standard',
            'amount' => $amount,
            'expires' => $expires
        ]);
        $subscriptionId = (int)$db->lastInsertId();
        $cartRow->setAttribute('subscription_id', $subscriptionId);
        $cartRow->save();

        $response->json([
            'success' => true,
            'message' => 'Payment successful & plan activated'
        ]);
    }
}
