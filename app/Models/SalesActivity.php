<?php

declare(strict_types=1);

namespace App\Models;

class SalesActivity extends Model
{
    protected string $table = 'sales_lead_activities';
    protected string $primaryKey = 'id';
    protected array $fillable = ['lead_id','user_id','type','old_stage_id','new_stage_id','title','description','created_at'];
}
