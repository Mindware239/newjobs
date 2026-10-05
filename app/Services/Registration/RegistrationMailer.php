<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Services\MailService;

/**
 * Bilingual (Hindi + English) emails for Jobsence registrations.
 * Candidate mail from gm@jobsence.com (SKILL_MAIL_FROM); owner copy to gm@indianbarcode.com (OWNER_MAIL).
 * The SMTP account must be allowed to send as the From address.
 */
class RegistrationMailer
{
    public const TAGLINE = 'भारत को कुशल बनाने की Jobsence पहल';
    private const HASHTAGS = '#SkillDevelopmentInIndia #Skills #Jobs #JobsInIndia #BestJobPortal #Internship #Jobsence';

    public static function sendThankYou(array $reg): bool
    {
        $form = FormRegistry::byType((string)$reg['type']);
        if (empty($reg['email']) || !$form) {
            return false;
        }

        $h = self::escaper();
        $fee = number_format((float)$reg['total_amount'], 0);
        $money = \App\Models\PortalRegistration::money($reg);
        $usd = ($reg['currency'] ?? 'INR') === 'USD';
        $free = (float)$reg['total_amount'] <= 0;
        $steps = '';
        foreach ($form['next_steps'] as [$hi, $en]) {
            $steps .= "<li>{$h($hi)}<br><span style='color:#4b5563'>{$h($en)}</span></li>";
        }

        $body = self::layout("
            <p>प्रिय {$h($reg['full_name'])},<br>नमस्ते!</p>
            <p>Jobsence पर <b>{$h($form['title'][0])}</b> के लिए धन्यवाद। " . ($free ? 'आपका मुफ़्त रजिस्ट्रेशन पूरा हो गया है।' : "आपका {$money}" . ($usd ? '' : ' (GST सहित)') . " का एकमुश्त प्रोसेसिंग शुल्क सफलतापूर्वक प्राप्त हो गया है।") . "</p>
            <p>Dear {$h($reg['full_name'])},<br>Thank you for your <b>{$h($form['title'][1])}</b> with Jobsence. " . ($free ? 'Your free registration is complete.' : "Your one-time processing fee of <b>{$money}</b>" . ($usd ? '' : ' (including GST)') . " has been successfully received.") . "</p>
            <h3 style='margin:20px 0 8px;font-size:16px'>रजिस्ट्रेशन विवरण / Registration Details</h3>
            " . self::detailsTable($reg, $form) . "
            <p>आपका फॉर्म सफलतापूर्वक जमा होकर हमारे डेटाबेस में सुरक्षित है। / Your form has been successfully submitted and stored in our database.</p>
            " . (!empty($reg['valid_until']) ? "<p style='background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 12px'><b>रजिस्ट्रेशन वैधता / Registration valid till: " . $h(date('d M Y', strtotime((string)$reg['valid_until']))) . "</b><br>रजिस्ट्रेशन भुगतान की तारीख से " . (\App\Models\PortalRegistration::validityLabel((string)$reg['type'])[0] ?? '3 महीने') . " तक मान्य है। / Your registration is valid for " . (\App\Models\PortalRegistration::validityLabel((string)$reg['type'])[1] ?? '3 months') . " from the date of payment.</p>" : '') . "
            <h3 style='margin:20px 0 8px;font-size:16px'>आगे क्या होगा? / What happens next?</h3>
            <ul style='padding-left:18px;margin:0 0 12px'>{$steps}</ul>
            <h3 style='margin:20px 0 8px;font-size:16px'>ज़रूरी बातें / Important Points</h3>
            <ul style='padding-left:18px;margin:0 0 12px'>
                <li>यह " . self::TAGLINE . " है – भारत सरकार की योजना नहीं। / This is Jobsence’s initiative – NOT a Government of India scheme.</li>
                " . ($free ? '' : "<li>{$money} शुल्क वापसी योग्य नहीं है। / The {$money} fee is non-refundable.</li>") . "
                <li>Jobsence किसी नौकरी, इंटर्नशिप या प्लेसमेंट की गारंटी नहीं देता। / Jobsence does not guarantee any job, internship or placement.</li>
            </ul>
            <p>कोई सवाल हो तो इस ईमेल का जवाब दें। / If you have any questions, you can reply to this email.</p>
            " . (($pass = ContactPass::accessUrl($reg)) !== null
                ? "<p style='margin-top:16px'><a href='" . $h(rtrim($_ENV['APP_URL'] ?? '', '/') . $pass) . "' style='display:inline-block;background:#f05537;color:#fff;padding:10px 16px;border-radius:8px;font-weight:bold;text-decoration:none'>अभी खोलें – नंबर और पता देखें / Open now – see numbers &amp; addresses</a><br><small>यह लिंक केवल आपके लिए है – किसी से साझा न करें। / This link is only for you – do not share it.</small></p>"
                : '') . "
            " . (in_array($reg['type'], ['skill', 'internship', 'provider', 'internpro', 'mentorplan', 'internplan', 'fulltime', 'parttime', 'wfh', 'jobpro', 'jobplan', 'nearpro', 'nearseek'], true)
                ? "<p style='margin-top:16px'><a href='" . $h(rtrim($_ENV['APP_URL'] ?? '', '/') . '/mentoring/access/' . $reg['token']) . "' style='display:inline-block;background:#138808;color:#fff;padding:10px 16px;border-radius:8px;font-weight:bold;text-decoration:none'>मेरा मेंटरिंग डैशबोर्ड / My mentoring dashboard</a><br><small>यह लिंक केवल आपके लिए है – किसी से साझा न करें। / This link is only for you – do not share it.</small></p>"
                : '') . "
            <p style='margin-top:16px'><a href='" . $h(self::statusUrl($reg)) . "' style='color:#f05537;font-weight:bold'>रसीद देखें / View your receipt</a></p>
        ");

