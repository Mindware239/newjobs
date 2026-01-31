<?php 
/**
 * @var string $title
 * @var array $application
 * @var array|null $candidate
 * @var \App\Models\Employer $employer
 */
?>

<div class="mb-6">
    <a href="/employer/applications" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2 mb-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Applications
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Application Details</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Match Analysis Card - Prominent at Top -->
        <div id="match-analysis" class="bg-gray-50 rounded-lg shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Match Analysis</h2>
                    <p class="text-gray-600 text-sm">AI-powered candidate-job matching analysis</p>
                </div>
                <button onclick="generateMatchScore()" 
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span><?= !empty($application['match_score']) ? 'Recalculate Match' : 'Calculate Match Score' ?></span>
                </button>
            </div>
            
            <?php if (!empty($application['match_score'])): ?>
                <?php
                $matchScore = $application['match_score'] ?? 0;
                $skillScore = $application['skill_score'] ?? 0;
                $expScore = $application['experience_score'] ?? 0;
                $eduScore = $application['education_score'] ?? 0;
                $matchedSkills = $application['matched_skills'] ?? [];
                $missingSkills = $application['missing_skills'] ?? [];
                $extraSkills = $application['extra_relevant_skills'] ?? [];
                $recommendation = $application['recommendation'] ?? 'Review';
                $matchMethod = $application['match_method'] ?? 'database';
                ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div id="overall-score" class="text-3xl font-bold text-indigo-700 mb-1"><?= $matchScore ?>%</div>
                        <div class="text-sm text-gray-600">Overall Match</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div id="overall-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $matchScore) ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div id="skill-score" class="text-2xl font-bold text-indigo-700 mb-1"><?= $skillScore ?>%</div>
                        <div class="text-sm text-gray-600">Skills Match</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div id="skill-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $skillScore) ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div id="exp-score" class="text-2xl font-bold text-indigo-700 mb-1"><?= $expScore ?>%</div>
                        <div class="text-sm text-gray-600">Experience</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div id="exp-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $expScore) ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div id="edu-score" class="text-2xl font-bold text-indigo-700 mb-1"><?= $eduScore ?>%</div>
                        <div class="text-sm text-gray-600">Education</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div id="edu-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $eduScore) ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Matched Skills -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-bold text-indigo-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Matched Skills (<?= count($matchedSkills) ?>)
                        </h3>
                        <div id="matched-skills" class="flex flex-wrap gap-2">
                            <?php if (!empty($matchedSkills)): ?>
                                <?php foreach ($matchedSkills as $skill): ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                        <?= htmlspecialchars($skill) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-sm">No skills matched</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Missing Skills -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Missing Skills (<?= count($missingSkills) ?>)
                        </h3>
                        <div id="missing-skills" class="flex flex-wrap gap-2">
                            <?php if (!empty($missingSkills)): ?>
                                <?php foreach ($missingSkills as $skill): ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                        <?= htmlspecialchars($skill) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-600 text-sm font-medium">✓ All required skills matched!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($extraSkills)): ?>
                <div id="extra-skills-container" class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-bold text-indigo-700 mb-3">Bonus Skills (<?= count($extraSkills) ?>)</h3>
                    <div id="extra-skills" class="flex flex-wrap gap-2">
                        <?php foreach (array_slice($extraSkills, 0, 10) as $skill): ?>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm">
                                <?= htmlspecialchars($skill) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($application['match_summary'])): ?>
                <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-2">Analysis Summary</h3>
                    <p id="match-summary" class="text-gray-700 text-sm"><?= htmlspecialchars($application['match_summary']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span id="match-recommendation" class="px-4 py-2 rounded-full font-bold text-sm bg-indigo-50 text-indigo-700">
                            Recommendation: <?= htmlspecialchars($recommendation) ?>
                        </span>
                        <span class="text-xs text-gray-500">Method: <?= ucfirst($matchMethod) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Match Analysis Yet</h3>
                    <p class="text-gray-600 mb-4">Click the <strong class="text-indigo-600">"Calculate Match Score"</strong> button above to analyze this candidate's match based on skills, experience, location, and other factors.</p>
                    <div class="mt-4">
                        <button onclick="generateMatchScore()" 
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Calculate Match Score Now</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Application Status Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($application['job_title'] ?? 'Job Title') ?></h2>
                    <p class="text-gray-600">Applied on <?= date('M d, Y', strtotime($application['applied_at'] ?? 'now')) ?></p>
                </div>
                <span class="px-4 py-2 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                    <?= ucfirst($application['status'] ?? 'applied') ?>
                </span>
            </div>

            <!-- Application Actions -->
            <div class="flex flex-wrap gap-3 mt-4">
                <button onclick="updateStatus('shortlisted')" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Shortlist
                </button>
                <button onclick="updateStatus('rejected')" 
                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Reject
                </button>
                <?php if (strtolower($application['status'] ?? '') !== 'interview'): ?>
                <button onclick="openScheduleInterviewModal()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Schedule Interview
                </button>
                <?php endif; ?>
                <?php if (strtolower($application['status'] ?? '') === 'interview' || strtolower($application['status'] ?? '') === 'offer'): ?>
                <button onclick="updateStatus('hired')" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Hire Candidate
                </button>
                <?php endif; ?>
                <button onclick="startMessage()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Message
                </button>
            </div>
        </div>

        <!-- Cover Letter -->
        <?php if (!empty($application['cover_letter'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Cover Letter</h2>
            <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                <?= htmlspecialchars($application['cover_letter']) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Candidate Profile -->
        <?php if ($candidate): ?>
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Candidate Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!empty($candidate['full_name'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-gray-900"><?= htmlspecialchars($candidate['full_name']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['mobile'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Mobile</label>
                    <p class="text-gray-900"><?= htmlspecialchars($candidate['mobile']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['dob'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                    <p class="text-gray-900"><?= date('M d, Y', strtotime($candidate['dob'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['gender'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Gender</label>
                    <p class="text-gray-900"><?= ucfirst(htmlspecialchars($candidate['gender'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['city']) || !empty($candidate['state'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Location</label>
                    <p class="text-gray-900">
                        <?= htmlspecialchars(trim(($candidate['city'] ?? '') . ', ' . ($candidate['state'] ?? '') . ', ' . ($candidate['country'] ?? ''), ', ')) ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['expected_salary_min']) || !empty($candidate['expected_salary_max'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Expected Salary</label>
                    <p class="text-gray-900">
                        ₹<?= number_format($candidate['expected_salary_min'] ?? 0) ?>
                        <?php if (!empty($candidate['expected_salary_max'])): ?>
                        - ₹<?= number_format($candidate['expected_salary_max']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['current_salary'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Current Salary</label>
                    <p class="text-gray-900">₹<?= number_format($candidate['current_salary']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($candidate['notice_period'])): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Notice Period</label>
                    <p class="text-gray-900"><?= htmlspecialchars($candidate['notice_period']) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Self Introduction -->
            <?php if (!empty($candidate['self_introduction'])): ?>
            <div class="mt-6">
                <label class="text-sm font-medium text-gray-500">About</label>
                <p class="text-gray-700 mt-2 whitespace-pre-wrap"><?= htmlspecialchars($candidate['self_introduction']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Social Links -->
            <?php if (!empty($candidate['linkedin_url']) || !empty($candidate['github_url']) || !empty($candidate['portfolio_url']) || !empty($candidate['website_url'])): ?>
            <div class="mt-6">
                <label class="text-sm font-medium text-gray-500 mb-2 block">Social Links</label>
                <div class="flex flex-wrap gap-3">
                    <?php if (!empty($candidate['linkedin_url'])): ?>
                        <a href="<?= htmlspecialchars($candidate['linkedin_url']) ?>" target="_blank" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                        LinkedIn
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($candidate['github_url'])): ?>
                    <a href="<?= htmlspecialchars($candidate['github_url']) ?>" target="_blank" 
                       class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($candidate['portfolio_url'])): ?>
                    <a href="<?= htmlspecialchars($candidate['portfolio_url']) ?>" target="_blank" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Portfolio
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($candidate['website_url'])): ?>
                    <a href="<?= htmlspecialchars($candidate['website_url']) ?>" target="_blank" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Website
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Education -->
        <?php if (!empty($candidate['education'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Education</h2>
            <div class="space-y-4">
                <?php foreach ($candidate['education'] as $edu): ?>
                <div class="border-l-4 border-gray-200 pl-4">
                    <h3 class="font-semibold text-gray-900">
                        <?= htmlspecialchars($edu['degree'] ?? '') ?> 
                        <?php if (!empty($edu['field'])): ?>
                        in <?= htmlspecialchars($edu['field']) ?>
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-600"><?= htmlspecialchars($edu['institution'] ?? '') ?></p>
                    <p class="text-sm text-gray-500">
                        <?php if (!empty($edu['start_date'])): ?>
                            <?= date('M Y', strtotime($edu['start_date'])) ?>
                        <?php endif; ?>
                        <?php if ($edu['is_current'] ?? 0): ?>
                            - Present
                        <?php elseif (!empty($edu['end_date'])): ?>
                            - <?= date('M Y', strtotime($edu['end_date'])) ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($edu['grade'])): ?>
                    <p class="text-sm text-gray-600">Grade: <?= htmlspecialchars($edu['grade']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($edu['description'])): ?>
                    <p class="text-gray-700 mt-2"><?= htmlspecialchars($edu['description']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Experience -->
        <?php if (!empty($candidate['experience'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Work Experience</h2>
            <div class="space-y-4">
                <?php foreach ($candidate['experience'] as $exp): ?>
                <div class="border-l-4 border-gray-200 pl-4">
                    <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($exp['title'] ?? '') ?></h3>
                    <p class="text-gray-600"><?= htmlspecialchars($exp['company'] ?? '') ?></p>
                    <?php if (!empty($exp['location'])): ?>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($exp['location']) ?></p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500">
                        <?php if (!empty($exp['start_date'])): ?>
                            <?= date('M Y', strtotime($exp['start_date'])) ?>
                        <?php endif; ?>
                        <?php if ($exp['is_current'] ?? 0): ?>
                            - Present
                        <?php elseif (!empty($exp['end_date'])): ?>
                            - <?= date('M Y', strtotime($exp['end_date'])) ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($exp['description'])): ?>
                    <p class="text-gray-700 mt-2 whitespace-pre-wrap"><?= htmlspecialchars($exp['description']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Skills -->
        <?php if (!empty($candidate['skills'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Skills</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($candidate['skills'] as $skill): ?>
                <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                    <?= htmlspecialchars($skill['name'] ?? '') ?>
                    <?php if (!empty($skill['level'])): ?>
                        (<?= ucfirst(htmlspecialchars($skill['level'])) ?>)
                    <?php endif; ?>
                    <?php if (!empty($skill['years_experience'])): ?>
                        • <?= htmlspecialchars($skill['years_experience']) ?> yrs
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Languages -->
        <?php if (!empty($candidate['languages'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Languages</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($candidate['languages'] as $lang): ?>
                <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                    <?= htmlspecialchars($lang['language'] ?? '') ?>
                    <?php if (!empty($lang['proficiency'])): ?>
                        (<?= ucfirst(htmlspecialchars($lang['proficiency'])) ?>)
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Resume Download -->
        <?php if (!empty($application['resume_url']) || !empty($candidate['resume_url'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Resume / CV</h3>
            <?php 
            $resumeUrl = $application['resume_url'] ?? $candidate['resume_url'] ?? null;
            if ($resumeUrl):
            ?>
            <a href="<?= htmlspecialchars($resumeUrl) ?>" target="_blank" 
               class="w-full px-4 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download Resume
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Application Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Application Details</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Applied Date</span>
                    <span class="font-semibold"><?= date('M d, Y', strtotime($application['applied_at'] ?? 'now')) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Expected Salary</span>
                    <span class="font-semibold">
                        <?php if (!empty($application['expected_salary'])): ?>
                            ₹<?= number_format($application['expected_salary']) ?>
                        <?php else: ?>
                            Not specified
                        <?php endif; ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Match Score</span>
                    <span class="font-semibold">
                        <?php 
                        $matchScore = $application['score'] ?? $application['match_score'] ?? 0;
                        echo $matchScore . '%';
                        ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Source</span>
                    <span class="font-semibold"><?= ucfirst($application['source'] ?? 'portal') ?></span>
                </div>
            </div>
        </div>

        <!-- Candidate Contact -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-gray-600">Email</span>
                    <p class="font-semibold"><?= htmlspecialchars($application['candidate_email'] ?? 'N/A') ?></p>
                </div>
                <?php if (!empty($application['phone'])): ?>
                <div>
                    <span class="text-gray-600">Phone</span>
                    <p class="font-semibold"><?= htmlspecialchars($application['phone']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse-slow {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

<script>
async function startMessage() {
    const candidateUserId = <?= $application['candidate_user_id'] ?? 0 ?>;
    if (!candidateUserId) {
        alert('Candidate user ID not found');
        return;
    }

    try {
        const response = await fetch('/employer/messages/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                candidate_user_id: candidateUserId,
                job_id: <?= $application['job_id'] ?? 'null' ?>,
                initial_message: 'Hello, I saw your application and would like to discuss further.'
            })
        });

        const data = await response.json();
        if (data.success) {
            window.location.href = '/employer/messages?conversation=' + data.conversation_id;
        } else {
            alert('Error: ' + (data.error || 'Failed to start conversation'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}

function updateStatus(newStatus) {
    if (!confirm(`Are you sure you want to ${newStatus} this application?`)) {
        return;
    }
    
    fetch('/employer/applications/<?= $application['id'] ?>/status', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

async function generateMatchScore() {
    const applicationId = <?= $application['id'] ?? 0 ?>;
    if (!applicationId) {
        alert('Application ID not found');
        return;
    }

    // Get button and match analysis container
    const button = event.target.closest('button');
    const matchContainer = document.getElementById('match-analysis');
    const originalButtonHTML = button.innerHTML;
    
    // Disable button and show loading
    button.disabled = true;
        button.innerHTML = `
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Calculating...</span>
    `;

    // Show progress overlay
    showProgressOverlay(matchContainer);

    // Simulate progress updates
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90; // Don't go to 100% until done
        updateProgress(progress);
    }, 200);

    try {
        const response = await fetch(`/employer/applications/${applicationId}/generate-score`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        const data = await response.json();
        clearInterval(progressInterval);
        
        if (data.success && data.match_data) {
            // Complete progress
            updateProgress(100);
            
            // Update UI with real data
            setTimeout(() => {
                updateMatchAnalysisUI(data.match_data);
                hideProgressOverlay();
                
                // Show success on button
                button.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Recalculate Match</span>
                `;
                button.disabled = false;
                button.classList.remove('from-blue-600', 'to-blue-700', 'hover:from-blue-700', 'hover:to-blue-800', 'from-green-600', 'to-green-700', 'hover:from-green-700', 'hover:to-green-800', 'bg-indigo-600', 'hover:bg-indigo-700');
                button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                
                // Scroll to match analysis
                document.getElementById('match-analysis').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 500);
        } else {
            hideProgressOverlay();
            button.disabled = false;
            button.innerHTML = originalButtonHTML;
            alert('Failed to calculate match score: ' + (data.message || data.error || 'Unknown error'));
        }
    } catch (error) {
        clearInterval(progressInterval);
        hideProgressOverlay();
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = originalButtonHTML;
        alert('An error occurred: ' + error.message);
    }
}

function showProgressOverlay(container) {
    // Create progress overlay
    const overlay = document.createElement('div');
    overlay.id = 'match-progress-overlay';
    overlay.className = 'absolute inset-0 bg-white bg-opacity-95 rounded-lg z-50 flex flex-col items-center justify-center';
    overlay.innerHTML = `
        <div class="text-center mb-6">
            <svg class="animate-spin mx-auto h-12 w-12 text-indigo-600 mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Calculating Match Score...</h3>
            <p class="text-gray-600 mb-4">Analyzing skills, experience, location, and qualifications</p>
            <div class="w-full max-w-md bg-gray-200 rounded-full h-4 mb-2">
                <div id="progress-bar" class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-4 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
            </div>
            <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                <span id="progress-text">0%</span>
                <span class="text-gray-400">•</span>
                <span id="progress-step">Initializing...</span>
            </div>
        </div>
    `;
    container.style.position = 'relative';
    container.appendChild(overlay);
}

function updateProgress(percentage) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressStep = document.getElementById('progress-step');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
    if (progressText) {
        progressText.textContent = Math.round(percentage) + '%';
    }
    if (progressStep) {
        if (percentage < 20) {
            progressStep.textContent = 'Loading candidate data...';
        } else if (percentage < 40) {
            progressStep.textContent = 'Analyzing skills...';
        } else if (percentage < 60) {
            progressStep.textContent = 'Evaluating experience...';
        } else if (percentage < 80) {
            progressStep.textContent = 'Checking location match...';
        } else if (percentage < 95) {
            progressStep.textContent = 'Calculating final score...';
        } else {
            progressStep.textContent = 'Finalizing results...';
        }
    }
}

function hideProgressOverlay() {
    const overlay = document.getElementById('match-progress-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s';
        setTimeout(() => overlay.remove(), 300);
    }
}

function updateMatchAnalysisUI(matchData) {
    // Check if match analysis section exists, if not create it
    let matchContainer = document.getElementById('match-analysis');
    if (!matchContainer) return;
    
    // Check if we need to show the match results section (if it was empty before)
    const emptyState = matchContainer.querySelector('.text-center.py-8');
    if (emptyState) {
        // Remove empty state and show match results
        emptyState.remove();
        createMatchResultsSection(matchContainer, matchData);
        return;
    }
    
    // Update existing match scores
    const overallScore = matchData.overall_match_score || 0;
    const skillScore = matchData.skill_match_score || 0;
    const expScore = matchData.experience_match_score || 0;
    const eduScore = matchData.education_match_score || 0;
    
    // Animate score updates
    animateScoreUpdate('overall-score', overallScore, 'text-indigo-700');
    animateScoreUpdate('skill-score', skillScore, 'text-indigo-700');
    animateScoreUpdate('exp-score', expScore, 'text-indigo-700');
    animateScoreUpdate('edu-score', eduScore, 'text-indigo-700');
    
    // Update progress bars
    animateProgressBar('overall-progress', overallScore);
    animateProgressBar('skill-progress', skillScore);
    animateProgressBar('exp-progress', expScore);
    animateProgressBar('edu-progress', eduScore);
    
    // Update matched skills
    updateSkillsList('matched-skills', matchData.matched_skills || [], 'matched');
    
    // Update missing skills
    updateSkillsList('missing-skills', matchData.missing_skills || [], 'missing');
    
    // Update extra skills
    const extraContainer = document.getElementById('extra-skills-container');
    if (matchData.extra_relevant_skills && matchData.extra_relevant_skills.length > 0) {
        if (!extraContainer) {
            // Create extra skills container
            const skillsGrid = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-4');
            if (skillsGrid) {
                const extraDiv = document.createElement('div');
                extraDiv.id = 'extra-skills-container';
                extraDiv.className = 'mt-4 bg-white rounded-lg p-4 shadow-sm';
                extraDiv.innerHTML = `
                    <h3 class="font-bold text-blue-700 mb-3">Bonus Skills (${matchData.extra_relevant_skills.length})</h3>
                    <div id="extra-skills" class="flex flex-wrap gap-2"></div>
                `;
                skillsGrid.parentNode.insertBefore(extraDiv, skillsGrid.nextSibling);
            }
        }
        updateSkillsList('extra-skills', matchData.extra_relevant_skills, 'extra');
    } else if (extraContainer) {
        extraContainer.remove();
    }
    
    // Update summary
    let summaryEl = document.getElementById('match-summary');
    if (matchData.summary) {
        if (!summaryEl) {
            const summaryDiv = document.createElement('div');
            summaryDiv.className = 'mt-4 bg-white rounded-lg p-4 shadow-sm';
            summaryDiv.innerHTML = `
                <h3 class="font-bold text-gray-900 mb-2">Analysis Summary</h3>
                <p id="match-summary" class="text-gray-700 text-sm"></p>
            `;
            matchContainer.appendChild(summaryDiv);
            summaryEl = document.getElementById('match-summary');
        }
        summaryEl.textContent = matchData.summary;
    }
    
    // Update recommendation
    let recEl = document.getElementById('match-recommendation');
    if (matchData.recommendation) {
        if (!recEl) {
            const recContainer = document.createElement('div');
            recContainer.className = 'mt-4 flex items-center justify-between';
            recContainer.innerHTML = `
                <div class="flex items-center gap-2">
                    <span id="match-recommendation" class="px-4 py-2 rounded-full font-bold text-sm"></span>
                    <span class="text-xs text-gray-500">Method: Database</span>
                </div>
            `;
            matchContainer.appendChild(recContainer);
            recEl = document.getElementById('match-recommendation');
        }
        recEl.textContent = 'Recommendation: ' + matchData.recommendation;
        recEl.className = 'px-4 py-2 rounded-full font-bold text-sm ' + getRecommendationClass(matchData.recommendation);
    }
}

function createMatchResultsSection(container, matchData) {
    const overallScore = matchData.overall_match_score || 0;
    const skillScore = matchData.skill_match_score || 0;
    const expScore = matchData.experience_match_score || 0;
    const eduScore = matchData.education_match_score || 0;
    
    const html = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div id="overall-score" class="text-3xl font-bold text-indigo-700 mb-1">${overallScore}%</div>
                <div class="text-sm text-gray-600">Overall Match</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="overall-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: ${overallScore}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div id="skill-score" class="text-2xl font-bold text-indigo-700 mb-1">${skillScore}%</div>
                <div class="text-sm text-gray-600">Skills Match</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="skill-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: ${skillScore}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div id="exp-score" class="text-2xl font-bold text-indigo-700 mb-1">${expScore}%</div>
                <div class="text-sm text-gray-600">Experience</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="exp-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: ${expScore}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div id="edu-score" class="text-2xl font-bold text-indigo-700 mb-1">${eduScore}%</div>
                <div class="text-sm text-gray-600">Education</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="edu-progress" class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: ${eduScore}%"></div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-bold text-indigo-700 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Matched Skills (${(matchData.matched_skills || []).length})
                </h3>
                <div id="matched-skills" class="flex flex-wrap gap-2"></div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Missing Skills (${(matchData.missing_skills || []).length})
                </h3>
                <div id="missing-skills" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    while (tempDiv.firstChild) {
        container.appendChild(tempDiv.firstChild);
    }
    
    // Now update the skills lists
    updateSkillsList('matched-skills', matchData.matched_skills || [], 'matched');
    updateSkillsList('missing-skills', matchData.missing_skills || [], 'missing');
    
    // Animate the scores
    setTimeout(() => {
        animateScoreUpdate('overall-score', overallScore, 'text-indigo-700');
        animateScoreUpdate('skill-score', skillScore, 'text-indigo-700');
        animateScoreUpdate('exp-score', expScore, 'text-indigo-700');
        animateScoreUpdate('edu-score', eduScore, 'text-indigo-700');
        
        animateProgressBar('overall-progress', overallScore);
        animateProgressBar('skill-progress', skillScore);
        animateProgressBar('exp-progress', expScore);
        animateProgressBar('edu-progress', eduScore);
    }, 100);
}

function animateScoreUpdate(elementId, targetValue, colorClass) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let current = 0;
    const increment = targetValue / 30;
    const interval = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
            current = targetValue;
            clearInterval(interval);
        }
        element.textContent = Math.round(current) + '%';
    }, 20);
}

function animateProgressBar(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let current = 0;
    const increment = targetValue / 30;
    const interval = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
            current = targetValue;
            clearInterval(interval);
        }
        element.style.width = Math.min(100, current) + '%';
    }, 20);
}

function updateSkillsList(containerId, skills, type) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (skills.length === 0) {
        if (type === 'matched') {
            container.innerHTML = '<p class="text-gray-500 text-sm">No skills matched</p>';
        } else {
            container.innerHTML = '<p class="text-green-600 text-sm font-medium">✓ All required skills matched!</p>';
        }
        return;
    }
    
    const countEl = container.previousElementSibling;
    if (countEl && countEl.tagName === 'H3') {
        const countMatch = countEl.textContent.match(/\((\d+)\)/);
        if (countMatch) {
            countEl.innerHTML = countEl.innerHTML.replace(/\(\d+\)/, `(${skills.length})`);
        }
    }
    
    container.innerHTML = skills.map(skill => {
    const bgClass = type === 'matched' ? 'bg-gray-100 text-gray-800' : 
                       type === 'missing' ? 'bg-gray-100 text-gray-800' : 
                       'bg-indigo-50 text-indigo-700';
        return `<span class="px-3 py-1 ${bgClass} rounded-full text-sm font-medium animate-fadeIn">${escapeHtml(skill)}</span>`;
    }).join('');
}

function getRecommendationClass(recommendation) {
    return 'bg-indigo-50 text-indigo-700';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openScheduleInterviewModal() {
    document.getElementById('scheduleInterviewModal').classList.remove('hidden');
}

function closeScheduleInterviewModal() {
    document.getElementById('scheduleInterviewModal').classList.add('hidden');
    // Reset form
    document.getElementById('scheduleInterviewForm').reset();
}

async function submitScheduleInterview(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = {
        application_id: <?= $application['id'] ?>,
        interview_type: formData.get('interview_type'),
        scheduled_start: formData.get('scheduled_date') + ' ' + formData.get('scheduled_time'),
        scheduled_end: formData.get('scheduled_date') + ' ' + formData.get('end_time'),
        timezone: formData.get('timezone') || 'Asia/Kolkata',
        location: formData.get('location') || '',
        meeting_link: formData.get('meeting_link') || ''
    };

    // Validate
    if (!data.scheduled_start || !data.scheduled_end) {
        alert('Please select date and time');
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Scheduling...';

    try {
        const response = await fetch('/employer/interviews/schedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Interview scheduled successfully!');
            closeScheduleInterviewModal();
            location.reload();
        } else {
            alert(result.error || 'Failed to schedule interview');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while scheduling the interview');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Update end time when start time changes
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('scheduled_time');
    const endTimeInput = document.getElementById('end_time');
    
    if (startTimeInput && endTimeInput) {
        startTimeInput.addEventListener('change', function() {
            if (this.value) {
                const [hours, minutes] = this.value.split(':');
                const startDate = new Date();
                startDate.setHours(parseInt(hours), parseInt(minutes));
                startDate.setHours(startDate.getHours() + 1); // Add 1 hour
                
                const endHours = String(startDate.getHours()).padStart(2, '0');
                const endMinutes = String(startDate.getMinutes()).padStart(2, '0');
                endTimeInput.value = `${endHours}:${endMinutes}`;
            }
        });
    }
});
</script>

<!-- Schedule Interview Modal -->
<div id="scheduleInterviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeScheduleInterviewModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Schedule Interview</h2>
                <button onclick="closeScheduleInterviewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="scheduleInterviewForm" onsubmit="submitScheduleInterview(event)" class="p-6 space-y-6">
            <!-- Interview Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Interview Type *</label>
                <select name="interview_type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="phone">Phone Interview</option>
                    <option value="video">Video Interview</option>
                    <option value="onsite">On-site Interview</option>
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
                <input type="date" 
                       name="scheduled_date" 
                       required
                       min="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Time Range -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time *</label>
                    <input type="time" 
                           id="scheduled_time"
                           name="scheduled_time" 
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">End Time *</label>
                    <input type="time" 
                           id="end_time"
                           name="end_time" 
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Timezone -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label>
                <select name="timezone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">America/New_York (EST)</option>
                    <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                    <option value="Europe/London">Europe/London (GMT)</option>
                </select>
            </div>

            <!-- Location (for on-site) -->
            <div id="locationField">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                <input type="text" 
                       name="location" 
                       placeholder="Enter interview location"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Required for on-site interviews</p>
            </div>

            <!-- Meeting Link (for video) -->
            <div id="meetingLinkField" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Meeting Link</label>
                <input type="url" 
                       name="meeting_link" 
                       placeholder="https://meet.example.com/room-id"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate a meeting link</p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" 
                        onclick="closeScheduleInterviewModal()" 
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                    Schedule Interview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide location and meeting link fields based on interview type
document.addEventListener('DOMContentLoaded', function() {
    const interviewTypeSelect = document.querySelector('select[name="interview_type"]');
    const locationField = document.getElementById('locationField');
    const meetingLinkField = document.getElementById('meetingLinkField');
    
    if (interviewTypeSelect) {
        interviewTypeSelect.addEventListener('change', function() {
            if (this.value === 'onsite') {
                locationField.querySelector('input').required = true;
                locationField.style.display = 'block';
                meetingLinkField.classList.add('hidden');
            } else if (this.value === 'video') {
                locationField.querySelector('input').required = false;
                locationField.style.display = 'none';
                meetingLinkField.classList.remove('hidden');
            } else {
                locationField.querySelector('input').required = false;
                locationField.style.display = 'block';
                meetingLinkField.classList.add('hidden');
            }
        });
    }
});
</script>


