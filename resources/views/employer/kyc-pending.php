<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Pending - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-4">KYC Verification Pending</h1>
                <p class="text-gray-600 mb-6">
                    Your KYC documents are currently under review. You'll be able to post jobs once your documents are approved.
                </p>
                <p class="text-sm text-gray-500 mb-6">
                    Status: <span class="font-semibold text-yellow-600"><?= $employer->kyc_status ?? 'pending' ?></span>
                </p>
                <div class="space-y-3">
                    <a href="/employer/kyc" class="block w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        View KYC Status
                    </a>
                    <a href="/employer/dashboard" class="block w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

