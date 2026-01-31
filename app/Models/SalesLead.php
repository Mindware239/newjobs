<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SalesLead extends Model
{
    protected string $table = 'sales_leads';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'company_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'stage',
        'assigned_to',
        'source',
        'deal_value',
        'currency',
        'next_followup_at',
        'followup_status',
        'manager_id',
        'paid_amount',
        'employer_id',
        'notes',
        'created_at',
        'updated_at'
    ];

    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    public static function findAll(): array
    {
        return self::all();
    }

    public static function forExecutive(int $userId, ?string $stage = null, int $limit = 100): array
    {
        $db = Database::getInstance();
        $params = ['uid' => $userId];
        $sql = 'SELECT * FROM sales_leads WHERE assigned_to = :uid';
        if ($stage) { $sql .= ' AND stage = :stage'; $params['stage'] = $stage; }
        $sql .= ' ORDER BY updated_at DESC LIMIT ' . (int)$limit;
        $rows = $db->fetchAll($sql, $params);
        return array_map(fn($r) => new self($r), $rows);
    }

    public static function getCampaignLeads(int $campaignId, int $limit = 100): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT * FROM sales_leads WHERE campaign = :cid ORDER BY created_at DESC LIMIT ' . (int)$limit,
            ['cid' => $campaignId] // Assuming 'campaign' column stores ID or name. Schema said 'campaign' varchar(128).
            // Ideally it should be campaign_id, but schema says campaign. Let's assume it matches the ID if it's numeric or name if string.
            // Wait, schema check said: campaign (varchar(128)). 
            // If the campaign logic uses IDs, we should probably update the table to use campaign_id.
            // But for now, let's assume the controller stores the campaign name or ID as string.
        );
        return array_map(fn($r) => new self($r), $rows);
    }

    public static function stats(): array
    {
        $db = Database::getInstance();
        $stats = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'demo_done' => 0,
            'follow_up' => 0,
            'payment_pending' => 0,
            'converted' => 0,
            'lost' => 0,
        ];
        $rows = $db->fetchAll('SELECT stage, COUNT(*) c FROM sales_leads GROUP BY stage');
        foreach ($rows as $r) { $stats[$r['stage']] = (int)$r['c']; $stats['total'] += (int)$r['c']; }
        return $stats;
    }
}
