<?php
// This file is assumed to be included inside a main layout (like layout.php)
// that defines the necessary variables and includes Tailwind CSS.

// Define $base if not already set by the layout
$base = $base ?? '/'; 

// --- Input Data from Controller ---
// The ContactController.php expects this view to handle the success/error messages
$status = $_GET['status'] ?? null;
$message_text = htmlspecialchars($_GET['msg'] ?? '');
$csrf_token = $_SESSION['csrf_token'] ?? ''; // Assuming session is started and token is set
?>
<?php
// 3. Include the Footer Component
// require 'include/header.php'; // Moved inside body
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Contact Us | Mindware Infotech</title>
    <!-- Load Tailwind CSS -->
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Custom CSS for Animations and Layout */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in-item {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
        }

        /* Subtle glow/shadow on hover for interactive elements */
        .interactive-card:hover {
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2); /* Green glow */
            transform: translateY(-2px);
        }

        /* Responsive aspect ratio for the map iframe */
        .map-container {
            position: relative;
            padding-bottom: 75%; /* 4:3 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 1.5rem; /* Matches parent card */
        }

        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Setting font for better readability */
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* === TOAST NOTIFICATION STYLES === */
        #toast-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1000;
            max-width: 90vw;
            pointer-events: none; /* Allows clicks to pass through empty space */
        }

        .toast {
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.5s ease-out;
            pointer-events: auto; /* Re-enable pointer events for the toast itself */
            cursor: pointer;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 800)">
    <!-- Skeleton Loader -->
    <div x-show="!loaded" x-transition.opacity.duration.500ms class="fixed inset-0 bg-white z-50 flex flex-col overflow-hidden">
        <!-- Header Skeleton -->
        <div class="h-20 border-b border-gray-100 flex items-center px-6 lg:px-[7.5rem] justify-between bg-white shrink-0">
            <div class="w-40 h-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="hidden md:flex gap-8">
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="flex gap-4">
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
            </div>
        </div>
        
        <!-- Hero/Title Skeleton -->
        <div class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
            <div class="flex flex-col items-center mb-16">
                <div class="w-3/4 md:w-1/2 h-12 bg-gray-200 rounded-lg animate-pulse mb-6"></div>
                <div class="w-full md:w-2/3 h-6 bg-gray-200 rounded animate-pulse mb-3"></div>
                <div class="w-5/6 md:w-1/2 h-6 bg-gray-200 rounded animate-pulse"></div>
            </div>

            <!-- Content Grid Skeleton -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Left Column -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="h-80 bg-gray-100 rounded-2xl animate-pulse"></div>
                    <div class="h-60 bg-gray-100 rounded-2xl animate-pulse"></div>
                </div>
                <!-- Right Column -->
                <div class="lg:col-span-2 h-[600px] bg-gray-100 rounded-2xl animate-pulse"></div>
            </div>
        </div>
    </div>
<?php require 'include/header.php'; ?>

