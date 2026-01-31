<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (\Throwable $e) {}

use App\Core\Database;

function out(string $msg): void {
    echo $msg . PHP_EOL;
}

try {
    $db = Database::getInstance();

    // Find any candidate to attach the resume to
    $candidateRow = $db->fetchOne("SELECT id FROM candidates ORDER BY id DESC LIMIT 1");
    if (!$candidateRow || empty($candidateRow['id'])) {
        out("No candidates found. Please create a candidate first.");
        exit(2);
    }
    $candidateId = (int)$candidateRow['id'];
    out("Using candidate_id={$candidateId}");

    // Insert resume via raw execute (control)
    $title = 'Verification Resume ' . date('Y-m-d H:i:s');
    $db->execute(
        "INSERT INTO resumes (candidate_id, template_id, title, status, is_primary, created_at, updated_at)
         VALUES (:cid, 1, :title, 'draft', 0, NOW(), NOW())",
        ['cid' => $candidateId, 'title' => $title]
    );
    $resumeId = (int)$db->lastInsertId();
    $pdoLastId = (int)$db->getConnection()->lastInsertId();
    out("Created resume_id={$resumeId} (db->lastInsertId), pdo_last_id={$pdoLastId}");

    if ($resumeId <= 0 || $pdoLastId <= 0) {
        out("ERROR: lastInsertId() returned invalid value");
        exit(3);
    }

    // Insert a section referencing the created resume
    $sectionData = json_encode(['content' => ['text' => 'verification section']], JSON_UNESCAPED_UNICODE);
    $db->query(
        "INSERT INTO resume_sections (resume_id, section_type, section_data, sort_order, is_visible)
         VALUES (:rid, :type, :data, 0, 1)",
        ['rid' => $resumeId, 'type' => 'summary', 'data' => $sectionData]
    );
    out("Inserted section for resume_id={$resumeId} successfully");

    // Now test via Model::save path to ensure lastInsertId works in models
    $resumeModel = new \App\Models\Resume();
    $resumeModel->fill([
        'candidate_id' => $candidateId,
        'template_id' => 1,
        'title' => 'Model Resume ' . date('Y-m-d H:i:s'),
        'status' => 'draft'
    ]);
    $ok = $resumeModel->save();
    $modelResumeId = (int)($resumeModel->attributes['id'] ?? 0);
    out("Model save ok=" . ($ok ? '1' : '0') . ", model_resume_id={$modelResumeId}");
    if ($modelResumeId <= 0) {
        out("ERROR: Model::save did not capture insert id");
        exit(4);
    }
    $sectionModel = new \App\Models\ResumeSection();
    $sectionModel->fill([
        'resume_id' => $modelResumeId,
        'section_type' => 'summary',
        'section_data' => json_encode(['content' => ['text' => 'model section']], JSON_UNESCAPED_UNICODE),
        'sort_order' => 0,
        'is_visible' => 1
    ]);
    $ok2 = $sectionModel->save();
    out("Model section save ok=" . ($ok2 ? '1' : '0'));
    if (!$ok2) {
        out("ERROR: Model section insert failed");
        exit(5);
    }

    out("Verification completed: lastInsertId captured correctly and FK respected.");
    exit(0);
} catch (\Throwable $e) {
    out("Verification failed: " . $e->getMessage());
    exit(1);
}
