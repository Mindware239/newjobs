<?php
// Listed companies – PSUs and banks on NSE/BSE with server-rendered recruitment pages (GovtListFetcher profiles).
// Surveyed and tested 2026-10-06. No state = All India. Not added: NALCO, BEL (incomplete TLS chains), IOCL (JS challenge),
// NMDC / HAL / RVNL / GAIL / Oil India / IRCON / NLC (JavaScript), IRCTC (robots.txt Disallow: /), Coal India / Canara / HPCL / BHEL (no dates),
// private companies (JavaScript ATS portals or 403 – no internal APIs used).
return array (
  'https://careers.ntpc.co.in/recruitment/' => 
  array (
    'name' => 'NTPC Limited (NTPC)',
    'org_type' => 'psu',
    'website' => 'https://ntpc.co.in',
    'row' => '#<div class="job-post-info">\\s*<h5>(?<title>.*?)</h5>(?:[^<]++|<(?!div class="job-post-info">))*?<b>Posting date\\s*:\\s*</b>\\s*(?<date>\\d{4}-\\d{2}-\\d{2})#is',
    'date' => 'Y-m-d',
    'keep' => '/recruitment|engagement of|online application portal will remain open/i',
    'drop' => '/^(?:result|list of|online interviews|interviews for|shortlist|individual scorecard|link for downloading|indicative syllabus|the computer based test|cbt for|reference is made)|admit card|cancelled|stands closed|has been closed|scores and cut-off/i',
    'apply' => 'https://careers.ntpc.co.in/recruitment/',
    'link_to_home' => true,
  ),
  'https://ongcindia.com/web/eng/career/recruitment-notice' => 
  array (
    'name' => 'Oil and Natural Gas Corporation Limited (ONGC)',
    'org_type' => 'psu',
    'website' => 'https://ongcindia.com',
    'row' => '#<li class="list-group-item">\\s*<a\\b[^>]*?href="?(?<link>[^"\\s>]+)"?[^>]*>\\s*<(?:span|p) class="list-group-title">(?<title>.*?)</(?:span|p)>\\s*</a>\\s*<p class="list-group-subtitle text-muted">\\s*(?<date>\\d{1,2} [A-Za-z]{3}, \\d{4})\\s*</p>#is',
    'date' => 'j M, Y',
    'drop' => '/call letter|result|admit|answer key|shortlist|interview schedule|corrigendum|addendum|syllabus|cut.?off|list of|selected candidates|postpone|last date .* extended/i',
    'apply' => 'https://ongcindia.com/web/eng/career/recruitment-notice',
  ),
  'https://www.powergrid.in/en/job-opportunities' => 
  array (
    'name' => 'Power Grid Corporation of India Limited (POWERGRID)',
    'org_type' => 'psu',
    'website' => 'https://www.powergrid.in',
    'row' => '#<span class="fa fa-calendar"></span>\\s*(?<date>\\d{2}/\\d{2}/\\d{4})\\s*</li>(?:[^<]++|<(?!div class="newBorderBoxTop"))*?<h4 class="midHeadSize">(?<title>.*?)</h4>(?:[^<]++|<(?!div class="newBorderBoxTop"))*?<a\\s[^>]*?href="(?<pdf>https://www\\.powergrid\\.in/sites/default/files/[^"]+\\.pdf)"#is',
    'date' => 'd/m/Y',
    'apply' => 'https://www.powergrid.in/en/job-opportunities',
  ),
  'https://sbi.bank.in/web/careers/current-openings' => 
  array (
    'name' => 'State Bank of India (SBI)',
    'org_type' => 'bank',
    'website' => 'https://sbi.bank.in',
    'row' => '#data-articleid="[^"]*"[^>]*>\\s*<div class="row">\\s*<div[^>]*>\\s*(?<title><p>[^<]*<span[^>]*>[^<]*?Apply Online from (?<date>\\d{2}\\.\\d{2}\\.\\d{4})[^<]*</span>\\s*</p>\\s*<p>ADVERTISEMENT NO:[^<]*</p>)(?:[^<]++|<(?!div class="accordion lateral))*?<button[^>]*>\\s*LAST DATE TO APPLY\\s*:\\s*(?<last>\\d{2}-\\d{2}-\\d{4})(?:[^<]++|<(?!div class="accordion lateral))*?<a\\s[^>]*?href="(?<pdf>/documents/[^"]+)"#is',
    'date' => 'd.m.Y',
    'clean' => 
    array (
      0 => '/\\s*\\(+\\s*Apply Online from [^)]*\\)/i',
    ),
    'apply' => 'https://sbi.bank.in/web/careers/current-openings',
  ),
  'https://pnb.bank.in/Recruitments.aspx' => 
  array (
    'name' => 'Punjab National Bank (PNB)',
    'org_type' => 'bank',
    'website' => 'https://pnb.bank.in',
    'row' => '#<h2><span id="ContentPlaceHolder1_rptGrid_Label1_\\d+">(?<date>\\d{2}\\.\\d{2}\\.\\d{4})</span></h2>\\s*<p><span id="ContentPlaceHolder1_rptGrid_lblName_\\d+">(?<title>.*?)</span></p>#is',
    'date' => 'd.m.Y',
    'drop' => '/result|admit|call letter|interview|shortlist|selected|answer key/i',
    'apply' => 'https://pnb.bank.in/Recruitments.aspx',
    'link_to_home' => true,
  ),
  'https://www.bharatpetroleum.in/careers/job-openings' => 
  array (
    'name' => 'Bharat Petroleum Corporation Limited (BPCL)',
    'org_type' => 'psu',
    'website' => 'https://www.bharatpetroleum.in',
    'row' => '#<tr>\\s*<td>\\s*<p>\\d+</p>\\s*</td>\\s*<td>\\s*<p>(?<title>[^<]*)</p>(?:[^<]++|<(?!/tr>))*?<p>[^<]*?open from (?<date>\\d{1,2}(?:st|nd|rd|th) [A-Za-z]+ \\d{4})(?:[^<]++|<(?!/tr>))*?<a\\s[^>]*?href="(?<pdf>[^"]+\\.pdf)"(?:[^<]++|<(?!/tr>))*?<td>\\s*(?:<p>)?\\s*(?<last>\\d{2}\\.\\d{2}\\.\\d{4})#is',
    'date' => 'jS F Y',
    'apply' => 'https://www.bharatpetroleum.in/careers/job-openings',
  ),
  'https://sailcareers.com/Archive.aspx?Section=Jobs' => 
  array (
    'name' => 'Steel Authority of India Limited (SAIL)',
    'org_type' => 'psu',
    'website' => 'https://sailcareers.com',
    'row' => '#<a class=hcatlist href=\'(?<pdf>[^\']*_(?<date>\\d{8})_\\d{6}\\.pdf)\'[^>]*>(?:<img[^>]*>)?(?<title>.*?)</a>#is',
    'date' => 'dmY',
    'drop' => '/result|shortlist|call letter|corrigendum|selected|admission|score card|cut.?off|reporting schedule|bio data|answer key|admit/i',
    'clean' => 
    array (
      0 => '/^[\\s"“]+|[\\s"”]+$/u',
    ),
    'apply' => 'https://sailcareers.com/',
  ),
  'https://www.hindustancopper.com/Page/Career_new' => 
  array (
    'name' => 'Hindustan Copper Limited (HCL)',
    'org_type' => 'psu',
    'website' => 'https://www.hindustancopper.com',
    'row' => '#<tr>\\s*(?:<!--.*?-->\\s*)?<td>(?<advt>[^<]*)</td>\\s*<td>(?<title>[^<]*)</td>\\s*<td>(?<date>\\d{2}-[A-Za-z]{3}-\\d{4})</td>\\s*<td>(?<last>\\d{2}-[A-Za-z]{3}-\\d{4})</td>\\s*<td[^>]*>\\s*<a[^>]*href=["\']?(?:\\.\\./)*(?<pdf>[^"\'\\s>]+)#is',
    'date' => 'd-M-Y',
    'apply' => 'https://www.hindustancopper.com/Page/Career_new',
  ),
  'https://mazagondock.in/English/career/Career-Executives' => 
  array (
    'name' => 'Mazagon Dock Shipbuilders Limited (MDL)',
    'org_type' => 'psu',
    'state' => 'Maharashtra',
    'location' => 'Mumbai',
    'website' => 'https://mazagondock.in',
    'row' => '#<tr>\\s*<td>\\s*\\d+\\s*</td>\\s*<td>\\s*(?<date>\\d{2}/\\d{2}/\\d{4})\\s*</td>\\s*<td>.*?</td>\\s*<td>(?<title>.*?)</td>\\s*<td>.*?</td>\\s*<td>(?<last>.*?)</td>\\s*<td>.*?href="(?<pdf>[^"]+)"#is',
    'date' => 'd/m/Y',
    'apply' => 'https://mazagondock.in/app/MDLJobPortal/Welcome.aspx',
  ),
  'https://cochinshipyard.in/Careers' => 
  array (
    'name' => 'Cochin Shipyard Limited (CSL)',
    'org_type' => 'psu',
    'website' => 'https://cochinshipyard.in',
    'row' => '#<tr><td>\\d+</td><td class=\'text-center\'>(?<title>[^<]*)</td><td>(?<last>\\d{2}-\\d{2}-\\d{4})</td><td>(?<location>[^<]*)</td>\\s*<td><a href="(?<link>[^"]+)"#is',
    'last_date' => 'd-m-Y',
    'apply' => 'https://cochinshipyard.in/Careers',
  ),
  'https://www.bemlindia.in/careers/' => 
  array (
    'name' => 'BEML Limited (BEML)',
    'org_type' => 'psu',
    'website' => 'https://www.bemlindia.in',
    'row' => '#<div class="pdf_urls_add_carrears"><h2>(?<advt>[^<]*)</h2>\\s*<div class="row">\\s*<div class="col-12 dateend"><span>Closing Date:\\s*(?<last>\\d{1,2}-[A-Za-z]{3}-\\d{4})</span>(?:[^<]++|<(?!div class="pdf_urls_add_carrears"))*?<a href="(?<pdf>[^"]+)"[^>]*>(?<title>[^<]*)#is',
    'last_date' => 'j-M-Y',
    'drop' => '/shortlist|selected|result|cancel|assessment|notification regarding|application form|^advertisement( in hindi)?$/i',
    'apply' => 'https://www.bemlindia.in/careers/',
  ),
);