<!-- === CONTACT SECTION START === -->
<section class="py-16 md:py-24">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        
        <!-- Header / Introduction -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-4 fade-in-item" style="animation-delay: 0.1s;">
                Let's Build Together
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto fade-in-item" style="animation-delay: 0.2s;">
                Have a question, proposal, or just want to say hello? We are ready to help you find the best talent or your next career move.
            </p>
        </div>

        <!-- Main Content Grid (Map & Form) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            <!-- Contact Details & Map (Left Column - 1/3 width) -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- Contact Details Card -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-xl border border-gray-100 fade-in-item interactive-card" style="animation-delay: 0.3s;">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Our Details</h3>
                    <div class="space-y-6">
                        
                        <!-- Email -->
                        <div class="flex items-start">
                            <!-- Icon for Email -->
                            <svg class="w-6 h-6 text-green-500 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.86 5.24a2 2 0 002.28 0L21 8m-2 10V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2v-3"></path></svg>
                            <div>
                                <p class="font-medium text-gray-900">Email Address</p>
                                <a href="mailto:gm@mindwareinfotech.com" class="text-sm text-gray-600 hover:text-blue-700 transition duration-200">gm@mindwareinfotech.com</a>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start">
                            <!-- Icon for Phone -->
                            <svg class="w-6 h-6 text-green-500 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1c-9.356 0-17-7.644-17-17V5z"></path></svg>
                            <div>
                                <p class="font-medium text-gray-900">Call Us</p>
                                <a href="tel:+918800122315" class="text-sm text-gray-600 hover:text-blue-700 transition duration-200">+91 8800122315</a>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start">
                            <!-- Icon for Location -->
                            <svg class="w-6 h-6 text-green-500 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div>
                                <p class="font-medium text-gray-900">Office Location</p>
                                <address class="text-sm text-gray-600 not-italic">
                                    Mindware Infotech, S4, Pankaj Plaza, Plot No-7, Pocket-7, Sector-12, Dwarka, New Delhi - 110078, India
                                </address>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media Links -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <p class="font-semibold text-gray-700 mb-3">Connect Online</p>
                        <div class="flex space-x-5">
                            <!-- LinkedIn Icon -->
                            <a href="#" aria-label="LinkedIn" class="text-gray-400 hover:text-blue-700 transition duration-300 transform hover:scale-110">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.483v-5.467c0-1.312-.469-2.213-1.644-2.213-1.229 0-1.967.842-1.967 2.22v5.459h-3.483s.047-9.426 0-10.435h3.483v1.488c.516-.723 1.34-1.745 3.128-1.745 2.288 0 3.998 1.496 3.998 4.706v5.986zM5.312 8.761c-1.218 0-1.986-.777-1.986-1.854 0-1.096.786-1.855 1.986-1.855 1.2 0 1.95.759 1.95 1.855 0 1.077-.759 1.854-1.95 1.854zm1.743 11.691H3.568V10.017h3.487v10.435zM22.25 2H1.75C.783 2 0 2.783 0 3.75v16.5C0 21.217.783 22 1.75 22h20.5C23.217 22 24 21.217 24 20.25V3.75C24 2.783 23.217 2 22.25 2z"/></svg>
                            </a>
                            <!-- Twitter/X Icon -->
                            <a href="#" aria-label="Twitter/X" class="text-gray-400 hover:text-blue-700 transition duration-300 transform hover:scale-110">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.901 1.996h3.693l-8.086 9.247 9.387 11.23h-7.615l-6.075-7.142-7.394 7.142H.912l8.32-9.52-8.634-10.704h7.828l5.584 6.945 4.881-5.696zm-1.868 18.005h1.5l-6.52-7.46-5.187 7.46h-1.636l7.466-10.704-5.35-6.14h1.76l4.42 5.074 5.76-5.074z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Google Map Card -->
                <div class="bg-white p-2 rounded-2xl shadow-xl border border-gray-100 fade-in-item" style="animation-delay: 0.5s;">
                    <h3 class="text-xl font-semibold text-gray-800 p-4 pb-2">Find Our Location</h3>
                    <div class="map-container rounded-xl overflow-hidden">
                        <!-- Embedded Google Map -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502.830026217462!2d77.040846!3d28.590775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d10664e42a98f%3A0x6b72a6b47c0a68d!2sMindware%20Infotech!5e0!3m2!1sen!2sin!4v1672531200000!5m2!1sen!2sin" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Office Location Map"
                        ></iframe>
                    </div>
                </div>

            </div>

            <!-- Contact Form (Right Column - 2/3 width) -->
            <div class="lg:col-span-2 bg-white p-8 md:p-12 rounded-2xl shadow-xl border border-gray-100 fade-in-item" style="animation-delay: 0.4s;">
                <h3 class="text-3xl font-bold text-gray-900 mb-8">Send us an Inquiry</h3>
                
                <!-- 
                    The previous status alert block is removed here.
                    The logic is now handled by the JavaScript toast below.
                -->
                
                <form action="<?php echo $base; ?>contact" method="POST" class="space-y-6">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <!-- Honeypot field for anti-spam (must be present in POST) -->
                    <input type="text" name="_hp_email" style="display:none;" tabindex="-1" autocomplete="off" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 transition duration-200 shadow-sm" placeholder="Your full name">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 transition duration-200 shadow-sm" placeholder="name@gmail.com">
                        </div>
                    </div>

                    <!-- Subject/Reason -->
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1">Subject / Reason for Contact <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="subject" name="subject" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 transition duration-200 bg-white appearance-none shadow-sm">
                                <option value="" disabled selected>Select a reason...</option>
                                <option value="Employer Inquiry">Employer Inquiry (Hiring)</option>
                                <option value="Candidate Support">Candidate Support (Job Seeker)</option>
                                <option value="Technical Issue">Technical Issue</option>
                                <option value="Partnership">Partnership / Media</option>
                                <option value="Other">Other</option>
                            </select>
                            <!-- Custom dropdown indicator for select box -->
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-1">Your Message <span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" rows="6" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 transition duration-200 shadow-sm" placeholder="Tell us how we can help you in detail..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-blue-600 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg shadow-gray-500/50 hover:bg-blue-700 transition duration-300 transform hover:scale-[1.005] active:scale-95 focus:outline-none focus:ring-4 focus:ring-blue-300">
                        Submit Inquiry
                    </button>
                    
                </form>
            </div>

        </div>

    </div>
