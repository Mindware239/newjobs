<?php
$content = ob_start();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Help & Support</h1>
        
        <div class="prose max-w-none">
            <div class="space-y-8">
                <!-- Getting Started -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Getting Started</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I create a profile?</h3>
                            <p class="text-gray-600">Click on "Complete Your Profile" in the dropdown menu to get started. Fill in your basic information, education, work experience, skills, and upload your resume to create a complete profile.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I search for jobs?</h3>
                            <p class="text-gray-600">Use the "Browse Jobs" link in the navigation bar to search for jobs. You can filter by location, job type, salary range, and more.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I apply for a job?</h3>
                            <p class="text-gray-600">Click on a job listing to view details, then click "Apply Now" button. Make sure your profile is complete before applying.</p>
                        </div>
                    </div>
                </section>

                <!-- Premium Features -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Premium Features</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">What are the benefits of Premium?</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-2">
                                <li>Show your profile at the top to recruiters</li>
                                <li>Higher visibility in search results</li>
                                <li>Priority in job recommendations</li>
                                <li>Verified badge on your profile</li>
                                <li>Unlimited job applications</li>
                                <li>Advanced analytics dashboard</li>
                                <li>Priority customer support</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I upgrade to Premium?</h3>
                            <p class="text-gray-600">Visit the <a href="/candidate/premium/plans" class="text-blue-600 hover:text-blue-700 underline">Premium Plans</a> page to view available plans and upgrade your account.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I manage my Premium subscription?</h3>
                            <p class="text-gray-600">You can view your billing history and manage your subscription on the <a href="/candidate/premium/billing" class="text-blue-600 hover:text-blue-700 underline">Billing & Receipts</a> page.</p>
                        </div>
                    </div>
                </section>

                <!-- Profile Management -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Profile Management</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I update my profile?</h3>
                            <p class="text-gray-600">Go to your Profile page and click "Edit Profile" to update any information. Make sure to save your changes.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I upload a video introduction?</h3>
                            <p class="text-gray-600">In the "Complete Your Profile" section, navigate to the Video Profile step. You can either record a video directly or upload a pre-recorded video file.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">What is profile strength?</h3>
                            <p class="text-gray-600">Profile strength is a percentage that indicates how complete your profile is. A higher percentage means better visibility to employers. Aim for at least 80% for best results.</p>
                        </div>
                    </div>
                </section>

                <!-- Applications -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Job Applications</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I track my applications?</h3>
                            <p class="text-gray-600">Go to the "My Applications" page to see all your submitted applications and their status.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">How do I save jobs for later?</h3>
                            <p class="text-gray-600">Click the bookmark icon on any job listing to save it. You can view all saved jobs in the "Saved Jobs" section.</p>
                        </div>
                    </div>
                </section>

                <!-- Contact Support -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Still Need Help?</h2>
                    <p class="text-gray-600 mb-4">If you can't find the answer you're looking for, please contact our support team:</p>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700"><strong>Email:</strong> <a href="mailto:support@mindwareinfotech.com" class="text-blue-600 hover:text-blue-700">support@mindwareinfotech.com</a></p>
                        <p class="text-gray-700 mt-2"><strong>Phone:</strong> <a href="tel:+918527522688" class="text-blue-600 hover:text-blue-700">+91 852 752 22688</a></p>
                        <p class="text-gray-700 mt-2"><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM IST</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

