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
        // Use :field_ins for insert values to avoid conflict with update values
        $placeholders = array_map(fn($f) => ":{$f}_ins", $fields);
        
        $updateFields = array_filter($fields, fn($f) => $f !== 'employer_id');
        // Use :field_upd for update values
        $set = array_map(fn($f) => "$f = :{$f}_upd", $updateFields);
        
        if (empty($set)) {
            // No fields to update, just insert (ignore if exists? or just insert)
            // If no fields to update, we can't really do ON DUPLICATE KEY UPDATE nothing.
            // But usually there are fields.
            // Let's just do a simple insert.
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            // Map params to _ins
            $params = [];
            foreach ($this->attributes as $key => $val) {
                $params["{$key}_ins"] = $val;
            }
        } else {
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $set);
            
            // Map params to _ins and _upd
            $params = [];
            foreach ($this->attributes as $key => $val) {
                $params["{$key}_ins"] = $val;
                if ($key !== 'employer_id') {
                    $params["{$key}_upd"] = $val;
                }
            }
        }
        
        try {
            $this->getDb()->query($sql, $params);
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

