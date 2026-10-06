<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExternalJob;
use App\Models\PortalRegistration;
use App\Services\Registration\FormRegistry;
use App\Services\SeoService;

/**
 * "See Jobs in India" – Railways, Army, Police, State Govts, PSUs, banks and big companies.
 * Job seekers use Jobsence free (FormRegistry::SEEKERS_FREE): full details + the official apply
 * link are open to everyone. (The Jobs Pass – ₹150 + GST, 1 month – only applies if seeker fees return.)
 */
class IndiaJobsController extends BaseController
{
    private const PER_PAGE = 20;

    public function index(Request $request, Response $response): void
    {
        $filters = [
            'type' => (string)$request->get('type', ''),
            'kind' => in_array((string)$request->get('kind', ''), ExternalJob::KINDS, true) ? (string)$request->get('kind') : '',
            'state' => (string)$request->get('state', ''),
            'q' => mb_substr(trim((string)$request->get('q', '')), 0, 80),
        ];
        $page = max(1, (int)$request->get('page', 1));
        $result = ExternalJob::search($filters, $page, self::PER_PAGE);

        $typeName = FormRegistry::INDIA_JOB_TYPES[$filters['type']][1] ?? null;
        $title = ($typeName ? $typeName . ' Jobs' : 'Jobs in India') . ($filters['state'] !== '' ? ' in ' . $filters['state'] : '')
            . ' – Govt, Railway, Army, Police, PSU & Company Jobs | Jobsence';
        $this->seo($title, 'See the latest Indian Railways, Army, Police, State Govt, PSU (GAIL, BHEL, NTPC) and top company job notifications in one place on Jobsence, with links to the official websites. सरकारी नौकरी, रेलवे, सेना, पुलिस भर्ती।', '/india-jobs', $page === 1);

        $response->view('front/india-jobs/index', [
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'filters' => $filters,
            'counts' => ExternalJob::countsByType(),
            'employerJobs' => $page === 1 && $filters['type'] === '' && $filters['kind'] === '' ? $this->employerJobs() : [],
            'notices' => $page === 1 && $filters['kind'] === '' && $filters['q'] === '' && in_array($filters['type'], ['', 'central_govt'], true) ? ExternalJob::latestNotices(8) : [],
            'kinds' => [
                'job' => ['नौकरियाँ', 'Jobs'],
                'internship' => ['इंटर्नशिप', 'Internships'],
                'apprenticeship' => ['अप्रेंटिसशिप', 'Apprenticeships'],
                'skill' => ['स्किल डेवलपमेंट', 'Skill development'],
            ],
        ] + $this->access(), 200, 'layout');
    }

    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $job = $id > 0 ? ExternalJob::find($id) : null;
        if (!$job || (!$job['is_active'] && !$this->isAdmin())) {
            $response->view('errors/404', [], 404);
            return;
        }

        $this->seo(
            $job['title'] . ' – ' . $job['org_name'] . ' | Jobs in India – Jobsence',
            mb_substr($job['org_name'] . ': ' . $job['title'] . ($job['last_date'] ? '. Last date ' . date('d M Y', strtotime((string)$job['last_date'])) : '') . '. See full details and the official apply link on Jobsence.', 0, 300),
            ExternalJob::url($job),
            true
        );
        $response->view('front/india-jobs/show', ['job' => $job] + $this->access(), 200, 'layout');
    }

    /** Login + Jobs Pass state for the views. */
    private function access(): array
    {
        $user = $this->currentUser;
        $pass = $user ? PortalRegistration::activeByEmail('jobpass', (string)$user->email) : null;
        return [
            'loggedIn' => (bool)$user,
            'pass' => $pass,
            // Job seekers free (FormRegistry::SEEKERS_FREE): Govt job details and apply links are open to everyone.
            'unlocked' => FormRegistry::SEEKERS_FREE || $pass !== null || $this->isAdmin(),
            'passFee' => FormRegistry::JOBS_PASS_FEE,
            'states' => array_column(SkillDevelopmentController::STATES, 0),
            'types' => FormRegistry::INDIA_JOB_TYPES,
        ];
    }

    private function isAdmin(): bool
    {
        return $this->currentUser !== null && $this->currentUser->isAdmin();
    }

    /** Latest jobs posted by employers on Jobsence itself (always free to view). */
    private function employerJobs(): array
    {
        try {
            return Database::getInstance()->fetchAll(
                "SELECT title, slug, company_name, locations, employment_type FROM jobs
                 WHERE status = 'published' AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY COALESCE(publish_at, created_at) DESC LIMIT 6"
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function seo(string $title, string $description, string $path, bool $index): void
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        SeoService::getInstance()->setMeta([
            'title' => $title,
            'description' => $description,
            'keywords' => 'jobs in india, sarkari naukri, govt jobs, railway jobs, army jobs, police jobs, state govt jobs, psu jobs, gail jobs, bhel jobs, ntpc jobs, bank jobs, reliance jobs, tata jobs, सरकारी नौकरी, रेलवे भर्ती, पुलिस भर्ती',
            'canonical' => $base . $path,
            'robots' => $index ? 'index, follow' : 'noindex, follow',
            'h1' => $title,
        ]);
    }
}
