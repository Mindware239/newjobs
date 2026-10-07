<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\TopPlaces\TopBidding;

/**
 * /top-places – companies bid for "Top Hiring Companies" places, buy place 1 for a month, buy the logo right.
 * /resume-boost – job seekers bid to show their resume in employers' "Top Candidates" (15 days, up to 500 employers).
 * /top-bid/pay/{id} – winners pay their bid (Razorpay checkout of the /apply forms). Rules: TopBidding.
 */
class TopPlacesController extends BaseController
{
    /** GET /top-places */
    public function companies(Request $request, Response $response): void
    {
        $employer = $this->currentEmployer();
        $this->seo('Top Hiring Companies – bid for the top places | Jobsence', 'Show your company first on Jobsence: daily bidding from ₹500 + GST, place 1 for a month ₹15,000 + GST, logo ₹1,200 + GST once.', '/top-places');
        $dates = TopBidding::companyDates();
        $auctions = [];
        foreach ($dates as $d) {
            $auctions[$d] = [
                'slots' => TopBidding::companyAuctionSlots($d),
                'bids' => array_map(static fn($b) => (int)$b['amount'], array_filter(TopBidding::ranking('company', $d), static fn($b) => $b['status'] === 'active')),
                'min' => TopBidding::minToEnter('company', $d),
                'month' => (bool)TopBidding::monthHolder($d),
            ];
        }
        $response->view('front/top/companies', [
            'employer' => $employer,
            'auctions' => $auctions,
            'open' => TopBidding::biddingOpenNow(),
            'monthStart' => $employer ? TopBidding::nextMonthStart() : null,
            'hasLogo' => $employer ? TopBidding::hasLogo((int)$employer['id']) : false,
            'myBids' => $employer ? array_values(array_filter(TopBidding::myBids((int)$this->currentUser->id), static fn($b) => $b['kind'] === 'company')) : [],
            'flash' => $this->flash(),
        ], 200, 'layout');
    }

    /** POST /top-places/bid */
    public function companyBid(Request $request, Response $response): void
    {
        $employer = $this->currentEmployer();
        if (!$employer) {
            $response->redirect('/login/employer?redirect=' . rawurlencode('/top-places'));
            return;
        }
        [$ok, $msg] = TopBidding::placeBid('company', (int)$this->currentUser->id, (int)$employer['id'], (string)$request->post('date', ''), '', (int)$request->post('amount', 0));
        $_SESSION['top_flash'] = [$ok, $msg];
        $response->redirect('/top-places#bid');
    }

    /** POST /top-places/month – place 1 for 30 days, ₹15,000 + GST, no bidding. */
    public function companyMonth(Request $request, Response $response): void
    {
        $employer = $this->currentEmployer();
        if (!$employer) {
            $response->redirect('/login/employer?redirect=' . rawurlencode('/top-places'));
            return;
        }
        $start = TopBidding::nextMonthStart();
        if (!$start || $start !== (string)$request->post('start', '')) {
            $_SESSION['top_flash'] = [false, 'Place 1 is not free for that month any more – please check the page again.'];
            $response->redirect('/top-places#month');
            return;
        }
        $monthId = TopBidding::reserveMonth((int)$this->currentUser->id, (int)$employer['id'], $start);
        $this->pay($response, 'topslot', 'TPM', TopBidding::MONTH_PRICE, $employer['company_name'] . ' – place 1 for ' . TopBidding::MONTH_DAYS . ' days from ' . $start,
            ['month_id' => $monthId, 'employer_id' => (int)$employer['id'], 'return_to' => '/top-places?paid=1']);
    }

    /** POST /top-places/logo – logo in all top places, ₹1,200 + GST once. */
    public function companyLogo(Request $request, Response $response): void
    {
        $employer = $this->currentEmployer();
        if (!$employer) {
            $response->redirect('/login/employer?redirect=' . rawurlencode('/top-places'));
            return;
        }
        if (TopBidding::hasLogo((int)$employer['id'])) {
            $response->redirect('/top-places');
            return;
        }
        $this->pay($response, 'toplogo', 'TPL', TopBidding::LOGO_PRICE, $employer['company_name'] . ' – logo in top places',
            ['employer_id' => (int)$employer['id'], 'return_to' => '/top-places?paid=1']);
    }

