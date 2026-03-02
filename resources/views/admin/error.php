<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="/admin/dashboard" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900">An error occurred</h1>
        <p class="mt-2 text-sm text-gray-600">Something went wrong while processing your request.</p>
        <?php if (!empty($errorMessage)): ?>
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
                <div class="text-sm text-red-700">
                    <?= htmlspecialchars((string)$errorMessage) ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="mt-6">
            <a href="/admin/dashboard" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Go to Dashboard</a>
        </div>
    </div>
    <div class="mt-6 text-xs text-gray-500">
        <p>If the problem persists, check System Logs in Master Admin or contact support.</p>
    </div>
    <script>
        console.error('Admin error:', <?= json_encode($errorMessage ?? ''); ?>);
    </script>
</div>
