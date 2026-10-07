<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Lang;
use App\Models\PortalRegistration;
use App\Services\Registration\CategoryCatalog;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\ProviderIdentity;
use App\Services\Registration\RegistrationPayments;
use App\Services\Registration\RegistrationValidator;
use App\Services\SeoService;
use App\Services\VerificationService;

/**
 * Jobsence registration forms (free for job seekers; employers / providers pay for plans):
 *   /apply                       hub with all buttons
 *   /apply/{form}                skill-development | internship | full-time-job | part-time-job | work-from-home | skill-provider
 *   /apply/pay/{token}           Razorpay checkout
 *   /apply/status/{token}        receipt
 */
class ApplyController extends BaseController
{
    private const OTP_PURPOSE = 'portal_provider';
    private const OTP_SESSION_TTL = 1800; // verified email stays valid for submission for 30 min

    // ------------------------------------------------------------------
    // Pages
    // ------------------------------------------------------------------

    public function hub(Request $request, Response $response): void
    {
        $this->seo(
            'Jobsence – भारत का Job Portal | Skill Development, Internship, Full-time, Part-time & Work from Home Jobs in India',
            'Apply on Jobsence – Skill Development, Internship, Full-time, Part-time and Work from Home registration. 3000+ categories, Hindi + English forms, free for job seekers. भारत को कुशल बनाने की Jobsence पहल.',
            '/apply'
        );
        $response->view('front/apply/hub', ['forms' => FormRegistry::all()] + $this->shared(), 200, 'layout');
    }

    public function form(Request $request, Response $response): void
    {
        $slug = (string)$request->param('form');
        $form = FormRegistry::get($slug);
        if (!$form) {
            $response->redirect('/apply');
            return;
        }

        $this->seo(
            $form['title'][1] . ' – Jobsence | ' . $form['title'][0] . ' | Fee ' . FormRegistry::feeLabel($form),
            $form['intro'][1] . ' Fill the form in Hindi or English. One-time processing fee ' . FormRegistry::feeLabel($form) . ' including GST (non-refundable).',
            '/apply/' . $slug,
            true,
            match ($form['type']) {
                'internship' => SkillDevelopmentController::KEYWORDS_INTERNSHIP,
                'fulltime', 'parttime', 'wfh' => SkillDevelopmentController::KEYWORDS_JOBS,
                default => SkillDevelopmentController::KEYWORDS,
            }
        );
        if (!$this->loginGate($response, $slug, $form)) {
            return;
        }
        if (!empty($form['internal'])) {
            // Provider plans are bought from the mentoring dashboard.
            $response->redirect('/mentoring');
            return;
        }
        // ?cat=Electrician (from the category catalogue) pre-selects that category.
        $cat = trim((string)$request->get('cat', ''));
        $prefill = $this->prefill($form) + ($cat !== '' && mb_strlen($cat) <= 80 ? ['categories' => [$cat]] : []);
        // Jobs abroad: ?country=Japan pre-fills the country and pays for it right after the free registration.
        if ($form['type'] === 'intljob') {
            $country = trim((string)$request->get('country', ''));
            if (preg_match('/^[\p{L} .&\'()-]{2,60}$/u', $country)) {
                $prefill['preferred_countries'] = $country;
                $_SESSION['intl_unlock_country'] = $country;
            }
        }
        $this->renderForm($response, $slug, $form, $prefill, []);
    }

