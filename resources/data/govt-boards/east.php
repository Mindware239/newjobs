<?php
// State recruitment boards – East & North-East India (GovtListFetcher profiles; keys = official listing URL).
// Surveyed and tested 2026-10-06. Mizoram URL changes each financial year (update in April). Not added: UPSSSC, WBPSC, WB Police,
// Kolkata Police (legacy TLS renegotiation), APSC (missing intermediate), BPSC (JS), BSSC / JSSC / OSSSC / SLPRB Assam (TLS / no dates),
// OPSC (postbacks), OSSC (timeouts), JPSC / WBSSC / TPSC (nothing current), UP Police (no 2026 recruitment notice).
return array (
  'https://uppsc.up.nic.in/CandidatePages/Notifications.aspx' => 
  array (
    'name' => 'Uttar Pradesh Public Service Commission (UPPSC)',
    'org_type' => 'state_govt',
    'state' => 'Uttar Pradesh',
    'location' => 'Prayagraj',
    'website' => 'https://uppsc.up.nic.in',
    'row' => '#Lbl_Exam_Name"[^>]*>(?<title>.*?_Lbl_Adv_number"[^>]*>[^<]*)</span>.*?_Lbl_adv_date"[^>]*>(?<date>\\d{2}/\\d{2}/\\d{4})</span>.*?_Lbl_ApplicationFormRegistration_LastDate"[^>]*>(?<last>\\d{2}/\\d{2}/\\d{4})</span>.*?href="(?:\\.\\./)?(?<link>OuterPages/View_Advertisement\\.aspx[^"]+)"#is',
    'date' => 'd/m/Y',
    'apply' => 'https://uppsc.up.nic.in/CandidatePages/Notifications.aspx',
    'title_prefix' => 'UPPSC ',
  ),
  'https://csbc.bihar.gov.in/' => 
  array (
    'name' => 'Central Selection Board of Constable, Bihar (CSBC)',
    'org_type' => 'police',
    'state' => 'Bihar',
    'location' => 'Patna',
    'website' => 'https://csbc.bihar.gov.in',
    'row' => '#<td class="C3"[^>]*>\\s*(?<date>\\d{2}-\\d{2}-\\d{4})\\s*</td>\\s*<td[^>]*>\\s*<a [^>]*href="(?<pdf>[^"]+)"[^>]*>(?<title>.*?)</a>#is',
    'date' => 'd-m-Y',
    'keep' => '/^Advt\\.?\\s*No\\./i',
    'drop' => '/result|admit|answer key|corrigendum/i',
    'clean' => 
    array (
      0 => '/^Important Notice\\s*\\d*\\s*:\\s*/i',
    ),
    'apply' => 'https://csbc.bihar.gov.in',
  ),
  'https://bpssc.bihar.gov.in/' => 
  array (
    'name' => 'Bihar Police Subordinate Services Commission (BPSSC)',
    'org_type' => 'police',
    'state' => 'Bihar',
    'location' => 'Patna',
    'website' => 'https://bpssc.bihar.gov.in',
    'row' => '#<td class="C8"[^>]*>\\s*(?<date>\\d{2}/\\d{2}/\\d{4})\\s*</td>\\s*<td[^>]*>\\s*<a [^>]*href="(?<pdf>[^"]+)"[^>]*>(?<title>.*?)</a>#is',
    'date' => 'd/m/Y',
    'keep' => '/^Advt\\.?\\s*No\\.?\\s*-?\\s*\\d+\\/\\d{4}\\s*:/i',
    'drop' => '/result|admit|answer key|corrigendum/i',
    'apply' => 'https://bpssc.bihar.gov.in',
  ),
  'https://jhpolice.gov.in/recruitments' => 
  array (
    'name' => 'Jharkhand Police',
    'org_type' => 'police',
    'state' => 'Jharkhand',
    'location' => 'Ranchi',
    'website' => 'https://jhpolice.gov.in',
    'row' => '#<li class="">\\s*<a href="(?<link>/recruitments/[^"]+)">(?<title>.*?)</a>\\s*<font class="update">\\s*:\\s*(?<date>\\d{2}-\\d{2}-\\d{4})\\s*</font>#is',
    'date' => 'd-m-Y',
    'drop' => '/result|admit|answer key|merit list|interview/iu',
    'apply' => 'https://jhpolice.gov.in/recruitments',
  ),
  'https://mpsc.meghalaya.gov.in/advertisements.html' => 
  array (
    'name' => 'Meghalaya Public Service Commission (MPSC)',
    'org_type' => 'state_govt',
    'state' => 'Meghalaya',
    'location' => 'Shillong',
    'website' => 'https://mpsc.meghalaya.gov.in',
    'row' => '#<a href="(?<pdf>advt/Advt[^"]+\\.pdf)"[^>]*>(?<title>.*?)</a>\\s*</li>\\s*</td>\\s*<td>\\s*<p>\\s*<strong>\\s*(?<date>\\d{2}-\\d{2}-\\d{4})\\s*</strong>#is',
    'date' => 'd-m-Y',
    'clean' => 
    array (
      0 => '/^.*?(?:post\\(s\\)|posts?) mentioned below\\s*:?\\s*-?\\s*/is',
    ),
    'title_prefix' => 'Meghalaya PSC recruitment: ',
    'apply' => 'https://rpa.meghalaya.gov.in/rpaonline',
  ),
  'https://mpsc.mizoram.gov.in/page/advertisement-2026-2027' => 
  array (
    'name' => 'Mizoram Public Service Commission (MPSC)',
    'org_type' => 'state_govt',
    'state' => 'Mizoram',
    'location' => 'Aizawl',
    'website' => 'https://mpsc.mizoram.gov.in',
    'row' => '#<a class="fr-file" href="(?<pdf>[^"]+\\.pdf)"[^>]*>(?<title>Advertisement[^<]*)</a>(?:(?!</tr>).)*?</td>\\s*<td[^>]*>\\s*(?<date>\\d{2}\\.\\d{2}\\.\\d{4})\\s*</td>\\s*<td[^>]*>\\s*(?<last>\\d{2}\\.\\d{2}\\.\\d{4})#is',
    'date' => 'd.m.Y',
    'drop' => '/corrigendum|addendum|result|answer key|admit/i',
    'title_prefix' => 'Mizoram PSC ',
    'apply' => 'https://mpsc.mizoram.gov.in',
  ),
  'https://mpscmanipur.gov.in/whats_new.html' => 
  array (
    'name' => 'Manipur Public Service Commission (MPSC)',
    'org_type' => 'state_govt',
    'state' => 'Manipur',
    'location' => 'Imphal',
    'website' => 'https://mpscmanipur.gov.in',
    'row' => '#<tr>\\s*<td>\\s*</td>\\s*<td>\\s*<a class="notification-link" href="(?<pdf>[^"]+)">(?<title>.*?)</a>(?:(?!</tr>).)*?class="upload-date">\\s*(?<date>\\d{2}-\\d{2}-\\d{4})\\s*<#is',
    'date' => 'd-m-Y',
    'keep' => '/notification for (?:the )?recruitment|advertisement|applications? (?:are )?invited|recruitment of \\d+ posts?/i',
    'drop' => '/schedule|answer key|result|cut.?off|marks|admit|interview|corrigendum|defer|postpone/i',
    'clean' => 
    array (
      0 => '/\\s*New\\s*$/',
    ),
    'apply' => 'https://mpscmanipur.gov.in',
  ),
  'https://appsc.gov.in/Index/sub_page/doc2195/Advertisements' => 
  array (
    'name' => 'Arunachal Pradesh Public Service Commission (APPSC)',
    'org_type' => 'state_govt',
    'state' => 'Arunachal Pradesh',
    'location' => 'Itanagar',
    'website' => 'https://appsc.gov.in',
    'row' => '#<a target=\'_blank\' href=\'(?<pdf>[^\']+_(?<date>20\\d{6})_\\d{6}\\.pdf)\'[^>]*>(?<title>[^<]+)</a>#i',
    'date' => 'Ymd',
    'keep' => '/advertisement/i',
    'drop' => '/corrigendum|cancellation|addendum/i',
    'apply' => 'https://appsc.gov.in',
  ),
  'https://npsc.nagaland.gov.in/advertisement' => 
  array (
    'name' => 'Nagaland Public Service Commission (NPSC)',
    'org_type' => 'state_govt',
    'state' => 'Nagaland',
    'location' => 'Kohima',
    'website' => 'https://npsc.nagaland.gov.in',
    'row' => '#<div class="fw-bold">\\s*<a href="(?<link>https://npsc\\.nagaland\\.gov\\.in/advertisement/\\d+)">(?<title>.*?)</a>.*?Published on\\s*:\\s*</i>\\s*(?<date>\\d{2}-\\d{2}-\\d{4})#is',
    'date' => 'd-m-Y',
    'keep' => '/^advertisement/i',
    'drop' => '/corrigendum|addendum|result|admission certificate|cancel/i',
    'apply' => 'https://npsc.nagaland.gov.in',
  ),
  'https://spsc.sikkim.gov.in/Advertisement.html' => 
  array (
    'name' => 'Sikkim Public Service Commission (SPSC)',
    'org_type' => 'state_govt',
    'state' => 'Sikkim',
    'location' => 'Gangtok',
    'website' => 'https://spsc.sikkim.gov.in',
    'row' => '#<tr>\\s*<td class="pl-4">\\s*<a href="[^"]*">(?<title>.*?)</a>(?:(?!</tr>).)*?Issued Date:\\s*(?<date>\\d{2}/\\d{2}/\\d{4})(?:(?!</tr>).)*?<a href="(?<pdf>[^"]+\\.pdf)"[^>]*>\\s*Advertisement\\s*</a>#is',
    'date' => 'd/m/Y',
    'drop' => '/corrigendum|addendum|result/i',
    'apply' => 'https://spscrecruitment.sikkim.gov.in/rpaonline/login',
  ),
  'https://nhmssd.assam.gov.in/eHRMIS_latest/Recruitments/' => 
  array (
    'name' => 'National Health Mission, Assam (NHM Assam)',
    'org_type' => 'state_govt',
    'state' => 'Assam',
    'location' => 'Guwahati',
    'website' => 'https://nhm.assam.gov.in',
    'row' => '#(?|<label[^>]*>(?<title>[^<]*?(?<date>\\d{2}\\.\\d{2}\\.\\d{4})[^<]*)</label>\\s*<li[^>]*>\\s*<a href=\'(?<pdf>[^\']+)\'|<label[^>]*>(?<title>[^<]*)</label>(?=\\s*<li[^>]*>\\s*<a href=\'[^\']+\'[^>]*>[^<]*?(?<date>\\d{2}\\.\\d{2}\\.\\d{4}))\\s*<li[^>]*>\\s*<a href=\'(?<pdf>[^\']+)\')#is',
    'date' => 'd.m.Y',
    'keep' => '/advertisement|vacanc|walk.?in|recruitment|engagement/i',
    'drop' => '/transfer|posting|extension|merit|result|cancel|postpone|corrigendum|addendum|interview|counsel+ing|revert|rationali|selected|list of/i',
    'clean' => 
    array (
      0 => '/[_\\s]*(?:dtd\\.?\\s*)?\\d{2}\\.\\d{2}\\.\\d{4}\\.*\\s*$/',
    ),
    'apply' => 'https://nhmssd.assam.gov.in/eHRMIS_latest/Recruitments/',
  ),
);
