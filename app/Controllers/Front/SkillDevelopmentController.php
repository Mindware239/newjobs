<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\PortalRegistration;
use App\Services\SeoService;

/**
 * Jobsence Skill Development Centre – SEO landing page (Pan-India + one page per State/UT).
 * The registration forms themselves live in ApplyController (/apply/skill-development, /apply/skill-provider).
 */
class SkillDevelopmentController extends BaseController
{
    public const KEYWORDS = 'skill development in india, free skill development course, skill development course online, skill development course with certificate, job oriented courses, vocational courses, short term courses after 12th, courses after 10th, basic computer course, tally course, advanced excel course, spoken english course, digital marketing course, ai course for beginners, data analytics course, electrician course, beautician course, tailoring course, mobile repairing course, skill training for unemployed youth, offline classes delhi ncr, online video classes india, become a mentor, trainer jobs, कौशल विकास कोर्स, फ्री कंप्यूटर कोर्स, फ्री कोर्स सर्टिफिकेट के साथ, ghar baithe kaam, naukri chahiye, jobs, jobs in india, internship, best job portal';
    public const KEYWORDS_JOBS = 'jobs for freshers, fresher jobs 2026, jobs near me, work from home jobs, work from home jobs without investment, online jobs from home, part time jobs, part time jobs for students, 10th pass jobs, 12th pass jobs, private jobs, jobs for women, data entry jobs, bpo jobs, call center jobs, customer support jobs, telecaller jobs, delivery boy jobs, driver jobs, sales executive jobs, back office jobs, accountant jobs, hotel jobs, nursing jobs, it jobs for freshers, remote jobs india, jobs in delhi, jobs in mumbai, jobs in bangalore, नौकरी चाहिए, घर बैठे नौकरी, 10वीं पास नौकरी, 12वीं पास नौकरी, पार्ट टाइम जॉब';
    public const KEYWORDS_INTERNSHIP = 'internship for students, paid internship, internship with stipend, online internship, work from home internship, summer internship 2026, winter internship 2026, internship for freshers, internship near me, internship with certificate, virtual internship, part time internship, internship for college students, internship for 1st year students, digital marketing internship, hr internship, finance internship, content writing internship, graphic design internship, web development internship, data science internship, ai ml internship, python internship, internship in delhi, internship in bangalore, इंटर्नशिप कैसे करें';
    public const KEYWORDS_LEGACY = 'skill development in india, skill development for unemployed youth India, skills, jobs, jobs in india, best job portal, internship, internship in india, work from home jobs, part time jobs, full time jobs, job oriented courses Delhi NCR, online skill development Pan India, Jobsence skill centre, Jobsence skill initiative, skill training for uneducated, driver training courses, electrician plumbing courses, self employment skills India, resume push after skill training, offline classes Delhi NCR, video skill classes India, कौशल विकास, स्किल ट्रेनिंग, बेरोजगार युवा, नौकरी, इंटर्नशिप';

