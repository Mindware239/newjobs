<?php

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\SalesRoleMiddleware;
use App\Middlewares\RbacMiddleware;
use App\Middlewares\CookieConsentMiddleware;
use App\Middlewares\AuthMiddleware;

use App\Controllers\Front\AuthController;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\AboutController;
use App\Controllers\Front\HomeController;
use App\Controllers\Front\BlogController;
use App\Controllers\Front\JobController;
use App\Controllers\Front\LegalController;
use App\Controllers\Front\HRVerificationController;
use App\Controllers\Candidate\JobController as CandidateJobController;
use App\Controllers\Candidate\PremiumController;
use App\Controllers\Company\CompanyController;
use App\Controllers\Company\CompanyFollowController;
use App\Controllers\Company\CompanyReviewController;
use App\Controllers\Interview\InterviewRoomController;
use App\Controllers\SocialServiceController;
use App\Controllers\SocialServiceController as SocialDetailsController;
use App\Controllers\Social\SocialJobsController;
use App\Controllers\Social\SocialOrganizationsController;
use App\Controllers\Social\SocialEmployerPaymentsController;
use App\Controllers\Social\CandidatesController;
use App\Controllers\Social\SocialAccountCandidateController;
use App\Controllers\EmployerAccountController;
use App\Controllers\NotificationController;
use App\Controllers\SEOController;
use App\Controllers\GeoController;
use App\Controllers\CookieController;
use App\Controllers\TrackingController;
use App\Controllers\JobAlertsController;
use App\Controllers\Gateway\RazorpayWebhookController;
use App\Controllers\Gateway\CashfreeController;
use App\Controllers\SalesManager;
use App\Controllers\SalesExecutive;
use App\Controllers\SupportExecutive;
use App\Controllers\FinanceManager;
use App\Controllers\SocialAuth;

$router = Router::getInstance();

// Security middlewares
$csrfMiddleware = new CsrfMiddleware();
$loginRateLimit = new RateLimitMiddleware(5, 60);
$formRateLimit = new RateLimitMiddleware(30, 60);
$cookieCsrf = new CsrfMiddleware();
$cookieConsentMw = new CookieConsentMiddleware();

// ==========================================
// 1. HOME & BASIC PAGES
// ==========================================
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [AboutController::class, 'index']);
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/contact', [ContactController::class, 'submitForm'], [$formRateLimit, $csrfMiddleware]);

// ==========================================
// 2. AUTH ROUTES
// ==========================================
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login'], [$loginRateLimit, $csrfMiddleware]);
$router->get('/register', [AuthController::class, 'registerCandidate']); // Default fallback
$router->post('/register', [AuthController::class, 'register'], [$formRateLimit, $csrfMiddleware]);
$router->get('/register-employer', [AuthController::class, 'registerEmployer']);
$router->post('/register-employer', [AuthController::class, 'registerEmployer'], [$formRateLimit, $csrfMiddleware]);
$router->get('/register-candidate', [AuthController::class, 'registerCandidate']);
$router->post('/register-candidate', [AuthController::class, 'registerCandidate'], [$formRateLimit, $csrfMiddleware]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [$csrfMiddleware]);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [$loginRateLimit, $csrfMiddleware]);
$router->get('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [$loginRateLimit, $csrfMiddleware]);
$router->get('/verify-account', [AuthController::class, 'verifyAccount']);
$router->post('/verify-account', [AuthController::class, 'processVerification']);

// Auth - Social Logins
$router->get('/auth/google', [AuthController::class, 'googleLogin']);
$router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);
$router->get('/auth/apple', [AuthController::class, 'appleLogin']);
$router->post('/auth/apple/callback', [AuthController::class, 'appleCallback']);
$router->get('/auth/apple/callback', [AuthController::class, 'appleCallback']);

// Auth - Phone OTP
$router->post('/auth/phone/send-otp', [AuthController::class, 'sendPhoneOtp'], [$formRateLimit, $csrfMiddleware]);
$router->post('/auth/phone/login', [AuthController::class, 'loginWithPhoneOtp'], [$loginRateLimit, $csrfMiddleware]);
$router->post('/auth/phone/register-candidate', [AuthController::class, 'registerCandidateWithPhoneOtp'], [$formRateLimit, $csrfMiddleware]);
$router->post('/auth/phone/register-employer', [AuthController::class, 'registerEmployerWithPhoneOtp'], [$formRateLimit, $csrfMiddleware]);

