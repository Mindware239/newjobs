<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

abstract class ApiController extends BaseController
{
    /**
     * Get the authenticated user from the request (set by ApiAuthMiddleware)
     */
    protected function user(Request $request): ?User
    {
        return $request->user();
    }

    /**
     * Standardized success response
     */
    protected function success(Response $response, $data = [], string $message = "Success", int $code = 200): void
    {
        $response->json($data, $code, $message, true);
    }

    /**
     * Standardized error response
     */
    protected function error(Response $response, string $message, int $code = 400, ?array $errors = null): void
    {
        $response->error($message, $code, $errors);
    }

    /**
     * Standardized validation error response
     */
    protected function validationError(Response $response, array $errors): void
    {
        $this->error($response, "Validation failed", 422, $errors);
    }
}
