<?php

declare(strict_types=1);

namespace App\Models;

class EmployerSetting extends Model
{
    protected string $table = 'employer_settings';
    protected string $primaryKey = 'employer_id';
    protected array $fillable = [
        'employer_id', 'billing_plan', 'credits', 'timezone', 'notification_pref'
    ];

    public function save(): bool
    {
        // For employer_settings, always use INSERT ... ON DUPLICATE KEY UPDATE
        // since employer_id is the primary key and we want to insert or update
        $employerId = $this->attributes['employer_id'] ?? null;
        if (!$employerId) {
            error_log("EmployerSetting save failed: employer_id is required");
            return false;
        }

        $fields = array_keys($this->attributes);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        $updateFields = array_filter($fields, fn($f) => $f !== 'employer_id');
        $set = array_map(fn($f) => "$f = :$f", $updateFields);
        
        if (empty($set)) {
            // No fields to update, just insert
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
        } else {
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $set);
        }
        
        try {
            $this->getDb()->query($sql, $this->attributes);
            return true;
        } catch (\Exception $e) {
            error_log("EmployerSetting save error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Attributes: " . json_encode($this->attributes));
            return false;
        }
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function hasCredits(int $required = 1): bool
    {
        return ($this->attributes['credits'] ?? 0) >= $required;
    }

    public function deductCredits(int $amount): bool
    {
        $current = $this->attributes['credits'] ?? 0;
        if ($current < $amount) {
            return false;
        }
        $this->attributes['credits'] = $current - $amount;
        return $this->save();
    }

    public function addCredits(int $amount): bool
    {
        $current = $this->attributes['credits'] ?? 0;
        $this->attributes['credits'] = $current + $amount;
        return $this->save();
    }
}

