<?php

declare(strict_types=1);

namespace App\Controllers\VerificationExecutive;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userId = (int)($this->currentUser->id ?? 0);

        $stats = [
            'assigned' => (int)($db->fetchOne("SELECT COUNT(*) c FROM employers WHERE kyc_assigned_to = :uid", ['uid' => $userId])['c'] ?? 0),
            'pending' => (int)($db->fetchOne("SELECT COUNT(*) c FROM employer_kyc_documents d INNER JOIN employers e ON e.id = d.employer_id WHERE e.kyc_assigned_to = :uid AND d.review_status = 'pending'", ['uid' => $userId])['c'] ?? 0),
            'approved' => (int)($db->fetchOne("SELECT COUNT(*) c FROM employer_kyc_documents d INNER JOIN employers e ON e.id = d.employer_id WHERE e.kyc_assigned_to = :uid AND d.review_status = 'approved'", ['uid' => $userId])['c'] ?? 0),
            'rejected' => (int)($db->fetchOne("SELECT COUNT(*) c FROM employer_kyc_documents d INNER JOIN employers e ON e.id = d.employer_id WHERE e.kyc_assigned_to = :uid AND d.review_status = 'rejected'", ['uid' => $userId])['c'] ?? 0),
        ];

        $response->view('verification_executive/dashboard', [
            'title' => 'Verification Executive Dashboard',
            'stats' => $stats
        ], 200, 'masteradmin/layout');
    }
}

