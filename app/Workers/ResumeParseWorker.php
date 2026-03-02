<?php

declare(strict_types=1);

namespace App\Workers;

use App\Core\Database;
use App\Models\ResumeFile;
use App\Services\ResumeParserService;
use App\Services\CandidateCreationService;

class ResumeParseWorker extends BaseWorker
{
    public function __construct()
    {
        parent::__construct(static::getQueueName());
    }

    protected static function getQueueName(): string
    {
        return 'queue:resume_parse';
    }

    public function process(array $data): bool
    {
        $limit = (int)($data['limit'] ?? 50);
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT * FROM resume_files WHERE status = 'pending' LIMIT " . $limit);
        if (empty($rows)) return true;
        $parser = new ResumeParserService();
        $creator = new CandidateCreationService();
        foreach ($rows as $row) {
            $file = new ResumeFile($row);
            $path = (string)$file->attributes['filepath'];
            if (!file_exists($path)) {
                $file->fill(['status' => 'failed', 'failure_reason' => 'not_found', 'processed_at' => date('Y-m-d H:i:s')]);
                $file->save();
                continue;
            }
            try {
                $parsed = $parser->parse($path);
                $res = $creator->createOrLinkFromParsedResume($parsed, $file, ['created_by' => 'bulk', 'source' => 'bulk_upload']);
                $file->fill(['status' => 'processed', 'parsed_data' => json_encode($parsed), 'processed_at' => date('Y-m-d H:i:s')]);
                $file->save();
            } catch (\Throwable $e) {
                $file->fill(['status' => 'failed', 'failure_reason' => substr($e->getMessage(), 0, 255), 'processed_at' => date('Y-m-d H:i:s')]);
                $file->save();
            }
        }
        return true;
    }
}
