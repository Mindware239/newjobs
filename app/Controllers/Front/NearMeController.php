<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\NearMeRating;
use App\Models\PortalRegistration;
use App\Services\Registration\ContactPass;
use App\Services\Registration\FormRegistry;
use App\Services\SeoService;

/**
 * "Near Me" – local service providers (plumber, electrician, carpenter, hair dresser, salon…)
 * found by PIN code. Anyone can see who is nearby; mobile number and full address are shown
 * only with an active Near Me pass (3 months) – free for people looking for a service; service
 * providers pay for their listing.
 */
class NearMeController extends BaseController
{
    /** [lat, lng] from "Use my location", or null. */
    private ?array $geo = null;
    /** Locality / colony typed by the seeker. */
    private string $area = '';

    /** A seeker is shown the best 1 – 5 nearest providers. */
    private const LIMIT = 5;

    public function index(Request $request, Response $response): void
    {
        $pass = ContactPass::active('nearseek');
        // Contacts are shared through the tripartite agreement: the pass holder is the seeker.
        $agreements = [];
        if ($pass) {
            $me = \App\Services\Registration\Mentoring::me();
            if (!$me || (int)$me['id'] !== (int)$pass['id']) {
                \App\Services\Registration\Mentoring::identify((string)$pass['token']);
            }
            foreach (\App\Services\Registration\Mentoring::agreementsFor($pass) as $a) {
                if (in_array($a['status'], ['pending', 'accepted'], true)) {
                    $agreements[(int)$a['mentor_reg_id']] ??= $a;
                }
            }
        }
        $places = self::places();
        $state = (string)$request->get('state', '');
        $state = isset($places[$state]) ? $state : '';
        $city = mb_substr(trim((string)$request->get('city', '')), 0, 80);
        // Homepage box: one "PIN or city" field.
        $where = trim((string)$request->get('where', ''));
        $wherePin = null;
        if ($where !== '') {
            if (strlen(preg_replace('/\D/', '', $where)) === 6) {
                $wherePin = $where;
            } else {
                $city = mb_substr($where, 0, 80);
            }
        }
        // Use the seeker's own PIN only when nothing else was asked for.
        $pinRaw = $wherePin ?? (string)$request->get('pin', ($state === '' && $city === '' ? ($pass['pincode'] ?? '') : ''));
        $pin = substr(preg_replace('/\D/', '', $pinRaw), 0, 6);
        $q = mb_substr(trim((string)$request->get('q', '')), 0, 80);
        // "Use my location" sends coordinates; "your address / area" narrows to a locality.
        $lat = (float)$request->get('lat', 0);
        $lng = (float)$request->get('lng', 0);
        $this->geo = ($lat >= 6 && $lat <= 37.5 && $lng >= 68 && $lng <= 98) ? [$lat, $lng] : null;
        $area = mb_substr(trim((string)$request->get('area', '')), 0, 80);
        $this->area = $area;
        $byPin = strlen($pin) === 6;
        $byPlace = $state !== '' || $city !== '' || $area !== '';
        $searched = $byPin || $byPlace || $this->geo !== null;
        $place = $byPin ? 'PIN ' . $pin : trim($city . ($city !== '' && $state !== '' ? ', ' : '') . $state);
        if ($area !== '') {
            $place = trim($area . ($place !== '' ? ', ' . $place : ''));
        }

        $title = ($q !== '' ? ucwords($q) . ' Near Me' : 'Plumber, Electrician, Carpenter, Salon Near Me') . ($place !== '' ? ' – ' . $place : '') . ' | Jobsence';
        SeoService::getInstance()->setMeta([
            'title' => $title,
            'h1' => $title,
            'description' => 'Find verified plumbers, electricians, carpenters, welders, painters, hair dressers and salons near your PIN code on Jobsence. Service providers enrol with photo and live selfie.',
            'keywords' => 'plumber near me, electrician near me, carpenter near me, hair dresser near me, salon near me, ac repair near me, painter near me, welder near me, local services india',
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . '/near-me',
            'robots' => $searched ? 'noindex, follow' : 'index, follow',
        ]);

        $response->view('front/near-me/index', [
            'pass' => $pass,
            'agreements' => $agreements,
            'pin' => $pin,
            'q' => $q,
            'state' => $state,
            'city' => $city,
            'area' => $area,
            'geo' => $this->geo,
            'places' => $places,
            'searched' => $searched,
            'pinError' => trim($pinRaw) !== '' && !$byPin && $state === '' && $city === '',
            'results' => $byPin ? $this->search($pin, $q) : ($byPlace ? $this->searchPlace($state, $city, $q) : ($this->geo ? $this->searchGeo($q) : [])),
            'flash' => $this->takeFlash(),
            'fee' => FormRegistry::NEAR_ME_FEE,
            'popular' => array_map(static fn($p) => $p[1], array_slice(FormRegistry::NEAR_ME_PROFESSIONS, 0, -1)),
        ], 200, 'layout');
    }

