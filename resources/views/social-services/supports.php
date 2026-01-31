<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-800">

<!-- ================= HEADER ================= -->
<header class="border-b border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

        <!-- LOGO -->
        <a href="<?= $base ?>" class="flex items-center gap-3">
            <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12 w-auto">
        </a>

        <!-- NAV -->
        <nav class="hidden md:flex items-center gap-8 text-sm">
            <a href="<?= rtrim($base,'/') ?>/employers" class="font-medium hover:text-[#5b6bd5]">Home</a>
            <a href="<?= rtrim($base,'/') ?>/pricing" class="font-medium hover:text-[#5b6bd5]">Pricing</a>
            <a href="<?= rtrim($base,'/') ?>/aboutus" class="font-medium hover:text-[#5b6bd5]">About us</a>
            <a href="<?= rtrim($base,'/') ?>/supports" class="font-medium hover:text-[#5b6bd5]">Support</a>
            <a href="<?= rtrim($base,'/') ?>/specials" class="font-medium hover:text-[#5b6bd5]">Specials</a>
        </nav>

        <!-- RIGHT -->
        <div class="text-sm flex items-center gap-4">
            <span class="text-gray-600">Employers:</span>
            <a href="#" class="text-red-500 hover:underline">Login</a>
            <span>/</span>
            <a href="#" class="text-red-500 hover:underline">Create account</a>

            <a href="/social-services"
               class="ml-4 px-4 py-2 rounded-md text-white transition"
               style="background-color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#4a59c8'"
               onmouseout="this.style.backgroundColor='#5b6bd5'">
               Employers
            </a>
        </div>

    </div>
</header>

<!-- ================= CONTENT ================= -->
<main class="max-w-7xl mx-auto px-6 py-16">

    <h1 class="text-3xl font-semibold mb-8">
        Need help?
    </h1>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-6">
        The Mindware Infotech platform has recently been migrated to a new system.
        Changes in your user experience should be minimal.
    </p>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-6">
        However, <strong>you will see new options when logging in for the first time.</strong>
    </p>

    <ol class="max-w-4xl list-decimal pl-6 text-gray-700 space-y-3 mb-8">
        <li>
            You will be prompted to provide the email attached to your account
            in order to receive a one-time login code.
        </li>
        <li>
            Check your inbox for the login code. Please allow a few minutes for your
            email server to receive the code. If the email has not arrived, be sure
            to check your spam or junk folder.
        </li>
        <li>
            Once you’ve received your login code, you can use it to log in.
            From there, you can update your account information, including your password.
        </li>
    </ol>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-10">
        You will be able to reset your password once you log in, but please note
        that you will always have the option to log in using a one-time login code.
    </p>

    <h2 class="text-lg font-semibold mb-3">
        Still have questions? We are happy to help!
    </h2>

    <p class="max-w-4xl text-gray-700 leading-relaxed mb-4">
        For further troubleshooting, please take a look at our
        <a href="#" class="text-red-500 hover:underline">Jobseeker FAQs</a>
        and
        <a href="#" class="text-red-500 hover:underline">Employer FAQs</a>.
    </p>

    <p class="max-w-4xl text-gray-700 leading-relaxed">
        You can also reach us via email at
        <a href="mailto:hello@workforgood.org" class="text-red-500 hover:underline">
            hello@workforgood.org
        </a>.
    </p>

</main>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
        <p>© 2026 Mindware Infotech. All Rights Reserved.</p>
        <div class="flex gap-6 mt-3 md:mt-0">
            <a href="<?= $base ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
            <a href="<?= $base ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
            <a href="<?= $base ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>
