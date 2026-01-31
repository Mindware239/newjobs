<?php
$expSection = $sectionsData['experience'] ?? null;
$expItems = $expSection['section_data']['content']['items'] ?? [];
if (empty($expItems) && !empty($candidate->attributes['experience_data'])) {
    $expData = json_decode($candidate->attributes['experience_data'], true);
    if (is_array($expData) && !empty($expData)) {
        $expItems = $expData;
    }
}

// Initialize items array if empty
if (empty($expItems)) {
    $expItems = [];
}
?>
<div class="space-y-6">
    <p class="text-gray-600 mb-8" style="font-size: 16px; line-height: 1.6;">
        Start with your most recent job first.
    </p>

    <div class="space-y-6">
        <template x-for="(item, index) in (getSection('experience').section_data.content.items || [])" :key="index">
            <div class="border rounded-lg p-6" style="border-color: var(--border-gray); background: #f9fafb;">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label block">Job Title <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            x-model="getSection('experience').section_data.content.items[index].job_title"
                            @blur="autoSave()"
                            class="form-input w-full rounded-lg"
                            placeholder="Software Developer"
                            style="font-size: 14px;">
                    </div>
                    <div>
                        <label class="form-label block">Company <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            x-model="getSection('experience').section_data.content.items[index].company_name"
                            @blur="autoSave()"
                            class="form-input w-full rounded-lg"
                            placeholder="Company Name"
                            style="font-size: 14px;">
                    </div>
                    <div>
                        <label class="form-label block">Location</label>
                        <input 
                            type="text" 
                            x-model="getSection('experience').section_data.content.items[index].location"
                            @blur="autoSave()"
                            class="form-input w-full rounded-lg"
                            placeholder="City, State"
                            style="font-size: 14px;">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="form-label block">Start Date</label>
                            <input 
                                type="month" 
                                x-model="getSection('experience').section_data.content.items[index].start_date"
                                @blur="autoSave()"
                                class="form-input w-full rounded-lg"
                                style="font-size: 14px;">
                        </div>
                        <div>
                            <label class="form-label block">End Date</label>
                            <input 
                                type="month" 
                                x-model="getSection('experience').section_data.content.items[index].end_date"
                                :disabled="getSection('experience').section_data.content.items[index].is_current"
                                @blur="autoSave()"
                                class="form-input w-full rounded-lg disabled:bg-gray-100"
                                style="font-size: 14px;">
                        </div>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                x-model="getSection('experience').section_data.content.items[index].is_current"
                                @change="autoSave()"
                                class="mr-2 w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm" style="color: var(--text-gray); font-size: 14px;">I currently work here</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="form-label block">Description</label>
                    <textarea 
                        x-model="getSection('experience').section_data.content.items[index].description"
                        @blur="autoSave()"
                        rows="4"
                        class="form-input w-full rounded-lg"
                        placeholder="Describe your responsibilities and achievements..."
                        style="font-size: 14px;"></textarea>
                </div>
                <button 
                    @click="if (getSection('experience').section_data.content.items) { getSection('experience').section_data.content.items.splice(index, 1); autoSave(); }"
                    class="mt-4 text-sm text-red-600 hover:text-red-700 transition font-medium">
                    Remove
                </button>
            </div>
        </template>

        <button 
            @click="if (!getSection('experience').section_data.content.items) { getSection('experience').section_data.content.items = []; } getSection('experience').section_data.content.items.push({job_title: '', company_name: '', location: '', start_date: '', end_date: '', is_current: false, description: ''});"
            class="w-full py-4 border-2 border-dashed rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition font-medium"
            style="border-color: var(--border-gray); font-size: 14px;">
            + Add Experience
        </button>
    </div>
</div>
