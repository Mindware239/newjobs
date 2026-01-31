<?php

declare(strict_types=1);

namespace App\Models;

class Employer extends Model
{
    protected string $table = 'employers';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'company_name', 'company_slug', 'website', 'logo_url',
        'description', 'industry', 'size', 'address', 'country', 'state',
        'city', 'postal_code', 'verified', 'kyc_status'
    ];

    public static function findByUserId(int $userId): ?self
    {
        $instance = new self();
        $row = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->getTable()} WHERE user_id = :user_id LIMIT 1",
            ['user_id' => $userId]
        );
        return $row ? new self($row) : null;
    }

    public function user()
    {
        return User::find($this->attributes['user_id'] ?? 0);
    }

    public function settings()
    {
        return EmployerSetting::where('employer_id', '=', $this->attributes['id'])->first();
    }

    public function kycDocuments()
    {
        return EmployerKycDocument::where('employer_id', '=', $this->attributes['id'])->get();
    }

    public function jobs()
    {
        return Job::where('employer_id', '=', $this->attributes['id'])->get();
    }

    public function isKycApproved(): bool
    {
        return ($this->attributes['kyc_status'] ?? '') === 'approved';
    }

    public function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $baseSlug = $slug;
        $counter = 1;
        $currentId = $this->attributes['id'] ?? null;

        while ($this->slugExists($slug, $currentId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE company_slug = :slug";
        $params = ['slug' => $slug];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->getDb()->fetchOne($sql, $params);
        return $result !== null;
    }
}

