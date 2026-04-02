# Complete API Implementation Guide - Job Portal Mobile App

## Overview

This guide provides instructions for implementing the remaining **127 missing API endpoints** with complete working code structure. All endpoints use:
- **JWT Authentication** via `ApiAuthMiddleware`
- **Standard JSON Response Format** with `ApiController::success()` and `error()`
- **Mobile App Optimization** (pagination, filtering, proper status codes)

---

## Quick Start

### 1. Replace Routes File
```bash
# Backup existing route file
mv routes/api_v1.php routes/api_v1.backup.php

# Copy complete routes
cp routes/api_v1_complete.php routes/api_v1.php
```

### 2. Create Required Directories
```bash
mkdir -p app/Controllers/Api/Candidate
mkdir -p app/Controllers/Api/Employer
```

### 3. Response Format (All Endpoints)
```json
{
  "success": true,
  "message": "Success message",
  "data": { },
  "errors": {}
}
```

---

## Standard Error Responses

### 401 Unauthorized
```php
$this->error($response, 'Unauthorized', 401);
```

### 403 Forbidden
```php
$this->error($response, 'Forbidden', 403);
```

### 404 Not Found
```php
$this->error($response, 'Resource not found', 404);
```

### 422 Validation Error
```php
$errors = $this->validate($data, $rules);
if (!empty($errors)) {
    $this->validationError($response, $errors);
}
```

### 500 Server Error
```php
$this->error($response, 'Server error', 500);
```

---

## Remaining Controllers to Implement

### A. Candidate-Specific Controllers

#### 1. AlertController ✅ (Job Alerts)
**File:** `app/Controllers/Api/Candidate/AlertController.php`

```php
<?php
namespace App\Controllers\Api\Candidate;

class AlertController extends ApiController {
    // POST /candidate/job-alerts - Create alert
    public function create(Request $request, Response $response): void {
        $user = $this->user($request);
        $errors = $this->validate($request->getJsonBody(), [
            'job_title' => 'required',
            'locations' => 'required|array',
            'experience_level' => 'required',
            'frequency' => 'required|in:daily,weekly,instant'
        ]);
        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }
        // Create JobAlert model record
        $this->success($response, ['id' => $alert->id], 'Alert created', 201);
    }
    
    // GET /candidate/job-alerts - List alerts
    public function index(Request $request, Response $response): void {}
    
    // PUT /candidate/job-alerts/{id} - Update alert
    public function update(Request $request, Response $response, int $id): void {}
    
    // DELETE /candidate/job-alerts/{id} - Delete alert
    public function delete(Request $request, Response $response, int $id): void {}
    
    // GET /candidate/job-alerts/{id}/count - Matching jobs count
    public function matchingCount(Request $request, Response $response, int $id): void {}
}
```

**Database Model:** `JobAlert`
```php
protected array $fillable = [
    'candidate_id', 'job_title', 'locations', 'experience_level',
    'frequency', 'salary_range', 'industry', 'is_active'
];
```

---

#### 2. Complete ApplicationController
**Additional methods needed:**
- `employerApplications()` ✅ (Already implemented)
- `downloadResume()` ✅
- `shortlist()` ✅
- `reject()` ✅
- `sendOffer()` ✅

**Missing endpoints to add:**
- POST `/candidate/job/{id}/apply` - in JobController

---

### B. Employer-Specific Controllers

#### 1. JobController (Employer)
**File:** `app/Controllers/Api/Employer/JobController.php`

```php
<?php
namespace App\Controllers\Api\Employer;

class JobController extends ApiController {
    // GET /employer/jobs
    public function index(Request $request, Response $response): void {
        $user = $this->user($request);
        $page = (int)$request->query('page', 1);
        $status = $request->query('status'); // active, closed, draft
        
        $jobs = Job::where('employer_id', '=', $user->id);
        if ($status) $jobs->where('status', '=', $status);
        
        $jobs = $jobs->paginate(10, $page);
        $this->success($response, [
            'jobs' => $jobs['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => 10,
                'total' => $jobs['total']
            ]
        ]);
    }
    
    // POST /employer/jobs
    public function create(Request $request, Response $response): void {
        $user = $this->user($request);
        $errors = $this->validate($request->getJsonBody(), [
            'title' => 'required',
            'description' => 'required',
            'location' => 'required',
            'salary_min' => 'required|numeric',
            'salary_max' => 'required|numeric',
            'experience_level' => 'required|in:entry,mid,senior'
        ]);
        
        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }
        
        $job = new Job();
        $job->fill(array_merge(
            $request->getJsonBody(),
            ['employer_id' => $user->id, 'status' => 'draft']
        ))->save();
        
        $this->success($response, ['id' => $job->id], 'Job created', 201);
    }
    
    // PUT /employer/jobs/{id}
    public function update(Request $request, Response $response, int $id): void {}
    
    // DELETE /employer/jobs/{id}
    public function delete(Request $request, Response $response, int $id): void {}
    
    // GET /employer/jobs/{id}/details
    public function show(Request $request, Response $response, int $id): void {}
    
    // POST /employer/jobs/{id}/duplicate
    public function duplicate(Request $request, Response $response, int $id): void {}
    
    // POST /employer/jobs/{id}/publish
    public function publish(Request $request, Response $response, int $id): void {}
    
    // POST /employer/jobs/{id}/unpublish
    public function unpublish(Request $request, Response $response, int $id): void {}
}
```

