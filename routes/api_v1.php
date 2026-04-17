<?php

declare(strict_types=1);

use App\Core\Router;
use App\Middlewares\ApiAuthMiddleware;
use App\Middlewares\RateLimitMiddleware;

// Controllers - Auth
use App\Controllers\Api\AuthController;

// Controllers - Core
use App\Controllers\Api\JobController;
use App\Controllers\Api\ProfileController;
use App\Controllers\Api\DashboardController;
use App\Controllers\Api\NotificationController;

// Controllers - Employer
use App\Controllers\Api\Employer\JobController as EmployerJobController;
use App\Controllers\Api\Employer\ProfileController as EmployerProfileController;
use App\Controllers\Api\Employer\CandidateController;
use App\Controllers\Api\Employer\DashboardController as EmployerDashboardController;

// Controllers - Candidate
use App\Controllers\Api\Candidate\ResumeController;
use App\Controllers\Api\Candidate\ApplicationController;
use App\Controllers\Api\Candidate\BookmarkController;
use App\Controllers\Api\Candidate\ProfileController as CandidateProfileController;
use App\Controllers\Api\Candidate\AlertController;

// Controllers - Shared
use App\Controllers\Api\ChatController;
use App\Controllers\Api\InterviewController;
use App\Controllers\Api\PaymentController;
use App\Controllers\Api\ReviewController;
use App\Controllers\Api\AnalyticsController;
use App\Controllers\Api\UtilityController;
use App\Controllers\Api\GeoController;

$router = Router::getInstance();

