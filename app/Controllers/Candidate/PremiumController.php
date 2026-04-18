<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\CandidatePremiumPurchase;
use App\Models\SubscriptionPlan;
use Razorpay\Api\Api;
use Dompdf\Dompdf;

use App\Helpers\SslHelper;

class PremiumController extends BaseController
{
    private function configureSslCa(): bool|string
    {
        return SslHelper::configureSslCa();
    }


    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return null;
        }

        $user = User::find((int)$userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser((int)$userId);
        }

        return $candidate;
    }

    /**
     * Premium plans page
     */
    public function plans(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $user = User::find((int)($candidate->attributes['user_id'] ?? 0));
        $candidateEmail = $user ? (string)($user->attributes['email'] ?? '') : '';
        $candidateName = (string)($candidate->attributes['full_name'] ?? '');
        $candidatePhone = (string)($candidate->attributes['mobile'] ?? '');

        $models = SubscriptionPlan::getActivePlansFor('candidate');
        $plans = [];
        foreach ($models as $m) {
            $attrs = is_array($m) ? $m : ($m->attributes ?? []);
            $slug = (string)($attrs['slug'] ?? '');
            $name = (string)($attrs['name'] ?? '');
            $priceMonthly = (float)($attrs['price_monthly'] ?? 0);
            $planFor = strtolower((string)($attrs['plan_for'] ?? ''));
            if ($planFor !== '' && $planFor !== 'candidate') {
                continue;
            }
            // Prefer admin-managed dynamic features JSON if available
            $featuresJson = $attrs['features'] ?? '[]';
            $featureStrings = [];
            if (is_string($featuresJson)) {
                $decoded = json_decode($featuresJson, true);
                if (is_array($decoded)) {
                    $featureStrings = array_values(array_filter(array_map(function ($item) {
                        if (is_string($item)) return trim($item);
                        if (is_array($item)) {
                            $text = (string)($item['feature_text'] ?? '');
                            $enabled = (int)($item['is_enabled'] ?? 1);
                            return $enabled === 1 ? trim($text) : '';
                        }
                        return '';
                    }, $decoded)));
                }
            }
            $featuresMap = [
                'priority_support' => 'Priority support',
                'advanced_filters' => 'Advanced analytics',
                'chat_enabled' => 'Chat with recruiters',
                'ai_matching' => 'AI-powered recommendations',
                'candidate_mobile_visible' => 'Higher visibility in searches',
                'resume_download_enabled' => 'Better resume tools',
                'job_post_boost' => 'Profile boost',
                'analytics_dashboard' => 'Insights dashboard',
                'custom_branding' => 'Enhanced profile branding',
                'api_access' => 'API access'
            ];
            if (empty($featureStrings)) {
                foreach ($featuresMap as $key => $label) {
                    $val = $attrs[$key] ?? 0;
                    if ((int)$val === 1) {
                        $featureStrings[] = $label;
                    }
                }
            }
            if ($slug !== '' && $name !== '' && $priceMonthly > 0) {
                $plans[] = [
                    'id' => $slug,
                    'name' => $name,
                    'price' => $priceMonthly,
                    'duration' => 30,
                    'features' => !empty($featureStrings) ? $featureStrings : ['Premium benefits']
                ];
            }
        }
        if (empty($plans)) {
            $db = \App\Core\Database::getInstance();
            $rows = $db->fetchAll("SELECT * FROM subscription_plans WHERE plan_for = 'candidate' ORDER BY sort_order ASC, price_monthly ASC LIMIT 10");
            foreach ($rows as $r) {
                $slug = (string)($r['slug'] ?? '');
                $name = (string)($r['name'] ?? '');
                $priceMonthly = (float)($r['price_monthly'] ?? 0);
                if ($slug !== '' && $name !== '' && $priceMonthly > 0) {
                    $plans[] = [
                        'id' => $slug,
                        'name' => $name,
                        'price' => $priceMonthly,
                        'duration' => 30,
                        'features' => ['Premium benefits']
                    ];
                }
            }
        }

        $response->view('candidate/premium/plans', [
            'title' => 'Premium Plans',
            'candidate' => $candidate,
            'plans' => $plans,
            'candidateEmail' => $candidateEmail,
            'candidateName' => $candidateName,
            'candidatePhone' => $candidatePhone
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        try {
            $data = $request->getJsonBody() ?? $request->all();
            $planId = $data['plan_id'] ?? '';
            $paymentMethod = $data['payment_method'] ?? 'razorpay';

            $planIdStr = (string)$planId;
            $plan = SubscriptionPlan::findBySlug($planIdStr);
            if (!$plan && is_numeric($planIdStr)) {
                $plan = SubscriptionPlan::find((int)$planIdStr);
            }
            if (!$plan) {
                $response->json(['error' => 'Invalid plan'], 400);
                return;
            }

            $amount = (float)$plan->getPrice('monthly');
            $duration = 30;

            // Enterprise Level: Validate amount consistency
            if ($amount <= 0) {
                $response->json(['error' => 'Invalid plan amount'], 400);
                return;
            }

            // Check if user is already premium
            if ($candidate->isPremium()) {
                $response->json(['error' => 'You already have an active premium membership'], 400);
                return;
            }

            $purchase = new CandidatePremiumPurchase();
            $purchase->fill([
                'candidate_id' => $candidate->id,
                'plan_type' => (string)$planId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending'
            ]);
            $purchase->save();

            switch ($paymentMethod) {
                case 'razorpay':
                    $paymentData = $this->createRazorpayOrder((int)$purchase->id, $amount);
                    break;
                case 'cashfree':
                    $paymentData = $this->createCashfreeOrder((int)$purchase->id, $amount, $candidate);
                    break;
                case 'stripe':
                    $paymentData = $this->createStripePayment($purchase->id, $amount);
                    break;
                case 'paypal':
                    $paymentData = $this->createPayPalPayment($purchase->id, $amount);
                    break;
                default:
                    $response->json(['error' => 'Invalid payment method'], 400);
                    return;
            }

            $response->json([
                'success' => true,
                'purchase_id' => $purchase->id,
                'payment_data' => $paymentData
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $msg = (string)$e->getResponse()->getBody();
            }
            error_log('Premium payment initiation error: ' . $msg);
            $response->json(['error' => 'Payment initiation failed: ' . $msg], 500);
        }
    }

    public function cashfreeVerify(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $orderId = $request->get('order_id');
        $purchaseId = (int)$request->get('purchase_id');
        
        $config = require __DIR__ . '/../../../config/cashfree.php';
        $client = new \GuzzleHttp\Client([
            'base_uri' => $config['base_url'],
            'verify' => $this->configureSslCa(), // Configure SSL CA
            'headers' => [
                'x-client-id' => $config['app_id'],
                'x-client-secret' => $config['secret_key'],
                'x-api-version' => $config['api_version'],
                'Accept' => 'application/json',
            ]
        ]);

        try {
            $res = $client->get("orders/{$orderId}");
            $body = json_decode((string)$res->getBody(), true);

            if (($body['order_status'] ?? '') === 'PAID') {
                $paymentsRes = $client->get("orders/{$orderId}/payments");
                $payments = json_decode((string)$paymentsRes->getBody(), true);
                $latest = $payments[0] ?? null;

                if ($latest && $latest['payment_status'] === 'SUCCESS') {
                    // Update purchase and activate premium
                    $purchase = CandidatePremiumPurchase::find($purchaseId);
                    if ($purchase && $purchase->status === 'pending') {
                        $purchase->fill([
                            'payment_id' => (string)$latest['cf_payment_id'],
                            'status' => 'completed'
                        ]);
                        $purchase->save();

                        $duration = $this->getPlanDuration($purchase->attributes['plan_type']);
                        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                        $candidate->fill(['is_premium' => 1, 'premium_expires_at' => $expiresAt]);
                        $candidate->save();
                    }
                    $response->redirect('/candidate/premium/plans?success=1');
                    return;
                }
            }
            $response->redirect('/candidate/premium/plans?error=payment_failed');
        } catch (\Throwable $e) {
            error_log('Cashfree Candidate Verify Error: ' . $e->getMessage());
            $response->redirect('/candidate/premium/plans?error=verification_failed');
        }
    }

    /**
     * Payment callback
     */
    public function paymentCallback(Request $request, Response $response): void
    {
        $data = $request->getJsonBody() ?? $request->all();
        $purchaseId = (int)($data['purchase_id'] ?? 0);
        $paymentId = $data['payment_id'] ?? '';
        $status = $data['status'] ?? 'failed';

        $purchase = $purchaseId > 0 ? CandidatePremiumPurchase::find($purchaseId) : null;
        if (!$purchase) {
            $response->json(['error' => 'Purchase not found'], 404);
            return;
        }

        if ($status === 'success' || $status === 'completed') {
            // Update purchase
            $purchase->fill([
                'payment_id' => $paymentId,
                'status' => 'completed'
            ]);
            $purchase->save();

            // Update candidate premium status
            $candidate = Candidate::find((int)$purchase->attributes['candidate_id']);
            if ($candidate) {
                $duration = $this->getPlanDuration($purchase->attributes['plan_type']);
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                
                $candidate->fill([
                    'is_premium' => 1,
                    'premium_expires_at' => $expiresAt
                ]);
                $candidate->save();
            }

            $receiptUrl = $this->generateReceipt((int)$purchase->attributes['candidate_id'], $purchase, $candidate ?? null);

            $response->json([
                'success' => true,
                'message' => 'Payment successful! Your premium membership is now active.',
                'receipt_url' => $receiptUrl
            ]);
        } else {
            $purchase->fill(['status' => 'failed']);
            $purchase->save();
            
            $response->json(['error' => 'Payment failed'], 400);
        }
    }

    public function cashfreeWebhook(Request $request, Response $response): void
    {
        $payload = (string)$request->getBody();
        $headers = $request->headers();
        $signature = $headers['x-webhook-signature'] ?? '';
        $timestamp = $headers['x-webhook-timestamp'] ?? '';
        
        $config = require __DIR__ . '/../../../config/cashfree.php';
        $rawData = $timestamp . $payload;
        $expected = base64_encode(hash_hmac('sha256', $rawData, $config['secret_key'], true));

        if (!hash_equals($expected, $signature)) {
            $response->json(['error' => 'Invalid signature'], 401);
            return;
        }

        $data = json_decode($payload, true);
        if (($data['type'] ?? '') === 'PAYMENT_SUCCESS_WEBHOOK') {
            $orderId = $data['data']['order']['order_id'] ?? '';
            $paymentData = $data['data']['payment'] ?? null;
            
            if ($orderId && $paymentData) {
                // Extract purchase ID from order ID (CAND-{purchaseId}-{timestamp})
                $parts = explode('-', $orderId);
                $purchaseId = isset($parts[1]) ? (int)$parts[1] : 0;
                
                $purchase = CandidatePremiumPurchase::find($purchaseId);
                if ($purchase && $purchase->status === 'pending') {
                    $purchase->fill([
                        'payment_id' => (string)$paymentData['cf_payment_id'],
                        'status' => 'completed'
                    ]);
                    $purchase->save();

                    $candidate = Candidate::find((int)$purchase->attributes['candidate_id']);
                    if ($candidate) {
                        $duration = $this->getPlanDuration($purchase->attributes['plan_type']);
                        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                        $candidate->fill(['is_premium' => 1, 'premium_expires_at' => $expiresAt]);
                        $candidate->save();
                    }
                }
            }
        }
        $response->json(['status' => 'ok']);
    }

    private function createCashfreeOrder(int $purchaseId, float $amount, Candidate $candidate): array
    {
        $config = require __DIR__ . '/../../../config/cashfree.php';
        $user = User::find((int)$candidate->attributes['user_id']);
        
        $client = new \GuzzleHttp\Client([
            'base_uri' => $config['base_url'],
            'verify' => $this->configureSslCa(), // Configure SSL CA
            'headers' => [
                'x-client-id' => $config['app_id'],
                'x-client-secret' => $config['secret_key'],
                'x-api-version' => $config['api_version'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        try {
            $orderId = 'CAND-' . $purchaseId . '-' . time();

            // Sanitize phone for Cashfree (must be 10 digits for India)
            $phoneRaw = $candidate->mobile ?? '9999999999';
            $phone = preg_replace('/\D+/', '', $phoneRaw);
            if (strlen($phone) > 10) {
                $phone = substr($phone, -10);
            } elseif (strlen($phone) < 10) {
                $phone = str_pad($phone, 10, '0', STR_PAD_LEFT);
            }

            $payload = [
                'order_id' => $orderId,
                'order_amount' => (float)$amount,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => 'CAND-' . $candidate->id,
                    'customer_email' => $user->email ?? '',
                    'customer_phone' => $phone,
                ],
                'order_meta' => [
                    'return_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/candidate/premium/cashfree/verify?order_id={order_id}&purchase_id=' . $purchaseId,
                    'notify_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/candidate/premium/cashfree/webhook',
                ],
                'order_note' => 'Candidate Premium Purchase'
            ];

            $res = $client->post('orders', ['json' => $payload]);
            $body = json_decode((string)$res->getBody(), true);

            if (isset($body['payment_session_id'])) {
                return [
                    'gateway' => 'cashfree',
                    'payment_session_id' => $body['payment_session_id'],
                    'order_id' => $orderId,
                    'environment' => $config['environment']
                ];
            }
            throw new \Exception('Failed to get payment_session_id');
        } catch (\Throwable $e) {
            error_log('Cashfree Candidate Order Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createRazorpayOrder(int $purchaseId, float $amount): array
    {
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $keyId = (string)($config['key_id'] ?? '');
        $keySecret = (string)($config['key_secret'] ?? '');
        $amountPaise = (int)round($amount * 100);

        try {
            if ($keyId === 'rzp_test_key' || $keySecret === 'rzp_test_secret') {
                throw new \RuntimeException('Razorpay test keys not configured. Set RAZORPAY_KEY and RAZORPAY_SECRET in .env');
            }

            $this->configureSslCa();
            $api = new Api($keyId, $keySecret);
            $order = $api->order->create([
                'receipt' => 'CAND-' . $purchaseId,
                'amount' => $amountPaise,
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'purchase_id' => $purchaseId,
                ]
            ]);

            return [
                'order_id' => $order['id'],
                'amount' => $amountPaise,
                'currency' => 'INR',
                'key' => $keyId,
                'name' => 'Mindware Infotech',
                'description' => 'Candidate Premium',
                'callback_url' => ($config['app_url'] ?? 'http://localhost') . '/candidate/premium/payment/callback'
            ];
        } catch (\Throwable $e) {
            error_log('Razorpay order error: ' . $e->getMessage());
            // Fallback: allow checkout without server order (for environments without SSL)
            return [
                'order_id' => null,
                'amount' => $amountPaise,
                'currency' => 'INR',
                'key' => $keyId ?: ($_ENV['RAZORPAY_KEY'] ?? 'rzp_test_key'),
                'name' => 'Mindware Infotech',
                'description' => 'Candidate Premium',
                'callback_url' => ($config['app_url'] ?? 'http://localhost') . '/candidate/premium/payment/callback'
            ];
        }
    }

    private function createStripePayment(int $purchaseId, float $amount): array
    {
        // Placeholder for future expansion
        throw new \RuntimeException('Stripe integration is coming soon.');
    }

    private function createPayPalPayment(int $purchaseId, float $amount): array
    {
        // Placeholder for future expansion
        throw new \RuntimeException('PayPal integration is coming soon.');
    }

    private function getPlanDuration(string $planType): int
    {
        $plan = SubscriptionPlan::findBySlug((string)$planType);
        if ($plan) {
            return 30;
        }
        return 0;
    }

    private function generateReceipt(int $candidateId, CandidatePremiumPurchase $purchase, ?Candidate $candidate): string
    {
        try {
            $company = $_ENV['COMPANY_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Mindware Infotech');
            $plan = ucfirst(str_replace('_', ' ', (string)$purchase->attributes['plan_type']));
            $amount = (float)($purchase->attributes['amount'] ?? 0);
            $taxRate = (float)($_ENV['TAX_RATE'] ?? 0.18);
            $tax = round($amount * $taxRate, 2);
            $total = $amount + $tax;
            $candidateName = $candidate ? (string)($candidate->attributes['full_name'] ?? '') : '';

            $html = '<html><head><style>body{font-family:Arial} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ddd;padding:8px} h1{margin-bottom:10px}</style></head><body>' .
                    '<h1>Payment Receipt</h1>' .
                    '<p><strong>Receipt #:</strong> RCPT-' . date('Ymd') . '-' . (int)$purchase->attributes['id'] . '</p>' .
                    '<p><strong>Date:</strong> ' . date('M d, Y') . '</p>' .
                    '<p><strong>From:</strong> ' . htmlspecialchars($company) . '</p>' .
                    '<p><strong>To:</strong> ' . htmlspecialchars($candidateName ?: ('Candidate #' . $candidateId)) . '</p>' .
                    '<table><thead><tr><th>Description</th><th>Amount</th></tr></thead><tbody>' .
                    '<tr><td>' . htmlspecialchars($plan) . '</td><td>₹' . number_format($amount, 2) . '</td></tr>' .
                    '</tbody></table>' .
                    '<p><strong>Tax:</strong> ₹' . number_format($tax, 2) . '</p>' .
                    '<p><strong>Total:</strong> ₹' . number_format($total, 2) . '</p>' .
                    '<p><strong>Payment ID:</strong> ' . htmlspecialchars((string)($purchase->attributes['payment_id'] ?? '')) . '</p>' .
                    '</body></html>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            $base = dirname(__DIR__, 3);
            $dir = $base . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'candidates' . DIRECTORY_SEPARATOR . $candidateId;
            if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
            $file = 'receipt_' . (int)$purchase->attributes['id'] . '.pdf';
            $pdfPath = $dir . '/' . $file;
            file_put_contents($pdfPath, $pdfOutput);
            if (file_exists($pdfPath)) {
                return '/storage/uploads/candidates/' . $candidateId . '/' . $file;
            }
            return '';
        } catch (\Throwable $e) {
            error_log('Candidate receipt generation failed: ' . $e->getMessage());
            return '';
        }
    }

    public function billing(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $purchases = CandidatePremiumPurchase::where('candidate_id', '=', (int)$candidate->attributes['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get();
        $items = array_map(function($p) use ($candidate) {
            $row = $p->attributes;
            $row['receipt_url'] = '/storage/uploads/candidates/' . (int)$candidate->attributes['id'] . '/receipt_' . (int)$row['id'] . '.pdf';
            return $row;
        }, $purchases);

        $response->view('candidate/premium/billing', [
            'title' => 'Billing & Receipts',
            'candidate' => $candidate,
            'items' => $items
        ]);
    }
}

