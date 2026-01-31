<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white shadow-xl rounded-2xl p-10 text-center max-w-md w-full transform transition-all hover:scale-[1.02]">
        <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 mt-6 mb-2">Review Submitted!</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Thank you for your feedback. Your review helps us maintain high quality standards and helps other candidates make informed decisions.
        </p>
        
        <div class="space-y-4 w-full max-w-md">
            <a href="/candidate/reviews" class="inline-flex items-center justify-center w-full bg-blue-600 text-white font-bold px-6 py-4 rounded-xl hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                Back to My Reviews
            </a>
        </div>
    </div>
<?php include __DIR__ . '/../../include/footer.php'; ?>
</body>
</html>
