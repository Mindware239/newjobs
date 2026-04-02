<?php

use App\Core\Router;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;

$router = \App\Core\Router::getInstance();
$adminMiddleware = new AdminMiddleware();
$csrfMiddleware = new CsrfMiddleware();
$adminLoginRateLimit = new RateLimitMiddleware(5, 60);

// Admin Auth Routes (No middleware)
$router->get('/admin/login', [App\Controllers\Admin\AuthController::class, 'showLogin']);
$router->post('/admin/login', [App\Controllers\Admin\AuthController::class, 'login'], [$adminLoginRateLimit, $csrfMiddleware]);
$router->post('/admin/2fa/verify', [App\Controllers\Admin\AuthController::class, 'verifyOtp'], [$adminLoginRateLimit, $csrfMiddleware]);
$router->get('/admin/logout', [App\Controllers\Admin\AuthController::class, 'logout']);

// Captcha Route (No middleware)
$router->get('/admin/captcha/generate', [App\Controllers\Admin\CaptchaController::class, 'generate']);

// Admin Error Page
$router->get('/admin/error', function ($request, $response) {
    $msg = $request->get('message') ?? null;
    $response->view('admin/error', [
        'title' => 'Admin Error',
        'errorMessage' => $msg ?: 'An unexpected error occurred'
    ], 500, 'admin/layout');
}, [$adminMiddleware]);

// Admin Forgot/Reset Password (reuse Front AuthController)
$router->get('/admin/forgot-password', [App\Controllers\Front\AuthController::class, 'forgotPassword']);
$router->post('/admin/forgot-password', [App\Controllers\Front\AuthController::class, 'forgotPassword'], [$adminLoginRateLimit, $csrfMiddleware]);
$router->get('/admin/reset-password', [App\Controllers\Front\AuthController::class, 'resetPassword']);
$router->post('/admin/reset-password', [App\Controllers\Front\AuthController::class, 'resetPassword'], [$adminLoginRateLimit, $csrfMiddleware]);

// Admin Dashboard (Protected)
$router->get('/admin/dashboard', [App\Controllers\Admin\DashboardController::class, 'index'], [$adminMiddleware]);