    public function submit(Request $request, Response $response): void
    {
        $slug = (string)$request->param('form');
        $form = FormRegistry::get($slug);
        if (!$form) {
            $response->redirect('/apply');
            return;
        }

        if (!$this->loginGate($response, $slug, $form)) {
            return;
        }
        if (!empty($form['internal'])) {
            $response->redirect('/mentoring');
            return;
        }

        $in = (array)($request->post() ?? []);
        if (!empty($in['_hp_website'])) {
            $response->redirect('/apply');
            return;
        }

        $errors = [];
        $expected = $_SESSION['apply_captcha'][$slug] ?? null;
        unset($_SESSION['apply_captcha'][$slug]);
        if ($expected === null || trim((string)($in['captcha'] ?? '')) !== (string)$expected) {
            $errors['captcha'] = ['सुरक्षा प्रश्न का सही उत्तर दें', 'Enter the correct answer to the security question'];
        }

        $files = ['resume' => $request->file('resume'), 'video' => $request->file('video'), 'photo' => $request->file('photo'), 'selfie' => $request->file('selfie'),
            'address_proof' => $request->file('address_proof')];
        [$cols, $details, $fieldErrors] = RegistrationValidator::validate($form, $in, $files);
        $errors += $fieldErrors;

        if ($form['type'] === 'nearpro' && !empty($details['profession'])) {
            $main = $details['profession'] === 'other'
                ? trim((string)($details['profession_other'] ?? ''))
                : (FormRegistry::NEAR_ME_PROFESSIONS[$details['profession']][1] ?? '');
            if ($details['profession'] === 'other' && $main === '' && !isset($errors['profession_other'])) {
                $errors['profession_other'] = ['अपना प्रोफ़ेशन लिखें', 'Write your profession'];
            }
            $list = array_filter(array_map('trim', explode(',', (string)($cols['categories'] ?? ''))));
            $cols['categories'] = implode(', ', array_slice(array_values(array_unique(array_merge($main !== '' ? [$main] : [], $list))), 0, 6));
        }

        if (!empty($form['login_required'])) {
            // One pass per account: the email must be the logged-in account's email.
            $account = strtolower(trim((string)($this->currentUser->email ?? '')));
            if (!isset($errors['email']) && strtolower((string)($cols['email'] ?? '')) !== $account) {
                $errors['email'] = ['वही ईमेल भरें जिससे आपने लॉगिन किया है (' . $account . ')', 'Use the email you are logged in with (' . $account . ')'];
            }
            if (!isset($errors['email']) && ($regNo = PortalRegistration::paidExistsByEmail($form['type'], (string)$cols['email']))) {
                $errors['email'] = ["इस ईमेल पर पहले से एक सक्रिय पास है ({$regNo})", "This email already has an active pass ({$regNo})"];
            }
            $details['user_id'] = (int)$this->currentUser->id;
        }

        if (!empty($form['otp'])) {
            if (($form['side'] ?? '') === 'provider') {
                foreach (ProviderIdentity::conflicts($cols['email'] ?? null, $cols['mobile'] ?? null, $cols['gstin'] ?? null) as $field => $msg) {
                    $errors[$field] ??= $msg;
                }
            } elseif (!isset($errors['mobile']) && !empty($cols['mobile']) && ($regNo = PortalRegistration::paidExists($form['type'], $cols['mobile']))) {
                $errors['mobile'] = ["इस मोबाइल नंबर पर पहले से सक्रिय रजिस्ट्रेशन है ({$regNo})", "This mobile number already has an active registration ({$regNo})"];
            }
            if (!isset($errors['email']) && !$this->emailVerified((string)($cols['email'] ?? ''))) {
                $errors['email'] = ['आगे बढ़ने से पहले ईमेल OTP सत्यापित करें', 'Please verify your email with OTP before continuing'];
            }
        } elseif (!isset($errors['mobile']) && !empty($cols['mobile']) && ($regNo = PortalRegistration::paidExists($form['type'], $cols['mobile']))) {
            $errors['mobile'] = ["इस मोबाइल नंबर से पहले ही रजिस्ट्रेशन हो चुका है ({$regNo})", "This mobile number is already registered ({$regNo})"];
        }

        if ($errors) {
            $this->seo($form['title'][1] . ' – Jobsence', '', '/apply/' . $slug, false);
            $this->renderForm($response, $slug, $form, $in, $errors, 422);
            return;
        }

        foreach (['resume' => 'resume_path', 'video' => 'video_path'] as $field => $column) {
            $file = $files[$field] ?? null;
            if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $path = $this->storeUpload($file, $form['type'], $field);
                if ($path === null) {
                    $this->seo($form['title'][1] . ' – Jobsence', '', '/apply/' . $slug, false);
                    $this->renderForm($response, $slug, $form, $in, [$field => ['फ़ाइल सेव नहीं हो सकी, दोबारा प्रयास करें', 'File could not be saved, please retry']], 500);
                    return;
                }
                $cols[$column] = $path;
                // Hospitals / hirers match keywords inside resumes.
                if ($field === 'resume' && !empty($form['resume_search'])) {
                    $details['resume_text'] = \App\Services\Registration\ResumeText::extract(dirname(__DIR__, 3) . '/' . $path);
                }
            }
            unset($cols[$field]);
        }
        // Photo / live selfie (Near Me providers, restaurant jobs) and address proof (senior citizens) are kept in details.
        foreach (['photo', 'selfie', 'address_proof'] as $field) {
            $file = $files[$field] ?? null;
            if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $path = $this->storeUpload($file, $form['type'], $field);
                if ($path === null) {
                    $this->seo($form['title'][1] . ' – Jobsence', '', '/apply/' . $slug, false);
                    $this->renderForm($response, $slug, $form, $in, [$field => ['फोटो सेव नहीं हो सकी, दोबारा प्रयास करें', 'Photo could not be saved, please retry']], 500);
                    return;
                }
                $details[$field . '_path'] = $path;
            }
        }

        if (!empty($form['otp'])) {
            $cols['email_verified_at'] = date('Y-m-d H:i:s');
        }

        $reg = PortalRegistration::create($cols + [
            'type' => $form['type'],
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'declaration_accepted' => 1,
            'ui_language' => Lang::mode(),
            'ip_address' => substr($request->ip(), 0, 45),
            'user_agent' => substr($request->userAgent(), 0, 255),
        ], $form['prefix'], FormRegistry::feeOf($form, $details));

        if (!$reg) {
            $this->seo($form['title'][1] . ' – Jobsence', '', '/apply/' . $slug, false);
            $this->renderForm($response, $slug, $form, $in, ['general' => ['सर्वर त्रुटि, कृपया दोबारा प्रयास करें', 'Server error, please try again']], 500);
            return;
        }

        if (!empty($form['otp'])) {
            unset($_SESSION['portal_otp_verified'][strtolower((string)$cols['email'])]);
        }

        // Jobs abroad: free registration, then straight to the payment for the chosen country.
        if ($form['type'] === 'intljob' && self::completeIfFree($reg)) {
            $reg = PortalRegistration::find((int)$reg['id']) ?? $reg;
            \App\Services\Registration\Mentoring::identify((string)$reg['token']);
            $country = (string)($_SESSION['intl_unlock_country'] ?? trim(explode(',', (string)($details['preferred_countries'] ?? ''))[0] ?? ''));
            unset($_SESSION['intl_unlock_country']);
            $currency = ($reg['details']['residence_country'] ?? 'India') === 'India' ? 'INR' : 'USD';
            $unlock = $country !== '' ? \App\Services\Registration\Mentoring::unlockCountry($reg, $country, $currency) : ['ok' => false];
            $response->redirect($unlock['ok'] ? '/apply/pay/' . $unlock['reg']['token'] : '/apply/status/' . $reg['token']);
            return;
        }

        $response->redirect('/apply/pay/' . $reg['token']);
    }

    /** Free registrations (skill / internship providers) are complete as soon as they are submitted. */
    private static function completeIfFree(array $reg): bool
    {
        if ((float)$reg['total_amount'] > 0) {
            return false;
        }
        RegistrationPayments::completePayment($reg, 'FREE');
        return true;
    }

    public function pay(Request $request, Response $response): void
    {
        $reg = PortalRegistration::findByToken((string)$request->param('token'));
        $form = $reg ? FormRegistry::byType((string)$reg['type']) : null;
        if (!$reg || !$form) {
            $response->redirect('/apply');
            return;
        }

        $want = strtoupper((string)$request->get('currency', ''));
        if (in_array($want, ['INR', 'USD'], true) && $reg['payment_status'] !== 'paid' && PortalRegistration::switchCurrency($reg, $want)) {
            $response->redirect('/apply/pay/' . $reg['token']);
            return;
        }
        if ($reg['payment_status'] !== 'paid' && (self::completeIfFree($reg) || RegistrationPayments::reconcile($reg))) {
            $reg = PortalRegistration::find((int)$reg['id']);
        }
        if ($reg['payment_status'] === 'paid') {
            $response->redirect('/apply/status/' . $reg['token']);
            return;
        }

        $orderId = (string)($reg['razorpay_order_id'] ?? '');
        if ($orderId === '') {
            $orderId = RegistrationPayments::createOrder($reg) ?? '';
        }

        $this->seo('Pay ₹' . number_format((float)$reg['total_amount'], 0) . ' – ' . $form['title'][1] . ' – Jobsence', '', '/apply', false);
        $response->view('front/apply/pay', [
            'reg' => $reg,
            'form' => $form,
            'orderId' => $orderId,
            'razorpayKey' => RegistrationPayments::keyId(),
            'failed' => $request->get('failed') === '1',
            'fee' => (float)$reg['total_amount'],
        ] + $this->shared(), 200, 'layout');
    }

    public function verify(Request $request, Response $response): void
    {
        $reg = PortalRegistration::findByToken((string)$request->post('token', ''));
        if (!$reg) {
            $response->redirect('/apply');
            return;
        }
        if ($reg['payment_status'] === 'paid') {
            $response->redirect('/apply/status/' . $reg['token']);
            return;
        }

        $orderId = (string)$request->post('razorpay_order_id', '');
        $paymentId = (string)$request->post('razorpay_payment_id', '');
        $signature = (string)$request->post('razorpay_signature', '');

        if (!hash_equals((string)$reg['razorpay_order_id'], $orderId)
            || !RegistrationPayments::verifySignature($orderId, $paymentId, $signature)) {
            $response->redirect('/apply/pay/' . $reg['token'] . '?failed=1');
            return;
        }

        RegistrationPayments::completePayment($reg, $paymentId, $signature);
        $response->redirect('/apply/status/' . $reg['token']);
    }

    public function status(Request $request, Response $response): void
    {
        $reg = PortalRegistration::findByToken((string)$request->param('token'));
        $form = $reg ? FormRegistry::byType((string)$reg['type']) : null;
        if (!$reg || !$form) {
            $response->redirect('/apply');
            return;
        }
        if ($reg['payment_status'] !== 'paid') {
            $response->redirect('/apply/pay/' . $reg['token']);
            return;
        }

        // Pay-per-use payments go straight back to where they started (receipt is emailed).
        $back = (string)($reg['details']['return_to'] ?? '');
        if (in_array($reg['type'], ['jobpostpaid', 'jobcontact'], true) && str_starts_with($back, '/') && !str_starts_with($back, '//')) {
            $response->redirect($back);
            return;
        }

        // Contact passes (Near Me, hospital, hirer) unlock in this browser straight away.
        $passPage = \App\Services\Registration\ContactPass::remember($reg);

        // Honorary workshops: remember the learner in this browser and finish a "Join free" started before registering.
        if ($reg['type'] === 'honorlearn') {
            $_SESSION['honor_learner_token'] = $reg['token'];
            if (!empty($_SESSION['honor_join'])) {
                $_SESSION['honor_flash'] = HonoraryController::signupMessage(\App\Models\HonoraryWorkshop::signup((int)$_SESSION['honor_join'], (int)$reg['id']));
                unset($_SESSION['honor_join']);
            }
        }

        $this->seo('Registration Successful – Jobsence', '', '/apply', false);
        $response->view('front/apply/status', ['reg' => $reg, 'form' => $form, 'fee' => (float)$reg['total_amount'], 'passPage' => $passPage] + $this->shared(), 200, 'layout');
    }

    public function categories(Request $request, Response $response): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo CategoryCatalog::json();
        exit;
    }

    // ------------------------------------------------------------------
    // AJAX: duplicate check + email OTP (providers)
    // ------------------------------------------------------------------

    /** POST field=email|mobile|gstin, value=… → {available, message_hi, message_en} */
    public function checkUnique(Request $request, Response $response): void
    {
        $field = (string)$request->post('field', '');
        $value = trim((string)$request->post('value', ''));

        $conflict = match ($field) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) && ProviderIdentity::emailExists($value),
            'mobile' => ($m = RegistrationValidator::mobile($value)) !== null && ProviderIdentity::mobileExists($m),
            'gstin' => preg_match(ProviderIdentity::GST_PATTERN, ProviderIdentity::normalizeGst($value)) === 1 && ProviderIdentity::gstExists($value),
            default => null,
        };

        if ($conflict === null) {
            $response->json(['error' => 'Invalid field'], 422);
            return;
        }

        $msg = ProviderIdentity::MESSAGES[$field];
        $response->json(['available' => !$conflict, 'message_hi' => $conflict ? $msg[0] : '', 'message_en' => $conflict ? $msg[1] : '']);
    }

    public function sendOtp(Request $request, Response $response): void
    {
        $email = strtolower(trim((string)$request->post('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json(['success' => false, 'error' => 'सही ईमेल भरें / Enter a valid email'], 422);
            return;
        }
        $otpForm = FormRegistry::get((string)$request->post('form', ''));
        if (($otpForm['side'] ?? 'provider') === 'provider' && ProviderIdentity::emailExists($email)) {
            $msg = ProviderIdentity::MESSAGES['email'];
            $response->json(['success' => false, 'error' => $msg[0] . ' / ' . $msg[1]], 409);
            return;
        }

        $last = (int)($_SESSION['portal_otp_last_sent'][$email] ?? 0);
        if ($last > time() - 60) {
            $response->json(['success' => false, 'error' => '1 मिनट बाद दोबारा भेजें / Please wait 1 minute before resending'], 429);
            return;
        }

        $result = VerificationService::sendEmailAuthOTP($email, self::OTP_PURPOSE);
        if (empty($result['success'])) {
            $response->json(['success' => false, 'error' => (string)($result['error'] ?? 'OTP नहीं भेजा जा सका / Could not send OTP')], !empty($result['blocked']) ? 429 : 500);
            return;
        }

        $_SESSION['portal_otp_last_sent'][$email] = time();
        $response->json(['success' => true, 'message' => "OTP {$email} पर भेजा गया (10 मिनट के लिए मान्य) / OTP sent to {$email} (valid for 10 minutes)"]);
    }

    public function verifyOtp(Request $request, Response $response): void
    {
        $email = strtolower(trim((string)$request->post('email', '')));
        $result = VerificationService::verifyEmailAuthOTP($email, (string)$request->post('otp', ''), self::OTP_PURPOSE);
        if (empty($result['success'])) {
            $response->json(['success' => false, 'error' => (string)($result['error'] ?? 'Invalid OTP')], !empty($result['blocked']) ? 429 : 422);
            return;
        }

        $_SESSION['portal_otp_verified'][$email] = time();
        $response->json(['success' => true, 'message' => 'ईमेल सत्यापित ✓ / Email verified ✓']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** GET /apply/renew/{token} – open (or reuse) a renewal payment for the next term. */
    public function renew(Request $request, Response $response): void
    {
        $old = PortalRegistration::findByToken((string)$request->param('token'));
        $form = $old ? FormRegistry::byType((string)$old['type']) : null;
        if (!$old || !$form || $old['payment_status'] !== 'paid' || !in_array($old['type'], PortalRegistration::RENEWABLE, true)) {
            $response->redirect('/apply');
            return;
        }
        $fee = PortalRegistration::renewalFee($old, FormRegistry::feeOf($form, $old['details']));
        $renewal = PortalRegistration::openRenewal((int)$old['id']);
        if ($renewal && abs((float)$renewal['total_amount'] - $fee) > 0.009) {
            // Price changed (e.g. the term ended meanwhile → late fee): re-price and start a fresh order.
            PortalRegistration::reprice((int)$renewal['id'], $fee);
            $renewal = PortalRegistration::find((int)$renewal['id']);
        }
        $renewal ??= PortalRegistration::createRenewal($old, $form['prefix'], $fee);
        $response->redirect($renewal ? '/apply/pay/' . $renewal['token'] : '/apply');
    }

    /** Forms marked login_required (Jobs Pass) need a logged-in account first. */
    private function loginGate(Response $response, string $slug, array $form): bool
    {
        if (empty($form['login_required']) || $this->currentUser) {
            return true;
        }
        $response->redirect('/login?redirect=' . rawurlencode('/apply/' . $slug));
        return false;
    }

    /** Pre-fill name / email / mobile from the logged-in account. */
    private function prefill(array $form): array
    {
        if (empty($form['login_required']) || !$this->currentUser) {
            return [];
        }
        $u = $this->currentUser;
        return array_filter([
            'full_name' => (string)($u->name ?? ''),
            'email' => (string)($u->email ?? ''),
            'mobile' => substr(preg_replace('/\D/', '', (string)($u->phone ?? '')), -10),
        ]);
    }

    private function emailVerified(string $email): bool
    {
        $at = (int)($_SESSION['portal_otp_verified'][strtolower(trim($email))] ?? 0);
        return $at > time() - self::OTP_SESSION_TTL;
    }

    private function renderForm(Response $response, string $slug, array $form, array $old, array $errors, int $code = 200): void
    {
        $a = random_int(2, 9);
        $b = random_int(1, 9);
        $_SESSION['apply_captcha'][$slug] = $a + $b;

        $email = strtolower(trim((string)($old['email'] ?? '')));

        $response->view('front/apply/form', [
            'slug' => $slug,
            'form' => $form,
            'old' => $old,
            'errors' => $errors,
            'captcha' => [$a, $b],
            'emailVerified' => !empty($form['otp']) && $email !== '' && $this->emailVerified($email),
            'fee' => FormRegistry::feeOf($form),
        ] + $this->shared(), $code, 'layout');
    }

    private function shared(): array
    {
        return [
            'fee' => \App\Services\Registration\FormRegistry::SEEKERS_FREE ? 0.0 : PortalRegistration::FEE,
            'courseFee' => PortalRegistration::COURSE_FEE,
            'states' => SkillDevelopmentController::STATES,
            'whatsappNumber' => preg_replace('/\D/', '', (string)($_ENV['SKILL_WHATSAPP_NUMBER'] ?? $_ENV['WHATSAPP_NUMBER'] ?? '')),
        ];
    }

    private function storeUpload(array $file, string $type, string $kind): ?string
    {
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $relative = 'storage/uploads/registrations/' . $type . '/' . date('Y-m') . '/' . $kind . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
        $absolute = dirname(__DIR__, 3) . '/' . $relative;

        if (!is_dir(dirname($absolute)) && !@mkdir(dirname($absolute), 0775, true)) {
            return null;
        }
        return move_uploaded_file((string)$file['tmp_name'], $absolute) ? $relative : null;
    }

    private function seo(string $title, string $description, string $path, bool $index = true, ?string $keywords = null): void
    {
        $meta = [
            'title' => $title,
            'h1' => $title,
            'canonical' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . $path,
            'robots' => $index ? 'index, follow' : 'noindex, nofollow',
            'keywords' => $keywords ?? (SkillDevelopmentController::KEYWORDS . ', ' . SkillDevelopmentController::KEYWORDS_JOBS),
        ];
        if ($description !== '') {
            $meta['description'] = $description;
        }
        SeoService::getInstance()->setMeta($meta);
    }
}
