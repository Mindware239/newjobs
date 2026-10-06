<?php
// Country pages /jobsence-in/{slug}: one short page per neighbouring "open" country and per mentor country, in that
// country's language + English. 'open' = residents may also register as job seekers (FormRegistry::OPEN_COUNTRIES);
// mentor-only countries invite trainers to teach Indian youth online. Translations drafted by Claude 2026-10-06 –
// have a native speaker review them.
//
// Text keys: title, intro, mentor_h, mentor_p, mentor_btn, jobs_h, jobs_p, jobs_btn ({c} = the country's own name).
$en = [
    'title' => 'Jobsence in {c}',
    'intro' => 'Jobsence is India\'s complete job portal – jobs, internships, skill development and mentoring in one place.',
    'mentor_h' => 'Teach young Indians – become a Jobsence mentor',
    'mentor_p' => 'Experts, trainers and institutes from {c} can register free and mentor young Indians online. Payment is per candidate, after successful training.',
    'mentor_btn' => 'Register free as a mentor',
    'jobs_h' => 'Looking for a job in India?',
    'jobs_p' => 'Residents of {c} can register on Jobsence. Working in India needs a valid Indian employment visa – arranging it is the responsibility of the candidate and the employer.',
    'jobs_btn' => 'Register as a job seeker',
];

