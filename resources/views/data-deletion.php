<?php
$base = $base ?? '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Data Deletion | Jobsence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f05537',   // Jobsence brand orange
                        secondary: '#fff1ed', // brand tint
                        accent: '#FF6A3D',    // brand hover
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

    <main class="pt-32 pb-20 lg:pt-40 lg:pb-32 container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold mb-2 text-center text-gray-900" data-aos="fade-up">User Data Deletion</h1>
        <p class="text-center text-gray-600 mb-8" data-aos="fade-up">अपना Jobsence खाता और डेटा हटवाने का तरीका</p>

        <div class="prose max-w-4xl mx-auto text-gray-700 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <p class="mb-6">Last updated: October 7, 2026</p>

            <p class="mb-6">
                You can ask Jobsence to delete your account and the personal data we hold about you at any time. This applies to every account,
                including accounts created or logged in with Google, Facebook or LinkedIn.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">1. How to request deletion</h3>
            <ol class="list-decimal pl-6 mb-6">
                <li class="mb-2">Send an email to <a href="mailto:gm@jobsence.com?subject=Delete%20my%20Jobsence%20account" class="text-primary hover:underline">gm@jobsence.com</a>
                    with the subject <b>“Delete my Jobsence account”</b>.</li>
                <li class="mb-2">Send it from the email address registered with Jobsence (or the email of your Google / Facebook / LinkedIn account), and mention your
                    registered mobile number. If you have a registration number (for example from an /apply form), please include it.</li>
                <li class="mb-2">We may send a one-time code (OTP) to that email to confirm the request really comes from you.</li>
                <li>We delete your data within <b>30 days</b> of confirming the request and reply by email when it is done.</li>
            </ol>
            <p class="mb-6">Users of the Jobsence mobile app can also delete their account from <b>Settings → Delete account</b> inside the app.</p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">2. What we delete</h3>
            <ul class="list-disc pl-6 mb-6">
                <li>Your profile: name, mobile number, email, photo, address and location</li>
                <li>Resumes, documents, videos and work history you uploaded</li>
                <li>Job applications, saved jobs, alerts, messages and trusted-device / PIN settings</li>
                <li>The link to your Google, Facebook or LinkedIn login (we only ever receive your name, email and profile picture from them)</li>
                <li>Registrations made through Jobsence forms (mentoring, internships, Near Me, senior citizens and others)</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">3. What we must keep</h3>
            <p class="mb-4">Indian law requires us to keep a few records even after an account is deleted. These are kept only as long as the law requires and are not used for anything else:</p>
            <ul class="list-disc pl-6 mb-6">
                <li>Payment and GST invoice records (name, amount, date, GSTIN) – as required under the GST and income-tax laws</li>
                <li>Signed agreements (for example mentoring agreements), where a dispute or legal claim may still arise</li>
                <li>Records needed to prevent fraud or to comply with an order of a court or government authority</li>
            </ul>
            <p class="mb-6">Fees already paid are not refunded when an account is deleted (see our <a href="/refund-cancellation-policy" class="text-primary hover:underline">Refund &amp; Cancellation Policy</a>).
                Employers who already received your application or contact details before deletion hold their own copy; you can ask them directly to delete it.</p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">4. If you logged in with Facebook</h3>
            <p class="mb-6">
                You can also remove Jobsence from your Facebook account: open Facebook → <b>Settings &amp; privacy → Settings → Apps and websites</b>,
                choose <b>Jobsence</b> and click <b>Remove</b>. This stops Facebook sharing any further data with us. To delete the data Jobsence already holds,
                send the email described in section 1.
            </p>

            <h3 class="text-xl font-semibold mb-3 text-gray-900">5. Contact</h3>
            <p class="mb-6">
                Questions about your data: <a href="mailto:gm@jobsence.com" class="text-primary hover:underline">gm@jobsence.com</a>.
                You can also raise a complaint through our <a href="/grievances" class="text-primary hover:underline">Grievance Redressal</a> page.
                See our <a href="/privacy-policy" class="text-primary hover:underline">Privacy Policy</a> for how we use your information.
            </p>
        </div>
    </main>

    <?php require 'include/footer.php'; ?>
</body>
</html>