// ==========================================
// 3. JOB ROUTES
// ==========================================
$router->get('/jobs', [CandidateJobController::class, 'index']);
$router->get('/job-categories', [JobController::class, 'categories']);
$router->get('/job/{slug}', [JobController::class, 'show']);
$router->get('/jobs-in-{location}', [JobController::class, 'jobsByLocation']);
$router->get('/{role}-jobs-in-{location}', [JobController::class, 'jobsByRoleAndLocation']);
$router->get('/jobs-in-category/{slug}', [JobController::class, 'jobsByCategory']);
$router->get('/job-alerts', [JobAlertsController::class, 'index']);
$router->post('/job-alerts/store', [JobAlertsController::class, 'store'], [$formRateLimit, $csrfMiddleware]);

// ==========================================
// 4. COMPANY ROUTES
// ==========================================
$router->get('/company/featured', [CompanyController::class, 'featured']);
$router->get('/company/{slug}', [CompanyController::class, 'show']);
$router->get('/company/{slug}/{tab}', [CompanyController::class, 'show']);
$router->post('/company/follow', [CompanyFollowController::class, 'toggle'], [$formRateLimit, $csrfMiddleware]);
$router->post('/company/{id}/review', [CompanyReviewController::class, 'store'], [$formRateLimit, $csrfMiddleware]);

// ==========================================
// 5. BLOG ROUTES
// ==========================================
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'detail']);
$router->get('/blog/category/{slug}', [BlogController::class, 'category']);
$router->get('/blog/tag/{slug}', [BlogController::class, 'tag']);

