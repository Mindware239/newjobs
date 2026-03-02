<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\EmploymentVerificationService;

class HRVerificationController extends BaseController
{
    public function show(Request $request, Response $response): void
    {
        $token = (string)$request->get('token', '');
        if ($token === '') { $response->view('front/verification/error', ['title' => 'Invalid Link'], 200, 'layout'); return; }
        $db = Database::getInstance();
        $req = $db->fetchOne("SELECT vr.*, er.company_name, c.full_name FROM verification_requests vr INNER JOIN employment_records er ON er.id = vr.employment_id INNER JOIN candidates c ON c.id = er.candidate_id WHERE vr.token = :tok", ['tok' => $token]);
        if (!$req) { $response->view('front/verification/error', ['title' => 'Invalid Link'], 200, 'layout'); return; }
        $expired = strtotime((string)$req['expires_at']) < time();
        if ($expired) { $response->view('front/verification/error', ['title' => 'Link Expired'], 200, 'layout'); return; }
        $response->view('front/verification/hr_form', [
            'title' => 'Employment Verification',
            'request' => $req
        ], 200, 'layout');
    }

    public function submit(Request $request, Response $response): void
    {
        $token = (string)$request->post('token', '');
        $data = [
            'status' => (string)$request->post('status', 'verified'),
            'confirmed_working' => (int)($request->post('confirmed_working') ?? 0) === 1,
            'duration_text' => (string)$request->post('duration_text', ''),
            'designation' => (string)$request->post('designation', ''),
            'rehire_eligibility' => (string)$request->post('rehire_eligibility', 'unknown'),
            'misconduct' => (int)($request->post('misconduct') ?? 0) === 1,
            'remarks' => (string)$request->post('remarks', '')
        ];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ok = EmploymentVerificationService::recordHrResponse($token, $data, $ip);
        if ($ok) { $response->view('front/verification/success', ['title' => 'Thank You'], 200, 'layout'); return; }
        $response->view('front/verification/error', ['title' => 'Invalid or expired link'], 200, 'layout');
    }
}
