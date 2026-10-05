-- Jobsence: 3 bilingual (Hindi + English) SEO blog posts for the Skill Development / Apply pages.
-- Safe to run more than once: each post is inserted only if its slug does not exist yet.
-- Category: "Career Advice" (blog_categories.slug = 'career-advice'); author_id 1.

INSERT INTO blogs (author_id, title, slug, excerpt, content, featured_image, status_id, published_at, meta_title, meta_description, meta_keywords, canonical_url, view_count, is_featured, sort_order, created_at, updated_at)
SELECT 1,
 'Skill Courses for Village Youth in India – गाँव के युवाओं के लिए स्किल कोर्स',
 'skill-courses-for-village-youth-india',
 'गाँव में रहकर भी सीखें रोज़गार देने वाले स्किल – ड्राइविंग, इलेक्ट्रीशियन, सिलाई, मोबाइल रिपेयर, कंप्यूटर और खेती से जुड़े कोर्स। Practical skill courses village youth can start today.',
 '<p><strong>गाँव में रहने का मतलब यह नहीं कि अच्छे काम के मौके कम हैं।</strong> सही स्किल सीखकर गाँव का युवा भी अपने ज़िले, शहर या घर से ही अच्छी कमाई कर सकता है। <em>Living in a village does not have to limit your income – the right skill opens work locally, in nearby towns, or from home.</em></p>

<h2>कौन से स्किल गाँव में सबसे ज़्यादा काम आते हैं? / Skills with the most local demand</h2>
<ul>
<li><strong>ड्राइविंग (LMV / HMV) / Driving</strong> – ट्रैक्टर, टैक्सी, ट्रक और स्कूल वैन ड्राइवर की हर जगह ज़रूरत है। Drivers are needed in every district.</li>
<li><strong>इलेक्ट्रीशियन व सोलर इंस्टॉलेशन / Electrician &amp; Solar</strong> – घरों की वायरिंग, पंप और सोलर पैनल का काम तेज़ी से बढ़ रहा है। Rooftop solar and pump work is growing fast.</li>
<li><strong>प्लंबिंग / Plumbing</strong> – नल-जल योजनाओं और नए मकानों में लगातार काम। Steady work in new houses and water connections.</li>
<li><strong>मोबाइल व इलेक्ट्रॉनिक्स रिपेयर / Mobile &amp; electronics repair</strong> – कम पूँजी में अपनी दुकान शुरू करें। Start a shop with small investment.</li>
<li><strong>सिलाई व ब्यूटी / Tailoring &amp; Beauty</strong> – घर से काम करने के लिए अच्छे विकल्प, ख़ासकर महिलाओं के लिए। Good home-based options, especially for women.</li>
<li><strong>कंप्यूटर बेसिक्स व डेटा एंट्री / Computer basics &amp; data entry</strong> – CSC केंद्र, दुकानों और ऑनलाइन वर्क फ्रॉम होम के लिए। Useful for CSC centres, shops and work-from-home.</li>
<li><strong>आधुनिक खेती / Modern agriculture</strong> – ड्रिप सिंचाई, मशरूम, मधुमक्खी पालन और डेयरी। Drip irrigation, mushroom farming, beekeeping, dairy.</li>
</ul>

<h2>बिना शहर जाए कैसे सीखें? / How to learn without moving to a city</h2>
<p>Jobsence Skill Development Centre पर आप <strong>ऑनलाइन वीडियो क्लास</strong> से पूरे भारत में कहीं से भी सीख सकते हैं; ऑफलाइन क्लास दिल्ली-NCR में होती हैं। क्लास रोज़ 2 घंटे, हफ्ते में 3 दिन, प्रैक्टिकल ट्रेनिंग के साथ होती हैं, और भाषा हिंदी, अंग्रेज़ी या स्थानीय भाषा हो सकती है। <em>Learn through online video classes from anywhere in India, or offline in Delhi-NCR – 2 hours a day, 3 days a week, in Hindi, English or a regional language depending on mentor availability.</em></p>

