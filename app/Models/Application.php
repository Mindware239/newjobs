<?php

declare(strict_types=1);

namespace App\Models;

class Application extends Model
{
    protected string $table = 'applications';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'job_id', 'candidate_user_id', 'resume_url', 'cover_letter',
        'expected_salary', 'status', 'score', 'source', 'accepted_at', 'rejected_at'
    ];

    public function job(): ?Job
    {
        return Job::find($this->attributes['job_id'] ?? 0);
    }

    public function candidate(): ?User
    {
        return User::find($this->attributes['candidate_user_id'] ?? 0);
    }

    public function events()
    {
        return ApplicationEvent::where('application_id', '=', $this->attributes['id'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function interviews()
    {
        return Interview::where('application_id', '=', $this->attributes['id'])->get();
    }

    /**
     * Get applications for an employer with filters
     */
    public static function getEmployerApplications(int $employerId, array $filters = [], int $limit = 200): array
    {
        $db = \App\Core\Database::getInstance();
        $jobId = (int)($filters['job_id'] ?? 0);
        $status = $filters['status'] ?? null;
        $source = $filters['source'] ?? null;
        $search = $filters['search'] ?? null;
        $sortBy = $filters['sort_by'] ?? 'date';
        $locationFilter = $filters['location'] ?? null;
        $locationDistance = (int)($filters['location_distance'] ?? 0);
        $interestFilter = $filters['interest'] ?? null;
        $hasResume = $filters['has_resume'] ?? null;
        $activeIn = (int)($filters['active_in'] ?? 0);
        $salaryMin = $filters['salary_min'] ?? null;
        $salaryMax = $filters['salary_max'] ?? null;
        $skillsFilter = $filters['skills'] ?? null;
        $languageFilter = $filters['language'] ?? null;

        $params = [];
        
        if ($source === 'database' && $jobId) {
             $baseSql = "FROM candidate_job_scores cjs
                JOIN candidates c ON cjs.candidate_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN jobs j ON cjs.job_id = j.id
                LEFT JOIN applications a ON a.job_id = cjs.job_id AND a.candidate_user_id = u.id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                WHERE cjs.job_id = :job_id AND a.id IS NULL";
             $params['job_id'] = $jobId;
        } else {
            $baseSql = "FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.candidate_user_id = u.id
                LEFT JOIN candidates c ON c.user_id = u.id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = j.id
                WHERE j.employer_id = :employer_id";
            $params['employer_id'] = $employerId;
        }

        $whereConditions = [];
        if ($jobId && $source !== 'database') {
            $whereConditions[] = "a.job_id = :job_id";
            $params['job_id'] = $jobId;
        }

        if ($status && $source !== 'database') {
            $statusMap = [
                'new' => 'applied',
                'reviewing' => ['screening', 'applied'],
                'contacting' => 'offer',
                'interviewing' => 'interview',
                'rejected' => 'rejected',
                'hired' => 'hired',
                'shortlist' => 'shortlisted',
                'undecided' => ['applied', 'screening', 'interview', 'offer']
            ];
            
            if (isset($statusMap[$status])) {
                if (is_array($statusMap[$status])) {
                    $placeholders = [];
                    foreach ($statusMap[$status] as $idx => $s) {
                        $key = 'status_' . $idx;
                        $placeholders[] = ":{$key}";
                        $params[$key] = $s;
                    }
                    $whereConditions[] = "a.status IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $whereConditions[] = "a.status = :status";
                    $params['status'] = $statusMap[$status];
                }
            }
        }

        if ($locationFilter) {
            $whereConditions[] = "(c.city LIKE :loc1 OR c.state LIKE :loc2 OR c.country LIKE :loc3)";
            $params['loc1'] = "%{$locationFilter}%";
            $params['loc2'] = "%{$locationFilter}%";
            $params['loc3'] = "%{$locationFilter}%";
        }

        if ($search) {
            $whereConditions[] = "(c.full_name LIKE :s1 OR u.email LIKE :s2 OR j.title LIKE :s3)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
        }

        if ($hasResume === '1') {
            $whereConditions[] = "(c.resume_url IS NOT NULL AND c.resume_url <> '')";
        }

        if ($activeIn > 0) {
            $since = date('Y-m-d H:i:s', time() - ($activeIn * 86400));
            $whereConditions[] = "u.last_login >= :active_since";
            $params['active_since'] = $since;
        }

        if ($interestFilter) {
            if ($interestFilter === 'shortlisted') $whereConditions[] = "a.status = 'shortlisted'";
            elseif ($interestFilter === 'rejected') $whereConditions[] = "a.status = 'rejected'";
            elseif ($interestFilter === 'undecided') $whereConditions[] = "a.status NOT IN ('shortlisted', 'rejected', 'hired')";
        }

        if ($salaryMin !== null && $salaryMin !== '') {
            $whereConditions[] = "c.expected_salary_min >= :salary_min";
            $params['salary_min'] = $salaryMin;
        }
        
        if ($salaryMax !== null && $salaryMax !== '') {
            $whereConditions[] = "c.expected_salary_max <= :salary_max";
            $params['salary_max'] = $salaryMax;
        }

        $whereClause = !empty($whereConditions) ? " AND " . implode(" AND ", $whereConditions) : "";

        $premiumOrder = "CASE WHEN c.is_premium = 1 AND c.premium_expires_at > NOW() THEN 1 ELSE 0 END DESC, c.profile_strength DESC, u.last_login DESC";
        $orderBy = $premiumOrder . ", a.applied_at DESC";
        if ($source === 'database') {
            $orderBy = $premiumOrder . ", cjs.overall_match_score DESC";
        }

        $sql = "SELECT 
                    a.*, a.id as application_id, j.id as job_id, j.title as job_title, j.slug as job_slug,
                    j.currency as job_currency, j.salary_min as job_salary_min, j.salary_max as job_salary_max,
                    u.email as candidate_email, u.phone, c.mobile as candidate_mobile, c.id as candidate_id,
                    c.full_name, c.city, c.state, c.country, c.profile_picture, c.resume_url,
                    c.current_salary, c.expected_salary_min, c.expected_salary_max,
                    c.education_data, c.experience_data, c.skills_data, c.languages_data,
                    rs_cert.section_data as certifications_data,
                    cjs.overall_match_score, cjs.skill_score, cjs.experience_score, cjs.education_score,
                    cjs.recommendation, cjs.matched_skills, cjs.missing_skills, cjs.extra_relevant_skills,
                    cjs.summary as match_summary
                " . $baseSql . $whereClause . " ORDER BY {$orderBy} LIMIT {$limit}";

        return $db->fetchAll($sql, $params);
    }

    /**
     * Paginate applications for an employer with comprehensive details
     */
    public static function paginateEmployerApplications(int $employerId, array $filters = [], int $perPage = 10, int $page = 1): array
    {
        $db = \App\Core\Database::getInstance();
        $params = [':employer_id' => $employerId];
        
        $whereConditions = ["j.employer_id = :employer_id"];
        
        if (!empty($filters['job_id'])) {
            $whereConditions[] = "a.job_id = :job_id";
            $params[':job_id'] = (int)$filters['job_id'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(c.full_name LIKE :s1 OR u.email LIKE :s2 OR j.title LIKE :s3)";
            $params[':s1'] = "%{$filters['search']}%";
            $params[':s2'] = "%{$filters['search']}%";
            $params[':s3'] = "%{$filters['search']}%";
        }

        if (!empty($filters['score_min'])) {
            $whereConditions[] = "cjs.overall_match_score >= :score_min";
            $params[':score_min'] = (int)$filters['score_min'];
        }

        if (!empty($filters['city'])) {
            $whereConditions[] = "c.city = :city";
            $params[':city'] = $filters['city'];
        }

        if (!empty($filters['is_verified'])) {
            $whereConditions[] = "c.is_verified = 1";
        }

        $whereClause = " WHERE " . implode(" AND ", $whereConditions);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM applications a
                     JOIN jobs j ON a.job_id = j.id
                     JOIN users u ON a.candidate_user_id = u.id
                     LEFT JOIN candidates c ON c.user_id = u.id
                     LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = j.id
                     {$whereClause}";
        
        $total = (int)$db->fetchOne($countSql, $params)['total'];
        $lastPage = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Sorting
        $sortField = 'a.applied_at';
        $sortDir = 'DESC';
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'score': $sortField = 'cjs.overall_match_score'; break;
                case 'experience': $sortField = 'c.experience_data'; break; // Simplified, might need more logic
                case 'latest': $sortField = 'a.applied_at'; break;
            }
        }
        
        $sql = "SELECT 
                    a.*, 
                    j.title as job_title, j.job_type as job_type,
                    e.company_name as company_name,
                    u.name as user_name, u.email as candidate_email, 
                    COALESCE(NULLIF(c.mobile, ''), NULLIF(u.phone, ''), 'N/A') as candidate_phone,
                    u.last_login as last_active,
                    u.is_email_verified, u.is_phone_verified,
                    c.id as candidate_id, COALESCE(c.full_name, u.name) as display_name, 
                    c.profile_picture, c.city, c.state, c.country,
                    c.current_salary, c.expected_salary_min, c.expected_salary_max,
                    c.experience_data, c.education_data, c.skills_data, c.is_verified,
                    r.preview_image as resume_preview_image,
                    cjs.overall_match_score, cjs.skill_score, cjs.experience_score, cjs.education_score
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                JOIN employers e ON j.employer_id = e.id
                JOIN users u ON a.candidate_user_id = u.id
                LEFT JOIN candidates c ON c.user_id = u.id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = j.id
                {$whereClause}
                ORDER BY {$sortField} {$sortDir}
                LIMIT {$perPage} OFFSET {$offset}";

        $results = $db->fetchAll($sql, $params);

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ];
    }

    public function updateStatus(string $newStatus, int|string|null $actorId = null, string $comment = ''): bool
    {
        $oldStatus = $this->attributes['status'] ?? 'applied';
        $actorUserId = is_null($actorId) ? null : (int)$actorId;
        $this->attributes['status'] = $newStatus;
        
        if ($this->save()) {
            // Log event
            $event = new ApplicationEvent();
            $event->fill([
                'application_id' => $this->attributes['id'],
                'actor_user_id' => $actorUserId,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => $comment
            ]);
            $event->save();
            return true;
        }
        return false;
    }
}