    /** GET /resume-boost */
    public function resume(Request $request, Response $response): void
    {
        $candidate = $this->currentCandidate();
        $this->seo('Resume Boost – show your resume to employers first | Jobsence', 'Bid from ₹500 + GST to show your resume at the top of employers\' Top Candidates for 15 days – seen by up to 500 employers.', '/resume-boost');
        $periods = TopBidding::resumePeriods();
        $category = (string)$request->get('category', '');
        $categories = array_column(Database::getInstance()->fetchAll('SELECT name FROM job_categories WHERE is_active = 1 ORDER BY name'), 'name');
        $auctions = [];
        if ($category !== '' && in_array($category, $categories, true)) {
            foreach ($periods as $p) {
                $auctions[$p] = [
                    'end' => TopBidding::periodEnd($p),
                    'bids' => array_map(static fn($b) => (int)$b['amount'], array_filter(TopBidding::ranking('resume', $p, $category), static fn($b) => $b['status'] === 'active')),
                    'min' => TopBidding::minToEnter('resume', $p, $category),
                ];
            }
        }
        $mine = $candidate ? array_values(array_filter(TopBidding::myBids((int)$this->currentUser->id), static fn($b) => $b['kind'] === 'resume')) : [];
        foreach ($mine as &$b) {
            $b['views'] = $b['status'] === 'paid' ? TopBidding::boostViews((int)$b['id']) : 0;
        }
        unset($b);
        $response->view('front/top/resume', [
            'candidate' => $candidate,
            'categories' => $categories,
            'category' => $category,
            'auctions' => $auctions,
            'open' => TopBidding::biddingOpenNow(),
            'myBids' => $mine,
            'flash' => $this->flash(),
        ], 200, 'layout');
    }

    /** POST /resume-boost/bid */
    public function resumeBid(Request $request, Response $response): void
    {
        $candidate = $this->currentCandidate();
        $category = (string)$request->post('category', '');
        if (!$candidate) {
            $response->redirect('/login/job-seeker?redirect=' . rawurlencode('/resume-boost'));
            return;
        }
        if (empty($candidate['resume_url'])) {
            $_SESSION['top_flash'] = [false, 'Please upload your resume in your profile first. / पहले अपना रिज़्यूमे अपलोड करें।'];
            $response->redirect('/resume-boost?category=' . rawurlencode($category));
            return;
        }
        if (!Database::getInstance()->fetchOne('SELECT id FROM job_categories WHERE name = ? AND is_active = 1', [$category])) {
            $_SESSION['top_flash'] = [false, 'Please choose a job category.'];
            $response->redirect('/resume-boost');
            return;
        }
        [$ok, $msg] = TopBidding::placeBid('resume', (int)$this->currentUser->id, (int)$candidate['id'], (string)$request->post('date', ''), $category, (int)$request->post('amount', 0));
        $_SESSION['top_flash'] = [$ok, $msg];
        $response->redirect('/resume-boost?category=' . rawurlencode($category) . '#bid');
    }

    /** GET /top-bid/pay/{id} – the winner pays the bid + GST. */
    public function payBid(Request $request, Response $response): void
    {
        $bid = TopBidding::find((int)$request->param('id'));
        if (!$this->currentUser) {
            $response->redirect('/login?redirect=' . rawurlencode('/top-bid/pay/' . (int)$request->param('id')));
            return;
        }
        $back = $bid && $bid['kind'] === 'resume' ? '/resume-boost' : '/top-places';
        if (!$bid || (int)$bid['user_id'] !== (int)$this->currentUser->id || $bid['status'] !== 'won') {
            $_SESSION['top_flash'] = [false, $bid && $bid['status'] === 'paid' ? 'This place is already paid.' : 'This bid cannot be paid now (not won, or the payment time is over).'];
            $response->redirect($back);
            return;
        }
        $open = Database::getInstance()->fetchOne(
            "SELECT token FROM portal_registrations WHERE type IN ('topslot','resumeboost') AND payment_status <> 'paid' AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.bid_id')) = ? ORDER BY id DESC LIMIT 1",
            [(string)$bid['id']]
        );
        if ($open) {
            $response->redirect('/apply/pay/' . $open['token']);
            return;
        }
        $what = $bid['kind'] === 'company' ? 'Top Hiring Companies place on ' . $bid['slot_date'] : 'Resume boost (' . $bid['category'] . ') from ' . $bid['slot_date'];
        $this->pay($response, $bid['kind'] === 'company' ? 'topslot' : 'resumeboost', $bid['kind'] === 'company' ? 'TPB' : 'RBS', (int)$bid['amount'], $what,
            ['bid_id' => (int)$bid['id'], 'return_to' => $back . '?paid=1']);
    }

