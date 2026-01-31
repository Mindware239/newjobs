<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SeoRule extends Model
{
    protected string $table = 'seo_rules';
    
    public $id;
    public $page_type;
    public $meta_title_template;
    public $meta_description_template;
    public $meta_keywords_template;
    public $h1_template;
    public $canonical_rule;
    public $indexable;
    public $created_at;
    public $updated_at;

    public static function findByPageType(string $pageType): ?self
    {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM seo_rules WHERE page_type = :page_type LIMIT 1", ['page_type' => $pageType]);
        
        if (!$result) {
            return null;
        }
        
        $instance = new self();
        // We need to set the attributes as well because Model uses $attributes array
        $instance->attributes = $result; 
        
        foreach ($result as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        
        return $instance;
    }
}
