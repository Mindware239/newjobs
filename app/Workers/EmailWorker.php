<?php

declare(strict_types=1);

namespace App\Workers;

use App\Services\NotificationService;

class EmailWorker extends BaseWorker
{
    public function __construct()
    {
        parent::__construct(static::getQueueName());
    }

    protected static function getQueueName(): string
    {
        return 'queue:email';
    }

    public function process(array $data): bool
    {
        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? '';
        $template = $data['template'] ?? '';
        $templateData = $data['data'] ?? [];
        if (isset($data['employer_id'])) {
            $templateData['employer_id'] = $data['employer_id'];
        }
        if (isset($data['candidate_user_id'])) {
            $templateData['candidate_user_id'] = $data['candidate_user_id'];
        }

        if (!$to) {
            return false;
        }

        $notificationService = new NotificationService();
        return $notificationService->sendEmail($to, $subject, $template, $templateData);
    }
}

