<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

abstract class BaseController
{
    protected ?User $currentUser = null;

    public function __construct()
    {
        $this->loadCurrentUser();
    }

    protected function loadCurrentUser(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->currentUser = User::find((int)$userId);
        }
    }

    protected function requireAuth(Request $request, Response $response): bool
    {
        if (!$this->currentUser) {
            if ($request->getMethod() === 'GET' && !$request->isAjax()) {
                $response->redirect('/login?redirect=' . urlencode($request->getPath()));
            } else {
                $response->setStatusCode(401);
                $response->json(['error' => 'Unauthorized']);
            }
            return false;
        }
        return true;
    }

    protected function requireRole(string $role, Request $request, Response $response): bool
    {
        if (!$this->requireAuth($request, $response)) {
            return false;
        }

        if ($this->currentUser->role !== $role) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/register-employer?message=' . urlencode('Please register as employer'));
            } else {
                $response->setStatusCode(403);
                $response->json(['error' => 'Forbidden']);
            }
            return false;
        }
        return true;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleParts = explode('|', $rule);

            foreach ($ruleParts as $part) {
                if ($part === 'required' && empty($value)) {
                    $errors[$field][] = "The $field field is required";
                } elseif ($part === 'email' && !empty($value)) {
                    // International email validation (RFC 5322 compliant)
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "The $field must be a valid email address";
                    } else {
                        // Additional RFC 5322 validation
                        $emailRegex = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';
                        if (!preg_match($emailRegex, $value)) {
                            $errors[$field][] = "The $field must be a valid email address";
                        }
                        // Check email length (RFC 5321: 320 characters max)
                        if (strlen($value) > 320) {
                            $errors[$field][] = "The $field must not exceed 320 characters";
                        }
                    }
                } elseif (strpos($part, 'min:') === 0) {
                    $min = (int)substr($part, 4);
                    if (strlen($value) < $min) {
                        $errors[$field][] = "The $field must be at least $min characters";
                    }
                } elseif (strpos($part, 'max:') === 0) {
                    $max = (int)substr($part, 4);
                    if (strlen($value) > $max) {
                        $errors[$field][] = "The $field must not exceed $max characters";
                    }
                } elseif ($part === 'password_strong' && !empty($value)) {
                    // International password validation (OWASP/NIST standards)
                    $passwordErrors = $this->validatePasswordStrength($value);
                    if (!empty($passwordErrors)) {
                        $errors[$field] = array_merge($errors[$field] ?? [], $passwordErrors);
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Validate password strength according to OWASP/NIST standards
     * Requirements:
     * - At least 8 characters (NIST minimum)
     * - Maximum 20 characters (NIST recommended for usability)
     * - At least one lowercase letter
     * - At least one uppercase letter
     * - At least one number
     * - At least one special character
     * - Not a common password
     */
    protected function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        // Length validation (NIST SP 800-63B: 8-64 characters, we use 8-20 for better UX)
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        if (strlen($password) > 20) {
            $errors[] = "Password must not exceed 20 characters";
        }
        
        // Character requirements (OWASP guidelines)
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $errors[] = "Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)";
        }
        
        // Check for common passwords (OWASP Top 10 weak passwords)
        $commonPasswords = [
            'password', 'password123', '12345678', '123456789', '1234567890',
            'qwerty123', 'admin123', 'letmein', 'welcome123', 'monkey123',
            'dragon', 'master', 'sunshine', 'princess', 'football',
            '123456', '1234567', 'qwerty', 'abc123', '111111',
            'admin', 'root', 'pass', 'test', 'guest'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = "Password is too common. Please choose a more unique password";
        }
        
        // Check for sequential characters (e.g., "12345", "abcde")
        if (preg_match('/(012|123|234|345|456|567|678|789|890|abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password)) {
            $errors[] = "Password should not contain sequential characters";
        }
        
        // Check for repeated characters (e.g., "aaaa", "1111")
        if (preg_match('/(.)\1{3,}/', $password)) {
            $errors[] = "Password should not contain repeated characters";
        }
        
        return $errors;
    }
}

