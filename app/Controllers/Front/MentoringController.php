<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\Registration\Mentoring;
use App\Services\SeoService;

/**
 * Young India mentoring screens.
 *   /skill-seekers, /internship-seekers        who wants which skill / internship (name, place, skills, timing)
 *   /skill-mentors, /internship-providers      verified mentors, institutes and companies
 *   /mentoring                                 my dashboard (identity from /mentoring/access/{token})
 *   /mentoring/seeker/{id}                     full profile (provider plan, 3 a day)
 *   /mentoring/agreement/{token}               tripartite agreement – sign with email OTP
 * Phone and email are never shown before both parties have paid and signed.
 */
class MentoringController extends BaseController
{
    private const PER_PAGE = 30;

    // ------------------------------------------------------------------
    // Identity & dashboard
    // ------------------------------------------------------------------

    public function access(Request $request, Response $response): void
    {
        $reg = Mentoring::identify((string)$request->param('token'));
        if (!$reg) {
            $this->flash(false, ['यह लिंक मान्य नहीं है या भुगतान बाकी है', 'This link is not valid or payment is pending']);
        }
        $response->redirect('/mentoring');
    }

    public function logout(Request $request, Response $response): void
    {
        Mentoring::forget();
        $response->redirect('/mentoring');
    }

    public function dashboard(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $this->meta('Young India Mentoring – Skill & Internship | Jobsence', 'Jobsence initiative for young India: free registration for mentors, institutes and internship providers; skill and internship seekers matched with a tripartite agreement.', '/mentoring', $me === null);
        $data = ['me' => $me, 'flash' => $this->takeFlash(), 'stats' => ['skill' => Mentoring::stats('skill'), 'internship' => Mentoring::stats('internship'), 'job' => Mentoring::stats('job')]];
        if ($me) {
            $role = Mentoring::roleOf((string)$me['type']);
            $kind = Mentoring::kindOf((string)$me['type']);
            $plan = $role === 'provider' ? Mentoring::activePlan($me) : null;
            $data += [
                'role' => $role,
                'kind' => $kind,
                'k' => Mentoring::KINDS[$kind],
                'state' => $role === 'seeker' ? Mentoring::seekerState($me) : null,
                'verified' => $role === 'provider' && Mentoring::isVerified($me),
                'plan' => $plan,
                'quota' => $plan ? Mentoring::quota($plan) : null,
                'agreements' => Mentoring::agreementsFor($me),
                'opened' => $role === 'provider' ? Mentoring::openedBy((int)$me['id']) : [],
            ];
        }
        $response->view('front/mentoring/dashboard', $data, 200, 'layout');
    }

    /** POST /mentoring/plan – start the ₹155 provider plan payment. */
    public function buyPlan(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        if (!$me || Mentoring::roleOf((string)$me['type']) !== 'provider') {
            $response->redirect('/mentoring');
            return;
        }
        $plan = Mentoring::createPlan($me, (string)$request->post('option', ''));
        if (!$plan) {
            $this->flash(false, Mentoring::planOptions($me)
                ? ['प्लान चुनें', 'Choose a plan']
                : ['प्लान शुरू नहीं हो सका, दोबारा प्रयास करें', 'Could not start the plan, please try again']);
            $response->redirect('/mentoring');
            return;
        }
        $response->redirect('/apply/pay/' . $plan['token']);
    }

    // ------------------------------------------------------------------
    // Directories
    // ------------------------------------------------------------------

    public function skillSeekers(Request $request, Response $response): void
    {
        $this->seekers('skill', $request, $response);
    }

    public function internshipSeekers(Request $request, Response $response): void
    {
        $this->seekers('internship', $request, $response);
    }

    public function jobSeekers(Request $request, Response $response): void
    {
        $this->seekers('job', $request, $response);
    }

    public function hiringCompanies(Request $request, Response $response): void
    {
        $this->providers('job', $request, $response);
    }

    public function skillMentors(Request $request, Response $response): void
    {
        $this->providers('skill', $request, $response);
    }

    public function internshipProviders(Request $request, Response $response): void
    {
        $this->providers('internship', $request, $response);
    }

    private function filters(Request $request): array
    {
        return [
            'q' => mb_substr(trim((string)$request->get('q', '')), 0, 100),
            'loc' => mb_substr(trim((string)$request->get('loc', '')), 0, 80),
            'pref' => mb_substr(trim((string)$request->get('pref', '')), 0, 80),
            'qual' => (string)$request->get('qual', ''),
            'timing' => (string)$request->get('timing', ''),
        ];
    }