    /** state => ['districts' => [...], 'towns' => [...]] (all of India). */
    public static function places(): array
    {
        static $p = null;
        return $p ??= (is_file($f = dirname(__DIR__, 3) . '/resources/data/india_places.php') ? require $f : []);
    }

    /** Search by state and/or city / district / town (no PIN). */
    private function searchPlace(string $state, string $city, string $q): array
    {
        PortalRegistration::ensureSchema();
        $where = "type = 'nearpro' AND payment_status = 'paid' AND status = 'selected' AND valid_until IS NOT NULL AND valid_until > NOW()";
        $params = [];
        $closeness = '1';
        if ($state !== '') {
            $where .= ' AND state = ?';
            $params[] = $state;
        }
        if ($city !== '') {
            $like = '%' . $city . '%';
            $where .= " AND (city LIKE ? OR district LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.village')) LIKE ?)";
            array_push($params, $like, $like, $like);
            $closeness = "CASE WHEN city = " . Database::getInstance()->getConnection()->quote($city) . " OR district = " . Database::getInstance()->getConnection()->quote($city) . " THEN 3 ELSE 2 END";
        }
        if ($q !== '') {
            $where .= ' AND (categories LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, \'$.business_name\')) LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%');
        }
        if ($this->area !== '') {
            $like = '%' . $this->area . '%';
            $where .= " AND (city LIKE ? OR district LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.village')) LIKE ?
                OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.address_line')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.landmark')) LIKE ?)";
            array_push($params, $like, $like, $like, $like, $like);
        }
        $rows = Database::getInstance()->fetchAll(
            "SELECT *, $closeness AS closeness FROM portal_registrations WHERE $where ORDER BY closeness DESC, paid_at DESC LIMIT 100",
            $params
        );
        return $this->rank($rows);
    }

    /** Only coordinates (no PIN / place): verified providers with a saved location within 50 km. */
    private function searchGeo(string $q): array
    {
        PortalRegistration::ensureSchema();
        $where = "type = 'nearpro' AND payment_status = 'paid' AND status = 'selected' AND valid_until IS NOT NULL AND valid_until > NOW()
                  AND JSON_EXTRACT(details, '$.geo') IS NOT NULL";
        $params = [];
        if ($q !== '') {
            $where .= ' AND (categories LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, \'$.business_name\')) LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%');
        }
        $rows = Database::getInstance()->fetchAll("SELECT *, 1 AS closeness FROM portal_registrations WHERE $where LIMIT 3000", $params);
        $ranked = $this->rank($rows, 1000);
        return array_slice(array_values(array_filter($ranked, static fn($r) => $r['distance'] !== null && $r['distance'] <= 50)), 0, self::LIMIT);
    }

    /** Great-circle distance in km. */
    private static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    private function takeFlash(): ?array
    {
        $f = $_SESSION['nm_flash'] ?? null;
        unset($_SESSION['nm_flash']);
        return $f;
    }

    /** GET /pass/{token} – remember a paid pass in this browser and open its search page. */
    public function access(Request $request, Response $response): void
    {
        $reg = PortalRegistration::findByToken((string)$request->param('token'));
        $page = $reg ? ContactPass::remember($reg) : null;
        if ($page === null) {
            $response->redirect($reg ? '/apply/pay/' . $reg['token'] : '/near-me');
            return;
        }
        $response->redirect($page . ($reg['type'] === 'nearseek' && !empty($reg['pincode']) ? '?pin=' . $reg['pincode'] : ''));
    }

    /** GET /near-me/photo/{id} – public photo of a paid, valid provider (never the selfie). */
    public function photo(Request $request, Response $response): void
    {
        $reg = PortalRegistration::find((int)$request->param('id'));
        $relative = ($reg && $reg['type'] === 'nearpro' && PortalRegistration::isValid($reg) && $reg['status'] !== 'rejected')
            ? (string)($reg['details']['photo_path'] ?? '') : '';
        $root = realpath(dirname(__DIR__, 3) . '/storage/uploads/registrations');
        $path = $relative !== '' ? realpath(dirname(__DIR__, 3) . '/' . $relative) : false;
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code(404);
            exit;
        }
        $mime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** POST /near-me/rate/{id} – a paid, verified seeker rates a provider (1–5 stars + feedback). */
    public function rate(Request $request, Response $response): void
    {
        $pass = ContactPass::active('nearseek');
        $provider = PortalRegistration::find((int)$request->param('id'));
        $back = '/near-me?' . http_build_query(array_filter(['pin' => (string)$request->post('pin', ''), 'q' => (string)$request->post('q', '')]));
        if (!$pass || !$provider || $provider['type'] !== 'nearpro') {
            $_SESSION['nm_flash'] = ['रेटिंग देने के लिए सक्रिय Near Me पास ज़रूरी है', 'An active Near Me pass is needed to rate'];
            $response->redirect($back);
            return;
        }
        $stars = (int)$request->post('stars', 0);
        if ($stars < 1 || $stars > 5) {
            $_SESSION['nm_flash'] = ['1 से 5 स्टार चुनें', 'Choose 1 to 5 stars'];
            $response->redirect($back);
            return;
        }
        NearMeRating::save($provider, $pass, $stars, (string)$request->post('feedback', ''));
        $_SESSION['nm_flash'] = ['धन्यवाद! आपकी रेटिंग सेव हो गई।', 'Thank you! Your rating has been saved.'];
        $response->redirect($back . '#p' . (int)$provider['id']);
    }

    /**
     * Listed providers = paid + still valid + verified by the Jobsence team (photo, selfie, ID).
     * Ranked by PIN closeness (same PIN, same first 4 / 3 digits, same region), then star rating,
     * then how many times they have kept paying (renewals), then the newest payment.
     */
    private function search(string $pin, string $q): array
    {
        PortalRegistration::ensureSchema();
        $params = [$pin, $pin, $pin, $pin];
        $where = "type = 'nearpro' AND payment_status = 'paid' AND status = 'selected'
                  AND valid_until IS NOT NULL AND valid_until > NOW() AND LEFT(pincode, 2) = LEFT(?, 2)";
        if ($this->area !== '') {
            $like = '%' . $this->area . '%';
            $where .= " AND (city LIKE ? OR district LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.village')) LIKE ?
                OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.address_line')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, '$.landmark')) LIKE ?)";
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($q !== '') {
            $where .= ' AND (categories LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(details, \'$.business_name\')) LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%');
        }
        $rows = Database::getInstance()->fetchAll(
            "SELECT *, CASE WHEN pincode = ? THEN 4 WHEN LEFT(pincode, 4) = LEFT(?, 4) THEN 3 WHEN LEFT(pincode, 3) = LEFT(?, 3) THEN 2 ELSE 1 END AS closeness
             FROM portal_registrations WHERE $where
             ORDER BY closeness DESC, paid_at DESC LIMIT 100",
            $params
        );
        return $this->rank($rows);
    }

    /** Rating + renewals ranking inside each closeness tier; keep the best 5. */
    private function rank(array $rows, int $keep = self::LIMIT): array
    {
        $mobiles = array_column($rows, 'mobile');
        $ratings = NearMeRating::summaries($mobiles);
        $terms = NearMeRating::paidTerms($mobiles);

        foreach ($rows as &$r) {
            $r['details'] = json_decode((string)($r['details'] ?? ''), true) ?: [];
            $r['rating'] = $ratings[$r['mobile']] ?? null;
            $r['terms'] = $terms[$r['mobile']] ?? 1;
            // Rating (Bayesian, so one 5★ does not beat many 4.8★) + a small bonus per renewal (max 10).
            $r['rank'] = ($r['rating']['score'] ?? NearMeRating::priorScore()) + 0.15 * min(10, $r['terms'] - 1);
            $r['distance'] = null;
            $r['reviews'] = [];
            if ($this->geo && preg_match('/^(-?[\d.]+),(-?[\d.]+)$/', (string)($r['details']['geo'] ?? ''), $g)) {
                $r['distance'] = self::km($this->geo[0], $this->geo[1], (float)$g[1], (float)$g[2]);
                // Within 3 km beats everything; within 10 km beats "same PIN" for providers without coordinates.
                $r['closeness'] = $r['distance'] <= 3 ? 6 : ($r['distance'] <= 10 ? 5 : (int)$r['closeness']);
            }
        }
        unset($r);
        usort($rows, static fn($a, $b) => [$b['closeness'], -($b['distance'] ?? 9999) , $b['rank'], $b['paid_at']] <=> [$a['closeness'], -($a['distance'] ?? 9999), $a['rank'], $a['paid_at']]);
        $rows = array_slice($rows, 0, $keep);
        foreach (array_slice(array_keys($rows), 0, self::LIMIT) as $i) {
            $rows[$i]['reviews'] = $rows[$i]['rating'] ? NearMeRating::recent((string)$rows[$i]['mobile'], 2) : [];
        }
        return $rows;
    }
}
