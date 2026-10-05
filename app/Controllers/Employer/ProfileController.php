<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\EmployerKycDocument;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;
use App\Core\Storage;
use App\Helpers\AddressHelper;

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
        $kycDocuments = EmployerKycDocument::where('employer_id', '=', $employer->id)->get();
        $kycDocumentsArray = array_map(fn($doc) => $doc->toArray(), $kycDocuments);

        $address = AddressHelper::normalize($employer->attributes['address'] ?? null, [
            'country' => $employer->country ?? '',
            'state' => $employer->state ?? '',
            'city' => $employer->city ?? '',
            'postal_code' => $employer->postal_code ?? '',
        ]);
        $address['country'] = $employer->country ?? ($address['country'] ?? '');
        $address['company_type'] = $employer->company_type ?? ($address['company_type'] ?? '');
        $address['tax_id'] = $employer->tax_id ?? ($address['tax_id'] ?? '');

        $basicInfoComplete = method_exists($employer, 'isBasicInfoComplete') && $employer->isBasicInfoComplete();
        $addressComplete = method_exists($employer, 'isAddressComplete') && $employer->isAddressComplete();
        $startStep = method_exists($employer, 'nextProfileStep') ? $employer->nextProfileStep() : ($basicInfoComplete ? 2 : 1);

        $response->view('employer/profile', [
            'title' => 'My Profile',
            'employer' => $employer,
            'user' => $this->currentUser,
            'address' => $address,
            'kycDocuments' => $kycDocumentsArray,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'startStep' => $startStep,
            'basicInfoComplete' => $basicInfoComplete,
            'addressComplete' => $addressComplete
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
                $phone = preg_replace('/\D+/', '', (string)$data['phone']);
                if ($phone !== '' && strlen($phone) < 10) {
                    $response->json(['error' => 'Mobile Number must be 10 digits'], 422);
                    return;
                }
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
            
            if (array_key_exists('company_type', $data)) {
                $updateData['company_type'] = $data['company_type'] ?: null;
            }
            
            if (array_key_exists('company_size', $data)) {
                $updateData['size'] = $data['company_size'] ?: null;
            }
            
            if (array_key_exists('tax_id', $data)) {
                $taxId = strtoupper(trim((string)$data['tax_id']));
                if ($taxId !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/', $taxId)) {
                    $response->json(['error' => 'Please enter valid GSTIN number.'], 422);
                    return;
                }
                $updateData['tax_id'] = $taxId ?: null;
            }

            if (array_key_exists('register_as', $data)) {
                $updateData['register_as'] = $data['register_as'] ?: 'company';
            }

            if (array_key_exists('profession_type', $data)) {
                $updateData['profession_type'] = $data['profession_type'] ?: null;
            }

            if (array_key_exists('service_category', $data)) {
                $updateData['service_category'] = $data['service_category'] ?: null;
            }
            
            if (array_key_exists('country', $data)) {
                $updateData['country'] = $data['country'] ?: null;
            }

            // Handle address
            if (isset($data['address'])) {
                $rawAddress = is_string($data['address']) 
                    ? json_decode($data['address'], true) 
                    : $data['address'];
                
                if (is_array($rawAddress)) {
                    $address = AddressHelper::forStorage($rawAddress, [
                        'country' => $data['country'] ?? '',
                    ]);
                    
                    if (($address['street'] ?? '') === '') {
                        $response->json(['error' => 'Street Address is required'], 422);
                        return;
                    }

                    if (!empty($address['postal_code']) && !preg_match('/^[0-9]{6}$/', (string)$address['postal_code'])) {
                        $response->json(['error' => 'Pin Code must be exactly 6 digits'], 422);
                        return;
                    }

                    if (array_key_exists('country', $data)) {
                        $address['country'] = is_string($data['country']) ? trim($data['country']) : $data['country'];
                    }

                    $updateData['address'] = json_encode($address, JSON_UNESCAPED_UNICODE);
                    $updateData['state'] = $address['state'] ?? null;
                    $updateData['city'] = $address['city'] ?? null;
                    $updateData['postal_code'] = $address['postal_code'] ?? null;
                    
                    error_log('Saving employer address: ' . $updateData['address']);
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

            // Handle missing company_type column in database
            try {
                $employer->save();
            } catch (\Throwable $e) {
                if (strpos($e->getMessage(), "Unknown column 'company_type'") !== false) {
                    error_log('company_type column missing, attempting to add it...');
                    \App\Core\Database::getInstance()->execute("ALTER TABLE employers ADD COLUMN company_type VARCHAR(100) DEFAULT NULL AFTER industry");
                    $employer->save();
                } else {
                    throw $e;
                }
            }

            $response->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'employer' => $employer->toArray(),
                'user' => $this->currentUser->toArray(),
                'basic_info_complete' => method_exists($employer, 'isBasicInfoComplete') ? $employer->isBasicInfoComplete() : false,
                'address_complete' => method_exists($employer, 'isAddressComplete') ? $employer->isAddressComplete() : false,
                'profile_complete' => method_exists($employer, 'isProfileComplete') ? $employer->isProfileComplete() : false,
                'next_profile_step' => method_exists($employer, 'nextProfileStep') ? $employer->nextProfileStep() : 1
            ]);
        } catch (\Throwable $t) {
            error_log('Employer profile update error: ' . $t->getMessage());
            $response->json(['error' => 'Failed to update profile', 'message' => $t->getMessage()], 500);
        }
    }
}

