<?php
$base = $base ?? '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Grievance Redressal | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#7e3aecff', // indigo-600
                        secondary: '#eef2ff', // indigo-50
                        accent: '#6c6ed8ff', // indigo-500
                    }
                }
            }
        }
    </script>

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <style>
        /* Fix header visibility */
        header {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        html, body {
            overflow-x: hidden;
            width: 100%;
        }
        .container {
            width: 100%;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            margin-left: auto;
            margin-right: auto;
            max-width: 1280px;
        }
    </style>
</head>

<body class="bg-white text-gray-800 antialiased" x-data="{ loaded: false }" x-init="setTimeout(() => { loaded = true; AOS.init({once: true}); }, 100)">
    <?php require 'include/header.php'; ?>

    <!-- Main Content -->
    <main class="pt-32 pb-20 lg:pt-40 lg:pb-32 container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-center text-gray-900" data-aos="fade-up">Grievance Redressal</h1>

        <div class="prose max-w-4xl mx-auto text-gray-700 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <p class="mb-6">
                Mindware Infotech is committed to providing a safe and secure platform for all users. If you have any grievances or complaints regarding our services, content, or user behavior, please reach out to our Grievance Officer.
            </p>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
                <h3 class="text-xl font-semibold mb-4 text-gray-900">Grievance Officer Details</h3>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> Grievance Officer</p>
                    <p><span class="font-medium">Company:</span> Mindware Infotech</p>
                    <p><span class="font-medium">Email:</span> <a href="mailto:grievance@mindwareinfotech.com" class="text-indigo-600 hover:underline">grievance@mindwareinfotech.com</a></p>
                    <p><span class="font-medium">Address:</span> Mindware Infotech, India</p>
                </div>
            </div>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">Process for Filing a Grievance</h3>
            <p class="mb-4">To file a grievance, please send an email to the address above with the following details:</p>
            <ul class="list-disc pl-6 mb-6">
                <li>Your full name and contact information.</li>
                <li>A clear description of the grievance or complaint.</li>
                <li>Supporting evidence or documentation (if applicable).</li>
                <li>Specific URL or location on the platform where the issue occurred (if applicable).</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">Resolution Timeline</h3>
            <p class="mb-6">
                We acknowledge all grievances within 24 hours of receipt. Our team will investigate the matter and aim to provide a resolution within 15 days, in accordance with applicable laws.
            </p>
        </div>
    </main>

    <?php require 'include/footer.php'; ?>
</body>
</html>