    /** slug => [English, Hindi] */
    public const STATES = [
        'andhra-pradesh' => ['Andhra Pradesh', 'आंध्र प्रदेश'],
        'arunachal-pradesh' => ['Arunachal Pradesh', 'अरुणाचल प्रदेश'],
        'assam' => ['Assam', 'असम'],
        'bihar' => ['Bihar', 'बिहार'],
        'chhattisgarh' => ['Chhattisgarh', 'छत्तीसगढ़'],
        'goa' => ['Goa', 'गोवा'],
        'gujarat' => ['Gujarat', 'गुजरात'],
        'haryana' => ['Haryana', 'हरियाणा'],
        'himachal-pradesh' => ['Himachal Pradesh', 'हिमाचल प्रदेश'],
        'jharkhand' => ['Jharkhand', 'झारखंड'],
        'karnataka' => ['Karnataka', 'कर्नाटक'],
        'kerala' => ['Kerala', 'केरल'],
        'madhya-pradesh' => ['Madhya Pradesh', 'मध्य प्रदेश'],
        'maharashtra' => ['Maharashtra', 'महाराष्ट्र'],
        'manipur' => ['Manipur', 'मणिपुर'],
        'meghalaya' => ['Meghalaya', 'मेघालय'],
        'mizoram' => ['Mizoram', 'मिज़ोरम'],
        'nagaland' => ['Nagaland', 'नागालैंड'],
        'odisha' => ['Odisha', 'ओडिशा'],
        'punjab' => ['Punjab', 'पंजाब'],
        'rajasthan' => ['Rajasthan', 'राजस्थान'],
        'sikkim' => ['Sikkim', 'सिक्किम'],
        'tamil-nadu' => ['Tamil Nadu', 'तमिलनाडु'],
        'telangana' => ['Telangana', 'तेलंगाना'],
        'tripura' => ['Tripura', 'त्रिपुरा'],
        'uttar-pradesh' => ['Uttar Pradesh', 'उत्तर प्रदेश'],
        'uttarakhand' => ['Uttarakhand', 'उत्तराखंड'],
        'west-bengal' => ['West Bengal', 'पश्चिम बंगाल'],
        'andaman-and-nicobar-islands' => ['Andaman and Nicobar Islands', 'अंडमान और निकोबार द्वीप समूह'],
        'chandigarh' => ['Chandigarh', 'चंडीगढ़'],
        'dadra-nagar-haveli-and-daman-diu' => ['Dadra and Nagar Haveli and Daman and Diu', 'दादरा और नगर हवेली और दमन और दीव'],
        'delhi' => ['Delhi', 'दिल्ली'],
        'jammu-and-kashmir' => ['Jammu and Kashmir', 'जम्मू और कश्मीर'],
        'ladakh' => ['Ladakh', 'लद्दाख'],
        'lakshadweep' => ['Lakshadweep', 'लक्षद्वीप'],
        'puducherry' => ['Puducherry', 'पुडुचेरी'],
    ];


    public const POPULAR_SKILLS = [
        'Driving (LMV/HGV)', 'Electrician', 'Plumbing', 'Welding', 'Tailoring', 'Beauty & Wellness',
        'Digital Marketing', 'Data Entry', 'Computer Basics', 'Mobile Repairing', 'AC/Refrigerator Repair',
        'Nursing Assistant', 'Retail Sales', 'Security Guard', 'Cooking / Bakery', 'Agriculture', 'Spoken English', 'Self-employment skills',
    ];

    public function index(Request $request, Response $response): void
    {
        $this->renderLanding($response, null);
    }

    public function state(Request $request, Response $response): void
    {
        $slug = strtolower((string)$request->param('state'));
        if (!isset(self::STATES[$slug])) {
            $response->redirect('/skill-development', 301);
            return;
        }
        $this->renderLanding($response, $slug);
    }

    /** GET /skills – public list of skills we train in and skills we need mentors for. */
    public function skills(Request $request, Response $response): void
    {
        SeoService::getInstance()->setMeta([
            'title' => 'Skills, Mentors & Training in India | Skill Development Courses | Become a Mentor – Jobsence',
            'description' => 'Skills Jobsence offers training in (online video classes across India or offline in Delhi-NCR) and the skills we are looking for mentors in. Learners choose up to 5 skills. Registration is free for learners.',
            'keywords' => self::KEYWORDS,
            'canonical' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . '/skills',
            'robots' => 'index, follow',
        ]);
        $response->view('front/skill-development/skills', [
            'fee' => \App\Services\Registration\FormRegistry::feeOf(\App\Services\Registration\FormRegistry::get('skill-development') ?? []),
            'whatsappNumber' => preg_replace('/\D/', '', (string)($_ENV['SKILL_WHATSAPP_NUMBER'] ?? $_ENV['WHATSAPP_NUMBER'] ?? '')),
        ], 200, 'layout');
    }

    /** Old form URL – now served by the shared /apply forms. */
    public function legacyRegister(Request $request, Response $response): void
    {
        $response->redirect($request->get('type') === 'mentor' ? '/apply/skill-provider' : '/apply/skill-development', 301);
    }