// Candidates Management (Protected)
$router->get('/admin/candidates/add', [App\Controllers\Admin\CandidatesController::class, 'add'], [$adminMiddleware]);
$router->post('/admin/candidates/add', [App\Controllers\Admin\CandidatesController::class, 'add'], [$adminMiddleware]);
$router->post('/admin/candidates/upload-resume', [App\Controllers\Admin\CandidatesController::class, 'uploadResume'], [$adminMiddleware]);
$router->get('/admin/candidates/import-history', [App\Controllers\Admin\CandidatesController::class, 'importHistory'], [$adminMiddleware]);
$router->get('/admin/candidates/import', [App\Controllers\Admin\CandidatesController::class, 'import'], [$adminMiddleware]);
$router->post('/admin/candidates/import/upload', [App\Controllers\Admin\CandidatesController::class, 'uploadImport'], [$adminMiddleware]);
$router->post('/admin/candidates/import/confirm', [App\Controllers\Admin\CandidatesController::class, 'confirmImport'], [$adminMiddleware]);
$router->get('/admin/candidates', [App\Controllers\Admin\CandidatesController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/candidates/{id}', [App\Controllers\Admin\CandidatesController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/block', [App\Controllers\Admin\CandidatesController::class, 'block'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/unblock', [App\Controllers\Admin\CandidatesController::class, 'unblock'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/delete', [App\Controllers\Admin\CandidatesController::class, 'delete'], [$adminMiddleware]);
// Premium management
$router->post('/admin/candidates/{id}/premium/enable', [App\Controllers\Admin\CandidatesController::class, 'enablePremium'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/premium/disable', [App\Controllers\Admin\CandidatesController::class, 'disablePremium'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/premium/extend', [App\Controllers\Admin\CandidatesController::class, 'extendPremium'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/premium/reduce', [App\Controllers\Admin\CandidatesController::class, 'reducePremium'], [$adminMiddleware]);
$router->post('/admin/candidates/{id}/suggest', [App\Controllers\Admin\CandidatesController::class, 'suggestToEmployer'], [$adminMiddleware]);
$router->get('/admin/candidates/{id}/payments', [App\Controllers\Admin\CandidatesController::class, 'paymentHistory'], [$adminMiddleware]);

// Employers Management (Protected)
$router->get('/admin/employers', [App\Controllers\Admin\EmployersController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/employers/{id}', [App\Controllers\Admin\EmployersController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/approve-kyc', [App\Controllers\Admin\EmployersController::class, 'approveKyc'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/reject-kyc', [App\Controllers\Admin\EmployersController::class, 'rejectKyc'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/kyc-documents/{doc_id}/approve', [App\Controllers\Admin\EmployersController::class, 'approveKycDocument'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/kyc-documents/{doc_id}/reject', [App\Controllers\Admin\EmployersController::class, 'rejectKycDocument'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/block', [App\Controllers\Admin\EmployersController::class, 'block'], [$adminMiddleware]);
$router->post('/admin/employers/{id}/unblock', [App\Controllers\Admin\EmployersController::class, 'unblock'], [$adminMiddleware]);

// Jobs Management (Protected)
$router->get('/admin/jobs', [App\Controllers\Admin\JobsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/jobs/{slug}', [App\Controllers\Admin\JobsController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/jobs/{slug}/approve', [App\Controllers\Admin\JobsController::class, 'approve'], [$adminMiddleware]);
$router->post('/admin/jobs/{slug}/reject', [App\Controllers\Admin\JobsController::class, 'reject'], [$adminMiddleware]);
$router->post('/admin/jobs/{slug}/take-down', [App\Controllers\Admin\JobsController::class, 'takeDown'], [$adminMiddleware]);

// Payments Management (Protected)
$router->get('/admin/payments', [App\Controllers\Admin\PaymentsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/payments/{id}', [App\Controllers\Admin\PaymentsController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/payments/{id}/refund', [App\Controllers\Admin\PaymentsController::class, 'refund'], [$adminMiddleware]);

// Subscriptions Management (Protected)
$router->get('/admin/subscriptions', [App\Controllers\Admin\SubscriptionsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/subscriptions/plans', [App\Controllers\Admin\SubscriptionsController::class, 'plans'], [$adminMiddleware]);
$router->get('/admin/subscriptions/plans/{id}/edit', [App\Controllers\Admin\SubscriptionsController::class, 'editPlan'], [$adminMiddleware]);
$router->get('/admin/subscriptions/payments/{payment_id}/invoice', [App\Controllers\Admin\SubscriptionsController::class, 'downloadInvoice'], [$adminMiddleware]);
$router->post('/admin/subscriptions/plans/{id}/duplicate', [App\Controllers\Admin\SubscriptionsController::class, 'duplicatePlan'], [$adminMiddleware]);
$router->post('/admin/subscriptions/plans', [App\Controllers\Admin\SubscriptionsController::class, 'createPlan'], [$adminMiddleware]);
$router->put('/admin/subscriptions/plans/{id}', [App\Controllers\Admin\SubscriptionsController::class, 'updatePlan'], [$adminMiddleware]);
$router->delete('/admin/subscriptions/plans/{id}', [App\Controllers\Admin\SubscriptionsController::class, 'deletePlan'], [$adminMiddleware]);
$router->get('/admin/subscriptions/{id}', [App\Controllers\Admin\SubscriptionsController::class, 'show'], [$adminMiddleware]);
// Lifecycle actions
$router->post('/admin/subscriptions/{id}/status', [App\Controllers\Admin\SubscriptionsController::class, 'updateStatus'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/change-plan', [App\Controllers\Admin\SubscriptionsController::class, 'changePlan'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/extend', [App\Controllers\Admin\SubscriptionsController::class, 'extend'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/auto-renew', [App\Controllers\Admin\SubscriptionsController::class, 'toggleAutoRenew'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/reset-usage', [App\Controllers\Admin\SubscriptionsController::class, 'resetUsage'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/grace', [App\Controllers\Admin\SubscriptionsController::class, 'setGrace'], [$adminMiddleware]);
$router->post('/admin/subscriptions/{id}/credits', [App\Controllers\Admin\SubscriptionsController::class, 'addCredit'], [$adminMiddleware]);
$router->post('/admin/subscriptions/payments/{payment_id}/regenerate-invoice', [App\Controllers\Admin\SubscriptionsController::class, 'regenerateInvoice'], [$adminMiddleware]);
$router->post('/admin/subscriptions/cleanup', [App\Controllers\Admin\SubscriptionsController::class, 'cleanupDuplicates'], [$adminMiddleware]);

// Reports (Protected)
$router->get('/admin/reports', [App\Controllers\Admin\ReportsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/reports/export', [App\Controllers\Admin\ReportsController::class, 'export'], [$adminMiddleware]);

// Settings (Protected)
$router->get('/admin/settings', [App\Controllers\Admin\SettingsController::class, 'index'], [$adminMiddleware]);
$router->post('/admin/settings', [App\Controllers\Admin\SettingsController::class, 'update'], [$adminMiddleware]);

// Employers - Featured company management
$router->post('/admin/employers/{id}/feature', [App\Controllers\Admin\EmployersController::class, 'setFeatured'], [$adminMiddleware]);

// Featured Companies - Bulk ordering page
$router->get('/admin/companies/featured', [App\Controllers\Admin\FeaturedCompaniesController::class, 'index'], [$adminMiddleware]);
$router->post('/admin/companies/featured/order', [App\Controllers\Admin\FeaturedCompaniesController::class, 'updateOrder'], [$adminMiddleware]);

// Marketing / Bulk Emails (Protected)
$router->get('/admin/marketing/campaigns', [App\Controllers\Admin\BulkEmailsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/marketing/campaigns/create', [App\Controllers\Admin\BulkEmailsController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/marketing/campaigns', [App\Controllers\Admin\BulkEmailsController::class, 'send'], [$adminMiddleware]);
$router->get('/admin/marketing/campaigns/{id}', [App\Controllers\Admin\BulkEmailsController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/marketing/campaigns/{id}/delete', [App\Controllers\Admin\BulkEmailsController::class, 'delete'], [$adminMiddleware]);

// Job Categories Management (Protected)
$router->get('/admin/job-categories', [App\Controllers\Admin\JobCategoriesController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/job-categories/create', [App\Controllers\Admin\JobCategoriesController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/job-categories', [App\Controllers\Admin\JobCategoriesController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/job-categories/{id}/edit', [App\Controllers\Admin\JobCategoriesController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/job-categories/{id}', [App\Controllers\Admin\JobCategoriesController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/job-categories/{id}/delete', [App\Controllers\Admin\JobCategoriesController::class, 'delete'], [$adminMiddleware]);

// Testimonials Management (Protected)
$router->get('/admin/testimonials', [App\Controllers\Admin\TestimonialsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/testimonials/create', [App\Controllers\Admin\TestimonialsController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/testimonials/store', [App\Controllers\Admin\TestimonialsController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/testimonials/{id}/edit', [App\Controllers\Admin\TestimonialsController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/testimonials/{id}/update', [App\Controllers\Admin\TestimonialsController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/testimonials/{id}/delete', [App\Controllers\Admin\TestimonialsController::class, 'delete'], [$adminMiddleware]);

// Interviews Control Center (Protected)
$router->get('/admin/interviews', [App\Controllers\Admin\InterviewsController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/interviews/{id}', [App\Controllers\Admin\InterviewsController::class, 'show'], [$adminMiddleware]);
$router->get('/admin/interviews/{id}/logs', [App\Controllers\Admin\InterviewsController::class, 'logs'], [$adminMiddleware]);
$router->post('/admin/interviews/{id}/force-end', [App\Controllers\Admin\InterviewsController::class, 'forceEnd'], [$adminMiddleware]);
$router->post('/admin/interviews/{id}/suspend', [App\Controllers\Admin\InterviewsController::class, 'suspend'], [$adminMiddleware]);
$router->post('/admin/interviews/{id}/join', [App\Controllers\Admin\InterviewsController::class, 'join'], [$adminMiddleware]);
$router->post('/admin/interviews/{id}/join-silent', [App\Controllers\Admin\InterviewsController::class, 'joinSilent'], [$adminMiddleware]);
$router->get('/admin/interviews/{id}/metrics', [App\Controllers\Admin\InterviewsController::class, 'metrics'], [$adminMiddleware]);
$router->get('/admin/interviews/{id}/metrics', [App\Controllers\Admin\InterviewsController::class, 'metrics'], [$adminMiddleware]);
$router->post('/admin/testimonials/{id}/toggle', [App\Controllers\Admin\TestimonialsController::class, 'toggleStatus'], [$adminMiddleware]);

// Blog Management (Protected)
$router->get('/admin/blog', [App\Controllers\Admin\BlogController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/blog/create', [App\Controllers\Admin\BlogController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/blog/store', [App\Controllers\Admin\BlogController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/blog/{id}/edit', [App\Controllers\Admin\BlogController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/update', [App\Controllers\Admin\BlogController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/delete', [App\Controllers\Admin\BlogController::class, 'delete'], [$adminMiddleware]);
$router->get('/admin/blog/{id}/preview', [App\Controllers\Admin\BlogController::class, 'preview'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/publish', [App\Controllers\Admin\BlogController::class, 'publish'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/draft', [App\Controllers\Admin\BlogController::class, 'draft'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/schedule', [App\Controllers\Admin\BlogController::class, 'schedule'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/feature', [App\Controllers\Admin\BlogController::class, 'feature'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/unfeature', [App\Controllers\Admin\BlogController::class, 'unfeature'], [$adminMiddleware]);
$router->post('/admin/blog/{id}/reorder', [App\Controllers\Admin\BlogController::class, 'reorder'], [$adminMiddleware]);
$router->post('/admin/blog/reorder-bulk', [App\Controllers\Admin\BlogController::class, 'reorderBulk'], [$adminMiddleware]);

// Blog Categories (Protected)
$router->get('/admin/blog-categories', [App\Controllers\Admin\CategoryController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/blog-categories/create', [App\Controllers\Admin\CategoryController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/blog-categories/store', [App\Controllers\Admin\CategoryController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/blog-categories/{id}/edit', [App\Controllers\Admin\CategoryController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/blog-categories/{id}/update', [App\Controllers\Admin\CategoryController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/blog-categories/{id}/delete', [App\Controllers\Admin\CategoryController::class, 'delete'], [$adminMiddleware]);

// Blog Tags (Protected)
$router->get('/admin/blog-tags', [App\Controllers\Admin\TagController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/blog-tags/create', [App\Controllers\Admin\TagController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/blog-tags/store', [App\Controllers\Admin\TagController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/blog-tags/{id}/edit', [App\Controllers\Admin\TagController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/blog-tags/{id}/update', [App\Controllers\Admin\TagController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/blog-tags/{id}/delete', [App\Controllers\Admin\TagController::class, 'delete'], [$adminMiddleware]);

// Career Insight Articles (Protected)
$router->get('/admin/career-articles', [App\Controllers\Admin\CareerArticlesController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/career-articles/create', [App\Controllers\Admin\CareerArticlesController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/career-articles/store', [App\Controllers\Admin\CareerArticlesController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/career-articles/{id}/edit', [App\Controllers\Admin\CareerArticlesController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/career-articles/{id}/update', [App\Controllers\Admin\CareerArticlesController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/career-articles/{id}/delete', [App\Controllers\Admin\CareerArticlesController::class, 'delete'], [$adminMiddleware]);
$router->get('/admin/career-articles/{id}/preview', [App\Controllers\Admin\CareerArticlesController::class, 'preview'], [$adminMiddleware]);
// Summernote image upload for career articles
$router->post('/admin/career-articles/upload-image', [App\Controllers\Admin\CareerArticlesController::class, 'uploadImage'], [$adminMiddleware]);

// Article Categories (Protected)
$router->get('/admin/article-categories', [App\Controllers\Admin\ArticleCategoriesController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/article-categories/create', [App\Controllers\Admin\ArticleCategoriesController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/article-categories/store', [App\Controllers\Admin\ArticleCategoriesController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/article-categories/{id}/edit', [App\Controllers\Admin\ArticleCategoriesController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/article-categories/{id}/update', [App\Controllers\Admin\ArticleCategoriesController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/article-categories/{id}/delete', [App\Controllers\Admin\ArticleCategoriesController::class, 'delete'], [$adminMiddleware]);
// Bulk Emails (Protected)
$router->get('/admin/bulk-emails', [App\Controllers\Admin\BulkEmailsController::class, 'index'], [$adminMiddleware]);
$router->post('/admin/bulk-emails/send', [App\Controllers\Admin\BulkEmailsController::class, 'send'], [$adminMiddleware]);
// Notification Templates (Protected)
$router->get('/admin/notification-templates', [App\Controllers\Admin\NotificationTemplatesController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/notification-templates/create', [App\Controllers\Admin\NotificationTemplatesController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/notification-templates', [App\Controllers\Admin\NotificationTemplatesController::class, 'store'], [$adminMiddleware]);
$router->get('/admin/notification-templates/{id}/edit', [App\Controllers\Admin\NotificationTemplatesController::class, 'edit'], [$adminMiddleware]);
$router->post('/admin/notification-templates/{id}', [App\Controllers\Admin\NotificationTemplatesController::class, 'update'], [$adminMiddleware]);
$router->post('/admin/notification-templates/{id}/delete', [App\Controllers\Admin\NotificationTemplatesController::class, 'delete'], [$adminMiddleware]);

// Bulk Uploaders
$router->get('/admin/bulk-uploaders', [App\Controllers\Admin\BulkUploadersController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/bulk-uploaders/create', [App\Controllers\Admin\BulkUploadersController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/create', [App\Controllers\Admin\BulkUploadersController::class, 'create'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/{id}/toggle', [App\Controllers\Admin\BulkUploadersController::class, 'toggleStatus'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/{id}/reset', [App\Controllers\Admin\BulkUploadersController::class, 'resetLimit'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/{id}/password', [App\Controllers\Admin\BulkUploadersController::class, 'resetPassword'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/{id}/delete', [App\Controllers\Admin\BulkUploadersController::class, 'deleteAccount'], [$adminMiddleware]);
$router->get('/admin/bulk-uploaders/{id}/batches', [App\Controllers\Admin\BulkUploadersController::class, 'batches'], [$adminMiddleware]);
$router->post('/admin/bulk-uploaders/{id}/credits', [App\Controllers\Admin\BulkUploadersController::class, 'addCredits'], [$adminMiddleware]);
$router->get('/admin/resumes/{id}/download', [App\Controllers\Admin\BulkUploadersController::class, 'downloadFile'], [$adminMiddleware]);
$router->get('/admin/resumes/{id}', [App\Controllers\Admin\BulkUploadersController::class, 'showFile'], [$adminMiddleware]);

use App\Controllers\Admin\AdminCookieController;
use App\Middlewares\CsrfMiddleware as AdmCsrf;
$admCsrf = new AdmCsrf();
$router->get('/admin/cookies', [AdminCookieController::class, 'index'], [$adminMiddleware]);
$router->post('/admin/cookies/category/toggle', [AdminCookieController::class, 'toggleCategory'], [$adminMiddleware, $admCsrf]);
$router->post('/admin/cookies/definition/upsert', [AdminCookieController::class, 'upsertDefinition'], [$adminMiddleware, $admCsrf]);
$router->post('/admin/cookies/policy/update', [AdminCookieController::class, 'updatePolicy'], [$adminMiddleware, $admCsrf]);
$router->post('/admin/cookies/policy/force-reconsent', [AdminCookieController::class, 'forceReconsent'], [$adminMiddleware, $admCsrf]);
$router->get('/admin/cookies/export', [AdminCookieController::class, 'exportLogs'], [$adminMiddleware]);
$router->post('/admin/cookies/consent/delete', [AdminCookieController::class, 'deleteConsent'], [$adminMiddleware, $admCsrf]);
$router->get('/admin/cookies/heatmap', [AdminCookieController::class, 'heatmap'], [$adminMiddleware]);
$router->post('/admin/resumes/{id}/approve', [App\Controllers\Admin\BulkUploadersController::class, 'approveFile'], [$adminMiddleware]);
$router->post('/admin/resumes/{id}/reject', [App\Controllers\Admin\BulkUploadersController::class, 'rejectFile'], [$adminMiddleware]);
$router->get('/admin/bulk-uploaders/{id}/candidates/export', [App\Controllers\Admin\BulkUploadersController::class, 'exportCandidates'], [$adminMiddleware]);
$router->post('/admin/resumes/{id}/delete', [App\Controllers\Admin\BulkUploadersController::class, 'deleteFile'], [$adminMiddleware]);

// Employment Verification (Admin)
use App\Controllers\Admin\EmploymentVerificationController as AdminVerificationController;
$router->get('/admin/verification', [AdminVerificationController::class, 'index'], [$adminMiddleware]);
$router->get('/admin/verification/{id}', [AdminVerificationController::class, 'show'], [$adminMiddleware]);
$router->post('/admin/verification/{id}/approve', [AdminVerificationController::class, 'approve'], [$adminMiddleware]);
$router->post('/admin/verification/{id}/reject', [AdminVerificationController::class, 'reject'], [$adminMiddleware]);
$router->post('/admin/verification/{id}/resend', [AdminVerificationController::class, 'resendHr'], [$adminMiddleware]);
$router->post('/admin/verification/{id}/report', [AdminVerificationController::class, 'generateReport'], [$adminMiddleware]);
$router->post('/admin/verification/{id}/delete', [AdminVerificationController::class, 'delete'], [$adminMiddleware]);
