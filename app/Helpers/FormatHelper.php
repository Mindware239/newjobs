<?php
namespace App\Helpers;

class FormatHelper
{
    public static function timeAgo(?string $timestamp): string
    {
        if (empty($timestamp)) {
            return 'Recently';
        }

        $createdTime = strtotime($timestamp);
        $now = time();
        $diff = $now - $createdTime;
        $minutes = floor($diff / 60);
        $hours = floor($diff / 3600);
        $days = floor($diff / 86400);

        if ($minutes < 1) {
            return 'Just now';
        } elseif ($minutes < 60) {
            return $minutes . ' min ago';
        } elseif ($hours < 24) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    public static function formatSalary($min, $max, string $currency = 'INR'): array
    {
        return [
            'min' => $min !== null ? (int)$min : null,
            'max' => $max !== null ? (int)$max : null,
            'currency' => $currency
        ];
    }

    public static function formatEmploymentType(?string $type): string
    {
        $type = $type ?? 'full_time';
        $map = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance'
        ];
        return $map[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}