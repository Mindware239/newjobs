<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Models\Employer;

class ApplicationTest extends TestCase
{
    private static ?Job $testJob = null;
    private static ?User $testCandidate = null;

    public static function setUpBeforeClass(): void
    {
        // Setup test employer and job
        $user = new User();
        $user->fill([
            'email' => 'test_employer2@example.com',
            'role' => 'employer',
            'status' => 'active'
        ]);
        $user->setPassword('password123');
        $user->save();

        $employer = new Employer();
        $employer->fill([
            'user_id' => $user->id,
            'company_name' => 'Test Company 2',
            'company_slug' => 'test-company-2',
            'kyc_status' => 'approved'
        ]);
        $employer->save();

        $job = new Job();
        $job->fill([
            'employer_id' => $employer->id,
            'title' => 'Test Job',
            'slug' => 'test-job',
            'description' => 'Test job description',
            'status' => 'published'
        ]);
        $job->save();
        self::$testJob = $job;

        // Setup test candidate
        $candidate = new User();
        $candidate->fill([
            'email' => 'test_candidate@example.com',
            'role' => 'candidate',
            'status' => 'active'
        ]);
        $candidate->setPassword('password123');
        $candidate->save();
        self::$testCandidate = $candidate;
    }

    public function testApplicationCreation(): void
    {
        $application = new Application();
        $application->fill([
            'job_id' => self::$testJob->id,
            'candidate_user_id' => self::$testCandidate->id,
            'cover_letter' => 'I am interested in this position.',
            'status' => 'applied'
        ]);

        $this->assertTrue($application->save());
        $this->assertNotNull($application->id);
        $this->assertEquals('applied', $application->status);
    }

    public function testApplicationStatusUpdate(): void
    {
        $application = Application::where('job_id', '=', self::$testJob->id)
            ->where('candidate_user_id', '=', self::$testCandidate->id)
            ->first();

        if ($application) {
            $this->assertTrue($application->updateStatus('shortlisted', self::$testCandidate->id, 'Good candidate'));
            $this->assertEquals('shortlisted', $application->status);
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Cleanup
        if (self::$testJob) {
            $applications = Application::where('job_id', '=', self::$testJob->id)->get();
            foreach ($applications as $app) {
                $app->delete();
            }
            self::$testJob->delete();
        }
        if (self::$testCandidate) {
            self::$testCandidate->delete();
        }
    }
}