// API V1 Group
$router->group(['prefix' => '/api/v1'], function(Router $router) {

    // ============================
    // PUBLIC ROUTES (No Auth Required)
    // ============================

    // 1. Authentication (with Rate Limiting)
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/send-phone-otp', [AuthController::class, 'sendPhoneOtp'], [new RateLimitMiddleware(5, 300)]);
    $router->post('/login-phone', [AuthController::class, 'loginWithPhoneOtp'], [new RateLimitMiddleware(10, 300)]);
    $router->post('/register-candidate', [AuthController::class, 'registerCandidate']);
    $router->post('/register-candidate-phone', [AuthController::class, 'registerCandidateWithPhoneOtp'], [new RateLimitMiddleware(5, 300)]);
    $router->post('/register-employer', [AuthController::class, 'registerEmployer']);
    $router->post('/register-employer-phone', [AuthController::class, 'registerEmployerWithPhoneOtp'], [new RateLimitMiddleware(5, 300)]);
    $router->post('/verify-email', [AuthController::class, 'verifyEmail']);
    $router->post('/verify-otp', [AuthController::class, 'verifyOtp'], [new RateLimitMiddleware(10, 300)]);
    $router->post('/resend-otp', [AuthController::class, 'resendOtp'], [new RateLimitMiddleware(3, 300)]);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [new RateLimitMiddleware(5, 3600)]);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);
    $router->post('/auth/google/callback', [AuthController::class, 'googleCallback']);
    $router->post('/auth/apple/callback', [AuthController::class, 'appleCallback']);

    // 2. Public Job Routes
    $router->get('/jobs', [JobController::class, 'index']);
    $router->get('/jobs/{slug}', [JobController::class, 'show']);
    $router->get('/jobs/search', [JobController::class, 'search']);
    $router->get('/jobs/search/filters', [JobController::class, 'filters']);

    // 3. Utility/Public
    $router->get('/locations', [UtilityController::class, 'locations']);
    $router->get('/locations/search', [UtilityController::class, 'searchLocations']);
    $router->get('/job-titles', [UtilityController::class, 'jobTitles']);
    $router->get('/job-titles/search', [UtilityController::class, 'searchJobTitles']);
    $router->get('/skills', [UtilityController::class, 'skills']);
    $router->get('/skills/suggest', [UtilityController::class, 'suggestSkills']);
    $router->get('/industries/all', [UtilityController::class, 'listIndustries']);
    $router->get('/companies', [UtilityController::class, 'companies']);
    $router->get('/subscription-plans', [PaymentController::class, 'listPlans']);
    $router->get('/app-version', [UtilityController::class, 'appVersion']);
    $router->get('/maintenance-status', [UtilityController::class, 'maintenanceStatus']);
    $router->get('/health', [UtilityController::class, 'healthCheck']);
    $router->get('/fcm-web-config', [UtilityController::class, 'fcmWebConfig']);

    // 4. Geo APIs (Public)
    $router->get('/countries', [GeoController::class, 'countries']);
    $router->get('/states', [GeoController::class, 'states']);
    $router->get('/cities', [GeoController::class, 'cities']);
    $router->post('/location/detect', [GeoController::class, 'detectLocation']);

    // 5. Payment Webhooks
    $router->post('/payments/razorpay/webhook', [PaymentController::class, 'razorpayWebhook']);
    $router->post('/payments/cashfree/webhook', [PaymentController::class, 'cashfreeWebhook']);

    // ============================
    // AUTHENTICATED ROUTES
    // ============================
    $router->group(['middlewares' => [new ApiAuthMiddleware()]], function(Router $router) {

        // 1. Auth Management
        $router->post('/logout', [AuthController::class, 'logout']);
        $router->post('/refresh-token', [AuthController::class, 'refreshToken']);
        $router->post('/change-password', [AuthController::class, 'changePassword']);
        $router->get('/me', [AuthController::class, 'me']);

        // 2. Profile
        $router->get('/profile', [ProfileController::class, 'show']);
        $router->put('/profile', [ProfileController::class, 'update']);
        $router->post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        $router->delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);

        // 3. Chat
        $router->get('/conversations', [ChatController::class, 'listConversations']);
        $router->post('/conversations', [ChatController::class, 'createConversation']);
        $router->get('/conversations/{id}/messages', [ChatController::class, 'getMessages']);
        $router->post('/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
        $router->delete('/conversations/{id}/messages/{message_id}', [ChatController::class, 'deleteMessage']);
        $router->patch('/conversations/{id}/messages/{message_id}', [ChatController::class, 'editMessage']);
        $router->post('/conversations/{id}/read', [ChatController::class, 'markAsRead']);
        $router->delete('/conversations/{id}', [ChatController::class, 'deleteConversation']);
        $router->post('/conversations/{id}/block', [ChatController::class, 'blockUser']);
        $router->post('/conversations/{id}/archive', [ChatController::class, 'archiveConversation']);
        $router->get('/conversations/unread-count', [ChatController::class, 'unreadCount']);

        // 4. Interviews
        $router->get('/interviews', [InterviewController::class, 'index']);
        $router->post('/interviews', [InterviewController::class, 'schedule']);
        $router->get('/interviews/{id}', [InterviewController::class, 'show']);
        $router->put('/interviews/{id}', [InterviewController::class, 'update']);
        $router->delete('/interviews/{id}', [InterviewController::class, 'cancel']);
        $router->post('/interviews/{id}/reschedule', [InterviewController::class, 'reschedule']);
        $router->post('/interviews/{id}/complete', [InterviewController::class, 'complete']);
        $router->post('/interviews/{id}/feedback', [InterviewController::class, 'addFeedback']);
        $router->get('/interviews/{id}/jitsi-token', [InterviewController::class, 'getJitsiToken']);
        $router->post('/interviews/{id}/attendance', [InterviewController::class, 'markAttendance']);

        // 5. Notifications & Push
        $router->get('/notifications', [NotificationController::class, 'index']);
        $router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        $router->post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        $router->post('/notifications/fcm-token', [NotificationController::class, 'registerFcmToken']);
        $router->delete('/notifications/fcm-token', [NotificationController::class, 'unregisterFcmToken']);
        $router->get('/notifications/preferences', [NotificationController::class, 'getPreferences']);
        $router->put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
        $router->post('/notifications/test', [NotificationController::class, 'testNotification']);
        $router->get('/notifications/history', [NotificationController::class, 'history']);

        // 6. Reviews
        $router->get('/reviews/company/{id}', [ReviewController::class, 'companyReviews']);
        $router->post('/reviews', [ReviewController::class, 'create']);
        $router->get('/reviews/my-reviews', [ReviewController::class, 'myReviews']);
        $router->put('/reviews/{id}', [ReviewController::class, 'update']);
        $router->delete('/reviews/{id}', [ReviewController::class, 'delete']);
        $router->get('/reviews/company/{id}/stats', [ReviewController::class, 'companyStats']);

        // 7. Analytics
        $router->get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        $router->get('/analytics/profile-views', [AnalyticsController::class, 'profileViews']);
        $router->get('/analytics/job/{id}/stats', [AnalyticsController::class, 'jobStats']);
        $router->post('/analytics/event', [AnalyticsController::class, 'trackEvent']);

        // 8. Utility (Authenticated)
        $router->post('/feedback', [UtilityController::class, 'submitFeedback']);
        $router->post('/report', [UtilityController::class, 'reportContent']);

        // 9. Payments
        $router->post('/payments/initiate', [PaymentController::class, 'initiate']);
        $router->post('/payments/verify', [PaymentController::class, 'verify']);
        $router->get('/payments/status', [PaymentController::class, 'status']);
        $router->get('/payments/history', [PaymentController::class, 'history']);
        $router->get('/invoices', [PaymentController::class, 'listInvoices']);
        $router->get('/invoices/{id}/download', [PaymentController::class, 'downloadInvoice']);
        $router->post('/subscription/upgrade', [PaymentController::class, 'upgradeSubscription']);
        $router->post('/subscription/cancel', [PaymentController::class, 'cancelSubscription']);
        $router->get('/subscription/current', [PaymentController::class, 'currentSubscription']);
        $router->post('/discount/validate', [PaymentController::class, 'validateDiscount']);
        $router->get('/wallet/balance', [PaymentController::class, 'walletBalance']);
        $router->post('/wallet/add', [PaymentController::class, 'addToWallet']);

        // ============================
        // CANDIDATE ROUTES
        // ============================
        $router->group(['prefix' => '/candidate'], function(Router $router) {

            // Profile Detailed
            $router->get('/profile', [CandidateProfileController::class, 'detailed']);
            $router->get('/profile/completion', [CandidateProfileController::class, 'completionStatus']);
            
            // Education
            $router->post('/profile/education', [CandidateProfileController::class, 'addEducation']);
            $router->put('/profile/education/{id}', [CandidateProfileController::class, 'updateEducation']);
            $router->delete('/profile/education/{id}', [CandidateProfileController::class, 'deleteEducation']);
            
            // Experience
            $router->post('/profile/experience', [CandidateProfileController::class, 'addExperience']);
            $router->put('/profile/experience/{id}', [CandidateProfileController::class, 'updateExperience']);
            $router->delete('/profile/experience/{id}', [CandidateProfileController::class, 'deleteExperience']);
            
            // Skills & Languages
            $router->post('/profile/skills', [CandidateProfileController::class, 'addSkill']);
            $router->delete('/profile/skills/{id}', [CandidateProfileController::class, 'removeSkill']);
            $router->post('/profile/languages', [CandidateProfileController::class, 'addLanguage']);
            $router->delete('/profile/languages/{id}', [CandidateProfileController::class, 'removeLanguage']);
            $router->post('/profile/interests', [CandidateProfileController::class, 'setInterests']);

            // Resumes
            $router->get('/resumes', [ResumeController::class, 'index']);
            $router->post('/resumes', [ResumeController::class, 'upload']);
            $router->get('/resumes/{id}', [ResumeController::class, 'show']);
            $router->put('/resumes/{id}', [ResumeController::class, 'update']);
            $router->delete('/resumes/{id}', [ResumeController::class, 'delete']);
            $router->post('/resumes/{id}/parse', [ResumeController::class, 'parse']);
            $router->get('/resumes/{id}/download', [ResumeController::class, 'download']);
            $router->get('/resumes/{id}/preview', [ResumeController::class, 'preview']);
            $router->post('/resumes/generate', [ResumeController::class, 'createFromProfile']);
            $router->patch('/resumes/{id}/default', [ResumeController::class, 'setDefault']);
            $router->get('/resumes/templates', [ResumeController::class, 'templates']);

            // Applications
            $router->get('/applications', [ApplicationController::class, 'index']);
            $router->get('/applications/{id}', [ApplicationController::class, 'show']);
            $router->post('/applications/{id}/withdraw', [ApplicationController::class, 'withdraw']);
            $router->post('/applications/{id}/offer/accept', [ApplicationController::class, 'acceptOffer']);
            $router->post('/applications/{id}/offer/reject', [ApplicationController::class, 'rejectOffer']);

            // Jobs & Bookmarks
            $router->post('/jobs/{id}/apply', [JobController::class, 'apply']);
            $router->post('/jobs/{id}/bookmark', [BookmarkController::class, 'bookmark']);
            $router->delete('/jobs/{id}/bookmark', [BookmarkController::class, 'unbookmark']);
            $router->get('/bookmarks', [BookmarkController::class, 'index']);
            $router->post('/bookmarks/bulk-delete', [BookmarkController::class, 'bulkDelete']);

            // Saved Searches & Alerts
            $router->get('/saved-searches', [JobController::class, 'savedSearches']);
            $router->post('/saved-searches', [JobController::class, 'saveSearch']);
            $router->delete('/saved-searches/{id}', [JobController::class, 'deleteSavedSearch']);
            
            $router->get('/alerts', [AlertController::class, 'index']);
            $router->post('/alerts', [AlertController::class, 'create']);
            $router->put('/alerts/{id}', [AlertController::class, 'update']);
            $router->delete('/alerts/{id}', [AlertController::class, 'delete']);
            $router->get('/alerts/{id}/matches', [AlertController::class, 'matchingCount']);

            $router->get('/dashboard', [DashboardController::class, 'candidateDashboard']);
        });

        // ============================
        // EMPLOYER ROUTES
        // ============================
        $router->group(['prefix' => '/employer'], function(Router $router) {

            // Company Profile
            $router->get('/profile', [EmployerProfileController::class, 'show']);
            $router->put('/profile', [EmployerProfileController::class, 'update']);
            $router->post('/profile/logo', [EmployerProfileController::class, 'uploadLogo']);
            $router->post('/profile/banner', [EmployerProfileController::class, 'uploadBanner']);
            $router->get('/profile/documents', [EmployerProfileController::class, 'listDocuments']);
            $router->post('/profile/documents', [EmployerProfileController::class, 'uploadDocument']);
            $router->delete('/profile/documents/{id}', [EmployerProfileController::class, 'deleteDocument']);
            $router->get('/profile/verification', [EmployerProfileController::class, 'verificationStatus']);
            $router->post('/profile/social', [EmployerProfileController::class, 'updateSocialLinks']);

            // Job Management
            $router->get('/jobs', [EmployerJobController::class, 'index']);
            $router->post('/jobs', [EmployerJobController::class, 'create']);
            $router->get('/jobs/{id}', [EmployerJobController::class, 'show']);
            $router->put('/jobs/{id}', [EmployerJobController::class, 'update']);
            $router->delete('/jobs/{id}', [EmployerJobController::class, 'delete']);
            $router->post('/jobs/{id}/duplicate', [EmployerJobController::class, 'duplicate']);
            $router->patch('/jobs/{id}/publish', [EmployerJobController::class, 'publish']);
            $router->patch('/jobs/{id}/unpublish', [EmployerJobController::class, 'unpublish']);

            // Applications Received
            $router->get('/applications', [ApplicationController::class, 'employerApplications']);
            $router->get('/applications/{id}', [ApplicationController::class, 'show']);
            $router->get('/applications/{id}/resume', [ApplicationController::class, 'downloadResume']);
            $router->post('/applications/{id}/shortlist', [ApplicationController::class, 'shortlist']);
            $router->post('/applications/{id}/reject', [ApplicationController::class, 'reject']);
            $router->post('/applications/{id}/offer', [ApplicationController::class, 'sendOffer']);

            // Candidate Sourcing
            $router->get('/candidates', [CandidateController::class, 'search']);
            $router->get('/candidates/{id}', [CandidateController::class, 'show']);
            $router->post('/candidates/{id}/invite', [CandidateController::class, 'invite']);
            $router->post('/candidates/{id}/shortlist', [CandidateController::class, 'shortlist']);
            $router->get('/shortlists', [CandidateController::class, 'shortlists']);

            // Team
            $router->get('/team', [EmployerDashboardController::class, 'teamMembers']);
            $router->post('/team', [EmployerDashboardController::class, 'addMember']);
            $router->put('/team/{id}', [EmployerDashboardController::class, 'updateMember']);
            $router->delete('/team/{id}', [EmployerDashboardController::class, 'removeMember']);

            // Dashboard & Analytics
            $router->get('/dashboard', [EmployerDashboardController::class, 'index']);
            $router->get('/dashboard/stats', [EmployerDashboardController::class, 'stats']);
            $router->get('/analytics/jobs', [AnalyticsController::class, 'jobStats']);
        });

    }); // ✅ close auth group

}); // ✅ close api/v1 group
