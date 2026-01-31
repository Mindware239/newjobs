<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class CompanyBlog
{
    protected Database $db;

    public function __construct()
    {
        // Use Database proxy which forwards to PDO
        $this->db = Database::getInstance();
    }

    // Fetch blogs for company
    public function getByCompanyId(int $companyId): array
    {
        $sql = "SELECT * FROM company_blogs 
                WHERE company_id = :company_id 
                AND status = 'published'
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, ['company_id' => $companyId]);
    }

    //  Fetch single blog
    public function getBySlug(string $slug): ?array
    {
        return $this->db->fetchOne("SELECT * FROM company_blogs WHERE slug = :slug LIMIT 1", ['slug' => $slug]);
    }

    // Get all blogs for company (including drafts/unpublished for management)
    public function getAllByCompanyId(int $companyId): array
    {
        $sql = "SELECT * FROM company_blogs 
                WHERE company_id = :company_id 
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, ['company_id' => $companyId]);
    }

    // Find blog by ID
    public function find(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM company_blogs WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    // Create new blog
    public function create(array $data, $imageFile = null): ?int
    {
        // Generate slug from title
        $title = $data['title'] ?? '';
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        $existing = $this->getBySlug($slug);
        while ($existing) {
            $slug = $baseSlug . '-' . $counter;
            $existing = $this->getBySlug($slug);
            $counter++;
        }

        // Handle image upload
        $imageUrl = null;
        if ($imageFile && isset($imageFile['tmp_name']) && is_uploaded_file($imageFile['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/company-blogs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($imageFile['tmp_name'], $filepath)) {
                $imageUrl = '/uploads/company-blogs/' . $filename;
            }
        }

        $sql = "INSERT INTO company_blogs 
                (company_id, title, slug, excerpt, content, image, status, created_at, updated_at) 
                VALUES (:company_id, :title, :slug, :excerpt, :content, :image, :status, NOW(), NOW())";
        
        $this->db->execute($sql, [
            'company_id' => $data['company_id'],
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? '',
            'content' => $data['content'] ?? '',
            'image' => $imageUrl,
            'status' => $data['status'] ?? 'published'
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Delete blog
    public function delete(int $id): bool
    {
        // Get blog to delete image
        $blog = $this->find($id);
        if ($blog && !empty($blog['image'])) {
            $imagePath = __DIR__ . '/../../public' . $blog['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        try {
            $this->db->execute("DELETE FROM company_blogs WHERE id = :id", ['id' => $id]);
            return true;
        } catch (\Exception $e) {
            error_log('Blog delete failed: ' . $e->getMessage());
            return false;
        }
    }

    // Update blog
    public function update(int $id, array $data, $imageFile = null): bool
    {
        $updates = [];
        $params = ['id' => $id];

        if (isset($data['title'])) {
            $updates[] = 'title = :title';
            $params['title'] = $data['title'];
            
            // Update slug if title changed
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            $slug = trim($slug, '-');
            $baseSlug = $slug;
            $counter = 1;
            while ($this->getBySlug($slug) && $slug !== ($this->find($id)['slug'] ?? '')) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $updates[] = 'slug = :slug';
            $params['slug'] = $slug;
        }

        if (isset($data['excerpt'])) {
            $updates[] = 'excerpt = :excerpt';
            $params['excerpt'] = $data['excerpt'];
        }

        if (isset($data['content'])) {
            $updates[] = 'content = :content';
            $params['content'] = $data['content'];
        }

        if (isset($data['status'])) {
            $updates[] = 'status = :status';
            $params['status'] = $data['status'];
        }

        // Handle image upload
        if ($imageFile && isset($imageFile['tmp_name']) && is_uploaded_file($imageFile['tmp_name'])) {
            // Delete old image
            $blog = $this->find($id);
            if ($blog && !empty($blog['image'])) {
                $oldImagePath = __DIR__ . '/../../public' . $blog['image'];
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $uploadDir = __DIR__ . '/../../public/uploads/company-blogs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($imageFile['tmp_name'], $filepath)) {
                $updates[] = 'image = :image';
                $params['image'] = '/uploads/company-blogs/' . $filename;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = 'updated_at = NOW()';
        $sql = "UPDATE company_blogs SET " . implode(', ', $updates) . " WHERE id = :id";
        
        try {
            $this->db->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log('Blog update failed: ' . $e->getMessage());
            return false;
        }
    }
}
