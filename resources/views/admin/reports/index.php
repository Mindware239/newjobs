<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="mt-2 text-sm text-gray-600">Generate and export platform reports</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Reports</h3>
            <p class="text-sm text-gray-600 mb-4">Export user data including employers and candidates</p>
            <a href="/admin/reports/export?type=users&format=csv" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Export Users (CSV)
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Reports</h3>
            <p class="text-sm text-gray-600 mb-4">Export all job postings and their details</p>
            <a href="/admin/reports/export?type=jobs&format=csv" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Export Jobs (CSV)
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Reports</h3>
            <p class="text-sm text-gray-600 mb-4">Export payment transactions and revenue data</p>
            <a href="/admin/reports/export?type=payments&format=csv" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Export Payments (CSV)
            </a>
        </div>
    </div>
</div>

