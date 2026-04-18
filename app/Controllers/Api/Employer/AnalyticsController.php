<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Services\AnalyticsService;

class AnalyticsController extends ApiController
{
    private AnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
    }

    private function getEmployer(Request $request, Response $response): ?Employer
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return null;
        }

        $employer = Employer::findByUserId((int)$user->id);
        if (!$employer) {
            $this->error($response, 'Employer not found', 404);
            return null;
        }
        
        return $employer;
    }

    public function getHiringFunnel(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $location = $request->get('location');

        try {
            $data = $this->analyticsService->getHiringFunnel((int)$employer->id, $jobId, $dateFrom, $dateTo, $location);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getTimeToHire(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        try {
            $data = $this->analyticsService->getTimeToHire((int)$employer->id, $jobId, $dateFrom, $dateTo);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getLocationAnalytics(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $category = $request->get('category');

        try {
            $data = $this->analyticsService->getLocationAnalytics((int)$employer->id, $jobId, $category);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getJobEngagement(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;

        try {
            $data = $this->analyticsService->getJobEngagement((int)$employer->id, $jobId);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getCandidateQuality(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;

        try {
            $data = $this->analyticsService->getCandidateQuality((int)$employer->id, $jobId);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getCommunicationAnalytics(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        try {
            $data = $this->analyticsService->getCommunicationAnalytics((int)$employer->id, $dateFrom, $dateTo);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getCandidateSources(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        try {
            $data = $this->analyticsService->getCandidateSources((int)$employer->id, $jobId, $dateFrom, $dateTo);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getInterviewOutcomes(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        try {
            $data = $this->analyticsService->getInterviewOutcomes((int)$employer->id, $jobId, $dateFrom, $dateTo);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function getOfferAcceptanceRate(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        $jobId = $request->get('job_id') ? (int)$request->get('job_id') : null;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        try {
            $data = $this->analyticsService->getOfferAcceptanceRate((int)$employer->id, $jobId, $dateFrom, $dateTo);
            $this->success($response, $data);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }

    public function exportReport(Request $request, Response $response): void
    {
        $employer = $this->getEmployer($request, $response);
        if (!$employer) return;

        try {
            // Mock export URL logic
            $this->success($response, ['download_url' => '/exports/analytics_report.pdf']);
        } catch (\Exception $e) {
            $this->error($response, $e->getMessage(), 500);
        }
    }
}
