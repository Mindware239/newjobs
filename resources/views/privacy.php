<?php
$base = $base ?? '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Privacy Policy | Mindware Infotech</title>
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
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-center text-gray-900" data-aos="fade-up">Privacy Policy</h1>

        <div class="prose max-w-4xl mx-auto text-gray-700 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <p class="mb-6">Last updated: <?= date('F d, Y') ?></p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">1. Information We Collect</h3>
            <p class="mb-4">We collect information that you provide directly to us, including:</p>
            <ul class="list-disc pl-6 mb-6">
                <li>Personal identification information (Name, email address, phone number, etc.)</li>
                <li>Professional information (Resume, work history, education)</li>
                <li>Account credentials</li>
                <li>Communications with us or other users</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">2. How We Use Your Information</h3>
            <p class="mb-4">We use the collected information to:</p>
            <ul class="list-disc pl-6 mb-6">
                <li>Provide and maintain our services</li>
                <li>Match candidates with job opportunities</li>
                <li>Notify you about changes to our service</li>
                <li>Provide customer support</li>
                <li>Monitor usage of our platform</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">3. Data Security</h3>
            <p class="mb-6">
                We value your trust in providing us your Personal Information and strive to use commercially acceptable means of protecting it. However, no method of transmission over the internet or method of electronic storage is 100% secure and reliable, and we cannot guarantee its absolute security.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">4. Third-Party Services</h3>
            <p class="mb-6">
                We may employ third-party companies and individuals due to the following reasons:
                To facilitate our Service;
                To provide the Service on our behalf;
                To perform Service-related services; or
                To assist us in analyzing how our Service is used.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">5. Contact Us</h3>
            <p class="mb-6">
                If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at <a href="mailto:gm@mindwareinfotech.com" class="text-indigo-600 hover:underline">gm@mindwareinfotech.com</a>.
            </p>
        </div>
    </main>

    <?php require 'include/footer.php'; ?>
</body>
</html>
