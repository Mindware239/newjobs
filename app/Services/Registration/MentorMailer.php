<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Services\MailService;

/** Bilingual emails for mentor ↔ candidate matching (from gm@jobsence.com). */
class MentorMailer
{
    public static function base(): string
    {
        return rtrim((string)($_ENV['APP_URL'] ?? 'http://localhost:8000'), '/');
    }

    public static function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    private static function modeLabel(string $mode): string
    {
        return $mode === 'offline_ncr' ? 'ऑफलाइन – दिल्ली-NCR / Offline – Delhi-NCR' : 'ऑनलाइन / वीडियो क्लास / Online / Video classes';
    }

    public static function button(string $url, string $label, string $color = '#f05537'): string
    {
        return "<a href='" . self::h($url) . "' style='background:{$color};color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;margin:4px'>{$label}</a>";
    }

    public static function layout(string $inner): string
    {
        $from = self::h($_ENV['SKILL_MAIL_FROM'] ?? 'gm@jobsence.com');
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.6'>
            <div style='max-width:620px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb'>
              <div style='background:#f05537;color:#fff;padding:18px 24px'><div style='font-size:20px;font-weight:bold'>Jobsence – भारत का Job Portal</div><div style='font-size:14px'>भारत को कुशल बनाने की Jobsence पहल</div></div>
              <div style='padding:24px;font-size:15px'>{$inner}</div>
              <div style='padding:16px 24px;background:#fafafa;border-top:1px solid #e5e7eb;font-size:13px;color:#4b5563'>सादर / Best regards,<br><b>Team Jobsence</b><br><a href='mailto:{$from}' style='color:#f05537'>{$from}</a></div>
            </div></body></html>";
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        if ($to === '') {
            return false;
        }
        try {
            return MailService::sendEmail($to, $subject, $body, (string)($_ENV['SKILL_MAIL_FROM'] ?? 'gm@jobsence.com'), 'Team Jobsence');
        } catch (\Throwable $e) {
            error_log('MentorMailer: ' . $e->getMessage());
            return false;
        }
    }

