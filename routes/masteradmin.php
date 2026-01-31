<?php

use App\Core\Router;
use App\Middlewares\SuperAdminMiddleware;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\RbacMiddleware;

$router = \App\Core\Router::getInstance();

$super = new SuperAdminMiddleware();
$admin = new AdminMiddleware();

$router->get('/master/dashboard', [\App\Controllers\MasterAdmin\DashboardController::class, 'index'], [$admin]);

$router->get('/master/roles', [\App\Controllers\MasterAdmin\RoleController::class, 'index'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/roles/create', [\App\Controllers\MasterAdmin\RoleController::class, 'create'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/roles/create', [\App\Controllers\MasterAdmin\RoleController::class, 'store'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/roles/{id}/edit', [\App\Controllers\MasterAdmin\RoleController::class, 'edit'], [new RbacMiddleware('roles.assign_permissions')]);
$router->post('/master/roles/{id}/edit', [\App\Controllers\MasterAdmin\RoleController::class, 'update'], [new RbacMiddleware('roles.assign_permissions')]);
$router->post('/master/roles/{id}/delete', [\App\Controllers\MasterAdmin\RoleController::class, 'delete'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/roles/{roleId}/users/{userId}/reset-password', [\App\Controllers\MasterAdmin\RoleController::class, 'resetUserPassword'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/roles/{roleId}/users/{userId}/remove', [\App\Controllers\MasterAdmin\RoleController::class, 'removeUserAssignment'], [new RbacMiddleware('roles.manage')]);

$router->get('/master/users', [\App\Controllers\MasterAdmin\UsersController::class, 'index'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/users/create', [\App\Controllers\MasterAdmin\UsersController::class, 'create'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/users/store', [\App\Controllers\MasterAdmin\UsersController::class, 'store'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/users/{id}/edit', [\App\Controllers\MasterAdmin\UsersController::class, 'edit'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/users/{id}/update', [\App\Controllers\MasterAdmin\UsersController::class, 'update'], [new RbacMiddleware('roles.manage')]);

$router->get('/master/permissions', [\App\Controllers\MasterAdmin\PermissionsController::class, 'index'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/permissions/create', [\App\Controllers\MasterAdmin\PermissionsController::class, 'create'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/permissions/create', [\App\Controllers\MasterAdmin\PermissionsController::class, 'store'], [new RbacMiddleware('roles.manage')]);
$router->get('/master/permissions/{id}/edit', [\App\Controllers\MasterAdmin\PermissionsController::class, 'edit'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/permissions/{id}/edit', [\App\Controllers\MasterAdmin\PermissionsController::class, 'update'], [new RbacMiddleware('roles.manage')]);
$router->post('/master/permissions/{id}/delete', [\App\Controllers\MasterAdmin\PermissionsController::class, 'delete'], [new RbacMiddleware('roles.manage')]);

$router->get('/master/sales', [\App\Controllers\MasterAdmin\SalesController::class, 'index'], [new RbacMiddleware('sales.view')]);
$router->get('/master/sales/leads', [\App\Controllers\MasterAdmin\SalesController::class, 'leads'], [new RbacMiddleware('sales.view')]);
$router->get('/master/sales/leads/create', [\App\Controllers\MasterAdmin\SalesController::class, 'create'], [new RbacMiddleware('sales.view')]);
$router->post('/master/sales/leads/store', [\App\Controllers\MasterAdmin\SalesController::class, 'store'], [new RbacMiddleware('sales.view')]);
$router->get('/master/sales/leads/{id}', [\App\Controllers\MasterAdmin\SalesController::class, 'showLead'], [new RbacMiddleware('sales.view')]);

$router->get('/master/verifications', [\App\Controllers\MasterAdmin\VerificationController::class, 'index'], [new RbacMiddleware('verification.view')]);
$router->get('/master/verifications/queue', [\App\Controllers\MasterAdmin\VerificationController::class, 'queue'], [new RbacMiddleware('verification.view')]);
$router->get('/master/verifications/{id}', [\App\Controllers\MasterAdmin\VerificationController::class, 'show'], [new RbacMiddleware('verification.view')]);

// Verification actions
$router->post('/master/verifications/assign', [\App\Controllers\MasterAdmin\VerificationController::class, 'assign'], [new RbacMiddleware('verification.assign')]);
$router->post('/master/verifications/set-level', [\App\Controllers\MasterAdmin\VerificationController::class, 'setLevel'], [new RbacMiddleware('verification.override')]);
$router->post('/master/verifications/approve', [\App\Controllers\MasterAdmin\VerificationController::class, 'approve'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/reject', [\App\Controllers\MasterAdmin\VerificationController::class, 'reject'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/escalate', [\App\Controllers\MasterAdmin\VerificationController::class, 'escalate'], [new RbacMiddleware('verification.review')]);

// Employer document-level actions
$router->post('/master/verifications/doc/approve', [\App\Controllers\MasterAdmin\VerificationController::class, 'approveDocument'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/doc/reject', [\App\Controllers\MasterAdmin\VerificationController::class, 'rejectDocument'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/doc/evidence', [\App\Controllers\MasterAdmin\VerificationController::class, 'uploadEvidence'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/doc/reverify', [\App\Controllers\MasterAdmin\VerificationController::class, 'reverifyDocument'], [new RbacMiddleware('verification.review')]);

// Candidate verification flows
$router->get('/master/verifications/candidates', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidates'], [new RbacMiddleware('verification.view')]);
$router->get('/master/verifications/candidates/{id}', [\App\Controllers\MasterAdmin\VerificationController::class, 'showCandidate'], [new RbacMiddleware('verification.view')]);
$router->post('/master/verifications/candidates/assign', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidateAssign'], [new RbacMiddleware('verification.assign')]);
$router->post('/master/verifications/candidates/approve', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidateApprove'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/candidates/reject', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidateReject'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/candidates/evidence', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidateEvidence'], [new RbacMiddleware('verification.review')]);
$router->post('/master/verifications/candidates/reverify', [\App\Controllers\MasterAdmin\VerificationController::class, 'candidateReverify'], [new RbacMiddleware('verification.review')]);

$router->get('/master/employers', [\App\Controllers\Admin\EmployersController::class, 'index'], [new RbacMiddleware('employer.manage')]);
$router->get('/master/employers/{id}', [\App\Controllers\Admin\EmployersController::class, 'show'], [new RbacMiddleware('employer.manage')]);

$router->get('/master/candidates', [\App\Controllers\Admin\CandidatesController::class, 'index'], [new RbacMiddleware('candidate.manage')]);
$router->get('/master/candidates/{id}', [\App\Controllers\Admin\CandidatesController::class, 'show'], [new RbacMiddleware('candidate.manage')]);

$router->get('/master/payments', [\App\Controllers\Admin\PaymentsController::class, 'index'], [new RbacMiddleware('payments.view')]);
$router->get('/master/subscriptions', [\App\Controllers\MasterAdmin\SubscriptionController::class, 'index'], [new RbacMiddleware('subscriptions.manage')]);

$router->get('/master/reports', [\App\Controllers\Admin\ReportsController::class, 'index'], [new RbacMiddleware('reports.view')]);
$router->get('/master/settings', [\App\Controllers\MasterAdmin\SettingsController::class, 'index'], [new RbacMiddleware('settings.manage'), new \App\Middlewares\IpWhitelistMiddleware(), new \App\Middlewares\PerformanceMiddleware()]);
$router->post('/master/settings', [\App\Controllers\MasterAdmin\SettingsController::class, 'update'], [new RbacMiddleware('settings.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/settings/live', [\App\Controllers\MasterAdmin\SettingsController::class, 'live'], [new RbacMiddleware('settings.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);

// Routes disabled until controllers are available

$router->get('/master/system/monitor', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'index'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware(), new \App\Middlewares\PerformanceMiddleware()]);
$router->get('/master/system/monitor/trends', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'trends'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/system/monitor/db', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'dbInsights'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/system/monitor/queue', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'queueCron'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/system/monitor/alerts', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'alerts'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/system/monitor/trace', [\App\Controllers\MasterAdmin\SystemMonitorController::class, 'trace'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);

$router->get('/master/system/cron', [\App\Controllers\MasterAdmin\SystemController::class, 'cron'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->post('/master/system/cron/run', [\App\Controllers\MasterAdmin\SystemController::class, 'runCron'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/system/api', [\App\Controllers\MasterAdmin\SystemController::class, 'apiKeys'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->get('/master/logs', [\App\Controllers\MasterAdmin\SystemController::class, 'logs'], [new RbacMiddleware('system.manage')]);
$router->get('/master/support', [\App\Controllers\MasterAdmin\SystemController::class, 'support'], [new RbacMiddleware('system.manage')]);
$router->get('/master/system/ip-whitelist', [\App\Controllers\MasterAdmin\SystemController::class, 'ipWhitelist'], [new RbacMiddleware('system.manage'), new \App\Middlewares\IpWhitelistMiddleware()]);
$router->post('/master/system/ip-whitelist/save', [\App\Controllers\MasterAdmin\SystemController::class, 'saveIpWhitelist'], [new RbacMiddleware('system.manage')]);
$router->post('/master/system/ip-whitelist/toggle', [\App\Controllers\MasterAdmin\SystemController::class, 'toggleIpWhitelist'], [new RbacMiddleware('system.manage')]);
$router->post('/master/system/ip-whitelist/delete', [\App\Controllers\MasterAdmin\SystemController::class, 'deleteIpWhitelist'], [new RbacMiddleware('system.manage')]);
$router->get('/master/system/panel-builder', [\App\Controllers\MasterAdmin\SystemController::class, 'panelBuilder'], [new RbacMiddleware('system.manage')]);
$router->post('/master/system/panel-builder', [\App\Controllers\MasterAdmin\SystemController::class, 'generatePanel'], [new RbacMiddleware('system.manage')]);
$router->get('/master/system/permissions/seed', [\App\Controllers\MasterAdmin\SystemController::class, 'seedPermissions'], [new RbacMiddleware('system.manage')]);
$router->get('/master/system/tickets/seed', [\App\Controllers\MasterAdmin\SystemController::class, 'seedSampleTickets'], [new RbacMiddleware('system.manage')]);
$router->get('/master/system/leads/seed', [\App\Controllers\MasterAdmin\SystemController::class, 'seedSampleLeads'], [new RbacMiddleware('system.manage')]);
$router->get('/master/impersonate/{id}', [\App\Controllers\MasterAdmin\SystemController::class, 'impersonate'], [new RbacMiddleware('impersonate.user')]);
$router->get('/master/impersonate/stop', [\App\Controllers\MasterAdmin\SystemController::class, 'stopImpersonate'], [new RbacMiddleware('impersonate.user')]);
