<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Resume Builder - Template Gallery
 * Exact match to ResumeNow template selection design
 * 
 * @var \App\Models\Candidate $candidate
 * @var array $freeTemplates
 * @var array $premiumTemplates
 * @var bool $isPremium
 * @var array $resumes
 * @var bool $canCreateMore
 */
?>
<div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-8" x-data="resumeBuilder()">
        
        <!-- Header with Logo -->
        <header class="mb-12 px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center gap-3 mb-8">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-3xl font-bold text-gray-900">Mindware Infotech.</span>
                </div>
                
                <!-- Main Title -->
                <h1 class="text-4xl font-bold text-gray-900 mb-3" style="font-size: 36px; line-height: 1.2;">
                    Templates we recommend for you
                </h1>
                <p class="text-lg text-gray-600" style="font-size: 18px;">
                    You can always change your template later.
                </p>
            </div>
        </header>

        <!-- Filters Bar -->
        <div class="mb-8 px-8 flex items-center gap-4 flex-wrap">
            <span class="text-gray-700 font-medium">Filter by</span>
            <select class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>Headshot</option>
                <option>Without Headshot</option>
            </select>
            <select class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>Graphics</option>
                <option>Minimal</option>
                <option>Modern</option>
            </select>
            <select class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>Columns</option>
                <option>Single Column</option>
                <option>Two Column</option>
            </select>
            <div class="flex items-center gap-2 ml-auto">
                <span class="text-gray-700 font-medium">Colors</span>
                <div class="flex gap-2">
                    <button @click="setColor('#111827')" class="w-8 h-8 rounded-full bg-[#111827] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#111827'}"></button>
                    <button @click="setColor('#4b5563')" class="w-8 h-8 rounded-full bg-[#4b5563] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#4b5563'}"></button>
                    <button @click="setColor('#2563eb')" class="w-8 h-8 rounded-full bg-[#2563eb] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#2563eb'}"></button>
                    <button @click="setColor('#60a5fa')" class="w-8 h-8 rounded-full bg-[#60a5fa] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#60a5fa'}"></button>
                    <button @click="setColor('#14b8a6')" class="w-8 h-8 rounded-full bg-[#14b8a6] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#14b8a6'}"></button>
                    <button @click="setColor('#f97316')" class="w-8 h-8 rounded-full bg-[#f97316] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#f97316'}"></button>
                    <button @click="setColor('#ef4444')" class="w-8 h-8 rounded-full bg-[#ef4444] border-2 border-transparent hover:border-gray-400 transition" :class="{'ring-2 ring-offset-2 ring-gray-400': selectedColor === '#ef4444'}"></button>
                </div>
            </div>
        </div>

        <!-- Template Count -->
        <div class="mb-6 px-8">
            <span class="text-sm text-gray-600">All templates (<?= count($freeTemplates) + count($premiumTemplates) ?>)</span>
        </div>

        <!-- Free Templates -->
        <?php if (!empty($freeTemplates)): ?>
        <div class="mb-12 px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($freeTemplates as $template): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition-all duration-300 group cursor-pointer"
                     @click="selectTemplate(<?= (int)($template->attributes['id'] ?? 0) ?>, false)">
                    <!-- Template Preview -->
                    <div class="relative h-96 bg-white p-6 overflow-hidden">
                        <!-- Resume Preview Card -->
                        <div class="bg-white border border-gray-300 rounded shadow-sm h-full p-4 transform scale-75 origin-top-left" style="width: 133.33%; height: 133.33%; font-family: Arial, sans-serif;">
                            <!-- Header -->
                            <div class="border-b-2 pb-3 mb-4" :style="'border-color: ' + selectedColor">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" :style="'background: ' + selectedColor + '; font-size: 14px;'">
                                        DA
                                    </div>
                                    <h1 class="text-lg font-bold text-gray-900 uppercase" style="font-size: 16px;">DIYA AGARWAL</h1>
                                </div>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">New Delhi, India, 110034</p>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">d.agarwal@sample.in</p>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">+91 11 1234 5677</p>
                            </div>
                            <!-- Summary -->
                            <div class="mb-3">
                                <h2 class="text-xs font-bold mb-1 uppercase" :style="'color: ' + selectedColor + '; font-size: 9px;'">SUMMARY</h2>
                                <p class="text-xs text-gray-700 leading-relaxed" style="font-size: 8px;">Customer-focused Retail Sales professional with experience in delivering exceptional customer service and driving sales growth in fast-paced retail environments.</p>
                            </div>
                            <!-- Skills -->
                            <div class="mb-3">
                                <h2 class="text-xs font-bold mb-1 uppercase" :style="'color: ' + selectedColor + '; font-size: 9px;'">SKILLS</h2>
                                <div class="grid grid-cols-2 gap-1">
                                    <p class="text-xs text-gray-700" style="font-size: 8px;">• Cash register operation</p>
                                    <p class="text-xs text-gray-700" style="font-size: 8px;">• POS system operation</p>
                                    <p class="text-xs text-gray-700" style="font-size: 8px;">• Customer service</p>
                                    <p class="text-xs text-gray-700" style="font-size: 8px;">• Teamwork</p>
                                </div>
                            </div>
                            <!-- Experience -->
                            <div class="mb-3">
                                <h2 class="text-xs font-bold mb-1 uppercase" :style="'color: ' + selectedColor + '; font-size: 9px;'">EXPERIENCE</h2>
                                <div class="mb-2">
                                    <p class="text-xs font-semibold text-gray-900" style="font-size: 8px;">ZARA</p>
                                    <p class="text-xs text-gray-600" style="font-size: 7px;">New Delhi, India</p>
                                    <p class="text-xs text-gray-600" style="font-size: 7px;">02/2017 - Current</p>
                                </div>
                            </div>
                            <!-- Education -->
                            <div>
                                <h2 class="text-xs font-bold mb-1 uppercase" :style="'color: ' + selectedColor + '; font-size: 9px;'">EDUCATION AND TRAINING</h2>
                                <p class="text-xs text-gray-700" style="font-size: 8px;">Oxford Software Institute</p>
                            </div>
                        </div>
                        
                        <!-- Recommended Badge -->
                        <?php if (($template->attributes['category'] ?? '') === 'Professional'): ?>
                        <div class="absolute top-4 right-4 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                            Recommended
                        </div>
                        <?php endif; ?>

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-opacity-0 group-hover:bg-opacity-5 transition-all duration-300"></div>
                    </div>
                    
                    <!-- Template Info -->
                    <div class="p-5 bg-white border-t border-gray-200">
                        <button 
                            @click.stop="selectTemplate(<?= (int)($template->attributes['id'] ?? 0) ?>, false)"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Choose template
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Premium Templates -->
        <?php if (!empty($premiumTemplates)): ?>
        <div class="mb-12">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Premium Templates</h2>
                <?php if (!$isPremium): ?>
                <p class="text-gray-600 mt-1">Unlock premium templates with a Premium subscription</p>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($premiumTemplates as $template): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-blue-200 hover:shadow-xl transition-all duration-300 group <?= !$isPremium ? 'opacity-75' : 'cursor-pointer' ?>"
                     @click="<?= $isPremium ? 'selectTemplate(' . (int)($template->attributes['id'] ?? 0) . ', true)' : '' ?>">
                    <!-- Template Preview -->
                    <div class="relative h-96 bg-blue-50 p-6 overflow-hidden">
                        <!-- Resume Preview Card -->
                        <div class="bg-white border border-gray-300 rounded shadow-sm h-full p-4 transform scale-75 origin-top-left" style="width: 133.33%; height: 133.33%; font-family: Georgia, serif;">
                            <!-- Header -->
                            <div class="border-b-2 pb-3 mb-4 text-center" :style="'border-color: ' + selectedColor">
                                <h1 class="text-lg font-bold text-gray-900 uppercase mb-2" style="font-size: 16px;">DIYA AGARWAL</h1>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">d.agarwal@sample.in</p>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">+91 11 1234 5677</p>
                                <p class="text-xs text-gray-600" style="font-size: 10px;">New Delhi, India</p>
                            </div>
                            <!-- Summary -->
                            <div class="mb-3">
                                <h2 class="text-xs font-bold mb-1 uppercase text-center" :style="'color: ' + selectedColor + '; font-size: 9px;'">SUMMARY</h2>
                                <p class="text-xs text-gray-700 leading-relaxed text-center" style="font-size: 8px;">Creative professional with expertise in design and innovation.</p>
                            </div>
                        </div>
                        
                        <!-- Premium Badge -->
                        <div class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            Premium
                        </div>
                        
                        <?php if (!$isPremium): ?>
                        <!-- Lock Overlay -->
                        <div class="absolute inset-0 bg-blue-900 bg-opacity-50 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <p class="text-white font-semibold">Premium Only</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Template Info -->
                    <div class="p-5 bg-white border-t border-blue-200">
                        <?php if ($isPremium): ?>
                        <button 
                            @click.stop="selectTemplate(<?= (int)($template->attributes['id'] ?? 0) ?>, true)"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Choose template
                        </button>
                        <?php else: ?>
                        <a href="/candidate/premium/plans" 
                           class="block w-full text-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Upgrade to Unlock
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Create Modal -->
        <div x-show="showCreateModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showCreateModal = false">
            <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4" @click.stop>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Create New Resume</h2>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resume Title</label>
                    <input 
                        type="text" 
                        x-model="newResumeTitle"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="My Resume">
                </div>
                <div class="flex gap-3">
                    <button 
                        @click="showCreateModal = false"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button 
                        @click="createResume()"
                        :disabled="creating"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!creating">Create</span>
                        <span x-show="creating">Creating...</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function resumeBuilder() {
                return {
                    showCreateModal: false,
                    selectedTemplateId: null,
                    selectedIsPremium: false,
                    newResumeTitle: 'My Resume',
                    creating: false,
                    selectedColor: '#2563eb',
                    setColor(hex) {
                        this.selectedColor = hex;
                    },

                    selectTemplate(templateId, isPremium) {
                        this.selectedTemplateId = templateId;
                        this.selectedIsPremium = isPremium;
                        this.showCreateModal = true;
                    },

                    async createResume() {
                        if (this.creating) return;
                        this.creating = true;

                        try {
                            const response = await fetch('/candidate/resume/builder/create', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                                },
                                body: JSON.stringify({
                                    template_id: this.selectedTemplateId,
                                    title: this.newResumeTitle,
                                    primary_color: this.selectedColor
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                window.location.href = data.redirect || '/candidate/resume/builder';
                            } else {
                                alert(data.error || 'Failed to create resume');
                                this.creating = false;
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                            this.creating = false;
                        }
                    }
                };
            }
        </script>
    </div>
</div>
</body>
</html>
