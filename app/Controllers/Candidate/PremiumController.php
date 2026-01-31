<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\CandidatePremiumPurchase;
use Razorpay\Api\Api;
use Dompdf\Dompdf;

class PremiumController extends BaseController
{
    private function configureSslCa(): void
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        $possible = [
            $envPath,
            __DIR__ . '/../../../vendor/guzzlehttp/guzzle/src/cacert.pem',
            'E:/xampp/php/extras/ssl/cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'E:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        foreach ($possible as $path) {
            if ($path && file_exists((string)$path)) {
                @ini_set('curl.cainfo', (string)$path);
                @putenv('CURL_CA_BUNDLE=' . (string)$path);
                @putenv('SSL_CERT_FILE=' . (string)$path);
                break;
            }
        }
    }


    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return null;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
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

        $plans = [
            [
                'id' => 'boost_7days',
                'name' => 'Profile Boost (7 Days)',
                'price' => 1,
                'duration' => 7,
                'features' => [
                    'Show profile at top to recruiters',
                    'Higher visibility in search results',
                    'Priority in job recommendations',
                    '7 days validity'
                ]
            ],
            [
                'id' => 'boost_30days',
                'name' => 'Profile Boost (30 Days)',
                'price' => 299,
                'duration' => 30,
                'features' => [
                    'Show profile at top to recruiters',
                    'Higher visibility in search results',
                    'Priority in job recommendations',
                    '30 days validity',
                    '10% discount'
                ]
            ],
            [
                'id' => 'premium_monthly',
                'name' => 'Premium Membership (Monthly)',
                'price' => 499,
                'duration' => 30,
                'features' => [
                    'All Boost features',
                    'Verified badge',
                    'Unlimited job applications',
                    'Advanced analytics',
                    'Priority support',
                    'Monthly renewal'
                ]
            ],
            [
                'id' => 'premium_yearly',
                'name' => 'Premium Membership (Yearly)',
                'price' => 999,
                'duration' => 365,
                'features' => [
                    'All Boost features',
                    'Verified badge',
                    'Unlimited job applications',
                    'Advanced analytics',
                    'Priority support',
                    '2 months free (save ₹3000)'
                ]
            ]
        ];

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

            $planPrices = [
                'boost_7days' => 1,
                'boost_30days' => 299,
                'premium_monthly' => 499,
                'premium_yearly' => 999
            ];

            $planDurations = [
                'boost_7days' => 7,
                'boost_30days' => 30,
                'premium_monthly' => 30,
                'premium_yearly' => 365
            ];

            if (!isset($planPrices[$planId])) {
                $response->json(['error' => 'Invalid plan'], 400);
                return;
            }

            $amount = $planPrices[$planId];
            $duration = $planDurations[$planId];

            $purchase = new CandidatePremiumPurchase();
            $purchase->fill([
                'candidate_id' => $candidate->id,
                'plan_type' => $planId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending'
            ]);
            $purchase->save();

            switch ($paymentMethod) {
                case 'razorpay':
                    $paymentData = $this->createRazorpayOrder((int)$purchase->id, $amount);
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
            error_log('Premium payment initiation error: ' . $e->getMessage());
            $response->json(['error' => 'Payment initiation failed. Please try again.'], 500);
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
        // TODO: Integrate Stripe SDK
        return [
            'client_secret' => 'sk_test_placeholder',
            'amount' => $amount * 100 // Stripe uses cents
        ];
    }

    private function createPayPalPayment(int $purchaseId, float $amount): array
    {
        // TODO: Integrate PayPal SDK
        return [
            'payment_id' => 'paypal_' . $purchaseId,
            'amount' => $amount
        ];
    }

    private function getPlanDuration(string $planType): int
    {
        $durations = [
            'boost_7days' => 7,
            'boost_30days' => 30,
            'premium_monthly' => 30,
            'premium_yearly' => 365
        ];
        return $durations[$planType] ?? 0;
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
            $dir = $base . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'candidates' . DIRECTORY_SEPARATOR . $candidateId;
            if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
            $file = 'receipt_' . (int)$purchase->attributes['id'] . '.pdf';
            $pdfPath = $dir . '/' . $file;
            file_put_contents($pdfPath, $pdfOutput);
            if (file_exists($pdfPath)) {
                return '/public/storage/uploads/candidates/' . $candidateId . '/' . $file;
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
            $row['receipt_url'] = '/public/storage/uploads/candidates/' . (int)$candidate->attributes['id'] . '/receipt_' . (int)$row['id'] . '.pdf';
            return $row;
        }, $purchases);

        $response->view('candidate/premium/billing', [
            'title' => 'Billing & Receipts',
            'candidate' => $candidate,
            'items' => $items
        ]);
    }
}

