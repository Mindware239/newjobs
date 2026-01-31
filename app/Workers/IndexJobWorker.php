<?php

declare(strict_types=1);

namespace App\Workers;

use App\Services\ESService;

class IndexJobWorker extends BaseWorker
{
    public function __construct()
    {
        parent::__construct(static::getQueueName());
    }

    protected static function getQueueName(): string
    {
        return 'queue:index_job';
    }

    public function process(array $data): bool
    {
        if (!isset($data['job_id'])) {
            return false;
        }

        $esService = new ESService();
        return $esService->indexJob((int)$data['job_id']);
    }
}

