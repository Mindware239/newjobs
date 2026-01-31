<?php

use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Controllers\Sales\SalesManagerController;
use App\Controllers\Sales\SalesExecutiveController;
use App\Controllers\Sales\LeadController;
use App\Controllers\Sales\PipelineController;
use App\Controllers\Sales\NotificationController;
use App\Controllers\Sales\TeamController;
use App\Controllers\Sales\PaymentController;
use App\Controllers\Sales\AnalyticsController;
use App\Controllers\Sales\FollowupController;
use App\Controllers\Sales\CommunicationController;

$router = Router::getInstance();

$auth = new AuthMiddleware();

$router->get('/sales/manager/dashboard', [SalesManagerController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/executive/dashboard', [SalesExecutiveController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

// Alias routes for Manager and Executive to access shared resources via role-specific URLs
$router->get('/sales/manager/leads', [LeadController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/executive/leads', [LeadController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

$router->get('/sales/manager/pipeline', [PipelineController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/executive/pipeline', [PipelineController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]); // If execs have pipeline access

$router->get('/sales/manager/followups', [\App\Controllers\Sales\FollowupController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

$router->get('/sales/manager/team', [TeamController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/view-executive/{id}', [SalesManagerController::class, 'viewExecutive'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

$router->get('/sales/manager/targets', [\App\Controllers\Sales\TargetsController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/targets/{id}/edit', [\App\Controllers\Sales\TargetsController::class, 'edit'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales/manager/targets', [\App\Controllers\Sales\TargetsController::class, 'store'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/targets', [\App\Controllers\Sales\TargetsController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

$router->get('/sales/manager/campaigns', [\App\Controllers\Sales\CampaignsController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/campaigns', [\App\Controllers\Sales\CampaignsController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/campaigns/create', [\App\Controllers\Sales\CampaignsController::class, 'create'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales/manager/campaigns', [\App\Controllers\Sales\CampaignsController::class, 'store'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/campaigns/{id}/edit', [\App\Controllers\Sales\CampaignsController::class, 'edit'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales/manager/campaigns/{id}/update', [\App\Controllers\Sales\CampaignsController::class, 'update'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales/manager/campaigns/{id}/delete', [\App\Controllers\Sales\CampaignsController::class, 'destroy'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/automation', [\App\Controllers\Sales\AutomationController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/automation/create', [\App\Controllers\Sales\AutomationController::class, 'create'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales/manager/automation', [\App\Controllers\Sales\AutomationController::class, 'store'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales/manager/analytics', [\App\Controllers\Sales\AnalyticsController::class, 'index'], [$auth, new \App\Middlewares\SalesRoleMiddleware()]);

$router->get('/sales/leads', [LeadController::class, 'index'], [$auth]);
$router->get('/sales/leads/create', [LeadController::class, 'create'], [$auth]);
$router->post('/sales/leads', [LeadController::class, 'store'], [$auth]);
$router->get('/sales/leads/{id}', [LeadController::class, 'show'], [$auth]);
$router->post('/sales/leads/{id}/assign', [LeadController::class, 'assign'], [$auth]);
$router->post('/sales/leads/{id}/stage', [LeadController::class, 'updateStage'], [$auth]);
$router->post('/sales/leads/{id}/note', [LeadController::class, 'addNote'], [$auth]);
$router->post('/sales/leads/{lead_id}/notes/{id}/delete', [LeadController::class, 'deleteNote'], [$auth]);
$router->post('/sales/leads/bulk-assign', [LeadController::class, 'bulkAssign'], [$auth]);

$router->get('/sales/pipeline', [PipelineController::class, 'index'], [$auth]);
$router->post('/sales/pipeline/move', [PipelineController::class, 'move'], [$auth]);

$router->get('/sales/notifications', [NotificationController::class, 'index'], [$auth]);
$router->post('/sales/notifications/read', [NotificationController::class, 'markRead'], [$auth]);

$router->get('/sales/payments', [PaymentController::class, 'index'], [$auth]);
$router->post('/sales/payments/{id}/mark-paid', [PaymentController::class, 'markPaid'], [$auth]);
$router->post('/sales/payments/{id}/generate-link', [PaymentController::class, 'generateLink'], [$auth]);

$router->get('/sales/team', [TeamController::class, 'index'], [$auth]);
$router->post('/sales/team/add', [TeamController::class, 'add'], [$auth]);
$router->post('/sales/team/{id}/remove', [TeamController::class, 'remove'], [$auth]);

// Sales analytics APIs
$router->get('/api/sales/lead-trends', [AnalyticsController::class, 'leadTrends'], [$auth]);
$router->get('/api/sales/team-performance', [AnalyticsController::class, 'teamPerformance'], [$auth]);
$router->get('/api/sales/stage-breakdown', [AnalyticsController::class, 'stageBreakdown'], [$auth]);
$router->get('/api/sales/source-breakdown', [AnalyticsController::class, 'sourceBreakdown'], [$auth]);
$router->get('/api/sales/conversion-rate', [AnalyticsController::class, 'conversionRate'], [$auth]);
$router->post('/sales/leads/{id}/followup', [\App\Controllers\Sales\FollowupController::class, 'schedule'], [$auth]);
$router->post('/sales/leads/{id}/followup/{fid}/done', [\App\Controllers\Sales\FollowupController::class, 'markDone'], [$auth]);
$router->post('/sales/leads/{id}/communication', [\App\Controllers\Sales\CommunicationController::class, 'log'], [$auth]);