// ==========================================
// 6. SOCIAL SERVICES ROUTES
// ==========================================
$router->get('/social-services', [SocialServiceController::class, 'index']);
$router->get('/index', [SocialServiceController::class, 'index']);
$router->get('/find-a-job', [SocialServiceController::class, 'findjob']);
$router->get('/roles', [SocialServiceController::class, 'roles']);
$router->get('/createjob', [SocialServiceController::class, 'createjob']);
$router->get('/candidate', [SocialServiceController::class, 'candidate']);
$router->get('/listings', [SocialServiceController::class, 'listings']);
$router->get('/subscriptions', [SocialServiceController::class, 'subscriptions']);
$router->get('/newsubscriptions', [SocialServiceController::class, 'newsubscriptions']);
$router->get('/employers', [SocialServiceController::class, 'employers']);
$router->get('/pricing', [SocialServiceController::class, 'pricing']);
$router->get('/aboutus', [SocialServiceController::class, 'aboutus']);
$router->get('/supports', [SocialServiceController::class, 'supports']);
$router->get('/supportss', [SocialServiceController::class, 'supportss']);
$router->get('/specials', [SocialServiceController::class, 'specials']);
$router->get('/cart', [SocialServiceController::class, 'cart']);
$router->get('/social-services/cart', [SocialServiceController::class, 'cart']);
$router->post('/social-services/cart/save', [SocialServiceController::class, 'saveCart'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-services/checkout', [SocialServiceController::class, 'checkout']);
$router->get('/social-employer/checkout', [SocialServiceController::class, 'checkout']);
$router->get('/searchEmployers', [SocialServiceController::class, 'searchEmployers']);
$router->get('/organizationDetails', [SocialServiceController::class, 'organizationDetails']);
$router->get('/hiringInsight', [SocialServiceController::class, 'hiringInsight']);
$router->get('/hiringInsight/article', [SocialServiceController::class, 'hiringInsightArticle']);
$router->get('/hiringInsightSignUp', [SocialServiceController::class, 'hiringInsightSignUp']);
$router->get('/contactus', [SocialServiceController::class, 'contactus']);
$router->get('/frequentlyCandidateAskedQuestions', [SocialServiceController::class, 'frequentlyCandidateAskedQuestions']);
$router->get('/frequentlyEmployerAskedQuestions', [SocialServiceController::class, 'frequentlyEmployerAskedQuestions']);
$router->get('/job-details', [SocialDetailsController::class, 'jobdetails']);

// Social Employer
$router->get('/social-employer/organisation', [SocialOrganizationsController::class, 'index']);
$router->get('/social-employer/orgnisation', [SocialOrganizationsController::class, 'index']); // Alias
$router->get('/social-employer/application', [SocialServiceController::class, 'application']);
$router->post('/social-employer/application/status', [SocialServiceController::class, 'applicationStatus'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-employer/newlisting', [SocialServiceController::class, 'newlisting']);
$router->post('/social-employer/newlisting', [SocialServiceController::class, 'store'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-employer/listings', [SocialServiceController::class, 'listings']);
$router->get('/social-employer/account', [EmployerAccountController::class, 'account']);
$router->post('/social-employer/account-save', [EmployerAccountController::class, 'store'], [$formRateLimit, $csrfMiddleware]);
$router->post('/social-employer/account-update', [EmployerAccountController::class, 'update'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-employer/job/{id}/edit', [SocialJobsController::class, 'edit']);
$router->post('/social-employer/job/{id}/update', [SocialJobsController::class, 'update'], [$formRateLimit, $csrfMiddleware]);
$router->post('/social-employer/job/{id}/delete', [SocialJobsController::class, 'delete'], [$formRateLimit, $csrfMiddleware]);
$router->post('/social-employer/job/{id}/status', [SocialJobsController::class, 'status'], [$formRateLimit, $csrfMiddleware]);

// Social Candidate
$router->get('/social-candidate/candidate', [CandidatesController::class, 'candidate']);
$router->post('/candidate/submit-application', [CandidatesController::class, 'submitApplication'], [$formRateLimit, $csrfMiddleware]);
$router->get('/candidatelisting', [CandidatesController::class, 'appliedJobs']);
$router->get('/social-candidate/accountcandidate', [SocialAccountCandidateController::class, 'accountcandidate']);
$router->post('/social-candidate/accountcandidate/save', [SocialAccountCandidateController::class, 'store'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-candidate/candidatesubscriptions', [SocialServiceController::class, 'candidatesubscriptions']);
$router->post('/social-candidate/candidatesubscriptions/save', [SocialServiceController::class, 'savecandidatesubscription'], [$formRateLimit, $csrfMiddleware]);

// Social Organizations & Payments
$router->post('/social/organizations/store', [SocialOrganizationsController::class, 'store'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social/organizations/search', [SocialOrganizationsController::class, 'search']);
$router->get('/social/organizations/{id}/edit', [SocialOrganizationsController::class, 'edit']);
$router->post('/social/organizations/{id}/update', [SocialOrganizationsController::class, 'update'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social/organizations/edit', [SocialOrganizationsController::class, 'editQuery']);
$router->get('/social/payments', [SocialEmployerPaymentsController::class, 'index']);
$router->post('/social/payments/store', [SocialEmployerPaymentsController::class, 'store'], [$formRateLimit, $csrfMiddleware]);

// Social Auth
$router->get('/social-services/login', [SocialAuth\AuthController::class, 'login']);
$router->post('/social-services/login', [SocialAuth\AuthController::class, 'login'], [$loginRateLimit, $csrfMiddleware]);
$router->post('/social-services/register', [SocialAuth\AuthController::class, 'register'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-services/forgot-password', [SocialAuth\AuthController::class, 'forgotPassword']);
$router->post('/social-services/forgot-password', [SocialAuth\AuthController::class, 'forgotPassword'], [$formRateLimit, $csrfMiddleware]);
$router->get('/social-services/logout', [SocialAuth\AuthController::class, 'logout']);
$router->post('/social-services/logout', [SocialAuth\AuthController::class, 'logout'], [$csrfMiddleware]);

// ==========================================
// 7. SALES & CRM ROUTES
// ==========================================
$salesMw = new SalesRoleMiddleware();
$router->get('/sales-manager/dashboard', [SalesManager\DashboardController::class, 'index'], [$salesMw]);
$router->post('/sales-manager/leads/assign', [SalesManager\DashboardController::class, 'assign'], [$salesMw]);
$router->post('/sales-manager/leads/stage', [SalesManager\DashboardController::class, 'stage'], [$salesMw]);
$router->get('/sales-manager/leads', [SalesManager\LeadController::class, 'index'], [$salesMw]);
$router->get('/sales-manager/leads/create', [SalesManager\LeadController::class, 'create'], [$salesMw]);
$router->post('/sales-manager/leads/store', [SalesManager\LeadController::class, 'store'], [$salesMw]);
$router->get('/sales-manager/leads/{id}', [SalesManager\LeadController::class, 'show'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/update', [SalesManager\LeadController::class, 'update'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/assign', [SalesManager\LeadController::class, 'assign'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/update-stage', [SalesManager\LeadController::class, 'updateStage'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/note', [SalesManager\LeadController::class, 'addNote'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/activity', [SalesManager\LeadController::class, 'addActivity'], [$salesMw]);
$router->post('/sales-manager/leads/{id}/followup', [SalesManager\LeadController::class, 'scheduleFollowup'], [$salesMw]);
$router->post('/sales-manager/leads/import-csv', [SalesManager\LeadController::class, 'importCsv'], [$salesMw]);
$router->get('/sales-manager/pipeline', [SalesManager\PipelineController::class, 'index'], [$salesMw]);
$router->post('/sales-manager/pipeline/update-stage', [SalesManager\PipelineController::class, 'updateStage'], [$salesMw]);
$router->get('/sales-manager/followups', [SalesManager\FollowupController::class, 'index'], [$salesMw]);
$router->post('/sales-manager/followups/{id}/update-status', [SalesManager\FollowupController::class, 'updateStatus'], [$salesMw]);
$router->get('/sales-manager/payments', [SalesManager\PaymentController::class, 'index'], [$salesMw]);
$router->post('/sales-manager/payments/{id}/update-status', [SalesManager\PaymentController::class, 'updateStatus'], [$salesMw]);
$router->get('/sales-manager/team', [SalesManager\TeamController::class, 'index'], [$salesMw]);
$router->get('/sales-manager/notifications', [SalesManager\NotificationController::class, 'index'], [$salesMw]);
$router->post('/sales-manager/notifications/mark-read', [SalesManager\NotificationController::class, 'markRead'], [$salesMw]);

$router->get('/sales-executive/dashboard', [SalesExecutive\DashboardController::class, 'index'], [$salesMw]);
$router->get('/sales-executive/leads', [SalesExecutive\LeadController::class, 'index'], [$salesMw]);
$router->get('/sales-executive/leads/{id}', [SalesExecutive\LeadController::class, 'show'], [$salesMw]);
$router->post('/sales-executive/leads/update', [SalesExecutive\LeadsController::class, 'update'], [$salesMw]);
$router->post('/sales-executive/leads/{id}/update-stage', [SalesExecutive\LeadController::class, 'updateStage'], [$salesMw]);
$router->post('/sales-executive/leads/{id}/note', [SalesExecutive\LeadController::class, 'addNote'], [$salesMw]);
$router->post('/sales-executive/leads/{id}/activity', [SalesExecutive\LeadController::class, 'addActivity'], [$salesMw]);
$router->post('/sales-executive/leads/{id}/followup', [SalesExecutive\LeadController::class, 'scheduleFollowup'], [$salesMw]);
$router->get('/sales-executive/pipeline', [SalesExecutive\PipelineController::class, 'index'], [$salesMw]);
$router->get('/sales-executive/followups', [SalesExecutive\FollowupController::class, 'index'], [$salesMw]);
$router->get('/sales-executive/notifications', [SalesExecutive\NotificationController::class, 'index'], [$salesMw]);
$router->post('/sales-executive/notifications/mark-read', [SalesExecutive\NotificationController::class, 'markRead'], [$salesMw]);

// Support Executive
$router->get('/support-exec/tickets', [SupportExecutive\TicketsController::class, 'index'], [new RbacMiddleware('support.tickets.view')]);
$router->get('/support-exec/tickets/{id}', [SupportExecutive\TicketsController::class, 'show'], [new RbacMiddleware('support.tickets.view')]);
$router->post('/support-exec/tickets/assign', [SupportExecutive\TicketsController::class, 'assign'], [new RbacMiddleware('support.tickets.assign')]);
$router->post('/support-exec/tickets/reply', [SupportExecutive\TicketsController::class, 'reply'], [new RbacMiddleware('support.tickets.reply')]);
$router->post('/support-exec/tickets/close', [SupportExecutive\TicketsController::class, 'close'], [new RbacMiddleware('support.tickets.close')]);
$router->post('/support-exec/tickets/escalate', [SupportExecutive\TicketsController::class, 'escalate'], [new RbacMiddleware('support.escalate')]);

// Finance Manager
$router->get('/finance/payments', [FinanceManager\PaymentsController::class, 'index'], [new RbacMiddleware('payments.view')]);
$router->get('/finance/payments/{id}', [FinanceManager\PaymentsController::class, 'show'], [new RbacMiddleware('payments.view')]);
$router->post('/finance/payments/approve', [FinanceManager\PaymentsController::class, 'approve'], [new RbacMiddleware('payments.approve')]);
$router->post('/finance/payments/refund', [FinanceManager\PaymentsController::class, 'refund'], [new RbacMiddleware('payments.refund')]);

// ==========================================
// 8. PAYMENT & WEBHOOK ROUTES
// ==========================================
$router->post('/webhook/razorpay', [RazorpayWebhookController::class, 'handle']);
$router->get('/gateway/cashfree/create-order', [CashfreeController::class, 'createOrder']);
$router->get('/gateway/cashfree/verify', [CashfreeController::class, 'verifyPayment']);
$router->post('/gateway/cashfree/webhook', [CashfreeController::class, 'webhook']);
$router->get('/candidate/premium/cashfree/verify', [PremiumController::class, 'cashfreeVerify']);
$router->post('/candidate/premium/cashfree/webhook', [PremiumController::class, 'cashfreeWebhook']);

// ==========================================
// 9. SEO & TRACKING ROUTES
// ==========================================
$router->get('/sitemap.xml', [SEOController::class, 'index']);
$router->get('/sitemap-main.xml', [SEOController::class, 'main']);
$router->get('/sitemap-jobs.xml', [SEOController::class, 'jobs']);
$router->get('/sitemap-countries.xml', [SEOController::class, 'countries']);
$router->get('/sitemap-states.xml', [SEOController::class, 'states']);
$router->get('/sitemap-cities.xml', [SEOController::class, 'cities']);
$router->get('/sitemap-categories.xml', [SEOController::class, 'categories']);
$router->get('/sitemap-skills.xml', [SEOController::class, 'skills']);
$router->get('/sitemap-companies.xml', [SEOController::class, 'companies']);
$router->get('/robots.txt', [SEOController::class, 'robots']);

$router->get('/cookie/status', [CookieController::class, 'getConsentStatus'], [$cookieConsentMw]);
$router->post('/cookie/consent', [CookieController::class, 'saveConsent'], [$cookieCsrf, $cookieConsentMw]);
$router->post('/cookie/withdraw', [CookieController::class, 'withdrawConsent'], [$cookieCsrf, $cookieConsentMw]);
$router->get('/js/consent-manager.js', [CookieController::class, 'serveConsentJS']);
$router->get('/js/script-loader.js', [CookieController::class, 'serveScriptLoaderJS']);
$router->get('/cookie/policy', [CookieController::class, 'policy']);
$router->get('/cookie/scripts', [CookieController::class, 'getScriptControls']);

$router->get('/track/visitor', [TrackingController::class, 'trackVisitor'], [$cookieConsentMw]);
$router->post('/track/session/start', [TrackingController::class, 'startSession'], [$cookieConsentMw]);
$router->post('/track/session/end', [TrackingController::class, 'endSession'], [$cookieConsentMw]);
$router->post('/track/event', [TrackingController::class, 'trackEvent'], [$cookieConsentMw, $cookieCsrf]);
$router->post('/track/heatmap', [TrackingController::class, 'trackHeatmap'], [$cookieConsentMw, $cookieCsrf]);

$router->get('/api/geo/reverse', [GeoController::class, 'reverse']);
$router->get('/api/geo/search', [GeoController::class, 'search']);

// ==========================================
// 10. NOTIFICATIONS & INTERVIEW ROUTES
// ==========================================
$router->get('/notifications/track/open', [NotificationController::class, 'trackOpen']);
$router->get('/notifications/track/click', [NotificationController::class, 'trackClick']);

$router->get('/interviews/{id}/room', [InterviewRoomController::class, 'room']);
$router->get('/interviews/{id}/state', [InterviewRoomController::class, 'state']);
$router->post('/interviews/{id}/start', [InterviewRoomController::class, 'start']);
$router->post('/interviews/{id}/end', [InterviewRoomController::class, 'end']);
$router->post('/interviews/{id}/events', [InterviewRoomController::class, 'event']);
$router->get('/interviews/{id}/analytics', [InterviewRoomController::class, 'analytics']);
$router->get('/interview/join', [InterviewRoomController::class, 'joinWithToken']);

// ==========================================
// 11. LEGAL PAGES & HR VERIFICATION
// ==========================================
$router->get('/terms', [LegalController::class, 'terms']);
$router->get('/privacy', [LegalController::class, 'privacy']);
$router->get('/grievances', [LegalController::class, 'grievances']);
$router->get('/refund-cancellation-policy', [LegalController::class, 'refundCancellationPolicy']);

$router->get('/hr/verify', [HRVerificationController::class, 'show']);
$router->post('/hr/verify', [HRVerificationController::class, 'submit'], [$formRateLimit, $csrfMiddleware]);

// ==========================================
// 12. SWAGGER ROUTES
// ==========================================
use App\Controllers\Front\SwaggerController;

$router->get('/swagger', [SwaggerController::class, 'ui']);
$router->get('/swagger.json', [SwaggerController::class, 'json']);