<h2>शुरुआत कैसे करें / How to start</h2>
<ol>
<li>अपनी रुचि और आसपास की माँग देखकर एक-दो स्किल चुनें। Pick one or two skills that match your interest and local demand.</li>
<li><a href="/apply/skill-development">Skill Development फॉर्म</a> भरें – एकमुश्त शुल्क ₹155 (GST सहित, वापसी योग्य नहीं)। Fill the form – one-time fee ₹155 incl. GST, non-refundable.</li>
<li>जाँच (3–6 महीने) के बाद क्लास जॉइन करें, फिर <a href="/apply/internship">इंटर्नशिप</a> और <a href="/apply/full-time-job">नौकरी</a> के लिए आवेदन करें। After scrutiny (3–6 months), join classes, then apply for internships and jobs.</li>
</ol>

<p><strong>ज़रूरी / Important:</strong> यह भारत को कुशल बनाने की Jobsence पहल है – भारत सरकार की योजना नहीं। Jobsence नौकरी की गारंटी नहीं देता; हम रिज़्यूमे कंपनियों तक पहुँचाने या स्व-रोज़गार में मार्गदर्शन की कोशिश करते हैं। <em>This is Jobsence’s initiative, not a Government scheme. No job is guaranteed – we try to push your resume to companies or guide you towards self-employment.</em></p>',
 NULL, 0, NOW(),
 'Skill Courses for Village Youth in India | Jobsence Skill Development',
 'Best skill courses for village youth in India – driving, electrician, solar, tailoring, mobile repair, computer & modern farming. Learn online in Hindi. Fee ₹155.',
 'skill development in india, skill courses for village youth, gaon ke yuvaon ke liye course, online skill training hindi, jobs in india, skills',
 NULL, 0, 1, 0, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE slug = 'skill-courses-for-village-youth-india');

INSERT INTO blogs (author_id, title, slug, excerpt, content, featured_image, status_id, published_at, meta_title, meta_description, meta_keywords, canonical_url, view_count, is_featured, sort_order, created_at, updated_at)
SELECT 1,
 'How to Become Job-Ready After 10th – 10वीं के बाद जॉब-रेडी कैसे बनें',
 'how-to-become-job-ready-after-10th',
 '10वीं पास हैं और जल्दी कमाना चाहते हैं? जानिए कौन से स्किल, सर्टिफिकेट और कदम आपको नौकरी के लिए तैयार करते हैं। A step-by-step guide for 10th pass youth.',
 '<p><strong>10वीं के बाद पढ़ाई जारी रखें या काम शुरू करें – दोनों रास्तों में स्किल सबसे ज़रूरी है।</strong> कंपनियाँ आज डिग्री से ज़्यादा यह देखती हैं कि आप काम कर सकते हैं या नहीं। <em>After 10th, whether you continue studies or start working, employers care most about what you can actually do.</em></p>

<h2>1. एक काम चुनें / Choose a direction</h2>
<p>10वीं पास युवाओं के लिए अच्छे विकल्प: इलेक्ट्रीशियन, फिटर, वेल्डर, ड्राइवर, डिलीवरी एक्ज़ीक्यूटिव, रिटेल सेल्स, सिक्योरिटी गार्ड, टेलीकॉलर, डेटा एंट्री, ब्यूटीशियन, कुक। <em>Good options for 10th pass: electrician, fitter, welder, driver, delivery executive, retail sales, security guard, telecaller, data entry, beautician, cook.</em></p>

<h2>2. प्रैक्टिकल स्किल सीखें / Learn a practical skill</h2>
<p>सिर्फ़ किताब नहीं – हाथ से काम करके सीखें। 2–6 महीने का स्किल कोर्स और कुछ हफ्तों की इंटर्नशिप आपके रिज़्यूमे को मज़बूत बनाती है। <em>A 2–6 month practical course plus a short internship makes a strong first resume.</em></p>

<h2>3. ये बेसिक स्किल ज़रूर सीखें / Basics every employer expects</h2>
<ul>
<li>मोबाइल और कंप्यूटर की बुनियादी जानकारी, WhatsApp व ईमेल का सही उपयोग। Basic phone &amp; computer use, WhatsApp and email.</li>
<li>थोड़ी स्पोकन इंग्लिश और ग्राहक से बात करने का तरीका। Some spoken English and customer communication.</li>
<li>समय पर पहुँचना, साफ़-सुथरा रहना और ज़िम्मेदारी लेना। Punctuality, neatness and responsibility.</li>
</ul>

