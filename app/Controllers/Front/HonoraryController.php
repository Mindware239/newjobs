<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\HonoraryWorkshop;
use App\Models\PortalRegistration;
use App\Services\Registration\FormRegistry;
use App\Services\SeoService;

/**
 * Honorary Mentorship – /honorary-mentorship (upcoming one-day 4-hour workshops, free for learners),
 * /honorary-mentorship/join/{id} (learners join free), workshop add / cancel from the mentor's status page.
 */
class HonoraryController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        SeoService::getInstance()->setMeta([
            'title' => 'Honorary Mentorship – Free One-Day 4-Hour Skill Workshops | Jobsence',
            'description' => 'Experienced honorary mentors teach free in one-day, 4-hour skill workshops held in person in Dwarka, New Delhi. Learners join free.',
            'canonical' => $base . '/honorary-mentorship',
            'robots' => 'index, follow',
        ]);
        $flash = $_SESSION['honor_flash'] ?? null;
        unset($_SESSION['honor_flash']);
        $response->view('front/honorary/index', ['workshops' => HonoraryWorkshop::upcoming(), 'flash' => $flash], 200, 'layout');
    }

    /** GET /honorary-mentorship/join/{id} – registered learners join at once; others register (free) first. */
    public function join(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $learner = PortalRegistration::findByToken((string)($_SESSION['honor_learner_token'] ?? ''));
        if ($learner && $learner['type'] === 'honorlearn' && $learner['payment_status'] === 'paid') {
            $_SESSION['honor_flash'] = self::signupMessage(HonoraryWorkshop::signup($id, (int)$learner['id']));
            $response->redirect('/apply/status/' . $learner['token'] . '#workshops');
            return;
        }
        $_SESSION['honor_join'] = $id;
        $response->redirect('/apply/honorary-learner');
    }

    /** POST /honorary-mentorship/workshop – an active honorary mentor adds a workshop. */
    public function add(Request $request, Response $response): void
    {
        $mentor = self::activeMentor((string)$request->post('token', ''));
        if (!$mentor) {
            $response->redirect('/honorary-mentorship');
            return;
        }
        $date = (string)$request->post('date', '');
        $start = (string)$request->post('start', '');
        $mode = 'offline'; // honorary workshops: in person, Dwarka (New Delhi) only
        $v = static fn(string $k, int $max) => mb_substr(trim((string)$request->post($k, '')), 0, $max);
        $w = [
            'title' => $v('title', 190), 'skill' => (string)(FormRegistry::HONORARY_SKILLS[(string)$request->post('skill', '')][1] ?? ''), 'date' => $date, 'start' => $start, 'mode' => $mode,
            'city' => FormRegistry::HONORARY_PLACE[1], 'venue' => $v('venue', 500) ?: \App\Services\Registration\Mentoring::legalAddress(), 'language' => $v('language', 60) ?: null,
            'seats' => max(1, min(500, (int)$request->post('seats', 30))),
        ];
        $t = strtotime($date);
        $until = $mentor['valid_until'] ? strtotime((string)$mentor['valid_until']) : strtotime('+6 months');
        $err = null;
        if (mb_strlen($w['title']) < 5 || $w['skill'] === '') {
            $err = 'वर्कशॉप का नाम भरें और सूची से स्किल चुनें / Enter the workshop title and choose a skill from the list';
        } elseif (!preg_match('/dwarka|द्वारका/iu', (string)$w['venue'])) {
            $err = 'वर्कशॉप केवल द्वारका, नई दिल्ली में हो सकती है – द्वारका का पता भरें / Workshops are held only in Dwarka, New Delhi – enter a Dwarka address';
        } elseif (!$t || $t < strtotime('tomorrow') || $t > $until) {
            $err = 'तारीख कल से आपके प्लान की वैधता तक होनी चाहिए / The date must be from tomorrow until your plan is valid';
        } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start) || $start < '06:00' || $start > '18:00') {
            $err = 'शुरू होने का समय 6:00 AM से 6:00 PM के बीच रखें / Start between 6:00 AM and 6:00 PM (4-hour workshop)';
        }
        if ($err === null) {
            HonoraryWorkshop::create((int)$mentor['id'], $w);
        }
        $_SESSION['honor_flash'] = $err ?? 'वर्कशॉप जोड़ दी गई – यह /honorary-mentorship पर दिख रही है / Workshop added – it is listed on /honorary-mentorship';
        $response->redirect('/apply/status/' . $mentor['token'] . '#workshops');
    }

    /** POST /honorary-mentorship/workshop/{id}/cancel */
    public function cancel(Request $request, Response $response): void
    {
        $mentor = self::activeMentor((string)$request->post('token', ''), false);
        if ($mentor) {
            HonoraryWorkshop::cancel((int)$request->param('id'), (int)$mentor['id']);
            $_SESSION['honor_flash'] = 'वर्कशॉप रद्द की गई / Workshop cancelled';
            $response->redirect('/apply/status/' . $mentor['token'] . '#workshops');
            return;
        }
        $response->redirect('/honorary-mentorship');
    }

    public static function signupMessage(string $result): string
    {
        return [
            'ok' => 'आप वर्कशॉप में जुड़ गए – विवरण नीचे है / You have joined the workshop – details below',
            'already' => 'आप इस वर्कशॉप में पहले से जुड़े हैं / You have already joined this workshop',
            'full' => 'यह वर्कशॉप भर गई है – कोई दूसरी चुनें / This workshop is full – please choose another',
        ][$result] ?? 'यह वर्कशॉप अब उपलब्ध नहीं है / This workshop is no longer available';
    }

    private static function activeMentor(string $token, bool $mustBeValid = true): ?array
    {
        $m = $token !== '' ? PortalRegistration::findByToken($token) : null;
        if (!$m || $m['type'] !== 'honormentor' || $m['payment_status'] !== 'paid') {
            return null;
        }
        return (!$mustBeValid || $m['valid_until'] === null || strtotime((string)$m['valid_until']) > time()) ? $m : null;
    }
}
