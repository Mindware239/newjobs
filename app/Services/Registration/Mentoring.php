<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\PortalRegistration;
use App\Services\VerificationService;

/**
 * Young India mentoring: skill / internship seekers ↔ mentors, institutes and internship providers.
 *
 *  - Providers (skill mentors / institutes, internship providers) register free.
 *  - Seekers pay the ₹155 form fee; the registration stays valid until they are matched.
 *  - Both sides see each other's name, location, skills / domains, qualification, timing –
 *    never phone or email.
 *  - A provider with a paid plan (₹155 / USD 5 outside India: mentors 30 days, internship providers 10 days, max 20)
 *    can open 3 full profiles a day and send / sign agreements.
 *  - Phone and email are shown only after the tripartite agreement (seeker + provider + Jobsence)
 *    is signed by both with an email OTP; Jobsence countersigns automatically.
 *  - A seeker who rejects (ends) a signed match must pay a new form fee.
 *
 * Agreements live in mentor_assignments (shared with the older admin / auto matching).
 */
class Mentoring
{
    public const DAILY_LIMIT = 3;
    public const TOTAL_LIMIT = ['mentorplan' => null, 'internplan' => 20, 'jobplan' => null];
    public const AGREEMENT_VERSION = '2026-10-v4';
    public const OTP_PURPOSE = 'mentoring_agreement';
    public const RESPONSE_DAYS = 7;

    /**
     * seekers: registration types that look for the thing · provider: type that offers it ·
     * plan: provider's paid plan type (null = the provider's own paid registration is the plan, e.g. Near Me) ·
     * single: one signed match at a time (others close, rejecting it closes the seeker's registration) ·
     * provider_initiates: providers can browse seekers and send agreements.
     */
    public const KINDS = [
        'skill' => ['seekers' => ['skill'], 'provider' => 'provider', 'plan' => 'mentorplan', 'single' => true, 'provider_initiates' => true,
            'seeker_form' => '/apply/skill-development', 'provider_form' => '/apply/skill-provider', 'seekers_page' => '/skill-seekers', 'providers_page' => '/skill-mentors',
            'seekers_label' => ['स्किल सीखने वाले', 'Skill seekers'], 'providers_label' => ['मेंटर / संस्थान', 'Mentors / institutes'], 'what' => ['स्किल मेंटरिंग', 'Skill Mentoring']],
        'internship' => ['seekers' => ['internship'], 'provider' => 'internpro', 'plan' => 'internplan', 'single' => true, 'provider_initiates' => true,
            'seeker_form' => '/apply/internship', 'provider_form' => '/apply/internship-provider', 'seekers_page' => '/internship-seekers', 'providers_page' => '/internship-providers',
            'seekers_label' => ['इंटर्नशिप चाहने वाले', 'Internship seekers'], 'providers_label' => ['इंटर्नशिप प्रदाता', 'Internship providers'], 'what' => ['इंटर्नशिप', 'Internship']],
        'job' => ['seekers' => ['fulltime', 'parttime', 'wfh', 'intljob'], 'provider' => 'jobpro', 'plan' => 'jobplan', 'single' => true, 'provider_initiates' => true,
            'seeker_form' => '/apply/full-time-job', 'provider_form' => '/apply/job-provider', 'seekers_page' => '/job-seekers', 'providers_page' => '/hiring-companies',
            'seekers_label' => ['नौकरी चाहने वाले', 'Job seekers'], 'providers_label' => ['भर्ती करने वाली कंपनियाँ', 'Hiring companies'], 'what' => ['नौकरी', 'Job']],
        'nearme' => ['seekers' => ['nearseek'], 'provider' => 'nearpro', 'plan' => null, 'single' => false, 'provider_initiates' => false,
            'seeker_form' => '/apply/near-me-seeker', 'provider_form' => '/apply/near-me-provider', 'seekers_page' => null, 'providers_page' => '/near-me',
            'seekers_label' => ['सेवा चाहने वाले', 'Service seekers'], 'providers_label' => ['Near Me सेवा प्रदाता', 'Near Me providers'], 'what' => ['Near Me सेवा', 'Near Me service']],
    ];

    /** Plan registration types (bought by providers). */
    public const PLAN_TYPES = ['mentorplan', 'internplan', 'jobplan'];

    private static function inList(array $types): string
    {
        return "'" . implode("','", $types) . "'";
    }

    private const COOKIE = 'jsx_mentoring';

    // ------------------------------------------------------------------
    // Types & identity
    // ------------------------------------------------------------------

    public static function kindOf(string $type): ?string
    {
        foreach (self::KINDS as $kind => $k) {
            if (in_array($type, array_merge($k['seekers'], [$k['provider'], $k['plan']]), true)) {
                return $kind;
            }
        }
        return null;
    }

    public static function roleOf(string $type): ?string
    {
        foreach (self::KINDS as $k) {
            if (in_array($type, $k['seekers'], true)) {
                return 'seeker';
            }
            if ($type === $k['provider']) {
                return 'provider';
            }
        }
        return null;
    }

    /** Remember who is using the mentoring screens (link from the receipt / email). Returns the registration or null. */
    public static function identify(string $token): ?array
    {
        $reg = PortalRegistration::findByToken($token);
        if ($reg && in_array($reg['type'], self::PLAN_TYPES, true)) {
            $reg = PortalRegistration::find((int)($reg['details']['provider_reg_id'] ?? 0));
        }
        if (!$reg || self::roleOf((string)$reg['type']) === null || $reg['payment_status'] !== 'paid') {
            return null;
        }
        $_SESSION['mentoring_token'] = $reg['token'];
        if (!headers_sent()) {
            setcookie(self::COOKIE, (string)$reg['token'], [
                'expires' => time() + 86400 * 180, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            ]);
        }
        return $reg;
    }

    public static function forget(): void
    {
        unset($_SESSION['mentoring_token']);
        if (!headers_sent()) {
            setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
        }
    }

    /** The current visitor's seeker / provider registration, or null. */
    public static function me(): ?array
    {
        static $me = false;
        if ($me !== false) {
            return $me;
        }
        $token = (string)($_SESSION['mentoring_token'] ?? $_COOKIE[self::COOKIE] ?? '');
        $reg = $token !== '' ? PortalRegistration::findByToken($token) : null;
        return $me = ($reg && self::roleOf((string)$reg['type']) !== null && $reg['payment_status'] === 'paid') ? $reg : null;
    }

    // ------------------------------------------------------------------
    // Eligibility, plans, quota
    // ------------------------------------------------------------------

    /** SQL for a seeker who is paid, open and not yet matched (alias r). */
    private const OPEN_SEEKER_SQL = "r.payment_status = 'paid' AND r.status NOT IN ('rejected','completed','expired')
        AND (r.valid_until IS NULL OR r.valid_until > NOW())
        AND NOT EXISTS (SELECT 1 FROM mentor_assignments a WHERE a.candidate_reg_id = r.id AND a.status = 'accepted')
        AND (r.type <> 'intljob' OR EXISTS (SELECT 1 FROM portal_registrations u WHERE u.type = 'intlcountry' AND u.payment_status = 'paid'
             AND JSON_UNQUOTE(JSON_EXTRACT(u.details, '$.seeker_reg_id')) = CAST(r.id AS CHAR)))";

