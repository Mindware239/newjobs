<?php
/**
 * The three separate logins – Job Seeker, Employer, Mentor – with what each one gets.
 * Shared by the login page (tabs) and the homepage (cards). Returns key => role info.
 */
return [
    'candidate' => [
        'icon' => '👤',
        'title' => ['जॉब सीकर लॉगिन', 'Job Seeker Login'],
        'tagline' => ['नौकरी, इंटर्नशिप और स्किल ढूँढने वालों के लिए', 'For people looking for jobs, internships and skills'],
        'points' => [
            ['नौकरियाँ खोजें और जल्दी आवेदन करें', 'Search jobs and apply quickly'],
            ['अपना रिज़्यूमे अपलोड करें – कंपनियाँ आपको खोजेंगी', 'Upload your resume – companies find you'],
            ['सरकारी, रेलवे, सेना, पुलिस, PSU नौकरियाँ – Jobs in India', 'Govt, Railway, Army, Police, PSU jobs – Jobs in India'],
            ['स्किल और इंटर्नशिप के लिए मेंटर से जुड़ें (Young India Mentoring)', 'Get a mentor for skills and internships (Young India Mentoring)'],
            ['आवेदन की स्थिति, इंटरव्यू और संदेश – सब एक डैशबोर्ड पर', 'Application status, interviews and messages – one dashboard'],
        ],
        'how' => ['मोबाइल नंबर या ईमेल → ईमेल OTP या आपका PIN', 'Mobile number or email → email OTP or your PIN'],
        'register' => [['/register-candidate', ['मुफ़्त जॉब सीकर खाता बनाएँ', 'Create a free job seeker account']]],
        'href' => '/login/job-seeker',
        'color' => '#f05537',
    ],
    'employer' => [
        'icon' => '🏢',
        'title' => ['एम्प्लॉयर लॉगिन', 'Employer Login'],
        'tagline' => ['कंपनियों, HR और भर्ती करने वालों के लिए', 'For companies, HR teams and recruiters'],
        'points' => [
            ['नौकरी पोस्ट करें और आवेदन एक जगह संभालें', 'Post jobs and manage applications in one place'],
            ['Talent Search – रिज़्यूमे खोजें और सीधे संपर्क करें', 'Talent Search – search resumes and contact candidates'],
            ['भर्ती प्लान – भारत (₹) और विदेश (USD) के लिए', 'Hiring plans for India (₹) and abroad (USD)'],
            ['इंटरव्यू, संदेश, कंपनी प्रोफ़ाइल और GST इनवॉइस', 'Interviews, messages, company profile and GST invoices'],
            ['एक कंपनी = एक रजिस्ट्रेशन (ईमेल, मोबाइल, GST)', 'One company = one registration (email, mobile, GST)'],
        ],
        'how' => ['कंपनी का मोबाइल नंबर या ईमेल → ईमेल OTP या PIN', 'Company mobile number or email → email OTP or PIN'],
        'register' => [['/register-employer', ['मुफ़्त एम्प्लॉयर खाता बनाएँ', 'Create a free employer account']]],
        'href' => '/login/employer',
        'color' => '#059669',
    ],
    'mentor' => [
        'icon' => '🧑‍🏫',
        'title' => ['मेंटर लॉगिन', 'Mentor Login'],
        'tagline' => ['मेंटर, ट्रेनिंग संस्थान, इंटर्नशिप प्रदाता और भर्ती करने वाली कंपनियाँ (Young India Mentoring)', 'Mentors, training institutes, internship providers and hiring companies (Young India Mentoring)'],
        'points' => [
            ['रजिस्ट्रेशन मुफ़्त – कौशल देना पुण्य है', 'Registration is free – giving skill is punya'],
            ['देखें कौन कौन सा स्किल / इंटर्नशिप / नौकरी चाहता है', 'See who wants which skill, internship or job'],
            ['₹155 प्लान: रोज़ 3 पूरी प्रोफ़ाइल, त्रिपक्षीय समझौता (भारत के बाहर USD 5)', '₹155 plan: 3 full profiles a day, tripartite agreement (USD 5 outside India)'],
            ['समझौते पर दोनों के हस्ताक्षर के बाद ही संपर्क नंबर', 'Contact details only after both sign the agreement'],
            ['किसी भी देश से मेंटर जुड़ सकते हैं', 'Mentors can join from any country'],
        ],
        'how' => ['रजिस्ट्रेशन वाला मोबाइल नंबर या ईमेल → ईमेल OTP', 'Mobile number or email used to register → email OTP'],
        'register' => [
            ['/apply/skill-provider', ['मेंटर / संस्थान – मुफ़्त रजिस्टर करें', 'Mentor / institute – register free']],
            ['/apply/internship-provider', ['इंटर्नशिप प्रदाता – मुफ़्त रजिस्टर करें', 'Internship provider – register free']],
            ['/apply/job-provider', ['भर्ती करने वाली कंपनी – मुफ़्त रजिस्टर करें', 'Hiring company – register free']],
        ],
        'href' => '/login/mentor',
        'color' => '#7c3aed',
    ],
];
