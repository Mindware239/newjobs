<?php
declare(strict_types=1);

namespace App\Workers;

use App\Core\Interfaces\WorkerInterface;
use App\Services\MailService;

class MailWorker implements WorkerInterface
{
    public function process(array $job): bool
    {
        $data = $job['data'];
        
        return MailService::sendEmail(
            $data['to'],
            $data['subject'],
            $data['htmlBody'],
            $data['fromEmail'] ?? null,
            $data['fromName'] ?? null,
            $data['attachments'] ?? []
        );
    }
}
