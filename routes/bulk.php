<?php

use App\Core\Router;
use App\Controllers\Bulk\BulkUploadController;

$router = \App\Core\Router::getInstance();

$router->get('/bulk/login', [BulkUploadController::class, 'login']);
$router->post('/bulk/login', [BulkUploadController::class, 'login']);
$router->get('/bulk/logout', [BulkUploadController::class, 'logout']);
$router->get('/bulk/upload', [BulkUploadController::class, 'upload']);
$router->post('/bulk/upload', [BulkUploadController::class, 'upload']);
$router->get('/bulk/batches/{id}', [BulkUploadController::class, 'batch']);
$router->get('/bulk/files', [BulkUploadController::class, 'files']);
$router->get('/bulk/files/{id}/download', [BulkUploadController::class, 'download']);