<h2>4. एक आसान रिज़्यूमे बनाएँ / Make a simple resume</h2>
<p>नाम, मोबाइल, पता, पढ़ाई, सीखे हुए स्किल, कोई भी काम का अनुभव (दुकान, खेत, घर का बिज़नेस भी) और भाषाएँ लिखें। एक पेज काफ़ी है। <em>One page with contact details, education, skills, any work experience (even family shop or farm work) and languages is enough.</em></p>

<h2>5. सही जगह आवेदन करें / Apply in the right place</h2>
<p>Jobsence पर एक ही जगह <a href="/apply/skill-development">स्किल डेवलपमेंट</a>, <a href="/apply/internship">इंटर्नशिप</a>, <a href="/apply/full-time-job">फुल-टाइम</a>, <a href="/apply/part-time-job">पार्ट-टाइम</a> और <a href="/apply/work-from-home">वर्क फ्रॉम होम</a> के फॉर्म उपलब्ध हैं – हर फॉर्म का एकमुश्त शुल्क ₹155 (GST सहित)। <em>Apply for skill development, internship, full-time, part-time and work-from-home on Jobsence – one-time fee ₹155 incl. GST per form.</em></p>

<p><strong>ज़रूरी / Important:</strong> Jobsence किसी नौकरी या प्लेसमेंट की गारंटी नहीं देता। यह भारत को कुशल बनाने की Jobsence पहल है – सरकारी योजना नहीं। <em>No job or placement is guaranteed. This is Jobsence’s initiative, not a Government scheme.</em></p>',
 NULL, 0, NOW(),
 'How to Become Job-Ready After 10th | Jobs for 10th Pass in India | Jobsence',
 'A simple guide for 10th pass youth: choose a trade, learn practical skills, build a one-page resume and apply for internships and jobs in India. Hindi + English.',
 '10th pass jobs, job ready after 10th, 10वीं के बाद नौकरी, skill development in india, jobs in india, internship, best job portal',
 NULL, 0, 0, 0, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE slug = 'how-to-become-job-ready-after-10th');

INSERT INTO blogs (author_id, title, slug, excerpt, content, featured_image, status_id, published_at, meta_title, meta_description, meta_keywords, canonical_url, view_count, is_featured, sort_order, created_at, updated_at)
SELECT 1,
 'Self-Employment Ideas After Skill Training – स्किल ट्रेनिंग के बाद स्व-रोज़गार के आइडिया',
 'self-employment-ideas-after-skill-training',
 'नौकरी के इंतज़ार की जगह अपना काम शुरू करें – स्किल ट्रेनिंग के बाद कम पूँजी में शुरू होने वाले स्व-रोज़गार के 12 आइडिया। Low-investment business ideas for trained youth.',
 '<p><strong>स्किल सीखने के बाद हर किसी को नौकरी का इंतज़ार नहीं करना पड़ता।</strong> थोड़ी पूँजी और सही योजना से आप अपना काम शुरू कर सकते हैं और दूसरों को भी रोज़गार दे सकते हैं। <em>After skill training you do not have to wait for a job – with a small investment and a plan you can start your own work.</em></p>

<h2>कम पूँजी वाले 12 आइडिया / 12 low-investment ideas</h2>
<ol>
<li><strong>मोबाइल रिपेयर व एक्सेसरी शॉप</strong> / Mobile repair &amp; accessories shop</li>
<li><strong>इलेक्ट्रिकल व सोलर सर्विस</strong> – वायरिंग, इन्वर्टर, सोलर मेंटेनेंस / Electrical &amp; solar servicing</li>
<li><strong>AC, फ्रिज, वॉशिंग मशीन रिपेयर</strong> / Appliance repair</li>
<li><strong>प्लंबिंग व सैनिटरी कॉन्ट्रैक्ट</strong> / Plumbing contracts</li>
<li><strong>सिलाई व बुटीक</strong> – स्कूल यूनिफ़ॉर्म, ब्लाउज़, अल्टरेशन / Tailoring &amp; boutique</li>
<li><strong>ब्यूटी पार्लर / होम सर्विस</strong> – मेकअप, मेहंदी, हेयर / Beauty parlour or home service</li>
<li><strong>टिफ़िन सर्विस व बेकरी</strong> / Tiffin service &amp; home bakery</li>
<li><strong>अचार, पापड़, मसाला यूनिट</strong> / Pickle, papad &amp; spice unit</li>
<li><strong>कंप्यूटर सेंटर / CSC / फ़ोटोकॉपी</strong> / Computer &amp; online services centre</li>
<li><strong>डेयरी, पोल्ट्री, मशरूम या मधुमक्खी पालन</strong> / Dairy, poultry, mushroom or beekeeping</li>
<li><strong>ड्राइविंग – अपनी टैक्सी / ई-रिक्शा</strong> / Own taxi or e-rickshaw</li>
<li><strong>डिजिटल मार्केटिंग फ्रीलांसिंग</strong> – लोकल दुकानों के लिए सोशल मीडिया / Social media for local shops</li>
</ol>

