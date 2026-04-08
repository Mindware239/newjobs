<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-800">500</h1>
        <p class="text-2xl font-medium text-gray-600 mt-4">Internal Server Error</p>
        <p class="text-gray-500 mt-2"><?= htmlspecialchars($errorMessage ?? 'Something went wrong on our end.') ?></p>
        <a href="/" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-300">
            Go back home
        </a>
    </div>
</body>
</html>
