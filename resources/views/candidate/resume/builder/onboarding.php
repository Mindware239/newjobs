<?php
/**
 * Resume Builder - Onboarding Page
 * "Here's how we get you hired" - Matching ResumeNow design
 * 
 * @var \App\Models\Candidate $candidate
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure base variable is set for header
$base = $base ?? '/';

// Ensure session variables exist for header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set variables that header.php might need
$role = $_SESSION['role'] ?? 'candidate';
$employer = null; // Not needed for candidate pages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Resume Builder - Mindware Infotech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="/css/output.css" rel="stylesheet">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-animate {
            animation: fadeInUp 0.6s ease-out;
        }
        .card-animate:nth-child(1) { animation-delay: 0.1s; }
        .card-animate:nth-child(2) { animation-delay: 0.2s; }
        .card-animate:nth-child(3) { animation-delay: 0.3s; }
        
        .step-card {
            transition: all 0.3s ease;
        }
        .step-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
        
        .btn-continue {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-white" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div class="min-h-screen flex flex-col">
        <!-- Website Header -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <?php 
            try {
                $base = $base ?? '/';
                // Ensure session is started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                // Ensure CSRF token exists
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                require __DIR__ . '/../../../include/header.php';
            } catch (\Exception $e) {
                // Fallback header if include fails
                echo '<header class="bg-white border-b px-8 py-4"><div class="max-w-7xl mx-auto flex items-center gap-3"><span class="text-xl font-bold text-gray-900">Mindware Infotech</span></div></header>';
                error_log("Header include error: " . $e->getMessage());
            }
            ?>
        </div>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-8 py-16">
            <div class="max-w-6xl w-full">
                <!-- Title -->
                <h1 class="text-5xl font-bold text-center text-gray-900 mb-16" style="font-size: 48px; line-height: 1.2;">
                    Here's how we get you hired
                </h1>

                <!-- Three Step Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- Step 1: Pick a template -->
                    <div class="step-card card-animate bg-white rounded-2xl p-8 border border-blue-200 shadow-sm" style="border-top: 4px solid #2563eb;">
                        <!-- Icon -->
                        <div class="mb-6 flex justify-center">
                            <div class="relative">
                                <svg class="w-20 h-20 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <svg class="w-8 h-8 text-blue-400 absolute -bottom-1 -right-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Title -->
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Pick a template</h2>
                        <!-- Features -->
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">ATS friendly</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Flexible layouts</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Job and industry match</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Step 2: Add content with AI -->
                    <div class="step-card card-animate bg-white rounded-2xl p-8 border border-blue-200 shadow-sm" style="border-top: 4px solid #2563eb;">
                        <!-- Icon -->
                        <div class="mb-6 flex justify-center">
                            <svg class="w-20 h-20 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <!-- Title -->
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Add content with AI</h2>
                        <!-- Features -->
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Words that match what you do</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Edit & enhance with AI</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Quickly tailor for every application</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Step 3: Download & send -->
                    <div class="step-card card-animate bg-white rounded-2xl p-8 border border-gray-200 shadow-sm" style="border-top: 4px solid #10b981;">
                        <!-- Icon -->
                        <div class="mb-6 flex justify-center">
                            <svg class="w-20 h-20 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                        <!-- Title -->
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Download & send</h2>
                        <!-- Features -->
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Popular file formats</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Instant digital profile</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700" style="font-size: 16px;">Unlimited versions</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Continue Button -->
                <div class="text-center">
                    <a href="/candidate/resume/builder/templates" 
                       class="btn-continue inline-block px-12 py-4 bg-blue-600 text-white font-semibold rounded-lg text-lg">
                        Continue
                    </a>
                </div>

                <!-- Terms Notice -->
                <p class="text-center mt-6 text-sm text-gray-600">
                    By clicking above, you agree to our <a href="#" class="text-blue-600 underline">Terms of Use</a> and <a href="#" class="text-blue-600 underline">Privacy Policy</a>.
                </p>

                <!-- Trustpilot Review -->
                <div class="mt-12 bg-gray-100 rounded-lg px-6 py-4 flex items-center justify-center gap-4">
                    <span class="font-bold text-gray-900">Excellent</span>
                    <div class="flex gap-1">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <?php endfor; ?>
                    </div>
                    <span class="text-gray-700">4.5 out of 5 based on 15,447 reviews</span>
                    <span class="text-green-600 font-semibold">Trustpilot</span>
                </div>
            </div>
        </main>

        <!-- Footer -->
    </div>
    <?php include __DIR__ . '/../../../include/footer.php'; ?>
</body>
</html>

