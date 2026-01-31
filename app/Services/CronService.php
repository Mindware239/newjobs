<?php

declare (strict_types = 1);

namespace App\Services;

use App\Core\Database;
use App\Models\Candidate;
use App\Services\ESService;
use App\Services\NotificationService;

class CronService
{
    public function runTask(string $task): void
    {
        switch ($task) {
            case 'monitor_aggregate_minute':
                $this->monitorAggregateMinute();
                break;
            case 'monitor_system_stats':
                $this->monitorSystemStats();
                break;
            case 'monitor_db_load':
                $this->monitorDbLoad();
                break;
            case 'monitor_queue_stats':
                $this->monitorQueueStats();
                break;
            case 'interview_reminders':
                $this->processInterviewReminders();
                break;
            case 'upgrade_reminders':
                $this->processUpgradeReminders();
                break;
            case 'expire_premium_candidates':
                $this->expirePremiumCandidates();
                break;
            case 'reindex_jobs':
                $this->reindexJobs();
                break;
            case 'notify_expiring_subscriptions':
                $this->notifyExpiringSubscriptions();
                break;
            case 'notify_incomplete_profiles':
                $this->notifyIncompleteProfiles();
                break;
            case 'notify_inactive_candidates':
                $this->notifyInactiveCandidates();
                break;
            case 'notify_abandoned_job_views':
                $this->notifyAbandonedJobViews();
                break;
            case 'notify_subscription_limits':
                $this->notifySubscriptionLimits();
                break;
            case 'auto_apply_candidates':
                $this->autoApplyCandidates();
                break;
            case 'notify_profile_views':
                $this->notifyProfileViews();
                break;
            case 'notify_low_match_suggestions':
                $this->notifyLowMatchSuggestions();
                break;
        }
    }

