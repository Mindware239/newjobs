<?php

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AntiSpamMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Controllers\Employer\DashboardController;
use App\Controllers\Employer\JobsController;
use App\Controllers\Employer\ApplicationsController;
use App\Controllers\Employer\KycController;
use App\Controllers\Employer\WebhookController;
use App\Controllers\Employer\AnalyticsController;
use App\Controllers\Employer\InterviewsController;
use App\Controllers\Employer\MessagesController;
use App\Controllers\Employer\SettingsController;
use App\Controllers\Employer\PaymentsController;
use App\Controllers\Employer\ProfileController;
use App\Controllers\Employer\NotificationsController;
use App\Controllers\Employer\SubscriptionController;
use App\Controllers\Employer\BillingController;
use App\Controllers\Employer\InvoiceController;
use App\Controllers\Gateway\RazorpayController;

$router = \App\Core\Router::getInstance();

// Employer routes (require authentication)
$authMiddleware = new AuthMiddleware(['role' => 'employer']);
$csrfMiddleware = new CsrfMiddleware();
$rateLimitMiddleware = new RateLimitMiddleware(60, 60);

// Dashboard
$router->get('/employer/dashboard', [DashboardController::class, 'index'], [$authMiddleware]);

// Analytics
$router->get('/employer/analytics', [AnalyticsController::class, 'index'], [$authMiddleware]);
// Analytics API Endpoints
$router->get('/api/employer/analytics/funnel', [AnalyticsController::class, 'getHiringFunnel'], [$authMiddleware]);
$router->get('/api/employer/analytics/time-to-hire', [AnalyticsController::class, 'getTimeToHire'], [$authMiddleware]);
$router->get('/api/employer/analytics/location', [AnalyticsController::class, 'getLocationAnalytics'], [$authMiddleware]);
$router->get('/api/employer/analytics/job-engagement', [AnalyticsController::class, 'getJobEngagement'], [$authMiddleware]);
$router->get('/api/employer/analytics/candidate-quality', [AnalyticsController::class, 'getCandidateQuality'], [$authMiddleware]);
$router->get('/api/employer/analytics/communication', [AnalyticsController::class, 'getCommunicationAnalytics'], [$authMiddleware]);
$router->get('/api/employer/analytics/notifications', [AnalyticsController::class, 'getNotificationPerformance'], [$authMiddleware]);
$router->get('/api/employer/analytics/activity', [AnalyticsController::class, 'getEmployerActivity'], [$authMiddleware]);
$router->get('/api/employer/analytics/subscription-roi', [AnalyticsController::class, 'getSubscriptionROI'], [$authMiddleware]);
$router->get('/api/employer/analytics/security-logs', [AnalyticsController::class, 'getSecurityLogs'], [$authMiddleware]);
$router->get('/api/employer/analytics/sources', [AnalyticsController::class, 'getCandidateSources'], [$authMiddleware]);
$router->get('/api/employer/analytics/interview-outcomes', [AnalyticsController::class, 'getInterviewOutcomes'], [$authMiddleware]);
$router->get('/api/employer/analytics/offer-acceptance', [AnalyticsController::class, 'getOfferAcceptanceRate'], [$authMiddleware]);
$router->get('/api/employer/analytics/export', [AnalyticsController::class, 'exportReport'], [$authMiddleware]);

// Interviews
$router->get('/employer/interviews', [InterviewsController::class, 'index'], [$authMiddleware]);
$router->post('/employer/interviews/schedule', [InterviewsController::class, 'schedule'], [$authMiddleware]);
$router->post('/employer/interviews/{id}/reschedule', [InterviewsController::class, 'reschedule'], [$authMiddleware]);
$router->post('/employer/interviews/{id}/cancel', [InterviewsController::class, 'cancel'], [$authMiddleware]);
$router->post('/employer/interviews/{id}/complete', [InterviewsController::class, 'complete'], [$authMiddleware]);

// Application Notes
$router->post('/employer/applications/{id}/note', [ApplicationsController::class, 'addNote'], [$authMiddleware]);