---

#### 2. ProfileController (Employer)
**File:** `app/Controllers/Api/Employer/ProfileController.php`

```php
<?php
namespace App\Controllers\Api\Employer;

class ProfileController extends ApiController {
    // GET /employer/profile
    public function show(Request $request, Response $response): void {
        $user = $this->user($request);
        $employer = Employer::where('user_id', '=', $user->id)->first();
        
        $this->success($response, [
            'id' => $employer->id,
            'company_name' => $employer->company_name,
            'description' => $employer->description,
            'website' => $employer->website,
            'industry' => $employer->industry,
            'employees_count' => $employer->employees_count,
            'locations' => $employer->locations,
            'logo' => $employer->logo,
            'banner' => $employer->banner,
            'verification_status' => $employer->verification_status,
            'social_links' => $employer->social_links ? json_decode($employer->social_links) : null
        ]);
    }
    
    // PUT /employer/profile
    public function update(Request $request, Response $response): void {}
    
    // POST /employer/profile/logo
    public function uploadLogo(Request $request, Response $response): void {}
    
    // POST /employer/profile/banner
    public function uploadBanner(Request $request, Response $response): void {}
    
    // POST /employer/profile/documents/upload
    public function uploadDocument(Request $request, Response $response): void {}
    
    // GET /employer/profile/documents
    public function listDocuments(Request $request, Response $response): void {}
    
    // DELETE /employer/profile/documents/{id}
    public function deleteDocument(Request $request, Response $response, int $id): void {}
    
    // GET /employer/profile/verification-status
    public function verificationStatus(Request $request, Response $response): void {}
    
    // POST /employer/profile/social-links
    public function updateSocialLinks(Request $request, Response $response): void {}
}
```

---

#### 3. CandidateController (Employer Search)
**File:** `app/Controllers/Api/Employer/CandidateController.php`

```php
<?php
namespace App\Controllers\Api\Employer;

class CandidateController extends ApiController {
    // GET /employer/candidates
    public function search(Request $request, Response $response): void {
        $user = $this->user($request);
        if ($user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        
        $filters = $request->getQueryParams();
        // Apply filters: job_title, location, experience, skills
        
        $candidates = Candidate::whereIn('user_id', function($q) {
            $q->select('id')->from('users')->where('role', '=', 'candidate');
        });
        
        // Apply filters from query
        if ($filters['job_title'] ?? null) {
            $candidates->where('headline', 'LIKE', '%' . $filters['job_title'] . '%');
        }
        
        $candidates = $candidates->paginate(20, $filters['page'] ?? 1);
        $this->success($response, ['candidates' => $candidates['data']]);
    }
    
    // GET /employer/candidates/{id}
    public function show(Request $request, Response $response, int $id): void {}
    
    // POST /employer/candidates/{id}/invite
    public function invite(Request $request, Response $response, int $id): void {}
    
    // POST /employer/candidates/{id}/shortlist
    public function shortlist(Request $request, Response $response, int $id): void {}
    
    // GET /employer/shortlists
    public function shortlists(Request $request, Response $response): void {}
}
```

---

#### 4. DashboardController (Employer)
**File:** `app/Controllers/Api/Employer/DashboardController.php`

