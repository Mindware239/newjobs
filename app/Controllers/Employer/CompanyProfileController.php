<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Company;
use App\Models\CompanyBlog;
use App\Models\CompanyFollower;
use App\Models\Employer;
use App\Models\Job;
use App\Core\Storage;
use PDO;

class CompanyProfileController extends BaseController
{
    /**
     * Show company profile management page
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->redirect('/employer/dashboard');
            return;
        }

        $companyModel = new Company();
        $company = [];
        try {
            $company = $companyModel->findByEmployerId((int)$employer->id);
            
            if (!$company) {
                // Create company if doesn't exist
                $company = $this->createCompanyForEmployer($employer);
            }
        } catch (\Exception $e) {
            error_log('Failed to fetch/create company: ' . $e->getMessage());
            $response->json(['error' => 'Failed to load company profile: ' . $e->getMessage()], 500);
            return;
        }

        if (empty($company) || !isset($company['id'])) {
            $response->json(['error' => 'Company not found and could not be created'], 500);
            return;
        }

        $companyId = (int)$company['id'];

        // Get company blogs (all statuses for management)
        $blogs = [];
        try {
            $blogModel = new CompanyBlog();
            $blogs = $blogModel->getAllByCompanyId($companyId);
        } catch (\Exception $e) {
            error_log('Failed to fetch blogs: ' . $e->getMessage());
        }

        // Get company reviews
        $db = \App\Core\Database::getInstance();
        $reviews = [];
        try {
            $reviews = $db->fetchAll(
                "SELECT r.*, u.email as user_email 
                 FROM reviews r 
                 LEFT JOIN users u ON u.id = r.user_id 
                 WHERE r.company_id = :cid 
                 ORDER BY r.created_at DESC",
                ['cid' => $companyId]
            );
        } catch (\Throwable $e) {
            error_log('Failed to fetch reviews: ' . $e->getMessage());
        }

        // Get followers count (handle missing table gracefully)
        $followersCount = 0;
        try {
            $followersCount = CompanyFollower::countFollowers($companyId);
        } catch (\Exception $e) {
            error_log('Failed to fetch followers count: ' . $e->getMessage());
        }

        // Get jobs (convert to arrays for view)
        $jobs = [];
        try {
            $jobModels = Job::where('employer_id', '=', (int)$employer->id)
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
            
            // Convert Job model objects to arrays
            foreach ($jobModels as $job) {
                $jobs[] = $job->toArray();
            }
        } catch (\Exception $e) {
            error_log('Failed to fetch jobs: ' . $e->getMessage());
        }

        // Get company stats (handle missing table gracefully)
        $stats = [];
        try {
            $stats = $companyModel->getStats($companyId) ?: [];
        } catch (\Exception $e) {
            error_log('Failed to fetch company stats: ' . $e->getMessage());
        }
        
        $stats = array_merge([
            'rating' => 0,
            'reviews_count' => count($reviews),
            'followers_count' => $followersCount
        ], $stats);
        $stats['followers_count'] = $followersCount;

        $response->view('employer/company-profile', [
            'title' => 'Company Profile',
            'company' => $company,
            'blogs' => $blogs,
            'reviews' => $reviews,
            'jobs' => $jobs,
            'stats' => $stats,
            'employer' => $employer
        ], 200, 'employer/layout');
    }

    /**
     * Update company profile
     */
    public function update(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer not found'], 404);
            return;
        }

        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $companyId = (int)$company['id'];
        $data = $request->all();