// Messages
$router->get('/employer/messages', [MessagesController::class, 'index'], [$authMiddleware]);
$router->get('/employer/messages/conversation/{id}', [MessagesController::class, 'getConversation'], [$authMiddleware]);
$router->get('/employer/messages/{id}/messages', [MessagesController::class, 'getMessages'], [$authMiddleware]);
$router->post('/employer/messages/send', [MessagesController::class, 'sendMessage'], [$authMiddleware]);
$router->post('/employer/messages/start', [MessagesController::class, 'startConversation'], [$authMiddleware]);
$router->get('/employer/messages/unread-count', [MessagesController::class, 'getUnreadCount'], [$authMiddleware]);

// Profile
$router->get('/employer/profile', [ProfileController::class, 'index'], [$authMiddleware]);
$router->post('/employer/profile', [ProfileController::class, 'update'], [$authMiddleware]);

// Notifications
$router->get('/employer/notifications', [NotificationsController::class, 'index'], [$authMiddleware]);

// Settings
$router->get('/employer/settings', [SettingsController::class, 'index'], [$authMiddleware]);
$router->put('/employer/settings/account', [SettingsController::class, 'updateAccount'], [$authMiddleware]);
$router->put('/employer/settings/password', [SettingsController::class, 'updatePassword'], [$authMiddleware]);
$router->put('/employer/settings/preferences', [SettingsController::class, 'updatePreferences'], [$authMiddleware]);
$router->put('/employer/settings/company', [SettingsController::class, 'updateCompany'], [$authMiddleware]);

// Payments
$router->get('/employer/payments', [PaymentsController::class, 'index'], [$authMiddleware]);
$router->get('/employer/invoices/{id}', [PaymentsController::class, 'invoice'], [$authMiddleware]);
$router->post('/employer/payments/billing-info', [PaymentsController::class, 'updateBillingInfo'], [$authMiddleware]);

// Billing & Invoices
$router->get('/employer/billing/overview', [BillingController::class, 'overview'], [$authMiddleware]);
$router->get('/employer/billing/transactions', [BillingController::class, 'transactions'], [$authMiddleware]);
$router->get('/employer/billing/invoices', [BillingController::class, 'invoices'], [$authMiddleware]);
$router->get('/employer/billing/payment-methods', [BillingController::class, 'paymentMethods'], [$authMiddleware]);
$router->post('/employer/billing/payment-methods', [BillingController::class, 'savePaymentMethod'], [$authMiddleware]);
$router->get('/employer/billing/pay/{id}', [BillingController::class, 'pay'], [$authMiddleware]);
$router->get('/employer/billing/settings', [BillingController::class, 'settings'], [$authMiddleware]);
// Employer billing result pages
$router->get('/employer/billing/success', [BillingController::class, 'success'], [$authMiddleware]);
$router->get('/employer/billing/failed', [BillingController::class, 'failed'], [$authMiddleware]);
// Invoice download
$router->get('/employer/billing/invoice/{id}', [InvoiceController::class, 'download'], [$authMiddleware]);
// Razorpay endpoints
$router->get('/payment/create-order', [RazorpayController::class, 'createOrder'], [$authMiddleware]);
$router->post('/payment/verify', [RazorpayController::class, 'verify'], [$authMiddleware]);

// Subscription
$router->get('/employer/subscription/plans', [SubscriptionController::class, 'plans'], [$authMiddleware]);
$router->post('/employer/subscription/subscribe', [SubscriptionController::class, 'subscribe'], [$authMiddleware, $csrfMiddleware, $rateLimitMiddleware]);
$router->get('/employer/subscription/dashboard', [SubscriptionController::class, 'dashboard'], [$authMiddleware]);
$router->post('/employer/subscription/cancel', [SubscriptionController::class, 'cancel'], [$authMiddleware, $csrfMiddleware]);
$router->post('/employer/subscription/payment/callback', [SubscriptionController::class, 'paymentCallback'], [$authMiddleware, $csrfMiddleware, $rateLimitMiddleware]);
$router->post('/employer/subscription/renew', [SubscriptionController::class, 'renew'], [$authMiddleware, $csrfMiddleware, $rateLimitMiddleware]);
$router->post('/employer/subscription/change-plan', [SubscriptionController::class, 'changePlan'], [$authMiddleware, $csrfMiddleware, $rateLimitMiddleware]);

