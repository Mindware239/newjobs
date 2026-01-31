<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Testimonial extends Model
{
    protected string $table = 'testimonials';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'title',
        'testimonial_type',
        'name',
        'designation',
        'company',
        'message',
        'video_url',
        'image',
        'is_active',
        'created_at',
        'updated_at'
    ];

    public static function getAll(int $limit = 200): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT " . (int)$limit);
        return array_map(fn($r) => new self($r), $rows);
    }

    public static function getByType(string $type, int $limit = 200): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT * FROM testimonials WHERE testimonial_type = :t AND is_active = 1 ORDER BY created_at DESC LIMIT " . (int)$limit,
            ['t' => $type]
        );
        return array_map(fn($r) => new self($r), $rows);
    }

    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    public static function create(array $data): ?self
    {
        $model = new self();
        $model->fill($data);
        if ($model->save()) {
            return $model;
        }
        return null;
    }

    public static function updateOne(int $id, array $data): bool
    {
        $row = self::find($id);
        if (!$row) {
            return false;
        }
        $row->fill($data);
        return $row->save();
    }

    public static function deleteOne(int $id): bool
    {
        $row = self::find($id);
        if (!$row) {
            return false;
        }
        return $row->delete();
    }
}
