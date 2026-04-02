<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\User;
use App\Models\Candidate;
use App\Models\ResumeFile;

class CandidateCreationService
{
    private const CREATED_BY_DEFAULT = 'self';
    private const CREATED_BY_MAP = [
        'system' => 'admin',
        'bulk' => 'agency',
        'oauth' => 'self',
        'third_party' => 'agency',
        'agency' => 'agency',
        'admin' => 'admin',
        'self' => 'self',
    ];

    private static function normalizeCreatedBy(string $createdBy): string
    {
        $value = strtolower(trim($createdBy));
        if (isset(self::CREATED_BY_MAP[$value])) {
            return self::CREATED_BY_MAP[$value];
        }

        // Default to self if unknown values are used to avoid db enum truncation
        return self::CREATED_BY_DEFAULT;
    }

    public function ensureCandidateForUser(int $userId, array $initial = []): Candidate
    {
        $existing = Candidate::findByUserId($userId);
        if ($existing) {
            if (!empty($initial)) {
                foreach ($initial as $k => $v) {
                    if ((string)$v !== '' && empty($existing->attributes[$k])) {
                        if ($k === 'created_by') {
                            $existing->setAttribute($k, self::normalizeCreatedBy((string)$v));
                        } else {
                            $existing->setAttribute($k, $v);
                        }
                    }
                }
                $existing->save();
            }
            return $existing;
        }

        $createdBy = self::normalizeCreatedBy((string)($initial['created_by'] ?? self::CREATED_BY_DEFAULT));
        $c = new Candidate();
        $c->fill([
            'user_id' => $userId,
            'profile_strength' => 0,
            'is_profile_complete' => 0,
            'profile_status' => 'unverified',
            'visibility' => 'limited',
            'created_by' => $createdBy,
            'source' => (string)($initial['source'] ?? 'website')
        ]);
        foreach ($initial as $k => $v) {
            if ((string)$v !== '') {
                if ($k === 'created_by') {
                    $c->setAttribute($k, self::normalizeCreatedBy((string)$v));
                } else {
                    $c->setAttribute($k, $v);
                }
            }
        }
        $c->save();
        $c->updateProfileStrength();
        return $c;
    }

    public function createOrLinkFromParsedResume(array $parsed, ResumeFile $file, array $options = []): array
    {
        $email = trim((string)($parsed['email'] ?? ''));
        $phoneRaw = (string)($parsed['mobile'] ?? ($parsed['phone'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', $phoneRaw);
        $db = Database::getInstance();
        $user = null;
        if ($email !== '') {
            $row = $db->fetchOne("SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if ($row) $user = new User($row);
        }
        if (!$user && $phone !== '') {
            $row = $db->fetchOne("SELECT * FROM users WHERE phone = :phone LIMIT 1", ['phone' => $phone]);
            if ($row) $user = new User($row);
        }
        $isNewUser = false;
        if (!$user) {
            $user = new User();
            $generatedEmail = $email !== '' ? $email : ('resume+' . substr(hash('sha256', (string)$file->attributes['hash']), 0, 16) . '@invalid.local');
            $user->fill([
                'email' => $generatedEmail,
                'role' => 'candidate',
                'status' => 'pending',
                'phone' => $phone !== '' ? $phone : null,
                'is_email_verified' => 0,
                'is_phone_verified' => 0
            ]);
            $user->setPassword(bin2hex(random_bytes(8)));
            $user->save();
            $isNewUser = true;
        }
        $loc = is_array($parsed['location'] ?? null) ? ($parsed['location'] ?? []) : ['city' => ($parsed['location'] ?? null)];
        $candidateInitial = [
            'full_name' => $parsed['full_name'] ?? ($parsed['name'] ?? null),
            'mobile' => $phone !== '' ? $phone : null,
            'city' => $loc['city'] ?? null,
            'state' => $loc['state'] ?? null,
            'country' => $loc['country'] ?? null,
            'skills_data' => !empty($parsed['skills_data']) ? json_encode($parsed['skills_data']) : (!empty($parsed['skills']) ? json_encode($parsed['skills']) : null),
            'education_data' => !empty($parsed['education_data']) ? json_encode($parsed['education_data']) : (!empty($parsed['education']) ? json_encode($parsed['education']) : null),
            'experience_data' => !empty($parsed['experience_data']) ? json_encode($parsed['experience_data']) : (!empty($parsed['experience']) ? json_encode($parsed['experience']) : null),
            'languages_data' => !empty($parsed['languages_data']) ? json_encode($parsed['languages_data']) : (!empty($parsed['languages']) ? json_encode($parsed['languages']) : null),
            'certificates_data' => !empty($parsed['certificates_data']) ? json_encode($parsed['certificates_data']) : null,
            'profile_status' => 'unverified',
            'visibility' => 'limited',
            'created_by' => $options['created_by'] ?? 'agency',
            'source' => $options['source'] ?? 'bulk_upload'
        ];
        $candidate = $this->ensureCandidateForUser((int)$user->id, $candidateInitial);
        try {
            (new CandidateProfileService())->updateProfileFromParsedData((int)$user->id, $parsed);
        } catch (\Throwable $e) {}
        $file->fill(['candidate_id' => $candidate->id]);
        $file->save();
        // Do NOT send emails here. Emails must be sent only after admin verification/publish.
        return ['user' => $user, 'candidate' => $candidate, 'notified' => false];
    }
}
