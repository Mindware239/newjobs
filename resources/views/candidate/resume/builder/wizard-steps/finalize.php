<div class="space-y-6">
    <h3 class="text-xl font-semibold mb-4">Review Your Resume</h3>
    
    <div class="bg-primary-50 border border-primary-100 rounded-lg p-6 mb-6">
        <p class="text-primary font-semibold mb-2">✅ Resume Strength: <span x-text="strengthScore"></span>%</p>
        <p class="text-sm text-primary-600">Your resume looks great! You can now download it as PDF or make further edits.</p>
    </div>

    <div class="flex gap-4">
        <a href="/candidate/resume/builder/<?= $resume->getId() ?>/edit" class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-center">
            Edit in Full Editor
        </a>
        <button 
            @click="exportPDF()"
            class="flex-1 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-600">
            Generate PDF
        </button>
    </div>
</div>












