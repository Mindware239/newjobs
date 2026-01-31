<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'email', 'password_hash', 'role', 'status', 'phone', 'notification_preferences',
        'is_email_verified', 'is_phone_verified', 'twofa_secret', 'last_login',
        'google_id', 'google_email', 'google_name', 'google_picture',
        'apple_id', 'apple_email', 'apple_name', 'fcm_token'
    ];

    protected array $hidden = ['password_hash', 'twofa_secret'];

    /* ========================== ✅ PASSWORD ========================== */

    public function setPassword(string $password): void
    {
        $this->attributes['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password_hash'] ?? '');
    }

    /* ========================== ✅ EXISTING ROLE SYSTEM ========================== */

    public function isEmployer(): bool
    {
        return $this->attributes['role'] === 'employer';
    }

    public function isCandidate(): bool
    {
        return $this->attributes['role'] === 'candidate';
    }

    public function isAdmin(): bool
    {
        return in_array($this->attributes['role'] ?? '', ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->attributes['role'] === 'super_admin';
    }

    /* ========================== ✅ RELATIONSHIPS ========================== */

    public function employer(): ?Employer
    {
        $userId = $this->id ?? $this->attributes['id'] ?? null;

        if (!$userId) return null;

        return Employer::where('user_id', '=', $userId)->first();
    }

    public function candidate(): ?Candidate
    {
        return Candidate::where('user_id', '=', $this->id)->first();
    }

    /* ========================== ✅ NOTIFICATIONS ========================== */

    public function getNotificationPreferences(): array
    {
        $prefs = $this->attributes['notification_preferences'] ?? null;
        if (empty($prefs)) {
            return [];
        }
        return is_string($prefs) ? json_decode($prefs, true) : $prefs;
    }

    public function setNotificationPreferences(array $preferences): void
    {
        $this->attributes['notification_preferences'] = json_encode($preferences);
    }

    /* ========================== ✅ RBAC LAYER ========================== */

    // ✅ GET ALL ROLES
    public function roles(): array
    {
        $db = Database::getInstance();
        try {
            return $db->fetchAll(
                "SELECT r.* FROM roles r \n                 INNER JOIN role_user ru ON r.id = ru.role_id \n                 WHERE ru.user_id = :uid",
                ['uid' => $this->id]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    // ✅ CHECK SINGLE ROLE
    public function hasRole(string $slug): bool
    {
        foreach ($this->roles() as $role) {
            if (($role['slug'] ?? null) === $slug) return true;
        }
        return false;
    }

    // ✅ CHECK PERMISSION
    public function hasPermission(string $permission): bool
    {
        $db = Database::getInstance();
        try {
            $row = $db->fetchOne(
                "SELECT COUNT(*) as cnt \n                 FROM permissions p \n                 INNER JOIN permission_role pr ON p.id = pr.permission_id \n                 INNER JOIN role_user ru ON pr.role_id = ru.role_id \n                 WHERE ru.user_id = :uid AND p.slug = :slug",
                ['uid' => $this->id, 'slug' => $permission]
            );
            return (int)($row['cnt'] ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ✅ FINAL AUTH CHECK
    public function can(string $permission): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->hasPermission($permission);
    }
}
