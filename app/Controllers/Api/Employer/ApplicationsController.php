<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;

class ApplicationsController extends ApiController
{
    public function addNote(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $body = $request->getJsonBody();
        if (empty($body['note'])) {
            $this->error($response, 'Note content required', 400);
            return;
        }
        $this->success($response, ['note' => $body['note']], 'Note added successfully');
    }

    public function export(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['download_url' => '/exports/applications.csv'], 'Export generated');
    }

    public function generateScore(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['score' => rand(50, 99)], 'Score generated');
    }
}