    public static function requestToMentor(array $mentor, array $candidate, string $skill, string $mode, string $token): bool
    {
        $url = self::base() . '/mentor/respond/' . $token;
        $cd = is_array($candidate['details'] ?? null) ? $candidate['details'] : (json_decode((string)($candidate['details'] ?? ''), true) ?: []);
        $langs = implode(', ', (array)($cd['languages'] ?? []));
        $place = trim(($candidate['district'] ?? '') . ', ' . ($candidate['state'] ?? ''), ', ');
        $days = MentorMatching::RESPONSE_DAYS;

        $body = self::layout("
            <p>नमस्ते " . self::h($mentor['full_name']) . ",<br>Hello " . self::h($mentor['full_name']) . ",</p>
            <p>एक प्रशिक्षार्थी को आपकी ज़रूरत है। कृपया बताएँ कि क्या आप उन्हें सिखा सकते हैं।<br>A candidate needs a mentor. Please tell us whether you can teach them.</p>
            <table cellpadding='8' style='border-collapse:collapse;width:100%;border:1px solid #e5e7eb;font-size:14px'>
              <tr><td style='background:#fafafa;width:40%'><b>स्किल / Skill</b></td><td>" . self::h($skill) . "</td></tr>
              <tr><td style='background:#fafafa'><b>तरीका / Mode</b></td><td>" . self::modeLabel($mode) . "</td></tr>
              <tr><td style='background:#fafafa'><b>प्रशिक्षार्थी / Candidate</b></td><td>" . self::h($candidate['full_name']) . " (" . self::h($candidate['reg_no']) . ")</td></tr>
              <tr><td style='background:#fafafa'><b>स्थान / Location</b></td><td>" . self::h($place) . "</td></tr>
              <tr><td style='background:#fafafa'><b>भाषा / Language</b></td><td>" . self::h($langs ?: '—') . "</td></tr>
            </table>
            <p style='text-align:center;margin:22px 0'>" . self::button($url . '?a=accept', '✔ स्वीकार करें / Accept', '#16a34a') . self::button($url . '?a=decline', '✖ मना करें / Decline', '#6b7280') . "</p>
            <p style='font-size:13px;color:#6b7280'>कृपया {$days} दिन के भीतर जवाब दें, नहीं तो प्रशिक्षार्थी को दूसरा मेंटर चुनने का विकल्प दिया जाएगा।<br>
            Please reply within {$days} days, otherwise the candidate will be offered other mentors. The emolument is paid per candidate after successful training.</p>");

        return self::send((string)($mentor['email'] ?? ''), 'Mentor request: ' . $skill . ' – ' . $candidate['reg_no'] . ' | Jobsence', $body);
    }

    public static function acceptedToCandidate(array $candidate, array $mentor, array $assignment): bool
    {
        $md = is_array($mentor['details'] ?? null) ? $mentor['details'] : [];
        $langs = implode(', ', (array)($md['languages'] ?? []));
        $body = self::layout("
            <p>प्रिय " . self::h($candidate['full_name']) . ",<br>Dear " . self::h($candidate['full_name']) . ",</p>
            <p><b>बधाई हो! आपका मेंटर तय हो गया है।</b><br><b>Good news – a mentor has accepted you!</b></p>
            <table cellpadding='8' style='border-collapse:collapse;width:100%;border:1px solid #e5e7eb;font-size:14px'>
              <tr><td style='background:#fafafa;width:40%'><b>मेंटर / Mentor</b></td><td>" . self::h($mentor['full_name']) . "</td></tr>
              <tr><td style='background:#fafafa'><b>स्किल / Skill</b></td><td>" . self::h($assignment['skill']) . "</td></tr>
              <tr><td style='background:#fafafa'><b>तरीका / Mode</b></td><td>" . self::modeLabel((string)$assignment['mode']) . "</td></tr>
              <tr><td style='background:#fafafa'><b>अनुभव / Experience</b></td><td>" . self::h((string)($md['years_experience'] ?? '—')) . " years</td></tr>
              <tr><td style='background:#fafafa'><b>भाषा / Languages</b></td><td>" . self::h($langs ?: '—') . "</td></tr>
              <tr><td style='background:#fafafa'><b>संपर्क / Contact</b></td><td>" . self::h($mentor['mobile']) . " · " . self::h($mentor['email']) . "</td></tr>
            </table>
            <p>Jobsence टीम जल्द ही आपकी क्लास का समय तय करने के लिए संपर्क करेगी। क्लास रोज़ 2 घंटे, हफ्ते में 3 दिन होंगी।<br>
            The Jobsence team will contact you shortly to fix your class schedule (2 hours/day, 3 days a week).</p>");
        return self::send((string)($candidate['email'] ?? ''), 'Your mentor is confirmed – ' . $assignment['skill'] . ' | Jobsence', $body);
    }

    public static function acceptedToOwner(array $candidate, array $mentor, array $assignment): bool
    {
        $owner = (string)($_ENV['OWNER_MAIL'] ?? 'gm@indianbarcode.com');
        $body = self::layout("<p><b>Mentor accepted a candidate</b></p><p>Mentor: " . self::h($mentor['full_name']) . " (" . self::h($mentor['reg_no']) . ")<br>Candidate: "
            . self::h($candidate['full_name']) . " (" . self::h($candidate['reg_no']) . ")<br>Skill: " . self::h($assignment['skill']) . "<br>Mode: " . self::modeLabel((string)$assignment['mode'])
            . "</p><p><a href='" . self::h(self::base() . '/admin/registrations/' . (int)$candidate['id']) . "'>Open in admin</a></p>");
        return self::send($owner, 'Mentor accepted: ' . $candidate['reg_no'] . ' ↔ ' . $mentor['reg_no'], $body);
    }

    public static function alternativesToCandidate(array $candidate, int $online, int $offline, string $reason): bool
    {
        $url = self::base() . '/apply/mentors/' . $candidate['token'];
        $why = [
            'declined' => ['चुने गए मेंटर अभी उपलब्ध नहीं हैं।', 'The mentor we requested is not available right now.'],
            'expired' => ['चुने गए मेंटर ने समय पर जवाब नहीं दिया।', 'The mentor we requested did not reply in time.'],
            'no_match' => ['अभी आपके चुने हुए स्किल के लिए कोई मेंटर अपने-आप तय नहीं हो पाया।', 'We could not automatically match a mentor for your chosen skills yet.'],
        ][$reason] ?? ['', ''];

        $list = ($online + $offline) > 0
            ? "<p>आपके चुने हुए स्किल के लिए उपलब्ध मेंटर / Mentors available for your chosen skills:</p>
               <ul><li>ऑनलाइन / Online: <b>{$online}</b></li><li>ऑफलाइन (दिल्ली-NCR) / Offline (Delhi-NCR): <b>{$offline}</b></li></ul>
               <p style='text-align:center;margin:22px 0'>" . self::button($url, 'मेंटर चुनें / Choose a mentor') . "</p>"
            : "<p>अभी आपके स्किल के लिए कोई मेंटर उपलब्ध नहीं है। जैसे ही कोई मेंटर जुड़ेगा, हम आपको सूचित करेंगे।<br>No mentor is available for your skills right now – we will email you as soon as one joins.</p>
               <p style='text-align:center'>" . self::button($url, 'स्थिति देखें / View status') . "</p>";

        $body = self::layout("
            <p>प्रिय " . self::h($candidate['full_name']) . ",<br>Dear " . self::h($candidate['full_name']) . ",</p>
            <p>" . self::h($why[0]) . "<br>" . self::h($why[1]) . "</p>{$list}
            <p style='font-size:13px;color:#6b7280'>आपको केवल आपके चुने हुए (अधिकतम 5) स्किल में ही ट्रेनिंग दी जाएगी।<br>You will be trained only in the skills you chose (up to 5).</p>");
        return self::send((string)($candidate['email'] ?? ''), 'Choose your mentor – Jobsence Skill Development', $body);
    }

    /** Status emails sent when admin changes a registration's status. */
    public static function statusChanged(array $reg, string $status): bool
    {
        $msg = match ([$reg['type'], $status]) {
            ['ngo', 'selected'] => ['आपकी संस्था सत्यापित हो गई है ✓', 'Your organisation is verified ✓', 'अब आप सामाजिक सेवा के लिए लोगों से जुड़ सकते हैं।', 'You can now connect with social service people on Jobsence.'],
            ['provider', 'selected'] => ['आप Jobsence मेंटर टीम में शामिल हो गए हैं ✓', 'You have been added to the Jobsence Mentor Team ✓', 'प्रशिक्षार्थी मिलने पर आपको स्वीकार / मना करने का ईमेल मिलेगा।', 'When a candidate needs you, you will get an email to accept or decline.'],
            ['skill', 'selected'] => ['आपका चयन हो गया है ✓', 'You have been selected ✓', 'हम आपके चुने हुए स्किल के लिए मेंटर तय कर रहे हैं।', 'We are now matching you with a mentor for your chosen skills.'],
            default => null,
        };
        if (!$msg) {
            return false;
        }
        $body = self::layout("<p>प्रिय " . self::h($reg['full_name']) . ",<br>Dear " . self::h($reg['full_name']) . ",</p><p><b>" . self::h($msg[0]) . "</b><br><b>" . self::h($msg[1]) . "</b></p><p>" . self::h($msg[2]) . "<br>" . self::h($msg[3]) . "</p><p>Reg. No.: " . self::h($reg['reg_no']) . "</p>");
        return self::send((string)($reg['email'] ?? ''), $msg[1] . ' | Jobsence', $body);
    }
}
