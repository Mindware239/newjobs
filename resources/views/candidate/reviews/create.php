<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="/css/output.css" rel="stylesheet">
    <title><?= $title ?? 'Write a Review' ?> - Mindware Infotech</title>

    <style>
        .option-btn.active { background-color: #1f2937 !important; color: #fff !important; border-color: #1f2937 !important; }
        .option-checkbox:checked + span { font-weight: 600; color: #4f46e5; }
        .star { cursor: pointer; transition: transform .15s ease; }
        .star:hover { transform: scale(1.05); }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <?php $base = '/'; require __DIR__ . '/../../include/header.php'; ?>

    <div class="flex-grow flex items-center justify-center py-10 px-4">
        <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl overflow-hidden p-8 sm:p-12">

            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">Share your experience</h1>

            <!-- Subtitle -->
            <p class="text-gray-500 text-center mb-8">
                Your feedback helps others make better career decisions.
            </p>

            <!-- Info Note -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-8 flex items-start gap-3">
                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z" />
                </svg>
                <p class="text-sm text-indigo-800">
                    Your review will be posted anonymously. Please be honest and constructive.
                </p>
            </div>

            <form id="reviewForm" onsubmit="submitReview(event)">
                <div class="flex justify-between items-center mb-6">
                    <a href="/candidate/reviews" class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Reviews
                    </a>
                    <div class="text-sm font-medium text-gray-500">
                        Reviewing as <span class="text-gray-900"><?= htmlspecialchars($candidate->attributes['full_name'] ?? 'Anonymous') ?></span>
                    </div>
                </div>
                <!-- Company and Rating -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" id="companyName" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="e.g. Mindware Infotech">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Overall Rating</label>
                        <div class="flex items-center gap-1" aria-label="Overall rating">
                            <button type="button" class="star" data-star="1" onclick="setRating(1)" aria-label="1 star">
                                <svg class="w-7 h-7 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927l1.902 0 1.07 3.292h3.462l.588 1.81-2.8 2.034 1.07 3.292-1.54 1.118-2.8-2.034-2.8 2.034-1.539-1.118 1.07-3.292-2.8-2.034.588-1.81h3.461l1.07-3.292z"/></svg>
                            </button>
                            <button type="button" class="star" data-star="2" onclick="setRating(2)" aria-label="2 stars">
                                <svg class="w-7 h-7 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927l1.902 0 1.07 3.292h3.462l.588 1.81-2.8 2.034 1.07 3.292-1.54 1.118-2.8-2.034-2.8 2.034-1.539-1.118 1.07-3.292-2.8-2.034.588-1.81h3.461l1.07-3.292z"/></svg>
                            </button>
                            <button type="button" class="star" data-star="3" onclick="setRating(3)" aria-label="3 stars">
                                <svg class="w-7 h-7 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927l1.902 0 1.07 3.292h3.462l.588 1.81-2.8 2.034 1.07 3.292-1.54 1.118-2.8-2.034-2.8 2.034-1.539-1.118 1.07-3.292-2.8-2.034.588-1.81h3.461l1.07-3.292z"/></svg>
                            </button>
                            <button type="button" class="star" data-star="4" onclick="setRating(4)" aria-label="4 stars">
                                <svg class="w-7 h-7 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927l1.902 0 1.07 3.292h3.462l.588 1.81-2.8 2.034 1.07 3.292-1.54 1.118-2.8-2.034-2.8 2.034-1.539-1.118 1.07-3.292-2.8-2.034.588-1.81h3.461l1.07-3.292z"/></svg>
                            </button>
                            <button type="button" class="star" data-star="5" onclick="setRating(5)" aria-label="5 stars">
                                <svg class="w-7 h-7 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927l1.902 0 1.07 3.292h3.462l.588 1.81-2.8 2.034 1.07 3.292-1.54 1.118-2.8-2.034-2.8 2.034-1.539-1.118 1.07-3.292-2.8-2.034.588-1.81h3.461l1.07-3.292z"/></svg>
                            </button>
                        </div>
                        <input type="hidden" id="rating" value="5">
                    </div>
                </div>
                <!-- Dynamic Questions -->
                <div id="questionContainer" class="space-y-8"></div>
                
                <!-- Additional Comments -->
                <div class="mt-8">
                    <label class="block text-lg font-medium text-gray-800 mb-3">Any other comments? (Optional)</label>
                    <textarea id="reviewText" rows="4" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-4 border" placeholder="Share more details about your experience..." oninput="updateCounter()"></textarea>
                    <div class="mt-1 text-right text-xs text-gray-500"><span id="charCount">0</span>/500</div>
                </div>

                <!-- Submit Button -->
                <div class="mt-10">
                    <button type="submit"
                        class="w-full bg-blue-50 font-bold text-lg py-4 rounded-xl shadow-lg transform transition duration-200 
                        hover:translate-y-[-2px] focus:outline-none focus:ring-4 focus:ring-blue-300 flex justify-center items-center gap-2">
                        <span>Submit Review</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
                
                <p id="errorMessage" class="text-red-500 text-center mt-4 hidden"></p>
            </form>

        </div>
    </div>

    <!-- Questions Data & Logic -->
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none;
            color: white;
            transition: all 0.2s ease;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }
    </style>
    <script>
        function setRating(val) {
            document.getElementById('rating').value = val;
            document.querySelectorAll('.star svg').forEach((svg, idx) => {
                svg.classList.toggle('text-yellow-500', idx < val);
                svg.classList.toggle('text-yellow-300', idx >= val);
            });
        }
        function updateCounter() {
            const el = document.getElementById('reviewText');
            const count = Math.min(500, el.value.length);
            document.getElementById('charCount').textContent = count;
            if (el.value.length > 500) el.value = el.value.substring(0, 500);
        }
        const reviewQuestions = [
            { question: "Do you approve of the company's leadership?", type: "yesno", key: "leadership" },
            { question: "Would you recommend this company as a good place to work?", type: "yesno", key: "recommend" },
            { question: "Are you satisfied with the company culture?", type: "yesno", key: "culture" },
            { question: "Do you feel satisfied with your current role?", type: "yesno", key: "role_satisfaction" },
            { question: "Do you believe the company provides enough opportunities for growth?", type: "yesno", key: "growth" },
            { 
                question: "How would you describe the work environment?", 
                type: "multiple", 
                key: "environment",
                options: ["Relaxed", "Fast-paced", "Stressful", "Collaborative", "Competitive", "Not sure"]
            },
            { 
                question: "Which benefits do you value the most?", 
                type: "multiple", 
                key: "benefits",
                options: ["Health Insurance", "Flexible Hours", "Work from Home", "Bonuses", "Training Programs", "Other"]
            },
            { 
                question: "What motivates you to stay with the company?", 
                type: "multiple", 
                key: "motivation",
                options: ["Career Growth", "Salary", "Work Culture", "Recognition", "Job Security", "Other"]
            },
            { 
                question: "Which areas do you think need improvement?", 
                type: "multiple", 
                key: "improvements",
                options: ["Communication", "Management", "Salary", "Work-Life Balance", "Training", "Other"]
            },
            { 
                question: "How do you usually collaborate with your team?", 
                type: "multiple", 
                key: "collaboration",
                options: ["In-person Meetings", "Video Calls", "Emails", "Messaging Apps", "Project Management Tools", "Other"]
            }
        ];

        // Render Questions
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("questionContainer");
            
            reviewQuestions.forEach((qObj, index) => {
                const block = document.createElement("div");
                block.classList.add("mb-8", "p-6", "bg-gray-50", "rounded-xl", "border", "border-gray-100");

                // Add question text
                let innerHTML = `<p class="text-lg font-semibold text-gray-800 mb-4">${index + 1}. ${qObj.question}</p>`;

                if (qObj.type === "yesno") {
                    innerHTML += `
                        <div class="grid grid-cols-2 gap-4 max-w-md">
                            <button type="button" class="option-btn py-3 px-6 rounded-lg border-2 border-gray-200 bg-white text-gray-600 font-medium hover:border-indigo-500 hover:text-indigo-600 transition-all duration-200" data-question="${index}" data-answer="Yes" onclick="selectOption(this)">Yes</button>
                            <button type="button" class="option-btn py-3 px-6 rounded-lg border-2 border-gray-200 bg-white text-gray-600 font-medium hover:border-indigo-500 hover:text-indigo-600 transition-all duration-200" data-question="${index}" data-answer="No" onclick="selectOption(this)">No</button>
                        </div>
                    `;
                } else if (qObj.type === "multiple") {
                    innerHTML += `<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">`;
                    qObj.options.forEach(option => {
                        innerHTML += `
                            <label class="option-label flex items-center gap-3 bg-white border-2 border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-400 transition-colors">
                                <input type="checkbox" data-question="${index}" value="${option}" class="option-checkbox w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300">
                                <span class="text-gray-700">${option}</span>
                            </label>
                        `;
                    });
                    innerHTML += `</div>`;
                }

                block.innerHTML = innerHTML;
                container.appendChild(block);
            });
        });

        // Helper for single select buttons
        function selectOption(btn) {
            const parent = btn.parentNode;
            parent.querySelectorAll(".option-btn").forEach(b => {
                b.classList.remove("active", "bg-gray-800", "text-white", "border-gray-800");
                b.classList.add("bg-white", "text-gray-600", "border-gray-200");
            });
            btn.classList.add("active", "bg-gray-800", "text-white", "border-gray-800");
            btn.classList.remove("bg-white", "text-gray-600", "border-gray-200");
        }

        // Submit Logic
        async function submitReview(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const errorMsg = document.getElementById('errorMessage');
            
            // Gather answers
            const answers = [];
            reviewQuestions.forEach((qObj, index) => {
                let answer = null;
                
                if (qObj.type === "yesno") {
                    const activeBtn = document.querySelector(`.option-btn[data-question="${index}"].active`);
                    if (activeBtn) answer = activeBtn.getAttribute("data-answer");
                } else {
                    const checked = document.querySelectorAll(`input[data-question="${index}"]:checked`);
                    if (checked.length > 0) {
                        answer = Array.from(checked).map(cb => cb.value);
                    }
                }
                
                if (answer) {
                    answers.push({
                        question: qObj.question,
                        key: qObj.key,
                        answer: answer
                    });
                }
            });

            if (answers.length === 0) {
                errorMsg.textContent = "Please answer at least one question.";
                errorMsg.classList.remove("hidden");
                return;
            }

            const reviewText = document.getElementById('reviewText').value;

            // Prepare payload
            const payload = {
                answers: answers,
                review_text: reviewText,
                company_name: document.getElementById('companyName').value || 'Company',
                rating: parseInt(document.getElementById('rating').value || '5', 10)
            };

            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting...
            `;

            try {
                const response = await fetch('/candidate/reviews/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = result.redirect || '/candidate/reviews';
                } else {
                    throw new Error(result.error || 'Failed to submit review');
                }
            } catch (err) {
                console.error(err);
                errorMsg.textContent = err.message;
                errorMsg.classList.remove("hidden");
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span>Try Again</span>`;
            }
        }
    </script>
<?php include __DIR__ . '/../../include/footer.php'; ?>

</body>
</html>
