<?php
/**
 * Resume Builder - Editor
 * 
 * @var \App\Models\Candidate $candidate
 * @var \App\Models\Resume $resume
 * @var \App\Models\ResumeTemplate $template
 * @var array $sections
 * @var bool $isPremium
 */

$base = $base ?? '/';
?>
<?php
/**
 * Resume Builder - Editor
 * Uses candidate/layout which includes header
 */
?>
<style>
    [x-cloak] { display: none !important; }
    .resume-preview {
        background: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        min-height: 297mm;
        width: 210mm;
        padding: 20mm;
        margin: 0 auto;
    }
    @media print {
        .no-print { display: none; }
    }
</style>
<?php
// Prepare data for Alpine.js
$editorData = [
    'resumeId' => (int)$resume->getId(),
    'sections' => $sections,
    'template' => $template ? [
        'id' => (int)($template->attributes['id'] ?? 0),
        'name' => $template->attributes['name'] ?? '',
        'schema' => $template->getSchema()
    ] : null,
    'isPremium' => (bool)$isPremium,
    'strengthScore' => (int)($resume->attributes['strength_score'] ?? 0)
];
$editorDataJson = json_encode($editorData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<?php 
$editorJsonSafe = json_encode($editorData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<div class="min-h-screen" x-data="resumeEditor(JSON.parse($el.dataset.editor))" data-editor='<?= $editorJsonSafe ?>'>
        
        <!-- Top Toolbar -->
        <div class="bg-white border-b sticky top-[64px] z-40 no-print">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="/candidate/resume/builder" class="text-gray-600 hover:text-gray-900">
                            ← Back to Templates
                        </a>
                        <h1 class="text-lg font-semibold text-gray-900">
                            <?= htmlspecialchars($resume->attributes['title'] ?? 'My Resume') ?>
                        </h1>
                        <span class="text-sm text-gray-500">
                            Strength: <span x-text="strengthScore"></span>%
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="saveResume()" 
                                :disabled="saving"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!saving">💾 Save</span>
                            <span x-show="saving">Saving...</span>
                        </button>
                        <?php if (!empty($resume->attributes['pdf_url'] ?? '')): ?>
                        <a href="<?= htmlspecialchars($resume->attributes['pdf_url']) ?>" 
                           target="_blank"
                           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 inline-flex items-center gap-2">
                            📥 Download PDF
                        </a>
                        <?php endif; ?>
                        <button @click="exportPDF()" 
                                :disabled="exporting"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!exporting">📄 Generate PDF</span>
                            <span x-show="exporting">Generating...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Sidebar - Sections -->
                <div class="lg:col-span-1 no-print">
                    <div class="bg-white rounded-lg shadow-sm p-4 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Sections</h2>
                        
                        <div class="space-y-2 mb-4">
                            <template x-for="(section, index) in sections" :key="section.id || index">
                                <div class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-move"
                                     @click="activeSection = index">
                                    <input type="checkbox" 
                                           :checked="section.is_visible"
                                           @change="section.is_visible = $event.target.checked"
                                           class="rounded">
                                    <span class="flex-1 text-sm text-gray-700" x-text="getSectionName(section.section_type)"></span>
                                    <button @click="removeSection(index)" 
                                            class="text-red-500 hover:text-red-700 text-xs">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button @click="addSection()" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">
                            + Add Section
                        </button>
                    </div>
                </div>

                <!-- Center - Resume Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <div class="resume-preview">
                            <!-- Header Section -->
                            <template x-if="getSectionByType('header')">
                                <div class="mb-6 border-b-2 pb-4">
                                    <template x-if="editingSection === 'header'">
                                        <div>
                                            <input type="text" 
                                                   x-model="getSectionByType('header').section_data.content.full_name"
                                                   placeholder="Full Name"
                                                   class="text-3xl font-bold mb-2 border-0 border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none w-full">
                                            <input type="text" 
                                                   x-model="getSectionByType('header').section_data.content.email"
                                                   placeholder="Email"
                                                   class="text-sm text-gray-600 mb-1 border-0 border-b border-gray-300 focus:border-blue-500 focus:outline-none w-full">
                                            <input type="text" 
                                                   x-model="getSectionByType('header').section_data.content.phone"
                                                   placeholder="Phone"
                                                   class="text-sm text-gray-600 mb-1 border-0 border-b border-gray-300 focus:border-blue-500 focus:outline-none w-full">
                                            <input type="text" 
                                                   x-model="getSectionByType('header').section_data.content.location"
                                                   placeholder="Location"
                                                   class="text-sm text-gray-600 border-0 border-b border-gray-300 focus:border-blue-500 focus:outline-none w-full">
                                        </div>
                                    </template>
                                    <template x-if="editingSection !== 'header'">
                                        <div>
                                            <h1 class="text-3xl font-bold mb-2 cursor-pointer hover:text-blue-600" 
                                                @click="editingSection = 'header'"
                                                x-text="getSectionByType('header').section_data.content.full_name || 'Your Name'"></h1>
                                            <p class="text-sm text-gray-600 cursor-pointer hover:text-blue-600" 
                                               @click="editingSection = 'header'">
                                                <span x-text="getSectionByType('header').section_data.content.email || 'email@example.com'"></span> | 
                                                <span x-text="getSectionByType('header').section_data.content.phone || 'Phone'"></span> | 
                                                <span x-text="getSectionByType('header').section_data.content.location || 'Location'"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Summary Section -->
                            <template x-if="getSectionByType('summary')">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold mb-3 border-b-2 pb-2">Professional Summary</h2>
                                    <template x-if="editingSection === 'summary'">
                                        <textarea x-model="getSectionByType('summary').section_data.content.text"
                                                  placeholder="Write your professional summary..."
                                                  class="w-full border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                                                  rows="4"></textarea>
                                    </template>
                                    <template x-if="editingSection !== 'summary'">
                                        <p class="text-gray-700 cursor-pointer hover:text-blue-600" 
                                           @click="editingSection = 'summary'"
                                           x-text="getSectionByType('summary').section_data.content.text || 'Click to add your professional summary...'"></p>
                                    </template>
                                </div>
                            </template>

                            <!-- Experience Section -->
                            <template x-if="getSectionByType('experience')">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold mb-3 border-b-2 pb-2 cursor-pointer hover:text-blue-600" 
                                        @click="editingSection = editingSection === 'experience' ? null : 'experience'">
                                        Work Experience
                                    </h2>
                                    <template x-if="editingSection === 'experience'">
                                        <div class="space-y-4">
                                            <template x-for="(item, idx) in getSectionByType('experience').section_data.content.items" :key="idx">
                                                <div class="border border-gray-300 rounded p-4 mb-4">
                                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Job Title</label>
                                                            <input type="text" x-model="item.job_title" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Company</label>
                                                            <input type="text" x-model="item.company_name" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                                                            <input type="text" x-model="item.location" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                                            <input type="date" x-model="item.start_date" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                                            <input type="date" x-model="item.end_date" 
                                                                   :disabled="item.is_current"
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div class="flex items-end">
                                                            <label class="flex items-center">
                                                                <input type="checkbox" x-model="item.is_current" class="mr-2">
                                                                <span class="text-xs text-gray-700">Current Job</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                        <textarea x-model="item.description" rows="3"
                                                                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                                                    </div>
                                                    <button @click="getSectionByType('experience').section_data.content.items.splice(idx, 1)" 
                                                            class="mt-2 text-xs text-red-600 hover:text-red-700">
                                                        Remove
                                                    </button>
                                                </div>
                                            </template>
                                            <button @click="addExperience()" 
                                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                                + Add Experience
                                            </button>
                                            <button @click="editingSection = null" 
                                                    class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                                Done
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editingSection !== 'experience'">
                                        <div>
                                            <template x-for="(item, idx) in getSectionByType('experience').section_data.content.items" :key="idx">
                                                <div class="mb-4 p-3 hover:bg-gray-50 rounded cursor-pointer" @click="editingSection = 'experience'">
                                                    <div class="flex justify-between items-start mb-1">
                                                        <div>
                                                            <h3 class="font-bold" x-text="item.job_title || 'Job Title'"></h3>
                                                            <p class="text-sm text-gray-600">
                                                                <span x-text="item.company_name || 'Company'"></span> | 
                                                                <span x-text="item.location || 'Location'"></span>
                                                            </p>
                                                        </div>
                                                        <span class="text-sm text-gray-500" 
                                                              x-text="formatDateRange(item.start_date, item.end_date, item.is_current)"></span>
                                                    </div>
                                                    <p class="text-sm text-gray-700" x-text="item.description || ''"></p>
                                                </div>
                                            </template>
                                            <button @click="editingSection = 'experience'" 
                                                    class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                                                + Add Experience
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Education Section -->
                            <template x-if="getSectionByType('education')">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold mb-3 border-b-2 pb-2 cursor-pointer hover:text-blue-600" 
                                        @click="editingSection = editingSection === 'education' ? null : 'education'">
                                        Education
                                    </h2>
                                    <template x-if="editingSection === 'education'">
                                        <div class="space-y-4">
                                            <template x-for="(item, idx) in getSectionByType('education').section_data.content.items" :key="idx">
                                                <div class="border border-gray-300 rounded p-4 mb-4">
                                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Degree</label>
                                                            <input type="text" x-model="item.degree" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Field of Study</label>
                                                            <input type="text" x-model="item.field_of_study" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Institution</label>
                                                            <input type="text" x-model="item.institution" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                                            <input type="date" x-model="item.start_date" 
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                                            <input type="date" x-model="item.end_date" 
                                                                   :disabled="item.is_current"
                                                                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div class="flex items-end">
                                                            <label class="flex items-center">
                                                                <input type="checkbox" x-model="item.is_current" class="mr-2">
                                                                <span class="text-xs text-gray-700">Currently Studying</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <button @click="getSectionByType('education').section_data.content.items.splice(idx, 1)" 
                                                            class="text-xs text-red-600 hover:text-red-700">
                                                        Remove
                                                    </button>
                                                </div>
                                            </template>
                                            <button @click="addEducation()" 
                                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                                + Add Education
                                            </button>
                                            <button @click="editingSection = null" 
                                                    class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                                Done
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editingSection !== 'education'">
                                        <div>
                                            <template x-for="(item, idx) in getSectionByType('education').section_data.content.items" :key="idx">
                                                <div class="mb-4 p-3 hover:bg-gray-50 rounded cursor-pointer" @click="editingSection = 'education'">
                                                    <h3 class="font-bold" 
                                                        x-text="(item.degree || 'Degree') + ' - ' + (item.field_of_study || 'Field')"></h3>
                                                    <p class="text-sm text-gray-600">
                                                        <span x-text="item.institution || 'Institution'"></span> | 
                                                        <span x-text="formatDateRange(item.start_date, item.end_date, item.is_current)"></span>
                                                    </p>
                                                </div>
                                            </template>
                                            <button @click="editingSection = 'education'" 
                                                    class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                                                + Add Education
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Skills Section -->
                            <template x-if="getSectionByType('skills')">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold mb-3 border-b-2 pb-2 cursor-pointer hover:text-blue-600" 
                                        @click="editingSection = editingSection === 'skills' ? null : 'skills'">
                                        Skills
                                    </h2>
                                    <template x-if="editingSection === 'skills'">
                                        <div>
                                            <div class="flex flex-wrap gap-2 mb-4">
                                                <template x-for="(item, idx) in getSectionByType('skills').section_data.content.items" :key="idx">
                                                    <div class="bg-blue-600 text-white px-3 py-1 rounded text-sm flex items-center gap-2">
                                                        <span x-text="item.name || item"></span>
                                                        <button @click="getSectionByType('skills').section_data.content.items.splice(idx, 1)" 
                                                                class="text-white hover:text-red-200">×</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex gap-2">
                                                <input type="text" 
                                                       x-model="newSkill" 
                                                       @keyup.enter="addSkill()"
                                                       placeholder="Enter skill name"
                                                       class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">
                                                <button @click="addSkill()" 
                                                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                                    Add
                                                </button>
                                            </div>
                                            <button @click="editingSection = null" 
                                                    class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                                Done
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editingSection !== 'skills'">
                                        <div>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="(item, idx) in getSectionByType('skills').section_data.content.items" :key="idx">
                                                    <span class="bg-blue-600 text-white px-3 py-1 rounded text-sm cursor-pointer hover:bg-blue-700"
                                                          @click="editingSection = 'skills'"
                                                          x-text="item.name || item"></span>
                                                </template>
                                                <button @click="editingSection = 'skills'" 
                                                        class="text-blue-600 hover:text-blue-700 text-sm border border-blue-600 px-3 py-1 rounded">
                                                    + Add Skill
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 text-center text-sm text-gray-500 no-print">
                        <p>Click on any section to edit. Changes are saved automatically.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resumeEditor(data) {
            // Ensure data is properly structured
            if (!data) data = {};
            if (!data.sections) data.sections = [];
            
            return {
                resumeId: data.resumeId || 0,
                sections: Array.isArray(data.sections) ? data.sections : [],
                template: data.template || null,
                isPremium: data.isPremium || false,
                activeSection: 0,
                editingSection: null,
                saving: false,
                exporting: false,
                strengthScore: data.strengthScore || 0,
                newSkill: '',
                init() {
                    if (!this.sections || this.sections.length === 0) {
                        this.sections = [
                            {
                                id: null,
                                section_type: 'header',
                                section_data: { content: { full_name: '', email: '', phone: '', location: '' } },
                                sort_order: 0,
                                is_visible: true
                            },
                            {
                                id: null,
                                section_type: 'summary',
                                section_data: { content: { text: '' } },
                                sort_order: 1,
                                is_visible: true
                            },
                            {
                                id: null,
                                section_type: 'skills',
                                section_data: { content: { items: [] } },
                                sort_order: 2,
                                is_visible: true
                            },
                            {
                                id: null,
                                section_type: 'experience',
                                section_data: { content: { items: [] } },
                                sort_order: 3,
                                is_visible: true
                            },
                            {
                                id: null,
                                section_type: 'education',
                                section_data: { content: { items: [] } },
                                sort_order: 4,
                                is_visible: true
                            }
                        ];
                    }
                },

                getSectionByType(type) {
                    const section = this.sections.find(s => s.section_type === type);
                    if (!section) return null;
                    // Ensure section_data structure exists
                    if (!section.section_data) {
                        section.section_data = { content: {} };
                    }
                    if (!section.section_data.content) {
                        section.section_data.content = {};
                    }
                    // For sections with items array, ensure it exists
                    if (['experience', 'education', 'skills'].includes(type)) {
                        if (!section.section_data.content.items) {
                            section.section_data.content.items = [];
                        }
                    }
                    return section;
                },

                getSectionName(type) {
                    const names = {
                        'header': 'Header',
                        'summary': 'Summary',
                        'experience': 'Experience',
                        'education': 'Education',
                        'skills': 'Skills',
                        'languages': 'Languages',
                        'certifications': 'Certifications',
                        'projects': 'Projects',
                        'achievements': 'Achievements',
                        'references': 'References'
                    };
                    return names[type] || type;
                },

                formatDateRange(start, end, isCurrent) {
                    if (!start) return '';
                    const startFormatted = new Date(start).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    const endFormatted = isCurrent ? 'Present' : (end ? new Date(end).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : '');
                    return startFormatted + ' - ' + endFormatted;
                },

                addSection() {
                    // Simple implementation - can be enhanced with modal
                    const type = prompt('Section type (header, summary, experience, education, skills):');
                    if (type && ['header', 'summary', 'experience', 'education', 'skills'].includes(type)) {
                        this.sections.push({
                            id: null,
                            section_type: type,
                            section_data: { content: {} },
                            sort_order: this.sections.length,
                            is_visible: true
                        });
                    }
                },

                removeSection(index) {
                    if (confirm('Remove this section?')) {
                        this.sections.splice(index, 1);
                    }
                },

                addExperience() {
                    const expSection = this.getSectionByType('experience');
                    if (expSection) {
                        if (!expSection.section_data.content.items) {
                            expSection.section_data.content.items = [];
                        }
                        expSection.section_data.content.items.push({
                            job_title: '',
                            company_name: '',
                            location: '',
                            start_date: '',
                            end_date: '',
                            is_current: false,
                            description: ''
                        });
                    }
                },

                addEducation() {
                    const eduSection = this.getSectionByType('education');
                    if (eduSection) {
                        if (!eduSection.section_data.content.items) {
                            eduSection.section_data.content.items = [];
                        }
                        eduSection.section_data.content.items.push({
                            degree: '',
                            field_of_study: '',
                            institution: '',
                            start_date: '',
                            end_date: '',
                            is_current: false,
                            grade: ''
                        });
                    }
                },

                addSkill() {
                    if (!this.newSkill || !this.newSkill.trim()) return;
                    const skillsSection = this.getSectionByType('skills');
                    if (skillsSection) {
                        if (!skillsSection.section_data.content.items) {
                            skillsSection.section_data.content.items = [];
                        }
                        skillsSection.section_data.content.items.push({
                            name: this.newSkill.trim()
                        });
                        this.newSkill = '';
                    }
                },

                async saveResume() {
                    this.saving = true;
                    try {
                        const response = await fetch(`/candidate/resume/builder/${this.resumeId}/save`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                sections: this.sections,
                                title: <?= json_encode($resume->attributes['title'] ?? 'My Resume', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.strengthScore = data.strength_score || 0;
                            // Show success message (can use toast notification)
                            console.log('Saved successfully');
                        } else {
                            alert(data.error || 'Failed to save resume');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.saving = false;
                    }
                },

                async exportPDF() {
                    this.exporting = true;
                    try {
                        // First save, then export
                        await this.saveResume();
                        
                        const response = await fetch(`/candidate/resume/builder/${this.resumeId}/export-pdf`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        });

                        const data = await response.json();
                        if (data.success && data.pdf_url) {
                            // Show success message and open PDF
                            alert('PDF generated successfully! Opening in new tab...');
                            window.open(data.pdf_url, '_blank');
                            // Reload page to show download link
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert(data.error || 'Failed to generate PDF');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.exporting = false;
                    }
                }
            };
        }
    </script>
</div>

