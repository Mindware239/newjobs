<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramString] = explode(':', $rule);
                    $params = explode(',', $paramString);
                }

                $methodName = 'validate' . str_replace('_', '', ucwords($rule, '_'));
                if (method_exists($this, $methodName)) {
                    if (!$this->$methodName($field, $value, $params)) {
                        // Stop after first error for a field
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    // --- Validation Rules ---

    private function validateRequired(string $field, $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->addError($field, "The {$field} field is required.");
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, $value): bool
    {
        if (empty($value)) return true;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The {$field} must be a valid email address.");
            return false;
        }
        return true;
    }

    private function validateNumeric(string $field, $value): bool
    {
        if (empty($value)) return true;
        if (!is_numeric($value)) {
            $this->addError($field, "The {$field} must be a number.");
            return false;
        }
        return true;
    }

    private function validateString(string $field, $value): bool
    {
        if (empty($value)) return true;
        if (!is_string($value)) {
            $this->addError($field, "The {$field} must be a string.");
            return false;
        }
        return true;
    }

    private function validatePhone(string $field, $value): bool
    {
        if (empty($value)) return true;
        // Basic international phone regex
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', (string)$value)) {
            $this->addError($field, "The {$field} format is invalid.");
            return false;
        }
        return true;
    }

    private function validateMin(string $field, $value, array $params): bool
    {
        if (empty($value)) return true;
        $min = (int)$params[0];
        if (is_string($value) && strlen($value) < $min) {
            $this->addError($field, "The {$field} must be at least {$min} characters.");
            return false;
        }
        if (is_numeric($value) && $value < $min) {
            $this->addError($field, "The {$field} must be at least {$min}.");
            return false;
        }
        return true;
    }

    private function validateMax(string $field, $value, array $params): bool
    {
        if (empty($value)) return true;
        $max = (int)$params[0];
        if (is_string($value) && strlen($value) > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max} characters.");
            return false;
        }
        if (is_numeric($value) && $value > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max}.");
            return false;
        }
        return true;
    }

    private function validateIn(string $field, $value, array $params): bool
    {
        if (empty($value)) return true;
        if (!in_array((string)$value, $params)) {
            $this->addError($field, "The selected {$field} is invalid.");
            return false;
        }
        return true;
    }

    private function validatePasswordStrong(string $field, $value): bool
    {
        if (empty($value)) return true;

        $password = (string)$value;
        if (strlen($password) < 8) {
            $this->addError($field, "Password must be at least 8 characters long");
            return false;
        }
        if (strlen($password) > 20) {
            $this->addError($field, "Password must not exceed 20 characters");
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->addError($field, "Password must contain at least one lowercase letter");
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->addError($field, "Password must contain at least one uppercase letter");
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->addError($field, "Password must contain at least one number");
            return false;
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $this->addError($field, "Password must contain at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)");
            return false;
        }

        $commonPasswords = [
            'password', 'password123', '12345678', '123456789', '1234567890',
            'qwerty123', 'admin123', 'letmein', 'welcome123', 'monkey123',
            'dragon', 'master', 'sunshine', 'princess', 'football',
            '123456', '1234567', 'qwerty', 'abc123', '111111',
            'admin', 'root', 'pass', 'test', 'guest'
        ];
        if (in_array(strtolower($password), $commonPasswords, true)) {
            $this->addError($field, "Password is too common. Please choose a more unique password");
            return false;
        }
        if (preg_match('/(012|123|234|345|456|567|678|789|890|abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password)) {
            $this->addError($field, "Password should not contain sequential characters");
            return false;
        }
        if (preg_match('/(.)\1{3,}/', $password)) {
            $this->addError($field, "Password should not contain repeated characters");
            return false;
        }

        return true;
    }
}
