<?php

declare(strict_types=1);

namespace App\Controllers\FinanceManager;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class PaymentsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $page = (int)($request->get('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $status = (string)$request->get('status', 'all');
        $search = (string)$request->get('search', '');

        $where = [];
        $params = [];

        if ($status !== 'all') { 
            $where[] = 'ep.status = :status'; 
            $params['status'] = $status; 
        }

        if ($search !== '') {
            $where[] = '(e.company_name LIKE :search OR ep.txn_id LIKE :search OR u.email LIKE :search)';
            $params['search'] = "%$search%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM employer_payments ep
             LEFT JOIN employers e ON e.id = ep.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             {$whereClause}",
            $params
        )['count'] ?? 0);

        $totalPages = ceil($total / $perPage);

        // Fetch paginated results
        $payments = $db->fetchAll(
            "SELECT ep.*, e.company_name, u.email as employer_email 
             FROM employer_payments ep
             LEFT JOIN employers e ON e.id = ep.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             {$whereClause}
             ORDER BY ep.created_at DESC 
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $response->view('finance_manager/payments/index', [
            'title' => 'Finance - Payments',
            'payments' => $payments,
            'status' => $status,
            'search' => $search,
            'pagination' => [
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ], 200, 'masteradmin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->param('id');
        $payment = $db->fetchOne(
            "SELECT ep.*, 
                    e.company_name, e.address, e.city, e.state, e.country, e.postal_code,
                    u.email as employer_email, u.phone as employer_phone, u.first_name, u.last_name
             FROM employer_payments ep
             LEFT JOIN employers e ON e.id = ep.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE ep.id = :id",
            ['id' => $id]
        );
        if (!$payment) { $response->redirect('/finance/payments'); return; }
        $response->view('finance_manager/payments/show', [
            'title' => 'Payment #' . $id,
            'payment' => $payment
        ], 200, 'masteradmin/layout');
    }

    public function approve(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $db->query("UPDATE employer_payments SET status = 'completed' WHERE id = :id", ['id' => $id]);
        $this->log('approve_payment', $id);
        $response->redirect('/finance/payments/' . $id);
    }

    public function refund(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $id = (int)$request->post('id');
        $amount = (float)$request->post('amount', 0);
        $reason = (string)$request->post('reason', '');
        
        $payment = $db->fetchOne("SELECT * FROM employer_payments WHERE id = :id", ['id' => $id]);
        
        // Allow refund for both 'success' (Razorpay auto) and 'completed' (Manual approved)
        if ($payment && in_array($payment['status'], ['success', 'completed'])) {
            // Prevent duplicate refunds
            $existingRefund = $db->fetchOne(
                "SELECT id FROM employer_payments WHERE description LIKE :desc AND employer_id = :eid",
                ['desc' => 'Refund: ' . $reason . '%', 'eid' => $payment['employer_id']]
            );
            
            // If already refunded fully (checked by status 'refunded' on original? No, original stays success/completed usually, or changes to refunded. 
            // Better to check if we have a refund record linked or if original is already refunded.
            // If the original record status is 'refunded', stop.
            if ($payment['status'] === 'refunded') {
                 $response->redirect('/finance/payments/' . $id . '?error=already_refunded');
                 return;
            }

            $db->beginTransaction();
            try {
                // Call Razorpay API if it's a Razorpay transaction
                if (($payment['gateway'] ?? '') === 'razorpay' && !empty($payment['txn_id'])) {
                    $config = require __DIR__ . '/../../../config/razorpay.php';
                    $this->configureSslCa();
                    $api = new \Razorpay\Api\Api($config['key_id'], $config['key_secret']);
                    
                    $refundAmount = ($amount > 0) ? (int)round($amount * 100) : (int)round((float)$payment['amount'] * 100);
                    
                    $refund = $api->payment->fetch($payment['txn_id'])->refund([
                        'amount' => $refundAmount,
                        'notes' => ['reason' => $reason, 'admin_user_id' => $this->currentUser->id]
                    ]);
                    
                    $refundTxnId = $refund['id']; // e.g. rfnd_...
                }

                $db->query(
                    "INSERT INTO employer_payments (employer_id, amount, currency, status, payment_method, txn_id, description, created_at)
                     VALUES (:employer_id, :amount, :currency, 'refunded', 'refund', :txn_id, :description, NOW())",
                    [
                        'employer_id' => $payment['employer_id'],
                        'amount' => -abs($amount ?: $payment['amount']),
                        'currency' => $payment['currency'],
                        'txn_id' => $refundTxnId ?? ('REFUND-' . $payment['txn_id']),
                        'description' => 'Refund: ' . $reason
                    ]
                );

                // Mark original as refunded if full refund
                if ($amount <= 0 || $amount >= (float)$payment['amount']) {
                    $db->query("UPDATE employer_payments SET status = 'refunded' WHERE id = :id", ['id' => $id]);
                }

                $this->log('refund_payment', $id);
                $db->commit();
            } catch (\Throwable $e) {
                $db->rollback();
                error_log("Refund failed: " . $e->getMessage());
                $response->redirect('/finance/payments/' . $id . '?error=refund_failed');
                return;
            }
        }
        $response->redirect('/finance/payments/' . $id);
    }

    private function log(string $action, int $paymentId): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, 'payment', :entity_id, NULL, NULL, :ip, NOW())",
                [
                    'user_id' => (int)($this->currentUser->id ?? 0),
                    'action' => $action,
                    'entity_id' => $paymentId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Throwable $t) {}
    }

    private function configureSslCa(): void
    {
        // Fix for cURL error 60 (SSL certificate problem) on Windows
        // Point to the bundled cacert.pem in vendor/razorpay/razorpay/src/
        // or use the system CA bundle if configured
        $caPath = __DIR__ . '/../../../vendor/razorpay/razorpay/src/cacert.pem';
        if (file_exists($caPath)) {
            // This is a bit of a hack since Razorpay SDK doesn't expose a way to set CURLOPT_CAINFO directly easily 
            // without modifying the Requests library it uses.
            // However, the Requests library (rmccue/requests) usually checks for a CA bundle.
            // If we are having issues, we might need to set ini_set('curl.cainfo', ...);
            ini_set('curl.cainfo', $caPath);
            ini_set('openssl.cafile', $caPath);
        }
    }
}