// Jobs
$router->get('/employer/jobs', [JobsController::class, 'index'], [$authMiddleware]);
$router->get('/employer/jobs/create', [JobsController::class, 'create']); // No middleware - handles auth in controller
// More specific routes first to avoid conflicts
$router->get('/employer/jobs/{slug}/preview', [JobsController::class, 'previewPublic'], [$authMiddleware]);
$router->get('/employer/jobs/{slug}/edit', [JobsController::class, 'edit'], [$authMiddleware]);
$router->get('/employer/jobs/{slug}', [JobsController::class, 'show'], [$authMiddleware]);
$spamMiddleware = new AntiSpamMiddleware();

$router->post('/employer/jobs', [JobsController::class, 'store'], [$authMiddleware, $spamMiddleware]);
$router->post('/employer/jobs/generate-description', [JobsController::class, 'generateDescription'], [$authMiddleware]);
$router->put('/employer/jobs/{slug}', [JobsController::class, 'update'], [$authMiddleware]);
$router->delete('/employer/jobs/{slug}', [JobsController::class, 'destroy'], [$authMiddleware]);
$router->post('/employer/jobs/{slug}/publish', [JobsController::class, 'publish'], [$authMiddleware]);
$router->post('/employer/jobs/bulk-import', [JobsController::class, 'bulkImport'], [$authMiddleware]);

// Applications
$router->get('/employer/applications', [ApplicationsController::class, 'index'], [$authMiddleware]);
$router->post('/employer/applications/bulk-status', [ApplicationsController::class, 'bulkStatus'], [$authMiddleware]);
$router->post('/employer/candidates/{id}/view', [ApplicationsController::class, 'recordView'], [$authMiddleware]);
$router->get('/employer/candidates/{id}/resume', [ApplicationsController::class, 'downloadResume'], [$authMiddleware]);
$router->get('/employer/applications/{id}', [ApplicationsController::class, 'show'], [$authMiddleware]);
$router->put('/employer/applications/{id}/status', [ApplicationsController::class, 'updateStatus'], [$authMiddleware]);
$router->post('/employer/applications/{id}/generate-score', [ApplicationsController::class, 'generateScore'], [$authMiddleware]);
$router->get('/employer/applications/export', [ApplicationsController::class, 'export'], [$authMiddleware]);

// KYC Verification
$router->get('/employer/kyc', [KycController::class, 'show'], [$authMiddleware]);
$router->post('/employer/kyc/documents', [KycController::class, 'uploadDocument'], [$authMiddleware]);
$router->post('/employer/kyc/submit', [KycController::class, 'submit'], [$authMiddleware]);

// Company Profile Management
use App\Controllers\Employer\CompanyProfileController;
$router->get('/employer/company-profile', [CompanyProfileController::class, 'index'], [$authMiddleware]);
$router->post('/employer/company-profile', [CompanyProfileController::class, 'update'], [$authMiddleware]);
$router->get('/employer/company-profile/blogs', [CompanyProfileController::class, 'getBlogs'], [$authMiddleware]);
$router->post('/employer/company-profile/blogs', [CompanyProfileController::class, 'createBlog'], [$authMiddleware]);
$router->delete('/employer/company-profile/blogs/{id}', [CompanyProfileController::class, 'deleteBlog'], [$authMiddleware]);
$router->get('/employer/company-profile/reviews', [CompanyProfileController::class, 'getReviews'], [$authMiddleware]);
$router->get('/employer/company-profile/followers', [CompanyProfileController::class, 'getFollowers'], [$authMiddleware]);

// AI Job Matching
use App\Controllers\Employer\JobMatchingController;
$router->get('/employer/jobs/{slug}/candidates', [JobMatchingController::class, 'getCandidatesForJob'], [$authMiddleware]);
$router->post('/employer/jobs/{slug}/generate-scores', [JobMatchingController::class, 'generateScores'], [$authMiddleware]);
$router->post('/employer/jobs/{slug}/candidates/{candidate_id}/score', [JobMatchingController::class, 'scoreCandidate'], [$authMiddleware]);