        return self::send((string)$reg['email'], 'Thank You – Your Jobsence ' . $form['title'][1] . ' is Received | ' . ($free ? 'Registered' : 'Payment Confirmed') . ' (' . $reg['reg_no'] . ')', $body);
    }

    /** "Your registration ends in N days – renew" (sent 10, 5 and 1 day before expiry). */
    public static function sendExpiryReminder(array $reg, int $daysLeft): bool
    {
        $form = FormRegistry::byType((string)$reg['type']);
        if (empty($reg['email']) || !$form) {
            return false;
        }
        $h = self::escaper();
        $fee = number_format(FormRegistry::feeOf($form), 0);
        $renew = $h(rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/apply/renew/' . $reg['token']);
        $till = $h(date('d M Y', strtotime((string)$reg['valid_until'])));
        $hidden = $reg['type'] === 'nearpro'
            ? '<p>समय ख़त्म होते ही आपका नंबर और प्रोफ़ाइल ग्राहकों से अपने आप छिप जाएगा। दोबारा भुगतान करते ही यह फिर से दिखने लगेगा, और आपकी स्टार रेटिंग बनी रहेगी।<br>When it ends, your number and profile are hidden from customers automatically. As soon as you pay again it shows up again – and your star ratings stay with you.</p>'
            : '';

        $body = self::layout("
            <p>प्रिय {$h($reg['full_name'])},<br>आपका <b>{$h($form['title'][0])}</b> ({$h($reg['reg_no'])}) <b>{$daysLeft} दिन</b> में ({$till}) समाप्त हो रहा है।</p>
            <p>Dear {$h($reg['full_name'])},<br>Your <b>{$h($form['title'][1])}</b> ({$h($reg['reg_no'])}) ends in <b>{$daysLeft} day(s)</b> on {$till}.</p>
            {$hidden}
            <p style='text-align:center;margin:22px 0'>
                <a href='{$renew}' style='background:#f05537;color:#fff;padding:14px 24px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block'>₹{$fee} देकर रिन्यू करें / Renew for ₹{$fee}</a>
            </p>
            <p style='font-size:12px;color:#6b7280;word-break:break-all'>{$renew}</p>
            <p>समय से पहले रिन्यू करने पर नई अवधि पुरानी अवधि के ख़त्म होने के बाद से शुरू होगी। / If you renew early, the new term starts after the current one ends.</p>
            <p style='background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 12px'><b>{$till} तक रिन्यू न करने पर 10% अतिरिक्त शुल्क लगेगा। / If you do not renew by {$till}, a 10% late fee is added.</b></p>
        ");
        return self::send((string)$reg['email'], "रिन्यू करें – {$daysLeft} दिन बाकी / Renew – {$daysLeft} day(s) left – {$form['title'][1]} – {$reg['reg_no']}", $body);
    }

    public static function sendReminder(array $reg): bool
    {
        $form = FormRegistry::byType((string)$reg['type']);
        if (empty($reg['email']) || !$form) {
            return false;
        }

        $h = self::escaper();
        $fee = number_format((float)$reg['total_amount'], 0);
        $money = \App\Models\PortalRegistration::money($reg);
        $usd = ($reg['currency'] ?? 'INR') === 'USD';
        $payUrl = $h(self::payUrl($reg));

        $body = self::layout("
            <p>प्रिय {$h($reg['full_name'])},<br>नमस्ते!</p>
            <p>आपने Jobsence का <b>{$h($form['title'][0])}</b> फॉर्म भरा है, लेकिन {$money} का भुगतान अभी बाकी है।</p>
            <p>Dear {$h($reg['full_name'])},<br>You have filled the <b>{$h($form['title'][1])}</b> form on Jobsence, but the payment of <b>{$money}</b> is still pending.</p>
            <p>रजिस्ट्रेशन पूरा करने के लिए नीचे दिए लिंक से {$money} (GST सहित, वापसी योग्य नहीं) का भुगतान करें।<br>
               To complete your registration, please pay the one-time non-refundable fee of {$money}" . ($usd ? '' : ' (including GST)') . " using the link below:</p>
            <p style='text-align:center;margin:22px 0'>
                <a href='{$payUrl}' style='background:#f05537;color:#fff;padding:14px 24px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block'>{$money} भुगतान करें / Pay {$money} Now</a>
            </p>
            <p style='font-size:12px;color:#6b7280;word-break:break-all'>{$payUrl}</p>
            <h3 style='margin:20px 0 8px;font-size:16px'>Jobsence क्यों? / Why Jobsence?</h3>
            <ul style='padding-left:18px;margin:0 0 12px'>
                <li>कौशल विकास → इंटर्नशिप → नौकरी: एक ही जगह / Skill Development → Internship → Job: all in one place</li>
                <li>3000+ कैटेगरी, पूरे भारत में / 3000+ categories across India</li>
                <li>हर युवा के लिए – गाँव से शहर तक / For every youth – from village to city</li>
            </ul>
            <h3 style='margin:20px 0 8px;font-size:16px'>ज़रूरी / Important</h3>
            <ul style='padding-left:18px;margin:0 0 12px'>
                <li>{$money} शुल्क वापसी योग्य नहीं है। / The {$money} fee is non-refundable.</li>
                <li>यह " . self::TAGLINE . " है (सरकारी योजना नहीं)। / This is Jobsence’s initiative (not a Government scheme).</li>
                <li>नौकरी, इंटर्नशिप या प्लेसमेंट की कोई गारंटी नहीं। / No job, internship or placement guarantee.</li>
            </ul>
            <p style='color:#6b7280;font-size:13px'>अगर आप भुगतान कर चुके हैं, तो इस ईमेल को अनदेखा करें। हम भुगतान होने तक हर 2 दिन में एक याद दिलाएँगे (अधिकतम 5 बार)।<br>
               If you have already paid, please ignore this email. We send a gentle reminder every 2 days until payment (maximum 5).</p>
        ");

        return self::send((string)$reg['email'], 'Reminder – Complete Your Jobsence ' . $form['title'][1] . ' (' . $money . ')', $body);
    }

    public static function notifyOwner(array $reg): bool
    {
        $form = FormRegistry::byType((string)$reg['type']);
        $owner = (string)($_ENV['OWNER_MAIL'] ?? 'gm@indianbarcode.com');
        if ($owner === '' || !$form) {
            return false;
        }

        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        $body = self::layout(
            '<p><b>Payment received – ' . htmlspecialchars($form['title'][1]) . '</b></p>'
            . self::detailsTable($reg, $form)
            . "<p><a href='{$base}/admin/registrations/" . (int)$reg['id'] . "' style='color:#f05537;font-weight:bold'>Open in admin</a></p>"
        );

        return self::send($owner, 'Payment Received ₹' . number_format((float)$reg['total_amount'], 0) . ' – ' . $form['title'][1] . ' – ' . $reg['reg_no'], $body);
    }

    public static function payUrl(array $reg): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/apply/pay/' . $reg['token'];
    }

    public static function statusUrl(array $reg): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/apply/status/' . $reg['token'];
    }

    private static function detailsTable(array $reg, array $form): string
    {
        $h = self::escaper();
        $fields = FormRegistry::fields($form);
        $label = static fn(string $key, array $fallback) => isset($fields[$key]['label']) ? $fields[$key]['label'][0] . ' / ' . $fields[$key]['label'][1] : implode(' / ', $fallback);

        $rows = [
            'रजि. नं. / Reg. No.' => $reg['reg_no'] ?? '',
            'फॉर्म / Form' => $form['title'][0] . ' / ' . $form['title'][1],
            'नाम / Name' => $reg['full_name'] ?? '',
            'मोबाइल / Mobile' => $reg['mobile'] ?? '',
            'ईमेल / Email' => $reg['email'] ?? '',
            ($form['categories_label'][0] . ' / ' . $form['categories_label'][1]) => $reg['categories'] ?? '',
            'स्थान / Location' => trim(implode(', ', array_filter([$reg['city'] ?? '', $reg['district'] ?? '', $reg['state'] ?? ''])) . ' – ' . ($reg['pincode'] ?? ''), ' –'),
        ];
        foreach (['training_mode', 'work_mode', 'duration'] as $key) {
            $v = $reg['details'][$key] ?? null;
            if ($v !== null && isset($fields[$key]['options'][$v])) {
                $rows[$label($key, [$key])] = implode(' / ', $fields[$key]['options'][$v]);
            }
        }
        if (($reg['payment_status'] ?? '') === 'paid') {
            $rows['भुगतान / Amount Paid'] = '₹' . number_format((float)$reg['total_amount'], 0) . ' (GST सहित / incl. GST)';
            $rows['पेमेंट आईडी / Payment ID'] = $reg['razorpay_payment_id'] ?? '';
        }

        $html = "<table cellpadding='8' style='border-collapse:collapse;font-size:14px;width:100%;border:1px solid #e5e7eb'>";
        foreach ($rows as $k => $v) {
            $html .= "<tr><td style='border-bottom:1px solid #e5e7eb;background:#fafafa;width:42%'><b>{$h($k)}</b></td><td style='border-bottom:1px solid #e5e7eb'>{$h($v)}</td></tr>";
        }
        return $html . '</table>';
    }

    private static function layout(string $inner): string
    {
        $from = htmlspecialchars(self::fromAddress());
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
        <body style='margin:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.6'>
          <div style='max-width:620px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb'>
            <div style='background:#f05537;color:#fff;padding:20px 24px'>
              <div style='font-size:20px;font-weight:bold'>Jobsence – भारत का Job Portal</div>
              <div style='font-size:14px;opacity:.95'>" . self::TAGLINE . "</div>
            </div>
            <div style='padding:24px;font-size:15px'>{$inner}</div>
            <div style='padding:18px 24px;background:#fafafa;border-top:1px solid #e5e7eb;font-size:13px;color:#4b5563'>
              सादर / Best regards,<br>
              <b>Team Jobsence</b><br>
              <a href='mailto:{$from}' style='color:#f05537'>{$from}</a><br>
              " . self::TAGLINE . "<br>
              <span style='color:#9ca3af'>" . self::HASHTAGS . "</span>
            </div>
          </div>
        </body></html>";
    }

    private static function send(string $to, string $subject, string $body): bool
    {
        try {
            return MailService::sendEmail($to, $subject, $body, self::fromAddress(), 'Team Jobsence');
        } catch (\Throwable $e) {
            error_log('RegistrationMailer error: ' . $e->getMessage());
            return false;
        }
    }

    private static function fromAddress(): string
    {
        return (string)($_ENV['SKILL_MAIL_FROM'] ?? 'gm@jobsence.com');
    }

    private static function escaper(): \Closure
    {
        return static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
