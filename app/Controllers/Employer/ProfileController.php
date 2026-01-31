<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;
use App\Core\Storage;

class ProfileController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;

        // Parse address JSON and merge with column fallbacks
        $address = [];
        if (!empty($employer->address)) {
            $address = is_string($employer->address)
                ? json_decode($employer->address, true)
                : $employer->address;
            if (!is_array($address)) {
                $address = [];
            }
        }
        $address['state'] = $address['state'] ?? ($employer->state ?? '');
        $address['city'] = $address['city'] ?? ($employer->city ?? '');
        $address['postal_code'] = $address['postal_code'] ?? ($employer->postal_code ?? '');
        $address['street'] = $address['street'] ?? '';

        $response->view('employer/profile', [
            'title' => 'My Profile',
            'employer' => $employer,
            'user' => $this->currentUser,
            'address' => $address,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $contentType = $request->header('Content-Type') ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $data = $isJson ? $request->getJsonBody() : $request->all();

        // Update user email if provided
        if (isset($data['email']) && $data['email'] !== $this->currentUser->email) {
            // Check if email already exists
            $existing = User::where('email', '=', $data['email'])
                ->where('id', '!=', $this->currentUser->id)
                ->first();
            
            if ($existing) {
                $response->json(['error' => 'Email already registered'], 409);
                return;
            }
            
            $this->currentUser->email = $data['email'];
            $this->currentUser->save();
        }

        // Update user phone if provided
        if (isset($data['phone'])) {
            $this->currentUser->phone = $data['phone'];
            $this->currentUser->save();
        }

        // Update employer profile
        $updateData = [];
        
        if (isset($data['company_name'])) {
            $updateData['company_name'] = $data['company_name'];
            $updateData['company_slug'] = $employer->generateSlug($data['company_name']);
        }
        
        if (isset($data['website'])) {
            $updateData['website'] = $data['website'] ?: null;
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'] ?: null;
        }
        
        if (isset($data['industry'])) {
            $updateData['industry'] = $data['industry'] ?: null;
        }
        
        if (isset($data['company_size'])) {
            $updateData['size'] = $data['company_size'];
        }
        
        if (isset($data['country'])) {
            $updateData['country'] = $data['country'];
        }

        // Handle address
        if (isset($data['address'])) {
            $address = is_string($data['address']) 
                ? json_decode($data['address'], true) 
                : $data['address'];
            
            if (is_array($address)) {
                $updateData['address'] = json_encode($address, JSON_UNESCAPED_UNICODE);
                $updateData['state'] = $address['state'] ?? null;
                $updateData['city'] = $address['city'] ?? null;
                $updateData['postal_code'] = $address['postal_code'] ?? null;
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $storage = new Storage();
                $filePath = $storage->store($file, 'employers/' . $employer->id);
                $updateData['logo_url'] = $storage->url($filePath);
            }
        }

        // Update employer
        $employer->fill($updateData);
        if ($employer->save()) {
            $response->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'employer' => $employer->toArray(),
                'user' => $this->currentUser->toArray()
            ]);
        } else {
            $response->json(['error' => 'Failed to update profile'], 500);
        }
    }
}

