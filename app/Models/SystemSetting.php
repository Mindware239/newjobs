<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SystemSetting extends Model
{
    protected string $table = 'system_settings';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'setting_key', 'setting_value', 'setting_group'
    ];

    public static function get(string $key, $default = null)
    {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = :key", ['key' => $key]);
        return $row ? $row['setting_value'] : $default;
    }

    public static function set(string $key, $value, string $group = 'general'): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO system_settings (setting_key, setting_value, setting_group) 
             VALUES (:key, :value, :group) 
             ON DUPLICATE KEY UPDATE setting_value = :value, setting_group = :group",
            ['key' => $key, 'value' => $value, 'group' => $group]
        );
    }
}
