<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\AddressHelper;

class Employer extends Model
{
    protected string $table = 'employers';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'register_as', 'company_name', 'company_slug', 'website', 'logo_url',
        'description', 'industry', 'company_type', 'profession_type', 'service_category', 'size', 
        'address', 'country', 'state', 'city', 'postal_code', 'tax_id', 'verified', 'kyc_status'
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

    public function isRegistrationFieldsComplete(): bool
    {
        return $this->isBasicInfoComplete();
    }

    public function isBasicInfoComplete(): bool
    {
        $regAs = $this->attributes['register_as'] ?? 'company';
        $company = trim((string)($this->attributes['company_name'] ?? ''));
        $size = trim((string)($this->attributes['size'] ?? ''));
        $industry = trim((string)($this->attributes['industry'] ?? ''));
        $compType = trim((string)($this->attributes['company_type'] ?? ''));

        if ($company === '') return false;

        if ($regAs === 'company') {
            if ($size === '' || $industry === '' || $compType === '') return false;
        } else {
            $profType = trim((string)($this->attributes['profession_type'] ?? ''));
            $svcCat = trim((string)($this->attributes['service_category'] ?? ''));
            if ($profType === '' || $svcCat === '') return false;
        }

        return true;
    }

    public function isAddressComplete(): bool
    {
        $country = trim((string)($this->attributes['country'] ?? ''));
        $state = trim((string)($this->attributes['state'] ?? ''));
        $city = trim((string)($this->attributes['city'] ?? ''));
        $postal = trim((string)($this->attributes['postal_code'] ?? ''));

        $addr = AddressHelper::normalize($this->attributes['address'] ?? null);
        $street = trim((string)($addr['street'] ?? ''));

        return $country !== '' && $state !== '' && $city !== '' && $postal !== '' && $street !== '';
    }

    public function nextProfileStep(): int
    {
        if (!$this->isBasicInfoComplete()) {
            return 1;
        }

        if (!$this->isAddressComplete()) {
            return 2;
        }

        return 3;
    }

    public function isProfileComplete(): bool
    {
        return $this->isBasicInfoComplete() && $this->isAddressComplete();
    }

    public function hasConsumedFreeJob(): bool
    {
        // Check if free job was already consumed by this employer ID
        try {
            $row = $this->getDb()->fetchOne(
                "SELECT id FROM subscription_usage_logs WHERE employer_id = :eid AND action_type = 'free_job_used' LIMIT 1",
                ['eid' => (int)$this->id]
            );
            if ($row !== null) return true;
        } catch (\Throwable $t) {}

        // Identity-based consumption: match by email/phone across employers
        try {
            $user = $this->user();
            if (!$user) return false;

            $email = (string)($user->attributes['email'] ?? '');
            $phoneRaw = (string)($user->attributes['phone'] ?? '');
            $phone = preg_replace('/\D+/', '', $phoneRaw);
            
            if ($email !== '' || $phone !== '') {
                $params = [];
                $where = [];
                if ($email !== '') { $where[] = 'u.email = :email'; $params['email'] = $email; }
                if ($phone !== '') { $where[] = 'REPLACE(REPLACE(REPLACE(u.phone, "-", ""), " ", ""), "+", "") LIKE :phone'; $params['phone'] = '%' . $phone . '%'; }
                
                if (!empty($where)) {
                    $sql = "SELECT l.id 
                            FROM subscription_usage_logs l 
                            INNER JOIN employers e ON e.id = l.employer_id 
                            INNER JOIN users u ON u.id = e.user_id 
                            WHERE l.action_type = 'free_job_used' 
                            AND (" . implode(' OR ', $where) . ")
                            LIMIT 1";
                    $exists = $this->getDb()->fetchOne($sql, $params);
                    return $exists !== null;
                }
            }
        } catch (\Throwable $t) {}

        return false;
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

