<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

class CompanySalary extends Model
{
    protected string $table = 'company_salaries';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'company_id',
        'job_title',
        'average_salary',
        'currency',
        'period',
        'reports_count',
    ];

    public static function forCompany(int $companyId): array
    {
        $pdo = Database::getInstance();

        $sql = "SELECT *
                FROM company_salaries
                WHERE company_id = ?
                ORDER BY average_salary DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
