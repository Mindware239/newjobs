<?php
/**
 * Resume Builder - Step-by-Step Wizard
 * Pixel-perfect match to ResumeNow design
 * 
 * @var \App\Models\Candidate $candidate
 * @var \App\Models\Resume $resume
 * @var \App\Models\ResumeTemplate $template
 * @var array $sections
 * @var bool $isPremium
 */

$base = $base ?? '/';

// Define wizard steps matching ResumeNow exactly
$steps = [
    'header' => ['title' => 'Header', 'label' => 'Header', 'number' => 1],
    'experience' => ['title' => 'Experience', 'label' => 'Experience', 'number' => 2],
    'education' => ['title' => 'Education', 'label' => 'Education', 'number' => 3],
    'skills' => ['title' => 'Skills', 'label' => 'Skills', 'number' => 4],
    'summary' => ['title' => 'Summary', 'label' => 'Summary', 'number' => 5],
    'additional' => ['title' => 'Additional', 'label' => 'Additional Details', 'number' => 6],
    'finalize' => ['title' => 'Finalize', 'label' => 'Finalize', 'number' => 7]
];

$currentStep = $_GET['step'] ?? 'header';
if (!isset($steps[$currentStep])) {
    $currentStep = 'header';
}

// Get current step index
$stepKeys = array_keys($steps);
$currentStepIndex = array_search($currentStep, $stepKeys);

// Prepare sections data for Alpine.js
$sectionsData = [];
foreach ($sections as $section) {
    $sectionsData[$section['section_type']] = $section;
}

// Step labels for progress messages
$stepLabels = [
    'header' => 'Header',
    'experience' => 'Experience',
    'education' => 'Education',
    'skills' => 'Skills',
    'summary' => 'Summary',
    'additional' => 'Additional Details',
    'finalize' => 'Finalize'
];