    private function seekers(string $kind, Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $f = $this->filters($request);
        $page = max(1, (int)$request->get('page', 1));
        $isProvider = $me && Mentoring::roleOf((string)$me['type']) === 'provider' && Mentoring::kindOf((string)$me['type']) === $kind;
        $plan = $isProvider ? Mentoring::activePlan($me) : null;
        $list = Mentoring::seekers($kind, $f, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
        $searched = array_filter($f) !== [];
        $label = Mentoring::KINDS[$kind]['seekers_label'][1];
        $this->meta("{$label} in India – Who Wants What, Where | Jobsence", "See how many people across India are {$label}: skills, roles, locations, qualifications and preferred timings. Mentors, institutes and companies register free on Jobsence.", (string)Mentoring::KINDS[$kind]['seekers_page'], !$searched && $page === 1);
        $response->view('front/mentoring/seekers', [
            'kind' => $kind, 'k' => Mentoring::KINDS[$kind], 'me' => $me, 'isProvider' => $isProvider, 'plan' => $plan,
            'quota' => $plan ? Mentoring::quota($plan) : null, 'f' => $f, 'page' => $page, 'perPage' => self::PER_PAGE,
            'rows' => $list['rows'], 'total' => $list['total'], 'stats' => Mentoring::stats($kind),
            'unpaid' => $isProvider ? Mentoring::unpaidSeekers($kind, $f, 20) : [],
            'flash' => $this->takeFlash(),
        ], 200, 'layout');
    }

    private function providers(string $kind, Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $f = $this->filters($request);
        $page = max(1, (int)$request->get('page', 1));
        $isSeeker = $me && Mentoring::roleOf((string)$me['type']) === 'seeker' && Mentoring::kindOf((string)$me['type']) === $kind;
        $list = Mentoring::providers($kind, $f, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
        $label = Mentoring::KINDS[$kind]['providers_label'][1];
        $this->meta("Verified {$label} in India | Jobsence", match ($kind) {
            'skill' => 'Verified skill mentors, group mentors and training institutes across India – online and offline. Choose up to 5 skills and connect through a Jobsence agreement.',
            'job' => 'Verified companies, shops, factories and institutes hiring across India – full-time, part-time, work from home and one-time work. Connect through a Jobsence agreement.',
            default => 'Verified companies, startups and institutes offering paid and unpaid internships across India. Connect through a Jobsence agreement.',
        }, Mentoring::KINDS[$kind]['providers_page'], array_filter($f) === [] && $page === 1);
        $response->view('front/mentoring/providers', [
            'kind' => $kind, 'k' => Mentoring::KINDS[$kind], 'me' => $me, 'isSeeker' => $isSeeker,
            'seekerState' => $isSeeker ? Mentoring::seekerState($me) : null, 'f' => $f, 'page' => $page, 'perPage' => self::PER_PAGE,
            'rows' => $list['rows'], 'total' => $list['total'], 'flash' => $this->takeFlash(),
        ], 200, 'layout');
    }

    // ------------------------------------------------------------------
    // Profiles, requests, invites
    // ------------------------------------------------------------------

    /** POST /mentoring/open/{id} – use one of today's 3 profile views. */
    public function open(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $seeker = PortalRegistration::find((int)$request->param('id'));
        if (!$me || !$seeker || Mentoring::roleOf((string)$me['type']) !== 'provider') {
            $response->redirect('/mentoring');
            return;
        }
        $r = Mentoring::openProfile($me, $seeker);
        if (!$r['ok']) {
            $this->flash(false, $r['msg']);
            $response->redirect($this->back($request, (string)(Mentoring::KINDS[Mentoring::kindOf((string)$me['type'])]['seekers_page'] ?? '/mentoring')));
            return;
        }
        $response->redirect('/mentoring/seeker/' . (int)$seeker['id']);
    }

    public function seeker(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $seeker = PortalRegistration::find((int)$request->param('id'));
        if (!$me || !$seeker || Mentoring::roleOf((string)$me['type']) !== 'provider' || !Mentoring::hasOpened((int)$me['id'], (int)$seeker['id'])) {
            $this->flash(false, ['यह प्रोफ़ाइल खोलने के लिए सूची से “पूरी प्रोफ़ाइल” दबाएँ', 'Use “Full profile” in the list to open this profile']);
            $response->redirect('/mentoring');
            return;
        }
        if (!Mentoring::activePlan($me)) {
            $this->flash(false, ['प्रोफ़ाइल देखने के लिए सक्रिय ₹155 प्लान ज़रूरी है', 'An active ₹155 plan is needed to see profiles']);
            $response->redirect('/mentoring');
            return;
        }
        $agreement = null;
        foreach (Mentoring::agreementsFor($me) as $a) {
            if ((int)$a['candidate_reg_id'] === (int)$seeker['id'] && in_array($a['status'], ['pending', 'accepted'], true)) {
                $agreement = $a;
                break;
            }
        }
        $this->meta('Candidate profile – Jobsence', '', '/mentoring', false);
        $response->view('front/mentoring/seeker', [
            'me' => $me, 'seeker' => $seeker, 'state' => Mentoring::seekerState($seeker), 'agreement' => $agreement, 'flash' => $this->takeFlash(),
        ], 200, 'layout');
    }

    /** GET /mentoring/resume/{id} – resume of a profile this provider opened (active plan) or signed with. */
    public function resume(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $seeker = PortalRegistration::find((int)$request->param('id'));
        $allowed = $me && $seeker && Mentoring::roleOf((string)$me['type']) === 'provider'
            && Mentoring::hasOpened((int)$me['id'], (int)$seeker['id']) && Mentoring::activePlan($me);
        $root = realpath(dirname(__DIR__, 3) . '/storage/uploads/registrations');
        $path = ($allowed && !empty($seeker['resume_path'])) ? realpath(dirname(__DIR__, 3) . '/' . $seeker['resume_path']) : false;
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code($allowed ? 404 : 403);
            exit;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'][$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9]+/', '_', (string)$seeker['full_name']) . '_resume.' . $ext . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** POST /mentoring/request/{id} – start an agreement with a seeker (provider) or provider (seeker). */
    public function requestAgreement(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $other = PortalRegistration::find((int)$request->param('id'));
        if (!$me || !$other) {
            $this->flash(false, ['पहले अपने ईमेल / रसीद के लिंक से डैशबोर्ड खोलें', 'First open your dashboard from the link in your email / receipt']);
            $response->redirect('/mentoring');
            return;
        }
        $r = Mentoring::request($me, $other);
        if (!$r['ok']) {
            $this->flash(false, $r['msg']);
            $response->redirect($this->back($request, '/mentoring'));
            return;
        }
        $response->redirect(Mentoring::linkFor($r['assignment'], (string)Mentoring::roleOf((string)$me['type'])));
    }

    /** POST /mentoring/invite/{id} – ask an unpaid seeker to pay the form fee. */
    public function invite(Request $request, Response $response): void
    {
        $me = Mentoring::me();
        $seeker = PortalRegistration::find((int)$request->param('id'));
        if (!$me || !$seeker) {
            $response->redirect('/mentoring');
            return;
        }
        $r = Mentoring::inviteToPay($me, $seeker);
        $this->flash($r['ok'], $r['msg']);
        $response->redirect($this->back($request, '/mentoring'));
    }

    // ------------------------------------------------------------------
    // Agreement
    // ------------------------------------------------------------------

    public function agreement(Request $request, Response $response): void
    {
        $found = Mentoring::findAgreement((string)$request->param('token'));
        if (!$found) {
            $this->meta('Agreement not found – Jobsence', '', '/mentoring', false);
            $response->view('front/mentoring/agreement', ['a' => null, 'flash' => null], 404, 'layout');
            return;
        }
        [$a, $role] = $found;
        [$seeker, $provider] = Mentoring::parties($a);
        // The emailed link identifies this party on the other mentoring screens too.
        $mine = $role === 'provider' ? $provider : $seeker;
        if ($mine && !Mentoring::me()) {
            Mentoring::identify((string)$mine['token']);
        }
        $this->meta('Jobsence Tripartite Agreement JSA-' . (int)$a['id'], '', '/mentoring', false);
        $response->view('front/mentoring/agreement', [
            'a' => $a, 'role' => $role, 'seeker' => $seeker, 'provider' => $provider,
            'token' => (string)$request->param('token'),
            'blocker' => ($role === 'provider' ? empty($a['mentor_signed_at']) : empty($a['candidate_signed_at'])) ? Mentoring::signBlocker($a, $role) : null,
            'otpSent' => !empty($_SESSION['mentoring_otp_sent'][$a['id'] . $role]) && $_SESSION['mentoring_otp_sent'][$a['id'] . $role] > time() - 600,
            'flash' => $this->takeFlash(),
        ], 200, 'layout');
    }

    public function agreementOtp(Request $request, Response $response): void
    {
        $token = (string)$request->param('token');
        $found = Mentoring::findAgreement($token);
        if ($found) {
            [$a, $role] = $found;
            if ($blocker = Mentoring::signBlocker($a, $role)) {
                $this->flash(false, $blocker);
            } else {
                $r = Mentoring::sendOtp($a, $role);
                $this->flash(!empty($r['success']), !empty($r['success'])
                    ? ['OTP आपके ईमेल पर भेजा गया (10 मिनट तक मान्य)', 'OTP sent to your email (valid for 10 minutes)']
                    : ['OTP नहीं भेजा जा सका', (string)($r['error'] ?? 'Could not send OTP')]);
            }
        }
        $response->redirect('/mentoring/agreement/' . $token . '#sign');
    }

    public function agreementSign(Request $request, Response $response): void
    {
        $token = (string)$request->param('token');
        $found = Mentoring::findAgreement($token);
        if ($found) {
            [$a, $role] = $found;
            if (!$request->post('agree')) {
                $this->flash(false, ['साइन करने से पहले समझौते की शर्तें स्वीकार करें', 'Accept the agreement terms before signing']);
            } else {
                $r = Mentoring::sign($a, $role, trim((string)$request->post('otp', '')), (string)$request->ip());
                $this->flash($r['ok'], $r['msg']);
            }
        }
        $response->redirect('/mentoring/agreement/' . $token);
    }

    public function agreementDecline(Request $request, Response $response): void
    {
        $token = (string)$request->param('token');
        if ($found = Mentoring::findAgreement($token)) {
            [$a, $role] = $found;
            if (Mentoring::decline($a, $role)) {
                $this->flash(true, ['अनुरोध अस्वीकार किया गया', 'Request declined']);
            }
        }
        $response->redirect('/mentoring/agreement/' . $token);
    }

    public function agreementEnd(Request $request, Response $response): void
    {
        $token = (string)$request->param('token');
        if ($found = Mentoring::findAgreement($token)) {
            [$a, $role] = $found;
            if (!$request->post('confirm')) {
                $this->flash(false, ['पुष्टि के लिए बॉक्स पर टिक करें', 'Tick the box to confirm']);
            } elseif (Mentoring::end($a, $role, mb_substr(trim((string)$request->post('reason', '')), 0, 500))) {
                $this->flash(true, $role === 'seeker'
                    ? ['समझौता समाप्त। आपका रजिस्ट्रेशन बंद हो गया – नए मेंटर / इंटर्नशिप के लिए नया फॉर्म भरें।', 'Agreement ended. Your registration is closed – fill a new form for a new mentor / internship.']
                    : ['समझौता समाप्त। उम्मीदवार का रजिस्ट्रेशन खुला रहेगा।', 'Agreement ended. The candidate’s registration stays open.']);
            }
        }
        $response->redirect('/mentoring/agreement/' . $token);
    }

    /** GET /agreement-terms/{kind} – the current standard tripartite agreement, readable before paying. */
    public function terms(Request $request, Response $response, array $params = []): void
    {
        $kind = (string)($params['kind'] ?? $request->param('kind'));
        if (!isset(Mentoring::KINDS[$kind])) {
            $response->redirect('/agreement-terms/skill');
            return;
        }
        $this->meta('Jobsence Tripartite Agreement – ' . Mentoring::KINDS[$kind]['what'][1] . ' (Standard Terms)', 'The standard Jobsence tripartite agreement between candidate, provider and Jobsence (operated by ' . Mentoring::legalName() . '), signed with an email OTP.', '/agreement-terms/' . $kind, true);
        $response->view('front/mentoring/terms', ['kind' => $kind], 200, 'layout');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function meta(string $title, string $description, string $path, bool $index): void
    {
        $meta = [
            'title' => $title,
            'h1' => $title,
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . $path,
            'robots' => $index ? 'index, follow' : 'noindex, follow',
            'keywords' => 'skill development india, free mentor registration, mentor near me, internship providers india, training institutes, young india skills, jobsence initiative',
        ];
        if ($description !== '') {
            $meta['description'] = $description;
        }
        SeoService::getInstance()->setMeta($meta);
    }

    private function flash(bool $ok, array $msg): void
    {
        $_SESSION['mt_flash'] = ['ok' => $ok, 'msg' => $msg];
    }

    private function takeFlash(): ?array
    {
        $f = $_SESSION['mt_flash'] ?? null;
        unset($_SESSION['mt_flash']);
        return $f;
    }

    /** Same-site return path from the form, else $default. */
    private function back(Request $request, string $default): string
    {
        $b = (string)$request->post('back', '');
        return (str_starts_with($b, '/') && !str_starts_with($b, '//')) ? $b : $default;
    }
}
