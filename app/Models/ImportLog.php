<?php

declare(strict_types=1);

namespace App\Models;

class ImportLog extends Model
{
    protected string $table = 'import_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'batch_id', 'admin_id', 'file_name', 'total_rows',
        'success_count', 'failed_count', 'status', 'error_log'
    ];

    public static function log(string $batchId, int $adminId, string $fileName, int $total, int $success, int $failed, string $status, array $errors = []): void
    {
        $log = new self();
        $log->fill([
            'batch_id' => $batchId,
            'admin_id' => $adminId,
            'file_name' => $fileName,
            'total_rows' => $total,
            'success_count' => $success,
            'failed_count' => $failed,
            'status' => $status,
            'error_log' => json_encode($errors)
        ]);
        $log->save();
    }
}
