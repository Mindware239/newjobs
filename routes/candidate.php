<?php

use App\Core\Router;
use App\Controllers\Candidate\CandidateController;
use App\Controllers\Candidate\JobController;
use App\Controllers\Candidate\ResumeBuilderController;
use App\Middlewares\AuthMiddleware;

$router = \App\Core\Router::getInstance();
$candidateAuth = new AuthMiddleware(['role' => 'candidate']);

// Candidate Dashboard & Profile
$router->get('/candidate/dashboard', [CandidateController::class, 'dashboard'], [$candidateAuth]);
// Candidate Application Routes
$router->get('/candidate/applications', [CandidateController::class, 'applications'], [$candidateAuth]);
$router->get('/candidate/interviews', [CandidateController::class, 'interviews'], [$candidateAuth]);

// Candidate Reviews Routes
$router->get('/candidate/reviews', [CandidateController::class, 'viewReviews'], [$candidateAuth]);
$router->get('/candidate/reviews/create', [CandidateController::class, 'createReview'], [$candidateAuth]);
$router->post('/candidate/reviews/submit', [CandidateController::class, 'submitReview'], [$candidateAuth]);
$router->get('/candidate/reviews/success', [CandidateController::class, 'reviewSuccess'], [$candidateAuth]);

// Profile routes - more specific routes must come first
$router->get('/candidate/profile/complete', [CandidateController::class, 'profileComplete'], [$candidateAuth]);
$router->get('/candidate/profile', [CandidateController::class, 'viewProfile'], [$candidateAuth]);
$router->post('/candidate/profile/save', [CandidateController::class, 'saveProfile'], [$candidateAuth]);
$router->post('/candidate/profile/upload', [CandidateController::class, 'uploadFile'], [$candidateAuth]);
$router->post('/candidate/profile/delete-video', [CandidateController::class, 'deleteVideo'], [$candidateAuth]);
$router->get('/candidate/change-password', [CandidateController::class, 'changePassword'], [$candidateAuth]);
$router->post('/candidate/update-password', [CandidateController::class, 'updatePassword'], [$candidateAuth]);

// Candidate Job Routes (both routes point to same controller - /jobs is public, /candidate/jobs is also public but may have candidate-specific features)
$router->get('/candidate/jobs', [JobController::class, 'index']);
$router->get('/candidate/jobs/search', [JobController::class, 'search']);
$router->get('/candidate/jobs/saved', [JobController::class, 'savedJobs'], [$candidateAuth]);
$router->get('/candidate/jobs/{slug}', [JobController::class, 'show']);
$router->post('/candidate/jobs/{slug}/apply', [JobController::class, 'apply'], [$candidateAuth]);
$router->post('/candidate/jobs/{slug}/bookmark', [JobController::class, 'bookmark'], [$candidateAuth]);

// Candidate Chat Routes
use App\Controllers\Candidate\ChatController;
use App\Middlewares\CandidateSubscriptionMiddleware;
$candPremium = new CandidateSubscriptionMiddleware();
$router->get('/candidate/chat', [ChatController::class, 'index'], [$candidateAuth, $candPremium]);
$router->get('/candidate/chat/{id}', [ChatController::class, 'show'], [$candidateAuth, $candPremium]);
$router->post('/candidate/chat/send', [ChatController::class, 'sendMessage'], [$candidateAuth, $candPremium]);
$router->get('/candidate/chat/messages', [ChatController::class, 'getMessages'], [$candidateAuth, $candPremium]);
$router->post('/candidate/chat/start', [ChatController::class, 'startConversation'], [$candidateAuth, $candPremium]);
$router->get('/candidate/chat/unread-count', [ChatController::class, 'getUnreadCount'], [$candidateAuth, $candPremium]);

