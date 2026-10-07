<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\SeoService;
use App\Services\VerificationService;

/**
 * Senior Citizen corner (user, 2026-10-07): retired people (59+) and the organisations that engage them –
 * for paid work or free community service – get their own login (/login/senior: registered mobile or
 * email → email OTP) and dashboard (/senior) with all their registrations and nearby matches.
 */
class SeniorController extends BaseController
{
    private const OTP_PURPOSE = 'senior_login';
    private const TYPES = ['senior', 'seniorhire'];

    /** GET /senior – dashboard when logged in, otherwise what the corner offers + login. */
    public function index(Request $request, Response $response): void
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        SeoService::getInstance()->setMeta([
            'title' => 'Senior Citizens (59+) – Work & Community Service Near Home | Jobsence',
            'description' => 'Retired people aged 59+ register for full-time, part-time or free community service near home; schools, hospitals, RWAs and NGOs engage them. Own login for seniors and organisations.',
            'canonical' => $base . '/senior',
            'robots' => 'index, follow',
        ]);
        $me = self::current();
        $response->view('front/senior/index', [
            'me' => $me,
            'regs' => $me ? self::registrationsOf($me) : [],
        ], 200, 'layout');
    }

    /** POST /login/senior/identify {identifier} → email OTP to the registration's email. */
    public function identify(Request $request, Response $response): void
    {
        $identifier = trim((string)($request->post('identifier') ?? ''));
        if ($identifier === '') {
            $response->json(['success' => false, 'error' => 'मोबाइल नंबर या ईमेल भरें / Enter your mobile number or email'], 422);
            return;
        }
        $reg = self::findForLogin($identifier);
        if (!$reg) {
            $response->json(['success' => false, 'status' => 'not_found', 'error' => 'इस मोबाइल / ईमेल से कोई वरिष्ठ नागरिक या संस्था रजिस्ट्रेशन नहीं मिला – पहले मुफ़्त रजिस्टर करें / No senior citizen or organisation registration found with this mobile or email – register free first'], 404);
            return;
        }
        $email = strtolower(trim((string)($reg['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_ends_with($email, '@mobile.local')) {
            $response->json(['success' => false, 'error' => 'इस रजिस्ट्रेशन में ईमेल नहीं है – gm@jobsence.com पर लिखें / This registration has no email – write to gm@jobsence.com'], 422);
            return;
        }
        $last = (int)($_SESSION['slogin_sent'][(int)$reg['id']] ?? 0);
        $_SESSION['slogin_reg'] = (int)$reg['id'];
        if ($last > time() - 30) {
            $response->json(['success' => true, 'status' => 'otp_sent', 'email' => self::maskEmail($email), 'wait' => 30 - (time() - $last)]);
            return;
        }
        $sent = VerificationService::sendEmailAuthOTP($email, self::OTP_PURPOSE);
        if (empty($sent['success'])) {
            $response->json(['success' => false, 'error' => (string)($sent['error'] ?? 'OTP नहीं भेजा जा सका / Could not send the OTP')], !empty($sent['blocked']) ? 429 : 500);
            return;
        }
        $_SESSION['slogin_sent'][(int)$reg['id']] = time();
        $response->json(['success' => true, 'status' => 'otp_sent', 'email' => self::maskEmail($email), 'wait' => 30]);
    }

    /** POST /login/senior/verify {otp} → logged in to /senior. */
    public function verify(Request $request, Response $response): void
    {
        $reg = PortalRegistration::find((int)($_SESSION['slogin_reg'] ?? 0));
        if (!$reg || !in_array($reg['type'], self::TYPES, true)) {
            $response->json(['success' => false, 'error' => 'दोबारा शुरू करें / Please start again'], 422);
            return;
        }
        $check = VerificationService::verifyEmailAuthOTP(strtolower(trim((string)$reg['email'])), trim((string)($request->post('otp') ?? '')), self::OTP_PURPOSE);
        if (empty($check['success'])) {
            $response->json(['success' => false, 'error' => (string)($check['error'] ?? 'OTP सही नहीं है / Incorrect OTP')], !empty($check['blocked']) ? 429 : 422);
            return;
        }
        unset($_SESSION['slogin_reg'], $_SESSION['slogin_sent'][(int)$reg['id']]);
        if (function_exists('session_regenerate_id') && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['senior_reg'] = (int)$reg['id'];
        $response->json(['success' => true, 'status' => 'logged_in', 'redirect' => '/senior']);
    }

    /** GET /senior/logout */
    public function logout(Request $request, Response $response): void
    {
        unset($_SESSION['senior_reg']);
        $response->redirect('/senior');
    }

    /** Logged-in senior / organisation registration, or null. */
    public static function current(): ?array
    {
        $reg = PortalRegistration::find((int)($_SESSION['senior_reg'] ?? 0));
        return $reg && in_array($reg['type'], self::TYPES, true) ? $reg : null;
    }

    /** All paid senior / organisation registrations with the same email or mobile, newest first. */
    private static function registrationsOf(array $me): array
    {
        $email = strtolower(trim((string)($me['email'] ?? '')));
        $rows = Database::getInstance()->fetchAll(
            "SELECT id FROM portal_registrations WHERE type IN ('senior','seniorhire') AND payment_status = 'paid'
               AND (LOWER(email) = ? OR mobile = ?) ORDER BY id DESC LIMIT 50",
            [$email !== '' ? $email : '-', (string)$me['mobile']]
        );
        return array_values(array_filter(array_map(static fn($r) => PortalRegistration::find((int)$r['id']), $rows)));
    }

    private static function findForLogin(string $identifier): ?array
    {
        if (str_contains($identifier, '@')) {
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return null;
            }
            $where = 'LOWER(email) = ?';
            $params = [strtolower($identifier)];
        } else {
            $d = (string)preg_replace('/\D/', '', $identifier);
            if (strlen($d) < 6) {
                return null;
            }
            $where = 'mobile IN (?, ?, ?)';
            $params = [strlen($d) >= 10 ? substr($d, -10) : $d, '+' . ltrim($d, '0'), $d];
        }
        $row = Database::getInstance()->fetchOne(
            "SELECT id FROM portal_registrations WHERE type IN ('senior','seniorhire') AND payment_status = 'paid' AND $where ORDER BY id DESC LIMIT 1",
            $params
        );
        return $row ? PortalRegistration::find((int)$row['id']) : null;
    }

    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($local, 0, 2) . str_repeat('•', max(1, min(6, mb_strlen($local) - 2))) . '@' . $domain;
    }
}
