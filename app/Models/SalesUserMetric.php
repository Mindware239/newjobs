<?php

declare(strict_types=1);

namespace App\Models;

class SalesUserMetric extends Model
{
    protected string $table = 'sales_user_metrics';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id','period','leads_handled','demos_done','conversions','revenue','calculated_at'];
}

