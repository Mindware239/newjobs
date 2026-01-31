<?php
$skillsSection = $sectionsData['skills'] ?? null;
$skillsItems = $skillsSection['section_data']['content']['items'] ?? [];
if (empty($skillsItems) && !empty($candidate->attributes['skills_data'])) {
    $skillsData = json_decode($candidate->attributes['skills_data'], true);
    if (is_array($skillsData) && !empty($skillsData)) {
        $skillsItems = array_map(function($skill) {
            return ['name' => $skill['name'] ?? '', 'proficiency_level' => $skill['proficiency_level'] ?? 'intermediate'];
        }, $skillsData);
    }
}
?>
<div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
        <p class="text-gray-600">List your key skills and areas of expertise.</p>
        <button @click="suggestSkills()" :disabled="loading" class="text-sm px-3 py-1.5 rounded-md bg-purple-100 text-purple-600 hover:bg-purple-200 disabled:bg-gray-200 disabled:text-gray-500">
            <span x-show="!loading">🔮 AI Suggest Skills</span>
            <span x-show="loading">Loading Suggestions...</span>
        </button>
    </div>

    <div x-show="skillSuggestions.length > 0" class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
        <h4 class="font-semibold text-purple-800 mb-2">Suggested Skills:</h4>
        <div class="flex flex-wrap gap-2">
            <template x-for="(suggestion, i) in skillSuggestions" :key="i">
                <button @click="addSkill(suggestion)" class="px-3 py-1 bg-white border border-purple-300 rounded-full text-sm text-purple-700 hover:bg-purple-100">
                    + <span x-text="suggestion"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="space-y-4">
        <template x-for="(item, index) in getSection('skills').section_data.content.items" :key="index">
            <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                <input 
                    type="text" 
                    x-model="item.name"
                    @input="autoSave()"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Skill name">
                <select 
                    x-model="item.proficiency_level"
                    @change="autoSave()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                    <option value="expert">Expert</option>
                </select>
                <button 
                    @click="getSection('skills').section_data.content.items.splice(index, 1); autoSave()"
                    class="text-red-600 hover:text-red-700">
                    ✕
                </button>
            </div>
        </template>

        <div class="flex gap-2">
            <input 
                type="text" 
                x-model="newSkill"
                @keyup.enter="if(newSkill.trim()) { addSkill(newSkill.trim()); newSkill = ''; }"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Add a skill and press Enter">
            <button 
                @click="if(newSkill.trim()) { addSkill(newSkill.trim()); newSkill = ''; }"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Add
            </button>
        </div>
    </div>
</div>

