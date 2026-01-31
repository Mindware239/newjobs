<div x-data="importWizard()" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Import Candidates</h1>
        <p class="mt-1 text-sm text-gray-500">Bulk import candidates from CSV/Excel file into the system.</p>
    </div>

    <!-- Steps Indicator -->
    <div class="mb-10">
        <div class="relative">
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-100 rounded-full -z-10"></div>
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-600 rounded-full -z-10 transition-all duration-500 ease-in-out" :style="'width: ' + ((step - 1) / (steps.length - 1) * 100) + '%'"></div>
            
            <div class="flex items-center justify-between w-full">
                <template x-for="(s, index) in steps" :key="index">
                    <div class="flex flex-col items-center group cursor-default">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-4 transition-all duration-300"
                             :class="step > index + 1 ? 'bg-blue-600 border-blue-600 text-white' : (step === index + 1 ? 'bg-white border-blue-600 text-blue-600 shadow-md transform scale-110' : 'bg-white border-gray-200 text-gray-400')">
                            <span x-show="step <= index + 1" x-text="index + 1"></span>
                            <svg x-show="step > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-xs font-semibold uppercase tracking-wider transition-colors duration-300" 
                              :class="step >= index + 1 ? 'text-blue-900' : 'text-gray-400'"
                              x-text="s"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Step 1: Upload -->
    <div x-show="step === 1" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-900">Step 1: Upload File</h2>
            <p class="text-sm text-gray-500 mt-1">Select or drag and drop your CSV file containing candidate data.</p>
        </div>
        
        <div class="p-8">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-blue-500 hover:bg-blue-50/30 transition-all duration-200"
                 @dragover.prevent="dragover = true"
                 @dragleave.prevent="dragover = false"
                 @drop.prevent="handleDrop($event)"
                 :class="{ 'border-blue-500 bg-blue-50': dragover }">
                
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900">Upload CSV File</h3>
                <p class="mt-1 text-sm text-gray-500 mb-6">Drag and drop your file here, or click to browse</p>
                
                <div>
                    <label for="file-upload" class="cursor-pointer inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Select CSV File
                        <input id="file-upload" name="file-upload" type="file" class="sr-only" accept=".csv" @change="handleFileSelect">
                    </label>
                </div>
                <p class="mt-4 text-xs text-gray-400">Supported format: .csv (Max 10MB)</p>
            </div>

            <div class="mt-8 flex items-center justify-between bg-gray-50 rounded-lg p-4 border border-gray-100">
                <div class="flex items-center">
                    <div class="bg-white p-2 rounded border border-gray-200 mr-3">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Need a starting point?</h4>
                        <p class="text-xs text-gray-500">Download our sample template to ensure correct formatting.</p>
                    </div>
                </div>
                <a href="#" @click.prevent="downloadTemplate" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center hover:underline">
                    Download Template
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Step 2: Preview -->
    <div x-show="step === 2" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Step 2: Preview Data</h2>
                <p class="text-sm text-gray-500 mt-1">Review the first 5 rows of <span class="font-medium text-gray-900" x-text="fileName"></span></p>
            </div>
            <div class="text-xs font-mono bg-gray-100 px-3 py-1 rounded text-gray-600 border border-gray-200">
                UTF-8 Encoding
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <template x-for="(header, index) in previewHeaders" :key="index">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap" x-text="header"></th>
                        </template>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(row, rowIndex) in previewRows" :key="rowIndex">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="cell"></td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
            <button @click="step = 1" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Upload
            </button>
            <button @click="confirmUpload" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Proceed to Confirmation
                <svg class="ml-2 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Step 3: Confirm -->
    <div x-show="step === 3" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-900">Step 3: Confirm Import</h2>
            <p class="text-sm text-gray-500 mt-1">Configure import settings and finalize.</p>
        </div>
        
        <div class="p-8">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Ready to Import</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>You are about to import data from <span class="font-semibold" x-text="fileName"></span>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="send_emails" type="checkbox" x-model="sendEmails" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded transition-colors">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="send_emails" class="font-medium text-gray-700">Send verification emails</label>
                        <p class="text-gray-500">If checked, all imported candidates will receive an email to verify their account.</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Required Columns</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Ensure your CSV contains these headers: <code class="bg-yellow-100 px-1 py-0.5 rounded font-mono text-yellow-900">name, email, phone, location, skills, category</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
            <button @click="step = 2" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Preview
            </button>
            <button @click="startImport" :disabled="isProcessing" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="isProcessing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isProcessing ? 'Processing...' : 'Confirm & Start Import'"></span>
            </button>
        </div>
    </div>

    <!-- Step 4: Result -->
    <div x-show="step === 4" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden text-center p-12">
        
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 animate-pulse">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Import Started Successfully!</h2>
        <p class="text-gray-600 mb-8 max-w-lg mx-auto text-lg">
            The import process has been queued. You can monitor the progress in the system logs or wait for the completion email.
        </p>
        
        <div class="bg-gray-50 rounded-xl p-6 max-w-sm mx-auto mb-10 border border-gray-100">
            <div class="text-sm text-gray-500 mb-2 uppercase tracking-wide font-semibold">Batch Reference ID</div>
            <div class="font-mono text-2xl font-bold text-gray-900 tracking-wider" x-text="batchId"></div>
        </div>

        <div class="flex justify-center space-x-4">
            <a href="/admin/candidates" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                Return to Candidates
            </a>
            <button @click="location.reload()" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors">
                Import Another File
            </button>
        </div>
    </div>
</div>

<script>
function importWizard() {
    return {
        step: 1,
        steps: ['Upload', 'Preview', 'Confirm', 'Result'],
        dragover: false,
        file: null,
        fileName: '',
        previewHeaders: [],
        previewRows: [],
        filePath: '',
        sendEmails: false,
        isProcessing: false,
        batchId: '',

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) this.processFile(file);
        },

        handleDrop(e) {
            this.dragover = false;
            const file = e.dataTransfer.files[0];
            if (file) this.processFile(file);
        },

        processFile(file) {
            if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
                alert('Please upload a CSV file.');
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('File size exceeds 10MB limit.');
                return;
            }
            this.file = file;
            this.fileName = file.name;
            this.uploadFile();
        },

        uploadFile() {
            const formData = new FormData();
            formData.append('file', this.file);

            fetch('/admin/candidates/import/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    this.filePath = data.filepath;
                    this.previewHeaders = data.header;
                    this.previewRows = data.preview;
                    this.step = 2;
                }
            })
            .catch(err => {
                alert('Upload failed');
                console.error(err);
            });
        },

        downloadTemplate() {
            const csvContent = "data:text/csv;charset=utf-8,name,email,phone,location,category,skills\nJohn Doe,john@example.com,1234567890,New York,Developer,\"PHP, Laravel\"";
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "candidate_import_template.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        confirmUpload() {
            this.step = 3;
        },

        startImport() {
            this.isProcessing = true;
            fetch('/admin/candidates/import/confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    filepath: this.filePath,
                    send_email: this.sendEmails
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isProcessing = false;
                if (data.error) {
                    alert(data.error);
                } else {
                    this.batchId = data.batch_id;
                    this.step = 4;
                }
            })
            .catch(err => {
                this.isProcessing = false;
                alert('Import failed to start');
                console.error(err);
            });
        }
    }
}
</script>
