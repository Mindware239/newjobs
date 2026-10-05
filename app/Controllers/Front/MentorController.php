<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\Registration\Mentoring;

/**
 * Older emailed links, kept working:
 *   /mentor/respond/{token}   mentor request  → the tripartite agreement page (sign with email OTP)
 *   /apply/mentors/{token}    choose a mentor → the mentors directory (/skill-mentors)
 */
class MentorController extends BaseController
{
    public function respondForm(Request $request, Response $response): void
    {
        $response->redirect('/mentoring/agreement/' . (string)$request->param('token'));
    }

    public function respond(Request $request, Response $response): void
    {
        $found = Mentoring::findAgreement((string)$request->param('token'));
        if ($found && $found[1] === 'provider' && (string)$request->post('action', '') === 'decline') {
            Mentoring::decline($found[0], 'provider');
        }
        $response->redirect('/mentoring/agreement/' . (string)$request->param('token'));
    }

    public function choose(Request $request, Response $response): void
    {
        $this->toDirectory($request, $response);
    }

    public function chooseSubmit(Request $request, Response $response): void
    {
        $this->toDirectory($request, $response);
    }

    private function toDirectory(Request $request, Response $response): void
    {
        $candidate = PortalRegistration::findByToken((string)$request->param('token'));
        if (!$candidate || $candidate['type'] !== 'skill' || $candidate['payment_status'] !== 'paid') {
            $response->redirect('/apply/skill-development');
            return;
        }
        Mentoring::identify((string)$candidate['token']);
        $response->redirect('/skill-mentors');
    }
}
