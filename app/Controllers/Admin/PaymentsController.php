<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class PaymentsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(ep.txn_id LIKE :search OR e.company_name LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== 'all') {
            $where[] = "ep.status = :status";
            $params['status'] = $status;
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

        // Get payments
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

        $totalPages = ceil($total / $perPage);

        $response->view('admin/payments/index', [
            'title' => 'Manage Payments',
            'payments' => $payments,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $payment = $db->fetchOne(
            "SELECT ep.*, e.company_name, u.email as employer_email
             FROM employer_payments ep
             LEFT JOIN employers e ON e.id = ep.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE ep.id = :id",
            ['id' => $id]
        );

        if (!$payment) {
            $response->redirect('/admin/payments');
            return;
        }

        $response->view('admin/payments/show', [
            'title' => 'Payment Details',
            'payment' => $payment,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function refund(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $amount = (float)($request->post('amount', 0));
        $reason = $request->post('reason', '');

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $payment = $db->fetchOne("SELECT * FROM employer_payments WHERE id = :id FOR UPDATE", ['id' => $id]);

            if ($payment && ($payment['status'] === 'completed' || $payment['status'] === 'success')) {
                // Check for existing refund
                $existingRefund = $db->fetchOne(
                    "SELECT id FROM employer_payments WHERE txn_id LIKE :tid",
                    ['tid' => 'REFUND-' . $payment['txn_id'] . '%']
                );
                if ($existingRefund) {
                     $db->commit();
                     $response->redirect('/admin/payments/' . $id . '?error=duplicate_refund');
                     return;
                }

                $refundTxnId = null;
                // Check gateway field, not payment_method
                if (($payment['gateway'] ?? '') === 'razorpay' && !empty($payment['txn_id'])) {
                    $config = require __DIR__ . '/../../../config/razorpay.php';
                    $this->configureSslCa();
                    $api = new \Razorpay\Api\Api($config['key_id'], $config['key_secret']);
                    $refundAmount = (int)(abs($amount ?: $payment['amount']) * 100);

                    $refund = $api->payment->fetch($payment['txn_id'])->refund([
                        'amount' => $refundAmount,
                        'notes' => ['reason' => $reason, 'admin_user_id' => $this->currentUser->id]
                    ]);
                    
                    $refundTxnId = $refund['id'];
                }

                // Create refund record
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

                // Mark original payment as refunded if full refund
                if ($amount <= 0 || $amount >= (float)$payment['amount']) {
                    $db->query("UPDATE employer_payments SET status = 'refunded' WHERE id = :id", ['id' => $id]);
                }

                $this->logAction('refund_payment', ['payment_id' => $id, 'amount' => $amount, 'reason' => $reason]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            error_log('Refund Error: ' . $e->getMessage());
            $response->redirect('/admin/payments/' . $id . '?error=refund_failed');
            return;
        }

        $response->redirect('/admin/payments/' . $id);
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    private function logAction(string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :old_value, :new_value, :ip_address, NOW())",
                [
                    'user_id' => $this->currentUser->id,
                    'action' => $action,
                    'entity_type' => 'payment',
                    'entity_id' => $data['payment_id'] ?? null,
                    'old_value' => json_encode($data),
                    'new_value' => json_encode(['status' => 'refunded']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    private function configureSslCa(): void
    {
        $caPath = __DIR__ . '/../../../vendor/razorpay/razorpay/src/cacert.pem';
        if (file_exists($caPath)) {
            ini_set('curl.cainfo', $caPath);
            ini_set('openssl.cafile', $caPath);
        }
    }
}