return [
    'nepal' => ['name' => 'Nepal', 'native' => 'नेपाल', 'lang' => 'ne', 'dir' => 'ltr', 'flag' => '🇳🇵', 'open' => true, 'en' => $en, 't' => [
        'title' => 'नेपालमा Jobsence',
        'intro' => 'Jobsence भारतको पूर्ण रोजगार पोर्टल हो – जागिर, इन्टर्नसिप, सीप विकास र मेन्टरिङ एकै ठाउँमा।',
        'mentor_h' => 'भारतीय युवालाई सिकाउनुहोस् – Jobsence मेन्टर बन्नुहोस्',
        'mentor_p' => 'नेपालका विशेषज्ञ, प्रशिक्षक र संस्थाहरूले निःशुल्क दर्ता गरी भारतीय युवालाई अनलाइन सिकाउन सक्छन्। भुक्तानी प्रत्येक उम्मेदवारको सफल तालिमपछि हुन्छ।',
        'mentor_btn' => 'मेन्टरको रूपमा निःशुल्क दर्ता गर्नुहोस्',
        'jobs_h' => 'भारतमा जागिर खोज्दै हुनुहुन्छ?',
        'jobs_p' => 'नेपालका बासिन्दाले Jobsence मा दर्ता गर्न सक्छन्। भारतमा काम गर्न मान्य भारतीय रोजगार भिसा चाहिन्छ – यसको व्यवस्था उम्मेदवार र रोजगारदाताको जिम्मेवारी हो।',
        'jobs_btn' => 'जागिर खोज्नेको रूपमा दर्ता गर्नुहोस्',
    ]],
    'sri-lanka' => ['name' => 'Sri Lanka', 'native' => 'ශ්‍රී ලංකාව', 'lang' => 'si', 'dir' => 'ltr', 'flag' => '🇱🇰', 'open' => true, 'en' => $en, 't' => [
        'title' => 'ශ්‍රී ලංකාවේ Jobsence',
        'intro' => 'Jobsence යනු ඉන්දියාවේ සම්පූර්ණ රැකියා ද්වාරයයි – රැකියා, පුහුණුවීම්, නිපුණතා සංවර්ධනය සහ උපදේශනය එකම තැනක.',
        'mentor_h' => 'ඉන්දීය තරුණයින්ට උගන්වන්න – Jobsence උපදේශකයෙකු වන්න',
        'mentor_p' => 'ශ්‍රී ලංකාවේ විශේෂඥයින්, පුහුණුකරුවන් සහ ආයතනවලට නොමිලේ ලියාපදිංචි වී ඉන්දීය තරුණයින්ට මාර්ගගතව උපදෙස් දිය හැක. ගෙවීම එක් එක් අපේක්ෂකයාගේ සාර්ථක පුහුණුවෙන් පසුව සිදු කෙරේ.',
        'mentor_btn' => 'උපදේශකයෙකු ලෙස නොමිලේ ලියාපදිංචි වන්න',
        'jobs_h' => 'ඉන්දියාවේ රැකියාවක් සොයනවාද?',
        'jobs_p' => 'ශ්‍රී ලංකාවේ පදිංචිකරුවන්ට Jobsence හි ලියාපදිංචි විය හැක. ඉන්දියාවේ වැඩ කිරීමට වලංගු ඉන්දීය රැකියා වීසා බලපත්‍රයක් අවශ්‍යය – එය සකස් කිරීම අපේක්ෂකයාගේ සහ සේවා යෝජකයාගේ වගකීමයි.',
        'jobs_btn' => 'රැකියා අපේක්ෂකයෙකු ලෙස ලියාපදිංචි වන්න',
    ]],
    'afghanistan' => ['name' => 'Afghanistan', 'native' => 'افغانستان', 'lang' => 'fa', 'dir' => 'rtl', 'flag' => '🇦🇫', 'open' => true, 'en' => $en, 't' => [
        'title' => 'Jobsence در افغانستان',
        'intro' => 'Jobsence پورتال کامل کاریابی هند است – وظایف، کارآموزی، انکشاف مهارت و رهنمایی در یک جا.',
        'mentor_h' => 'به جوانان هندی آموزش دهید – رهنمای Jobsence شوید',
        'mentor_p' => 'متخصصان، مربیان و مؤسسات افغانستان می‌توانند رایگان ثبت‌نام کنند و جوانان هندی را به صورت آنلاین رهنمایی کنند. پرداخت برای هر متقاضی پس از آموزش موفق انجام می‌شود.',
        'mentor_btn' => 'رایگان به عنوان رهنما ثبت‌نام کنید',
        'jobs_h' => 'در جستجوی وظیفه در هند هستید؟',
        'jobs_p' => 'باشندگان افغانستان می‌توانند در Jobsence ثبت‌نام کنند. کار در هند به ویزای معتبر کاری هند نیاز دارد – تهیه آن مسئولیت متقاضی و کارفرما است.',
        'jobs_btn' => 'به عنوان کارجو ثبت‌نام کنید',
    ]],
    'bangladesh' => ['name' => 'Bangladesh', 'native' => 'বাংলাদেশ', 'lang' => 'bn', 'dir' => 'ltr', 'flag' => '🇧🇩', 'open' => true, 'en' => $en, 't' => [
        'title' => 'বাংলাদেশে Jobsence',
        'intro' => 'Jobsence ভারতের সম্পূর্ণ চাকরির পোর্টাল – চাকরি, ইন্টার্নশিপ, দক্ষতা উন্নয়ন ও মেন্টরিং এক জায়গায়।',
        'mentor_h' => 'ভারতীয় তরুণদের শেখান – Jobsence মেন্টর হন',
        'mentor_p' => 'বাংলাদেশের বিশেষজ্ঞ, প্রশিক্ষক ও প্রতিষ্ঠান বিনামূল্যে নিবন্ধন করে অনলাইনে ভারতীয় তরুণদের মেন্টর করতে পারেন। প্রতিটি প্রার্থীর সফল প্রশিক্ষণের পরে অর্থ প্রদান করা হয়।',
        'mentor_btn' => 'বিনামূল্যে মেন্টর হিসেবে নিবন্ধন করুন',
        'jobs_h' => 'ভারতে চাকরি খুঁজছেন?',
        'jobs_p' => 'বাংলাদেশের বাসিন্দারা Jobsence-এ নিবন্ধন করতে পারেন। ভারতে কাজ করতে বৈধ ভারতীয় কর্মসংস্থান ভিসা লাগে – তার ব্যবস্থা করা প্রার্থী ও নিয়োগকর্তার দায়িত্ব।',
        'jobs_btn' => 'চাকরিপ্রার্থী হিসেবে নিবন্ধন করুন',
    ]],
    'china' => ['name' => 'China', 'native' => '中国', 'lang' => 'zh-Hans', 'dir' => 'ltr', 'flag' => '🇨🇳', 'open' => true, 'en' => $en, 't' => [
        'title' => 'Jobsence 在中国',
        'intro' => 'Jobsence 是印度的综合求职门户——工作、实习、技能培训和导师指导，一站式服务。',
        'mentor_h' => '指导印度青年——成为 Jobsence 导师',
        'mentor_p' => '来自中国的专家、培训师和机构可以免费注册，在线指导印度青年。每位学员培训成功后按人支付报酬。',
        'mentor_btn' => '免费注册成为导师',
        'jobs_h' => '想在印度找工作？',
        'jobs_p' => '中国居民可以在 Jobsence 注册。在印度工作需要有效的印度就业签证——办理签证由求职者和雇主负责。',
        'jobs_btn' => '注册为求职者',
    ]],
    'thailand' => ['name' => 'Thailand', 'native' => 'ประเทศไทย', 'lang' => 'th', 'dir' => 'ltr', 'flag' => '🇹🇭', 'open' => true, 'en' => $en, 't' => [
        'title' => 'Jobsence ในประเทศไทย',
        'intro' => 'Jobsence คือพอร์ทัลหางานที่ครบวงจรของอินเดีย – งาน ฝึกงาน พัฒนาทักษะ และการเป็นพี่เลี้ยง ในที่เดียว',
        'mentor_h' => 'สอนเยาวชนอินเดีย – มาเป็นพี่เลี้ยงของ Jobsence',
        'mentor_p' => 'ผู้เชี่ยวชาญ ผู้ฝึกสอน และสถาบันจากประเทศไทยสามารถลงทะเบียนฟรีและเป็นพี่เลี้ยงให้เยาวชนอินเดียทางออนไลน์ ค่าตอบแทนจ่ายต่อผู้เรียนหนึ่งคนหลังการฝึกอบรมสำเร็จ',
        'mentor_btn' => 'ลงทะเบียนเป็นพี่เลี้ยงฟรี',
        'jobs_h' => 'กำลังหางานในอินเดียอยู่ใช่ไหม?',
        'jobs_p' => 'ผู้พำนักในประเทศไทยสามารถลงทะเบียนกับ Jobsence ได้ การทำงานในอินเดียต้องมีวีซ่าทำงานของอินเดียที่ถูกต้อง – การจัดหาวีซ่าเป็นความรับผิดชอบของผู้สมัครและนายจ้าง',
        'jobs_btn' => 'ลงทะเบียนเป็นผู้หางาน',
    ]],
    'russia' => ['name' => 'Russia', 'native' => 'Россия', 'lang' => 'ru', 'dir' => 'ltr', 'flag' => '🇷🇺', 'open' => false, 'en' => $en, 't' => [
        'title' => 'Jobsence в России',
        'intro' => 'Jobsence – комплексный индийский портал вакансий: работа, стажировки, развитие навыков и наставничество в одном месте.',
        'mentor_h' => 'Обучайте молодёжь Индии – станьте наставником Jobsence',
        'mentor_p' => 'Эксперты, преподаватели и учебные организации из России могут бесплатно зарегистрироваться и обучать молодых индийцев онлайн. Оплата производится за каждого ученика после успешного обучения.',
        'mentor_btn' => 'Бесплатно зарегистрироваться наставником',
    ]],
    'israel' => ['name' => 'Israel', 'native' => 'ישראל', 'lang' => 'he', 'dir' => 'rtl', 'flag' => '🇮🇱', 'open' => false, 'en' => $en, 't' => [
        'title' => 'Jobsence בישראל',
        'intro' => 'Jobsence הוא פורטל התעסוקה המקיף של הודו – משרות, התמחויות, פיתוח מיומנויות וחונכות במקום אחד.',
        'mentor_h' => 'למדו צעירים הודים – הפכו לחונכים ב-Jobsence',
        'mentor_p' => 'מומחים, מדריכים ומוסדות מישראל יכולים להירשם בחינם ולחנוך צעירים הודים באופן מקוון. התשלום הוא לכל מועמד, לאחר הכשרה מוצלחת.',
        'mentor_btn' => 'הירשמו בחינם כחונכים',
    ]],
    'usa' => ['name' => 'United States', 'native' => 'United States', 'lang' => 'en', 'dir' => 'ltr', 'flag' => '🇺🇸', 'open' => false, 'en' => $en, 't' => [
        'title' => 'Jobsence in the United States',
        'intro' => $en['intro'],
        'mentor_h' => $en['mentor_h'],
        'mentor_p' => 'Experts, trainers and institutes from the United States can register free and mentor young Indians online. Payment is per candidate, after successful training.',
        'mentor_btn' => $en['mentor_btn'],
    ]],
];