<h2>शुरू करने से पहले / Before you start</h2>
<ul>
<li><strong>माँग देखें / Check demand:</strong> आपके इलाके में किस सेवा की कमी है? What is missing in your area?</li>
<li><strong>छोटा शुरू करें / Start small:</strong> पहले घर से या ऑर्डर पर काम करें, फिर दुकान लें। Work from home or on order first.</li>
<li><strong>हिसाब रखें / Keep accounts:</strong> हर दिन की कमाई और खर्च लिखें; UPI से भुगतान लें। Record income and expenses; accept UPI.</li>
<li><strong>ग्राहक बनाएँ / Build customers:</strong> WhatsApp ग्रुप, Google बिज़नेस प्रोफ़ाइल और अच्छी सर्विस सबसे बड़ा प्रचार है। WhatsApp, a Google Business profile and good service are the best marketing.</li>
</ul>

<h2>Jobsence कैसे मदद करता है / How Jobsence helps</h2>
<p><a href="/apply/skill-development">Skill Development फॉर्म</a> भरकर ऑनलाइन या दिल्ली-NCR में ऑफलाइन प्रैक्टिकल ट्रेनिंग लें। ट्रेनिंग के बाद हम स्व-रोज़गार में मार्गदर्शन या रिज़्यूमे कंपनियों तक पहुँचाने की कोशिश करते हैं। अगर आप पहले से किसी स्किल में माहिर हैं, तो <a href="/apply/skill-provider">Jobsence मेंटर टीम</a> से जुड़कर दूसरों को सिखाएँ। <em>Fill the Skill Development form for practical training; after training we try to guide you towards self-employment or push your resume. Already an expert? Join the Jobsence Mentor Team.</em></p>

<p><strong>ज़रूरी / Important:</strong> एकमुश्त शुल्क ₹155 (GST सहित) वापसी योग्य नहीं है। कोई कमाई या नौकरी की गारंटी नहीं है। यह भारत को कुशल बनाने की Jobsence पहल है – सरकारी योजना नहीं। <em>One-time fee ₹155 incl. GST, non-refundable. No income or job is guaranteed. This is Jobsence’s initiative, not a Government scheme.</em></p>',
 NULL, 0, NOW(),
 'Self-Employment Ideas After Skill Training in India | Jobsence',
 '12 low-investment self-employment ideas after skill training – repair shops, solar, tailoring, beauty, tiffin, dairy, digital marketing. Hindi + English guide.',
 'self employment ideas india, swarozgar ideas, small business after skill training, skill development in india, skills, jobs in india',
 NULL, 0, 0, 0, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE slug = 'self-employment-ideas-after-skill-training');

-- Link all three to the "Career Advice" category (if it exists).
INSERT INTO blog_category_map (blog_id, category_id)
SELECT b.id, c.id
FROM blogs b
JOIN blog_categories c ON c.slug = 'career-advice'
WHERE b.slug IN ('skill-courses-for-village-youth-india', 'how-to-become-job-ready-after-10th', 'self-employment-ideas-after-skill-training')
  AND NOT EXISTS (SELECT 1 FROM blog_category_map m WHERE m.blog_id = b.id AND m.category_id = c.id);

-- Categories linked from the blog menu and footer (Technology, Interview Questions); safe to re-run.
INSERT INTO blog_categories (name, slug, description, is_active, created_at, updated_at)
SELECT 'Technology', 'technology', 'Technology careers, skills and trends in India.', 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM blog_categories WHERE slug = 'technology');
INSERT INTO blog_categories (name, slug, description, is_active, created_at, updated_at)
SELECT 'Interview Questions', 'interview-questions', 'Common interview questions and how to answer them.', 1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM blog_categories WHERE slug = 'interview-questions');
