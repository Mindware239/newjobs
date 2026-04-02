<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\EmployerProfile;
use App\Models\Employer;

class EmployerAccountController extends BaseController
{
    // ============================
    // SHOW ACCOUNT PAGE
    // ============================

public function account(Request $request, Response $response): void
{
    $profileModel = new EmployerProfile();

    $employerId = (int)($_SESSION['employer_id'] ?? 0);
    if ($employerId === 0 && !empty($_SESSION['user_id'])) {
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $appModelTmp = new \App\Models\SocialJobApplication($pdo);
            $derived = (int)($appModelTmp->getEmployerProfileId((int)$_SESSION['user_id']) ?? 0);
            if ($derived > 0) {
                $employerId = $derived;
                $_SESSION['employer_id'] = $employerId;
            }
        } catch (\Throwable $t) {}
    }

        if ($employerId <= 0) {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if ($uid <= 0) {
            $response->redirect('/social-services/login?redirect=/social-employer/account');
            return;
        }
        $existing = Employer::where('user_id', '=', $uid)->first();
        if ($existing && isset($existing->id)) {
            $employerId = (int)$existing->id;
            $_SESSION['employer_id'] = $employerId;
        } else {
                $slugBase = 'employer-' . $uid;
                $slug = $slugBase;
                $new = new Employer();
                $new->fill([
                    'user_id' => $uid,
                    'company_name' => '',
                    'company_slug' => $slug,
                    'verified' => 0
                ]);
                try {
                    $new->save();
                } catch (\Throwable $t) {
                    // Fallback: ensure unique slug if duplicate
                    $suffix = 1;
                    do {
                        $slug = $slugBase . '-' . $suffix++;
                        $new->attributes['company_slug'] = $slug;
                        try {
                            $new->save();
                            break;
                        } catch (\Throwable $e) {
                            // continue
                        }
                    } while ($suffix < 10);
                }
                // If still not inserted, try to fetch existing by user_id
                $employerId = (int)($new->attributes['id'] ?? 0);
                if ($employerId <= 0) {
                    $existing = Employer::where('user_id', '=', $uid)->first();
                    if ($existing && isset($existing->id)) {
                        $employerId = (int)$existing->id;
                    }
                }
                $_SESSION['employer_id'] = $employerId;
        }
    }

    /* PROFILE */
    $profile = null;
    try {
        $profile = $profileModel->findByUser((int)$_SESSION['user_id']);
    } catch (\Throwable $e) {}

    /* APPLICATIONS */
    $applications = [];
    try {
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $appModel = new \App\Models\SocialJobApplication($pdo);
        $applications = $appModel->employerApplicants($employerId);
    } catch (\Throwable $t) {}

