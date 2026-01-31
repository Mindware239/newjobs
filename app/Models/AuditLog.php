<?php

declare(strict_types=1);

namespace App\Models;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'entity_type', 'entity_id', 'action', 'performed_by', 'metadata'
    ];

    public static function log(string $entityType, int $entityId, string $action, ?int $performedBy = null, array $metadata = []): void
    {
        $log = new self();
        $log->fill([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'performed_by' => $performedBy,
            'metadata' => json_encode($metadata)
        ]);
        $log->save();
    }
}

