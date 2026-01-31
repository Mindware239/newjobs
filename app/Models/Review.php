<?php

class Review extends Model
{
    // Note: Model assumes a 'reviews' table exists with appropriate schema.

    /**
     * Saves a new review record.
     * @return int|bool The new review ID or false on failure.
     */
    public function saveReview($data)
    {
        // Fields assumed to be present in $data: company_id, user_id, reviewer_name, rating, title, review_text
        $fields = ['company_id', 'user_id', 'reviewer_name', 'rating', 'title', 'review_text'];
        
        $insertFields = [];
        $placeholders = [];
        $values = [];
        
        foreach ($fields as $field) {
            // Check for existence and handle optional fields like user_id
            if (array_key_exists($field, $data)) { 
                $insertFields[] = "`" . $field . "`";
                $placeholders[] = "?";
                $values[] = $data[$field];
            }
        }
        
        $sql = "INSERT INTO reviews (" . implode(', ', $insertFields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}