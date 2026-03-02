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

        try {
            $contentType = $request->header('Content-Type') ?? '';
            $isJson = strpos($contentType, 'application/json') !== false;
            $data = $isJson ? $request->getJsonBody() : $request->all();

            // Normalize strings
            foreach (['company_name','website','description','company_type','industry','company_size','country'] as $k) {
                if (isset($data[$k]) && is_string($data[$k])) {
                    $data[$k] = trim($data[$k]);
                }
            }

            // Update user email if provided
            if (isset($data['email']) && $data['email'] !== $this->currentUser->email) {
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
            
            if (isset($data['company_name']) && $data['company_name'] !== '') {
                $updateData['company_name'] = $data['company_name'];
                $updateData['company_slug'] = $employer->generateSlug($data['company_name']);
            }
            
            if (array_key_exists('website', $data)) {
                $updateData['website'] = $data['website'] ?: null;
            }
            
            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'] ?: null;
            }
            
            if (array_key_exists('industry', $data)) {
                $industry = $data['industry'];
                if (is_string($industry) && strtolower($industry) === 'other' && !empty($data['industry_custom'])) {
                    $industry = (string)$data['industry_custom'];
                }
                $updateData['industry'] = $industry ?: null;
            }
            
            if (array_key_exists('company_size', $data)) {
                $updateData['size'] = $data['company_size'] ?: null;
            }
            
            if (array_key_exists('country', $data)) {
                $updateData['country'] = $data['country'] ?: null;
            }

            // Handle address
            if (isset($data['address'])) {
                $address = is_string($data['address']) 
                    ? json_decode($data['address'], true) 
                    : $data['address'];
                
                if (is_array($address)) {
                    // Normalize address strings
                    foreach (['state','city','postal_code','street'] as $k) {
                        if (isset($address[$k]) && is_string($address[$k])) {
                            $address[$k] = trim($address[$k]);
                        }
                    }
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
                    // Store employer logos under uploads/employers/{id}
                    $filePath = $storage->store($file, 'uploads/employers/' . $employer->id);
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
        } catch (\Throwable $t) {
            error_log('Employer profile update error: ' . $t->getMessage());
            $response->json(['error' => 'Failed to update profile', 'message' => $t->getMessage()], 500);
        }
    }
}

