<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Webhook;

class WebhookController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $webhooks = Webhook::where('employer_id', '=', $employer->id)->get();

        // Mask secrets in list view
        $data = array_map(function($w) {
            $arr = $w->toArray();
            $arr['secret'] = '****' . substr($arr['secret'] ?? '', -4);
            return $arr;
        }, $webhooks);

        $response->json(['data' => $data]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        // 1. CSRF Protection
        $token = $request->header('X-CSRF-Token') ?? $request->input('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $employer = $this->currentUser->employer();
        $data = $request->getJsonBody();

        // 2. Strong Input Validation
        $errors = [];
        if (empty($data['url'])) {
            $errors['url'] = 'Webhook URL is required';
        } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL) || strpos($data['url'], 'https://') !== 0) {
            $errors['url'] = 'Webhook URL must be a valid HTTPS URL';
        }

        if (empty($data['events']) || !is_array($data['events'])) {
            $errors['events'] = 'At least one event must be selected';
        } else {
            $allowedEvents = ['payment.captured', 'payment.failed', 'refund.processed'];
            foreach ($data['events'] as $event) {
                if (!in_array($event, $allowedEvents)) {
                    $errors['events'] = 'Invalid event type: ' . $event;
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $response->json(['errors' => $errors], 422);
            return;
        }

        // 3. Better Error Handling
        try {
            $webhook = new Webhook();
            $webhook->fill([
                'employer_id' => $employer->id,
                'url' => $data['url'],
                'events' => json_encode($data['events']),
                'secret' => bin2hex(random_bytes(16)),
                'active' => true
            ]);

            if ($webhook->save()) {
                // Show full secret ONLY on creation
                $response->json($webhook->toArray(), 201);
            } else {
                throw new \Exception('Failed to create webhook');
            }
        } catch (\Exception $e) {
            $response->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        // 1. CSRF Protection
        $token = $request->header('X-CSRF-Token') ?? $request->input('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $employer = $this->currentUser->employer();
        $webhookId = (int)$request->param('id');

        $webhook = Webhook::find($webhookId);
        if (!$webhook || $webhook->employer_id !== $employer->id) {
            $response->json(['error' => 'Webhook not found'], 404);
            return;
        }

        $data = $request->getJsonBody();
        
        // 2. Strong Input Validation
        $errors = [];
        if (isset($data['url'])) {
            if (empty($data['url'])) {
                $errors['url'] = 'Webhook URL is required';
            } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL) || strpos($data['url'], 'https://') !== 0) {
                $errors['url'] = 'Webhook URL must be a valid HTTPS URL';
            }
        }

        if (isset($data['events'])) {
            if (!is_array($data['events']) || empty($data['events'])) {
                 $errors['events'] = 'At least one event must be selected';
            } else {
                $allowedEvents = ['payment.captured', 'payment.failed', 'refund.processed'];
                foreach ($data['events'] as $event) {
                    if (!in_array($event, $allowedEvents)) {
                        $errors['events'] = 'Invalid event type: ' . $event;
                        break;
                    }
                }
            }
        }

        if (!empty($errors)) {
            $response->json(['errors' => $errors], 422);
            return;
        }

        try {
            $webhook->fill($data);

            if ($webhook->save()) {
                // Mask secret in update response
                $arr = $webhook->toArray();
                $arr['secret'] = '****' . substr($arr['secret'] ?? '', -4);
                $response->json($arr);
            } else {
                throw new \Exception('Failed to update webhook');
            }
        } catch (\Exception $e) {
             $response->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        // 1. CSRF Protection
        $token = $request->header('X-CSRF-Token') ?? $request->input('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $employer = $this->currentUser->employer();
        $webhookId = (int)$request->param('id');

        $webhook = Webhook::find($webhookId);
        if (!$webhook || $webhook->employer_id !== $employer->id) {
            $response->json(['error' => 'Webhook not found'], 404);
            return;
        }

        try {
            if ($webhook->delete()) {
                $response->json(['message' => 'Webhook deleted']);
            } else {
                throw new \Exception('Failed to delete webhook');
            }
        } catch (\Exception $e) {
            $response->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}

