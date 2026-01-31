<?php
$eduSection = $sectionsData['education'] ?? null;
$eduItems = $eduSection['section_data']['content']['items'] ?? [];
if (empty($eduItems) && !empty($candidate->attributes['education_data'])) {
    $eduData = json_decode($candidate->attributes['education_data'], true);
    if (is_array($eduData) && !empty($eduData)) {
        $eduItems = $eduData;
    }
}
?>
<div class="space-y-6">
    <p class="text-gray-600 mb-6">Add your educational background.</p>

    <div class="space-y-4">
        <template x-for="(item, index) in (getSection('education').section_data.content.items || [])" :key="index">
            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Degree *</label>
                        <input 
                            type="text" 
                            x-model="getSection('education').section_data.content.items[index].degree"
                            @input="autoSave()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Bachelor's Degree">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Field of Study</label>
                        <input 
                            type="text" 
                            x-model="getSection('education').section_data.content.items[index].field_of_study"
                            @input="autoSave()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Computer Science">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Institution *</label>
                        <input 
                            type="text" 
                            x-model="getSection('education').section_data.content.items[index].institution"
                            @input="autoSave()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="University Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Grade/GPA</label>
                        <input 
                            type="text" 
                            x-model="getSection('education').section_data.content.items[index].grade"
                            @input="autoSave()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="3.8 or A">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input 
                                type="date" 
                                x-model="getSection('education').section_data.content.items[index].start_date"
                                @input="autoSave()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input 
                                type="date" 
                                x-model="getSection('education').section_data.content.items[index].end_date"
                                :disabled="getSection('education').section_data.content.items[index].is_current"
                                @input="autoSave()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100">
                        </div>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                x-model="getSection('education').section_data.content.items[index].is_current"
                                @change="autoSave()"
                                class="mr-2 w-4 h-4 text-blue-600">
                            <span class="text-sm text-gray-700">Currently studying</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea 
                        x-model="getSection('education').section_data.content.items[index].description"
                        @input="autoSave()"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Additional details..."></textarea>
                </div>
                <button 
                    @click="(getSection('education').section_data.content.items || []).splice(index, 1); autoSave()"
                    class="mt-4 text-red-600 hover:text-red-700 text-sm">
                    Remove
                </button>
            </div>
        </template>

        <button 
            @click="const newItem = {degree: '', field_of_study: '', institution: '', start_date: '', end_date: '', is_current: false, grade: '', description: ''}; if (!getSection('education').section_data.content.items) { getSection('education').section_data.content.items = []; } getSection('education').section_data.content.items.push(newItem);"
            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600">
            + Add Education
        </button>
    </div>
</div>

