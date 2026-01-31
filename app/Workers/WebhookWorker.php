<?php

declare(strict_types=1);

namespace App\Workers;

use App\Models\Webhook;

class WebhookWorker extends BaseWorker
{
    public function __construct()
    {
        parent::__construct(static::getQueueName());
    }

    protected static function getQueueName(): string
    {
        return 'queue:webhook';
    }

    public function process(array $data): bool
    {
        $employerId = $data['employer_id'] ?? null;
        $event = $data['event'] ?? '';
        $payload = $data['data'] ?? [];

        if (!$employerId || !$event) {
            return false;
        }

        $webhooks = Webhook::where('employer_id', '=', $employerId)
            ->where('active', '=', 1)
            ->get();

        $success = true;
        foreach ($webhooks as $webhook) {
            if ($webhook->shouldTrigger($event)) {
                if (!$this->deliver($webhook, $event, $payload)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    private function deliver(Webhook $webhook, string $event, array $payload): bool
    {
        $url = $webhook->url;
        $secret = $webhook->secret;

        $body = json_encode([
            'event' => $event,
            'data' => $payload,
            'timestamp' => time()
        ]);

        $signature = hash_hmac('sha256', $body, $secret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Webhook-Signature: ' . $signature,
                'X-Webhook-Event: ' . $event
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success = $httpCode >= 200 && $httpCode < 300;

        // Update last delivery
        $webhook->last_delivery_at = date('Y-m-d H:i:s');
        $webhook->save();

        return $success;
    }
}