```php
<?php
namespace App\Controllers\Api\Employer;

class DashboardController extends ApiController {
    // GET /employer/dashboard
    public function index(Request $request, Response $response): void {
        $user = $this->user($request);
        
        $stats = [
            'total_jobs' => Job::where('employer_id', '=', $user->id)->count(),
            'active_jobs' => Job::where('employer_id', '=', $user->id)
                ->where('status', '=', 'active')->count(),
            'total_applications' => Application::whereIn('job_id', function($q) use ($user) {
                $q->select('id')->from('jobs')->where('employer_id', '=', $user->id);
            })->count(),
            'pending_applications' => Application::whereIn('job_id', function($q) use ($user) {
                $q->select('id')->from('jobs')->where('employer_id', '=', $user->id);
            })->where('status', '=', 'pending')->count(),
            'upcoming_interviews' => Interview::whereIn('application_id', function($q) use ($user) {
                $q->select('id')->from('applications')
                  ->whereIn('job_id', function($q2) use ($user) {
                      $q2->select('id')->from('jobs')->where('employer_id', '=', $user->id);
                  });
            })->where('status', '=', 'scheduled')->count()
        ];
        
        $this->success($response, $stats);
    }
    
    // GET /employer/dashboard/stats
    // GET /employer/dashboard/recent-applications
    // GET /employer/dashboard/active-jobs
    // GET /employer/dashboard/upcoming-interviews
    // GET /employer/team-members
    // POST /employer/team-members
    // PUT /employer/team-members/{id}
    // DELETE /employer/team-members/{id}
}
```

---

### C. Shared Controllers (Already Implemented)

- ✅ `ChatController` - Full implementation
- ✅ `InterviewController` - Full implementation
- ✅ `PaymentController` - Full implementation
- ✅ `ResumeController` - Full implementation
- ✅ `ReviewController` - Full implementation
- ✅ `AnalyticsController` - Full implementation
- ✅ `UtilityController` - Full implementation

---

## Required Database Models

### 1. Conversation Model
```php
<?php
namespace App\Models;

class Conversation extends Model {
    protected string $table = 'conversations';
    protected array $fillable = [
        'user1_id', 'user2_id', 'last_message_at',
        'blocked_by_user1', 'blocked_by_user2',
        'archived_by_user'
    ];
    
    public function hasUser(int $userId): bool {
        return $this->user1_id === $userId || $this->user2_id === $userId;
    }
    
    public function user1() {
        return $this->belongsTo(User::class, 'user1_id');
    }
    
    public function user2() {
        return $this->belongsTo(User::class, 'user2_id');
    }
    
    public function lastMessage() {
        return Message::where('conversation_id', '=', $this->id)
            ->latest()->first();
    }
    
    public function unreadCount(int $userId): int {
        return Message::where('conversation_id', '=', $this->id)
            ->where('sender_id', '!=', $userId)
            ->where('read_at', '=', null)
            ->count();
    }
}
```

### 2. Message Model
```php
<?php
namespace App\Models;

class Message extends Model {
    protected string $table = 'messages';
    protected array $fillable = [
        'conversation_id', 'sender_id', 'content', 'type',
        'metadata', 'read_at', 'edited_at'
    ];
}
```

### 3. Interview Model
```php
<?php
namespace App\Models;

class Interview extends Model {
    protected string $table = 'interviews';
    protected array $fillable = [
        'application_id', 'scheduled_at', 'interview_type',
        'duration_minutes', 'status', 'interviewer_notes',
        'feedback', 'meeting_link', 'attended'
    ];
}
```

### 4. Resume & ResumeFile Models
```php
<?php
namespace App\Models;

class Resume extends Model {
    protected string $table = 'resumes';
    protected array $fillable = [
        'candidate_id', 'title', 'is_default',
        'parsed_data', 'is_auto_generated'
    ];
    
    public function latestFile() {
        return ResumeFile::where('resume_id', '=', $this->id)
            ->latest()->first();
    }
}

class ResumeFile extends Model {
    protected string $table = 'resume_files';
    protected array $fillable = [
        'resume_id', 'file_path', 'file_type', 'file_size'
    ];
}
```

---

## Required Database Migrations

### Conversations & Messages
```sql
CREATE TABLE conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user1_id BIGINT NOT NULL,
    user2_id BIGINT NOT NULL,
    last_message_at TIMESTAMP NULL,
    blocked_by_user1 BOOLEAN DEFAULT 0,
    blocked_by_user2 BOOLEAN DEFAULT 0,
    archived_by_user BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id),
    FOREIGN KEY (user2_id) REFERENCES users(id),
    INDEX (user1_id, user2_id),
    INDEX (last_message_at)
);

CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,
    content LONGTEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'text',
    metadata JSON NULL,
    read_at TIMESTAMP NULL,
    edited_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    INDEX (conversation_id),
    INDEX (sender_id),
    INDEX (created_at)
);
```

### Interviews
```sql
CREATE TABLE interviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    application_id BIGINT NOT NULL,
    scheduled_at TIMESTAMP NOT NULL,
    interview_type VARCHAR(50) NOT NULL,
    duration_minutes INT DEFAULT 60,
    status VARCHAR(50) DEFAULT 'scheduled',
    interviewer_notes LONGTEXT NULL,
    feedback LONGTEXT NULL,
    feedback_rating INT NULL,
    meeting_link VARCHAR(255) NULL,
    attended BOOLEAN NULL,
    completed_at TIMESTAMP NULL,
    cancellation_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id),
    INDEX (status),
    INDEX (scheduled_at)
);
```