    /** GET /admin/top-places – all bids, monthly place 1 bookings and logo rights. */
    public function admin(Request $request, Response $response): void
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return;
        }
        TopBidding::ensureSchema();
        $db = Database::getInstance();
        $kind = (string)$request->get('kind', '');
        $status = (string)$request->get('status', '');
        $response->view('admin/top-places', [
            'title' => 'Top places & resume boosts',
            'bids' => TopBidding::adminList($kind, $status),
            'months' => $db->fetchAll('SELECT m.*, e.company_name FROM top_months m LEFT JOIN employers e ON e.id = m.employer_id ORDER BY m.start_date DESC LIMIT 100'),
            'logos' => $db->fetchAll('SELECT l.*, e.company_name, e.logo_url FROM top_logo_rights l LEFT JOIN employers e ON e.id = l.employer_id ORDER BY l.id DESC'),
            'kind' => $kind,
            'status' => $status,
        ], 200, 'admin/layout');
    }

    /** POST /admin/top-places/logo/{id} – hide / show a company logo (e.g. unsuitable image). */
    public function adminLogo(Request $request, Response $response): void
    {
        if ($this->currentUser && $this->currentUser->isAdmin()) {
            $st = $request->post('status') === 'hidden' ? 'hidden' : 'active';
            Database::getInstance()->execute('UPDATE top_logo_rights SET status = ? WHERE id = ?', [$st, (int)$request->param('id')]);
        }
        $response->redirect('/admin/top-places');
    }

    // ------------------------------------------------------------------

    /** Create the checkout registration (base price + 18% GST) and send the user to pay. */
    private function pay(Response $response, string $type, string $prefix, int $base, string $what, array $details): void
    {
        $u = $this->currentUser;
        $reg = PortalRegistration::create([
            'type' => $type,
            'full_name' => mb_substr(trim((string)($u->name ?? '')) ?: (string)($u->email ?? 'Jobsence user'), 0, 120),
            'mobile' => substr((string)preg_replace('/\D/', '', (string)($u->phone ?? '')), -10),
            'email' => (string)($u->email ?? ''),
            'categories' => mb_substr($what, 0, 190),
            'details' => json_encode($details + ['user_id' => (int)$u->id, 'base' => $base], JSON_UNESCAPED_UNICODE),
            'declaration_accepted' => 1,
        ], $prefix, TopBidding::withGst((float)$base));
        $response->redirect($reg ? '/apply/pay/' . $reg['token'] : ($details['return_to'] ?? '/'));
    }

    private function currentEmployer(): ?array
    {
        if (!$this->currentUser || ($this->currentUser->role ?? '') !== 'employer') {
            return null;
        }
        return Database::getInstance()->fetchOne('SELECT id, company_name, logo_url FROM employers WHERE user_id = ?', [(int)$this->currentUser->id]) ?: null;
    }

    private function currentCandidate(): ?array
    {
        if (!$this->currentUser || ($this->currentUser->role ?? '') !== 'candidate') {
            return null;
        }
        return Database::getInstance()->fetchOne('SELECT id, full_name, resume_url FROM candidates WHERE user_id = ?', [(int)$this->currentUser->id]) ?: null;
    }

    private function flash(): ?array
    {
        $f = $_SESSION['top_flash'] ?? null;
        unset($_SESSION['top_flash']);
        if (!$f && isset($_GET['paid'])) {
            $f = [true, 'Payment received – thank you. Your place is confirmed.'];
        }
        return $f;
    }

    private function seo(string $title, string $description, string $path): void
    {
        \App\Services\SeoService::getInstance()->setMeta([
            'title' => $title,
            'description' => $description,
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/') . $path,
            'robots' => 'index, follow',
        ]);
    }
}