    $response->view('social-employer/account', [
        'profile' => $profile,
        'applications' => $applications
    ]);
}


    // ============================
    // SAVE PROFILE
    // ============================

    public function store(Request $request, Response $response): void
    {
        $profileModel = new EmployerProfile();

        $employerId = (int)($_SESSION['employer_id'] ?? 0);
        if ($employerId === 0 && !empty($_SESSION['user_id'])) {
            $existing = Employer::where('user_id', '=', (int)$_SESSION['user_id'])->first();
            if ($existing && isset($existing->id)) {
                $employerId = (int)$existing->id;
                $_SESSION['employer_id'] = $employerId;
            } else {
                $uid = (int)$_SESSION['user_id'];
                $slugBase = 'employer-' . $uid;
                $slug = $slugBase;
                $new = new Employer();
                $new->fill([
                    'user_id' => $uid,
                    'company_name' => '',
                    'company_slug' => $slug,
                    'verified' => 0
                ]);
                try {
                    $new->save();
                } catch (\Throwable $t) {
                    $suffix = 1;
                    do {
                        $slug = $slugBase . '-' . $suffix++;
                        $new->attributes['company_slug'] = $slug;
                        try { $new->save(); break; } catch (\Throwable $e) {}
                    } while ($suffix < 10);
                }
                $employerId = (int)($new->attributes['id'] ?? 0);
                if ($employerId <= 0) {
                    $existing = Employer::where('user_id', '=', $uid)->first();
                    if ($existing && isset($existing->id)) {
                        $employerId = (int)$existing->id;
                    }
                }
                $_SESSION['employer_id'] = $employerId;
            }
        }

        $data = [
            'user_id'         => (int)($_SESSION['user_id'] ?? 0),
            'full_name'       => $request->post('full_name'),
            'preferred_name' => $request->post('preferred_name'),
            'pronouns'        => $request->post('pronouns'),
            'prefix'          => $request->post('prefix'),
            'first_name'      => $request->post('first_name'),
            'middle_name'     => $request->post('middle_name'),
            'last_name'       => $request->post('last_name'),
            'suffix'          => $request->post('suffix'),
        ];

        try {
            $id = $profileModel->create($data);
            $accept = $request->header('Accept') ?? '';
            if (strpos($accept, 'application/json') !== false) {
                $response->json(['success' => true, 'id' => $id]);
                return;
            }
            $response->redirect('/social-employer/account');
        } catch (\Throwable $t) {
            $msg = $t->getMessage();
            $accept = $request->header('Accept') ?? '';
            if (strpos($accept, 'application/json') !== false) {
                $response->json(['success' => false, 'error' => $msg], 500);
                return;
            }
            $response->view('social-employer/account', ['profile' => null, 'error' => $msg]);
        }
    }

    public function update(Request $request, Response $response): void
    {
        $profileModel = new EmployerProfile();
        $payload = $request->getJsonBody() ?? $request->all();
        $employerId = (int)($_SESSION['employer_id'] ?? 0);
        if ($employerId === 0 && !empty($_SESSION['user_id'])) {
            $existing = Employer::where('user_id', '=', (int)$_SESSION['user_id'])->first();
            if ($existing && isset($existing->id)) {
                $employerId = (int)$existing->id;
                $_SESSION['employer_id'] = $employerId;
            }
        }
        $data = [
            'full_name'       => $payload['full_name'] ?? $request->post('full_name'),
            'preferred_name'  => $payload['preferred_name'] ?? $request->post('preferred_name'),
            'pronouns'        => $payload['pronouns'] ?? $request->post('pronouns'),
            'prefix'          => $payload['prefix'] ?? $request->post('prefix'),
            'first_name'      => $payload['first_name'] ?? $request->post('first_name'),
            'middle_name'     => $payload['middle_name'] ?? $request->post('middle_name'),
            'last_name'       => $payload['last_name'] ?? $request->post('last_name'),
            'suffix'          => $payload['suffix'] ?? $request->post('suffix'),
        ];
        try {
            $current = $profileModel->findByUser((int)($_SESSION['user_id'] ?? 0));
            if ($current && isset($current['id'])) {
                $ok = $profileModel->update((int)$current['id'], $data);
                $accept = $request->header('Accept') ?? '';
                if (strpos($accept, 'application/json') !== false) {
                    $response->json(['success' => $ok]);
                    return;
                }
                $response->redirect('/social-employer/account');
                return;
            }
            $dataCreate = array_merge(['user_id' => (int)($_SESSION['user_id'] ?? 0)], $data);
            $id = $profileModel->create($dataCreate);
            $accept = $request->header('Accept') ?? '';
            if (strpos($accept, 'application/json') !== false) {
                $response->json(['success' => true, 'id' => $id]);
                return;
            }
            $response->redirect('/social-employer/account');
        } catch (\Throwable $t) {
            $msg = $t->getMessage();
            $accept = $request->header('Accept') ?? '';
            if (strpos($accept, 'application/json') !== false) {
                $response->json(['success' => false, 'error' => $msg], 500);
                return;
            }
            $response->view('social-employer/account', ['profile' => null, 'error' => $msg]);
        }
    }

}
