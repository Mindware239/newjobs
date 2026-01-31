<?php

declare(strict_types=1);

namespace App\Models;

class SalesLeadNote extends Model
{
    protected string $table = 'sales_lead_notes';
    protected string $primaryKey = 'id';
    protected array $fillable = ['lead_id','user_id','note_text','is_pinned','created_at','updated_at'];
}

