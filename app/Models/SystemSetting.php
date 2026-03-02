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
        // PDO::ATTR_EMULATE_PREPARES is false, so we cannot reuse named parameters.
        // We must use unique parameter names for each placeholder.
        $db->query(
            "INSERT INTO system_settings (setting_key, setting_value, setting_group) 
             VALUES (:key, :val_ins, :grp_ins) 
             ON DUPLICATE KEY UPDATE setting_value = :val_upd, setting_group = :grp_upd",
            [
                'key' => $key, 
                'val_ins' => $value, 
                'grp_ins' => $group,
                'val_upd' => $value,
                'grp_upd' => $group
            ]
        );
    }
}
