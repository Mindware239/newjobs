<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class City extends Model
{
    protected static string $table = 'cities';
    
    public $id;
    public $state_id;
    public $name;
    public $slug;
    public $latitude;
    public $longitude;
    public $created_at;
    public $updated_at;

    public function state()
    {
        // Assuming State model will be created or we just fetch manually
        // For now, let's keep it simple
        return null; 
    }
}
