<?php

declare(strict_types=1);

namespace App\Services;

class ResumeParserService
{
    public function parse(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $text = '';
        if ($ext === 'pdf') {
            $text = $this->parsePdf($path);
        } elseif ($ext === 'doc' || $ext === 'docx') {
            $text = $this->parseDoc($path);
        } else {
            $text = file_exists($path) ? (string)file_get_contents($path) : '';
        }
        $text = $text ?? '';
        $sections = $this->detectSections($text);
        $pi = $this->extractPersonalInfo($text, $sections);
        $skillsData = $this->extractSkillsStructured($sections, $text);
        $experienceData = $this->extractExperienceStructured($sections, $text);
        $educationData = $this->extractEducationStructured($sections, $text);
        $languagesData = $this->extractLanguagesStructured($sections, $text);
        $certsData = $this->extractCertificatesStructured($sections, $text);
        $missing = [];
        if ($pi['full_name'] === null) $missing[] = 'full_name';
        if ($pi['email'] === null) $missing[] = 'email';
        if ($pi['mobile'] === null) $missing[] = 'mobile';
        if ($pi['location']['city'] === null && $pi['location']['state'] === null && $pi['location']['country'] === null) $missing[] = 'location';
        if (empty($skillsData)) $missing[] = 'skills_data';
        if (empty($experienceData)) $missing[] = 'experience_data';
        if (empty($educationData)) $missing[] = 'education_data';
        $confidence = $this->computeOverallConfidence($pi, $skillsData, $experienceData, $educationData, $languagesData, $certsData, $text);
        $out = [
            'full_name' => $pi['full_name'],
            'email' => $pi['email'],
            'mobile' => $pi['mobile'],
            'location' => $pi['location'],
            'skills_data' => $skillsData,
            'experience_data' => $experienceData,
            'education_data' => $educationData,
            'languages_data' => $languagesData,
            'certificates_data' => $certsData,
            'confidence_summary' => [
                'overall_confidence' => $confidence,
                'missing_fields' => $missing
            ]
        ];
        $compatName = $pi['full_name'];
        $compatPhone = $pi['mobile'];
        $compatLocationFlat = $pi['location']['city'] ?: ($pi['location']['state'] ?: $pi['location']['country']);
        $compatSkills = array_map(function($s){ return $s['name']; }, $skillsData);
        $compatExp = array_map(function($e){
            $parts = [];
            if (!empty($e['job_title'])) $parts[] = $e['job_title'];
            if (!empty($e['company_name'])) $parts[] = $e['company_name'];
            if (!empty($e['start_date']) || !empty($e['end_date'])) $parts[] = trim(($e['start_date'] ?? '') . ' - ' . ($e['end_date'] ?? ''));
            if (!empty($e['description'])) $parts[] = $e['description'];
            return implode(' | ', array_filter($parts));
        }, $experienceData);
        $compatEdu = array_map(function($e){
            $parts = [];
            if (!empty($e['degree'])) $parts[] = $e['degree'];
            if (!empty($e['institution'])) $parts[] = $e['institution'];
            if (!empty($e['year'])) $parts[] = (string)$e['year'];
            return implode(' | ', array_filter($parts));
        }, $educationData);
        $compatLangs = array_map(function($l){
            return $l['language'] . (!empty($l['proficiency']) ? ' - ' . $l['proficiency'] : '');
        }, $languagesData);
        $out['name'] = $compatName;
        $out['email_confidence'] = $pi['email'] ? 90 : 0;
        $out['phone'] = $compatPhone;
        $out['phone_confidence'] = $pi['mobile'] ? 70 : 0;
        $out['skills'] = $compatSkills;
        $out['skills_confidence'] = !empty($compatSkills) ? 60 : 0;
        $out['experience'] = $compatExp;
        $out['experience_confidence'] = !empty($compatExp) ? 50 : 0;
        $out['education'] = $compatEdu;
        $out['education_confidence'] = !empty($compatEdu) ? 50 : 0;
        $out['location'] = $compatLocationFlat;
        $out['location_confidence'] = $compatLocationFlat ? 40 : 0;
        $out['raw_text_length'] = strlen($text);
        return $out;
    }

