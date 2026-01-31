<?php

class Benefit
{
    private $db;
    private $table = "benefits";

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get all benefits (to show in UI as checkboxes)
     */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create new benefit
     */
    public function create($name, $slug, $category)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, slug, category)
                                    VALUES (:name, :slug, :category)");
        return $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'category' => $category
        ]);
    }
}
