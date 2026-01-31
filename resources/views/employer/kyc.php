<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $documents
 * @var string $kyc_status
 */
?>

<h1 class="text-3xl font-bold text-gray-900 mb-6">Documents Verification</h1>

<!-- KYC Status Card -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Verification Status</h2>
            <div class="flex items-center space-x-3">
                <?php if ($kyc_status === 'approved'): ?>
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        ✓ Approved
                    </span>
                <?php elseif ($kyc_status === 'pending'): ?>
                    <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        ⏳ Pending Review
                    </span>
                <?php elseif ($kyc_status === 'rejected'): ?>
                    <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        ✗ Rejected
                    </span>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                        Not Submitted
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($kyc_status === 'not_submitted' || $kyc_status === 'rejected'): ?>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Upload Documents
            </button>
        <?php endif; ?>
    </div>
    
    <?php if ($kyc_status === 'pending'): ?>
        <p class="text-sm text-gray-600 mt-4">
            Your KYC documents are under review. You will be notified once the review is complete.
        </p>
    <?php elseif ($kyc_status === 'rejected'): ?>
        <p class="text-sm text-red-600 mt-4">
            Your KYC documents were rejected. Please upload new documents to continue.
        </p>
    <?php endif; ?>
</div>

<!-- Required Documents Info -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <h3 class="text-sm font-semibold text-blue-900 mb-2">Required Documents</h3>
    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
        <li>Business License / Registration Certificate</li>
        <li>Tax ID / GST Certificate</li>
        <li>Address Proof (Utility Bill / Rent Agreement)</li>
    </ul>
</div>

<!-- Uploaded Documents -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Uploaded Documents</h2>
    
    <?php if (empty($documents)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents uploaded</h3>
            <p class="mt-1 text-sm text-gray-500">Upload your KYC documents to get verified.</p>
            <div class="mt-6">
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Upload Documents
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($documents as $doc): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-gray-900 capitalize">
                                <?= str_replace('_', ' ', htmlspecialchars($doc['doc_type'])) ?>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                <?= htmlspecialchars($doc['file_name']) ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Uploaded: <?= date('M d, Y', strtotime($doc['uploaded_at'])) ?>
                            </p>
                            <div class="mt-2">
                                <?php if ($doc['review_status'] === 'approved'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Approved
                                    </span>
                                <?php elseif ($doc['review_status'] === 'rejected'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                        ✗ Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($doc['review_notes'])): ?>
                                <p class="text-xs text-gray-600 mt-2">
                                    <strong>Note:</strong> <?= htmlspecialchars($doc['review_notes']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <a href="<?= htmlspecialchars($doc['file_url']) ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload KYC Document</h3>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select name="doc_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Select document type</option>
                        <option value="business_license">Business License</option>
                        <option value="tax_id">Tax ID / GST</option>
                        <option value="address_proof">Address Proof</option>
                        <option value="director_id">Director ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">File</label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }
    
    try {
        const response = await fetch('/employer/kyc/documents', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Document uploaded successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to upload document'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>

