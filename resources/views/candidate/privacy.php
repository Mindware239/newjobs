<?php
$content = ob_start();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>
        
        <div class="prose max-w-none">
            <p class="text-sm text-gray-500 mb-6">Last updated: <?= date('F d, Y') ?></p>
            
            <div class="space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introduction</h2>
                    <p class="text-gray-600">
                        Mindware Infotech ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our job portal platform.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Information We Collect</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Personal Information</h3>
                            <p class="text-gray-600">We collect information that you provide directly to us, including:</p>
                            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                                <li>Name, email address, phone number</li>
                                <li>Resume, cover letter, and other application materials</li>
                                <li>Work experience, education, and skills</li>
                                <li>Profile picture and video introductions</li>
                                <li>Payment information for premium subscriptions</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Automatically Collected Information</h3>
                            <p class="text-gray-600">We automatically collect certain information when you use our services:</p>
                            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                                <li>IP address and device information</li>
                                <li>Browser type and version</li>
                                <li>Pages visited and time spent on pages</li>
                                <li>Referral sources and search queries</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">How We Use Your Information</h2>
                    <p class="text-gray-600 mb-3">We use the information we collect to:</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Provide, maintain, and improve our services</li>
                        <li>Match you with relevant job opportunities</li>
                        <li>Send you job recommendations and notifications</li>
                        <li>Process payments and manage subscriptions</li>
                        <li>Communicate with you about our services</li>
                        <li>Detect and prevent fraud and abuse</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Information Sharing</h2>
                    <p class="text-gray-600 mb-3">We may share your information in the following circumstances:</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li><strong>With Employers:</strong> When you apply for a job, we share your profile and application materials with the employer</li>
                        <li><strong>Service Providers:</strong> We may share information with third-party service providers who perform services on our behalf</li>
                        <li><strong>Legal Requirements:</strong> We may disclose information if required by law or to protect our rights</li>
                        <li><strong>Business Transfers:</strong> Information may be transferred in connection with a merger, acquisition, or sale of assets</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Data Security</h2>
                    <p class="text-gray-600">
                        We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet is 100% secure.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Rights</h2>
                    <p class="text-gray-600 mb-3">You have the right to:</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Access and update your personal information</li>
                        <li>Delete your account and personal information</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Request a copy of your data</li>
                        <li>Object to certain processing of your information</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cookies and Tracking</h2>
                    <p class="text-gray-600">
                        We use cookies and similar tracking technologies to track activity on our platform and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Changes to This Policy</h2>
                    <p class="text-gray-600">
                        We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Contact Us</h2>
                    <p class="text-gray-600">
                        If you have any questions about this Privacy Policy, please contact us at:
                    </p>
                    <div class="bg-gray-50 rounded-lg p-4 mt-4">
                        <p class="text-gray-700"><strong>Email:</strong> <a href="mailto:gm@mindwareinfotech.com" class="text-green-600 hover:text-green-700">gm@mindwareinfotech.com</a></p>
                        <p class="text-gray-700 mt-2"><strong>Address:</strong> Mindware Infotech, India</p>
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