// Progress messages
$progressMessages = [
    'header' => "Let's get started!",
    'experience' => "Great progress! Next up →",
    'education' => "Great progress! Next up →",
    'skills' => "Great progress! Next up →",
    'summary' => "Almost there! Next up →",
    'additional' => "Almost there! Next up →",
    'finalize' => "Final step!"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($resume->attributes['title'] ?? 'My Resume') ?> - Resume Builder</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="/css/output.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        
        /* ResumeNow Exact Colors */
        :root {
            --sidebar-blue: #1e3a8a; /* Dark blue for sidebar */
            --sidebar-blue-light: #1e40af;
            --current-step-bg: #ffffff;
            --current-step-text: #1e3a8a;
            --completed-green: #10b981;
            --button-blue: #2563eb;
            --button-blue-hover: #1d4ed8;
            --text-gray: #4b5563;
            --text-dark: #111827;
            --border-gray: #e5e7eb;
        }

        /* Smooth transitions */
        * {
            transition-property: color, background-color, border-color, transform, opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        /* Sidebar animations */
        .step-item {
            transition: all 0.3s ease;
        }

        .step-item:hover {
            transform: translateX(4px);
        }

        /* Content fade-in animation */
        .step-content-enter {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Button hover effects */
        .btn-continue {
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .btn-continue:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Input focus animations */
        input:focus, textarea:focus, select:focus {
            transform: scale(1.01);
            transition: transform 0.2s ease;
        }

        /* Step circle animations */
        .step-circle {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-circle.completed {
            animation: checkmarkPop 0.4s ease-out;
        }

        @keyframes checkmarkPop {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Resume preview animation */
        .preview-card {
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Sidebar exact styling */
        .sidebar-nav {
            background: var(--sidebar-blue);
        }

        .step-circle-active {
            background: var(--current-step-bg);
            color: var(--current-step-text);
            font-weight: 600;
        }

        .step-circle-pending {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.7);
        }

        .step-circle-completed {
            background: var(--completed-green);
        }

        /* Button exact styling */
        .btn-back {
            border: 1px solid var(--border-gray);
            background: white;
            color: var(--text-dark);
        }

        .btn-back:hover:not(:disabled) {
            background: #f9fafb;
        }

        .btn-continue {
            background: var(--button-blue);
            color: white;
        }

        .btn-continue:hover {
            background: var(--button-blue-hover);
        }

        /* Form styling */
        .form-input {
            border: 1px solid var(--border-gray);
            font-size: 14px;
            padding: 10px 14px;
        }

        .form-input:focus {
            border-color: var(--button-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        /* Title styling matching reference */
        .step-title {
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--text-dark);
            margin-bottom: 24px;
        }

        .progress-message {
            font-size: 18px;
            color: var(--text-gray);
            margin-bottom: 16px;
        }

        /* Preview panel */
        .preview-panel {
            background: #f3f4f6;
        }

        .resume-preview-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transform: scale(0.85);
            transform-origin: top left;
            width: 117.65%;
        }
    </style>
</head>
<body class="bg-gray-50" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <!-- Website Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <?php $base = $base ?? '/'; require __DIR__ . '/../../../include/header.php'; ?>
    </div>
    
    <div x-data="resumeWizard()" x-init="init()" class="flex" style="height: calc(100vh - 80px);">
        
        <!-- Left Sidebar - Exact ResumeNow Design -->
        <aside class="sidebar-nav w-64 flex flex-col text-white flex-shrink-0" style="background: var(--sidebar-blue);">

            
            <!-- Steps Navigation -->
            <nav class="flex-1 overflow-y-auto p-6 space-y-1">
                <?php 
                foreach ($steps as $stepKey => $stepInfo): 
                    $section = $sectionsData[$stepKey] ?? null;
                    $isCompleted = $section && !empty($section['section_data']['content']);
                    if ($stepKey === 'header') {
                        $isCompleted = $section && !empty($section['section_data']['content']['full_name']);
                    } elseif ($stepKey === 'experience' || $stepKey === 'education') {
                        $isCompleted = $section && !empty($section['section_data']['content']['items']) && count($section['section_data']['content']['items']) > 0;
                    } elseif ($stepKey === 'summary') {
                        $isCompleted = $section && !empty($section['section_data']['content']['text']);
                    }
                    $isActive = $stepKey === $currentStep;
                ?>
                <div class="step-item py-2">
                    <div class="flex items-center gap-3">
                        <?php if ($isCompleted && !$isActive): ?>
                            <!-- Completed Step -->
                            <div class="step-circle step-circle-completed w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        <?php elseif ($isActive): ?>
                            <!-- Active Step -->
                            <div class="step-circle step-circle-active w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                <?= $stepInfo['number'] ?>
                            </div>
                        <?php else: ?>
                            <!-- Pending Step -->
                            <div class="step-circle step-circle-pending w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                <?= $stepInfo['number'] ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <div class="font-medium text-sm <?= $isActive ? 'text-white' : 'text-white/70' ?>">
                                <?= htmlspecialchars($stepInfo['number'] . ' ' . $stepInfo['label']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex overflow-hidden">
            <!-- Center Panel - Form Content -->
            <div class="flex-1 bg-white overflow-y-auto">
                <div class="max-w-4xl mx-auto px-12 py-16">
                    <!-- Progress Message -->
                    <div class="mb-6 progress-message">
                        <?php if ($currentStepIndex === 0): ?>
                            <span><?= $progressMessages[$currentStep] ?></span>
                        <?php else: ?>
                            <span><?= $progressMessages[$currentStep] ?? "Great progress! Next up →" ?> <span class="font-semibold text-gray-900"><?= $stepLabels[$currentStep] ?></span></span>
                        <?php endif; ?>
                    </div>

                    <!-- Step Title -->
                    <h1 class="step-title">
                        <?php if ($currentStep === 'header'): ?>
                            Let's start with your header
                        <?php elseif ($currentStep === 'experience'): ?>
                            Add details about your work experience
                        <?php elseif ($currentStep === 'education'): ?>
                            Now, let's add your education
                        <?php elseif ($currentStep === 'skills'): ?>
                            Time to showcase your skills
                        <?php elseif ($currentStep === 'summary'): ?>
                            Let's craft your professional summary
                        <?php elseif ($currentStep === 'additional'): ?>
                            Add any additional details
                        <?php else: ?>
                            Review and finalize your resume
                        <?php endif; ?>
                    </h1>

                    <!-- Step Content -->
                    <div class="mb-12 step-content-enter">
                        <?php include __DIR__ . '/wizard-steps/' . $currentStep . '.php'; ?>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between pt-8 border-t" style="border-color: var(--border-gray);">
                        <button 
                            @click="previousStep()"
                            :disabled="currentStepIndex === 0"
                            class="btn-back px-8 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"
                            style="min-width: 120px;">
                            Back
                        </button>
                        
                        <button 
                            @click="nextStep()"
                            class="btn-continue px-8 py-3 rounded-lg font-medium text-white transition"
                            style="min-width: 120px;">
                            Continue
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Resume Preview -->
            <aside class="preview-panel w-96 border-l overflow-y-auto p-6 flex-shrink-0" style="background: #f3f4f6; border-color: var(--border-gray);">
                <div class="sticky top-6">
                    <div class="preview-card resume-preview-card p-6">
                        <?php include __DIR__ . '/wizard-preview.php'; ?>
                    </div>
                    <div class="mt-6 text-center">
                        <a href="/candidate/resume/builder/<?= (int)($resume->attributes['id'] ?? 0) ?>/edit" 
                           class="text-sm text-blue-600 hover:text-blue-700 transition font-medium">
                            Change template
                        </a>
                    </div>
                </div>
            </aside>
        </main>

        <script>
            function resumeWizard() {
                return {
                    resumeId: <?= (int)($resume->attributes['id'] ?? 0) ?>,
                    currentStep: '<?= $currentStep ?>',
                    sections: <?= json_encode($sectionsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                    template: <?= $template ? json_encode([
                        'id' => (int)($template->attributes['id'] ?? 0),
                        'name' => $template->attributes['name'] ?? '',
                        'schema' => $template->getSchema()
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null' ?>,
                    isPremium: <?= $isPremium ? 'true' : 'false' ?>,
                    strengthScore: <?= (int)($resume->attributes['strength_score'] ?? 0) ?>,
                    saving: false,
                    exporting: false,
                    autoSaveTimer: null,
                    loading: false,
                    skillSuggestions: [],
                    newSkill: '',

                    steps: ['header', 'experience', 'education', 'skills', 'summary', 'additional', 'finalize'],
                    
                    get currentStepIndex() {
                        return this.steps.indexOf(this.currentStep);
                    },

                    async parseJson(response) {
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error(text || 'Invalid JSON response');
                        }
                    },

                    addSkill(skillName) {
                        if (!skillName) return;
                        const skillsSection = this.getSection('skills');
                        if (!skillsSection.section_data) {
                            skillsSection.section_data = { content: { items: [] } };
                        }
                        if (!skillsSection.section_data.content) {
                            skillsSection.section_data.content = { items: [] };
                        }
                        if (!Array.isArray(skillsSection.section_data.content.items)) {
                            skillsSection.section_data.content.items = [];
                        }
                        const items = skillsSection.section_data.content.items;
                        if (!items.some(item => (item.name || item).toLowerCase() === skillName.toLowerCase())) {
                            items.push({ name: skillName, proficiency_level: 'intermediate' });
                            this.autoSave();
                        }
                    },

                    async suggestSkills() {
                        this.loading = true;
                        this.skillSuggestions = [];
                        
                        try {
                            // Get job role from first experience or prompt user
                            const experienceSection = this.getSection('experience');
                            const expItems = experienceSection.section_data.content.items;
                            const firstExp = (expItems && Array.isArray(expItems) && expItems.length > 0) ? expItems[0] : null;
                            const jobRole = firstExp?.job_title || '';
                            
                            // Prompt for job role if not available
                            if (!jobRole) {
                                const userJobRole = prompt('Enter the target job role for skill suggestions:');
                                if (!userJobRole) {
                                    this.loading = false;
                                    return;
                                }
                                const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/suggest-skills`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    },
                                    body: JSON.stringify({
                                        job_role: userJobRole
                                    })
                                });

                                if (!response.ok) {
                                    throw new Error('AI skill suggestion failed');
                                }

                                const result = await this.parseJson(response);
                                
                                if (result.success && result.skills && result.skills.length > 0) {
                                    this.skillSuggestions = result.skills;
                                    alert(`✅ Found ${result.skills.length} comprehensive skill suggestions! Click on them to add.`);
                                } else {
                                    alert('No skills suggested. Please try again.');
                                }
                            } else {
                                const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/suggest-skills`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    },
                                    body: JSON.stringify({
                                        job_role: jobRole
                                    })
                                });

                                if (!response.ok) {
                                    throw new Error('AI skill suggestion failed');
                                }

                                const result = await this.parseJson(response);
                                
                                if (result.success && result.skills && result.skills.length > 0) {
                                    this.skillSuggestions = result.skills;
                                    alert(`✅ Found ${result.skills.length} comprehensive skill suggestions! Click on them to add.`);
                                } else {
                                    alert('No skills suggested. Please try again.');
                                }
                            }

                        } catch (error) {
                            console.error('Error suggesting skills:', error);
                            alert('Failed to get skill suggestions. Please check your OpenAI API key in .env file.');
                        } finally {
                            this.loading = false;
                        }
                    },

                    async enhanceDescription(index) {
                        this.loading = true;
                        const experienceItem = this.getSection('experience').section_data.content.items[index];
                        
                        try {
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/enhance-description`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    job_title: experienceItem.job_title,
                                    company: experienceItem.company_name,
                                    description: experienceItem.description,
                                    skills: (this.getSection('skills').section_data.content.items || []).map(s => s.name)
                                })
                            });

                            if (!response.ok) {
                                throw new Error('AI enhancement failed');
                            }

                            const result = await this.parseJson(response);
                            
                            if (result.success && result.description) {
                                this.getSection('experience').section_data.content.items[index].description = result.description;
                                this.autoSave();
                            }

                        } catch (error) {
                            console.error('Error enhancing description:', error);
                            // Optionally, show an error message to the user
                        } finally {
                            this.loading = false;
                        }
                    },

                    async generateJobSummary() {
                        // Set loading state for summary step
                        const summaryStep = document.querySelector('[x-data*="generatingAI"]');
                        if (summaryStep && summaryStep.__x) {
                            summaryStep.__x.$data.generatingAI = true;
                        }
                        this.loading = true;
                        
                        try {
                            // Get target job role from first experience or prompt user
                            const firstExp = this.getSection('experience').section_data.content.items?.[0];
                            const targetJobRole = firstExp?.job_title || '';
                            
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/generate-job-summary`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify({
                                    target_job_role: targetJobRole
                                })
                            });

                            if (!response.ok) {
                                throw new Error('AI generation failed');
                            }

                            const result = await this.parseJson(response);
                            
                            if (result.success && result.summary) {
                                this.getSection('summary').section_data.content.text = result.summary;
                                await this.autoSave();
                                alert('AI summary generated successfully!');
                            } else {
                                alert('Failed to generate summary. Please try again.');
                            }

                        } catch (error) {
                            console.error('Error generating summary:', error);
                            alert('An error occurred. Please try again.');
                        } finally {
                            this.loading = false;
                            if (summaryStep && summaryStep.__x) {
                                summaryStep.__x.$data.generatingAI = false;
                            }
                        }
                    },

                    async enhanceSummary() {
                        const summaryStep = document.querySelector('[x-data*="enhancingAI"]');
                        if (summaryStep && summaryStep.__x) {
                            summaryStep.__x.$data.enhancingAI = true;
                        }
                        this.loading = true;
                        const summaryText = this.getSection('summary').section_data.content.text;
                        
                        try {
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/enhance-description`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify({
                                    job_title: (this.getSection('experience').section_data.content.items && this.getSection('experience').section_data.content.items[0]) ? this.getSection('experience').section_data.content.items[0].job_title : '',
                                    company: '',
                                    description: summaryText,
                                    skills: (this.getSection('skills').section_data.content.items && Array.isArray(this.getSection('skills').section_data.content.items)) ? this.getSection('skills').section_data.content.items.map(s => s.name || s).filter(Boolean) : []
                                })
                            });

                            if (!response.ok) {
                                throw new Error('AI enhancement failed');
                            }

                            const result = await this.parseJson(response);
                            
                            if (result.success && result.description) {
                                this.getSection('summary').section_data.content.text = result.description;
                                await this.autoSave();
                                alert('Summary enhanced successfully!');
                            } else {
                                alert('Failed to enhance summary. Using original content.');
                            }

                        } catch (error) {
                            console.error('Error enhancing summary:', error);
                            alert('An error occurred. Please try again.');
                        } finally {
                            this.loading = false;
                            if (summaryStep && summaryStep.__x) {
                                summaryStep.__x.$data.enhancingAI = false;
                            }
                        }
                    },

                    async generateExperienceDescription(index) {
                        this.loading = true;
                        const experienceSection = this.getSection('experience');
                        if (!experienceSection.section_data.content.items || !Array.isArray(experienceSection.section_data.content.items)) {
                            experienceSection.section_data.content.items = [];
                        }
                        const experienceItem = experienceSection.section_data.content.items[index];
                        
                        if (!experienceItem || !experienceItem.job_title) {
                            alert('Please enter a job title first.');
                            this.loading = false;
                            return;
                        }
                        
                        try {
                            // Get target job role from first experience or current job
                            const items = experienceSection.section_data.content.items;
                            const firstExp = items && items.length > 0 ? items[0] : null;
                            const targetJobRole = firstExp?.job_title || experienceItem.job_title;
                            
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/generate-experience`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify({
                                    job_title: experienceItem.job_title || '',
                                    company: experienceItem.company_name || '',
                                    target_job_role: targetJobRole
                                })
                            });

                            if (!response.ok) {
                                throw new Error('AI generation failed');
                            }

                            const result = await this.parseJson(response);
                            
                            if (result.success && result.description) {
                                experienceItem.description = result.description;
                                await this.autoSave();
                                alert('✅ Comprehensive experience description generated successfully!');
                            } else {
                                alert('Failed to generate description. Please try again.');
                            }

                        } catch (error) {
                            console.error('Error generating experience description:', error);
                            alert('An error occurred. Please try again.');
                        } finally {
                            this.loading = false;
                        }
                    },

                    init() {
                        // Initialize sections if empty
                        this.steps.forEach(step => {
                            if (!this.sections[step]) {
                                this.sections[step] = {
                                    section_type: step,
                                    section_data: { content: this.getDefaultContent(step) },
                                    is_visible: true
                                };
                            }
                            // Ensure section_data.content exists
                            if (!this.sections[step].section_data) {
                                this.sections[step].section_data = { content: this.getDefaultContent(step) };
                            } else if (!this.sections[step].section_data.content) {
                                this.sections[step].section_data.content = this.getDefaultContent(step);
                            }
                        });

                        // Auto-save every 30 seconds
                        this.autoSaveTimer = setInterval(() => {
                            this.autoSave();
                        }, 30000);
                    },

                    getSection(type) {
                        if (!this.sections[type]) {
                            this.sections[type] = {
                                section_type: type,
                                section_data: { content: this.getDefaultContent(type) },
                                is_visible: true
                            };
                        }
                        // Ensure section_data.content exists
                        if (!this.sections[type].section_data) {
                            this.sections[type].section_data = { content: this.getDefaultContent(type) };
                        } else if (!this.sections[type].section_data.content) {
                            this.sections[type].section_data.content = this.getDefaultContent(type);
                        }
                        // For header, ensure first_name and last_name exist if full_name exists
                        if (type === 'header' && this.sections[type].section_data.content) {
                            const content = this.sections[type].section_data.content;
                            if (content.full_name && !content.first_name && !content.last_name) {
                                const nameParts = content.full_name.split(' ', 2);
                                content.first_name = nameParts[0] || '';
                                content.last_name = nameParts[1] || '';
                            }
                        }
                        return this.sections[type];
                    },

                    getDefaultContent(type) {
                        switch(type) {
                            case 'header':
                                return { first_name: '', last_name: '', full_name: '', email: '', phone: '', city: '', country: '', pin_code: '', location: '', linkedin: '', website: '' };
                            case 'experience':
                            case 'education':
                                return { items: [] };
                            case 'skills':
                                return { items: [] };
                            case 'summary':
                                return { text: '' };
                            case 'additional':
                                return { projects: [], certifications: [] };
                            default:
                                return {};
                        }
                    },

                    async autoSave() {
                        if (this.saving) return;
                        this.saving = true;
                        try {
                            await this.saveResume();
                        } catch (error) {
                            console.error('Auto-save error:', error);
                        } finally {
                            this.saving = false;
                        }
                    },

                    async saveResume() {
                        const sectionsArray = Object.values(this.sections).map(s => ({
                            id: s.id || null,
                            section_type: s.section_type || s.section_type,
                            section_data: s.section_data,
                            sort_order: s.sort_order || 0,
                            is_visible: s.is_visible !== false
                        }));

                        const response = await fetch(`/candidate/resume/builder/${this.resumeId}/save`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                            },
                            body: JSON.stringify({
                                sections: sectionsArray,
                                title: '<?= htmlspecialchars($resume->attributes['title'] ?? 'My Resume', ENT_QUOTES) ?>'
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.strengthScore = data.strength_score || 0;
                            return true;
                        }
                        return false;
                    },

                    async exportPDF() {
                        if (this.exporting) return;
                        this.exporting = true;
                        try {
                            await this.autoSave();
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/export-pdf`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                                }
                            });
                            const data = await response.json();
                            if (data.success && data.pdf_url) {
                                window.open(data.pdf_url, '_blank');
                            } else {
                                alert(data.error || 'Failed to generate PDF');
                            }
                        } catch (e) {
                            console.error(e);
                            alert('An error occurred while generating PDF.');
                        } finally {
                            this.exporting = false;
                        }
                    },

                    nextStep() {
                        const nextIndex = this.currentStepIndex + 1;
                        if (nextIndex < this.steps.length) {
                            this.autoSave();
                            window.location.href = `/candidate/resume/builder/<?= (int)($resume->attributes['id'] ?? 0) ?>/wizard?step=${this.steps[nextIndex]}`;
                        }
                    },

                    previousStep() {
                        const prevIndex = this.currentStepIndex - 1;
                        if (prevIndex >= 0) {
                            window.location.href = `/candidate/resume/builder/<?= (int)($resume->attributes['id'] ?? 0) ?>/wizard?step=${this.steps[prevIndex]}`;
                        }
                    },

                    // AI Features
                    async generateJobSummary() {
                        try {
                            const summaryEl = this.$el.querySelector('[x-data*=\"generatingAI\"]');
                            if (summaryEl) summaryEl.__x.$data.generatingAI = true;
                            
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/generate-summary`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                                }
                            });
                            
                            if (!response.ok) {
                                throw new Error('AI generate summary failed');
                            }
                            
                            const result = await this.parseJson(response);
                            if (result.success && result.summary) {
                                const normalized = String(result.summary)
                                    .replace(/\\s*\\n+\\s*/g, ' ')
                                    .replace(/\\s{2,}/g, ' ')
                                    .trim();
                                this.getSection('summary').section_data.content.text = normalized;
                                await this.autoSave();
                            }
                        } catch (e) {
                            console.error('Generate summary error:', e);
                        } finally {
                            const summaryEl = this.$el.querySelector('[x-data*=\"generatingAI\"]');
                            if (summaryEl) summaryEl.__x.$data.generatingAI = false;
                        }
                    },
                    async generateAISummary() {
                        if (this.$el.querySelector('[x-data*="generatingAI"]')) {
                            this.$el.querySelector('[x-data*="generatingAI"]').__x.$data.generatingAI = true;
                        }
                        try {
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/generate-summary`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                                }
                            });
                            const data = await response.json();
                            if (data.success && data.summary) {
                                this.getSection('summary').section_data.content.text = data.summary;
                                await this.autoSave();
                                alert('AI summary generated successfully!');
                            } else {
                                alert('Failed to generate summary. Please try again.');
                            }
                        } catch (e) {
                            console.error('AI Summary Error:', e);
                            alert('An error occurred. Please try again.');
                        } finally {
                            if (this.$el.querySelector('[x-data*="generatingAI"]')) {
                                this.$el.querySelector('[x-data*="generatingAI"]').__x.$data.generatingAI = false;
                            }
                        }
                    },

                    async optimizeAIContent() {
                        const summaryText = this.getSection('summary').section_data.content.text;
                        if (!summaryText) {
                            alert('Please enter some content first.');
                            return;
                        }
                        
                        if (this.$el.querySelector('[x-data*="optimizingAI"]')) {
                            this.$el.querySelector('[x-data*="optimizingAI"]').__x.$data.optimizingAI = true;
                        }
                        try {
                            const response = await fetch(`/candidate/resume/builder/${this.resumeId}/ai/enhance-description`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                                },
                                body: JSON.stringify({
                                    job_title: '',
                                    company: '',
                                    description: summaryText,
                                    skills: []
                                })
                            });
                            const data = await response.json();
                            if (data.success && data.description) {
                                this.getSection('summary').section_data.content.text = data.description;
                                await this.autoSave();
                                alert('Content optimized successfully!');
                            } else {
                                alert('Failed to optimize content. Please try again.');
                            }
                        } catch (e) {
                            console.error('AI Optimization Error:', e);
                            alert('An error occurred. Please try again.');
                        } finally {
                            if (this.$el.querySelector('[x-data*="optimizingAI"]')) {
                                this.$el.querySelector('[x-data*="optimizingAI"]').__x.$data.optimizingAI = false;
                            }
                        }
                    }
                };
            }
        </script>
    </div>
    <?php include __DIR__ . '/../../../include/footer.php'; ?>
</body>
</html>