    private function renderLanding(Response $response, ?string $stateSlug): void
    {
        $stateEn = $stateSlug ? self::STATES[$stateSlug][0] : null;
        $stateHi = $stateSlug ? self::STATES[$stateSlug][1] : null;

        $title = $stateEn
            ? "Skill Development in {$stateEn} for Unemployed Youth | Jobsence – Best Job Portal | Free Registration"
            : 'Skill Development in India | Jobsence Skill Development Centre | Skills, Jobs & Internship | Free Registration';
        $where = $stateEn ? "in {$stateEn}" : 'across India';
        $description = "Skill development for unemployed youth {$where} – 10th pass, uneducated, drivers, homemakers, graduates, engineers. 2000+ skills, online video classes Pan-India or offline in Delhi-NCR, then internship and jobs. Free registration for learners. भारत को कुशल बनाने की Jobsence पहल – not a Government scheme.";

        $faqs = $this->faqs();
        $path = $stateSlug ? '/skill-development/' . $stateSlug : '/skill-development';
        SeoService::getInstance()->setMeta([
            'title' => $title,
            'h1' => $title,
            'description' => $description,
            'canonical' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/') . $path,
            'robots' => 'index, follow',
            'keywords' => self::KEYWORDS,
            'json_ld' => $this->jsonLd($faqs, $stateEn),
        ]);

        $response->view('front/skill-development/index', [
            'stateEn' => $stateEn,
            'stateHi' => $stateHi,
            'faqs' => $faqs,
            'fee' => \App\Services\Registration\FormRegistry::feeOf(\App\Services\Registration\FormRegistry::get('skill-development') ?? []),
            'courseFee' => PortalRegistration::COURSE_FEE,
            'states' => self::STATES,
            'popularSkills' => self::POPULAR_SKILLS,
            'whatsappNumber' => preg_replace('/\D/', '', (string)($_ENV['SKILL_WHATSAPP_NUMBER'] ?? $_ENV['WHATSAPP_NUMBER'] ?? '')),
        ], 200, 'layout');
    }

    private function faqs(): array
    {
        $courseGst = number_format(PortalRegistration::COURSE_FEE * PortalRegistration::GST_RATE, 0);

        return [
            [
                'क्या यह सरकारी योजना है?', 'Is this a Government scheme?',
                'नहीं। यह भारत को कुशल बनाने की Jobsence पहल है, भारत सरकार की कोई योजना नहीं है।',
                'No. This is Jobsence’s own initiative to make India skilled. It is NOT a Government of India scheme or programme.',
            ],
            [
                'क्या रजिस्ट्रेशन का कोई शुल्क है?', 'Is there a registration fee?',
                'नहीं। सीखने वालों और नौकरी ढूँढने वालों के लिए रजिस्ट्रेशन मुफ़्त है। केवल मेंटर / नियोक्ता अपने प्लान का शुल्क देते हैं।',
                'No. Registration is free for learners and job seekers. Only mentors / employers pay for their plans.',
            ],
            [
                'चयन के बाद कोर्स फीस कितनी है?', 'What is the course fee after selection?',
                "चयन होने पर कुल कोर्स फीस ₹12,000 + 18% GST (₹{$courseGst}) है, जो वापस नहीं होगी।",
                "After selection, the total course fee is ₹12,000 + 18% GST (₹{$courseGst}). It is non-refundable.",
            ],
            [
                'क्या नौकरी की गारंटी है?', 'Is a job guaranteed?',
                'नहीं। Jobsence नौकरी या प्लेसमेंट की कोई गारंटी नहीं देता। हम आपका रिज़्यूमे कंपनियों तक पहुँचाने, आपको जॉब-रेडी बनाने या स्व-रोज़गार में मार्गदर्शन की कोशिश करते हैं।',
                'No. Jobsence does not guarantee any job or placement. We try to push your resume to interested companies, make you job-ready, or guide you towards self-employment.',
            ],
            [
                'क्लास कब और कैसे होंगी?', 'When and how will classes happen?',
                'क्लास हफ्ते में 3 दिन, रोज़ 2 घंटे, प्रैक्टिकल ट्रेनिंग सहित होंगी। ऑनलाइन वीडियो क्लास पूरे भारत में, और ऑफलाइन क्लास केवल दिल्ली-NCR में।',
                'Classes are 2 hours per day, 3 days a week, including practical training. Online video classes are available Pan-India; offline classes only in Delhi-NCR.',
            ],
            [
                'फॉर्म भरने के बाद कितना समय लगेगा?', 'How long after submitting the form?',
                'फॉर्म और रिज़्यूमे की जाँच में कम से कम 3 से 6 महीने लगते हैं।',
                'Scrutiny of forms and resumes takes a minimum of 3 to 6 months.',
            ],
            [
                'कौन आवेदन कर सकता है?', 'Who can apply?',
                'कोई भी बेरोज़गार – अशिक्षित, 10वीं/12वीं पास, ITI, ग्रेजुएट, इंजीनियर, डॉक्टर, ड्राइवर, गृहिणी – गाँव से राजधानी तक, पूरे भारत से।',
                'Anyone unemployed – uneducated, 10th/12th pass, ITI, graduates, engineers, doctors, drivers, homemakers – from any village, district, city or capital in India.',
            ],
            [
                'ट्रेनिंग किस भाषा में होगी?', 'In which language is training given?',
                'मेंटर की उपलब्धता के अनुसार हिंदी, अंग्रेज़ी या स्थानीय/क्षेत्रीय भाषा में।',
                'In Hindi, English or a local/regional language, depending on mentor availability.',
            ],
            [
                'ट्रेनिंग कौन देगा?', 'Who conducts the training?',
                'ट्रेनिंग थर्ड-पार्टी स्किल डेवलपमेंट पार्टनर्स और रजिस्टर्ड मेंटर्स द्वारा दी जाती है।',
                'Training is conducted through third-party skill development partners and registered mentors.',
            ],
            [
                'क्या मैं मेंटर / ट्रेनर के रूप में जुड़ सकता हूँ?', 'Can I join as a mentor / trainer?',
                "हाँ। Jobsence अपनी मेंटर टीम बना रहा है। अपनी जानकारी और डेमो वीडियो के साथ मुफ़्त नामांकन करें। चयन होने पर प्रति प्रशिक्षार्थी तय मानदेय, ट्रेनिंग पूरी होने के बाद दिया जाएगा।",
                "Yes. Jobsence is building its own Mentor Team. Enrol free with your details and a demo video. If selected, you get a fixed emolument per candidate, paid after the training is completed.",
            ],
        ];
    }

