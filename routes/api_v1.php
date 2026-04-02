<?php

declare(strict_types=1);

use App\Core\Router;
use App\Middlewares\ApiAuthMiddleware;

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

$router = Router::getInstance();

// API V1 Group
$router->group(['prefix' => '/api/v1'], function(Router $router) {

    // ============================
    // PUBLIC ROUTES (No Auth Required)
    // ============================

    // 1. Authentication
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/register-candidate', [AuthController::class, 'registerCandidate']);
    $router->post('/register-employer', [AuthController::class, 'registerEmployer']);
    $router->post('/verify-email', [AuthController::class, 'verifyEmail']);
    $router->post('/verify-otp', [AuthController::class, 'verifyOtp']);
    $router->post('/resend-otp', [AuthController::class, 'resendOtp']);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
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
    $router->get('/job-titles', [UtilityController::class, 'jobTitles']);
    $router->get('/skills', [UtilityController::class, 'skills']);
    $router->get('/companies', [UtilityController::class, 'companies']);
    $router->get('/subscription-plans', [PaymentController::class, 'listPlans']);
    $router->get('/app-version', [UtilityController::class, 'appVersion']);
    $router->get('/maintenance-status', [UtilityController::class, 'maintenanceStatus']);

    // 4. Payment Webhooks (Public - External Services)
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

        // ============================
        // SHARED/COMMON ROUTES
        // ============================

        // Profile (Dynamic based on user role)
        $router->get('/profile', [ProfileController::class, 'show']);
        $router->put('/profile', [ProfileController::class, 'update']);
        $router->post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        $router->delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);

        // Chat
        $router->get('/conversations', [ChatController::class, 'listConversations']);
        $router->post('/conversations', [ChatController::class, 'createConversation']);
        $router->get('/conversations/{id}/messages', [ChatController::class, 'getMessages']);
        $router->post('/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
        $router->delete('/conversations/{id}/messages/{msg_id}', [ChatController::class, 'deleteMessage']);
        $router->patch('/conversations/{id}/messages/{msg_id}', [ChatController::class, 'editMessage']);
        $router->post('/conversations/{id}/read', [ChatController::class, 'markAsRead']);
        $router->delete('/conversations/{id}', [ChatController::class, 'deleteConversation']);
        $router->post('/conversations/{id}/block', [ChatController::class, 'blockUser']);
        $router->post('/conversations/{id}/archive', [ChatController::class, 'archiveConversation']);
        $router->get('/conversations/unread-count', [ChatController::class, 'unreadCount']);

        // Interviews
        $router->post('/interviews/schedule', [InterviewController::class, 'schedule']);
        $router->get('/interviews', [InterviewController::class, 'index']);
        $router->get('/interviews/{id}', [InterviewController::class, 'show']);
        $router->put('/interviews/{id}', [InterviewController::class, 'update']);
        $router->delete('/interviews/{id}', [InterviewController::class, 'cancel']);
        $router->post('/interviews/{id}/reschedule', [InterviewController::class, 'reschedule']);
        $router->post('/interviews/{id}/complete', [InterviewController::class, 'complete']);
        $router->post('/interviews/{id}/feedback', [InterviewController::class, 'addFeedback']);
        $router->get('/interviews/{id}/jitsi-token', [InterviewController::class, 'getJitsiToken']);
        $router->post('/interviews/{id}/attendance', [InterviewController::class, 'markAttendance']);

        // Notifications
        $router->get('/notifications', [NotificationController::class, 'index']);
        $router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        $router->post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        $router->post('/notifications/fcm-token', [NotificationController::class, 'registerFcmToken']);
        $router->delete('/notifications/fcm-token', [NotificationController::class, 'unregisterFcmToken']);
        $router->get('/notifications/preferences', [NotificationController::class, 'getPreferences']);
        $router->put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
        $router->post('/notifications/test', [NotificationController::class, 'testNotification']);
        $router->get('/notifications/history', [NotificationController::class, 'history']);

        // Reviews
        $router->get('/reviews/company/{id}', [ReviewController::class, 'companyReviews']);
        $router->post('/reviews', [ReviewController::class, 'create']);
        $router->get('/reviews/my-reviews', [ReviewController::class, 'myReviews']);
        $router->put('/reviews/{id}', [ReviewController::class, 'update']);
        $router->delete('/reviews/{id}', [ReviewController::class, 'delete']);
        $router->get('/reviews/company/{id}/stats', [ReviewController::class, 'companyStats']);

        // Analytics
        $router->get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        $router->get('/analytics/profile-views', [AnalyticsController::class, 'profileViews']);
        $router->get('/analytics/job/{id}/stats', [AnalyticsController::class, 'jobStats']);
        $router->post('/analytics/event', [AnalyticsController::class, 'trackEvent']);

        // Utility
        $router->post('/feedback', [UtilityController::class, 'submitFeedback']);
        $router->post('/report', [UtilityController::class, 'reportContent']);

        // ============================
        // PAYMENTS & SUBSCRIPTIONS
        // ============================
        $router->post('/payments/initiate', [PaymentController::class, 'initiate']);
        $router->post('/payments/verify', [PaymentController::class, 'verify']);
        $router->get('/payments/status', [PaymentController::class, 'status']);
        $router->get('/payments/history', [PaymentController::class, 'history']);
        $router->post('/subscription/upgrade', [PaymentController::class, 'upgradeSubscription']);
        $router->post('/subscription/cancel', [PaymentController::class, 'cancelSubscription']);
        $router->get('/subscription/current', [PaymentController::class, 'currentSubscription']);
        $router->post('/payments/refund', [PaymentController::class, 'requestRefund']);
        $router->get('/invoices', [PaymentController::class, 'listInvoices']);
        $router->get('/invoices/{id}/download', [PaymentController::class, 'downloadInvoice']);
        $router->post('/payments/wallet/add', [PaymentController::class, 'addToWallet']);
        $router->get('/payments/wallet/balance', [PaymentController::class, 'walletBalance']);

        // ============================
        // CANDIDATE ROUTES
        // ============================
        $router->group(['prefix' => '/candidate'], function(Router $router) {

            // Profile
            $router->get('/profile/detailed', [CandidateProfileController::class, 'detailed']);
            $router->post('/profile/education', [CandidateProfileController::class, 'addEducation']);
            $router->put('/profile/education/{id}', [CandidateProfileController::class, 'updateEducation']);
            $router->delete('/profile/education/{id}', [CandidateProfileController::class, 'deleteEducation']);
            $router->post('/profile/experience', [CandidateProfileController::class, 'addExperience']);
            $router->put('/profile/experience/{id}', [CandidateProfileController::class, 'updateExperience']);
            $router->delete('/profile/experience/{id}', [CandidateProfileController::class, 'deleteExperience']);
            $router->post('/profile/skills', [CandidateProfileController::class, 'addSkill']);
            $router->delete('/profile/skills/{id}', [CandidateProfileController::class, 'removeSkill']);
            $router->post('/profile/languages', [CandidateProfileController::class, 'addLanguage']);
            $router->delete('/profile/languages/{id}', [CandidateProfileController::class, 'removeLanguage']);
            $router->post('/profile/interests', [CandidateProfileController::class, 'setInterests']);
            $router->get('/profile/completion-status', [CandidateProfileController::class, 'completionStatus']);

            // Resumes
            $router->post('/resumes/upload', [ResumeController::class, 'upload']);
            $router->get('/resumes', [ResumeController::class, 'index']);
            $router->get('/resumes/{id}', [ResumeController::class, 'show']);
            $router->put('/resumes/{id}', [ResumeController::class, 'update']);
            $router->delete('/resumes/{id}', [ResumeController::class, 'delete']);
            $router->post('/resumes/{id}/parse', [ResumeController::class, 'parse']);
            $router->post('/resumes/{id}/download', [ResumeController::class, 'download']);
            $router->get('/resumes/{id}/preview', [ResumeController::class, 'preview']);
            $router->post('/resumes/create-from-profile', [ResumeController::class, 'createFromProfile']);
            $router->put('/resumes/{id}/set-default', [ResumeController::class, 'setDefault']);
            $router->get('/resume-templates', [ResumeController::class, 'templates']);
            $router->post('/resumes/{id}/export', [ResumeController::class, 'export']);

            // Applications
            $router->get('/applications', [ApplicationController::class, 'index']);
            $router->get('/applications/{id}', [ApplicationController::class, 'show']);
            $router->post('/applications/{id}/withdraw', [ApplicationController::class, 'withdraw']);
            $router->post('/applications/{id}/accept-offer', [ApplicationController::class, 'acceptOffer']);
            $router->post('/applications/{id}/reject-offer', [ApplicationController::class, 'rejectOffer']);

            // Job Actions
            $router->post('/jobs/{id}/apply', [JobController::class, 'apply']);
            $router->post('/jobs/{id}/bookmark', [BookmarkController::class, 'bookmark']);
            $router->delete('/jobs/{id}/bookmark', [BookmarkController::class, 'unbookmark']);
            $router->get('/bookmarks', [BookmarkController::class, 'index']);
            $router->post('/bookmarks/bulk-delete', [BookmarkController::class, 'bulkDelete']);

            // Saved Searches
            $router->post('/jobs/search-saved', [JobController::class, 'saveSearch']);
            $router->get('/saved-searches', [JobController::class, 'savedSearches']);
            $router->delete('/saved-searches/{id}', [JobController::class, 'deleteSavedSearch']);
            $router->get('/jobs/trending', [JobController::class, 'trending']);

            // Job Alerts
            $router->post('/job-alerts', [AlertController::class, 'create']);
            $router->get('/job-alerts', [AlertController::class, 'index']);
            $router->put('/job-alerts/{id}', [AlertController::class, 'update']);
            $router->delete('/job-alerts/{id}', [AlertController::class, 'delete']);
            $router->get('/job-alerts/{id}/count', [AlertController::class, 'matchingCount']);

            // Analytics (Candidate)
            $router->get('/analytics/profile-views', [AnalyticsController::class, 'profileViews']);
            $router->get('/analytics/applications', [AnalyticsController::class, 'applicationStats']);

            // Dashboard
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
            $router->post('/profile/documents/upload', [EmployerProfileController::class, 'uploadDocument']);
            $router->get('/profile/documents', [EmployerProfileController::class, 'listDocuments']);
            $router->delete('/profile/documents/{id}', [EmployerProfileController::class, 'deleteDocument']);
            $router->get('/profile/verification-status', [EmployerProfileController::class, 'verificationStatus']);
            $router->post('/profile/social-links', [EmployerProfileController::class, 'updateSocialLinks']);

            // Jobs
            $router->get('/jobs', [EmployerJobController::class, 'index']);
            $router->post('/jobs', [EmployerJobController::class, 'create']);
            $router->put('/jobs/{id}', [EmployerJobController::class, 'update']);
            $router->delete('/jobs/{id}', [EmployerJobController::class, 'delete']);
            $router->get('/jobs/{id}/details', [EmployerJobController::class, 'show']);
            $router->post('/jobs/{id}/duplicate', [EmployerJobController::class, 'duplicate']);
            $router->post('/jobs/{id}/publish', [EmployerJobController::class, 'publish']);
            $router->post('/jobs/{id}/unpublish', [EmployerJobController::class, 'unpublish']);

            // Applications (Received)
            $router->get('/applications', [ApplicationController::class, 'employerApplications']);
            $router->get('/applications/{id}', [ApplicationController::class, 'show']);
            $router->get('/applications/{id}/resume', [ApplicationController::class, 'downloadResume']);
            $router->post('/applications/{id}/shortlist', [ApplicationController::class, 'shortlist']);
            $router->post('/applications/{id}/reject', [ApplicationController::class, 'reject']);
            $router->post('/applications/{id}/send-offer', [ApplicationController::class, 'sendOffer']);

            // Candidate Search & Screening
            $router->get('/candidates', [CandidateController::class, 'search']);
            $router->get('/candidates/{id}', [CandidateController::class, 'show']);
            $router->post('/candidates/{id}/invite', [CandidateController::class, 'invite']);
            $router->post('/candidates/{id}/shortlist', [CandidateController::class, 'shortlist']);
            $router->get('/shortlists', [CandidateController::class, 'shortlists']);

            // Team Management
            $router->get('/team-members', [EmployerDashboardController::class, 'teamMembers']);
            $router->post('/team-members', [EmployerDashboardController::class, 'addMember']);
            $router->put('/team-members/{id}', [EmployerDashboardController::class, 'updateMember']);
            $router->delete('/team-members/{id}', [EmployerDashboardController::class, 'removeMember']);

            // Dashboard
            $router->get('/dashboard', [EmployerDashboardController::class, 'index']);
            $router->get('/dashboard/stats', [EmployerDashboardController::class, 'stats']);
            $router->get('/dashboard/recent-applications', [EmployerDashboardController::class, 'recentApplications']);
            $router->get('/dashboard/active-jobs', [EmployerDashboardController::class, 'activeJobs']);
            $router->get('/dashboard/upcoming-interviews', [EmployerDashboardController::class, 'upcomingInterviews']);

            // Analytics (Employer)
            $router->get('/analytics/job-stats', [AnalyticsController::class, 'jobStats']);
            $router->get('/analytics/candidates-views', [AnalyticsController::class, 'candidateViews']);
        });

    }); // ✅ close auth group

}); // ✅ close api/v1 group