<?php

declare(strict_types=1);

namespace App\Models;

class LeadAssignment extends Model
{
    protected string $table = 'sales_lead_assignments';
    protected string $primaryKey = 'id';
    protected array $fillable = ['lead_id','assigned_to_id','assigned_by_id','assigned_at','is_active'];
}
