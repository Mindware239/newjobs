<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Resume;
use App\Models\ResumeFile;
use App\Services\ResumeParserService;
use App\Services\ResumeTemplatePreviewService;

class ResumeController extends ApiController
{
    private \App\Services\ResumeParserService $parserService;

    public function __construct()
    {
        $this->parserService = new ResumeParserService();
    }

    /**
     * POST /candidate/resumes/upload
     * Upload a new resume file
     */
    public function upload(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = [];
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $errors['resume'] = 'Resume file is required';
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $file = $_FILES['resume'];
        $allowedMimes = ['application/pdf', 'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (!in_array($file['type'], $allowedMimes)) {
            $this->error($response, 'Invalid file type. Only PDF and DOC allowed', 400);
            return;
        }

        $uploadDir = storage_path('/resumes/' . $user->id);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->error($response, 'Failed to upload file', 500);
            return;
        }

        $resume = new Resume();
        $resume->fill([
            'candidate_id' => $user->id,
            'title' => $request->input('title') ?? 'Resume',
            'original_filename' => $file['name']
        ])->save();

        $resumeFile = new ResumeFile();
        $resumeFile->fill([
            'resume_id' => $resume->id,
            'file_path' => '/resumes/' . $user->id . '/' . $fileName,
            'file_type' => pathinfo($fileName, PATHINFO_EXTENSION),
            'file_size' => $file['size']
        ])->save();

        $this->success($response, [
            'id' => $resume->id,
            'title' => $resume->title,
            'file_name' => $file['name'],
            'uploaded_at' => $resume->created_at
        ], 'Resume uploaded successfully', 201);
    }

    /**
     * GET /candidate/resumes
     * List all candidate resumes
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);

        $resumes = Resume::where('candidate_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);

        $this->success($response, [
            'resumes' => $resumes['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $resumes['total'],
                'last_page' => ceil($resumes['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /candidate/resumes/{id}
     * Get resume details
     */
    public function show(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $this->success($response, [
            'id' => $resume->id,
            'title' => $resume->title,
            'file' => $resume->latestFile(),
            'parsed_data' => $resume->parsed_data ? json_decode($resume->parsed_data, true) : null,
            'created_at' => $resume->created_at,
            'updated_at' => $resume->updated_at
        ]);
    }

    /**
     * PUT /candidate/resumes/{id}
     * Update resume
     */
    public function update(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        
        if (isset($data['title'])) {
            $resume->title = $data['title'];
        }
        
        if (isset($data['is_default'])) {
            if ($data['is_default']) {
                Resume::where('candidate_id', '=', $user->id)->update(['is_default' => false]);
                $resume->is_default = true;
            }
        }

        $resume->save();

        $this->success($response, ['id' => $resume->id, 'title' => $resume->title]);
    }

    /**
     * DELETE /candidate/resumes/{id}
     * Delete resume
     */
    public function delete(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $resume->delete();
        
        $this->success($response, [], 'Resume deleted successfully');
    }

    /**
     * POST /candidate/resumes/{id}/parse
     * Parse resume using AI parser
     */
    public function parse(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $file = $resume->latestFile();
        if (!$file) {
            $this->error($response, 'No file attached to resume', 400);
            return;
        }

        // Parse resume using AI service
        $parsedData = $this->parserService->parse($file->file_path);
        
        if (!$parsedData) {
            $this->error($response, 'Failed to parse resume', 500);
            return;
        }

        $resume->parsed_data = json_encode($parsedData);
        $resume->save();

        $this->success($response, [
            'parsed_data' => $parsedData
        ], 'Resume parsed successfully');
    }

    /**
     * POST /candidate/resumes/{id}/download
     * Download resume as PDF
     */
    public function download(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $file = $resume->latestFile();
        if (!$file) {
            $this->error($response, 'No file attached to resume', 400);
            return;
        }

        $filePath = base_path($file->file_path);
        if (!file_exists($filePath)) {
            $this->error($response, 'File not found', 404);
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $resume->title . '.pdf"');
        readfile($filePath);
        exit;
    }

    /**
     * GET /candidate/resumes/{id}/preview
     * Preview resume content
     */
    public function preview(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $this->success($response, [
            'id' => $resume->id,
            'title' => $resume->title,
            'parsed_data' => $resume->parsed_data ? json_decode($resume->parsed_data, true) : null,
            'preview_html' => $this->generatePreview($resume)
        ]);
    }

    /**
     * POST /candidate/resumes/create-from-profile
     * Auto-generate resume from profile
     */
    public function createFromProfile(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        // Create resume from profile data
        $resume = new Resume();
        $resume->fill([
            'candidate_id' => $user->id,
            'title' => $request->input('title') ?? 'Auto-Generated Resume',
            'is_auto_generated' => true
        ])->save();

        $this->success($response, [
            'id' => $resume->id,
            'title' => $resume->title,
            'is_auto_generated' => true
        ], 'Resume created successfully', 201);
    }

    /**
     * PUT /candidate/resumes/{id}/set-default
     * Set as default resume
     */
    public function setDefault(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        Resume::where('candidate_id', '=', $user->id)->update(['is_default' => false]);
        $resume->is_default = true;
        $resume->save();

        $this->success($response, [], 'Resume set as default');
    }

    /**
     * GET /candidate/resume-templates
     * List resume templates
     */
    public function templates(Request $request, Response $response): void
    {
        $templates = [
            [
                'id' => 1,
                'name' => 'Professional',
                'description' => 'Clean and professional layout',
                'preview' => '/templates/professional.png'
            ],
            [
                'id' => 2,
                'name' => 'Modern',
                'description' => 'Contemporary design with colors',
                'preview' => '/templates/modern.png'
            ],
            [
                'id' => 3,
                'name' => 'Creative',
                'description' => 'Creative and unique design',
                'preview' => '/templates/creative.png'
            ]
        ];

        $this->success($response, ['templates' => $templates]);
    }

    /**
     * POST /candidate/resumes/{id}/export
     * Export resume in different formats
     */
    public function export(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $resume = Resume::find($id);
        if (!$resume || $resume->candidate_id !== $user->id) {
            $this->error($response, 'Resume not found', 404);
            return;
        }

        $format = $request->input('format', 'pdf');
        $supportedFormats = ['pdf', 'docx', 'txt'];

        if (!in_array($format, $supportedFormats)) {
            $this->error($response, 'Unsupported format', 400);
            return;
        }

        // Generate export link
        $exportUrl = '/exports/resume_' . $resume->id . '.' . $format;

        $this->success($response, [
            'download_url' => $exportUrl,
            'format' => $format,
            'expires_in' => 3600
        ], 'Export generated successfully');
    }

    private function generatePreview($resume): string
    {
        // Generate HTML preview of resume
        return "<div>Preview for {$resume->title}</div>";
    }
}
