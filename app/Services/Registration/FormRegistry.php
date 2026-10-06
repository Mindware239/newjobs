<?php

declare(strict_types=1);

namespace App\Services\Registration;

/**
 * Definitions of every Jobsence ₹155 registration form (candidate + skill-provider side).
 * Each form = metadata + sections of fields + declaration points; the form view, validator,
 * admin detail page and emails are all driven from these definitions.
 *
 * Field keys listed in COLUMN_FIELDS are stored in their own table column; everything else goes
 * into the `details` JSON column.
 */
class FormRegistry
{
    public const COLUMN_FIELDS = [
        'full_name', 'dob', 'gender', 'mobile', 'whatsapp', 'email', 'aadhaar',
        'state', 'district', 'city', 'pincode', 'qualification', 'categories', 'preferred_location', 'resume',
        'gstin', 'video',
    ];

    public const MAX_CATEGORIES = 10;

    /** Paid internship stipend range in India (₹ per month). */
    public const STIPEND_MIN = 8000;
    public const STIPEND_MAX = 500000;

    /** Category limit for a form (skill development: max 5 skill choices, in order of preference). */
    public static function maxCategories(array $form): int
    {
        return (int)($form['max_categories'] ?? self::MAX_CATEGORIES);
    }

    public static function minCategories(array $form): int
    {
        return (int)($form['min_categories'] ?? 1);
    }

    /** Part-time aspirants: ₹250 + 18% GST, valid 3 months. */
    public const PARTTIME_FEE = 295.00;

    /** Fee for a form, total including GST. */
    /**
     * Fee for a form, total including GST. Forms with 'fee_by' => [field, [option => total]]
     * (healthcare: doctor / front desk / sweeper …) price by the option chosen in $details.
     */
    public static function feeOf(array $form, array $details = []): float
    {
        if (!empty($form['fee_by'])) {
            [$field, $map] = $form['fee_by'];
            $choice = $details[$field] ?? null;
            if (is_string($choice) && isset($map[$choice])) {
                return (float)$map[$choice];
            }
            return (float)max($map);
        }
        return (float)($form['fee'] ?? \App\Models\PortalRegistration::FEE);
    }

    /** "₹155" / "₹295" */
    public static function feeLabel(array $form): string
    {
        return '₹' . number_format(self::feeOf($form), 0);
    }

    /** slug => form definition */
    /**
     * Pricing model (user, 2026-10-06): job seekers use Jobsence free, employers / providers pay.
     * With SEEKERS_FREE every job-seeker-side form costs nothing; their fee wording is removed and a
     * "free for job seekers" line added (makeFree). Set to false to bring the old seeker fees back.
     */
    public const SEEKERS_FREE = true;
    public const SEEKER_FREE_SLUGS = ['skill-development', 'internship', 'full-time-job', 'part-time-job', 'work-from-home', 'jobs-pass',
        'restaurant-chef-jobs', 'healthcare-jobs', 'senior-citizen-jobs', 'near-me-seeker', 'intl-country', 'international-job'];

    /** Is this registration type free because it is on the job-seeker side? */
    public static function seekerIsFree(string $type): bool
    {
        if (!self::SEEKERS_FREE) {
            return false;
        }
        foreach (self::SEEKER_FREE_SLUGS as $slug) {
            if ((self::all()[$slug]['type'] ?? null) === $type) {
                return true;
            }
        }
        return false;
    }

    /** Job-seeker form without fees: price-free labels, no fee sentences, a clear "free" line. */
    private static function makeFree(array $f): array
    {
        $f['fee'] = 0.0;
        if (!empty($f['fee_by'])) {
            $field = $f['fee_by'][0];
            unset($f['fee_by']);
            foreach ($f['sections'] as &$sec) {
                foreach ($sec['fields'] as &$fld) {
                    if (($fld['key'] ?? '') === $field && !empty($fld['options'])) {
                        foreach ($fld['options'] as &$o) {
                            $o = array_map(static fn($l) => trim((string)preg_replace('/\s*[–-]\s*₹.*$/u', '', $l)), $o);
                        }
                        unset($o);
                    }
                }
                unset($fld);
            }
            unset($sec);
        }
        $isFee = static fn(array $p): bool => (bool)preg_match('/processing fee|platform fee|one-time fee|one-time ₹|\bthe fee\b|fee of ₹|fee for work|form fee again|pass is valid|₹\s?\d[\d,]*\s*\+\s*(18%\s*)?GST|^\s*(doctor|front desk|sweeper|other hospital staff):/iu', (string)$p[1])
            && !preg_match('/course fee/i', (string)$p[1]);
        $free = ['जॉब सीकर के लिए रजिस्ट्रेशन पूरी तरह मुफ़्त है – भुगतान नौकरी देने वाले करते हैं।', 'Registration is completely free for job seekers – employers pay.'];
        // "(₹1,180 or USD 10 each)" inside an otherwise useful sentence: keep the sentence, drop the price.
        $noPrice = static fn(array $p): array => array_map(static fn($s) => trim((string)preg_replace('/\s*\([^()]*(?:₹|USD)[^()]*\)/u', '', (string)$s)), $p);
        foreach (['declaration', 'next_steps'] as $k) {
            if (!empty($f[$k])) {
                $f[$k] = array_values(array_map($noPrice, array_filter($f[$k], static fn($p) => !$isFee($p))));
            }
        }
        $f['declaration'] = array_merge([$free], $f['declaration'] ?? []);
        $points = array_values(array_filter($f['info']['points'] ?? [], static fn($p) => !$isFee($p)));
        $f['info'] = [
            'title' => preg_match('/fee|शुल्क/iu', (string)($f['info']['title'][1] ?? 'fee')) ? ['जॉब सीकर के लिए मुफ़्त', 'Free for job seekers'] : $f['info']['title'],
            'points' => array_merge([$free], $points),
        ];
        foreach (['intro', 'button'] as $k) {
            if (!empty($f[$k])) {
                $f[$k] = array_map(static function (string $text): string {
                    $parts = preg_split('/(?<=[.।])\s+/u', $text) ?: [$text];
                    $keep = array_filter($parts, static fn($s) => !preg_match('/₹|GST/u', $s));
                    return $keep ? trim(implode(' ', $keep)) : trim((string)preg_replace('/\s*[–-]?\s*₹.*$/u', '', $text));
                }, $f[$k]);
            }
        }
        return $f;
    }

    public static function all(): array
    {
        static $forms = null;
        if ($forms !== null) {
            return $forms;
        }
        $forms = self::definitions();
        if (self::SEEKERS_FREE) {
            foreach (self::SEEKER_FREE_SLUGS as $slug) {
                if (isset($forms[$slug])) {
                    $forms[$slug] = self::makeFree($forms[$slug]);
                }
            }
            $forms['jobs-pass']['hidden_from_hub'] = true; // Govt job details are free now – no pass needed
        }
        return $forms;
    }