    private function jsonLd(array $faqs, ?string $stateEn): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        $org = [
            '@type' => ['EducationalOrganization', 'LocalBusiness'],
            '@id' => $base . '/skill-development#org',
            'name' => 'Jobsence Skill Development Centre',
            'description' => 'भारत को कुशल बनाने की Jobsence पहल – Jobsence’s initiative to make India skilled. Not a Government of India scheme.',
            'url' => $base . '/skill-development',
            'areaServed' => $stateEn ? ['@type' => 'State', 'name' => $stateEn] : ['@type' => 'Country', 'name' => 'India'],
            'priceRange' => '₹0',
        ];
        $course = [
            '@type' => 'Course',
            'name' => 'Job-oriented Skill Development Training (2000+ skills)',
            'description' => 'Practical skill training: 2 hours/day, 3 days/week. Online video classes Pan-India or offline in Delhi-NCR, in Hindi, English or regional languages.',
            'provider' => ['@id' => $base . '/skill-development#org'],
            'inLanguage' => ['hi', 'en'],
            'offers' => [
                '@type' => 'Offer',
                'category' => 'Registration form fee',
                'price' => '0.00',
                'priceCurrency' => 'INR',
            ],
            'hasCourseInstance' => [
                ['@type' => 'CourseInstance', 'courseMode' => 'online', 'courseSchedule' => ['@type' => 'Schedule', 'repeatFrequency' => 'P1W', 'repeatCount' => 3, 'duration' => 'PT2H']],
                ['@type' => 'CourseInstance', 'courseMode' => 'onsite', 'location' => 'Delhi-NCR', 'courseSchedule' => ['@type' => 'Schedule', 'repeatFrequency' => 'P1W', 'repeatCount' => 3, 'duration' => 'PT2H']],
            ],
        ];
        $faqPage = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn($f) => [
                '@type' => 'Question',
                'name' => $f[1],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[3]],
            ], $faqs),
        ];

        return (string)json_encode(
            ['@context' => 'https://schema.org', '@graph' => [$org, $course, $faqPage]],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
        );
    }

}
