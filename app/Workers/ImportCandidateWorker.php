<?php

declare(strict_types=1);

namespace App\Workers;

use App\Models\User;
use App\Models\Candidate;
use App\Models\ImportLog;
use App\Core\Database;

class ImportCandidateWorker extends BaseWorker
{
    public function __construct()
    {
        parent::__construct(static::getQueueName());
    }

    protected static function getQueueName(): string
    {
        return 'queue:import_candidates';
    }

    public function process(array $data): bool
    {
        $batchId = $data['batch_id'];
        $filePath = $data['file_path'];
        $adminId = $data['admin_id'];
        $columnMapping = $data['mapping'] ?? [];
        $sendEmail = $data['send_email'] ?? false;

        if (!file_exists($filePath)) {
            ImportLog::log($batchId, $adminId, basename($filePath), 0, 0, 0, 'failed', ['error' => 'File not found']);
            return false;
        }

        // Update log to processing
        $db = Database::getInstance();
        $db->execute("UPDATE import_logs SET status = 'processing' WHERE batch_id = :batch_id", ['batch_id' => $batchId]);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            ImportLog::log($batchId, $adminId, basename($filePath), 0, 0, 0, 'failed', ['error' => 'Cannot open file']);
            return false;
        }

        $header = fgetcsv($handle); // Assume first row is header if mapping provided, or skip based on logic
        // If mapping is provided, we use it to map CSV columns to DB fields.
        // mapping: ['Name' => 'full_name', 'Email' => 'email', ...]
        
        // Invert mapping for easier lookup: index => field_name
        $indexToField = [];
        if (!empty($columnMapping) && $header) {
            foreach ($header as $index => $colName) {
                // Find which field this column maps to
                // columnMapping keys are field names, values are csv headers? Or vice versa?
                // Usually FE sends: field_name => csv_header_index or csv_header_name
                // Let's assume mapping is: field_name => csv_column_index
                foreach ($columnMapping as $field => $csvIndex) {
                    if ((int)$csvIndex === $index) {
                        $indexToField[$index] = $field;
                    }
                }
            }
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $rowNum = 1; // 1-based, starting after header

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $rowData = [];
            
            // Map row data
            foreach ($indexToField as $index => $field) {
                if (isset($row[$index])) {
                    $rowData[$field] = trim($row[$index]);
                }
            }
            
            // Basic Validation
            if (empty($rowData['email'])) {
                $failedCount++;
                $errors[] = ["row" => $rowNum, "error" => "Missing email"];
                continue;
            }

            // Check if email exists
            $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $rowData['email']]);
            if ($existingUser) {
                 // Skip duplicates as per requirement
                $failedCount++; // Or count as skipped? Requirement says "Skipped duplicates". I'll count as failed/skipped.
                $errors[] = ["row" => $rowNum, "error" => "Duplicate email: " . $rowData['email']];
                continue;
            }

            try {
                $db->beginTransaction();

                // Create User
                $user = new User();
                $user->fill([
                    'email' => $rowData['email'],
                    'role' => 'candidate',
                    'status' => 'active', // User account active, but profile inactive? Requirement: "Create candidates as inactive"
                    'is_email_verified' => 0,
                    'verification_token' => bin2hex(random_bytes(32)),
                    'verification_expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
                ]);
                
                // No password set yet
                $user->save();
                $userId = $user->id;

                if (!$userId) {
                    // Fetch ID if not set on object (depends on Model implementation)
                    $u = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $rowData['email']]);
                    $userId = $u['id'];
                }

                // Create Candidate
                $candidate = new Candidate();
                $candData = [
                    'user_id' => $userId,
                    'full_name' => $rowData['full_name'] ?? $rowData['name'] ?? 'Unknown',
                    'mobile' => $rowData['phone'] ?? $rowData['mobile'] ?? null,
                    'city' => $rowData['city'] ?? $rowData['location'] ?? null,
                    'created_by' => 'admin',
                    'source' => $data['source'] ?? 'excel',
                    'profile_status' => 'inactive',
                    'visibility' => 'private', // Not visible until verified
                    'profile_strength' => 0,
                    'is_profile_complete' => 0
                ];
                
                // Handle Skills (tag input -> json)
                if (!empty($rowData['skills'])) {
                    // Assume comma separated
                    $skills = array_map('trim', explode(',', $rowData['skills']));
                    $candData['skills_data'] = json_encode($skills);
                }

                $candidate->fill($candData);
                $candidate->save();

                $db->commit();
                $successCount++;

                // Send Verification Email
                if ($sendEmail) {
                     EmailWorker::enqueue([
                        'to' => $user->attributes['email'],
                        'subject' => 'Verify your account',
                        'template' => 'emails/candidate_verification',
                        'data' => [
                            'name' => $candidate->attributes['full_name'],
                            'token' => $user->attributes['verification_token'],
                            'link' => getenv('APP_URL') . '/verify-account?token=' . $user->attributes['verification_token']
                        ]
                    ]);
                }

            } catch (\Exception $e) {
                $db->rollBack();
                $failedCount++;
                $errors[] = ["row" => $rowNum, "error" => $e->getMessage()];
            }
        }

        fclose($handle);

        // Update Log
        $db->execute(
            "UPDATE import_logs SET total_rows = :total, success_count = :success, failed_count = :failed, status = 'completed', error_log = :errors WHERE batch_id = :batch_id",
            [
                'total' => $successCount + $failedCount,
                'success' => $successCount,
                'failed' => $failedCount,
                'errors' => json_encode($errors),
                'batch_id' => $batchId
            ]
        );

        return true;
    }
}
