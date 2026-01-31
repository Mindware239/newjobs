<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Employer;
use App\Services\MailService;

class EmployerVerificationService
{
    private const SCORE_RULES = [
        'email_verified'      => 10,
        'domain_matched'      => 15,
        'ocr_success'         => 15,
        'gst_valid'           => 10,
        'address_match'       => 10,
        'reupload_attempt'    => -15,
        'vpn_detected'        => -20,
        'free_email_domain'   => -20,
        'duplicate_gst'       => -50,
    ];

    public function evaluate(Employer $employer, array $context): array
    {
        $score = 0;
        $logs = [];

        foreach (self::SCORE_RULES as $rule => $value) {
            $result = $this->$rule($employer, $context);
            if ($result['passed']) {
                $score += $value;
            }
            $this->log($employer->id, $rule, $result, $result['passed'] ? $value : 0);
            $logs[$rule] = $result;
        }

        $riskLevel = $this->riskLevel($score);
        $this->storeScore($employer->id, $score, $riskLevel);

        if ($riskLevel === 'blocked') {
            $this->blacklist($employer, $logs);
        }

        return ['score' => $score, 'risk_level' => $riskLevel, 'logs' => $logs];
    }

    private function domain_matched(Employer $employer, array $context): array
    {
        $emailDomain = substr(strrchr($context['user_email'], '@'), 1);
        $websiteDomain = parse_url($employer->website, PHP_URL_HOST) ?? '';
        $match = str_ends_with($websiteDomain, $emailDomain);
        if (!$match) {
            $match = levenshtein($websiteDomain, $emailDomain) <= 2;
        }
        return ['passed' => $match, 'details' => compact('emailDomain', 'websiteDomain')];
    }

    private function gst_valid(Employer $employer, array $context): array
    {
        $gst = $context['documents']['gst'] ?? null;
        $valid = $gst && preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gst);
        return ['passed' => (bool)$valid, 'details' => ['gst' => $gst]];
    }

    // ...other rule methods...

    private function storeScore(int $employerId, int $score, string $riskLevel): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO employer_risk_scores (employer_id, score, risk_level, last_updated)
             VALUES (:id, :score, :risk)
             ON DUPLICATE KEY UPDATE score = :score, risk_level = :risk, last_updated = NOW()",
            ['id' => $employerId, 'score' => $score, 'risk' => $riskLevel]
        );
    }

    private function riskLevel(int $score): string
    {
        if ($score >= 80) {
            return 'low';
        }
        if ($score >= 50) {
            return 'medium';
        }
        if ($score >= 0) {
            return 'high';
        }
        return 'blocked';
    }

    private function log(int $employerId, string $rule, array $result, int $delta): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO employer_verification_logs (employer_id, rule_name, result, risk_score_change)
             VALUES (:employer_id, :rule_name, :result, :delta)",
            [
                'employer_id' => $employerId,
                'rule_name'   => $rule,
                'result'      => json_encode($result),
                'delta'       => $delta,
            ]
        );
    }

    private function blacklist(Employer $employer, array $logs): void
    {
        $db = Database::getInstance();

        if (!empty($employer->company_name)) {
            $this->insertBlacklist('company', $employer->company_name, 'Risk score blocked');
        }

        if (!empty($employer->website)) {
            $host = parse_url($employer->website, PHP_URL_HOST);
            if ($host) {
                $this->insertBlacklist('domain', $host, 'Risk score blocked');
            }
        }
    }

    private function insertBlacklist(string $type, string $value, string $reason): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT IGNORE INTO employer_blacklist (type, value, reason) VALUES (:type, :value, :reason)",
            ['type' => $type, 'value' => $value, 'reason' => $reason]
        );
    }
}