    public static function activeMatch(int $seekerId): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne("SELECT * FROM mentor_assignments WHERE candidate_reg_id = ? AND status = 'accepted' LIMIT 1", [$seekerId]) ?: null;
    }

    /** ['open'|'matched'|'closed', reason] for a seeker. */
    public static function seekerState(array $seeker): string
    {
        if ($seeker['payment_status'] !== 'paid' || in_array($seeker['status'], ['rejected', 'completed', 'expired'], true)
            || (!empty($seeker['valid_until']) && strtotime((string)$seeker['valid_until']) < time())) {
            return 'closed';
        }
        $single = self::KINDS[self::kindOf((string)$seeker['type']) ?? 'skill']['single'] ?? true;
        return $single && self::activeMatch((int)$seeker['id']) ? 'matched' : 'open';
    }

    public static function isVerified(array $provider): bool
    {
        return ($provider['status'] ?? '') === 'selected';
    }

    /** Latest active paid plan of a provider. */
    public static function activePlan(array $provider): ?array
    {
        $kind = self::kindOf((string)$provider['type']);
        if (!$kind) {
            return null;
        }
        if (self::KINDS[$kind]['plan'] === null) {
            return PortalRegistration::isValid($provider) && !empty($provider['valid_until']) ? $provider : null;
        }
        $row = Database::getInstance()->fetchOne(
            "SELECT id FROM portal_registrations WHERE type = ? AND payment_status = 'paid' AND valid_until > NOW()
             AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.provider_reg_id')) = ? ORDER BY valid_until DESC LIMIT 1",
            [self::KINDS[$kind]['plan'], (string)$provider['id']]
        );
        return $row ? PortalRegistration::find((int)$row['id']) : null;
    }

    /** Hiring plan options this provider may buy (India or abroad); empty for ₹155 single-option plans. */
    public static function planOptions(array $provider): array
    {
        if (($provider['type'] ?? '') !== 'jobpro') {
            return [];
        }
        $abroad = ($provider['details']['based_in'] ?? 'india') === 'abroad';
        return array_filter(FormRegistry::HIRING_PLANS, static fn($o) => $o['abroad'] === $abroad);
    }

    /** Provider living outside India (mentors / internship providers pay their plan in USD). */
    public static function livesAbroad(array $provider): bool
    {
        $d = $provider['details'] ?? [];
        if (is_string($d)) {
            $d = json_decode($d, true) ?: [];
        }
        return ($d['residence_country'] ?? 'India') !== 'India';
    }

    /** Price of this provider's plan: "₹155" or "USD 5" (hiring companies: the cheapest option, e.g. "₹216"). */
    public static function planPrice(array $provider): string
    {
        if ($opts = self::planOptions($provider)) {
            $o = reset($opts);
            return $o['currency'] === 'USD' ? 'USD ' . number_format($o['fee'], 0) : '₹' . number_format($o['fee'], 0);
        }
        return self::livesAbroad($provider)
            ? 'USD ' . rtrim(rtrim(number_format(FormRegistry::PROVIDER_PLAN_FEE_USD, 2), '0'), '.')
            : '₹' . number_format(FormRegistry::PROVIDER_PLAN_FEE, 0);
    }

    // ------------------------------------------------------------------
    // Jobs abroad: unlock a country (₹1,180 or USD 10, one-time)
    // ------------------------------------------------------------------

    /** country => ['paid' => bool, 'token' => unlock token] for a jobs-abroad seeker. */
    public static function countriesOf(array $seeker): array
    {
        $out = [];
        foreach (Database::getInstance()->fetchAll(
            "SELECT token, payment_status, JSON_UNQUOTE(JSON_EXTRACT(details, '$.country')) AS country FROM portal_registrations
             WHERE type = 'intlcountry' AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.seeker_reg_id')) = ? ORDER BY id",
            [(string)$seeker['id']]
        ) as $r) {
            $key = mb_strtolower((string)$r['country']);
            if (!isset($out[$key]) || $r['payment_status'] === 'paid') {
                $out[$key] = ['country' => (string)$r['country'], 'paid' => $r['payment_status'] === 'paid', 'token' => $r['token']];
            }
        }
        return $out;
    }

    /** Paid countries of a seeker (names). */
    public static function paidCountries(array $seeker): array
    {
        return array_values(array_map(static fn($c) => $c['country'], array_filter(self::countriesOf($seeker), static fn($c) => $c['paid'])));
    }

    /** Start (or reuse) the payment that unlocks $country for this seeker. Returns ['ok', 'reg'?, 'msg'?]. */
    public static function unlockCountry(array $seeker, string $country, string $currency): array
    {
        $country = trim((string)preg_replace('/\s+/', ' ', $country));
        if (($seeker['type'] ?? '') !== 'intljob' || $seeker['payment_status'] !== 'paid') {
            return ['ok' => false, 'msg' => ['यह केवल विदेश में नौकरी के रजिस्ट्रेशन के लिए है', 'Only for Jobs Abroad registrations']];
        }
        $home = (string)($seeker['details']['residence_country'] ?? 'India');
        $isHome = mb_strtolower($country) === mb_strtolower($home) || ($home === 'India' && mb_strtolower($country) === 'भारत');
        if (!preg_match('/^[\p{L} .&\'()-]{2,60}$/u', $country) || $isHome) {
            return ['ok' => false, 'msg' => ['सही देश का नाम लिखें (आपके अपने देश के अलावा)', 'Enter a valid country other than your own country of residence']];
        }
        $have = self::countriesOf($seeker)[mb_strtolower($country)] ?? null;
        if ($have && $have['paid']) {
            return ['ok' => false, 'msg' => ['यह देश पहले से अनलॉक है', 'This country is already unlocked']];
        }
        if ($have) {
            $reg = PortalRegistration::findByToken($have['token']);
            if ($reg) {
                PortalRegistration::switchCurrency($reg, $currency);
                return ['ok' => true, 'reg' => PortalRegistration::findByToken($have['token'])];
            }
        }
        $usd = $currency === 'USD';
        $reg = PortalRegistration::create([
            'type' => 'intlcountry',
            'full_name' => $seeker['full_name'],
            'mobile' => $seeker['mobile'],
            'email' => $seeker['email'],
            'city' => $seeker['city'],
            'state' => $seeker['state'],
            'pincode' => $seeker['pincode'],
            'categories' => $seeker['categories'],
            'details' => json_encode(['seeker_reg_id' => (int)$seeker['id'], 'country' => $country,
                'price_inr' => FormRegistry::INTL_COUNTRY_FEE_INR, 'price_usd' => FormRegistry::INTL_COUNTRY_FEE_USD], JSON_UNESCAPED_UNICODE),
            'declaration_accepted' => 1,
            'currency' => $usd ? 'USD' : 'INR',
        ], 'ICU', $usd ? FormRegistry::INTL_COUNTRY_FEE_USD : FormRegistry::INTL_COUNTRY_FEE_INR);
        return $reg ? ['ok' => true, 'reg' => $reg] : ['ok' => false, 'msg' => ['सर्वर त्रुटि, दोबारा प्रयास करें', 'Server error, please retry']];
    }

    /** Open (or reuse) an unpaid plan; paying while a plan is active extends it from its end date. */
    public static function createPlan(array $provider, ?string $option = null): ?array
    {
        $options = self::planOptions($provider);
        if ($options && !isset($options[(string)$option])) {
            return null;
        }
        $opt = $options[(string)$option] ?? null;
        $kind = self::kindOf((string)$provider['type']);
        if (!$kind || self::roleOf((string)$provider['type']) !== 'provider' || self::KINDS[$kind]['plan'] === null) {
            return null;
        }
        $planType = self::KINDS[$kind]['plan'];
        $open = Database::getInstance()->fetchOne(
            "SELECT id FROM portal_registrations WHERE type = ? AND payment_status <> 'paid'
             AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.provider_reg_id')) = ?
             AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(details, '$.plan_option')), '') = ?
             AND created_at > NOW() - INTERVAL 1 DAY ORDER BY id DESC LIMIT 1",
            [$planType, (string)$provider['id'], (string)($opt ? $option : '')]
        );
        if ($open) {
            return PortalRegistration::find((int)$open['id']);
        }
        $form = FormRegistry::byType($planType);
        $active = self::activePlan($provider);
        $details = ['provider_reg_id' => (int)$provider['id'], 'business_name' => $provider['details']['business_name'] ?? ($provider['details']['institute_name'] ?? '')];
        if ($active) {
            $details['renews_reg_id'] = (int)$active['id'];
        }
        if ($opt) {
            $details['plan_option'] = (string)$option;
            $details['plan_days'] = (int)$opt['days'];
            if ($opt['currency'] === 'USD') {
                $details['usd_price'] = (float)$opt['fee']; // payable in ₹ too, at the admin rate
            }
        } elseif (self::livesAbroad($provider)) {
            // Mentors / internship providers outside India: USD 5, payable in ₹ too at the admin rate.
            $opt = ['currency' => 'USD', 'fee' => FormRegistry::PROVIDER_PLAN_FEE_USD];
            $details['usd_price'] = FormRegistry::PROVIDER_PLAN_FEE_USD;
        }
        return PortalRegistration::create([
            'type' => $planType,
            'full_name' => $provider['full_name'],
            'mobile' => $provider['mobile'],
            'email' => $provider['email'],
            'gstin' => $provider['gstin'] ?? null,
            'city' => $provider['city'],
            'district' => $provider['district'],
            'state' => $provider['state'],
            'pincode' => $provider['pincode'],
            'categories' => $provider['categories'],
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'email_verified_at' => $provider['email_verified_at'] ?? null,
            'declaration_accepted' => 1,
            'currency' => $opt['currency'] ?? 'INR',
        ], $form['prefix'], (float)($opt['fee'] ?? FormRegistry::PROVIDER_PLAN_FEE));
    }

    /** ['today' => left today, 'total' => left in plan or null, 'can' => bool] */
    public static function quota(array $plan): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $today = (int)($db->fetchOne('SELECT COUNT(*) AS c FROM mentoring_views WHERE plan_reg_id = ? AND viewed_on = CURDATE()', [(int)$plan['id']])['c'] ?? 0);
        $cap = self::TOTAL_LIMIT[$plan['type']] ?? null;
        $total = $cap === null ? null : max(0, $cap - (int)($db->fetchOne('SELECT COUNT(*) AS c FROM mentoring_views WHERE plan_reg_id = ?', [(int)$plan['id']])['c'] ?? 0));
        $leftToday = max(0, self::DAILY_LIMIT - $today);
        if ($total !== null) {
            $leftToday = min($leftToday, $total);
        }
        return ['today' => $leftToday, 'total' => $total, 'can' => $leftToday > 0];
    }

    public static function hasOpened(int $providerId, int $seekerId): bool
    {
        self::ensureSchema();
        return (bool)Database::getInstance()->fetchOne('SELECT 1 FROM mentoring_views WHERE provider_reg_id = ? AND seeker_reg_id = ?', [$providerId, $seekerId]);
    }

    /** Use one of today's 3 profile views. @return array{ok: bool, msg?: array} */
    public static function openProfile(array $provider, array $seeker): array
    {
        if (self::kindOf((string)$seeker['type']) !== self::kindOf((string)$provider['type']) || self::roleOf((string)$seeker['type']) !== 'seeker') {
            return ['ok' => false, 'msg' => ['यह प्रोफ़ाइल आपके प्रकार की नहीं है', 'This profile is not for your type of provider']];
        }
        if (self::hasOpened((int)$provider['id'], (int)$seeker['id'])) {
            return self::activePlan($provider) ? ['ok' => true] : ['ok' => false, 'msg' => self::needPlanMsg()];
        }
        $plan = self::activePlan($provider);
        if (!$plan) {
            return ['ok' => false, 'msg' => self::needPlanMsg()];
        }
        if (!self::quota($plan)['can']) {
            return ['ok' => false, 'msg' => ($plan['type'] === 'internplan' && self::quota($plan)['total'] === 0)
                ? ['आपके प्लान की 20 प्रोफ़ाइल पूरी हो गई हैं। नया प्लान लें।', 'Your plan’s 20 profiles are used up. Get a new plan.']
                : ['आज की 3 प्रोफ़ाइल पूरी हो गई हैं – कल फिर खोलें।', 'Today’s 3 profiles are used up – open more tomorrow.']];
        }
        Database::getInstance()->execute(
            'INSERT IGNORE INTO mentoring_views (provider_reg_id, plan_reg_id, seeker_reg_id, viewed_on) VALUES (?, ?, ?, CURDATE())',
            [(int)$provider['id'], (int)$plan['id'], (int)$seeker['id']]
        );
        return ['ok' => true];
    }

    private static function needPlanMsg(): array
    {
        return ['पूरी प्रोफ़ाइल और समझौते के लिए प्लान लें (मेंटर / इंटर्नशिप ₹155 – भारत के बाहर USD 5, भर्ती ₹216 से)', 'Get a plan to open full profiles and sign agreements (mentors / internships ₹155 – USD 5 outside India, hiring from ₹216)'];
    }

    // ------------------------------------------------------------------
    // Directories & numbers
    // ------------------------------------------------------------------

    /**
     * Open seekers. Filters: q (skills / name / qualification / course / about), loc (city, district,
     * state, PIN), pref (preferred location), qual (qualification code), timing.
     */
    public static function seekers(string $kind, array $f, int $limit = 30, int $offset = 0): array
    {
        self::ensureSchema();
        [$where, $params] = self::filterSql($f, true);
        $where = 'r.type IN (' . self::inList(self::KINDS[$kind]['seekers']) . ') AND ' . self::OPEN_SEEKER_SQL . $where;
        $db = Database::getInstance();
        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM portal_registrations r WHERE $where", $params)['c'] ?? 0);
        $rows = $db->fetchAll("SELECT r.* FROM portal_registrations r WHERE $where ORDER BY r.paid_at DESC LIMIT " . (int)$limit . ' OFFSET ' . max(0, $offset), $params);
        return ['rows' => array_map([self::class, 'decode'], $rows), 'total' => $total];
    }

    /**
     * Seekers who filled the form but have not paid yet (last 90 days, no paid registration of the
     * same type on that mobile). Shown masked; a paid provider can invite them to pay.
     */
    public static function unpaidSeekers(string $kind, array $f, int $limit = 30): array
    {
        self::ensureSchema();
        [$where, $params] = self::filterSql($f, true);
        $rows = Database::getInstance()->fetchAll(
            "SELECT r.* FROM portal_registrations r
             WHERE r.type IN (" . self::inList(self::KINDS[$kind]['seekers']) . ") AND r.payment_status IN ('pending','failed') AND r.created_at > NOW() - INTERVAL 90 DAY
               AND NOT EXISTS (SELECT 1 FROM portal_registrations p WHERE p.type = r.type AND p.mobile = r.mobile AND p.payment_status = 'paid')
               AND r.id = (SELECT MAX(x.id) FROM portal_registrations x WHERE x.type = r.type AND x.mobile = r.mobile)" . $where . "
             ORDER BY r.created_at DESC LIMIT " . (int)$limit,
            $params
        );
        return array_map([self::class, 'decode'], $rows);
    }

    /**
     * A paid provider asks an unpaid seeker to pay the one-time form fee (one invite per pair,
     * at most one invite email per seeker a day).
     */
    public static function inviteToPay(array $provider, array $seeker): array
    {
        self::ensureSchema();
        if (self::roleOf((string)$provider['type']) !== 'provider' || self::kindOf((string)$provider['type']) !== self::kindOf((string)$seeker['type'])
            || self::roleOf((string)$seeker['type']) !== 'seeker' || $seeker['payment_status'] === 'paid') {
            return ['ok' => false, 'msg' => ['यह आमंत्रण संभव नहीं है', 'This invite is not possible']];
        }
        if (!self::isVerified($provider) || !self::activePlan($provider)) {
            return ['ok' => false, 'msg' => self::isVerified($provider) ? self::needPlanMsg() : ['Jobsence टीम द्वारा आपका सत्यापन बाकी है', 'Your verification by the Jobsence team is pending']];
        }
        $db = Database::getInstance();
        if ($db->fetchOne('SELECT 1 FROM mentoring_invites WHERE provider_reg_id = ? AND seeker_reg_id = ?', [(int)$provider['id'], (int)$seeker['id']])) {
            return ['ok' => true, 'msg' => ['आप पहले ही आमंत्रण भेज चुके हैं', 'You have already invited this candidate']];
        }
        $recent = $db->fetchOne('SELECT 1 FROM mentoring_invites WHERE seeker_reg_id = ? AND created_at > NOW() - INTERVAL 1 DAY', [(int)$seeker['id']]);
        $db->execute('INSERT IGNORE INTO mentoring_invites (provider_reg_id, seeker_reg_id, emailed) VALUES (?, ?, ?)', [(int)$provider['id'], (int)$seeker['id'], $recent ? 0 : 1]);
        if (!$recent) {
            MentoringMailer::payToConnect('seeker', $seeker, self::displayName(self::decode($provider)), (string)$seeker['categories']);
        }
        return ['ok' => true, 'msg' => ['उम्मीदवार को शुल्क भरने का ईमेल भेज दिया गया। भुगतान होते ही वह आपकी सूची में दिखेगा।', 'The candidate has been emailed to pay the form fee. Once paid, they appear in your list.']];
    }

    /** Verified providers of a kind (free registration + Jobsence verification). */
    public static function providers(string $kind, array $f, int $limit = 30, int $offset = 0): array
    {
        self::ensureSchema();
        [$where, $params] = self::filterSql($f, false);
        $where = "r.type = ? AND r.payment_status = 'paid' AND r.status = 'selected'" . $where;
        array_unshift($params, self::KINDS[$kind]['provider']);
        $db = Database::getInstance();
        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM portal_registrations r WHERE $where", $params)['c'] ?? 0);
        $rows = $db->fetchAll("SELECT r.* FROM portal_registrations r WHERE $where ORDER BY r.paid_at DESC LIMIT " . (int)$limit . ' OFFSET ' . max(0, $offset), $params);
        return ['rows' => array_map([self::class, 'decode'], $rows), 'total' => $total];
    }

    private static function filterSql(array $f, bool $seeker): array
    {
        $where = '';
        $params = [];
        $q = trim((string)($f['q'] ?? ''));
        if ($q !== '') {
            foreach (array_slice(preg_split('/[\s,]+/u', $q) ?: [], 0, 5) as $word) {
                if (mb_strlen($word) < 2) {
                    continue;
                }
                $like = '%' . $word . '%';
                $where .= " AND (r.categories LIKE ? OR r.full_name LIKE ? OR r.qualification LIKE ?
                    OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.about_me')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.known_skills')) LIKE ?
                    OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.current_course')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.qualification_detail')) LIKE ?
                    OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.business_name')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.institute_name')) LIKE ?
                    OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.education_experience')) LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.details, '$.about_internship')) LIKE ?)";
                array_push($params, ...array_fill(0, 11, $like));
            }
        }
        $loc = trim((string)($f['loc'] ?? ''));
        if ($loc !== '') {
            $like = '%' . $loc . '%';
            $where .= ' AND (r.city LIKE ? OR r.district LIKE ? OR r.state LIKE ? OR r.pincode = ?)';
            array_push($params, $like, $like, $like, preg_replace('/\D/', '', $loc));
        }
        $pref = trim((string)($f['pref'] ?? ''));
        if ($pref !== '') {
            $where .= ' AND r.preferred_location LIKE ?';
            $params[] = '%' . $pref . '%';
        }
        if (!empty($f['qual']) && isset(FormRegistry::QUALIFICATIONS[$f['qual']])) {
            $where .= ' AND r.qualification = ?';
            $params[] = $f['qual'];
        }
        if ($seeker && !empty($f['timing']) && isset(FormRegistry::TIMINGS[$f['timing']])) {
            $where .= " AND JSON_CONTAINS(COALESCE(JSON_EXTRACT(r.details, '$.preferred_timing'), JSON_ARRAY()), JSON_QUOTE(?))";
            $params[] = $f['timing'];
        }
        return [$where, $params];
    }

    /** How many are looking for what, where and when (public numbers). */
    public static function stats(string $kind): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT r.categories, r.state, JSON_EXTRACT(r.details, '$.preferred_timing') AS timing FROM portal_registrations r
             WHERE r.type IN (" . self::inList(self::KINDS[$kind]['seekers']) . ") AND " . self::OPEN_SEEKER_SQL
        );
        $cats = $states = $timing = [];
        foreach ($rows as $r) {
            foreach (array_filter(array_map('trim', explode(',', (string)$r['categories']))) as $c) {
                $cats[$c] = ($cats[$c] ?? 0) + 1;
            }
            if (!empty($r['state'])) {
                $states[$r['state']] = ($states[$r['state']] ?? 0) + 1;
            }
            foreach ((array)(json_decode((string)$r['timing'], true) ?: []) as $t) {
                $timing[$t] = ($timing[$t] ?? 0) + 1;
            }
        }
        arsort($cats);
        arsort($states);
        arsort($timing);
        $providers = (int)($db->fetchOne(
            "SELECT COUNT(*) AS c FROM portal_registrations WHERE type = ? AND payment_status = 'paid' AND status = 'selected'",
            [self::KINDS[$kind]['provider']]
        )['c'] ?? 0);
        return ['seekers' => count($rows), 'providers' => $providers, 'categories' => array_slice($cats, 0, 30, true),
            'states' => array_slice($states, 0, 15, true), 'timing' => $timing];
    }

    public static function decode(array $row): array
    {
        if (!is_array($row['details'] ?? null)) {
            $row['details'] = json_decode((string)($row['details'] ?? ''), true) ?: [];
        }
        return $row;
    }

    /** Display name of a provider (organisation first). */
    public static function displayName(array $reg): string
    {
        $d = $reg['details'] ?? [];
        return (string)(($d['business_name'] ?? '') ?: (($d['institute_name'] ?? '') ?: $reg['full_name']));
    }

    // ------------------------------------------------------------------
    // Agreements
    // ------------------------------------------------------------------

    /** [assignment, role ('seeker'|'provider')] for either party's agreement link. */
    public static function findAgreement(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }
        self::ensureSchema();
        $a = Database::getInstance()->fetchOne('SELECT * FROM mentor_assignments WHERE token = ? OR candidate_token = ? LIMIT 1', [$token, $token]);
        if (!$a) {
            return null;
        }
        return [self::withCandidateToken($a), hash_equals((string)$a['token'], $token) ? 'provider' : 'seeker'];
    }

    /** Older (admin / auto) requests have no seeker link yet. */
    private static function withCandidateToken(array $a): array
    {
        if (empty($a['candidate_token'])) {
            $a['candidate_token'] = bin2hex(random_bytes(20));
            Database::getInstance()->execute('UPDATE mentor_assignments SET candidate_token = ? WHERE id = ? AND candidate_token IS NULL', [$a['candidate_token'], (int)$a['id']]);
        }
        return $a;
    }

    public static function linkFor(array $a, string $role): string
    {
        return '/mentoring/agreement/' . ($role === 'provider' ? $a['token'] : $a['candidate_token']);
    }

    /**
     * A seeker or provider asks to connect. Returns ['ok', 'assignment'?, 'msg'?].
     * The initiator then signs on the agreement page; the other party is emailed after that.
     */
    public static function request(array $me, array $other): array
    {
        $role = self::roleOf((string)$me['type']);
        $seeker = $role === 'seeker' ? $me : $other;
        $provider = $role === 'seeker' ? $other : $me;
        $kind = self::kindOf((string)$seeker['type']);
        if (!$kind || $kind !== self::kindOf((string)$provider['type']) || self::roleOf((string)$seeker['type']) !== 'seeker' || self::roleOf((string)$provider['type']) !== 'provider') {
            return ['ok' => false, 'msg' => ['यह अनुरोध संभव नहीं है', 'This request is not possible']];
        }
        if (self::seekerState($seeker) !== 'open') {
            return ['ok' => false, 'msg' => $role === 'seeker'
                ? ['आपका रजिस्ट्रेशन सक्रिय नहीं है या आपको पहले से मेंटर मिल चुका है', 'Your registration is not open, or you already have a mentor']
                : ['यह उम्मीदवार अब उपलब्ध नहीं है', 'This candidate is no longer available']];
        }
        if (!self::isVerified($provider)) {
            return ['ok' => false, 'msg' => $role === 'provider'
                ? ['Jobsence टीम द्वारा आपका सत्यापन बाकी है', 'Your verification by the Jobsence team is pending']
                : ['यह प्रदाता अभी सत्यापित नहीं है', 'This provider is not verified yet']];
        }
        if ($role === 'provider') {
            if (!self::KINDS[$kind]['provider_initiates']) {
                return ['ok' => false, 'msg' => ['ग्राहक आपसे सेवा माँगेंगे – आप अनुरोध का जवाब दें', 'Customers request your service – you answer their requests']];
            }
            if (!self::activePlan($provider)) {
                return ['ok' => false, 'msg' => self::needPlanMsg()];
            }
            if (!self::hasOpened((int)$provider['id'], (int)$seeker['id'])) {
                return ['ok' => false, 'msg' => ['पहले उम्मीदवार की पूरी प्रोफ़ाइल खोलें', 'Open the candidate’s full profile first']];
            }
        }

        $db = Database::getInstance();
        $existing = $db->fetchOne(
            "SELECT * FROM mentor_assignments WHERE candidate_reg_id = ? AND mentor_reg_id = ? AND status IN ('pending','accepted') LIMIT 1",
            [(int)$seeker['id'], (int)$provider['id']]
        );
        if ($existing) {
            return ['ok' => true, 'assignment' => self::withCandidateToken($existing)];
        }

        $skill = self::sharedSkill($seeker, $provider);
        $mode = $kind === 'skill' ? MentorMatching::candidateMode($seeker) : 'na';
        if ($kind === 'nearme') {
            $skill = (string)(explode(',', (string)$provider['categories'])[0] ?? 'Service');
        }
        $db->execute(
            "INSERT INTO mentor_assignments (candidate_reg_id, mentor_reg_id, kind, skill, mode, status, token, candidate_token, source, initiated_by, agreement_version, expires_at)
             VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, NOW() + INTERVAL " . self::RESPONSE_DAYS . ' DAY)',
            [(int)$seeker['id'], (int)$provider['id'], $kind, mb_substr($skill, 0, 120), $mode, bin2hex(random_bytes(20)), bin2hex(random_bytes(20)),
                $role === 'seeker' ? 'candidate_choice' : 'mentor_choice', $role, self::AGREEMENT_VERSION]
        );
        return ['ok' => true, 'assignment' => $db->fetchOne('SELECT * FROM mentor_assignments WHERE id = ?', [(int)$db->lastInsertId()])];
    }

    /** First of the seeker's skills (in their order) that the provider offers; else the seeker's first. */
    public static function sharedSkill(array $seeker, array $provider): string
    {
        $mine = array_values(array_filter(array_map('trim', explode(',', (string)$seeker['categories']))));
        $theirs = array_map(static fn($s) => mb_strtolower(trim($s)), explode(',', (string)$provider['categories']));
        foreach ($mine as $s) {
            $l = mb_strtolower($s);
            foreach ($theirs as $t) {
                if ($t !== '' && ($t === $l || str_contains($t, $l) || str_contains($l, $t))) {
                    return $s;
                }
            }
        }
        return $mine[0] ?? 'General';
    }

    /** Parties of an agreement. */
    public static function parties(array $a): array
    {
        return [PortalRegistration::find((int)$a['candidate_reg_id']), PortalRegistration::find((int)$a['mentor_reg_id'])];
    }

    /** Why this party cannot sign right now, or null. Provider signing may use one of today's profile views. */
    public static function signBlocker(array $a, string $role, bool $consume = false): ?array
    {
        [$seeker, $provider] = self::parties($a);
        if (!$seeker || !$provider) {
            return ['समझौता उपलब्ध नहीं है', 'Agreement not available'];
        }
        if ($a['status'] !== 'pending') {
            return ['यह समझौता अब खुला नहीं है', 'This agreement is no longer open'];
        }
        if (strtotime((string)$a['expires_at']) < time()) {
            return ['समझौते का समय समाप्त हो गया', 'This agreement request has expired'];
        }
        if (self::seekerState($seeker) !== 'open') {
            return ['उम्मीदवार का रजिस्ट्रेशन अब खुला नहीं है', 'The candidate’s registration is no longer open'];
        }
        if ($role === 'provider') {
            if (!self::isVerified($provider)) {
                return ['Jobsence टीम द्वारा आपका सत्यापन बाकी है', 'Your verification by the Jobsence team is pending'];
            }
            if (!self::activePlan($provider)) {
                return self::KINDS[$a['kind'] ?? 'skill']['plan'] === null
                    ? ['आपकी Near Me लिस्टिंग की अवधि समाप्त है – पहले रिन्यू करें', 'Your Near Me listing has expired – renew it first']
                    : self::needPlanMsg();
            }
            if (self::KINDS[$a['kind'] ?? 'skill']['plan'] !== null && !self::hasOpened((int)$provider['id'], (int)$seeker['id'])) {
                if (!$consume) {
                    return self::quota(self::activePlan($provider))['can'] ? null : ['आज की 3 प्रोफ़ाइल पूरी हो गई हैं – कल साइन करें', 'Today’s 3 profiles are used up – sign tomorrow'];
                }
                $open = self::openProfile($provider, $seeker);
                if (!$open['ok']) {
                    return $open['msg'];
                }
            }
        }
        return null;
    }

    public static function sendOtp(array $a, string $role): array
    {
        [$seeker, $provider] = self::parties($a);
        $email = (string)(($role === 'provider' ? $provider : $seeker)['email'] ?? '');
        if ($email === '') {
            return ['success' => false, 'error' => 'No email on this registration'];
        }
        $last = (int)($_SESSION['mentoring_otp_sent'][$a['id'] . $role] ?? 0);
        if ($last > time() - 60) {
            return ['success' => false, 'error' => '1 मिनट बाद दोबारा भेजें / Please wait 1 minute before resending'];
        }
        $r = VerificationService::sendEmailAuthOTP($email, self::OTP_PURPOSE);
        if (!empty($r['success'])) {
            $_SESSION['mentoring_otp_sent'][$a['id'] . $role] = time();
        }
        return $r + ['email' => $email];
    }

    /** Sign with the email OTP. When both have signed, Jobsence countersigns and contacts are shared. */
    public static function sign(array $a, string $role, string $otp, string $ip): array
    {
        if ($blocker = self::signBlocker($a, $role)) {
            return ['ok' => false, 'msg' => $blocker];
        }
        [$seeker, $provider] = self::parties($a);
        $email = (string)(($role === 'provider' ? $provider : $seeker)['email'] ?? '');
        $v = VerificationService::verifyEmailAuthOTP($email, $otp, self::OTP_PURPOSE);
        if (empty($v['success'])) {
            return ['ok' => false, 'msg' => ['OTP सही नहीं है', (string)($v['error'] ?? 'Invalid OTP')]];
        }
        if ($blocker = self::signBlocker($a, $role, true)) {
            return ['ok' => false, 'msg' => $blocker];
        }
        $col = $role === 'provider' ? 'mentor' : 'candidate';
        $db = Database::getInstance();
        $db->execute("UPDATE mentor_assignments SET {$col}_signed_at = NOW(), {$col}_sign_ip = ? WHERE id = ? AND {$col}_signed_at IS NULL", [substr($ip, 0, 45), (int)$a['id']]);
        $a = $db->fetchOne('SELECT * FROM mentor_assignments WHERE id = ?', [(int)$a['id']]);

        if (!empty($a['candidate_signed_at']) && !empty($a['mentor_signed_at'])) {
            $done = $db->execute(
                "UPDATE mentor_assignments SET status = 'accepted', jobsence_signed_at = NOW(), responded_at = NOW(),
                 agreement_version = COALESCE(agreement_version, ?)
                 WHERE id = ? AND status = 'pending'",
                ['2026-10-v1', (int)$a['id']]
            );
            if ($done) {
                // One mentor / provider at a time: other open requests for this seeker close.
                if (self::KINDS[$a['kind'] ?? 'skill']['single'] ?? true) $db->execute("UPDATE mentor_assignments SET status = 'cancelled', responded_at = NOW() WHERE candidate_reg_id = ? AND status = 'pending' AND id <> ?", [(int)$seeker['id'], (int)$a['id']]);
                $a = $db->fetchOne('SELECT * FROM mentor_assignments WHERE id = ?', [(int)$a['id']]);
                MentoringMailer::agreementSigned($a, $seeker, $provider);
            }
            return ['ok' => true, 'msg' => ['समझौता पूरा! अब आप एक-दूसरे का फ़ोन और ईमेल देख सकते हैं।', 'Agreement complete! You can now see each other’s phone and email.']];
        }
        if ($role === 'seeker' && !self::activePlan($provider)) {
            // Fee is mandatory on both sides: ask the provider to pay before they can see the candidate.
            MentoringMailer::payToConnect('provider', $provider, (string)$seeker['full_name'], (string)$a['skill'], $a);
        } else {
            MentoringMailer::pleaseSign($a, $role === 'provider' ? 'seeker' : 'provider', $seeker, $provider);
        }
        return ['ok' => true, 'msg' => ['आपने साइन कर दिया। दूसरे पक्ष को ईमेल भेज दिया गया है।', 'You have signed. The other party has been emailed to sign.']];
    }

    public static function decline(array $a, string $role): bool
    {
        if ($a['status'] !== 'pending') {
            return false;
        }
        Database::getInstance()->execute("UPDATE mentor_assignments SET status = 'declined', responded_at = NOW() WHERE id = ? AND status = 'pending'", [(int)$a['id']]);
        [$seeker, $provider] = self::parties($a);
        if ($seeker && $provider) {
            MentoringMailer::declined($a, $role, $seeker, $provider);
            if ($role === 'provider' && ($a['kind'] ?? 'skill') === 'skill') {
                MentorMatching::offerAlternatives($seeker, 'declined');
            }
        }
        return true;
    }

    /**
     * End a signed agreement. A seeker who rejects their mentor / provider closes their registration
     * (a new form fee is needed); a provider ending it leaves the seeker eligible.
     */
    public static function end(array $a, string $role, string $reason): bool
    {
        if ($a['status'] !== 'accepted') {
            return false;
        }
        $db = Database::getInstance();
        $db->execute(
            "UPDATE mentor_assignments SET status = 'ended', ended_at = NOW(), ended_by = ?, end_reason = ? WHERE id = ? AND status = 'accepted'",
            [$role, mb_substr($reason, 0, 500), (int)$a['id']]
        );
        [$seeker, $provider] = self::parties($a);
        if ($role === 'seeker' && $seeker && (self::KINDS[$a['kind'] ?? 'skill']['single'] ?? true)) {
            $db->execute("UPDATE portal_registrations SET status = 'completed', admin_notes = CONCAT(COALESCE(admin_notes, ''), ?) WHERE id = ?",
                ["\n[" . date('Y-m-d H:i') . '] Rejected mentor/provider ' . ($provider['reg_no'] ?? '') . ' – registration closed, new form fee needed.', (int)$seeker['id']]);
        }
        if ($seeker && $provider) {
            MentoringMailer::ended($a, $role, $seeker, $provider, $reason);
        }
        return true;
    }

    /** Agreements of a seeker or provider, newest first, with the other party's display data. */
    public static function agreementsFor(array $reg): array
    {
        self::ensureSchema();
        $role = self::roleOf((string)$reg['type']);
        $col = $role === 'provider' ? 'mentor_reg_id' : 'candidate_reg_id';
        $rows = Database::getInstance()->fetchAll("SELECT * FROM mentor_assignments WHERE $col = ? ORDER BY id DESC LIMIT 100", [(int)$reg['id']]);
        foreach ($rows as &$a) {
            $a = self::withCandidateToken($a);
            $a['other'] = PortalRegistration::find((int)($role === 'provider' ? $a['candidate_reg_id'] : $a['mentor_reg_id']));
            $a['link'] = self::linkFor($a, (string)$role);
        }
        unset($a);
        return $rows;
    }

    /** Profiles a provider has opened (newest first). */
    public static function openedBy(int $providerId): array
    {
        self::ensureSchema();
        return array_map([self::class, 'decode'], Database::getInstance()->fetchAll(
            'SELECT r.*, v.viewed_on FROM mentoring_views v JOIN portal_registrations r ON r.id = v.seeker_reg_id WHERE v.provider_reg_id = ? ORDER BY v.id DESC LIMIT 100',
            [$providerId]
        ));
    }

    /** Legal entity behind the Jobsence brand (party to agreements); override with JOBSENCE_LEGAL_NAME. */
    public static function legalName(): string
    {
        return trim((string)($_ENV['JOBSENCE_LEGAL_NAME'] ?? '')) ?: 'Mindware';
    }

    /** Registered address of the legal entity; override with JOBSENCE_LEGAL_ADDRESS. */
    public static function legalAddress(): string
    {
        return trim((string)($_ENV['JOBSENCE_LEGAL_ADDRESS'] ?? '')) ?: 'S-4, Pankaj Plaza, Plot No. 7, Sector 12, Dwarka, New Delhi, India';
    }

    /**
     * Tripartite agreement clauses [hi, en] for the version a party signed.
     * v2 (2026-10-v2) is the full agreement; v1 is kept so older signed agreements show what was signed.
     */
    public static function clauses(string $kind, string $skill, ?string $version = null): array
    {
        $version ??= self::AGREEMENT_VERSION;
        $what = match ($kind) {
            'internship' => ['इंटर्नशिप', 'an internship'],
            'job' => ['नौकरी / रोज़गार', 'a job / employment'],
            'nearme' => ['Near Me सेवा', 'a Near Me service'],
            default => ['स्किल ट्रेनिंग / मेंटरिंग', 'skill training / mentoring'],
        };
        if ($version === '2026-10-v1') {
            return self::clausesV1($what, $skill);
        }
        $entity = self::legalName();
        $address = self::legalAddress();

        $c = [
            ["1. पक्ष और उद्देश्य – यह समझौता इलेक्ट्रॉनिक रूप से (1) उम्मीदवार / ग्राहक, (2) प्रदाता (मेंटर, संस्थान, कंपनी या सेवा प्रदाता) और (3) Jobsence (jobsence.com, संचालक: {$entity}, पंजीकृत पता: {$address}) के बीच “{$skill}” में {$what[0]} के लिए किया गया है।",
                "1. Parties and purpose – This agreement is made electronically between (1) the Candidate / Customer, (2) the Provider (mentor, institute, company or service provider) and (3) Jobsence (jobsence.com, operated by {$entity}, registered address: {$address}) for {$what[1]} in “{$skill}”."],
            ["2. Jobsence की भूमिका – इस समझौते में “Jobsence” का अर्थ {$entity} है, जो Jobsence ब्रांड और jobsence.com का संचालन करता है। Jobsence सूचना प्रौद्योगिकी अधिनियम, 2000 के तहत एक ऑनलाइन प्लेटफ़ॉर्म और मध्यस्थ (intermediary) है। Jobsence किसी भी पक्ष का नियोक्ता, ट्रेनिंग संस्थान, ठेकेदार या एजेंट नहीं है और काम, ट्रेनिंग या सेवा की देखरेख या नियंत्रण नहीं करता।",
                "2. Role of Jobsence – In this agreement “Jobsence” means {$entity}, which operates the Jobsence brand and jobsence.com. Jobsence is an online platform and intermediary under the Information Technology Act, 2000. Jobsence is not the employer, training institute, contractor or agent of either party and does not supervise or control the work, training or service."],
            ['3. कोई गारंटी नहीं – Jobsence किसी नौकरी, इंटर्नशिप, प्लेसमेंट, आय, ट्रेनिंग के परिणाम या सेवा की गुणवत्ता की गारंटी नहीं देता।',
                '3. No guarantee – Jobsence does not guarantee any job, internship, placement, income, training result or quality of service.'],
            ['4. सत्यापन – Jobsence पहचान और दस्तावेज़ जाँचने का उचित प्रयास करता है, लेकिन उनकी पूरी सत्यता की ज़िम्मेदारी नहीं लेता। काम / ट्रेनिंग शुरू करने से पहले दोनों पक्ष स्वयं भी जाँच करेंगे।',
                '4. Verification – Jobsence makes reasonable efforts to check identity and documents but does not warrant their accuracy. Both parties will make their own checks before work or training starts.'],
            ['5. Jobsence का शुल्क – कोई रिफ़ंड नहीं – Jobsence को दिया गया हर शुल्क (फॉर्म शुल्क, पास, प्लान, रिन्यूअल या लेट फ़ीस) केवल प्लेटफ़ॉर्म के उपयोग और मिलान का शुल्क है और किसी भी स्थिति में वापस नहीं होगा – चाहे मिलान, नौकरी, इंटर्नशिप या ट्रेनिंग न मिले, कोई पक्ष समझौता समाप्त करे या प्रदाता को छोड़े, प्लान या पास का उपयोग न हो, या नियम तोड़ने पर एक्सेस बंद हो। केवल तकनीकी त्रुटि से हुआ दोहरा या असफल भुगतान (राशि दो बार कटना, या राशि कटने पर भी रजिस्ट्रेशन न बनना) मूल भुगतान माध्यम में लौटाया जाएगा। यह शुल्क किसी नौकरी या ट्रेनिंग की क़ीमत नहीं है; पक्षों के बीच वेतन, स्टाइपेंड, फ़ीस या सेवा शुल्क दोनों आपस में लिखित रूप में तय करेंगे और उसकी ज़िम्मेदारी Jobsence की नहीं है।',
                '5. Jobsence fees – no refunds – Every amount paid to Jobsence (form fee, pass, plan, renewal or late fee) is a platform fee for access and matching only and is non-refundable in any condition – including if no match, job, internship or training results, if either party ends the agreement or rejects the provider, if a plan or pass is not used, or if access is suspended for a breach. Only a duplicate or failed payment caused by a technical error (amount debited twice, or debited without a registration being created) is returned to the original payment method. This fee is not the price of any job or training; any salary, stipend, fee or service charge between the parties is agreed between them in writing, and Jobsence is not responsible for it.'],
            ['6. अवैध वसूली पर रोक – प्रदाता उम्मीदवार से नौकरी या इंटर्नशिप के बदले कोई पैसा, सिक्योरिटी डिपॉज़िट या मूल दस्तावेज़ नहीं माँगेगा और लिखित में तय राशि से अधिक नहीं लेगा। ऐसी माँग की तुरंत Jobsence को शिकायत करें।',
                '6. No unlawful charges – The Provider will not ask the Candidate for any money, security deposit or original documents in return for a job or internship, and will not charge more than what is agreed in writing. Report any such demand to Jobsence at once.'],
            ['7. व्यक्तिगत जानकारी – दोनों पक्ष एक-दूसरे की जानकारी (फ़ोन, ईमेल, पता, रिज़्यूमे) केवल इसी उद्देश्य के लिए उपयोग करेंगे, गोपनीय रखेंगे, किसी को साझा या बेचेंगे नहीं और ज़रूरत ख़त्म होने पर हटा देंगे, डिजिटल व्यक्तिगत डेटा संरक्षण अधिनियम, 2023 के अनुसार। दोनों पक्ष सेवा देने और इस समझौते का रिकॉर्ड रखने के लिए Jobsence द्वारा अपनी जानकारी के उपयोग की सहमति देते हैं।',
                '7. Personal data – Each party will use the other’s details (phone, email, address, resume) only for this purpose, keep them confidential, never share or sell them, and delete them when no longer needed, in line with the Digital Personal Data Protection Act, 2023. Both parties consent to Jobsence processing their data to provide the service and to keep a record of this agreement.'],
            ['8. प्लेटफ़ॉर्म का सही उपयोग – प्रदाता Jobsence से मिली उम्मीदवारों की जानकारी की नकल, स्क्रैपिंग, बल्क डाउनलोड या दूसरे काम में उपयोग नहीं करेगा और अपना लॉगिन / लिंक किसी से साझा नहीं करेगा। उल्लंघन पर बिना रिफ़ंड खाता बंद किया जाएगा और ज़रूरत होने पर अधिकारियों को सूचना दी जाएगी।',
                '8. Fair use of the platform – The Provider will not copy, scrape, bulk-download or reuse candidate information from Jobsence for any other purpose, and will not share its login or links. Breach leads to suspension without refund and, where needed, a report to the authorities.'],
            ['9. आचरण और सुरक्षा – दोनों पक्ष सम्मानजनक, क़ानूनी और भेदभाव-रहित व्यवहार करेंगे; कोई उत्पीड़न नहीं होगा। जहाँ लागू हो, कार्यस्थल पर यौन उत्पीड़न (रोकथाम) अधिनियम, 2013 का पालन होगा। पहली मुलाक़ात आधिकारिक या सार्वजनिक स्थान पर हो। 18 साल से कम उम्र के उम्मीदवार के लिए माता-पिता / अभिभावक की सहमति ज़रूरी है और बाल श्रम क़ानूनों का पालन होगा।',
                '9. Conduct and safety – Both parties will behave respectfully, lawfully and without discrimination; no harassment. Where applicable, the Sexual Harassment of Women at Workplace Act, 2013 will be followed. First meetings should be at official or public places. A candidate under 18 needs parent / guardian consent, and child labour laws will be followed.'],
            ['10. प्रदाता की क़ानूनी ज़िम्मेदारी – इस काम / ट्रेनिंग / सेवा से जुड़े सभी क़ानूनों (श्रम, न्यूनतम वेतन, स्टाइपेंड, टैक्स, लाइसेंस, पेशेवर रजिस्ट्रेशन, सुरक्षा) का पालन केवल प्रदाता की ज़िम्मेदारी है।',
                '10. Provider’s legal duties – Compliance with all laws for this work, training or service (labour, minimum wages, stipend, tax, licences, professional registrations, safety) is solely the Provider’s responsibility.'],
            ['11. उम्मीदवार की ज़िम्मेदारी – उम्मीदवार ने सही जानकारी दी है, तय समय पर उपस्थित रहेगा, प्रदाता के उचित नियमों का पालन करेगा और काम / ट्रेनिंग छोड़ने पर प्रदाता और Jobsence को सूचित करेगा।',
                '11. Candidate’s duties – The Candidate has given true information, will attend as agreed, will follow the Provider’s reasonable rules, and will inform the Provider and Jobsence before leaving.'],
            ['12. समाप्ति – कोई भी पक्ष Jobsence डैशबोर्ड से यह समझौता समाप्त कर सकता है। साइन के बाद उम्मीदवार द्वारा प्रदाता को छोड़ने पर उम्मीदवार का रजिस्ट्रेशन बंद हो जाएगा और नए फॉर्म के लिए फिर से शुल्क देना होगा; प्रदाता द्वारा समाप्त करने पर उम्मीदवार का रजिस्ट्रेशन खुला रहेगा। नियम तोड़ने, धोखाधड़ी या गंभीर शिकायत पर Jobsence किसी भी पक्ष का एक्सेस बिना रिफ़ंड निलंबित या समाप्त कर सकता है।',
                '12. Ending the agreement – Either party may end this agreement from the Jobsence dashboard. If the Candidate rejects the Provider after signing, the Candidate’s registration closes and a fresh form fee is payable; if the Provider ends it, the Candidate stays eligible. Jobsence may suspend or end any party’s access without refund for breach, fraud or serious complaints.'],
            ['13. दायित्व की सीमा – क़ानून द्वारा अनुमत सीमा तक, Jobsence किसी भी पक्ष के कार्य, चूक, धोखाधड़ी, जालसाज़ी, चोट, हानि या नुक़सान के लिए ज़िम्मेदार नहीं है। इस समझौते के तहत Jobsence की कुल ज़िम्मेदारी, दावा करने वाले पक्ष द्वारा इस रजिस्ट्रेशन के लिए Jobsence को दिए गए प्लेटफ़ॉर्म शुल्क से अधिक नहीं होगी।',
                '13. Limitation of liability – To the extent permitted by law, Jobsence is not liable for any act, omission, fraud, forgery, injury, loss or damage caused by either party. Jobsence’s total liability under this agreement will not exceed the platform fee the claiming party paid to Jobsence for this registration.'],
            ['14. क्षतिपूर्ति – उम्मीदवार और प्रदाता, अपने-अपने नियम उल्लंघन, ग़ैरक़ानूनी कार्य या ग़लत जानकारी से Jobsence पर होने वाले किसी भी दावे, नुक़सान या जुर्माने की भरपाई करेंगे।',
                '14. Indemnity – The Candidate and the Provider will each make good any claim, loss or penalty against Jobsence arising from their own breach, unlawful act or false information.'],
            ["15. शिकायत – कोई भी शिकायत Jobsence के शिकायत अधिकारी को gm@jobsence.com पर या डाक से {$entity}, {$address} पर भेजें। शिकायत की पावती 24 घंटे में और समाधान का प्रयास 15 दिन में किया जाएगा। क़ानून के अनुसार ज़रूरत होने पर Jobsence जानकारी सरकारी / पुलिस अधिकारियों को दे सकता है।",
                "15. Grievances – Send any complaint to the Jobsence Grievance Officer at gm@jobsence.com or by post to {$entity}, {$address}. Complaints are acknowledged within 24 hours and Jobsence aims to resolve them within 15 days. Jobsence may share information with government or police authorities where the law requires."],
            ['16. विवाद और क़ानून – यह समझौता भारतीय क़ानून के अधीन है। पहले 30 दिन तक Jobsence की शिकायत प्रक्रिया से सुलझाने का प्रयास होगा। न सुलझने पर, Jobsence से जुड़ा विवाद मध्यस्थता और सुलह अधिनियम, 1996 के तहत एकल मध्यस्थ (पक्षों की सहमति से या उस अधिनियम के अनुसार नियुक्त) को भेजा जाएगा; मध्यस्थता का स्थान नई दिल्ली होगा। इसके अधीन, नई दिल्ली की अदालतों को अधिकार होगा। जो अधिकार क़ानून से समाप्त नहीं किए जा सकते (जैसे उपभोक्ता अधिकार) वे बने रहेंगे।',
                '16. Disputes and law – This agreement is governed by Indian law. The parties will first try to settle any dispute through the Jobsence grievance process for 30 days. If unresolved, a dispute involving Jobsence will go to a sole arbitrator appointed by agreement of the parties or under the Arbitration and Conciliation Act, 1996; the seat of arbitration is New Delhi. Subject to this, courts at New Delhi have jurisdiction. Rights that cannot be excluded by law (such as consumer rights) are not affected.'],
            ['17. इलेक्ट्रॉनिक साइन – ईमेल OTP से साइन किया गया यह समझौता सूचना प्रौद्योगिकी अधिनियम, 2000 की धारा 10A के तहत मान्य इलेक्ट्रॉनिक अनुबंध है। Jobsence दोनों के साइन होते ही अपने आप साइन करता है और साइन का समय, IP और संस्करण सबूत के रूप में सुरक्षित रखता है।',
                '17. Electronic signature – This agreement, signed with an email OTP, is a valid electronic contract under Section 10A of the Information Technology Act, 2000. Jobsence countersigns automatically once both have signed and keeps the signing time, IP address and version as evidence.'],
            ['18. पूरा समझौता – यह समझौता, Jobsence की उपयोग की शर्तें और गोपनीयता नीति मिलकर इस संबंध का पूरा समझौता हैं। Jobsence भविष्य के समझौतों के लिए शर्तें बदल सकता है; आप पर वही संस्करण लागू होगा जिस पर आपने साइन किया है। यह भारत सरकार की योजना नहीं है।',
                '18. Entire agreement – This agreement, the Jobsence Terms of Use and the Privacy Policy together form the whole agreement for this connection. Jobsence may change the terms for future agreements; the version you signed applies to you. This is not a Government of India scheme.'],
        ];

        $c[] = match ($kind) {
            'skill' => ['19. ट्रेनिंग सामग्री – ट्रेनिंग सामग्री प्रदाता / Jobsence की है; उम्मीदवार बिना अनुमति क्लास रिकॉर्ड या साझा नहीं करेगा। मेंटर द्वारा जमा डेमो वीडियो Jobsence की संपत्ति हैं। मेंटर का मानदेय Jobsence की मेंटर शर्तों के अनुसार, सफल ट्रेनिंग के बाद होगा।',
                '19. Training content – Training material belongs to the Provider / Jobsence; the Candidate will not record or share sessions without permission. Demo videos submitted by mentors are Jobsence property. Mentor emolument is per the Jobsence mentor terms, after successful training.'],
            'internship' => ['19. इंटर्नशिप – पेड इंटर्नशिप का स्टाइपेंड ₹8,000 से ₹5,00,000 प्रति माह के बीच होगा जैसा घोषित किया गया है; काम, अवधि और प्रमाणपत्र लिखित में तय होंगे। इंटर्न से इंटर्नशिप के बदले कोई शुल्क नहीं लिया जाएगा।',
                '19. Internship – A paid internship’s stipend will be between ₹8,000 and ₹5,00,000 a month as declared; work, duration and certificate will be agreed in writing. No fee will be taken from the intern for the internship.'],
            'job' => $version === '2026-10-v2'
                ? ['19. नौकरी – प्रदाता पुष्टि करता है कि नौकरी असली और क़ानूनी है, वेतन लागू न्यूनतम वेतन से कम नहीं है, और नियुक्ति पत्र / शर्तें लिखित में दी जाएँगी।',
                    '19. Job – The Provider confirms the vacancy is genuine and lawful, the pay is not below the applicable minimum wage, and the appointment letter / terms will be given in writing.']
                : ['19. नौकरी – प्रदाता पुष्टि करता है कि नौकरी असली और क़ानूनी है, वेतन लागू न्यूनतम वेतन से कम नहीं है, और नियुक्ति पत्र / शर्तें लिखित में दी जाएँगी। विदेश की नौकरी के लिए: ' . FormRegistry::abroadDisclaimer()[0]
                        . ($version === '2026-10-v3' ? '' : ' वीज़ा: ' . FormRegistry::visaDisclaimer()[0]),
                    '19. Job – The Provider confirms the vacancy is genuine and lawful, the pay is not below the applicable minimum wage, and the appointment letter / terms will be given in writing. For jobs abroad: ' . FormRegistry::abroadDisclaimer()[1]
                        . ($version === '2026-10-v3' ? '' : ' Visa: ' . FormRegistry::visaDisclaimer()[1])],
            default => ['19. सेवा – काम का दायरा, क़ीमत, सामान और समय काम शुरू होने से पहले तय होंगे; ग्राहक भुगतान सीधे प्रदाता को करेगा; काम की गुणवत्ता और सुरक्षा प्रदाता की ज़िम्मेदारी है। Jobsence इस सेवा अनुबंध का पक्ष नहीं है।',
                '19. Service – Scope, price, materials and timing are agreed before work starts; the Customer pays the Provider directly; workmanship and safety are the Provider’s responsibility. Jobsence is not a party to the service contract itself.'],
        };
        $c[] = FormRegistry::platformDisclaimer();
        return $c;
    }

    /** Version 1 text (agreements signed before 2026-10-v2). */
    private static function clausesV1(array $what, string $skill): array
    {
        return [
            ["यह समझौता उम्मीदवार, प्रदाता (मेंटर / संस्थान / कंपनी) और Jobsence के बीच “{$skill}” में {$what[0]} के लिए है।", "This agreement is between the candidate, the provider (mentor / institute / company) and Jobsence for {$what[1]} in “{$skill}”."],
            ['Jobsence केवल एक प्लेटफ़ॉर्म और सुविधा देने वाला है; Jobsence नौकरी, प्लेसमेंट या परिणाम की गारंटी नहीं देता।', 'Jobsence is only a platform and facilitator; Jobsence does not guarantee any job, placement or outcome.'],
            ['दोनों पक्ष एक-दूसरे का फ़ोन नंबर और ईमेल केवल इसी उद्देश्य के लिए उपयोग करेंगे और किसी और के साथ साझा नहीं करेंगे।', 'Both parties will use each other’s phone number and email only for this purpose and will not share them with anyone else.'],
            ['प्रदाता उम्मीदवार से Jobsence की जानकारी के बिना कोई अतिरिक्त शुल्क नहीं माँगेगा; सभी फ़ीस और भुगतान लिखित रूप में तय होंगे।', 'The provider will not ask the candidate for any extra fee without Jobsence’s knowledge; all fees and payments will be agreed in writing.'],
            ['दोनों पक्ष सम्मानजनक और सुरक्षित व्यवहार करेंगे; कोई भी शिकायत gm@jobsence.com पर भेजी जा सकती है।', 'Both parties will behave respectfully and safely; any complaint can be sent to gm@jobsence.com.'],
            ['अगर उम्मीदवार इस समझौते के बाद प्रदाता को छोड़ता है, तो उसका रजिस्ट्रेशन बंद होगा और नए फॉर्म के लिए फिर से शुल्क देना होगा। प्रदाता द्वारा समझौता ख़त्म करने पर उम्मीदवार का रजिस्ट्रेशन खुला रहेगा।', 'If the candidate rejects the provider after this agreement, the candidate’s registration closes and a new form fee is payable. If the provider ends the agreement, the candidate’s registration stays open.'],
            ['सभी फ़ीस वापसी योग्य नहीं हैं। यह भारत सरकार की योजना नहीं है।', 'All fees are non-refundable. This is not a Government of India scheme.'],
            FormRegistry::platformDisclaimer(),
            ['उम्मीदवार और प्रदाता अपने ईमेल पर भेजे गए OTP से साइन करते हैं; Jobsence दोनों के साइन होते ही अपने आप साइन करता है। साइन का समय और IP रिकॉर्ड किया जाता है।', 'The candidate and the provider sign with an OTP sent to their email; Jobsence countersigns automatically when both have signed. Signing time and IP are recorded.'],
        ];
    }

    // ------------------------------------------------------------------
    // Schema
    // ------------------------------------------------------------------

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        MentorMatching::ensureSchema();
        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return;
        }
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS mentoring_views (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                provider_reg_id BIGINT UNSIGNED NOT NULL,
                plan_reg_id BIGINT UNSIGNED NOT NULL,
                seeker_reg_id BIGINT UNSIGNED NOT NULL,
                viewed_on DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_mview_pair (provider_reg_id, seeker_reg_id),
                KEY idx_mview_plan_day (plan_reg_id, viewed_on)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS mentoring_invites (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                provider_reg_id BIGINT UNSIGNED NOT NULL,
                seeker_reg_id BIGINT UNSIGNED NOT NULL,
                emailed TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_minvite_pair (provider_reg_id, seeker_reg_id),
                KEY idx_minvite_seeker (seeker_reg_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        try {
            $db = Database::getInstance();
            $cols = array_column($db->fetchAll(
                "SELECT COLUMN_NAME AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentor_assignments'"
            ), 'c');
            $add = [
                'kind' => "ENUM('skill','internship','job','nearme') NOT NULL DEFAULT 'skill' AFTER mentor_reg_id",
                'candidate_token' => 'CHAR(40) NULL AFTER token',
                'initiated_by' => "ENUM('seeker','provider','jobsence') NOT NULL DEFAULT 'jobsence'",
                'candidate_signed_at' => 'DATETIME NULL',
                'candidate_sign_ip' => 'VARCHAR(45) NULL',
                'mentor_signed_at' => 'DATETIME NULL',
                'mentor_sign_ip' => 'VARCHAR(45) NULL',
                'jobsence_signed_at' => 'DATETIME NULL',
                'agreement_version' => 'VARCHAR(20) NULL',
                'ended_at' => 'DATETIME NULL',
                'ended_by' => "ENUM('seeker','provider','jobsence') NULL",
                'end_reason' => 'VARCHAR(500) NULL',
            ];
            foreach ($add as $col => $def) {
                if (!in_array($col, $cols, true)) {
                    $pdo->exec("ALTER TABLE mentor_assignments ADD COLUMN {$col} {$def}");
                }
            }
            $statusType = (string)($db->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentor_assignments' AND COLUMN_NAME = 'source'"
            )['t'] ?? '');
            $kindType = (string)($db->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentor_assignments' AND COLUMN_NAME = 'kind'"
            )['t'] ?? '');
            if ($kindType !== '' && !str_contains($kindType, 'nearme')) {
                $pdo->exec("ALTER TABLE mentor_assignments MODIFY kind ENUM('skill','internship','job','nearme') NOT NULL DEFAULT 'skill'");
            }
            if (!str_contains($statusType, 'mentor_choice')) $pdo->exec("ALTER TABLE mentor_assignments
                MODIFY status ENUM('pending','accepted','declined','expired','cancelled','ended') NOT NULL DEFAULT 'pending',
                MODIFY mode ENUM('online','offline_ncr','na') NOT NULL DEFAULT 'online',
                MODIFY source ENUM('auto','admin','candidate_choice','mentor_choice') NOT NULL DEFAULT 'auto'");
            if (!$db->fetchOne("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentor_assignments' AND INDEX_NAME = 'uq_mentor_assign_ctoken'")) {
                $pdo->exec('ALTER TABLE mentor_assignments ADD UNIQUE KEY uq_mentor_assign_ctoken (candidate_token)');
            }
        } catch (\Throwable $e) {
            error_log('Mentoring::ensureSchema: ' . $e->getMessage());
        }
        $done = true;
    }
}
