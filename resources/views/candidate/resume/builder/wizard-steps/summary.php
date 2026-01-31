<?php
$summarySection = $sectionsData['summary'] ?? null;
$summaryText = $summarySection['section_data']['content']['text'] ?? ($candidate->attributes['self_introduction'] ?? '');
?>
<div class="space-y-6" x-data="{ generatingAI: false, enhancingAI: false }">
    <div class="flex items-center justify-between mb-6">
        <p class="text-gray-600">Write a brief summary of your professional background and key strengths.</p>
        <div class="flex gap-2">
            <!-- AI Generate Summary Button -->
            <button 
                @click="generateJobSummary()"
                :disabled="loading || generatingAI"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-md">
                <svg x-show="!generatingAI" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <svg x-show="generatingAI" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="generatingAI ? 'Generating...' : '✨ AI Generate'"></span>
            </button>
            
            <!-- AI Enhance Button -->
            <button 
                @click="enhanceSummary()"
                :disabled="loading || enhancingAI || !getSection('summary').section_data.content.text"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <svg x-show="!enhancingAI" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <svg x-show="enhancingAI" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="enhancingAI ? 'Enhancing...' : '🔧 AI Enhance'"></span>
            </button>
        </div>
    </div>

    <div>
        <textarea 
            x-model="getSection('summary').section_data.content.text"
            @blur="autoSave()"
            rows="8"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Experienced professional with a proven track record in..."><?= htmlspecialchars($summaryText) ?></textarea>
        <p class="mt-2 text-sm text-gray-500"> Generate a professional summary!</p>
    </div>
</div>

