<div class="space-y-6">
    <h3 class="text-xl font-semibold mb-4">Review Your Resume</h3>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <p class="text-blue-900 font-semibold mb-2">✅ Resume Strength: <span x-text="strengthScore"></span>%</p>
        <p class="text-sm text-blue-700">Your resume looks great! You can now download it as PDF or make further edits.</p>
    </div>

    <div class="flex gap-4">
        <a href="/candidate/resume/builder/<?= $resume->getId() ?>/edit" class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-center">
            Edit in Full Editor
        </a>
        <button 
            @click="exportPDF()"
            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Generate PDF
        </button>
    </div>
</div>

