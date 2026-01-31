<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Job;
use App\Models\Employer;
use App\Models\User;

class JobTest extends TestCase
{
    private static ?User $testUser = null;
    private static ?Employer $testEmployer = null;

    public static function setUpBeforeClass(): void
    {
        // Setup test user and employer
        self::$testUser = new User();
        self::$testUser->fill([
            'email' => 'test_employer@example.com',
            'role' => 'employer',
            'status' => 'active'
        ]);
        self::$testUser->setPassword('password123');
        self::$testUser->save();

        self::$testEmployer = new Employer();
        self::$testEmployer->fill([
            'user_id' => self::$testUser->id,
            'company_name' => 'Test Company',
            'company_slug' => 'test-company',
            'kyc_status' => 'approved'
        ]);
        self::$testEmployer->save();
    }

    public function testJobCreation(): void
    {
        $job = new Job();
        $job->fill([
            'employer_id' => self::$testEmployer->id,
            'title' => 'Senior PHP Developer',
            'slug' => 'senior-php-developer',
            'description' => 'We are looking for a senior PHP developer with 5+ years of experience.',
            'employment_type' => 'full_time',
            'seniority' => 'senior',
            'salary_min' => 50000,
            'salary_max' => 80000,
            'currency' => 'INR',
            'is_remote' => 1,
            'status' => 'draft',
            'vacancies' => 2
        ]);

        $this->assertTrue($job->save());
        $this->assertNotNull($job->id);
        $this->assertEquals('Senior PHP Developer', $job->title);
    }

    public function testJobUpdate(): void
    {
        $job = Job::where('employer_id', '=', self::$testEmployer->id)->first();
        
        if ($job) {
            $job->status = 'published';
            $this->assertTrue($job->save());
            $this->assertEquals('published', $job->status);
        }
    }

    public function testJobSlugGeneration(): void
    {
        $job = new Job();
        $slug = $job->generateSlug('Test Job Title');
        $this->assertEquals('test-job-title', $slug);
    }

    public static function tearDownAfterClass(): void
    {
        // Cleanup
        if (self::$testEmployer) {
            $jobs = Job::where('employer_id', '=', self::$testEmployer->id)->get();
            foreach ($jobs as $job) {
                $job->delete();
            }
            self::$testEmployer->delete();
        }
        if (self::$testUser) {
            self::$testUser->delete();
        }
    }
}