### Resumes
```sql
CREATE TABLE resumes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    candidate_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT 0,
    parsed_data JSON NULL,
    is_auto_generated BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    INDEX (candidate_id)
);

CREATE TABLE resume_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    resume_id BIGINT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    INDEX (resume_id)
);
```

### Job Bookmarks
```sql
CREATE TABLE job_bookmarks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    candidate_id BIGINT NOT NULL,
    job_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id),
    UNIQUE KEY (candidate_id, job_id),
    INDEX (candidate_id)
);
```

### Job Alerts
```sql
CREATE TABLE job_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    candidate_id BIGINT NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    locations JSON NOT NULL,
    experience_level VARCHAR(50),
    frequency VARCHAR(50) DEFAULT 'weekly',
    salary_range JSON NULL,
    industry VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    INDEX (candidate_id, is_active)
);
```

---

## Authentication Pattern (All Endpoints)

All protected endpoints follow this pattern:

```php
/**
 * Action description
 */
public function action(Request $request, Response $response): void
{
    // 1. Get authenticated user
    $user = $this->user($request);
    if (!$user) {
        $this->error($response, 'Unauthorized', 401);
        return;
    }
    
    // 2. Verify role if needed
    if ($user->role !== 'candidate') {
        $this->error($response, 'Forbidden', 403);
        return;
    }
    
    // 3. Validate request
    $errors = $this->validate($request->getJsonBody(), [
        'field' => 'required|validation_rules'
    ]);
    if (!empty($errors)) {
        $this->validationError($response, $errors);
        return;
    }
    
    // 4. Process business logic
    
    // 5. Return response
    $this->success($response, $data, 'Success message', 200);
}
```

---

## Testing API Endpoints

### Example cURL Commands

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"candidate@example.com","password":"password123"}'
```

**List Resumes:**
```bash
curl -X GET "http://localhost:8000/api/v1/candidate/resumes?page=1&per_page=10" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Upload Resume:**
```bash
curl -X POST http://localhost:8000/api/v1/candidate/resumes/upload \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "resume=@resume.pdf" \
  -F "title=My Resume"
```

**Send Message:**
```bash
curl -X POST http://localhost:8000/api/v1/conversations/1/messages \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Hello!","type":"text"}'
```

---

## Implementation Checklist

### Phase 1 - Critical (Week 1)
- [ ] Implement all stub controllers (copy & extend provided code)
- [ ] Create all database migrations
- [ ] Create all Model classes
- [ ] Test authentication flow
- [ ] Deploy routes file changes

### Phase 2 - High Priority (Week 2-3)
- [ ] Implement Services layer for business logic
- [ ] Add Firebase/FCM notification integration
- [ ] Set up payment gateway webhooks
- [ ] Implement email notifications
- [ ] Test all endpoints

### Phase 3 - Medium Priority (Week 4)
- [ ] Add advanced filtering/search
- [ ] Implement analytics tracking
- [ ] Add image upload optimization
- [ ] Performance optimization
- [ ] Production deployment

### Phase 4 - Polish
- [ ] API documentation with Swagger
- [ ] Rate limiting
- [ ] Caching strategy
- [ ] Error logging
- [ ] Monitoring setup

---

## Mobile App Integration Notes

### Headers Required
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
Accept: application/json
User-Agent: JobPortal/1.0 (iOS/Android)
```

### Pagination
```
GET /resource?page=1&per_page=20
```

### Filtering
```
GET /jobs/search?job_title=Engineer&location=Delhi&experience=senior
```

### Response Always Includes
```json
{
  "success": boolean,
  "message": "string",
  "data": object|array,
  "errors": object|null
}
```

### Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Failed
- 500: Server Error

---

## Common Issues & Solutions

### Issue: CORS Errors
**Solution:** Update CORS middleware in config or add headers to API responses

### Issue: File Upload Size Limits
**Solution:** Check `php.ini` settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Issue: JWT Token Expiry
**Solution:** Implement token refresh endpoint:
```php
public function refreshToken(Request $request, Response $response): void {
    $user = $this->user($request);
    $newToken = $this->authService->generateToken($user);
    $this->success($response, ['token' => $newToken]);
}
```

### Issue: Slow Pagination
**Solution:** Add database indexes on `created_at` and filter fields

---

## Next Steps

1. **Review** all provided controller implementations
2. **Copy** controller code to your project
3. **Run** database migrations
4. **Update** routes file to `api_v1_complete.php`
5. **Test** each endpoint with cURL or Postman
6. **Integrate** with mobile app
7. **Monitor** for errors and optimize performance
