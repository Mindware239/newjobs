<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class QueryBuilder
{
    protected Model $model;
    protected array $wheres = [];
    protected array $orderBys = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function where(string $field, string $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'Basic',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $this->wheres[] = [
            'type' => 'In',
            'field' => $field,
            'values' => array_values($values)
        ];
        return $this;
    }

    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->orderBys[] = [
            'field' => $field,
            'direction' => strtoupper($direction)
        ];

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $table = $this->model->getTable();
        $sql = "SELECT * FROM {$table}";
        $params = [];

        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            foreach ($this->wheres as $index => $where) {
                if (($where['type'] ?? '') === 'In') {
                    $vals = $where['values'] ?? [];
                    if (empty($vals)) {
                        $conditions[] = "1=0";
                        continue;
                    }
                    $placeholders = [];
                    foreach ($vals as $k => $v) {
                        $p = "w_{$index}_{$where['field']}_{$k}";
                        $placeholders[] = ":{$p}";
                        $params[$p] = $v;
                    }
                    $conditions[] = "{$where['field']} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $paramName = "w_{$index}_{$where['field']}";
                    $conditions[] = "{$where['field']} {$where['operator']} :{$paramName}";
                    $params[$paramName] = $where['value'];
                }
            }
            $sql .= implode(' AND ', $conditions);
        }

        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY ";
            $orders = [];
            foreach ($this->orderBys as $orderBy) {
                $orders[] = "{$orderBy['field']} {$orderBy['direction']}";
            }
            $sql .= implode(', ', $orders);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        $db = Database::getInstance();
        $results = $db->fetchAll($sql, $params);

        $models = [];
        foreach ($results as $row) {
            $instance = new $this->model($row);
            if (method_exists($instance, 'fill')) {
                $instance->fill($row);
            }
            $models[] = $instance;
        }

        return $models;
    }

    public function first(): ?Model
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }
    
    public function count(): int
    {
        $table = $this->model->getTable();
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        $params = [];

        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            foreach ($this->wheres as $index => $where) {
                if (($where['type'] ?? '') === 'In') {
                    $vals = $where['values'] ?? [];
                    if (empty($vals)) {
                        $conditions[] = "1=0";
                        continue;
                    }
                    $placeholders = [];
                    foreach ($vals as $k => $v) {
                        $p = "w_{$index}_{$where['field']}_{$k}";
                        $placeholders[] = ":{$p}";
                        $params[$p] = $v;
                    }
                    $conditions[] = "{$where['field']} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $paramName = "w_{$index}_{$where['field']}";
                    $conditions[] = "{$where['field']} {$where['operator']} :{$paramName}";
                    $params[$paramName] = $where['value'];
                }
            }
            $sql .= implode(' AND ', $conditions);
        }
        
        $db = Database::getInstance();
        $result = $db->fetchOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    public function pluck(string $column): array
    {
        $table = $this->model->getTable();
        $sql = "SELECT {$column} FROM {$table}";
        $params = [];

        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            foreach ($this->wheres as $index => $where) {
                if (($where['type'] ?? '') === 'In') {
                    $vals = $where['values'] ?? [];
                    if (empty($vals)) {
                        $conditions[] = "1=0";
                        continue;
                    }
                    $placeholders = [];
                    foreach ($vals as $k => $v) {
                        $p = "w_{$index}_{$where['field']}_{$k}";
                        $placeholders[] = ":{$p}";
                        $params[$p] = $v;
                    }
                    $conditions[] = "{$where['field']} IN (" . implode(',', $placeholders) . ")";
                } else {
                    $paramName = "w_{$index}_{$where['field']}";
                    $conditions[] = "{$where['field']} {$where['operator']} :{$paramName}";
                    $params[$paramName] = $where['value'];
                }
            }
            $sql .= implode(' AND ', $conditions);
        }

        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY ";
            $orders = [];
            foreach ($this->orderBys as $orderBy) {
                $orders[] = "{$orderBy['field']} {$orderBy['direction']}";
            }
            $sql .= implode(', ', $orders);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        $db = Database::getInstance();
        $rows = $db->fetchAll($sql, $params);
        $values = [];
        foreach ($rows as $row) {
            if (array_key_exists($column, $row)) {
                $values[] = $row[$column];
            }
        }
        return $values;
    }
}
