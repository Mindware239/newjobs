<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Candidate;
use App\Models\SystemSetting;
use App\Services\NotificationService;

/**
 * Profile Reminder Service
 * 
 * Handles automated reminders for candidates with incomplete profiles.
 */
class ProfileReminderService
{
    /**
     * Process reminders for all candidates with incomplete profiles
     */
    public function processReminders(): int
    {
        if (SystemSetting::get('profile_reminder_enabled', '1') !== '1') {
            return 0;
        }

        $minStrength = (int)SystemSetting::get('profile_reminder_min_strength', '100');
        $frequencyDays = (int)SystemSetting::get('profile_reminder_frequency_days', '3');
        $maxPerWeek = (int)SystemSetting::get('profile_reminder_max_per_week', '2');

        $db = Database::getInstance();
        
        // Find candidates with strength below threshold
        // And who haven't been reminded recently (based on frequency)
        // AND haven't exceeded weekly limit
        $sql = "SELECT c.id, c.user_id, c.full_name, c.profile_strength, u.email, u.phone
                FROM candidates c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN profile_reminders pr ON c.id = pr.candidate_id
                WHERE c.profile_strength < :min_strength
                AND u.status = 'active'
                AND (
                    pr.id IS NULL OR (
                        pr.last_reminder_at <= DATE_SUB(NOW(), INTERVAL :freq DAY)
                        AND pr.last_reminder_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        AND pr.reminder_count < :max_week
                    ) OR (
                        pr.last_reminder_at <= DATE_SUB(NOW(), INTERVAL :freq2 DAY)
                        AND pr.last_reminder_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                    )
                )
                LIMIT 500";
        
        // Simplified SQL logic:
        // 1. Never reminded OR
        // 2. (Last reminder > frequency days ago AND last reminder in this week AND count < max per week) OR
        // 3. (Last reminder > frequency days ago AND last reminder NOT in this week)
        
        // Let's refine the query to be more robust
        $sql = "SELECT c.id, c.user_id, c.full_name, c.profile_strength, u.email, u.phone,
                       pr.last_reminder_at, pr.reminder_count
                FROM candidates c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN profile_reminders pr ON c.id = pr.candidate_id
                WHERE c.profile_strength < :min_strength
                AND u.status = 'active'
                AND (
                    pr.id IS NULL 
                    OR pr.last_reminder_at <= DATE_SUB(NOW(), INTERVAL :freq DAY)
                )
                LIMIT 200";

        $candidates = $db->fetchAll($sql, [
            'min_strength' => $minStrength,
            'freq' => $frequencyDays
        ]);

        $sentCount = 0;
        foreach ($candidates as $cand) {
            if ($this->shouldSendReminder($cand, $maxPerWeek)) {
                if ($this->sendReminder($cand)) {
                    $sentCount++;
                    $this->updateReminderLog((int)$cand['id'], (int)$cand['user_id']);
                }
            }
        }

        return $sentCount;
    }

    /**
     * Check if a reminder should be sent based on weekly limits
     */
    private function shouldSendReminder(array $cand, int $maxPerWeek): bool
    {
        if (!isset($cand['last_reminder_at'])) {
            return true; // Never sent
        }

        $db = Database::getInstance();
        // Count reminders sent in the last 7 days
        $weeklyCount = (int)$db->fetchOne(
            "SELECT COUNT(*) as c FROM notification_logs 
             WHERE candidate_id = :uid 
             AND template_key = 'profile_reminder' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            ['uid' => (int)$cand['user_id']]
        )['c'];

        return $weeklyCount < $maxPerWeek;
    }

    /**
     * Send reminder via multiple channels
     */
    private function sendReminder(array $cand): bool
    {
        $candidateModel = Candidate::find((int)$cand['id']);
        if (!$candidateModel) return false;

        $missingFields = $candidateModel->getMissingFields();
        if (empty($missingFields)) {
            $missingFields = ['Overall profile completeness'];
        }

        $missingHtml = '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($missingFields as $field) {
            $missingHtml .= "<li style='margin-bottom: 5px; color: #4b5563;'>{$field}</li>";
        }
        $missingHtml .= '</ul>';
        
