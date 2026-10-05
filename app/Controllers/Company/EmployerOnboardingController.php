<?php

namespace App\Controllers\Company;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Company;

class EmployerOnboardingController extends BaseController
{
    public function create(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'message' => 'Employer onboarding flow is not implemented yet.'
        ], 501);
    }

    public function store(Request $request, Response $response): void
    {
        $response->json([
            'success' => false,
            'message' => 'Employer onboarding flow is not implemented yet.'
        ], 501);
    }
    
    private function getAuthenticatedEmployerId(): int
    {
        return (int)($_SESSION['employer_id'] ?? 0);
    }

    private function validateAndSanitize(array $postData): array
    {
        return $postData;
    }

    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}
