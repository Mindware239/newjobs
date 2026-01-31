<?php

namespace App\Controllers\Company;

use App\Controllers\BaseController;
use App\Models\Company;

class EmployerOnboardingController extends BaseController
{
    public function create(): void {}
    public function store(): void {}
    
    private function getAuthenticatedEmployerId() { return 1; }
    private function validateAndSanitize($postData) { return $postData; }
    private function generateSlug($name) { 
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')); 
    }
}