    private function notifyLowMatchSuggestions(): void
    {
        try {
            $db = Database::getInstance();
            $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
            
            // Find low match applications
            $apps = $db->fetchAll(
                "SELECT a.id, a.job_id, a.candidate_id, a.match_score, j.title as job_title, c.user_id
                 FROM applications a
                 JOIN jobs j ON a.job_id = j.id
                 JOIN candidates c ON a.candidate_id = c.id
                 WHERE a.created_at >= :start 
                 AND a.match_score < 60
                 AND a.match_score > 0", // Ignore 0 if it means not calculated
                ['start' => $start]
            );

            foreach ($apps as $app) {
                // Check if already notified for this application
                $exists = $db->fetchOne(
                    "SELECT id FROM notification_logs 
                     WHERE candidate_id = :cid 
                     AND template_key = 'low_match_suggestion' 
                     AND metadata LIKE :meta",
                    ['cid' => (int)$app['user_id'], 'meta' => '%"application_id":' . $app['id'] . '%']
                );

                if (!$exists) {
                    NotificationService::send(
                        (int)$app['user_id'],
                        'low_match_suggestion',
                        'Improve Your Profile for ' . $app['job_title'],
                        "Your match score for {$app['job_title']} is {$app['match_score']}%. Update your skills and experience to improve your chances!",
                        [
                            'application_id' => $app['id'],
                            'job_title' => $app['job_title'],
                            'match_score' => $app['match_score'],
                            'link' => '/candidate/profile/edit'
                        ],
                        '/candidate/profile/edit'
                    );
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (notify_low_match_suggestions): " . $t->getMessage());
        }
    }

    private function notifyAbandonedJobViews(): void
    {
        try {
            $db = Database::getInstance();
            // Look for views from yesterday (24-26 hours ago)
            $start = date('Y-m-d H:i:s', strtotime('-26 hours'));
            $end = date('Y-m-d H:i:s', strtotime('-24 hours'));

            $views = $db->fetchAll(
                "SELECT jv.job_id, jv.candidate_id, j.title as job_title, c.user_id, c.full_name
                 FROM job_views jv
                 JOIN jobs j ON jv.job_id = j.id
                 JOIN candidates c ON jv.candidate_id = c.id
                 WHERE jv.viewed_at BETWEEN :start AND :end
                 GROUP BY jv.job_id, jv.candidate_id"
                 , ['start' => $start, 'end' => $end]
            );

            foreach ($views as $view) {
                // Check if applied
                $applied = $db->fetchOne(
                    "SELECT id FROM applications WHERE job_id = :jid AND candidate_id = :cid",
                    ['jid' => (int)$view['job_id'], 'cid' => (int)$view['candidate_id']]
                );

                if (!$applied) {
                    // Check if already notified
                    $exists = $db->fetchOne(
                        "SELECT id FROM notification_logs 
                         WHERE candidate_id = :cid 
                         AND template_key = 'abandoned_job_view' 
                         AND metadata LIKE :meta",
                        ['cid' => (int)$view['user_id'], 'meta' => '%"job_id":' . $view['job_id'] . '%']
                    );

                    if (!$exists) {
                        NotificationService::send(
                            (int)$view['user_id'],
                            'abandoned_job_view',
                            'Still interested in ' . $view['job_title'] . '?',
                            "You viewed {$view['job_title']} recently but haven't applied yet. Don't miss this opportunity!",
                            [
                                'job_id' => $view['job_id'],
                                'job_title' => $view['job_title'],
                                'link' => '/candidate/jobs/' . $view['job_id']
                            ],
                            '/candidate/jobs/' . $view['job_id']
                        );
                    }
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (notify_abandoned_job_views): " . $t->getMessage());
        }
    }

    private function notifyProfileViews(): void
    {
        try {
            $db = Database::getInstance();
            // Find views 2-3 days old
            $start = date('Y-m-d H:i:s', strtotime('-3 days'));
            $end = date('Y-m-d H:i:s', strtotime('-2 days'));

            $views = $db->fetchAll(
                "SELECT cv.id, cv.employer_id, cv.candidate_id, e.user_id as employer_user_id, c.full_name as candidate_name, c.user_id as candidate_user_id
                 FROM candidate_views cv
                 JOIN employers e ON cv.employer_id = e.id
                 JOIN candidates c ON cv.candidate_id = c.id
                 WHERE cv.viewed_at BETWEEN :start AND :end
                 GROUP BY cv.employer_id, cv.candidate_id", 
                ['start' => $start, 'end' => $end]
            );

            foreach ($views as $view) {
                 // Check for application
                 $hasApp = $db->fetchOne(
                     "SELECT id FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = :eid) AND candidate_user_id = :cuid",
                     ['eid' => (int)$view['employer_id'], 'cuid' => (int)$view['candidate_user_id']]
                 );

                 // Check for conversation
                 $hasMsg = $db->fetchOne(
                     "SELECT id FROM conversations WHERE employer_id = :eid AND candidate_user_id = :cuid",
                     ['eid' => (int)$view['employer_id'], 'cuid' => (int)$view['candidate_user_id']]
                 );

                 if (!$hasApp && !$hasMsg) {
                     // Check log
                     $logExists = $db->fetchOne(
                        "SELECT id FROM notification_logs WHERE employer_id = :eid AND template_key = 'profile_view_nudge' AND metadata LIKE :meta",
                        ['eid' => (int)$view['employer_id'], 'meta' => '%"candidate_id":' . $view['candidate_id'] . '%']
                     );

                     if (!$logExists) {
                         NotificationService::send(
                             (int)$view['employer_user_id'],
                             'profile_view_nudge',
                             'Did you like ' . $view['candidate_name'] . '?',
                             "You viewed {$view['candidate_name']}'s profile recently. Don't miss out - invite them to apply or send a message!",
                             [
                                 'candidate_id' => $view['candidate_id'],
                                 'candidate_name' => $view['candidate_name'],
                                 'link' => '/employer/messages/start?candidate_id=' . $view['candidate_id'],
                                 'employer_id' => $view['employer_id']
                             ],
                             '/employer/messages/start?candidate_id=' . $view['candidate_id']
                         );
                     }
                 }
            }

        } catch (\Throwable $t) {
            error_log("Cron Error (notify_profile_views): " . $t->getMessage());
        }
    }

    private function processInterviewReminders(): void
    {
        try {
            $db            = Database::getInstance();
            $window24Start = $db->fetchOne("SELECT DATE_ADD(NOW(), INTERVAL 24 HOUR) as ts")['ts'];
            $window24End   = $db->fetchOne("SELECT DATE_ADD(NOW(), INTERVAL 25 HOUR) as ts")['ts'];
            $window2Start  = $db->fetchOne("SELECT DATE_ADD(NOW(), INTERVAL 2 HOUR) as ts")['ts'];
            $window2End    = $db->fetchOne("SELECT DATE_ADD(NOW(), INTERVAL 3 HOUR) as ts")['ts'];

            $rem24 = $db->fetchAll(
                "SELECT i.id as interview_id, j.title as job_title, u.id as candidate_user_id, u.email as candidate_email
                 FROM interviews i
                 INNER JOIN applications a ON i.application_id = a.id
                 INNER JOIN jobs j ON a.job_id = j.id
                 INNER JOIN users u ON a.candidate_user_id = u.id
                 WHERE i.status IN ('scheduled','rescheduled')
                   AND i.scheduled_start BETWEEN :start AND :end",
                ['start' => $window24Start, 'end' => $window24End]
            );

            foreach ($rem24 as $row) {
                if (empty($row['candidate_email'])) {
                    continue;
                }

                $exists = $db->fetchOne(
                    "SELECT id FROM notification_logs
                     WHERE candidate_id = :cid AND template_key = 'interview_reminder_24h'
                       AND metadata LIKE :meta",
                    ['cid' => (int) $row['candidate_user_id'], 'meta' => '%\"interview_id\":' . (int) $row['interview_id'] . '%']
                );
                if (! $exists) {
                    NotificationService::queueEmail(
                        $row['candidate_email'],
                        'interview_reminder_24h',
                        [
                            'job_title'         => (string) ($row['job_title'] ?? ''),
                            'candidate_user_id' => (int) $row['candidate_user_id'],
                            'interview_id'      => (int) $row['interview_id'],
                        ]
                    );
                }
            }

            $rem2 = $db->fetchAll(
                "SELECT i.id as interview_id, j.title as job_title, u.id as candidate_user_id, u.email as candidate_email
                 FROM interviews i
                 INNER JOIN applications a ON i.application_id = a.id
                 INNER JOIN jobs j ON a.job_id = j.id
                 INNER JOIN users u ON a.candidate_user_id = u.id
                 WHERE i.status IN ('scheduled','rescheduled')
                   AND i.scheduled_start BETWEEN :start AND :end",
                ['start' => $window2Start, 'end' => $window2End]
            );

            foreach ($rem2 as $row) {
                if (empty($row['candidate_email'])) {
                    continue;
                }

                $exists = $db->fetchOne(
                    "SELECT id FROM notification_logs
                     WHERE candidate_id = :cid AND template_key = 'interview_reminder_2h'
                       AND metadata LIKE :meta",
                    ['cid' => (int) $row['candidate_user_id'], 'meta' => '%\"interview_id\":' . (int) $row['interview_id'] . '%']
                );
                if (! $exists) {
                    NotificationService::queueEmail(
                        $row['candidate_email'],
                        'interview_reminder_2h',
                        [
                            'job_title'         => (string) ($row['job_title'] ?? ''),
                            'candidate_user_id' => (int) $row['candidate_user_id'],
                            'interview_id'      => (int) $row['interview_id'],
                        ]
                    );
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (interview_reminders): " . $t->getMessage());
        }
    }

    private function processUpgradeReminders(): void
    {
        try {
            $db = Database::getInstance();
            // Interviews live for ~4 minutes (send 1-minute remaining reminder for 5-min free window)
            $rows = $db->fetchAll(
                "SELECT
                    i.id as interview_id, i.employer_id, i.started_at,
                    a.candidate_user_id, u.phone as candidate_phone, eu.phone as employer_phone
                 FROM interviews i
                 INNER JOIN applications a ON a.id = i.application_id
                 INNER JOIN users u ON u.id = a.candidate_user_id
                 INNER JOIN employers e ON e.id = i.employer_id
                 INNER JOIN users eu ON eu.id = e.user_id
                 WHERE i.status = 'live'
                   AND i.started_at IS NOT NULL
                   AND i.started_at BETWEEN DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND DATE_SUB(NOW(), INTERVAL 4 MINUTE)"
            );
            foreach ($rows as $r) {
                $meta   = json_encode(['interview_id' => (int) $r['interview_id']], JSON_UNESCAPED_UNICODE);
                $exists = $db->fetchOne(
                    "SELECT id FROM notification_logs
                     WHERE channel = 'whatsapp' AND template_key = 'upgrade_reminder'
                       AND metadata LIKE :meta",
                    ['meta' => '%\"interview_id\":' . (int) $r['interview_id'] . '%']
                );
                if ($exists) {
                    continue;
                }

                $upgradeLink = ($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/employer/subscription';
                $data        = [
                    'upgrade_link'      => $upgradeLink,
                    'interview_id'      => (int) $r['interview_id'],
                    'employer_id'       => (int) $r['employer_id'],
                    'candidate_user_id' => (int) $r['candidate_user_id'],
                ];
                $candPhone = (string) ($r['candidate_phone'] ?? '');
                $empPhone  = (string) ($r['employer_phone'] ?? '');
                if ($candPhone !== '') {
                    NotificationService::queueWhatsApp($candPhone, 'upgrade_reminder', $data);
                }
                if ($empPhone !== '') {
                    NotificationService::queueWhatsApp($empPhone, 'upgrade_reminder', $data);
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (upgrade_reminders): " . $t->getMessage());
        }
    }

    private function expirePremiumCandidates(): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE candidates
                 SET is_premium = 0
                 WHERE is_premium = 1
                   AND premium_expires_at IS NOT NULL
                   AND premium_expires_at <= NOW()"
            );
        } catch (\Throwable $t) {
            error_log("Cron Error (expire_premium_candidates): " . $t->getMessage());
        }
    }

    private function reindexJobs(): void
    {
        try {
            $es = new ESService();
            $es->createIndices();
            $db   = Database::getInstance();
            $jobs = $db->fetchAll('SELECT id FROM jobs WHERE status = "active" ORDER BY updated_at DESC LIMIT 200');
            foreach ($jobs as $j) {$es->indexJob((int) $j['id']);}
        } catch (\Throwable $t) {
            error_log("Cron Error (reindex_jobs): " . $t->getMessage());
        }
    }

    private function notifyExpiringSubscriptions(): void
    {
        try {
            $db = Database::getInstance();

            // --- 1. Upcoming Expirations (7, 3, 1 days) ---
            // Fetch active subscriptions expiring within next 8 days
            $upcomingExpirations = $db->fetchAll(
                "SELECT es.id, es.employer_id, es.expires_at, e.user_id, es.plan_id
                 FROM employer_subscriptions es
                 JOIN employers e ON es.employer_id = e.id
                 WHERE es.status IN ('active', 'trial')
                   AND es.expires_at IS NOT NULL
                   AND es.expires_at > NOW()
                   AND es.expires_at <= DATE_ADD(NOW(), INTERVAL 8 DAY)"
            );

            foreach ($upcomingExpirations as $sub) {
                try {
                    $expiresAt = new \DateTime($sub['expires_at']);
                    $now       = new \DateTime();
                    $diff      = $now->diff($expiresAt);
                    $days      = $diff->days; // Difference in days

                    // Logic: If expires tomorrow (diff=1), notify. If today (diff=0), handled by expiry logic soon.
                    // We target 7, 3, and 1 days.
                    $thresholds = [7, 3, 1];

                    if (in_array($days, $thresholds, true)) {
                        $templateKey = "subscription_expiring_{$days}d";

                        // Check deduplication in notification_logs to ensure we haven't sent this specific alert recently
                        // We check if a notification with this template_key was sent to this user in last 24h
                        $exists = $db->fetchOne(
                            "SELECT id FROM notification_logs
                             WHERE employer_id = :eid
                               AND template_key = :tpl
                               AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                            ['eid' => (int) $sub['employer_id'], 'tpl' => $templateKey]
                        );

                        if (! $exists) {
                            NotificationService::send(
                                (int) $sub['user_id'],
                                $templateKey,                                                                              // Type
                                'Subscription Expiring Soon',                                                              // Title
                                "Your subscription will expire in {$days} days. Renew now to avoid service interruption.", // Message
                                [
                                    'subscription_id' => (int) $sub['id'],
                                    'days_remaining'  => $days,
                                    'expires_at'      => $sub['expires_at'],
                                    'link'            => '/employer/subscription',
                                    'email_template'  => 'generic_notification',    // Use generic for now unless specialized template exists
                                    'employer_id'     => (int) $sub['employer_id'], // For logging
                                ],
                                '/employer/subscription' // Link
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("Error processing subscription {$sub['id']}: " . $e->getMessage());
                }
            }

            // --- 2. Expired Subscriptions ---
            $expiredSubs = $db->fetchAll(
                "SELECT es.id, es.employer_id, es.expires_at, e.user_id
                 FROM employer_subscriptions es
                 JOIN employers e ON es.employer_id = e.id
                 WHERE es.status IN ('active', 'trial')
                   AND es.expires_at IS NOT NULL
                   AND es.expires_at <= NOW()"
            );

            foreach ($expiredSubs as $sub) {
                try {
                    // Update status to expired
                    $db->query(
                        "UPDATE employer_subscriptions SET status = 'expired' WHERE id = :id",
                        ['id' => (int) $sub['id']]
                    );

                    // Send Notification
                    NotificationService::send(
                        (int) $sub['user_id'],
                        'subscription_expired',
                        'Subscription Expired',
                        "Your subscription has expired. Premium features are now restricted. Please renew to continue.",
                        [
                            'subscription_id' => (int) $sub['id'],
                            'expires_at'      => $sub['expires_at'],
                            'link'            => '/employer/subscription',
                            'email_template'  => 'generic_notification',
                            'employer_id'     => (int) $sub['employer_id'],
                        ],
                        '/employer/subscription'
                    );
                } catch (\Throwable $e) {
                    error_log("Error expiring subscription {$sub['id']}: " . $e->getMessage());
                }
            }

        } catch (\Throwable $t) {
            error_log("Cron Error (notify_expiring_subscriptions): " . $t->getMessage());
        }
    }

    private function notifyIncompleteProfiles(): void
    {
        try {
            $db = Database::getInstance();
            // Find candidates with incomplete profiles created more than 24h ago
            // Limit to 50 per run
            $candidates = $db->fetchAll(
                "SELECT c.id, c.user_id, c.full_name, c.profile_strength, c.resume_url, c.skills_data, c.education_data, u.email
                 FROM candidates c
                 JOIN users u ON c.user_id = u.id
                 WHERE (c.is_profile_complete = 0 OR c.profile_strength < 80)
                   AND c.created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                   AND u.status = 'active'
                 LIMIT 50"
            );

            foreach ($candidates as $cand) {
                // Check if notified recently (7 days)
                $exists = $db->fetchOne(
                    "SELECT id FROM notification_logs
                     WHERE candidate_id = :cid
                       AND template_key = 'profile_nudge'
                       AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
                    ['cid' => (int) $cand['user_id']]
                );

                if ($exists) {
                    continue;
                }

                $missing = [];
                if (empty($cand['resume_url'])) {
                    $missing[] = 'Resume';
                }

                $skills = json_decode($cand['skills_data'] ?? '[]', true);
                if (empty($skills)) {
                    $missing[] = 'Skills';
                }

                $edu = json_decode($cand['education_data'] ?? '[]', true);
                if (empty($edu)) {
                    $missing[] = 'Education';
                }

                if (empty($missing) && (int) $cand['profile_strength'] < 80) {
                    $missing[] = 'Profile Details';
                }

                if (! empty($missing)) {
                    $missingStr = implode(', ', $missing);
                    $message    = "Your profile is missing: {$missingStr}. Complete it now to get 3x more job matches!";

                    NotificationService::send(
                        (int) $cand['user_id'],
                        'profile_nudge',
                        'Complete Your Profile',
                        $message,
                        [
                            'missing_fields'   => $missing,
                            'profile_strength' => $cand['profile_strength'],
                            'link'             => '/candidate/profile/edit',
                            'email_template'   => 'generic_notification',
                        ],
                        '/candidate/profile/edit'
                    );
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (notify_incomplete_profiles): " . $t->getMessage());
        }
    }

    private function notifyInactiveCandidates(): void
    {
        try {
            $db = Database::getInstance();

            // Tiers: 7, 30, 60 days
            $tiers = [7, 30, 60];

            foreach ($tiers as $days) {
                // Find candidates inactive for approximately X days
                $users = $db->fetchAll(
                    "SELECT u.id, u.first_name, u.email
                     FROM users u
                     WHERE u.role = 'candidate'
                       AND u.status = 'active'
                       AND u.last_login IS NOT NULL
                       AND u.last_login <= DATE_SUB(NOW(), INTERVAL :days DAY)
                       AND u.last_login > DATE_SUB(NOW(), INTERVAL :days + 2 DAY)
                     LIMIT 50",
                    ['days' => $days]
                );

                foreach ($users as $user) {
                    $templateKey = "inactivity_{$days}d";

                    $exists = $db->fetchOne(
                        "SELECT id FROM notification_logs
                         WHERE candidate_id = :cid
                           AND template_key = :tpl
                           AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)",
                        ['cid' => (int) $user['id'], 'tpl' => $templateKey, 'days' => $days]
                    );

                    if ($exists) {
                        continue;
                    }

                    // Content customization
                    $title   = "We miss you!";
                    $message = "Come back to see new jobs.";
                    $link    = "/candidate/dashboard";

                    if ($days === 7) {
                        $title   = "New Jobs Are Waiting";
                        $message = "It's been a week! We've found new jobs that match your profile.";
                        $link    = "/candidate/jobs";
                    } elseif ($days === 30) {
                        $title   = "Don't Miss Out";
                        $message = "You haven't checked in for a month. Top employers are hiring now.";
                    } elseif ($days === 60) {
                        $title   = "Are You Still Looking?";
                        $message = "We can help you find your next role. Update your profile today.";
                    }

                    NotificationService::send(
                        (int) $user['id'],
                        $templateKey,
                        $title,
                        $message,
                        [
                            'days_inactive'  => $days,
                            'link'           => $link,
                            'email_template' => 'generic_notification',
                        ],
                        $link
                    );
                }
            }

        } catch (\Throwable $t) {
            error_log("Cron Error (notify_inactive_candidates): " . $t->getMessage());
        }
    }

    /* duplicate removed */

    private function autoApplyCandidates(): void
    {
        try {
            $db     = Database::getInstance();
            $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
            // System-level controls (admin-governed)
            $enabled               = 1;
            $minScore              = 70;
            $maxPerCandidatePerDay = 3;
            $companyCooldownDays   = 30;
            $dailyGlobalLimit      = 1000;
            $minProfileStrength    = 60;
            $pauseRejThreshold     = 0;
            $pauseRejDays          = 30;
            $mandatorySections     = [];
            $blacklistCandidates   = [];
            $blacklistEmployers    = [];
            try {
                $enabled               = (int) (\App\Models\SystemSetting::get('auto_apply_enabled', '1'));
                $minScore              = (int) (\App\Models\SystemSetting::get('auto_apply_min_match_score', '70'));
                $maxPerCandidatePerDay = (int) (\App\Models\SystemSetting::get('auto_apply_max_per_candidate_per_day', '3'));
                $companyCooldownDays   = (int) (\App\Models\SystemSetting::get('auto_apply_company_cooldown_days', '30'));
                $dailyGlobalLimit      = (int) (\App\Models\SystemSetting::get('auto_apply_daily_global_limit', '1000'));
                $minProfileStrength    = (int) (\App\Models\SystemSetting::get('auto_apply_min_profile_strength', '60'));
                $pauseRejThreshold     = (int) (\App\Models\SystemSetting::get('auto_apply_pause_rejections_threshold', '0'));
                $pauseRejDays          = (int) (\App\Models\SystemSetting::get('auto_apply_pause_rejections_days', '30'));
                $mandatory             = (string) (\App\Models\SystemSetting::get('auto_apply_mandatory_sections', ''));
                $mandatorySections     = array_filter(array_map('trim', explode(',', $mandatory)));
                $bc                    = (string) (\App\Models\SystemSetting::get('auto_apply_blacklist_candidates', ''));
                $be                    = (string) (\App\Models\SystemSetting::get('auto_apply_blacklist_employers', ''));
                $blacklistCandidates   = array_filter(array_map('intval', array_map('trim', explode(',', $bc))));
                $blacklistEmployers    = array_filter(array_map('intval', array_map('trim', explode(',', $be))));
            } catch (\Throwable $e) {
                // Fallback defaults if settings table not available
                $enabled = 1;
            }
            // Env overrides
            $enabled               = isset($_ENV['AUTO_APPLY_ENABLED']) ? (int) $_ENV['AUTO_APPLY_ENABLED'] : $enabled;
            $minScore              = isset($_ENV['AUTO_APPLY_MIN_MATCH_SCORE']) ? (int) $_ENV['AUTO_APPLY_MIN_MATCH_SCORE'] : $minScore;
            $maxPerCandidatePerDay = isset($_ENV['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY']) ? (int) $_ENV['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY'] : $maxPerCandidatePerDay;
            $companyCooldownDays   = isset($_ENV['AUTO_APPLY_COMPANY_COOLDOWN_DAYS']) ? (int) $_ENV['AUTO_APPLY_COMPANY_COOLDOWN_DAYS'] : $companyCooldownDays;
            $dailyGlobalLimit      = isset($_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT']) ? (int) $_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] : $dailyGlobalLimit;
            $minProfileStrength    = isset($_ENV['AUTO_APPLY_MIN_PROFILE_STRENGTH']) ? (int) $_ENV['AUTO_APPLY_MIN_PROFILE_STRENGTH'] : $minProfileStrength;
            $pauseRejThreshold     = isset($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD']) ? (int) $_ENV['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD'] : $pauseRejThreshold;
            $pauseRejDays          = isset($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_DAYS']) ? (int) $_ENV['AUTO_APPLY_PAUSE_REJECTIONS_DAYS'] : $pauseRejDays;
            if (isset($_ENV['AUTO_APPLY_MANDATORY_SECTIONS'])) {
                $mandatorySections = array_filter(array_map('trim', explode(',', (string) $_ENV['AUTO_APPLY_MANDATORY_SECTIONS'])));
            }
            if (isset($_ENV['AUTO_APPLY_BLACKLIST_CANDIDATES'])) {
                $blacklistCandidates = array_filter(array_map('intval', array_map('trim', explode(',', (string) $_ENV['AUTO_APPLY_BLACKLIST_CANDIDATES']))));
            }
            if (isset($_ENV['AUTO_APPLY_BLACKLIST_EMPLOYERS'])) {
                $blacklistEmployers = array_filter(array_map('intval', array_map('trim', explode(',', (string) $_ENV['AUTO_APPLY_BLACKLIST_EMPLOYERS']))));
            }
            if ($enabled !== 1) {
                return;
            }
            // Enforce daily global limit
            $globalCountRow = $db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE application_method = 'auto' AND DATE(applied_at) = CURDATE()");
            $globalToday    = (int) ($globalCountRow['c'] ?? 0);
            if ($dailyGlobalLimit > 0 && $globalToday >= $dailyGlobalLimit) {
                return;
            }
            // Candidate pool (premium, opted-in)
            $candidates = $db->fetchAll(
                "SELECT id, user_id
                 FROM candidates
                 WHERE is_premium = 1
                   AND premium_expires_at > NOW()
                   AND auto_apply_enabled = 1
                 ORDER BY auto_apply_last_run_at ASC NULLS FIRST"
            );
            foreach ($candidates as $cand) {
                $candidateId = (int) $cand['id'];
                $userId      = (int) $cand['user_id'];
                if (! empty($blacklistCandidates) && in_array($userId, $blacklistCandidates, true)) {
                    continue;
                }
                $candModel = Candidate::find($candidateId);
                if (! $candModel) {
                    continue;
                }
                $ps = (int) ($candModel->attributes['profile_strength'] ?? 0);
                if ($minProfileStrength > 0 && $ps < $minProfileStrength) {
                    continue;
                }
                if (! empty($mandatorySections)) {
                    $ok = true;
                    foreach ($mandatorySections as $section) {
                        $val = $candModel->attributes[$section] ?? null;
                        if ($val === null || $val === '' || (is_string($val) && trim($val) === '')) {
                            $ok = false;
                            break;
                        }
                    }
                    if (! $ok) {
                        continue;
                    }
                }
                if ($pauseRejThreshold > 0) {
                    $rejRow = $db->fetchOne(
                        "SELECT COUNT(*) as c
                         FROM applications
                         WHERE candidate_user_id = :uid
                           AND status = 'rejected'
                           AND applied_at >= DATE_SUB(NOW(), INTERVAL :days DAY)",
                        ['uid' => $userId, 'days' => $pauseRejDays]
                    );
                    $rejCount = (int) ($rejRow['c'] ?? 0);
                    if ($rejCount >= $pauseRejThreshold) {
                        continue;
                    }
                }
                // Per-candidate daily cap
                $candCountRow = $db->fetchOne("SELECT COUNT(*) as c FROM applications WHERE candidate_user_id = :uid AND application_method = 'auto' AND DATE(applied_at) = CURDATE()", ['uid' => $userId]);
                $candToday    = (int) ($candCountRow['c'] ?? 0);
                if ($maxPerCandidatePerDay > 0 && $candToday >= $maxPerCandidatePerDay) {
                    continue;
                }
                // Find scored jobs above threshold
                $jobs = $db->fetchAll(
                    "SELECT cjs.job_id, cjs.overall_match_score
                     FROM candidate_job_scores cjs
                     JOIN jobs j ON j.id = cjs.job_id
                     WHERE cjs.candidate_id = :cid
                       AND cjs.overall_match_score >= :thr
                       AND j.status = 'published'
                     ORDER BY cjs.overall_match_score DESC, cjs.updated_at DESC
                     LIMIT 20",
                    ['cid' => $candidateId, 'thr' => $minScore]
                );
                foreach ($jobs as $row) {
                    $jobId = (int) $row['job_id'];
                    $score = (int) $row['overall_match_score'];
                    // Deduplicate
                    $exists = $db->fetchOne(
                        "SELECT id FROM applications WHERE job_id = :jid AND candidate_user_id = :uid",
                        ['jid' => $jobId, 'uid' => $userId]
                    );
                    if ($exists) {
                        continue;
                    }
                    // Company cooldown: skip if applied to same employer within cooldown window
                    $jobRow     = $db->fetchOne("SELECT employer_id FROM jobs WHERE id = :jid", ['jid' => $jobId]);
                    $employerId = (int) ($jobRow['employer_id'] ?? 0);
                    if (! empty($blacklistEmployers) && in_array($employerId, $blacklistEmployers, true)) {
                        continue;
                    }
                    if ($employerId > 0 && $companyCooldownDays > 0) {
                        $recent = $db->fetchOne(
                            "SELECT a.id
                             FROM applications a
                             INNER JOIN jobs j ON j.id = a.job_id
                             WHERE a.candidate_user_id = :uid
                               AND j.employer_id = :eid
                               AND a.applied_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                             LIMIT 1",
                            ['uid' => $userId, 'eid' => $employerId, 'days' => $companyCooldownDays]
                        );
                        if ($recent) {
                            continue;
                        }
                    }
                    // Enforce per-candidate cap (incrementally)
                    if ($maxPerCandidatePerDay > 0 && $candToday >= $maxPerCandidatePerDay) {
                        break;
                    }
                    // Create application (auto)
                    try {
                        $db->query(
                            "INSERT INTO applications (job_id, candidate_user_id, status, source, application_method, match_score, applied_at, updated_at)
                             VALUES (:jid, :uid, 'applied', 'portal', 'auto', :score, NOW(), NOW())",
                            ['jid' => $jobId, 'uid' => $userId, 'score' => $score]
                        );
                        $appId = (int) $db->fetchOne("SELECT LAST_INSERT_ID() as id")['id'];
                        $db->query(
                            "INSERT INTO application_events (application_id, actor_user_id, from_status, to_status, comment, created_at)
                             VALUES (:aid, NULL, NULL, 'applied', :comment, NOW())",
                            ['aid' => $appId, 'comment' => 'Auto-applied based on preferences and match score']
                        );
                        \App\Services\NotificationService::send(
                            (int)$userId,
                            'auto_apply_success',
                            'Auto Applied',
                            'We applied you to a matching job.',
                            [
                                'job_id' => (int)$jobId,
                                'match_score' => (int)$score,
                                'dashboard_link' => $appUrl . '/candidate/applications',
                            ],
                            $appUrl . '/candidate/applications',
                            ['in_app']
                        );
                        $candToday++;
                        $globalToday++;
                        if ($dailyGlobalLimit > 0 && $globalToday >= $dailyGlobalLimit) {
                            break;
                        }
                    } catch (\Throwable $e) {
                        error_log("Auto-apply insert error (candidate {$candidateId}, job {$jobId}): " . $e->getMessage());
                        try {
                            $db->query(
                                "INSERT INTO audit_logs (user_id, action, entity_type, old_value, new_value, ip_address, created_at)
                                 VALUES (:uid, 'auto_apply_error', 'application', NULL, :newv, :ip, NOW())",
                                [
                                    'uid'  => $userId,
                                    'newv' => json_encode(['candidate_id' => $candidateId, 'job_id' => $jobId, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                                    'ip'   => $_SERVER['REMOTE_ADDR'] ?? 'system',
                                ]
                            );
                        } catch (\Throwable $ignore) {}
                    }
                }
                // Update last run
                $db->query(
                    "UPDATE candidates SET auto_apply_last_run_at = NOW() WHERE id = :cid",
                    ['cid' => $candidateId]
                );
                if ($dailyGlobalLimit > 0 && $globalToday >= $dailyGlobalLimit) {
                    break;
                }
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (auto_apply_candidates): " . $t->getMessage());
        }
    }

    private function notifySubscriptionLimits(): void
    {
        try {
            $db = Database::getInstance();

            // Fetch active subscriptions with plan details
            $subs = $db->fetchAll(
                "SELECT es.*,
                        sp.max_job_posts, sp.max_resume_downloads, sp.name as plan_name,
                        e.user_id as employer_user_id
                 FROM employer_subscriptions es
                 JOIN subscription_plans sp ON es.plan_id = sp.id
                 JOIN employers e ON es.employer_id = e.id
                 WHERE es.status IN ('active', 'trial')"
            );

            foreach ($subs as $sub) {
                // 1. Check Job Posts Limit
                $this->checkLimit(
                    $db,
                    (int) $sub['employer_user_id'],
                    (int) $sub['id'],
                    'job_posts',
                    (int) ($sub['job_posts_used'] ?? 0),
                    (int) ($sub['max_job_posts'] ?? 0),
                    null// Lifetime limit
                );

                                                                                     // 2. Check Resume Downloads Limit
                $resetDate = $sub['last_usage_reset_at'] ?? date('Y-m-01 00:00:00'); // Default to start of month
                $this->checkLimit(
                    $db,
                    (int) $sub['employer_user_id'],
                    (int) $sub['id'],
                    'resume_downloads',
                    (int) ($sub['resume_downloads_used_this_month'] ?? 0),
                    (int) ($sub['max_resume_downloads'] ?? 0),
                    $resetDate
                );
            }

        } catch (\Throwable $t) {
            error_log("Cron Error (notify_subscription_limits): " . $t->getMessage());
        }
    }

    private function checkLimit(Database $db, int $userId, int $subId, string $type, int $used, int $limit, ?string $since): void
    {
        if ($limit <= 0) {
            return;
        }
        // Unlimited or invalid

        $percent   = ($used / $limit) * 100;
        $threshold = 0;

        if ($percent >= 100) {
            $threshold = 100;
        } elseif ($percent >= 90) {
            $threshold = 90;
        } elseif ($percent >= 80) {
            $threshold = 80;
        } else {
            return;
        }

        $templateKey = "limit_alert_{$type}_{$threshold}";

        // Check if already notified for this threshold in the current period
        $sql = "SELECT id FROM notification_logs
                WHERE user_id = :uid
                  AND template_key = :tpl
                  AND metadata LIKE :meta";
        $params = [
            'uid'  => $userId,
            'tpl'  => $templateKey,
            'meta' => '%"subscription_id":' . $subId . '%',
        ];

        if ($since) {
            $sql             .= " AND created_at >= :since";
            $params['since']  = $since;
        }

        $exists  = $db->fetchOne($sql, $params);

        if (! $exists) {
            $title   = "Usage Alert: {$type}";
            $message = "";

            if ($threshold === 100) {
                $message = "You have reached 100% of your {$type} limit. Upgrade now to continue.";
            } else {
                $message = "You have used {$threshold}% of your {$type} limit.";
            }

            // Human readable type
            $typeLabel = $type === 'job_posts' ? 'Job Posting' : 'Resume Download';
            $title     = "{$typeLabel} Limit Alert";

            NotificationService::send(
                $userId,
                $templateKey,
                $title,
                $message,
                [
                    'subscription_id' => $subId,
                    'used'            => $used,
                    'limit'           => $limit,
                    'threshold'       => $threshold,
                    'limit_type'      => $type,
                    'link'            => '/employer/subscription',
                    'email_template'  => 'generic_notification',
                ],
                '/employer/subscription'
            );
        }
    }

    private function monitorAggregateMinute(): void
    {
        try {
            $db      = Database::getInstance();
            $rpmRow  = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL 1 MINUTE");
            $respRow = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL 1 MINUTE");
            $errRow  = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type IN ('error','critical') AND created_at >= NOW() - INTERVAL 1 MINUTE");
            $payload = json_encode([
                'rpm'    => (int) ($rpmRow['c'] ?? 0),
                'avg_ms' => (int) ($respRow['a'] ?? 0),
                'errors' => (int) ($errRow['c'] ?? 0),
            ], JSON_UNESCAPED_UNICODE);
            $db->query(
                "INSERT INTO system_logs (type, module, message, user_id, created_at)
                 VALUES ('monitor_minute', '/monitor', :msg, 0, NOW())",
                ['msg' => $payload]
            );
        } catch (\Throwable $t) {
            error_log("Cron Error (monitor_aggregate_minute): " . $t->getMessage());
        }
    }

    private function monitorSystemStats(): void
    {
        try {
            $cpu  = null;
            $ram  = null;
            $disk = null;
            if (function_exists('sys_getloadavg')) {
                $load  = sys_getloadavg();
                $cores = (int) ($_ENV['CPU_CORES'] ?? ($_SERVER['NUMBER_OF_PROCESSORS'] ?? 1));
                $cpu   = $cores > 0 ? min(100, round(($load[0] / $cores) * 100)) : null;
            }
            if (stripos(PHP_OS, 'WIN') === 0) {
                $out = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
                if ($out) {
                    $lines = explode("\n", trim($out));
                    $free  = null;
                    $total = null;
                    foreach ($lines as $ln) {
                        if (stripos($ln, 'FreePhysicalMemory') === 0) {$free = (int) trim(explode('=', $ln)[1] ?? 0);}
                        if (stripos($ln, 'TotalVisibleMemorySize') === 0) {$total = (int) trim(explode('=', $ln)[1] ?? 0);}
                    }
                    if ($free && $total) {
                        $usedPct = 100 - round(($free / $total) * 100);
                        $ram     = max(0, min(100, $usedPct));
                    }
                }
            }
            $totalDisk = @disk_total_space(__DIR__ . '/../../') ?: 0;
            $freeDisk  = @disk_free_space(__DIR__ . '/../../') ?: 0;
            if ($totalDisk > 0) {$disk = round((($totalDisk - $freeDisk) / $totalDisk) * 100);}
            $payload = json_encode(['cpu' => $cpu, 'ram' => $ram, 'disk' => $disk], JSON_UNESCAPED_UNICODE);
            $db      = Database::getInstance();
            $db->query(
                "INSERT INTO system_logs (type, module, message, user_id, created_at)
                 VALUES ('server_snapshot', '/monitor', :msg, 0, NOW())",
                ['msg' => $payload]
            );
        } catch (\Throwable $t) {
            error_log("Cron Error (monitor_system_stats): " . $t->getMessage());
        }
    }

    private function monitorDbLoad(): void
    {
        try {
            $db     = Database::getInstance();
            $tables = ['users', 'jobs', 'applications', 'auto_apply_logs', 'payments'];
            foreach ($tables as $t) {
                $avg     = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type IN ('query_time','slow_query') AND table_name = :t AND created_at >= NOW() - INTERVAL 5 MINUTE", ['t' => $t]);
                $rows    = $db->fetchOne("SELECT COUNT(*) as c FROM {$t}");
                $ins     = $db->fetchOne("SELECT COUNT(*) as c FROM {$t} WHERE created_at >= NOW() - INTERVAL 1 MINUTE");
                $upd     = $db->fetchOne("SELECT COUNT(*) as c FROM {$t} WHERE updated_at >= NOW() - INTERVAL 1 MINUTE");
                $payload = json_encode([
                    'table' => $t, 'rows' => (int) ($rows['c'] ?? 0), 'ins_min' => (int) ($ins['c'] ?? 0), 'upd_min' => (int) ($upd['c'] ?? 0), 'avg_ms' => (float) ($avg['a'] ?? 0.0),
                ], JSON_UNESCAPED_UNICODE);
                $db->query("INSERT INTO system_logs (type, module, table_name, message, user_id, created_at) VALUES ('db_load','/monitor', :t, :msg, 0, NOW())", ['t' => $t, 'msg' => $payload]);
            }
        } catch (\Throwable $t) {
            error_log("Cron Error (monitor_db_load): " . $t->getMessage());
        }
    }

    private function monitorQueueStats(): void
    {
        try {
            $db        = Database::getInstance();
            $hasQueue  = $db->fetchOne("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_name = 'queue_jobs'");
            $processed = 0;
            $failed    = 0;
            if ((int) ($hasQueue['c'] ?? 0) > 0) {
                $p         = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status='processed' AND updated_at >= NOW() - INTERVAL 1 MINUTE");
                $f         = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status='failed' AND updated_at >= NOW() - INTERVAL 1 MINUTE");
                $processed = (int) ($p['c'] ?? 0);
                $failed    = (int) ($f['c'] ?? 0);
            } else {
                $f      = $db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action LIKE '%job_failed%' AND created_at >= NOW() - INTERVAL 1 MINUTE");
                $failed = (int) ($f['c'] ?? 0);
            }
            $payload = json_encode(['processed' => $processed, 'failed' => $failed], JSON_UNESCAPED_UNICODE);
            $db->query("INSERT INTO system_logs (type, module, message, user_id, created_at) VALUES ('queue_stats','/monitor', :msg, 0, NOW())", ['msg' => $payload]);
        } catch (\Throwable $t) {
            error_log("Cron Error (monitor_queue_stats): " . $t->getMessage());
        }
    }
}
