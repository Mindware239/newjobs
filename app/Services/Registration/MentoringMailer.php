<?php

declare(strict_types=1);

namespace App\Services\Registration;

/** Emails for the tripartite mentoring / internship agreement (from gm@jobsence.com, owner copy on signing). */
class MentoringMailer
{
    private static function what(array $a): string
    {
        $w = Mentoring::KINDS[$a['kind'] ?? 'skill']['what'] ?? ['स्किल मेंटरिंग', 'Skill mentoring'];
        return $w[0] . ' / ' . strtolower($w[1]);
    }

    /** The other party has signed – please review and sign. */
    public static function pleaseSign(array $a, string $toRole, array $seeker, array $provider): bool
    {
        $h = [MentorMailer::class, 'h'];
        $to = $toRole === 'provider' ? $provider : $seeker;
        $from = $toRole === 'provider' ? $seeker['full_name'] : Mentoring::displayName(Mentoring::decode($provider));
        $url = MentorMailer::base() . Mentoring::linkFor($a, $toRole);
        $price = Mentoring::planPrice($provider);
        $planNote = $toRole === 'provider'
            ? "<p style=\"font-size:13px;color:#4b5563\">साइन करने के लिए सक्रिय {$price} प्लान ज़रूरी है। / An active {$price} plan is needed to sign.</p>" : '';
        $body = MentorMailer::layout("
            <p>प्रिय / Dear {$h($to['full_name'])},</p>
            <p><b>{$h($from)}</b> ने आपके साथ <b>{$h($a['skill'])}</b> ({$h(self::what($a))}) के लिए Jobsence त्रिपक्षीय समझौते पर साइन किया है।</p>
            <p><b>{$h($from)}</b> has signed a Jobsence tripartite agreement with you for <b>{$h($a['skill'])}</b> ({$h(self::what($a))}).</p>
            <p>समझौता पढ़ें और ईमेल OTP से साइन करें – दोनों के साइन होते ही आप एक-दूसरे का फ़ोन और ईमेल देख सकेंगे।<br>Read the agreement and sign with an email OTP – once both have signed you will see each other’s phone and email.</p>
            <p>" . MentorMailer::button($url, 'समझौता देखें / Review agreement', '#138808') . "</p>{$planNote}
            <p style='font-size:13px;color:#4b5563'>यह अनुरोध " . Mentoring::RESPONSE_DAYS . " दिन में समाप्त हो जाएगा। / This request expires in " . Mentoring::RESPONSE_DAYS . " days.</p>");
        return MentorMailer::send((string)$to['email'], 'Please sign: Jobsence ' . strtolower(Mentoring::KINDS[$a['kind'] ?? 'skill']['what'][1] ?? 'mentoring') . ' agreement – ' . $a['skill'], $body);
    }

    /**
     * Fee is mandatory on both sides. To an unpaid seeker: "a mentor is interested – pay the one-time
     * form fee". To a provider without a plan: "a candidate wants to connect – pay to see candidates".
     */
    public static function payToConnect(string $toRole, array $to, string $fromName, string $skills, ?array $a = null): bool
    {
        $h = [MentorMailer::class, 'h'];
        $kind = Mentoring::kindOf((string)$to['type']) ?? 'skill';
        $intern = $kind === 'internship';
        if ($toRole === 'seeker') {
            $url = MentorMailer::base() . '/apply/pay/' . $to['token'];
            $fee = number_format((float)$to['total_amount'], 0);
            $body = MentorMailer::layout("
                <p>प्रिय / Dear {$h($to['full_name'])},</p>
                <p><b>{$h($fromName)}</b> (" . ($intern ? 'इंटर्नशिप प्रदाता / internship provider' : 'मेंटर / mentor') . ") आपकी प्रोफ़ाइल में रुचि रखते हैं: <b>{$h($skills)}</b>।<br>
                <b>{$h($fromName)}</b> is interested in your profile: <b>{$h($skills)}</b>.</p>
                <p>अगर आप " . ($intern ? 'इंटर्नशिप' : 'मेंटर') . " ढूँढ रहे हैं, तो एक बार का फॉर्म शुल्क ₹{$fee} (GST सहित) भरें। शुल्क दोनों पक्षों के लिए ज़रूरी है – उसके बाद ही समझौता और संपर्क संभव है।<br>
                If you are looking for " . ($intern ? 'an internship' : 'a mentor') . ", pay the one-time form fee of ₹{$fee} (incl. GST). The fee is mandatory for both parties – only then are the agreement and contact possible.</p>
                <p>" . MentorMailer::button($url, "₹{$fee} भरें / Pay ₹{$fee}", '#138808') . '</p>');
            return MentorMailer::send((string)$to['email'], 'A ' . ($intern ? 'internship provider' : 'mentor') . ' is interested in you – complete your Jobsence registration', $body);
        }
        $masked = ContactPass::maskName($fromName);
        $url = MentorMailer::base() . '/mentoring/access/' . $to['token'];
        if ($kind === 'nearme') {
            $renew = MentorMailer::base() . '/apply/renew/' . $to['token'];
            return MentorMailer::send((string)$to['email'], 'A customer wants your service – renew your Near Me listing | Jobsence', MentorMailer::layout("
                <p>प्रिय / Dear {$h($to['full_name'])},</p>
                <p>ग्राहक <b>{$h($masked)}</b> आपसे <b>{$h($skills)}</b> सेवा चाहते हैं, लेकिन आपकी Near Me लिस्टिंग की अवधि समाप्त है। रिन्यू करें और समझौता साइन करें।<br>
                Customer <b>{$h($masked)}</b> wants your <b>{$h($skills)}</b> service, but your Near Me listing has expired. Renew it and sign the agreement.</p>
                <p>" . MentorMailer::button($renew, 'लिस्टिंग रिन्यू करें / Renew listing', '#138808') . '</p>'));
        }
        $signUrl = $a ? MentorMailer::base() . Mentoring::linkFor($a, 'provider') : '';
        $price = Mentoring::planPrice($to);
        $gstNote = Mentoring::livesAbroad($to) ? ['', ''] : [' (GST सहित)', ' (incl. GST)'];
        $body = MentorMailer::layout("
            <p>प्रिय / Dear {$h($to['full_name'])},</p>
            <p>उम्मीदवार <b>{$h($masked)}</b> आपसे <b>{$h($skills)}</b> के लिए जुड़ना चाहते हैं और उन्होंने समझौते पर साइन कर दिया है।<br>
            Candidate <b>{$h($masked)}</b> wants to connect with you for <b>{$h($skills)}</b> and has signed the agreement.</p>
            " . ($kind === 'job'
                ? "<p>उम्मीदवार देखने और साइन करने के लिए भर्ती प्लान लें – 3 दिन (₹216) से 1 साल (₹19,424) तक; भारत के बाहर USD 12 से। शुल्क दोनों पक्षों के लिए ज़रूरी है।<br>
                   Get a hiring plan to see candidates and sign – 3 days (₹216) to 1 year (₹19,424); outside India from USD 12. The fee is mandatory for both parties.</p>
                   <p>" . MentorMailer::button($url, 'भर्ती प्लान लें / Get a hiring plan', '#138808') . '</p>'
                : "<p>उम्मीदवार देखने और साइन करने के लिए एक बार का शुल्क {$price}{$gstNote[0]} भरें – " . ($intern ? '10 दिन, रोज़ 3 प्रोफ़ाइल, अधिकतम 20' : '30 दिन, रोज़ 3 प्रोफ़ाइल') . "। शुल्क दोनों पक्षों के लिए ज़रूरी है।<br>
                   Pay the one-time fee of {$price}{$gstNote[1]} to see candidates and sign – " . ($intern ? '10 days, 3 profiles a day, up to 20' : '30 days, 3 profiles a day') . ". The fee is mandatory for both parties.</p>
                   <p>" . MentorMailer::button($url, "{$price} प्लान लें / Get the {$price} plan", '#138808') . '</p>') . "
            " . ($signUrl !== '' ? "<p style='font-size:13px;color:#4b5563'>भुगतान के बाद समझौता यहाँ साइन करें / After paying, sign here: <a href='{$h($signUrl)}'>{$h($signUrl)}</a></p>" : ''));
        return MentorMailer::send((string)$to['email'], 'A candidate wants to connect – ' . ($kind === 'job' ? 'get a hiring plan' : 'pay ' . $price) . ' to see candidates | Jobsence', $body);
    }

    /** Both signed: each party gets the other's contact details; the owner gets a copy. */
    public static function agreementSigned(array $a, array $seeker, array $provider): void
    {
        $h = [MentorMailer::class, 'h'];
        $p = Mentoring::decode($provider);
        $contact = static fn(array $r, string $name) => "<table style='border-collapse:collapse;margin:8px 0'>
            <tr><td style='padding:4px 10px 4px 0'>नाम / Name</td><td><b>{$h($name)}</b></td></tr>
            <tr><td style='padding:4px 10px 4px 0'>मोबाइल / Mobile</td><td><b>{$h($r['mobile'])}</b></td></tr>
            <tr><td style='padding:4px 10px 4px 0'>ईमेल / Email</td><td><b>{$h($r['email'])}</b></td></tr>
            <tr><td style='padding:4px 10px 4px 0'>स्थान / Location</td><td>{$h(implode(', ', array_filter([$r['city'], $r['state']])))}</td></tr></table>";
        $signed = 'समझौता नं. / Agreement no. JSA-' . (int)$a['id'] . ' · ' . $h(date('d M Y, h:i A', strtotime((string)$a['jobsence_signed_at'])));

        $mail = static function (array $to, array $other, string $otherName, string $role) use ($a, $h, $contact, $signed): void {
            $body = MentorMailer::layout("
                <p>प्रिय / Dear {$h($to['full_name'])},</p>
                <p>✅ <b>{$h($a['skill'])}</b> के लिए त्रिपक्षीय समझौता पूरा हुआ – उम्मीदवार, प्रदाता और Jobsence तीनों के साइन हो गए हैं।<br>
                ✅ The tripartite agreement for <b>{$h($a['skill'])}</b> is complete – signed by the candidate, the provider and Jobsence.</p>
                <p>{$signed}</p>
                <h3 style='font-size:16px;margin:16px 0 6px'>संपर्क / Contact</h3>" . $contact($other, $otherName) . "
                <p style='font-size:13px;color:#4b5563'>यह जानकारी केवल इसी उद्देश्य के लिए है – किसी से साझा न करें। / Use these details only for this purpose – do not share them.</p>
                <p>" . MentorMailer::button(MentorMailer::base() . Mentoring::linkFor($a, $role), 'समझौता देखें / View agreement', '#138808') . '</p>');
            MentorMailer::send((string)$to['email'], 'Agreement complete – contact details | Jobsence JSA-' . (int)$a['id'], $body);
        };
        $mail($seeker, $provider, Mentoring::displayName($p), 'seeker');
        $mail($provider, $seeker, (string)$seeker['full_name'], 'provider');

        $owner = \App\Helpers\AdminMail::to();
        MentorMailer::send($owner, 'Agreement signed JSA-' . (int)$a['id'] . ': ' . $seeker['reg_no'] . ' ↔ ' . $provider['reg_no'], MentorMailer::layout("
            <p>Tripartite agreement signed ({$h($a['kind'] ?? 'skill')} – {$h($a['skill'])}).</p>
            <p><b>Candidate:</b> {$h($seeker['full_name'])} ({$h($seeker['reg_no'])}) · signed {$h($a['candidate_signed_at'])} from {$h($a['candidate_sign_ip'])}<br>
            <b>Provider:</b> {$h(Mentoring::displayName($p))} ({$h($provider['reg_no'])}) · signed {$h($a['mentor_signed_at'])} from {$h($a['mentor_sign_ip'])}<br>
            <b>Jobsence countersigned:</b> {$h($a['jobsence_signed_at'])} · version {$h($a['agreement_version'])}</p>"));
    }

    public static function declined(array $a, string $byRole, array $seeker, array $provider): bool
    {
        $h = [MentorMailer::class, 'h'];
        $to = $byRole === 'provider' ? $seeker : $provider;
        $by = $byRole === 'provider' ? Mentoring::displayName(Mentoring::decode($provider)) : $seeker['full_name'];
        $more = $byRole === 'provider'
            ? MentorMailer::button(MentorMailer::base() . (Mentoring::KINDS[$a['kind'] ?? 'skill']['providers_page'] ?? '/skill-mentors'), 'दूसरे प्रदाता देखें / See other providers', '#138808')
            : '';
        return MentorMailer::send((string)$to['email'], 'Agreement request declined – ' . $a['skill'] . ' | Jobsence', MentorMailer::layout("
            <p>प्रिय / Dear {$h($to['full_name'])},</p>
            <p>{$h($by)} ने <b>{$h($a['skill'])}</b> का समझौता अनुरोध अस्वीकार कर दिया। / {$h($by)} declined the agreement request for <b>{$h($a['skill'])}</b>.</p>
            <p>{$more}</p>"));
    }

    public static function ended(array $a, string $byRole, array $seeker, array $provider, string $reason): void
    {
        $h = [MentorMailer::class, 'h'];
        $note = $byRole === 'seeker'
            ? '<p><b>उम्मीदवार ने प्रदाता को छोड़ दिया है – उम्मीदवार का रजिस्ट्रेशन बंद हो गया है। नए मेंटर / इंटर्नशिप के लिए नया फॉर्म शुल्क देना होगा।</b><br>The candidate rejected the provider – the candidate’s registration is now closed. A new form fee is needed for a new mentor / internship.</p>'
            : '<p><b>प्रदाता ने समझौता ख़त्म किया – उम्मीदवार का रजिस्ट्रेशन खुला है और वह दूसरा प्रदाता चुन सकता है।</b><br>The provider ended the agreement – the candidate’s registration stays open and they can choose another provider.</p>';
        $inner = "<p>समझौता JSA-" . (int)$a['id'] . " ({$h($a['skill'])}) समाप्त हुआ। / Agreement JSA-" . (int)$a['id'] . " ({$h($a['skill'])}) has ended.</p>{$note}"
            . ($reason !== '' ? "<p>कारण / Reason: {$h($reason)}</p>" : '');
        foreach ([$seeker, $provider] as $r) {
            MentorMailer::send((string)$r['email'], 'Agreement ended JSA-' . (int)$a['id'] . ' | Jobsence', MentorMailer::layout("<p>प्रिय / Dear {$h($r['full_name'])},</p>" . $inner));
        }
        MentorMailer::send(\App\Helpers\AdminMail::to(), 'Agreement ended JSA-' . (int)$a['id'] . ' by ' . $byRole, MentorMailer::layout($inner));
    }
}
