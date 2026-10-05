<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Jobsence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, #fff1ed 0%, #fff8f5 100%);
        }
        .error-glow {
            background: radial-gradient(circle, rgba(255, 90, 54, 0.1) 0%, transparent 70%);
            filter: blur(80px);
        }
        .btn-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 90, 54, 0.2);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    
    <!-- Background Elements -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] error-glow -z-10"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] error-glow -z-10"></div>

    <div class="max-w-3xl w-full text-center relative z-10">
        <!-- 404 Illustration Area -->
        <div class="relative mb-12">
            <h1 class="text-[150px] sm:text-[220px] font-[900] leading-none text-[#ff5a36]/10 select-none">
                404
            </h1>
        </div>

        <!-- Text Content -->
        <div class="space-y-6">
            <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                Oops! This page is taking a break.
            </h2>
            <p class="text-lg sm:text-xl text-gray-500 max-w-xl mx-auto leading-relaxed">
                The page you're looking for might have been moved, deleted, or never existed in the first place. Don't worry, let's get you back on track.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" 
               class="btn-lift w-full sm:w-auto px-10 py-4 bg-[#ff5a36] text-white font-bold text-lg rounded-full shadow-lg hover:bg-[#e54e2d] transition-all">
                Take me Home
            </a>
            <a href="/jobs" 
               class="btn-lift w-full sm:w-auto px-10 py-4 bg-white text-[#ff5a36] border-2 border-[#ff5a36] font-bold text-lg rounded-full hover:bg-orange-50 transition-all">
                Browse All Jobs
            </a>
        </div>

        <!-- Support Info -->
        <div class="mt-16 pt-8 border-t border-gray-200/50">
            <p class="text-sm text-gray-400 font-medium">
                Need help finding something? <a href="/contact" class="text-[#ff5a36] hover:underline">Contact our support team</a>
            </p>
        </div>
    </div>

    <!-- Decorative Floating Shapes -->
    <div class="hidden lg:block absolute top-1/4 left-10 w-12 h-12 bg-orange-200 rounded-lg rotate-12 opacity-20 animate-pulse"></div>
    <div class="hidden lg:block absolute bottom-1/4 right-10 w-16 h-16 border-4 border-orange-200 rounded-full opacity-20 animate-bounce" style="animation-duration: 4s;"></div>
</body>
</html>











