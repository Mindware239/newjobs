<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\PortalRegistration;

/**
 * Connects skill-development candidates with Jobsence mentors.
 *
 *  candidate selected (or admin / candidate picks a mentor)
 *      → request emailed to the mentor with Accept / Decline links (no login needed)
 *      → accepted: candidate gets the mentor's details
 *      → declined / no reply in 3 days / no matching mentor:
 *        candidate gets a link to choose from available mentors (Online and Offline Delhi-NCR)
 *
 * Only a candidate's (max 5) chosen skills are ever matched.
 * Available mentors = paid mentor registrations that admin marked "selected" (added to the Mentor Team).
 */
class MentorMatching
{
    public const RESPONSE_DAYS = 3;

    // ------------------------------------------------------------------
    // Queries
    // ------------------------------------------------------------------

    /** Candidate's skill choices, in priority order. */
    public static function candidateSkills(array $candidate): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string)($candidate['categories'] ?? '')))));
    }

    /**
     * Mentors who can teach at least one of the candidate's skills, best first.
     * Each item: mentor registration + matched_skill, skill_rank, modes (online/offline), languages.
     * @param string|null $modeFilter 'online' | 'offline_ncr' | null (any)
     */
    public static function availableMentors(array $candidate, ?string $modeFilter = null, int $limit = 50): array
    {
        self::ensureSchema();
        $skills = self::candidateSkills($candidate);
        if (!$skills) {
            return [];
        }

        $declined = array_column(Database::getInstance()->fetchAll(
            "SELECT mentor_reg_id FROM mentor_assignments WHERE candidate_reg_id = ? AND status IN ('declined','expired','cancelled')",
            [(int)$candidate['id']]
        ), 'mentor_reg_id');

        $mentors = Database::getInstance()->fetchAll(
            "SELECT r.*, (SELECT COUNT(*) FROM mentor_assignments a WHERE a.mentor_reg_id = r.id AND a.status = 'accepted') AS active_students
             FROM portal_registrations r
             WHERE r.type = 'provider' AND r.payment_status = 'paid' AND r.status = 'selected'"
        );

        $candLangs = (array)(self::detailsOf($candidate)['languages'] ?? []);
        $out = [];
        foreach ($mentors as $m) {
            if (in_array($m['id'], $declined)) {
                continue;
            }
            $d = self::detailsOf($m);
            $modes = self::modesOf((string)($d['training_mode'] ?? 'online'));
            if ($modeFilter && !in_array($modeFilter, $modes, true)) {
                continue;
            }
            $teaches = array_map(static fn($s) => mb_strtolower(trim($s)), explode(',', (string)$m['categories']));
            $rank = null;
            $matched = null;
            foreach ($skills as $i => $skill) {
                $s = mb_strtolower($skill);
                foreach ($teaches as $t) {
                    if ($t !== '' && ($t === $s || str_contains($t, $s) || str_contains($s, $t))) {
                        $rank = $i;
                        $matched = $skill;
                        break 2;
                    }
                }
            }
            if ($rank === null) {
                continue;
            }
            $langs = (array)($d['languages'] ?? []);
            $m['details'] = $d;
            $m['matched_skill'] = $matched;
            $m['skill_rank'] = $rank;
            $m['modes'] = $modes;
            $m['language_overlap'] = count(array_intersect($candLangs, $langs));
            $out[] = $m;
        }

        usort($out, static fn($a, $b) => [$a['skill_rank'], -$a['language_overlap'], (int)$a['active_students'], (int)$a['id']]
            <=> [$b['skill_rank'], -$b['language_overlap'], (int)$b['active_students'], (int)$b['id']]);

        return array_slice($out, 0, $limit);
    }

    /** Modes the candidate can be taught in, given their own preference. */
    public static function candidateMode(array $candidate): string
    {
        $d = self::detailsOf($candidate);
        return ($d['training_mode'] ?? 'online') === 'offline_ncr' ? 'offline_ncr' : 'online';
    }

    public static function assignmentsForCandidate(int $candidateRegId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT a.*, m.full_name AS mentor_name, m.reg_no AS mentor_reg_no
             FROM mentor_assignments a JOIN portal_registrations m ON m.id = a.mentor_reg_id
             WHERE a.candidate_reg_id = ? ORDER BY a.id DESC",
            [$candidateRegId]
        );
    }

    public static function assignmentsForMentor(int $mentorRegId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT a.*, c.full_name AS candidate_name, c.reg_no AS candidate_reg_no
             FROM mentor_assignments a JOIN portal_registrations c ON c.id = a.candidate_reg_id
             WHERE a.mentor_reg_id = ? ORDER BY a.id DESC",
            [$mentorRegId]
        );
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }
        self::ensureSchema();
        return Database::getInstance()->fetchOne('SELECT * FROM mentor_assignments WHERE token = ?', [$token]);
    }

    // ------------------------------------------------------------------
    // Actions
    // ------------------------------------------------------------------

    /**
     * Send a mentor request for a candidate. With $mentorRegId null the best available mentor is chosen.
     * If no mentor is available the candidate is emailed the list of alternatives instead.
     * @return array{ok: bool, message: string, assignment_id?: int}
     */
    public static function request(array $candidate, ?int $mentorRegId, string $source): array
    {
        self::ensureSchema();
        $db = Database::getInstance();

        if (($candidate['type'] ?? '') !== 'skill' || ($candidate['payment_status'] ?? '') !== 'paid') {
            return ['ok' => false, 'message' => 'Only paid skill-development candidates can be matched with a mentor.'];
        }
        if (!PortalRegistration::isValid($candidate)) {
            return ['ok' => false, 'message' => 'This registration is closed. Please register again.'];
        }
        $open = $db->fetchOne(
            "SELECT id, status FROM mentor_assignments WHERE candidate_reg_id = ? AND status IN ('pending','accepted') LIMIT 1",
            [(int)$candidate['id']]
        );
        if ($open) {
            return ['ok' => false, 'message' => $open['status'] === 'accepted'
                ? 'This candidate already has a mentor.'
                : 'A mentor request is already waiting for a reply.'];
        }

        $mode = self::candidateMode($candidate);
        $options = self::availableMentors($candidate);
        if ($mentorRegId !== null) {
            $mentor = null;
            foreach ($options as $o) {
                if ((int)$o['id'] === $mentorRegId) {
                    $mentor = $o;
                    break;
                }
            }
            if (!$mentor) {
                return ['ok' => false, 'message' => 'That mentor is not available for this candidate’s chosen skills.'];
            }
        } else {
            // prefer mentors who teach in the candidate's preferred mode
            $preferred = array_values(array_filter($options, static fn($o) => in_array($mode, $o['modes'], true)));
            $mentor = $preferred[0] ?? null;
        }

        if (!$mentor) {
            self::offerAlternatives($candidate, 'no_match');
            return ['ok' => false, 'message' => 'No matching mentor is available right now. The candidate has been emailed the list of available mentors.'];
        }

        $mentorMode = in_array($mode, $mentor['modes'], true) ? $mode : $mentor['modes'][0];
        $token = bin2hex(random_bytes(20));
        $db->execute(
            "INSERT INTO mentor_assignments (candidate_reg_id, mentor_reg_id, skill, mode, status, token, source, expires_at)
             VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW() + INTERVAL " . (int)self::RESPONSE_DAYS . " DAY)",
            [(int)$candidate['id'], (int)$mentor['id'], (string)$mentor['matched_skill'], $mentorMode, $token, $source]
        );
        $id = (int)$db->lastInsertId();

        MentorMailer::requestToMentor($mentor, $candidate, (string)$mentor['matched_skill'], $mentorMode, $token);

        return ['ok' => true, 'message' => 'Request sent to mentor ' . $mentor['full_name'] . ' (' . $mentor['reg_no'] . ').', 'assignment_id' => $id];
    }

    /** Mentor's answer via the emailed link. */
    public static function respond(array $assignment, bool $accept): string
    {
        $db = Database::getInstance();
        if ($assignment['status'] !== 'pending') {
            return $assignment['status'];
        }
        if (strtotime((string)$assignment['expires_at']) < time()) {
            self::expire($assignment);
            return 'expired';
        }

        $status = $accept ? 'accepted' : 'declined';
        $db->execute("UPDATE mentor_assignments SET status = ?, responded_at = NOW() WHERE id = ? AND status = 'pending'", [$status, (int)$assignment['id']]);

        $candidate = PortalRegistration::find((int)$assignment['candidate_reg_id']);
        $mentor = PortalRegistration::find((int)$assignment['mentor_reg_id']);
        if ($candidate && $mentor) {
            if ($accept) {
                MentorMailer::acceptedToCandidate($candidate, $mentor, $assignment);
                MentorMailer::acceptedToOwner($candidate, $mentor, $assignment);
            } else {
                self::offerAlternatives($candidate, 'declined');
            }
        }
        return $status;
    }

    /** Candidate picked a mentor from the alternatives page. */
    public static function candidateChoose(array $candidate, int $mentorRegId): array
    {
        return self::request($candidate, $mentorRegId, 'candidate_choice');
    }

    /** Cron: pending requests older than RESPONSE_DAYS → expired → candidate gets alternatives. */
    public static function expireOverdue(): int
    {
        self::ensureSchema();
        $rows = Database::getInstance()->fetchAll("SELECT * FROM mentor_assignments WHERE status = 'pending' AND expires_at < NOW() LIMIT 200");
        foreach ($rows as $a) {
            self::expire($a);
        }
        return count($rows);
    }

    private static function expire(array $assignment): void
    {
        Database::getInstance()->execute("UPDATE mentor_assignments SET status = 'expired', responded_at = NOW() WHERE id = ? AND status = 'pending'", [(int)$assignment['id']]);
        $candidate = PortalRegistration::find((int)$assignment['candidate_reg_id']);
        if ($candidate && ($assignment['kind'] ?? 'skill') === 'skill') {
            self::offerAlternatives($candidate, 'expired');
        }
    }

    /** Email the candidate the link to choose another mentor (online & offline options). */
    public static function offerAlternatives(array $candidate, string $reason): void
    {
        $online = self::availableMentors($candidate, 'online', 100);
        $offline = self::availableMentors($candidate, 'offline_ncr', 100);
        MentorMailer::alternativesToCandidate($candidate, count($online), count($offline), $reason);
    }

    /** Public skills showcase: skills with mentors (training available) and skills in demand without mentors. */
    public static function skillShowcase(int $limit = 40): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $offered = [];
        foreach ($db->fetchAll("SELECT categories, details FROM portal_registrations WHERE type = 'provider' AND payment_status = 'paid' AND status = 'selected'") as $m) {
            $d = self::detailsOf($m);
            $modes = self::modesOf((string)($d['training_mode'] ?? 'online'));
            foreach (array_filter(array_map('trim', explode(',', (string)$m['categories']))) as $skill) {
                $key = mb_strtolower($skill);
                $offered[$key] ??= ['skill' => $skill, 'mentors' => 0, 'online' => false, 'offline' => false];
                $offered[$key]['mentors']++;
                $offered[$key]['online'] = $offered[$key]['online'] || in_array('online', $modes, true);
                $offered[$key]['offline'] = $offered[$key]['offline'] || in_array('offline_ncr', $modes, true);
            }
        }

        $demand = [];
        foreach ($db->fetchAll("SELECT categories FROM portal_registrations WHERE type = 'skill' AND payment_status = 'paid'") as $c) {
            foreach (array_filter(array_map('trim', explode(',', (string)$c['categories']))) as $skill) {
                $key = mb_strtolower($skill);
                $demand[$key] ??= ['skill' => $skill, 'learners' => 0];
                $demand[$key]['learners']++;
            }
        }
        // Core trades Jobsence always recruits mentors for, even before demand data exists
        foreach (\App\Controllers\Front\SkillDevelopmentController::POPULAR_SKILLS as $i => $skill) {
            $key = mb_strtolower($skill);
            $demand[$key] ??= ['skill' => $skill, 'learners' => 0];
            $demand[$key]['core'] = true;
        }

        $wanted = array_values(array_filter($demand, static fn($s, $k) => !isset($offered[$k]), ARRAY_FILTER_USE_BOTH));
        usort($wanted, static fn($a, $b) => [$b['learners'], $a['skill']] <=> [$a['learners'], $b['skill']]);
        $offeredList = array_values($offered);
        usort($offeredList, static fn($a, $b) => [$b['mentors'], $a['skill']] <=> [$a['mentors'], $b['skill']]);

        return ['offered' => array_slice($offeredList, 0, $limit), 'wanted' => array_slice($wanted, 0, $limit)];
    }

    /** Registration details whether still JSON (raw DB row) or already decoded (PortalRegistration::find). */
    private static function detailsOf(array $reg): array
    {
        $d = $reg['details'] ?? [];
        return is_array($d) ? $d : (json_decode((string)$d, true) ?: []);
    }

    public static function modesOf(string $trainingMode): array
    {
        return match ($trainingMode) {
            'both' => ['online', 'offline_ncr'],
            'offline_ncr' => ['offline_ncr'],
            default => ['online'],
        };
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        PortalRegistration::ensureSchema();
        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return;
        }
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS mentor_assignments (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                candidate_reg_id BIGINT UNSIGNED NOT NULL,
                mentor_reg_id BIGINT UNSIGNED NOT NULL,
                skill VARCHAR(120) NOT NULL,
                mode ENUM('online','offline_ncr') NOT NULL DEFAULT 'online',
                status ENUM('pending','accepted','declined','expired','cancelled') NOT NULL DEFAULT 'pending',
                token CHAR(40) NOT NULL,
                source ENUM('auto','admin','candidate_choice') NOT NULL DEFAULT 'auto',
                expires_at DATETIME NOT NULL,
                responded_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_mentor_assign_token (token),
                KEY idx_mentor_assign_candidate (candidate_reg_id, status),
                KEY idx_mentor_assign_mentor (mentor_reg_id, status),
                KEY idx_mentor_assign_pending (status, expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $done = true;
    }
}
