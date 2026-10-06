<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\FreeJobPost;
use App\Services\JobBoard\StateJobBoard;
use App\Services\SeoService;

/**
 * Jobs by state & city (A–Z) – /jobs-by-state, /jobs-by-state/{state}.
 * Free job posting for companies – /post-job-free (employer account required, no fee), /free-job/{id}-{slug}.
 */
class StateJobsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $q = mb_substr(trim((string)$request->get('q', '')), 0, 80);
        $groups = StateJobBoard::grouped();
        $this->seo('Jobs by State & City (A–Z) – All India Jobs | Jobsence',
            'All jobs in India state-wise and city-wise from A to Z – private company jobs posted free on Jobsence, plus Govt, PSU and state board jobs – with posting date and time.',
            '/jobs-by-state', $q === '');
        $response->view('front/state-jobs/index', [
            'groups' => $groups,
            'latest' => $q === '' ? StateJobBoard::latest(30) : self::search($q),
            'q' => $q,
            'total' => array_sum(array_column($groups, 'count')),
        ], 200, 'layout');
    }

    public function state(Request $request, Response $response): void
    {
        $state = StateJobBoard::stateFromSlug((string)$request->param('state'));
        if ($state === null) {
            $response->view('errors/404', [], 404);
            return;
        }
        $groups = StateJobBoard::grouped($state);
        $this->seo('Jobs in ' . $state . ' – City-wise A–Z | Jobsence',
            'Latest jobs in ' . $state . ' city-wise from A to Z – company jobs posted free on Jobsence and Govt / state board jobs, with posting date and time.',
            '/jobs-by-state/' . StateJobBoard::slug($state), true);
        $response->view('front/state-jobs/state', [
            'state' => $state,
            'cities' => $groups[$state]['cities'] ?? [],
            'count' => $groups[$state]['count'] ?? 0,
            'allStates' => array_keys(StateJobBoard::grouped()),
        ], 200, 'layout');
    }

    /** GET /post-job-free – employer account required; posting is free. */
    public function postForm(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->redirect('/login/employer?redirect=' . rawurlencode('/post-job-free'));
            return;
        }
        $this->seo('Post a Job Free – Jobsence', 'Post your company job free of cost on Jobsence – state and city wise.', '/post-job-free', false);
        $response->view('front/state-jobs/post', $this->formData([]) + ['errors' => [], 'old' => $this->prefill()], 200, 'layout');
    }

    /** POST /post-job-free */
    public function postSave(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->redirect('/login/employer?redirect=' . rawurlencode('/post-job-free'));
            return;
        }
        if (!$this->canPost()) {
            $response->redirect('/post-job-free');
            return;
        }
        $in = $request->all();
        $states = StateJobBoard::states();
        $v = static fn(string $k, int $max) => mb_substr(trim((string)($in[$k] ?? '')), 0, $max);
        $p = [
            'user_id' => (int)$this->currentUser->id,
            'company_name' => $v('company_name', 190),
            'company_type' => (string)($in['company_type'] ?? ''),
            'contact_person' => $v('contact_person', 120),
            'phone' => preg_replace('/\D/', '', (string)($in['phone'] ?? '')),
            'email' => strtolower($v('email', 190)),
            'title' => $v('title', 190),
            'job_type' => (string)($in['job_type'] ?? 'full_time'),
            'state' => (string)($in['state'] ?? ''),
            'city' => $v('city', 120),
            'vacancies' => ($n = (int)($in['vacancies'] ?? 0)) > 0 ? min($n, 100000) : null,
            'salary' => $v('salary', 120) ?: null,
            'qualification' => $v('qualification', 190) ?: null,
            'experience' => $v('experience', 80) ?: null,
            'description' => $v('description', 5000),
            'how_to_apply' => $v('how_to_apply', 500) ?: null,
        ];
        $e = [];
        if (mb_strlen($p['company_name']) < 2) {
            $e['company_name'] = ['कंपनी / फ़र्म का नाम भरें', 'Enter the company / firm name'];
        }
        if (!isset(FreeJobPost::COMPANY_TYPES[$p['company_type']])) {
            $e['company_type'] = ['कंपनी का प्रकार चुनें', 'Choose the type of company'];
        }
        if (mb_strlen($p['contact_person']) < 2) {
            $e['contact_person'] = ['संपर्क व्यक्ति का नाम भरें', 'Enter the contact person'];
        }
        if (strlen($p['phone']) === 12 && str_starts_with($p['phone'], '91')) {
            $p['phone'] = substr($p['phone'], 2);
        }
        if ($p['phone'] !== '' && !preg_match('/^[6-9]\d{9}$/', $p['phone'])) {
            $e['phone'] = ['सही 10 अंकों का मोबाइल नंबर भरें', 'Enter a valid 10-digit mobile number'];
        }
        if ($p['email'] !== '' && !filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            $e['email'] = ['सही ईमेल भरें', 'Enter a valid email'];
        }
        if ($p['phone'] === '' && $p['email'] === '') {
            $e['phone'] = ['आवेदकों के लिए मोबाइल या ईमेल ज़रूरी है', 'Give a mobile number or email for applicants'];
        }
        if (mb_strlen($p['title']) < 3) {
            $e['title'] = ['पद का नाम भरें', 'Enter the job title'];
        }
        if (!isset(FreeJobPost::JOB_TYPES[$p['job_type']])) {
            $e['job_type'] = ['नौकरी का प्रकार चुनें', 'Choose the job type'];
        }
        if (!isset($states[$p['state']])) {
            $e['state'] = ['राज्य चुनें', 'Choose the state'];
        }
        if (mb_strlen($p['city']) < 2) {
            $e['city'] = ['शहर / ज़िला भरें', 'Enter the city / district'];
        }
        if (mb_strlen($p['description']) < 30) {
            $e['description'] = ['काम का विवरण कम से कम 30 अक्षर में लिखें', 'Describe the work in at least 30 characters'];
        }
        if (preg_match('/\b(registration|security|processing|joining)\s+(fee|charge|deposit)\b|शुल्क\s*जमा/iu', $p['description'] . ' ' . $p['how_to_apply'])) {
            $e['description'] = ['उम्मीदवारों से कोई शुल्क / डिपॉज़िट माँगने वाली नौकरियाँ पोस्ट नहीं की जा सकतीं', 'Jobs that ask candidates for any fee or deposit cannot be posted'];
        }
        if (FreeJobPost::postedToday((int)$this->currentUser->id) >= FreeJobPost::MAX_PER_DAY) {
            $e['general'] = ['आज की सीमा (' . FreeJobPost::MAX_PER_DAY . ' नौकरियाँ) पूरी – कल फिर पोस्ट करें', 'Daily limit of ' . FreeJobPost::MAX_PER_DAY . ' free posts reached – post again tomorrow'];
        }
        if ($e) {
            $response->view('front/state-jobs/post', $this->formData([]) + ['errors' => $e, 'old' => $in], 422, 'layout');
            return;
        }
        // Employers pay: FREE_POSTS_PER_MONTH free posts a month, then ₹200 + GST per post (paid through the usual checkout).
        $overQuota = !$this->currentUser->isAdmin() && FreeJobPost::postedThisMonth((int)$this->currentUser->id) >= FreeJobPost::FREE_POSTS_PER_MONTH;
        $id = FreeJobPost::create($p, $overQuota);
        if ($overQuota) {
            $reg = \App\Models\PortalRegistration::create([
                'type' => 'jobpost',
                'full_name' => $p['contact_person'],
                'mobile' => $p['phone'] !== '' ? $p['phone'] : (string)preg_replace('/\D/', '', (string)($this->currentUser->phone ?? '')),
                'email' => $p['email'] !== '' ? $p['email'] : (string)($this->currentUser->email ?? ''),
                'city' => $p['city'],
                'state' => $p['state'],
                'categories' => mb_substr($p['title'], 0, 190),
                'details' => json_encode(['post_id' => $id, 'company' => $p['company_name'], 'user_id' => (int)$this->currentUser->id], JSON_UNESCAPED_UNICODE),
                'declaration_accepted' => 1,
            ], 'JPS', FreeJobPost::EXTRA_POST_FEE);
            if ($reg) {
                $response->redirect('/apply/pay/' . $reg['token']);
                return;
            }
        }
        $_SESSION['fjp_flash'] = 'posted';
        $response->redirect(FreeJobPost::url(['id' => $id] + $p));
    }

    /** GET /free-job/{id}-{slug} */
    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $post = $id > 0 ? FreeJobPost::find($id) : null;
        $mine = $post && $this->currentUser && ((int)$post['user_id'] === (int)$this->currentUser->id || $this->currentUser->isAdmin());
        $live = $post && $post['status'] === 'live' && strtotime((string)$post['expires_at']) > time();
        if (!$post || (!$live && !$mine)) {
            $response->view('errors/404', [], 404);
            return;
        }
        if ($live && !$mine) {
            FreeJobPost::countView($id);
        }
        $this->seo($post['title'] . ' – ' . $post['company_name'] . ', ' . $post['city'] . ' | Jobsence',
            mb_substr($post['title'] . ' job at ' . $post['company_name'] . ' in ' . $post['city'] . ', ' . $post['state'] . '. ' . (string)$post['description'], 0, 290),
            FreeJobPost::url($post), $live);
        $flash = $_SESSION['fjp_flash'] ?? null;
        unset($_SESSION['fjp_flash']);
        $response->view('front/state-jobs/show', ['post' => $post, 'mine' => $mine, 'live' => $live, 'flash' => $flash], 200, 'layout');
    }

    /** POST /free-job/{id}/contact – phone / email of a live post, for logged-in users within the daily limit. */
    public function contact(Request $request, Response $response): void
    {
        $post = FreeJobPost::find((int)$request->param('id'));
        if (!$post || $post['status'] !== 'live' || strtotime((string)$post['expires_at']) <= time()) {
            $response->json(['success' => false, 'error' => 'यह नौकरी अब उपलब्ध नहीं है / This job is no longer available'], 404);
            return;
        }
        if (!$this->currentUser) {
            $response->json(['success' => false, 'login' => '/login/job-seeker?redirect=' . rawurlencode(FreeJobPost::url($post)),
                'error' => 'संपर्क देखने के लिए लॉगिन करें / Log in to see the contact'], 401);
            return;
        }
        if (!FreeJobPost::revealContact((int)$this->currentUser->id, (int)$post['id'])) {
            $response->json(['success' => false, 'error' => 'आज की सीमा (' . FreeJobPost::CONTACTS_PER_DAY . ' संपर्क) पूरी – कल फिर देखें / Daily limit of ' . FreeJobPost::CONTACTS_PER_DAY . ' contacts reached – try again tomorrow'], 429);
            return;
        }
        $response->json(['success' => true, 'phone' => (string)$post['phone'], 'email' => (string)$post['email']]);
    }

    /** POST /free-job/{id}/close – the poster (or admin) closes a post. */
    public function close(Request $request, Response $response): void
    {
        $post = FreeJobPost::find((int)$request->param('id'));
        if ($post && $this->currentUser && ((int)$post['user_id'] === (int)$this->currentUser->id || $this->currentUser->isAdmin())) {
            FreeJobPost::setStatus((int)$post['id'], 'closed');
        }
        $response->redirect('/post-job-free');
    }

    // ------------------------------------------------------------------ admin

    public function adminIndex(Request $request, Response $response): void
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return;
        }
        $status = (string)$request->get('status', '');
        $q = mb_substr(trim((string)$request->get('q', '')), 0, 80);
        $response->view('admin/free-jobs', ['title' => 'Free job posts', 'posts' => FreeJobPost::adminList($status, $q), 'status' => $status, 'q' => $q], 200, 'admin/layout');
    }

    public function adminStatus(Request $request, Response $response): void
    {
        if ($this->currentUser && $this->currentUser->isAdmin()) {
            FreeJobPost::setStatus((int)$request->param('id'), (string)$request->post('status', ''));
        }
        $response->redirect('/admin/free-jobs');
    }

    // ------------------------------------------------------------------ helpers

    /** Employer accounts (and admins) can post; job seeker accounts are asked to use an employer account. */
    private function canPost(): bool
    {
        return $this->currentUser !== null && ($this->currentUser->isEmployer() || $this->currentUser->isAdmin());
    }

    private function formData(array $extra): array
    {
        return [
            'canPost' => $this->canPost(),
            'states' => StateJobBoard::states(),
            'companyTypes' => FreeJobPost::COMPANY_TYPES,
            'jobTypes' => FreeJobPost::JOB_TYPES,
            'myPosts' => $this->currentUser ? FreeJobPost::byUser((int)$this->currentUser->id) : [],
            'usedThisMonth' => $this->currentUser ? FreeJobPost::postedThisMonth((int)$this->currentUser->id) : 0,
        ] + $extra;
    }

    private function prefill(): array
    {
        $u = $this->currentUser;
        $emp = $u ? $u->employer() : null;
        $phone = preg_replace('/\D/', '', (string)($u->phone ?? ''));
        return [
            'company_name' => $emp->company_name ?? '',
            'email' => str_ends_with((string)($u->email ?? ''), '@mobile.local') ? '' : (string)($u->email ?? ''),
            'phone' => strlen((string)$phone) > 10 ? substr((string)$phone, -10) : $phone,
            'state' => StateJobBoard::stateName((string)($emp->state ?? '')) ?? '',
            'city' => $emp->city ?? '',
        ];
    }

    private static function search(string $q): array
    {
        $needle = mb_strtolower($q);
        $hits = array_filter(StateJobBoard::all(), static fn($j) => str_contains(mb_strtolower($j['title'] . ' ' . $j['company'] . ' ' . $j['city'] . ' ' . $j['state']), $needle));
        usort($hits, static fn($x, $y) => strcmp((string)$y['posted'], (string)$x['posted']));
        return array_slice(array_values($hits), 0, 100);
    }

    private function seo(string $title, string $description, string $path, bool $index): void
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        SeoService::getInstance()->setMeta([
            'title' => $title,
            'description' => $description,
            'canonical' => $base . $path,
            'robots' => $index ? 'index, follow' : 'noindex, follow',
            'h1' => $title,
        ]);
    }
}
