<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($job['title'] ?? 'Job') ?> - Mindware Infotech</title>
    <meta name="description" content="<?= htmlspecialchars($job['description'] ?? 'Job Description') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($job['keywords'] ?? 'Job Keywords') ?>">
    <meta name="author" content="Mindware Infotech">
    <link href="/css/output.css" rel="stylesheet">
    <link rel="canonical" href="<?= htmlspecialchars($job['url'] ?? '#') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <?php
    // Prepare JobPosting Schema
    $schemaDescription = strip_tags($job['description'] ?? '');
    $schemaDatePosted = isset($job['created_at']) ? date('Y-m-d', strtotime($job['created_at'])) : date('Y-m-d');
    $schemaValidThrough = isset($job['expires_at']) ? date('Y-m-d', strtotime($job['expires_at'])) : date('Y-m-d', strtotime('+30 days'));
    
    // Map employment type
    $empTypeMap = [
        'full_time' => 'FULL_TIME',
        'part_time' => 'PART_TIME',
        'contract' => 'CONTRACTOR',
        'temporary' => 'TEMPORARY',
        'internship' => 'INTERN',
        'volunteer' => 'VOLUNTEER',
        'per_diem' => 'PER_DIEM',
        'other' => 'OTHER'
    ];
    $schemaEmpType = $empTypeMap[$job['employment_type'] ?? 'full_time'] ?? 'FULL_TIME';
    
    $schemaOrgName = $job['company_name'] ?? 'Mindware Infotech';
    $schemaOrgLogo = $job['company_logo'] ?? '';
    
    // Location
    $schemaLocation = [
        '@type' => 'Place',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $job['city'] ?? '',
            'addressRegion' => $job['state'] ?? '',
            'addressCountry' => $job['country'] ?? 'IN'
        ]
    ];
    
    // Salary
    $schemaSalary = null;
    if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
        $schemaSalary = [
            '@type' => 'MonetaryAmount',
            'currency' => $job['currency'] ?? 'INR',
            'value' => [
                '@type' => 'QuantitativeValue',
                'minValue' => $job['salary_min'] ?? 0,
                'maxValue' => $job['salary_max'] ?? 0,
                'unitText' => 'MONTH' // Assuming monthly, adjust if needed
            ]
        ];
    }
    
    $jobSchema = [
        '@context' => 'https://schema.org/',
        '@type' => 'JobPosting',
        'title' => $job['title'] ?? '',
        'description' => $schemaDescription,
        'identifier' => [
            '@type' => 'PropertyValue',
            'name' => $schemaOrgName,
            'value' => $job['id'] ?? ''
        ],
        'datePosted' => $schemaDatePosted,
        'validThrough' => $schemaValidThrough,
        'employmentType' => $schemaEmpType,
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => $schemaOrgName,
            'logo' => $schemaOrgLogo
        ],
        'jobLocation' => $schemaLocation
    ];
    
    if ($schemaSalary) {
        $jobSchema['baseSalary'] = $schemaSalary;
    }
    ?>
    <script type="application/ld+json">
        <?= json_encode($jobSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-6 {
            display: -webkit-box;
            -webkit-line-clamp: 6;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .map-container {
            height: 200px;
            width: 100%;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php $base = $base ?? '/'; require __DIR__ . '/../../include/header.php'; ?>
    <div x-data="jobDetail()" x-cloak>

        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Job Header Section -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <!-- Top Row: Timestamp -->
                <div class="mb-4">
                    <span class="text-sm text-gray-500">
                        <?php 
                        $createdAt = $job['created_at'] ?? date('Y-m-d H:i:s');
                        $timeDiff = time() - strtotime($createdAt);
                        if ($timeDiff < 60) {
                            echo 'Just now';
                        } elseif ($timeDiff < 3600) {
                            echo floor($timeDiff / 60) . ' min ago';
                        } elseif ($timeDiff < 86400) {
                            echo floor($timeDiff / 3600) . ' hour' . (floor($timeDiff / 3600) > 1 ? 's' : '') . ' ago';
                        } else {
                            echo floor($timeDiff / 86400) . ' day' . (floor($timeDiff / 86400) > 1 ? 's' : '') . ' ago';
                        }
                        ?>
                    </span>
                </div>

                <!-- Job Title and Company -->
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                    <div class="flex items-start gap-4 flex-1">
                    <?php if (!empty($job['company_logo'])): ?>
                    <img src="<?= htmlspecialchars($job['company_logo']) ?>" 
                         alt="<?= htmlspecialchars($job['company_name'] ?? 'Company') ?>"
                         class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold text-xl">
                        <?= strtoupper(substr($job['company_name'] ?? 'C', 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    <div class="flex-1">
                            <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($job['title'] ?? 'Job Title Not Available') ?></h1>
                            <p class="text-lg text-gray-700 font-medium"><?= htmlspecialchars($job['company_name'] ?? 'Company Name Not Available') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="bookmarkJob()" 
                                :class="job.is_bookmarked ? 'text-gray-800' : 'text-gray-400'"
                                class="p-2 hover:bg-gray-100 rounded transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"></path>
                            </svg>
                        </button>
                    <button x-show="!job.has_applied" 
                            @click="showApplyModal = true" 
                                class="px-6 py-3 bg-gray-800 text-white font-semibold rounded-lg border border-gray-800 hover:bg-gray-900 hover:border-gray-900 transition whitespace-nowrap">
                            Apply Now
                    </button>
                    <button x-show="job.has_applied" 
                            disabled
                                class="px-6 py-3 bg-gray-200 text-gray-600 font-semibold rounded-lg cursor-not-allowed whitespace-nowrap">
                        ✓ Already Applied
                    </button>
                    </div>
                </div>

                <!-- Job Attributes Bar -->
                <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-gray-200">
                    <?php if (!empty($job['category'])): ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['category']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?></span>
                    </div>
                    <?php if (($job['salary_min'] ?? 0) > 0 || ($job['salary_max'] ?? 0) > 0): ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">
                            <?php 
                            $currency = $job['currency'] ?? 'INR';
                            $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '₹'));
                            if (($job['salary_min'] ?? 0) > 0 && ($job['salary_max'] ?? 0) > 0) {
                                echo $symbol . number_format($job['salary_min']) . '-' . $symbol . number_format($job['salary_max']);
                            } elseif (($job['salary_min'] ?? 0) > 0) {
                                echo $symbol . number_format($job['salary_min']);
                            }
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['location_display'] ?? 'Location not specified') ?></span>
                    </div>
                </div>
            </div>
                            

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Job Description -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Job Description</h2>
                        <div class="text-gray-700 leading-relaxed">
                            <?php 
                            $description = $job['description'] ?? 'No description available';
                            $description = trim((string) $description);
                            $extractedResponsibilities = [];

                            if ($description !== '' && strip_tags($description) !== $description) {
                                if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $description, $matches)) {
                                    foreach ($matches[1] as $li) {
                                        $text = trim(strip_tags($li));
                                        if ($text !== '') {
                                            $extractedResponsibilities[] = $text;
                                        }
                                    }
                                }
                                echo '<div class="ql-editor prose max-w-none" style="padding: 0;">' . $description . '</div>';
                            } else {
                                $description = preg_replace('/[ \t]+/', ' ', $description);
                                $description = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $description);
                                $description = trim($description);

                                $desc = $description;
                                $desc = preg_replace('/([a-z\)\]\d])(?=[A-Z])/', "$1\n", $desc);
                                $lines = preg_split('/\r\n|\r|\n/', $desc);

                                $pendingList = [];
                                $html = '';

                                $currentSection = null;
                                $sectionHeadings = [
                                    'about the role' => 'about',
                                    'job summary' => 'summary',
                                    'key responsibilities' => 'responsibilities',
                                    'required qualifications' => 'qualifications',
                                    'technical skills' => 'technical',
                                    'what we offer' => 'offer',
                                ];

                                foreach ($lines as $line) {
                                    $t = trim($line);
                                    if ($t === '') {
                                        continue;
                                    }

                                    $lower = mb_strtolower($t);
                                    if (isset($sectionHeadings[$lower])) {
                                        if (!empty($pendingList)) {
                                            $html .= '<ul class="list-disc list-inside space-y-1">';
                                            foreach ($pendingList as $li) {
                                                $html .= '<li>' . $li . '</li>';
                                            }
                                            $html .= '</ul>';
                                            $pendingList = [];
                                        }
                                        $currentSection = $sectionHeadings[$lower];
                                        $html .= '<h3 class="text-lg font-semibold text-gray-900 mt-4 mb-2">' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</h3>';
                                        continue;
                                    }

                                    if (preg_match('/^[\-\*•]\s+(.*)$/u', $t, $m)) {
                                        $pendingList[] = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                                        if ($currentSection === 'responsibilities') {
                                            $extractedResponsibilities[] = $m[1];
                                        }
                                        continue;
                                    }

                                    if (preg_match('/^\d+[\.)]\s+(.*)$/', $t, $m)) {
                                        $pendingList[] = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                                        if ($currentSection === 'responsibilities') {
                                            $extractedResponsibilities[] = $m[1];
                                        }
                                        continue;
                                    }

                                    if (in_array($currentSection, ['responsibilities', 'qualifications', 'technical', 'offer'], true)
                                        && mb_strlen($t) <= 200
                                        && !preg_match('/[\.?!]$/', $t)
                                    ) {
                                        $pendingList[] = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
                                        if ($currentSection === 'responsibilities') {
                                            $extractedResponsibilities[] = $t;
                                        }
                                        continue;
                                    }

                                    if (!empty($pendingList)) {
                                        $html .= '<ul class="list-disc list-inside space-y-1">';
                                        foreach ($pendingList as $li) {
                                            $html .= '<li>' . $li . '</li>';
                                        }
                                        $html .= '</ul>';
                                        $pendingList = [];
                                    }

                                    $html .= '<p class="mb-2">' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</p>';
                                }

                                if (!empty($pendingList)) {
                                    $html .= '<ul class="list-disc list-inside space-y-1">';
                                    foreach ($pendingList as $li) {
                                        $html .= '<li>' . $li . '</li>';
                                    }
                                    $html .= '</ul>';
                                }

                                echo $html;
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Key Responsibilities -->
                    <?php
                    $responsibilities = [];
                    if (!empty($extractedResponsibilities)) {
                        $responsibilities = array_slice($extractedResponsibilities, 0, 6);
                    }
                    ?>
                    <?php if (!empty($responsibilities)): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Key Responsibilities</h2>
                        <ul class="space-y-3">
                            <?php foreach ($responsibilities as $resp): ?>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700"><?= htmlspecialchars(strip_tags($resp)) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Professional Skills -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Professional Skills</h2>
                        <ul class="space-y-3">
                            <?php if (!empty($job['skills']) && is_array($job['skills'])): ?>
                                <?php foreach (array_slice($job['skills'], 0, 5) as $skill): ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    <span class="text-gray-700"><?= htmlspecialchars($skill['name'] ?? '') ?></span>
                                </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700">Strong problem-solving and analytical skills</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($job['employment_type_display']) || !empty($job['category'])): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-3">Tags:</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php if (!empty($job['employment_type_display'])): ?>
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-sm font-medium">
                                <?= htmlspecialchars($job['employment_type_display']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($job['category'])): ?>
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-sm font-medium">
                                <?= htmlspecialchars($job['category']) ?>
                            </span>
                            <?php endif; ?>
                            <?php 
                            $locationParts = explode(',', $job['location_display'] ?? '');
                            if (!empty($locationParts[0])): 
                            ?>
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-sm font-medium">
                                <?= htmlspecialchars(trim($locationParts[0])) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Share Job -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-3">Share Job</h3>
                        <div class="flex items-center gap-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank"
                               class="w-10 h-10 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                                </svg>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($job['title'] ?? 'Job') ?>" 
                               target="_blank"
                               class="w-10 h-10 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"></path>
                                </svg>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank"
                               class="w-10 h-10 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Job Overview</h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Job Title</div>
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['title'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Job Type</div>
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?></div>
                                </div>
                            </div>
                            <?php if (!empty($job['category'])): ?>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Category</div>
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['category']) ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($job['min_experience']) || !empty($job['max_experience'])): ?>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Experience</div>
                                    <div class="font-semibold text-gray-900">
                                        <?php 
                                        if (!empty($job['min_experience']) && !empty($job['max_experience'])) {
                                            echo $job['min_experience'] . ' - ' . $job['max_experience'] . ' years';
                                        } elseif (!empty($job['min_experience'])) {
                                            echo $job['min_experience'] . '+ years';
                                        } elseif (!empty($job['max_experience'])) {
                                            echo 'Up to ' . $job['max_experience'] . ' years';
                                        } else {
                                            echo 'Not specified';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($job['required_education']) || !empty($job['education_level'])): ?>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v9"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v5.55"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Education</div>
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['required_education'] ?? $job['education_level'] ?? 'Not specified') ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (($job['salary_min'] ?? 0) > 0 || ($job['salary_max'] ?? 0) > 0): ?>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-600">Offered Salary</div>
                                    <div class="font-semibold text-gray-900">
                                        <?php 
                                        $currency = $job['currency'] ?? 'INR';
                                        $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '₹'));
                                        if (($job['salary_min'] ?? 0) > 0 && ($job['salary_max'] ?? 0) > 0) {
                                            echo $symbol . number_format($job['salary_min']) . '-' . $symbol . number_format($job['salary_max']);
                                        } elseif (($job['salary_min'] ?? 0) > 0) {
                                            echo $symbol . number_format($job['salary_min']);
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-sm text-gray-600 mb-1">Location</div>
                                    <div class="font-semibold text-gray-900 mb-3"><?= htmlspecialchars($mapLocation['address'] ?? $job['location_display'] ?? 'Location not specified') ?></div>
                                    <?php if (!empty($mapLocation) && !empty($mapLocation['address'])): ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                                        <div id="job-location-map" class="map-container"></div>
                                        <div class="bg-gray-50 px-3 py-2 text-xs text-gray-500 flex items-center justify-between border-t border-gray-200">
                                            <a href="https://www.openstreetmap.org/copyright" target="_blank" class="text-gray-600 hover:text-gray-800 hover:underline">
                                                © OpenStreetMap contributors
                                            </a>
                                            <a href="https://www.openstreetmap.org/search?query=<?= urlencode($mapLocation['address']) ?>" target="_blank" class="text-gray-600 hover:text-gray-800 hover:underline">
                                                Report a problem
                                            </a>
                                        </div>
                                    </div>
                                    <script>
                                        // Initialize map with exact location
                                        document.addEventListener('DOMContentLoaded', function() {
                                            <?php if (!empty($mapLocation['latitude']) && !empty($mapLocation['longitude'])): ?>
                                            const map = L.map('job-location-map').setView([<?= $mapLocation['latitude'] ?>, <?= $mapLocation['longitude'] ?>], 15);
                                            
                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '© OpenStreetMap contributors',
                                                maxZoom: 19
                                            }).addTo(map);
                                            
                                            // Add marker for exact location
                                            const marker = L.marker([<?= $mapLocation['latitude'] ?>, <?= $mapLocation['longitude'] ?>]).addTo(map);
                                            marker.bindPopup('<?= htmlspecialchars($mapLocation['address'], ENT_QUOTES) ?>').openPopup();
                                            <?php else: ?>
                                            // Fallback: Show map centered on address (will be geocoded)
                                            const map = L.map('job-location-map').setView([20.5937, 78.9629], 5); // Default to India center
                                            
                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '© OpenStreetMap contributors',
                                                maxZoom: 19
                                            }).addTo(map);
                                            
                                            // Try to geocode and show location
                                            fetch('https://nominatim.openstreetmap.org/search?q=<?= urlencode($mapLocation['address']) ?>&format=json&limit=1', {
                                                headers: {
                                                    'User-Agent': 'MindwareInfotech/1.0'
                                                }
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data && data.length > 0) {
                                                    const lat = parseFloat(data[0].lat);
                                                    const lon = parseFloat(data[0].lon);
                                                    map.setView([lat, lon], 15);
                                                    const marker = L.marker([lat, lon]).addTo(map);
                                                    marker.bindPopup('<?= htmlspecialchars($mapLocation['address'], ENT_QUOTES) ?>').openPopup();
                                                }
                                            })
                                            .catch(err => console.error('Geocoding error:', err));
                                            <?php endif; ?>
                                        });
                                    </script>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">For Interview &amp; Details</h3>
                        <div class="space-y-2 text-gray-700 text-sm">
                            <p>
                                <span class="font-semibold">Company:</span>
                                <span class="ml-1"><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></span>
                            </p>
                            <?php if (!empty($job['company_website'])): ?>
                            <p>
                                <span class="font-semibold">Website:</span>
                                <a href="<?= htmlspecialchars($job['company_website']) ?>" target="_blank" class="ml-1 text-blue-600 hover:underline">
                                    <?= htmlspecialchars($job['company_website']) ?>
                                </a>
                            </p>
                            <?php endif; ?>
                            <p>
                                <span class="font-semibold">Location:</span>
                                <span class="ml-1"><?= htmlspecialchars($job['location_display'] ?? 'Location not specified') ?></span>
                            </p>
                        </div>
                        <button type="button"
                                @click="reportJob()"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-sm text-red-700 hover:bg-red-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18m0-16h13l-4 4 4 4H3" />
                            </svg>
                            <span>Report this job</span>
                        </button>
                    </div>

                    <div id="candidate-contact-card" class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Send Us Message</h3>
                        <form @submit.prevent="sendMessage()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                                <input type="text" x-model="messageForm.full_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                                       placeholder="Your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" x-model="messageForm.email" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                                       placeholder="your.email@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" x-model="messageForm.phone"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                                       placeholder="+1 234 567 8900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                                <textarea x-model="messageForm.message" rows="4" required
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                                          placeholder="Write your message here..."></textarea>
                            </div>
                            <button type="submit" 
                                    :disabled="isSendingMessage"
                                    class="w-full px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg border border-gray-800 hover:bg-gray-900 hover:border-gray-900 disabled:opacity-50 transition">
                                <span x-show="!isSendingMessage">Send Message</span>
                                <span x-show="isSendingMessage">Sending...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Related Jobs Section with Interview Blogs -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Similar Jobs - Left Side (2/3 width) -->
            <?php if (!empty($relatedJobs) && is_array($relatedJobs) && count($relatedJobs) > 0): ?>
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h4 class="text-xl font-semibold mb-4">Similar jobs that you might be interested in</h4>
                        <div class="space-y-2">
                            <?php foreach (array_slice($relatedJobs, 0, 5) as $relatedJob): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-all bg-white shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-3">
                                    <!-- Company Logo -->
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($relatedJob['company_logo'])): ?>
                                        <img src="<?= htmlspecialchars($relatedJob['company_logo']) ?>" 
                                             alt="<?= htmlspecialchars($relatedJob['company_name'] ?? 'Company') ?>"
                                             class="w-14 h-14 rounded-lg object-cover border border-gray-200">
                                        <?php else: ?>
                                        <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 font-semibold text-base border border-gray-200">
                                            <?= strtoupper(substr($relatedJob['company_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Job Title + Meta Info -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 mb-1 text-base leading-snug">
                            <a href="/candidate/jobs/<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>" 
                                               class="hover:text-gray-700">
                                                <?= htmlspecialchars($relatedJob['company_name'] ?? 'Company') ?> - <?= htmlspecialchars($relatedJob['title'] ?? 'Job Title') ?>
                            </a>
                        </h3>
                                        
                                        <!-- Experience, Location, Posted Date -->
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-600 mb-1">
                                            <?php 
                                            $minExp = $relatedJob['min_experience'] ?? 0;
                                            $maxExp = $relatedJob['max_experience'] ?? 0;
                                            $expText = '';
                                            if ($minExp > 0 || $maxExp > 0):
                                                if ($minExp > 0 && $maxExp > 0) {
                                                    $expText = $minExp . ' - ' . $maxExp . ' yrs';
                                                } elseif ($minExp > 0) {
                                                    $expText = $minExp . '+ yrs';
                                                } elseif ($maxExp > 0) {
                                                    $expText = 'Up to ' . $maxExp . ' yrs';
                                                }
                                                echo $expText;
                                            endif;
                                            ?>
                                            <?php if (!empty($expText) && !empty($relatedJob['location_display'])): ?>
                                            <span class="text-gray-400">•</span>
                                            <?php endif; ?>
                                            <?php if (!empty($relatedJob['location_display'])): ?>
                                            <span><?= htmlspecialchars(trim(explode(',', $relatedJob['location_display'])[0])) ?></span>
                                            <?php endif; ?>
                                            <span class="text-gray-400">•</span>
                                            <span>Posted <?php 
                                                $createdAt = $relatedJob['created_at'] ?? date('Y-m-d H:i:s');
                                                $timeDiff = time() - strtotime($createdAt);
                                                if ($timeDiff < 86400) {
                                                    $hours = floor($timeDiff / 3600);
                                                    echo $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                                                } elseif ($timeDiff < 604800) {
                                                    $days = floor($timeDiff / 86400);
                                                    echo $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                                                } elseif ($timeDiff < 2592000) {
                                                    $weeks = floor($timeDiff / 604800);
                                                    echo $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                                                } else {
                                                    $months = floor($timeDiff / 2592000);
                                                    echo $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
                                                }
                                            ?></span>
                                        </div>
                                        
                                        <!-- Skills/Keywords -->
                                        <?php 
                                        $skills = [];
                                        if (!empty($relatedJob['skills']) && is_array($relatedJob['skills']) && count($relatedJob['skills']) > 0) {
                                            $skills = array_slice(array_map(fn($s) => $s['name'] ?? '', $relatedJob['skills']), 0, 3);
                                        } elseif (!empty($relatedJob['category'])) {
                                            $skills = [$relatedJob['category']];
                                        }
                                        if (!empty($skills)):
                                        ?>
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-600 mb-2 sm:mb-0">
                                            <?php foreach ($skills as $index => $skill): ?>
                                            <span><?= htmlspecialchars($skill) ?></span>
                                            <?php if ($index < count($skills) - 1): ?>
                                            <span class="text-gray-400">•</span>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Action Buttons: Mobile - Full Width, Desktop - Inline -->
                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 mt-2 sm:mt-0 sm:hidden">
                                            <a href="/candidate/jobs/<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>" 
                                               class="flex-1 sm:flex-none px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 text-sm font-semibold transition shadow-sm text-center">
                                                Job Details
                                            </a>
                                            <button @click.stop="bookmarkRelatedJob('<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>')" 
                                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 text-sm font-medium transition shadow-sm"
                                                    :class="relatedJobSaved['<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>'] ? 'bg-gray-100 border-gray-400' : ''">
                                                <svg class="w-5 h-5" :class="relatedJobSaved['<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>'] ? 'fill-current text-gray-700' : 'stroke-current text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-700">SAVE</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons: Desktop - Right Side -->
                                    <div class="hidden sm:flex flex-shrink-0 items-center gap-3">
                                        <a href="/candidate/jobs/<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>" 
                                           class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 text-sm font-semibold transition shadow-sm whitespace-nowrap">
                                            Job Details
                                        </a>
                                        <button @click.stop="bookmarkRelatedJob('<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>')" 
                                                class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 text-sm font-medium transition shadow-sm whitespace-nowrap"
                                                :class="relatedJobSaved['<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>'] ? 'bg-gray-100 border-gray-400' : ''">
                                            <svg class="w-5 h-5" :class="relatedJobSaved['<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>'] ? 'fill-current text-gray-700' : 'stroke-current text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">SAVE</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Right Sidebar - Multiple Cards (1/3 width) -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Jobs by Location -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jobs by location</h3>
                        <ul class="space-y-2">
                            <?php 
                            $popularLocations = [
                                'Bangalore', 'Mumbai', 'Delhi NCR', 'Noida', 
                                'Gurgaon/Gurugram', 'Hyderabad', 'Chennai', 
                                'Coimbatore', 'Pune', 'Kolkata'
                            ];
                            foreach ($popularLocations as $location): 
                            ?>
                            <li>
                                <a href="/candidate/jobs?location=<?= urlencode($location) ?>" 
                                   class="text-sm text-gray-700 hover:text-gray-900 transition flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Jobs in <?= htmlspecialchars($location) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Apply on the go -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-start gap-6">
                            <!-- Left Section: Text and App Store Buttons -->
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Apply on the go!</h3>
                                <p class="text-sm text-gray-600 mb-4">Download the hirist app to <br>apply for jobs anywhere, anytime</p>
                                <div class="flex items-center gap-2">
                                    <!-- App Store Button -->
                                    <a href="#" class="inline-block">
                                        <div class="min-h-[40px] bg-black rounded-md flex items-center px-3 py-2 hover:opacity-90 transition">
                                            <svg class="w-5 h-5 text-white mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                            </svg>
                                            <div class="flex flex-col items-start justify-center">
                                                <span class="text-white text-[10px] leading-none">Download on</span>
                                                <span class="text-white text-xs font-semibold leading-tight">App Store</span>
                                            </div>
                                        </div>
                                    </a>
                                    <!-- Google Play Button -->
                                    <a href="#" class="inline-block">
                                        <div class="min-h-[40px] bg-black rounded-md flex items-center px-3 py-2 hover:opacity-90 transition">
                                            <svg class="w-5 h-5 mr-2 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                                                <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5Z" fill="#00D9FF"/>
                                                <path d="M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12Z" fill="#FFCE00"/>
                                                <path d="M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.19,15.12L14.54,12.85L17.19,10.81L20.16,10.81Z" fill="#00F076"/>
                                                <path d="M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" fill="#FF3A44"/>
                                            </svg>
                                            <div class="flex flex-col items-start justify-center">
                                                <span class="text-white text-[10px] leading-none">Get it on</span>
                                                <span class="text-white text-xs font-semibold leading-tight">Google Play</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Right Section: QR Code -->
                            <div class="flex-shrink-0 text-center">
                                <div class="w-24 h-24 bg-white border-2 border-gray-300 rounded-lg mx-auto mb-2 p-1.5 flex items-center justify-center">
                                    <img src="/uploads/qr.jpeg" alt="QR Code" class="w-full h-full object-contain rounded">
                                </div>
                                <p class="text-xs text-gray-500">Scan to Download</p>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Blogs -->
                    <?php 
                    $displayBlogs = !empty($interviewBlogs) && is_array($interviewBlogs) ? $interviewBlogs : [];
                    $blogCount = min(count($displayBlogs), 5);
                    ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6 sticky top-20">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Interview Questions for you</h3>
                            <a href="/blog" class="text-sm font-medium text-gray-600 hover:text-gray-800">View All</a>
                        </div>
                        <?php if ($blogCount > 0): ?>
                        <div x-data="blogSlider()" class="relative">
                            <div class="overflow-hidden">
                                <div class="flex transition-transform duration-300 ease-in-out" 
                                     :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                                    <?php foreach (array_slice($displayBlogs, 0, $blogCount) as $blog): ?>
                                    <div class="min-w-full">
                                        <a href="/blog/<?= htmlspecialchars($blog['slug'] ?? '') ?>" 
                                           class="block border border-gray-200 rounded-lg overflow-hidden hover:border-gray-300 transition bg-white">
                                            <?php if (!empty($blog['featured_image'])): ?>
                                            <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                                                 alt="<?= htmlspecialchars($blog['title'] ?? '') ?>"
                                                 class="w-full h-48 object-cover">
                                            <?php else: ?>
                                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                            <?php endif; ?>
                                            <div class="p-4">
                                                <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-sm leading-snug">
                                                    <?= htmlspecialchars($blog['title'] ?? '') ?>
                                                </h4>
                                            </div>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($blogCount > 1): ?>
                            <div class="flex justify-center gap-2 mt-4">
                                <?php for ($i = 0; $i < $blogCount; $i++): ?>
                                <button @click="currentSlide = <?= $i ?>"
                                        :class="currentSlide === <?= $i ?> ? 'bg-blue-600' : 'bg-gray-300'"
                                        class="w-2 h-2 rounded-full transition"></button>
                                <?php endfor; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <script>
                            function blogSlider() {
                                return {
                                    currentSlide: 0,
                                    totalSlides: <?= max($blogCount, 1) ?>,
                                    prevSlide() {
                                        this.currentSlide = (this.currentSlide > 0) ? this.currentSlide - 1 : this.totalSlides - 1;
                                    },
                                    nextSlide() {
                                        this.currentSlide = (this.currentSlide < this.totalSlides - 1) ? this.currentSlide + 1 : 0;
                                    }
                                }
                            }
                        </script>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-500">No interview questions available at the moment.</p>
                            <a href="/blog" class="inline-block mt-3 text-sm text-gray-600 hover:text-gray-800 underline">Browse all blogs</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apply Modal -->
        <div x-show="showApplyModal" 
             x-transition
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showApplyModal = false">
            <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-semibold mb-6">Apply for This Job</h2>
                
                <form @submit.prevent="submitApplication()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resume</label>
                        <select x-model="applicationData.resume_url" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                            <option value="<?= $candidate->attributes['resume_url'] ?? '' ?>">
                                Use Profile Resume
                            </option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Or upload a new one</p>
                        <input type="file" @change="uploadResume($event)" 
                               accept=".pdf,.doc,.docx" 
                               class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Letter (Optional)</label>
                        <textarea x-model="applicationData.cover_letter" rows="6" 
                                  placeholder="Tell the employer why you're a great fit..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Salary</label>
                        <input type="number" x-model="applicationData.expected_salary" 
                               placeholder="Leave blank to use profile default"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showApplyModal = false" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                            Cancel
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg border border-gray-800 hover:bg-gray-900 hover:border-gray-900 disabled:opacity-50 font-semibold transition">
                            <span x-show="!isSubmitting">Submit Application</span>
                            <span x-show="isSubmitting">Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function jobDetail() {
            return {
                showApplyModal: false,
                isSubmitting: false,
                isSendingMessage: false,
                job: {
                    id: <?= $job['id'] ?? 0 ?>,
                    slug: <?= json_encode($job['slug'] ?? '') ?>,
                    employer_id: <?= $job['employer_id'] ?? 0 ?>,
                    title: <?= json_encode($job['title'] ?? '') ?>,
                    is_bookmarked: <?= ($job['is_bookmarked'] ?? false) ? 'true' : 'false' ?>,
                    has_applied: <?= ($job['has_applied'] ?? false) ? 'true' : 'false' ?>
                },
                applicationData: {
                    resume_url: '<?= $candidate->attributes['resume_url'] ?? '' ?>',
                    cover_letter: '',
                    expected_salary: <?= $candidate->attributes['expected_salary_min'] ?? 'null' ?>
                },
                messageForm: {
                    full_name: '<?= htmlspecialchars($candidate->attributes['full_name'] ?? '') ?>',
                    email: '<?= htmlspecialchars($candidate->user()->attributes['email'] ?? '') ?>',
                    phone: '<?= htmlspecialchars($candidate->attributes['mobile'] ?? '') ?>',
                    message: ''
                },
                relatedJobSaved: {
                    <?php if (!empty($relatedJobs)): ?>
                    <?php 
                    $relatedJobsList = array_slice($relatedJobs, 0, 5);
                    $lastIndex = count($relatedJobsList) - 1;
                    foreach ($relatedJobsList as $index => $relatedJob): ?>
                    '<?= htmlspecialchars($relatedJob['slug'] ?? $relatedJob['id'] ?? '') ?>': <?= ($relatedJob['is_bookmarked'] ?? false) ? 'true' : 'false' ?><?= $index < $lastIndex ? ',' : '' ?>

                    <?php endforeach; ?>
                    <?php endif; ?>
                    },
                async bookmarkRelatedJob(jobSlug) {
                    try {
                        if (!this.relatedJobSaved[jobSlug]) {
                            this.relatedJobSaved[jobSlug] = false;
                        }
                        const response = await fetch(`/candidate/jobs/${jobSlug}/bookmark`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.relatedJobSaved[jobSlug] = data.bookmarked;
                        }
                    } catch (error) {
                        console.error('Bookmark error:', error);
                    }
                },
                async bookmarkJob() {
                    try {
                        const jobSlug = this.job.slug || this.job.id;
                        const response = await fetch(`/candidate/jobs/${jobSlug}/bookmark`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.job.is_bookmarked = data.bookmarked;
                        }
                    } catch (error) {
                        console.error('Bookmark error:', error);
                    }
                },
                async submitApplication() {
                    this.isSubmitting = true;
                    try {
                        const jobSlug = this.job.slug || this.job.id;
                        const response = await fetch(`/candidate/jobs/${jobSlug}/apply`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(this.applicationData)
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.job.has_applied = true;
                            this.showApplyModal = false;
                            alert('Application submitted successfully!');
                        } else {
                            alert(data.error || 'Failed to submit application');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                async uploadResume(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', 'resume');

                    try {
                        const response = await fetch('/candidate/profile/upload', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.applicationData.resume_url = data.url;
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                    }
                },
                async sendMessage() {
                    this.isSendingMessage = true;
                    try {
                        const response = await fetch('/candidate/chat/start', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                employer_id: this.job.employer_id,
                                job_id: this.job.id,
                                initial_message: `Name: ${this.messageForm.full_name}\nEmail: ${this.messageForm.email}\nPhone: ${this.messageForm.phone}\n\nMessage:\n${this.messageForm.message}`
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            alert('Message sent successfully!');
                            this.messageForm = { full_name: '', email: '', phone: '', message: '' };
                        } else {
                            if (data.upgrade_url) {
                                const goPremium = confirm('Direct chat/report is a premium feature. Upgrade now?');
                                if (goPremium) {
                                    window.location.href = data.upgrade_url;
                                }
                            } else {
                                alert('Error: ' + (data.error || 'Failed to send message'));
                            }
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    } finally {
                        this.isSendingMessage = false;
                    }
                },
                reportJob() {
                    const reason = prompt('Please tell us why you are reporting this job');
                    if (!reason) {
                        return;
                    }
                    const title = this.job.title || '';
                    const headerParts = [];
                    if (title) {
                        headerParts.push(`Job: ${title}`);
                    }
                    if (this.job.id) {
                        headerParts.push(`ID: ${this.job.id}`);
                    }
                    const header = headerParts.length ? headerParts.join(' | ') + '\n\n' : '';
                    this.messageForm.message = `${header}Report reason:\n${reason}`;
                    const card = document.getElementById('candidate-contact-card');
                    if (card && typeof card.scrollIntoView === 'function') {
                        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    this.sendMessage();
                },
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            }
        }
    </script>
       <?php
require __DIR__ . '/../../include/footer.php';
?>
</body>
</html>
