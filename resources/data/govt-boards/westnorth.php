<?php
// State recruitment boards – West & North India (GovtListFetcher profiles; keys = official listing URL).
// Surveyed and tested 2026-10-06. MPESB over plain http (incomplete https chain). Not added: RSSB (JSON list), GSSSB / OJAS (JS / postback),
// MPSC (React; robots), Maharashtra police (Marathi-numeral dates), MPPSC (robots.txt Disallow: /), CG Vyapam (timeouts), JKPSC / JKSSB / HPPSC /
// Chandigarh (TLS chain problems), HPRCA (Angular shell).
return array (
  'https://rpsc.rajasthan.gov.in/advertisements' => 
  array (
    'name' => 'Rajasthan Public Service Commission (RPSC)',
    'org_type' => 'state_govt',
    'state' => 'Rajasthan',
    'location' => 'Ajmer',
    'website' => 'https://rpsc.rajasthan.gov.in',
    'row' => '#<tr class=\'align-middle\'><td[^>]*>\\d+</td><td>(?<date>\\d{2}/\\d{2}/\\d{4})</td><td[^>]*>[^<]*</td><td align=\'left\'>(?<title>.*?)</td><td><a href=\'(?<pdf>[^\']+)\'#is',
    'date' => 'd/m/Y',
    'keep' => '/\\badvt\\b|advertisement/i',
    'drop' => '/corrigendum|amendment|result|answer key|admit/i',
    'apply' => 'https://sso.rajasthan.gov.in',
  ),
  'https://gpsc.gujarat.gov.in/dashboard?stage=Advertisement' => 
  array (
    'name' => 'Gujarat Public Service Commission (GPSC)',
    'org_type' => 'state_govt',
    'state' => 'Gujarat',
    'location' => 'Gandhinagar',
    'website' => 'https://gpsc.gujarat.gov.in',
    'row' => '#<tr>\\s*<td>(?<title>(?:(?!</td>).)+)</td>\\s*<td class="dt_center adt_width">\\s*<a href=\'(?<link>AdvertisementDetail\\?no=\\d+)&tab=\'[^>]*>[^<]*</a>(?:(?!</tr>).)*?_sDate_\\d+"[^>]*>\\s*(?<date>\\d{2}-\\d{2}-\\d{4})(?:(?!</tr>).)*?<br/>\\s*(?<last>\\d{2}-\\d{2}-\\d{4})#is',
    'date' => 'd-m-Y',
    'clean' => 
    array (
      0 => '/\\|\\s*Class-\\d+\\s*(?=\\|)|\\s*\\|\\s*Class-\\d+\\s*$/i',
      1 => '/\\s*\\|\\s*Other\\s*$/i',
    ),
    'title_prefix' => 'GPSC ',
    'apply' => 'https://gpsc-ojas.gujarat.gov.in',
  ),
  'https://gprb.gujarat.gov.in/advertisements.htm' => 
  array (
    'name' => 'Gujarat Police Recruitment Board (GPRB) - Lokrakshak / PSI',
    'org_type' => 'police',
    'state' => 'Gujarat',
    'location' => 'Gandhinagar',
    'website' => 'https://gprb.gujarat.gov.in',
    'row' => '#<h3>\\s*<a id="[^"]*hlTitle_\\d+" href="(?<link>[^"]+)">[^<]*</a>\\s*</h3>\\s*<p class="examTypeTitle">\\s*<span[^>]*></span>(?<title>[^<]+)(?:(?!examTypeTitle).)*?<ul class="examDocumentBulletText">\\s*<li>\\s*<a id="[^"]*lnkPdfFile_\\d+" class="pdfIcon" href="(?<pdf>ViewDocument\\.aspx\\?CDID=[0-9a-f]+)"(?:(?!</a>).)*?lblDate_\\d+">\\s*(?<date>\\d{2}/\\d{2}/\\d{4})</span>#is',
    'date' => 'd/m/Y',
    'apply' => 'https://ojas.gujarat.gov.in',
  ),
  'https://www.mahadiscom.in/en/recruitment-career-options/' => 
  array (
    'name' => 'Maharashtra State Electricity Distribution Co. Ltd. (MSEDCL / Mahavitaran)',
    'org_type' => 'psu',
    'state' => 'Maharashtra',
    'location' => 'Mumbai',
    'website' => 'https://www.mahadiscom.in',
    'row' => '#<tr>\\s*<td><a href="(?<link>https://www\\.mahadiscom\\.in/[^"]+)">(?<title>[^<]+)</a></td>\\s*<td>(?<date>[A-Z][a-z]+ \\d{1,2}, \\d{4})</td>#s',
    'date' => 'F j, Y',
    'keep' => '/advt|advertisement|recruitment|vacanc|apprentice|engagement|post of|posts of/i',
    'drop' => '/result|roll no|answer key|admit|hall ticket|proficiency|lottery|merit|shortlist|selection list|provisional|document verification|corrigendum|extension/i',
    'apply' => 'https://www.mahadiscom.in/en/recruitment-career-options/',
  ),
  'https://www.goa.gov.in/citizen/recruitment/' => 
  array (
    'name' => 'Government of Goa - Recruitment (all departments, GPSC and GSSC)',
    'org_type' => 'state_govt',
    'state' => 'Goa',
    'website' => 'https://www.goa.gov.in',
    'row' => '#<tr>\\s*<td>(?<title>(?:(?!</td>).)+</td>\\s*<td>(?:(?!</td>).)*)</td>\\s*<td>\\s*(?<date>\\d{2}/\\d{2}/\\d{4})\\s*</td>\\s*<td[^>]*>\\s*(?<last>\\d{2}/\\d{2}/\\d{4})?(?:(?!</tr>).)*?href="(?<pdf>[^"]+)"#is',
    'date' => 'd/m/Y',
    'drop' => '/result|selected candidates|select list|merit list|answer key|admit card|hall ticket|shortlist|provisional list|final list|interview schedule|corrigendum/i',
    'apply' => NULL,
  ),
  'http://esb.mp.gov.in/home_n.html' => 
  array (
    'name' => 'Madhya Pradesh Employees Selection Board (MPESB)',
    'org_type' => 'state_govt',
    'state' => 'Madhya Pradesh',
    'location' => 'Bhopal',
    'website' => 'https://esb.mp.gov.in',
    'row' => '#<li>\\s*&nbsp;<a target="_blank" href="(?<link>https://esb\\.mponline\\.gov\\.in/[^"]+)">\\s*Online Form\\s*-\\s*(?<title>[^<]*?)\\s*Start (?:Date\\s*:|From\\s*-)\\s*(?<date>\\d{2}/\\d{2}/\\d{4})\\s*</a>(?:\\s|&nbsp;)*<a target="_blank" href="(?<pdf>[^"]+\\.pdf)"#is',
    'date' => 'd/m/Y',
    'drop' => '/departmental|विभागीय/iu',
    'title_prefix' => 'MPESB ',
    'apply' => 'https://esb.mponline.gov.in/Portal/Examinations/Vyapam/examsList.aspx',
  ),
  'https://psc.cg.gov.in/Advertisement.php' => 
  array (
    'name' => 'Chhattisgarh Public Service Commission (CGPSC)',
    'org_type' => 'state_govt',
    'state' => 'Chhattisgarh',
    'location' => 'Raipur',
    'website' => 'https://psc.cg.gov.in',
    'row' => '#<li><a href=\'(?<pdf>PDFs/advertisement/[^\']+\\.pdf)\'[^>]*>(?<title>[^<]*?)[\\s_]*\\(\\s*(?<date>\\d{2}-\\d{2}-\\d{4})\\s*\\)\\s*</a>#i',
    'date' => 'd-m-Y',
    'keep' => '/advertisement|advt/i',
    'drop' => '/corrigendum|result|answer/i',
    'title_prefix' => 'CGPSC ',
    'apply' => 'https://psc.cg.gov.in',
  ),
  'https://ladakh.gov.in/' => 
  array (
    'name' => 'Administration of Union Territory of Ladakh',
    'org_type' => 'state_govt',
    'state' => 'Ladakh',
    'location' => 'Leh',
    'website' => 'https://ladakh.gov.in',
    'row' => '#<li>\\s*<a\\s+href="(?<link>https://ladakh\\.gov\\.in/notice/[^"]+)"\\s*>(?<title>[^<]+)</a>\\s*<span class="date">(?<date>\\d{2} [A-Za-z]{3}, \\d{4})</span>#is',
    'date' => 'd M, Y',
    'keep' => '/recruit|advertisement|engagement|post of|posts of|vacanc|walk.?in|filling up/i',
    'drop' => '/result|admission|answer key|admit|merit|selection list|provisional|tender/i',
    'clean' => 
    array (
      0 => '/^\\s*Notice\\s*:-\\s*/i',
    ),
    'apply' => NULL,
  ),
  'https://kargil.nic.in/notice_category/recruitment/' => 
  array (
    'name' => 'District Administration Kargil (Ladakh)',
    'org_type' => 'state_govt',
    'state' => 'Ladakh',
    'location' => 'Kargil',
    'website' => 'https://kargil.nic.in',
    'row' => '#<tr>\\s*<td>(?<title>(?:(?!</td>).)+)</td>\\s*<td>(?:(?!</td>).)*</td>\\s*<td>(?<date>\\d{2}/\\d{2}/\\d{4})</td>\\s*<td>[^<]*</td>\\s*<td>\\s*<span class="pdf-downloads"><a [^>]*?href="(?<pdf>[^"]+)"#is',
    'date' => 'd/m/Y',
    'keep' => '/recruit|advertisement|advertisment|engagement|applications? (?:are )?invited|walk.?in|filling up|vacanc/i',
    'drop' => '/result|selection list|select list|selected|wait list|merit|provisional|answer key|admit|extension|shortlist|interview schedule|objection|document verification/i',
    'apply' => NULL,
  ),
);