        // Handle file uploads
        $storage = new Storage();
        
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $logoPath = $storage->store($file, 'companies/logos');
                $data['logo_url'] = $storage->url($logoPath);
            }
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $bannerPath = $storage->store($file, 'companies/banners');
                $data['banner_url'] = $storage->url($bannerPath);
            }
        }

        if ($request->hasFile('ceo_photo')) {
            $file = $request->file('ceo_photo');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $ceoPhotoPath = $storage->store($file, 'companies/ceo');
                $data['ceo_photo'] = $storage->url($ceoPhotoPath);
            }
        }

        // Handle description with why_points
        $whyPoints = $data['why_points'] ?? [];
        if (is_array($whyPoints)) {
            $whyPoints = array_filter(array_map('trim', $whyPoints));
            $description = [
                'about' => $data['description'] ?? '',
                'why_points' => array_values($whyPoints)
            ];
            $data['description'] = json_encode($description, JSON_UNESCAPED_UNICODE);
        }

        $success = $companyModel->updateCompany($companyId, $data);

        if ($success) {
            $response->json(['success' => true, 'message' => 'Company profile updated successfully']);
        } else {
            $response->json(['error' => 'Failed to update company profile'], 500);
        }
    }

    /**
     * Get company blogs for management
     */
    public function getBlogs(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $blogModel = new CompanyBlog();
        $blogs = $blogModel->getAllByCompanyId((int)$company['id']);
        
        $response->json(['success' => true, 'blogs' => $blogs]);
    }

    /**
     * Create new blog
     */
    public function createBlog(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $data = $request->all();
        $title = trim($data['title'] ?? '');
        $excerpt = trim($data['excerpt'] ?? '');
        $content = trim($data['content'] ?? '');

        if (empty($title) || empty($content)) {
            $response->json(['error' => 'Title and content are required'], 422);
            return;
        }

        $blogModel = new CompanyBlog();
        $blogId = $blogModel->create([
            'company_id' => (int)$company['id'],
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => 'published'
        ], $request->file('image') ?? null);

        if ($blogId) {
            $response->json(['success' => true, 'message' => 'Blog created successfully', 'id' => $blogId]);
        } else {
            $response->json(['error' => 'Failed to create blog'], 500);
        }
    }

    /**
     * Delete blog
     */
    public function deleteBlog(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $blogId = (int)$request->param('id');
        $employer = $this->currentUser->employer();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $blogModel = new CompanyBlog();
        $blog = $blogModel->find($blogId);
        
        if (!$blog || (int)$blog['company_id'] !== (int)$company['id']) {
            $response->json(['error' => 'Blog not found or access denied'], 404);
            return;
        }

        $success = $blogModel->delete($blogId);
        
        if ($success) {
            $response->json(['success' => true, 'message' => 'Blog deleted successfully']);
        } else {
            $response->json(['error' => 'Failed to delete blog'], 500);
        }
    }

    /**
     * Get reviews
     */
    public function getReviews(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $db = \App\Core\Database::getInstance();
        $reviews = [];
        try {
            $reviews = $db->fetchAll(
                "SELECT r.*, u.email as user_email 
                 FROM reviews r 
                 LEFT JOIN users u ON u.id = r.user_id 
                 WHERE r.company_id = :cid 
                 ORDER BY r.created_at DESC",
                ['cid' => (int)$company['id']]
            );
        } catch (\Throwable $e) {
            error_log('Failed to fetch reviews: ' . $e->getMessage());
        }

        $response->json(['success' => true, 'reviews' => $reviews]);
    }

    /**
     * Get followers
     */
    public function getFollowers(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        
        if (!$company) {
            $response->json(['error' => 'Company not found'], 404);
            return;
        }

        $db = \App\Core\Database::getInstance();
        $followers = [];
        try {
            $followers = $db->fetchAll(
                "SELECT cf.*, c.full_name, c.profile_picture, u.email 
                 FROM company_followers cf 
                 LEFT JOIN candidates c ON c.id = cf.candidate_id 
                 LEFT JOIN users u ON u.id = c.user_id 
                 WHERE cf.company_id = :cid 
                 ORDER BY cf.created_at DESC",
                ['cid' => (int)$company['id']]
            );
        } catch (\Throwable $e) {
            error_log('Failed to fetch followers: ' . $e->getMessage());
        }

        $response->json(['success' => true, 'followers' => $followers]);
    }

    /**
     * Create company for employer if doesn't exist
     */
    private function createCompanyForEmployer($employer): array
    {
        $db = \App\Core\Database::getInstance();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $employer->company_name ?? 'company')));
        $slug = trim($slug, '-');
        
        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        $existing = $db->fetchOne("SELECT id FROM companies WHERE slug = ?", [$slug]);
        while ($existing) {
            $slug = $baseSlug . '-' . $counter;
            $existing = $db->fetchOne("SELECT id FROM companies WHERE slug = ?", [$slug]);
            $counter++;
        }

        $sql = "INSERT INTO companies (employer_id, short_name, name, slug, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $db->execute($sql, [
            (int)$employer->id,
            $employer->company_name ?? 'Company',
            $employer->company_name ?? 'Company',
            $slug
        ]);

        $companyId = (int)$db->lastInsertId();
        $companyModel = new Company();
        $company = $companyModel->findByEmployerId((int)$employer->id);
        return $company ?: [];
    }
}

