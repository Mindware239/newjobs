<?php
$base = $base ?? '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Terms of Service | Mindware Infotech</title>
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
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-center text-gray-900" data-aos="fade-up">Terms of Service</h1>

        <div class="prose max-w-4xl mx-auto text-gray-700 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <p class="mb-6">Last updated: <?= date('F d, Y') ?></p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">1. Acceptance of Terms</h3>
            <p class="mb-6">
                By accessing and using Mindware Infotech ("the Platform"), you agree to comply with and be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">2. Services Description</h3>
            <p class="mb-6">
                Mindware Infotech provides an online platform connecting employers with job seekers. We act as an intermediary and are not a party to any employment relationship formed through our platform.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">3. User Obligations</h3>
            <p class="mb-4">You agree to:</p>
            <ul class="list-disc pl-6 mb-6">
                <li>Provide accurate and complete information in your profile and applications.</li>
                <li>Maintain the confidentiality of your account credentials.</li>
                <li>Use the platform only for lawful purposes.</li>
                <li>Not violate the intellectual property rights of others.</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">4. Employer Terms</h3>
            <p class="mb-6">
                Employers are responsible for the content of their job postings. We reserve the right to remove any job posting that violates our policies or applicable laws.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">5. Limitation of Liability</h3>
            <p class="mb-6">
                Mindware Infotech is not liable for any direct, indirect, incidental, or consequential damages arising from your use of the platform.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">6. Changes to Terms</h3>
            <p class="mb-6">
                We reserve the right to modify these terms at any time. Continued use of the platform constitutes acceptance of the modified terms.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">7. Contact Information</h3>
            <p class="mb-6">
                For questions regarding these Terms, please contact us at <a href="mailto:gm@mindwareinfotech.com" class="text-indigo-600 hover:underline">gm@mindwareinfotech.com</a>.
            </p>
        </div>
    </main>

    <?php require 'include/footer.php'; ?>
</body>
</html>
