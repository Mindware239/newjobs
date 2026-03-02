

<?php
$base = $base ?? '/';
$jobs = $jobs ?? [];

// Read GET values
$keyword = isset($_GET['keyword']) ? trim((string)$_GET['keyword']) : '';
$location = isset($_GET['location']) ? trim((string)$_GET['location']) : '';
$radius = isset($_GET['radius']) ? trim((string)$_GET['radius']) : '';

// When search params are present, fetch filtered jobs from social_jobs
try {
    $db = \App\Core\Database::getInstance();
    $pdo = $db->getConnection();
    $sql = "SELECT sj.*, so.logo_url 
            FROM social_jobs sj 
            LEFT JOIN social_organizations so 
              ON so.employer_id = sj.employer_id 
             AND so.organization_name = sj.organization_name
            WHERE sj.is_deleted = 0";
    $params = [];
    if ($keyword !== '') {
        $sql .= " AND (role_name LIKE :kw OR short_description LIKE :kw OR full_description LIKE :kw OR organization_name LIKE :kw)";
        $params['kw'] = '%' . $keyword . '%';
    }
    if ($location !== '') {
        $sql .= " AND (job_location LIKE :loc OR location_details LIKE :loc)";
        $params['loc'] = '%' . $location . '%';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Map to UI schema
    $jobs = [];
    foreach ($rows as $row) {
        $website = (string)($row['website'] ?? '');
        $host = '';
        if ($website !== '') {
            $url = preg_match('~^https?://~i', $website) ? $website : ('http://' . $website);
            $host = parse_url($url, PHP_URL_HOST) ?: '';
        }
        $logo = !empty($row['logo_url']) ? (string)$row['logo_url'] : ($host ? ('https://www.google.com/s2/favicons?domain=' . $host . '&sz=64') : '/uploads/mindware-infotechlogo.png');
        $jobs[] = [
            'id'          => (int)($row['id'] ?? 0),
            'title'       => (string)($row['role_name'] ?? ''),
            'company'     => (string)($row['organization_name'] ?? ''),
            'location'    => (string)($row['job_location'] ?? ($row['location_details'] ?? '')),
            'type'        => (string)($row['time_commitment'] ?? ''),
            'workplace'   => (string)($row['workplace_option'] ?? ''),
            'description' => (string)strip_tags($row['full_description'] ?? ($row['short_description'] ?? '')),
            'posted'      => !empty($row['created_at']) ? date('F d, Y', strtotime($row['created_at'])) : '',
            'expires'     => !empty($row['publish_date']) ? date('F d, Y', strtotime($row['publish_date'])) : 'Open',
            'education'   => (string)($row['education_level'] ?? ''),
            'experience'  => (int)($row['experience_years'] ?? 0),
            'mission_focus' => (string)($row['org_mission_focus'] ?? ''),
            'category'    => (string)($row['work_category'] ?? ''),
            'publish_type'=> (string)($row['publish_type'] ?? 'standard'),
            'logo'        => $logo,
        ];
    }
} catch (\Throwable $e) {
    // leave $jobs as passed in if query fails
}

$navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label' => 'Pricing', 'url' => 'pricing'],
    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutus'],
    ['label' => 'Support', 'url' => 'supports'],
    ['label' => 'Specials', 'url' => 'specials'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find a Job | Mindware Infotech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="text-slate-800" x-data="jobSearch()">

<header class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50" x-data="{ mobileMenu: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="uploads/Mindware-infotech.png" alt="Logo" class="h-10 md:h-14 w-auto">
            </a>
        </div>

        <div class="hidden lg:flex items-center gap-6">
            <div class="text-[14px]">
                <span class="text-black font-medium mr-2">Candidates & Job Seekers:</span>
                <a href="/social-services/login" class="text-[#e15f55] font-semibold hover:underline">Login/CreateAccount</a>
            </div>
            <a href="employers" 
               class="bg-[#e15f55] text-white px-6 py-2.5 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white hover:border-[#e15f55] border-2 hover:text-[#e15f55] transition-all duration-300">
                Employers
            </a>
        </div>

        <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
<nav class="hidden min-[900px]:block border-t border-gray-50">
<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">

<?php 
$current_page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
if ($current_page === '') $current_page = 'index';

$navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label'=> 'Find a job','url'=> 'find-a-job'],
    ['label'=> 'Create job alerts','url'=> 'social-services/login'],
    ['label'=> 'Search Employers','url'=> 'searchEmployers'],
    ['label' => 'Career Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutus'],
    ['label' => 'Support', 'url' => 'supports'],
];

foreach($navItems as $item): 

$isActive = ($current_page === $item['url']);

$class = $isActive
    ? 'text-[#e15f55] border-b-2 border-[#e15f55]'
    : 'text-black border-b-2 border-transparent hover:text-[#e15f55] hover:border-[#e15f55]';
?>

<a href="<?= $base . $item['url']; ?>"
   class="py-4 text-[15px] font-semibold transition-all duration-300 <?= $class ?>">
   <?= $item['label']; ?>
</a>

<?php endforeach; ?>

</div>
</nav>



    <div x-show="mobileMenu" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden bg-white px-4 border-t shadow-lg overflow-y-auto max-h-[calc(100vh-80px)]">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-50 hover:text-[#e15f55]">
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-6 pb-4 space-y-3">
                <a href="candidate" class="block w-full py-4 bg-[#5b6bd5] text-white text-center font-bold rounded shadow-md">JOBSEEKERS</a>
                <div class="text-center text-[14px] py-2 text-[#54595f]">
                    Employers: <a href="employers" class="text-[#e15f55] font-bold">Login</a>
                </div>
            </li>
        </ul>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pb-24 pt-12">
    <div class="flex flex-col lg:flex-row gap-10">
        
        <aside class="w-full lg:w-80 flex-shrink-0">
            <div class="bg-gray-200 p-7 border border-slate-200 lg:sticky lg:top-28">    
                <div class="flex items-center justify-between mb-8">
                    <h6 class="font-bold text-[16px] text-slate-800 flex items-center gap-2">
                        Search job postings by using one or more of the filters below.
                    </h6>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="text-[16px] font-bold tracking-wider text-black block mb-2">Keywords</label>
                        <div class="relative group">
                            <input type="text" x-model="searchKeyword" @input="currentPage = 1" class="w-full pl-3 pr-4 py-3 bg-white border border-black text-sm"
                                   placeholder="eg. development,remote...">
                        </div>
                    </div>

                    <div>
                        <label class="text-[16px] font-bold tracking-wider text-black block mb-2">State / UT</label>
                        <div class="relative">
                            <select x-model="selectedState" @change="currentPage = 1" class="w-full px-4 py-3 bg-white border border-black text-sm appearance-none">
                                <option value="">Select Location</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Karnataka">Karnataka</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-4 top-4 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-[16px] font-bold tracking-wider text-black block mb-2">City or zip code</label>
                        <div class="relative group">
                            <input type="text" class="w-full pl-3 pr-4 py-3 bg-white border border-black text-sm"
                                   placeholder="Enter city or zip code">
                        </div>            
                    </div>

                    <div>
                        <label class="text-[16px] font-bold tracking-wider text-black block mb-3">Time Commitment</label>
                        <div class="relative">
                            <select class="w-full px-4 py-3 bg-white border border-black text-sm appearance-none">
                                 <option value="">Select Location</option>
                                 <option value="Contract">Contract</option>
                                 <option value="Full-time">Full-time</option>
                                 <option value="Intern">Intern</option>
                                 <option value="Part-time">Part-time</option>
                                 <option value="Temporary / Seasonal">Temporary / Seasonal</option>
                                 <option value="Volunteer">Volunteer</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-4 top-4 text-slate-400 pointer-events-none text-xs"></i>      
                        </div>
                    </div>

                    <div>
    <label class="text-[16px] font-bold tracking-wider text-black block mb-3">Workplace options</label>
    <div class="relative border border-black bg-white p-4 text-sm space-y-3">
<template x-for="option in ['On-site', 'Remote', 'Hybrid']">
    <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" :value="option" x-model="selectedWorkplaces" @change="currentPage = 1" class="w-4 h-4 border-gray-400">
                <span x-text="option"></span>
            </label>
        </template>
    </div>
</div>

                    <div>
                        <label class="text-[16px] font-bold tracking-wider text-black block mb-3">Education Level</label>
                            <div class="relative border border-black bg-white p-4 text-sm space-y-3 max-h-[320px] overflow-y-auto custom-scrollbar">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="Associate's Degree" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">Associate's Degree</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="Bachelor's Degree" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">Bachelor's Degree</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="Doctorate" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">Doctorate</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="High-school Diploma / GED" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">High-school Diploma / GED</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="JD" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">JD</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="Master's Degree" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">Master's Degree</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="MBA" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">MBA</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="MD / DDS" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">MD / DDS</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="PhD" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">PhD</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="education[]" value="Some College" class="w-4 h-4 border-gray-400 rounded-sm accent-[#5b6bd5] cursor-pointer">
                                        <span class="text-black group-hover:text-gray-600 transition-colors">Some College</span>
                                </label>
                            </div>
                    </div>
                    
                    <div class="mb-6" x-data="{ minExp: 0 }">
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-[16px] font-bold tracking-wider text-black block">
                                 Min. Experience
                            </label>
                        </div>
                        <div class="relative group border border-black p-4 bg-white">
                           <div class="relative flex items-center">
                               <input  type="range" min="0" max="100" step="1" x-model="minExp" @input="currentPage = 1" class="w-full h-1.5 bg-gray-200 appearance-none cursor-pointer accent-black hover:accent-gray-800 transition-all">
                               <input type="hidden" :value="minExp">  
                            </div>
                        </div> 
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px] font-bold text-black uppercase"><span x-text="minExp"></span> Yrs</span>
                            <span class="text-[10px] font-bold text-black uppercase">100 Yrs</span> 
                        </div>     
                    </div>

                    <div class="mb-6" x-data="{ minRate: 0 }">
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-[16px] font-bold tracking-wider text-black block">
                                Min. Hourly Rate
                            </label>  
                        </div> 
                        <div class="relative group border border-black p-4 bg-white">
                           <div class="relative flex items-center">
                               <input type="range" min="0" max="75" step="0.50" x-model="minRate" @input="currentPage = 1" class="w-full h-1.5 bg-gray-200 appearance-none cursor-pointer accent-black hover:accent-gray-800 transition-all">
                               <input type="hidden" :value="minRate">  
                            </div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px] font-bold text-black uppercase">$<span x-text="Number(minRate).toFixed(2)"></span><span>/hr</span></span>
                            <span class="text-[10px] font-bold text-black uppercase">$75.00/hr</span> 
                        </div>
                    </div> 

                    <div class="mb-6" x-data="{ salary: 0 }">
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-[16px] font-bold tracking-wider text-black block">
                                Salary Range
                            </label>
                        </div>
                        <div class="relative group border border-black p-4 bg-white">
                           <div class="relative flex items-center">
                               <input type="range" min="0" max="360000" step="5000" x-model="minSalary" @input="currentPage = 1" class="w-full h-1.5 bg-gray-200 appearance-none cursor-pointer accent-black hover:accent-gray-800 transition-all">
                               <input type="hidden" :value="minSalary">  
                            </div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px] font-bold text-black uppercase">
                                $<span x-text="Number(salary).toLocaleString()"></span>
                            </span>
                            <span class="text-[10px] font-bold text-black uppercase">$360,000</span> 
                        </div>
                    </div> 

                    <div class="mb-6"> 
                        <div class="flex justify-between items-center mb-3">
                            <label class="text-[16px] font-bold tracking-wider text-black uppercase">
                                Impact & Mission Focus
                            </label>
                            <button x-show="selectedMissions.length > 0" 
                               @click="selectedMissions = []"  
                               class="text-[10px] font-bold text-[#e15f55] hover:underline uppercase">
                               Clear All
                            </button>
                        </div>   
                        <div class="mb-4 flex flex-wrap gap-2 min-h-[20px]">
                            <template x-for="item in selectedMissions" :key="item">
                                <div class="flex items-center gap-2 bg-black text-white text-[10px] font-bold px-2 py-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,0.2)]">
                                   <span x-text="item"></span>
                                   <button type="button" @click="toggleMission(item)" class="hover:text-gray-400 font-bold ml-1">✕</button>
                                </div>
                            </template>
                            <div x-show="selectedMissions.length === 0" class="text-[11px] font-medium text-gray-400 italic">
                                     No focus areas selected...
                            </div>
                        </div>
                        <div class="border border-black bg-white p-4 max-h-[350px] overflow-y-auto custom-scrollbar shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <div class="space-y-3">
                               <?php
                               $focusAreas = [
                                   "Aging / Seniors", "Agriculture & Nutrition", "Alternative & Sustainable Energy", 
                                   "Animal-Related", "Arts, Culture & Humanities", "Association / Mutual & Membership Benefit / Union", 
                                   "Broadcast / Journalism", "Childcare / Preschool / After-school Care", "Civil Rights, Social Action & Advocacy", 
                                   "Community Improvement & Capacity Building", "Conservation / Environment Advocacy", "Crime & Legal-Related", 
                                   "Culture & Humanities", "Disability-Related", "Disaster Preparedness & Relief", 
                                   "Disease & Medical Disorder Related", "Education", "Employment", "Food, Agriculture & Nutrition", 
                                   "Foreign Affairs & National Security", "Government", "Health Care", "Housing & Shelter", 
                                   "Human Services", "International, Foreign Affairs & National Security", "Medical Research", 
                                   "Mental Health & Crisis Intervention", "Philanthropy, Voluntarism & Grantmaking Foundations", 
                                   "Public Safety, Disaster Preparedness & Relief", "Recreation & Sports", "Religion-Related", 
                                   "Research", "Science & Technology", "Social Action & Advocacy", "Veterans", 
                                   "Voluntarism & Grantmaking Foundations", "Voluntary Health Associations & Medical Disciplines", 
                                   "Youth Development", "Zoo", "Zoological Society", "Unknown / Other"
                                ];
                                foreach($focusAreas as $area):
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative flex items-center">
                                         <input type="checkbox" 
                                            value="<?= htmlspecialchars($area) ?>"
                                            :checked="selectedMissions.includes('<?= addslashes($area) ?>')"
                                            @change="toggleMission($event.target.value); currentPage = 1"
                                            class="peer w-5 h-5 border-2 border-black rounded-none appearance-none checked:bg-black cursor-pointer transition-all focus:ring-0">
                                        <svg class="absolute w-3.5 h-3.5 text-white pointer-events-none hidden peer-checked:block left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" 
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-[13px] font-semibold text-black group-hover:text-gray-500 transition-colors lowercase first-letter:uppercase">
                                        <?= $area ?>
                                    </span> 
                                </label>
                                <?php endforeach; ?>
                            </div>
                       </div>
                </div>

                <div class="mb-6">  
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-[16px] font-bold tracking-wider text-black uppercase">
                            Role Categories
                        </label>
                        <button type="button"
                            x-show="selectedRoles.length > 0"
                            @click="selectedRoles = []"
                            class="text-[10px] font-bold text-[#e15f55] hover:underline uppercase">
                            Clear All
                        </button>  
                    </div>
                    <div class="mb-4 flex flex-wrap gap-2 min-h-[20px]">
                       <template x-for="item in selectedRoles" :key="item">
                            <div class="flex items-center gap-2 bg-black text-white text-[10px] font-bold px-2 py-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,0.2)]">   
                             <span x-text="item"></span>
                                <button type="button" @click="toggleRole(item)" class="hover:text-gray-400 font-bold ml-1">✕</button>
                            </div>
                        </template>
                        <div x-show="selectedRoles.length === 0" class="text-[11px] font-medium text-gray-400 italic">
                                  No categories selected...
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" x-model="roleSearch" placeholder="Search roles..." 
                            class="w-full text-[12px] border border-black p-2 focus:ring-0 focus:outline-none italic">
                    </div>
                    <div class="border border-black bg-white p-4 max-h-[350px] overflow-y-auto custom-scrollbar shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <div class="space-y-3">
                            <?php
                            $roles = [
                                "Accounting / Finance", "Administrative / Clerical", "Advocacy / Lobbying", "Animal Care",
                                "Campaign Management / Canvassing / Field Organizer", "Child Care / After school / Counselor / Mentor",
                                "Childhood Development / Early Childhood Education", "Community Engagement", "Conservation",
                                "Consulting", "Creative / Art Production", "Customer Service", "Customer Service / Retail",
                                "Development / Fundraising", "Direct Service / Social Service", "Education / Teaching",
                                "Event Planning", "Executive / Senior Management", "Facilities & Warehouse Management / Equipment / Drivers",
                                "Food Service", "Health / Medical / Nutrition", "Home Health Aid / Senior Care",
                                "Horticulture / Groundskeeper", "Housing / Construction", "Human Resources / Recruiting",
                                "Journalism / Broadcasting", "Legal", "Library Science", "Marketing / Communications / Public Relations",
                                "Member / Membership Management", "Mental Health Services", "Operations / Business Management",
                                "Program / Project Management", "Public Policy / Administration", "Recreational / Camp Associates & Management",
                                "Research", "Sales / Business Development", "Social Work / Counseling", "Technology / Data Management",
                                "Training / Curriculum Development", "Transportation", "Volunteer Services", "Unknown / Other"
                            ];
                            $roles = array_unique($roles);
                            foreach($roles as $role): 
                                $cleanRole = htmlspecialchars($role);
                                $jsRole = addslashes($role);
                            ?>
                            <label class="flex items-center gap-3 cursor-pointer group" 
                                x-show="'<?= strtolower($jsRole) ?>'.includes(roleSearch.toLowerCase())">
                                <div class="relative flex items-center">
                                    <input type="checkbox"
                                        value="<?= $cleanRole ?>"
                                         :checked="selectedRoles.includes('<?= $jsRole ?>')"
                                         @change="toggleRole($event.target.value); currentPage = 1"
                                        class="peer w-5 h-5 border-2 border-black rounded-none appearance-none checked:bg-black cursor-pointer transition-all focus:ring-0">
                                    <svg class="absolute w-3.5 h-3.5 text-white pointer-events-none hidden peer-checked:block left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" 
                                           fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                           <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-[13px] font-semibold text-black group-hover:text-gray-500 transition-colors">
                                      <?= $role ?>
                                </span>
                                </label>
                                 <?php endforeach; ?>
                            </div>
                        </div>
                </div>
                </div>
            </div>
        </aside>

    <section class="flex-1 min-w-0">
    <template x-for="job in paginatedJobs" :key="job.id">
        <div class="bg-white rounded border border-slate-300 overflow-hidden mb-6 relative hover:shadow-lg transition-shadow">
            
            <!-- Featured Badge -->
            <template x-if="job.publish_type && (job.publish_type.toLowerCase() === 'featured')">
                <div class="absolute top-0 right-0 bg-teal-200 text-teal-800 text-xs font-bold px-2 py-1">
                    Featured
                </div>
            </template>

            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 font-serif mb-1" x-text="job.title"></h2>
                        <h3 class="text-base font-bold text-slate-900" x-text="job.company"></h3>
                        
                        <div class="mt-3 text-sm text-[#e15f55] space-y-1">
                            <p class="font-medium">Posted: <span x-text="job.posted"></span></p>
                            <p class="font-medium">Expires: <span x-text="job.expires"></span></p>
                        </div>
                    </div>
                    
                    <div class="bg-white border border-slate-200 p-2 rounded shadow-sm w-20 h-20 flex items-center justify-center">
                         <img :src="job.logo" :alt="job.company" class="max-w-full max-h-full object-contain"
                              @error="$el.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(job.company) + '&background=ffffff&color=54595f'">
                    </div>
                </div>

                <div class="mb-4 text-slate-800 text-[15px] leading-relaxed">
                    <p x-text="job.description.length > 200 ? job.description.substring(0, 200) + '...' : job.description"></p>
                </div>

                <ul class="space-y-2 text-slate-900 text-[15px]">
                    <li class="flex items-start gap-2">
                         <span class="font-bold text-lg leading-none">•</span> <span x-text="job.location"></span>
                    </li>
                    <li class="flex items-start gap-2">
                         <span class="font-bold text-lg leading-none">•</span> <span x-text="job.type"></span>
                    </li>
                    <li class="flex items-start gap-2">
                         <span class="font-bold text-lg leading-none">•</span> <span x-text="job.workplace"></span>
                    </li>
                    <li class="flex items-start gap-2" x-show="job.education">
                         <span class="font-bold text-lg leading-none">•</span> <span x-text="job.education"></span>
                    </li>
                    <li class="flex items-start gap-2" x-show="job.experience">
                         <span class="font-bold text-lg leading-none">•</span> <span x-text="job.experience + ' year(s) experience required'"></span>
                    </li>
                    <li class="flex items-start gap-2" x-show="job.mission_focus">
                         <span class="font-bold text-lg leading-none">•</span> 
                         <span>Mission focus areas: <span x-text="job.mission_focus"></span></span>
                    </li>
                    <li class="flex items-start gap-2" x-show="job.category">
                         <span class="font-bold text-lg leading-none">•</span> 
                         <span>Role categories: <span x-text="job.category"></span></span>
                    </li>
                </ul>

                <div class="mt-6 flex justify-between items-center">
                    <a :href="'job-details?id=' + job.id" class="bg-[#e15f55] text-white font-bold px-4 py-2 rounded text-sm hover:bg-white hover:text-[#e15f55] hover:border hover:border-[#e15f55] transition-colors shadow-sm">
                        View Details
                    </a>
                     <button class="text-[#e15f55] hover:text-red-700 font-medium flex items-center gap-1 text-sm">
                        <i class="fa-regular fa-star"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </template>

    <div x-show="filteredJobs.length === 0" class="text-center py-20 bg-white border-2 border-dashed rounded-xl">
        <p class="text-slate-400 font-medium text-lg">No jobs found matching your current filters.</p>
        <button  @click="resetFilters()" class="text-[#5b6bd5] font-bold mt-2 hover:underline">Reset all filters</button>
    </div>

    <div class="pt-8 flex items-center justify-center gap-2" x-show="totalPages > 1">
        <button @click="currentPage--" :disabled="currentPage === 1" 
                class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-[#5b6bd5] disabled:opacity-30">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        
        <div class="px-4 py-2 bg-[#5b6bd5] text-white font-bold rounded-xl" x-text="currentPage"></div>
        
        <button @click="currentPage++" :disabled="currentPage === totalPages" 
                class="px-4 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 font-bold disabled:opacity-30">
            Next <i class="fa-solid fa-chevron-right text-xs ml-2"></i>
        </button>
    </div>
</section>
    </div>
</main>
<footer class="w-full">
    <section class="bg-[#f2f2f2] py-4 border-b border-[#eeeeee]">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="text-center">
                <p class="text-[#333333] text-[15px]">
                    Need help? Email 
                    <a href="mailto:gm@mindwareinfotech.com" class="text-red-500 font-bold hover:underline break-all">
                        gm@mindwareinfotech.com
                    </a>.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-[#232323] py-12">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="flex flex-col items-center">
                <div class="mb-8">
                    <img width="127" height="70" src="/uploads/Mindware-infotech.png" class="h-auto w-32 brightness-0 invert" alt="Logo">
                </div>

                <nav class="mb-8">
                    <ul class="flex flex-wrap justify-center gap-x-6 gap-y-3">
                        <li><a href="aboutus" class="text-white text-sm font-medium hover:text-[#e15f55] transition">About us</a></li>
                        <li><a href="/../contact" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Contact us</a></li>
                        <li><a href="hiringInsightSignUp" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Subscribe</a></li>
                        <li><a href="/../terms" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Terms & Conditions</a></li>
                        <li><a href="/../privacy" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Privacy policy</a></li>
                        <li><a href="supports" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Support</a></li>
                        <li><a href="employers" class="text-white text-sm font-medium hover:text-[#e15f55] transition">Post a job</a></li>
                    </ul>
                </nav>

                <div class="flex justify-center mb-8">
                    <a href="https://www.linkedin.com/company/mindwareinfotech/" target="_blank" class="bg-[#444444] hover:bg-[#0077b5] transition-all p-3 rounded-full">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 310 310">
                            <path d="M72.16,99.73H9.927c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5H72.16c2.762,0,5-2.238,5-5V104.73 C77.16,101.969,74.922,99.73,72.16,99.73z"></path>
                            <path d="M41.066,0.341C18.422,0.341,0,18.743,0,41.362C0,63.991,18.422,82.4,41.066,82.4 c22.626,0,41.033-18.41,41.033-41.038C82.1,18.743,63.692,0.341,41.066,0.341z"></path>
                            <path d="M230.454,94.761c-24.995,0-43.472,10.745-54.679,22.954V104.73c0-2.761-2.238-5-5-5h-59.599 c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5h62.097c2.762,0,5-2.238,5-5v-98.918c0-33.333,9.054-46.319,32.29-46.319 c25.306,0,27.317,20.818,27.317,48.034v97.204c0,2.762,2.238,5,5,5H305c2.762,0,5-2.238,5-5V194.995 C310,145.43,300.549,94.761,230.454,94.761z"></path>
                        </svg>
                    </a>
                </div>

                <div class="text-[#7a7a7a] text-[13px] text-center">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>
            </div>
        </div>
    </section>
</footer>
<script>
function jobSearch() {
    return {
        rawJobs: <?= json_encode($jobs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,

        /* Filters */
        searchKeyword: '',
        roleSearch: '',
        selectedState: '',
        selectedWorkplaces: [],
        selectedMissions: [],
        selectedRoles: [],
        minSalary: 0,

        /* Pagination */
        currentPage: 1,
        itemsPerPage: 5,

        init() {
            // URL se parameters read karo
            const urlParams = new URLSearchParams(window.location.search);
            
            // Role parameter check karo
            const roleParam = urlParams.get('role');
            if (roleParam) {
                const roleName = this.convertSlugToName(roleParam);
                // Check karo ki ye role list mein hai
                const matchingRole = this.findMatchingRole(roleName);
                if (matchingRole) {
                    this.selectedRoles.push(matchingRole);
                }
            }
            
            // Mission parameter check karo
            const missionParam = urlParams.get('mission');
            if (missionParam) {
                const missionName = this.convertSlugToName(missionParam);
                const matchingMission = this.findMatchingMission(missionName);
                if (matchingMission) {
                    this.selectedMissions.push(matchingMission);
                }
            }
        },

        convertSlugToName(slug) {
            // Slug ko readable text mein convert: "child-care" -> "child care"
            return slug.replace(/-/g, ' ');
        },

        findMatchingRole(searchText) {
            const roles = [
                "Accounting / Finance", "Administrative / Clerical", "Advocacy / Lobbying", "Animal Care",
                "Campaign Management / Canvassing / Field Organizer", "Child Care / After school / Counselor / Mentor",
                "Childhood Development / Early Childhood Education", "Community Engagement", "Conservation",
                "Consulting", "Creative / Art Production", "Customer Service", "Customer Service / Retail",
                "Development / Fundraising", "Direct Service / Social Service", "Education / Teaching",
                "Event Planning", "Executive / Senior Management", "Facilities & Warehouse Management / Equipment / Drivers",
                "Food Service", "Health / Medical / Nutrition", "Home Health Aid / Senior Care",
                "Horticulture / Groundskeeper", "Housing / Construction", "Human Resources / Recruiting",
                "Journalism / Broadcasting", "Legal", "Library Science", "Marketing / Communications / Public Relations",
                "Member / Membership Management", "Mental Health Services", "Operations / Business Management",
                "Program / Project Management", "Public Policy / Administration", "Recreational / Camp Associates & Management",
                "Research", "Sales / Business Development", "Social Work / Counseling", "Technology / Data Management",
                "Training / Curriculum Development", "Transportation", "Volunteer Services", "Unknown / Other"
            ];

            // Partial match karo (case-insensitive)
            const search = searchText.toLowerCase();
            return roles.find(role => role.toLowerCase().includes(search));
        },

        findMatchingMission(searchText) {
            const missions = [
                "Aging / Seniors", "Agriculture & Nutrition", "Alternative & Sustainable Energy", 
                "Animal-Related", "Arts, Culture & Humanities", "Association / Mutual & Membership Benefit / Union", 
                "Broadcast / Journalism", "Childcare / Preschool / After-school Care", "Civil Rights, Social Action & Advocacy", 
                "Community Improvement & Capacity Building", "Conservation / Environment Advocacy", "Crime & Legal-Related", 
                "Culture & Humanities", "Disability-Related", "Disaster Preparedness & Relief", 
                "Disease & Medical Disorder Related", "Education", "Employment", "Food, Agriculture & Nutrition", 
                "Foreign Affairs & National Security", "Government", "Health Care", "Housing & Shelter", 
                "Human Services", "International, Foreign Affairs & National Security", "Medical Research", 
                "Mental Health & Crisis Intervention", "Philanthropy, Voluntarism & Grantmaking Foundations", 
                "Public Safety, Disaster Preparedness & Relief", "Recreation & Sports", "Religion-Related", 
                "Research", "Science & Technology", "Social Action & Advocacy", "Veterans", 
                "Voluntarism & Grantmaking Foundations", "Voluntary Health Associations & Medical Disciplines", 
                "Youth Development", "Zoo", "Zoological Society", "Unknown / Other"
            ];

            const search = searchText.toLowerCase();
            return missions.find(mission => mission.toLowerCase().includes(search));
        },

        get filteredJobs() {
            return this.rawJobs.filter(job => {

                const keyword = this.searchKeyword.toLowerCase();

                const matchesKeyword =
                    !keyword ||
                    job.title?.toLowerCase().includes(keyword) ||
                    job.description?.toLowerCase().includes(keyword);

                const matchesState =
                    !this.selectedState ||
                    job.location?.toLowerCase().includes(this.selectedState.toLowerCase());

                const matchesWorkplace =
                    this.selectedWorkplaces.length === 0 ||
                    this.selectedWorkplaces.includes(job.workplace);

                const matchesMission =
                    this.selectedMissions.length === 0 ||
                    (job.mission_focus && this.selectedMissions.some(m => job.mission_focus.includes(m)));

                const matchesRole =
                    this.selectedRoles.length === 0 ||
                    (job.category && this.selectedRoles.some(r => job.category.includes(r)));

                const salary =
                    parseInt(String(job.salary_min).replace(/[^0-9]/g, '')) || 0;

                const matchesSalary = salary >= this.minSalary;

                return (
                    matchesKeyword &&
                    matchesState &&
                    matchesWorkplace &&
                    matchesMission &&
                    matchesRole &&
                    matchesSalary
                );
            });
        },

        get paginatedJobs() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredJobs.slice(start, start + this.itemsPerPage);
        },

        get totalPages() {
            return Math.ceil(this.filteredJobs.length / this.itemsPerPage);
        },

        toggleMission(mission) {
            this.selectedMissions.includes(mission)
                ? this.selectedMissions = this.selectedMissions.filter(m => m !== mission)
                : this.selectedMissions.push(mission);
        },

        toggleRole(role) {
            this.selectedRoles.includes(role)
                ? this.selectedRoles = this.selectedRoles.filter(r => r !== role)
                : this.selectedRoles.push(role);
        },

        resetFilters() {
            this.searchKeyword = '';
            this.selectedState = '';
            this.selectedWorkplaces = [];
            this.selectedMissions = [];
            this.selectedRoles = [];
            this.minSalary = 0;
            this.currentPage = 1;
            
            // URL se bhi parameters remove karo
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
}
</script>
</body>
</html>
