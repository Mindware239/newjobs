<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Helpers\Validator;

abstract class ApiController extends BaseController
{
    /**
     * Get authenticated user (from middleware)
     */
    protected function user(Request $request): ?User
    {
        return $request->user();
    }

    /**
     * Validate request data (FIXED - return type must match BaseController)
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();

        if (!$validator->validate($data, $rules)) {
            return $validator->getErrors(); // always array
        }

        return []; // IMPORTANT: never return null
    }

    /**
     * Standard success response
     */
    protected function success(
        Response $response,
        $data = [],
        string $message = "Success",
        int $code = 200
    ): void {
        $response->json([
            'status' => true,
            'success' => true,
            'message' => $message,
            'data' => $data,
            'error' => null,
            'errors' => null
        ], $code);
    }

    /**
     * Standard error response
     */
    protected function error(
        Response $response,
        string $message,
        int $code = 400,
        ?array $errors = null
    ): void {
        $response->json([
            'status' => false,
            'success' => false,
            'message' => $message,
            'data' => null,
            'error' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Validation error helper
     */
    protected function validationError(Response $response, array $errors): void
    {
        $this->error($response, "Validation failed", 422, $errors);
    }

    /**
     * Recursive sanitize (safe input handling)
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } elseif (is_string($data)) {
            $data = trim($data);
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $data;
    }
}