        // Smart AI Suggestions
        $suggestions = $this->getSmartSuggestions($missingFields);
        $suggestionsHtml = '<div style="margin-top: 15px; font-size: 13px; color: #059669; font-style: italic;">';
        $suggestionsHtml .= '<strong>💡 Pro Tip:</strong> ' . $suggestions;
        $suggestionsHtml .= '</div>';

        $strength = (int)$cand['profile_strength'];
        $subject = "Boost Your Profile: Complete Your Profile ({$strength}%)";
        
        // Determine primary CTA based on what's missing
        $ctaLink = '/candidate/profile/edit';
        $ctaLabel = 'Complete Profile';
        
        if (empty($candidateModel->attributes['resume_url'])) {
            $ctaLink = '/candidate/profile/resume';
            $ctaLabel = 'Upload Resume';
        } elseif (empty($candidateModel->attributes['skills_data']) || count($candidateModel->skills()) < 3) {
            $ctaLink = '/candidate/profile/skills';
            $ctaLabel = 'Add Skills';
        } elseif (empty($candidateModel->attributes['profile_picture'])) {
            $ctaLink = '/candidate/profile/edit';
            $ctaLabel = 'Add Photo';
        }

        NotificationService::send(
            (int)$cand['user_id'],
            'profile_reminder',
            $subject,
            "Your profile is {$strength}% complete. Complete the missing sections to increase your visibility to employers.",
            [
                'user_name' => $cand['full_name'],
                'profile_strength' => $strength,
                'missing_fields_list' => $missingHtml . $suggestionsHtml,
                'link' => ($_ENV['APP_URL'] ?? 'http://localhost:8000') . $ctaLink,
                'cta_label' => $ctaLabel,
                'email_template' => 'profile_completion_reminder'
            ],
            $ctaLink
        );

        return true;
    }

    /**
     * Generate smart suggestions based on missing fields
     */
    private function getSmartSuggestions(array $missingFields): string
    {
        if (in_array('Resume (CV)', $missingFields)) {
            return "Candidates with a resume are 8x more likely to be hired. Upload yours now to unlock better job matches!";
        }
        if (in_array('Skills (at least 3)', $missingFields)) {
            return "Adding specific skills helps our AI match you with the right jobs. Add at least 3-5 key skills.";
        }
        if (in_array('Work Experience', $missingFields)) {
            return "Recruiters look for detailed work history. Even listing your current role increases profile trust.";
        }
        if (in_array('Profile Photo', $missingFields)) {
            return "Profiles with professional photos receive 40% more views from recruiters.";
        }
        if (in_array('Self Introduction', $missingFields)) {
            return "A short bio helps employers understand your professional personality. Keep it brief and impactful!";
        }
        return "A complete profile increases your visibility in search results by up to 500%.";
    }

    /**
     * Update or create the reminder log entry
     */
    private function updateReminderLog(int $candidateId, int $userId): void
    {
        $db = Database::getInstance();
        
        // Check if log exists for this week to reset counter or increment
        $existing = $db->fetchOne(
            "SELECT id, reminder_count, last_reminder_at FROM profile_reminders WHERE candidate_id = :cid",
            ['cid' => $candidateId]
        );

        if ($existing) {
            $lastDate = new \DateTime($existing['last_reminder_at']);
            $now = new \DateTime();
            $diff = $now->diff($lastDate);
            
            $newCount = ($diff->days >= 7) ? 1 : (int)$existing['reminder_count'] + 1;
            
            $db->query(
                "UPDATE profile_reminders 
                 SET last_reminder_at = NOW(), reminder_count = :count, updated_at = NOW() 
                 WHERE id = :id",
                ['count' => $newCount, 'id' => $existing['id']]
            );
        } else {
            $db->query(
                "INSERT INTO profile_reminders (candidate_id, user_id, last_reminder_at, reminder_count, created_at, updated_at)
                 VALUES (:cid, :uid, NOW(), 1, NOW(), NOW())",
                ['cid' => $candidateId, 'uid' => $userId]
            );
        }
    }
}
