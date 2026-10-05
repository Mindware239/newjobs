<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\Registration\ContactPass;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\ResumeText;
use App\Services\SeoService;

/**
 * Paid talent search.
 *   /hospital-talent – hospitals / clinics (pass type "hospital", 15 days) see healthcare
 *                      job seekers of the role they paid for, matched by keywords.
 *   /talent-search   – part-time hirers (pass type "hirer", ₹5,900, 2 days) search part-time
 *                      / gig candidates by work, city and resume keywords.
 * Without an active pass the list is a masked preview (no name, number, email or resume).
 */
class TalentController extends BaseController
{
    private const LIMIT = 50;

    /** Pass type => what it searches. */
    private const MODES = [
        'hospital' => ['list' => 'healthcare', 'path' => '/hospital-talent', 'form' => '/apply/hospital-hiring'],
        'hirer' => ['list' => 'parttime', 'path' => '/talent-search', 'form' => '/apply/hire-part-time'],
    ];

    public function hospital(Request $request, Response $response): void
    {
        $this->page('hospital', $request, $response);
    }

    public function hirer(Request $request, Response $response): void
    {
        $this->page('hirer', $request, $response);
    }

    private function page(string $mode, Request $request, Response $response): void
    {
        $pass = ContactPass::active($mode);
        $passDetails = $pass['details'] ?? [];
        $q = mb_substr(trim((string)$request->get('q', '')), 0, 120);
        $city = mb_substr(trim((string)$request->get('city', '')), 0, 80);
        $role = $mode === 'hospital' ? (string)($passDetails['hire_role'] ?? $request->get('role', '')) : '';
        if ($role !== '' && !isset(FormRegistry::HEALTH_ROLES[$role])) {
            $role = '';
        }

        // With a pass and no query, match the hirer's own requirement.
        $auto = false;
        $keywords = ResumeText::keywords($q);
        if ($pass && $q === '' && !$request->get('all')) {
            $keywords = array_slice(ResumeText::keywords(($pass['categories'] ?? '') . ' ' . ($passDetails['requirement_brief'] ?? '')), 0, 25);
            $auto = $keywords !== [];
        }

        $isHospital = $mode === 'hospital';
        $title = $isHospital ? 'Hire Doctors, Nurses & Hospital Staff' : 'Hire Part-time Staff, Event Manpower, Models, Drivers';
        SeoService::getInstance()->setMeta([
            'title' => $title . ($city !== '' ? ' in ' . $city : '') . ' | Jobsence',
            'h1' => $title,
            'description' => $isHospital
                ? 'Hospitals and clinics: find doctors, nurses, front desk, sales and housekeeping staff matched by brief and resume keywords on Jobsence.'
                : 'Find part-time staff, exhibition manpower, promoters, models, bouncers and drivers by resume keywords, city, hours and daily pay on Jobsence.',
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . self::MODES[$mode]['path'],
            'robots' => ($q !== '' || $city !== '') ? 'noindex, follow' : 'index, follow',
        ]);

        $response->view('front/talent/index', [
            'mode' => $mode,
            'pass' => $pass,
            'q' => $q,
            'city' => $city,
            'role' => $role,
            'auto' => $auto,
            'keywords' => $keywords,
            'results' => $this->search(self::MODES[$mode]['list'], $role, $city, $keywords),
            'formUrl' => self::MODES[$mode]['form'],
            'fees' => $isHospital ? FormRegistry::HEALTH_FEES_HOSPITAL : null,
            'fee' => $isHospital ? null : FormRegistry::HIRER_FEE,
        ], 200, 'layout');
    }

    /**
     * Paid, still-valid, not-rejected candidates of $type. Scored per keyword:
     * categories ×3, brief / skills ×2, resume ×1. With keywords, only matches are returned.
     */
    private function search(string $type, string $role, string $city, array $keywords): array
    {
        PortalRegistration::ensureSchema();
        $where = "type = ? AND payment_status = 'paid' AND status <> 'rejected' AND valid_until IS NOT NULL AND valid_until > NOW()";
        $params = [$type];
        if ($role !== '') {
            $where .= " AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.health_role')) = ?";
            $params[] = $role;
        }
        if ($city !== '') {
            $like = '%' . $city . '%';
            $where .= ' AND (city LIKE ? OR district LIKE ? OR state LIKE ? OR preferred_location LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        $rows = Database::getInstance()->fetchAll(
            "SELECT * FROM portal_registrations WHERE $where ORDER BY paid_at DESC LIMIT 1000",
            $params
        );

        $out = [];
        foreach ($rows as $r) {
            $d = json_decode((string)($r['details'] ?? ''), true) ?: [];
            $fields = [
                3 => mb_strtolower((string)$r['categories']),
                2 => mb_strtolower(($d['about_me'] ?? '') . ' ' . ($d['known_skills'] ?? '') . ' ' . ($r['qualification'] ?? '')),
                1 => mb_strtolower((string)($d['resume_text'] ?? '')),
            ];
            $score = 0;
            $hits = [];
            foreach ($keywords as $k) {
                foreach ($fields as $weight => $text) {
                    if ($text !== '' && str_contains($text, $k)) {
                        $score += $weight;
                        $hits[$k] = true;
                        break;
                    }
                }
            }
            if ($keywords !== [] && $score === 0) {
                continue;
            }
            unset($d['resume_text']);
            $r['details'] = $d;
            $r['score'] = $score;
            $r['hits'] = array_keys($hits);
            $out[] = $r;
        }
        usort($out, static fn($a, $b) => [$b['score'], $b['paid_at']] <=> [$a['score'], $a['paid_at']]);
        return array_slice($out, 0, self::LIMIT);
    }

    /** GET /talent/resume/{id} – resume download for an active hospital / hirer pass only. */
    public function resume(Request $request, Response $response): void
    {
        $reg = PortalRegistration::find((int)$request->param('id'));
        $allowed = false;
        if ($reg && PortalRegistration::isValid($reg) && $reg['status'] !== 'rejected') {
            foreach (self::MODES as $mode => $m) {
                $pass = ContactPass::active($mode);
                if ($pass && $reg['type'] === $m['list']
                    && ($mode !== 'hospital' || ($reg['details']['health_role'] ?? '') === ($pass['details']['hire_role'] ?? ''))) {
                    $allowed = true;
                    break;
                }
            }
        }
        $root = realpath(dirname(__DIR__, 3) . '/storage/uploads/registrations');
        $path = ($allowed && !empty($reg['resume_path'])) ? realpath(dirname(__DIR__, 3) . '/' . $reg['resume_path']) : false;
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code($allowed ? 404 : 403);
            exit;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'][$ext] ?? 'application/octet-stream';
        $name = preg_replace('/[^A-Za-z0-9]+/', '_', (string)$reg['full_name']) . '_resume.' . $ext;
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}