// Candidate Resume Builder Routes
$router->get('/candidate/resume/builder/onboarding', [ResumeBuilderController::class, 'onboarding'], [$candidateAuth]);
$router->get('/candidate/resume/builder/templates', [ResumeBuilderController::class, 'templates'], [$candidateAuth]);
$router->get('/candidate/resume/builder', [ResumeBuilderController::class, 'onboarding'], [$candidateAuth]); // Default to onboarding
$router->post('/candidate/resume/builder/create', [ResumeBuilderController::class, 'create'], [$candidateAuth]);
$router->get('/candidate/resume/builder/{resumeId}/wizard', [ResumeBuilderController::class, 'wizard'], [$candidateAuth]);
$router->get('/candidate/resume/builder/{resumeId}/edit', [ResumeBuilderController::class, 'edit'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/save', [ResumeBuilderController::class, 'save'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/export-pdf', [ResumeBuilderController::class, 'exportPDF'], [$candidateAuth]);
// AI-powered resume features
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-summary', [ResumeBuilderController::class, 'aiGenerateSummary'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-job-summary', [ResumeBuilderController::class, 'aiGenerateJobSummary'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-experience', [ResumeBuilderController::class, 'aiGenerateExperience'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-section', [ResumeBuilderController::class, 'aiGenerateSection'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/ai/enhance-description', [ResumeBuilderController::class, 'aiEnhanceDescription'], [$candidateAuth]);
$router->post('/candidate/resume/builder/{resumeId}/ai/suggest-skills', [ResumeBuilderController::class, 'aiSuggestSkills'], [$candidateAuth]);

// Candidate Premium Routes
use App\Controllers\Candidate\PremiumController;
$router->get('/candidate/premium/plans', [PremiumController::class, 'plans'], [$candidateAuth]);
$router->post('/candidate/premium/payment', [PremiumController::class, 'initiatePayment'], [$candidateAuth]);
$router->post('/candidate/premium/payment/callback', [PremiumController::class, 'paymentCallback'], [$candidateAuth]);
$router->get('/candidate/premium/billing', [PremiumController::class, 'billing'], [$candidateAuth]);

// Candidate Help & Legal Pages
$router->get('/candidate/help', [CandidateController::class, 'help'], [$candidateAuth]);
$router->get('/candidate/privacy', [CandidateController::class, 'privacy'], [$candidateAuth]);
$router->get('/candidate/terms', [CandidateController::class, 'terms'], [$candidateAuth]);

// Candidate Notification Routes
use App\Controllers\Candidate\NotificationController;
$router->get('/candidate/notifications', [NotificationController::class, 'index'], [$candidateAuth]);
$router->get('/candidate/notifications/unread', [NotificationController::class, 'getUnread'], [$candidateAuth]);
$router->post('/candidate/notifications/{id}/read', [NotificationController::class, 'markAsRead'], [$candidateAuth]);

// Candidate Employment Verification
use App\Controllers\Candidate\EmploymentVerificationController as CandVerificationController;
$router->post('/candidate/verification', [CandVerificationController::class, 'create'], [$candidateAuth]);
$router->post('/candidate/verification/{id}/documents', [CandVerificationController::class, 'uploadDocument'], [$candidateAuth]);
$router->post('/candidate/verification/{id}/hr', [CandVerificationController::class, 'submitHr'], [$candidateAuth]);
$router->get('/api/candidate/verification/{id}/status', [CandVerificationController::class, 'status'], [$candidateAuth]);
$router->post('/candidate/notifications/read-all', [NotificationController::class, 'markAllAsRead'], [$candidateAuth]);
$router->post('/candidate/notifications/{id}/delete', [NotificationController::class, 'delete'], [$candidateAuth]);
$router->post('/candidate/notifications/delete-read', [NotificationController::class, 'deleteRead'], [$candidateAuth]);

// Resume Parsing & Job Recommendations
use App\Controllers\Candidate\ResumeController;
use App\Controllers\Candidate\JobRecommendationsController;
$router->post('/candidate/resume/parse', [ResumeController::class, 'parseResume'], [$candidateAuth]);
$router->get('/candidate/jobs/recommended', [JobRecommendationsController::class, 'getRecommendedJobs'], [$candidateAuth]);

// Candidate Settings
use App\Controllers\Candidate\SettingsController;
$router->get('/candidate/settings', [SettingsController::class, 'index'], [$candidateAuth]);
$router->post('/candidate/settings', [SettingsController::class, 'update'], [$candidateAuth]);
$router->post('/candidate/settings/phone/send-otp', [SettingsController::class, 'sendPhoneOtp'], [$candidateAuth]);
$router->post('/candidate/settings/phone/verify-otp', [SettingsController::class, 'verifyPhoneOtp'], [$candidateAuth]);
