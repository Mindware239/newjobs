<?php
// State recruitment boards – South India & UTs (GovtListFetcher profiles; keys = official listing URL).
// Surveyed and tested 2026-10-06. Not added: APPSC (incomplete TLS chain), TNUSRB / MRB / TRB / KEA (no dated
// list), Telangana police (JavaScript only).
// South India + Delhi + southern/island UTs – profiles for App\Services\IndiaJobs\GovtListFetcher (surveyed 2026-10-06).
return [
    // Tamil Nadu – TNPSC home "What's New" (posting date in the event-date title attribute; keep only notification publications)
    'https://tnpsc.gov.in/home.aspx' => [
        'name' => 'Tamil Nadu Public Service Commission (TNPSC)', 'org_type' => 'state_govt', 'state' => 'Tamil Nadu', 'location' => 'Chennai', 'website' => 'https://www.tnpsc.gov.in',
        'row' => '#<div class="event-date" title="(?<date>[A-Z][a-z]{2} \d{1,2}, \d{4})">(?:(?!event-date).)*?<h4 class="event-title"><a href="[^"]*"[^>]*>(?<title>.*?)</a>#is',
        'date' => 'M j, Y',
        'keep' => '/publication of (the )?notification/i',
        'drop' => '/result|hall ticket|answer key|tentative key|marks|counselling|certificate verification|interview(?! posts)|schedule|postpone|departmental exam/i',
        'clean' => ['/^PRESS RELEASE REGARDING (THE )?/i', '/\s*\(Press Release\)\s*$/i'],
        'title_prefix' => 'TNPSC: ',
        'apply' => 'https://tnpsc.gov.in/web/examdashboard/index.aspx',
    ],
    // Kerala – Kerala PSC notifications (one row = one Extraordinary Gazette with a range of category numbers)
    'https://www.keralapsc.gov.in/notifications' => [
        'name' => 'Kerala Public Service Commission (Kerala PSC)', 'org_type' => 'state_govt', 'state' => 'Kerala', 'location' => 'Thiruvananthapuram', 'website' => 'https://www.keralapsc.gov.in',
        'row' => '#<td headers="view-title-table-column"[^>]*><a href="(?<link>[^"]+)"[^>]*>(?<title>[^<]*?(?<date>\d{2}/\d{2}/\d{4})</a>\s*</td>\s*<td headers="view-field-category-number-table-column"[^>]*><a [^>]*>[^<]*)</a>\s*</td>\s*<td headers="view-field-last-date-table-column"[^>]*><time[^>]*>(?<last>\d{2}-\d{2}-\d{4})</time>#is',
        'date' => 'd/m/Y',
        'title_prefix' => 'Kerala PSC recruitment notifications – ',
        'apply' => 'https://thulasi.psc.kerala.gov.in/thulasi/',
    ],
    // Karnataka – KPSC notification page (old FrontPage HTML; only notifications whose PDF file name carries the date are dated)
    'https://kpsc.kar.nic.in/notification.html' => [
        'name' => 'Karnataka Public Service Commission (KPSC)', 'org_type' => 'state_govt', 'state' => 'Karnataka', 'location' => 'Bengaluru', 'website' => 'https://kpsc.kar.nic.in',
        'row' => '#<a href="(?<pdf>[^"]*?(?:%20dt|%20dated|%20ON)%20(?<date>\d{2}-\d{2}-\d{4})\.pdf)"[^>]*>(?<title>.*?)</a>#is',
        'date' => 'd-m-Y',
        'keep' => '/notification/i',
        'drop' => '/withdraw|cancel|corrigendum|result|key answer|departmental/i',
        'title_prefix' => 'KPSC: ',
        'apply' => 'https://kpsconline.karnataka.gov.in',
    ],
    // Andhra Pradesh – State Level Police Recruitment Board
    'https://slprb.ap.gov.in/UI/recruitments.aspx' => [
        'name' => 'Andhra Pradesh State Level Police Recruitment Board (AP SLPRB)', 'org_type' => 'police', 'state' => 'Andhra Pradesh', 'location' => 'Mangalagiri', 'website' => 'https://slprb.ap.gov.in',
        'row' => '#<tr>\s*<td[^>]*>\s*[^<]*?SLPRB[^<]*?</td>\s*<td[^>]*>\s*(?<date>\d{2}\.\d{2}\.\d{4})\s*</td>\s*<td[^>]*>(?<title>.*?)</td>(?:(?!</tr>).)*?<a href="(?<pdf>[^"]+)"#is',
        'date' => 'd.m.Y',
        'clean' => ['/,\s*$/'],
        'title_prefix' => 'AP Police recruitment: ',
        'apply' => 'https://slprb.ap.gov.in',
    ],
    // Telangana – TGPSC direct recruitment (no posting date on the site: application start date is used as the date)
    'https://websitenew.tgpsc.gov.in/directRecruitment' => [
        'name' => 'Telangana Public Service Commission (TGPSC)', 'org_type' => 'state_govt', 'state' => 'Telangana', 'location' => 'Hyderabad', 'website' => 'https://www.tgpsc.gov.in',
        'row' => '#<a href="(?<pdf>/preview/[^"]+)"[^>]*>\s*<font[^>]*>(?:\s|<br>)*(?<title>[^<]+?)(?:\s|<br>)*</font></a>(?:(?!<a href="/preview/).)*?Start Date:</font><br>\s*<font[^>]*>(?<date>\d{2}/\d{2}/\d{4})</font>(?:(?!<a href="/preview/).)*?End Date</font><br>\s*<font[^>]*>\s*(?<last>\d{2}/\d{2}/\d{4})#is',
        'date' => 'd/m/Y',
        'title_prefix' => 'TGPSC Notification ',
        'apply' => 'https://otr.tgpsc.gov.in/login?type=new',
    ],
    // Delhi – DSSSB current vacancies
    'https://dsssb.delhi.gov.in/dsssb-vacancies' => [
        'name' => 'Delhi Subordinate Services Selection Board (DSSSB)', 'org_type' => 'state_govt', 'state' => 'Delhi', 'location' => 'Delhi', 'website' => 'https://dsssb.delhi.gov.in',
        'row' => '#<div class="tab-title">\s*(?<title>.*?)\s*</div>\s*<div class="tab-date">\s*Date:\s*(?<date>\d{2}-\d{2}-\d{4}).*?<a class="tab-view"[^>]*href="(?<pdf>[^"]+)"#is',
        'date' => 'd-m-Y',
        'keep' => '/vacancy|advertisement|recruit|engagement|applications? (are )?invited/i',
        'drop' => '/corrigendum|addendum|extension|result|answer key|admit|e-dossier|marks|speaking order|cancel/i',
        'title_prefix' => 'DSSSB: ',
        'apply' => 'https://dsssbonline.nic.in',
    ],
    // Puducherry – Government of Puducherry online recruitment portal
    'https://recruitment.py.gov.in/' => [
        'name' => 'Government of Puducherry – Recruitment Portal', 'org_type' => 'state_govt', 'state' => 'Puducherry', 'location' => 'Puducherry', 'website' => 'https://recruitment.py.gov.in',
        'row' => '#<tr class="dept-[^"]*">(?:(?!</tr>).)*?<a class="[^"]*" href="(?<link>[^"]+)">(?<title>[^<]+)</a>(?:(?!</tr>).)*?<td class="text-nowrap">\s*(?<date>\d{2}-\d{2}-\d{4}) \d{2}:\d{2}(?::\d{2})?\s*</td>\s*<td class="text-nowrap">\s*<span class="d-none">[^<]*</span>\s*(?<last>\d{2}-\d{2}-\d{4})#is',
        'date' => 'd-m-Y',
        'apply' => 'https://recruitment.py.gov.in',
    ],
    // Andaman and Nicobar Islands – A&N Administration vacancy notices (department + subject as the title)
    'https://andamannicobar.gov.in/vacancy_all' => [
        'name' => 'Andaman and Nicobar Administration', 'org_type' => 'state_govt', 'state' => 'Andaman and Nicobar Islands', 'location' => 'Sri Vijaya Puram', 'website' => 'https://andamannicobar.gov.in',
        'row' => '#<tr>\s*<td>\d+</td>\s*<td>(?<title>[^<]*</td>\s*<td>[^<]*)</td>\s*<td>(?:(?<last>\d{2}-\d{2}-\d{4})|[^<]*)</td>\s*<td>(?<date>\d{2}-\d{2}-\d{4})</td>\s*<td[^>]*>\s*<a href="(?<pdf>[^"]+)"#is',
        'date' => 'd-m-Y',
        'drop' => '/result|selection list|merit list|provisional list|shortlist|answer key|admit card|cancel/i',
        'title_prefix' => 'A&N Administration: ',
        'apply' => 'https://andamannicobar.gov.in/vacancy_all',
    ],
    // Lakshadweep – UT Administration (S3WaaS recruitment notices; start date = posting date)
    'https://lakshadweep.gov.in/notice_category/recruitment/' => [
        'name' => 'Union Territory of Lakshadweep Administration', 'org_type' => 'state_govt', 'state' => 'Lakshadweep', 'location' => 'Kavaratti', 'website' => 'https://lakshadweep.gov.in',
        'row' => '#<tr>\s*<td>(?<title>[^<]+)</td>\s*<td>(?:(?!</td>).)*</td>\s*<td>(?<date>\d{2}/\d{2}/\d{4})</td>\s*<td>(?<last>\d{2}/\d{2}/\d{4})</td>(?:(?!</tr>).)*?href="(?<pdf>[^"]+)"#is',
        'date' => 'd/m/Y',
        'drop' => '/selection list|result|merit list|rank list|provisional list|shortlist|answer key|admit card|cancel/i',
        'title_prefix' => 'Lakshadweep: ',
        'apply' => 'https://lakshadweep.gov.in/notice_category/recruitment/',
    ],
    // Dadra and Nagar Haveli and Daman and Diu – UT Administration (S3WaaS; office + description as the title)
    'https://ddd.gov.in/notice-category/recruitments/' => [
        'name' => 'UT Administration of Dadra and Nagar Haveli and Daman and Diu', 'org_type' => 'state_govt', 'state' => 'Dadra and Nagar Haveli and Daman and Diu', 'location' => 'Daman', 'website' => 'https://ddd.gov.in',
        'row' => '#<tr>\s*<td>\d+</td>\s*<td scope="row">(?<title>(?:(?!</td>).)*</td>\s*<td>(?:(?!</td>).)*)</td>\s*<td>(?<date>\d{2}/\d{2}/\d{4})</td>\s*<td>(?<last>\d{2}/\d{2}/\d{4})</td>(?:(?!</tr>).)*?href="(?<pdf>[^"]+)"#is',
        'date' => 'd/m/Y',
        'drop' => '/selection list|result|merit list|provisional list|shortlist|answer key|admit card|cancel/i',
        'title_prefix' => 'DNH & DD: ',
        'apply' => 'https://ddd.gov.in/notice-category/recruitments/',
    ],
];
