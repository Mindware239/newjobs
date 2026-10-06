<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Front\SkillDevelopmentController;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExternalJob;
use App\Services\IndiaJobs\FeedFetcher;
use App\Services\Registration\FormRegistry;

/**
 * Admin → Jobs in India: listings (add / edit / hide / delete) and official sources
 * (feed URL, enable, fetch now).
 */
class IndiaJobsController extends BaseController
{
    private const PER_PAGE = 30;

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $filters = [
            'type' => (string)$request->get('type', ''),
            'q' => trim((string)$request->get('q', '')),
            'source_id' => (int)$request->get('source_id', 0) ?: '',
        ];
        $page = max(1, (int)$request->get('page', 1));
        $result = ExternalJob::search($filters, $page, self::PER_PAGE, false);

        $response->view('admin/india-jobs/index', [
            'title' => 'Jobs in India',
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'filters' => $filters,
            'counts' => ExternalJob::countsByType(),
            'types' => FormRegistry::INDIA_JOB_TYPES + ['other' => ['अन्य', 'Other']],
            'flash' => $this->flash(),
        ], 200, 'admin/layout');
    }

    public function form(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id');
        $job = $id ? ExternalJob::find($id) : null;
        if ($id && !$job) {
            $response->redirect('/admin/india-jobs');
            return;
        }
        $this->renderForm($response, $job ?? ['is_active' => 1, 'org_type' => 'state_govt'], []);
    }

    public function save(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id');
        $in = (array)$request->post();
        $job = [
            'id' => $id ?: null,
            'org_name' => trim((string)($in['org_name'] ?? '')),
            'org_type' => (string)($in['org_type'] ?? 'other'),
            'title' => trim((string)($in['title'] ?? '')),
            'location' => trim((string)($in['location'] ?? '')),
            'state' => trim((string)($in['state'] ?? '')),
            'qualification' => trim((string)($in['qualification'] ?? '')),
            'vacancies' => trim((string)($in['vacancies'] ?? '')),
            'salary' => trim((string)($in['salary'] ?? '')),
            'last_date' => trim((string)($in['last_date'] ?? '')),
            'summary' => trim((string)($in['summary'] ?? '')),
            'details' => trim((string)($in['details'] ?? '')),
            'apply_url' => trim((string)($in['apply_url'] ?? '')),
            'source_url' => trim((string)($in['source_url'] ?? '')),
            'kind' => (string)($in['kind'] ?? 'job'),
            'published_at' => $id ? (ExternalJob::find($id)['published_at'] ?? '') : '',
            'is_active' => !empty($in['is_active']) ? 1 : 0,
            'source_id' => null,
        ];

        $errors = [];
        foreach (['org_name' => 'Organisation', 'title' => 'Job title'] as $k => $label) {
            if ($job[$k] === '') {
                $errors[$k] = "$label is required.";
            }
        }
        if (!in_array($job['org_type'], ExternalJob::ORG_TYPES, true)) {
            $errors['org_type'] = 'Choose a type.';
        }
        foreach (['apply_url', 'source_url'] as $k) {
            if ($job[$k] !== '' && !preg_match('#^https?://#i', $job[$k])) {
                $errors[$k] = 'Must start with http:// or https://';
            }
        }
        if ($job['source_url'] === '' && $job['apply_url'] === '') {
            $errors['source_url'] = 'Give the official notification or apply link – every listing must point to its official source.';
        }
        if ($job['vacancies'] !== '' && !ctype_digit($job['vacancies'])) {
            $errors['vacancies'] = 'Numbers only.';
        }
        if ($job['last_date'] !== '' && !strtotime($job['last_date'])) {
            $errors['last_date'] = 'Invalid date.';
        }
        if ($errors) {
            $this->renderForm($response, $job, $errors, 422);
            return;
        }

        if ($id) {
            ExternalJob::update($id, $job);
        } else {
            $job['guid_hash'] = sha1('manual|' . ($job['source_url'] ?: $job['apply_url']) . '|' . $job['title']);
            ExternalJob::upsert($job);
        }
        ExternalJob::clearTickerCache();
        $_SESSION['flash_ij'] = $id ? 'Job updated.' : 'Job added.';
        $response->redirect('/admin/india-jobs');
    }

    /** POST /admin/india-jobs/{id}/feature – star / unstar for the homepage "Featured Govt & PSU jobs". */
    public function feature(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id');
        if ($job = ExternalJob::find($id)) {
            $on = empty($job['is_featured']);
            ExternalJob::setFeatured($id, $on);
            $_SESSION['flash_ij'] = $on ? 'Featured on the homepage.' : 'Removed from the homepage featured list.';
        }
        $response->redirect('/admin/india-jobs');
    }

    public function toggle(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id');
        if ($job = ExternalJob::find($id)) {
            ExternalJob::setActive($id, !$job['is_active']);
            ExternalJob::clearTickerCache();
            $_SESSION['flash_ij'] = $job['is_active'] ? 'Job hidden from the website.' : 'Job is live again.';
        }
        $response->redirect('/admin/india-jobs');
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        ExternalJob::delete((int)$request->param('id'));
        ExternalJob::clearTickerCache();
        $_SESSION['flash_ij'] = 'Job deleted.';
        $response->redirect('/admin/india-jobs');
    }

    // ------------------------------------------------------------------
    // Sources
    // ------------------------------------------------------------------

    public function sources(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $filters = ['type' => (string)$request->get('type', ''), 'q' => trim((string)$request->get('q', '')), 'feed' => (string)$request->get('feed', '')];
        $response->view('admin/india-jobs/sources', [
            'title' => 'Jobs in India – Sources',
            'sources' => ExternalJob::sources($filters),
            'filters' => $filters,
            'types' => FormRegistry::INDIA_JOB_TYPES + ['other' => ['अन्य', 'Other']],
            'states' => array_column(SkillDevelopmentController::STATES, 0),
            'flash' => $this->flash(),
        ], 200, 'admin/layout');
    }

    public function saveSource(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $id = (int)$request->param('id') ?: null;
        $in = (array)$request->post();
        $name = trim((string)($in['name'] ?? ''));
        $feed = trim((string)($in['feed_url'] ?? ''));
        $msg = null;
        if ($name === '') {
            $msg = 'Source name is required.';
        } elseif ($feed !== '' && ($problem = FeedFetcher::checkUrl($feed)) !== null) {
            $msg = 'Feed URL refused: ' . $problem;
        } elseif (!empty($in['enabled']) && $feed === '') {
            $msg = 'Add the feed URL before enabling automatic fetching.';
        }
        if ($msg === null) {
            ExternalJob::saveSource($id, $in + ['feed_type' => $feed !== '' ? ($in['feed_type'] ?? 'rss') : 'manual']);
            $msg = $id ? 'Source saved.' : 'Source added.';
        }
        $_SESSION['flash_ij'] = $msg;
        $response->redirect('/admin/india-jobs/sources' . ($id ? '#src-' . $id : ''));
    }

    public function fetchSource(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($response)) {
            return;
        }
        $source = ExternalJob::source((int)$request->param('id'));
        if ($source && !empty($source['feed_url'])) {
            $status = FeedFetcher::fetchSource($source);
            ExternalJob::clearTickerCache();
            $_SESSION['flash_ij'] = $source['name'] . ' – ' . $status;
        } else {
            $_SESSION['flash_ij'] = 'This source has no feed URL yet.';
        }
        $response->redirect('/admin/india-jobs/sources#src-' . (int)$request->param('id'));
    }

    private function renderForm(Response $response, array $job, array $errors, int $code = 200): void
    {
        $response->view('admin/india-jobs/form', [
            'title' => !empty($job['id']) ? 'Edit job – Jobs in India' : 'Add job – Jobs in India',
            'job' => $job,
            'errors' => $errors,
            'types' => FormRegistry::INDIA_JOB_TYPES + ['other' => ['अन्य', 'Other']],
            'states' => array_column(SkillDevelopmentController::STATES, 0),
        ], $code, 'admin/layout');
    }

    private function flash(): ?string
    {
        $f = $_SESSION['flash_ij'] ?? null;
        unset($_SESSION['flash_ij']);
        return $f;
    }

    private function requireAdmin(Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
