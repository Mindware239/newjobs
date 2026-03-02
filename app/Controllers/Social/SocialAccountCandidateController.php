<?php

declare(strict_types=1);

namespace App\Controllers\Social;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SocialAccountCandidate;

class SocialAccountCandidateController extends BaseController
{
    private SocialAccountCandidate $candidateModel;

    public function __construct()
    {
        parent::__construct();
        $this->candidateModel = new SocialAccountCandidate();
    }

    /**
     * READ: View OWN Candidate Profile (SOCIAL)
     */
    public function view(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->redirect('/social-services/login?redirect=' . urlencode($request->getPath()));
            return;
        }

        // ✅ Fetch profile by logged-in user_id
        $candidate = $this->candidateModel->findByUserId(
            (int) $this->currentUser->id
        );

        if (!$candidate) {
            $response->redirect('/candidate/profile/create');
            return;
        }

        // ✅ Social view + layout
        $response->view('social/candidate/profile', [
            'title'     => 'My Profile',
            'candidate' => $candidate,
            'user'      => $this->currentUser
        ], 200, 'social/layout');
    }
    public function accountcandidate(Request $request, Response $response): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $uid = $_SESSION['user_id'] ?? null;
        if (!$uid) {
            \App\Middlewares\CsrfMiddleware::generateToken();
            $response->view('social-services/login', ['redirect' => $request->getPath()]);
            return;
        }
        $user = \App\Models\User::find((int)$uid);
        if (!$user) {
            session_destroy();
            session_start();
            session_regenerate_id(true);
            \App\Middlewares\CsrfMiddleware::generateToken();
            $response->view('social-services/login', ['redirect' => $request->getPath()]);
            return;
        }
        $role = strtolower((string)($user->role ?? ''));
        if (in_array($role, ['employer','social_employer','social-employer'], true)) {
            $response->redirect('/social-employer/account');
            return;
        }
        \App\Middlewares\CsrfMiddleware::generateToken();
        $response->view('social-candidate/accountcandidate');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->setStatusCode(401);
            $response->json(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $data = $request->isAjax() ? $request->getJsonBody() : $request->post();
        $full = trim((string)($data['full_name'] ?? ''));
        $pref = trim((string)($data['preferred_name'] ?? ''));
        if ($full === '' && $pref !== '') { $full = $pref; }
        if ($pref === '' && $full !== '') { $pref = $full; }
        if ($full === '' && $pref === '') {
            $fallbackName = trim((string)($this->currentUser->google_name ?? $this->currentUser->name ?? ''));
            if ($fallbackName !== '') {
                $full = $fallbackName;
                $pref = $pref !== '' ? $pref : $fallbackName;
            }
        }
        if ($full === '' || $pref === '') {
            $response->setStatusCode(400);
            $response->json(['success' => false, 'error' => 'Missing required fields']);
            return;
        }

        // Split name
        $parts = explode(' ', $full, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';

        // Map fields
        $roles = is_array($data['roles'] ?? []) ? implode(', ', $data['roles']) : ($data['roles'] ?? '');
        $focusAreas = is_array($data['focus_areas'] ?? []) ? implode(', ', $data['focus_areas']) : ($data['focus_areas'] ?? '');

        // Prepare for model
        $saveData = [
            'user_id' => (int) $this->currentUser->id,
            'email' => $this->currentUser->email ?? '',
            'phone' => $this->currentUser->phone ?? '', 
            'first_name' => $firstName,
            'last_name' => $lastName,
            'preferred_name' => $pref,
            'pronouns' => $data['pronouns'] ?? null,
            'work_category' => $roles,
            'role_type' => $focusAreas,
        ];

        // Save
        try {
            $this->candidateModel->saveOrUpdate($saveData);
            $response->json(['success' => true]);
        } catch (\Exception $e) {
            $response->setStatusCode(500);
            $response->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