    private function parsePdf(string $path): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function parseDoc(string $path): string
    {
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $sections = $phpWord->getSections();
            $texts = [];
            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $texts[] = (string)$element->getText();
                    }
                }
            }
            return implode("\n", $texts);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function detectSections(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $indices = [];
        $keys = [
            'personal' => ['personal information','contact','details'],
            'skills' => ['skills','technical skills','key skills','skills & competencies'],
            'experience' => ['experience','work experience','professional experience'],
            'education' => ['education','academic','qualifications'],
            'projects' => ['projects','academic projects','professional projects'],
            'certifications' => ['certifications','certificates','training'],
            'languages' => ['languages']
        ];
        for ($i=0;$i<count($lines);$i++) {
            $l = strtolower(trim($lines[$i]));
            foreach ($keys as $k => $words) {
                foreach ($words as $w) {
                    if ($l !== '' && (strpos($l, $w) === 0 || preg_match('/^[-*•\s]*'.$this->pregQuote($w).'\b/i', $lines[$i]))) {
                        $indices[] = [$k,$i];
                        break 2;
                    }
                }
            }
        }
        usort($indices, function($a,$b){ return $a[1] <=> $b[1]; });
        $sections = [];
        for ($j=0;$j<count($indices);$j++) {
            $key = $indices[$j][0];
            $start = $indices[$j][1]+1;
            $end = ($j+1<count($indices)) ? $indices[$j+1][1] : count($lines);
            $sections[$key] = implode("\n", array_slice($lines, $start, max(0,$end-$start)));
        }
        return $sections;
    }
 
    private function extractPersonalInfo(string $text, array $sections): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $fullName = null;
        for ($i=0;$i<min(10,count($lines));$i++) {
            $t = trim($lines[$i]);
            if ($t === '') continue;
            if (stripos($t,'resume') !== false || stripos($t,'curriculum') !== false) continue;
            if (preg_match('/name\s*[:\-]\s*(.+)$/i', $t, $m)) {
                $fullName = trim($m[1]);
                break;
            }
            if ($fullName === null && preg_match('/^[A-Za-z][A-Za-z .-]{2,}$/', $t)) {
                $fullName = trim($t);
                break;
            }
        }
        $email = null;
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text, $m)) {
            $email = $m[0];
        }
        $mobile = null;
        $digitsText = preg_replace('/[^0-9+ \-]/', ' ', $text);
        if (preg_match('/(\+?\d{1,3}[\s-]?)?(\d{10,12})/', $digitsText, $m)) {
            $mobile = trim($m[0]);
        }
        $loc = ['city'=>null,'state'=>null,'country'=>null];
        $countries = ['india','usa','united states','canada','uk','united kingdom','australia'];
        $states = ['delhi','maharashtra','karnataka','telangana','tamil nadu','west bengal'];
        $cities = ['delhi','mumbai','pune','bangalore','hyderabad','chennai','kolkata','gurgaon','noida'];
        $lower = strtolower($text);
        foreach ($cities as $c) { if (strpos($lower,$c) !== false) { $loc['city'] = ucfirst($c); break; } }
        foreach ($states as $s) { if (strpos($lower,$s) !== false) { $loc['state'] = ucwords($s); break; } }
        foreach ($countries as $c) { if (strpos($lower,$c) !== false) { $loc['country'] = ucwords($c); break; } }
        return ['full_name'=>$fullName,'email'=>$email,'mobile'=>$mobile,'location'=>$loc];
    }
 
    private function extractSkillsStructured(array $sections, string $text): array
    {
        $raw = $sections['skills'] ?? $text;
        $list = [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            $parts = preg_split('/[,|•\-]+/', $t);
            foreach ($parts as $p) {
                $n = trim($p);
                if ($n === '') continue;
                $n = preg_replace('/\s+/', ' ', $n);
                $list[] = $n;
            }
        }
        $list = array_values(array_unique(array_map(function($s){ return $s; }, $list)));
        $out = [];
        foreach ($list as $name) {
            $p = null;
            $lower = strtolower($name);
            if (preg_match('/\b(expert|advanced|proficient)\b/', $lower)) $p = 'advanced';
            elseif (preg_match('/\b(intermediate|competent)\b/', $lower)) $p = 'intermediate';
            elseif (preg_match('/\b(beginner|basic|familiar)\b/', $lower)) $p = 'basic';
            $clean = trim(preg_replace('/\b(expert|advanced|proficient|intermediate|competent|beginner|basic|familiar)\b/i','',$name));
            if ($clean === '') continue;
            $out[] = ['name'=>$clean,'proficiency'=>$p];
        }
        return $out;
    }
 
    private function extractExperienceStructured(array $sections, string $text): array
    {
        $raw = $sections['experience'] ?? '';
        if ($raw === '') $raw = $text;
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $entries = [];
        $buffer = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/\b(19|20)\d{2}\b/', $t) || preg_match('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Sept|Oct|Nov|Dec)[a-z]*\s+\d{4}\b/i', $t) || stripos($t,'present') !== false) {
                if (!empty($buffer)) {
                    $entries[] = implode(' ', $buffer);
                    $buffer = [];
                }
            }
            $buffer[] = $t;
        }
        if (!empty($buffer)) $entries[] = implode(' ', $buffer);
        $out = [];
        foreach ($entries as $entry) {
            $job = null; $company = null; $sd = null; $ed = null; $desc = null;
            if (preg_match('/(.+?)\s+at\s+(.+?)(?:\s|,|$)/i', $entry, $m)) {
                $job = trim($m[1]); $company = trim($m[2]);
            }
            if (!$job && preg_match('/^([A-Za-z][^,|]+)\s*[-|]\s*([A-Za-z][^,|]+)/', $entry, $m)) {
                $job = trim($m[1]); $company = trim($m[2]);
            }
            if (preg_match('/((Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}|\b(19|20)\d{2}\b)\s*[-–to]+\s*((Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}|\b(19|20)\d{2}\b|Present)/i', $entry, $m)) {
                $sd = $this->normalizeDate($m[1]);
                $ed = $this->normalizeDate($m[4]);
            } elseif (preg_match('/\b(19|20)\d{2}\b/', $entry, $m)) {
                $sd = $this->normalizeDate($m[0]);
            }
            $desc = trim($entry);
            $out[] = [
                'job_title' => $job ?: null,
                'company_name' => $company ?: null,
                'start_date' => $sd,
                'end_date' => $ed,
                'description' => $desc ?: null
            ];
        }
        return $out;
    }
 
    private function extractEducationStructured(array $sections, string $text): array
    {
        $raw = $sections['education'] ?? '';
        if ($raw === '') $raw = $text;
        $degrees = ['btech','mtech','b.e','be','bsc','msc','bca','mca','bcom','mcom','mba','phd','bachelor','master','engineering','computer science','commerce','arts','science'];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out = [];
        foreach ($lines as $line) {
            $l = strtolower($line);
            $deg = null;
            foreach ($degrees as $d) {
                if (strpos($l, $d) !== false) { $deg = $d; break; }
            }
            if ($deg) {
                $inst = null; $year = null;
                if (preg_match('/\b(19|20)\d{2}\b/', $line, $m)) $year = (int)$m[0];
                if (preg_match('/(?:at|from)\s+(.+)$/i', $line, $m)) $inst = trim($m[1]);
                if (!$inst) {
                    $parts = array_map('trim', preg_split('/[,|\-]+/', $line));
                    $inst = isset($parts[1]) ? $parts[1] : null;
                }
                $degClean = strtoupper($deg) === 'BE' ? 'B.E' : ucwords($deg);
                $out[] = ['degree'=>$degClean,'institution'=>$inst ?: null,'year'=>$year ? (string)$year : null];
            }
        }
        return $out;
    }
 
    private function extractLanguagesStructured(array $sections, string $text): array
    {
        $raw = $sections['languages'] ?? '';
        if ($raw === '') return [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            $parts = preg_split('/[,|]+/', $t);
            foreach ($parts as $p) {
                $seg = trim($p);
                if ($seg === '') continue;
                $lang = $seg; $prof = null;
                if (preg_match('/^(.*?)[\s\-–:]+(fluent|native|advanced|intermediate|basic|beginner)$/i', $seg, $m)) {
                    $lang = trim($m[1]); $prof = strtolower($m[2]);
                }
                $out[] = ['language'=>$lang,'proficiency'=>$prof];
            }
        }
        return $out;
    }
 
    private function extractCertificatesStructured(array $sections, string $text): array
    {
        $raw = $sections['certifications'] ?? '';
        if ($raw === '') return [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            $name = $t; $issuer = null;
            if (preg_match('/^(.*?)\s+(?:-|by)\s+(.+)$/i', $t, $m)) {
                $name = trim($m[1]); $issuer = trim($m[2]);
            }
            $out[] = ['name'=>$name,'issuer'=>$issuer];
        }
        return $out;
    }
 
    private function computeOverallConfidence(array $pi, array $skills, array $exp, array $edu, array $langs, array $certs, string $text): int
    {
        $score = 0;
        if ($pi['full_name']) $score += 10;
        if ($pi['email']) $score += 15;
        if ($pi['mobile']) $score += 10;
        if ($pi['location']['city'] || $pi['location']['state'] || $pi['location']['country']) $score += 10;
        if (!empty($skills)) $score += min(20, count($skills)*2);
        if (!empty($exp)) $score += min(20, count($exp)*5);
        if (!empty($edu)) $score += min(10, count($edu)*3);
        if (!empty($langs)) $score += min(5, count($langs)*2);
        if (!empty($certs)) $score += min(10, count($certs)*2);
        $len = strlen($text);
        if ($len > 2000) $score += 5;
        return max(0, min(100, $score));
    }
 
    private function normalizeDate(string $input): ?string
    {
        $t = trim($input);
        if ($t === '') return null;
        if (preg_match('/\b(19|20)\d{2}\b/', $t, $m)) {
            return sprintf('%s-01-01', $m[0]);
        }
        if (preg_match('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(\d{4})\b/i', $t, $m)) {
            $map = ['Jan'=>'01','Feb'=>'02','Mar'=>'03','Apr'=>'04','May'=>'05','Jun'=>'06','Jul'=>'07','Aug'=>'08','Sep'=>'09','Sept'=>'09','Oct'=>'10','Nov'=>'11','Dec'=>'12'];
            $mon = $map[ucfirst(strtolower($m[1]))] ?? '01';
            return sprintf('%s-%s-01', $m[2], $mon);
        }
        if (stripos($t,'present') !== false) {
            return null;
        }
        return null;
    }
 
    private function pregQuote(string $s): string
    {
        return preg_quote($s, '/');
    }
}
