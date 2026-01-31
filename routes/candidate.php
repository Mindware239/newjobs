<?php

use App\Core\Router;
use App\Controllers\Candidate\CandidateController;
use App\Controllers\Candidate\JobController;
use App\Controllers\Candidate\ResumeBuilderController;

$router = \App\Core\Router::getInstance();

// Candidate Dashboard & Profile
$router->get('/candidate/dashboard', [CandidateController::class, 'dashboard']);
// Candidate Application Routes
$router->get('/candidate/applications', [CandidateController::class, 'applications']);
$router->get('/candidate/interviews', [CandidateController::class, 'interviews']);

// Candidate Reviews Routes
$router->get('/candidate/reviews', [CandidateController::class, 'viewReviews']);
$router->get('/candidate/reviews/create', [CandidateController::class, 'createReview']);
$router->post('/candidate/reviews/submit', [CandidateController::class, 'submitReview']);
$router->get('/candidate/reviews/success', [CandidateController::class, 'reviewSuccess']);

// Profile routes - more specific routes must come first
$router->get('/candidate/profile/complete', [CandidateController::class, 'profileComplete']);
$router->get('/candidate/profile', [CandidateController::class, 'viewProfile']);
$router->post('/candidate/profile/save', [CandidateController::class, 'saveProfile']);
$router->post('/candidate/profile/upload', [CandidateController::class, 'uploadFile']);
$router->post('/candidate/profile/delete-video', [CandidateController::class, 'deleteVideo']);
$router->get('/candidate/change-password', [CandidateController::class, 'changePassword']);
$router->post('/candidate/update-password', [CandidateController::class, 'updatePassword']);

// Candidate Job Routes (both routes point to same controller - /jobs is public, /candidate/jobs is also public but may have candidate-specific features)
$router->get('/candidate/jobs', [JobController::class, 'index']);
$router->get('/candidate/jobs/search', [JobController::class, 'search']);
$router->get('/candidate/jobs/saved', [JobController::class, 'savedJobs']);
$router->get('/candidate/jobs/{slug}', [JobController::class, 'show']);
$router->post('/candidate/jobs/{slug}/apply', [JobController::class, 'apply']);
$router->post('/candidate/jobs/{slug}/bookmark', [JobController::class, 'bookmark']);

// Candidate Chat Routes
use App\Controllers\Candidate\ChatController;
$router->get('/candidate/chat', [ChatController::class, 'index']);
$router->get('/candidate/chat/{id}', [ChatController::class, 'show']);
$router->post('/candidate/chat/send', [ChatController::class, 'sendMessage']);
$router->get('/candidate/chat/messages', [ChatController::class, 'getMessages']);
$router->post('/candidate/chat/start', [ChatController::class, 'startConversation']);
$router->get('/candidate/chat/unread-count', [ChatController::class, 'getUnreadCount']);

// Candidate Resume Builder Routes
$router->get('/candidate/resume/builder/onboarding', [ResumeBuilderController::class, 'onboarding']);
$router->get('/candidate/resume/builder/templates', [ResumeBuilderController::class, 'templates']);
$router->get('/candidate/resume/builder', [ResumeBuilderController::class, 'onboarding']); // Default to onboarding
$router->post('/candidate/resume/builder/create', [ResumeBuilderController::class, 'create']);
$router->get('/candidate/resume/builder/{resumeId}/wizard', [ResumeBuilderController::class, 'wizard']);
$router->get('/candidate/resume/builder/{resumeId}/edit', [ResumeBuilderController::class, 'edit']);
$router->post('/candidate/resume/builder/{resumeId}/save', [ResumeBuilderController::class, 'save']);
$router->post('/candidate/resume/builder/{resumeId}/export-pdf', [ResumeBuilderController::class, 'exportPDF']);
// AI-powered resume features
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-summary', [ResumeBuilderController::class, 'aiGenerateSummary']);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-job-summary', [ResumeBuilderController::class, 'aiGenerateJobSummary']);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-experience', [ResumeBuilderController::class, 'aiGenerateExperience']);
$router->post('/candidate/resume/builder/{resumeId}/ai/generate-section', [ResumeBuilderController::class, 'aiGenerateSection']);
$router->post('/candidate/resume/builder/{resumeId}/ai/enhance-description', [ResumeBuilderController::class, 'aiEnhanceDescription']);
$router->post('/candidate/resume/builder/{resumeId}/ai/suggest-skills', [ResumeBuilderController::class, 'aiSuggestSkills']);

// Candidate Premium Routes
use App\Controllers\Candidate\PremiumController;
$router->get('/candidate/premium/plans', [PremiumController::class, 'plans']);
$router->post('/candidate/premium/payment', [PremiumController::class, 'initiatePayment']);
$router->post('/candidate/premium/payment/callback', [PremiumController::class, 'paymentCallback']);
$router->get('/candidate/premium/billing', [PremiumController::class, 'billing']);

// Candidate Help & Legal Pages
$router->get('/candidate/help', [CandidateController::class, 'help']);
$router->get('/candidate/privacy', [CandidateController::class, 'privacy']);
$router->get('/candidate/terms', [CandidateController::class, 'terms']);

// Candidate Notification Routes
use App\Controllers\Candidate\NotificationController;
$router->get('/candidate/notifications', [NotificationController::class, 'index']);
$router->get('/candidate/notifications/unread', [NotificationController::class, 'getUnread']);
$router->post('/candidate/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
$router->post('/candidate/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
$router->post('/candidate/notifications/{id}/delete', [NotificationController::class, 'delete']);
$router->post('/candidate/notifications/delete-read', [NotificationController::class, 'deleteRead']);

// Resume Parsing & Job Recommendations
use App\Controllers\Candidate\ResumeController;
use App\Controllers\Candidate\JobRecommendationsController;
$router->post('/candidate/resume/parse', [ResumeController::class, 'parseResume']);
$router->get('/candidate/jobs/recommended', [JobRecommendationsController::class, 'getRecommendedJobs']);

// Candidate Settings
use App\Controllers\Candidate\SettingsController;
$router->get('/candidate/settings', [SettingsController::class, 'index']);
$router->post('/candidate/settings', [SettingsController::class, 'update']);
