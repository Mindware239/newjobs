<div class="space-y-8">
    <p class="text-gray-600 mb-6">Add any additional sections like certifications, projects, or achievements.</p>
    
    <!-- Projects Section -->
    <div class="p-6 border border-gray-200 rounded-lg bg-white shadow-sm">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-gray-800">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Projects
        </h3>
        
        <div class="space-y-6 mb-6">
             <template x-for="(project, index) in (getSection('additional').section_data.content.projects || [])" :key="index">
                <div class="border rounded-lg p-6 bg-gray-50 relative hover:shadow-md transition-shadow">
                    <button @click="getSection('additional').section_data.content.projects.splice(index, 1); autoSave();" class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors" title="Remove Project">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Project Title <span class="text-red-500">*</span></label>
                            <input type="text" x-model="project.title" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g. E-commerce Website">
                        </div>
                        <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <input type="text" x-model="project.role" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g. Lead Developer">
                        </div>
                        <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Project URL</label>
                            <input type="url" x-model="project.url" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="https://...">
                        </div>
                         <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="month" x-model="project.start_date" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                        </div>
                        <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="month" x-model="project.end_date" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                        </div>
                    </div>
                    <div>
                        <label class="form-label block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea x-model="project.description" @blur="autoSave()" rows="3" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="Describe the project and your contribution..."></textarea>
                    </div>
                </div>
             </template>
        </div>
        
        <button @click="if(!getSection('additional').section_data.content.projects) getSection('additional').section_data.content.projects = []; getSection('additional').section_data.content.projects.push({}); autoSave();" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition font-medium flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Project
        </button>
    </div>

    <!-- Certifications Section -->
    <div class="p-6 border border-gray-200 rounded-lg bg-white shadow-sm">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-gray-800">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Certifications
        </h3>
        
        <div class="space-y-6 mb-6">
             <template x-for="(cert, index) in (getSection('additional').section_data.content.certifications || [])" :key="index">
                <div class="border rounded-lg p-6 bg-gray-50 relative hover:shadow-md transition-shadow">
                    <button @click="getSection('additional').section_data.content.certifications.splice(index, 1); autoSave();" class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors" title="Remove Certification">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Certification Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="cert.name" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g. AWS Certified Solutions Architect">
                        </div>
                        <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Issuing Organization</label>
                            <input type="text" x-model="cert.issuer" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g. Amazon Web Services">
                        </div>
                        <div>
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
                            <input type="month" x-model="cert.date" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                        </div>
                         <div class="col-span-2">
                            <label class="form-label block text-sm font-medium text-gray-700 mb-1">Credential URL</label>
                            <input type="url" x-model="cert.url" @blur="autoSave()" class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="https://...">
                        </div>
                    </div>
                </div>
             </template>
        </div>
        
        <button @click="if(!getSection('additional').section_data.content.certifications) getSection('additional').section_data.content.certifications = []; getSection('additional').section_data.content.certifications.push({}); autoSave();" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition font-medium flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Certification
        </button>
    </div>
</div>