    private static function definitions(): array
    {
        return [
            'skill-development' => self::skillDevelopment(),
            'internship' => self::internship(),
            'full-time-job' => self::job('fulltime'),
            'part-time-job' => self::job('parttime'),
            'work-from-home' => self::job('wfh'),
            'skill-provider' => self::skillProvider(),
            'internship-provider' => self::internshipProvider(),
            'mentor-plan' => self::providerPlan('mentorplan'),
            'internship-plan' => self::providerPlan('internplan'),
            'job-provider' => self::jobProvider(),
            'job-plan' => self::providerPlan('jobplan'),
            'international-job' => self::internationalJob(),
            'intl-country' => self::countryUnlock(),
            'ngo-registration' => self::ngo(),
            'jobs-pass' => self::jobsPass(),
            'near-me-provider' => self::nearMeProvider(),
            'near-me-seeker' => self::nearMeSeeker(),
            'restaurant-chef-jobs' => self::restaurantJobs(),
            'healthcare-jobs' => self::healthcareJobs(),
            'hospital-hiring' => self::hospitalHiring(),
            'hire-part-time' => self::hirePartTime(),
            'senior-citizen-jobs' => self::seniorJobs(),
            'senior-citizen-hiring' => self::seniorHiring(),
            'extra-job-post' => self::extraJobPost(),
        ];
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /** Form type stored in DB => slug */
    public static function slugForType(string $type): ?string
    {
        foreach (self::all() as $slug => $form) {
            if ($form['type'] === $type) {
                return $slug;
            }
        }
        return null;
    }

    public static function byType(string $type): ?array
    {
        $slug = self::slugForType($type);
        return $slug ? self::all()[$slug] : null;
    }

    /** Flat key => field map for a form. */
    public static function fields(array $form): array
    {
        $out = [];
        foreach ($form['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $out[$field['key']] = $field;
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Forms
    // ------------------------------------------------------------------

    private static function skillDevelopment(): array
    {
        return [
            'type' => 'skill',
            'multipart' => true,
            'prefix' => 'SKL',
            'side' => 'candidate',
            'icon' => '🎓',
            'title' => ['कौशल विकास रजिस्ट्रेशन', 'Skill Development Registration'],
            'button' => ['कौशल विकास के लिए आवेदन करें', 'Candidates for Skill Development – Apply Here'],
            'intro' => ['पहले कुशल बनें – 2000+ स्किल्स, ऑनलाइन वीडियो (पूरे भारत में) या ऑफलाइन (दिल्ली-NCR)।', 'First become skilled – online video classes (Pan-India) or offline (Delhi-NCR).'],
            'categories_label' => ['आप कौन से स्किल सीखना चाहते हैं – अधिकतम 5 विकल्प, पसंद के क्रम में', 'Your skill choices – up to 5, in order of preference (search 3000+ list)'],
            'max_categories' => 5,
            'ordered_categories' => true,
            'sections' => [
                self::personalSection(['guardian' => true, 'aadhaar_required' => false]),
                self::addressSection(true),
                [
                    'title' => ['शिक्षा व वर्तमान स्थिति', 'Education & Current Status'],
                    'fields' => [
                        self::qualification(),
                        self::text('qualification_detail', ['विषय / स्ट्रीम / अन्य योग्यता', 'Stream / Specialisation / Other (e.g. B.Com, Engineer, Doctor)'], false, ['full' => true]),
                        self::radio('current_status', ['वर्तमान स्थिति', 'Current Status'], self::CANDIDATE_STATUSES, true, ['full' => true, 'other' => 'current_status_other']),
                        self::radio('has_experience', ['क्या आपके पास कोई कार्य अनुभव है?', 'Do you have any work experience?'], ['1' => ['हाँ', 'Yes'], '0' => ['नहीं', 'No']], false),
                        self::textarea('experience_detail', ['अनुभव संक्षेप में (काम + कितने साल)', 'If yes, describe briefly (job role + years)'], false),
                    ],
                ],
                [
                    'title' => ['स्किल पसंद', 'Skill Preference'],
                    'fields' => [
                        self::radio('training_mode', ['ट्रेनिंग का पसंदीदा तरीका', 'Preferred Mode of Training'], [
                            'online' => ['ऑनलाइन / वीडियो क्लास (पूरे भारत में)', 'Online / Video classes (Pan-India)'],
                            'offline_ncr' => ['ऑफलाइन क्लास – केवल दिल्ली-NCR', 'Offline classes – Delhi-NCR only'],
                        ], true, ['full' => true]),
                        self::languagesField(['ट्रेनिंग की पसंदीदा भाषा', 'Preferred Language of Training'], true),
                        self::checkboxes('preferred_timing', ['पसंदीदा समय', 'Preferred timing'], self::TIMINGS, false, ['full' => true]),
                        self::text('preferred_location', ['पसंदीदा शहर / स्थान (ऑफलाइन के लिए)', 'Preferred city / location (for offline)'], false),
                        self::categories(true),
                        self::textarea('other_skills', ['कोई और स्किल जो आप सीखना चाहते हैं', 'Any other skill you want to learn'], false),
                        self::textarea('about_me', ['अपने बारे में संक्षेप में (पढ़ाई, काम, लक्ष्य)', 'Brief description about yourself (education, work, goals)'], false),
                        self::file('resume', ['रिज़्यूमे अपलोड करें (यदि हो – PDF/DOC, अधिकतम 5MB)', 'Upload resume if any (PDF/DOC, max 5MB)']),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['₹155 का एकमुश्त प्रोसेसिंग शुल्क (GST सहित) वापसी योग्य नहीं है।', 'The one-time processing fee of ₹155 (including GST) is non-refundable.'],
                self::untilMatched(),
                self::rejectRule(),
                ['मुझे केवल मेरे चुने हुए (अधिकतम 5) स्किल्स में ही ट्रेनिंग दी जाएगी।', 'I will be trained only in the skills I chose (up to 5).'],
                ['चयन के बाद कुल कोर्स फीस ₹12,000 + 18% GST भी वापसी योग्य नहीं है।', 'After selection, the total course fee of ₹12,000 + 18% GST is also non-refundable.'],
                ['क्लास रोज़ 2 घंटे, हफ्ते में 3 दिन, प्रैक्टिकल ट्रेनिंग सहित होंगी।', 'Classes will be 2 hours per day, 3 days a week, including practical training.'],
                ['फॉर्म और रिज़्यूमे की जाँच में कम से कम 3 से 6 महीने लगते हैं।', 'Minimum 3 to 6 months is required for scrutiny of forms and resumes.'],
                ['Jobsence मेरा रिज़्यूमे इच्छुक कंपनियों तक पहुँचाने, जॉब-रेडी बनाने या स्व-रोज़गार में मार्गदर्शन की कोशिश करेगा।', 'Jobsence will try to push my resume to interested companies, make me job-ready, or guide me towards self-employment.'],
                ['Jobsence नौकरी / प्लेसमेंट की कोई ज़िम्मेदारी या गारंटी नहीं लेता।', 'Jobsence does not take any responsibility or guarantee of job / placement.'],
                ['ट्रेनिंग थर्ड-पार्टी स्किल डेवलपमेंट पार्टनर्स द्वारा, मेंटर की उपलब्धता के अनुसार स्थानीय भाषा / हिंदी / अंग्रेज़ी में होगी।', 'Training will be conducted through third-party skill development partners, in local language / Hindi / English depending on mentor availability.'],
                self::notGovt(),
            ],
            'next_steps' => [
                ['फॉर्म की जाँच में कम से कम 3 से 6 महीने लगते हैं।', 'Scrutiny of forms takes minimum 3 to 6 months.'],
                ['चयन होने पर आपको ₹12,000 + 18% GST कोर्स फीस (वापसी योग्य नहीं) की जानकारी दी जाएगी।', 'After selection, you will be informed about the course fee of ₹12,000 + 18% GST (non-refundable).'],
                ['क्लास रोज़ 2 घंटे, हफ्ते में 3 दिन, प्रैक्टिकल ट्रेनिंग सहित।', 'Classes will be 2 hours per day, 3 days a week, with practical training.'],
            ],
        ];
    }

    private static function internship(): array
    {
        return [
            'type' => 'internship',
            'prefix' => 'INT',
            'side' => 'candidate',
            'icon' => '🧑‍💻',
            'title' => ['इंटर्नशिप रजिस्ट्रेशन', 'Internship Registration'],
            'button' => ['इंटर्नशिप के लिए आवेदन करें', 'Candidates for Internship – Apply Here'],
            'intro' => ['प्रैक्टिकल अनुभव पाएँ – 3000+ डोमेन, ऑफिस में या वर्क फ्रॉम होम।', 'Get practical experience – 3000+ domains, on-site or work from home.'],
            'categories_label' => ['पसंदीदा इंटर्नशिप डोमेन (सूची में खोजें)', 'Preferred internship domain (search 3000+ categories)'],
            'sections' => [
                self::personalSection([]),
                self::addressSection(false),
                [
                    'title' => ['शिक्षा', 'Education'],
                    'fields' => [
                        self::qualification(),
                        self::text('current_course', ['वर्तमान कोर्स', 'Current Course (e.g. B.Tech CSE, BBA, ITI Electrician)'], true),
                        self::text('institution', ['कॉलेज / संस्थान का नाम', 'College / Institute Name'], false),
                        self::radio('course_year', ['कोर्स का वर्ष', 'Year of Course'], [
                            '1' => ['पहला वर्ष', '1st Year'], '2' => ['दूसरा वर्ष', '2nd Year'], '3' => ['तीसरा वर्ष', '3rd Year'],
                            '4' => ['चौथा / अंतिम वर्ष', '4th / Final Year'], 'completed' => ['पूरा हो चुका', 'Completed'],
                        ], true, ['full' => true]),
                    ],
                ],
                [
                    'title' => ['इंटर्नशिप पसंद', 'Internship Preference'],
                    'fields' => [
                        self::categories(true),
                        self::workMode(true),
                        self::radio('duration', ['पसंदीदा अवधि', 'Duration Preferred'], [
                            '1' => ['1 महीना', '1 month'], '2' => ['2 महीने', '2 months'], '3' => ['3 महीने', '3 months'], '6' => ['6 महीने', '6 months'],
                        ], true),
                        self::text('preferred_location', ['पसंदीदा शहर / स्थान', 'Preferred City / Location'], false),
                        self::checkboxes('preferred_timing', ['पसंदीदा समय', 'Preferred timing'], self::TIMINGS, false, ['full' => true]),
                        self::radio('internship_pay', ['पेड या अनपेड इंटर्नशिप', 'Paid or Unpaid Internship'], [
                            'paid' => ['केवल पेड', 'Paid only'], 'unpaid' => ['अनपेड भी चलेगी', 'Unpaid is okay'], 'either' => ['कोई भी', 'Either'],
                        ], true, ['full' => true]),
                        self::number('stipend_expectation', ['अपेक्षित स्टाइपेंड (₹ प्रति माह) – ₹8,000 से ₹5,00,000', 'Expected stipend (₹ per month) – ₹8,000 to ₹5,00,000'], false, self::STIPEND_MIN, self::STIPEND_MAX)
                            + ['hint' => ['पेड इंटर्नशिप का स्टाइपेंड कम से कम ₹8,000 और अधिकतम ₹5,00,000 प्रति माह होता है। अनपेड के लिए खाली छोड़ें।', 'Paid internships pay ₹8,000 – ₹5,00,000 per month. Leave empty for unpaid.']],
                        self::textarea('known_skills', ['आपको पहले से आने वाले स्किल', 'Skills Already Known'], false),
                    ],
                ],
            ],
            'declaration' => array_merge(self::commonJobDeclaration('इंटर्नशिप', 'internship'), [self::untilMatched(), self::rejectRule(), self::notGovt()]),
            'next_steps' => self::commonJobNextSteps('इंटर्नशिप', 'internship'),
        ];
    }

    private static function job(string $kind): array
    {
        $meta = [
            'fulltime' => ['FTJ', '💼', ['फुल-टाइम जॉब रजिस्ट्रेशन', 'Full-time Job Registration'], ['फुल-टाइम जॉब के लिए आवेदन करें', 'Candidates looking for Full-time Job – Apply Here'], ['नियमित काम पाएँ – 3000+ जॉब कैटेगरी, पूरे भारत में।', 'Get regular work – 3000+ job categories across India.']],
            'parttime' => ['PTJ', '⏰', ['पार्ट-टाइम जॉब रजिस्ट्रेशन', 'Part-time Job Registration'], ['पार्ट-टाइम जॉब के लिए आवेदन करें', 'Candidates for Part-time Job – Apply Here'], ['अपने समय के अनुसार काम – कुछ घंटे, कुछ दिन।', 'Work on your own schedule – a few hours, a few days.']],
            'wfh' => ['WFH', '🏠', ['वर्क फ्रॉम होम रजिस्ट्रेशन', 'Work from Home Registration'], ['वर्क फ्रॉम होम के लिए आवेदन करें', 'Candidates looking for Work from Home – Apply Here'], ['घर बैठे काम – डेटा एंट्री, डिजिटल मार्केटिंग, कस्टमर सपोर्ट और अधिक।', 'Work from home – data entry, digital marketing, customer support and more.']],
        ][$kind];

        $preference = [
            self::categories(true),
            self::text('preferred_location', $kind === 'wfh' ? ['आपका शहर', 'Your City'] : ['पसंदीदा शहर / स्थान', 'Preferred Location / City'], $kind === 'fulltime'),
            self::number('expected_salary', ['अपेक्षित वेतन (₹ प्रति माह)', 'Expected Salary (₹ per month)'], false, 0, 10000000),
            self::radio('notice_period', ['नोटिस पीरियड (यदि अभी नौकरी कर रहे हैं)', 'Notice Period (if currently working)'], [
                'na' => ['लागू नहीं / तुरंत', 'Not working / Immediate'], '15' => ['15 दिन', '15 days'], '30' => ['30 दिन', '30 days'],
                '60' => ['60 दिन', '60 days'], '90' => ['90 दिन', '90 days'],
            ], false, ['full' => true]),
        ];

        if ($kind === 'parttime') {
            // Gig work: expected pay per day, hours, dates and timings instead of monthly salary / notice period.
            $preference = [
                self::categories(true),
                self::text('preferred_location', ['पसंदीदा शहर / स्थान', 'Preferred Location / City'], true),
                self::number('min_pay_per_day', ['न्यूनतम अपेक्षित भुगतान (₹ प्रति दिन)', 'Minimum expected payment (₹ per day)'], true, 100, 200000)
                    + ['hint' => ['जैसे एग्ज़िबिशन स्टाफ ₹800, मॉडल ₹3,000, बाउंसर ₹1,500 प्रति दिन।', 'e.g. exhibition staff ₹800, model ₹3,000, bouncer ₹1,500 per day.']],
                self::number('hours_per_day', ['आप प्रति दिन कितने घंटे काम कर सकते हैं', 'How many hours per day can you work'], true, 1, 16),
                ['key' => 'available_from', 'type' => 'date', 'date_mode' => 'future', 'label' => ['किस तारीख से उपलब्ध', 'Available from (date)'], 'required' => true],
                ['key' => 'available_to', 'type' => 'date', 'date_mode' => 'future', 'label' => ['किस तारीख तक उपलब्ध (वैकल्पिक)', 'Available till (date, optional)'], 'required' => false],
                self::text('timing', ['काम का समय (जैसे सुबह 10 से शाम 6)', 'Timings you can offer (e.g. 10am – 6pm)'], true, ['max' => 100]),
                self::workingHours(),
                self::checkboxes('available_days', ['उपलब्ध दिन', 'Available Days'], [
                    'mon' => ['सोम', 'Mon'], 'tue' => ['मंगल', 'Tue'], 'wed' => ['बुध', 'Wed'], 'thu' => ['गुरु', 'Thu'],
                    'fri' => ['शुक्र', 'Fri'], 'sat' => ['शनि', 'Sat'], 'sun' => ['रवि', 'Sun'],
                ], true),
                self::workMode(true),
                self::text('height_cm', ['लंबाई (सेमी – मॉडल / बाउंसर / होस्टेस के लिए)', 'Height (cm – for models / bouncers / hostesses)'], false, ['max' => 10]),
                self::text('vehicle_licence', ['ड्राइविंग लाइसेंस प्रकार (ड्राइवर के लिए)', 'Driving licence type (for drivers)'], false, ['max' => 60]),
            ];
        }

        if ($kind === 'wfh') {
            $preference[] = self::radio('internet', ['इंटरनेट उपलब्धता', 'Internet Availability'], [
                'broadband' => ['वाई-फाई / ब्रॉडबैंड', 'Wi-Fi / Broadband'],
                'mobile' => ['मोबाइल डेटा (4G/5G)', 'Mobile data (4G/5G)'],
                'none' => ['स्थिर इंटरनेट नहीं है', 'No stable internet'],
            ], true, ['full' => true]);
            $preference[] = self::checkboxes('device', ['आपके पास उपलब्ध डिवाइस', 'Devices You Have'], [
                'laptop' => ['लैपटॉप', 'Laptop'], 'desktop' => ['डेस्कटॉप', 'Desktop'], 'smartphone' => ['स्मार्टफोन', 'Smartphone'],
            ], false);
            $preference[] = self::workingHours();
            $preference[] = self::checkboxes('wfh_work_type', ['पसंदीदा काम का प्रकार', 'Type of Work Preferred'], [
                'data_entry' => ['डेटा एंट्री', 'Data Entry'], 'digital_marketing' => ['डिजिटल मार्केटिंग', 'Digital Marketing'],
                'customer_support' => ['कस्टमर सपोर्ट', 'Customer Support'], 'content_writing' => ['कंटेंट राइटिंग', 'Content Writing'],
                'telecalling' => ['टेलीकॉलिंग', 'Telecalling'], 'tutoring' => ['ऑनलाइन ट्यूशन', 'Online Tutoring'],
                'graphic_design' => ['ग्राफिक डिज़ाइन', 'Graphic Design'], 'translation' => ['अनुवाद', 'Translation'],
                'accounting' => ['अकाउंटिंग / बुककीपिंग', 'Accounting / Bookkeeping'], 'software' => ['सॉफ्टवेयर डेवलपमेंट', 'Software Development'],
                'other' => ['अन्य', 'Other'],
            ], true);
        }

        $preference[] = self::file('resume', ['रिज़्यूमे अपलोड करें (वैकल्पिक – PDF/DOC, अधिकतम 5MB)', 'Resume Upload (optional – PDF/DOC, max 5MB)']);
        $preference[] = self::textarea('known_skills', ['आपके मुख्य स्किल', 'Your Key Skills'], false);

        $isPart = $kind === 'parttime';
        return [
            'type' => $kind,
            'prefix' => $meta[0],
            'side' => 'candidate',
            'icon' => $meta[1],
            'title' => $meta[2],
            'button' => $meta[3],
            'intro' => $isPart
                ? ['एग्ज़िबिशन मैनपावर, मॉडल, बाउंसर, ड्राइवर, लॉन्ड्री सर्विस और 30,000+ पार्ट-टाइम काम। एक बार ₹250 + GST (₹295), 3 महीने के लिए मान्य।', 'Exhibition manpower, models, bouncers, drivers, laundry services and 30,000+ part-time roles. One-time ₹250 + GST (₹295), valid for 3 months.']
                : $meta[4],
            'categories_label' => $isPart
                ? ['आप कौन सा पार्ट-टाइम काम चाहते हैं – कम से कम 2, अधिकतम 5 विकल्प', 'Part-time work you want – minimum 2, maximum 5 options (search 30,000+ list)']
                : ['पसंदीदा जॉब कैटेगरी (सूची में खोजें)', 'Preferred job category (search 30,000+ categories)'],
            'multipart' => true,
            'resume_search' => $isPart,
            'fee' => $isPart ? self::PARTTIME_FEE : null,
            'validity_months' => $isPart ? 3 : null,
            'min_categories' => $isPart ? 2 : 1,
            'max_categories' => $isPart ? 5 : self::MAX_CATEGORIES,
            'info' => $isPart ? [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['एकमुश्त शुल्क ₹250 + 18% GST = ₹295 – रजिस्ट्रेशन भुगतान की तारीख से 3 महीने तक मान्य।', 'One-time fee ₹250 + 18% GST = ₹295 – registration valid for 3 months from payment.'],
                    ['कम से कम 2 और अधिकतम 5 काम चुनें, साथ में प्रति दिन न्यूनतम अपेक्षित भुगतान, घंटे, तारीखें और समय।', 'Choose 2 to 5 kinds of work, with your minimum expected pay per day, hours, dates and timings.'],
                    self::platformDisclaimer(),
                ],
            ] : null,
            'sections' => [
                self::personalSection([]),
                self::addressSection(false),
                [
                    'title' => ['योग्यता व अनुभव', 'Qualification & Experience'],
                    'fields' => [
                        self::qualification(),
                        self::radio('experience', ['कुल अनुभव', 'Total Experience'], [
                            'fresher' => ['फ्रेशर', 'Fresher'], '0_1' => ['1 साल से कम', 'Less than 1 year'], '1_3' => ['1–3 साल', '1–3 years'],
                            '3_5' => ['3–5 साल', '3–5 years'], '5_10' => ['5–10 साल', '5–10 years'], '10_plus' => ['10+ साल', '10+ years'],
                        ], true, ['full' => true]),
                        self::text('qualification_detail', ['विषय / डिग्री / सर्टिफिकेट', 'Degree / Stream / Certificate'], false),
                        self::text('current_role', ['वर्तमान / पिछला पद और कंपनी', 'Current / Last Job Role & Company'], false),
                    ],
                ],
                [
                    'title' => ['जॉब पसंद', 'Job Preference'],
                    'fields' => $preference,
                ],
            ],
            'declaration' => $isPart
                ? array_merge(self::commonJobDeclaration('नौकरी', 'job', '₹295 (₹250 + GST)'), [
                    ['मेरा रजिस्ट्रेशन भुगतान की तारीख से केवल 3 महीने तक मान्य रहेगा।', 'My registration is valid for 3 months from the date of payment.'],
                    self::platformDisclaimer(),
                    self::notGovt(),
                ])
                : array_merge(self::commonJobDeclaration('नौकरी', 'job'), [self::notGovt()]),
            'next_steps' => self::commonJobNextSteps('नौकरी', 'job'),
        ];
    }

    /** Jobs Pass price: ₹150 + 18% GST, valid 1 month for one email + one mobile. */
    public const JOBS_PASS_FEE = 177.00;

    private static function jobsPass(): array
    {
        return [
            'type' => 'jobpass',
            'prefix' => 'JIP',
            'side' => 'candidate',
            'icon' => '🇮🇳',
            'otp' => true,
            'login_required' => true,
            'hidden_from_hub' => true,
            'fee' => self::JOBS_PASS_FEE,
            'validity_months' => 1,
            'title' => ['Jobs in India पास (1 महीना)', 'Jobs in India Pass (1 month)'],
            'button' => ['सरकारी, PSU और बड़ी कंपनियों की सभी नौकरियाँ देखें', 'See all Govt, PSU & top company jobs'],
            'intro' => ['रेलवे, सेना, पुलिस, राज्य सरकारें, PSU (GAIL, BHEL…) और बड़ी कंपनियों की नौकरियाँ एक ही जगह। ₹150 + GST (₹177) में 1 महीने तक पूरी जानकारी और आवेदन लिंक देखें।', 'Railways, Army, Police, State Govts, PSUs (GAIL, BHEL…) and top company jobs in one place. ₹150 + GST (₹177) unlocks full details and official apply links for 1 month.'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['एक पास = एक ईमेल + एक मोबाइल नंबर, 1 महीने के लिए। ईमेल OTP से सत्यापन ज़रूरी है।', 'One pass = one email + one mobile number, for 1 month. Email OTP verification is required.'],
                    ['यह शुल्क Jobsence की जॉब-लिस्टिंग सेवा का है – यह कोई सरकारी शुल्क नहीं है। सरकारी नौकरियों के लिए आवेदन हमेशा आधिकारिक वेबसाइट पर ही करें।', 'This fee is for Jobsence’s job-listing service – it is NOT a government fee. Always apply for government jobs on the official website only.'],
                    ['Jobsence इन विभागों / कंपनियों से संबद्ध नहीं है; हर नौकरी के साथ आधिकारिक स्रोत का लिंक दिया जाता है।', 'Jobsence is not affiliated with these departments / companies; every job links to its official source.'],
                ],
            ],
            'sections' => [
                [
                    'title' => ['आपकी जानकारी', 'Your Details'],
                    'fields' => [
                        self::text('full_name', ['पूरा नाम', 'Full Name'], true, ['max' => 150, 'autocomplete' => 'name']),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर', 'Mobile Number'], 'required' => true, 'autocomplete' => 'tel'],
                        ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल (आपके लॉगिन वाला)', 'Email (same as your login)'], 'required' => true, 'autocomplete' => 'email',
                            'hint' => ['इसी ईमेल पर OTP और पास की पुष्टि आएगी।', 'OTP and pass confirmation will be sent here.']],
                        ['key' => 'state', 'type' => 'state', 'label' => ['राज्य / केंद्र शासित प्रदेश', 'State / UT'], 'required' => true],
                        self::qualification(),
                        self::checkboxes('job_interests', ['आप किन नौकरियों में रुचि रखते हैं', 'Jobs you are interested in'], self::INDIA_JOB_TYPES, true),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['₹177 (₹150 + GST) का शुल्क वापसी योग्य नहीं है और पास भुगतान से 1 महीने तक मान्य है।', 'The fee of ₹177 (₹150 + GST) is non-refundable and the pass is valid for 1 month from payment.'],
                ['पास केवल मेरे ईमेल और मोबाइल के लिए है; इसे किसी और के साथ साझा नहीं करूँगा/करूँगी।', 'The pass is only for my email and mobile; I will not share it with anyone.'],
                ['यह कोई सरकारी शुल्क नहीं है। Jobsence किसी नौकरी की गारंटी नहीं देता और नौकरी देने वाले विभागों / कंपनियों से संबद्ध नहीं है।', 'This is not a government fee. Jobsence does not guarantee any job and is not affiliated with the hiring departments / companies.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['अब “See Jobs in India” पेज पर सभी नौकरियों की पूरी जानकारी और आधिकारिक आवेदन लिंक देखें।', 'Now open the “See Jobs in India” page to see full details and official apply links of all jobs.'],
                ['आवेदन हमेशा आधिकारिक वेबसाइट पर ही करें। कोई भी आपसे नौकरी के बदले पैसे माँगे तो सावधान रहें।', 'Always apply on the official website. Beware of anyone asking for money in exchange for a job.'],
            ],
        ];
    }

    /** Near Me professions (main profession of a provider; also the search shortcuts). */
    public const NEAR_ME_PROFESSIONS = [
        'plumber' => ['प्लंबर', 'Plumber'],
        'electrician' => ['इलेक्ट्रीशियन', 'Electrician'],
        'carpenter' => ['कारपेंटर / बढ़ई', 'Carpenter'],
        'welder' => ['वेल्डर', 'Welder'],
        'painter' => ['पेंटर', 'House Painter'],
        'mason' => ['राज मिस्त्री', 'Mason (Raj Mistri)'],
        'tile_fitter' => ['टाइल / मार्बल फिटर', 'Tile & Marble Fitter'],
        'pop_ceiling' => ['POP / फॉल्स सीलिंग', 'POP & False Ceiling'],
        'waterproofing' => ['वॉटरप्रूफ़िंग', 'Waterproofing'],
        'fabricator' => ['ग्रिल / फ़ैब्रिकेटर', 'Grill & Steel Fabricator'],
        'aluminium_glass' => ['एल्युमिनियम / ग्लास फिटर', 'Aluminium & Glass Fitter'],
        'ac_mechanic' => ['AC मैकेनिक', 'AC Mechanic'],
        'fridge_mechanic' => ['फ्रिज मैकेनिक', 'Refrigerator Mechanic'],
        'washing_machine' => ['वॉशिंग मशीन रिपेयर', 'Washing Machine Repair'],
        'ro_technician' => ['RO / वॉटर प्यूरीफ़ायर टेक्नीशियन', 'RO / Water Purifier Technician'],
        'geyser_repair' => ['गीज़र रिपेयर', 'Geyser Repair'],
        'tv_repair' => ['TV रिपेयर', 'TV Repair'],
        'mobile_repair' => ['मोबाइल रिपेयर', 'Mobile Repair'],
        'computer_repair' => ['कंप्यूटर / लैपटॉप रिपेयर', 'Computer & Laptop Repair'],
        'cctv' => ['CCTV इंस्टॉलेशन', 'CCTV Installation'],
        'inverter_battery' => ['इन्वर्टर / बैटरी', 'Inverter & Battery Service'],
        'solar' => ['सोलर पैनल इंस्टॉलर', 'Solar Panel Installer'],
        'motor_pump' => ['मोटर / पंप मैकेनिक', 'Motor & Pump Mechanic'],
        'borewell' => ['बोरवेल', 'Borewell Service'],
        'water_tank' => ['पानी की टंकी सफ़ाई', 'Water Tank Cleaning'],
        'pest_control' => ['पेस्ट कंट्रोल', 'Pest Control'],
        'deep_cleaning' => ['घर की डीप क्लीनिंग', 'Home Deep Cleaning'],
        'sofa_carpet' => ['सोफ़ा / कारपेट क्लीनिंग', 'Sofa & Carpet Cleaning'],
        'laundry' => ['लॉन्ड्री / ड्राई क्लीनिंग', 'Laundry & Dry Cleaning'],
        'ironing' => ['इस्त्री (प्रेस वाला)', 'Ironing (Press Wala)'],
        'tailor' => ['दर्ज़ी / टेलर', 'Tailor'],
        'gents_tailor' => ['जेंट्स टेलर', 'Gents Tailor'],
        'ladies_tailor' => ['लेडीज़ टेलर', 'Ladies Tailor'],
        'cobbler' => ['मोची', 'Cobbler'],
        'goldsmith' => ['सुनार / गोल्डस्मिथ', 'Goldsmith (Sunar)'],
        'locksmith' => ['चाबी वाला / लॉकस्मिथ', 'Locksmith'],
        'packers_movers' => ['पैकर्स एंड मूवर्स', 'Packers & Movers'],
        'driver' => ['ड्राइवर', 'Driver'],
        'driver_on_call' => ['ड्राइवर ऑन कॉल', 'Driver on Call'],
        'car_mechanic' => ['कार मैकेनिक', 'Car Mechanic'],
        'bike_mechanic' => ['बाइक मैकेनिक', 'Bike Mechanic'],
        'car_wash' => ['कार वॉश', 'Car Wash'],
        'hair_dresser' => ['हेयर ड्रेसर / नाई', 'Hair Dresser / Barber'],
        'salon' => ['सैलून', 'Salon'],
        'beautician' => ['ब्यूटीशियन', 'Beautician'],
        'beauty_parlour' => ['ब्यूटी पार्लर', 'Beauty Parlour'],
        'makeup_artist' => ['मेकअप आर्टिस्ट', 'Makeup Artist'],
        'mehendi' => ['मेहंदी आर्टिस्ट', 'Mehendi Artist'],
        'massage_spa' => ['मसाज / स्पा', 'Massage & Spa'],
        'yoga_fitness' => ['योग / फ़िटनेस ट्रेनर', 'Yoga & Fitness Trainer'],
        'home_tutor' => ['होम ट्यूटर', 'Home Tutor'],
        'music_dance' => ['संगीत / डांस टीचर', 'Music & Dance Teacher'],
        'cook' => ['कुक / रसोइया', 'Cook'],
        'maid' => ['कामवाली / मेड', 'Maid / House Help'],
        'baby_sitter' => ['बेबी सिटर / नैनी', 'Baby Sitter / Nanny'],
        'elder_care' => ['बुज़ुर्ग देखभाल', 'Elder Care Attendant'],
        'home_nurse' => ['होम नर्स', 'Home Nurse'],
        'physiotherapist' => ['फ़िज़ियोथेरेपिस्ट (होम विज़िट)', 'Physiotherapist (Home Visit)'],
        'security_guard' => ['सिक्योरिटी गार्ड / बाउंसर', 'Security Guard / Bouncer'],
        'gardener' => ['माली', 'Gardener (Mali)'],
        'photographer' => ['फ़ोटोग्राफ़र / वीडियोग्राफ़र', 'Photographer / Videographer'],
        'caterer' => ['कैटरर / हलवाई', 'Caterer / Halwai'],
        'tent_decorator' => ['टेंट / डेकोरेशन', 'Tent & Decoration'],
        'dj_sound' => ['DJ / साउंड', 'DJ & Sound'],
        'event_helper' => ['इवेंट हेल्पर / मैनपावर', 'Event Helper / Manpower'],
        'pandit' => ['पंडित / पुजारी', 'Pandit / Priest'],
        'interior' => ['इंटीरियर डिज़ाइनर', 'Interior Designer'],
        'furniture_repair' => ['फ़र्नीचर रिपेयर / पॉलिश', 'Furniture Repair & Polish'],
        'curtain_blinds' => ['पर्दे / ब्लाइंड्स फिटर', 'Curtain & Blinds Fitter'],
        'courier' => ['कूरियर / डिलीवरी', 'Courier & Delivery'],
        'pet_care' => ['पेट केयर / डॉग वॉकर', 'Pet Care / Dog Walker'],
        'vet' => ['पशु चिकित्सक (होम विज़िट)', 'Veterinary (Home Visit)'],
        'lab_test_home' => ['घर पर ब्लड टेस्ट', 'Home Blood Test / Lab Collection'],
        'ca_tax' => ['CA / टैक्स / GST फ़ाइलिंग', 'CA / Tax / GST Filing'],
        'advocate' => ['वकील / नोटरी', 'Advocate / Notary'],
        'property_dealer' => ['प्रॉपर्टी डीलर', 'Property Dealer'],
        'other' => ['अन्य', 'Other'],
    ];

    /** Near Me services: provider listing and seeker contact pass – ₹150 + 18% GST, valid 3 months. */
    public const NEAR_ME_FEE = 177.00;
    /** Restaurant / chef job seekers: ₹250 + 18% GST, valid 3 months. */
    public const RESTAURANT_FEE = 295.00;

    private static function locationSection(array $title): array
    {
        return [
            'title' => $title,
            'fields' => [
                ['key' => 'geo', 'type' => 'geo', 'label' => ['मेरी लोकेशन', 'My location'], 'required' => false, 'full' => true,
                    'hint' => ['बटन दबाएँ – पिन कोड, शहर और राज्य अपने आप भर जाएँगे और ग्राहक दूरी के अनुसार आपको देखेंगे। या नीचे पता लिखें।', 'Tap the button – PIN, city and state fill in automatically and customers see you by distance. Or type your address below.']],
                self::text('address_line', ['मकान / दुकान नं., गली, इलाका', 'House / Shop No., Street, Area'], true, ['full' => true, 'max' => 255, 'autocomplete' => 'street-address']),
                self::text('village', ['मोहल्ला / कॉलोनी / गाँव', 'Locality / Colony / Village'], true, ['max' => 120]),
                self::text('city', ['शहर', 'City'], true, ['max' => 120, 'autocomplete' => 'address-level2']),
                self::text('district', ['ज़िला', 'District'], true, ['max' => 120]),
                ['key' => 'state', 'type' => 'state', 'label' => ['राज्य / केंद्र शासित प्रदेश', 'State / UT'], 'required' => true],
                ['key' => 'pincode', 'type' => 'pincode', 'label' => ['पिन कोड (ज़रूरी)', 'PIN Code (mandatory)'], 'required' => true, 'autocomplete' => 'postal-code',
                    'hint' => ['आपके पास की सेवाएँ इसी पिन कोड से ढूँढी जाती हैं।', 'Nearby services are matched using this PIN code.']],
                self::text('landmark', ['नज़दीकी लैंडमार्क', 'Nearest Landmark'], false),
            ],
        ];
    }

    private static function nearMeProvider(): array
    {
        return [
            'type' => 'nearpro',
            'prefix' => 'NMP',
            'side' => 'service',
            'icon' => '🛠️',
            'otp' => true,
            'multipart' => true,
            'hidden_from_hub' => true,
            'fee' => self::NEAR_ME_FEE,
            'validity_months' => 3,
            'max_categories' => 5,
            'title' => ['Near Me सेवा प्रदाता रजिस्ट्रेशन', 'Near Me Service Provider Registration'],
            'button' => ['प्लंबर, इलेक्ट्रीशियन, कारपेंटर, हेयर ड्रेसर, सैलून – सेवा प्रदाता के रूप में जुड़ें', 'Plumber, electrician, carpenter, hair dresser, salon – enrol as a service provider'],
            'intro' => ['अपने इलाके के ग्राहकों तक पहुँचें। ₹150 + GST (₹177) – 3 महीने के लिए। फोटो और लाइव सेल्फ़ी से पहचान सत्यापन ज़रूरी।', 'Reach customers near you. ₹150 + GST (₹177) for 3 months. Photo and a live selfie are mandatory for identity verification.'],
            'categories_label' => ['अन्य सेवाएँ (वैकल्पिक) – अधिकतम 5, 47,000+ की सूची से', 'Other services (optional) – up to 5, from a 47,000+ list'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['शुल्क ₹150 + 18% GST = ₹177, भुगतान की तारीख से 3 महीने तक मान्य।', 'Fee ₹150 + 18% GST = ₹177, valid for 3 months from payment.'],
                    ['भुगतान करने वाले ग्राहकों को आपका नाम / दुकान का नाम, मोबाइल नंबर और पता दिखाया जाएगा।', 'Your name / shop name, mobile number and address will be shown to paying customers.'],
                    ['अपनी साफ़ फोटो और लाइव सेल्फ़ी देना ज़रूरी है – Jobsence टीम पहचान की जाँच करेगी।', 'A clear photo and a live selfie are mandatory – the Jobsence team verifies your identity.'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                [
                    'title' => ['आपकी पहचान', 'Your Identity'],
                    'fields' => [
                        ['key' => 'profession', 'type' => 'select', 'label' => ['आपका मुख्य काम / प्रोफ़ेशन', 'Your main profession'], 'required' => true, 'full' => true,
                            'options' => self::NEAR_ME_PROFESSIONS,
                            'hint' => ['70+ प्रोफ़ेशन में से चुनें; और सेवाएँ नीचे “अन्य सेवाएँ” में जोड़ें।', 'Choose from 70+ professions; add more services below under “Other services”.']],
                        self::text('profession_other', ['अन्य हो तो प्रोफ़ेशन लिखें', 'If other, write your profession'], false, ['max' => 80]),
                        self::radio('provider_kind', ['आप कौन हैं', 'You are'], [
                            'individual' => ['व्यक्तिगत कारीगर / प्रोफ़ेशनल', 'Individual worker / professional'],
                            'shop' => ['दुकान / सैलून / पार्लर', 'Shop / salon / parlour'],
                            'agency' => ['एजेंसी / कंपनी', 'Agency / company'],
                        ], true, ['full' => true]),
                        self::text('full_name', ['पूरा नाम (आधार के अनुसार)', 'Full Name (as per Aadhaar)'], true, ['max' => 150, 'autocomplete' => 'name']),
                        self::text('business_name', ['दुकान / सैलून / संस्था का नाम (यदि हो)', 'Shop / Salon / Organisation Name (if any)'], false, ['max' => 150]),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर (ग्राहकों को दिखेगा)', 'Mobile Number (shown to customers)'], 'required' => true, 'autocomplete' => 'tel'],
                        ['key' => 'whatsapp', 'type' => 'mobile', 'label' => ['WhatsApp नंबर (अलग हो तो)', 'WhatsApp Number (if different)'], 'required' => false],
                        ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल', 'Email ID'], 'required' => true, 'autocomplete' => 'email'],
                        ['key' => 'aadhaar', 'type' => 'aadhaar', 'label' => ['आधार नंबर', 'Aadhaar Number'], 'required' => true,
                            'hint' => ['सुरक्षा के लिए हम केवल आख़िरी 4 अंक सेव करते हैं।', 'For your safety we store only the last 4 digits.']],
                    ],
                ],
                [
                    'title' => ['फोटो और लाइव सेल्फ़ी (ज़रूरी)', 'Photo & Live Selfie (mandatory)'],
                    'fields' => [
                        ['key' => 'photo', 'type' => 'image', 'label' => ['आपकी साफ़ फोटो / दुकान की फोटो (JPG/PNG, अधिकतम 5MB)', 'Your clear photo / shop photo (JPG/PNG, max 5MB)'], 'required' => true,
                            'hint' => ['यही फोटो ग्राहकों को दिखेगी।', 'This photo is shown to customers.']],
                        ['key' => 'selfie', 'type' => 'selfie', 'label' => ['लाइव सेल्फ़ी लें', 'Take a live selfie'], 'required' => true,
                            'hint' => ['सेल्फ़ी केवल Jobsence टीम सत्यापन के लिए देखेगी – ग्राहकों को नहीं दिखेगी।', 'The selfie is seen only by the Jobsence team for verification – never shown to customers.']],
                    ],
                ],
                [
                    'title' => ['सेवा की जानकारी', 'Service Details'],
                    'fields' => [
                        self::categories(false),
                        self::radio('experience', ['अनुभव', 'Experience'], [
                            '0_1' => ['1 साल से कम', 'Less than 1 year'], '1_3' => ['1–3 साल', '1–3 years'], '3_5' => ['3–5 साल', '3–5 years'],
                            '5_10' => ['5–10 साल', '5–10 years'], '10_plus' => ['10+ साल', '10+ years'],
                        ], true, ['full' => true]),
                        self::number('visit_charge', ['न्यूनतम विज़िट / सर्विस चार्ज (₹)', 'Minimum visit / service charge (₹)'], true, 0, 100000),
                        self::radio('service_radius', ['आप कितनी दूर तक सेवा देते हैं', 'How far do you travel for work'], [
                            '2' => ['2 किमी', '2 km'], '5' => ['5 किमी', '5 km'], '10' => ['10 किमी', '10 km'], '25' => ['25 किमी', '25 km'], 'shop' => ['केवल दुकान पर', 'At my shop only'],
                        ], true, ['full' => true]),
                        self::checkboxes('available_days', ['उपलब्ध दिन', 'Available Days'], [
                            'mon' => ['सोम', 'Mon'], 'tue' => ['मंगल', 'Tue'], 'wed' => ['बुध', 'Wed'], 'thu' => ['गुरु', 'Thu'],
                            'fri' => ['शुक्र', 'Fri'], 'sat' => ['शनि', 'Sat'], 'sun' => ['रवि', 'Sun'],
                        ], true),
                        self::text('timing', ['काम का समय (जैसे सुबह 9 से शाम 7)', 'Working hours (e.g. 9am – 7pm)'], true, ['max' => 100]),
                        self::textarea('about_service', ['अपनी सेवा के बारे में (वैकल्पिक)', 'About your service (optional)'], false),
                    ],
                ],
                self::locationSection(['आपका पता और पिन कोड', 'Your Address & PIN Code']),
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी, फोटो और सेल्फ़ी सही और मेरी अपनी है।', 'The information, photo and selfie provided are true and my own.'],
                ['₹177 (₹150 + GST) का शुल्क वापसी योग्य नहीं है; रजिस्ट्रेशन भुगतान से 3 महीने तक मान्य है।', 'The fee of ₹177 (₹150 + GST) is non-refundable; registration is valid for 3 months from payment.'],
                ['मैं सहमत हूँ कि भुगतान करने वाले ग्राहकों को मेरा नाम, मोबाइल नंबर और पता दिखाया जाए।', 'I agree that my name, mobile number and address are shown to paying customers.'],
                ['मैं ग्राहकों से उचित शुल्क लूँगा/लूँगी और अच्छा व्यवहार करूँगा/करूँगी।', 'I will charge customers fairly and behave properly.'],
                self::platformDisclaimer(),
                self::notGovt(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपकी फोटो और सेल्फ़ी से पहचान की जाँच करेगी; सत्यापन के बाद आपकी प्रोफ़ाइल पर “Verified” दिखेगा।', 'The Jobsence team will verify your identity from your photo and selfie; after verification your profile shows “Verified”.'],
                ['आपके इलाके (पिन कोड) में सेवा ढूँढने वाले ग्राहक आपको सीधे कॉल / WhatsApp करेंगे।', 'Customers looking for your service in your PIN code area will call / WhatsApp you directly.'],
            ],
        ];
    }

    private static function nearMeSeeker(): array
    {
        return [
            'type' => 'nearseek',
            'prefix' => 'NMS',
            'side' => 'candidate',
            'icon' => '📍',
            'otp' => true,
            'hidden_from_hub' => true,
            'fee' => self::NEAR_ME_FEE,
            'validity_months' => 3,
            'max_categories' => 5,
            'title' => ['Near Me सेवा चाहिए – रजिस्ट्रेशन', 'Near Me – Find a Service Provider'],
            'button' => ['अपने पास प्लंबर, इलेक्ट्रीशियन, कारपेंटर, सैलून ढूँढें', 'Find a plumber, electrician, carpenter or salon near you'],
            'intro' => ['₹150 + GST (₹177) प्लेटफ़ॉर्म शुल्क देकर 3 महीने तक अपने पिन कोड के पास के सत्यापित सेवा प्रदाता चुनें; समझौते पर दोनों के साइन होते ही उनका मोबाइल नंबर और पता दिखेगा।', 'Pay a platform fee of ₹150 + GST (₹177) and choose verified service providers near your PIN code for 3 months; their mobile number and address appear once both of you sign the agreement.'],
            'categories_label' => ['आपको कौन सी सेवा चाहिए – अधिकतम 5', 'Services you need – up to 5'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['प्लेटफ़ॉर्म शुल्क ₹150 + 18% GST = ₹177, भुगतान से 3 महीने तक मान्य।', 'Platform fee ₹150 + 18% GST = ₹177, valid for 3 months from payment.'],
                    ['सेवा का भुगतान आप सीधे सेवा प्रदाता को करेंगे – Jobsence केवल संपर्क जोड़ता है।', 'You pay the service provider directly for the work – Jobsence only connects you.'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                [
                    'title' => ['आपकी जानकारी', 'Your Details'],
                    'fields' => [
                        self::text('full_name', ['पूरा नाम', 'Full Name'], true, ['max' => 150, 'autocomplete' => 'name']),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर', 'Mobile Number'], 'required' => true, 'autocomplete' => 'tel'],
                        ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल', 'Email ID'], 'required' => true, 'autocomplete' => 'email',
                            'hint' => ['इसी ईमेल पर OTP और आपका एक्सेस लिंक आएगा।', 'Your OTP and access link will be sent here.']],
                    ],
                ],
                self::locationSection(['आपका पता और पिन कोड', 'Your Address & PIN Code']),
                [
                    'title' => ['आपको क्या चाहिए', 'What You Need'],
                    'fields' => [
                        self::categories(true),
                        self::radio('when_needed', ['कब चाहिए', 'When do you need it'], [
                            'today' => ['आज / तुरंत', 'Today / urgent'], 'week' => ['इस हफ़्ते', 'This week'], 'flexible' => ['कभी भी', 'Flexible'],
                        ], true, ['full' => true]),
                        self::textarea('need_note', ['काम के बारे में संक्षेप में (वैकल्पिक)', 'Briefly describe the work (optional)'], false),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['₹177 (₹150 + GST) प्लेटफ़ॉर्म शुल्क वापसी योग्य नहीं है; एक्सेस भुगतान से 3 महीने तक मान्य है।', 'The platform fee of ₹177 (₹150 + GST) is non-refundable; access is valid for 3 months from payment.'],
                ['मैं सेवा प्रदाताओं की जानकारी केवल अपने काम के लिए उपयोग करूँगा/करूँगी और किसी के साथ साझा या बेचूँगा/बेचूँगी नहीं।', 'I will use service providers’ details only for my own work and will not share or sell them.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['“Near Me” पेज पर पिन कोड डालें, प्रदाता चुनें और ईमेल OTP से समझौता साइन करें – प्रदाता के साइन करते ही नंबर दिखेगा।', 'Enter your PIN code on the “Near Me” page, choose a provider and sign the agreement with an email OTP – the number appears as soon as the provider signs.'],
                ['काम शुरू करने से पहले कीमत तय कर लें और पहचान देख लें।', 'Agree on the price and check identity before work starts.'],
            ],
        ];
    }

    private static function restaurantJobs(): array
    {
        return [
            'type' => 'hospitality',
            'prefix' => 'RCJ',
            'side' => 'candidate',
            'icon' => '👨‍🍳',
            'multipart' => true,
            'fee' => self::RESTAURANT_FEE,
            'validity_months' => 3,
            'max_categories' => 5,
            'title' => ['रेस्टोरेंट और शेफ जॉब रजिस्ट्रेशन', 'Restaurant & Chef Jobs Registration'],
            'button' => ['रेस्टोरेंट, होटल और शेफ की नौकरी – पास में, दूसरे शहर में या विदेश में', 'Restaurant, hotel & chef jobs – near you, in another city or abroad'],
            'intro' => ['शेफ, कुक, तंदूर, वेटर, कैप्टन, बारटेंडर, स्टीवर्ड – पास में, दूसरे शहर में या विदेश में नौकरी के लिए पहले रजिस्टर करें। ₹250 + 18% GST (₹295), 3 महीने के लिए।', 'Chef, cook, tandoor, waiter, captain, bartender, steward – enrol first for jobs near you, in another city or abroad. ₹250 + 18% GST (₹295) for 3 months.'],
            'categories_label' => ['आप कौन सा काम चाहते हैं – अधिकतम 5 (जैसे Tandoor Chef, Commis Chef, Waiter)', 'Roles you want – up to 5 (e.g. Tandoor Chef, Commis Chef, Waiter)'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['एकमुश्त शुल्क ₹250 + 18% GST = ₹295 – भुगतान से 3 महीने तक मान्य।', 'One-time fee ₹250 + 18% GST = ₹295 – valid for 3 months from payment.'],
                    ['विदेश की नौकरी के लिए केवल भारत सरकार के eMigrate पर पंजीकृत रिक्रूटिंग एजेंट से ही जाएँ; किसी को नकद न दें।', 'For jobs abroad, go only through recruiting agents registered on the Government of India eMigrate portal; never pay anyone in cash.'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                self::personalSection([]),
                self::addressSection(false),
                [
                    'title' => ['अनुभव और कुकिंग', 'Experience & Cuisine'],
                    'fields' => [
                        self::qualification(),
                        self::radio('experience', ['कुल अनुभव', 'Total Experience'], [
                            'fresher' => ['फ्रेशर', 'Fresher'], '0_1' => ['1 साल से कम', 'Less than 1 year'], '1_3' => ['1–3 साल', '1–3 years'],
                            '3_5' => ['3–5 साल', '3–5 years'], '5_10' => ['5–10 साल', '5–10 years'], '10_plus' => ['10+ साल', '10+ years'],
                        ], true, ['full' => true]),
                        self::checkboxes('cuisines', ['आप कौन सा खाना बनाते हैं / किस काम में माहिर हैं', 'Cuisines / specialisation'], [
                            'north_indian' => ['नॉर्थ इंडियन', 'North Indian'], 'south_indian' => ['साउथ इंडियन', 'South Indian'], 'chinese' => ['चाइनीज़', 'Chinese'],
                            'tandoor' => ['तंदूर', 'Tandoor'], 'mughlai' => ['मुग़लई', 'Mughlai'], 'continental' => ['कॉन्टिनेंटल', 'Continental'],
                            'italian' => ['इटैलियन / पिज़्ज़ा', 'Italian / Pizza'], 'bakery' => ['बेकरी / पेस्ट्री', 'Bakery / Pastry'], 'sweets' => ['हलवाई / मिठाई', 'Halwai / Sweets'],
                            'arabic' => ['अरबी / शवरमा', 'Arabic / Shawarma'], 'thai' => ['थाई / एशियन', 'Thai / Asian'], 'fast_food' => ['फ़ास्ट फ़ूड', 'Fast Food'],
                            'service' => ['सर्विस (वेटर / कैप्टन)', 'Service (waiter / captain)'], 'bar' => ['बार / बारटेंडर', 'Bar / bartender'], 'other' => ['अन्य', 'Other'],
                        ], true),
                        self::text('current_role', ['वर्तमान / पिछला पद और होटल / रेस्टोरेंट', 'Current / last role and hotel / restaurant'], false),
                    ],
                ],
                [
                    'title' => ['आप कहाँ काम करना चाहते हैं', 'Where You Want to Work'],
                    'fields' => [
                        self::categories(true),
                        self::checkboxes('work_where', ['काम कहाँ चाहिए', 'Work location'], [
                            'near_me' => ['मेरे पास', 'Near me'], 'other_city' => ['दूसरे शहर में', 'In another city'], 'abroad' => ['विदेश में', 'Abroad'],
                        ], true),
                        self::text('preferred_location', ['पसंदीदा शहर', 'Preferred city / cities'], false, ['max' => 190]),
                        self::text('abroad_countries', ['विदेश में कौन से देश (यदि चुना हो)', 'Countries abroad (if chosen)'], false, ['max' => 190]),
                        self::radio('passport', ['पासपोर्ट', 'Passport'], [
                            'yes' => ['है', 'Yes, I have'], 'applied' => ['आवेदन किया है', 'Applied'], 'no' => ['नहीं है', 'No'],
                        ], true),
                        self::number('expected_salary', ['अपेक्षित वेतन (₹ प्रति माह)', 'Expected salary (₹ per month)'], false, 0, 10000000),
                        self::file('resume', ['रिज़्यूमे (वैकल्पिक – PDF/DOC, अधिकतम 5MB)', 'Resume (optional – PDF/DOC, max 5MB)']),
                        ['key' => 'photo', 'type' => 'image', 'label' => ['आपकी फोटो (वैकल्पिक)', 'Your photo (optional)'], 'required' => false],
                    ],
                ],
            ],
            'declaration' => array_merge(self::commonJobDeclaration('नौकरी', 'job', '₹295 (₹250 + GST)'), [
                ['मेरा रजिस्ट्रेशन भुगतान की तारीख से 3 महीने तक मान्य रहेगा।', 'My registration is valid for 3 months from the date of payment.'],
                ['Jobsence कोई रिक्रूटिंग एजेंट नहीं है और विदेश भेजने के लिए कोई शुल्क नहीं लेता।', 'Jobsence is not a recruiting agent and charges nothing for sending anyone abroad.'],
                self::platformDisclaimer(),
                self::notGovt(),
            ]),
            'next_steps' => self::commonJobNextSteps('नौकरी', 'job'),
        ];
    }

    /**
     * Healthcare pricing (total incl. 18% GST).
     * Candidate: doctor ₹4,500+GST · front desk / sales & marketing ₹1,500+GST · sweeper ₹500+GST · other ₹600+GST (1 month).
     * Hospital / clinic: doctor ₹11,000+GST · front desk / sales & marketing ₹1,500+GST · sweeper ₹5,000+GST · other ₹600+GST (15 days).
     */
    public const HEALTH_ROLES = [
        'doctor' => ['डॉक्टर', 'Doctor'],
        'front_desk' => ['फ्रंट डेस्क / रिसेप्शन', 'Front Desk / Reception'],
        'sales_marketing' => ['सेल्स और मार्केटिंग', 'Sales & Marketing'],
        'sweeper' => ['स्वीपर / हाउसकीपिंग', 'Sweeper / Housekeeping'],
        'other' => ['अन्य अस्पताल स्टाफ़ (नर्स, टेक्नीशियन, फ़ार्मासिस्ट, वार्ड बॉय…)', 'Other hospital staff (nurse, technician, pharmacist, ward boy…)'],
    ];
    public const HEALTH_FEES_CANDIDATE = ['doctor' => 5310.00, 'front_desk' => 1770.00, 'sales_marketing' => 1770.00, 'sweeper' => 590.00, 'other' => 708.00];
    public const HEALTH_FEES_HOSPITAL = ['doctor' => 12980.00, 'front_desk' => 1770.00, 'sales_marketing' => 1770.00, 'sweeper' => 5900.00, 'other' => 708.00];

    /** Part-time talent pass for hirers: ₹5,000 + 18% GST, 2 days of resume / keyword search. */
    public const HIRER_FEE = 5900.00;

    /** Senior citizens (59+): full-time / part-time work ₹500 + 18% GST once a year; community service free. */
    public const SENIOR_FEES = ['full_time' => 590.00, 'part_time' => 590.00, 'community' => 0.00];
    public const SENIOR_MIN_AGE = 59;
    /** Organisations offering a senior-citizen job: ₹500 per job (GST included), job live for 7 days. */
    public const SENIOR_JOB_FEE = 500.00;

    private static function seniorJobs(): array
    {
        return [
            'type' => 'senior',
            'prefix' => 'SNR',
            'side' => 'candidate',
            'icon' => '👴',
            'otp' => true,
            'multipart' => true,
            'resume_search' => true,
            'fee_by' => ['work_type', self::SENIOR_FEES],
            'max_categories' => 5,
            'title' => ['वरिष्ठ नागरिक (59+) – फ़ुल-टाइम / पार्ट-टाइम काम और सामुदायिक सेवा', 'Senior Citizens (59+) – Full-time / Part-time Work & Community Service'],
            'button' => ['वरिष्ठ नागरिक (59+) – काम या सामुदायिक सेवा के लिए रजिस्टर करें', 'Senior citizens (59+) – register for work or community service'],
            'intro' => ['अनुभव कभी रिटायर नहीं होता। 59 वर्ष या उससे अधिक उम्र के लोग फ़ुल-टाइम या पार्ट-टाइम काम के लिए, या समाज सेवा के लिए रजिस्टर करें – कंपनियाँ, स्कूल, अस्पताल और NGO आपके अनुभव से जुड़ेंगे।', 'Experience never retires. People aged 59 and above can register for full-time or part-time work, or for community service – companies, schools, hospitals and NGOs connect with your experience.'],
            'categories_label' => ['आप क्या काम कर सकते हैं – अधिकतम 5 (जैसे Accountant, Teacher, Consultant, Supervisor)', 'Work you can do – up to 5 (e.g. Accountant, Teacher, Consultant, Supervisor)'],
            'info' => [
                'title' => ['शुल्क', 'Fees'],
                'points' => [
                    ['फ़ुल-टाइम या पार्ट-टाइम काम: ₹500 + 18% GST = ₹590, साल में एक बार (12 महीने मान्य)', 'Full-time or part-time work: ₹500 + 18% GST = ₹590, once a year (valid 12 months)'],
                    ['सामुदायिक सेवा (समाज सेवा / स्वयंसेवा): पूरी तरह मुफ़्त', 'Community service (social service / volunteering): completely free'],
                    ['न्यूनतम आयु 59 वर्ष – जन्म तिथि से जाँची जाती है', 'Minimum age 59 years – checked from your date of birth'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                self::personalSection(['dob_mode' => 'senior']),
                self::addressSection(false),
                [
                    'title' => ['आपातकाल के लिए दो अपनों के संपर्क और पते का प्रमाण', 'Two Emergency Contacts (near & dear) and Address Proof'],
                    'fields' => [
                        self::text('em1_name', ['पहला संपर्क – नाम', 'First contact – name'], true, ['max' => 150]),
                        self::text('em1_relation', ['आपसे रिश्ता (बेटा, बेटी, पति/पत्नी, भाई…)', 'Relation to you (son, daughter, spouse, brother…)'], true, ['max' => 60]),
                        ['key' => 'em1_mobile', 'type' => 'mobile', 'label' => ['पहले संपर्क का मोबाइल नंबर', 'First contact – mobile number'], 'required' => true, 'differs_from' => ['mobile']],
                        ['key' => 'em1_address', 'type' => 'textarea', 'label' => ['पहले संपर्क का पूरा पता', 'First contact – full address'], 'required' => true, 'full' => true, 'max' => 500],
                        self::text('em2_name', ['दूसरा संपर्क – नाम', 'Second contact – name'], true, ['max' => 150]),
                        self::text('em2_relation', ['आपसे रिश्ता', 'Relation to you'], true, ['max' => 60]),
                        ['key' => 'em2_mobile', 'type' => 'mobile', 'label' => ['दूसरे संपर्क का मोबाइल नंबर', 'Second contact – mobile number'], 'required' => true, 'differs_from' => ['mobile', 'em1_mobile']],
                        ['key' => 'em2_address', 'type' => 'textarea', 'label' => ['दूसरे संपर्क का पूरा पता', 'Second contact – full address'], 'required' => true, 'full' => true, 'max' => 500],
                        ['key' => 'address_proof', 'type' => 'file', 'label' => ['आपके पते का प्रमाण – आधार, वोटर आईडी, राशन कार्ड, पासपोर्ट या बिजली / पानी / फ़ोन बिल (PDF / JPG / PNG, अधिकतम 5MB)', 'Your address proof – Aadhaar, voter ID, ration card, passport or electricity / water / phone bill (PDF / JPG / PNG, max 5MB)'], 'required' => true, 'full' => true, 'allow_images' => true,
                            'hint' => ['आपात स्थिति में Jobsence या काम देने वाली संस्था इन संपर्कों से बात कर सकती है।', 'In an emergency Jobsence or the organisation may contact these people.']],
                    ],
                ],
                [
                    'title' => ['आप क्या चाहते हैं', 'What You Are Looking For'],
                    'fields' => [
                        self::radio('work_type', ['काम का प्रकार', 'Type of work'], [
                            'full_time' => ['फ़ुल-टाइम काम – ₹500 + GST / साल', 'Full-time work – ₹500 + GST / year'],
                            'part_time' => ['पार्ट-टाइम काम – ₹500 + GST / साल', 'Part-time work – ₹500 + GST / year'],
                            'community' => ['सामुदायिक सेवा – मुफ़्त', 'Community service – free'],
                        ], true, ['full' => true]),
                        self::categories(true),
                        self::text('retired_from', ['आप कहाँ से रिटायर हुए (विभाग / कंपनी / पद)', 'Retired from (department / company / post)'], false, ['max' => 190]),
                        self::radio('experience', ['कुल अनुभव', 'Total experience'], [
                            '10_20' => ['10–20 साल', '10–20 years'], '20_30' => ['20–30 साल', '20–30 years'], '30_plus' => ['30+ साल', '30+ years'],
                        ], true, ['full' => true]),
                        self::radio('hours', ['कितना समय दे सकते हैं', 'Time you can give'], [
                            'full_day' => ['पूरा दिन', 'Full day'], 'half_day' => ['आधा दिन', 'Half day'], 'few_hours' => ['रोज़ कुछ घंटे', 'A few hours a day'],
                            'weekly' => ['हफ़्ते में कुछ दिन', 'A few days a week'], 'from_home' => ['घर से', 'From home'],
                        ], true, ['full' => true]),
                        ['key' => 'about_me', 'type' => 'textarea', 'label' => ['अपने अनुभव और स्किल के बारे में लिखें', 'About your experience and skills'], 'required' => true, 'full' => true, 'max' => 2000],
                        self::text('preferred_location', ['पसंदीदा शहर / इलाका', 'Preferred city / area'], false, ['max' => 190]),
                        self::number('expected_salary', ['अपेक्षित मानदेय (₹ प्रति माह, सामुदायिक सेवा के लिए खाली छोड़ें)', 'Expected pay (₹ per month, leave empty for community service)'], false, 0, 10000000),
                        self::file('resume', ['रिज़्यूमे / अनुभव प्रमाणपत्र (PDF/DOC, अधिकतम 5MB, वैकल्पिक)', 'Resume / experience certificate (PDF/DOC, max 5MB, optional)']),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है और मेरी आयु 59 वर्ष या उससे अधिक है।', 'The information I have given is true and I am 59 years of age or older.'],
                ['काम के लिए शुल्क वापसी योग्य नहीं है और भुगतान से 12 महीने तक मान्य है। सामुदायिक सेवा मुफ़्त है।', 'The fee for work is non-refundable and valid for 12 months from payment. Community service is free.'],
                ['मैं सहमत हूँ कि रजिस्टर्ड संस्थाएँ मेरा प्रोफ़ाइल और संपर्क देख सकें।', 'I agree that registered organisations can see my profile and contact details.'],
                self::platformDisclaimer(),
                self::notGovt(),
            ],
            'next_steps' => [
                ['आपका प्रोफ़ाइल वरिष्ठ नागरिकों को काम देने वाली संस्थाओं को दिखेगा।', 'Your profile is shown to organisations that want to engage senior citizens.'],
                ['संस्थाएँ आपसे सीधे कॉल / ईमेल से संपर्क करेंगी।', 'Organisations will contact you directly by call / email.'],
            ],
        ];
    }

    /** Organisations that want to engage senior citizens (work or community service) – registration free. */
    private static function seniorHiring(): array
    {
        return [
            'type' => 'seniorhire',
            'prefix' => 'SNH',
            'side' => 'provider',
            'icon' => '🤝',
            'otp' => true,
            // Employers pay (user, 2026-10-06): a nominal ₹500 per senior-citizen job, live for 7 days (PortalRegistration::VALIDITY).
            'fee' => self::SENIOR_JOB_FEE,
            'max_categories' => 5,
            'title' => ['वरिष्ठ नागरिकों के लिए नौकरी दें – ₹500 प्रति नौकरी, 7 दिन', 'Offer a Job to Senior Citizens – ₹500 per job, 7 days'],
            'button' => ['कंपनी / स्कूल / अस्पताल / NGO – वरिष्ठ नागरिकों को काम दें', 'Company / school / hospital / NGO – engage senior citizens'],
            'intro' => ['अनुभवी वरिष्ठ नागरिकों (59+) को फ़ुल-टाइम, पार्ट-टाइम या सामुदायिक सेवा के लिए जोड़ें। हर नौकरी के लिए नाममात्र शुल्क ₹500 (GST सहित) – नौकरी 7 दिन तक वरिष्ठ नागरिकों को दिखेगी।', 'Engage experienced senior citizens (59+) for full-time, part-time or community-service roles. A nominal ₹500 (incl. GST) per job – the job is shown to senior citizens for 7 days.'],
            'info' => [
                'title' => ['शुल्क', 'Fee'],
                'points' => [
                    ['हर नौकरी के लिए ₹500 (GST सहित), 7 दिन के लिए। एक और नौकरी के लिए यह फ़ॉर्म दोबारा भरें।', '₹500 (including GST) per job, for 7 days. Fill this form again for another job.'],
                    ['वरिष्ठ नागरिकों के लिए रजिस्ट्रेशन मुफ़्त है – वरिष्ठ नागरिकों से कोई शुल्क न माँगें।', 'Registration is free for senior citizens – never ask them for any fee.'],
                    self::platformDisclaimer(),
                ],
            ],
            'categories_label' => ['आपको किस काम के लिए लोग चाहिए – अधिकतम 5', 'Work you need people for – up to 5'],
            'sections' => [
                [
                    'title' => ['संस्था', 'Organisation'],
                    'fields' => [
                        self::text('business_name', ['संस्था / कंपनी का नाम', 'Organisation / company name'], true, ['max' => 150]),
                        self::radio('org_kind', ['संस्था का प्रकार', 'Type of organisation'], [
                            'company' => ['कंपनी', 'Company'], 'proprietorship' => ['प्रोप्राइटरशिप / दुकान', 'Proprietorship / shop'], 'school' => ['स्कूल / कॉलेज', 'School / college'],
                            'hospital' => ['अस्पताल / क्लिनिक', 'Hospital / clinic'], 'ngo' => ['NGO / ट्रस्ट', 'NGO / trust'], 'rwa' => ['RWA / सोसाइटी', 'RWA / housing society'], 'other' => ['अन्य', 'Other'],
                        ], true, ['full' => true]),
                        ['key' => 'gstin', 'type' => 'gst', 'label' => ['GST नंबर', 'GST Number'], 'required' => false],
                        self::text('website', ['वेबसाइट (यदि हो)', 'Website (if any)'], false, ['max' => 190]),
                    ],
                ],
                self::personalSection(['name_label' => ['संपर्क व्यक्ति का नाम', 'Contact Person Name'], 'dob' => false, 'gender' => false, 'aadhaar_required' => false]),
                self::locationSection(['कार्यस्थल का पता', 'Workplace address']),
                [
                    'title' => ['आपको क्या चाहिए', 'What You Need'],
                    'fields' => [
                        self::radio('work_type', ['काम का प्रकार', 'Type of work'], [
                            'full_time' => ['फ़ुल-टाइम', 'Full-time'], 'part_time' => ['पार्ट-टाइम', 'Part-time'], 'community' => ['सामुदायिक सेवा (स्वयंसेवा)', 'Community service (volunteer)'],
                        ], true, ['full' => true]),
                        self::categories(true),
                        self::number('vacancies', ['कितने लोग चाहिए', 'How many people'], false, 1, 10000),
                        self::text('pay', ['मानदेय / वेतन (सामुदायिक सेवा के लिए खाली छोड़ें)', 'Pay / honorarium (leave empty for community service)'], false, ['max' => 120]),
                        ['key' => 'about_need', 'type' => 'textarea', 'label' => ['काम का विवरण, समय और जगह', 'Describe the work, timings and place'], 'required' => true, 'full' => true, 'max' => 2000],
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['हम वरिष्ठ नागरिकों से कोई शुल्क या डिपॉज़िट नहीं माँगेंगे और उनसे सम्मानजनक व्यवहार करेंगे।', 'We will not ask senior citizens for any fee or deposit and will treat them with respect.'],
                ['₹500 का शुल्क प्रति नौकरी है, 7 दिन तक मान्य और वापसी योग्य नहीं।', 'The ₹500 fee is per job, valid for 7 days and non-refundable.'],
                self::platformDisclaimer(),
                self::notGovt(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपका रजिस्ट्रेशन जाँचेगी और वरिष्ठ नागरिकों के प्रोफ़ाइल से मिलाएगी।', 'The Jobsence team will check your registration and match it with senior citizens’ profiles.'],
            ],
        ];
    }

    /** Role options with the fee in the label, e.g. "Doctor – ₹4,500 + GST". */
    private static function pricedRoles(array $fees): array
    {
        $out = [];
        foreach (self::HEALTH_ROLES as $k => [$hi, $en]) {
            $base = number_format($fees[$k] / 1.18, 0);
            $out[$k] = ["{$hi} – ₹{$base} + GST", "{$en} – ₹{$base} + GST"];
        }
        return $out;
    }

    private static function healthcareJobs(): array
    {
        return [
            'type' => 'healthcare',
            'prefix' => 'HCJ',
            'side' => 'candidate',
            'icon' => '🩺',
            'otp' => true,
            'multipart' => true,
            'resume_search' => true,
            'fee_by' => ['health_role', self::HEALTH_FEES_CANDIDATE],
            'max_categories' => 5,
            'title' => ['डॉक्टर और अस्पताल स्टाफ़ जॉब रजिस्ट्रेशन', 'Doctor & Hospital Staff Job Registration'],
            'button' => ['डॉक्टर, नर्स, फ्रंट डेस्क, अस्पताल स्टाफ़ – नौकरी के लिए रजिस्टर करें', 'Doctors, nurses, front desk & hospital staff – register for jobs'],
            'intro' => ['अस्पताल, क्लिनिक और नर्सिंग होम की नौकरियाँ। आपका संक्षिप्त विवरण और रिज़्यूमे के कीवर्ड अस्पतालों की ज़रूरत से मिलाए जाते हैं। रजिस्ट्रेशन 1 महीने के लिए।', 'Jobs in hospitals, clinics and nursing homes. Keywords from your brief and resume are matched with what hospitals need. Registration is valid for 1 month.'],
            'categories_label' => ['विशेषज्ञता / पद – अधिकतम 5 (जैसे MD Medicine, Staff Nurse, Lab Technician)', 'Specialisation / post – up to 5 (e.g. MD Medicine, Staff Nurse, Lab Technician)'],
            'info' => [
                'title' => ['शुल्क (भूमिका के अनुसार, 1 महीना)', 'Fees (by role, 1 month)'],
                'points' => [
                    ['डॉक्टर: ₹4,500 + GST = ₹5,310', 'Doctor: ₹4,500 + GST = ₹5,310'],
                    ['फ्रंट डेस्क / सेल्स और मार्केटिंग: ₹1,500 + GST = ₹1,770', 'Front desk / Sales & marketing: ₹1,500 + GST = ₹1,770'],
                    ['स्वीपर / हाउसकीपिंग: ₹500 + GST = ₹590 (एक बार)', 'Sweeper / Housekeeping: ₹500 + GST = ₹590 (one-time)'],
                    ['अन्य अस्पताल स्टाफ़: ₹600 + GST = ₹708', 'Other hospital staff: ₹600 + GST = ₹708'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                self::personalSection([]),
                self::addressSection(false),
                [
                    'title' => ['आपकी भूमिका', 'Your Role'],
                    'fields' => [
                        self::radio('health_role', ['आप किस भूमिका के लिए रजिस्टर कर रहे हैं', 'Role you are registering for'], self::pricedRoles(self::HEALTH_FEES_CANDIDATE), true, ['full' => true]),
                        self::categories(true),
                        self::qualification(),
                        self::text('medical_reg_no', ['मेडिकल / नर्सिंग काउंसिल रजिस्ट्रेशन नं. (डॉक्टर / नर्स के लिए)', 'Medical / Nursing Council registration no. (for doctors / nurses)'], false, ['max' => 60]),
                        self::radio('experience', ['कुल अनुभव', 'Total Experience'], [
                            'fresher' => ['फ्रेशर', 'Fresher'], '0_1' => ['1 साल से कम', 'Less than 1 year'], '1_3' => ['1–3 साल', '1–3 years'],
                            '3_5' => ['3–5 साल', '3–5 years'], '5_10' => ['5–10 साल', '5–10 years'], '10_plus' => ['10+ साल', '10+ years'],
                        ], true, ['full' => true]),
                        ['key' => 'about_me', 'type' => 'textarea', 'label' => ['संक्षिप्त विवरण – आपके स्किल, विभाग, शिफ़्ट (यही कीवर्ड मिलाए जाएँगे)', 'Brief – your skills, departments, shifts (these keywords are matched)'], 'required' => true, 'full' => true, 'max' => 2000],
                        self::text('preferred_location', ['पसंदीदा शहर', 'Preferred city / cities'], false, ['max' => 190]),
                        self::number('expected_salary', ['अपेक्षित वेतन (₹ प्रति माह)', 'Expected salary (₹ per month)'], false, 0, 10000000),
                        self::file('resume', ['रिज़्यूमे (PDF/DOC, अधिकतम 5MB) – इसके कीवर्ड भी मिलाए जाते हैं', 'Resume (PDF/DOC, max 5MB) – its keywords are matched too']),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['शुल्क वापसी योग्य नहीं है और रजिस्ट्रेशन भुगतान से 1 महीने तक मान्य है।', 'The fee is non-refundable and the registration is valid for 1 month from payment.'],
                ['मैं सहमत हूँ कि भुगतान करने वाले अस्पताल / क्लिनिक मेरा प्रोफ़ाइल, रिज़्यूमे और संपर्क देख सकें।', 'I agree that paying hospitals / clinics can see my profile, resume and contact details.'],
                self::platformDisclaimer(),
                self::notGovt(),
            ],
            'next_steps' => [
                ['आपका प्रोफ़ाइल 1 महीने तक उन अस्पतालों को दिखेगा जिनकी ज़रूरत आपके कीवर्ड से मिलती है।', 'For 1 month your profile is shown to hospitals whose needs match your keywords.'],
                ['अस्पताल आपसे सीधे कॉल / ईमेल से संपर्क करेंगे।', 'Hospitals will contact you directly by call / email.'],
            ],
        ];
    }

    private static function hospitalHiring(): array
    {
        return [
            'type' => 'hospital',
            'prefix' => 'HSP',
            'side' => 'service',
            'icon' => '🏥',
            'otp' => true,
            'hidden_from_hub' => true,
            'fee_by' => ['hire_role', self::HEALTH_FEES_HOSPITAL],
            'max_categories' => 5,
            'title' => ['अस्पताल / क्लिनिक – स्टाफ़ चाहिए', 'Hospital / Clinic – Hire Doctors & Staff'],
            'button' => ['डॉक्टर और अस्पताल स्टाफ़ चाहिए? यहाँ रजिस्टर करें', 'Need doctors or hospital staff? Register here'],
            'intro' => ['अपनी ज़रूरत लिखें – हम आपके कीवर्ड को उम्मीदवारों के विवरण और रिज़्यूमे से मिलाकर दिखाएँगे। एक बार का शुल्क, 15 दिन तक नाम, नंबर, ईमेल और रिज़्यूमे देखें।', 'Describe your need – we match your keywords with candidates’ briefs and resumes. One-time fee; see names, numbers, emails and resumes for 15 days.'],
            'categories_label' => ['ज़रूरी विशेषज्ञता / पद – अधिकतम 5', 'Specialisation / post needed – up to 5'],
            'info' => [
                'title' => ['शुल्क (एक बार, 15 दिन के लिए)', 'Fees (one-time, valid 15 days)'],
                'points' => [
                    ['डॉक्टर चाहिए: ₹11,000 + 18% GST = ₹12,980', 'Doctors: ₹11,000 + 18% GST = ₹12,980'],
                    ['फ्रंट डेस्क / सेल्स और मार्केटिंग: ₹1,500 + GST = ₹1,770', 'Front desk / Sales & marketing: ₹1,500 + GST = ₹1,770'],
                    ['स्वीपर / हाउसकीपिंग: ₹5,000 + GST = ₹5,900', 'Sweeper / Housekeeping: ₹5,000 + GST = ₹5,900'],
                    ['अन्य अस्पताल स्टाफ़: ₹600 + GST = ₹708', 'Other hospital staff: ₹600 + GST = ₹708'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                [
                    'title' => ['अस्पताल / क्लिनिक', 'Hospital / Clinic'],
                    'fields' => [
                        self::radio('org_kind', ['संस्था का प्रकार', 'Type of organisation'], [
                            'hospital' => ['अस्पताल', 'Hospital'], 'clinic' => ['क्लिनिक', 'Clinic'], 'nursing_home' => ['नर्सिंग होम', 'Nursing Home'],
                            'diagnostic' => ['डायग्नोस्टिक / लैब', 'Diagnostic / Lab'], 'pharmacy' => ['फ़ार्मेसी', 'Pharmacy'], 'other' => ['अन्य', 'Other'],
                        ], true, ['full' => true]),
                        self::text('business_name', ['अस्पताल / क्लिनिक का नाम', 'Hospital / Clinic Name'], true, ['max' => 150]),
                        self::text('registration_number', ['क्लिनिकल एस्टैब्लिशमेंट / रजिस्ट्रेशन नं.', 'Clinical establishment / registration no.'], false, ['max' => 60]),
                        self::text('full_name', ['संपर्क व्यक्ति का नाम', 'Contact Person Name'], true, ['max' => 150, 'autocomplete' => 'name']),
                        self::text('designation', ['पद', 'Designation'], true, ['max' => 100]),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर', 'Mobile Number'], 'required' => true, 'autocomplete' => 'tel'],
                        ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल', 'Email ID'], 'required' => true, 'autocomplete' => 'email'],
                    ],
                ],
                self::locationSection(['पता और पिन कोड', 'Address & PIN Code']),
                [
                    'title' => ['आपको किसकी ज़रूरत है', 'Who You Need'],
                    'fields' => [
                        self::radio('hire_role', ['भूमिका', 'Role to hire'], self::pricedRoles(self::HEALTH_FEES_HOSPITAL), true, ['full' => true]),
                        self::categories(true),
                        self::number('positions', ['कितने लोग चाहिए', 'Number of positions'], true, 1, 500),
                        ['key' => 'requirement_brief', 'type' => 'textarea', 'label' => ['ज़रूरत का संक्षिप्त विवरण – विभाग, योग्यता, शिफ़्ट (यही कीवर्ड मिलाए जाएँगे)', 'Brief requirement – department, qualification, shift (these keywords are matched)'], 'required' => true, 'full' => true, 'max' => 2000],
                        self::text('salary_offered', ['वेतन (₹ प्रति माह)', 'Salary offered (₹ per month)'], false, ['max' => 60]),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है और मैं इस संस्था की ओर से अधिकृत हूँ।', 'The information is true and I am authorised on behalf of this organisation.'],
                ['शुल्क वापसी योग्य नहीं है; एक्सेस भुगतान से 15 दिन तक मान्य है।', 'The fee is non-refundable; access is valid for 15 days from payment.'],
                ['उम्मीदवारों की जानकारी केवल भर्ती के लिए उपयोग होगी और किसी के साथ साझा / बेची नहीं जाएगी।', 'Candidates’ details will be used only for hiring and never shared or sold.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['आपके कीवर्ड से मिलते उम्मीदवार अभी देखें – नाम, मोबाइल, ईमेल और रिज़्यूमे के साथ (15 दिन)।', 'See matching candidates now – with name, mobile, email and resume (15 days).'],
            ],
        ];
    }

    private static function hirePartTime(): array
    {
        return [
            'type' => 'hirer',
            'prefix' => 'HPT',
            'side' => 'service',
            'icon' => '🧑‍💼',
            'otp' => true,
            'hidden_from_hub' => true,
            'fee' => self::HIRER_FEE,
            'max_categories' => 5,
            'title' => ['पार्ट-टाइम लोग चाहिए – टैलेंट पास (2 दिन)', 'Hire Part-time People – Talent Pass (2 days)'],
            'button' => ['एग्ज़िबिशन स्टाफ़, मॉडल, बाउंसर, ड्राइवर चाहिए? 2 दिन का पास', 'Need exhibition staff, models, bouncers, drivers? 2-day pass'],
            'intro' => ['₹5,000 + 18% GST (₹5,900) में 2 दिन तक पार्ट-टाइम उम्मीदवारों के रिज़्यूमे कीवर्ड से खोजें और सीधे संपर्क करें।', 'For ₹5,000 + 18% GST (₹5,900), search part-time candidates by resume keywords for 2 days and contact them directly.'],
            'categories_label' => ['किस काम के लिए लोग चाहिए – अधिकतम 5', 'Work you are hiring for – up to 5'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['₹5,000 + 18% GST = ₹5,900, भुगतान से 2 दिन (48 घंटे) तक।', '₹5,000 + 18% GST = ₹5,900, valid for 2 days (48 hours) from payment.'],
                    ['खोज: काम, शहर / पिन कोड, घंटे, दिन और रिज़्यूमे के कीवर्ड।', 'Search by work, city / PIN, hours, days and resume keywords.'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                [
                    'title' => ['आपकी जानकारी', 'Your Details'],
                    'fields' => [
                        self::radio('hirer_kind', ['आप कौन हैं', 'You are'], [
                            'individual' => ['व्यक्ति', 'Individual'], 'company' => ['कंपनी', 'Company'], 'institution' => ['संस्था', 'Institution'], 'event_agency' => ['इवेंट / एग्ज़िबिशन एजेंसी', 'Event / exhibition agency'],
                        ], true, ['full' => true]),
                        self::text('business_name', ['कंपनी / संस्था का नाम (यदि हो)', 'Company / institution name (if any)'], false, ['max' => 150]),
                        self::text('full_name', ['आपका नाम', 'Your Name'], true, ['max' => 150, 'autocomplete' => 'name']),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर', 'Mobile Number'], 'required' => true, 'autocomplete' => 'tel'],
                        ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल', 'Email ID'], 'required' => true, 'autocomplete' => 'email'],
                        self::text('city', ['शहर', 'City'], true, ['max' => 120]),
                        ['key' => 'state', 'type' => 'state', 'label' => ['राज्य', 'State / UT'], 'required' => true],
                        ['key' => 'pincode', 'type' => 'pincode', 'label' => ['पिन कोड', 'PIN Code'], 'required' => true],
                    ],
                ],
                [
                    'title' => ['आपकी ज़रूरत', 'Your Requirement'],
                    'fields' => [
                        self::categories(true),
                        ['key' => 'requirement_brief', 'type' => 'textarea', 'label' => ['संक्षिप्त विवरण – तारीख़, घंटे, जगह, कितने लोग', 'Brief – dates, hours, venue, how many people'], 'required' => true, 'full' => true, 'max' => 2000],
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['₹5,900 (₹5,000 + GST) वापसी योग्य नहीं है; एक्सेस भुगतान से 2 दिन तक।', '₹5,900 (₹5,000 + GST) is non-refundable; access lasts 2 days from payment.'],
                ['उम्मीदवारों की जानकारी केवल भर्ती के लिए उपयोग होगी और किसी के साथ साझा / बेची नहीं जाएगी।', 'Candidates’ details will be used only for hiring and never shared or sold.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['अभी “Talent Search” खोलें और कीवर्ड से उम्मीदवार खोजें (2 दिन)।', 'Open “Talent Search” now and find candidates by keyword (2 days).'],
            ],
        ];
    }

    /** Organisation groups for "Jobs in India" (external listings). */
    public const INDIA_JOB_TYPES = [
        'railways' => ['भारतीय रेलवे', 'Indian Railways'],
        'defence' => ['सेना / नौसेना / वायुसेना', 'Army / Navy / Air Force'],
        'police' => ['पुलिस / अर्धसैनिक बल', 'Police / Paramilitary'],
        'central_govt' => ['केंद्र सरकार (UPSC, SSC…)', 'Central Govt (UPSC, SSC…)'],
        'state_govt' => ['राज्य सरकारें', 'State Govts'],
        'psu' => ['PSU (GAIL, BHEL, NTPC…)', 'PSUs (GAIL, BHEL, NTPC…)'],
        'bank' => ['बैंक', 'Banks'],
        'private' => ['बड़ी प्राइवेट कंपनियाँ (Reliance, Tata…)', 'Top private companies (Reliance, Tata…)'],
    ];

    private static function ngo(): array
    {
        $person = static function (int $n, bool $required): array {
            $label = $n === 1 ? ['प्रमुख व्यक्ति 1 (अध्यक्ष / सचिव / निदेशक)', 'Prominent Person 1 (President / Secretary / Director)'] : ["प्रमुख व्यक्ति {$n}", "Prominent Person {$n}"];
            return [
                self::text("person{$n}_name", [$label[0] . ' – नाम', $label[1] . ' – Name'], $required, ['max' => 150]),
                self::text("person{$n}_designation", ['पद', 'Designation'], $required, ['max' => 100]),
                ['key' => "person{$n}_mobile", 'type' => 'mobile', 'label' => ['मोबाइल', 'Mobile'], 'required' => $required],
                ['key' => "person{$n}_email", 'type' => 'email', 'label' => ['ईमेल', 'Email'], 'required' => false],
            ];
        };

        return [
            'type' => 'ngo',
            'prefix' => 'NGO',
            'side' => 'provider',
            'icon' => '🤝',
            'otp' => true,
            'title' => ['NGO / सामाजिक संस्था रजिस्ट्रेशन', 'NGO / Social Organisation Registration'],
            'button' => ['सामाजिक सेवा के लिए लोग चाहने वाले NGO – यहाँ रजिस्टर करें', 'For NGOs looking for Social Service People – Register Here'],
            'intro' => ['सामाजिक सेवा के लिए लोग ढूँढने वाली संस्थाएँ एक बार ₹155 देकर रजिस्टर करें। भुगतान के बाद हमारी टीम आपकी जानकारी सत्यापित करेगी। सामाजिक सेवा के आवेदकों के लिए कोई शुल्क नहीं है।', 'Organisations looking for social service people register once for ₹155. After payment our team verifies your details. Social service applicants pay nothing.'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['सामाजिक सेवा नौकरियों के लिए आवेदन करने वालों से कोई शुल्क नहीं लिया जाता।', 'Social service job applicants never pay any fee.'],
                    ['एक संस्था = एक ईमेल + एक मोबाइल + एक रजिस्ट्रेशन / GST नंबर।', 'One organisation = one email + one mobile + one registration / GST number.'],
                    ['भुगतान के बाद Jobsence टीम आपकी संस्था के दस्तावेज़ और विवरण सत्यापित करेगी; सत्यापन के बाद ही आपकी प्रोफ़ाइल “Verified” दिखेगी।', 'After payment the Jobsence team verifies your organisation’s details and documents; your profile shows “Verified” only after verification.'],
                ],
            ],
            'categories_label' => ['आपको किस तरह के सामाजिक सेवा कार्य के लिए लोग चाहिए (सूची में खोजें)', 'Social service roles you need people for (search the list)'],
            'sections' => [
                [
                    'title' => ['संस्था की जानकारी', 'Organisation Details'],
                    'fields' => [
                        self::text('organization_name', ['संस्था का पूरा नाम', 'Organisation Name (as registered)'], true, ['max' => 190, 'full' => true]),
                        self::radio('org_type', ['संस्था का प्रकार', 'Type of Organisation'], [
                            'trust' => ['ट्रस्ट', 'Trust'], 'society' => ['सोसाइटी', 'Society'], 'section8' => ['सेक्शन 8 कंपनी', 'Section 8 Company'],
                            'fpo' => ['FPO / सहकारी', 'FPO / Co-operative'], 'shg' => ['स्वयं सहायता समूह', 'Self Help Group'], 'other' => ['अन्य', 'Other'],
                        ], true, ['full' => true, 'other' => 'org_type_other']),
                        self::checkboxes('cause_areas', ['कार्य क्षेत्र (श्रेणी)', 'Category / Cause Areas'], [
                            'education' => ['शिक्षा', 'Education'], 'health' => ['स्वास्थ्य', 'Health'], 'women' => ['महिला सशक्तिकरण', 'Women Empowerment'],
                            'children' => ['बाल कल्याण', 'Child Welfare'], 'elderly' => ['वृद्ध सेवा', 'Elderly Care'], 'disability' => ['दिव्यांग सहायता', 'Disability Support'],
                            'environment' => ['पर्यावरण', 'Environment'], 'rural' => ['ग्रामीण विकास', 'Rural Development'], 'livelihood' => ['आजीविका / कौशल', 'Livelihood / Skills'],
                            'animal' => ['पशु कल्याण', 'Animal Welfare'], 'disaster' => ['आपदा राहत', 'Disaster Relief'], 'other' => ['अन्य', 'Other'],
                        ], true),
                        self::text('registration_number', ['संस्था रजिस्ट्रेशन नंबर', 'Registration Number (Trust / Society / CIN)'], true, ['max' => 80]),
                        self::text('darpan_id', ['NGO Darpan ID (यदि हो)', 'NGO Darpan ID (if any)'], false, ['max' => 40]),
                        ['key' => 'gstin', 'type' => 'gst', 'label' => ['GST नंबर', 'GST Number'], 'required' => true],
                        self::text('pan', ['संस्था का PAN (यदि हो)', 'Organisation PAN (if any)'], false, ['max' => 10]),
                        self::checkboxes('tax_status', ['कर पंजीकरण', 'Tax Registrations'], ['12a' => ['12A', '12A'], '80g' => ['80G', '80G'], 'fcra' => ['FCRA', 'FCRA']], false),
                        self::number('established_year', ['स्थापना वर्ष', 'Year Established'], true, 1850, (int)date('Y')),
                        self::text('website', ['वेबसाइट / सोशल मीडिया (यदि हो)', 'Website / Social media (if any)'], false, ['max' => 255]),
                        self::textarea('about', ['संस्था के बारे में संक्षेप में', 'About the organisation (work, reach, beneficiaries)'], true),
                    ],
                ],
                [
                    'title' => ['संपर्क (OTP ईमेल पर भेजा जाएगा)', 'Contact (OTP will be sent to email)'],
                    'fields' => [
                        self::text('full_name', ['संपर्क व्यक्ति का नाम', 'Contact Person Name'], true, ['max' => 150]),
                        ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर (WhatsApp)', 'Mobile Number (WhatsApp)'], 'required' => true],
                        ['key' => 'whatsapp', 'type' => 'mobile', 'label' => ['WhatsApp नंबर (अलग हो तो)', 'WhatsApp Number (if different)'], 'required' => false],
                        ['key' => 'email', 'type' => 'email', 'label' => ['संस्था का ईमेल', 'Organisation Email'], 'required' => true],
                    ],
                ],
                [
                    'title' => ['प्रमुख व्यक्ति', 'Prominent Persons'],
                    'fields' => array_merge($person(1, true), $person(2, false), $person(3, false)),
                ],
                self::addressSection(true),
                [
                    'title' => ['आपको किस तरह के लोग चाहिए', 'People You Are Looking For'],
                    'fields' => [
                        self::categories(true),
                        self::text('service_locations', ['कहाँ काम करना होगा (शहर / ज़िले)', 'Where the work is (cities / districts)'], false, ['full' => true, 'max' => 255]),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई संस्था की सभी जानकारी सही है और मैं संस्था की ओर से रजिस्टर करने के लिए अधिकृत हूँ।', 'All organisation details are true and I am authorised to register on behalf of the organisation.'],
                ['₹155 का एकमुश्त रजिस्ट्रेशन शुल्क (GST सहित) वापसी योग्य नहीं है।', 'The one-time registration fee of ₹155 (including GST) is non-refundable.'],
                ['भुगतान के बाद Jobsence टीम दस्तावेज़ों की जाँच करेगी; गलत जानकारी मिलने पर रजिस्ट्रेशन रद्द किया जा सकता है।', 'After payment Jobsence will verify the documents; registrations with false information may be cancelled.'],
                ['हम सामाजिक सेवा आवेदकों से किसी भी प्रकार का शुल्क नहीं लेंगे।', 'We will not charge social service applicants any fee.'],
                self::notGovt(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपकी संस्था की जानकारी और दस्तावेज़ सत्यापित करेगी।', 'The Jobsence team will verify your organisation’s details and documents.'],
                ['सत्यापन होते ही आपको ईमेल मिलेगा और आप सामाजिक सेवा के लिए लोगों से जुड़ सकेंगे।', 'Once verified you will receive an email and can start connecting with social service people.'],
            ],
        ];
    }

    private static function skillProvider(): array
    {
        // Aadhaar belongs to a person: mandatory for an individual mentor only. A group, institute, PSU or
        // Govt body has no Aadhaar of its own – its contact person may still give theirs.
        $personal = self::personalSection(['name_label' => ['पूरा नाम / संपर्क व्यक्ति', 'Full Name / Contact Person'], 'dob' => false, 'gender' => false, 'aadhaar_required' => true,
            'aadhaar_label' => ['आधार नंबर (व्यक्तिगत मेंटर / संपर्क व्यक्ति)', 'Aadhaar Number (individual mentor / contact person)'],
            'aadhaar_hint' => ['केवल व्यक्तिगत मेंटर / ट्रेनर के लिए ज़रूरी। ग्रुप, संस्थान, PSU व सरकारी विभाग के लिए वैकल्पिक। हम केवल आख़िरी 4 अंक सेव करते हैं।', 'Mandatory only for an individual mentor / trainer; optional for groups, institutes, PSUs and Govt bodies. We store only the last 4 digits.'],
            'aadhaar_optional_when' => ['provider_kind' => ['group', 'institute', 'psu', 'govt']]]);

        return [
            'type' => 'provider',
            'international' => true,
            'any_country' => true, // mentors may live in any country (users: India + open countries elsewhere)
            'quotes' => true, // "giving skill is punya" verses above the form
            'prefix' => 'MEN',
            'fee' => 0.0,
            'side' => 'provider',
            'icon' => '👩‍🏫',
            'otp' => true,
            'multipart' => true,
            'title' => ['मेंटर नामांकन फॉर्म – Jobsence स्किल डेवलपमेंट टीम', 'Mentor Enrolment Form – Jobsence Skill Development Team'],
            'button' => ['स्किल प्रोवाइडर / मेंटर / ट्रेनिंग संस्थान – यहाँ रजिस्टर करें', 'For Skill Providers / Mentors / Training Institutes – Register Here'],
            'intro' => ['Jobsence भारत के बेरोज़गार युवाओं को स्किल देने के लिए अपनी मेंटर टीम बना रहा है। अगर आप किसी भी स्किल में अच्छे हैं, तो जुड़ें।', 'Jobsence is building its own Team of Mentors to give skills to unemployed youth of India. If you are good at any skill, enrol yourself.'],
            'info' => [
                'title' => ['आपको क्या मिलेगा व ज़रूरी शर्तें', 'What You Will Get & Important Conditions'],
                'points' => [
                    ['प्रति प्रशिक्षार्थी तय मानदेय (Fixed Emolument) – वीडियो / ऑनलाइन क्लास और ऑफलाइन क्लास (दिल्ली-NCR) दोनों के लिए।', 'Fixed emolument (fixed payment) per candidate – for video / online classes and for offline classes (Delhi-NCR).'],
                    ['भुगतान प्रशिक्षार्थी की ट्रेनिंग सफलतापूर्वक पूरी होने के बाद ही मिलेगा।', 'Payment is given only after successful completion of the candidate’s training.'],
                    ['अपनी जानकारी के साथ डेमो वीडियो भेजना ज़रूरी है। वीडियो में साफ़ बताएँ: “This video is for Jobsence Skill Development”।', 'You must send your details with a demo video. The video must clearly mention: “This video is for Jobsence Skill Development”.'],
                    ['सभी वीडियो Jobsence की संपत्ति होंगी – आप इनका कहीं और उपयोग नहीं कर सकते।', 'All videos will be the property of Jobsence – you cannot use them anywhere else.'],
                    ['Jobsence के नियमों व दिशानिर्देशों का पालन करना होगा।', 'You must follow Jobsence rules & guidelines.'],
                    ['प्रशिक्षार्थियों से Jobsence को मिलने वाली राशि का 20% स्किल डेवलपमेंट एजेंसियों / मेंटर्स को दिया जाता है; बाकी (कंपनी के संचालन खर्च के बाद) Jobsence का अपना स्किल डेवलपमेंट सेंटर बनाने में लगाया जाता है।', '20% of what Jobsence receives from candidates goes to skill development agencies / mentors; the rest (after company operating expenses) is used to build Jobsence’s own skill development centre.'],
                    ['ट्रेनिंग संस्थान, PSU और सरकारी विभाग भी स्किल डेवलपमेंट ऑफ़र कर सकते हैं।', 'Training institutes, PSUs and government bodies can also offer skill development.'],
                ],
            ],
            'categories_label' => ['आप कौन से स्किल सिखा सकते हैं (सूची में खोजें, एक से ज़्यादा चुनें)', 'Skills you can teach (search 3000+ list, select multiple)'],
            'sections' => [
                [
                    'title' => ['आप कौन हैं', 'Who Are You'],
                    'fields' => [
                        self::residenceField(true),
                        self::radio('provider_kind', ['रजिस्ट्रेशन का प्रकार', 'Registering As'], [
                            'individual' => ['व्यक्तिगत मेंटर / ट्रेनर', 'Individual Mentor / Trainer'],
                            'group' => ['ग्रुप मेंटरिंग', 'Group Mentoring'],
                            'institute' => ['ट्रेनिंग संस्थान / स्किल प्रोवाइडर', 'Training Institute / Skill Provider'],
                            'psu' => ['सार्वजनिक उपक्रम (PSU)', 'PSU (Public Sector Undertaking)'],
                            'govt' => ['सरकारी विभाग / संस्था', 'Government Department / Body'],
                        ], true, ['full' => true]),
                        self::text('institute_name', ['संस्थान / PSU / विभाग का नाम (व्यक्तिगत मेंटर के लिए नहीं)', 'Institute / PSU / Department name (not for individual mentors)'], false),
                        ['key' => 'gstin', 'type' => 'gst', 'label' => ['GST नंबर', 'GST Number'], 'required' => true,
                            'optional_when' => ['provider_kind' => ['individual', 'group']], // individuals and mentor groups usually have no GST
                            'hint' => ['व्यक्तिगत मेंटर / ट्रेनर और ग्रुप मेंटरिंग के लिए GST ज़रूरी नहीं है।', 'GST is not mandatory for an individual mentor / trainer or group mentoring.']],
                    ],
                ],
                $personal,
                self::addressSection(false, true),
                [
                    'title' => ['आप क्या सिखा सकते हैं', 'What You Can Teach'],
                    'fields' => [
                        self::categories(true),
                        self::radio('training_mode', ['आप कैसे पढ़ाना चाहते हैं', 'Mode You Prefer'], [
                            'online' => ['केवल ऑनलाइन / वीडियो', 'Only Online / Video'],
                            'offline_ncr' => ['केवल ऑफलाइन (दिल्ली-NCR)', 'Only Offline (Delhi-NCR)'],
                            'both' => ['दोनों', 'Both'],
                        ], true, ['full' => true]),
                        self::number('years_experience', ['पढ़ाने / काम का अनुभव (वर्ष)', 'Experience in Teaching / Working (Years)'], true, 0, 60),
                        self::languagesField(['आप किन भाषाओं में पढ़ा सकते हैं', 'Languages You Can Teach'], false),
                        self::textarea('education_experience', ['शिक्षा व अनुभव संक्षेप में', 'Brief education & experience (qualification, where you worked / taught)'], true),
                        self::textarea('certifications', ['प्रमाणपत्र / लाइसेंस (यदि हो)', 'Certifications / Licences (if any)'], false),
                        ['key' => 'resume', 'type' => 'file', 'label' => ['दस्तावेज़ / प्रमाणपत्र अपलोड करें (PDF/JPG/PNG/DOC, अधिकतम 5MB)', 'Upload documents / certificates (PDF/JPG/PNG/DOC, max 5MB)'], 'required' => false, 'full' => true, 'allow_images' => true],
                        self::number('expected_emolument', ['अपेक्षित मानदेय – प्रति प्रशिक्षार्थी ₹ (वैकल्पिक)', 'Expected Emolument per candidate ₹ (optional)'], false, 0, 1000000),
                    ],
                ],
                [
                    'title' => ['डेमो वीडियो', 'Demo Video'],
                    'fields' => [
                        ['key' => 'video', 'type' => 'video', 'label' => ['डेमो वीडियो अपलोड करें (ज़रूरी – MP4/MOV/WEBM, अधिकतम 200MB)', 'Upload Demo Video (mandatory – MP4/MOV/WEBM, max 200MB)'], 'required' => true, 'full' => true,
                            'hint' => ['वीडियो में “This video is for Jobsence” लिखा / बोला होना चाहिए। फ़ाइल बड़ी हो तो नीचे Google Drive / YouTube (Unlisted) लिंक दें।', 'The video must show / say “This video is for Jobsence”. If the file is too large, give a Google Drive / YouTube (Unlisted) link below.']],
                        self::text('video_link', ['या वीडियो लिंक (Google Drive / YouTube Unlisted)', 'Or video link (Google Drive / YouTube Unlisted)'], false, ['full' => true, 'max' => 500]),
                    ],
                ],
                [
                    'title' => ['बैंक विवरण (भुगतान के लिए)', 'Bank Details (for payment)'],
                    'fields' => [
                        self::text('bank_holder', ['खाताधारक का नाम', 'Account Holder Name'], true, ['max' => 150]),
                        ['key' => 'bank_account', 'type' => 'bank_account', 'label' => ['बैंक खाता संख्या (भारत के बाहर: खाता संख्या / IBAN)', 'Bank Account Number (outside India: account number / IBAN)'], 'required' => true,
                            'hint' => ['सुरक्षा के लिए खाता संख्या एन्क्रिप्ट करके रखी जाती है।', 'Your account number is stored encrypted.']],
                        ['key' => 'ifsc', 'type' => 'ifsc', 'label' => ['IFSC कोड (भारत के बाहर: SWIFT / BIC कोड)', 'IFSC Code (outside India: SWIFT / BIC code)'], 'required' => true],
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['मेरे द्वारा जमा की गई सभी वीडियो केवल Jobsence की संपत्ति होंगी।', 'I agree that all videos submitted by me will be the sole property of Jobsence.'],
                ['मैं इन वीडियो का कहीं और उपयोग नहीं करूँगा/करूँगी।', 'I will not use these videos anywhere else.'],
                ['मैं Jobsence के सभी नियमों का पालन करूँगा/करूँगी।', 'I will follow all rules of Jobsence.'],
                ['मानदेय प्रति प्रशिक्षार्थी, सफल ट्रेनिंग के बाद ही दिया जाएगा।', 'I understand that emolument will be paid per candidate only after successful training.'],
                ['रजिस्ट्रेशन मुफ़्त है। उम्मीदवारों से जुड़ने (पूरी प्रोफ़ाइल, समझौता, संपर्क) के लिए ₹155 (GST सहित) का पेड प्लान – 30 दिन, रोज़ 3 प्रोफ़ाइल; भारत के बाहर रहने वाले मेंटर के लिए USD 5।', 'Registration is free. To connect with candidates (full profile, agreement, contact) a paid plan of ₹155 (incl. GST) is needed – 30 days, 3 profiles a day; USD 5 for mentors living outside India.'],
                self::agreementRule(),
                ['चयन या काम मिलने की कोई गारंटी नहीं है; काम ज़रूरत के अनुसार दिया जाएगा।', 'There is no guarantee of selection; work will be given as per requirement.'],
                self::notGovt(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपके फॉर्म और वीडियो की समीक्षा करेगी – सत्यापन के बाद उम्मीदवार आपको देख सकेंगे।', 'The Jobsence team will review your form and video – after verification candidates can see you.'],
                ['अभी “मेरा मेंटरिंग डैशबोर्ड” खोलें: कौन कौन सा स्किल सीखना चाहता है देखें और ₹155 का प्लान लें (भारत के बाहर USD 5)।', 'Open “My mentoring dashboard” now: see who wants to learn which skill and get the ₹155 plan (USD 5 outside India).'],
                ['चयन होने पर आपको Jobsence मेंटर टीम में जोड़ा जाएगा; काम ज़रूरत के अनुसार दिया जाएगा।', 'If selected, you will be added to the Jobsence Mentor Team; work will be given as per requirement.'],
                ['भुगतान प्रति प्रशिक्षार्थी, ट्रेनिंग पूरी होने के बाद जारी किया जाएगा।', 'Payment will be released per candidate after training completion.'],
            ],
        ];
    }

    /** Preferred timing options (skill / internship seekers). */
    public const TIMINGS = [
        'morning' => ['सुबह', 'Morning'], 'afternoon' => ['दोपहर', 'Afternoon'], 'evening' => ['शाम', 'Evening'],
        'weekend' => ['केवल शनिवार-रविवार', 'Weekends only'], 'flexible' => ['कोई भी समय', 'Flexible'],
    ];

    /** Paid provider plans: ₹155 incl. GST. */
    public const PROVIDER_PLAN_FEE = 155.00;

    /** Mentors / internship providers living outside India: USD 5 (no Indian GST – export of services), payable in ₹ too. */
    public const PROVIDER_PLAN_FEE_USD = 5.00;

    /**
     * Hiring plans for companies (3 full profiles a day). India (round totals incl. 18% GST): 3, 7, 15 days, 1 month, 6 months (5 months' price), 1 year (9 months' price).
     * Outside India: USD 12 / 1 day … USD 1,800 / 1 year (no Indian GST – export of services).
     */
    public const HIRING_PLANS = [
        'in3' => ['days' => 3, 'fee' => 216.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['3 दिन – ₹216 (₹183 + GST)', '3 days – ₹216 (₹183 + GST)']],
        'in7' => ['days' => 7, 'fee' => 504.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['7 दिन – ₹504 (₹427 + GST)', '7 days – ₹504 (₹427 + GST)']],
        'in15' => ['days' => 15, 'fee' => 1080.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['15 दिन – ₹1,080 (₹915 + GST)', '15 days – ₹1,080 (₹915 + GST)']],
        'in30' => ['days' => 30, 'fee' => 2158.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['1 महीना – ₹2,158 (₹1,829 + GST)', '1 month – ₹2,158 (₹1,829 + GST)']],
        'in180' => ['days' => 180, 'fee' => 10791.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['6 महीने (अर्धवार्षिक) – ₹10,791 (₹9,145 + GST)', '6 months (half-yearly) – ₹10,791 (₹9,145 + GST)']],
        'in365' => ['days' => 365, 'fee' => 19424.00, 'currency' => 'INR', 'abroad' => false, 'label' => ['1 साल – ₹19,424 (₹16,461 + GST)', '1 year – ₹19,424 (₹16,461 + GST)']],
        'usd1' => ['days' => 1, 'fee' => 12.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['1 दिन – USD 12', '1 day – USD 12']],
        'usd3' => ['days' => 3, 'fee' => 30.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['3 दिन – USD 30', '3 days – USD 30']],
        'usd7' => ['days' => 7, 'fee' => 60.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['7 दिन – USD 60', '7 days – USD 60']],
        'usd15' => ['days' => 15, 'fee' => 110.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['15 दिन – USD 110', '15 days – USD 110']],
        'usd30' => ['days' => 30, 'fee' => 200.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['1 महीना – USD 200', '1 month – USD 200']],
        'usd180' => ['days' => 180, 'fee' => 1000.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['6 महीने (अर्धवार्षिक) – USD 1,000', '6 months (half-yearly) – USD 1,000']],
        'usd365' => ['days' => 365, 'fee' => 1800.00, 'currency' => 'USD', 'abroad' => true, 'label' => ['1 साल – USD 1,800', '1 year – USD 1,800']],
    ];

    private static function untilMatched(): array
    {
        return ['मेरा रजिस्ट्रेशन तब तक मान्य रहेगा जब तक मुझे उपयुक्त मेंटर / इंटर्नशिप प्रदाता नहीं मिल जाता।', 'My registration stays valid until I get a suitable mentor / internship provider.'];
    }

    private static function rejectRule(): array
    {
        return ['समझौते के बाद अगर मैं मेंटर / प्रदाता को छोड़ता/छोड़ती हूँ, तो नए फॉर्म के लिए फिर से शुल्क देना होगा।', 'If I reject my mentor / provider after the agreement is signed, I must pay the form fee again for a new registration.'];
    }

    private static function agreementRule(): array
    {
        return ['फ़ोन नंबर और ईमेल केवल उम्मीदवार, प्रदाता और Jobsence के बीच त्रिपक्षीय समझौते (ईमेल OTP से) के बाद ही दिखते हैं।', 'Phone numbers and emails are shown only after a tripartite agreement between candidate, provider and Jobsence (signed with email OTP).'];
    }

    private static function internshipProvider(): array
    {
        return [
            'type' => 'internpro',
            'international' => true,
            'prefix' => 'INP',
            'side' => 'provider',
            'icon' => '🏢',
            'otp' => true,
            'fee' => 0.0,
            'title' => ['इंटर्नशिप प्रदाता रजिस्ट्रेशन (मुफ़्त)', 'Internship Provider Registration (free)'],
            'button' => ['इंटर्नशिप देने वाली कंपनियाँ – मुफ़्त रजिस्टर करें', 'Companies offering Internship – Register free'],
            'intro' => ['मुफ़्त रजिस्टर करें और देखें कौन किस डोमेन में इंटर्नशिप चाहता है। जुड़ने के लिए ₹155 का प्लान (भारत के बाहर USD 5) – 10 दिन, रोज़ 3 प्रोफ़ाइल, अधिकतम 20 उम्मीदवार।', 'Register free and see who wants an internship in which domain. To connect: ₹155 plan (USD 5 outside India) – 10 days, 3 profiles a day, up to 20 candidates.'],
            'categories_label' => ['इंटर्नशिप डोमेन – जो आप देते हैं (सूची में खोजें)', 'Internship domains you offer (search the list)'],
            'sections' => [
                [
                    'title' => ['आपकी संस्था', 'Organisation'],
                    'fields' => [
                        self::residenceField(),
                        self::radio('provider_kind', ['आप कौन हैं', 'You are'], [
                            'company' => ['कंपनी', 'Company'], 'startup' => ['स्टार्टअप', 'Startup'], 'ngo' => ['NGO', 'NGO'],
                            'institute' => ['संस्थान', 'Institute'], 'individual' => ['व्यक्ति / प्रोफ़ेशनल', 'Individual / Professional'],
                        ], true, ['full' => true]),
                        self::text('business_name', ['कंपनी / संस्था का नाम', 'Company / Organisation name'], true, ['max' => 150]),
                        ['key' => 'gstin', 'type' => 'gst', 'label' => ['GST नंबर', 'GST Number'], 'required' => false],
                        self::text('website', ['वेबसाइट (यदि हो)', 'Website (if any)'], false, ['max' => 190]),
                    ],
                ],
                self::personalSection(['name_label' => ['संपर्क व्यक्ति का नाम', 'Contact Person Name'], 'dob' => false, 'gender' => false, 'aadhaar_required' => false]),
                self::locationSection(['पता', 'Address']),
                [
                    'title' => ['इंटर्नशिप', 'Internships'],
                    'fields' => [
                        self::categories(true),
                        self::radio('work_mode', ['कहाँ से', 'Where'], ['onsite' => ['ऑफिस में', 'On-site'], 'wfh' => ['वर्क फ्रॉम होम', 'Work from home'], 'hybrid' => ['दोनों', 'Hybrid']], true, ['full' => true]),
                        self::radio('internship_pay', ['पेड या अनपेड', 'Paid or unpaid'], ['paid' => ['पेड', 'Paid'], 'unpaid' => ['अनपेड', 'Unpaid'], 'both' => ['दोनों', 'Both']], true, ['full' => true]),
                        self::number('stipend_offered', ['स्टाइपेंड (₹ प्रति माह) – पेड के लिए ₹8,000 से ₹5,00,000', 'Stipend (₹ per month) – ₹8,000 to ₹5,00,000 for paid'], false, self::STIPEND_MIN, self::STIPEND_MAX),
                        self::number('positions', ['कितने इंटर्न', 'Number of interns'], true, 1, 500),
                        self::textarea('about_internship', ['इंटर्नशिप के बारे में संक्षेप में', 'About the internship (work, skills needed, duration)'], true),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है और मैं इस संस्था की ओर से अधिकृत हूँ।', 'The information is true and I am authorised on behalf of this organisation.'],
                ['पेड इंटर्नशिप का स्टाइपेंड ₹8,000 से ₹5,00,000 प्रति माह होगा।', 'Paid internships will pay a stipend of ₹8,000 to ₹5,00,000 per month.'],
                self::agreementRule(),
                ['उम्मीदवारों की जानकारी केवल इंटर्नशिप के लिए उपयोग होगी और किसी के साथ साझा / बेची नहीं जाएगी।', 'Candidates’ details will be used only for the internship and never shared or sold.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपकी जानकारी सत्यापित करेगी – उसके बाद उम्मीदवार आपको देख सकेंगे।', 'The Jobsence team will verify your details – after that candidates can see you.'],
                ['अभी “मेरा मेंटरिंग डैशबोर्ड” खोलें और ₹155 का प्लान लें (भारत के बाहर USD 5; 10 दिन, रोज़ 3 प्रोफ़ाइल, अधिकतम 20)।', 'Open “My mentoring dashboard” now and get the ₹155 plan (USD 5 outside India; 10 days, 3 profiles a day, up to 20).'],
            ],
        ];
    }

    private static function jobProvider(): array
    {
        return [
            'type' => 'jobpro',
            'international' => true,
            'prefix' => 'JBP',
            'side' => 'provider',
            'icon' => '🏭',
            'otp' => true,
            'fee' => 0.0,
            'title' => ['नौकरी देने वाली कंपनियाँ – मुफ़्त रजिस्ट्रेशन', 'Companies Hiring – Free Registration'],
            'button' => ['नौकरी देने वाली कंपनियाँ / दुकानें – मुफ़्त रजिस्टर करें', 'Companies & shops offering jobs – Register free'],
            'intro' => ['मुफ़्त रजिस्टर करें और देखें कौन किस नौकरी के लिए तैयार है। जुड़ने के लिए भर्ती प्लान: ₹216 (3 दिन) से ₹19,424 (1 साल) तक – 3, 7, 15 दिन, 1 महीना, 6 महीने या 1 साल; भारत के बाहर USD 12 (1 दिन) से – रोज़ 3 पूरी प्रोफ़ाइल और रिज़्यूमे।', 'Register free and see who is ready for which job. To connect, a hiring plan: from ₹216 (3 days) to ₹19,424 (1 year) – 3, 7, 15 days, 1 month, 6 months or 1 year; outside India from USD 12 (1 day) – 3 full profiles with resume a day.'],
            'categories_label' => ['आप किन पदों के लिए भर्ती करते हैं (सूची में खोजें)', 'Roles you hire for (search the list)'],
            'sections' => [
                [
                    'title' => ['आपकी संस्था', 'Organisation'],
                    'fields' => [
                        self::residenceField(),
                        self::radio('provider_kind', ['आप कौन हैं', 'You are'], [
                            'company' => ['कंपनी', 'Company'], 'startup' => ['स्टार्टअप', 'Startup'], 'msme' => ['दुकान / MSME / फ़ैक्टरी', 'Shop / MSME / Factory'],
                            'institute' => ['स्कूल / कॉलेज / संस्थान', 'School / College / Institute'], 'hospital' => ['अस्पताल / क्लिनिक', 'Hospital / Clinic'],
                            'ngo' => ['NGO', 'NGO'], 'individual' => ['व्यक्ति / घर के लिए', 'Individual / Household'],
                        ], true, ['full' => true]),
                        self::text('business_name', ['कंपनी / दुकान / संस्था का नाम', 'Company / shop / organisation name'], true, ['max' => 150]),
                        self::radio('based_in', ['ये नौकरियाँ कहाँ हैं', 'Where are these jobs located'], [
                            'india' => ['भारत में (शुल्क ₹ में)', 'In India (fees in ₹)'], 'abroad' => ['भारत के बाहर (शुल्क USD में – ₹ में भी भुगतान)', 'Outside India (fees in USD – payable in ₹ too)'],
                        ], true, ['full' => true]),
                        self::text('country', ['नौकरी का देश (भारत के बाहर की नौकरी के लिए)', 'Country of the jobs (for jobs outside India)'], false, ['max' => 80]),
                        ['key' => 'gstin', 'type' => 'gst', 'label' => ['GST नंबर', 'GST Number'], 'required' => false],
                        self::text('website', ['वेबसाइट (यदि हो)', 'Website (if any)'], false, ['max' => 190]),
                    ],
                ],
                self::personalSection(['name_label' => ['संपर्क व्यक्ति का नाम', 'Contact Person Name'], 'dob' => false, 'gender' => false, 'aadhaar_required' => false]),
                self::locationSection(['कंपनी / कार्यस्थल का पता', 'Company / workplace address']),
                [
                    'title' => ['नौकरियाँ', 'Jobs'],
                    'fields' => [
                        self::categories(true),
                        self::checkboxes('job_types', ['नौकरी का प्रकार', 'Job types'], [
                            'fulltime' => ['फुल-टाइम', 'Full-time'], 'parttime' => ['पार्ट-टाइम', 'Part-time'], 'wfh' => ['वर्क फ्रॉम होम', 'Work from home'], 'onetime' => ['एक बार का काम', 'One-time work'],
                        ], true, ['full' => true]),
                        self::number('positions', ['कितने लोग चाहिए', 'Number of people needed'], true, 1, 5000),
                        self::text('salary_offered', ['वेतन (₹ प्रति माह / प्रति दिन)', 'Salary (₹ per month / per day)'], false, ['max' => 80]),
                        self::textarea('about_jobs', ['नौकरी के बारे में संक्षेप में', 'About the jobs (work, timings, skills needed)'], true),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है और मैं इस संस्था की ओर से अधिकृत हूँ।', 'The information is true and I am authorised on behalf of this organisation.'],
                ['मैं उम्मीदवारों से नौकरी के बदले कोई पैसा नहीं लूँगा/लूँगी।', 'I will not take any money from candidates in return for a job.'],
                self::visaDisclaimer(),
                self::agreementRule(),
                ['उम्मीदवारों की जानकारी केवल भर्ती के लिए उपयोग होगी और किसी के साथ साझा / बेची नहीं जाएगी।', 'Candidates’ details will be used only for hiring and never shared or sold.'],
                self::platformDisclaimer(),
            ],
            'next_steps' => [
                ['Jobsence टीम आपकी जानकारी सत्यापित करेगी – उसके बाद नौकरी चाहने वाले आपको देख सकेंगे।', 'The Jobsence team will verify your details – after that job seekers can see you.'],
                ['आपकी जानकारी Jobsence (gm@jobsence.com) को भेज दी गई है; भारत के बाहर की कंपनियाँ सवाल इसी ईमेल पर भेजें।', 'Your details have been sent to Jobsence (gm@jobsence.com); companies outside India can write to this email with any questions.'],
                ['अभी “मेरा डैशबोर्ड” खोलें और भर्ती प्लान लें – ₹216 (3 दिन) से ₹19,424 (1 साल) तक – 3, 7, 15 दिन, 1 महीना, 6 महीने या 1 साल; भारत के बाहर USD 12 (1 दिन) से।', 'Open “My dashboard” now and get a hiring plan – from ₹216 (3 days) to ₹19,424 (1 year) – 3, 7, 15 days, 1 month, 6 months or 1 year; outside India from USD 12 (1 day).'],
            ],
        ];
    }

    /** Jobsence is open for these countries (country => dialling code); India first. */
    public const OPEN_COUNTRIES = [
        'India' => '91', 'Nepal' => '977', 'Sri Lanka' => '94', 'Afghanistan' => '93',
        'Bangladesh' => '880', 'China' => '86', 'Thailand' => '66',
    ];

    /**
     * "Giving skill is punya": classic lines on sharing knowledge – home page (rotating) and mentor form.
     * Original script, meaning in Hindi / English and the source as traditionally cited (public-domain classics).
     */
    public static function skillGivingQuotes(): array
    {
        return [
            ['lang' => 'sa', 'text' => "न चोरहार्यं न च राजहार्यं न भ्रातृभाज्यं न च भारकारि।\nव्यये कृते वर्धत एव नित्यं विद्याधनं सर्वधनप्रधानम्॥",
             'meaning' => ['विद्या को न चोर चुरा सकता है, न राजा छीन सकता है, न भाई बाँट सकता है, न यह बोझ है। बाँटने से यह रोज़ बढ़ती ही है – विद्या-धन सब धनों में श्रेष्ठ है।', 'Knowledge cannot be stolen by a thief, seized by a king or divided among brothers, and it is no burden. The more you give it, the more it grows – the wealth of knowledge is the greatest of all wealth.'],
             'source' => ['संस्कृत सुभाषित (पारंपरिक श्लोक)', 'Sanskrit Subhashita (traditional verse)']],
            ['lang' => 'sa', 'text' => "सर्वेषामेव दानानां ब्रह्मदानं विशिष्यते।",
             'meaning' => ['सभी दानों में ज्ञान का दान (विद्या-दान) सबसे श्रेष्ठ है।', 'Of all gifts, the gift of knowledge is the highest.'],
             'source' => ['मनुस्मृति 4.233', 'Manusmriti 4.233']],
            ['lang' => 'sa', 'text' => "न हि ज्ञानेन सदृशं पवित्रमिह विद्यते।",
             'meaning' => ['इस संसार में ज्ञान के समान पवित्र करने वाला कुछ भी नहीं है।', 'In this world there is nothing as purifying as knowledge.'],
             'source' => ['श्रीमद्भगवद्गीता 4.38', 'Bhagavad Gita 4.38']],
            ['lang' => 'hi', 'text' => "सब धरती कागद करूँ, लेखनी सब बनराय।\nसात समुद की मसि करूँ, गुरु गुन लिखा न जाय॥",
             'meaning' => ['सारी धरती को कागज़, सारे जंगल को कलम और सातों समुद्र को स्याही बना लूँ, तब भी गुरु के गुण नहीं लिखे जा सकते।', 'Were the whole earth paper, every forest a pen and the seven seas ink, the virtues of a teacher still could not be written.'],
             'source' => ['संत कबीर, साखी (गुरुदेव को अंग)', 'Sant Kabir, Sakhi (Gurudev ko Ang)']],
            ['lang' => 'en', 'text' => "The quality of mercy is not strain'd…\nIt blesseth him that gives and him that takes.",
             'meaning' => ['सच्चा दान देने वाले और पाने वाले – दोनों को आशीर्वाद देता है।', 'True giving blesses both the one who gives and the one who receives.'],
             'source' => ['विलियम शेक्सपियर, द मर्चेंट ऑफ़ वेनिस, अंक 4 दृश्य 1 (पोर्शिया)', 'William Shakespeare, The Merchant of Venice, Act IV Scene 1 (Portia)']],
            ['lang' => 'en', 'text' => "He who receives an idea from me, receives instruction himself without lessening mine; as he who lights his taper at mine, receives light without darkening me.",
             'meaning' => ['जो मुझसे ज्ञान लेता है वह सीखता है, पर मेरा ज्ञान कम नहीं होता – जैसे मेरे दीये से दीया जलाने वाले को रोशनी मिलती है और मेरा दीया मंद नहीं होता।', 'Sharing knowledge takes nothing from the giver – like lighting another candle from your own.'],
             'source' => ['थॉमस जेफ़र्सन, आइज़ैक मैकफ़र्सन को पत्र, 13 अगस्त 1813', 'Thomas Jefferson, letter to Isaac McPherson, 13 August 1813']],
            ['lang' => 'zh', 'text' => "学而不厌，诲人不倦。",
             'meaning' => ['सीखते हुए कभी तृप्त न होना, और दूसरों को सिखाते हुए कभी न थकना।', 'Never tire of learning, never weary of teaching others.'],
             'source' => ['कन्फ़्यूशियस, लुनयू (論語) 7.2 – शुएर', 'Confucius, Analects (論語) 7.2 – Shu Er']],
            ['lang' => 'zh', 'text' => "授人以鱼，不如授人以渔。",
             'meaning' => ['किसी को मछली देने से अच्छा है, उसे मछली पकड़ना सिखा देना।', 'Giving someone a fish is not as good as teaching them to fish.'],
             'source' => ['पारंपरिक चीनी कहावत', 'Traditional Chinese proverb']],
            ['lang' => 'ur', 'dir' => 'rtl', 'text' => "نہیں ہے ناامید اقبالؔ اپنی کشتِ ویراں سے\nذرا نم ہو تو یہ مٹی بہت زرخیز ہے ساقی",
             'meaning' => ['इक़बाल अपनी वीरान खेती से निराश नहीं है – ज़रा-सी नमी मिले तो यह मिट्टी बहुत उपजाऊ है। (थोड़ा-सा मार्गदर्शन मिले तो हमारे युवा बहुत कुछ कर सकते हैं।)', 'Iqbal does not despair of his barren field – with a little moisture this soil is very fertile. (Give our youth a little guidance and they will flourish.)'],
             'source' => ['अल्लामा इक़बाल', 'Allama Iqbal']],
            ['lang' => 'ar', 'dir' => 'rtl', 'text' => "إذا مات الإنسان انقطع عنه عمله إلا من ثلاثة: صدقة جارية، أو علم يُنتفع به، أو ولد صالح يدعو له",
             'meaning' => ['मनुष्य के जाने के बाद उसके कर्म रुक जाते हैं, सिवाय तीन के: निरंतर चलने वाला दान, ऐसा ज्ञान जिससे लोग लाभ उठाते रहें, और नेक संतान जो उसके लिए दुआ करे।', 'When a person dies, their deeds end except three: a continuing charity, knowledge that others keep benefiting from, and a righteous child who prays for them.'],
             'source' => ['हदीस, सहीह मुस्लिम 1631', 'Hadith, Sahih Muslim 1631']],
        ];
    }

    /**
     * Countries a resident of which may use $form, with dialling codes: India + the open countries, or
     * every country (Pakistan excluded by policy) for forms marked 'any_country' such as mentors.
     */
    public static function residenceCountries(array $form = []): array
    {
        if (empty($form['any_country'])) {
            return self::OPEN_COUNTRIES;
        }
        static $world = null;
        return $world ??= ['India' => '91'] + (require dirname(__DIR__, 3) . '/resources/data/dial_codes.php');
    }

    /** Bilingual "country of residence" field; $anyCountry lists every country instead of the open ones. */
    private static function residenceField(bool $anyCountry = false): array
    {
        $opts = [];
        foreach (array_keys(self::residenceCountries(['any_country' => $anyCountry])) as $c) {
            $opts[$c] = $c === 'India' ? ['भारत', 'India'] : [$c, $c];
        }
        return ['key' => 'residence_country', 'type' => 'select', 'label' => ['आप किस देश में रहते हैं', 'Country of residence'], 'options' => $opts, 'required' => true, 'full' => true,
            // India pre-selected and shown first; the other countries follow A–Z in their own group.
            'default' => 'India', 'pinned' => ['India'], 'pinned_label' => ['भारत', 'India'], 'others_label' => ['भारत के बाहर (A–Z)', 'Outside India (A–Z)'],
            'hint' => ['भारत के बाहर: मोबाइल नंबर देश कोड के साथ (जैसे +977) और राज्य की जगह अपना देश चुनें।', 'Outside India: give your mobile with country code (e.g. +977) and pick your country instead of a state.']];
    }

    /** Jobs abroad: candidate verifies every job with the embassy / consulate or the country's ministry; Jobsence is not liable. */
    public static function abroadDisclaimer(): array
    {
        return [
            'Jobsence केवल एक प्लेटफ़ॉर्म है। विदेश की किसी भी नौकरी और नियोक्ता की सच्चाई उम्मीदवार स्वयं उस देश के भारत स्थित दूतावास / कॉन्सुलेट से, या सीधे उस देश की सरकार / श्रम मंत्रालय से, और भारत के eMigrate (emigrate.gov.in) पर पक्का करेगा। किसी भी धोखाधड़ी, जालसाज़ी या ठगी के लिए Jobsence ज़िम्मेदार नहीं है।',
            'Jobsence is only a platform. The candidate will double-check every job and employer abroad personally – with that country’s embassy / consulate in India, or directly with that country’s government / labour ministry, and on India’s eMigrate (emigrate.gov.in). Jobsence is not liable for any cheating or fraud.',
        ];
    }

    /** Visa: Indian employment visa / clearance mandatory for foreigners; any other country's visa is the parties' sole responsibility. */
    public static function visaDisclaimer(): array
    {
        return [
            'भारत में नौकरी के लिए विदेशी नागरिक के पास वैध भारतीय रोज़गार वीज़ा और ज़रूरी सरकारी मंज़ूरी होना अनिवार्य है। किसी भी दूसरे देश का वीज़ा, वर्क परमिट और मंज़ूरी पूरी तरह नौकरी चाहने वाले या नौकरी देने वाले की ज़िम्मेदारी है – इसमें Jobsence की कोई भूमिका या ज़िम्मेदारी नहीं है।',
            'For a job in India, a foreign national must hold a valid Indian employment visa and the mandatory government clearance. The visa, work permit and clearances for any other country are solely the responsibility of the job seeker or the job giver – Jobsence has no role or responsibility in them.',
        ];
    }

    /** Jobs abroad: free registration, then ₹1,000 + 18% GST (₹1,180) or USD 10 per country, one-time. */
    public const INTL_COUNTRY_FEE_INR = 1180.00;
    public const INTL_COUNTRY_FEE_USD = 10.00;

    private static function internationalJob(): array
    {
        return [
            'type' => 'intljob',
            'international' => true,
            'prefix' => 'IJB',
            'side' => 'candidate',
            'icon' => '🌍',
            'otp' => true,
            'multipart' => true,
            'fee' => 0.0,
            'max_categories' => 5,
            'title' => ['विदेश में नौकरी – मुफ़्त रजिस्ट्रेशन', 'Jobs Abroad – Free Registration'],
            'button' => ['विदेश में नौकरी चाहिए? मुफ़्त रजिस्टर करें', 'Looking for a job abroad? Register free'],
            'intro' => ['रजिस्ट्रेशन मुफ़्त है। जिस देश की कंपनियों को आपकी प्रोफ़ाइल दिखानी है, उस हर देश के लिए एक बार ₹1,000 + GST (₹1,180) या USD 10।', 'Registration is free. For each country whose employers should see your profile, pay once: ₹1,000 + GST (₹1,180) or USD 10.'],
            'categories_label' => ['आप कौन सी नौकरी चाहते हैं – अधिकतम 5', 'Jobs you want – up to 5'],
            'info' => [
                'title' => ['ज़रूरी जानकारी', 'Important'],
                'points' => [
                    ['हर देश के लिए एकमुश्त ₹1,180 (₹1,000 + 18% GST) या USD 10 – किसी भी स्थिति में वापसी योग्य नहीं।', 'Each country: one-time ₹1,180 (₹1,000 + 18% GST) or USD 10 – non-refundable in any condition.'],
                    self::abroadDisclaimer(),
                    self::visaDisclaimer(),
                    ['वैध पासपोर्ट ज़रूरी है – पासपोर्ट नंबर और समाप्ति तिथि भरें।', 'A valid passport is mandatory – enter its number and expiry date.'],
                    ['Jobsence एक प्लेटफ़ॉर्म है, रिक्रूटिंग एजेंट नहीं। ECR पासपोर्ट वाले केवल eMigrate (emigrate.gov.in) पर पंजीकृत एजेंट के माध्यम से विदेश जाएँ और नियोक्ता की जाँच करें।', 'Jobsence is a platform, not a recruiting agent. ECR passport holders must emigrate only through agents registered on eMigrate (emigrate.gov.in), and should verify every employer there.'],
                    self::platformDisclaimer(),
                ],
            ],
            'sections' => [
                ['title' => ['निवास', 'Residence'], 'fields' => [self::residenceField()]],
                self::personalSection(['name_label' => ['पूरा नाम (पासपोर्ट के अनुसार)', 'Full Name (as per passport)']]),
                self::addressSection(false),
                [
                    'title' => ['विदेश में नौकरी', 'Job Abroad'],
                    'fields' => [
                        self::text('preferred_countries', ['पसंदीदा देश (कॉमा से अलग)', 'Preferred countries (comma separated)'], true, ['full' => true, 'max' => 255,
                            'hint' => ['जैसे: UAE, Saudi Arabia, Canada, Germany', 'e.g. UAE, Saudi Arabia, Canada, Germany']]),
                        self::radio('passport_status', ['पासपोर्ट (ज़रूरी)', 'Passport (mandatory)'], [
                            'ecnr' => ['वैध पासपोर्ट – ECNR', 'Valid passport – ECNR'], 'ecr' => ['वैध पासपोर्ट – ECR', 'Valid passport – ECR'],
                        ], true, ['full' => true]),
                        ['key' => 'passport', 'type' => 'passport', 'label' => ['पासपोर्ट नंबर', 'Passport number'], 'required' => true,
                            'hint' => ['सुरक्षा के लिए एन्क्रिप्ट करके रखा जाता है।', 'Stored encrypted for your safety.']],
                        ['key' => 'passport_expiry', 'type' => 'date', 'date_mode' => 'expiry', 'label' => ['पासपोर्ट की समाप्ति तिथि', 'Passport expiry date'], 'required' => true],
                        self::categories(true),
                        self::qualification(),
                        self::radio('experience', ['कुल अनुभव', 'Total Experience'], [
                            'fresher' => ['फ्रेशर', 'Fresher'], '0_1' => ['1 साल से कम', 'Less than 1 year'], '1_3' => ['1–3 साल', '1–3 years'],
                            '3_5' => ['3–5 साल', '3–5 years'], '5_10' => ['5–10 साल', '5–10 years'], '10_plus' => ['10+ साल', '10+ years'],
                        ], true, ['full' => true]),
                        self::number('expected_salary_usd', ['अपेक्षित वेतन (USD प्रति माह)', 'Expected salary (USD per month)'], false, 0, 100000),
                        self::languagesField(['आप कौन सी भाषाएँ जानते हैं', 'Languages you know'], false),
                        self::textarea('about_me', ['अपने बारे में संक्षेप में (स्किल, अनुभव, सर्टिफ़िकेट)', 'About you (skills, experience, certificates)'], false),
                        self::file('resume', ['रिज़्यूमे / CV (PDF/DOC, अधिकतम 5MB)', 'Resume / CV (PDF/DOC, max 5MB)']),
                    ],
                ],
            ],
            'declaration' => [
                ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
                ['रजिस्ट्रेशन मुफ़्त है; हर देश के लिए ₹1,180 या USD 10 का एकमुश्त शुल्क किसी भी स्थिति में वापसी योग्य नहीं है।', 'Registration is free; the one-time fee of ₹1,180 or USD 10 per country is non-refundable in any condition.'],
                ['Jobsence रिक्रूटिंग एजेंट नहीं है और विदेश में नौकरी या वीज़ा की गारंटी नहीं देता। मैं विदेश जाने से पहले नियोक्ता और एजेंट की जाँच eMigrate पर करूँगा/करूँगी।', 'Jobsence is not a recruiting agent and does not guarantee any job or visa abroad. I will verify the employer and any agent on eMigrate before travelling.'],
                ['मैं किसी को भी नौकरी या वीज़ा के लिए पैसे नहीं दूँगा/दूँगी और ऐसी माँग की शिकायत Jobsence से करूँगा/करूँगी।', 'I will not pay anyone money for a job or visa and will report any such demand to Jobsence.'],
                self::abroadDisclaimer(),
                self::visaDisclaimer(),
                self::platformDisclaimer(),
                self::notGovt(),
            ],
            'next_steps' => [
                ['अभी “मेरा डैशबोर्ड” खोलें और जिन देशों में आपकी प्रोफ़ाइल दिखानी है उन्हें अनलॉक करें (हर देश ₹1,180 या USD 10)।', 'Open “My dashboard” now and unlock the countries where your profile should be shown (₹1,180 or USD 10 each).'],
                ['उस देश में भर्ती करने वाली कंपनियाँ आपकी प्रोफ़ाइल देखकर त्रिपक्षीय समझौता भेजेंगी।', 'Companies hiring in that country see your profile and send a tripartite agreement.'],
            ],
        ];
    }

    /** One country unlocked for a jobs-abroad seeker (bought from the dashboard). */
    /** Employers pay (user, 2026-10-06): free board posts beyond FreeJobPost::FREE_POSTS_PER_MONTH cost ₹200 + 18% GST each. */
    private static function extraJobPost(): array
    {
        return [
            'type' => 'jobpost', 'prefix' => 'JPS', 'side' => 'service', 'icon' => '📢', 'internal' => true, 'hidden_from_hub' => true,
            'fee' => \App\Models\FreeJobPost::EXTRA_POST_FEE,
            'title' => ['अतिरिक्त नौकरी पोस्ट', 'Extra job post'],
            'button' => ['अतिरिक्त नौकरी पोस्ट', 'Extra job post'],
            'intro' => ['इस महीने की मुफ़्त पोस्ट पूरी हो गईं – यह पोस्ट ₹200 + GST में 30 दिन तक लाइव रहेगी।', 'This month’s free posts are used – this post goes live for 30 days for ₹200 + GST.'],
            'sections' => [],
            'declaration' => [self::platformDisclaimer()],
            'next_steps' => [
                ['आपकी नौकरी अब राज्य और शहर की सूची में लाइव है।', 'Your job is now live in the state & city list.'],
            ],
        ];
    }

    private static function countryUnlock(): array
    {
        return [
            'type' => 'intlcountry', 'prefix' => 'ICU', 'side' => 'service', 'icon' => '🌍', 'internal' => true, 'hidden_from_hub' => true,
            'fee' => self::INTL_COUNTRY_FEE_INR,
            'title' => ['देश अनलॉक – विदेश में नौकरी', 'Country unlock – Jobs Abroad'],
            'button' => ['देश अनलॉक', 'Country unlock'],
            'intro' => ['इस देश की कंपनियाँ आपकी प्रोफ़ाइल देख सकेंगी।', 'Employers in this country can see your profile.'],
            'sections' => [],
            'declaration' => [self::platformDisclaimer()],
            'next_steps' => [
                ['इस देश में भर्ती करने वाली कंपनियाँ अब आपकी प्रोफ़ाइल देख सकती हैं।', 'Companies hiring in this country can now see your profile.'],
                ['और देश जोड़ने के लिए अपना डैशबोर्ड खोलें।', 'Open your dashboard to add more countries.'],
            ],
        ];
    }

    /** Paid plan for a registered provider (bought from the mentoring dashboard, never filled as a form). */
    private static function providerPlan(string $type): array
    {
        if ($type === 'jobplan') {
            return [
                'type' => 'jobplan', 'prefix' => 'JPL', 'side' => 'service', 'icon' => '🏭', 'internal' => true, 'hidden_from_hub' => true,
                'fee' => self::PROVIDER_PLAN_FEE,
                'title' => ['भर्ती प्लान', 'Hiring Plan'],
                'button' => ['भर्ती प्लान', 'Hiring Plan'],
                'intro' => ['रोज़ 3 नौकरी चाहने वालों की पूरी प्रोफ़ाइल, रिज़्यूमे और समझौता – 3 दिन (₹216) से 1 साल (₹19,424) तक; भारत के बाहर USD 12 से।', 'Full profile, resume and agreement for 3 job seekers a day – 3 days (₹216) to 1 year (₹19,424); outside India from USD 12.'],
                'sections' => [],
                'declaration' => [self::agreementRule(), self::platformDisclaimer()],
                'next_steps' => [['अपने डैशबोर्ड से उम्मीदवारों की प्रोफ़ाइल खोलें और समझौता भेजें।', 'Open candidate profiles from your dashboard and send agreements.']],
            ];
        }
        $mentor = $type === 'mentorplan';
        return [
            'type' => $type,
            'prefix' => $mentor ? 'MPL' : 'IPL',
            'side' => 'service',
            'icon' => $mentor ? '👩‍🏫' : '🏢',
            'internal' => true,
            'hidden_from_hub' => true,
            'fee' => self::PROVIDER_PLAN_FEE,
            'title' => $mentor ? ['मेंटर प्लान – 30 दिन', 'Mentor Plan – 30 days'] : ['इंटर्नशिप प्रदाता प्लान – 10 दिन', 'Internship Provider Plan – 10 days'],
            'button' => $mentor ? ['मेंटर प्लान', 'Mentor Plan'] : ['इंटर्नशिप प्लान', 'Internship Plan'],
            'intro' => $mentor
                ? ['₹155 (GST सहित; भारत के बाहर USD 5): 30 दिन तक रोज़ 3 उम्मीदवारों की पूरी प्रोफ़ाइल और समझौता।', '₹155 (incl. GST; USD 5 outside India): full profile and agreement for 3 candidates a day for 30 days.']
                : ['₹155 (GST सहित; भारत के बाहर USD 5): 10 दिन तक रोज़ 3 उम्मीदवारों की पूरी प्रोफ़ाइल, अधिकतम 20।', '₹155 (incl. GST; USD 5 outside India): full profile for 3 candidates a day for 10 days, up to 20 in total.'],
            'sections' => [],
            'declaration' => [self::agreementRule(), self::platformDisclaimer()],
            'next_steps' => [
                ['अपने मेंटरिंग डैशबोर्ड से उम्मीदवारों की प्रोफ़ाइल खोलें और समझौता भेजें।', 'Open candidate profiles from your mentoring dashboard and send agreements.'],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Shared pieces
    // ------------------------------------------------------------------

    public const QUALIFICATIONS = [
        'illiterate' => ['अशिक्षित', 'Uneducated'],
        '5th_8th' => ['5वीं–8वीं', '5th–8th'],
        '10th' => ['10वीं पास', '10th Pass'],
        '12th' => ['12वीं पास', '12th Pass'],
        'iti_diploma' => ['आईटीआई / डिप्लोमा', 'ITI / Diploma'],
        'graduate' => ['स्नातक', 'Graduate'],
        'post_graduate' => ['स्नातकोत्तर / प्रोफेशनल', 'Post Graduate / Professional'],
        'other' => ['अन्य', 'Other'],
    ];

    public const CANDIDATE_STATUSES = [
        'unemployed' => ['बेरोज़गार', 'Unemployed'],
        'student' => ['विद्यार्थी', 'Student'],
        'daily_wage' => ['दिहाड़ी मज़दूर', 'Daily wage worker'],
        'self_employed' => ['स्व-रोज़गार (कम आय)', 'Self-employed (low income)'],
        'homemaker' => ['गृहिणी', 'Homemaker'],
        'other' => ['अन्य', 'Other'],
    ];

    public const LANGUAGES = [
        'hindi' => ['हिंदी', 'Hindi'],
        'english' => ['अंग्रेज़ी', 'English'],
        'regional' => ['स्थानीय / क्षेत्रीय भाषा', 'Local / Regional language'],
        'mix' => ['मिश्रित (मेंटर के अनुसार)', 'Mix (as per mentor availability)'],
    ];

    private static function personalSection(array $opt): array
    {
        $fields = [
            self::text('full_name', $opt['name_label'] ?? ['पूरा नाम (आधार के अनुसार)', 'Full Name (as per Aadhaar)'], true, ['max' => 150, 'autocomplete' => 'name']),
        ];
        if (!empty($opt['guardian'])) {
            $fields[] = self::text('guardian_name', ['पिता / माता / अभिभावक का नाम', 'Father’s / Mother’s / Guardian’s Name'], false, ['max' => 150]);
        }
        if ($opt['dob'] ?? true) {
            $fields[] = ['key' => 'dob', 'type' => 'date', 'label' => ['जन्म तिथि', 'Date of Birth'], 'required' => true]
                + (isset($opt['dob_mode']) ? ['date_mode' => $opt['dob_mode']] : []);
        }
        if ($opt['gender'] ?? true) {
            $fields[] = self::radio('gender', ['लिंग', 'Gender'], ['male' => ['पुरुष', 'Male'], 'female' => ['महिला', 'Female'], 'other' => ['अन्य', 'Other']], true);
        }
        $fields[] = ['key' => 'mobile', 'type' => 'mobile', 'label' => ['मोबाइल नंबर (WhatsApp)', 'Mobile Number (WhatsApp)'], 'required' => true, 'autocomplete' => 'tel'];
        $fields[] = ['key' => 'whatsapp', 'type' => 'mobile', 'label' => ['WhatsApp नंबर (अलग हो तो)', 'WhatsApp Number (if different)'], 'required' => false];
        $fields[] = ['key' => 'email', 'type' => 'email', 'label' => ['ईमेल', 'Email ID'], 'required' => true, 'autocomplete' => 'email',
            'hint' => ['पुष्टि ईमेल इसी पते पर आएगा।', 'Confirmation email will be sent here.']];
        $fields[] = ['key' => 'aadhaar', 'type' => 'aadhaar', 'label' => $opt['aadhaar_label'] ?? ['आधार नंबर', 'Aadhaar Number'], 'required' => (bool)($opt['aadhaar_required'] ?? false),
            'hint' => $opt['aadhaar_hint'] ?? ['सुरक्षा के लिए हम केवल आख़िरी 4 अंक सेव करते हैं।', 'For your safety we store only the last 4 digits.']]
            + (isset($opt['aadhaar_optional_when']) ? ['optional_when' => $opt['aadhaar_optional_when']] : []);

        return ['title' => ['व्यक्तिगत जानकारी', 'Personal Details'], 'fields' => $fields];
    }

    private static function addressSection(bool $full, bool $villageRequired = false): array
    {
        $fields = [];
        if ($full) {
            $fields[] = self::text('address_line', ['मकान / गली / लैंडमार्क', 'House / Street / Landmark'], true, ['full' => true, 'max' => 255, 'autocomplete' => 'street-address']);
        }
        $fields[] = self::text('village', ['गाँव / कस्बा / शहर का इलाका', 'Village / Town / Locality'], $full || $villageRequired);
        $fields[] = self::text('district', ['ज़िला', 'District'], true, ['max' => 120]);
        $fields[] = self::text('city', ['शहर', 'City'], false, ['max' => 120, 'autocomplete' => 'address-level2']);
        $fields[] = ['key' => 'state', 'type' => 'state', 'label' => ['राज्य / केंद्र शासित प्रदेश', 'State / UT'], 'required' => true];
        $fields[] = ['key' => 'pincode', 'type' => 'pincode', 'label' => ['पिन कोड', 'PIN Code'], 'required' => true, 'autocomplete' => 'postal-code'];
        if ($full) {
            $fields[] = self::text('landmark', ['नज़दीकी लैंडमार्क / थाना', 'Nearest Landmark / Police Station'], false);
        }

        return ['title' => ['पूरा पता', 'Complete Address'], 'fields' => $fields];
    }

    private static function qualification(): array
    {
        return self::radio('qualification', ['उच्चतम योग्यता', 'Highest Qualification'], self::QUALIFICATIONS, true, ['full' => true]);
    }

    private static function categories(bool $required): array
    {
        return ['key' => 'categories', 'type' => 'categories', 'label' => null, 'required' => $required, 'full' => true];
    }

    private static function languagesField(array $label, bool $withMix): array
    {
        $opts = self::LANGUAGES;
        if (!$withMix) {
            unset($opts['mix']);
        }
        return self::checkboxes('languages', $label, $opts, true, ['other' => 'language_other', 'other_placeholder' => ['स्थानीय भाषा का नाम (जैसे भोजपुरी, मराठी, तमिल)', 'Name of local language (e.g. Bhojpuri, Marathi, Tamil)']]);
    }

    private static function workMode(bool $full): array
    {
        return self::radio('work_mode', ['काम का तरीका', 'Work Mode'], [
            'onsite' => ['ऑफिस / साइट पर', 'On-site / Office'],
            'wfh' => ['वर्क फ्रॉम होम', 'Work from Home'],
            'either' => ['कोई भी', 'Either'],
        ], true, $full ? ['full' => true] : []);
    }

    private static function workingHours(): array
    {
        return self::checkboxes('working_hours', ['पसंदीदा काम के घंटे', 'Preferred Working Hours'], [
            'morning' => ['सुबह (6–12)', 'Morning (6am–12pm)'],
            'afternoon' => ['दोपहर (12–5)', 'Afternoon (12–5pm)'],
            'evening' => ['शाम (5–10)', 'Evening (5–10pm)'],
            'night' => ['रात', 'Night'],
            'flexible' => ['कोई भी समय', 'Flexible'],
        ], true);
    }

    private static function notGovt(): array
    {
        return ['यह भारत को कुशल बनाने की Jobsence पहल है – भारत सरकार की योजना नहीं।', 'This is Jobsence’s initiative to make India skilled – not a Government of India scheme.'];
    }

    /** Platform liability disclaimer (part-time gigs, hirer pass, jobs pass). */
    public static function platformDisclaimer(): array
    {
        return [
            'Jobsence केवल एक प्लेटफ़ॉर्म है। हम उम्मीदवारों की जानकारी की पूरी सावधानी से जाँच करने की कोशिश करते हैं, लेकिन नौकरी चाहने वालों या नौकरी देने वालों के बीच किसी भी धोखाधड़ी, जालसाज़ी या अनहोनी की ज़िम्मेदारी Jobsence की नहीं होगी।',
            'Jobsence is only a platform. We take utmost care to verify the credentials of candidates, but Jobsence will not take any responsibility for cheating, forgery or any mishappening between job seekers and job providers.',
        ];
    }

    private static function commonJobDeclaration(string $hiWord, string $enWord, string $fee = '₹155'): array
    {
        return [
            ['मेरे द्वारा दी गई जानकारी सही है।', 'The information provided by me is true and correct.'],
            ["{$fee} का एकमुश्त प्रोसेसिंग शुल्क (GST सहित) वापसी योग्य नहीं है।", "The one-time processing fee of {$fee} (including GST) is non-refundable."],
            ["Jobsence किसी {$hiWord} या प्लेसमेंट की गारंटी नहीं देता।", "Jobsence does not guarantee any {$enWord} or placement."],
            ['Jobsence मेरी प्रोफ़ाइल को उपयुक्त कंपनियों तक पहुँचाने की कोशिश करेगा; मिलान में समय लग सकता है।', 'Jobsence will try to share my profile with suitable companies; matching may take time.'],
            ['मैं Jobsence या कंपनियों द्वारा कॉल / WhatsApp / ईमेल से संपर्क किए जाने के लिए सहमत हूँ।', 'I agree to be contacted by Jobsence or companies via call / WhatsApp / email.'],
        ];
    }

    private static function commonJobNextSteps(string $hiWord, string $enWord): array
    {
        return [
            ['हमारी टीम आपकी प्रोफ़ाइल की जाँच करेगी और उपयुक्त कंपनियों तक पहुँचाने की कोशिश करेगी।', 'Our team will review your profile and try to share it with suitable companies.'],
            ["उपयुक्त {$hiWord} मिलने पर आपसे कॉल / WhatsApp / ईमेल से संपर्क किया जाएगा।", "When a suitable {$enWord} matches, you will be contacted by call / WhatsApp / email."],
        ];
    }

    private static function text(string $key, array $label, bool $required, array $extra = []): array
    {
        return ['key' => $key, 'type' => 'text', 'label' => $label, 'required' => $required] + $extra;
    }

    private static function textarea(string $key, array $label, bool $required): array
    {
        return ['key' => $key, 'type' => 'textarea', 'label' => $label, 'required' => $required, 'full' => true, 'max' => 2000];
    }

    private static function number(string $key, array $label, bool $required, int $min, int $max): array
    {
        return ['key' => $key, 'type' => 'number', 'label' => $label, 'required' => $required, 'min' => $min, 'maxv' => $max];
    }

    private static function radio(string $key, array $label, array $options, bool $required, array $extra = []): array
    {
        return ['key' => $key, 'type' => 'radio', 'label' => $label, 'required' => $required, 'options' => $options] + $extra;
    }

    private static function checkboxes(string $key, array $label, array $options, bool $required, array $extra = []): array
    {
        return ['key' => $key, 'type' => 'checkboxes', 'label' => $label, 'required' => $required, 'options' => $options, 'full' => true] + $extra;
    }

    private static function file(string $key, array $label): array
    {
        return ['key' => $key, 'type' => 'file', 'label' => $label, 'required' => false, 'full' => true];
    }
}
