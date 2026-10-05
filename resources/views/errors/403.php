<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden | Jobsence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, #fff1ed 0%, #fff8f5 100%);
        }
        .error-glow {
            background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%);
            filter: blur(80px);
        }
        .btn-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.2);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .animate-float {
            animation: float 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    
    <!-- Background Elements -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] error-glow -z-10"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] error-glow -z-10"></div>

    <div class="max-w-3xl w-full text-center relative z-10">
        <!-- 403 Illustration Area -->
        <div class="relative mb-12">
            <h1 class="text-[150px] sm:text-[220px] font-[900] leading-none text-red-500/10 select-none">
                403
            </h1>
           
        </div>

        <!-- Text Content -->
        <div class="space-y-6">
            <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                Hold on! Access Denied.
            </h2>
            <p class="text-lg sm:text-xl text-gray-500 max-w-xl mx-auto leading-relaxed">
                It seems you don't have the necessary permissions to view this page. If you believe this is an error, please try logging in with a different account.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" 
               class="btn-lift w-full sm:w-auto px-10 py-4 bg-gray-900 text-white font-bold text-lg rounded-full shadow-lg hover:bg-black transition-all">
                Back to Safety
            </a>
            <a href="/login" 
               class="btn-lift w-full sm:w-auto px-10 py-4 bg-white text-gray-900 border-2 border-gray-900 font-bold text-lg rounded-full hover:bg-gray-50 transition-all">
                Switch Account
            </a>
        </div>

        <!-- Support Info -->
        <div class="mt-16 pt-8 border-t border-gray-200/50">
            <p class="text-sm text-gray-400 font-medium">
                Need specialized access? <a href="/contact" class="text-[#ff5a36] hover:underline font-bold">Contact Administrator</a>
            </p>
        </div>
    </div>
</body>
</html>