</section>
<!-- === CONTACT SECTION END === -->

<!-- === TOAST NOTIFICATION CONTAINER AND SCRIPT === -->
<div id="toast-container">
    <!-- Toasts will be injected here by JavaScript -->
</div>

<script>
    // PHP variables are embedded into JavaScript for dynamic use
    const status = "<?php echo $status; ?>";
    const messageText = "<?php echo $message_text; ?>";
    const toastContainer = document.getElementById('toast-container');

    function showToast(type, message) {
        if (!toastContainer) return;

        // Determine styling based on type
        let bgColor, textColor, title;
        if (type === 'success') {
            bgColor = 'bg-green-600';
            textColor = 'text-white';
            title = 'Success!';
        } else if (type === 'error') {
            bgColor = 'bg-blue-600';
            textColor = 'text-white';
            title = 'Error:';
        } else {
            return; // Don't show toast for unknown status
        }
        
        // Ensure error message has a fallback
        const displayMessage = (type === 'error' && !message) 
            ? 'An error occurred while sending your message. Please try again.' 
            : message;

        // Create the toast element
        const toast = document.createElement('div');
        toast.className = `toast p-4 mb-2 rounded-xl shadow-xl ${bgColor} ${textColor} text-sm font-medium w-full sm:w-auto max-w-sm`;
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3 mt-0.5">
                    <!-- Icon based on status -->
                    ${type === 'success' ? 
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' : 
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                    }
                </div>
                <div>
                    <strong class="block text-base mb-0.5">${title}</strong>
                    <span>${displayMessage}</span>
                </div>
            </div>
        `;
        
        // Append to container and show
        toastContainer.appendChild(toast);
        // Delay adding the 'show' class to trigger the CSS transition
        setTimeout(() => toast.classList.add('show'), 10); 

        // Hide and remove after 5 seconds
        const hideTimeout = setTimeout(() => {
            toast.classList.remove('show');
            // Remove element after transition completes
            setTimeout(() => toast.remove(), 500); 
        }, 5000); 
        
        // Allow clicking to dismiss early
        toast.addEventListener('click', () => {
            clearTimeout(hideTimeout);
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500); 
        });
    }

    // Initialize the toast display on page load if status is present
    window.onload = function() {
        if (status) {
            // Delay to allow body/fade-in animations to finish
            setTimeout(() => {
                // PHP ensures messageText is always escaped
                const msg = messageText.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                showToast(status, msg);
            }, 500); 
        }
    };
</script>

</body>
</html>
<?php
// 3. Include the Footer Component
require 'include/footer.php';
?>
