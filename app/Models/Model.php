<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];

    public function getTable(): string
    {
        return $this->table;
    }

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getDb(): Database
    {
        return Database::getInstance();
    }

    public function __get(string $key)
    {
        // CRITICAL: If accessing 'attributes' property directly, return the property itself
        if ($key === 'attributes') {
            return $this->attributes;
        }
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        // CRITICAL: If setting 'attributes' property directly, set the property itself
        if ($key === 'attributes') {
            $this->attributes = $value;
            return;
        }
        $this->attributes[$key] = $value;
    }
    
    /**
     * Get attributes array directly
     * Use this method to access attributes without going through __get
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set a single attribute
     * Use this instead of direct attribute modification to avoid "Indirect modification" errors
     */
    public function setAttribute(string $key, $value): self
    {
        if (in_array($key, $this->fillable) || empty($this->fillable)) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function save(): bool
    {
        try {
            // Check if primary key exists and has a value (not null/empty)
            $primaryKeyValue = $this->attributes[$this->primaryKey] ?? null;
            if ($primaryKeyValue && $primaryKeyValue !== '0' && $primaryKeyValue !== 0) {
                return $this->update();
            }
            return $this->insert();
        } catch (\Exception $e) {
            error_log("Model save error for {$this->table}: " . $e->getMessage());
            error_log("Attributes: " . json_encode($this->attributes));
            return false;
        }
    }

    private function insert(): bool
    {
        // CRITICAL: Filter out 'attributes' key and only include fillable fields
        $fields = array_filter(array_keys($this->attributes), function($k) {
            // Never include 'attributes' as a database column
            if ($k === 'attributes') {
                return false;
            }
            // If fillable is defined, only include fillable fields
            if (!empty($this->fillable)) {
                return in_array($k, $this->fillable);
            }
            return true;
        });
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $params = array_intersect_key($this->attributes, array_flip($fields));
        
        try {
            $this->getDb()->execute($sql, $params);
            $this->attributes[$this->primaryKey] = $this->getDb()->lastInsertId();
            return true;
        } catch (\Exception $e) {
            error_log("Model insert error for {$this->table}: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Attributes: " . json_encode($this->attributes));
            return false;
        }
    }

    private function update(): bool
    {
        $id = $this->attributes[$this->primaryKey];
        // CRITICAL: Filter out 'attributes' key if it exists (shouldn't, but safety check)
        // Also filter out any keys that aren't in fillable (if fillable is defined)
        $fields = array_filter(array_keys($this->attributes), function($k) {
            // Never include 'attributes' as a database column
            if ($k === 'attributes') {
                return false;
            }
            // Exclude primary key
            if ($k === $this->primaryKey) {
                return false;
            }
            // If fillable is defined, only include fillable fields
            if (!empty($this->fillable)) {
                return in_array($k, $this->fillable);
            }
            return true;
        });
        $set = array_map(fn($f) => "$f = :$f", $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        
        $params = array_intersect_key($this->attributes, array_flip($fields));
        $params['id'] = $id;
        
        try {
            $this->getDb()->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Model update error for {$this->table}: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            return false;
        }
    }

    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        try {
            $this->getDb()->query($sql, ['id' => $this->attributes[$this->primaryKey]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Find a model by its primary key
     * @return static|null
     */
    public static function find(int $id): ?self
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->getTable()} WHERE {$model->primaryKey} = :id LIMIT 1";
        $result = $model->getDb()->fetchOne($sql, ['id' => $id]);
        
        if ($result) {
            return new static($result);
        }
        return null;
    }

    public static function where(string $field, string $operator, $value): QueryBuilder
    {
        $model = new static();
        $builder = new QueryBuilder($model);
        return $builder->where($field, $operator, $value);
    }

    public static function whereIn(string $field, array $values): QueryBuilder
    {
        $model = new static();
        $builder = new QueryBuilder($model);
        return $builder->whereIn($field, $values);
    }

    public static function all(): array
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->getTable()}";
        $results = $model->getDb()->fetchAll($sql);
        
        return array_map(fn($row) => new static($row), $results);
    }

    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }
}
